<?php
/**
 * Izgled filtera u prodavnici (bočna traka na desktopu, panel filtera na telefonu).
 *
 * - Boja: jedna kolona; svaka boja ima kružić. Boje iz ERP-a koje nemaju svoju boju u
 *   WoodMart-u (dvobojne i šare: „braon-crna“, „leopard“...) dobijaju kružić ovdje, po
 *   nazivu. Boja „Default“ (greška iz ERP-a) se u filteru ne prikazuje.
 * - Brend: veći logotipi, svaki u svojoj kartici, dvije kolone.
 *
 * Widgeti: woodmart-woocommerce-layered-nav-7 (Boja), -8 (Brend).
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Kružići za boje bez sopstvene boje (ključ = naziv kako ga šalje ERP).
 */
function lm_filter_boje_bez_swatcha() {
	$dvije = function ( $a, $b ) {
		return 'linear-gradient(135deg,' . $a . ' 50%,' . $b . ' 50%)';
	};
	return array(
		'bijelo-crvena' => $dvije( '#ffffff', '#c0392b' ),
		'braon-crna'    => $dvije( '#7a5230', '#1d1d1d' ),
		'crno-bijela'   => $dvije( '#1d1d1d', '#ffffff' ),
		'crnobijela'    => $dvije( '#1d1d1d', '#ffffff' ),
		'crno-siva'     => $dvije( '#1d1d1d', '#9b9b9b' ),
		'plavo-bijela'  => $dvije( '#3b7fc4', '#ffffff' ),
		'plavobijela'   => $dvije( '#3b7fc4', '#ffffff' ),
		'sivo-bijela'   => $dvije( '#9b9b9b', '#ffffff' ),
		'sivo-roza'     => $dvije( '#9b9b9b', '#e8a0a8' ),
		'teget-bijela'  => $dvije( '#1f3864', '#ffffff' ),
		'teget-siva'    => $dvije( '#1f3864', '#9b9b9b' ),
		'cvjetna'       => 'conic-gradient(#e8a0a8 0 25%,#a8dcc8 0 50%,#f3d27a 0 75%,#ffffff 0)',
		'leopard'       => 'radial-gradient(circle at 30% 35%,#3b2a1a 0 14%,transparent 15%),radial-gradient(circle at 68% 62%,#3b2a1a 0 14%,transparent 15%),radial-gradient(circle at 70% 25%,#3b2a1a 0 9%,transparent 10%),#c9995e',
		'tigar'         => 'repeating-linear-gradient(135deg,#d9822b 0 5px,#1d1d1d 5px 7px)',
		'lisica-sova'   => 'conic-gradient(#d9822b 0 50%,#f3e6d6 0)',
		'misici-koale'  => 'conic-gradient(#9b9b9b 0 50%,#f3e6d6 0)',
	);
}

