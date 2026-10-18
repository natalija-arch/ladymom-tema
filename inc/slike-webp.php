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
