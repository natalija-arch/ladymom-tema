<?php
/**
 * Lady Mom – keš stranica poslije izmjena iz ERP-a (24.10.2026).
 *
 * 1) Kad se artiklu (ili varijanti) promijeni cijena, akcija ili zaliha – ERP preko REST-a,
 *    admin ili uvoz – briše se keš tog artikla i stranica na kojima se vidi (kategorije,
 *    prodavnica, početna), po pravilima iz LiteSpeed → Cache → Purge. LiteSpeed sam briše
 *    artikal samo kad se promijeni količina, a promjenu cijene preko REST-a ne vidi, pa je
 *    kupac mogao vidjeti staru cijenu do 7 dana (javni keš).
 * 2) Cijeli keš (meniji, kad kategorija ili brend ostanu bez artikala ili se vrate) briše se
 *    najviše jednom u 30 minuta. Odmah se brišu samo stranice tih kategorija i brendova i početna.
 * 3) Dnevnik brisanja keša (dok traje ERP log, do 30.11.): cijeli keš u lm_kes_puni_log, artikli u
 *    lm_kes_artikal_log (po 300 zapisa), da se vidi šta i koliko često ga pokreće.
 * 4) Zagrijavanje (n16, 24.10.2026): poslije brisanja keša sajt u pozadini (WP-Cron, minut kasnije)
 *    sam otvori obrisane stranice kao gost, pa prvi kupac dobija keširanu
 *    stranicu (~60 ms) umjesto 1,1–1,2 s. Artikal poslije ERP izmjene; poslije brisanja cijelog
 *    keša početna, prodavnica, 12 kategorija sa najviše artikala, škola i kontakt. LiteSpeed-ov crawler hosting ne dozvoljava.
 */

defined( 'ABSPATH' ) || exit;

define( 'LM_KES_PUNI_RAZMAK', 30 * MINUTE_IN_SECONDS );

/** 1) Artikal: cijena, akcija ili zaliha. */
add_action( 'woocommerce_product_object_updated_props', function ( $product, $props ) {
	static $obrisano = array();
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$bitno = array( 'regular_price', 'sale_price', 'price', 'date_on_sale_from', 'date_on_sale_to', 'stock_quantity', 'stock_status', 'manage_stock', 'backorders' );
	if ( ! array_intersect( (array) $props, $bitno ) ) {
		return;
	}
	$id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
	if ( ! $id || isset( $obrisano[ $id ] ) ) {
		return;
	}
	$obrisano[ $id ] = true;
	do_action( 'litespeed_purge_post', $id );
	lm_kes_zagrij_dodaj( array( get_permalink( $id ) ) );
	lm_kes_zapisi( 'lm_kes_artikal_log', array( 'id' => $id, 'props' => implode( ',', array_values( array_intersect( (array) $props, $bitno ) ) ) ) );
}, 10, 2 );

/** Dnevnik brisanja keša (dok traje ERP log, do 30.11.): koliko često i šta ga pokreće. */
function lm_kes_zapisi( $opcija, $podaci ) {
	$do = (int) get_option( 'lm_erp_log_do', 0 );
	if ( $do && time() > $do ) {
		return;
	}
	$log   = get_option( $opcija, array() );
	$log   = is_array( $log ) ? $log : array();
	$log[] = array_merge( array( 't' => wp_date( 'Y-m-d H:i:s' ), 'vrsta' => wp_doing_cron() ? 'cron' : ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ? 'rest' : ( is_admin() ? 'admin' : 'sajt' ) ) ), $podaci );
	update_option( $opcija, array_slice( $log, -300 ), false );
}

/**
 * 2) Cijeli keš, najviše jednom u LM_KES_PUNI_RAZMAK. $adrese se brišu odmah (stranice
 * kategorija i brendova kojima se promijenilo stanje); početna uvijek.
 */
function lm_kes_obrisi_sve( $razlog, $adrese = array() ) {
	foreach ( array_unique( array_merge( array( home_url( '/' ) ), (array) $adrese ) ) as $url ) {
		do_action( 'litespeed_purge_url', $url );
	}
	$zadnji = (int) get_option( 'lm_kes_puni_zadnji', 0 );
	if ( time() - $zadnji >= LM_KES_PUNI_RAZMAK ) {
		update_option( 'lm_kes_puni_zadnji', time(), false );
		do_action( 'litespeed_purge_all', 'Lady Mom: ' . $razlog );
		return;
	}
	update_option( 'lm_kes_puni_razlog', $razlog, false );
	if ( ! wp_next_scheduled( 'lm_kes_obrisi_sve_zakazano' ) ) {
		wp_schedule_single_event( $zadnji + LM_KES_PUNI_RAZMAK, 'lm_kes_obrisi_sve_zakazano' );
	}
}
add_action( 'lm_kes_obrisi_sve_zakazano', function () {
	update_option( 'lm_kes_puni_zadnji', time(), false );
	do_action( 'litespeed_purge_all', 'Lady Mom (zakazano): ' . get_option( 'lm_kes_puni_razlog', '' ) );
} );

