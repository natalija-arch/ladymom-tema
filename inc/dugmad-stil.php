<?php
/**
 * Lady Mom – dugmad i sitni elementi po Lady Mom stilu (pregled telefona 20.10.2026).
 *
 * Opšte pravilo teme boji svako <button> kao glavnu akciju (puna roze). Zato su
 * „−“ i „+“ kod količine i dugme za prikaz lozinke izgledali kao glavna dugmad.
 * Ovdje ih vraćamo na hijerarhiju iz claude/brend-stil.md:
 * glavna akcija puna roze, sporedna roze okvir, link roze sa podvlakom, ostalo neutralno.
 *
 * - Količina: jedan okvir (−, broj, +), bijelo sa smeđim znakom, iste visine kao dugme.
 * - Na telefonu dugme „Dodaj u korpu“ zauzima ostatak reda pored količine.
 * - Prikaži lozinku: ikonica bez pozadine.
 * - Google dugme: ikonica u ravni sa tekstom (tema je pomjerala za −10 px).
 * - Donja traka na telefonu: puni nazivi („Prodavnica“ se sjekao na „Prodavni…“).
 * - Česta pitanja (Kontakt): Raleway umjesto Lato.
 * - Dijeljenje članka: roze ikonice umjesto boja mreža.
 * - „Pročitaj više“ na blogu: roze link sa podvlakom.
 * - Polja za unos: zaobljena 6 px kao dugmad; obavezno polje (*) roze; kvačice veće i roze.
 * - WhatsApp i Viber na proizvodu: roze okvir kao sporedna dugmad.
 * - Veličine u filteru: jednaka dugmad.
 * - Kartice proizvoda: naslov tačno dva reda, cijene u istoj ravni (uz WoodMart „Product title lines limit“ = dva reda).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	?>
<style id="lm-dugmad-stil">
/* Količina */
.quantity{display:inline-flex!important;align-items:stretch;border:1px solid #e8d8cd;border-radius:6px;overflow:hidden;background:#fff;vertical-align:middle}
/* Selektori su jači od opšteg pravila teme `.btn:not(.btn-style-bordered):not(.btn-style-link)` (!important). */
div.quantity .minus:not(.btn-style-bordered):not(.btn-style-link),div.quantity .plus:not(.btn-style-bordered):not(.btn-style-link){background:#fff!important;color:var(--lm-smedja,#6d4e39)!important;border:0!important;box-shadow:none!important;border-radius:0!important;min-width:34px!important;width:34px!important;height:auto!important;min-height:0!important;padding:0!important;font-size:17px!important;font-weight:500!important;line-height:1!important;text-transform:none!important;letter-spacing:0!important}
div.quantity .minus:not(.btn-style-bordered):not(.btn-style-link):hover,div.quantity .plus:not(.btn-style-bordered):not(.btn-style-link):hover{background:var(--lm-krem,#fdf2ec)!important}
.quantity input.qty{border:0!important;border-left:1px solid #f0e4dc!important;border-right:1px solid #f0e4dc!important;border-radius:0!important;box-shadow:none!important;width:38px!important;min-width:0!important;padding:0 2px!important;color:var(--lm-smedja,#6d4e39)!important;font-weight:600;background:#fff!important}
.summary form.cart .quantity,.summary .woocommerce-variation-add-to-cart .quantity{height:42px}
@media (max-width:768px){
	.summary form.cart .single_add_to_cart_button,.summary .woocommerce-variation-add-to-cart .single_add_to_cart_button{flex:1 1 auto;width:auto!important}
	.summary .woocommerce-variation-add-to-cart{display:flex!important;gap:10px;align-items:stretch}
}
/* Prikaži lozinku */
button.show-password-input:not(.btn-style-bordered):not(.wd-tools-icon){background:transparent!important;color:var(--lm-smedja,#6d4e39)!important;border:0!important;box-shadow:none!important}
button.show-password-input:not(.btn-style-bordered):not(.wd-tools-icon):hover{background:var(--lm-krem,#fdf2ec)!important}
/* Google dugme */
.login-goo-link:before,.lm-google-dugme:before{transform:none!important;top:auto!important}
/* Donja traka na telefonu */
.wd-toolbar-label{overflow:visible!important;text-overflow:clip!important;font-size:10px!important;letter-spacing:0!important}
/* Česta pitanja */
.wd-accordion-title-text{font-family:Raleway,system-ui,sans-serif!important;font-weight:600!important;color:var(--lm-smedja,#6d4e39)!important}
/* Dijeljenje članka */
.social-share.wd-style-colored .wd-social-icon{background-color:var(--lm-krem,#fdf2ec)!important;color:var(--lm-roze-jaka,#a05c57)!important;box-shadow:inset 0 0 0 1px #ecd9cf}
.social-share.wd-style-colored .wd-social-icon:hover{background-color:var(--lm-roze-jaka,#a05c57)!important;color:#fff!important}
/* Pročitaj više */
.wd-post-read-more a{color:var(--lm-roze-jaka,#a05c57)!important;text-decoration:underline!important;text-underline-offset:4px;text-decoration-thickness:1px}
/* Polja za unos */
.woocommerce form .input-text:not(.qty),.woocommerce form select,.woocommerce form textarea,.wpcf7 input[type=email],.wpcf7 input[type=text],.wpcf7 input[type=tel],.wpcf7 textarea,.lm-povrat-forma input[type=text],.lm-povrat-forma input[type=email],.lm-povrat-forma input[type=tel],.lm-povrat-forma textarea,.woocommerce-checkout .select2-container .select2-selection{border-radius:6px!important}
.required,abbr.required{color:var(--lm-roze-jaka,#a05c57)!important;text-decoration:none}
.woocommerce form input[type=checkbox],.wpcf7 input[type=checkbox],.woocommerce-form input[type=checkbox],.lm-povrat-forma input[type=checkbox],.woocommerce form input[type=radio]{accent-color:var(--lm-roze-jaka,#a05c57);width:18px;height:18px;vertical-align:-3px}
/* WhatsApp i Viber na proizvodu */
.lm-ask{border-color:#cf9b95!important}
.lm-ask:hover{background:var(--lm-krem,#fdf2ec)!important}
/* Tekst u blokovima */
.lm-cp-lead,.lm-reg-lead{color:#7a6250!important}
/* Veličine u filteru */
.wd-swatches-filter .wd-swatch.wd-text,.wd-swatches-product .wd-swatch.wd-text{min-width:44px;width:auto!important;height:34px!important;padding:0 10px;border:1px solid #e8d8cd!important;border-radius:6px!important;background:#fff!important;display:inline-flex!important;align-items:center;justify-content:center;box-shadow:none!important}
.wd-swatches-filter .wd-swatch.wd-text .wd-swatch-text,.wd-swatches-product .wd-swatch.wd-text .wd-swatch-text{font-size:13px;font-weight:600;color:var(--lm-smedja,#6d4e39);line-height:1}
.wd-swatches-filter .wd-active .wd-swatch.wd-text,.wd-swatches-filter .chosen .wd-swatch.wd-text,.wd-swatches-product .wd-swatch.wd-text.wd-active{border-color:var(--lm-roze-jaka,#a05c57)!important;background:var(--lm-krem,#fdf2ec)!important}
/* Kartice proizvoda: naslov uvijek tačno dva reda (sa „…“), pa su cijene svuda u istoj ravni */
.wd-product .wd-entities-title{line-height:1.35!important;height:calc(2.7em + 1px)!important;max-height:none!important;overflow:hidden!important;display:-webkit-box!important;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.wd-product .wd-entities-title a{display:inline!important}
</style>
	<?php
}, 45 );
