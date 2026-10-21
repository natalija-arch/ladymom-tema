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
 * Pojmovi po kojima se slažu rezultati: tražena riječ i njeni WoodMart sinonimi.
 * Za pretragu sa više riječi bez sinonima vraća prazno: tu WordPress sam stavlja naprijed
 * artikle sa cijelom frazom u nazivu.
 *
 * @param string $s Tekst pretrage.
 * @return string[]
 */
function lm_pretraga_pojmovi( $s ) {
	$s = trim( (string) $s );
	if ( '' === $s ) {
		return array();
	}
	$pojmovi = array( mb_strtolower( $s, 'UTF-8' ) );
	$klasa   = '\XTS\Modules\Search\Query\Search_With_Synonyms';
	if ( function_exists( 'woodmart_get_opt' ) && woodmart_get_opt( 'search_synonyms' ) && class_exists( $klasa ) ) {
		$sinonimi = $klasa::get_instance();
		$pojmovi  = array_merge( $pojmovi, $sinonimi->generate_new_terms( $sinonimi->get_original_terms( $s ) ) );
	}
	$pojmovi = array_values(
		array_unique(
			array_filter(
				array_map( 'trim', $pojmovi ),
				function ( $p ) {
					return '' !== $p && '-' !== $p[0];
				}
			)
		)
	);
	if ( 1 === count( $pojmovi ) && false !== strpos( $pojmovi[0], ' ' ) ) {
		return array();
	}
	return $pojmovi;
}

// Redoslijed rezultata: cijela riječ u nazivu, pa dio riječi u nazivu, pa ostalo. Samo kad kupac nije sam izabrao redoslijed.
add_filter(
	'posts_search_orderby',
	function ( $orderby, $query ) {
		$padajuci = wp_doing_ajax() && isset( $_REQUEST['action'] ) && 'woodmart_ajax_search' === $_REQUEST['action']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $padajuci && ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) ) {
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
	10,
	2
);
