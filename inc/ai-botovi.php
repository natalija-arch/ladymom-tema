<?php
/**
 * Lady Mom – AI botovi: koliko AI pretraživači otvaraju sajt i da li ih zaštita odbija
 * (Marko 22.10.2026: sedmični email o posjetama AI botova).
 *
 * Dodatak hostinga Imunify Security po satu bilježi koliko je zahtjeva kojeg bota pušteno, a koliko
 * odbijeno (tabela `{prefix}imunify_bot_hourly`; čuva samo oko dva dana i broji samo stranice koje
 * nijesu došle iz LiteSpeed keša). Zato sajt jednom dnevno sačuva zbir za prethodni dan, a
 * ponedjeljkom pošalje sedmični pregled na natalija@ladymom.me. Pregled: Alati → AI botovi.
 *
 * - Dan je kalendarski dan po vremenu sajta (Europe/Podgorica); `hour_bucket` je Unix sat (UTC).
 * - Opcija po danu `lm_ai_botovi_d_GGGGMMDD` (bez autoload), spisak dana `lm_ai_botovi_dani`, 400 dana.
 * - Broje se provjereni botovi (kategorije verified_ai_crawler i verified_search_engine). Botovi koji
 *   se predstavljaju kao poznati botovi, a dodatak ih ne može potvrditi (unverified_bot), idu zbirno u red
 *   „Neprovjereni botovi“.
 * - GPTBot i CCBot server hostinga odbija prije WordPress-a (403), pa ih u ovoj statistici nema.
 * - Samo čita statistiku dodatka; podešavanja zaštite se ne mijenjaju.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/** Grupe botova za pregled (naziv grupe => tokeni koje Imunify upisuje u kolonu `bot`). */
function lm_ai_botovi_grupe() {
	return array(
		'ChatGPT pretraga'        => array( 'OAI-SearchBot' ),
		'ChatGPT (korisnik pita)' => array( 'ChatGPT-User', 'ChatGPT Agent', 'Operator', 'OpenAI' ),
		'Perplexity'              => array( 'PerplexityBot', 'Perplexity-User' ),
		'Claude pretraga'         => array( 'Claude-SearchBot', 'Claude-User' ),
		'Claude treniranje'       => array( 'ClaudeBot', 'Claude-Web', 'anthropic-ai', 'Claude-Code' ),
		'Google pretraga'         => array( 'Googlebot', 'Googlebot-Image', 'Googlebot-News', 'Googlebot-Video', 'Google-InspectionTool', 'Storebot-Google' ),
		'Google AI i ostalo'      => array( 'GoogleOther', 'GoogleOther-Image', 'GoogleOther-Video', 'Google-Extended', 'Google-CloudVertexBot', 'CloudVertexBot', 'Gemini-Deep-Research', 'Google-Agent', 'GoogleAgent-Mariner', 'GoogleAgent-URLContext', 'Google-NotebookLM', 'NotebookLM' ),
		'Bing i Copilot'          => array( 'Bingbot', 'BingPreview', 'MicrosoftPreview', 'AdIdxBot', 'AzureAI-SearchBot' ),
		'Apple'                   => array( 'Applebot', 'Applebot-Extended' ),
		'Meta AI'                 => array( 'Meta-ExternalAgent', 'Meta-ExternalFetcher', 'meta-externalagent', 'meta-externalfetcher', 'FacebookBot' ),
		'Amazon'                  => array( 'Amazonbot', 'Amzn-SearchBot', 'Amzn-User' ),
		'DuckDuckGo'              => array( 'DuckDuckBot', 'DuckAssistBot' ),
		'Brave'                   => array( 'Bravebot' ),
		'Mistral'                 => array( 'MistralAI-User', 'MistralAI-User/1.0', 'MistralAI-Index' ),
	);
}

