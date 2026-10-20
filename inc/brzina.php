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
add_filter( 'pre_wp_get_loading_optimization_attributes', function ( $pre, $tag ) {
	if ( ! empty( $GLOBALS['lm_u_headeru'] ) && 'img' === $tag ) {
		return array( 'loading' => 'lazy', 'decoding' => 'async' );
	}
	return $pre;
}, 10, 2 );
