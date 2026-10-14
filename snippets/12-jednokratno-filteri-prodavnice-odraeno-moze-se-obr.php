<?php
/**
 * Snippet #12: Jednokratno – filteri prodavnice (odrađeno, može se obrisati)
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */


add_action( 'init', function () {
	if ( 'v4' === get_option( 'lm_filteri_v1' ) ) { return; }
	$w = get_option( 'widget_woodmart-woocommerce-layered-nav', array() );
	$uzor = isset( $w[8] ) && is_array( $w[8] ) ? $w[8] : array();
	$w[6] = array_merge( $uzor, array( 'title' => 'Veličina', 'attribute' => 'velicina' ) );
	$w[7] = array_merge( $uzor, array( 'title' => 'Boja', 'attribute' => 'boja' ) );
	update_option( 'widget_woodmart-woocommerce-layered-nav', $w );
	update_option( 'woocommerce_attribute_lookup_enabled', 'no' );
	update_option( 'lm_filteri_v1', 'v4' );
}, 99 );
