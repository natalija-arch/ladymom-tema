<?php
/**
 * Snippet #26: Lady Mom – CX dorade (gradovi, pretraga, popup, Pitaj nas)
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Lady Mom – CX dorade (9.10.2026): gradovi na plaćanju, pretraga, popup, „Pitaj nas“.

// 1) Gradovi Crne Gore sa poštanskim brojem (Pošta CG, glavne pošte).
function lm_gradovi() {
	return array(
		'Podgorica' => '81000', 'Tuzi' => '81206', 'Golubovci' => '81304', 'Zeta' => '81304', 'Danilovgrad' => '81410', 'Spuž' => '81412',
		'Nikšić' => '81400', 'Plužine' => '81435', 'Šavnik' => '81450', 'Žabljak' => '84220', 'Cetinje' => '81250',
		'Rijeka Crnojevića' => '81253', 'Virpazar' => '81305', 'Kolašin' => '81210', 'Mojkovac' => '84205', 'Bijelo Polje' => '84000',
		'Berane' => '84300', 'Andrijevica' => '84320', 'Plav' => '84325', 'Gusinje' => '84326', 'Rožaje' => '84310',
		'Petnjica' => '84312', 'Pljevlja' => '84210', 'Bar' => '85000', 'Sutomore' => '85355', 'Stari Bar' => '85354',
		'Ulcinj' => '85360', 'Budva' => '85310', 'Bečići' => '85316', 'Sveti Stefan' => '85315', 'Petrovac' => '85300',
		'Kotor' => '85330', 'Dobrota' => '85331', 'Prčanj' => '85335', 'Risan' => '85337', 'Tivat' => '85320',
		'Radovići' => '85323', 'Herceg Novi' => '85340', 'Igalo' => '85347', 'Zelenika' => '85346', 'Đenovići' => '85345',
		'Bijela' => '85343', 'Meljine' => '85348', 'Grahovo' => '81420',
	);
}
function lm_grad_kljuc( $s ) {
	$s = str_replace( array( 'đ', 'Đ' ), 'd', (string) $s );
	$s = preg_replace( '/\s+/', ' ', trim( mb_strtolower( remove_accents( $s ) ) ) );
	return str_replace( 'dj', 'd', $s );
}
add_filter( 'woocommerce_checkout_fields', function ( $f ) {
	foreach ( array( 'billing', 'shipping' ) as $g ) {
		if ( isset( $f[ $g ][ $g . '_city' ] ) ) {
			$f[ $g ][ $g . '_city' ]['custom_attributes']['list'] = 'lm-gradovi';
			$f[ $g ][ $g . '_city' ]['placeholder']                = 'Počnite da kucate, npr. Podgorica';
		}
	}
	return $f;
}, 100 );
add_action( 'woocommerce_after_checkout_form', function () {
	echo '<datalist id="lm-gradovi">';
	foreach ( lm_gradovi() as $g => $pb ) {
		echo '<option value="' . esc_attr( $g ) . '">';
	}
	echo '</datalist>';
} );
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	$map = array();
	foreach ( lm_gradovi() as $g => $pb ) {
		$map[ lm_grad_kljuc( $g ) ] = array( $g, $pb );
	}
	foreach ( array( 'billing', 'shipping' ) as $g ) {
		$city = call_user_func( array( $order, 'get_' . $g . '_city' ) );
		$k    = lm_grad_kljuc( $city );
		if ( '' === $k || ! isset( $map[ $k ] ) ) {
			continue;
		}
		call_user_func( array( $order, 'set_' . $g . '_city' ), $map[ $k ][0] );
		if ( ! call_user_func( array( $order, 'get_' . $g . '_postcode' ) ) ) {
			call_user_func( array( $order, 'set_' . $g . '_postcode' ), $map[ $k ][1] );
		}
	}
}, 20 );
add_filter( 'woocommerce_localisation_address_formats', function ( $f ) {
	$f['ME'] = "{name}\n{company}\n{address_1}\n{address_2}\n{postcode} {city}\n{country}";
	return $f;
} );

// 2) Tekstovi: nalog na plaćanju, pretraga, donja traka.
add_filter( 'gettext', function ( $t, $text, $domain ) {
	static $m = array(
		'Create an account?'  => 'Napravite nalog — sljedeći put su adresa i telefon već upisani',
		'Returning customer?' => 'Već imate nalog?',
		'Click here to login' => 'Prijavite se',
		'Results from blog'   => 'Iz bloga',
	);
	if ( isset( $m[ $text ] ) ) {
		return $m[ $text ];
	}
	if ( 'Sidebar' === $text && 'woodmart' === $domain && ! is_admin() ) {
		return 'Kategorije';
	}
	return $t;
}, 30, 3 );

// 3) Kad pretraga ili prodavnica nema rezultata: popularne pretrage, police i WhatsApp.
add_action( 'woocommerce_no_products_found', function () {
	$pop = function_exists( 'woodmart_get_opt' ) ? array_filter( array_map( 'trim', explode( "\n", (string) woodmart_get_opt( 'popular_requests' ) ) ) ) : array();
	echo '<div class="lm-nema">';
	if ( is_search() ) {
		echo '<p class="lm-nema-uvod">Probajte kraću riječ ili naziv brenda, ili izaberite nešto od ponuđenog.</p>';
	}
	if ( $pop ) {
		echo '<h3 class="lm-pk-naslov">Najčešće se traži</h3><div class="lm-pk-kat">';
		foreach ( array_slice( $pop, 0, 8 ) as $p ) {
			echo '<a href="' . esc_url( add_query_arg( array( 's' => $p, 'post_type' => 'product' ), home_url( '/' ) ) ) . '">' . esc_html( $p ) . '</a>';
		}
		echo '</div>';
	}
	if ( function_exists( 'lm_pk_kategorije' ) ) {
		echo '<h3 class="lm-pk-naslov">Police</h3><div class="lm-pk-kat">';
		foreach ( lm_pk_kategorije() as $slug => $naziv ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			$url  = ( $term && ! is_wp_error( $term ) ) ? get_term_link( $term ) : home_url( '/?product_cat=' . $slug );
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $naziv ) . '</a>';
		}
		echo '</div>';
	}
	$q   = is_search() ? get_search_query() : '';
	$txt = 'Zdravo, tražim' . ( $q ? ': ' . $q : ' ' );
	echo '<div class="lm-nema-wa"><p><strong>Niste našli?</strong> Pišite nam na WhatsApp ili Viber i provjerićemo da li imamo u butiku.</p>'
		. '<div class="lm-nema-btns"><a class="button lm-nema-btn" href="' . esc_url( 'https://wa.me/38267030655?text=' . rawurlencode( $txt ) ) . '" target="_blank" rel="noopener">WhatsApp</a>'
			. '<a class="button lm-nema-btn lm-nema-vb" href="viber://chat?number=%2B38267030655">Viber</a></div></div>';
	echo '</div>';
	echo '<script>(function(){var s=document.currentScript,n=s&&s.previousElementSibling,b=document.querySelector(".wd-blog-search-results");if(n&&b&&n.classList.contains("lm-nema")){b.parentNode.insertBefore(n,b);}})();</script>';
}, 20 );

// 4) Newsletter popup se ne prikazuje na korpi, plaćanju, nalogu i proizvodu.
add_filter( 'woodmart_option', function ( $v, $slug ) {
	if ( 'promo_popup' !== $slug || is_admin() || ! did_action( 'wp' ) ) {
		return $v;
	}
	if ( ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) || ( function_exists( 'is_product' ) && is_product() ) ) {
		return '0';
	}
	return $v;
}, 10, 2 );

// 5) „Pitaj nas“ u donjoj traci (WoodMart Button [1], link #lm-pitaj) otvara izbor WhatsApp / Viber / poziv.
add_action( 'wp_footer', function () {
	if ( is_admin() ) {
		return;
	}
	?>
<div class="lm-pitaj" id="lm-pitaj" hidden>
	<div class="lm-pitaj-bg" data-lm-zatvori></div>
	<div class="lm-pitaj-panel" role="dialog" aria-modal="true" aria-labelledby="lm-pitaj-t">
		<button type="button" class="lm-pitaj-x" data-lm-zatvori aria-label="Zatvori">&times;</button>
		<p class="lm-pitaj-t" id="lm-pitaj-t">Pitajte nas</p>
		<p class="lm-pitaj-s">Za veličinu, porudžbinu ili savjet. Butik radi pon–sub 9:30–21:00.</p>
		<a class="lm-pitaj-b lm-pitaj-wa" href="https://wa.me/38267030655" target="_blank" rel="noopener">WhatsApp</a>
		<a class="lm-pitaj-b lm-pitaj-vb" href="viber://chat?number=%2B38267030655">Viber</a>
		<a class="lm-pitaj-b lm-pitaj-tel" href="tel:+38267030655">Pozovite 067 030 655</a>
	</div>
</div>
<script id="lm-pitaj-js">
(function(){
	var box=document.getElementById('lm-pitaj'); if(!box) return; var last=null;
	function otvori(){ last=document.activeElement; box.hidden=false; document.documentElement.classList.add('lm-pitaj-open'); var b=box.querySelector('.lm-pitaj-wa'); if(b) b.focus(); }
	function zatvori(){ box.hidden=true; document.documentElement.classList.remove('lm-pitaj-open'); if(last&&last.focus) last.focus(); }
	document.addEventListener('click',function(e){
		var a=e.target.closest('a[href="#lm-pitaj"]'); if(a){ e.preventDefault(); otvori(); return; }
		if(e.target.closest('[data-lm-zatvori]')){ zatvori(); }
	});
	document.addEventListener('keydown',function(e){ if(e.key==='Escape'&&!box.hidden) zatvori(); });
})();
</script>
	<?php
}, 98 );

// 6) Stilovi.
add_action( 'wp_head', function () {
	?>
<style id="lm-cx-dorade">
/* donja traka: pune oznake */
.wd-toolbar .wd-toolbar-label{max-width:100%!important;font-size:10.5px!important;letter-spacing:-.1px}
.wd-toolbar>.wd-tools-element>a{padding-left:3px!important;padding-right:3px!important}

