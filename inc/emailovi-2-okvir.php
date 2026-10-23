<?php
/**
 * Lady Mom – emailovi (2/4): okvir (zaglavlje, footer), dugmad, kutije, telefon i tamni režim.
 * (Ranije Code Snippets #23, prebačeno u child temu.)
 *
 * Prepravka 20.10.2026 (pregled svih emailova na telefonu, svijetlo i tamno):
 * - Dugmad: tabela sa obojenom ćelijom („bulletproof“), visina 52 px, 14 px tekst, ugao 8 px.
 *   Na telefonu je dugme cijele širine; dva dugmeta stoje jedno ispod drugog.
 *   Ranije: pill 13 px sa 13 px gore/dole, izgledalo je skučeno.
 * - Telefon (≤ 620 px): kartica ide od ivice do ivice, bočno 20 px umjesto 40 px,
 *   kartice Dostava/Plaćanje i adresa/napomena jedna ispod druge, naslov 25 px.
 * - Tamni režim:
 *   - Apple Mail (iPhone, Mac), Outlook (Mac, iPhone, Android, Outlook.com), Samsung Email i Thunderbird
 *     čitaju naš CSS (`prefers-color-scheme: dark`, `[data-ogsc]`/`[data-ogsb]`) i dobijaju paletu iz
 *     lm_em_tamne_boje(); zaglavlje se tamo mijenja tamnim blokom sa bijelim logom.
 *   - Gmail i Outlook za Windows boje mijenjaju sami i naš CSS za tamno ne čitaju. Zato je zaglavlje
 *     slika sa svojom pozadinom, logo u footeru stoji na neprozirnoj tirkiznoj pločici, a bijeli tekst
 *     na glavnom dugmetu ostaje bijel i u Gmail-u (trik sa mix-blend-mode koji vidi samo Gmail).
 * - Klase za tamni režim (lmd-t-*, lmd-b-*, lmd-o-*) dodaje lm_em_dovrsi() sama, po boji iz inline stila,
 *   pa nova boja u emailu treba samo da se upiše u lm_em_tamne_boje().
 */

defined( 'ABSPATH' ) || exit;

/** Paleta za tamni režim: svijetla boja iz emaila => tamna zamjena (bez #). */
function lm_em_tamne_boje() {
	return array(
		// Tekst.
		'tekst'    => array(
			'6d4e39' => 'f3e3d7', // naslovi (smeđa)
			'7a6250' => 'd8c4b6', // osnovni tekst
			'8a7565' => 'b5a092', // sitan, prigušen tekst
			'a05c57' => 'eba59d', // nadnaslovi, oznake (roze)
			'8a4b47' => 'efb2aa', // linkovi (tamna roze)
			'3f6265' => '8fc7c2', // tirkizni tekst („Besplatna“, Ready Mom)
			'2f4a4c' => 'cfe8e5', // naslov u mint kutiji
		),
		// Pozadine.
		'pozadina' => array(
			'ffffff' => '211a16', // kartica
			'fdf2ec' => '2c231e', // krem kutija (spoljašnja pozadina ima svoju klasu lm-ovoj)
			'f6ded4' => '3a2824', // blijedo roze kutija
			'd6ede9' => '1e3433', // mint kutija
			'e8d5cb' => '4c3e36', // linija statusa
		),
		// Okviri i linije.
		'okvir'    => array(
			'f3e6de' => '3a2f29',
			'f6ded4' => '4a3631',
			'e8d5cb' => '4c3e36',
			'e8d8cd' => '4c3e36',
			'a05c57' => 'c98b84',
		),
	);
}

