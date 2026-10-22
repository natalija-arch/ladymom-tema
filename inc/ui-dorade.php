<?php
/**
 * Lady Mom – UI/UX dorade (korpa, traka, footer, kontakt)
 * (Ranije Code Snippets #25, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – UI/UX dorade (9.10.2026). Meniji se ne diraju do uvoza iz ERP-a.

// 2) Prazna korpa: dugme, prečice na police i najprodavanije (kad stignu proizvodi).
function lm_pk_kategorije() {
	return array(
		'garderoba'        => 'Garderoba',
		'dojenje'          => 'Dojenje',
		'jastuci'          => 'Jastuci',
		'pojasevi'         => 'Pojasevi i nosiljke',
		'bebe'             => 'Njega i kozmetika',
		'torbe-i-neseseri' => 'Torbe i neseseri',
	);
}
add_action( 'woocommerce_cart_is_empty', function () {
	if ( ! is_cart() ) {
		return;
	}
	echo '<div class="lm-pk">';
	echo '<p class="lm-pk-dugme"><a class="button wc-backward" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Nazad u prodavnicu</a></p>';
	echo '<h3 class="lm-pk-naslov">Pogledajte police</h3><div class="lm-pk-kat">';
	foreach ( lm_pk_kategorije() as $slug => $naziv ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		$url  = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/?product_cat=' . $slug );
		echo '<a href="' . esc_url( $url ) . '">' . esc_html( $naziv ) . '</a>';
	}
	echo '</div>';
	$best = do_shortcode( '[products limit="4" columns="4" best_selling="true"]' );
	if ( preg_match( '/\bpost-\d+/', $best ) ) {
		echo '<h3 class="lm-pk-naslov">Omiljeno kod mama</h3><div class="lm-pk-best">' . $best . '</div>';
	}
	echo '</div>';
}, 20 );

// 3) Pretraga u mobilnom meniju: kratak placeholder da se ne siječe.
add_action( 'wp_footer', function () {
	echo '<script id="lm-ui-js">document.querySelectorAll(".mobile-nav input.s").forEach(function(i){i.placeholder="Šta tražite?";});</script>';
}, 99 );

// 4) Stilovi.
add_action( 'wp_head', function () {
	?>
<style id="lm-ui-dorade">
/* plutajuća dugmad */
@media (max-width:1024px){.scrollToTop{display:none!important}}
.woocommerce-cart .lm-chat,.woocommerce-checkout .lm-chat,.woocommerce-account .lm-chat{display:none!important}

/* traka povjerenja iznad footera: na mobilnom u jednom redu */
@media (max-width:767.98px){
.wd-prefooter .wp-block-wd-section{padding-top:22px!important;padding-bottom:20px!important}
.wd-prefooter .wp-block-wd-section>.wp-block-wd-row{flex-wrap:nowrap!important;gap:10px!important;align-items:flex-start!important}
.wd-prefooter .wp-block-wd-section>.wp-block-wd-row>.wp-block-wd-column{justify-content:flex-start!important;align-self:flex-start!important;margin:0!important}
.wd-prefooter .wp-block-wd-section>.wp-block-wd-row>.wp-block-wd-column{flex:1 1 0!important;min-width:0!important;row-gap:8px!important}
.wd-prefooter .wp-block-wd-icon svg,.wd-prefooter .wp-block-wd-icon img{width:30px!important;height:30px!important}
.wd-prefooter .wp-block-wd-paragraph{font-size:12px!important;line-height:1.35!important}
}

/* footer: kontakt butika uvijek otvoren na mobilnom */
@media (max-width:767.98px){
.wd-footer .wd-toggle:has(a[href^="tel:"]) .wd-toggle-content{display:block!important;height:auto!important}
.wd-footer .wd-toggle:has(a[href^="tel:"]) .wd-toggle-content-inner{opacity:1!important;visibility:visible!important;transform:none!important}
.wd-footer .wd-toggle:has(a[href^="tel:"]) .wd-toggle-icon{display:none!important}
.wd-footer .wd-toggle:has(a[href^="tel:"]) .wd-toggle-head{pointer-events:none}
}

