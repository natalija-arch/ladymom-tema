<?php
/**
 * Lady Mom – pretraga (pregled sajta 21.10.2026, stavka r2; Marko potvrdio u listi).
 *
 * - Upiti o školi za trudnice („ready mom“, „škola za trudnice“, „kurs za trudnice“…):
 *   puna pretraga preusmjerava na /skola-za-trudnice/, a u padajućim rezultatima WoodMart
 *   pretrage jedini rezultat je Ready Mom (#51472; adresa artikla vodi na stranicu škole).
 *   Artikal i dalje ostaje sakriven iz prodavnice i kategorija.
 * - U rezultatima pretrage artikli se slažu po relevantnosti; u kategorijama ostaje „Najpopularnije“.
 *
 * Česte greške u kucanju (haakka, spektra, body…) su u WoodMart podešavanju „Search synonyms“.
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
