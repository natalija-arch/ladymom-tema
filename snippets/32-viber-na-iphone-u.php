<?php
/**
 * Snippet #32: Viber na iPhone-u
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Lady Mom – Viber na iPhone-u (12.10.2026).
// iOS Viber ne otvara razgovor preko viber://chat sa brojem koji nije u imeniku, pa na iPhone-u/iPad-u link vodi na profil Lady Mom (odatle jedan dodir do poruke).
add_action( 'wp_footer', function () {
	?><script id="lm-viber-ios">(function(){var ios=/iPad|iPhone|iPod/.test(navigator.userAgent)||(navigator.platform==='MacIntel'&&navigator.maxTouchPoints>1);if(!ios)return;function fix(a){var h=a.getAttribute('href')||'';if(h.indexOf('viber://chat')===0){var m=h.match(/number=([^&]+)/);var num=m?decodeURIComponent(m[1]).replace(/[^0-9]/g,''):'38267030655';a.setAttribute('href','viber://contact?number=%2B'+num);}}function all(){document.querySelectorAll('a[href^="viber://chat"]').forEach(fix);}document.addEventListener('DOMContentLoaded',all);all();document.addEventListener('click',function(e){var a=e.target.closest&&e.target.closest('a[href^="viber://chat"]');if(a)fix(a);},true);})();</script><?php
}, 99 );
