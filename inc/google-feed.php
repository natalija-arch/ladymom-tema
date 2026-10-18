<?php
/**
 * Lady Mom – spisak proizvoda za Google Merchant Center (Markova odluka 18.10.2026).
 *
 * Adresa: https://ladymom.me/?lm_feed=google
 * Google je preuzima po rasporedu (jednom dnevno). Pravi se iz WooCommerce-a, dakle
 * iz onoga što šalje ERP – ništa se ne upisuje nazad. Spisak se čuva 2 sata da se
 * server ne opterećuje, a briše se čim se neki proizvod promijeni.
 */

defined( 'ABSPATH' ) || exit;

const LM_FEED_TRANSIENT = 'lm_google_feed_v1';

add_action( 'init', function () {
	if ( ! isset( $_GET['lm_feed'] ) || 'google' !== $_GET['lm_feed'] ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	do_action( 'litespeed_control_set_nocache', 'Lady Mom Google feed' );
	nocache_headers();
	$xml = get_transient( LM_FEED_TRANSIENT );
	if ( ! $xml ) {
		$xml = lm_feed_napravi();
		set_transient( LM_FEED_TRANSIENT, $xml, 2 * HOUR_IN_SECONDS );
	}
	header( 'Content-Type: application/xml; charset=UTF-8' );
	echo $xml; // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}, 20 );

// Promjena proizvoda (ERP ili ručno) briše sačuvani spisak.
foreach ( array( 'woocommerce_update_product', 'woocommerce_update_product_variation', 'woocommerce_new_product', 'woocommerce_product_set_stock_status', 'woocommerce_variation_set_stock_status' ) as $lm_hook ) {
	add_action( $lm_hook, function () {
		delete_transient( LM_FEED_TRANSIENT );
	} );
}
unset( $lm_hook );

/** Vrijednost za XML. */
function lm_feed_x( $s ) {
	$s = html_entity_decode( (string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$s = preg_replace( '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $s );
	return htmlspecialchars( $s, ENT_XML1 | ENT_QUOTES, 'UTF-8' );
}

/** Cijena sa PDV-om u formatu „12.90 EUR“. */
function lm_feed_cijena( $p, $iznos ) {
	$c = wc_get_price_including_tax( $p, array( 'price' => $iznos ) );
	return number_format( (float) $c, 2, '.', '' ) . ' ' . get_woocommerce_currency();
}

/** Putanja kategorije „Za mame > Odjeća > Haljine“ (bez „Prodavnica ERP“). */
function lm_feed_kategorija( $id ) {
	$najdublja = null;
	$dubina    = -1;
	foreach ( wc_get_product_terms( $id, 'product_cat' ) as $t ) {
		if ( LM_KAT_PRODAVNICA_ERP === (int) $t->term_id ) {
			continue;
		}
		$d = count( get_ancestors( $t->term_id, 'product_cat' ) );
		if ( $d > $dubina ) {
			$dubina    = $d;
			$najdublja = $t;
		}
	}
	if ( ! $najdublja ) {
		return '';
	}
	$dio = array( $najdublja->name );
	foreach ( get_ancestors( $najdublja->term_id, 'product_cat' ) as $a ) {
		if ( LM_KAT_PRODAVNICA_ERP === (int) $a ) {
			continue;
		}
		$t = get_term( $a, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) ) {
			array_unshift( $dio, $t->name );
		}
	}
	return implode( ' > ', $dio );
}

/** Opis: opis proizvoda, kratki opis, ili rečenica od naziva, brenda i kategorije. */
function lm_feed_opis( $p, $brend, $kat ) {
	$t = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $p->get_description() ?: $p->get_short_description() ) ) ) );
	if ( '' === $t ) {
		$t = ( $brend ? $brend . ' – ' : '' ) . $p->get_name() . '.';
		if ( $kat ) {
			$t .= ' ' . $kat . '. Lady Mom, butik za trudnice i mame u Podgorici.';
		}
	}
	return mb_substr( $t, 0, 4900 );
}

/** Jedan red (artikal ili varijanta). */
function lm_feed_stavka( $p, $roditelj = null ) {
	$glavni = $roditelj ?: $p;
	$cijena = $p->get_regular_price() !== '' ? $p->get_regular_price() : $p->get_price();
	if ( '' === (string) $cijena || (float) $cijena <= 0 ) {
		return '';
	}
	$brend = wp_get_post_terms( $glavni->get_id(), 'product_brand', array( 'fields' => 'names' ) );
	$brend = $brend && ! is_wp_error( $brend ) ? $brend[0] : '';
	$kat   = lm_feed_kategorija( $glavni->get_id() );

	$naziv = $glavni->get_name();
	$boja  = '';
	$vel   = '';
	if ( $roditelj ) {
		foreach ( $p->get_attributes() as $tax => $slug ) {
			$term = $slug ? get_term_by( 'slug', $slug, $tax ) : null;
			$ime  = $term ? $term->name : (string) $slug;
			if ( 'pa_boja' === $tax ) {
				$boja = $ime;
			} elseif ( 'pa_velicina' === $tax ) {
				$vel = $ime;
			}
		}
		$dodatak = array_filter( array( $boja, $vel ? mb_strtoupper( $vel ) : '' ) );
		if ( $dodatak ) {
			$naziv .= ' – ' . implode( ', ', $dodatak );
		}
	}

	$slika = $p->get_image_id() ?: $glavni->get_image_id();
	$url_s = $slika ? wp_get_attachment_url( $slika ) : '';
	if ( ! $url_s ) {
		return '';
	}
	$dodatne = array();
	foreach ( array_slice( $glavni->get_gallery_image_ids(), 0, 10 ) as $g ) {
		$u = wp_get_attachment_url( $g );
		if ( $u && $u !== $url_s ) {
			$dodatne[] = $u;
		}
	}

	$status = $p->get_stock_status();
	$dost   = 'instock' === $status ? 'in_stock' : ( 'onbackorder' === $status ? 'backorder' : 'out_of_stock' );

	$r  = "<item>\n";
	$r .= '<g:id>' . $p->get_id() . "</g:id>\n";
	if ( $roditelj ) {
		$r .= '<g:item_group_id>' . $roditelj->get_id() . "</g:item_group_id>\n";
	}
	$r .= '<g:title>' . lm_feed_x( mb_substr( $naziv, 0, 150 ) ) . "</g:title>\n";
	$r .= '<g:description>' . lm_feed_x( lm_feed_opis( $glavni, $brend, $kat ) ) . "</g:description>\n";
	$r .= '<g:link>' . lm_feed_x( $p->get_permalink() ) . "</g:link>\n";
	$r .= '<g:image_link>' . lm_feed_x( $url_s ) . "</g:image_link>\n";
	foreach ( $dodatne as $u ) {
		$r .= '<g:additional_image_link>' . lm_feed_x( $u ) . "</g:additional_image_link>\n";
	}
	$r .= '<g:availability>' . $dost . "</g:availability>\n";
	$r .= '<g:price>' . lm_feed_cijena( $p, $cijena ) . "</g:price>\n";
	$akcija = $p->get_sale_price();
	if ( $p->is_on_sale() && '' !== (string) $akcija && (float) $akcija < (float) $cijena ) {
		$r .= '<g:sale_price>' . lm_feed_cijena( $p, $akcija ) . "</g:sale_price>\n";
		$od = $p->get_date_on_sale_from();
		$do = $p->get_date_on_sale_to();
		if ( $od && $do ) {
			$r .= '<g:sale_price_effective_date>' . $od->format( 'c' ) . '/' . $do->format( 'c' ) . "</g:sale_price_effective_date>\n";
		}
	}
	$r .= "<g:condition>new</g:condition>\n";
	if ( $brend ) {
		$r .= '<g:brand>' . lm_feed_x( $brend ) . "</g:brand>\n";
	}
	$sku  = $p->get_sku();
	$gtin = function_exists( 'lm_gtin_kljuc' ) ? lm_gtin_kljuc( $sku ) : null;
	if ( $gtin ) {
		$r .= '<g:gtin>' . lm_feed_x( preg_replace( '/\s+/', '', $sku ) ) . "</g:gtin>\n";
	} elseif ( $sku && $brend ) {
		$r .= '<g:mpn>' . lm_feed_x( $sku ) . "</g:mpn>\n";
	} else {
		$r .= "<g:identifier_exists>no</g:identifier_exists>\n";
	}
	if ( $kat ) {
		$r .= '<g:product_type>' . lm_feed_x( $kat ) . "</g:product_type>\n";
	}
	if ( $boja ) {
		$r .= '<g:color>' . lm_feed_x( $boja ) . "</g:color>\n";
	}
	if ( $vel ) {
		$r .= '<g:size>' . lm_feed_x( mb_strtoupper( $vel ) ) . "</g:size>\n";
	}
	$r .= "</item>\n";
	return $r;
}

/** Cijeli spisak. */
function lm_feed_napravi() {
	$samo_na_stanju = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );
	$out  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$out .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"><channel>' . "\n";
	$out .= '<title>Lady Mom</title><link>' . esc_url( home_url( '/' ) ) . '</link><description>Lady Mom – butik za trudnice i mame</description>' . "\n";

	$strana = 1;
	do {
		$ids = wc_get_products( array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'type'       => array( 'simple', 'variable' ),
			'limit'      => 100,
			'page'       => $strana,
			'return'     => 'ids',
			'orderby'    => 'ID',
			'order'      => 'ASC',
		) );
		foreach ( $ids as $id ) {
			$p = wc_get_product( $id );
			if ( ! $p || $p->get_catalog_visibility() === 'hidden' ) {
				continue;
			}
			if ( $p->is_type( 'variable' ) ) {
				foreach ( $p->get_children() as $vid ) {
					$v = wc_get_product( $vid );
					if ( ! $v || 'publish' !== $v->get_status() ) {
						continue;
					}
					if ( $samo_na_stanju && ! $v->is_in_stock() ) {
						continue;
					}
					$out .= lm_feed_stavka( $v, $p );
				}
			} else {
				if ( $samo_na_stanju && ! $p->is_in_stock() ) {
					continue;
				}
				$out .= lm_feed_stavka( $p );
			}
		}
		++$strana;
	} while ( count( $ids ) === 100 );

	$out .= "</channel></rss>\n";
	return $out;
}
