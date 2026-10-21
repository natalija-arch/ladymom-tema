<?php
/**
 * Lady Mom – šablon emaila liste čekanja (override, 21.10.2026, r6).
 * Sadržaj crta inc/emailovi-4-dodatni.php. Ako funkcija ne postoji, koristi se originalni WoodMart šablon.
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'lm_em_dodatni_sablon' ) && isset( $email ) && lm_em_dodatni_sablon( $email, get_defined_vars() ) ) {
	return;
}
include get_template_directory() . '/woocommerce/emails/waitlist-in-stock.php';
