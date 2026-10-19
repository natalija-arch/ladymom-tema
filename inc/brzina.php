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
