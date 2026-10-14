<?php
/**
 * Snippet #19: Jednokratno – pune sirine traka
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

add_action( 'init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! isset( $_GET['lm_puna'] ) ) { return; }
	$arr = json_decode( get_post_meta( 3228, '_elementor_data', true ), true );
	if ( ! is_array( $arr ) ) { return; }
	$meta = array( 'lmb0001', 'lmp0001', 'lms0001' );
	foreach ( $arr as $i => $el ) {
		if ( in_array( $el['id'], $meta, true ) ) {
			$arr[ $i ]['settings']['wd_section_stretch'] = 'stretch';
		}
	}
	update_post_meta( 3228, '_elementor_data', wp_slash( wp_json_encode( $arr ) ) );
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	$up = wp_upload_dir();
	file_put_contents( $up['basedir'] . '/lm-puna.txt', 'gotovo' );
} );
