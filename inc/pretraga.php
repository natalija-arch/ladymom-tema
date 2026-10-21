<?php
/**
 * Lady Mom – pretraga (pregled sajta 21.10.2026, stavka r2; Marko potvrdio u listi).
 *
 * - Upiti o školi za trudnice („ready mom“, „škola za trudnice“, „kurs za trudnice“…):
 *   puna pretraga preusmjerava na /skola-za-trudnice/, a u padajućim rezultatima WoodMart
 *   pretrage jedini rezultat je Ready Mom (#51472; adresa artikla vodi na stranicu škole).
 *   Artikal i dalje ostaje sakriven iz prodavnice i kategorija.
 * - U rezultatima pretrage artikli se slažu po relevantnosti; u kategorijama ostaje „Najpopularnije“.
 *   Naprijed idu artikli koji u nazivu imaju traženu riječ (ili njen sinonim) kao cijelu riječ,
 *   pa oni gdje je ona dio riječi, pa ostali („pojas“: prvo pojasevi, pa helanke „sa pojasom“).
 * - Kategorije, brendovi i atributi se pretražuju po nazivu i sinonimima, ne po opisu
 *   (opisi kategorija su dugi tekstovi pa su dovlačili gomilu nepovezanih artikala).
 *
 * Česte greške u kucanju (haakka, spektra, body…) su u WoodMart podešavanju „Search synonyms“
 * (Theme Settings → General → Search). Oblik reda je „pogrešno = tačno, tačno 2“; red bez „=“
 * WoodMart preskače.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/** ID artikla Ready Mom (škola za trudnice). */
function lm_pretraga_skola_id() {
	return 51472;
}

/**
 * Da li je upit o školi za trudnice. Poredi bez kvačica, pa „skola“ i „škola“ prolaze isto.
 * „Školjka“ (Haakaa) namjerno ne prolazi.
 */
function lm_pretraga_je_skola( $upit ) {
	$q = mb_strtolower( trim( (string) $upit ), 'UTF-8' );
	if ( '' === $q ) {
		return false;
	}
	$q = strtr( $q, array( 'š' => 's', 'č' => 'c', 'ć' => 'c', 'ž' => 'z', 'đ' => 'dj' ) );
	return (bool) preg_match(
		'/ready\s*-?\s*mom|\bskol(a|e|i|u|om)\b|\b(kurs|tecaj)[a-z]*\s+za\s+trudnic|\btrudnick[a-z]*\s+(kurs|skol)|\bpriprem[a-z]*\s+za\s+porodj?aj|\bpredavanj[a-z]*\s+za\s+trudnic/u',
		$q
	);
}

// Puna pretraga: upit o školi vodi pravo na stranicu škole.
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || wp_doing_ajax() || ! is_search() ) {
			return;
		}
		if ( lm_pretraga_je_skola( get_search_query( false ) ) ) {
			wp_safe_redirect( home_url( '/skola-za-trudnice/' ), 302 );
			exit;
		}
	},
	5
);

// Padajući rezultati (WoodMart AJAX): za upit o školi prikaži samo Ready Mom.
add_filter(
	'woodmart_ajax_search_args',
	function ( $args, $post_type ) {
		if ( 'product' !== $post_type || empty( $args['s'] ) || ! lm_pretraga_je_skola( $args['s'] ) ) {
			return $args;
		}
		$id = lm_pretraga_skola_id();
		if ( 'publish' !== get_post_status( $id ) ) {
			return $args;
		}
		unset( $args['s'], $args['tax_query'], $args['meta_query'] );
		$args['post__in']       = array( $id );
		$args['posts_per_page'] = 1;
		return $args;
	},
	10,
	2
);

// Rezultati pretrage: najbolje poklapanje prvo (WooCommerce „relevance“).
add_filter(
	'woocommerce_default_catalog_orderby',
	function ( $orderby ) {
		if ( ! is_admin() && is_search() ) {
			return 'relevance';
		}
		return $orderby;
	},
	20
);

/**
 * Da li je ovo WoodMart padajuća pretraga (AJAX).
 */
function lm_pretraga_padajuca() {
	return wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'woodmart_ajax_search' === $_REQUEST['action']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Da li upit treba da dira ova datoteka: pretraga artikala na sajtu (puna ili padajuća).
 *
 * @param WP_Query $query Upit.
 */
function lm_pretraga_artikala( $query ) {
	if ( ! lm_pretraga_padajuca() && ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) ) {
		return false;
	}
	return in_array( 'product', (array) $query->get( 'post_type' ), true );
}

/**
 * Samo sinonimi tražene riječi iz WoodMart podešavanja (bez nje same).
 *
 * @param string $s Tekst pretrage.
 * @return string[]
 */
