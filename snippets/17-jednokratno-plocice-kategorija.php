<?php
/**
 * Snippet #17: Jednokratno – plocice kategorija
 * Aktivan: ne | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

add_action( 'init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! isset( $_GET['lm_plocice'] ) ) { return; }

	$arr = json_decode( get_post_meta( 3228, '_elementor_data', true ), true );
	if ( ! is_array( $arr ) ) { return; }

	$stavke = array(
		array(
			'id' => 'lmp1001', 'elType' => 'widget', 'widgetType' => 'wd_banner',
			'settings' => array(
				'image' => array( 'url' => 'https://dev.ladymom.me/wp-content/uploads/2026/10/lm-privremeno-garderoba.webp', 'id' => 35930 ),
				'image_size' => 'full',
				'title' => 'Garderoba',
				'subtitle' => 'Mame',
				'content' => '',
				'btn_text' => '',
				'align' => 'left',
				'vertical_alignment' => 'bottom',
				'woodmart_color_scheme' => 'dark',
				'title_size' => 'custom',
				'title_typography_typography' => 'custom',
				'title_typography_font_size' => array( 'unit' => 'px', 'size' => '20', 'sizes' => array() ),
				'title_typography_font_size_tablet' => array( 'unit' => 'px', 'size' => '18', 'sizes' => array() ),
				'title_typography_font_size_mobile' => array( 'unit' => 'px', 'size' => '16', 'sizes' => array() ),
				'title_typography_font_weight' => '700',
				'_element_width' => 'initial',
				'_element_custom_width' => array( 'unit' => '%', 'size' => '31', 'sizes' => array() ),
				'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => '47', 'sizes' => array() ),
				'link' => array( 'url' => '/?product_cat=garderoba', 'is_external' => '', 'nofollow' => '' ),
			),
			'elements' => array(),
		),
		array(
			'id' => 'lmp1002', 'elType' => 'widget', 'widgetType' => 'wd_banner',
			'settings' => array(
				'image' => array( 'url' => 'https://dev.ladymom.me/wp-content/uploads/2026/10/lm-privremeno-dojenje.webp', 'id' => 35931 ),
				'image_size' => 'full',
				'title' => 'Dojenje',
				'subtitle' => 'Mame › Oprema',
				'content' => '',
				'btn_text' => '',
				'align' => 'left',
				'vertical_alignment' => 'bottom',
				'woodmart_color_scheme' => 'dark',
				'title_size' => 'custom',
				'title_typography_typography' => 'custom',
				'title_typography_font_size' => array( 'unit' => 'px', 'size' => '20', 'sizes' => array() ),
				'title_typography_font_size_tablet' => array( 'unit' => 'px', 'size' => '18', 'sizes' => array() ),
				'title_typography_font_size_mobile' => array( 'unit' => 'px', 'size' => '16', 'sizes' => array() ),
				'title_typography_font_weight' => '700',
				'_element_width' => 'initial',
				'_element_custom_width' => array( 'unit' => '%', 'size' => '31', 'sizes' => array() ),
				'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => '47', 'sizes' => array() ),
				'link' => array( 'url' => '/?product_cat=dojenje', 'is_external' => '', 'nofollow' => '' ),
			),
			'elements' => array(),
		),
		array(
			'id' => 'lmp1003', 'elType' => 'widget', 'widgetType' => 'wd_banner',
			'settings' => array(
				'image' => array( 'url' => 'https://dev.ladymom.me/wp-content/uploads/2026/10/lm-privremeno-jastuci.webp', 'id' => 35932 ),
				'image_size' => 'full',
				'title' => 'Jastuci',
				'subtitle' => 'Mame › Oprema',
				'content' => '',
				'btn_text' => '',
				'align' => 'left',
				'vertical_alignment' => 'bottom',
				'woodmart_color_scheme' => 'dark',
				'title_size' => 'custom',
				'title_typography_typography' => 'custom',
				'title_typography_font_size' => array( 'unit' => 'px', 'size' => '20', 'sizes' => array() ),
				'title_typography_font_size_tablet' => array( 'unit' => 'px', 'size' => '18', 'sizes' => array() ),
				'title_typography_font_size_mobile' => array( 'unit' => 'px', 'size' => '16', 'sizes' => array() ),
				'title_typography_font_weight' => '700',
				'_element_width' => 'initial',
				'_element_custom_width' => array( 'unit' => '%', 'size' => '31', 'sizes' => array() ),
				'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => '47', 'sizes' => array() ),
				'link' => array( 'url' => '/?product_cat=jastuci', 'is_external' => '', 'nofollow' => '' ),
			),
			'elements' => array(),
		),
		array(
			'id' => 'lmp1004', 'elType' => 'widget', 'widgetType' => 'wd_banner',
			'settings' => array(
				'image' => array( 'url' => 'https://dev.ladymom.me/wp-content/uploads/2026/10/lm-privremeno-pojasevi.webp', 'id' => 35933 ),
				'image_size' => 'full',
				'title' => 'Pojasevi i nosiljke',
				'subtitle' => 'Mame › Oprema',
				'content' => '',
				'btn_text' => '',
				'align' => 'left',
				'vertical_alignment' => 'bottom',
				'woodmart_color_scheme' => 'dark',
				'title_size' => 'custom',
				'title_typography_typography' => 'custom',
				'title_typography_font_size' => array( 'unit' => 'px', 'size' => '20', 'sizes' => array() ),
				'title_typography_font_size_tablet' => array( 'unit' => 'px', 'size' => '18', 'sizes' => array() ),
				'title_typography_font_size_mobile' => array( 'unit' => 'px', 'size' => '16', 'sizes' => array() ),
				'title_typography_font_weight' => '700',
				'_element_width' => 'initial',
				'_element_custom_width' => array( 'unit' => '%', 'size' => '31', 'sizes' => array() ),
				'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => '47', 'sizes' => array() ),
				'link' => array( 'url' => '/?product_cat=pojasevi', 'is_external' => '', 'nofollow' => '' ),
			),
			'elements' => array(),
		),
		array(
			'id' => 'lmp1005', 'elType' => 'widget', 'widgetType' => 'wd_banner',
			'settings' => array(
				'image' => array( 'url' => 'https://dev.ladymom.me/wp-content/uploads/2026/10/lm-privremeno-bebe.webp', 'id' => 35934 ),
				'image_size' => 'full',
				'title' => 'Njega i kozmetika',
				'subtitle' => 'Bebe',
				'content' => '',
				'btn_text' => '',
				'align' => 'left',
				'vertical_alignment' => 'bottom',
				'woodmart_color_scheme' => 'dark',
				'title_size' => 'custom',
				'title_typography_typography' => 'custom',
				'title_typography_font_size' => array( 'unit' => 'px', 'size' => '20', 'sizes' => array() ),
				'title_typography_font_size_tablet' => array( 'unit' => 'px', 'size' => '18', 'sizes' => array() ),
				'title_typography_font_size_mobile' => array( 'unit' => 'px', 'size' => '16', 'sizes' => array() ),
				'title_typography_font_weight' => '700',
				'_element_width' => 'initial',
				'_element_custom_width' => array( 'unit' => '%', 'size' => '31', 'sizes' => array() ),
				'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => '47', 'sizes' => array() ),
				'link' => array( 'url' => '/?product_cat=bebe', 'is_external' => '', 'nofollow' => '' ),
			),
			'elements' => array(),
		),
		array(
			'id' => 'lmp1006', 'elType' => 'widget', 'widgetType' => 'wd_banner',
			'settings' => array(
				'image' => array( 'url' => 'https://dev.ladymom.me/wp-content/uploads/2026/10/lm-privremeno-torbe.webp', 'id' => 35935 ),
				'image_size' => 'full',
				'title' => 'Torbe i neseseri',
				'subtitle' => 'Mame › Oprema',
				'content' => '',
				'btn_text' => '',
				'align' => 'left',
				'vertical_alignment' => 'bottom',
				'woodmart_color_scheme' => 'dark',
				'title_size' => 'custom',
				'title_typography_typography' => 'custom',
				'title_typography_font_size' => array( 'unit' => 'px', 'size' => '20', 'sizes' => array() ),
				'title_typography_font_size_tablet' => array( 'unit' => 'px', 'size' => '18', 'sizes' => array() ),
				'title_typography_font_size_mobile' => array( 'unit' => 'px', 'size' => '16', 'sizes' => array() ),
				'title_typography_font_weight' => '700',
				'_element_width' => 'initial',
				'_element_custom_width' => array( 'unit' => '%', 'size' => '31', 'sizes' => array() ),
				'_element_custom_width_mobile' => array( 'unit' => '%', 'size' => '47', 'sizes' => array() ),
				'link' => array( 'url' => '/?product_cat=torbe-i-neseseri', 'is_external' => '', 'nofollow' => '' ),
			),
			'elements' => array(),
		),
	);

	$nadjeno = false;
	foreach ( $arr as $i => $el ) {
		if ( '8bad4f7' !== $el['id'] ) { continue; }
		foreach ( $el['elements'] as $j => $dijete ) {
			if ( '58dccd6' !== $dijete['id'] ) { continue; }
			$s = isset( $dijete['settings'] ) ? $dijete['settings'] : array();
			$s['flex_direction'] = 'row';
			$s['flex_wrap'] = 'wrap';
			$s['flex_justify_content'] = 'center';
			$s['flex_gap'] = array( 'size' => '16', 'column' => '16', 'row' => '16', 'unit' => 'px' );
			$s['flex_gap_mobile'] = array( 'size' => '10', 'column' => '10', 'row' => '10', 'unit' => 'px' );
			$arr[ $i ]['elements'][ $j ]['settings'] = $s;
			$arr[ $i ]['elements'][ $j ]['elements'] = $stavke;
			$nadjeno = true;
		}
	}
	if ( ! $nadjeno ) { return; }

	update_post_meta( 3228, '_elementor_data', wp_slash( wp_json_encode( $arr ) ) );
	if ( class_exists( '\\Elementor\\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	$up = wp_upload_dir();
	file_put_contents( $up['basedir'] . '/lm-plocice.txt', 'gotovo ' . count( $stavke ) );
} );