add_action(
	'wp_head',
	function () {
		if ( is_admin() || ! ( is_shop() || is_product_taxonomy() ) ) {
			return;
		}
		$boja = '#woodmart-woocommerce-layered-nav-7';
		$css  = '';
		foreach ( lm_filter_boje_bez_swatcha() as $naziv => $pozadina ) {
			$css .= $boja . ' li:not(.wd-swatch-wrap) a.layered-nav-link[aria-label="Filtriraj: ' . esc_attr( $naziv ) . '"]::before{background:' . $pozadina . '}';
		}
		?>
<style id="lm-filteri">
/* Boja: jedna kolona, kružić + naziv + broj desno */
#woodmart-woocommerce-layered-nav-7 .wd-swatches-filter{flex-direction:column!important;flex-wrap:nowrap!important;gap:2px!important}
#woodmart-woocommerce-layered-nav-7 .wd-swatches-filter>li{width:100%!important;max-width:100%!important;flex:0 0 auto!important;margin:0!important;padding:5px 0!important;align-items:center}
#woodmart-woocommerce-layered-nav-7 .layered-nav-link{flex:1 1 auto!important;width:auto!important;min-width:0;display:flex!important;align-items:center;gap:10px}
#woodmart-woocommerce-layered-nav-7 .wd-swatch{width:22px!important;height:22px!important;min-width:22px;flex:0 0 22px!important;margin:0!important;border-radius:50%;box-shadow:inset 0 0 0 1px rgba(109,78,57,.18)}
#woodmart-woocommerce-layered-nav-7 .wd-swatch-bg{border-radius:50%}
#woodmart-woocommerce-layered-nav-7 .layer-term-lable{margin:0!important;padding:0!important;font-size:15px;line-height:1.3;color:var(--lm-smedja,#6d4e39);white-space:normal}
#woodmart-woocommerce-layered-nav-7 li:not(.wd-swatch-wrap) .layered-nav-link::before{content:"";flex:0 0 22px;width:22px;height:22px;border-radius:50%;background:#f3e6d6;box-shadow:inset 0 0 0 1px rgba(109,78,57,.25)}
#woodmart-woocommerce-layered-nav-7 .count{margin-left:auto!important}
#woodmart-woocommerce-layered-nav-7 li.chosen .layer-term-lable,#woodmart-woocommerce-layered-nav-7 li.wd-active .layer-term-lable{font-weight:700}
#woodmart-woocommerce-layered-nav-7 a[aria-label="Filtriraj: default"],#woodmart-woocommerce-layered-nav-7 li:has(> a[aria-label="Filtriraj: default"]){display:none!important}
<?php echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- statične vrijednosti iz koda. ?>

/* Brend: kartice sa većim logotipom, dvije kolone */
#woodmart-woocommerce-layered-nav-8 .wd-swatches-filter{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px!important}
#woodmart-woocommerce-layered-nav-8 .wd-swatches-filter>li{width:auto!important;max-width:none!important;margin:0!important;padding:0!important;position:relative}
#woodmart-woocommerce-layered-nav-8 .layered-nav-link{display:block!important;width:100%!important}
#woodmart-woocommerce-layered-nav-8 .wd-swatch{display:flex!important;align-items:center;justify-content:center;width:100%!important;max-width:none!important;min-width:0!important;height:58px!important;margin:0!important;padding:8px 10px;background:#fff;border:1px solid var(--lm-linija,#e7d7cc);border-radius:10px;transition:border-color .2s ease,box-shadow .2s ease}
#woodmart-woocommerce-layered-nav-8 .wd-swatch img{width:auto!important;height:auto!important;max-width:100%!important;max-height:40px!important;object-fit:contain}
#woodmart-woocommerce-layered-nav-8 .layered-nav-link:hover .wd-swatch{border-color:var(--lm-roze,#db9e99)}
/* Izabrani brend (23.10.): WoodMart je izabranu karticu blijedio (opacity .5) i dodavao dvostruki prsten; sada tanak okvir, puna boja logotipa i „×“ za uklanjanje */
#woodmart-woocommerce-layered-nav-8 li.chosen .wd-swatch,#woodmart-woocommerce-layered-nav-8 li.wd-active .wd-swatch{border:1px solid var(--lm-roze-jaka,#a05c57)!important;background:#fff!important;box-shadow:none!important;outline:0!important;opacity:1!important}
#woodmart-woocommerce-layered-nav-8 li.chosen .wd-swatch:before,#woodmart-woocommerce-layered-nav-8 li.wd-active .wd-swatch:before{display:none!important}
#woodmart-woocommerce-layered-nav-8 li.chosen .wd-swatch:after,#woodmart-woocommerce-layered-nav-8 li.wd-active .wd-swatch:after{content:"\00d7"!important;display:flex!important;align-items:center;justify-content:center;position:absolute;top:5px;right:5px;left:auto;bottom:auto;width:18px;height:18px;border-radius:50%;background:var(--lm-roze-jaka,#a05c57);color:#fff;font-size:14px;line-height:1;font-family:inherit;opacity:1!important;transform:none!important;box-shadow:none!important;border:0!important}
#woodmart-woocommerce-layered-nav-8 li.chosen .wd-swatch-bg,#woodmart-woocommerce-layered-nav-8 li.wd-active .wd-swatch-bg{background:transparent!important;box-shadow:none!important}
#woodmart-woocommerce-layered-nav-8 .wd-swatch{position:relative}
#woodmart-woocommerce-layered-nav-8 li.chosen .count,#woodmart-woocommerce-layered-nav-8 li.wd-active .count{display:none!important}
#woodmart-woocommerce-layered-nav-8 .count{position:absolute;top:4px;right:6px;font-size:11px}

/* Na telefonu (panel filtera) malo veći razmaci za prst */
@media (max-width:1024px){
	#woodmart-woocommerce-layered-nav-7 .wd-swatches-filter>li{padding:7px 0!important}
	#woodmart-woocommerce-layered-nav-8 .wd-swatch{height:62px!important}
}
</style>
		<?php
	},
	60
);
