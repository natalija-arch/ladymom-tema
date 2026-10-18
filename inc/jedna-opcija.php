<?php
/**
 * Lady Mom – kad artikal ima samo jednu boju (ili jednu veličinu), ta opcija je
 * izabrana sama, pa kupac ne mora da je klikne (Markova odluka 18.10.2026).
 * 18.10. to je 138 od 144 artikla sa varijantama na stanju; kod 33 su sve osobine
 * po jedna opcija, pa je dugme „Dodaj u korpu“ odmah aktivno.
 *
 * Samo prikaz u pregledaču: podaci iz ERP-a (varijante, podrazumijevane opcije) se ne diraju.
 * Radi na stranici proizvoda i u brzom pregledu (WoodMart quick view / quick shop).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
	?>
<style>form.variations_form.lm-sve-jedna .reset_variations{display:none!important}</style>
<script id="lm-jedna-opcija">
(function(){
  function izaberi(forma){
    if (!forma || forma.dataset.lmJedna) return;
    forma.dataset.lmJedna = '1';
    var svi = forma.querySelectorAll('select[name^="attribute_"]'), jednih = 0;
    forma.querySelectorAll('select[name^="attribute_"]').forEach(function(s){
      if (s.value) return;
      var opcije = Array.prototype.filter.call(s.options, function(o){ return o.value && !o.disabled; });
      if (opcije.length !== 1) return;
      jednih++;
      var red = s.closest('tr') || s.parentElement;
      var sw = red ? Array.prototype.filter.call(red.querySelectorAll('.wd-swatch'), function(x){ return !x.classList.contains('wd-disabled'); }) : [];
      if (sw.length === 1) { sw[0].click(); }
      else if (window.jQuery) { jQuery(s).val(opcije[0].value).trigger('change'); }
      else { s.value = opcije[0].value; s.dispatchEvent(new Event('change', { bubbles: true })); }
    });
    // Kad nema šta da se bira, link „Očisti“ nema smisla.
    if (svi.length && jednih === svi.length) forma.classList.add('lm-sve-jedna');
  }
  function sve(){ document.querySelectorAll('form.variations_form').forEach(function(f){ setTimeout(function(){ izaberi(f); }, 60); }); }
  if (window.jQuery) {
    jQuery(document).on('wc_variation_form', 'form.variations_form', function(){ var f = this; setTimeout(function(){ izaberi(f); }, 60); });
    jQuery(document).on('woodmart-quick-view-displayed wdQuickShopSuccess', sve);
  }
  if (document.readyState === 'complete') sve(); else window.addEventListener('load', sve);
})();
</script>
	<?php
}, 99 );
