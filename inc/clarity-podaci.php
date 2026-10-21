<?php
/**
 * Microsoft Clarity brojevi na sajtu (Marko 21.10.2026: „sajt sam čuva podatke“).
 *
 * Clarity Data Export API daje samo posljednja 1–3 dana i najviše 10 poziva dnevno, pa sajt
 * jednom dnevno povuče brojeve za posljednja 24 sata i sačuva ih. Tako se skuplja istorija
 * koju Claude i Marko čitaju kasnije (Alati → Clarity brojevi).
 *
 * - API token upisuje Marko: Podešavanja → Opšte → „Microsoft Clarity – API token“
 *   (opcija `lm_clarity_api_token`, nije u kodu). Clarity → Settings → Data export.
 * - Dnevno 4 poziva: ukupno, po uređaju, po kanalu, po stranici (URL).
 * - Ponedjeljkom (05:40 UTC) sedmični pregled emailom na natalija@ladymom.me (čita ga i
 *   sedmični izvještaj o oglasima preko Gmail-a).
 * - Čuvanje: opcija po danu `lm_clarity_d_GGGGMMDD` (bez autoload), spisak dana u
 *   `lm_clarity_dani`. Po stranicama se čuva prvih 20 redova svake metrike. Stariji od 400 dana
 *   se brišu (to je samo kopija Clarity brojeva).
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

const LM_CLARITY_API = 'https://www.clarity.ms/export-data/api/v1/project-live-insights';

/** Jedan poziv API-ja. Vraća niz metrika ili WP_Error. */
function lm_clarity_poziv( $dimenzija = '' ) {
	$token = trim( (string) get_option( 'lm_clarity_api_token', '' ) );
	if ( '' === $token ) {
		return new WP_Error( 'lm_clarity_token', 'Nema API tokena (Podešavanja → Opšte).' );
	}
	$args = array( 'numOfDays' => 1 );
	if ( $dimenzija ) {
		$args['dimension1'] = $dimenzija;
	}
	$odgovor = wp_remote_get(
		add_query_arg( $args, LM_CLARITY_API ),
		array(
			'timeout' => 20,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
		)
	);
	if ( is_wp_error( $odgovor ) ) {
		return $odgovor;
	}
	$kod = (int) wp_remote_retrieve_response_code( $odgovor );
	if ( 200 !== $kod ) {
		$poruke = array(
			401 => 'token nije ispravan ili je istekao',
			403 => 'nema dozvole',
			400 => 'pogrešni parametri',
			429 => 'potrošeno 10 poziva za danas',
		);
		return new WP_Error( 'lm_clarity_http', 'Clarity HTTP ' . $kod . ( isset( $poruke[ $kod ] ) ? ' (' . $poruke[ $kod ] . ')' : '' ) );
	}
	$podaci = json_decode( wp_remote_retrieve_body( $odgovor ), true );
	return is_array( $podaci ) ? $podaci : new WP_Error( 'lm_clarity_json', 'Clarity: neočekivan odgovor.' );
}

/** Skrati svaku metriku na prvih $n redova. */
function lm_clarity_skrati( $metrike, $n ) {
	$out = array();
	foreach ( (array) $metrike as $m ) {
		if ( ! is_array( $m ) || empty( $m['metricName'] ) ) {
			continue;
		}
		$info  = isset( $m['information'] ) && is_array( $m['information'] ) ? array_slice( $m['information'], 0, $n ) : array();
		$out[] = array(
			'metricName'  => (string) $m['metricName'],
			'information' => $info,
		);
	}
	return $out;
}

