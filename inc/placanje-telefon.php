<?php
/**
 * Lady Mom – kraće plaćanje na telefonu (Markovo odobrenje 20.10.2026, test kupovine).
 *
 * Na telefonu je plaćanje bilo ~5 ekrana, a prvo polje je počinjalo na ~720 px.
 * - Vrh: „Već imate nalog?“ i „Imate kupon?“ u jednom redu, kraći tekstovi.
 * - Traka do besplatne dostave, polja, dostava i plaćanje zbijeniji.
 * - Napomena uz porudžbinu se otvara linkom (polje je bilo ~260 px i rijetko se koristi).
 * - Kad su u korpi 2 ili više artikala, lista artikala u pregledu je sklopljena („Artikli (N)“).
 * - Ispod dugmeta „Poruči“ nema trake prednosti ni velikog footera, samo copyright.
 * - Mini korpa (svuda): „Plaćanje“ je glavno dugme, „Prikaži korpu“ sporedno.
 * Sve je CSS i mali JS; polja i redoslijed WooCommerce-a se ne mijenjaju.
 */

defined( 'ABSPATH' ) || exit;

function lm_je_placanje_forma() {
	return function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() && ! is_checkout_pay_page();
}

add_filter( 'gettext_woocommerce', function ( $prevod, $tekst ) {
	if ( 'Click here to enter your code' === $tekst ) {
		return 'Unesite kod';
	}
	return $prevod;
}, 40, 2 );

// Mini korpa: glavno dugme je „Plaćanje“.
add_action( 'wp_head', function () {
	echo '<style id="lm-mini-korpa">.woocommerce-mini-cart__buttons .button.checkout{background:var(--lm-roze-jaka,#a05c57)!important;color:#fff!important;border-color:var(--lm-roze-jaka,#a05c57)!important}.woocommerce-mini-cart__buttons .button.checkout:hover{filter:brightness(.94)}.woocommerce-mini-cart__buttons .button:not(.checkout){background:transparent!important;color:var(--lm-smedja,#6d4e39)!important;box-shadow:inset 0 0 0 1px #e8d8cd!important}.woocommerce-mini-cart__buttons .button:not(.checkout):hover{background:var(--lm-krem,#fdf2ec)!important}</style>';
}, 41 );

/*
 * Mini korpa, cijena artikla (21.10.2026, Marko: „loše izgleda ova cijena“).
 * WooCommerce piše „3 × 19,00 €“ pored polja za količinu, pa se količina vidi dvaput,
 * a opšte pravilo za okvir količine (`inc/dugmad-stil.php`) je i taj tekst stavljalo u okvir.
 * Sada: polje za količinu i desno ukupno za artikal u istom redu, a ispod sitno „po 19,00 €“
 * kad je više od jednog komada. Na uskom telefonu cijena pređe u red ispod, poravnata desno.
 */
add_filter( 'woocommerce_widget_cart_item_quantity', function ( $html, $stavka ) {
	if ( empty( $stavka['data'] ) || ! is_a( $stavka['data'], 'WC_Product' ) || ! WC()->cart ) {
		return $html;
	}
	$kol = (int) $stavka['quantity'];
	$out = '<span class="quantity lm-mk-cijena"><span class="lm-mk-ukupno">' . WC()->cart->get_product_subtotal( $stavka['data'], $kol ) . '</span>';
	if ( $kol > 1 ) {
		$out .= '<span class="lm-mk-kom">po ' . WC()->cart->get_product_price( $stavka['data'] ) . '</span>';
	}
	return $out . '</span>';
}, 20, 2 );