/** 3) Dnevnik: kad je i odakle obrisan cijeli keš (najviše 300 zapisa). */
add_action( 'litespeed_purged_all', function () {
	$stek = array_values( array_diff( (array) ( $GLOBALS['wp_current_filter'] ?? array() ), array( 'litespeed_purged_all', 'litespeed_purge_all' ) ) );
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ) : ''; // bez parametara (ključevi API-ja)
	lm_kes_zapisi( 'lm_kes_puni_log', array(
		'hook' => implode( ' > ', array_slice( $stek, -4 ) ),
		'uri'  => substr( (string) $uri, 0, 120 ),
		'k'    => get_current_user_id(),
	) );
	$adrese = array( home_url( '/' ), get_permalink( wc_get_page_id( 'shop' ) ), home_url( '/skola-za-trudnice/' ), home_url( '/kontakt/' ) );
	$kat    = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 12, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
	foreach ( is_array( $kat ) ? $kat : array() as $k ) {
		$l = get_term_link( $k );
		if ( is_string( $l ) ) {
			$adrese[] = $l;
		}
	}
	lm_kes_zagrij_dodaj( $adrese );
}, 10, 0 );

/** 4) Zagrijavanje: adrese u red, obrada minut kasnije (WP-Cron), najviše 15 adresa po prolazu, 2 s razmaka. */
function lm_kes_zagrij_dodaj( $adrese ) {
	$red = get_option( 'lm_kes_zagrij', array() );
	$red = is_array( $red ) ? $red : array();
	foreach ( (array) $adrese as $u ) {
		if ( is_string( $u ) && 0 === strpos( $u, home_url( '/' ) ) ) {
			$red[] = strtok( $u, '#' );
		}
	}
	update_option( 'lm_kes_zagrij', array_slice( array_values( array_unique( $red ) ), -60 ), false );
	if ( ! wp_next_scheduled( 'lm_kes_zagrij' ) ) {
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'lm_kes_zagrij' );
	}
}
add_action( 'lm_kes_zagrij', function () {
	$red = get_option( 'lm_kes_zagrij', array() );
	$red = is_array( $red ) ? $red : array();
	$sad = array_slice( $red, 0, 15 );
	update_option( 'lm_kes_zagrij', array_slice( $red, 15 ), false );
	// LiteSpeed ne pravi poseban keš za telefon (cache-mobile isključen), pa je dovoljan jedan zahtjev.
	// Imunify vraća 429 na sat vremena kad server prečesto otvara sam sebe (vidi server-veza.php),
	// zato pauza od 2 s između adresa; ako stigne 429, prolaz staje i red ostaje za kasnije.
	$ok = 0;
	foreach ( $sad as $i => $u ) {
		if ( $i ) {
			sleep( 2 );
		}
		$r   = wp_remote_get( $u, array( 'timeout' => 20, 'redirection' => 2, 'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36 LadyMomZagrijavanje', 'cookies' => array() ) );
		$kod = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
		if ( 200 === $kod ) {
			$ok++;
		} elseif ( 429 === $kod ) {
			update_option( 'lm_kes_zagrij', array_values( array_unique( array_merge( array_slice( $sad, $i ), get_option( 'lm_kes_zagrij', array() ) ) ) ), false );
			update_option( 'lm_kes_zagrij_zadnje', array( 't' => wp_date( 'Y-m-d H:i:s' ), 'adresa' => $i, 'ok' => $ok, 'greska' => '429 Imunify' ), false );
			return;
		}
	}
	update_option( 'lm_kes_zagrij_zadnje', array( 't' => wp_date( 'Y-m-d H:i:s' ), 'adresa' => count( $sad ), 'ok' => $ok, 'ostalo' => max( 0, count( $red ) - 15 ) ), false );
	if ( count( $red ) > 15 && ! wp_next_scheduled( 'lm_kes_zagrij' ) ) {
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'lm_kes_zagrij' );
	}
} );
