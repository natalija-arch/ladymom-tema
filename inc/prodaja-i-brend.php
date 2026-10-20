<?php
/**
 * Lady Mom – prodaja (rate, WhatsApp, korpa, oznake)
 * (Ranije Code Snippets #11, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – prodaja: rate, pitanje za velicinu, predlozi u korpi, oznake, zastita marze.

// ---------------------------------------------------------------------------
// 2) "Pitaj za velicinu" preko WhatsApp-a.
// ---------------------------------------------------------------------------
add_action( 'woocommerce_after_add_to_cart_form', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	// Veličina se pominje samo kod artikala koji imaju veličinu (20.10.2026).
	$ima_velicinu = '' !== trim( (string) $product->get_attribute( 'pa_velicina' ) );
	$msg  = 'Zdravo! Zanima me proizvod „' . wp_strip_all_tags( $product->get_name() ) . '“ – ' . get_permalink( $product->get_id() ) . ( $ima_velicinu ? ' Možete li mi pomoći sa veličinom?' : ' Imam pitanje.' );
	$link = 'https://wa.me/38267030655?text=' . rawurlencode( $msg );
	echo '<div class="lm-ask-box"><p class="lm-ask-t">' . ( $ima_velicinu ? 'Pitajte nas za veličinu ili savjet' : 'Pitajte nas za savjet' ) . '</p><div class="lm-ask-row">'
		. '<a class="lm-ask lm-ask-wa" href="' . esc_url( $link ) . '" target="_blank" rel="noopener nofollow">'
		. '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 0 1-11.8 7L4 20l1.1-4A8 8 0 1 1 20 12z"/></svg>'
		. '<span>WhatsApp</span></a>'
		. '<a class="lm-ask lm-ask-vb" href="viber://chat?number=%2B38267030655" rel="nofollow">'
		. '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3c-4.4 0-8 2.9-8 7.3 0 2.5 1.1 4.6 3 6V21l3.2-2.3c.6.1 1.2.2 1.8.2 4.4 0 8-2.9 8-7.6S16.4 3 12 3z"/><path d="M9.6 8.4c.3 1.7 1.5 3.5 3.5 4.7.5.3 1 .1 1.3-.3l.3-.6-1.3-.8-.6.5c-.7-.4-1.3-1-1.7-1.7l.5-.6-.8-1.3-.6.3c-.4.2-.6.6-.6 1z"/></svg>'
		. '<span>Viber</span></a></div></div>';
}, 15 );

// ---------------------------------------------------------------------------
// 3) Predlozi u korpi kad fali malo do besplatne dostave.
// ---------------------------------------------------------------------------
function lm_free_shipping_min() {
	return 100;
}
add_action( 'woocommerce_after_cart_table', function () {
	if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	$total = (float) WC()->cart->get_displayed_subtotal();
	$left  = lm_free_shipping_min() - $total;
	if ( $left <= 0 || $left > 30 ) {
		return;
	}
	$in_cart = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		$in_cart[] = $item['product_id'];
	}
	$q = new WP_Query( array(
		'post_type'           => 'product',
		'posts_per_page'      => 4,
		'post__not_in'        => $in_cart,
		'orderby'             => 'meta_value_num',
		'meta_key'            => 'total_sales',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'tax_query'           => array( array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'outofstock', 'exclude-from-catalog' ),
			'operator' => 'NOT IN',
		) ),
		'meta_query'          => array( array(
			'key'     => '_price',
			'value'   => array( 1, max( 5, $left ) ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL(10,2)',
		) ),
	) );
	if ( ! $q->have_posts() ) {
		wp_reset_postdata();
		return;
	}
	echo '<section class="lm-nudge"><h2>Još ' . wp_kses_post( wc_price( $left ) ) . ' do besplatne dostave</h2>';
	echo '<p>Dodajte nešto od ovoga i dostava je na nas:</p><ul class="products lm-nudge-list">';
	while ( $q->have_posts() ) {
		$q->the_post();
		wc_get_template_part( 'content', 'product' );
	}
	echo '</ul></section>';
	wp_reset_postdata();
}, 20 );

// ---------------------------------------------------------------------------
// 4) Oznake "Najprodavanije" i "Jos samo X komada".
// ---------------------------------------------------------------------------
function lm_low_stock_qty() {
	return 3;
}
function lm_is_bestseller( $product ) {
	$sales = (int) get_post_meta( $product->get_id(), 'total_sales', true );
	return $sales >= 10;
}
function lm_stock_note( $product ) {
	// Isključeno 20.10.2026 (Markova odluka: količina na stanju se ne prikazuje kupcima;
	// end-to-end test je našao oznaku „Još samo 2 komada“ u katalogu i na artiklu).
	if ( ! apply_filters( 'lm_prikazi_preostalo', false ) ) {
		return '';
	}
	if ( ! $product->managing_stock() || ! $product->is_in_stock() ) {
		return '';
	}
	$qty = $product->get_stock_quantity();
	if ( null === $qty || $qty <= 0 || $qty > lm_low_stock_qty() ) {
		return '';
	}
	$word = ( 1 === (int) $qty ) ? 'komad' : 'komada';
	return 'Još samo ' . (int) $qty . ' ' . $word;
}
// U katalogu: oznaka preko slike.
add_action( 'woocommerce_before_shop_loop_item_title', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	$stock = lm_stock_note( $product );
	if ( $stock ) {
		echo '<span class="lm-badge lm-badge-stock">' . esc_html( $stock ) . '</span>';
	} elseif ( lm_is_bestseller( $product ) ) {
		echo '<span class="lm-badge lm-badge-top">Najprodavanije</span>';
	}
}, 11 );
// Na stranici proizvoda: red iznad naslova.
add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	$out = array();
	if ( lm_is_bestseller( $product ) ) {
		$out[] = '<span class="lm-flag lm-flag-top">Najprodavanije</span>';
	}
	$stock = lm_stock_note( $product );
	if ( $stock ) {
		$out[] = '<span class="lm-flag lm-flag-stock">' . esc_html( $stock ) . '</span>';
	}
	if ( $out ) {
		echo '<p class="lm-flags">' . implode( ' ', $out ) . '</p>';
	}
}, 4 );

// ---------------------------------------------------------------------------
// 6) Stil za sve gore.
// ---------------------------------------------------------------------------
add_action( 'wp_head', function () {
	?>
<style id="lm-prodaja">
.lm-instal{margin:-4px 0 14px;font-size:14px;color:#75666C}
.lm-instal strong{color:#2A2226;font-weight:700}
.lm-ask{display:inline-flex;align-items:center;gap:9px;margin:4px 0 18px;padding:11px 16px;border:1px solid #EADDDF;border-radius:999px;font-size:14px;font-weight:600;color:#2A2226;background:#fff;transition:border-color .15s,color .15s}
.lm-ask:hover{border-color:#25D366;color:#1C7A3E}
.lm-ask svg{width:19px;height:19px;fill:none;stroke:#25D366;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;flex:none}
.lm-flags{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 8px}
.lm-flag{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:4px 10px;border-radius:999px}
.lm-flag-top{background:#F8E4EC;color:#A8264F}
.lm-flag-stock{background:#FBEFD9;color:#8A5200}
.lm-badge{position:absolute;left:10px;top:10px;z-index:3;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;padding:4px 9px;border-radius:999px;pointer-events:none}
.lm-badge-top{background:#A8264F;color:#fff}
.lm-badge-stock{background:#fff;color:#8A5200;box-shadow:0 1px 6px rgba(42,34,38,.18)}
.product-labels~.lm-badge,.product-labels+.lm-badge{top:auto;bottom:10px}
.lm-nudge{margin:34px 0 10px;padding:26px 0 0;border-top:1px solid #EADDDF}
.lm-nudge h2{font-size:22px;margin:0 0 4px}
.lm-nudge>p{color:#75666C;margin:0 0 18px}
@media (max-width:767px){.lm-ask{width:100%;justify-content:center}}
</style>
	<?php
}, 20 );


// ---------------------------------------------------------------------------
// 7) Prevod za liste zelja (vise lista) i stil za tekst na stranici Lista zelja.
// ---------------------------------------------------------------------------
function lm_wl_map() {
	return array(
		'Your wishlists'        => 'Vaše liste želja',
		'Create wishlist'       => 'Napravi listu',
		'Edit name'             => 'Preimenuj',
		'Wishlist name'         => 'Naziv liste',
		'Save'                  => 'Sačuvaj',
		'Cancel'                => 'Odustani',
		'Move'                  => 'Premjesti',
		'Remove'                => 'Ukloni',
		'Delete'                => 'Obriši',
		'Copy link'             => 'Kopiraj link',
		'Link copied'           => 'Link je kopiran',
		'Move to wishlist'      => 'Premjesti u listu',
		'Select wishlist'       => 'Izaberite listu',
	);
}
add_filter( 'gettext', function ( $t, $text, $domain ) {
	$m = lm_wl_map();
	return isset( $m[ $text ] ) ? $m[ $text ] : $t;
}, 30, 3 );
add_filter( 'gettext_with_context', function ( $t, $text, $ctx, $domain ) {
	$m = lm_wl_map();
	return isset( $m[ $text ] ) ? $m[ $text ] : $t;
}, 30, 4 );
add_action( 'wp_head', function () {
	echo '<style id="lm-registry-css">.lm-registry{width:100%;max-width:none;margin:40px 0 0}.wd-my-account-wrapper>.lm-registry{grid-column:1/-1}.lm-registry h2{font-size:24px;margin:26px 0 10px}.lm-registry p{margin:0 0 12px}.lm-registry ul{margin:0 0 14px;padding-left:20px}.lm-registry li{margin-bottom:6px}.lm-reg-lead{color:#75666C}</style>';
}, 21 );

// 10) Zatamnjenje iza panela pretrage i poruke o obaveznim poljima na crnogorskom.
add_action( 'wp_footer', function () {
	echo '<script id="lm-search-ux">(function(){var W=1025;function box(){return document.querySelector(\'.whb-header .wd-header-search.wd-display-dropdown\');}function veil(on){var v=document.querySelector(\'.lm-veil\');if(on){if(!v){v=document.createElement(\'div\');v.className=\'lm-veil\';document.body.appendChild(v);}}else if(v){v.remove();}}function sync(){var h=box();if(!h)return;var on=h.classList.contains(\'lm-open\')&&window.innerWidth>=W;document.body.classList.toggle(\'lm-search-on\',on);veil(on);}function start(){var h=box();if(!h){return;}new MutationObserver(sync).observe(h,{attributes:true,attributeFilter:[\'class\']});sync();document.addEventListener(\'click\',function(e){if(document.body.classList.contains(\'lm-search-on\')&&!e.target.closest(\'.wd-search-dropdown\')&&!e.target.closest(\'.wd-header-search\')){document.body.classList.remove(\'lm-search-on\');veil(false);}},true);}function poruke(){document.querySelectorAll(\'input[name="s"]\').forEach(function(i){if(i.lmV)return;i.lmV=1;i.addEventListener(\'invalid\',function(){i.setCustomValidity(\'Upišite šta tražite.\');});i.addEventListener(\'input\',function(){i.setCustomValidity(\'\');});});document.querySelectorAll(\'input[required]:not([name="s"]),select[required],textarea[required]\').forEach(function(i){if(i.lmV2)return;i.lmV2=1;i.addEventListener(\'invalid\',function(){i.setCustomValidity(i.value?\'Unesite podatak u traženom formatu.\':\'Ovo polje je obavezno.\');});i.addEventListener(\'input\',function(){i.setCustomValidity(\'\');});i.addEventListener(\'change\',function(){i.setCustomValidity(\'\');});});}function init(){start();poruke();}if(document.readyState!==\'loading\')init();else document.addEventListener(\'DOMContentLoaded\',init);document.addEventListener(\'click\',poruke,true);})();</script>';
}, 99 );
add_action( 'wp_head', function () {
	echo '<style id="lm-search-veil">@media (min-width:1025px){.lm-veil{position:fixed;inset:0;background:rgba(42,34,38,.38);z-index:360;animation:lmVeil .2s ease}@keyframes lmVeil{from{opacity:0}to{opacity:1}}.whb-header .wd-header-search .wd-search-dropdown{max-height:calc(100vh - 150px);overflow-y:auto;overscroll-behavior:contain}}</style>';
}, 22 );

// 11) Brzina: stilovi platnog prometa samo gdje trebaju + preload prve slike slajdera.
function lm_bez_bankarta() {
	if ( is_admin() ) { return; }
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() || is_wc_endpoint_url( 'order-received' ) ) ) { return; }
	wp_dequeue_style( 'bankart_style' );
	wp_deregister_style( 'bankart_style' );
}
add_action( 'wp_enqueue_scripts', 'lm_bez_bankarta', 9999 );
add_action( 'wp_print_styles', 'lm_bez_bankarta', 1 );
add_action( 'wp_head', function () {
	if ( ! is_front_page() ) { return; }
	$up = home_url( '/wp-content/uploads/2026/10/' );
	echo '<link rel="preload" as="image" href="' . esc_url( $up . 'lm-slajd1-prodavnica-desktop-v4.webp' ) . '" media="(min-width: 769px)" fetchpriority="high">' . "\n";
	echo '<link rel="preload" as="image" href="' . esc_url( $up . 'lm-slajd1-prodavnica-mobilni-v4.webp' ) . '" media="(max-width: 768px)" fetchpriority="high">' . "\n";
}, 1 );
// 11b) Početna: najveći element je pozadina slajdera (preload iznad), a ne slika.
// WordPress bi prvoj slici u sadržaju (pločica kategorije ispod slajdera) dao fetchpriority="high"
// bez lazy učitavanja, pa bi se skidala u punoj veličini i otimala protok slajderu.
add_filter( 'wp_get_loading_optimization_attributes', function ( $attrs, $tag ) {
	if ( 'img' === $tag && ! is_admin() && is_front_page() && isset( $attrs['fetchpriority'] ) && 'high' === $attrs['fetchpriority'] ) {
		unset( $attrs['fetchpriority'] );
		$attrs['loading'] = 'lazy';
	}
	return $attrs;
}, 10, 2 );

// 13) Mobilna brzina: desktop stilovi teme se ne blokiraju na telefonu.
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( is_admin() || is_customize_preview() || 'base-desktop' !== $handle ) { return $tag; }
	return str_replace( "media='all'", "media='(min-width: 769px)'", str_replace( 'media="all"', 'media="(min-width: 769px)"', $tag ) );
}, 10, 2 );

// 14) Proizvodi iz teksta na kraju svakog clanka.
function lm_proizvodi_za_clanak( $slug ) {
	$map = array(
		'sta-spakovati-u-porodiliste'      => array( 'naslov' => 'Za torbu za porodilište', 'trazi' => 'grudnjak za dojenje' ),
		'kako-izabrati-grudnjak-za-dojenje' => array( 'naslov' => 'Grudnjaci i sve za dojenje', 'trazi' => 'grudnjak' ),
		'kalendar-trudnoce-sta-vam-treba'   => array( 'naslov' => 'Za svaki trimestar', 'trazi' => 'trudnice' ),
		'garderoba-za-trudnice-sta-vam-treba' => array( 'naslov' => 'Trudnička garderoba', 'trazi' => 'trudnice' ),
		'spectra-pumpice-za-izdajanje'      => array( 'naslov' => 'Spectra pumpice', 'trazi' => 'Spectra' ),
		'haakaa-silikonska-pumpica-za-dojenje' => array( 'naslov' => 'Haakaa pumpice', 'trazi' => 'Haakaa' ),
		'skola-za-trudnice-ready-mom-podgorica' => array( 'naslov' => 'Za pripremu i prve dane', 'trazi' => '' ),
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : array( 'naslov' => 'Iz naše prodavnice', 'trazi' => '' );
}
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) { return $content; }
	if ( ! function_exists( 'wc_get_products' ) ) { return $content; }
	$cfg  = lm_proizvodi_za_clanak( get_post_field( 'post_name', get_the_ID() ) );
	$args = array( 'status' => 'publish', 'limit' => 4, 'orderby' => 'popularity', 'visibility' => 'catalog' );
	if ( $cfg['trazi'] ) { $args['s'] = $cfg['trazi']; }
	$proizvodi = wc_get_products( $args );
	if ( count( $proizvodi ) < 2 ) {
		unset( $args['s'] );
		$proizvodi = wc_get_products( $args );
	}
	if ( count( $proizvodi ) < 2 ) { return $content; }
	$ids = array();
	foreach ( $proizvodi as $p ) { $ids[] = $p->get_id(); }
	$blok  = '<section class="lm-clanak-proizvodi">';
	$blok .= '<h2>' . esc_html( $cfg['naslov'] ) . '</h2>';
	$blok .= '<p class="lm-cp-lead">Sve iz teksta imate kod nas — online ili u butiku u Podgorici.</p>';
	$blok .= do_shortcode( '[products ids="' . implode( ',', $ids ) . '" columns="4" limit="4"]' );
	$blok .= '<p class="lm-cp-vise"><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">Pogledajte cijelu ponudu →</a></p>';
	$blok .= '</section>';
	return $content . $blok;
}, 20 );
add_action( 'wp_head', function () {
	if ( ! is_singular( 'post' ) ) { return; }
	echo '<style id="lm-clanak-proizvodi-css">.lm-clanak-proizvodi{margin-top:42px;padding-top:30px;border-top:1px solid #EADDDF}.lm-clanak-proizvodi h2{margin:0 0 6px}.lm-cp-lead{color:#75666C;margin:0 0 20px}.lm-cp-vise{margin-top:6px}.lm-cp-vise a{font-weight:700;color:#C01157}</style>';
}, 23 );



define( 'LM_BREND_CSS', ':root{--lm-krem:#fdf2ec;--lm-blijedo:#f6ded4;--lm-roze:#db9e99;--lm-roze-tamna:#a06f68;--lm-tirkiz:#659699;--lm-mint:#d6ede9;--lm-smedja:#6d4e39;--lm-smedja-tamna:#553c2c;--lm-tekst:#7a6250;--lm-linija:#e7d7cc}h1,h2,h3,h4,h5,h6,.title,.wd-entities-title,.wd-post-title,.section-title,.woocommerce-loop-product__title,.product_title,.wd-el-title,.widget-title,.widgettitle{font-family:"Clash Display","Raleway",system-ui,sans-serif!important;font-weight:600!important;color:var(--lm-smedja);letter-spacing:-.01em}h1,.product_title,.page-title{line-height:1.12!important}h2{line-height:1.18!important}h3{line-height:1.25!important}h4{line-height:1.3!important}h5{line-height:1.35!important}h6{line-height:1.4!important}.wd-entities-title,.woocommerce-loop-product__title{font-size:16px!important;line-height:1.35!important;font-weight:500!important}.widget-title,.widgettitle{font-size:14px!important;letter-spacing:.08em}body,p,li,td,input,select,textarea,.wd-product-cats,.woocommerce-breadcrumb{font-family:"Raleway",system-ui,sans-serif}p{font-size:16px;line-height:1.7}.wd-product-cats,.woocommerce-breadcrumb,.posted_in,.wd-post-meta,small,.wd-entry-meta{font-size:14px}.title-subtitle,.lm-nl-eyebrow,.wd-el-subtitle,.lm-eyebrow{font-family:"Raleway",system-ui,sans-serif!important;font-size:13px!important;font-weight:700!important;letter-spacing:.16em!important;text-transform:uppercase!important;line-height:1.4!important;margin-bottom:10px!important}.price,.amount,.woocommerce-Price-amount{font-family:"Raleway",system-ui,sans-serif;font-size:17px;font-weight:700}.price del,del .amount{font-weight:400}.price ins,ins .amount{text-decoration:none}.btn,.button,button,input[type=submit],.wd-buy-now-btn,.single_add_to_cart_button,.checkout-button,.elementor-button,.wp-element-button,.wd-btn{font-family:"Raleway",system-ui,sans-serif!important;font-size:13px!important;font-weight:700!important;letter-spacing:.06em!important;text-transform:uppercase!important;border-radius:6px!important}.btn-style-bordered,.button.alt-bordered{border:1px solid var(--lm-smedja)!important}.btn-style-link,.wd-more-link,.lm-cp-vise a{background:none!important;padding-left:0!important;padding-right:0!important}.btn-style-link:hover,.wd-more-link:hover{text-decoration:underline!important}.product-label,.onsale,.wd-product-label,.lm-badge-top,.lm-flag-top{background-color:var(--lm-roze)!important;color:var(--lm-smedja)!important;font-family:"Raleway",sans-serif!important;font-weight:700!important;letter-spacing:.06em!important;text-transform:uppercase!important}.product-label.out-of-stock,.wd-product-label.out-of-stock{background-color:#d8cfc8!important;color:#6b5b50!important}a{color:var(--lm-smedja)}a:hover{color:var(--lm-smedja-tamna)}.wd-nav-main>li>a,.whb-header a{color:var(--lm-smedja)}.lm-trust{background:var(--lm-blijedo)!important}.lm-nudge h2{color:var(--lm-smedja)}.wd-search-dropdown .wd-search-requests ul a{background:var(--lm-blijedo)!important;color:var(--lm-smedja)!important}.whb-header .wd-search-dropdown .searchform input[type=text]{background:var(--lm-krem)!important} @media (max-width:768px){p{font-size:15px}.title-subtitle,.lm-nl-eyebrow,.wd-el-subtitle,.lm-eyebrow{font-size:12px!important}}' );
// 15) Brend: Clash Display + Raleway, paleta i hijerarhija dugmadi.
add_action( 'wp_head', function () {
	// Clash Display je lokalno (18.10.2026), u uploads/lm-fonts/ (preuzet sa fontshare.com).
	// Koriste se samo debljine 500 (nazivi artikala) i 600 (naslovi).
	$f = content_url( 'uploads/lm-fonts/' );
	echo '<link rel="preload" href="' . esc_url( $f . 'clash-display-600.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	echo '<style id="lm-font-clash">'
		. '@font-face{font-family:"Clash Display";src:url("' . esc_url( $f . 'clash-display-500.woff2' ) . '") format("woff2");font-weight:500;font-style:normal;font-display:swap}'
		. '@font-face{font-family:"Clash Display";src:url("' . esc_url( $f . 'clash-display-600.woff2' ) . '") format("woff2");font-weight:600 700;font-style:normal;font-display:swap}'
		. '</style>' . "\n";
	echo '<style id="lm-brend">' . LM_BREND_CSS . '</style>' . "\n";
}, 26 );

// 16) Brend: dopune preko starih ruzicastih vrijednosti.
add_action( 'wp_head', function () {
	echo '<style id="lm-brend-dopune">.whb-header .wd-header-search.lm-open > a{color:var(--lm-smedja)!important}#slide-3240 .wd-slide-bg,#slide-3267 .wd-slide-bg,#slide-3282 .wd-slide-bg{background-color:var(--lm-krem)!important}.lm-trust li{color:var(--lm-smedja)!important}.lm-secure-pay{color:var(--lm-tekst)!important}.lm-secure-pay svg{stroke:var(--lm-smedja)!important}.whb-header .wd-search-dropdown .wd-search-requests{border-bottom-color:var(--lm-linija)!important}.lm-instal{color:var(--lm-tekst)!important}.lm-instal strong{color:var(--lm-smedja)!important}.lm-nudge{border-top-color:var(--lm-linija)!important}.lm-clanak-proizvodi{border-top-color:var(--lm-linija)!important}.lm-ask{border-color:var(--lm-linija)!important;color:var(--lm-smedja)!important}.lm-ask-box{margin:4px 0 18px}.lm-ask-t{margin:0 0 8px;font-size:14px;font-weight:600;color:var(--lm-smedja)}.lm-ask-row{display:flex;gap:10px}.lm-ask-row .lm-ask{flex:1 1 0;width:auto!important;margin:0!important}.lm-ask-vb svg{stroke:#7360F2!important}.lm-ask-vb:hover{border-color:#7360F2!important;color:#5B4BC4!important}.lm-registry{color:var(--lm-tekst)}'
		// Ikonica korpe u traci na slici artikla: bez pozadine dugmeta (18.10.2026).
		. '.wd-action-btn.wd-style-icon>a.button{background:none!important;border:0!important;box-shadow:none!important;border-radius:0!important;color:var(--lm-smedja)!important;padding:0!important;min-height:0!important}.wd-action-btn.wd-style-icon>a.button:hover{color:var(--lm-roze-tamna)!important}</style>';
}, 27 );

// 17) Skala naslova: sadrzaj krupnije, navigacija i footer sitnije.
add_action( 'wp_head', function () {
	echo '<style id="lm-skala">h1,.product_title,.page-title{font-size:40px!important}h2{font-size:30px!important}h3{font-size:23px!important}h4{font-size:19px!important}h5{font-size:17px!important}h6{font-size:16px!important}.wd-footer h1,.wd-footer h2,.wd-footer h3,.wd-footer h4,.wd-footer h5,.wd-footer h6,.wd-footer .wp-block-wd-title{font-size:15px!important;letter-spacing:.1em!important;text-transform:uppercase!important;line-height:1.4!important;margin-bottom:14px!important}.wd-prefooter h1,.wd-prefooter h2,.wd-prefooter h3,.wd-prefooter h4,.wd-prefooter .wp-block-wd-title{font-size:17px!important;line-height:1.4!important}.wd-dropdown-menu h1,.wd-dropdown-menu h2,.wd-dropdown-menu h3,.wd-dropdown-menu h4,.wd-dropdown-menu .wp-block-wd-title{font-size:15px!important;letter-spacing:.08em!important;text-transform:uppercase!important;line-height:1.4!important}.wd-search-dropdown h1,.wd-search-dropdown h2,.wd-search-dropdown h3,.wd-search-dropdown h4,.wd-search-dropdown .wp-block-wd-title,.wd-search-dropdown .wd-search-title{font-size:16px!important;line-height:1.35!important}.wd-search-dropdown .wd-post-title,.wd-search-dropdown .wd-entities-title{font-size:14px!important}.wd-sidebar .widget-title,.widget .widget-title,.widget .widgettitle{font-size:14px!important;letter-spacing:.1em!important}@media (max-width:1024px){h1,.product_title,.page-title{font-size:32px!important}h2{font-size:26px!important}h3{font-size:21px!important}h4{font-size:18px!important}}@media (max-width:768px){h1,.product_title,.page-title{font-size:28px!important}h2{font-size:23px!important}h3{font-size:19px!important}h4{font-size:17px!important}h5{font-size:16px!important}.wd-footer h1,.wd-footer h2,.wd-footer h3,.wd-footer h4,.wd-footer .wp-block-wd-title{font-size:14px!important}}</style>';
}, 28 );

// 18) Vise boja iz palete: tirkizna kao druga boja.
add_action( 'wp_head', function () {
	echo '<style id="lm-boje">.mfp-content .wd-popup,.wd-popup-newsletter{background-color:var(--lm-blijedo)!important}.lm-nudge,.lm-clanak-proizvodi{background:var(--lm-krem);padding:26px 24px 20px!important;border-radius:14px;border-top:0!important;margin-top:36px}.lm-registry{background:var(--lm-krem);padding:26px 28px;border-radius:14px}.woocommerce-message,.woocommerce-info,.wd-notice{background-color:var(--lm-mint)!important;color:#35595c!important;border-color:#bcdcd8!important}.woocommerce-message a,.woocommerce-info a{color:#35595c!important}.woocommerce-error{background-color:#f7e2de!important;color:#8a3a2d!important;border-color:#e9c4bc!important}.wd-entry-content p a:not(.btn):not(.button),.entry-content p a:not(.btn):not(.button),.wd-entry-content li a:not(.btn),.entry-content li a:not(.btn),.lm-registry p a,.lm-registry li a{color:#6d4e39!important;text-decoration:underline;text-underline-offset:3px;text-decoration-thickness:1px}.wd-entry-content p a:hover,.entry-content p a:hover{color:var(--lm-smedja)!important}.wd-footer a,.wd-footer a:hover{text-decoration:none!important}.wd-post-title a,.wd-entities-title a{color:var(--lm-smedja)!important;text-decoration:none!important}.lm-badge-stock,.lm-flag-stock{background-color:var(--lm-tirkiz)!important;color:#fff!important}.lm-clanak-proizvodi h2{color:var(--lm-smedja)!important}</style>';
}, 29 );

// 19) Kontakt: mapa ispod sadrzaja, uklopljena u stil.
add_action( 'wp_head', function () {
	if ( ! is_page( 'kontakt' ) ) { return; }
	echo '<style id="lm-mapa">.elementor-element-68d0e93{background-color:var(--lm-krem)!important;padding:54px 20px 60px!important;margin-top:20px}.elementor-element-68d0e93>.e-con-inner{max-width:1200px!important;margin:0 auto!important}.elementor-element-ff44223 .elementor-custom-embed{border-radius:16px;overflow:hidden;border:1px solid var(--lm-linija);box-shadow:0 10px 30px rgba(109,78,57,.08)}.elementor-element-ff44223 iframe{height:380px!important;display:block;filter:saturate(.85)}@media (max-width:768px){.elementor-element-68d0e93{padding:34px 16px 40px!important}.elementor-element-ff44223 iframe{height:280px!important}}</style>';
}, 30 );

// 20) Raspored boja D: bijela i roze nose sajt, smedja minimalno, tirkizna gore i u footeru.
add_action( 'wp_head', function () {
	echo '<style id="lm-raspored-d">:root{--lm-roze-jaka:#a05c57;--lm-roze-jaka-h:#8a4b47;--lm-tirkiz-gore:#4a7a7d;--lm-tirkiz-footer:#3f6265}.whb-top-bar{background-color:var(--lm-tirkiz-gore)!important}.whb-top-bar,.whb-top-bar a,.whb-top-bar *{color:#fff!important}.wd-footer,.wd-footer p,.wd-footer li,.wd-footer .wp-block-wd-paragraph,.wd-footer .textwidget{color:#d4e5e4!important}.wd-footer .wp-block-wd-title,.wd-footer .widget-title,.wd-footer .widgettitle,.wd-footer h2,.wd-footer h3,.wd-footer h4{color:#fff!important}.wd-footer a{color:#e3efee!important}.wd-footer .wd-entry-content a:not(.btn):not(.button),.wd-footer .entry-content a:not(.btn):not(.button),.wd-footer .wp-block-wd-menu-list-item a,.wd-footer li a,.wd-footer p a{color:#e3efee!important;text-decoration:none!important}.wd-footer a:hover,.wd-footer .wd-entry-content a:not(.btn):hover,.wd-footer li a:hover{color:#fff!important}.wd-footer .wp-block-wd-image img{filter:brightness(0) invert(1)}.wd-footer svg{stroke:#d4e5e4!important}.wd-footer .wd-social-icons a,.wd-footer .wd-social-icon{background-color:rgba(255,255,255,.16)!important;border-color:transparent!important;color:#fff!important}.wd-footer .wd-social-icon:hover{background-color:#fff!important;color:var(--lm-tirkiz-footer)!important}.wd-footer .wd-widget,.wd-footer hr{border-color:rgba(255,255,255,.18)!important}.wd-copyrights{background-color:var(--lm-tirkiz-footer)!important;color:#c6dcda!important;border-top:1px solid rgba(255,255,255,.14)!important}.wd-copyrights a{color:#e3efee!important}.wd-prefooter{background-color:var(--lm-blijedo)!important}.wd-prefooter .wp-block-wd-section,.wd-footer .wp-block-wd-section,.wd-footer .wp-block-wd-section.color-scheme-dark{background-color:transparent!important}.wd-footer .wp-block-wd-section .wp-block-wd-paragraph{color:#d4e5e4!important}.wd-prefooter .wp-block-wd-title,.wd-prefooter .wp-block-wd-paragraph{color:var(--lm-smedja)!important}.wd-prefooter svg,.wd-prefooter svg *{stroke:var(--lm-roze-jaka)!important;fill:none!important}.wd-prefooter .wd-icon,.wd-prefooter i{color:var(--lm-roze-jaka)!important}.btn:not(.btn-style-bordered):not(.btn-style-link),.button:not(.btn-style-bordered),button:not(.btn-style-bordered):not(.wd-tools-icon),input[type=submit],.single_add_to_cart_button,.checkout-button,.elementor-button:not(.elementor-button-link),.wp-element-button,.wd-buy-now-btn{background-color:var(--lm-roze-jaka)!important;border-color:var(--lm-roze-jaka)!important;color:#fff!important}.btn:not(.btn-style-bordered):hover,.button:not(.btn-style-bordered):hover,input[type=submit]:hover,.single_add_to_cart_button:hover,.checkout-button:hover,.elementor-button:hover,.wp-element-button:hover{background-color:var(--lm-roze-jaka-h)!important;border-color:var(--lm-roze-jaka-h)!important;color:#fff!important}.btn-style-bordered,.button.alt-bordered{background-color:transparent!important;color:var(--lm-roze-jaka)!important;border-color:#cf9b95!important}.btn-style-bordered:hover,.button.alt-bordered:hover{background-color:var(--lm-roze-jaka)!important;border-color:var(--lm-roze-jaka)!important;color:#fff!important}.price,.amount,.price ins,ins .amount,.woocommerce-Price-amount{color:var(--lm-roze-jaka)!important}.price del,del .amount{color:#a89588!important}.title-subtitle,.wd-el-subtitle,.lm-eyebrow,.lm-nl-eyebrow{color:var(--lm-roze-jaka)!important}.lm-trust svg{stroke:var(--lm-roze-jaka)!important}.lm-toast,.lm-chat-btn{background:var(--lm-roze-jaka)!important}.lm-chat-btn:hover{background:var(--lm-roze-jaka-h)!important}.wd-progress-bar .wd-progress-bar-inner,.woocommerce-shipping-progress-bar span{background-color:var(--lm-roze-jaka)!important}.wd-search-dropdown .wd-search-requests ul a:hover{background:var(--lm-roze-jaka)!important;color:#fff!important}#slide-35678 .wd-slide-bg{background-color:var(--lm-roze-tamna)!important}.wd-nav-main>li>a:hover,.whb-header .wd-tools-element:hover{color:var(--lm-roze-jaka)!important}.lm-cp-vise a,.btn-style-link,.wd-more-link{color:var(--lm-roze-jaka)!important}</style>';
}, 31 );

// 21) Skola i butik: bijeli tekst na tirkiznoj koloni.
add_action( 'wp_head', function () {
	echo '<style id="lm-skola-butik">.elementor-element-lms0002 .wd-el-title,.elementor-element-lms0002 h1,.elementor-element-lms0002 h2,.elementor-element-lms0002 h3,.elementor-element-lms0002 h4,.elementor-element-lms0002 p,.elementor-element-lms0002 .wd-entities-title{color:#fff!important}.elementor-element-lms0002 .title-subtitle,.elementor-element-lms0002 .wd-el-subtitle,.elementor-element-lms0002 .liner-continer{color:#a8ccca!important}.elementor-element-lms0002 .btn:not(.btn-style-bordered):not(.btn-style-link),.elementor-element-lms0002 a.btn,.elementor-element-lms0002 .button{background-color:#fff!important;border-color:#fff!important;color:#3f6265!important}.elementor-element-lms0002 .btn:hover,.elementor-element-lms0002 .wd-btn:hover{background-color:#e3efee!important;color:#3f6265!important}@media (max-width:768px){.elementor-element-lms0002,.elementor-element-lms0005{width:100%!important}}</style>';
}, 32 );

// 22) Razmaci: pocetna bez praznog pojasa ispod menija, 404 pretraga uza.
add_action( 'wp_head', function () {
	echo '<style id="lm-razmaci">body.home .wd-content-layout{padding-top:0!important;padding-bottom:0!important}.wd-post-cat.wd-style-with-bg{background-color:var(--lm-roze-jaka)!important}.wd-post-cat.wd-style-with-bg a,.wd-post-cat.wd-style-with-bg a:hover{color:#fff!important;text-decoration:none!important}.wd-post-cat:not(.wd-style-with-bg) a{color:var(--lm-roze-jaka)!important;text-decoration:none!important}.wd-side-hidden .searchform .searchsubmit span,.mobile-nav .searchform .searchsubmit span{position:absolute!important;width:1px!important;height:1px!important;overflow:hidden!important;clip:rect(0 0 0 0)!important;white-space:nowrap!important}.wd-side-hidden .searchform .searchsubmit,.mobile-nav .searchform .searchsubmit{background-image:url("data:image/svg+xml;utf8,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%23ffffff%27 stroke-width=%272%27 stroke-linecap=%27round%27><circle cx=%2711%27 cy=%2711%27 r=%277%27/><line x1=%2716.2%27 y1=%2716.2%27 x2=%2721%27 y2=%2721%27/></svg>")!important;background-repeat:no-repeat!important;background-position:center!important;background-size:18px 18px!important;min-width:46px!important;padding:0!important}@media (max-width:1024px){.header-banner,.wd-hb,.wd-header-banner{height:auto!important;min-height:38px!important;display:flex!important;align-items:center!important;justify-content:center!important;padding:8px 14px!important}.header-banner p,.wd-hb p,.wd-header-banner p{font-size:13px!important;line-height:1.3!important;margin:0!important}}@media (max-width:480px){.header-banner p,.wd-hb p,.wd-header-banner p{font-size:12px!important}}body.page-id-35870 .wd-content-layout{padding-top:30px!important}body.page-id-35870 .wd-search-form{width:100%!important;max-width:620px!important;margin-left:auto!important;margin-right:auto!important}body.page-id-35870 .wd-search-form form.searchform{width:100%!important}body.page-id-35870 .wd-search-form .searchsubmit{width:auto!important;min-width:110px;padding-left:18px!important;padding-right:18px!important;white-space:nowrap;overflow:visible}body.page-id-35870 .wd-search-form input.s{padding-right:126px!important}</style>';
}, 33 );

// 23) NLB rate: napomena na proizvodu, u korpi i na placanju.
function lm_nlb_rate_html() {
	return '<div class="lm-nlb-rate"><svg width="18" height="18" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg><span><strong>Plaćanje NLB karticom na rate do 12 mjeseci.</strong> Broj rata birate prilikom plaćanja.</span></div>';
}
// Rate se pominju tek od 30 € (20.10.2026): kod jeftinih artikala napomena samo zauzima prostor.
function lm_nlb_prag() {
	return 30;
}
function lm_nlb_korpa_ok() {
	return function_exists( 'WC' ) && WC()->cart && (float) WC()->cart->get_total( 'edit' ) >= lm_nlb_prag();
}
add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	$cijena = $product->is_type( 'variable' ) ? (float) $product->get_variation_price( 'max', true ) : (float) wc_get_price_including_tax( $product );
	if ( $cijena >= lm_nlb_prag() ) {
		echo lm_nlb_rate_html();
	}
}, 11 );
add_action( 'woocommerce_proceed_to_checkout', function () { if ( lm_nlb_korpa_ok() ) echo lm_nlb_rate_html(); }, 25 );
add_action( 'woocommerce_review_order_before_payment', function () { if ( lm_nlb_korpa_ok() ) echo lm_nlb_rate_html(); }, 10 );
add_action( 'wp_head', function () {
	echo '<style id="lm-nlb">.lm-nlb-rate{background:var(--lm-blijedo);border-radius:10px;padding:11px 14px;margin:14px 0;font-size:13px;line-height:1.5;color:var(--lm-smedja);display:flex;align-items:flex-start;gap:9px}.lm-nlb-rate strong{color:var(--lm-smedja);font-weight:700}.lm-nlb-rate svg{flex:0 0 auto;margin-top:1px;stroke:var(--lm-roze-jaka);fill:none}@media (max-width:768px){.lm-nlb-rate{font-size:12.5px;padding:10px 12px}}</style>';
}, 34 );

// 24) Filteri: tanak okvir oko kolor tackica da se bijela vidi.
add_action( 'wp_head', function () {
	echo '<style id="lm-swatch-okvir">.wd-swatches-filter .wd-swatch-wrap .wd-swatch,.wd-swatches-filter .wd-swatch{box-shadow:inset 0 0 0 1px rgba(109,78,57,.22)!important}.wd-swatches-filter .wd-swatch-wrap.wd-active .wd-swatch{box-shadow:inset 0 0 0 1px rgba(109,78,57,.22),0 0 0 2px var(--lm-roze-jaka)!important}</style>';
}, 35 );

// 25) Pristupacnost: kontrast, redoslijed naslova, H1 na korpi i placanju.
add_action( 'wp_head', function () {
	echo '<style id="lm-a11y">.elementor-element-lms0003 .title-subtitle{color:#d6ede9!important}.elementor-element-lms0006 .title-subtitle{color:#8a4b47!important}.elementor-element-lms0006 .title-after_title,.elementor-element-lms0006 .title-after_title p{color:#6d4e39!important}.wd-post-date,.wd-post-date .published,.wd-post-date .updated,.wd-post-date time{color:#7a6250!important}.wd-page-title.page-title{font-size:16px!important}.wd-page-title .wd-breadcrumbs{font-size:14px!important}.wd-single-post-header .wd-post-title{font-size:40px!important;line-height:1.15;max-width:860px;margin-left:auto;margin-right:auto}@media (max-width:1024px){.wd-single-post-header .wd-post-title{font-size:32px!important}}@media (max-width:768px){.wd-single-post-header .wd-post-title{font-size:26px!important;line-height:1.2}}.wd-content-area .wd-search-form .searchsubmit{width:auto!important;min-width:110px;padding-left:18px!important;padding-right:18px!important;white-space:nowrap;overflow:visible}.wd-content-area .wd-search-form input.s{padding-right:126px!important}.no-products-footer .wd-search-form{max-width:620px}.wd-content-area .wd-search-form .searchsubmit:after,body.page-id-35870 .wd-search-form .searchsubmit:after{display:none!important}</style>';
}, 36 );
add_filter( 'dynamic_sidebar_params', function ( $params ) {
	if ( isset( $params[0]['before_title'] ) ) {
		$params[0]['before_title'] = preg_replace( '/<h[3-6](\s|>)/', '<h2$1', $params[0]['before_title'] );
		$params[0]['after_title']  = preg_replace( '/<\/h[3-6]>/', '</h2>', $params[0]['after_title'] );
	}
	return $params;
} );
add_action( 'loop_start', function ( $q ) {
	static $lm_done = false;
	if ( $lm_done || is_admin() || ! $q->is_main_query() ) return;
	if ( is_home() || is_category() || is_tag() || is_author() || is_date() ) {
		$lm_done = true;
		echo '<h2 class="screen-reader-text">Članci</h2>';
	}
} );
function lm_skriveni_h1( $t ) { echo '<h1 class="screen-reader-text">' . esc_html( $t ) . '</h1>'; }
add_action( 'woocommerce_before_cart', function () { lm_skriveni_h1( 'Korpa' ); }, 1 );
add_action( 'woocommerce_cart_is_empty', function () { lm_skriveni_h1( 'Korpa' ); }, 1 );
add_action( 'woocommerce_before_checkout_form', function () { lm_skriveni_h1( 'Plaćanje' ); }, 1 );
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( 'No product categories exist.' === $text ) return 'Kategorije stižu uskoro.';
	return $t;
}, 20, 3 );

// 26) Prevod kupovine: poruke korpe, plaćanja i kartice (nađeno pri testnoj kupovini 8.10.).
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( is_admin() && ! wp_doing_ajax() ) return $t;
	static $m = array(
		'Billing %s'                       => '%s',
		'Please read and accept the terms and conditions to proceed with your order.' => 'Da biste poručili, potvrdite da prihvatate Uslove korišćenja.',
		'terms and conditions'             => 'Uslove korišćenja',
		'Note:'                            => 'Napomena:',
		'Shipping'                         => 'Dostava',
		'Coupon code applied successfully.' => 'Kupon je uspješno primijenjen.',
		'Shipping options will be updated during checkout.' => 'Opcije dostave biće ažurirane na plaćanju.',
		'Pay for order'                    => 'Plati porudžbinu',
		'Card holder'                      => 'Ime na kartici',
		'Card number'                      => 'Broj kartice',
		'Expiration date'                  => 'Datum isteka',
		'Number of instalments'            => 'Broj rata',
		'Number of instalments:'           => 'Broj rata:',
	);
	return isset( $m[ $text ] ) ? $m[ $text ] : $t;
}, 30, 3 );
add_filter( 'ngettext', function ( $t, $single, $plural, $number, $domain ) {
	if ( '%s has been added to your cart.' === $single ) return 'U korpu je dodato: %s.';
	return $t;
}, 30, 5 );
add_filter( 'gettext_with_context', function ( $t, $text, $context, $domain ) {
	if ( 'Billing %s' === $text ) return '%s';
	return $t;
}, 30, 4 );
add_filter( 'woocommerce_shipping_package_name', function () { return 'Dostava'; } );
