<?php
/**
 * Lady Mom – emailovi (2/3) zaglavlje i footer
 * (Ranije Code Snippets #23, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – emailovi kupcima (2/3): zaglavlje, footer i osnovni blokovi.

function lm_em_header( $email_heading, $email = null ) {
	$f   = lm_em_font();
	$c   = $email ? lm_em_copy( $email ) : array( 'pre' => '' );
	$hdr = wp_get_attachment_url( lm_em_media( 'zaglavlje' ) );
	echo '<!DOCTYPE html><html lang="sr-ME"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="x-apple-disable-message-reformatting"><title>Lady Mom</title>';
	echo '<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700;800&amp;display=swap" rel="stylesheet"></head>';
	echo '<body style="margin:0;padding:0;background:#fdf2ec;' . $f . '">';
	if ( ! empty( $c['pre'] ) ) {
		echo '<div style="font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;color:#fdf2ec">' . esc_html( $c['pre'] ) . str_repeat( '&#847;&zwnj;&nbsp;', 60 ) . '</div>';
	}
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fdf2ec"><tr><td align="center" style="padding:24px 10px">';
	echo '<table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:18px;overflow:hidden">';
	echo '<tr><td style="background:#fdf2ec;border-bottom:1px solid #f6ded4;font-size:0;line-height:0">';
	if ( $hdr ) {
		echo '<a href="' . esc_url( home_url( '/' ) ) . '"><img src="' . esc_url( $hdr ) . '" width="600" alt="Lady Mom" style="display:block;width:100%;max-width:600px;height:auto;border:0"></a>';
	} else {
		echo '<div style="padding:34px;text-align:center;font-size:28px;line-height:1.2;color:#6d4e39;letter-spacing:.08em">LADY MOM</div>';
	}
	echo '</td></tr><tr><td style="padding:0">';
}

function lm_em_footer( $email = null ) {
	$f = lm_em_font();
	$o = lm_em_order( $email );
	echo '</td></tr>';
	if ( $o ) {
		$wa = 'https://wa.me/38267030655';
		echo '<tr><td style="padding:32px 40px 0">' . lm_em_box(
			'<div style="font-size:19px;font-weight:800;color:#6d4e39;margin:0 0 8px">Pitanje o porudžbini?</div>'
			. '<div style="font-size:15px;line-height:1.55;color:#6d4e39;margin:0 0 16px">Odgovorite na ovaj email, pišite nam na WhatsApp ili Viber, ili pozovite butik.</div>'
			. lm_em_btn( $wa, 'OTVORI WHATSAPP' )
			. '<div style="font-size:15px;line-height:1.5;color:#6d4e39;margin:14px 0 0">Viber i telefon: <a href="tel:+38267030655" style="font-weight:700;color:#8a4b47;text-decoration:underline;white-space:nowrap">067 030 655</a></div>',
			'#f6ded4' ) . '</td></tr>';
	}
	if ( $email && in_array( $email->id, array( 'customer_processing_order', 'customer_new_account' ), true ) ) {
		echo '<tr><td style="padding:14px 40px 0"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#d6ede9;border-radius:16px"><tr>'
			. '<td style="padding:18px 22px;' . $f . '"><div style="font-size:12px;font-weight:700;letter-spacing:.12em;color:#3f6265">ŠKOLA RODITELJSTVA</div><div style="font-size:15px;font-weight:700;color:#2f4a4c;margin:4px 0 8px">Family Nest — prijave su otvorene</div>'
			. '<a href="' . esc_url( home_url( '/skola-za-trudnice-ready-mom-podgorica/' ) ) . '" style="font-size:13px;font-weight:700;color:#2f4a4c">Saznajte više →</a></td></tr></table></td></tr>';
	}
	$logo  = wp_get_attachment_url( lm_em_media( 'logo_bijeli' ) );
	$links = array( 'Prodavnica' => '/prodavnica/', 'Dostava' => '/dostava/', 'Povrat i zamjena' => '/povrat-robe-i-sredstava/', 'Uslovi' => '/uslovi-koriscenja/' );
	$l = array();
	foreach ( $links as $t => $u ) $l[] = '<a href="' . esc_url( home_url( $u ) ) . '" style="color:#e3efee;text-decoration:underline">' . $t . '</a>';
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$why  = $o ? 'Ovaj email ste dobili jer ste poručili na ' . $host . '.' : 'Ovaj email ste dobili jer imate nalog na ' . $host . '.';
	echo '<tr><td style="padding:40px 0 0;font-size:0;line-height:0">&nbsp;</td></tr><tr><td align="center" style="background:#3f6265;padding:34px 40px 30px;text-align:center;' . $f . '">';
	if ( $logo ) echo '<img src="' . esc_url( $logo ) . '" width="128" alt="Lady Mom" style="display:block;margin:0 auto 14px;width:128px;height:auto;border:0">';
	echo '<div style="font-size:14px;line-height:1.6;color:#d4e5e4;margin:0 0 12px">Butik: Branka Deletića bb, Podgorica<br>ponedjeljak–subota 9:30–21:00</div>';
	echo '<div style="font-size:13px;font-weight:600;line-height:1.8;color:#e3efee">' . implode( '&nbsp;&nbsp;·&nbsp;&nbsp;', $l ) . '</div>';
	echo '<div style="height:1px;background:#557779;margin:18px 0 14px;font-size:0;line-height:0">&nbsp;</div>';
	echo '<div style="font-size:11px;line-height:1.6;color:#b9d2d0">Tangerine Limited d.o.o. · PIB 03464415<br>' . esc_html( $why ) . '</div>';
	echo '</td></tr></table></td></tr></table></body></html>';
}

function lm_em_btn( $url, $label, $outline = false ) {
	$st = $outline ? 'background:#ffffff;color:#8a4b47;border:1.5px solid #a05c57;' : 'background:#a05c57;color:#ffffff;border:1.5px solid #a05c57;';
	return '<a href="' . esc_url( $url ) . '" style="display:inline-block;' . $st . 'border-radius:999px;padding:13px 24px;font-size:13px;font-weight:700;letter-spacing:.06em;text-decoration:none;line-height:1.2;' . lm_em_font() . '">' . esc_html( $label ) . '</a>';
}

function lm_em_box( $html, $bg = '#fdf2ec' ) {
	return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:' . $bg . ';border-radius:16px"><tr><td style="padding:22px 24px;' . lm_em_font() . 'color:#7a6250">' . $html . '</td></tr></table>';
}

function lm_em_label( $t, $col = '#a05c57' ) {
	return '<div style="font-size:12px;font-weight:700;letter-spacing:.12em;color:' . $col . ';margin:0 0 6px">' . esc_html( $t ) . '</div>';
}

function lm_em_hero( $eyebrow, $heading, $txt ) {
	$o = '<tr><td style="padding:38px 40px 6px;' . lm_em_font() . '">';
	if ( $eyebrow ) $o .= '<div style="font-size:12px;font-weight:700;letter-spacing:.16em;color:#a05c57;margin:0 0 12px">' . esc_html( $eyebrow ) . '</div>';
	$o .= '<h1 style="margin:0 0 12px;font-size:30px;line-height:1.15;font-weight:800;color:#6d4e39;' . lm_em_font() . '">' . esc_html( $heading ) . '</h1>';
	if ( $txt ) $o .= '<p style="margin:0;font-size:16px;line-height:1.6;color:#7a6250">' . esc_html( $txt ) . '</p>';
	return $o . '</td></tr>';
}

function lm_em_tracker( $labels, $states ) {
	$o = '<tr><td style="padding:24px 40px 4px"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>';
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
