<?php
/**
 * Lady Mom – dorade za telefon i Google (Markova odluka 18.10.2026, to-do t3, t4, t6, m7, m8, m3).
 *
 * Ništa od ovoga ne mijenja podatke koje šalje ERP: sve je samo prikaz na sajtu.
 */

defined( 'ABSPATH' ) || exit;

/** ID ERP-ove glavne kategorije „Prodavnica ERP“. */
const LM_KAT_PRODAVNICA_ERP = 104;

/** Da li je zahtjev prikaz za kupca (ne admin, ne REST za ERP, ne cron). */
function lm_prikaz_za_kupca() {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}
	if ( wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return false;
	}
	return ! is_admin() || wp_doing_ajax();
}

/**
 * t3) „Prodavnica ERP“ se kupcu prikazuje kao „Prodavnica“ (putanja, naslov, meniji).
 * Naziv u bazi i ono što ERP vidi preko REST-a ostaju isti.
 */
add_filter( 'get_term', function ( $term, $taxonomy ) {
	if ( 'product_cat' === $taxonomy && is_object( $term ) && LM_KAT_PRODAVNICA_ERP === (int) $term->term_id && lm_prikaz_za_kupca() ) {
		$term       = clone $term;
		$term->name = 'Prodavnica';
	}
	return $term;
}, 10, 2 );
add_filter( 'woocommerce_get_breadcrumb', function ( $crumbs ) {
	foreach ( $crumbs as $i => $c ) {
		if ( isset( $c[0] ) && 'Prodavnica ERP' === $c[0] ) {
			$crumbs[ $i ][0] = 'Prodavnica';
		}
	}
	return $crumbs;
} );
add_filter( 'wpseo_breadcrumb_links', function ( $links ) {
	foreach ( (array) $links as $i => $l ) {
		if ( isset( $l['text'] ) && 'Prodavnica ERP' === $l['text'] ) {
			$links[ $i ]['text'] = 'Prodavnica';
		}
	}
	return $links;
} );

/**
 * t4) Telefon, stranica proizvoda: putanja i strelice „prethodni/sljedeći“ stoje između
 * slike i naziva, pa naziv i cijena padaju ispod prvog ekrana. Na telefonu se sklanjaju,
 * a logo brenda je manji.
 * t6) Telefon, korpa: polje za kupon je sklopljeno u link „Imate kupon?“.
 */
add_action( 'wp_head', function () {
	?>
<style id="lm-telefon">
@media (max-width:768.98px){
.single-product .summary .single-breadcrumbs-wrapper{display:none!important}
.single-product .summary .wd-product-brands{margin-bottom:6px!important}
.single-product .summary .wd-product-brands img{max-height:26px!important;width:auto!important}
.woocommerce-cart .wd-coupon-form.lm-sklopljen{display:none!important}
.lm-kupon-link{display:inline-block;margin:4px 0 10px;font-size:14px;font-weight:600;color:var(--lm-smedja,#6d4e39);text-decoration:underline;cursor:pointer;background:none;border:0;padding:0}
}
@media (min-width:769px){.lm-kupon-link{display:none!important}}
</style>
	<?php
}, 40 );

add_action( 'wp_footer', function () {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}
	?>
<script id="lm-kupon-telefon">
(function(){
  function sklopi(){
    if (!window.matchMedia('(max-width:768.98px)').matches) return;
    document.querySelectorAll('.woocommerce-cart .wd-coupon-form').forEach(function(f){
      if (f.dataset.lmKupon) return;
      f.dataset.lmKupon = '1';
      if (f.querySelector('input[name=coupon_code]') && f.querySelector('input[name=coupon_code]').value) return;
      f.classList.add('lm-sklopljen');
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'lm-kupon-link'; b.textContent = 'Imate kupon?';
      b.addEventListener('click', function(){ f.classList.remove('lm-sklopljen'); b.remove(); var i=f.querySelector('input[name=coupon_code]'); if(i) i.focus(); });
      f.parentNode.insertBefore(b, f);
    });
  }
  sklopi();
  if (window.jQuery) jQuery(document.body).on('updated_wc_div updated_cart_totals', sklopi);
})();
</script>
	<?php
}, 99 );

/**
 * m7) Meta opis proizvoda bez kratkog opisa: Yoast-ov šablon tada daje samo rečenicu
 * o dostavi. Pravi se od naziva, brenda i kategorije. Ručno upisan Yoast opis ima prednost.
 */
function lm_meta_opis_proizvoda( $opis ) {
	if ( ! is_singular( 'product' ) ) {
		return $opis;
	}
	$id = get_queried_object_id();
	if ( get_post_meta( $id, '_yoast_wpseo_metadesc', true ) ) {
		return $opis;
	}
	$p = wc_get_product( $id );
	if ( ! $p || trim( wp_strip_all_tags( $p->get_short_description() . $p->get_description() ) ) !== '' ) {
		return $opis;
	}
	$naziv  = trim( preg_replace( '/^[A-ZČĆŠĐŽ&\'’\s\.\-–]+\s[-–]\s/u', '', $p->get_name() ) );
	$brend  = wp_get_post_terms( $id, 'product_brand', array( 'fields' => 'names' ) );
	$brend  = $brend && ! is_wp_error( $brend ) ? $brend[0] : '';
	$kat    = '';
	foreach ( wc_get_product_terms( $id, 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) ) as $t ) {
		if ( LM_KAT_PRODAVNICA_ERP !== (int) $t->term_id ) {
			$kat = $t->name;
			break;
		}
	}
	$tekst = ( $brend ? $brend . ' – ' : '' ) . $naziv . '.';
	if ( $kat ) {
		$tekst .= ' ' . $kat . ' u Lady Mom butiku za trudnice i mame u Podgorici i online.';
	}
	$tekst .= ' Besplatna dostava po Crnoj Gori za porudžbine od 100 €, plaćanje pouzećem ili karticom.';
	return $tekst;
}
add_filter( 'wpseo_metadesc', 'lm_meta_opis_proizvoda', 20 );
add_filter( 'wpseo_opengraph_desc', 'lm_meta_opis_proizvoda', 20 );

