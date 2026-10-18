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
	$args['meta_query'] = $mq;
	return $args;
} );

/**
 * Privremeno bilježenje onoga što ERP šalje za artikle (dijagnostika, 18.10.2026).
 *
 * Uključeno dok je opcija `lm_erp_log` = 1 i dok ne prođe datum u `lm_erp_log_do`
 * (UNIX vrijeme). Bilježe se samo izmjene artikala i varijanti (POST/PUT/PATCH/DELETE
 * na /wc/vN/products…), sa tijelom zahtjeva i kratkim odgovorom. Porudžbine, kupci
 * i ključevi se ne bilježe. Fajl je van public_html: ~/lm-erp-log/GGGG-MM-DD.log.
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
	if ( file_exists( $fajl ) && filesize( $fajl ) > 30 * MB_IN_BYTES ) {
		return;
	}
	file_put_contents( $fajl, $red . "\n", FILE_APPEND | LOCK_EX );
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
