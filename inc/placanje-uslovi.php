<?php
/**
 * Lady Mom – plaćanje: kad kupac ne označi uslove, vodi ga do polja (Marko, 23.10.2026).
 *
 * WooCommerce poslije neuspjelog slanja porudžbine skroluje na vrh stranice, do liste
 * grešaka. Kad je jedina greška neoznačeno polje „Pročitao/la sam uslove“, kupac je
 * morao sam da traži polje na dnu. Sada se stranica zaustavi kod tog polja, polje se
 * istakne i ispod njega se ispiše kratka poruka; poruka nestaje kad se polje označi.
 * Ako ima i drugih grešaka (npr. prazno polje adrese), ostaje WooCommerce ponašanje.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() || is_checkout_pay_page() ) {
		return;
	}
	?>
<style id="lm-uslovi-css">
.lm-uslovi-greska{outline:2px solid #c0392b;outline-offset:6px;border-radius:4px}
.lm-uslovi-poruka{display:block;color:#c0392b;font-size:13px;margin:8px 0 0}
</style>
<script id="lm-uslovi-js">
(function () {
	function kadJQuery(fn) { if (window.jQuery) { jQuery(fn); } else { setTimeout(function () { kadJQuery(fn); }, 50); } }
	kadJQuery(function ($) {
		function ocisti() {
			$('.lm-uslovi-greska').removeClass('lm-uslovi-greska');
			$('.lm-uslovi-poruka').remove();
		}
		$(document.body).on('checkout_error', function () {
			var $polje = $('#terms');
			if (!$polje.length || $polje.is(':checked')) { return; }
			var $greske = $('.woocommerce-NoticeGroup-checkout .woocommerce-error li, .woocommerce-notices-wrapper .woocommerce-error li');
			var samoUslovi = $greske.length === 0 || $greske.filter(function () {
				return $(this).attr('data-id') !== 'terms';
			}).length === 0;
			if (!samoUslovi) { return; }

			var $red = $polje.closest('.form-row');
			if (!$red.length) { $red = $polje.closest('.woocommerce-terms-and-conditions-wrapper'); }
			ocisti();
			$red.addClass('lm-uslovi-greska');
			$red.append('<span class="lm-uslovi-poruka" role="alert">Označite da ste pročitali i prihvatate uslove korišćenja, pa kliknite „Poruči“.</span>');
			$('.woocommerce-NoticeGroup-checkout').hide();

			// Zaustavi WooCommerce skrol na vrh i idi do polja (sa malo prostora iznad).
			setTimeout(function () {
				$('html, body').stop(true);
				var vrh = $red.offset().top - Math.round(window.innerHeight * 0.35);
				$('html, body').animate({ scrollTop: Math.max(0, vrh) }, 400);
				try { $polje[0].focus({ preventScroll: true }); } catch (e) { $polje.trigger('focus'); }
			}, 20);
		});
		$(document.body).on('change', '#terms', function () { if (this.checked) { ocisti(); } });
	});
})();
</script>
	<?php
}, 45 );
