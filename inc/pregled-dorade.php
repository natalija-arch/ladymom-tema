<?php
/**
 * Lady Mom – dorade iz pregleda sajta 21.10.2026 (stavke koje je Marko potvrdio u to-do listi).
 *
 * r6   Lista čekanja (WoodMart waitlist): rasprodate varijante se mogu izabrati na stranici artikla.
 * r13  „sa PDV-om“ samo na stranici artikla, u korpi i na plaćanju (ne na karticama u listama).
 * r16  Korpa na telefonu: ljepljiva traka sa ukupnim iznosom i „Kreni na plaćanje“;
 *      na korpi i plaćanju bez donje WoodMart trake.
 * r18  „Šifra: N/A“ se ne prikazuje dok šifra nije poznata; veličine u „Dodatnim informacijama“
 *      istim redom kao kod izbora; viber:// linkovi dozvoljeni (meni); Lady Mom znak kao slika autora.
 * r20  Spisak korisnika preko REST-a samo za prijavljene; ?author=N ne otkriva korisničko ime;
 *      uređivanje fajlova iz admina isključeno; bez zaglavlja X-Powered-By.
 * r22  Najviše 10 revizija po stranici/članku.
 * r17  Stranica „Česta pitanja“: podaci za Google (FAQPage) iz blokova „Details“ i izgled pitanja.
 * r26  Podaci o dostavi i povratu u ponudi artikla (schema.org) za Google.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/* ---------- r6: lista čekanja za rasprodate veličine ---------- */

// WooCommerce sakriva rasprodate varijante (podešavanje „Sakrij artikle kojih nema na stanju“), pa
// kupac ne može da izabere rasprodatu veličinu i WoodMart ne može da prikaže „Javi mi kad stigne“.
// Samo u formi za izbor varijante na stranici artikla rasprodate varijante ostaju vidljive (kupiti
// se ne mogu); u listama artikala, pretrazi i feed-u sve ostaje kao do sada.
$GLOBALS['lm_varijante_sve'] = false;
add_action( 'woocommerce_single_product_summary', function () { $GLOBALS['lm_varijante_sve'] = true; }, 29 );
add_action( 'woocommerce_single_product_summary', function () { $GLOBALS['lm_varijante_sve'] = false; }, 31 );
add_filter(
	'pre_option_woocommerce_hide_out_of_stock_items',
	function ( $v ) {
		return ! empty( $GLOBALS['lm_varijante_sve'] ) ? 'no' : $v;
	}
);

// Kad je izabrana rasprodata veličina, „Dodaj u korpu“ (koje tada ne radi) se sklanja, pa je
// forma „Javi mi kad stigne“ jedina radnja. Prije izbora veličine dugme ostaje kao i do sada.
add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		echo '<style id="lm-lista-cekanja">.summary-inner:has(> .wd-wtl-form:not(.wd-hide):not(.wd-wtl-is-template)) .woocommerce-variation-add-to-cart-disabled{display:none!important}</style>' . "\n";
	},
	50
);

/* ---------- r13: „sa PDV-om“ ---------- */

$GLOBALS['lm_pdv_sufiks'] = false;
add_action( 'woocommerce_single_product_summary', function () { $GLOBALS['lm_pdv_sufiks'] = true; }, 0 );
add_action( 'woocommerce_single_product_summary', function () { $GLOBALS['lm_pdv_sufiks'] = false; }, 999 );
add_filter(
	'woocommerce_get_price_suffix',
	function ( $html ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $html;
		}
		if ( ! empty( $GLOBALS['lm_pdv_sufiks'] ) ) {
			return $html;
		}
		if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
			return $html;
		}
		return '';
	},
	20
);

/* ---------- r16: korpa i plaćanje na telefonu ---------- */

add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'is_cart' ) || ! is_cart() || ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}
		?>
<div class="lm-korpa-traka" aria-hidden="true">
	<div class="lm-kt-ukupno"><span>Ukupno</span><strong class="lm-kt-iznos"><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong></div>
	<a class="lm-kt-dugme" href="<?php echo esc_url( wc_get_checkout_url() ); ?>" tabindex="-1">Kreni na plaćanje</a>
