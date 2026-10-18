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
<style>form.variations_form.lm-sve-jedna .reset_variations{display:none!important}
.variations .lm-info-red td.value>*:not(.lm-jedina){display:none!important}
.variations .lm-info-red .wd-attr-selected{display:none!important}
.lm-jedina{display:inline-block;font-size:15px;color:var(--lm-tekst,#7a6250);font-weight:500;padding:2px 0}
</style>
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
      // Samo informacija: umjesto izbora se ispiše vrijednost („Boja: Zelena“).
      var polje = s.closest('td') || s.parentElement;
      if (polje && !polje.querySelector('.lm-jedina')) {
        var t = document.createElement('span');
        t.className = 'lm-jedina';
        t.textContent = opcije[0].textContent.trim();
        polje.appendChild(t);
        if (red) red.classList.add('lm-info-red');
      }
    });
    // Kad nema šta da se bira, link „Očisti“ nema smisla.
    if (svi.length && jednih === svi.length) forma.classList.add('lm-sve-jedna');
  }
  function sve(){ document.querySelectorAll('form.variations_form').forEach(function(f){ setTimeout(function(){ izaberi(f); }, 60); }); }
  var vezano = false;
  function veze(){
  if (vezano || !window.jQuery) return; vezano = true;
  {
    jQuery(document).on('wc_variation_form', 'form.variations_form', function(){ var f = this; setTimeout(function(){ izaberi(f); }, 60); });
    jQuery(document).on('woodmart-quick-view-displayed wdQuickShopSuccess', sve);
  }
  }
  veze();
  document.addEventListener('DOMContentLoaded', veze);
  window.addEventListener('load', function(){ veze(); sve(); });
  if (document.readyState === 'complete') sve();

  // Traka na dnu ekrana (WoodMart sticky): kad je varijanta izabrana, dugme dodaje u korpu
  // umjesto „Izaberi opcije“ (koje samo skroluje do izbora).
  var vezanoT = false;
  function trakaVeze(){
    if (vezanoT || !window.jQuery) return; vezanoT = true;
    var $ = jQuery;
    function dugme(){ return $('.wd-sticky-btn .wd-sticky-add-to-cart'); }
    $(document).on('found_variation', 'form.variations_form.cart', function(e, v){
      var b = dugme(); if (!b.length) return;
      if (!b.data('lmTekst')) b.data('lmTekst', $.trim(b.text()));
      var moze = v && v.is_purchasable && v.is_in_stock;
      b.text(moze ? 'Dodaj u korpu' : b.data('lmTekst')).toggleClass('lm-dodaj', !!moze);
    });
    $(document).on('reset_data', 'form.variations_form.cart', function(){
      var b = dugme(); if (b.data('lmTekst')) b.text(b.data('lmTekst')); b.removeClass('lm-dodaj');
    });
    $(document).on('click', '.wd-sticky-btn .wd-sticky-add-to-cart.lm-dodaj', function(e){
      var glavno = $('form.variations_form.cart .single_add_to_cart_button').first();
      if (!glavno.length || glavno.hasClass('disabled')) return;
      e.preventDefault(); e.stopImmediatePropagation();
      glavno.trigger('click');
    });
    // Ako je varijanta već izabrana prije nego što su se veze postavile.
    $('form.variations_form.cart').each(function(){ var id = parseInt($(this).find('input.variation_id').val(), 10); if (id) $(this).trigger('check_variations'); });
  }
  trakaVeze();
  document.addEventListener('DOMContentLoaded', trakaVeze);
  window.addEventListener('load', trakaVeze);
})();
</script>
	<?php
}, 99 );
