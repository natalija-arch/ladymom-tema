<?php
/**
 * ERP (TiramisuERP) – veza preko WooCommerce REST API-ja.
 *
 * Porudžbine sa starog sajta su prenesene pod novim brojevima (stari broj je u
 * meta polju `_lm_stari_broj`). ERP ih već ima pod starim brojevima, pa ih ne
 * smije ponovo vidjeti kao nove. Zato ERP-ov korisnik u listi porudžbina preko
 * REST API-ja dobija samo porudžbine nastale na novom sajtu.
 *
 * Ostali (admin, WooCommerce aplikacija, izvještaji) i dalje vide sve porudžbine.
 * ERP-ov korisnik je Zoran Mastilović; ID se čuva u opciji `lm_erp_korisnik`.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_rest_orders_prepare_object_query', function ( $args ) {
	$erp = (int) get_option( 'lm_erp_korisnik', 0 );
	if ( ! $erp || get_current_user_id() !== $erp ) {
		return $args;
	}
	$mq   = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
	$mq[] = array(
		'key'     => '_lm_stari_broj',
		'compare' => 'NOT EXISTS',
	);
	// Test porudžbine (19.10.2026): ERP ih ne vidi.
	$mq[] = array(
		'key'     => '_lm_test',
		'compare' => 'NOT EXISTS',
	);
	$args['meta_query'] = $mq;
	return $args;
} );

/**
 * Test porudžbine: kupac sa adresom natalija+test…@ladymom.me (end-to-end testiranje
 * 19.10.2026). Dobijaju oznaku `_lm_test`, pa ih ERP ne preuzima, ni u listi ni pojedinačno.
 */
function lm_je_test_email( $email ) {
	return (bool) preg_match( '/^natalija\+test[^@]*@ladymom\.me$/i', trim( (string) $email ) );
}
add_action( 'woocommerce_new_order', function ( $order_id, $order = null ) {
	$order = $order instanceof WC_Order ? $order : wc_get_order( $order_id );
	if ( $order && lm_je_test_email( $order->get_billing_email() ) ) {
		$order->update_meta_data( '_lm_test', '1' );
		$order->save_meta_data();
	}
}, 5, 2 );
add_action( 'woocommerce_checkout_order_created', function ( $order ) {
	if ( $order instanceof WC_Order && lm_je_test_email( $order->get_billing_email() ) && ! $order->get_meta( '_lm_test' ) ) {
		$order->update_meta_data( '_lm_test', '1' );
		$order->save_meta_data();
	}
}, 5 );
add_filter( 'rest_pre_dispatch', function ( $res, $server, $request ) {
	$erp = (int) get_option( 'lm_erp_korisnik', 0 );
	if ( null !== $res || ! $erp || get_current_user_id() !== $erp ) {
		return $res;
	}
	if ( preg_match( '#^/wc/v\d/orders/(\d+)#', $request->get_route(), $m ) ) {
		$o = wc_get_order( (int) $m[1] );
		if ( $o && $o->get_meta( '_lm_test' ) ) {
			return new WP_Error( 'woocommerce_rest_shop_order_invalid_id', 'Invalid ID.', array( 'status' => 404 ) );
		}
	}
	return $res;
}, 10, 3 );

/**
 * ERP šalje ID-jeve kategorija i atributa sa starog sajta.
 *
 * Pri prelasku 17.10.2026 artikli su zadržali svoje ID-jeve, ali kategorije,
 * brendovi i atributi (boja, veličina) su na novom sajtu dobili nove ID-jeve
 * (npr. „Prodavnica ERP“ je bila 20, a sada je 104; atribut boja 8 → 1).
 * ERP i dalje šalje stare brojeve, pa ih WooCommerce nije prepoznavao: artikal
 * je ostajao „Bez kategorije“, bez boje i veličine.
 *
 * Ovdje se, samo za ERP-ove zahtjeve na artikle, stari ID prevodi u novi prije
 * nego što ga WooCommerce obradi. Sadržaj (koje kategorije, koja boja) je i dalje
 * onaj koji je ERP poslao. Mapa je napravljena iz stare baze po slug-u i čuva se
 * u opciji `lm_erp_mapa` (termini po taksonomiji i atributi). Oznake (tags) se ne
 * prevode: ERP ih ne šalje, a stari i novi ID-jevi oznaka se preklapaju.
 */