/** Povuci i sačuvaj današnji presjek (posljednja 24 sata). */
function lm_clarity_povuci() {
	$dan    = gmdate( 'Ymd' );
	$zapis  = array(
		'vrijeme' => gmdate( 'c' ),
		'greske'  => array(),
	);
	$pozivi = array(
		'ukupno'   => array( '', 5 ),
		'uredjaj'  => array( 'Device', 10 ),
		'kanal'    => array( 'Channel', 15 ),
		'stranica' => array( 'URL', 20 ),
	);
	$ok = 0;
	foreach ( $pozivi as $kljuc => $p ) {
		$r = lm_clarity_poziv( $p[0] );
		if ( is_wp_error( $r ) ) {
			$zapis['greske'][ $kljuc ] = $r->get_error_message();
			// Bez tokena ili sa lošim tokenom nema smisla trošiti ostale pozive.
			if ( in_array( $r->get_error_code(), array( 'lm_clarity_token' ), true ) || false !== strpos( $r->get_error_message(), '401' ) || false !== strpos( $r->get_error_message(), '429' ) ) {
				break;
			}
			continue;
		}
		$zapis[ $kljuc ] = lm_clarity_skrati( $r, $p[1] );
		++$ok;
	}
	update_option( 'lm_clarity_zadnje', array( 'vrijeme' => $zapis['vrijeme'], 'ok' => $ok, 'greske' => $zapis['greske'] ), false );
	if ( ! $ok ) {
		return false;
	}
	update_option( 'lm_clarity_d_' . $dan, $zapis, false );

	$dani = (array) get_option( 'lm_clarity_dani', array() );
	if ( ! in_array( $dan, $dani, true ) ) {
		$dani[] = $dan;
	}
	sort( $dani );
	$granica = gmdate( 'Ymd', time() - 400 * DAY_IN_SECONDS );
	foreach ( $dani as $i => $d ) {
		if ( $d < $granica ) {
			delete_option( 'lm_clarity_d_' . $d );
			unset( $dani[ $i ] );
		}
	}
	update_option( 'lm_clarity_dani', array_values( $dani ), false );
	return true;
}

/* Dnevni zadatak (05:20 UTC). */
add_action( 'lm_clarity_dnevno', 'lm_clarity_povuci' );
add_action(
	'init',
	function () {
		if ( '' !== (string) get_option( 'lm_clarity_api_token', '' ) && ! wp_next_scheduled( 'lm_clarity_dnevno' ) ) {
			$sutra = strtotime( gmdate( 'Y-m-d' ) . ' 05:20:00 UTC' );
			if ( $sutra <= time() ) {
				$sutra += DAY_IN_SECONDS;
			}
			wp_schedule_event( $sutra, 'daily', 'lm_clarity_dnevno' );
		}
	}
);

/*
 * Sedmični pregled emailom (ponedjeljak 05:40 UTC) na natalija@ladymom.me. Iz njega ga čita
 * i sedmični izvještaj o oglasima (Gmail), jer zakazani zadaci nemaju pristup administraciji.
 */
function lm_clarity_red( $info ) {
	$djelovi = array();
	foreach ( (array) $info as $k => $v ) {
		$djelovi[] = $k . '=' . lm_clarity_vrijednost( $v );
	}
	return implode( '; ', $djelovi );
}