/* pretraga bez rezultata */
.lm-nema{text-align:center;max-width:820px;margin:8px auto 40px}
.lm-nema-uvod{color:var(--lm-tekst);margin:0 0 22px}
.lm-nema .lm-pk-kat{margin-bottom:28px}
.lm-nema-wa{background:var(--lm-krem);border-radius:16px;padding:20px 18px}
.lm-nema-wa p{margin:0 0 12px;color:var(--lm-smedja)}

/* „Pitaj nas“: ikonica u donjoj traci i panel */
.wd-toolbar-link-1 .wd-tools-icon:before{content:"";display:inline-block;width:20px;height:20px;background:currentColor;-webkit-mask:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.5z'/%3E%3Cpath d='M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01' stroke-width='2.6'/%3E%3C/svg%3E") center/contain no-repeat;mask:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 21l2.1-5.4A8.4 8.4 0 1 1 21 11.5z'/%3E%3Cpath d='M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01' stroke-width='2.6'/%3E%3C/svg%3E") center/contain no-repeat}
@media (max-width:1024px){.lm-chat{display:none!important}}
.lm-pitaj{position:fixed;inset:0;z-index:100000}
.lm-pitaj[hidden]{display:none}
.lm-pitaj-bg{position:absolute;inset:0;background:rgba(40,28,20,.35)}
.lm-pitaj-panel{position:absolute;left:0;right:0;bottom:0;background:#fff;border-radius:18px 18px 0 0;padding:22px 20px calc(20px + env(safe-area-inset-bottom,0px));box-shadow:0 -10px 30px rgba(60,40,30,.18);max-width:520px;margin:0 auto}
@media (min-width:1025px){.lm-pitaj-panel{bottom:24px;border-radius:18px}}
html body #lm-pitaj button.lm-pitaj-x,html body #lm-pitaj button.lm-pitaj-x:hover{background-color:transparent!important;position:absolute;top:8px;right:10px;width:40px;height:40px;border:0!important;background:none!important;box-shadow:none!important;font-size:30px!important;font-weight:400!important;line-height:1!important;color:var(--lm-smedja)!important;cursor:pointer;padding:0!important;min-height:0!important}
.lm-pitaj-t{font-size:19px;font-weight:700;color:var(--lm-smedja);margin:0 0 4px}
.lm-pitaj-s{font-size:14px;color:var(--lm-tekst);margin:0 0 16px}
.lm-pitaj-b{display:flex;align-items:center;justify-content:center;min-height:48px;border-radius:999px;margin:0 0 10px;font-weight:700;font-size:15px;text-decoration:none!important}
.lm-pitaj-wa{background:var(--lm-roze-jaka);color:#fff!important}
.lm-pitaj-vb{border:1.5px solid var(--lm-roze-jaka);color:var(--lm-roze-jaka-h)!important}
.lm-pitaj-tel{color:var(--lm-smedja)!important;text-decoration:underline!important;min-height:40px;margin:0}

/* newsletter popup na telefonu kao traka dolje */
@media (max-width:767.98px){
.mfp-wrap:has(.wd-promo-popup) .mfp-container{display:flex!important;align-items:flex-end!important;padding:0!important}
.mfp-wrap:has(.wd-promo-popup) .mfp-content{width:100%!important;max-width:none!important;vertical-align:bottom}
.mfp-wrap .wd-popup-wrap:has(.wd-promo-popup),.mfp-wrap .wd-promo-popup{width:100%!important;max-width:none!important;margin:0!important}
.mfp-wrap .wd-promo-popup{border-radius:18px 18px 0 0!important;padding:20px 18px calc(16px + env(safe-area-inset-bottom,0px))!important;max-height:75vh;overflow:auto}
.mfp-wrap .wd-promo-popup .lm-nl-eyebrow,.mfp-wrap .wd-promo-popup .wp-block-wd-paragraph:not(.lm-nl-eyebrow){display:none!important}
.mfp-wrap .wd-promo-popup h3{font-size:19px!important;margin:0 0 12px!important}
body:has(.mfp-wrap .wd-promo-popup) .mfp-bg{opacity:.3!important}
}

/* blog: bez broja komentara */
.wd-post-meta .wd-post-reply,.wd-meta-reply,.post-meta .meta-reply{display:none!important}
.lm-nema-btns{display:flex;flex-wrap:wrap;gap:10px;justify-content:center}</style>
	<?php
}, 31 );


// 7) Povrat robe: stilovi forme (formu crta snippet 29).
add_action( 'wp_head', function () {
	if ( ! is_page( 'povrat-robe-i-sredstava' ) ) {
		return;
	}
	?>
<style id="lm-povrat-css">
.lm-povrat{background:var(--lm-krem);border-radius:18px;padding:26px 22px;margin:0 0 40px}
.lm-povrat-naslov{font-size:clamp(22px,3vw,28px);margin:0 0 18px;color:var(--lm-smedja)}
.lm-povrat-koraci{list-style:none;margin:0 0 24px;padding:0;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;counter-reset:k}
.lm-povrat-koraci li{background:#fff;border-radius:14px;padding:16px;display:flex;flex-direction:column;gap:4px;counter-increment:k}
.lm-povrat-koraci li:before{content:counter(k);width:28px;height:28px;border-radius:50%;background:var(--lm-roze-jaka);color:#fff;font-weight:700;font-size:14px;display:grid;place-items:center;margin-bottom:6px}
.lm-povrat-koraci strong{color:var(--lm-smedja);font-size:16px}
.lm-povrat-koraci span{color:var(--lm-tekst);font-size:14px;line-height:1.5}
.lm-povrat-forma{background:#fff;border-radius:14px;padding:18px;display:flex;flex-direction:column;gap:12px}
.lm-povrat-ft{font-weight:700;color:var(--lm-smedja);margin:0;font-size:16px}
.lm-povrat-forma label{display:flex;flex-direction:column;gap:6px;font-size:14px;color:var(--lm-smedja);margin:0}
.lm-pf-red{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.lm-pf-izbor{border:0;margin:0;padding:0;display:flex;flex-wrap:wrap;gap:8px 18px}
.lm-pf-izbor legend{font-size:14px;color:var(--lm-smedja);margin-bottom:6px;padding:0}
.lm-pf-izbor label{flex-direction:row;align-items:center;gap:8px;color:var(--lm-tekst)}
.lm-povrat-forma .button{align-self:flex-start}
.lm-pf-nap{font-size:13px;color:var(--lm-tekst);margin:0}
.lm-povrat-forma input:not([type=radio]){height:46px;padding:0 12px;border:1px solid var(--lm-linija);border-radius:10px;background:#fff;width:100%;font-size:16px;color:var(--lm-smedja)}
.lm-povrat-forma textarea{height:96px;min-height:96px;padding:10px 12px;border:1px solid var(--lm-linija);border-radius:10px;font-size:16px;width:100%}
.lm-povrat-forma input[aria-invalid=true]{border-color:var(--lm-roze-jaka)}
@media (max-width:767.98px){.lm-povrat-koraci{grid-template-columns:1fr}.lm-pf-red{grid-template-columns:1fr}.lm-povrat-forma .button{align-self:stretch}}
</style>
	<?php
}, 32 );


// 8) Favicon: samo monogram „A“, smeđe na svijetloj temi, bijelo na tamnoj (SVG sa prefers-color-scheme).
//    PNG 64 px je rezerva za pregledače bez SVG favicona; apple-touch-icon ostaje site icon (35968).
add_filter( 'site_icon_meta_tags', function ( $tags ) {
	$svg = wp_get_attachment_url( 36007 );
	$png = wp_get_attachment_url( 36008 );
	if ( ! $svg || ! $png ) {
		return $tags;
	}
	$tags = array_values( array_filter( (array) $tags, function ( $t ) { return false === strpos( $t, 'rel="icon"' ); } ) );
	array_unshift( $tags, '<link rel="icon" href="' . esc_url( $svg ) . '" type="image/svg+xml" />' );
	array_unshift( $tags, '<link rel="icon" href="' . esc_url( $png ) . '" sizes="32x32" type="image/png" />' );
	return $tags;
} );
