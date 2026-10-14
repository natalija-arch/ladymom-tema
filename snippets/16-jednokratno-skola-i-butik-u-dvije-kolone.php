<?php
/**
 * Snippet #16: Jednokratno – skola i butik u dvije kolone
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

add_action( 'init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! isset( $_GET['lm_skola'] ) ) { return; }

	$arr = json_decode( get_post_meta( 3228, '_elementor_data', true ), true );
	if ( ! is_array( $arr ) ) { return; }

	$idx = null;
	foreach ( $arr as $i => $el ) { if ( $el['id'] === 'c1f30b0' ) { $idx = $i; } }
	if ( null === $idx ) { return; }
	foreach ( $arr[ $idx ]['elements'] as $el ) { if ( 'c1a2b31' === $el['id'] ) { return; } }

	$sirina = array(
		'_column_size'  => 50,
		'content_width' => 'full',
		'width'         => array( 'unit' => '%', 'size' => '50', 'sizes' => array() ),
		'width_mobile'  => array( 'unit' => '%', 'size' => '100', 'sizes' => array() ),
		'flex_direction' => 'column',
		'flex_gap'      => array( 'size' => '18', 'column' => '18', 'row' => '18', 'unit' => 'px' ),
		'flex_justify_content' => 'center',
		'padding'        => array( 'unit' => 'px', 'top' => '54', 'right' => '40', 'bottom' => '54', 'left' => '40', 'isLinked' => '' ),
		'padding_mobile' => array( 'unit' => 'px', 'top' => '32', 'right' => '20', 'bottom' => '32', 'left' => '20', 'isLinked' => '' ),
		'background_background' => 'classic',
	);

	$skola = array(
		'id' => 'c1a2b31', 'elType' => 'container', 'isInner' => true,
		'settings' => array_merge( $sirina, array( 'background_color' => '#3F6265' ) ),
		'elements' => $arr[ $idx ]['elements'],
	);

	$butik = array(
		'id' => 'c1a2b32', 'elType' => 'container', 'isInner' => true,
		'settings' => array_merge( $sirina, array( 'background_color' => '#F6DED4' ) ),
		'elements' => array(
			array(
				'id' => 'w1a2b33', 'elType' => 'widget', 'widgetType' => 'wd_title',
				'settings' => array(
					'subtitle' => 'Butik u Podgorici',
					'title'    => 'Branka Deletića bb',
					'after_title' => '<p>Pon–sub 9:30–21:00, nedjelja zatvoreno. Dođi da probaš i pitaš — izmjerimo te i savjetujemo.</p>',
					'color'    => 'primary',
				),
				'elements' => array(),
			),
			array(
				'id' => 'w1a2b34', 'elType' => 'widget', 'widgetType' => 'wd_button',
				'settings' => array(
					'color' => 'primary',
					'_element_width' => 'auto',
					'text'  => 'Otvori mapu',
					'link'  => array( 'url' => 'https://www.google.com/maps/search/?api=1&query=Lady+Mom+prodavnica+za+trudnice+i+mame&query_place_id=ChIJU0tXHcnrTRMRuXXUSvfb4b8', 'is_external' => 'on', 'nofollow' => '' ),
				),
				'elements' => array(),
			),
		),
	);

	$arr[ $idx ]['elements'] = array( $skola, $butik );
	$s = isset( $arr[ $idx ]['settings'] ) ? $arr[ $idx ]['settings'] : array();
	$s['flex_direction']        = 'row';
	$s['flex_direction_mobile'] = 'column';
	$s['flex_gap']              = array( 'size' => '0', 'column' => '0', 'row' => '0', 'unit' => 'px' );
	$s['padding']               = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' );
	$s['padding_mobile']        = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => '1' );
	$s['background_color']      = '#FFFFFF';
	$s['flex_align_items']      = 'stretch';
	$arr[ $idx ]['settings']    = $s;

	update_post_meta( 3228, '_elementor_data', wp_slash( wp_json_encode( $arr ) ) );
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	$up = wp_upload_dir();
	file_put_contents( $up['basedir'] . '/lm-skola.txt', 'gotovo' );
} );