function lm_clarity_sedmicni_email() {
	$dani = array_slice( (array) get_option( 'lm_clarity_dani', array() ), -7 );
	if ( ! $dani ) {
		return false;
	}
	$html  = '<div style="font-family:Arial,sans-serif;font-size:13px;color:#333">';
	$html .= '<p>Clarity brojevi za ladymom.me, posljednjih ' . count( $dani ) . ' dana (svaki dan = posljednja 24 sata do jutarnjeg povlačenja, UTC). Detalji: Alati → Clarity brojevi.</p>';
	$html .= '<h3>Po danima (ukupno)</h3><table cellpadding="4" cellspacing="0" border="1" style="border-collapse:collapse">';
	foreach ( $dani as $d ) {
		$z = get_option( 'lm_clarity_d_' . $d );
		if ( empty( $z['ukupno'] ) ) {
			continue;
		}
		foreach ( $z['ukupno'] as $m ) {
			$prvi  = ! empty( $m['information'][0] ) ? $m['information'][0] : array();
			$html .= '<tr><td>' . esc_html( substr( $d, 6, 2 ) . '.' . substr( $d, 4, 2 ) . '.' ) . '</td><td>' . esc_html( $m['metricName'] ) . '</td><td>' . esc_html( lm_clarity_red( $prvi ) ) . '</td></tr>';
		}
	}
	$html   .= '</table>';
	$zadnji  = get_option( 'lm_clarity_d_' . end( $dani ) );
	$dijelovi = array(
		'uredjaj'  => array( 'Po uređaju (posljednji dan)', 5 ),
		'kanal'    => array( 'Po kanalu (posljednji dan)', 8 ),
		'stranica' => array( 'Po stranici (posljednji dan, prvih 10)', 10 ),
	);
	foreach ( $dijelovi as $k => $p ) {
		if ( empty( $zadnji[ $k ] ) ) {
			continue;
		}
		$html .= '<h3>' . esc_html( $p[0] ) . '</h3>';
		foreach ( $zadnji[ $k ] as $m ) {
			$redovi = array_slice( (array) $m['information'], 0, $p[1] );
			if ( ! $redovi ) {
				continue;
			}
			$html .= '<p style="margin:10px 0 2px"><b>' . esc_html( $m['metricName'] ) . '</b></p><ul style="margin:0">';
			foreach ( $redovi as $r ) {
				$html .= '<li>' . esc_html( lm_clarity_red( $r ) ) . '</li>';
			}
			$html .= '</ul>';
		}
	}
	$html .= '</div>';
	$od    = $dani[0];
	$do    = end( $dani );
	$naslov = sprintf( '[Clarity] sedmični brojevi ladymom.me %s.%s.–%s.%s.', substr( $od, 6, 2 ), substr( $od, 4, 2 ), substr( $do, 6, 2 ), substr( $do, 4, 2 ) );
	return wp_mail( 'natalija@ladymom.me', $naslov, $html, array( 'Content-Type: text/html; charset=UTF-8' ) );
}
add_action( 'lm_clarity_sedmicno', 'lm_clarity_sedmicni_email' );
add_action(
	'init',
	function () {
		if ( '' !== (string) get_option( 'lm_clarity_api_token', '' ) && ! wp_next_scheduled( 'lm_clarity_sedmicno' ) ) {
			wp_schedule_event( strtotime( 'next monday 05:40:00 UTC' ), 'weekly', 'lm_clarity_sedmicno' );
		}
	}
);

/* Polje za token: Podešavanja → Opšte. */
add_action(
	'admin_init',
	function () {
		register_setting(
			'general',
			'lm_clarity_api_token',
			array(
				'type'              => 'string',
				'sanitize_callback' => function ( $nova ) {
					$nova = trim( sanitize_text_field( (string) $nova ) );
					return '' === $nova ? (string) get_option( 'lm_clarity_api_token', '' ) : $nova;
				},
			)
		);
		add_settings_field(
			'lm_clarity_api_token',
			'Microsoft Clarity – API token',
			function () {
				$ima = '' !== (string) get_option( 'lm_clarity_api_token', '' );
				printf(
					'<input type="password" name="lm_clarity_api_token" id="lm_clarity_api_token" value="" class="regular-text" autocomplete="new-password" placeholder="%s"><p class="description">%s</p>',
					esc_attr( $ima ? 'Sačuvano (ostavite prazno da ostane isto)' : '' ),
					esc_html( 'Clarity → Settings → Data export → Generate new API token. Sajt jednom dnevno čuva Clarity brojeve (Alati → Clarity brojevi).' )
				);
			},
			'general'
		);
	}
);

/* Prvi presjek odmah kad se token prvi put sačuva. */
add_action( 'add_option_lm_clarity_api_token', 'lm_clarity_povuci_prvi_put' );
add_action( 'update_option_lm_clarity_api_token', 'lm_clarity_povuci_prvi_put' );
function lm_clarity_povuci_prvi_put() {
	if ( ! get_option( 'lm_clarity_dani' ) ) {
		lm_clarity_povuci();
	}
}

/* Pregled: Alati → Clarity brojevi. */
add_action(
	'admin_menu',
	function () {
		add_management_page( 'Clarity brojevi', 'Clarity brojevi', 'manage_options', 'lm-clarity', 'lm_clarity_stranica' );
	}
);

function lm_clarity_vrijednost( $v ) {
	if ( is_array( $v ) ) {
		return wp_json_encode( $v );
	}
	return (string) $v;
}