add_action( 'wp_head', function () {
	echo '<style id="lm-mini-korpa-cijena">'
		. '.woocommerce-mini-cart-item .cart-info{display:flex;flex-wrap:wrap;align-items:center;column-gap:8px;row-gap:8px}'
		. '.woocommerce-mini-cart-item .cart-info>*{flex:0 0 100%;margin-bottom:0}'
		. '.woocommerce-mini-cart-item .cart-info>div.quantity{flex:0 0 auto;height:34px;margin:0!important}'
		. '.woocommerce-mini-cart-item div.quantity .minus:not(.btn-style-bordered):not(.btn-style-link),.woocommerce-mini-cart-item div.quantity .plus:not(.btn-style-bordered):not(.btn-style-link){width:30px!important;min-width:30px!important}'
		. '.woocommerce-mini-cart-item div.quantity input.qty{width:32px!important}'
		. '.woocommerce-mini-cart-item .cart-info>span.lm-mk-cijena{flex:1 1 auto;display:flex!important;flex-direction:column;align-items:flex-end;justify-content:center;gap:2px;margin:0 0 0 auto!important;padding:0!important;border:0!important;background:none!important;line-height:1.2;text-align:right;white-space:nowrap}'
		. '.lm-mk-ukupno,.lm-mk-ukupno .amount{font-size:15px;font-weight:700;color:var(--lm-smedja,#6d4e39)!important}'
		. '.lm-mk-kom,.lm-mk-kom .amount{font-size:12px;font-weight:400;color:#9b8576!important}'
		. '</style>';
}, 42 );