/** CSS u <head>: telefon, tamni režim, Outlook i Gmail. Odvojeni <style> da greška u jednom ne obori ostale. */
function lm_em_css() {
	$b     = lm_em_tamne_boje();
	$tamno = '';
	$ogsc  = '';
	$ogsb  = '';
	foreach ( $b['tekst'] as $s => $t ) {
		$tamno .= '.lmd-t-' . $s . '{color:#' . $t . '!important}';
		$ogsc  .= '[data-ogsc] .lmd-t-' . $s . '{color:#' . $t . '!important}';
	}
	foreach ( $b['pozadina'] as $s => $t ) {
		$tamno .= '.lmd-b-' . $s . '{background-color:#' . $t . '!important}';
		$ogsb  .= '[data-ogsb] .lmd-b-' . $s . '{background-color:#' . $t . '!important}';
	}
	foreach ( $b['okvir'] as $s => $t ) {
		$tamno .= '.lmd-o-' . $s . '{border-color:#' . $t . '!important}';
	}
	// Posebni slučajevi poslije palete (ista specifičnost, kasnije pravilo pobjeđuje).
	$tamno .= '.lm-ovoj{background-color:#161210!important}'
		. '.lm-hdr{background-color:#211a16!important;border-color:#3a2f29!important}'
		. '.lm-hdr-svijetlo{display:none!important}'
		. '.lm-hdr-tamno{display:block!important;max-height:none!important;overflow:visible!important}'
		. '.lm-btn-o{background-color:transparent!important}';
	$ogsb .= '[data-ogsb] .lm-ovoj{background-color:#161210!important}[data-ogsb] .lm-btn-o{background-color:transparent!important}';

	$telefon = '@media only screen and (max-width:620px){'
		. '.lm-ovoj-td{padding:0!important}'
		. '.lm-kartica{border-radius:0!important}'
		. '.lm-px{padding-left:20px!important;padding-right:20px!important}'
		. '.lm-hero{padding-top:30px!important}'
		. '.lm-h1{font-size:25px!important;line-height:1.2!important}'
		. '.lm-stack{display:block!important;width:100%!important;max-width:100%!important;box-sizing:border-box!important;padding-left:0!important;padding-right:0!important}'
		. '.lm-stack-2{padding-top:12px!important}'
		. '.lm-btn-t{width:100%!important}'
		. '.lm-box-td{padding:18px!important}'
		. '}';
	// Gmail (samo on ima <u></u> ispred tijela): bijeli tekst na dugmetu ostaje bijel kad Gmail obrne boje.
	$gmail = 'u + .body .lm-gb-s{background:#000;mix-blend-mode:screen}u + .body .lm-gb-d{background:#000;mix-blend-mode:difference}';

	return '<style type="text/css">:root{color-scheme:light dark;supported-color-schemes:light dark}' . $telefon . $gmail . '</style>'
		. '<style type="text/css">@media (prefers-color-scheme: dark){' . $tamno . '}</style>'
		. '<style type="text/css">' . $ogsc . $ogsb . '</style>';
}

/**
 * Završna obrada HTML-a (poslije WooCommerce Emogrifier-a): klase za tamni režim po boji i CSS u <head>.
 * Radi samo na emailovima sa našim okvirom (klasa lm-em-okvir).
 */
function lm_em_dovrsi( $html ) {
	if ( ! is_string( $html ) || false === strpos( $html, 'lm-em-okvir' ) || false !== strpos( $html, 'lm-em-dovrseno' ) ) {
		return $html;
	}
	$b   = lm_em_tamne_boje();
	$poz = stripos( $html, '<body' );
	if ( false === $poz ) {
		return $html;
	}
	$glava  = substr( $html, 0, $poz );
	$tijelo = substr( $html, $poz );
	$tijelo = preg_replace_callback(
		'/<([a-z][a-z0-9]*)\b[^>]*\bstyle\s*=\s*("|\')(.*?)\2[^>]*>/is',
		function ( $m ) use ( $b ) {
			$kl = array();
			foreach ( explode( ';', strtolower( html_entity_decode( $m[3], ENT_QUOTES ) ) ) as $dek ) {
				$p = strpos( $dek, ':' );
				if ( false === $p || ! preg_match( '/#([0-9a-f]{6}|[0-9a-f]{3})(?![0-9a-f])/', substr( $dek, $p + 1 ), $h ) ) {
					continue;
				}
				$hex = 3 === strlen( $h[1] ) ? $h[1][0] . $h[1][0] . $h[1][1] . $h[1][1] . $h[1][2] . $h[1][2] : $h[1];
				$sv  = trim( substr( $dek, 0, $p ) );
				if ( 'color' === $sv && isset( $b['tekst'][ $hex ] ) ) {
					$kl[] = 'lmd-t-' . $hex;
				} elseif ( ( 'background' === $sv || 'background-color' === $sv ) && isset( $b['pozadina'][ $hex ] ) ) {
					$kl[] = 'lmd-b-' . $hex;
				} elseif ( 0 === strpos( $sv, 'border' ) && isset( $b['okvir'][ $hex ] ) ) {
					$kl[] = 'lmd-o-' . $hex;
				}
			}
			if ( ! $kl ) {
				return $m[0];
			}
			$kl  = implode( ' ', array_unique( $kl ) );
			$tag = $m[0];
			if ( preg_match( '/\sclass\s*=\s*("|\')/i', $tag ) ) {
				return preg_replace( '/\sclass\s*=\s*("|\')(.*?)\1/is', ' class=$1$2 ' . $kl . '$1', $tag, 1 );
			}
			return '<' . $m[1] . ' class="' . $kl . '"' . substr( $tag, strlen( $m[1] ) + 1 );
		},
		$tijelo
	);
	if ( ! is_string( $tijelo ) ) {
		// Obrada nije uspjela (npr. PCRE ograničenje na velikom emailu): šalje se email bez
		// dorade za tamni režim, umjesto praznog (24.10.2026).
		return $html;
	}
	$tijelo = preg_replace( '/<body\b/i', '<body data-lm="lm-em-dovrseno"', $tijelo, 1 );
	if ( ! is_string( $tijelo ) ) {
		return $html;
	}
	if ( false !== stripos( $glava, '</head>' ) ) {
		$glava = preg_replace( '/<\/head>/i', lm_em_css() . '</head>', $glava, 1 );
	} else {
		$glava .= lm_em_css();
	}
	return $glava . $tijelo;
}
add_filter( 'woocommerce_mail_content', 'lm_em_dovrsi', 20 );

