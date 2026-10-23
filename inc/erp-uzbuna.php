<?php
/**
 * Lady Mom – uzbuna kad ERP prestane da šalje podatke (23.10.2026).
 *
 * TiramisuERP se javlja ~800 puta na sat, i danju i noću. Sinhronizacija je 21.10.
 * u 13:39 stala i to se primijetilo tek dva dana kasnije. Svakih 15 minuta provjerava se
 * kad je WooCommerce API ključ „TiramisuERP“ zadnji put korišćen (last_access). Ako je
 * prošlo više od 60 minuta, šalje se jedan email; kad se ERP ponovo javi, šalje se
 * email da je proradio. Primaoci: opcija `lm_erp_uzbuna_primaoci` (zarezom odvojeni),
 * podrazumijevano natalija@ladymom.me. ERP podatke ništa ne mijenja.
 */

defined( 'ABSPATH' ) || exit;

const LM_ERP_UZBUNA_MINUTA = 60;

add_filter( 'cron_schedules', function ( $s ) {
	if ( ! isset( $s['lm_15min'] ) ) {
		$s['lm_15min'] = array( 'interval' => 15 * MINUTE_IN_SECONDS, 'display' => 'Svakih 15 minuta' );
	}
	return $s;
} );

add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'lm_erp_uzbuna_provjera' ) ) {
		wp_schedule_event( time() + 300, 'lm_15min', 'lm_erp_uzbuna_provjera' );
	}
} );

/** Kad je ERP zadnji put pristupio (Unix vrijeme) ili 0. */
function lm_erp_zadnji_pristup() {
	global $wpdb;
	$v = $wpdb->get_var( "SELECT MAX(last_access) FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE '%Tiramisu%'" );
	if ( ! $v ) {
		return 0;
	}
	// last_access se upisuje u lokalnom vremenu sajta.
	$d = date_create_immutable( $v, wp_timezone() );
	return $d ? $d->getTimestamp() : 0;
}

function lm_erp_uzbuna_primaoci() {
	$p = (string) get_option( 'lm_erp_uzbuna_primaoci', 'natalija@ladymom.me' );
	return array_filter( array_map( 'trim', explode( ',', $p ) ), 'is_email' );
}

add_action( 'lm_erp_uzbuna_provjera', 'lm_erp_uzbuna_provjeri' );
function lm_erp_uzbuna_provjeri() {
	$zadnji = lm_erp_zadnji_pristup();
	if ( ! $zadnji ) {
		return;
	}
	$minuta   = (int) floor( ( time() - $zadnji ) / 60 );
	$stanje   = get_option( 'lm_erp_uzbuna_stanje', 'radi' );
	$kad_txt  = wp_date( 'd.m.Y. \u H:i', $zadnji );
	$primaoci = lm_erp_uzbuna_primaoci();

	if ( $minuta > LM_ERP_UZBUNA_MINUTA && 'radi' === $stanje ) {
		update_option( 'lm_erp_uzbuna_stanje', 'stoji', false );
		update_option( 'lm_erp_uzbuna_od', $zadnji, false );
		if ( $primaoci ) {
			wp_mail(
				$primaoci,
				'[ERP] Sinhronizacija sa sajtom je stala',
				"TiramisuERP se nije javio sajtu ladymom.me od {$kad_txt} ({$minuta} minuta).\n\n" .
				"Dok ovo traje, cijene, zalihe i novi artikli se ne osvježavaju na sajtu, a nove porudžbine ne stižu u ERP.\n\n" .
				"Šta uraditi: javiti Data Design-u (Zoran) da provjeri sinhronizaciju na svojoj strani. Sajt i API ključ su u redu, ništa na sajtu nije mijenjano.\n\n" .
				"Kad se ERP ponovo javi, stići će email da je proradio.\n"
			);
		}
	} elseif ( $minuta <= LM_ERP_UZBUNA_MINUTA && 'stoji' === $stanje ) {
		$od = (int) get_option( 'lm_erp_uzbuna_od', 0 );
		update_option( 'lm_erp_uzbuna_stanje', 'radi', false );
		if ( $primaoci ) {
			$trajalo = $od ? human_time_diff( $od, $zadnji ) : '';
			wp_mail(
				$primaoci,
				'[ERP] Sinhronizacija ponovo radi',
				"TiramisuERP se ponovo javlja sajtu (zadnji put {$kad_txt})." . ( $trajalo ? " Prekid je trajao oko {$trajalo}." : '' ) . "\n\n" .
				"Porudžbine iz vremena prekida ERP preuzima sam pri sljedećim pozivima; vrijedi provjeriti u ERP-u da su sve stigle.\n"
			);
		}
	}
}
