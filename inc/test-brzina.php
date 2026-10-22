<?php
/**
 * PRIVREMENO (22.10.2026): varijante početne za PageSpeed test (?lmx=…).
 *
 * Bez parametra lmx stranica je potpuno ista. Fajl se briše čim se mjerenje završi
 * (zapis u docs/podesavanja.md). Varijante:
 *  - img:   prvi slajd dobija pravu sliku (<picture>) umjesto CSS pozadine
 *  - nojs:  stranica bez ijedne skripte (osim JSON-LD)
 *  - notop: gornja traka bez animacije (prva poruka odmah vidljiva)
 *  - 0:     kontrola, bez izmjene
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'litespeed_buffer_after',
	function ( $html ) {
		if ( empty( $_GET['lmx'] ) || ! is_string( $html ) || false === strpos( $html, '</head>' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $html;
		}
		$v = sanitize_key( wp_unslash( $_GET['lmx'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'img' === $v ) {
			$p = strpos( $html, 'id="slide-3240"' );
			$t = '<div class="wd-slide-bg wd-fill"></div>';
			$q = false === $p ? false : strpos( $html, $t, $p );
			if ( false !== $q ) {
				$up  = content_url( 'uploads/2026/10/' );
				$img = '<div class="wd-slide-bg wd-fill"><picture>'
					. '<source media="(max-width: 768px)" srcset="' . esc_url( $up . 'lm-slajd1-prodavnica-mobilni-v4.webp' ) . '" width="800" height="1040">'
					. '<img src="' . esc_url( $up . 'lm-slajd1-prodavnica-desktop-v4.webp' ) . '" width="2400" height="1400" alt="" fetchpriority="high" decoding="async" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center center">'
					. '</picture></div>';
				$html = substr_replace( $html, $img, $q, strlen( $t ) );
				$html = str_replace( '</head>', '<style id="lm-test-img">@media (min-width:769px){#slide-3240 .wd-slide-bg img{object-fit:contain!important}}#slide-3240 .wd-slide-bg{background-image:none!important}</style></head>', $html );
			}
		} elseif ( 'nojs' === $v ) {
			$html = preg_replace( '#<script\b(?![^>]*application/ld\+json)[^>]*>.*?</script>#is', '', $html );
		} elseif ( 'syncss' === $v ) {
			// bez kritičnog CSS-a: cijeli CSS kao običan stylesheet (kao prije 20.10.)
			$html = preg_replace( '#<style id="litespeed-ccss">.*?</style>#s', '', $html );
			$html = preg_replace( '#<link rel="preload" data-asynced="1" data-optimized="2" as="style" onload="[^"]*" href="([^"]+)"\s*/?>#', '<link rel="stylesheet" href="$1" />', $html );
		} elseif ( 'notop' === $v ) {
			$html = str_replace( '</head>', '<style id="lm-test-top">.lm-topbar a{animation:none!important;opacity:1!important;visibility:visible!important;transform:none!important}@media (max-width:1199.98px){.lm-topbar a+a{display:none!important}}</style></head>', $html );
		}

		return str_replace( '</body>', '<!-- lm-test ' . esc_html( $v ) . ' --></body>', $html );
	},
	20
);
