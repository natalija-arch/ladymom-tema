<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_register_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
	wp_style_add_data( 'child-style', 'path', get_stylesheet_directory() . '/style.css' );

	wp_enqueue_style( 'child-style' );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );

// Zaštita kopija sajta (staging): uvijek se učitava, na ladymom.me ne radi ništa.
require_once __DIR__ . '/inc/kopija-zastita.php';

/**
 * Lady Mom – prilagođeni kod sajta (inc/).
 * Od 24.10.2026 se učitava uvijek (ranije samo uz opciju lm_tema_kod, dok su radili snippeti;
 * snippeti su ugašeni 14.10.). Uslov je da je WooCommerce aktivan, jer kod računa na njegove funkcije.
 * – zajednicko.php ide prvi: funkcije koje koristi više fajlova.
 * – Ako fajl nedostaje ili ima grešku, ostali se ipak učitaju; admin vidi upozorenje, a na
 *   natalija@ladymom.me stiže email (najviše jednom u 12 sati).
 */
function lm_tema_javi_neucitano( $spisak ) {
	$GLOBALS['lm_tema_neucitano'] = $spisak;
	add_action( 'admin_notices', function () {
		if ( ! current_user_can( 'manage_options' ) || empty( $GLOBALS['lm_tema_neucitano'] ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p><strong>Lady Mom: dio koda sajta nije učitan.</strong> Sajt radi bez tog dijela dok se greška ne ispravi u repozitorijumu ladymom-tema.</p><ul style="list-style:disc;margin-left:20px">';
		foreach ( $GLOBALS['lm_tema_neucitano'] as $f => $r ) {
			echo '<li><code>' . esc_html( $f ) . '</code>: ' . esc_html( $r ) . '</li>';
		}
		echo '</ul></div>';
	} );
	add_action( 'init', function () {
		$spisak = $GLOBALS['lm_tema_neucitano'];
		$kljuc  = 'lm_tema_nije_ucitano_' . md5( wp_json_encode( $spisak ) );
		if ( get_transient( $kljuc ) ) {
			return;
		}
		set_transient( $kljuc, 1, 12 * HOUR_IN_SECONDS );
		error_log( 'Lady Mom tema: nije učitano: ' . wp_json_encode( $spisak, JSON_UNESCAPED_UNICODE ) ); // phpcs:ignore
		$redovi = array();
		foreach ( $spisak as $f => $r ) {
			$redovi[] = '- ' . $f . ': ' . $r;
		}
		wp_mail( 'natalija@ladymom.me', '[Sajt] Dio koda sajta nije učitan', "Na " . home_url( '/' ) . " sljedeći dijelovi prilagođenog koda nijesu učitani:\n\n" . implode( "\n", $redovi ) . "\n\nSajt radi bez njih. Ispravka ide kroz repozitorijum ladymom-tema (Claude). Ovaj email stiže najviše jednom u 12 sati za istu grešku." );
	} );
}

$lm_nije_ucitano = array();
if ( ! class_exists( 'WooCommerce' ) ) {
	$lm_nije_ucitano['WooCommerce'] = 'dodatak nije aktivan, pa se prilagođeni kod (inc/) ne učitava';
} else {
	foreach ( array(
		'zajednicko.php',
		'jezik.php',
		'besplatna-dostava.php',
		'h1-naslovi.php',
		'ux-dodaci.php',
		'obavjestenja.php',
		'seo-schema.php',
		'prodaja-i-brend.php',
		'preusmjerenja.php',
		'emailovi-1-tekstovi.php',
		'emailovi-2-okvir.php',
		'emailovi-3-sadrzaj.php',
		'emailovi-4-dodatni.php',
		'ui-dorade.php',
		'cx-dorade.php',
		'povrat-forma.php',
		'adresa-dostave.php',
		'prevodi-2.php',
		'viber-ios.php',
		'erp.php',
		'erp-uzbuna.php',
		'brendovi.php',
		'aktivni-termini.php',
		'kes.php',
		'zastite.php',
		'server-veza.php',
		'slike-webp.php',
		'telefon-i-seo.php',
		'pocetna-tabovi.php',
		'google-feed.php',
		'meta-katalog.php',
		'google-prijava.php',
		'nalog-uz-porudzbinu.php',
		'molba-za-ocjenu.php',
		'naslov-traka.php',
		'bez-default.php',
		'jedna-opcija.php',
		'ready-mom.php',
		'placanje-provjere.php',
		'prodavnica-dorade.php',
		'placanje-telefon.php',
		'placanje-uslovi.php',
		'dugmad-stil.php',
		'seo-dorade.php',
		'seo-pravila.php',
		'pocetna-dizajn.php',
		'brzina.php',
		'mjerenje.php',
		'kolacici.php',
		'filteri-stil.php',
		'mjerenje-porudzbine.php',
		'clarity-podaci.php',
		'ai-botovi.php',
		'pretraga.php',
		'kategorije-dugmad.php',
		'pregled-dorade.php',
		'povezani-proizvodi.php',
		'pristupacnost.php',
		'put-kupca-mobilni.php',
	) as $lm_fajl ) {
		$lm_put = __DIR__ . '/inc/' . $lm_fajl;
		if ( ! is_readable( $lm_put ) ) {
			$lm_nije_ucitano[ $lm_fajl ] = 'fajl ne postoji na serveru';
			continue;
		}
		try {
			require_once $lm_put;
		} catch ( \Throwable $lm_greska ) {
			$lm_nije_ucitano[ $lm_fajl ] = $lm_greska->getMessage() . ' (red ' . $lm_greska->getLine() . ')';
		}
	}
	unset( $lm_fajl, $lm_put, $lm_greska );
}
if ( $lm_nije_ucitano ) {
	lm_tema_javi_neucitano( $lm_nije_ucitano );
}
unset( $lm_nije_ucitano );