/* početna: sekcije bez proizvoda se ne prikazuju dok ne stignu proizvodi iz ERP-a */
body:not(.elementor-editor-active) .elementor-element-lmn0001:not(:has(.product,.wd-product,.product-grid-item)),
body:not(.elementor-editor-active) .elementor-element-lmb0001:not(:has(.product,.wd-product,.product-grid-item)),
body:not(.elementor-editor-active) .elementor-element-lmo0001:not(:has(.product,.wd-product,.product-grid-item)){display:none!important}

/* slajd 4 (škola) je sad teal */
body #slide-35678 .wd-slide-bg{background-color:var(--lm-tirkiz-footer)!important}

/* gornja traka: tri poruke, na užim ekranima se smjenjuju */
.header-banner .lm-topbar{display:flex;align-items:center;justify-content:center;width:100%;text-align:center}
.lm-topbar a{color:#fff!important;font-size:13px;line-height:1.3;font-weight:500;text-decoration:none}
.lm-topbar a:hover,.lm-topbar a:focus-visible{text-decoration:underline}
@media (min-width:1200px){.lm-topbar a+a{margin-left:26px;padding-left:26px;border-left:1px solid rgba(255,255,255,.4)}}
@media (max-width:1199.98px){
.header-banner .lm-topbar{display:grid}
.lm-topbar a{grid-area:1/1;align-self:center;justify-self:center;display:inline-flex;align-items:center;min-height:24px;font-size:12.5px;opacity:0;visibility:hidden;animation:lmTop 12s infinite both}
.lm-topbar a:nth-child(2){animation-delay:4s}
.lm-topbar a:nth-child(3){animation-delay:8s}
.lm-topbar:hover a,.lm-topbar:focus-within a{animation-play-state:paused}
}
/* poruke se ne preklapaju: prethodna nestane prije nego što se pojavi sljedeća (link od 24px, pristupačnost 22.10.) */
@keyframes lmTop{0%{opacity:0;visibility:hidden;transform:translateY(6px)}4%,30%{opacity:1;visibility:visible;transform:none}33.2%{opacity:0;visibility:visible;transform:translateY(-6px)}33.3%,100%{opacity:0;visibility:hidden;transform:translateY(-6px)}}
@media (max-width:1199.98px) and (prefers-reduced-motion:reduce){
.lm-topbar a{animation:none;opacity:0;visibility:hidden}
.lm-topbar a:first-child{opacity:1;visibility:visible}
}

/* prazna korpa */
.woocommerce-cart .woocommerce:has(>.lm-pk){display:flex;flex-direction:column}
.woocommerce-cart .woocommerce:has(>.lm-pk)>.wd-empty-block-text{order:1}
.lm-pk{order:2;text-align:center;max-width:960px;width:100%;margin:0 auto}
.lm-pk~.return-to-shop{display:none!important}
.lm-pk-dugme{margin:0 0 38px}
.lm-pk-naslov{font-size:18px;margin:0 0 14px;color:var(--lm-smedja)}
.lm-pk-kat{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin:0 0 42px}
.lm-pk-kat a{display:inline-flex;align-items:center;min-height:44px;padding:10px 18px;border:1.5px solid var(--lm-linija);border-radius:999px;background:#fff;color:var(--lm-smedja)!important;font-size:14px;font-weight:600;text-decoration:none;transition:border-color .2s,background-color .2s}
.lm-pk-kat a:hover{border-color:var(--lm-roze-jaka);background:var(--lm-krem)}
.lm-pk-best{text-align:left}

/* kontakt: brze akcije na vrhu */
.lm-kontakt{max-width:1100px;margin:0 auto;text-align:center}
.lm-k-eyebrow{font-size:12px;font-weight:700;letter-spacing:.16em;color:var(--lm-roze-jaka);margin:0 0 10px}
.lm-kontakt .lm-k-naslov{font-size:clamp(28px,4vw,40px);line-height:1.15;margin:0 0 10px;color:var(--lm-smedja)}
.lm-k-uvod{color:var(--lm-tekst);margin:0 0 26px}
.lm-k-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}
@media (min-width:768px) and (max-width:1024.98px){.lm-k-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
.lm-k-card{display:flex;flex-direction:column;align-items:center;gap:6px;padding:22px 14px;border-radius:18px;background:var(--lm-krem);color:var(--lm-smedja)!important;text-decoration:none!important;transition:transform .2s,box-shadow .2s}
.lm-k-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(109,78,57,.12)}
.lm-k-ic{width:46px;height:46px;border-radius:50%;background:#fff;display:grid;place-items:center;color:var(--lm-roze-jaka);margin-bottom:4px}
.lm-k-ic svg{width:22px;height:22px}
.lm-k-t{font-weight:700;font-size:15px}
.lm-k-v{font-size:14px;color:var(--lm-tekst);overflow-wrap:anywhere}
.lm-k-sati{margin:18px 0 0;color:var(--lm-tekst);font-size:14px}
@media (max-width:767.98px){.lm-k-grid{grid-template-columns:1fr 1fr;gap:10px}.lm-k-card{padding:16px 10px}.lm-k-grid>.lm-k-card:last-child:nth-child(odd){grid-column:1/-1}}

/* obavještenja: dugme u poruci je samo link u roze boji, bez polja */
.woocommerce-info .button,.woocommerce-message .button,.woocommerce-error .button,.wc-block-components-notice-banner .button,.woocommerce-info a.wc-forward,.woocommerce-message a.wc-forward{background:none!important;border:0!important;box-shadow:none!important;padding:0!important;min-height:0!important;height:auto!important;line-height:inherit!important;color:var(--lm-roze-jaka)!important;font-size:inherit!important;font-weight:700!important;letter-spacing:0!important;text-transform:none!important;text-decoration:underline!important;text-underline-offset:3px;border-radius:0!important;float:right;margin:0 0 0 18px!important}
.woocommerce-info .button:hover,.woocommerce-message .button:hover,.woocommerce-error .button:hover{color:var(--lm-roze-jaka-hover,#8a4b47)!important;background:none!important}
@media (max-width:767.98px){.woocommerce-info .button,.woocommerce-message .button,.woocommerce-error .button{float:none;display:block!important;margin:8px 0 0!important}}

/* footer: strelica za otvaranje kolona na telefonu, bijela na tirkiznoj */
.wd-footer .wd-toggle-icon svg,.wd-footer .wd-toggle-icon svg *{fill:#fff!important;stroke:none!important}
.wd-footer .wd-toggle-icon svg{width:14px!important;height:14px!important}
.wd-footer .wd-toggle-head>.wd-toggle-icon{align-self:flex-start!important;margin-top:3px!important}

/* „Napravi nalog“ u panelu za prijavu: sporedno dugme (roze okvir), iste visine kao „Prijavi se“ */
html body .create-account-question a.btn.create-account-button.create-account-button{display:inline-flex!important;align-items:center;justify-content:center;min-height:42px;padding:5px 28px!important;background:transparent!important;color:var(--lm-roze-jaka,#a05c57)!important;border:1.5px solid #cf9b95!important;border-radius:6px!important;font-size:13px!important;font-weight:700!important;letter-spacing:.06em!important;text-transform:uppercase!important;text-decoration:none!important;line-height:1.2!important;box-shadow:none!important}
html body .create-account-question a.btn.create-account-button.create-account-button:hover,html body .create-account-question a.btn.create-account-button.create-account-button:focus-visible{background:var(--lm-roze-jaka,#a05c57)!important;border-color:var(--lm-roze-jaka,#a05c57)!important;color:#fff!important}
html body .create-account-question a.btn.create-account-button.create-account-button::after,html body .create-account-question a.btn.create-account-button.create-account-button::before{display:none!important}

/* header na telefonu: poruke u gornjoj traci u jednom redu, logo se skuplja na uskim ekranima da ništa ne izlazi van */
@media (max-width:767.98px){
.header-banner .container{padding-left:12px!important;padding-right:12px!important}
.lm-topbar a{white-space:nowrap;max-width:100%;overflow:hidden;text-overflow:ellipsis}
}
@media (max-width:380px){.lm-topbar a{font-size:12px!important;letter-spacing:-.005em}}
@media (max-width:420px){
.whb-general-header .whb-mobile-center .site-logo{padding-left:6px!important;padding-right:6px!important}
.whb-general-header .whb-mobile-center .wd-logo img{width:148px!important;max-width:148px!important;height:auto!important}
}

/* Moj nalog: pločice (Porudžbine, Adrese…) bez podvlačenja */
html body .wd-my-account-links a,html body .wd-my-account-links a:hover,html body .wd-my-account-links a:focus{text-decoration:none!important}

/* pretraga: okruglo dugme sa lupom umjesto teksta, polje normalne visine */
html body form.searchform{position:relative}
html body form.searchform input.s{height:54px!important;min-height:0!important;padding-right:62px!important}
html body form.searchform .searchsubmit{position:absolute!important;top:50%!important;right:6px!important;bottom:auto!important;transform:translateY(-50%);width:42px!important;height:42px!important;min-width:42px!important;max-width:42px!important;flex:none!important;min-height:0!important;padding:0!important;margin:0!important;border-radius:50%!important;font-size:0!important;line-height:0!important;background:var(--lm-roze-jaka,#a05c57)!important;display:flex!important;align-items:center;justify-content:center}
html body form.searchform .searchsubmit:hover,html body form.searchform .searchsubmit:focus-visible{background-color:var(--lm-roze-jaka-hover,#8a4b47)!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-3.6-3.6'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:center!important;background-size:19px 19px!important;color:#fff!important}
/* tekst „Pretraga“ ostaje za čitače ekrana (naziv dugmeta), samo se ne vidi (pristupačnost 22.10.) */
html body form.searchform .searchsubmit>span{display:block!important;position:absolute!important;width:1px!important;height:1px!important;margin:-1px!important;padding:0!important;overflow:hidden!important;clip:rect(0 0 0 0)!important;clip-path:inset(50%)!important;white-space:nowrap!important;border:0!important;font-size:12px!important;line-height:1!important}
html body form.searchform .searchsubmit{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M20 20l-3.6-3.6'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:center!important;background-size:19px 19px!important}
html body form.searchform .searchsubmit::after{content:none!important}
html body form.searchform .searchsubmit::before{color:#fff!important}
/* pretraga u meniju na telefonu: odvojena od tabova Meni / Kategorije */
.mobile-nav form.searchform{margin:14px 14px 16px!important}
.mobile-nav form.searchform input.s{height:48px!important;border:1.5px solid var(--lm-linija,#e8d6d0)!important;border-radius:999px!important;padding-left:18px!important;padding-right:56px!important;background:#fff!important}
html body .mobile-nav form.searchform .searchsubmit{width:38px!important;min-width:38px!important;max-width:38px!important;height:38px!important;right:5px!important}
.mobile-nav form.searchform{box-shadow:none!important}
.mobile-nav .wd-search-results,.mobile-nav .search-results-wrapper{margin:0 14px}

/* header na laptopu (1025–1366 px): meni ne smije da dodiruje logo */
@media (min-width:1025px) and (max-width:1366.98px){
.whb-general-header .wd-nav-main{--nav-gap:14px!important;column-gap:14px!important}
.whb-general-header .wd-nav-main>li>a{font-size:13px!important}
}
@media (min-width:1025px) and (max-width:1280.98px){
.whb-general-header .wd-header-my-account .wd-tools-text{display:none!important}
.whb-general-header .wd-logo img{max-width:170px!important;height:auto!important}
}

/* korpa i plaćanje: načini dostave kao kartice, adresa kao jedan kratak red */
.cart_totals table.shop_table,.cart_totals table.shop_table>tbody{display:block!important;width:100%!important}
.cart_totals table.shop_table>tbody>tr{display:flex!important;justify-content:space-between;align-items:baseline;gap:12px;width:100%;border-bottom:1px solid var(--lm-linija,#eee)}
.cart_totals table.shop_table>tbody>tr>th,.cart_totals table.shop_table>tbody>tr>td{display:block!important;border:0!important}
.cart_totals table.shop_table>tbody>tr>td{text-align:right!important;flex:1 1 auto;width:auto!important;padding-left:0!important;padding-right:0!important}
.cart_totals table.shop_table>tbody>tr>th{flex:none;width:auto!important;padding-left:0!important;padding-right:0!important}
.cart_totals table.shop_table>tbody>tr>td::before{display:none!important}
.cart_totals tr.woocommerce-shipping-totals>td{text-align:left!important;width:100%!important}
.cart_totals table.shop_table>tbody>tr:not(.woocommerce-shipping-totals){flex-direction:row!important}
.cart_totals table.shop_table>tbody>tr.woocommerce-shipping-totals{flex-direction:column!important;align-items:stretch!important;gap:0!important}
#order_review tr.woocommerce-shipping-totals{display:flex!important;flex-direction:column}
#order_review tr.woocommerce-shipping-totals>td{width:100%!important}
.cart_totals .order-total td .includes_tax,.cart_totals .order-total td small{display:block;font-size:12px;white-space:nowrap}
.cart_totals tr.woocommerce-shipping-totals>th,.cart_totals tr.woocommerce-shipping-totals>td,#order_review tr.woocommerce-shipping-totals>th,#order_review tr.woocommerce-shipping-totals>td{display:block!important;width:100%!important;text-align:left!important;border:0!important}
.cart_totals tr.woocommerce-shipping-totals>th,#order_review tr.woocommerce-shipping-totals>th{padding:16px 0 8px!important}
.cart_totals tr.woocommerce-shipping-totals>td,#order_review tr.woocommerce-shipping-totals>td{padding:0 0 16px!important}
html body ul#shipping_method{margin:0!important;padding:0!important;list-style:none!important;display:grid;gap:8px}
html body ul#shipping_method li{position:relative;display:flex!important;flex-wrap:wrap;align-items:center;gap:4px 10px;margin:0!important;padding:12px 14px!important;border:1.5px solid var(--lm-linija,#e8d6d0);border-radius:12px;background:#fff;text-align:left!important;transition:border-color .15s,background-color .15s}
html body ul#shipping_method li:has(input:checked){border-color:var(--lm-roze-jaka,#a05c57);background:var(--lm-krem,#fdf2ec)}
html body ul#shipping_method li input.shipping_method{margin:0!important;flex:none;width:18px;height:18px;accent-color:var(--lm-roze-jaka,#a05c57)}
html body ul#shipping_method li label{flex:1;margin:0!important;font-size:15px;font-weight:600;line-height:1.35;color:var(--lm-smedja,#6d4e39);cursor:pointer}
html body ul#shipping_method li label::after{content:"";position:absolute;inset:0}
html body ul#shipping_method li .lm-pickup-info{flex-basis:100%;padding-left:28px;font-size:13px;line-height:1.45;color:var(--lm-tekst,#7a6250)}
html body ul#shipping_method li input[type=hidden]+label{padding-left:0}
.cart_totals .woocommerce-shipping-destination{margin:10px 0 0!important;font-size:13px!important;line-height:1.5;color:var(--lm-tekst,#7a6250);text-align:left!important}
.cart_totals .woocommerce-shipping-destination strong{font-weight:600}
.cart_totals .woocommerce-shipping-calculator{margin:2px 0 0!important;text-align:left!important}
.cart_totals .shipping-calculator-button{font-size:13px!important;font-weight:600;color:var(--lm-roze-jaka,#a05c57)!important;text-decoration:underline!important;text-underline-offset:3px}
.cart_totals .shipping-calculator-button::after{display:none!important}

/* pretraga u sadržaju (prodavnica bez rezultata): centrirana i zaobljena kao u headeru */
.wd-content-area .wd-search-form{max-width:620px;margin:0 auto 26px!important}
.wd-content-area form.searchform input.s{border-radius:999px!important;padding-left:22px!important}

/* Dugmad-linkovi (Saznaj više, Pogledaj ponudu…) umjesto podvučenog teksta: roze okvir kao „Napravi nalog“ (12.10.2026) */
html body .wd-button-wrapper a.btn.btn-style-link,html body a.btn.btn-style-link.btn-color-primary{display:inline-flex!important;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:5px 26px!important;background:transparent!important;color:var(--lm-roze-jaka,#a05c57)!important;border:1.5px solid #cf9b95!important;border-radius:6px!important;font-size:13px!important;font-weight:700!important;letter-spacing:.06em!important;text-transform:uppercase!important;text-decoration:none!important;line-height:1.2!important;box-shadow:none!important;transition:background-color .2s,color .2s}
html body .wd-button-wrapper a.btn.btn-style-link:hover,html body a.btn.btn-style-link.btn-color-primary:hover,html body .wd-button-wrapper a.btn.btn-style-link:focus-visible{background:#cf9b95!important;color:#fff!important;border-color:#cf9b95!important}
html body a.btn.btn-style-link::after,html body a.btn.btn-style-link::before{content:none!important;display:none!important}
/* Aktivna stavka menija: boja umjesto podvlake */
.whb-header .wd-nav-main>li.current-menu-item>a .nav-link-text,.whb-header .wd-nav-main>li.current-menu-item>a{color:var(--lm-roze-jaka,#a05c57)!important}
.whb-header .wd-nav-main>li>a::after,.whb-header .wd-nav-main>li>a .nav-link-text::after{display:none!important}
.whb-header .wd-nav-main>li.current-menu-item>a{text-decoration:none!important;box-shadow:none!important;border-bottom:0!important}

/* slike artikala svuda 3:4 (18.10.2026): liste i karuseli režu kao WooCommerce kopija; galerija proizvoda bez rezanja */
.wd-product .product-element-top img,.wd-product .wd-product-thumb img,.product-grid-item .product-element-top img{aspect-ratio:3/4;object-fit:cover;width:100%;height:auto}
.woocommerce-product-gallery .wd-carousel-item img,.woocommerce-product-gallery__image img{aspect-ratio:3/4;object-fit:contain;background:#fff;width:100%;height:auto}
.wd-gallery-thumb img{aspect-ratio:3/4;object-fit:cover}
.woocommerce-cart-form .product-thumbnail img,.shop_table .product-thumbnail img,.woocommerce-mini-cart-item img,.wd-search-results .wd-suggestion img,.autocomplete-suggestion img,.wd-wishlist-content .product-thumbnail img{aspect-ratio:3/4;object-fit:cover;height:auto}
</style>
	<?php
}, 30 );

// Kartice proizvoda: bez korijenske ERP kategorije „Prodavnica ERP“ (slug prodavnica).
// Samo za prikaz posjetiocima; admin, REST (ERP) i cron vide sve kategorije.
add_filter( 'get_the_terms', function ( $terms, $post_id, $taxonomy ) {
	if ( 'product_cat' !== $taxonomy || ! is_array( $terms ) || is_admin() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $terms;
	}
	return array_values( array_filter( $terms, function ( $t ) { return 'prodavnica' !== $t->slug; } ) );
}, 10, 3 );