/** Grupe koje se uvijek prikazuju (i sa nulom) i za koje se upozorava kad ima odbijenih. */
function lm_ai_botovi_vazne() {
	return array( 'ChatGPT pretraga', 'ChatGPT (korisnik pita)', 'Perplexity', 'Claude pretraga', 'Google pretraga', 'Bing i Copilot', 'Apple' );
}

/** Naziv grupe za jedan red statistike. */
function lm_ai_botovi_grupa( $bot, $kategorija ) {
	if ( 'unverified_bot' === $kategorija ) {
		return 'Neprovjereni botovi';
	}
	foreach ( lm_ai_botovi_grupe() as $naziv => $tokeni ) {
		if ( in_array( $bot, $tokeni, true ) ) {
			return $naziv;
		}
	}
	return 'verified_ai_crawler' === $kategorija ? 'Ostali AI botovi' : 'Ostali pretraživači';
}

/** Tabela dodatka, ili '' ako je nema (dodatak isključen ili obrisan). */
function lm_ai_botovi_tabela() {
	global $wpdb;
	$t = $wpdb->prefix . 'imunify_bot_hourly';
	return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) === $t ? $t : ''; // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * Sačuva zbir za jedan dan (GGGGMMDD po vremenu sajta; podrazumijevano juče).
 * Vraća sačuvani zapis ili false.
 */
function lm_ai_botovi_sacuvaj( $dan = '' ) {
	global $wpdb;
	$t = lm_ai_botovi_tabela();
	if ( '' === $t ) {
		return false;
	}
	$tz = wp_timezone();
	if ( ! preg_match( '/^\d{8}$/', (string) $dan ) ) {
		$dan = ( new DateTimeImmutable( 'yesterday', $tz ) )->format( 'Ymd' );
	}
	$pocetak = DateTimeImmutable::createFromFormat( 'Ymd H:i:s', $dan . ' 00:00:00', $tz );
	if ( ! $pocetak ) {
		return false;
	}
	$h0 = (int) floor( $pocetak->getTimestamp() / HOUR_IN_SECONDS );
	$h1 = (int) floor( $pocetak->modify( '+1 day' )->getTimestamp() / HOUR_IN_SECONDS );

	$redovi = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT category, bot, verdict, SUM(req_count) AS n, MAX(peak_req_min) AS vrh FROM {$t} WHERE hour_bucket >= %d AND hour_bucket < %d AND category IN ('verified_ai_crawler','verified_search_engine','unverified_bot') GROUP BY category, bot, verdict", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$h0,
			$h1
		),
		ARRAY_A
	);
	$prvi = (int) $wpdb->get_var( "SELECT MIN(hour_bucket) FROM {$t}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$botovi = array();
	foreach ( (array) $redovi as $r ) {
		$kljuc = $r['category'] . '|' . $r['bot'];
		if ( ! isset( $botovi[ $kljuc ] ) ) {
			$botovi[ $kljuc ] = array(
				'kat'      => $r['category'],
				'bot'      => $r['bot'],
				'pusteno'  => 0,
				'odbijeno' => 0,
				'vrh'      => 0,
			);
		}
		if ( 'allow' === $r['verdict'] ) {
			$botovi[ $kljuc ]['pusteno'] += (int) $r['n'];
		} else {
			$botovi[ $kljuc ]['odbijeno'] += (int) $r['n'];
		}
		$botovi[ $kljuc ]['vrh'] = max( $botovi[ $kljuc ]['vrh'], (int) $r['vrh'] );
	}
	$zapis = array(
		'dan'       => $dan,
		'botovi'    => array_values( $botovi ),
		'nepotpun'  => $prvi > $h0,
		'sacuvano'  => gmdate( 'c' ),
	);
	update_option( 'lm_ai_botovi_d_' . $dan, $zapis, false );

	$dani   = (array) get_option( 'lm_ai_botovi_dani', array() );
	$dani[] = $dan;
	$dani   = array_values( array_unique( $dani ) );
	sort( $dani );
	$granica = ( new DateTimeImmutable( '-400 days', $tz ) )->format( 'Ymd' );
	foreach ( $dani as $i => $d ) {
		if ( $d < $granica ) {
			delete_option( 'lm_ai_botovi_d_' . $d );
			unset( $dani[ $i ] );
		}
	}
	update_option( 'lm_ai_botovi_dani', array_values( $dani ), false );
	return $zapis;
}

