<?php
/**
 * Snippet #15: Jednokratno – redoslijed sekcija B
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

add_action( 'init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! isset( $_GET['lm_bred'] ) ) { return; }

	$arr = json_decode( get_post_meta( 3228, '_elementor_data', true ), true );
	if ( ! is_array( $arr ) ) { return; }

	$redom = array( 'a3ba9e8', '8bad4f7', '2a51e1d', 'ca27740', '7f20f14', '40ec030', 'c1f30b0', '89a6493', '46c4117' );
	$sakri = array( '05d2f6c', '3d963fa' );

	$po_id = array();
	foreach ( $arr as $el ) { $po_id[ $el['id'] ] = $el; }

	foreach ( $redom as $id ) { if ( ! isset( $po_id[ $id ] ) ) { return; } }
	foreach ( $sakri as $id ) { if ( ! isset( $po_id[ $id ] ) ) { return; } }

	foreach ( $sakri as $id ) {
		if ( ! isset( $po_id[ $id ]['settings'] ) ) { $po_id[ $id ]['settings'] = array(); }
		$po_id[ $id ]['settings']['hide_desktop'] = 'hidden-desktop';
		$po_id[ $id ]['settings']['hide_tablet']  = 'hidden-tablet';
		$po_id[ $id ]['settings']['hide_mobile']  = 'hidden-mobile';
	}

	$novi = array();
	foreach ( $redom as $id ) { $novi[] = $po_id[ $id ]; unset( $po_id[ $id ] ); }
	foreach ( $po_id as $el ) { $novi[] = $el; }

	update_post_meta( 3228, '_elementor_data', wp_slash( wp_json_encode( $novi ) ) );
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	$up = wp_upload_dir();
	file_put_contents( $up['basedir'] . '/lm-bred.txt', 'gotovo ' . count( $novi ) );
} );
