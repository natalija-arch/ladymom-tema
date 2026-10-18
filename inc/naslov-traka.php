<?php
/**
 * Lady Mom – traka naslova stranice (Moj nalog, Korpa, Prodavnica, Blog…) sa
 * krivudavim linijama i tačkama iz brend dizajna, kao na zaglavlju emailova
 * (Markova odluka 18.10.2026). Krem pozadina #fdf2ec, linije u roze tonovima.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_head', function () {
	$desno  = "%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 300 160' fill='none'%3E%3Cpath d='M8 0C58 38 128 34 170 52c44 19 40 70 82 96 14 9 30 12 48 12' stroke='%23cf9b95' stroke-width='1.4' stroke-linecap='round'/%3E%3Cpath d='M120 0c22 20 54 26 86 28' stroke='%23e8c4bf' stroke-width='1.1' stroke-linecap='round'/%3E%3Ccircle cx='236' cy='46' r='6' fill='%23db9e99'/%3E%3Ccircle cx='262' cy='76' r='3' fill='%23cf9b95'/%3E%3C/svg%3E";
	$lijevo = "%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 260 160' fill='none'%3E%3Cpath d='M0 34c44 6 62 50 98 74 34 23 82 18 120 34 16 7 30 12 42 18' stroke='%23e3b7b1' stroke-width='1.3' stroke-linecap='round'/%3E%3Ccircle cx='54' cy='128' r='4' fill='%23e8c4bf'/%3E%3C/svg%3E";
	?>
<style id="lm-naslov-traka">
.wd-page-title{background-color:#fdf2ec!important;background-image:url("data:image/svg+xml,<?php echo $desno; // phpcs:ignore ?>"),url("data:image/svg+xml,<?php echo $lijevo; // phpcs:ignore ?>")!important;background-repeat:no-repeat!important;background-position:right top,left bottom!important;background-size:auto 100%,auto 85%!important}
.wd-page-title>.wd-page-title-bg{display:none!important}
@media (max-width:768.98px){.wd-page-title{background-size:auto 100%,auto 80%!important;background-position:right -110px top,left -90px bottom!important}}
</style>
	<?php
}, 45 );
