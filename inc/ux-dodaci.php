<?php
/**
 * Lady Mom – UX dodaci (povjerenje, checkout, meta opisi)
 * (Ranije Code Snippets #8, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – UX dodaci: traka povjerenja, checkout polja, meta opisi.

// 1) Traka povjerenja na stranici proizvoda, ispod dugmeta "Dodaj u korpu".
add_action( 'woocommerce_single_product_summary', function () {
	$items = array(
		array( 'M3 7h11v9H3zM14 10h4l3 3v3h-7', 'Besplatna dostava za porudžbine od 100 €' ),
		array( 'M12 7v5l3 2M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z', 'Dostava na teritoriji Crne Gore za 1–3 radna dana' ),
		array( 'M4 12a8 8 0 0 1 14-5l2-2v6h-6l2-2a5 5 0 1 0 1 6', 'Povrat ili zamjena u roku od 14 dana' ),
		array( 'M3 6h18v12H3zM3 10h18', 'Plaćanje pouzećem ili karticom (Visa, Mastercard)' ),
	);
	echo '<ul class="lm-trust">';
	foreach ( $items as $it ) {
		echo '<li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="' . esc_attr( $it[0] ) . '"/></svg><span>' . esc_html( $it[1] ) . '</span></li>';
	}
	echo '</ul>';
}, 35 );

// 2) Checkout: samo potrebna polja (ime, prezime, ulica i broj, grad, telefon, email).
add_filter( 'woocommerce_default_address_fields', function ( $f ) {
	unset( $f['postcode'], $f['state'] ); // firma i adresa 2: Prilagodi → WooCommerce → Plaćanje (sakriveno)
	if ( isset( $f['address_1'] ) ) {
		$f['address_1']['label']       = 'Ulica i broj';
		$f['address_1']['placeholder'] = 'npr. Branka Deletića 12, stan 5';
	}
	if ( isset( $f['country'] ) ) {
		$f['country']['class'][] = 'lm-hidden-field';
	}
	return $f;
}, 1000 );
add_filter( 'woocommerce_billing_fields', function ( $f ) {
	unset( $f['billing_postcode'], $f['billing_state'] );
	if ( isset( $f['billing_phone'] ) ) {
		$f['billing_phone']['description'] = 'Kurir će vas pozvati prije isporuke.';
		$f['billing_phone']['priority']    = 25;
	}
	return $f;
}, 1000 );
add_filter( 'woocommerce_shipping_fields', function ( $f ) {
	unset( $f['shipping_postcode'], $f['shipping_state'], $f['shipping_phone'] );
	return $f;
}, 1000 );
add_filter( 'woocommerce_get_country_locale', function ( $locale ) {
	$locale['ME']['postcode'] = array( 'required' => false, 'hidden' => true );
	$locale['ME']['state']    = array( 'required' => false, 'hidden' => true );
	return $locale;
}, 1000 );
add_action( 'wp_head', function () {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		echo '<style>.lm-hidden-field{display:none!important}</style>';
	}
} );

// 3) Napomena o sigurnom plaćanju iznad dugmeta za poručivanje.
add_action( 'woocommerce_review_order_before_submit', function () {
	// Tekst je u jednom <span>: .lm-secure-pay je flex, pa bi inače svaka riječ bila poseban dio reda (pregled 21.10., r5).
	echo '<p class="lm-secure-pay"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 10V7a6 6 0 0 1 12 0v3M5 10h14v11H5z"/></svg><span>Plaćanje karticom je zaštićeno (3D Secure) – prihvatamo <strong>Visa</strong> i <strong>Mastercard</strong>.</span></p>';
	// Napomena o NLB ratama je iznad načina plaćanja (od 30 €), pa se ovdje ne ponavlja (20.10.2026).
} );

// 5) Preuzimanje u butiku: bez ulice i grada, podaci butika u korpi, na potvrdi i u emailu kupcu.
function lm_je_preuzimanje( $metode ) {
	foreach ( (array) $metode as $m ) {
		if ( is_string( $m ) && 0 === strpos( $m, 'local_pickup' ) ) return true;
	}
	return false;
}
function lm_izabrano_preuzimanje() {
	if ( isset( $_POST['shipping_method'] ) ) {
		return lm_je_preuzimanje( wc_clean( wp_unslash( $_POST['shipping_method'] ) ) );
	}
	if ( function_exists( 'WC' ) && WC()->session ) {
		return lm_je_preuzimanje( WC()->session->get( 'chosen_shipping_methods' ) );
	}
	return false;
}
add_filter( 'woocommerce_checkout_fields', function ( $f ) {
	if ( lm_izabrano_preuzimanje() ) {
		foreach ( array( 'billing_address_1', 'billing_city' ) as $k ) {
			if ( isset( $f['billing'][ $k ] ) ) $f['billing'][ $k ]['required'] = false;
		}
	}
	return $f;
}, 99 );
add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) return;
	?>
<script id="lm-preuzimanje-js">
jQuery(function ($) {
	var polja = '#billing_address_1_field, #billing_city_field';
	function lmPreuzimanje() {
		var m = $('input[name^="shipping_method"]:checked').val() || $('input[name^="shipping_method"][type="hidden"]').val() || $('select[name^="shipping_method"]').val() || '';
		var p = m.indexOf('local_pickup') === 0;
		$(polja).toggle(!p).toggleClass('validate-required', !p);
		if (p) { $(polja).removeClass('woocommerce-invalid woocommerce-invalid-required-field'); }
	}
	$(document.body).on('updated_checkout', lmPreuzimanje);
	$(document.body).on('change', 'input[name^="shipping_method"], select[name^="shipping_method"]', lmPreuzimanje);
	lmPreuzimanje();
});
</script>
	<?php
}, 30 );
function lm_butik_mapa() {
	return 'https://www.google.com/maps/search/?api=1&query=Lady+Mom+prodavnica+za+trudnice+i+mame&query_place_id=ChIJU0tXHcnrTRMRuXXUSvfb4b8';
}
add_action( 'woocommerce_after_shipping_rate', function ( $method ) {
	if ( 0 !== strpos( $method->get_method_id(), 'local_pickup' ) ) return;
	echo '<div class="lm-pickup-info">Branka Deletića bb, Podgorica · pon–sub 9:30–21:00</div>';
} );
function lm_porudzbina_preuzimanje( $order ) {
	if ( ! $order || ! is_a( $order, 'WC_Order' ) ) return false;
	foreach ( $order->get_shipping_methods() as $s ) {
		if ( 0 === strpos( $s->get_method_id(), 'local_pickup' ) ) return true;
	}
	return false;
}
function lm_butik_html( $email = false ) {
	$box = $email
		? 'background:#f6ded4;border-radius:10px;padding:16px 18px;margin:0 0 24px;color:#6d4e39;font-size:14px;line-height:1.6'
		: '';
	$h = $email ? 'margin:0 0 8px;font-size:17px;color:#6d4e39' : '';
	$a = $email ? 'color:#a05c57;font-weight:700' : '';
	$o  = '<div class="lm-preuzimanje"' . ( $box ? ' style="' . $box . '"' : '' ) . '>';
	$o .= '<h2' . ( $h ? ' style="' . $h . '"' : '' ) . '>Preuzimanje u butiku</h2>';
	$o .= '<p' . ( $email ? ' style="margin:0 0 4px"' : '' ) . '><strong>Lady Mom</strong>, Branka Deletića bb, 81000 Podgorica</p>';
	$o .= '<p' . ( $email ? ' style="margin:0 0 4px"' : '' ) . '>Radno vrijeme: ponedjeljak–subota 9:30–21:00, nedjeljom ne radimo</p>';
	$o .= '<p' . ( $email ? ' style="margin:0 0 10px"' : '' ) . '>Telefon: <a href="tel:+38267030655"' . ( $a ? ' style="' . $a . '"' : '' ) . '>067 030 655</a></p>';
	$o .= '<p style="margin:0"><a class="lm-preuzimanje-mapa" href="' . esc_url( lm_butik_mapa() ) . '" target="_blank" rel="noopener"' . ( $a ? ' style="' . $a . '"' : '' ) . '>Otvori mapu</a></p>';
	$o .= '</div>';
	return $o;
}
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	if ( lm_porudzbina_preuzimanje( wc_get_order( $order_id ) ) ) echo lm_butik_html();
}, 5 );
add_action( 'woocommerce_email_order_details', function ( $order, $sent_to_admin, $plain_text, $email ) {
	if ( $sent_to_admin || ! lm_porudzbina_preuzimanje( $order ) ) return;
	if ( $plain_text ) {
		echo "\nPREUZIMANJE U BUTIKU\nLady Mom, Branka Deletića bb, 81000 Podgorica\nRadno vrijeme: ponedjeljak–subota 9:30–21:00, nedjeljom ne radimo\nTelefon: 067 030 655\nMapa: " . lm_butik_mapa() . "\n\n";
		return;
	}
	echo lm_butik_html( true );
}, 5, 4 );

// 6) Traka za kolačiće: ispod bočnih panela (korpa, meni) i kompaktnija na telefonu. Stil za podatke butika.
add_action( 'wp_head', function () {
	echo '<style id="lm-preuzimanje-kolacici">'
		. '.wd-cookies-popup{z-index:395!important}'
		. '@media (max-width:768px){.wd-cookies-popup{padding:10px 14px!important}.wd-cookies-popup .wd-cookies-inner{gap:8px}.wd-cookies-popup .cookies-info-text{font-size:12.5px;line-height:1.45}.wd-cookies-popup .cookies-buttons{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0}.wd-cookies-popup .cookies-accept-btn{min-height:40px;padding:6px 18px}}'
		. '.lm-pickup-info{font-size:12.5px;line-height:1.4;color:var(--lm-tekst,#7a6250);margin:2px 0 0 26px}'
		. '.lm-preuzimanje{background:var(--lm-blijedo,#f6ded4);border-radius:12px;padding:18px 20px;margin:0 0 28px;color:#6d4e39}'
		. '.lm-preuzimanje h2{font-size:19px!important;margin:0 0 8px}.lm-preuzimanje p{margin:0 0 4px}'
		. '.lm-preuzimanje a{color:#8a4b47;font-weight:700}.lm-preuzimanje .lm-preuzimanje-mapa{display:inline-block;margin-top:8px}'
		. '</style>';
}, 37 );
