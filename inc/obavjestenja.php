<?php
/**
 * Lady Mom – obavještenja u stilu sajta
 * (Ranije Code Snippets #9, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Obavjestenja (umjesto browser alert prozora) u stilu sajta.
add_action( 'wp_head', function () {
	echo '<style id="lm-toast-css">
.lm-toast{position:fixed;left:50%;top:24px;z-index:100000;display:flex;align-items:center;gap:12px;max-width:min(520px,calc(100vw - 32px));padding:14px 16px 14px 18px;background:#fff;color:#2A2226;border-left:4px solid #C01157;border-radius:10px;box-shadow:0 14px 40px rgba(42,34,38,.18);font-size:15px;line-height:1.45;opacity:0;visibility:hidden;transform:translate(-50%,-12px);transition:opacity .25s,transform .25s,visibility .25s}
.lm-toast.lm-show{opacity:1;visibility:visible;transform:translate(-50%,0)}
.lm-toast svg{flex:0 0 22px;width:22px;height:22px;fill:#C01157}
.lm-toast span{flex:1}
.lm-toast button{flex:0 0 auto;background:none;border:0;padding:0 2px;font-size:22px;line-height:1;color:#8A7A80;cursor:pointer;min-height:0}
@media (max-width:768px){.lm-toast{top:auto;bottom:80px;transform:translate(-50%,12px)}.lm-toast.lm-show{transform:translate(-50%,0)}}
.variations .lm-req{margin:8px 0 0;color:#C01157;font-style:italic;font-size:13px;line-height:1.3}
.variations td.lm-need .wd-swatch:not(.wd-active),.variations td.lm-need select{border-color:#C01157!important}
.lm-rate{display:flex;align-items:center;gap:8px;font-size:13px;color:#2A2226;margin:-6px 0 14px;padding:10px 12px;background:#FBF3F1;border-radius:8px}
.lm-rate-badge{flex:0 0 auto;background:#4A2B7A;color:#fff;font-weight:700;font-size:11px;letter-spacing:.05em;padding:3px 7px;border-radius:4px}
</style>';
} );
add_filter( 'woocommerce_get_script_data', function ( $data, $handle ) {
	if ( 'wc-add-to-cart-variation' === $handle && is_array( $data ) ) {
		$data['i18n_make_a_selection_text'] = 'Izaberite veličinu i boju prije dodavanja u korpu.';
		$data['i18n_unavailable_text']      = 'Ova kombinacija trenutno nije dostupna. Izaberite drugu.';
	}
	return $data;
}, 20, 2 );
