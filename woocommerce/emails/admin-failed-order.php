<?php
/**
 * Lady Mom – email butiku (override, 20.10.2026).
 * Sadržaj crta inc/emailovi-3-sadrzaj.php (lm_em_admin_body). Ako funkcije ne postoje, koristi se WooCommerce šablon.
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'lm_em_handles' ) && isset( $email ) && lm_em_handles( $email ) && isset( $order ) && is_a( $order, 'WC_Order' ) ) {
	lm_em_header( $email_heading, $email );
	lm_em_body( $email, get_defined_vars() );
	lm_em_footer( $email );
	return;
}
include WC()->plugin_path() . '/templates/emails/admin-failed-order.php';
