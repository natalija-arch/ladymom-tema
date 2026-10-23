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

/* ---------- Dostava: prag, cijena i preuzimanje u butiku (24.10.2026) ----------
 * Iznosi se čitaju iz WooCommerce → Podešavanja → Dostava (zona Crna Gora), da tekstovi na sajtu,
 * u emailovima, meta opisima i podacima za Google prate podešavanje. Rezerva: 100 € i 4,50 €.
 * Izračun se čuva u transient-u `lm_dostava_iznosi` (12 h, i odmah se ponavlja kad WooCommerce
 * promijeni verziju keša dostave, tj. kad se sačuva način dostave), jer učitavanje zona na
 * stranici artikla traje ~20 ms. */

/** Uključeni načini dostave po ID-ju (free_shipping, flat_rate, local_pickup…). */
function lm_dostava_metode() {
	$metode = array();
	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return $metode;
	}
	try {
		foreach ( WC_Shipping_Zones::get_zones() as $zona ) {
			foreach ( (array) ( $zona['shipping_methods'] ?? array() ) as $m ) {
				if ( $m instanceof WC_Shipping_Method && $m->is_enabled() && ! isset( $metode[ $m->id ] ) ) {
					$metode[ $m->id ] = $m;
				}
			}
		}
	} catch ( Throwable $e ) {
		$metode = array();
	}
	return $metode;
}

/** Prag (Besplatna dostava → Minimalni iznos) i cijena kurira sa PDV-om (Fiksna cijena + PDV na dostavu). */
function lm_dostava_izracunaj() {
	$prag   = 100.0;
	$cijena = 4.5;
	$m      = lm_dostava_metode();
	if ( isset( $m['free_shipping'] ) && in_array( $m['free_shipping']->get_option( 'requires' ), array( 'min_amount', 'either', 'both' ), true ) ) {
		$iznos = (float) wc_format_decimal( $m['free_shipping']->get_option( 'min_amount' ) );
		if ( $iznos > 0 ) {
			$prag = $iznos;
		}
	}
	$osnovica = isset( $m['flat_rate'] ) ? trim( (string) $m['flat_rate']->get_option( 'cost' ) ) : '';
	if ( '' !== $osnovica && is_numeric( $osnovica ) ) { // formula ([qty], [fee]) – ostaje rezerva
		$iznos = (float) $osnovica;
		try {
			if ( 'taxable' === $m['flat_rate']->get_option( 'tax_status' ) && wc_tax_enabled() ) {
				// Porez se računa po adresi prodavnice (WooCommerce → Porez), PDV 21 %. find_shipping_rates()
				// samo sa državom ne vraća stopu (provjereno 24.10.), pa idu osnovne stope sa „na dostavu“.
				$klasa  = (string) get_option( 'woocommerce_shipping_tax_class' );
				$stope  = array_filter(
					WC_Tax::get_base_tax_rates( 'inherit' === $klasa ? '' : $klasa ),
					function ( $s ) {
						return isset( $s['shipping'] ) && 'yes' === $s['shipping'];
					}
				);
				$iznos += array_sum( WC_Tax::calc_shipping_tax( $iznos, $stope ) );
			}
			if ( $iznos > 0 ) {
				$cijena = round( $iznos, 2 );
			}
		} catch ( Throwable $e ) {
			$cijena = 4.5;
		}
	}
	return array( 'prag' => $prag, 'cijena' => $cijena );
}

/** Iznosi iz keša (ili izračun), jednom po zahtjevu. */
function lm_dostava_iznosi() {
	static $iznosi = null;
	if ( null !== $iznosi ) {
		return $iznosi;
	}
	$verzija = class_exists( 'WC_Cache_Helper' ) ? (string) WC_Cache_Helper::get_transient_version( 'shipping' ) : '';
	$kes     = get_transient( 'lm_dostava_iznosi' );
	if ( is_array( $kes ) && isset( $kes['v'], $kes['prag'], $kes['cijena'] ) && $kes['v'] === $verzija ) {
		$iznosi = $kes;
		return $iznosi;
	}
	$iznosi      = lm_dostava_izracunaj();
	$iznosi['v'] = $verzija;
	set_transient( 'lm_dostava_iznosi', $iznosi, 12 * HOUR_IN_SECONDS );
	return $iznosi;
}

/** Iznos porudžbine od kojeg je dostava besplatna (€). */
function lm_dostava_prag() {
	return (float) lm_dostava_iznosi()['prag'];
}

/** Cijena dostave kurirom sa PDV-om (€). */
function lm_dostava_cijena() {
	return (float) lm_dostava_iznosi()['cijena'];
}

/** Iznos za tekst: „100 €“, „4,50 €“. */
function lm_dostava_tekst( $iznos ) {
	$iznos = (float) $iznos;
	return number_format( $iznos, abs( $iznos - round( $iznos ) ) < 0.005 ? 0 : 2, ',', '.' ) . ' €';
}

/** Da li je način dostave (ID, npr. „local_pickup“ ili „local_pickup:4“) preuzimanje u butiku. */
function lm_je_metoda_preuzimanja( $id ) {
	return is_string( $id ) && ( 0 === strpos( $id, 'local_pickup' ) || 0 === strpos( $id, 'pickup_location' ) );
}

if ( ! function_exists( 'lm_je_preuzimanje' ) ) {
	/** Da li je među izabranim načinima dostave (niz ID-jeva iz korpe) preuzimanje u butiku. */
	function lm_je_preuzimanje( $metode ) {
		foreach ( (array) $metode as $m ) {
			if ( lm_je_metoda_preuzimanja( $m ) ) {
				return true;
			}
		}
		return false;
	}
}

if ( ! function_exists( 'lm_porudzbina_preuzimanje' ) ) {
	/** Da li porudžbina ide na preuzimanje u butiku (korpa, plaćanje, potvrda i emailovi koriste ovu provjeru). */
	function lm_porudzbina_preuzimanje( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return false;
		}
		foreach ( $order->get_shipping_methods() as $s ) {
			if ( lm_je_metoda_preuzimanja( $s->get_method_id() ) ) {
				return true;
			}
		}
		return false;
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
