<?php
/**
 * Lady Mom – brend proizvoda iz naziva.
 *
 * ERP (TiramisuERP) ne šalje brend, ali je brend uvijek na početku naziva
 * („MAMALICIOUS – Helanke za trudnice“). Kad se proizvod sačuva (iz ERP-a preko
 * REST-a ili u administraciji), a nema brend, dodjeljuje mu se brend po nazivu.
 * Brend koji je već ručno dodijeljen se ne mijenja.
 */

defined( 'ABSPATH' ) || exit;

/** Početak naziva (velikim slovima) => array( slug, naziv brenda ). Duži prefiksi prvi. */
function lm_brend_mapa() {
	return array(
		'BABY BOO PACK'  => array( 'baby-boo', 'Baby Boo' ),
		'BABY BOO'       => array( 'baby-boo', 'Baby Boo' ),
		'ONLY MATERNITY' => array( 'only-maternity', 'Only Maternity' ),
		'VERO MODA'      => array( 'vero-moda-maternity', 'Vero Moda Maternity' ),
		'CACHE COUER'    => array( 'cache-coeur', 'Cache Cœur' ),
		'CACHE COEUR'    => array( 'cache-coeur', 'Cache Cœur' ),
		'KI ET LA'       => array( 'ki-et-la', 'Ki ET LA' ),
		'MAMALICIOUS'    => array( 'mamalicious', 'Mamalicious' ),
		'MAMALICIUS'     => array( 'mamalicious', 'Mamalicious' ),
		'SERAPHINE'      => array( 'seraphine-maternity', 'Seraphine Maternity' ),
		'KOALA'          => array( 'koala-babycare', 'Koala Babycare' ),
		'HAAKAA'         => array( 'haakaa', 'Haakaa' ),
		'TIBBA'          => array( 'tibba', 'Tibba' ),
		'SPECTRA'        => array( 'spectra', 'Spectra' ),
		'BUBBI'          => array( 'bubbi', 'Bubbi' ),
		'ZIPSTER'        => array( 'zipster', 'Zipster' ),
		'MAYMOM'         => array( 'spectra', 'Spectra' ), // Maymom dijelovi se prodaju uz Spectra pumpice (odluka 18.10.).
	);
}

/** ID brenda (product_brand) za naziv proizvoda, ili 0. Brend se pravi ako ne postoji. */
function lm_brend_za_naziv( $naziv ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return 0;
	}
	$n = mb_strtoupper( trim( html_entity_decode( (string) $naziv, ENT_QUOTES, 'UTF-8' ) ), 'UTF-8' );
	foreach ( lm_brend_mapa() as $prefiks => $b ) {
		if ( 0 !== strpos( $n, $prefiks ) ) {
			continue;
		}
		$sljedeci = mb_substr( $n, mb_strlen( $prefiks ), 1, 'UTF-8' );
		if ( '' !== $sljedeci && preg_match( '/[\p{L}\p{N}]/u', $sljedeci ) ) {
			continue; // npr. „SPECTRAX“ nije „SPECTRA“.
		}
		$t = get_term_by( 'slug', $b[0], 'product_brand' );
		if ( ! $t ) {
			$t = get_term_by( 'name', $b[1], 'product_brand' );
		}
		if ( ! $t ) {
			$r = wp_insert_term( $b[1], 'product_brand', array( 'slug' => $b[0] ) );
			return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
		}
		return (int) $t->term_id;
	}
	return 0;
}

/** Dodijeli brend proizvodu bez brenda. Vraća true ako je dodijeljen. */
function lm_brend_dodijeli( $product_id ) {
	if ( 'product' !== get_post_type( $product_id ) ) {
		return false;
	}
	$ima = wp_get_object_terms( $product_id, 'product_brand', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $ima ) || $ima ) {
		return false;
	}
	$brend = lm_brend_za_naziv( get_the_title( $product_id ) );
	if ( ! $brend ) {
		return false;
	}
	wp_set_object_terms( $product_id, array( $brend ), 'product_brand', false );
	return true;
}

add_action( 'woocommerce_after_product_object_save', function ( $product ) {
	if ( $product && ! $product->is_type( 'variation' ) ) {
		lm_brend_dodijeli( $product->get_id() );
	}
}, 20 );

/**
 * Pretraga po nazivu brenda („Baby Boo“, „koala“) vodi pravo na stranicu brenda.
 * Inače WordPress traži riječi u cijelom opisu, pa za „Baby Boo“ izlaze i
 * „Hug Baby jastuk“ ili „Baby Doll“.
 */
add_action( 'template_redirect', function () {
	if ( ! is_search() || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}
	$q = mb_strtoupper( trim( (string) get_search_query( false ) ), 'UTF-8' );
	if ( '' === $q ) {
		return;
	}
	$q = preg_replace( '/\s+/u', ' ', $q );
	$term = null;
	foreach ( get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => true ) ) as $t ) {
		if ( mb_strtoupper( $t->name, 'UTF-8' ) === $q ) {
			$term = $t;
			break;
		}
	}
	if ( ! $term ) {
		$mapa = lm_brend_mapa();
		if ( isset( $mapa[ $q ] ) ) {
			$term = get_term_by( 'slug', $mapa[ $q ][0], 'product_brand' );
		}
	}
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			wp_safe_redirect( $link, 302 );
			exit;
		}
	}
} );
