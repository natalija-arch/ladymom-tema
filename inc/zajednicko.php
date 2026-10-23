<?php
/**
 * Lady Mom – zajedničke funkcije (24.10.2026).
 * Učitava se prvi (functions.php). Ovdje su funkcije koje koristi više fajlova iz inc/,
 * da izbacivanje ili greška u jednom fajlu ne obori ostale (npr. emailove o porudžbinama).
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'lm_je_default' ) ) {
	/** Vrijednost „Default“ koju ERP šalje kad atribut nije popunjen (koristi se u prikazu, feedu i SEO-u). */
	function lm_je_default( $v ) {
		return 'default' === strtolower( trim( wp_strip_all_tags( (string) $v ) ) );
	}
}

if ( ! function_exists( 'lm_em_slika_url' ) ) {
	/** Slika za email: mala kopija ako je JPG/PNG, inače original (JPG iz ERP-a), jer email programi slabo prikazuju WebP. */
	function lm_em_slika_url( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
		if ( $url && preg_match( '/\.webp(\?|$)/i', $url ) ) {
			$url = wp_get_attachment_image_url( $attachment_id, 'full' );
		}
		return $url;
	}
}

/**
 * Da li su učitani svi dijelovi Lady Mom emailova (inc/emailovi-1…4). Ako nisu, šabloni u
 * woocommerce/emails/ koriste originalne WooCommerce šablone umjesto da email padne napola.
 */
function lm_em_sve_ucitano() {
	static $ok = null;
	if ( null === $ok ) {
		$ok = true;
		foreach ( array(
			// emailovi-1-tekstovi.php
			'lm_em_ids', 'lm_em_admin_ids', 'lm_em_font', 'lm_em_order', 'lm_em_copy', 'lm_em_card', 'lm_em_media',
			// emailovi-2-okvir.php
			'lm_em_header', 'lm_em_footer', 'lm_em_dovrsi', 'lm_em_red', 'lm_em_box', 'lm_em_hero', 'lm_em_btn', 'lm_em_btns', 'lm_em_label', 'lm_em_tracker',
			// emailovi-3-sadrzaj.php i emailovi-4-dodatni.php
			'lm_em_body', 'lm_em_dodatni_copy',
		) as $f ) {
			if ( ! function_exists( $f ) ) {
				$ok = false;
				break;
			}
		}
	}
	return $ok;
}
