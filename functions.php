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

/**
 * Lady Mom – prilagođeni kod sajta (inc/).
 * Ranije je bio u dodatku Code Snippets. Učitava se samo kad je opcija
 * lm_tema_kod = 1, da se kod ne bi izvršio dva puta dok su snippeti aktivni.
 */
if ( get_option( 'lm_tema_kod' ) ) {
	foreach ( array(
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
		'ui-dorade.php',
		'cx-dorade.php',
		'cf7-php82.php',
		'povrat-forma.php',
		'adresa-dostave.php',
		'prevodi-2.php',
		'viber-ios.php',
		'erp.php',
		'brendovi.php',
	) as $lm_fajl ) {
		require_once __DIR__ . '/inc/' . $lm_fajl;
	}
	unset( $lm_fajl );
}
