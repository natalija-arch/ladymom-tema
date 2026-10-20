<?php
/**
 * Lady Mom – prodavnica: dorade iz testa kupovine (Markova odobrenja 20.10.2026).
 *
 * - Sortiranje: podrazumijevano „Najpopularnije“ (podešavanje WooCommerce-a
 *   `woocommerce_default_catalog_orderby` = popularity). Ručni redoslijed („Preporučeno“)
 *   je izbačen iz liste jer je prikazivao Baby Boo prvo.
 * - Traka kategorija na /prodavnica/: umjesto „Bez kategorije“ i „Prodavnica“ (korijen
 *   kategorija iz ERP-a) prikazuju se kategorije ispod korijena (Mame, Bebe…).
 *   WoodMart traku pravi preko wp_list_categories(); ovdje se samo za tu listu, i samo na
 *   glavnoj stranici prodavnice, postavlja `child_of` na korijen „Prodavnica“.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_catalog_orderby', function ( $opcije ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $opcije;
	}
	unset( $opcije['menu_order'] );
	return $opcije;
}, 100 );

/** Korijen kategorija iz ERP-a („Prodavnica ERP“, slug prodavnica). */
function lm_korijen_kategorija() {
	static $id = null;
	if ( null === $id ) {
		$t  = get_term_by( 'slug', 'prodavnica', 'product_cat' );
		$id = $t ? (int) $t->term_id : 0;
	}
	return $id;
}

add_filter( 'get_terms_args', function ( $args, $taksonomije ) {
	if ( is_admin() || ! in_array( 'product_cat', (array) $taksonomije, true ) ) {
		return $args;
	}
	// Samo WoodMart traka kategorija (njeni argumenti imaju `mobile_categories_layout`).
	if ( ! isset( $args['mobile_categories_layout'] ) || ! empty( $args['child_of'] ) ) {
		return $args;
	}
	if ( ! function_exists( 'is_shop' ) || ! is_shop() || is_product_taxonomy() ) {
		return $args;
	}
	$korijen = lm_korijen_kategorija();
	if ( $korijen ) {
		$args['child_of'] = $korijen;
	}
	return $args;
}, 20, 2 );

/*
 * Stranica proizvoda na telefonu (odobreno 20.10.2026).
 * - Galerija je svuda 3:4 (inc/ui-dorade.php). Kod kvadratnih slika (većina opreme) to je
 *   ostavljalo bijeli pojas iznad i ispod slike, pa su naziv, cijena i dugme bili ispod
 *   prvog ekrana. Kad je glavna slika artikla približno kvadratna, galerija je 1:1.
 * - U srcset-u ostaje samo WebP kad postoji: telefon sa gustim ekranom je birao originalni
 *   JPG iz ERP-a (1000 px i više) umjesto WebP kopije od 812 px.
 */
function lm_glavna_slika_kvadrat( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	$meta = wp_get_attachment_metadata( (int) $product->get_image_id() );
	if ( empty( $meta['width'] ) || empty( $meta['height'] ) ) {
		return false;
	}
	return ( (float) $meta['height'] / (float) $meta['width'] ) <= 1.1;
}
add_filter( 'body_class', function ( $klase ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_queried_object_id() );
		if ( lm_glavna_slika_kvadrat( $p ) ) {
			$klase[] = 'lm-galerija-kvadrat';
		}
	}
	return $klase;
} );
add_action( 'wp_head', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	echo '<style id="lm-galerija-kvadrat">body.lm-galerija-kvadrat .woocommerce-product-gallery .wd-carousel-item img,body.lm-galerija-kvadrat .woocommerce-product-gallery__image img{aspect-ratio:1/1}</style>';
}, 40 );

add_filter( 'wp_calculate_image_srcset', function ( $izvori ) {
	if ( is_admin() || ! is_array( $izvori ) ) {
		return $izvori;
	}
	$webp = array_filter( $izvori, function ( $s ) {
		return isset( $s['url'] ) && preg_match( '/\.webp$/i', $s['url'] );
	} );
	return count( $webp ) >= 2 ? $webp : $izvori;
}, 20 );

// Korpa (20.10.2026): bez rečenice „Opcije dostave biće ažurirane na plaćanju.“ (dostava je samo po Crnoj Gori).
add_filter( 'woocommerce_shipping_estimate_html', '__return_empty_string', 20 );

// Nove veličine (npr. iz ERP-a) se na proizvodu prikazuju kao dugmad, kao i postojeće (20.10.2026).
add_action( 'created_pa_velicina', function ( $term_id ) {
	if ( ! get_term_meta( $term_id, 'not_dropdown', true ) ) {
		update_term_meta( $term_id, 'not_dropdown', 'on' );
	}
} );
