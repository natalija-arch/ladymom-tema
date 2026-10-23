<?php
/**
 * Lady Mom – zaštita kopija sajta (staging) (23.10.2026).
 *
 * Kad ovaj kod radi bilo gdje osim na https://ladymom.me (npr. Softaculous staging
 * kopija), kopija ne smije da dira kupce ni spoljne servise, jer ima istu bazu:
 *  - nijedan email se ne šalje (bilježi se u debug log),
 *  - WP-Cron se ne pokreće (napuštene korpe, lista želja, Meta sinhronizacija, Clarity…),
 *  - odlazni HTTP pozivi su blokirani, osim prema samom serveru i wordpress.org,
 *  - stranice vidi samo prijavljeni administrator, ostali dobijaju 403; sve je noindex,
 *  - Google tag (Site Kit), Meta piksel i Clarity se ne učitavaju.
 * Na živom sajtu ovaj fajl ne radi ništa.
 */

defined( 'ABSPATH' ) || exit;

function lm_je_kopija() {
	static $je = null;
	if ( null === $je ) {
		$je = 'https://ladymom.me' !== untrailingslashit( (string) get_option( 'home' ) );
	}
	return $je;
}

if ( lm_je_kopija() ) {

	if ( ! defined( 'DISABLE_WP_CRON' ) ) {
		define( 'DISABLE_WP_CRON', true );
	}

	add_filter( 'pre_wp_mail', function ( $null, $atts ) {
		error_log( '[LM kopija] email nije poslat: ' . ( is_array( $atts['to'] ) ? implode( ',', $atts['to'] ) : $atts['to'] ) . ' – ' . $atts['subject'] );
		return true;
	}, 1, 2 );

	add_filter( 'pre_http_request', function ( $pre, $args, $url ) {
		$host     = (string) wp_parse_url( $url, PHP_URL_HOST );
		$dozvolja = array( 'ladymom.me', 'api.wordpress.org', 'downloads.wordpress.org', 'localhost', '127.0.0.1' );
		if ( in_array( $host, $dozvolja, true ) ) {
			return $pre;
		}
		return new WP_Error( 'lm_kopija', 'Kopija sajta: spoljni poziv blokiran (' . $host . ').' );
	}, 1, 3 );

	add_filter( 'pre_option_blog_public', '__return_zero' );
	add_filter( 'googlesitekit_analytics-4_tag_blocked', '__return_true' );
	add_filter( 'googlesitekit_ads_tag_blocked', '__return_true' );
	add_filter( 'facebook_for_woocommerce_integration_pixel_enabled', '__return_false' );

	add_action( 'template_redirect', function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			nocache_headers();
			status_header( 403 );
			echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><title>Kopija sajta</title><p style="font:16px sans-serif;margin:3em">Ovo je radna kopija sajta Lady Mom. Prava prodavnica je na <a href="https://ladymom.me">ladymom.me</a>.</p>';
			exit;
		}
	}, 0 );

	add_action( 'admin_bar_menu', function ( $bar ) {
		$bar->add_node( array( 'id' => 'lm-kopija', 'title' => 'KOPIJA SAJTA (staging)', 'href' => admin_url() ) );
	}, 1 );
	add_action( 'admin_head', function () {
		echo '<style>#wpadminbar{background:#a32d2d!important}</style>';
	} );
	add_action( 'wp_head', function () {
		echo '<style>#wpadminbar{background:#a32d2d!important}</style>';
	} );
}
