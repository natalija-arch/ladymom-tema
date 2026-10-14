<?php
/**
 * Lady Mom – šablon emaila (override, 9.10.2026).
 * Sadržaj crtaju snippeti „Lady Mom – emailovi (1–3/3)“. Ako su isključeni, koristi se WooCommerce šablon.
 */
defined( 'ABSPATH' ) || exit;
if ( function_exists( 'lm_em_handles' ) && isset( $email ) && lm_em_handles( $email ) ) {
	lm_em_header( $email_heading, $email );
	lm_em_body( $email, get_defined_vars() );
	lm_em_footer( $email );
	return;
}
include WC()->plugin_path() . '/templates/emails/customer-new-account.php';