function lm_erp_prevedi_termin( $id, $taxonomy ) {
	static $mapa = null;
	if ( null === $mapa ) {
		$mapa = (array) get_option( 'lm_erp_mapa', array() );
	}
	$id = (int) $id;
	if ( ! isset( $mapa['termini'][ $taxonomy ][ $id ] ) ) {
		return $id;
	}
	// Ako ERP jednom pošalje već novi ID, ne diraj ga.
	$postoji = get_term( $id, $taxonomy );
	if ( $postoji && ! is_wp_error( $postoji ) ) {
		return $id;
	}
	return (int) $mapa['termini'][ $taxonomy ][ $id ];
}

function lm_erp_prevedi_atribut( $id ) {
	$mapa = (array) get_option( 'lm_erp_mapa', array() );
	$id   = (int) $id;
	if ( ! isset( $mapa['atributi'][ $id ] ) || wc_get_attribute( $id ) ) {
		return $id;
	}
	return (int) $mapa['atributi'][ $id ];
}

/** Prevedi ID-jeve u podacima jednog artikla ili varijante. */
function lm_erp_prevedi_artikal( $d ) {
	if ( ! is_array( $d ) ) {
		return $d;
	}
	foreach ( array( 'categories' => 'product_cat', 'brands' => 'product_brand' ) as $polje => $tx ) {
		if ( ! empty( $d[ $polje ] ) && is_array( $d[ $polje ] ) ) {
			foreach ( $d[ $polje ] as $i => $t ) {
				if ( is_array( $t ) && isset( $t['id'] ) ) {
					$d[ $polje ][ $i ]['id'] = lm_erp_prevedi_termin( $t['id'], $tx );
				} elseif ( is_numeric( $t ) ) {
					$d[ $polje ][ $i ] = lm_erp_prevedi_termin( $t, $tx );
				}
			}
		}
	}
	foreach ( array( 'attributes', 'default_attributes' ) as $polje ) {
		if ( ! empty( $d[ $polje ] ) && is_array( $d[ $polje ] ) ) {
			foreach ( $d[ $polje ] as $i => $a ) {
				if ( is_array( $a ) && ! empty( $a['id'] ) ) {
					$d[ $polje ][ $i ]['id'] = lm_erp_prevedi_atribut( $a['id'] );
				}
			}
		}
	}
	return $d;
}

add_filter( 'rest_request_before_callbacks', function ( $response, $handler, $request ) {
	if ( is_wp_error( $response ) || 'GET' === $request->get_method() ) {
		return $response;
	}
	if ( ! preg_match( '#^/wc/v\d+/products(/\d+)?(/variations)?(/\d+|/batch)?$#', $request->get_route() ) || ! lm_erp_log_je_erp( $request ) ) {
		return $response;
	}
	if ( ! get_option( 'lm_erp_mapa' ) ) {
		return $response;
	}
	foreach ( array( 'create', 'update' ) as $k ) {
		$stavke = $request->get_param( $k );
		if ( is_array( $stavke ) ) {
			$request->set_param( $k, array_map( 'lm_erp_prevedi_artikal', $stavke ) );
		}
	}
	foreach ( array( 'categories', 'brands', 'attributes', 'default_attributes' ) as $k ) {
		if ( null !== $request->get_param( $k ) ) {
			$prevedeno = lm_erp_prevedi_artikal( array( $k => $request->get_param( $k ) ) );
			$request->set_param( $k, $prevedeno[ $k ] );
		}
	}
	return $response;
}, 10, 3 );

/**
 * Privremeno bilježenje onoga što ERP šalje za artikle (dijagnostika, 18.10.2026).
 *
 * Uključeno dok je opcija `lm_erp_log` = 1 i dok ne prođe datum u `lm_erp_log_do`
 * (UNIX vrijeme). Bilježe se samo izmjene artikala i varijanti (POST/PUT/PATCH/DELETE
 * na /wc/vN/products…), sa tijelom zahtjeva i kratkim odgovorom. Porudžbine, kupci
 * i ključevi se ne bilježe. Fajl je van public_html: ~/lm-erp-log/GGGG-MM-DD.log (raniji dani .log.gz).
 * 24.10.2026: `lm_erp_log_do` produžen do 30.11.2026 u 23:59 (Podgorica).
 */
