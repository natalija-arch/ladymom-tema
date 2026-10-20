<?php
/**
 * Lady Mom – SEO dorade (21.10.2026, iz dokumenta „SEO analiza ladymom.me“; Marko: „napravi sve što možeš“).
 *
 * 1. Škola za trudnice: stare adrese (/ready-mom/, članak o školi, prazna kategorija „Škola za trudnice“)
 *    vode na stranicu škole (301). Adresa stranice se čita iz opcije `lm_ready_mom_stranica`.
 * 2. Šema Product na stranicama proizvoda. WooCommerce je pravi na akciji
 *    `woocommerce_single_product_summary` (60), a WoodMart prikaz proizvoda je ne pokreće,
 *    pa se ispisivala samo BreadcrumbList (bez cijene i dostupnosti u Google rezultatima).
 * 3. Kategorije: duži H1 (term meta `lm_h1`) i tekst ispod artikala (term meta `lm_tekst`).
 *    Naziv i opis kategorije stižu iz ERP-a i ne mijenjaju se.
 * 4. „Default artikal“ iz ERP-a: noindex i van sitemapa, dok ga ERP ne ugasi.
 * 5. Meta opis proizvoda: cijele rečenice iz opisa umjesto rezanja usred rečenice.
 * 6. Stranica škole: podaci za Google (Course i FAQPage).
 */

defined( 'ABSPATH' ) || exit;

/** Adresa stranice škole. */
function lm_skola_url() {
	$st = (int) get_option( 'lm_ready_mom_stranica' );
	return $st ? get_permalink( $st ) : '';
}

/* 1) Stare adrese škole → stranica škole. */
add_action( 'template_redirect', function () {
	$url = lm_skola_url();
	if ( ! $url || is_admin() ) {
		return;
	}
	$put  = '/' . trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ), '/' ) . '/';
	$cilj = '/' . trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' ) . '/';
	$stare = array( '/ready-mom/', '/skola-za-trudnice-ready-mom-podgorica/', '/kategorija/skola-za-trudnice/' );
	if ( $put !== $cilj && in_array( $put, $stare, true ) ) {
		wp_safe_redirect( $url, 301 );
		exit;
	}
}, 0 );

/* 2) Šema Product kad je WooCommerce sam ne napravi.
 * Pravi se na akciji `wp` (glavni upit je tada poznat), a WooCommerce-ov poziv na
 * `woocommerce_single_product_summary` se skida da ne bi bilo dva Product zapisa.
 * Na wp_footer više nije pouzdano: WoodMart prikaz mijenja globalni upit, pa is_product() tamo ne važi. */
add_action( 'wp', function () {
	if ( ! function_exists( 'is_product' ) || ! is_product() || empty( WC()->structured_data ) ) {
		return;
	}
	$sd = WC()->structured_data;
	remove_action( 'woocommerce_single_product_summary', array( $sd, 'generate_product_data' ), 60 );
	$p = wc_get_product( get_queried_object_id() );
	if ( $p ) {
		$sd->generate_product_data( $p );
	}
}, 20 );

/* 3) Kategorije: H1 i tekst ispod artikala. */
add_filter( 'woocommerce_page_title', function ( $naslov ) {
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$h1 = get_term_meta( get_queried_object_id(), 'lm_h1', true );
		if ( $h1 ) {
			return esc_html( $h1 );
		}
	}
	return $naslov;
}, 20 );

add_action( 'woocommerce_after_shop_loop', function () {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() || is_paged() ) {
		return;
	}
	$tekst = get_term_meta( get_queried_object_id(), 'lm_tekst', true );
	if ( $tekst ) {
		echo '<div class="lm-kat-tekst">' . wp_kses_post( wpautop( $tekst ) ) . '</div>';
	}
}, 30 );

add_action( 'wp_head', function () {
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		echo '<style id="lm-kat-tekst">.lm-kat-tekst{max-width:780px;margin:34px auto 10px;color:#7a6250;font-size:15px;line-height:1.7}.lm-kat-tekst h2{font-size:21px;margin:0 0 10px;color:#6d4e39}.lm-kat-tekst p{margin:0 0 12px}.lm-kat-tekst a{color:#6d4e39;text-decoration:underline;text-underline-offset:3px}</style>';
	}
}, 47 );

/* 4) „Default artikal“ iz ERP-a. */
function lm_default_artikli() {
	$ids = get_transient( 'lm_default_artikli' );
	if ( false === $ids ) {
		global $wpdb;
		$ids = array_map( 'intval', $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'product' AND post_name LIKE 'default-artikal%'" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		set_transient( 'lm_default_artikli', $ids, HOUR_IN_SECONDS );
	}
	return $ids;
}
add_filter( 'wpseo_robots', function ( $r ) {
	if ( is_singular( 'product' ) && in_array( (int) get_queried_object_id(), lm_default_artikli(), true ) ) {
		return 'noindex, follow';
	}
	return $r;
} );
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	return array_merge( (array) $ids, lm_default_artikli() );
} );

