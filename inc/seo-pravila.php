<?php
/**
 * Lady Mom – SEO pravila (21.10.2026, skill „static-site-seo“; Marko: „ajde“).
 *
 * 1. Jedno pravilo za noindex i sitemap: ono što ne treba u tražilicama dobija
 *    `noindex, follow` i izbacuje se iz Yoast sitemapa (artikli i kategorije).
 *    - „Default artikal“ iz ERP-a (lm_default_artikli(), seo-dorade.php);
 *    - artikal Ready Mom (vodi 301 na stranicu škole);
 *    - kategorije: korijen „Prodavnica“ iz ERP-a (duplikat /prodavnica/),
 *      „Bez kategorije“ i „Škola za trudnice“ (vodi 301 na stranicu škole).
 * 2. Naslovi do ~60 znakova: kad je predug, skraćuje se dodatak („| Lady Mom Podgorica“
 *    → „| Lady Mom“ → bez dodatka). Artikli istog naziva (ERP pravi poseban artikal
 *    za svaku boju) dobijaju boju u naslovu i opisu, da naslovi ne budu isti.
 *    Kategorije i brendovi bez ručnog Yoast naslova dobijaju opisniji naslov.
 * 3. Meta opisi do 160 znakova, cijele rečenice (artikli, kategorije i sve ostalo).
 * 4. Alt tekst slike artikla: ERP šalje ime fajla („20024227_1.jpg“). Podatak ostaje
 *    kako ga ERP šalje; na prikazu se umjesto imena fajla piše naziv artikla.
 * 5. H1 podkategorija koje su same po sebi nejasne („0-2 godine“, „Odrasli“):
 *    „Naočare za sunce: 0-2 godine“. Ručni H1 (term meta lm_h1) ima prednost.
 *
 * Podaci iz ERP-a (nazivi, kategorije, slike) se ne mijenjaju; sve je samo na prikazu.
 */

defined( 'ABSPATH' ) || exit;

const LM_SEO_MAX_NASLOV = 60;
const LM_SEO_MAX_OPIS   = 160;

/* ---------- 1) Jedno pravilo: noindex + van sitemapa ---------- */

/** ID-jevi artikala i stranica koje ne idu u tražilice. */
function lm_seo_bez_indeksa_postovi() {
	$ids = function_exists( 'lm_default_artikli' ) ? lm_default_artikli() : array();
	$rm  = function_exists( 'lm_rm_proizvod_id' ) ? (int) lm_rm_proizvod_id() : 0;
	if ( $rm ) {
		$ids[] = $rm;
	}
	return array_values( array_unique( array_map( 'intval', $ids ) ) );
}

/** ID-jevi kategorija artikala koje ne idu u tražilice. */
function lm_seo_bez_indeksa_kategorije() {
	$ids = array( LM_KAT_PRODAVNICA_ERP, (int) get_option( 'default_product_cat' ) );
	$t   = get_term_by( 'slug', 'skola-za-trudnice', 'product_cat' );
	if ( $t ) {
		$ids[] = (int) $t->term_id;
	}
	return array_values( array_filter( array_unique( array_map( 'intval', $ids ) ) ) );
}

add_filter( 'wpseo_robots', function ( $r ) {
	if ( is_singular() && in_array( (int) get_queried_object_id(), lm_seo_bez_indeksa_postovi(), true ) ) {
		return 'noindex, follow';
	}
	if ( function_exists( 'is_product_category' ) && is_product_category() && in_array( (int) get_queried_object_id(), lm_seo_bez_indeksa_kategorije(), true ) ) {
		return 'noindex, follow';
	}
	return $r;
}, 20 );

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	return array_values( array_unique( array_merge( (array) $ids, lm_seo_bez_indeksa_postovi() ) ) );
}, 20 );

add_filter( 'wpseo_exclude_from_sitemap_by_term_ids', function ( $ids ) {
	return array_values( array_unique( array_merge( (array) $ids, lm_seo_bez_indeksa_kategorije() ) ) );
}, 20 );

/* ---------- Pomoćne funkcije ---------- */

/** Prvi kandidat koji staje u $max znakova; ako nijedan, posljednji. */
function lm_seo_prvi_do( array $kandidati, $max ) {
	foreach ( $kandidati as $k ) {
		if ( mb_strlen( $k ) <= $max ) {
			return $k;
		}
	}
	return end( $kandidati );
}

/** Glavni dio + dodatne rečenice dok staju u 160 znakova. */
function lm_seo_opis( $glavni, array $dodaci ) {
	$opis = trim( $glavni );
	foreach ( $dodaci as $d ) {
		if ( '' !== $d && mb_strlen( $opis . ' ' . $d ) <= LM_SEO_MAX_OPIS ) {
			$opis .= ' ' . $d;
		}
	}
	return $opis;
}