/* Dnevno (03:05 UTC, poslije ponoći po vremenu sajta): zbir za juče. */
add_action( 'lm_ai_botovi_dnevno', 'lm_ai_botovi_sacuvaj' );
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'lm_ai_botovi_dnevno' ) ) {
			$sutra = strtotime( gmdate( 'Y-m-d' ) . ' 03:05:00 UTC' );
			if ( $sutra <= time() ) {
				$sutra += DAY_IN_SECONDS;
			}
			wp_schedule_event( $sutra, 'daily', 'lm_ai_botovi_dnevno' );
			// Prvi put odmah sačuvaj juče, dok je još u tabeli dodatka.
			if ( ! get_option( 'lm_ai_botovi_dani' ) ) {
				wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'lm_ai_botovi_dnevno' );
			}
		}
		if ( ! wp_next_scheduled( 'lm_ai_botovi_sedmicno' ) ) {
			wp_schedule_event( strtotime( 'next monday 05:50:00 UTC' ), 'weekly', 'lm_ai_botovi_sedmicno' );
		}
	}
);

/** Zbir po grupama za listu dana: naziv => [pusteno, odbijeno, vrh]. */
function lm_ai_botovi_zbir( $dani ) {
	$zbir = array();
	foreach ( lm_ai_botovi_vazne() as $g ) {
		$zbir[ $g ] = array(
			'pusteno'  => 0,
			'odbijeno' => 0,
			'vrh'      => 0,
		);
	}
	foreach ( (array) $dani as $d ) {
		$z = get_option( 'lm_ai_botovi_d_' . $d );
		if ( empty( $z['botovi'] ) ) {
			continue;
		}
		foreach ( $z['botovi'] as $b ) {
			$g = lm_ai_botovi_grupa( $b['bot'], $b['kat'] );
			if ( ! isset( $zbir[ $g ] ) ) {
				$zbir[ $g ] = array(
					'pusteno'  => 0,
					'odbijeno' => 0,
					'vrh'      => 0,
				);
			}
			$zbir[ $g ]['pusteno']  += (int) $b['pusteno'];
			$zbir[ $g ]['odbijeno'] += (int) $b['odbijeno'];
			$zbir[ $g ]['vrh']       = max( $zbir[ $g ]['vrh'], (int) $b['vrh'] );
		}
	}
	return $zbir;
}

/** Datum GGGGMMDD → „DD.MM.“ */
function lm_ai_botovi_datum( $d ) {
	return substr( $d, 6, 2 ) . '.' . substr( $d, 4, 2 ) . '.';
}

