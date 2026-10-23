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
 * 3) Dnevnik brisanja cijelog keša (dok traje ERP log, do 30.11.), da se vidi šta ga pokreće.
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
}, 10, 2 );

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
	$do = (int) get_option( 'lm_erp_log_do', 0 );
	if ( $do && time() > $do ) {
		return;
	}
	$stek = array_values( array_diff( (array) ( $GLOBALS['wp_current_filter'] ?? array() ), array( 'litespeed_purged_all', 'litespeed_purge_all' ) ) );
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? strtok( wp_unslash( $_SERVER['REQUEST_URI'] ), '?' ) : ''; // bez parametara (ključevi API-ja)
	$log  = get_option( 'lm_kes_puni_log', array() );
	$log  = is_array( $log ) ? $log : array();
	$log[] = array(
		't'    => wp_date( 'Y-m-d H:i:s' ),
		'hook' => implode( ' > ', array_slice( $stek, -4 ) ),
		'uri'  => substr( (string) $uri, 0, 120 ),
		'vrsta' => wp_doing_cron() ? 'cron' : ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ? 'rest' : ( is_admin() ? 'admin' : 'sajt' ) ),
		'k'    => get_current_user_id(),
	);
	update_option( 'lm_kes_puni_log', array_slice( $log, -300 ), false );
}, 10, 0 );
