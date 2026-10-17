<?php
/**
 * ERP (TiramisuERP) – veza preko WooCommerce REST API-ja.
 *
 * Porudžbine sa starog sajta su prenesene pod novim brojevima (stari broj je u
 * meta polju `_lm_stari_broj`). ERP ih već ima pod starim brojevima, pa ih ne
 * smije ponovo vidjeti kao nove. Zato ERP-ov korisnik u listi porudžbina preko
 * REST API-ja dobija samo porudžbine nastale na novom sajtu.
 *
 * Ostali (admin, WooCommerce aplikacija, izvještaji) i dalje vide sve porudžbine.
 * ERP-ov korisnik je Zoran Mastilović; ID se čuva u opciji `lm_erp_korisnik`.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_rest_orders_prepare_object_query', function ( $args ) {
	$erp = (int) get_option( 'lm_erp_korisnik', 0 );
	if ( ! $erp || get_current_user_id() !== $erp ) {
		return $args;
	}
	$mq   = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
	$mq[] = array(
		'key'     => '_lm_stari_broj',
		'compare' => 'NOT EXISTS',
	);
	$args['meta_query'] = $mq;
	return $args;
} );
