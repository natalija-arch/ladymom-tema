<?php
/**
 * Snippet #13: Jednokratno – mapa na kontaktu dolje (odrađeno)
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */


// Jednokratno: mapa na stranici Kontakt ide ispod sadrzaja.
add_action( 'init', function () {
	if ( 'da' === get_option( 'lm_kontakt_mapa_dolje' ) ) { return; }
	$id   = 4439;
	$json = get_post_meta( $id, '_elementor_data', true );
	if ( ! $json ) { return; }
	$data = json_decode( $json, true );
	if ( ! is_array( $data ) || count( $data ) < 2 ) { return; }
	if ( isset( $data[0]['id'] ) && '68d0e93' === $data[0]['id'] ) {
		$mapa = array_shift( $data );
		$data[] = $mapa;
		update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
		if ( class_exists( '\\Elementor\\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		update_option( 'lm_kontakt_mapa_dolje', 'da' );
	}
}, 99 );
