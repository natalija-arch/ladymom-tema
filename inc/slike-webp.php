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

// lm_em_slika_url() (slika za email) je od 24.10.2026 u inc/zajednicko.php, da emailovi ne zavise od ovog fajla.
// Ova kopija ostaje samo za vrijeme prelaska i uklanja se u sljedećoj objavi.
if ( ! function_exists( 'lm_em_slika_url' ) ) {
	function lm_em_slika_url( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
		if ( $url && preg_match( '/\.webp(\?|$)/i', $url ) ) {
			$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		}
		return $url;
	}
}

/**
 * Učitavanje slika artikala u listama (18.10.2026, dopunjeno 24.10.2026).
 * – Na kategorijama, brendovima, prodavnici i pretrazi prve 4 slike artikala u listi (prvi red na
 *   računaru, prva dva reda na telefonu) učitavaju se odmah, a prva dobija visok prioritet, jer je ona
 *   najveći element prvog ekrana (PageSpeed „LCP request discovery“). WordPress im je sam
 *   dodavao loading="lazy", pa ih je telefon tražio tek poslije stilova i skripti.
 * – Ostale slike artikala i druga slika (na hover) čekaju (loading="lazy").
 * – Na ostalim stranicama (početna, artikal) liste su ispod prvog ekrana i sve čekaju.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment, $size ) {
	static $broj = 0;
	if ( is_admin() || wp_doing_ajax() ) {
		return $attr;
	}
	if ( 'woocommerce_thumbnail' !== $size && ! ( is_string( $size ) && false !== strpos( (string) ( $attr['class'] ?? '' ), 'woocommerce_thumbnail' ) ) ) {
		return $attr;
	}
	// Druga slika (na hover) se crta u woodmart_hover_image(). Slike u headeru (mega meni i panel
	// pretrage, skriveni dok se ne otvore) ostaju lijene i ne broje se (vidi inc/brzina.php).
	$hover = in_array( 'woodmart_hover_image', array_column( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 ), 'function' ), true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	if ( $hover || ! empty( $GLOBALS['lm_u_headeru'] ) ) {
		$attr['loading'] = 'lazy';
		unset( $attr['fetchpriority'] );
		return $attr;
	}
	++$broj;
	$lista = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) );
	if ( $lista && $broj <= 4 ) {
		// „eager“ izričito, da WordPress kasnije (filter sadržaja) ne doda „lazy“.
		$attr['loading'] = 'eager';
		if ( 1 === $broj ) {
			$attr['fetchpriority'] = 'high';
		} else {
			unset( $attr['fetchpriority'] );
		}
		return $attr;
	}
	$attr['loading'] = 'lazy';
	unset( $attr['fetchpriority'] );
	return $attr;
}, 20, 3 );