function lm_erp_log_aktivan() {
	if ( ! get_option( 'lm_erp_log' ) ) {
		return false;
	}
	$do = (int) get_option( 'lm_erp_log_do', 0 );
	return ! $do || time() < $do;
}

function lm_erp_log_je_erp( $request ) {
	$erp = (int) get_option( 'lm_erp_korisnik', 0 );
	if ( $erp && get_current_user_id() === $erp ) {
		return true;
	}
	return false !== stripos( (string) $request->get_header( 'user_agent' ), 'WooCommerce API Client' );
}

function lm_erp_log_upisi( $red ) {
	$dir = dirname( untrailingslashit( ABSPATH ) ) . '/lm-erp-log';
	if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
		return;
	}
	$fajl = $dir . '/' . wp_date( 'Y-m-d' ) . '.log';
	$novi = ! file_exists( $fajl );
	if ( ! $novi && filesize( $fajl ) > 30 * MB_IN_BYTES ) {
		return;
	}
	file_put_contents( $fajl, $red . "\n", FILE_APPEND | LOCK_EX );
	if ( $novi ) {
		lm_erp_log_sazmi( $dir, basename( $fajl ) );
	}
}

/**
 * Logovi prethodnih dana se sažimaju u .log.gz (24.10.2026; log je produžen do 30.11.).
 * Dan je do 30 MB teksta, sažeto oko desetak puta manje. Podaci ostaju, samo u sažetom fajlu.
 */
function lm_erp_log_sazmi( $dir, $danas ) {
	if ( ! function_exists( 'gzencode' ) ) {
		return;
	}
	foreach ( (array) glob( $dir . '/????-??-??.log' ) as $f ) {
		if ( basename( $f ) === $danas || ! is_file( $f ) ) {
			continue;
		}
		$sadrzaj = file_get_contents( $f );
		if ( false === $sadrzaj ) {
			continue;
		}
		$gz = gzencode( $sadrzaj, 6 );
		if ( false !== $gz && false !== file_put_contents( $f . '.gz', $gz, LOCK_EX ) && filesize( $f . '.gz' ) === strlen( $gz ) ) {
			unlink( $f ); // phpcs:ignore -- sažeta kopija je upisana i provjerena
		}
	}
}

add_filter( 'rest_post_dispatch', function ( $response, $server, $request ) {
	if ( ! lm_erp_log_aktivan() || 'GET' === $request->get_method() ) {
		return $response;
	}
	if ( ! preg_match( '#^/wc/v\d+/products#', $request->get_route() ) || ! lm_erp_log_je_erp( $request ) ) {
		return $response;
	}
	$tijelo = $request->get_json_params();
	if ( ! $tijelo ) {
		$tijelo = $request->get_body_params();
	}
	// Kratak odgovor: id, sku, cijene, zaliha i atributi (za batch po stavkama).
	$kratko = function ( $d ) {
		if ( ! is_array( $d ) ) {
			return $d;
		}
		$out = array();
		foreach ( array( 'id', 'parent_id', 'sku', 'regular_price', 'sale_price', 'price', 'stock_quantity', 'stock_status', 'status', 'attributes', 'categories', 'error', 'code', 'message' ) as $k ) {
			if ( array_key_exists( $k, $d ) ) {
				$out[ $k ] = $d[ $k ];
			}
		}
		return $out;
	};
	$data = $response instanceof WP_REST_Response ? $response->get_data() : null;
	if ( is_array( $data ) && ( isset( $data['create'] ) || isset( $data['update'] ) || isset( $data['delete'] ) ) ) {
		foreach ( array( 'create', 'update', 'delete' ) as $k ) {
			if ( isset( $data[ $k ] ) ) {
				$data[ $k ] = array_map( $kratko, $data[ $k ] );
			}
		}
	} else {
		$data = $kratko( $data );
	}
	lm_erp_log_upisi( wp_json_encode( array(
		'vrijeme'  => wp_date( 'Y-m-d H:i:s' ),
		'metod'    => $request->get_method(),
		'ruta'     => $request->get_route(),
		'status'   => $response instanceof WP_REST_Response ? $response->get_status() : null,
		'poslato'  => $tijelo,
		'odgovor'  => $data,
	), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
	return $response;
}, 10, 3 );