/** „ – strana N“ na stranicama 2, 3… */
function lm_seo_strana() {
	$n = (int) get_query_var( 'paged' );
	return $n > 1 ? ' – strana ' . $n : '';
}

/** Nazivi artikala koji se ponavljaju (objavljeni artikli). */
function lm_seo_dupli_nazivi() {
	$nazivi = get_transient( 'lm_seo_dupli_nazivi' );
	if ( false === $nazivi ) {
		global $wpdb;
		$nazivi = $wpdb->get_col( "SELECT post_title FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish' GROUP BY post_title HAVING COUNT(*) > 1" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		set_transient( 'lm_seo_dupli_nazivi', $nazivi, HOUR_IN_SECONDS );
	}
	return (array) $nazivi;
}

/** Boja artikla kad je jedna (bez „Default“), malim slovima. */
function lm_seo_boja( $p ) {
	$boje = wc_get_product_terms( $p->get_id(), 'pa_boja', array( 'fields' => 'names' ) );
	$boje = array_values( array_filter( (array) $boje, function ( $b ) {
		return ! ( function_exists( 'lm_je_default' ) && lm_je_default( $b ) );
	} ) );
	if ( 1 !== count( $boje ) ) {
		$boja = $p->get_attribute( 'Boja' );
		$boje = array_values( array_filter( array_map( 'trim', explode( ',', (string) $boja ) ) ) );
		if ( 1 !== count( $boje ) || ( function_exists( 'lm_je_default' ) && lm_je_default( $boje[0] ) ) ) {
			return '';
		}
	}
	return mb_strtolower( $boje[0] );
}

/** Boja samo za artikle čiji se naziv ponavlja. */
function lm_seo_boja_ako_treba( $p ) {
	return in_array( $p->get_name(), lm_seo_dupli_nazivi(), true ) ? lm_seo_boja( $p ) : '';
}

/** Oznaka kategorije: „Glodalice za bebe“, „Naočare za sunce: 0-2 godine“, „Sve za bebe“. */
function lm_seo_kat_oznaka( $term ) {
	$preci = array_values( array_diff( array_map( 'intval', get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ), array( LM_KAT_PRODAVNICA_ERP ) ) );
	$vrh   = $preci ? get_term( end( $preci ), 'product_cat' ) : $term;
	$vrh   = ( $vrh && ! is_wp_error( $vrh ) ) ? $vrh->slug : '';
	$za    = array(
		'mame' => 'za trudnice i mame',
		'bebe' => 'za bebe',
		'tate' => 'za tate',
	);
	if ( ! $preci ) {
		return isset( $za[ $term->slug ] ) ? 'Sve ' . $za[ $term->slug ] : $term->name;
	}
	$naziv = $term->name;
	if ( preg_match( '/^\d/u', $naziv ) || 'odrasli' === mb_strtolower( $naziv ) ) {
		$rod   = get_term( $term->parent, 'product_cat' );
		$naziv = ( $rod && ! is_wp_error( $rod ) ? $rod->name . ': ' : '' ) . mb_strtolower( $naziv );
	}
	if ( isset( $za[ $vrh ] ) && false === mb_stripos( ' ' . $naziv . ' ', ' za ' ) ) {
		$naziv .= ' ' . $za[ $vrh ];
	}
	return $naziv;
}

/** Ručni Yoast naslov ili opis termina. */
function lm_seo_term_yoast( $term, $polje ) {
	if ( ! class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
		return '';
	}
	return (string) WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy, $polje );
}

/* ---------- 2) Naslovi ---------- */

add_filter( 'wpseo_title', function ( $naslov ) {
	$strana = lm_seo_strana();

	// Artikal bez ručnog Yoast naslova.
	if ( is_singular( 'product' ) && ! get_post_meta( get_queried_object_id(), '_yoast_wpseo_title', true ) ) {
		$p = wc_get_product( get_queried_object_id() );
		if ( $p ) {
			$boja  = lm_seo_boja_ako_treba( $p );
			$naziv = $p->get_name() . ( $boja ? ', ' . $boja : '' );
			return lm_seo_prvi_do( array( $naziv . ' | Lady Mom Podgorica', $naziv . ' | Lady Mom', $naziv ), LM_SEO_MAX_NASLOV );
		}
	}

	// Kategorija artikala bez ručnog Yoast naslova.
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$t = get_queried_object();
		if ( $t && '' === lm_seo_term_yoast( $t, 'title' ) ) {
			$o = lm_seo_kat_oznaka( $t ) . $strana;
			return lm_seo_prvi_do( array( $o . ' – kupite online | Lady Mom Podgorica', $o . ' – kupite online | Lady Mom', $o . ' | Lady Mom' ), LM_SEO_MAX_NASLOV );
		}
	}

	// Brend bez ručnog Yoast naslova.
	if ( is_tax( 'product_brand' ) ) {
		$t = get_queried_object();
		if ( $t && '' === lm_seo_term_yoast( $t, 'title' ) ) {
			$b = $t->name . ' u Crnoj Gori';
			return lm_seo_prvi_do( array( $b . $strana . ' – butik za trudnice i mame | Lady Mom', $b . $strana . ' – Lady Mom Podgorica', $b . $strana . ' – Lady Mom' ), LM_SEO_MAX_NASLOV );
		}
	}

	// Sve ostalo: predug naslov gubi dodatak sa nazivom sajta.
	// Yoast separator stiže kao HTML entitet („&ndash;“), pa se dužina mjeri na dekodiranom tekstu.
	$cist = html_entity_decode( (string) $naslov, ENT_QUOTES, 'UTF-8' );
	if ( mb_strlen( $cist ) > LM_SEO_MAX_NASLOV ) {
		$kraci = preg_replace( '/\s+[–|-]\s+Lady Mom( Podgorica| blog)?$/u', '', $cist );
		if ( $kraci && $kraci !== $cist ) {
			return $kraci;
		}
	}
	return $naslov;
}, 30 );

