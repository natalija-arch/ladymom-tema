<?php
/**
 * Lady Mom – vrijednost „Default“ (boja) se kupcu ne prikazuje (Markova odluka 18.10.2026).
 *
 * ERP za artikle bez varijanti šalje boju „Default“ (termin pa_boja „default“, 20 artikala,
 * 90 varijanti). Podatak ostaje kako ga ERP šalje; ovdje se samo skriva na prikazu:
 * tabela osobina, izbor na proizvodu, korpa, porudžbina i emailovi, filter u prodavnici,
 * Google feed. Ispravka u ERP-u je u emailu Nataliji (to-do k3).
 */

defined( 'ABSPATH' ) || exit;

/** Da li je vrijednost „Default“. */
// lm_je_default() je od 24.10.2026 u inc/zajednicko.php (koriste je i feed i SEO).

// Tabela „Dodatne informacije“.
add_filter( 'woocommerce_display_product_attributes', function ( $redovi ) {
	foreach ( $redovi as $k => $r ) {
		if ( isset( $r['value'] ) && lm_je_default( $r['value'] ) ) {
			unset( $redovi[ $k ] );
		}
	}
	return $redovi;
}, 20 );

// Korpa i plaćanje: „Boja: Default“.
add_filter( 'woocommerce_get_item_data', function ( $podaci ) {
	foreach ( (array) $podaci as $k => $p ) {
		if ( lm_je_default( $p['display'] ?? ( $p['value'] ?? '' ) ) ) {
			unset( $podaci[ $k ] );
		}
	}
	return $podaci;
}, 20 );

// Porudžbina, emailovi, Moj nalog.
add_filter( 'woocommerce_order_item_get_formatted_meta_data', function ( $meta ) {
	foreach ( (array) $meta as $k => $m ) {
		if ( isset( $m->display_value ) && lm_je_default( $m->display_value ) ) {
			unset( $meta[ $k ] );
		}
	}
	return $meta;
}, 20 );

// Naziv varijante „Artikal - Default“ / „Artikal - M, Default“.
function lm_bez_default_u_nazivu( $naziv ) {
	$naziv = preg_replace( '/,\s*Default\b/u', '', (string) $naziv );
	return preg_replace( '/\s+[-–]\s+Default\b/u', '', $naziv );
}
add_filter( 'woocommerce_cart_item_name', 'lm_bez_default_u_nazivu', 20 );
add_filter( 'woocommerce_order_item_name', 'lm_bez_default_u_nazivu', 20 );
add_filter( 'woocommerce_product_variation_title', function ( $t ) {
	return is_admin() && ! wp_doing_ajax() ? $t : lm_bez_default_u_nazivu( $t );
}, 20 );

// Filter boja u prodavnici i izbor na proizvodu (red sa jedinom opcijom „Default“).
add_action( 'wp_head', function () {
	?>
<style id="lm-bez-default">
li:has(> a[href*="filter_boja=default"]),li:has(> .wd-swatch[data-term-slug="default"]),.wd-swatch.default,.wd-swatch[data-value="default"]{display:none!important}
table.variations tr.lm-skriven-red{display:none!important}
</style>
<script>
(function(){
  function skrij(){
    document.querySelectorAll('form.variations_form select[name^="attribute_"]').forEach(function(s){
      var o = Array.prototype.filter.call(s.options, function(x){ return x.value; });
      if (o.length === 1 && o[0].value === 'default') { var r = s.closest('tr'); if (r) r.classList.add('lm-skriven-red'); }
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', skrij); else skrij();
  window.addEventListener('load', skrij);
})();
</script>
	<?php
}, 46 );