/** Email prodavnici (butiku) o porudžbini. */
function lm_em_je_admin( $email ) {
	return $email && is_object( $email ) && isset( $email->id ) && function_exists( 'lm_em_admin_ids' ) && in_array( $email->id, lm_em_admin_ids(), true );
}

/**
 * Zaglavlje. $email može biti null (zahtjev za povrat), tada se preheader daje kroz $GLOBALS['lm_em_pre'].
 */
function lm_em_header( $email_heading, $email = null ) {
	$f = lm_em_font();
	if ( ! empty( $GLOBALS['lm_em_pre'] ) ) {
		$pre = $GLOBALS['lm_em_pre'];
	} elseif ( lm_em_je_admin( $email ) ) {
		$pre = function_exists( 'lm_em_admin_pre' ) ? lm_em_admin_pre( lm_em_order( $email ) ) : '';
	} elseif ( $email ) {
		$c   = ( function_exists( 'lm_em_je_dodatni' ) && lm_em_je_dodatni( $email ) ) ? lm_em_dodatni_copy( $email ) : lm_em_copy( $email );
		$pre = $c['pre'];
	} else {
		$pre = '';
	}
	$hdr  = wp_get_attachment_url( lm_em_media( 'zaglavlje' ) );
	$logo = wp_get_attachment_url( lm_em_media( 'logo_bijeli' ) );
	$home = esc_url( home_url( '/' ) );
	echo '<!DOCTYPE html><html lang="sr-ME"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="x-apple-disable-message-reformatting"><meta name="format-detection" content="telephone=no, date=no, address=no, email=no">';
	echo '<meta name="color-scheme" content="light dark"><meta name="supported-color-schemes" content="light dark"><title>Lady Mom</title>';
	echo '<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700;800&amp;display=swap" rel="stylesheet"></head>';
	echo '<body class="body lm-ovoj" style="margin:0;padding:0;background:#fdf2ec;-webkit-text-size-adjust:100%;' . $f . '">';
	if ( $pre ) {
		echo '<div style="font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;color:#fdf2ec">' . esc_html( $pre ) . str_repeat( '&#847;&zwnj;&nbsp;', 60 ) . '</div>';
	}
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="lm-em-okvir lm-ovoj" style="background:#fdf2ec"><tr><td align="center" class="lm-ovoj-td" style="padding:24px 10px">';
	echo '<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" class="lm-kartica" style="width:100%;max-width:600px;background:#ffffff;border-radius:18px;overflow:hidden">';
	echo '<tr><td class="lm-hdr" style="background:#fdf2ec;border-bottom:1px solid #f6ded4;font-size:0;line-height:0">';
	if ( $hdr ) {
		echo '<a href="' . $home . '" class="lm-hdr-svijetlo" style="display:block"><img src="' . esc_url( $hdr ) . '" width="600" alt="Lady Mom" style="display:block;width:100%;max-width:600px;height:auto;border:0"></a>';
		if ( $logo ) {
			// Tamni režim (Apple Mail, Outlook): tamni blok sa bijelim logom umjesto svijetle slike.
			echo '<div class="lm-hdr-tamno -emogrifier-keep" style="display:none;max-height:0;overflow:hidden;mso-hide:all;background:#211a16;text-align:center">'
				. '<a href="' . $home . '" style="display:block;padding:30px 20px;text-decoration:none"><img src="' . esc_url( $logo ) . '" width="150" alt="Lady Mom" style="display:inline-block;width:150px;height:auto;border:0"></a></div>';
		}
	} else {
		echo '<div style="padding:34px;text-align:center;font-size:28px;line-height:1.2;color:#6d4e39;letter-spacing:.08em">LADY MOM</div>';
	}
	echo '</td></tr><tr><td style="padding:0">';
}