/* 5) Meta opis proizvoda: cijele rečenice. */
function lm_skrati_recenice( $t, $max ) {
	$t = trim( $t );
	if ( mb_strlen( $t ) <= $max ) {
		return rtrim( $t, " .\t" ) . '.';
	}
	$dio  = mb_substr( $t, 0, $max );
	$kraj = -1;
	foreach ( array( '. ', '! ', '? ' ) as $z ) {
		$i = mb_strrpos( $dio, $z );
		if ( false !== $i && $i > $kraj ) {
			$kraj = $i;
		}
	}
	if ( $kraj > 40 ) {
		return mb_substr( $dio, 0, $kraj + 1 );
	}
	$sp = mb_strrpos( $dio, ' ' );
	return rtrim( mb_substr( $dio, 0, $sp ? $sp : $max ), ' ,;:–-' ) . '…';
}
function lm_meta_opis_proizvoda_recenice( $opis ) {
	if ( ! is_singular( 'product' ) ) {
		return $opis;
	}
	$id = get_queried_object_id();
	if ( get_post_meta( $id, '_yoast_wpseo_metadesc', true ) ) {
		return $opis;
	}
	$p = wc_get_product( $id );
	if ( ! $p ) {
		return $opis;
	}
	$izvor = $p->get_short_description() ? $p->get_short_description() : $p->get_description();
	$tekst = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $izvor ), ENT_QUOTES, 'UTF-8' ) ) );
	if ( '' === $tekst ) {
		return $opis; // Bez opisa: lm_meta_opis_proizvoda() (telefon-i-seo.php).
	}
	return lm_skrati_recenice( $tekst, 100 ) . ' Besplatna dostava po Crnoj Gori od 100 €, plaćanje pouzećem ili karticom.';
}
add_filter( 'wpseo_metadesc', 'lm_meta_opis_proizvoda_recenice', 25 );
add_filter( 'wpseo_opengraph_desc', 'lm_meta_opis_proizvoda_recenice', 25 );

/* 6) Stranica škole: Course i FAQPage. Pitanja su ista kao na stranici (lm_rm_pitanja()). */
add_action( 'wp_head', function () {
	$st = (int) get_option( 'lm_ready_mom_stranica' );
	if ( ! $st || ! is_page( $st ) || ! function_exists( 'lm_rm_pitanja' ) ) {
		return;
	}
	$url    = get_permalink( $st );
	$id     = function_exists( 'lm_rm_proizvod_id' ) ? lm_rm_proizvod_id() : 0;
	$p      = $id ? wc_get_product( $id ) : null;
	$cijena = $p ? (float) wc_get_price_to_display( $p ) : 129.0;
	$slika  = wp_get_attachment_image_url( 35725, 'large' );
	$kurs   = array(
		'@context'          => 'https://schema.org',
		'@type'             => 'Course',
		'@id'               => $url . '#kurs',
		'name'              => 'Škola za trudnice Ready Mom',
		'description'       => 'Priprema za porođaj i prve mjesece sa bebom: 8 predavanja sa 6 stručnjaka (ginekolog, babica, pedijatri, patronažna sestra, psihoterapeut). Uživo u Podgorici ili online.',
		'url'               => $url,
		'inLanguage'        => 'sr-Latn-ME',
		'provider'          => array( '@type' => 'Organization', 'name' => 'Lady Mom', 'url' => home_url( '/' ) ),
		'offers'            => array( '@type' => 'Offer', 'category' => 'Paid', 'price' => number_format( $cijena, 2, '.', '' ), 'priceCurrency' => 'EUR', 'availability' => 'https://schema.org/InStock', 'url' => $url ),
		'hasCourseInstance' => array(
			array( '@type' => 'CourseInstance', 'courseMode' => 'Onsite', 'location' => array( '@type' => 'Place', 'name' => 'Family Nest – Centar za trudnoću i roditeljstvo', 'address' => array( '@type' => 'PostalAddress', 'addressLocality' => 'Podgorica', 'addressCountry' => 'ME' ) ) ),
			array( '@type' => 'CourseInstance', 'courseMode' => 'Online' ),
		),
	);
	if ( $slika ) {
		$kurs['image'] = $slika;
	}
	$faq = array( '@context' => 'https://schema.org', '@type' => 'FAQPage', '@id' => $url . '#pitanja', 'mainEntity' => array() );
	foreach ( lm_rm_pitanja() as $q ) {
		$faq['mainEntity'][] = array( '@type' => 'Question', 'name' => $q[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $q[1] ) ) );
	}
	foreach ( array( $kurs, $faq ) as $j ) {
		echo '<script type="application/ld+json">' . wp_json_encode( $j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
	}
}, 30 );
