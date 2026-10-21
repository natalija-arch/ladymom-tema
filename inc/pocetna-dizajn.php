<?php
/**
 * Lady Mom – dizajn početne (21.10.2026, Marko: „kreni sa podešavanjem“, „možeš i dizajn poboljšati“).
 *
 * Raspored i sadržaj su u Elementoru (WoodMart elementi): kategorije u karuselu (lmc0001),
 * proizvodi u tabovima Najprodavanije / Novo / Na akciji (76d6c0d), brendovi, blog.
 * Ovdje je samo izgled tih elemenata, vezan za njihove ID-jeve, i učitava se samo na početnoj
 * i na njenom nacrtu (opcija `lm_pocetna_nacrt`). Predlozi: dokument „Početna ladymom.me: predlozi za dizajn“.
 */

defined( 'ABSPATH' ) || exit;

function lm_je_pocetna_ili_nacrt() {
	if ( is_front_page() ) {
		return true;
	}
	$nacrt = (int) get_option( 'lm_pocetna_nacrt' );
	return $nacrt && is_page( $nacrt );
}

add_action( 'wp_head', function () {
	if ( ! lm_je_pocetna_ili_nacrt() ) {
		return;
	}
	?>
<style id="lm-pocetna-dizajn">
/* Kategorije: zaobljene slike, naziv običnim slovima ispod slike */
.elementor-element-lmc0001 .wd-cat-thumb,.elementor-element-lmc0001 .wd-cat-image{display:block;border-radius:14px;overflow:hidden}
.elementor-element-lmc0001 .wd-cat-image img{transition:transform .5s ease}
.elementor-element-lmc0001 .wd-cat:hover .wd-cat-image img{transform:scale(1.04)}
.elementor-element-lmc0001 .wd-cat-content{position:static!important;inset:auto!important;transform:none!important;padding:10px 2px 0!important;text-align:center!important}
.elementor-element-lmc0001 .wd-cat-inner{overflow:visible!important}
.elementor-element-lmc0001 .wd-entities-title{text-align:center!important;width:100%;position:static!important;inset:auto!important;transform:none!important;opacity:1!important;text-transform:none!important;letter-spacing:0!important;font-size:16px!important;font-weight:600!important;line-height:1.25!important;color:var(--lm-smedja,#6d4e39)!important;word-break:normal!important;overflow-wrap:normal!important;hyphens:none!important}
.elementor-element-lmc0001 .wd-carousel-container{margin-bottom:0}

/* Tabovi proizvoda: sitniji natpisi, svi tabovi u jednom redu */
.elementor-element-76d6c0d .wd-nav-tabs{justify-content:center}
.elementor-element-76d6c0d .wd-nav-tabs>li>a{color:var(--lm-tekst,#7a6250)}
.elementor-element-76d6c0d .wd-nav-tabs>li.wd-active>a{color:var(--lm-smedja,#6d4e39)}

/* Strelice karusela: bijeli krug, da se ne stapaju sa slikom */
.elementor-element-lmo0001 .wd-btn-arrow .wd-arrow-inner,.elementor-element-lmb0001 .wd-btn-arrow .wd-arrow-inner,.elementor-element-lmc0001 .wd-btn-arrow .wd-arrow-inner{width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,.94);box-shadow:0 2px 10px rgba(109,78,57,.14);color:var(--lm-smedja,#6d4e39)}

/* Koala: tačka na pojasu u boji brenda, kartica zaobljena */
.elementor-element-lmhs000 .hotspot-btn{background:var(--lm-roze-jaka,#a05c57)!important;width:26px!important;height:26px!important;box-shadow:0 0 0 3px rgba(255,255,255,.9),0 3px 12px rgba(0,0,0,.18)}
.elementor-element-lmhs000 .hotspot-btn:after{width:8px!important;height:8px!important}
.elementor-element-lmhs000 .hotspot-content{border-radius:14px;box-shadow:0 12px 32px rgba(109,78,57,.2)}
.elementor-element-lmhs000 .hotspot-content .wd-entities-title{font-size:17px}

/* Blog: tri kompaktne kartice u jednom redu (slika lijevo, naslov desno), kao na telefonu.
   Widget je u jednoj koloni sa velikim slikama, pa je sekcija bila visoka ~1400 px. */
.elementor-element-38198c0 .wd-post-inner{display:flex!important;align-items:center;gap:16px}
.elementor-element-38198c0 .wd-post-thumb{flex:0 0 40%;max-width:40%;margin:0!important;border-radius:12px;overflow:hidden}
.elementor-element-38198c0 .wd-post-thumb img{display:block;width:100%;height:auto;aspect-ratio:4/3;object-fit:cover;transition:transform .5s ease}
.elementor-element-38198c0 .wd-post:hover .wd-post-thumb img{transform:scale(1.04)}
.elementor-element-38198c0 .wd-post-thumb .wd-post-date{display:none}
.elementor-element-38198c0 .wd-post-content{flex:1 1 auto;padding:0!important;text-align:left!important}
.elementor-element-38198c0 .wd-post-title{font-size:16px!important;line-height:1.3!important;margin-bottom:6px!important}
.elementor-element-38198c0 .wd-post-read-more{margin-top:0!important;text-align:left!important;justify-content:flex-start!important}
@media (min-width:1025px){
	.elementor-element-38198c0 .wd-blog-holder{grid-template-columns:repeat(3,minmax(0,1fr))!important;column-gap:28px!important;row-gap:20px!important}
	.elementor-element-38198c0 .wd-post{margin-bottom:0}
}
@media (min-width:769px) and (max-width:1024px){
	.elementor-element-38198c0 .wd-blog-holder{grid-template-columns:repeat(2,minmax(0,1fr))!important;column-gap:24px!important;row-gap:20px!important}
}

/* Škola: lista činjenica kao kompaktan blok */
.elementor-element-lms0008{max-width:420px;margin-left:auto;margin-right:auto}

@media (max-width:768px){
	.elementor-element-lmc0001 .wd-entities-title{font-size:14px!important}
	.elementor-element-76d6c0d .wd-nav-tabs{gap:18px;flex-wrap:nowrap}
	.elementor-element-76d6c0d .wd-nav-tabs>li>a{font-size:13px!important;letter-spacing:.06em!important;padding-left:0!important;padding-right:0!important}
	.elementor-element-lmo0001 .wd-btn-arrow .wd-arrow-inner,.elementor-element-lmb0001 .wd-btn-arrow .wd-arrow-inner,.elementor-element-lmc0001 .wd-btn-arrow .wd-arrow-inner{width:32px;height:32px}

	/* Blog na telefonu: kartice jedna ispod druge, slika nešto uža */
	.elementor-element-38198c0 .wd-post-inner{gap:14px}
	.elementor-element-38198c0 .wd-post-thumb{flex-basis:38%;max-width:38%}
	.elementor-element-38198c0 .wd-post{margin-bottom:16px}
}
</style>
	<?php
}, 48 );
