<?php
/**
 * Lady Mom – plaćanje: ispravke nađene u end-to-end testu (20.10.2026).
 *
 * - Telefon: prolazio je i broj „12“. Sada mora imati 8–15 cifara
 *   (067 123 456, +382 67 123 456, 00382…).
 * - „Plaćanje pouzećem“ se nudilo i kod preuzimanja u butiku i za Ready Mom
 *   (nema dostave). Naziv i opis se mijenjaju prema korpi:
 *     preuzimanje u butiku → „Plaćanje u butiku“,
 *     korpa bez dostave (npr. Ready Mom) → „Plaćanje gotovinom“.
 *   Isti naziv se upisuje na porudžbinu, pa ga vide i emailovi i ERP.
 * - Stranica „Porudžbina primljena“ je za Ready Mom pisala „kuriru ili u butiku“.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_after_checkout_validation', function ( $podaci, $greske ) {
	$tel = isset( $podaci['billing_phone'] ) ? (string) $podaci['billing_phone'] : '';
	if ( '' === trim( $tel ) ) {
		return; // Prazno polje već prijavljuje WooCommerce.
	}
	$cifre = preg_replace( '/\D+/', '', $tel );
	if ( strlen( $cifre ) < 8 || strlen( $cifre ) > 15 ) {
		$greske->add( 'billing_phone_validation', 'Telefon nije ispravan. Upiši broj sa pozivnim brojem, npr. 067 123 456.', array( 'id' => 'billing_phone' ) );
	}
}, 20, 2 );

/** Vrsta plaćanja gotovinom za trenutnu korpu: 'butik', 'bez_dostave' ili ''. */
function lm_cod_vrsta_korpe() {
	if ( ( is_admin() && ! wp_doing_ajax() ) || ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) {
		return '';
	}
	if ( ! WC()->cart->needs_shipping() ) {
		return 'bez_dostave';
	}
	return lm_je_preuzimanje( WC()->session->get( 'chosen_shipping_methods', array() ) ) ? 'butik' : '';
}

/** Ista provjera za već napravljenu porudžbinu. */
function lm_cod_vrsta_porudzbine( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return '';
	}
	if ( ! $order->get_shipping_methods() ) {
		return 'bez_dostave';
	}
	return lm_porudzbina_preuzimanje( $order ) ? 'butik' : '';
}

add_filter( 'woocommerce_gateway_title', function ( $naziv, $id ) {
	if ( 'cod' !== $id ) {
		return $naziv;
	}
	$v = lm_cod_vrsta_korpe();
	if ( 'butik' === $v ) {
		return 'Plaćanje u butiku';
	}
	if ( 'bez_dostave' === $v ) {
		return 'Plaćanje gotovinom';
	}
	return $naziv;
}, 20, 2 );

add_filter( 'woocommerce_gateway_description', function ( $opis, $id ) {
	if ( 'cod' !== $id ) {
		return $opis;
	}
	$v = lm_cod_vrsta_korpe();
	if ( 'butik' === $v ) {
		return 'Plaćaš prilikom preuzimanja u butiku, Branka Deletića bb.';
	}
	if ( 'bez_dostave' === $v ) {
		return 'Plaćaš gotovinom. Javićemo ti se sa detaljima.';
	}
	return $opis;
}, 20, 2 );

// „Porudžbina primljena“: uputstvo za plaćanje gotovinom prema porudžbini.
add_action( 'woocommerce_thankyou_cod', function ( $order_id ) {
	$v = lm_cod_vrsta_porudzbine( wc_get_order( $order_id ) );
	if ( ! $v || ! function_exists( 'WC' ) ) {
		return;
	}
	$gw = WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();
	if ( isset( $gw['cod'] ) ) {
		$gw['cod']->instructions = 'butik' === $v
			? 'Porudžbinu plaćaš prilikom preuzimanja u butiku, Branka Deletića bb. Javićemo ti kad bude spremna.'
			: 'Plaćaš gotovinom. Javićemo ti se telefonom ili emailom sa detaljima.';
	}
}, 1 );

/*
 * Korpa bez dostave (npr. Ready Mom), Markovo odobrenje 20.10.2026: na plaćanju samo
 * ime, prezime, telefon i email. Ulica i grad se ne traže, a naslov je „Vaši podaci“.
 */
function lm_korpa_bez_dostave() {
	return did_action( 'wp_loaded' ) && function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() && ! WC()->cart->needs_shipping();
}
add_filter( 'woocommerce_checkout_fields', function ( $f ) {
	if ( ! lm_korpa_bez_dostave() ) {
		return $f;
	}
	foreach ( array( 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_state', 'billing_company' ) as $k ) {
		unset( $f['billing'][ $k ] );
	}
	return $f;
}, 120 );
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( ( 'Billing details' === $text || 'Billing &amp; Shipping' === $text || 'Billing & Shipping' === $text ) && 'woocommerce' === $domain && lm_korpa_bez_dostave() ) {
		return 'Vaši podaci';
	}
	return $t;
}, 40, 3 );
