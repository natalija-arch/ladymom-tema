<?php
/**
 * Lady Mom – tabovi sa artiklima na početnoj („Najprodavanije“, „Na akciji“…) se
 * učitavaju u pozadini (Markova odluka 18.10.2026).
 *
 * WoodMart prvi tab šalje sa stranicom, a ostale traži od servera tek na klik
 * (admin-ajax, oko 1,3 s, bez keša). Ova skripta te iste zahtjeve pošalje tiho,
 * kad se stranica već učitala, pa je klik trenutan.
 *
 * Sadržaj je uvijek svjež: server ga pravi pri svakom otvaranju početne, kao i do
 * sada na klik – novi artikli na akciji se pojavljuju odmah. Ako zahtjev u pozadini
 * ne uspije, klik radi kao ranije.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
	if ( ! is_front_page() ) {
		return;
	}
	?>
<script id="lm-tabovi-unaprijed">
(function(){
  function kreni(){
    var $ = window.jQuery;
    if (!$ || !window.woodmart_settings || $.ajax.lmTabovi) return;
    var spremno = {}; // ključ -> jQuery Deferred sa odgovorom
    var orig = $.ajax;
    function kljuc(atts){ try { return JSON.stringify(atts); } catch(e){ return ''; } }

    // WoodMart na klik zove $.ajax sa action=woodmart_get_products_tab_shortcode;
    // ako je odgovor već stigao (ili stiže), predaje se on umjesto novog zahtjeva.
    $.ajax = function(url, opt){
      var o = typeof url === 'object' ? url : (opt || {});
      var d = o && o.data;
      if (d && d.action === 'woodmart_get_products_tab_shortcode') {
        var p = spremno[kljuc(d.atts)];
        if (p && p.state() !== 'rejected') {
          var x = $.Deferred();
          p.done(function(data){
            if (o.success) o.success(data);
            if (o.complete) o.complete();
            x.resolve(data);
          }).fail(function(){
            delete spremno[kljuc(d.atts)];
            orig.call($, o).done(x.resolve).fail(x.reject);
          });
          return x.promise();
        }
      }
      return orig.apply($, arguments);
    };
    $.ajax.lmTabovi = true;

    function ucitaj(){
      $('.wd-products-tabs .products-tabs-title li').not('.wd-active').each(function(){
        var atts = $(this).data('atts');
        if (!atts) return;
        var k = kljuc(atts);
        if (spremno[k]) return;
        spremno[k] = orig.call($, {
          url: woodmart_settings.ajaxurl,
          data: { atts: atts, action: 'woodmart_get_products_tab_shortcode' },
          dataType: 'json',
          method: 'POST'
        });
      });
    }
    // Poslije učitavanja stranice, kad pregledač nema preča posla.
    if (window.requestIdleCallback) requestIdleCallback(ucitaj, { timeout: 2500 });
    else setTimeout(ucitaj, 1200);
  }
  if (document.readyState === 'complete') kreni();
  else window.addEventListener('load', kreni);
})();
</script>
	<?php
}, 99 );