/* ---------- 3) Meta opisi ---------- */

/** Rečenice o kupovini koje se dodaju dok ima mjesta. */
function lm_seo_dodaci_kupovina() {
	return array(
		'Besplatna dostava po Crnoj Gori od 100 €.',
		'Plaćanje pouzećem ili karticom.',
		'Besplatno preuzimanje u butiku u Podgorici.',
	);
}

/** Opis artikla (poziva se iz seo-dorade.php i telefon-i-seo.php). */
function lm_seo_opis_artikla( $glavni, $p ) {
	$boja    = $p ? lm_seo_boja_ako_treba( $p ) : '';
	$dodaci  = lm_seo_dodaci_kupovina();
	if ( $boja ) {
		array_unshift( $dodaci, 'Boja: ' . $boja . '.' );
	}
	return lm_seo_opis( $glavni, $dodaci );
}

add_filter( 'wpseo_metadesc', function ( $opis ) {
	// Kategorija artikala bez ručnog Yoast opisa.
	if ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$t = get_queried_object();
		if ( $t && '' === lm_seo_term_yoast( $t, 'desc' ) ) {
			return lm_seo_opis( lm_seo_kat_oznaka( $t ) . ' u Lady Mom prodavnici, Podgorica i online.', lm_seo_dodaci_kupovina() );
		}
	}
	// Sve ostalo: najviše 160 znakova, cijele rečenice.
	$cist = is_string( $opis ) ? html_entity_decode( $opis, ENT_QUOTES, 'UTF-8' ) : '';
	if ( mb_strlen( $cist ) > LM_SEO_MAX_OPIS && function_exists( 'lm_skrati_recenice' ) ) {
		return lm_skrati_recenice( $cist, LM_SEO_MAX_OPIS );
	}
	return $opis;
}, 40 );

/* ---------- 4) Alt tekst slika artikla ---------- */

add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $attr;
	}
	$alt = trim( (string) ( $attr['alt'] ?? '' ) );
	if ( '' !== $alt && ! preg_match( '/^[\w\s().\-]+\.(jpe?g|png|webp|gif|avif)$/iu', $alt ) ) {
		return $attr;
	}
	$p = null;
	if ( $attachment && $attachment->post_parent ) {
		$p = wc_get_product( $attachment->post_parent );
		if ( $p && $p->is_type( 'variation' ) ) {
			$p = wc_get_product( $p->get_parent_id() );
		}
	}
	if ( ! $p && isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof WC_Product ) {
		$g   = $GLOBALS['product'];
		$sve = array_merge( array( (int) $g->get_image_id() ), array_map( 'intval', $g->get_gallery_image_ids() ) );
		if ( $attachment && in_array( (int) $attachment->ID, $sve, true ) ) {
			$p = $g;
		}
	}
	if ( $p ) {
		$boja        = lm_seo_boja_ako_treba( $p );
		$attr['alt'] = $p->get_name() . ( $boja ? ', ' . $boja : '' );
	}
	return $attr;
}, 30, 2 );

/* ---------- 5) H1 nejasnih podkategorija ---------- */

add_filter( 'woocommerce_page_title', function ( $naslov ) {
	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return $naslov;
	}
	$t = get_queried_object();
	if ( ! $t || get_term_meta( $t->term_id, 'lm_h1', true ) ) {
		return $naslov;
	}
	if ( preg_match( '/^\d/u', $t->name ) || 'odrasli' === mb_strtolower( $t->name ) ) {
		$rod = get_term( $t->parent, 'product_cat' );
		if ( $rod && ! is_wp_error( $rod ) ) {
			return esc_html( $rod->name . ': ' . mb_strtolower( $t->name ) );
		}
	}
	return $naslov;
}, 25 );
