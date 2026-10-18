<?php
/**
 * Lady Mom – kategorije i brendovi bez artikala na stanju se ne prikazuju.
 *
 * Artikli i zalihe dolaze iz ERP-a, pa se kategorija ili brend lako isprazne.
 * Meniji, mega meni, traka brendova i pločice na početnoj su ručno složeni, a
 * WordPress/WooCommerce nemaju opciju da u njima sakriju prazne kategorije.
 * Isto važi za „Na akciji“ (stavka menija i tab na početnoj) dok nema popusta.
 * Ovaj kod ih sakriva samo u prikazu: termini, artikli i meniji se ne mijenjaju,
 * pa se sve samo vrati čim ERP javi da je artikal ponovo na stanju.
 *
 * „Aktivan“ termin = ima bar jedan objavljen artikal na stanju (ili na
 * naručivanje). Kategorija je aktivna i ako je aktivna neka njena podkategorija.
 */

defined( 'ABSPATH' ) || exit;

/** Spisak aktivnih termina: array( 'product_cat' => array( id => true ), 'product_brand' => … ). */
function lm_aktivni_termini( $svjeze = false ) {
	static $aktivni = null;
	if ( null !== $aktivni && ! $svjeze ) {
		return $aktivni;
	}
	$aktivni = get_transient( 'lm_aktivni_termini' );
	if ( is_array( $aktivni ) ) {
		return $aktivni;
	}
	global $wpdb;
	$redovi = $wpdb->get_results(
		"SELECT DISTINCT tt.term_id, tt.taxonomy
		FROM {$wpdb->term_relationships} tr
		JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
		JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_type = 'product' AND p.post_status = 'publish'
		JOIN {$wpdb->wc_product_meta_lookup} l ON l.product_id = p.ID AND l.stock_status IN ('instock','onbackorder')
		WHERE tt.taxonomy IN ('product_cat','product_brand')"
	);
	$aktivni = array( 'product_cat' => array(), 'product_brand' => array() );
	// Ima li bar jedan artikal na popustu i na stanju (za „Na akciji“).
	$aktivni['akcija'] = (bool) $wpdb->get_var(
		"SELECT 1 FROM {$wpdb->wc_product_meta_lookup} l
		JOIN {$wpdb->posts} p ON p.ID = l.product_id AND p.post_status = 'publish'
		WHERE l.onsale = 1 AND l.stock_status IN ('instock','onbackorder') LIMIT 1"
	);
	foreach ( (array) $redovi as $r ) {
		$aktivni[ $r->taxonomy ][ (int) $r->term_id ] = true;
		if ( 'product_cat' === $r->taxonomy ) {
			foreach ( get_ancestors( (int) $r->term_id, 'product_cat', 'taxonomy' ) as $a ) {
				$aktivni['product_cat'][ (int) $a ] = true;
			}
		}
	}
	set_transient( 'lm_aktivni_termini', $aktivni, HOUR_IN_SECONDS );
	return $aktivni;
}

/** Da li je termin aktivan. Nepoznata taksonomija je uvijek „aktivna“. */
function lm_termin_aktivan( $term_id, $taxonomy ) {
	$a = lm_aktivni_termini();
	if ( ! isset( $a[ $taxonomy ] ) || ! is_array( $a[ $taxonomy ] ) ) {
		return true;
	}
	return isset( $a[ $taxonomy ][ (int) $term_id ] );
}

/**
 * Termin iz adrese (/kategorija/…/slug/ ili /marka/slug/), ili null ako adresa
 * nije kategorija ni brend.
 */
function lm_termin_iz_adrese( $url ) {
	$put = wp_parse_url( (string) $url, PHP_URL_PATH );
	if ( ! $put ) {
		return null;
	}
	$host = wp_parse_url( (string) $url, PHP_URL_HOST );
	if ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) {
		return null;
	}
	$dijelovi = array_values( array_filter( explode( '/', $put ) ) );
	if ( count( $dijelovi ) < 2 ) {
		return null;
	}
	$baza = array( 'kategorija' => 'product_cat', 'marka' => 'product_brand' );
	if ( ! isset( $baza[ $dijelovi[0] ] ) ) {
		return null;
	}
	$t = get_term_by( 'slug', sanitize_title( urldecode( end( $dijelovi ) ) ), $baza[ $dijelovi[0] ] );
	return $t ? $t : null;
}

/** true ako adresa vodi na kategoriju ili brend bez artikala na stanju. */
function lm_adresa_prazna( $url ) {
	$t = lm_termin_iz_adrese( $url );
	return $t && ! lm_termin_aktivan( $t->term_id, $t->taxonomy );
}

/**
 * Kad se promijeni zaliha, artikal ili termin, spisak se na kraju zahtjeva
 * izračuna ponovo. Ako se spisak promijenio (npr. brend je ostao bez artikala na
 * stanju ili se vratio), briše se keš stranica, jer su meniji na svakoj stranici.
 */
function lm_aktivni_termini_obrisi() {
	static $zakazano = false;
	if ( $zakazano ) {
		return;
	}
	$zakazano = true;
	add_action( 'shutdown', function () {
		$staro = get_option( 'lm_aktivni_termini_otisak', '' );
		delete_transient( 'lm_aktivni_termini' );
		$novo   = lm_aktivni_termini_svjeze();
		$otisak = md5( wp_json_encode( array( array_keys( $novo['product_cat'] ), array_keys( $novo['product_brand'] ), ! empty( $novo['akcija'] ) ) ) );
		if ( $otisak !== $staro ) {
			update_option( 'lm_aktivni_termini_otisak', $otisak, false );
			if ( '' !== $staro ) {
				do_action( 'litespeed_purge_all' );
			}
		}
	} );
}