/**
 * m8) Google: barkod iz ERP-a (SKU varijante ili artikla) kao GTIN u podacima o proizvodu.
 * Kod artikla sa više varijanti svaka ponuda dobija svoj GTIN.
 */
function lm_gtin_kljuc( $kod ) {
	$kod = preg_replace( '/\s+/', '', (string) $kod );
	if ( ! preg_match( '/^\d{8}$|^\d{12,14}$/', $kod ) ) {
		return null;
	}
	return 'gtin' . strlen( $kod );
}
add_filter( 'woocommerce_structured_data_product', function ( $markup, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $markup;
	}
	$sku = $product->get_sku();
	$k   = lm_gtin_kljuc( $sku );
	if ( $k ) {
		$markup[ $k ] = $sku;
		return $markup;
	}
	if ( $product->is_type( 'variable' ) ) {
		$kodovi = array();
		foreach ( $product->get_children() as $vid ) {
			$v = wc_get_product( $vid );
			if ( $v && 'publish' === $v->get_status() && lm_gtin_kljuc( $v->get_sku() ) ) {
				$kodovi[] = $v;
			}
		}
		if ( 1 === count( $kodovi ) ) {
			$markup[ lm_gtin_kljuc( $kodovi[0]->get_sku() ) ] = $kodovi[0]->get_sku();
			$markup['sku'] = $kodovi[0]->get_sku();
		} elseif ( $kodovi ) {
			// Više varijanti: svaka varijanta kao posebna ponuda sa svojim barkodom i cijenom.
			$ponude = array();
			foreach ( $kodovi as $v ) {
				$ponude[] = array(
					'@type'         => 'Offer',
					'sku'           => $v->get_sku(),
					lm_gtin_kljuc( $v->get_sku() ) => $v->get_sku(),
					'price'         => wc_format_decimal( $v->get_price(), wc_get_price_decimals() ),
					'priceCurrency' => get_woocommerce_currency(),
					'availability'  => 'https://schema.org/' . ( $v->is_in_stock() ? 'InStock' : 'OutOfStock' ),
					'url'           => $v->get_permalink(),
					'name'          => wp_strip_all_tags( $v->get_name() ),
				);
			}
			$markup['offers'] = $ponude;
		}
	}
	return $markup;
}, 20, 2 );

/**
 * m3) Prijava na novosti iz popupa (Contact Form 7, forma 35763) upisuje kupca u dodatak
 * „Newsletter“, sa potvrđenom saglasnošću. Kod za popust i poruka ostaju kao do sada.
 */
add_action( 'wpcf7_before_send_mail', function ( $forma ) {
	if ( 35763 !== (int) $forma->id() || ! class_exists( 'TNP' ) ) {
		return;
	}
	$sub = class_exists( 'WPCF7_Submission' ) ? WPCF7_Submission::get_instance() : null;
	$email = $sub ? sanitize_email( (string) $sub->get_posted_data( 'your-email' ) ) : '';
	if ( ! is_email( $email ) ) {
		return;
	}
	try {
		TNP::add_subscriber( array( 'email' => $email, 'status' => 'C', 'referrer' => 'popup-10' ) );
	} catch ( \Throwable $e ) {
		error_log( 'Lady Mom newsletter: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
} );
