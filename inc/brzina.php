<?php
/**
 * Lady Mom – brzina učitavanja (19.10.2026, analiza PageSpeed).
 *
 * WooCommerce skripte blockUI i js-cookie su se učitavale prije prikaza stranice
 * (PageSpeed: „render-blocking“, po ~450 ms na sporom mobilnom). Dobijaju `defer`,
 * kao i jQuery od kog zavise, pa redoslijed ostaje isti.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( is_admin() ) {
		return $tag;
	}
	$odlozi = array( 'wc-jquery-blockui', 'jquery-blockui', 'wc-js-cookie', 'js-cookie' );
	if ( in_array( $handle, $odlozi, true ) && false === strpos( $tag, ' defer' ) && false === strpos( $tag, ' async' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}, 20, 2 );

/*
 * Slike u headeru (20.10.2026, PageSpeed 51 na telefonu): panel pretrage („Novo“ i
 * članci), mega meni i mobilni meni su skriveni, ali je WordPress njihovih ~7 slika
 * (~120 KB) označavao kao „učitaj odmah“, jer su prve na stranici. Na sporom mobilnom
 * su se otimale za protok sa prvom slikom slajdera (LCP), a trošile su i WordPress-ov
 * limit „prve 3 slike odmah“, pa je glavna slika proizvoda mogla da ostane bez prioriteta.
 * Slike koje nastaju u headeru sada dobijaju loading="lazy" i ne ulaze u to brojanje.
 */
add_action( 'woodmart_after_body_open', function () {
	$GLOBALS['lm_u_headeru'] = true;
}, 1 );
add_action( 'woodmart_after_header', function () {
	$GLOBALS['lm_u_headeru'] = false;
}, 1 );
add_filter( 'pre_wp_get_loading_optimization_attributes', function ( $pre, $tag, $attr = array() ) {
	$logo = is_array( $attr ) && ( false !== stripos( (string) ( isset( $attr['class'] ) ? $attr['class'] : '' ), 'logo' ) || false !== stripos( (string) ( isset( $attr['src'] ) ? $attr['src'] : '' ), 'logo' ) );
	if ( ! empty( $GLOBALS['lm_u_headeru'] ) && 'img' === $tag && ! $logo ) {
		return array( 'loading' => 'lazy', 'decoding' => 'async' );
	}
	return $pre;
}, 10, 3 );

/*
 * Slike artikala i kategorija (22.10.2026, PageSpeed „Improve image delivery“, ~260 KB na telefonu).
 * WordPress za veličinu woocommerce_thumbnail (430 px) piše sizes="(max-width: 430px) 100vw, 430px",
 * pa je telefon (412 px, gustina ekrana 1,75–3) birao 768×1024 za pločicu široku 136–190 px.
 * Pločica je na telefonu najviše pola ekrana, na tabletu trećina, a na računaru oko 300 px, pa
 * pregledač sad bira 430 px (ili 600 px na ekranima veće gustine). Odnosi se na karusele, prodavnicu,
 * kategorije i povezane artikle; velika slika na stranici artikla ima drugu veličinu i ne mijenja se.
 */
add_filter( 'wp_calculate_image_sizes', function ( $sizes, $size ) {
	if ( is_admin() || ! is_array( $size ) || empty( $size[0] ) || ! function_exists( 'wc_get_image_size' ) ) {
		return $sizes;
	}
	$sl = wc_get_image_size( 'woocommerce_thumbnail' );
	if ( ! empty( $sl['width'] ) && (int) $size[0] === (int) $sl['width'] ) {
		return '(max-width: 767px) 50vw, (max-width: 1024px) 34vw, 300px';
	}
	return $sizes;
}, 10, 2 );

/*
 * Slajder na početnoj: pozadine slajdova 2–4 se ne skidaju odmah (22.10.2026).
 * Slajdovi su pravljeni u Elementoru, pa se pozadina svakog slajda ispisuje bez uslova i telefon je
 * odmah, zajedno sa prvom slikom, skidao i ostale tri (~150 KB). WoodMart za svoj blok-slajder već
 * ima pravilo „pozadina tek kad slajd dobije klasu woodmart-loaded“ (el-slider-block.css), a
 * njegova skripta tu klasu dodaje na prvi dodir, skrol ili pomjeranje miša i pri promjeni slajda.
 * Ovdje se isto pravilo primjenjuje i na Elementor slajder, a dvije sekunde poslije učitavanja
 * stranice svi slajdovi dobijaju klasu, da bi slika bila spremna prije prve smjene (6 s).
 * data-no-optimize: LiteSpeed stil ostavlja u zaglavlju, umjesto da ga prebaci u CSS koji stiže kasnije.
 */
add_action( 'wp_head', function () {
	if ( is_admin() || ! is_front_page() ) {
		return;
	}
	echo '<style id="lm-slajdovi-lijeno" data-no-optimize="1">.wd-slider .wd-slide:not(.woodmart-loaded):not(:first-child)>.wd-slide-bg{background-image:none!important}</style>' . "\n";
}, 30 );
add_action( 'wp_footer', function () {
	if ( is_admin() || ! is_front_page() ) {
		return;
	}
	echo '<script id="lm-slajdovi-lijeno-js">window.addEventListener("load",function(){setTimeout(function(){document.querySelectorAll(".wd-slider .wd-slide").forEach(function(s){s.classList.add("woodmart-loaded");});},2000);});</script>' . "\n";
}, 60 );

/*
 * REST i MCP (23.10.2026): sajt već na početku zahtjeva troši ~190–215 MB (22 dodatka,
 * OPcache na serveru nije uključen), pa je indeks /wp-json/ (982 rute, ~2 MB odgovora)
 * prelazio limit od 256 MB i padao sa „kritičnom greškom“. Zbog toga Elementor MCP
 * (Claude) nije mogao da se poveže („Request timed out“). Samo za REST zahtjeve limit
 * se podiže na 512 MB; obične stranice ostaju na 256 MB.
 *
 * Od 24.10.2026 (n45) samo za REST zahtjeve sa prijavom: kolačić prijavljenog korisnika
 * (admin, Elementor), Authorization zaglavlje (MCP, aplikacijske lozinke, ERP preko
 * WooCommerce ključeva) ili consumer_key. Gosti i botovi (npr. na /wp-json/) ostaju na 256 MB.
 * Provjera ne zavisi od toga kad WordPress odredi korisnika, pa ne može da zakine ERP ni MCP.
 */
function lm_rest_sa_prijavom() {
	foreach ( array( 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION', 'PHP_AUTH_USER' ) as $k ) {
		if ( ! empty( $_SERVER[ $k ] ) ) {
			return true;
		}
	}
	if ( isset( $_GET['consumer_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return true;
	}
	foreach ( array_keys( (array) $_COOKIE ) as $k ) {
		if ( 0 === strpos( (string) $k, 'wordpress_logged_in_' ) ) {
			return true;
		}
	}
	return false;
}
add_action( 'rest_api_init', function () {
	if ( ! lm_rest_sa_prijavom() ) {
		return;
	}
	if ( wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) ) < 512 * MB_IN_BYTES ) {
		@ini_set( 'memory_limit', '512M' ); // phpcs:ignore WordPress.PHP.IniSet
	}
}, 0 );