/**
 * Footer. Opcije (za emailove bez WooCommerce objekta): 'kontakt' => naslov kutije sa kontaktom ili false.
 * Razlog slanja i link za odjavu: $GLOBALS['lm_em_razlog'] = array( razlog, link ).
 */
function lm_em_footer( $email = null, $opcije = array() ) {
	$f     = lm_em_font();
	$o     = lm_em_order( $email );
	$admin = lm_em_je_admin( $email );
	echo '</td></tr>';
	$kontakt = array_key_exists( 'kontakt', $opcije ) ? $opcije['kontakt'] : ( ( $o && ! $admin ) ? 'Pitanje o porudžbini?' : false );
	if ( $kontakt ) {
		echo lm_em_red( lm_em_kontakt_box( $kontakt ), 32 );
	}
	// Poziv na Ready Mom se ne šalje onome ko je upravo kupio program.
	if ( $email && ! $admin && in_array( $email->id, array( 'customer_processing_order', 'customer_new_account' ), true ) && ! ( function_exists( 'lm_em_ima_rm' ) && lm_em_ima_rm( $o ) ) ) {
		echo lm_em_red( '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#d6ede9;border-radius:16px"><tr>'
			. '<td class="lm-box-td" style="padding:20px 24px;' . $f . '"><div style="font-size:12px;font-weight:700;letter-spacing:.12em;color:#3f6265">ŠKOLA ZA TRUDNICE</div><div style="font-size:16px;font-weight:700;color:#2f4a4c;margin:4px 0 10px">Ready Mom — prijave su otvorene</div>'
			. '<a href="' . esc_url( function_exists( 'lm_skola_url' ) && lm_skola_url() ? lm_skola_url() : home_url( '/ready-mom/' ) ) . '" style="font-size:14px;font-weight:700;color:#2f4a4c;text-decoration:underline">Saznajte više →</a></td></tr></table>', 14 );
	}
	$logo  = wp_get_attachment_url( lm_em_media( 'logo_footer' ) );
	$logo  = $logo ? $logo : wp_get_attachment_url( lm_em_media( 'logo_bijeli' ) );
	$links = array( 'Prodavnica' => '/prodavnica/', 'Dostava' => '/dostava/', 'Povrat i zamjena' => '/povrat-robe-i-sredstava/', 'Uslovi' => '/uslovi-koriscenja/' );
	$l     = array();
	foreach ( $links as $t => $u ) {
		$l[] = '<a href="' . esc_url( home_url( $u ) ) . '" style="color:#e3efee;text-decoration:underline;white-space:nowrap">' . $t . '</a>';
	}
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	if ( $admin ) {
		$why = 'Obavještenje za butik o porudžbini na ' . $host . '.';
	} else {
		$why = $o ? 'Ovaj email ste dobili jer ste poručili na ' . $host . '.' : 'Ovaj email ste dobili jer imate nalog na ' . $host . '.';
	}
	$odjava = '';
	if ( ! empty( $GLOBALS['lm_em_razlog'] ) ) {
		$why    = $GLOBALS['lm_em_razlog'][0];
		$odjava = $GLOBALS['lm_em_razlog'][1];
	}
	echo '<tr><td style="padding:40px 0 0;font-size:0;line-height:0">&nbsp;</td></tr><tr><td align="center" class="lm-px" style="background:#3f6265;padding:34px 40px 30px;text-align:center;' . $f . '">';
	if ( $logo ) {
		echo '<img src="' . esc_url( $logo ) . '" width="128" alt="Lady Mom" style="display:block;margin:0 auto 14px;width:128px;height:auto;border:0">';
	}
	echo '<div style="font-size:14px;line-height:1.6;color:#d4e5e4;margin:0 0 12px">Butik: Branka Deletića bb, Podgorica<br>ponedjeljak–subota 9:30–21:00</div>';
	echo '<div style="font-size:13px;font-weight:600;line-height:2;color:#e3efee">' . implode( '&nbsp;&nbsp;·&nbsp;&nbsp;', $l ) . '</div>';
	echo '<div style="height:1px;background:#557779;margin:18px 0 14px;font-size:0;line-height:0">&nbsp;</div>';
	echo '<div style="font-size:12px;line-height:1.6;color:#b9d2d0">Tangerine Limited d.o.o. · PIB 03464415<br>' . esc_html( $why )
		. ( $odjava ? ' <a href="' . esc_url( $odjava ) . '" style="color:#e3efee;text-decoration:underline">Ne želim više ove emailove</a>' : '' ) . '</div>';
	echo '</td></tr></table></td></tr></table></body></html>';
}