/** HTML pregleda (isti u emailu i u Alatima). */
function lm_ai_botovi_html( $dani ) {
	$dani  = array_values( (array) $dani );
	$zbir  = lm_ai_botovi_zbir( $dani );
	$vazne = lm_ai_botovi_vazne();
	$html  = '<div style="font-family:Arial,sans-serif;font-size:13px;color:#333;line-height:1.5">';
	$html .= '<p>Koliko su AI pretraživači i pretraživači (Google, Bing) otvarali stranice ladymom.me od ' . esc_html( lm_ai_botovi_datum( $dani[0] ) ) . ' do ' . esc_html( lm_ai_botovi_datum( end( $dani ) ) ) . ' i da li ih je zaštita na sajtu (Imunify Security) odbijala. Broje se samo stranice koje nijesu bile u kešu; stranice iz keša botovi dobijaju bez ograničenja.</p>';

	$upozorenja = array();
	foreach ( $vazne as $g ) {
		if ( ! empty( $zbir[ $g ]['odbijeno'] ) ) {
			$upozorenja[] = $g . ': ' . (int) $zbir[ $g ]['odbijeno'];
		}
	}
	if ( $upozorenja ) {
		$html .= '<p style="padding:8px 10px;background:#fbe9e7;border-left:4px solid #c0392b"><b>Pažnja:</b> zaštita je odbijala botove pretrage (' . esc_html( implode( '; ', $upozorenja ) ) . '). Ako se ponavlja, treba blaže podesiti Imunify Security ili pitati hosting.</p>';
	} else {
		$html .= '<p style="padding:8px 10px;background:#e8f5e9;border-left:4px solid #2e7d32">Zaštita nije odbila nijedan zahtjev botova pretrage (ChatGPT, Perplexity, Claude pretraga, Google, Bing, Apple).</p>';
	}

	uksort(
		$zbir,
		function ( $a, $b ) use ( $zbir, $vazne ) {
			$va = in_array( $a, $vazne, true );
			$vb = in_array( $b, $vazne, true );
			if ( 'Neprovjereni botovi' === $a || 'Neprovjereni botovi' === $b ) {
				return 'Neprovjereni botovi' === $a ? 1 : -1;
			}
			if ( $va !== $vb ) {
				return $va ? -1 : 1;
			}
			return $zbir[ $b ]['pusteno'] <=> $zbir[ $a ]['pusteno'];
		}
	);
	$html .= '<table cellpadding="5" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#ddd"><tr style="background:#f6f1ee"><th align="left">Bot</th><th>Pušteno</th><th>Odbijeno</th><th>Najviše u minuti</th></tr>';
	foreach ( $zbir as $g => $v ) {
		if ( ! in_array( $g, $vazne, true ) && 0 === $v['pusteno'] + $v['odbijeno'] ) {
			continue;
		}
		$crveno = $v['odbijeno'] > 0 && in_array( $g, $vazne, true ) ? ' style="color:#c0392b;font-weight:bold"' : '';
		$html  .= '<tr><td>' . esc_html( $g ) . '</td><td align="right">' . (int) $v['pusteno'] . '</td><td align="right"' . $crveno . '>' . (int) $v['odbijeno'] . '</td><td align="right">' . (int) $v['vrh'] . '</td></tr>';
	}
	$html .= '</table>';

	// Po danima: ChatGPT pretraga i Perplexity (najvažniji za AI odgovore).
	$html .= '<h3 style="margin:18px 0 6px">Po danima</h3><table cellpadding="5" cellspacing="0" border="1" style="border-collapse:collapse;border-color:#ddd"><tr style="background:#f6f1ee"><th align="left">Dan</th><th>ChatGPT pretraga</th><th>ChatGPT (korisnik)</th><th>Perplexity</th><th>Claude (svi)</th><th>Google</th><th>Bing</th><th>Odbijeno (svi)</th></tr>';
	$nepotpuni = array();
	foreach ( $dani as $d ) {
		$z = lm_ai_botovi_zbir( array( $d ) );
		$o = 0;
		foreach ( $z as $g => $v ) {
			if ( 'Neprovjereni botovi' !== $g ) {
				$o += $v['odbijeno'];
			}
		}
		$c = function ( $g ) use ( $z ) {
			return isset( $z[ $g ] ) ? (int) $z[ $g ]['pusteno'] : 0;
		};
		$zapis = get_option( 'lm_ai_botovi_d_' . $d );
		if ( ! empty( $zapis['nepotpun'] ) ) {
			$nepotpuni[] = lm_ai_botovi_datum( $d );
		}
		$html .= '<tr><td>' . esc_html( lm_ai_botovi_datum( $d ) ) . ( ! empty( $zapis['nepotpun'] ) ? '*' : '' ) . '</td><td align="right">' . $c( 'ChatGPT pretraga' ) . '</td><td align="right">' . $c( 'ChatGPT (korisnik pita)' ) . '</td><td align="right">' . $c( 'Perplexity' ) . '</td><td align="right">' . ( $c( 'Claude pretraga' ) + $c( 'Claude treniranje' ) ) . '</td><td align="right">' . $c( 'Google pretraga' ) . '</td><td align="right">' . $c( 'Bing i Copilot' ) . '</td><td align="right">' . (int) $o . '</td></tr>';
	}
	$html .= '</table>';
	if ( $nepotpuni ) {
		$html .= '<p style="color:#777">* Nepotpun dan (statistika dodatka počinje tek tog dana).</p>';
	}
	$html .= '<p style="color:#777">GPTBot (OpenAI, treniranje modela) i CCBot (Common Crawl) server hostinga odbija prije sajta, pa ih ovdje nema; na ChatGPT pretragu to ne utiče. „Neprovjereni botovi“ se predstavljaju kao poznati botovi, ali ne dolaze sa njihovih potvrđenih adresa (mogu biti i lažni). Detalji: Alati → AI botovi.</p>';
	$html .= '</div>';
	return $html;
}

