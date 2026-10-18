<?php
/**
 * Lady Mom – šablon emaila (override, 18.10.2026).
 * Sadržaj crta inc/emailovi-4-dodatni.php. Ako funkcija ne postoji, koristi se originalni šablon.
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'lm_em_dodatni_sablon' ) && isset( $email ) && lm_em_dodatni_sablon( $email, get_defined_vars() ) ) {
	return;
}
include get_template_directory() . '/woocommerce/emails/back-in-stock.php';