function lm_pretraga_sinonimi( $s ) {
	$s     = trim( (string) $s );
	$klasa = '\XTS\Modules\Search\Query\Search_With_Synonyms';
	if ( '' === $s || ! function_exists( 'woodmart_get_opt' ) || ! woodmart_get_opt( 'search_synonyms' ) || ! class_exists( $klasa ) ) {
		return array();
	}
	$sinonimi = $klasa::get_instance();
	$izvorni  = $sinonimi->get_original_terms( $s );
	$novi     = array_diff( $sinonimi->generate_new_terms( $izvorni ), $izvorni );
	return array_values(
		array_unique(
			array_filter(
				array_map( 'trim', $novi ),
				function ( $p ) {
					return '' !== $p && '-' !== $p[0];
				}
			)
		)
	);
}

/**
 * Pojmovi po kojima se slažu rezultati: tražena riječ i njeni sinonimi.
 * Za pretragu sa više riječi bez sinonima vraća prazno: tu WoodMart sam stavlja naprijed
 * artikle sa cijelom frazom, pa sa svim riječima u nazivu.
 *
 * @param string $s Tekst pretrage.
 * @return string[]
 */
function lm_pretraga_pojmovi( $s ) {
	$s = mb_strtolower( trim( (string) $s ), 'UTF-8' );
	if ( '' === $s ) {
		return array();
	}
	$sinonimi = lm_pretraga_sinonimi( $s );
	if ( ! $sinonimi && false !== strpos( $s, ' ' ) ) {
		return array();
	}
	return array_values( array_unique( array_merge( array( $s ), $sinonimi ) ) );
}

// Kategorije, brendovi i atributi u pretrazi: po nazivu i po sinonimima, ne po opisu.
// WoodMart traži i po opisu kategorije, a naši opisi su dugi tekstovi koji pominju mnogo vrsta
// artikala, pa je „nosiljke“ vraćalo 157 artikala (jakne, štitnike…), a „pojas“ 89.
// Sinonime dodajemo istom WoodMart funkcijom („body“ nađe i artikle iz kategorije „Bodi“).
add_filter(
	'posts_search',
	function ( $search, $query ) {
		if ( empty( $search ) || ! lm_pretraga_artikala( $query ) || ! function_exists( 'woodmart_get_opt' ) ) {
			return $search;
		}
		$klasa = '\XTS\Modules\Search\Query\Search_With_Taxonomies';
		$taks  = woodmart_get_opt( 'search_by_product_categories' ) || woodmart_get_opt( 'search_by_product_tag' ) || woodmart_get_opt( 'search_by_product_attributes' ) || woodmart_get_opt( 'search_by_product_brands' );
		if ( $taks && class_exists( $klasa ) ) {
			$sinonimi = lm_pretraga_sinonimi( $query->get( 's' ) );
			if ( $sinonimi ) {
				$search = $klasa::get_instance()->extend_query( $search, implode( ',', $sinonimi ) );
			}
		}
		$bez_opisa = preg_replace( "/\)\s+OR\s+\(tt\.description LIKE '(?:[^'\\\\]|\\\\.)*'\)/", ')', $search );
		return null === $bez_opisa ? $search : $bez_opisa;
	},
	11,
	2
);

// Redoslijed: cijela riječ (ili sinonim) u nazivu, pa dio riječi u nazivu, pa WoodMart-ov redoslijed.
// Prioritet 30, jer WoodMart na 20 zamijeni redoslijed svojim. Kad kupac sam izabere redoslijed, ne miješamo se.
add_filter(
	'posts_search_orderby',
	function ( $orderby, $query ) {
		if ( ! lm_pretraga_artikala( $query ) ) {
			return $orderby;
		}
		$izbor = $query->get( 'orderby' );
		if ( $izbor && 'relevance' !== $izbor ) {
			return $orderby;
		}
		$pojmovi = lm_pretraga_pojmovi( $query->get( 's' ) );
		if ( ! $pojmovi ) {
			return $orderby;
		}
		global $wpdb;
		$naziv  = "CONCAT(' ', REPLACE(REPLACE(REPLACE(REPLACE({$wpdb->posts}.post_title, ',', ' '), '(', ' '), ')', ' '), '–', ' '), ' ')";
		$cijela = array();
		$dio    = array();
		foreach ( $pojmovi as $p ) {
			$cijela[] = $wpdb->prepare( "{$naziv} LIKE %s", '% ' . $wpdb->esc_like( $p ) . ' %' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$dio[]    = $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", '%' . $wpdb->esc_like( $p ) . '%' ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		$red = '(CASE WHEN ' . implode( ' OR ', $cijela ) . ' THEN 1 WHEN ' . implode( ' OR ', $dio ) . ' THEN 2 ELSE 3 END)';
		return $orderby ? $red . ', ' . $orderby : $red;
	},
	30,
	2
);
