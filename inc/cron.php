<?php
/**
 * Lady Mom – pravi cron sa servera (24.10.2026, stavka n19).
 *
 * WP-Cron se inače pokreće samo kad neko otvori nekešnu stranicu, pa ERP uzbuna, napuštena
 * korpa, lista čekanja i noćni zadaci kasne kad je sajt miran. Kad server sam pokreće
 * wp-cron.php (cPanel → Cron Jobs, svakih 5 minuta):
 *
 *   /opt/alt/php83/usr/bin/php -d memory_limit=512M -q /home/ladymomm/public_html/wp-cron.php >/dev/null 2>&1
 *
 * ovaj fajl to bilježi (opcija `lm_pravi_cron`, vrijeme posljednjeg pokretanja) i gasi
 * pokretanje cron-a iz posjeta (DISABLE_WP_CRON), da posjetioci ne pokreću zadatke.
 * Ako server prestane da ga pokreće duže od 15 minuta, pokretanje iz posjeta se samo vraća,
 * pa zadaci ne mogu da stanu. wp-config.php se ne mijenja.
 * Dok cron na serveru nije podešen, fajl ne radi ništa.
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'DOING_CRON' ) && DOING_CRON && 'cli' === PHP_SAPI ) {
	update_option( 'lm_pravi_cron', time(), true );
}

if ( ! defined( 'DISABLE_WP_CRON' ) ) {
	$lm_pravi_cron = (int) get_option( 'lm_pravi_cron', 0 );
	if ( $lm_pravi_cron && time() - $lm_pravi_cron < 15 * MINUTE_IN_SECONDS ) {
		define( 'DISABLE_WP_CRON', true );
	}
	unset( $lm_pravi_cron );
}
