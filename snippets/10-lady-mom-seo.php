<?php
/**
 * Snippet #10: Lady Mom – SEO
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Lady Mom – SEO podešavanja (Yoast dopune).

// 1) Sitemap: bez tehničkih tipova sadržaja i autora.
add_filter( 'wpseo_sitemap_exclude_taxonomy', function ( $exclude, $taxonomy ) {
	return in_array( $taxonomy, array( 'cms_block_cat', 'woodmart_slider', 'category_portfolio', 'post_tag', 'product_tag', 'pa_color', 'pa_size' ), true ) ? true : $exclude;
}, 10, 2 );
add_filter( 'wpseo_sitemap_exclude_post_type', function ( $exclude, $post_type ) {
	return in_array( $post_type, array( 'cms_block', 'woodmart_slide', 'woodmart_layout', 'portfolio', 'woodmart_sidebar', 'woodmart_woo_fbt' ), true ) ? true : $exclude;
}, 10, 2 );
add_filter( 'wpseo_sitemap_exclude_author', function () { return array(); } );

// Stranice koje ne treba da budu na Google-u.
function lm_seo_noindex_slugs() {
	return array( 'cart', 'checkout', 'my-account', 'wishlist', 'compare', 'stranica-nije-pronadjena' );
}
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	foreach ( lm_seo_noindex_slugs() as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			$ids[] = $p->ID;
		}
	}
	return $ids;
} );

// 2) noindex za korpu, plaćanje, nalog, listu želja, pretragu i 404.
add_filter( 'wpseo_robots', function ( $robots ) {
	$noindex = is_search() || is_404();
	if ( is_page() ) {
		$slug    = get_post_field( 'post_name', get_queried_object_id() );
		$noindex = $noindex || in_array( $slug, lm_seo_noindex_slugs(), true );
	}
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		$noindex = true;
	}
	return $noindex ? 'noindex, follow' : $robots;
} );

// 3) Naslovi: crtica "–" i bolji naslovi za blog, proizvode i kategorije.
add_filter( 'wpseo_title', function ( $title ) {
	if ( is_home() ) {
		return 'Savjeti za trudnice i mame – Lady Mom blog';
	}
	if ( function_exists( 'is_product' ) && is_product() && ! get_post_meta( get_the_ID(), '_yoast_wpseo_title', true ) ) {
		return wp_strip_all_tags( get_the_title() ) . ' | Lady Mom Podgorica';
	}
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$t = single_term_title( '', false );
		return $t . ' – kupite online | Lady Mom';
	}
	if ( function_exists( 'is_product_taxonomy' ) && is_tax( 'product_brand' ) ) {
		return single_term_title( '', false ) . ' u Crnoj Gori – Lady Mom';
	}
	return str_replace( ' - Lady Mom', ' – Lady Mom', $title );
} );

// 4) Meta opisi za proizvode, kategorije i brendove kad nisu ručno upisani.
add_filter( 'wpseo_metadesc', function ( $desc ) {
	if ( ! empty( $desc ) ) {
		return $desc;
	}
	$tail = ' Besplatna dostava po Crnoj Gori za porudžbine od 100 €, plaćanje pouzećem ili karticom.';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$p = wc_get_product( get_the_ID() );
		if ( $p ) {
			$txt = wp_strip_all_tags( $p->get_short_description() ? $p->get_short_description() : $p->get_description() );
			$txt = trim( preg_replace( '/\s+/', ' ', $txt ) );
			if ( mb_strlen( $txt ) > 110 ) {
				$txt = rtrim( mb_substr( $txt, 0, 107 ), " ,.;:" ) . '…';
			}
			return trim( ( $txt ? $txt : $p->get_name() . ' u Lady Mom prodavnici.' ) . $tail );
		}
	}
	if ( ( function_exists( 'is_product_category' ) && is_product_category() ) || is_tax( 'product_brand' ) ) {
		$term = get_queried_object();
		$txt  = trim( wp_strip_all_tags( term_description( $term ) ) );
		if ( ! $txt ) {
			$txt = 'Kupite ' . mb_strtolower( $term->name ) . ' online u Lady Mom prodavnici za trudnice i mame.';
		}
		return mb_substr( $txt, 0, 110 ) . $tail;
	}
	return $desc;
} );

// 5) Podrazumijevana slika za dijeljenje na mrežama (kad stranica nema svoju).
add_filter( 'wpseo_add_opengraph_additional_images', function ( $images ) {
	if ( ! $images->has_images() ) {
		$images->add_image( array( 'url' => home_url( '/wp-content/uploads/2026/09/lady-mom-butik-podgorica.jpg' ) ) );
	}
} );

// 6) Lokalni biznis (butik) u strukturiranim podacima.
add_filter( 'wpseo_schema_organization', function ( $data ) {
	$data['@type']      = array( 'Organization', 'ClothingStore' );
	$data['name']       = 'Lady Mom';
	$data['description'] = 'Butik za trudnice i mame u Podgorici – trudnička garderoba, oprema za dojenje, jastuci, kozmetika i oprema za bebe.';
	$data['telephone']  = '+38267030655';
	$data['email']      = 'natalija@ladymom.me';
	$data['priceRange'] = '€€';
	$data['currenciesAccepted'] = 'EUR';
	$data['paymentAccepted']    = 'Gotovina, Visa, Mastercard';
	$data['image']      = home_url( '/wp-content/uploads/2026/09/lady-mom-butik-podgorica.jpg' );
	$data['address']    = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'Branka Deletića bb',
		'addressLocality' => 'Podgorica',
		'postalCode'      => '81000',
		'addressCountry'  => 'ME',
	);
	$data['openingHoursSpecification'] = array(
		array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
			'opens'     => '09:30',
			'closes'    => '21:00',
		),
	);
	$data['areaServed'] = array( '@type' => 'Country', 'name' => 'Crna Gora' );
	$data['sameAs']     = array( 'https://www.facebook.com/ladymom.me', 'https://www.instagram.com/ladymom.me' );
	$data['hasMap']     = 'https://www.google.com/maps/search/?api=1&query=Lady+Mom+prodavnica+za+trudnice+i+mame&query_place_id=ChIJU0tXHcnrTRMRuXXUSvfb4b8';
	return $data;
} );


// 7) Preusmjerenja starih adresa stranica (301).
add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
	$map  = array(
		'about-us-3' => '/o-nama/',
		'about-us'   => '/o-nama/',
		'contact-us' => '/kontakt/',
	);
	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( $map[ $path ] ), 301 );
		exit;
	}
} );
