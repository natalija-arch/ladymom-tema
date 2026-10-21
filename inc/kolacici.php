<?php
/**
 * Izgled trake za kolačiće (Complianz) i polja za promjenu izbora na Politici privatnosti.
 *
 * Globalni stil dugmadi sajta (pozadina #a05c57, zaobljeno) hvata i Complianz-ovo dugme
 * za otvaranje opisa kategorije, a polje [cmplz-manage-consent] nema Complianz-ov stil
 * prekidača, pa su se vidjeli obični plavi checkboxovi. Ovdje ih uklapamo u boje sajta.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		if ( is_admin() || ! defined( 'CMPLZ_VERSION' ) ) {
			return;
		}
		?>
<style id="lm-kolacici">
/* Kategorije (traka i stranica) */
.cmplz-cookiebanner .cmplz-categories .cmplz-category,.cmplz-manage-consent-container .cmplz-category{background:var(--lm-krem,#fdf2ec)!important;border:1px solid var(--lm-linija,#e7d7cc);border-radius:10px!important;overflow:hidden}
.cmplz-cookiebanner .cmplz-categories .cmplz-category .cmplz-category-header,.cmplz-manage-consent-container .cmplz-category .cmplz-category-header{background:transparent!important;padding:12px 14px!important;align-items:center}
.cmplz-cookiebanner .cmplz-category-title,.cmplz-manage-consent-container .cmplz-category-title{font-family:"Raleway",system-ui,sans-serif;font-weight:600!important;font-size:15px!important;color:var(--lm-smedja,#6d4e39)!important}
.cmplz-cookiebanner .cmplz-always-active,.cmplz-manage-consent-container .cmplz-always-active{color:var(--lm-tirkiz-gore,#4a7a7d)!important;font-size:13px!important;font-weight:600!important}
.cmplz-cookiebanner .cmplz-description,.cmplz-manage-consent-container .cmplz-description{background:transparent!important;padding:0 14px 14px!important;font-size:14px!important;line-height:1.6!important;color:var(--lm-tekst,#7a6250)!important}

/* Dugme za opis: samo strelica, bez stila dugmadi sajta */
.cmplz-cookiebanner button.cmplz-category-toggle,.cmplz-manage-consent-container button.cmplz-category-toggle{background:transparent!important;border:0!important;box-shadow:none!important;padding:4px!important;margin:0 0 0 6px!important;min-height:0!important;height:auto!important;width:auto!important;border-radius:0!important;color:var(--lm-smedja,#6d4e39)!important;line-height:1!important}
.cmplz-cookiebanner button.cmplz-category-toggle:hover,.cmplz-manage-consent-container button.cmplz-category-toggle:hover{background:transparent!important;opacity:.7}
.cmplz-cookiebanner button.cmplz-category-toggle svg,.cmplz-manage-consent-container button.cmplz-category-toggle svg{fill:currentColor!important;width:14px;height:14px}

/* Prekidač umjesto običnog checkboxa na stranici (u traci ga Complianz već crta) */
.cmplz-manage-consent-container .cmplz-banner-checkbox{display:inline-flex;align-items:center}
.cmplz-manage-consent-container input.cmplz-consent-checkbox{-webkit-appearance:none;appearance:none;position:relative;width:40px!important;height:22px!important;margin:0!important;border:0!important;border-radius:11px!important;background:#c9bdb4!important;cursor:pointer;transition:background .2s ease;accent-color:auto;vertical-align:middle}
.cmplz-manage-consent-container input.cmplz-consent-checkbox::before{content:"";position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:transform .2s ease}
.cmplz-manage-consent-container input.cmplz-consent-checkbox:checked{background:var(--lm-tirkiz-gore,#4a7a7d)!important}
.cmplz-manage-consent-container input.cmplz-consent-checkbox:checked::before{transform:translateX(18px)}
.cmplz-manage-consent-container input.cmplz-consent-checkbox:focus-visible{outline:2px solid var(--lm-smedja,#6d4e39);outline-offset:2px}
.cmplz-manage-consent-container .cmplz-always-active .cmplz-banner-checkbox{display:none}
.cmplz-manage-consent-container .cmplz-label{display:none!important}
</style>
		<?php
	},
	100
);