/** Red sadržaja sa bočnim razmakom (40 px, na telefonu 20 px). */
function lm_em_red( $html, $gore = 26, $dole = 0 ) {
	return '<tr><td class="lm-px" style="padding:' . (int) $gore . 'px 40px ' . (int) $dole . 'px;' . lm_em_font() . '">' . $html . '</td></tr>';
}

/**
 * Dugme. Glavno: puna roze ćelija (Gmail ne dira gradijent, a bijeli tekst čuva lm-gb-*).
 * Sporedno ($outline): roze okvir i roze tekst.
 */
function lm_em_btn( $url, $label, $outline = false ) {
	$f = lm_em_font();
	$a = 'display:block;' . $f . 'font-size:14px;font-weight:700;letter-spacing:.06em;line-height:20px;text-decoration:none;text-align:center;white-space:nowrap;';
	if ( $outline ) {
		return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" class="lm-btn-t" style="border-collapse:separate"><tr>'
			. '<td align="center" class="lm-btn-o" style="background:#ffffff;border:1.5px solid #a05c57;border-radius:8px;mso-padding-alt:15px 26px">'
			. '<a href="' . esc_url( $url ) . '" style="' . $a . 'padding:15px 26px;color:#8a4b47;border-radius:8px">' . esc_html( $label ) . '</a></td></tr></table>';
	}
	return '<table role="presentation" cellpadding="0" cellspacing="0" border="0" class="lm-btn-t" style="border-collapse:separate"><tr>'
		. '<td align="center" bgcolor="#a05c57" style="background-color:#a05c57;background-image:linear-gradient(#a05c57,#a05c57);border-radius:8px;mso-padding-alt:16px 28px">'
		. '<a href="' . esc_url( $url ) . '" style="' . $a . 'padding:16px 28px;color:#ffffff;border-radius:8px">'
		. '<span class="lm-gb-s" style="display:block"><span class="lm-gb-d" style="display:block">' . esc_html( $label ) . '</span></span></a></td></tr></table>';
}

/** Više dugmadi: u redu na računaru, jedno ispod drugog na telefonu. $dugmad = [ [url, natpis, outline], … ]. */
function lm_em_btns( $dugmad ) {
	$dugmad = array_values( array_filter( $dugmad ) );
	$n      = count( $dugmad );
	$o      = '<table role="presentation" cellpadding="0" cellspacing="0" border="0" class="lm-btn-t" style="border-collapse:collapse"><tr>';
	foreach ( $dugmad as $i => $d ) {
		$o .= '<td valign="top" class="lm-stack' . ( $i ? ' lm-stack-2' : '' ) . '" style="padding:0 ' . ( $i < $n - 1 ? '10px' : '0' ) . ' 0 0">' . lm_em_btn( $d[0], $d[1], ! empty( $d[2] ) ) . '</td>';
	}
	return $o . '</tr></table>';
}

