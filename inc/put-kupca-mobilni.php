<?php
/**
 * Lady Mom – put kupca na telefonu (Markova odluka 23.10.2026: „popravi, jako je bitno
 * da kupcima bude dobar journey“).
 *
 * Clarity (23.10., 609 posjeta, 58 % iz Instagram aplikacije): na stranicama kategorija
 * prvi ekran telefona su bili naslov (93 px), opis, putanja i broj rezultata, a ispod
 * njih traka za kolačiće (308 px). Proizvodi nisu bili vidljivi bez skrolovanja;
 * 36 % posjetilaca nije skrolovalo ni četvrtinu stranice.
 *
 * Na telefonu (do 768 px), na prodavnici, kategorijama i brendovima:
 * - manji naslov u jednoj traci;
 * - opis kategorije i putanja se ne prikazuju na vrhu (ostaju u HTML-u i u schema
 *   podacima, pa se ništa ne gubi za pretragu); broj rezultata se ne prikazuje.
 * Rezultat u probi (390×844): prvi red proizvoda sa cijenama je na prvom ekranu.
 * Povratak: ukloniti ovaj fajl iz liste u functions.php.
 *
 * data-no-optimize: LiteSpeed inače prebaci ovaj stil u CSS koji stiže kasnije (kao u inc/brzina.php),
 * pa bi se pravila primijenila tek posle učitavanja i stranica bi opet skočila.
 *
 * Traka za kolačiće je u inc/kolacici.php.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		if ( is_admin() || ! function_exists( 'is_woocommerce' ) ) {
			return;
		}
		if ( ! ( is_shop() || is_product_category() || is_tax( 'product_brand' ) ) ) {
			return;
		}
		?>
<style id="lm-put-kupca-mobilni" data-no-optimize="1">
@media (max-width:768.98px){
body .wd-page-title{padding-top:12px!important;padding-bottom:12px!important;min-height:0!important}
body .wd-page-title h1,body .wd-page-title .page-title,body .wd-page-title .entry-title{font-size:22px!important;line-height:1.25!important;margin:0!important}
body .term-description{display:none!important}
body .shop-loop-head .wd-breadcrumbs,body .woocommerce-result-count{display:none!important}
body .shop-loop-head{margin-bottom:10px!important;padding-top:0!important}
}
/* Pomjeranje stranice (CLS 0,89 na telefonu, PageSpeed 23.10.): LiteSpeed puni CSS učitava
   naknadno, pa je bočna traka sa filterima prvo stajala iznad proizvoda, a onda je WoodMart
   skloni u bočni panel i sve skoči nagore. Ovdje je od početka u istom stanju u kojem je
   WoodMart drži (sakrivena lijevo); kad se filteri otvore (wd-opened), važi WoodMart-ov stil. */
@media (max-width:1024px){
main.wd-sidebar-hidden-md-sm>aside.wd-sidebar:not(.wd-opened){position:fixed;top:0;left:0;bottom:0;width:300px;transform:translateX(-100%);z-index:500}
}
@media (max-width:768.98px){
main.wd-sidebar-hidden-sm>aside.wd-sidebar:not(.wd-opened){position:fixed;top:0;left:0;bottom:0;width:300px;transform:translateX(-100%);z-index:500}
}
</style>
		<?php
	},
	1
);
