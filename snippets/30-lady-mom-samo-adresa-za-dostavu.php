<?php
/**
 * Snippet #30: Lady Mom – samo adresa za dostavu
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Lady Mom – kupac vidi samo „Adresa za dostavu“ (12.10.2026).
// WooCommerce je podešen da šalje samo na adresu iz polja za naplatu (woocommerce_ship_to_destination = billing_only),
// pa kupac upisuje jednu adresu. Ta polja se kupcu svuda zovu „Adresa za dostavu“.

// 1) Nazivi.
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $t;
	}
	if ( 'woocommerce' !== $domain && 'woodmart' !== $domain ) {
		return $t;
	}
	switch ( $text ) {
		case 'Billing address':
		case 'Billing Address':
		case 'Shipping address':
		case 'Shipping Address':
			return 'Adresa za dostavu';
		case 'Billing details':
		case 'Billing &amp; Shipping':
		case 'Billing & Shipping':
			return 'Podaci za dostavu';
		case 'Edit %s':
			return 'Promijeni adresu';
		case 'Add %s':
			return 'Dodaj adresu';
		case 'The following addresses will be used on the checkout page by default.':
			return 'Ova adresa se automatski upisuje kad poručujete.';
		case 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.':
		case 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">billing address</a>, and <a href="%3$s">edit your password and account details</a>.':
			return 'Ovdje možete pregledati svoje <a href="%1$s">posljednje porudžbine</a>, promijeniti <a href="%2$s">adresu za dostavu</a> i urediti <a href="%3$s">lozinku i podatke naloga</a>.';
		case 'You have not set up this type of address yet.':
			return 'Još niste sačuvali adresu za dostavu.';
	}
	return $t;
}, 30, 3 );

// 2) „Moj nalog → Adrese“: jedna adresa, sa jasnim nazivom.
add_filter( 'woocommerce_my_account_get_addresses', function ( $a ) {
	return array( 'billing' => 'Adresa za dostavu' );
}, 30 );
add_filter( 'woocommerce_endpoint_edit-address_title', function () {
	return 'Adresa za dostavu';
} );

// 3) Na stranici adrese bez dvije kolone (ostala je jedna).
add_action( 'wp_head', function () {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}
	echo '<style id="lm-adresa">.woocommerce-Addresses.col2-set .woocommerce-Address{width:100%!important;max-width:520px;float:none!important}.woocommerce-Address-title .edit{font-weight:600}</style>';
}, 32 );