function lm_em_box( $html, $bg = '#fdf2ec' ) {
	return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . $bg . ';border-radius:16px"><tr><td class="lm-box-td" style="padding:22px 24px;' . lm_em_font() . 'color:#7a6250">' . $html . '</td></tr></table>';
}

/** Kutija sa kontaktom (WhatsApp, Viber, telefon). */
function lm_em_kontakt_box( $naslov = 'Pitanje o porudžbini?' ) {
	return lm_em_box(
		'<div style="font-size:18px;font-weight:800;color:#6d4e39;margin:0 0 6px">' . esc_html( $naslov ) . '</div>'
		. '<div style="font-size:15px;line-height:1.55;color:#6d4e39;margin:0 0 16px">Odgovorite na ovaj email ili nam pišite na WhatsApp ili Viber. Možete i da pozovete butik.</div>'
		. lm_em_btns( array( array( 'https://wa.me/38267030655', 'PIŠITE NA WHATSAPP' ), array( 'tel:+38267030655', 'POZOVITE BUTIK', true ) ) )
		. '<div style="font-size:14px;line-height:1.5;color:#6d4e39;margin:14px 0 0">Viber i telefon: <a href="tel:+38267030655" style="font-weight:700;color:#8a4b47;text-decoration:underline;white-space:nowrap">067 030 655</a></div>',
		'#f6ded4'
	);
}

function lm_em_label( $t, $col = '#a05c57' ) {
	return '<div style="font-size:12px;font-weight:700;letter-spacing:.12em;color:' . $col . ';margin:0 0 6px">' . esc_html( $t ) . '</div>';
}

function lm_em_hero( $eyebrow, $heading, $txt ) {
	$o = '<tr><td class="lm-px lm-hero" style="padding:38px 40px 6px;' . lm_em_font() . '">';
	if ( $eyebrow ) {
		$o .= '<div style="font-size:12px;font-weight:700;letter-spacing:.16em;line-height:1.5;color:#a05c57;margin:0 0 12px">' . esc_html( $eyebrow ) . '</div>';
	}
	$o .= '<h1 class="lm-h1" style="margin:0 0 12px;font-size:28px;line-height:1.18;font-weight:800;color:#6d4e39;' . lm_em_font() . '">' . esc_html( $heading ) . '</h1>';
	if ( $txt ) {
		$o .= '<p style="margin:0;font-size:16px;line-height:1.6;color:#7a6250">' . esc_html( $txt ) . '</p>';
	}
	return $o . '</td></tr>';
}

function lm_em_tracker( $labels, $states ) {
	$o = '<tr><td class="lm-px" style="padding:24px 40px 4px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>';
	foreach ( $labels as $i => $l ) {
		if ( $i > 0 ) {
			$line = ( 'todo' === $states[ $i ] ) ? '#e8d5cb' : '#a05c57';
			$o   .= '<td valign="top" style="padding-top:15px"><div style="height:2px;background:' . $line . ';font-size:0;line-height:0">&nbsp;</div></td>';
		}
		$s = $states[ $i ];
		if ( 'done' === $s ) {
			$dot = '<div style="width:30px;height:30px;line-height:30px;border-radius:15px;background:#a05c57;color:#ffffff;font-size:15px;font-weight:700;text-align:center;margin:0 auto">&#10003;</div>';
		} elseif ( 'current' === $s ) {
			$dot = '<div style="width:26px;height:26px;border-radius:15px;border:2px solid #a05c57;background:#ffffff;margin:0 auto"><div style="width:10px;height:10px;border-radius:5px;background:#a05c57;margin:8px auto 0;font-size:0;line-height:0">&nbsp;</div></div>';
		} else {
			$dot = '<div style="width:26px;height:26px;border-radius:15px;border:2px solid #e8d5cb;background:#ffffff;margin:0 auto;font-size:0;line-height:0">&nbsp;</div>';
		}
		$col = 'todo' === $s ? '#8a7565' : '#6d4e39';
		$fw  = 'todo' === $s ? '500' : '700';
		$o  .= '<td width="80" align="center" valign="top" style="width:80px;' . lm_em_font() . '">' . $dot . '<div style="font-size:12px;font-weight:' . $fw . ';color:' . $col . ';margin-top:8px">' . esc_html( $l ) . '</div></td>';
	}
	return $o . '</tr></table></td></tr>';
}
