<?php
/**
 * Snippet #20: Jednokratno – pune sirine kolona
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

add_action( 'init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! isset( $_GET['lm_sirina2'] ) ) { return; }
	$arr = json_decode( get_post_meta( 3228, '_elementor_data', true ), true );
	if ( ! is_array( $arr ) ) { return; }
	$nula = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' );
	foreach ( $arr as $i => $el ) {
		if ( 'lmp0001' === $el['id'] ) {
			$arr[ $i ]['settings']['padding'] = $nula;
			$arr[ $i ]['settings']['padding_mobile'] = $nula;
			$arr[ $i ]['settings']['flex_gap'] = array( 'size' => '0', 'column' => '0', 'row' => '0', 'unit' => 'px' );
		}
	}
	update_post_meta( 3228, '_elementor_data', wp_slash( wp_json_encode( $arr ) ) );
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	$up = wp_upload_dir();
	file_put_contents( $up['basedir'] . '/lm-sirina.txt', 'gotovo2' );
} );
