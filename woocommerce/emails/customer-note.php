<?php
/**
 * Lady Mom – šablon emaila (override, 9.10.2026).
 * Sadržaj crtaju inc/emailovi-1…4-*.php. Ako neki od njih nije učitan, koristi se WooCommerce šablon.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 10.4.0
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'lm_em_handles' ) && isset( $email ) && lm_em_handles( $email ) ) {
	lm_em_header( $email_heading, $email );
	lm_em_body( $email, get_defined_vars() );
	lm_em_footer( $email );
	return;
}
include WC()->plugin_path() . '/templates/emails/customer-note.php';