/* Ponedjeljkom (05:50 UTC) sedmični pregled na natalija@ladymom.me. */
function lm_ai_botovi_sedmicni_email() {
	$dani = array_slice( (array) get_option( 'lm_ai_botovi_dani', array() ), -7 );
	if ( ! $dani ) {
		return false;
	}
	$naslov = sprintf( '[AI botovi] sedmični brojevi ladymom.me %s–%s', lm_ai_botovi_datum( $dani[0] ), lm_ai_botovi_datum( end( $dani ) ) );
	return wp_mail( 'natalija@ladymom.me', $naslov, lm_ai_botovi_html( $dani ), array( 'Content-Type: text/html; charset=UTF-8' ) );
}
add_action( 'lm_ai_botovi_sedmicno', 'lm_ai_botovi_sedmicni_email' );

/* Pregled: Alati → AI botovi. */
add_action(
	'admin_menu',
	function () {
		add_management_page( 'AI botovi', 'AI botovi', 'manage_options', 'lm-ai-botovi', 'lm_ai_botovi_stranica' );
	}
);

function lm_ai_botovi_stranica() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	echo '<div class="wrap"><h1>AI botovi</h1>';
	if ( isset( $_POST['lm_ai_botovi_email'] ) && check_admin_referer( 'lm_ai_botovi_email' ) ) {
		$ok = lm_ai_botovi_sedmicni_email();
		echo '<div class="notice ' . ( $ok ? 'notice-success' : 'notice-error' ) . '"><p>' . ( $ok ? 'Email je poslat na natalija@ladymom.me.' : 'Email nije poslat (nema sačuvanih dana ili greška pri slanju).' ) . '</p></div>';
	}
	if ( '' === lm_ai_botovi_tabela() ) {
		echo '<div class="notice notice-error"><p>Nema statistike dodatka Imunify Security (dodatak isključen ili obrisan).</p></div>';
	}
	$dani = (array) get_option( 'lm_ai_botovi_dani', array() );
	echo '<p>Sajt svakog jutra sačuva zbir za prethodni dan iz statistike dodatka Imunify Security (koja sama čuva samo oko dva dana), a ponedjeljkom šalje sedmični pregled na natalija@ladymom.me.</p>';
	if ( ! $dani ) {
		echo '<p>Još nema sačuvanih dana.</p></div>';
		return;
	}
	echo '<h2>Posljednjih 7 dana</h2>';
	echo lm_ai_botovi_html( array_slice( $dani, -7 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( count( $dani ) > 7 ) {
		echo '<h2 style="margin-top:28px">Posljednjih 28 dana</h2>';
		echo lm_ai_botovi_html( array_slice( $dani, -28 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '<form method="post" style="margin:20px 0">';
	wp_nonce_field( 'lm_ai_botovi_email' );
	echo '<button class="button" name="lm_ai_botovi_email" value="1">Pošalji sedmični email sada</button></form>';
	echo '</div>';
}