add_action( 'wp_head', function () {
	if ( ! lm_je_placanje_forma() ) {
		return;
	}
	?>
<style id="lm-placanje-telefon">
.woocommerce-additional-fields.lm-napomena-skrivena h3,.woocommerce-additional-fields.lm-napomena-skrivena .woocommerce-additional-fields__field-wrapper{display:none}
.lm-napomena-link{display:inline-block;margin:4px 0 6px;font-size:14px;font-weight:600;color:var(--lm-smedja,#6d4e39)!important;text-decoration:underline;text-underline-offset:3px}
.lm-artikli-red td{padding:10px 0!important}
.woocommerce-checkout-review-order-table .lm-artikli-dugme{display:flex!important;width:100%!important;min-height:0!important;align-items:center;justify-content:space-between;gap:10px;background:var(--lm-krem,#fdf2ec)!important;border:0!important;border-radius:8px!important;padding:10px 12px!important;font-size:14px!important;font-weight:600!important;text-transform:none!important;letter-spacing:0!important;color:var(--lm-smedja,#6d4e39)!important;box-shadow:none!important;cursor:pointer}
.lm-artikli-dugme:after{content:"";width:8px;height:8px;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:rotate(45deg);margin-top:-4px;transition:transform .15s}
.lm-artikli-otvoreno .lm-artikli-dugme:after{transform:rotate(-135deg);margin-top:4px}
@media (max-width:768px){
	.woocommerce-checkout .woocommerce-notices-wrapper .woocommerce-message{padding:10px 12px!important;font-size:13px;margin-bottom:12px!important}
	.lm-brza-kupovina{margin-bottom:12px!important}
	.lm-brza-kupovina .lm-google-dugme{min-height:44px}
	.lm-brza-kupovina .lm-brza-ili{margin:10px 0 6px!important}
	.lm-brza-kupovina .lm-nalog-napomena{font-size:12px!important;line-height:1.4!important}
	body .woocommerce-form-login-toggle,body .woocommerce-form-coupon-toggle{float:left;width:calc(50% - 6px)}
	body .woocommerce-form-login-toggle{margin-right:12px}
	body.logged-in .woocommerce-form-coupon-toggle{float:none;width:100%}
	.woocommerce-form-coupon-toggle~form,.woocommerce-form-coupon-toggle~div{clear:both}
	body .woocommerce-form-login-toggle>.woocommerce-info,body .woocommerce-form-coupon-toggle>.woocommerce-info{display:block!important;margin-bottom:14px!important;font-size:13px!important;line-height:1.4!important}
	body .woocommerce-form-login-toggle>.woocommerce-info a,body .woocommerce-form-coupon-toggle>.woocommerce-info a{display:block}
	body.logged-in .woocommerce-form-coupon-toggle>.woocommerce-info a{display:inline}
	body.woocommerce-checkout .wd-shipping-progress-bar{padding:0!important;margin-bottom:14px!important}
	body.woocommerce-checkout .wd-shipping-progress-bar .wd-progress-bar{padding:10px 12px!important}
	body.woocommerce-checkout .wd-shipping-progress-bar p{font-size:13px!important;line-height:1.4!important;margin-bottom:6px!important}
	body.woocommerce-checkout .wd-shipping-progress-bar .amount{font-size:14px!important}
	.woocommerce-billing-fields h3,#order_review_heading{margin-bottom:10px!important;font-size:17px!important}
	.woocommerce-billing-fields .form-row{margin-bottom:12px!important}
	.woocommerce-billing-fields .form-row label{margin-bottom:4px!important}
	#_wd_recover_guest_cart_consent_field,#_wd_recover_guest_cart_consent_field label{font-size:12.5px!important;line-height:1.4!important}
	.woocommerce-additional-fields{margin-bottom:0!important}
	.checkout-order-review{margin-top:14px!important;padding-top:14px!important}
	.woocommerce-checkout-review-order-table thead{display:none}
	.woocommerce-checkout-review-order-table th,.woocommerce-checkout-review-order-table td{padding-top:9px!important;padding-bottom:9px!important}
	.woocommerce-checkout-review-order-table #shipping_method li{margin-bottom:6px!important}
	.woocommerce-checkout-review-order-table .lm-pickup-info{font-size:12px}
	#payment .payment_box{font-size:12.5px!important;padding:10px 12px!important}
	.woocommerce-privacy-policy-text,.woocommerce-privacy-policy-text p{font-size:12px!important;line-height:1.45!important;margin-bottom:8px!important}
	.lm-secure-pay{font-size:12px!important;margin:8px 0!important}
	body.woocommerce-checkout .wd-prefooter,body.woocommerce-checkout .wd-footer .main-footer{display:none!important}
	body.woocommerce-checkout .wd-copyrights{padding-top:14px!important;padding-bottom:14px!important}
}
</style>
	<?php
}, 42 );

add_action( 'wp_footer', function () {
	if ( ! lm_je_placanje_forma() ) {
		return;
	}
	?>
<script id="lm-placanje-telefon-js">
(function () {
	function kadJQuery(fn) { if (window.jQuery) { jQuery(fn); } else { setTimeout(function () { kadJQuery(fn); }, 50); } }
	kadJQuery(function ($) {
		// Napomena uz porudžbinu: otvara se linkom, osim ako je već upisana.
		var $dodatno = $('.woocommerce-additional-fields');
		if ($dodatno.length && !$.trim($('#order_comments').val() || '')) {
			$dodatno.addClass('lm-napomena-skrivena');
			var $link = $('<a href="#" class="lm-napomena-link">+ Dodaj napomenu uz porudžbinu</a>');
			$dodatno.prepend($link);
			$link.on('click', function (e) {
				e.preventDefault();
				$dodatno.removeClass('lm-napomena-skrivena');
				$link.remove();
				$('#order_comments').trigger('focus');
			});
		}

		// Lista artikala u pregledu: sklopljena na telefonu kad ima 2 ili više artikala.
		var otvoreno = false, mq = window.matchMedia('(max-width: 768px)');
		function artikli() {
			var $t = $('.woocommerce-checkout-review-order-table'), $redovi = $t.find('tr.cart_item');
			$t.find('tr.lm-artikli-red').remove();
			$redovi.show();
			$t.removeClass('lm-artikli-otvoreno');
			if (!mq.matches || $redovi.length < 2) { return; }
			var n = 0;
			$redovi.each(function () { n += parseInt($(this).find('input.qty').val() || $(this).find('.product-quantity').text().replace(/\D/g, '') || 1, 10) || 1; });
			var $red = $('<tr class="lm-artikli-red"><td colspan="2"><button type="button" class="lm-artikli-dugme" aria-expanded="false"><span>Artikli (' + n + ')</span></button></td></tr>');
			$redovi.first().before($red);
			function primijeni() {
				$redovi.toggle(otvoreno);
				$t.toggleClass('lm-artikli-otvoreno', otvoreno);
				$red.find('button').attr('aria-expanded', otvoreno ? 'true' : 'false').find('span').text('Artikli (' + n + ')' + (otvoreno ? '' : ' · prikaži'));
			}
			$red.on('click', 'button', function () { otvoreno = !otvoreno; primijeni(); });
			primijeni();
		}
		$(document.body).on('updated_checkout', artikli);
		if (mq.addEventListener) { mq.addEventListener('change', artikli); }
		artikli();
	});
})();
</script>
	<?php
}, 40 );