function lm_clarity_tabela( $metrike ) {
	foreach ( (array) $metrike as $m ) {
		$redovi = isset( $m['information'] ) ? (array) $m['information'] : array();
		echo '<h4 style="margin:18px 0 6px">' . esc_html( $m['metricName'] ) . '</h4>';
		if ( ! $redovi ) {
			echo '<p>—</p>';
			continue;
		}
		$kolone = array();
		foreach ( $redovi as $r ) {
			$kolone = array_unique( array_merge( $kolone, array_keys( (array) $r ) ) );
		}
		echo '<table class="widefat striped" style="max-width:1100px"><thead><tr>';
		foreach ( $kolone as $k ) {
			echo '<th>' . esc_html( $k ) . '</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ( $redovi as $r ) {
			echo '<tr>';
			foreach ( $kolone as $k ) {
				echo '<td>' . esc_html( isset( $r[ $k ] ) ? lm_clarity_vrijednost( $r[ $k ] ) : '' ) . '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table>';
	}
}

function lm_clarity_stranica() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( isset( $_POST['lm_clarity_sada'] ) && check_admin_referer( 'lm_clarity_sada' ) ) {
		$zadnje = (array) get_option( 'lm_clarity_zadnje', array() );
		if ( ! empty( $zadnje['vrijeme'] ) && strtotime( $zadnje['vrijeme'] ) > time() - HOUR_IN_SECONDS ) {
			echo '<div class="notice notice-warning"><p>Posljednje povlačenje je bilo prije manje od sat vremena (Clarity dozvoljava 10 poziva dnevno).</p></div>';
		} else {
			lm_clarity_povuci();
		}
	}
	$dani   = array_reverse( (array) get_option( 'lm_clarity_dani', array() ) );
	$izabr  = isset( $_GET['dan'] ) ? preg_replace( '/[^0-9]/', '', (string) wp_unslash( $_GET['dan'] ) ) : ( $dani ? $dani[0] : '' );
	$zadnje = (array) get_option( 'lm_clarity_zadnje', array() );

	echo '<div class="wrap"><h1>Clarity brojevi</h1>';
	echo '<p>Sajt jednom dnevno (05:20 UTC) čuva Clarity brojeve za posljednja 24 sata. Snimci i mape klikova su u Clarity-ju.</p>';
	if ( '' === (string) get_option( 'lm_clarity_api_token', '' ) ) {
		echo '<div class="notice notice-error"><p>Nema API tokena: Podešavanja → Opšte → „Microsoft Clarity – API token“.</p></div>';
	}
	if ( $zadnje ) {
		echo '<p><b>Posljednje povlačenje:</b> ' . esc_html( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', strtotime( $zadnje['vrijeme'] ) ), 'd.m.Y. H:i' ) ) . ', uspješnih poziva: ' . (int) $zadnje['ok'] . '/4';
		if ( ! empty( $zadnje['greske'] ) ) {
			echo ' — <span style="color:#b32d2e">' . esc_html( implode( '; ', (array) $zadnje['greske'] ) ) . '</span>';
		}
		echo '</p>';
	}
	echo '<form method="post" style="margin:8px 0 16px">';
	wp_nonce_field( 'lm_clarity_sada' );
	echo '<button class="button" name="lm_clarity_sada" value="1">Povuci sada</button></form>';

	if ( ! $dani ) {
		echo '<p>Još nema sačuvanih dana.</p></div>';
		return;
	}
	echo '<form method="get"><input type="hidden" name="page" value="lm-clarity"><select name="dan">';
	foreach ( $dani as $d ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $d ), selected( $d, $izabr, false ), esc_html( substr( $d, 6, 2 ) . '.' . substr( $d, 4, 2 ) . '.' . substr( $d, 0, 4 ) ) );
	}
	echo '</select> <button class="button">Prikaži</button></form>';

	$zapis = get_option( 'lm_clarity_d_' . $izabr );
	if ( ! is_array( $zapis ) ) {
		echo '<p>Nema podataka za taj dan.</p></div>';
		return;
	}
	$naslovi = array(
		'ukupno'   => 'Ukupno (posljednja 24 sata)',
		'uredjaj'  => 'Po uređaju',
		'kanal'    => 'Po kanalu',
		'stranica' => 'Po stranici (prvih 20)',
	);
	foreach ( $naslovi as $k => $naslov ) {
		if ( empty( $zapis[ $k ] ) ) {
			continue;
		}
		echo '<h2 style="margin-top:28px">' . esc_html( $naslov ) . '</h2>';
		lm_clarity_tabela( $zapis[ $k ] );
	}
	echo '</div>';
}
