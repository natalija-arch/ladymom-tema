<?php
/**
 * Lady Mom – tabele veličina po brendu (r8, 24.10.2026).
 *
 * WoodMart tabela veličina („Size guide“) bira se na artiklu ili po kategoriji. Artikle i
 * kategorije šalje ERP, pa se tabela ovdje bira po brendu: tabela (post woodmart_size_guide)
 * ima meta lm_brend = ID brenda (product_brand). Tabele: Mamalicious, Seraphine, Cache Cœur
 * (mjere sa sajtova proizvođača, u cm, preuzete 24.10.2026).
 *
 * Uključeno kad je opcija lm_tabele_brend = 1. Dok nije, tabelu vidi samo admin uz ?lm_tabela=1
 * (pregled prije uključivanja).
 */

defined( 'ABSPATH' ) || exit;

function lm_tabele_aktivne() {
	if ( get_option( 'lm_tabele_brend' ) ) {
		return true;
	}
	return isset( $_GET['lm_tabela'] ) && current_user_can( 'manage_options' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/** ID tabele za artikal po brendu (0 ako brend nema tabelu). */
function lm_tabela_za_artikal( $pid ) {
	static $mapa = null;
	$brendovi = wp_get_post_terms( $pid, 'product_brand', array( 'fields' => 'ids' ) );
	if ( is_wp_error( $brendovi ) || ! $brendovi ) {
		return 0;
	}
	if ( null === $mapa ) {
		$mapa = array();
		$tabele = get_posts( array(
			'post_type'   => 'woodmart_size_guide',
			'post_status' => 'publish',
			'numberposts' => 30,
			'meta_key'    => 'lm_brend', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		) );
		foreach ( $tabele as $t ) {
			$mapa[ (int) get_post_meta( $t->ID, 'lm_brend', true ) ] = $t->ID;
		}
	}
	foreach ( $brendovi as $b ) {
		if ( isset( $mapa[ (int) $b ] ) ) {
			return $mapa[ (int) $b ];
		}
	}
	return 0;
}

/*
 * woodmart_sguide_display() (WoodMart, inc/integrations/woocommerce/modules/size-guide.php) traži tabelu na
 * artiklu, pa u kategoriji, i na kraju propusti ID kroz filter wpml_object_id. To je jedino mjesto gdje
 * se izbor može dopuniti bez mijenjanja teme: kad artikal i kategorija nemaju tabelu, vraćamo tabelu brenda.
 */
add_filter( 'wpml_object_id', function ( $id, $tip = '', $original = false ) {
	if ( 'woodmart_size_guide' !== $tip || ! empty( $id ) || ! lm_tabele_aktivne() ) {
		return $id;
	}
	$pid = get_the_ID();
	if ( ! $pid || 'product' !== get_post_type( $pid ) ) {
		return $id;
	}
	// Samo artikli sa izborom veličine (ulošci, pumpice i sl. istog brenda ne dobijaju tabelu).
	$p = wc_get_product( $pid );
	if ( ! $p || ! array_filter( array_keys( $p->get_attributes() ), function ( $k ) {
		return (bool) preg_match( '/veli|size|korp|kup/i', $k );
	} ) ) {
		return $id;
	}
	$t = lm_tabela_za_artikal( $pid );
	return $t ? $t : $id;
}, 10, 3 );
