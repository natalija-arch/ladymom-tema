<?php
/**
 * Lady Mom – manje kopije slika u WebP formatu (Markova odluka 18.10.2026).
 *
 * ERP šalje slike artikala kao JPG. Original ostaje tačno kako ga je ERP poslao;
 * WordPress samo kopije koje sam pravi (sličice, slika na stranici proizvoda…)
 * snima kao WebP, koji je za oko četvrtinu do trećinu lakši. Važi i kad ERP
 * istu sliku pošalje ponovo.
 *
 * Emailovi koriste original (JPG), jer Outlook na računaru ne prikazuje WebP.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'image_editor_output_format', function ( $formati ) {
	// Glavna slika ostaje JPG: WordPress je pretvara samo ako ovaj filter to traži
	// direktno iz wp_create_image_subsizes(); kopije idu preko get_output_format().
	$poziv = array_column( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 8 ), 'function' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	if ( in_array( 'wp_create_image_subsizes', $poziv, true ) && ! in_array( 'get_output_format', $poziv, true ) ) {
		return $formati;
	}
	if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formati['image/jpeg'] = 'image/webp';
	}
	return $formati;
} );

/** Slika za email: mala kopija ako je JPG/PNG, inače original (JPG iz ERP-a). */
function lm_em_slika_url( $attachment_id ) {
	$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	if ( $url && preg_match( '/\.webp(\?|$)/i', $url ) ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'full' );
	}
	return $url;
}

/**
 * Odloženo učitavanje slika artikala u listama (18.10.2026).
 * WordPress `loading="lazy"` ne dodaje slikama artikala jer se liste prikazuju
 * izvan glavne petlje, pa telefon odmah skida i slike niže na stranici.
 * Prve 4 slike (prvi red) se učitavaju odmah; ostale i druga slika na hover čekaju.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
	static $broj = 0;
	if ( is_admin() || wp_doing_ajax() || isset( $attr['loading'] ) ) {
		return $attr;
	}
	if ( 'woocommerce_thumbnail' !== $size && ! ( is_string( $size ) && false !== strpos( (string) ( $attr['class'] ?? '' ), 'woocommerce_thumbnail' ) ) ) {
		return $attr;
	}
	// Druga slika (na hover) se crta u woodmart_hover_image().
	$hover = in_array( 'woodmart_hover_image', array_column( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 ), 'function' ), true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	if ( $hover ) {
		$attr['loading'] = 'lazy';
		return $attr;
	}
	++$broj;
	if ( $broj > 4 ) {
		$attr['loading'] = 'lazy';
	}
	return $attr;
}, 20, 3 );