</div>
<script id="lm-korpa-traka-js">
(function(){
  function traka(){ return document.querySelector('.lm-korpa-traka'); }
  function osvjezi(){
    var t = traka(); if (!t) return;
    var iznos = document.querySelector('.cart_totals .order-total .woocommerce-Price-amount');
    if (!iznos) { t.style.display = 'none'; return; }
    t.querySelector('.lm-kt-iznos').innerHTML = iznos.outerHTML;
    pratiDugme();
  }
  var posmatrac = null;
  function pratiDugme(){
    var t = traka(), d = document.querySelector('.wc-proceed-to-checkout .checkout-button');
    if (!t || !d || !('IntersectionObserver' in window)) return;
    if (posmatrac) posmatrac.disconnect();
    posmatrac = new IntersectionObserver(function(e){ t.classList.toggle('lm-kt-skriveno', e[0].isIntersecting); });
    posmatrac.observe(d);
  }
  document.addEventListener('DOMContentLoaded', function(){
    osvjezi();
    if (window.jQuery) jQuery(document.body).on('updated_cart_totals updated_wc_div', osvjezi);
  });
})();
</script>
		<?php
	},
	50
);

add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'is_cart' ) || ! ( is_cart() || is_checkout() ) ) {
			return;
		}
		?>
<style id="lm-korpa-placanje-telefon">
@media (max-width:768px){
	body.woocommerce-cart .wd-toolbar,body.woocommerce-checkout .wd-toolbar{display:none!important}
	body.woocommerce-cart{padding-bottom:84px!important}
	.lm-korpa-traka{position:fixed;left:0;right:0;bottom:0;z-index:390;display:flex;align-items:center;justify-content:space-between;gap:14px;padding:10px 16px calc(10px + env(safe-area-inset-bottom,0px));background:#fff;box-shadow:0 -4px 18px rgba(109,78,57,.14);transition:transform .25s ease}
	.lm-korpa-traka.lm-kt-skriveno{transform:translateY(110%)}
	.lm-kt-ukupno{display:flex;flex-direction:column;line-height:1.2}
	.lm-kt-ukupno span{font-size:12px;color:var(--lm-tekst,#7a6250)}
	.lm-kt-iznos,.lm-kt-iznos .amount,.lm-kt-iznos bdi{font-size:18px;font-weight:700;color:var(--lm-smedja,#6d4e39)!important}
	.lm-kt-dugme{flex:0 0 auto;display:inline-flex;align-items:center;min-height:46px;padding:0 22px;border-radius:6px;background:var(--lm-roze-jaka,#a05c57);color:#fff!important;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;text-decoration:none!important}
}
@media (min-width:769px){.lm-korpa-traka{display:none}}
</style>
		<?php
	},
	50
);

/* ---------- r18: sitnice na artiklu ---------- */

// „Šifra: N/A“ se sakrije dok kupac ne izabere varijantu koja ima šifru.
add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		?>
<script id="lm-sifra-na">
document.addEventListener('DOMContentLoaded', function(){
  var w = document.querySelector('.product_meta .sku_wrapper'); if (!w) return;
  var s = w.querySelector('.sku');
  function prazno(){ var t = s ? s.textContent.trim() : ''; return !t || t === 'N/A'; }
  if (prazno()) w.style.display = 'none';
  if (!window.jQuery) return;
  jQuery('form.variations_form').on('found_variation', function(e, v){ if (v && v.sku) w.style.display = ''; })
    .on('reset_data', function(){ setTimeout(function(){ if (prazno()) w.style.display = 'none'; }, 0); });
});
</script>
		<?php
	},
	60
);

// Veličine u „Dodatnim informacijama“ istim redom kao kod izbora (poredak iz podešavanja atributa).
add_filter(
	'woocommerce_display_product_attributes',
	function ( $atributi, $product ) {
		if ( empty( $atributi['attribute_pa_velicina'] ) || ! $product instanceof WC_Product ) {
			return $atributi;
		}
		$imena = wc_get_product_terms( $product->get_id(), 'pa_velicina', array( 'fields' => 'names' ) );
		if ( $imena && ! is_wp_error( $imena ) ) {
			$atributi['attribute_pa_velicina']['value'] = wpautop( wptexturize( implode( ', ', array_map( 'esc_html', $imena ) ) ) );
		}
		return $atributi;
	},
	20,
	2
);

// viber:// u linkovima (meni „Lady Mom“ → Viber), kao na Kontaktu.
add_filter(
	'kses_allowed_protocols',
	function ( $p ) {
		if ( ! in_array( 'viber', $p, true ) ) {
			$p[] = 'viber';
		}
		return $p;
	}
);

// Slika autora na blogu: Lady Mom znak umjesto prazne sive slike.
add_filter(
	'get_avatar_url',
	function ( $url, $id_or_email ) {
		if ( is_admin() ) {
			return $url;
		}
		$id = 0;
		if ( is_numeric( $id_or_email ) ) {
			$id = (int) $id_or_email;
		} elseif ( $id_or_email instanceof WP_User ) {
			$id = (int) $id_or_email->ID;
		} elseif ( $id_or_email instanceof WP_Post ) {
			$id = (int) $id_or_email->post_author;
		}
		if ( $id && 'Lady Mom' === get_the_author_meta( 'display_name', $id ) ) {
			return 'https://ladymom.me/wp-content/uploads/2026/10/favicon-lady-mom-monogram.png';
		}
		return $url;
	},
	10,
	2
);

/* ---------- r20: manje javnih podataka ---------- */

// Spisak korisnika preko REST-a samo za prijavljene (ERP, Claude i urednici su prijavljeni).
add_filter(
	'rest_endpoints',
	function ( $rute ) {
		if ( is_user_logged_in() ) {
			return $rute;
		}
		foreach ( array_keys( $rute ) as $ruta ) {
			if ( 0 === strpos( $ruta, '/wp/v2/users' ) ) {
				unset( $rute[ $ruta ] );
			}
		}
		return $rute;
	}
);

// ?author=N ne vodi na /author/korisnicko-ime/ (otkrivalo je korisnička imena).
add_action(
	'template_redirect',
	function () {
		if ( ! is_admin() && isset( $_GET['author'] ) && ! is_user_logged_in() ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/blog/' ), 301 );
			exit;
		}
	},
	1
);

// Link na autora članka vodi na blog (arhive autora su isključene u Yoast-u).
add_filter(
	'author_link',
	function () {
		return home_url( '/blog/' );
	}
);

// Bez uređivanja fajlova teme i dodataka iz admina.
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// Bez oznake verzije PHP-a u odgovoru servera.
add_action(
	'send_headers',
	function () {
		if ( function_exists( 'header_remove' ) ) {
			header_remove( 'X-Powered-By' );
		}
	}
);

/* ---------- r22: revizije ---------- */

add_filter(
	'wp_revisions_to_keep',
	function () {
		return 10;
	}
);

/* ---------- r26: dostava i povrat u podacima artikla ---------- */

/** Dostava i povrat kao u kutiji na artiklu i na stranici Dostava (4,50 €, besplatno od 100 €, 1–3 radna dana, povrat 14 dana). */
function lm_schema_dostava_povrat( $cijena ) {
	$besplatno = (float) $cijena >= 100;
	return array(
		'shippingDetails'         => array(
			'@type'               => 'OfferShippingDetails',
			'shippingRate'        => array(
				'@type'    => 'MonetaryAmount',
				'value'    => $besplatno ? '0' : '4.50',
				'currency' => 'EUR',
			),
			'shippingDestination' => array(
				'@type'          => 'DefinedRegion',
				'addressCountry' => 'ME',
			),
			'deliveryTime'        => array(
				'@type'        => 'ShippingDeliveryTime',
				'handlingTime' => array( '@type' => 'QuantitativeValue', 'minValue' => 0, 'maxValue' => 1, 'unitCode' => 'DAY' ),
				'transitTime'  => array( '@type' => 'QuantitativeValue', 'minValue' => 1, 'maxValue' => 3, 'unitCode' => 'DAY' ),
			),
		),
		'hasMerchantReturnPolicy' => array(
			'@type'                => 'MerchantReturnPolicy',
			'applicableCountry'    => 'ME',
			'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
			'merchantReturnDays'   => 14,
			'returnMethod'         => 'https://schema.org/ReturnByMail',
		),
	);
}

add_filter(
	'woocommerce_structured_data_product',
	function ( $markup ) {
		if ( empty( $markup['offers'] ) || ! is_array( $markup['offers'] ) ) {
			return $markup;
		}
		$ponude = isset( $markup['offers']['@type'] ) ? array( $markup['offers'] ) : $markup['offers'];
		foreach ( $ponude as $i => $o ) {
			if ( ! is_array( $o ) ) {
				continue;
			}
			$cijena = isset( $o['price'] ) ? $o['price'] : ( isset( $o['lowPrice'] ) ? $o['lowPrice'] : 0 );
			$ponude[ $i ] = array_merge( $o, lm_schema_dostava_povrat( $cijena ) );
		}
		$markup['offers'] = isset( $markup['offers']['@type'] ) ? $ponude[0] : $ponude;
		return $markup;
	},
	30
);

/* ---------- r17: stranica „Česta pitanja“ (/cesta-pitanja/) ---------- */

/**
 * Pitanja i odgovori sa stranice: svaki blok „Details“ (sklopivi red) je jedno pitanje.
 * Kad se tekst izmijeni u editoru, mijenjaju se i podaci za Google.
 *
 * @param int $post_id Stranica.
 * @return array<int, array{0:string,1:string}>
 */
function lm_cp_pitanja( $post_id ) {
	$izlaz = array();
	$prodji = function ( $blokovi ) use ( &$prodji, &$izlaz ) {
		foreach ( $blokovi as $b ) {
			if ( 'core/details' === $b['blockName'] ) {
				$q = preg_match( '#<summary[^>]*>(.*?)</summary>#s', (string) $b['innerHTML'], $m ) ? trim( wp_strip_all_tags( $m[1] ) ) : '';
				$a = '';
				foreach ( $b['innerBlocks'] as $u ) {
					$a .= ' ' . wp_strip_all_tags( render_block( $u ) );
				}
				$a = trim( preg_replace( '/\s+/u', ' ', $a ) );
				if ( '' !== $q && '' !== $a ) {
					$izlaz[] = array( html_entity_decode( $q, ENT_QUOTES, 'UTF-8' ), html_entity_decode( $a, ENT_QUOTES, 'UTF-8' ) );
				}
			} elseif ( ! empty( $b['innerBlocks'] ) ) {
				$prodji( $b['innerBlocks'] );
			}
		}
	};
	$prodji( parse_blocks( (string) get_post_field( 'post_content', $post_id ) ) );
	return $izlaz;
}

add_action(
	'wp_head',
	function () {
		if ( ! is_page( 'cesta-pitanja' ) ) {
			return;
		}
		$pitanja = lm_cp_pitanja( get_queried_object_id() );
		if ( $pitanja ) {
			$faq = array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'@id'        => get_permalink( get_queried_object_id() ) . '#pitanja',
				'mainEntity' => array(),
			);
			foreach ( $pitanja as $q ) {
				$faq['mainEntity'][] = array(
					'@type'          => 'Question',
					'name'           => $q[0],
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $q[1],
					),
				);
			}
			echo '<script type="application/ld+json">' . wp_json_encode( $faq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
		?>
<style id="lm-cesta-pitanja">
.entry-content h2.wp-block-heading{margin:34px 0 8px;font-size:22px}
.entry-content .wp-block-details{margin:0;border-bottom:1px solid var(--lm-linija,#e7d7cc);padding:0}
.entry-content .wp-block-details summary{cursor:pointer;list-style:none;position:relative;padding:15px 34px 15px 0;font-family:"Raleway",system-ui,sans-serif;font-weight:700;font-size:16px;line-height:1.4;color:var(--lm-smedja,#6d4e39)}
.entry-content .wp-block-details summary::-webkit-details-marker{display:none}
.entry-content .wp-block-details summary::after{content:"";position:absolute;right:6px;top:50%;width:9px;height:9px;margin-top:-7px;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:rotate(45deg);transition:transform .2s ease}
.entry-content .wp-block-details[open] summary::after{transform:rotate(-135deg);margin-top:-2px}
.entry-content .wp-block-details > p{margin:0 0 16px;line-height:1.65}
</style>
		<?php
	},
	30
);
