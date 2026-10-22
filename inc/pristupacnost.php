<?php
/**
 * Lady Mom – pristupačnost (22.10.2026, PageSpeed pristupačnost 88 → 100).
 *
 * Pločice kategorija (WoodMart, šablon content-product-cat-default.php): slika je link, a preko
 * cijele pločice stoji još jedan link (.category-link) sa natpisom „Product category <slug>“.
 * Link na slici je zato prekriven drugim linkom (Lighthouse: „target-size“) i dupli je.
 * Slika ostaje bez linka (klik ionako ide na link preko pločice), a link preko pločice dobija
 * ime kategorije kao natpis za čitače ekrana. Šablon teme se ne dira.
 *
 * Ostale izmjene istog dana: tekst dugmeta pretrage (inc/ui-dorade.php), smjena poruka u
 * gornjoj traci (inc/ui-dorade.php) i kontrast oznaka na karticama (inc/prodaja-i-brend.php).
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		?>
<script id="lm-pristupacnost">
(function(){function lmKat(){document.querySelectorAll('.wd-cat-inner').forEach(function(c){var s=c.querySelector('a.wd-cat-image'),l=c.querySelector('a.category-link');if(!s||!l||!s.hasAttribute('href')){return;}var t=c.querySelector('.wd-entities-title'),n=(s.getAttribute('aria-label')||(t?t.textContent:'')).trim();if(n){l.setAttribute('aria-label',n);}s.removeAttribute('href');s.removeAttribute('aria-label');});}
if(document.readyState!=='loading'){lmKat();}else{document.addEventListener('DOMContentLoaded',lmKat);}})();
</script>
		<?php
	},
	50
);
