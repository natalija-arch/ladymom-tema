<?php
/**
 * Lady Mom – šablon emaila (override, 18.10.2026).
 * Sadržaj crta inc/emailovi-4-dodatni.php. Ako funkcija ne postoji, koristi se originalni šablon.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 11.0.0
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'lm_em_dodatni_sablon' ) && isset( $email ) && lm_em_dodatni_sablon( $email, get_defined_vars() ) ) {
	return;
}
include WC()->plugin_path() . '/templates/emails/customer-verify-email.php';
