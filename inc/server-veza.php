<?php
/**
 * Lady Mom – pozivi servera prema samom sajtu idu direktno, ne preko Cloudflare-a.
 *
 * Kad WordPress pozove sopstvenu adresu (LiteSpeed crawler čita sitemap, Site
 * Health, pozadinski zadaci), zahtjev ide preko Cloudflare-a i vraća se na server
 * sa IP adresom data centra. Imunify Security (dodatak hostinga) to vidi kao bota
 * i vraća „429 Rate limit exceeded“ na sat vremena (utvrđeno 18.10.2026).
 * Ovdje se za ladymom.me adresa razrješava direktno na IP servera, pa zahtjev
 * ne prolazi kroz Cloudflare. Sertifikat se i dalje provjerava.
 *
 * IP servera: LiteSpeed → General → Server IP (opcija litespeed.conf.server_ip),
 * a ako nije upisan, adresa servera iz trenutnog zahtjeva.
 */

defined( 'ABSPATH' ) || exit;

function lm_server_ip() {
	$ip = (string) get_option( 'litespeed.conf.server_ip', '' );
	if ( ! $ip && ! empty( $_SERVER['SERVER_ADDR'] ) ) {
		$ip = (string) $_SERVER['SERVER_ADDR'];
	}
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '';
}

add_action( 'http_api_curl', function ( $handle, $args, $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$sajt = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( ! $host || ! in_array( $host, array( $sajt, 'www.' . $sajt ), true ) ) {
		return;
	}
	$ip = lm_server_ip();
	if ( ! $ip ) {
		return;
	}
	$port = wp_parse_url( $url, PHP_URL_PORT );
	if ( ! $port ) {
		$port = 'http' === wp_parse_url( $url, PHP_URL_SCHEME ) ? 80 : 443;
	}
	curl_setopt( $handle, CURLOPT_RESOLVE, array( $host . ':' . $port . ':' . $ip ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}, 10, 3 );