/** Izračunaj spisak ponovo, bez statičke kopije iz istog zahtjeva. */
function lm_aktivni_termini_svjeze() {
	delete_transient( 'lm_aktivni_termini' );
	return lm_aktivni_termini( true );
}
foreach ( array(
	'woocommerce_product_set_stock_status',
	'woocommerce_variation_set_stock_status',
	'woocommerce_update_product',
	'woocommerce_new_product',
	'woocommerce_delete_product',
	'woocommerce_trash_product',
	'created_product_cat',
	'edited_product_cat',
	'delete_product_cat',
	'created_product_brand',
	'edited_product_brand',
	'delete_product_brand',
) as $lm_hook ) {
	add_action( $lm_hook, 'lm_aktivni_termini_obrisi' );
}
unset( $lm_hook );
add_action( 'set_object_terms', function ( $object_id, $terms, $tt_ids, $taxonomy ) {
	if ( in_array( $taxonomy, array( 'product_cat', 'product_brand' ), true ) ) {
		lm_aktivni_termini_obrisi();
	}
}, 10, 4 );

/** 1) WordPress meniji (mobilni meni, glavni meni): stavka i njeni podmeniji. */
add_filter( 'wp_nav_menu_objects', function ( $stavke ) {
	if ( is_admin() ) {
		return $stavke;
	}
	$skloni = array();
	foreach ( $stavke as $s ) {
		if ( 'taxonomy' === $s->type && in_array( $s->object, array( 'product_cat', 'product_brand' ), true ) ) {
			if ( ! lm_termin_aktivan( $s->object_id, $s->object ) ) {
				$skloni[ (int) $s->ID ] = true;
			}
		} elseif ( in_array( 'lm-m-akcija', (array) $s->classes, true ) && ! lm_ima_akcija() ) {
			$skloni[ (int) $s->ID ] = true;
		} elseif ( 'custom' === $s->type && lm_adresa_prazna( $s->url ) ) {
			$skloni[ (int) $s->ID ] = true;
		}
	}
	if ( ! $skloni ) {
		return $stavke;
	}
	// Ukloni i sve ispod sklonjene stavke.
	do {
		$novo = false;
		foreach ( $stavke as $s ) {
			if ( ! isset( $skloni[ (int) $s->ID ] ) && isset( $skloni[ (int) $s->menu_item_parent ] ) ) {
				$skloni[ (int) $s->ID ] = true;
				$novo                   = true;
			}
		}
	} while ( $novo );
	return array_values( array_filter( $stavke, function ( $s ) use ( $skloni ) {
		return ! isset( $skloni[ (int) $s->ID ] );
	} ) );
}, 20 );

/**
 * 2) WoodMart blokovi (mega meni „Prodavnica“, pretraga): stavka liste menija
 * i linkovi u HTML bloku pretrage na mobilnom.
 */
add_filter( 'render_block', function ( $html, $block ) {
	if ( is_admin() || empty( $block['blockName'] ) ) {
		return $html;
	}
	if ( 'wd/menu-list-item' === $block['blockName'] ) {
		if ( preg_match( '/<a\s[^>]*href="([^"]+)"/i', $html, $m ) && lm_adresa_prazna( html_entity_decode( $m[1] ) ) ) {
			return '';
		}
		return $html;
	}
	if ( 'core/html' === $block['blockName'] && false !== strpos( $html, 'lm-mp' ) ) {
		return preg_replace_callback( '#<a\s[^>]*href="([^"]+)"[^>]*>.*?</a>#is', function ( $m ) {
			return lm_adresa_prazna( html_entity_decode( $m[1] ) ) ? '' : $m[0];
		}, $html );
	}
	return $html;
}, 10, 2 );

/** Ima li artikala na popustu i na stanju. */
function lm_ima_akcija() {
	$a = lm_aktivni_termini();
	return ! empty( $a['akcija'] );
}

/** 3) Elementor: traka brendova (banner carousel) i pločice (banner) na početnoj. */
/** Tab „Na akciji“ (WoodMart products tabs, izvor „sale“) se ne prikazuje dok nema popusta. */
add_action( 'elementor/frontend/widget/before_render', function ( $widget ) {
	if ( 'wd_products_tabs' !== $widget->get_name() || lm_ima_akcija() ) {
		return;
	}
	$tabovi = $widget->get_settings( 'tabs_items' );
	if ( ! is_array( $tabovi ) ) {
		return;
	}
	$ostali = array_values( array_filter( $tabovi, function ( $t ) {
		return ! isset( $t['post_type'] ) || 'sale' !== $t['post_type'];
	} ) );
	if ( $ostali && count( $ostali ) !== count( $tabovi ) ) {
		$widget->set_settings( 'tabs_items', $ostali );
	}
} );

add_action( 'elementor/frontend/widget/before_render', function ( $widget ) {
	if ( 'wd_banner_carousel' !== $widget->get_name() ) {
		return;
	}
	$stavke = $widget->get_settings( 'content_repeater' );
	if ( ! is_array( $stavke ) ) {
		return;
	}
	$filtrirano = array_values( array_filter( $stavke, function ( $s ) {
		return empty( $s['link']['url'] ) || ! lm_adresa_prazna( $s['link']['url'] );
	} ) );
	if ( count( $filtrirano ) !== count( $stavke ) ) {
		$widget->set_settings( 'content_repeater', $filtrirano );
	}
} );

add_filter( 'elementor/frontend/widget/should_render', function ( $prikazi, $widget ) {
	if ( $prikazi && 'wd_banner' === $widget->get_name() ) {
		$link = $widget->get_settings( 'link' );
		if ( ! empty( $link['url'] ) && lm_adresa_prazna( $link['url'] ) ) {
			return false;
		}
	}
	return $prikazi;
}, 10, 2 );
