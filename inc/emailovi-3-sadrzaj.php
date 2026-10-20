<?php
/**
 * Lady Mom – emailovi (3/4): sadržaj (kartice, artikli, zbir) i sadržaj po vrsti emaila.
 * (Ranije Code Snippets #24, prebačeno u child temu.)
 * 20.10.2026: redovi preko lm_em_red() (telefon 20 px), kolone se slažu jedna ispod druge,
 * nova dugmad, i emailovi butiku (nova, otkazana, neuspjela porudžbina) u istom okviru.
 */

defined( 'ABSPATH' ) || exit;

function lm_em_money( $order, $amount ) {
	return wp_strip_all_tags( wc_price( $amount, array( 'currency' => $order->get_currency() ) ) );
}

function lm_em_payment_html( $order, $stage ) {
	$t = lm_em_money( $order, $order->get_total() );
	if ( lm_em_card( $order ) ) {
		$r = (int) $order->get_meta( '_bankart_instalments' );
		$h = 'Kartica' . ( $r > 1 ? ' · ' . $r . ' rata' : '' );
		$d = 'completed' === $stage ? 'Plaćeno karticom: <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>' : '<strong style="color:#6d4e39">' . esc_html( $t ) . '</strong> je rezervisano na kartici. Naplaćuje se kad porudžbinu ' . ( lm_em_bez_dostave( $order ) ? 'potvrdimo.' : 'pripremimo za slanje.' );
	} elseif ( 'cod' === $order->get_payment_method() ) {
		if ( lm_em_bez_dostave( $order ) ) {
			$h = 'Gotovinom';
			$d = 'Plaćate gotovinom <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>';
		} elseif ( lm_em_pickup( $order ) ) {
			$h = 'Prilikom preuzimanja';
			$d = 'U butiku plaćate <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>';
		} else {
			$h = 'Pouzećem';
			$d = 'Kuriru plaćate gotovinom <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>';
		}
	} else {
		$h = $order->get_payment_method_title();
		$d = esc_html( $t );
	}
	return lm_em_label( 'PLAĆANJE' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">' . esc_html( $h ) . '</div><div style="font-size:14px;line-height:1.5">' . $d . '</div>';
}

function lm_em_delivery_html( $order ) {
	$free = (float) $order->get_shipping_total() <= 0;
	$txt  = '1–3 radna dana · ' . ( $free ? 'besplatno, porudžbina je preko 100 €' : esc_html( lm_em_money( $order, (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() ) ) );
	return lm_em_label( 'DOSTAVA' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">Kurirska služba The Vault d.o.o.</div><div style="font-size:14px;line-height:1.5">' . $txt . '</div>';
}

function lm_em_pickup_box() {
	$map = 'https://www.google.com/maps/search/?api=1&query=Lady+Mom+prodavnica+za+trudnice+i+mame&query_place_id=ChIJU0tXHcnrTRMRuXXUSvfb4b8';
	return lm_em_box( lm_em_label( 'PREUZIMANJE U BUTIKU', '#8a4b47' )
		. '<div style="font-size:17px;font-weight:800;color:#6d4e39;margin:0 0 6px">Lady Mom, Branka Deletića bb</div>'
		. '<div style="font-size:14px;line-height:1.6;color:#6d4e39;margin:0 0 16px">81000 Podgorica<br>ponedjeljak–subota 9:30–21:00, nedjeljom ne radimo<br>Telefon: 067 030 655</div>'
		. lm_em_btns( array( array( $map, 'OTVORI MAPU' ), array( 'tel:+38267030655', 'POZOVI BUTIK', true ) ) ), '#f6ded4' );
}

/** Dvije kolone na računaru, jedna ispod druge na telefonu. Prazna desna kolona se ne crta. */
function lm_em_dvije_kolone( $lijevo, $desno, $razmak = 6 ) {
	if ( '' === $desno ) {
		return $lijevo;
	}
	return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'
		. '<td width="50%" valign="top" class="lm-stack" style="width:50%;padding-right:' . (int) $razmak . 'px">' . $lijevo . '</td>'
		. '<td width="50%" valign="top" class="lm-stack lm-stack-2" style="width:50%;padding-left:' . (int) $razmak . 'px">' . $desno . '</td>'
		. '</tr></table>';
}

function lm_em_cards( $order, $stage ) {
	if ( lm_em_bez_dostave( $order ) ) {
		return lm_em_red( lm_em_box( lm_em_payment_html( $order, $stage ) ), 26 );
	}
	if ( lm_em_pickup( $order ) ) {
		return lm_em_red( lm_em_pickup_box(), 26 ) . lm_em_red( lm_em_box( lm_em_payment_html( $order, $stage ) ), 12 );
	}
	return lm_em_red( lm_em_dvije_kolone( lm_em_box( lm_em_delivery_html( $order ) ), lm_em_box( lm_em_payment_html( $order, $stage ) ) ), 26 );
}

function lm_em_items( $order, $title = 'Šta ste poručili', $sifra = false ) {
	$o = '<h2 style="margin:0 0 10px;font-size:19px;font-weight:800;color:#6d4e39">' . esc_html( $title ) . '</h2><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">';
	foreach ( $order->get_items() as $item ) {
		$p    = $item->get_product();
		$img  = $p ? lm_em_slika_url( $p->get_image_id() ) : '';
		$im   = $img ? '<img src="' . esc_url( $img ) . '" width="64" height="64" alt="" style="display:block;width:64px;height:64px;border-radius:10px;object-fit:cover;border:0">' : '<div style="width:64px;height:64px;border-radius:10px;background:#fdf2ec"></div>';
		$meta = wp_strip_all_tags( wc_display_item_meta( $item, array( 'echo' => false, 'before' => '', 'after' => '', 'separator' => ' · ', 'label_before' => '', 'label_after' => ': ', 'autop' => false ) ) );
		if ( $sifra && $p && $p->get_sku() ) {
			$meta = 'Šifra: ' . $p->get_sku() . ( $meta ? ' · ' . $meta : '' );
		}
		$o .= '<tr><td width="64" valign="middle" style="padding:12px 0;border-top:1px solid #f3e6de;width:64px">' . $im . '</td>'
			. '<td valign="middle" style="padding:12px 12px 12px 14px;border-top:1px solid #f3e6de"><div style="font-size:15px;line-height:1.35;font-weight:700;color:#6d4e39">' . esc_html( $item->get_name() ) . '</div>'
			. ( $meta ? '<div style="font-size:13px;line-height:1.4;color:#8a7565;margin-top:3px">' . esc_html( $meta ) . '</div>' : '' ) . '</td>'
			. '<td align="right" valign="middle" style="padding:12px 0;border-top:1px solid #f3e6de;white-space:nowrap"><div style="font-size:15px;font-weight:700;color:#6d4e39">' . wp_kses_post( $order->get_formatted_line_subtotal( $item ) ) . '</div>'
			. '<div style="font-size:13px;color:#8a7565;margin-top:3px">' . (int) $item->get_quantity() . ' kom.</div></td></tr>';
	}
	return lm_em_red( $o . '</table>', 30 );
}

function lm_em_totals( $order ) {
	$row = function ( $l, $v, $b = false ) {
		return '<tr><td style="padding:5px 0;font-size:' . ( $b ? '17px;font-weight:800;color:#6d4e39' : '15px;color:#7a6250' ) . '">' . $l . '</td><td align="right" style="padding:5px 0;white-space:nowrap;font-size:' . ( $b ? '24px;font-weight:800;color:#6d4e39' : '15px;color:#7a6250' ) . '">' . $v . '</td></tr>';
	};
	$o  = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #f3e6de">';
	$o .= '<tr><td colspan="2" style="height:10px;font-size:0;line-height:0">&nbsp;</td></tr>';
	$o .= $row( 'Međuzbir', esc_html( wp_strip_all_tags( $order->get_subtotal_to_display( false, 'incl' ) ) ) );
	if ( (float) $order->get_total_discount() > 0 ) {
		$o .= $row( 'Popust', '−' . esc_html( lm_em_money( $order, (float) $order->get_total_discount() + (float) $order->get_discount_tax() ) ) );
	}
	if ( lm_em_bez_dostave( $order ) ) {
		$o .= ''; // bez reda za dostavu
	} elseif ( lm_em_pickup( $order ) ) {
		$o .= $row( 'Preuzimanje u butiku', '0,00 €' );
	} elseif ( (float) $order->get_shipping_total() <= 0 ) {
		$o .= $row( 'Dostava', '<span style="color:#3f6265;font-weight:700">Besplatna</span>' );
	} else {
		$o .= $row( 'Dostava', esc_html( lm_em_money( $order, (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() ) ) );
	}
	foreach ( $order->get_refunds() as $r ) {
		$o .= $row( 'Vraćeno', '−' . esc_html( lm_em_money( $order, $r->get_amount() ) ) );
	}
	$o  .= '<tr><td colspan="2" style="border-top:1px solid #f3e6de;height:8px;font-size:0;line-height:0">&nbsp;</td></tr>';
	$net = (float) $order->get_total() - (float) $order->get_total_refunded();
	$o  .= $row( 'Ukupno', esc_html( lm_em_money( $order, $order->get_total_refunded() > 0 ? $net : $order->get_total() ) ), true );
	if ( (float) $order->get_total_tax() > 0 ) {
		$o .= '<tr><td colspan="2" align="right" style="font-size:12px;color:#8a7565">uključuje ' . esc_html( lm_em_money( $order, $order->get_total_tax() ) ) . ' PDV-a</td></tr>';
	}
	return lm_em_red( $o . '</table>', 6 );
}

function lm_em_addresses( $order ) {
	$left  = ( lm_em_pickup( $order ) || lm_em_bez_dostave( $order ) )
		? lm_em_label( 'KUPAC' ) . '<div style="font-size:14px;line-height:1.6">' . esc_html( $order->get_formatted_billing_full_name() ) . '<br>' . esc_html( $order->get_billing_phone() ) . '<br>' . esc_html( $order->get_billing_email() ) . '</div>'
		: lm_em_label( 'DOSTAVA NA ADRESU' ) . '<div style="font-size:14px;line-height:1.6">' . wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ) . '<br>' . esc_html( $order->get_billing_phone() ) . '</div>';
	$note  = $order->get_customer_note();
	$right = $note ? lm_em_label( 'VAŠA NAPOMENA' ) . '<div style="font-size:14px;line-height:1.6">' . esc_html( $note ) . '</div>' : '';
	return '<tr><td class="lm-px" style="padding:28px 40px 0;' . lm_em_font() . 'color:#7a6250">' . lm_em_dvije_kolone( $left, $right, 12 ) . '</td></tr>';
}

function lm_em_body( $email, $vars = array() ) {
	$o  = ( isset( $vars['order'] ) && is_a( $vars['order'], 'WC_Order' ) ) ? $vars['order'] : lm_em_order( $email );
	$id = $email->id;
	if ( lm_em_je_admin( $email ) ) {
		lm_em_admin_body( $email, $o );
		return;
	}
	$c      = lm_em_copy( $email );
	$p      = lm_em_pickup( $o );
	$b      = lm_em_bez_dostave( $o );
	$ey     = $o ? 'PORUDŽBINA #' . $o->get_order_number() . ( $o->get_date_created() ? ' · ' . mb_strtoupper( wc_format_datetime( $o->get_date_created(), 'j. F Y.' ) ) : '' ) : 'VAŠ NALOG';
	$labels = $p ? array( 'Primljena', 'Pripremamo', 'Spremna' ) : array( 'Primljena', 'Pripremamo', 'Na putu' );
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="' . lm_em_font() . 'color:#7a6250">';
	echo lm_em_hero( $ey, $c['h'] ? $c['h'] : ( isset( $vars['email_heading'] ) ? $vars['email_heading'] : '' ), $c['txt'] );
	switch ( $id ) {
		case 'customer_processing_order':
		case 'customer_on_hold_order':
			if ( ! $b ) {
				echo lm_em_tracker( $labels, array( 'done', 'customer_on_hold_order' === $id ? 'todo' : 'current', 'todo' ) );
			}
			echo lm_em_cards( $o, 'processing' );
			echo lm_em_items( $o );
			echo lm_em_totals( $o );
			echo lm_em_addresses( $o );
			break;
		case 'customer_completed_order':
			if ( ! $b ) {
				echo lm_em_tracker( array( 'Primljena', 'Pripremljena', $p ? 'Spremna' : 'Na putu' ), array( 'done', 'done', 'done' ) );
			}
			if ( $p ) {
				echo lm_em_red( lm_em_pickup_box(), 26 );
			}
			echo lm_em_red( lm_em_box( lm_em_payment_html( $o, 'completed' ) ), $p ? 12 : 26 );
			echo lm_em_items( $o, ( $p || $b ) ? 'Vaša porudžbina' : 'U paketu' );
			echo lm_em_totals( $o );
			if ( ! $b ) {
				echo lm_em_red( '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1.5px solid #f6ded4;border-radius:16px"><tr><td class="lm-box-td" style="padding:20px 24px;' . lm_em_font() . '">'
					. '<div style="font-size:17px;font-weight:800;color:#6d4e39;margin:0 0 6px">Ne odgovara veličina?</div>'
					. '<div style="font-size:15px;line-height:1.55;color:#7a6250;margin:0 0 10px">Zamjena ili povrat je moguć u roku od 14 dana.</div>'
					. '<a href="' . esc_url( home_url( '/povrat-robe-i-sredstava/' ) ) . '" style="font-size:14px;font-weight:700;color:#8a4b47;text-decoration:underline">Kako ide zamjena →</a></td></tr></table>', 28 );
			}
			break;
		case 'customer_failed_order':
			echo lm_em_red( lm_em_btn( $o->get_checkout_payment_url(), 'POKUŠAJ PONOVO' ), 20 );
			echo lm_em_red( lm_em_box( lm_em_label( 'ČEŠĆI RAZLOZI' ) . '<div style="font-size:14px;line-height:1.7">· Nedovoljno sredstava ili dostignut dnevni limit<br>· Kartica nije omogućena za plaćanje preko interneta<br>· Kod iz SMS-a ili aplikacije banke nije potvrđen na vrijeme</div>' ), 28 );
			echo lm_em_items( $o, 'Porudžbina' );
			echo lm_em_totals( $o );
			break;
		case 'customer_cancelled_order':
			echo lm_em_items( $o, 'Otkazana porudžbina' );
			echo lm_em_totals( $o );
			echo lm_em_red( lm_em_btn( home_url( '/prodavnica/' ), 'NASTAVI KUPOVINU', true ), 26 );
			break;
		case 'customer_refunded_order':
		case 'customer_partially_refunded_order':
			echo lm_em_items( $o, 'Porudžbina' );
			echo lm_em_totals( $o );
			break;
		case 'customer_note':
			$note = isset( $vars['customer_note'] ) ? $vars['customer_note'] : ( isset( $email->customer_note ) ? $email->customer_note : '' );
			echo lm_em_red( lm_em_box( '<div style="font-size:16px;line-height:1.6;color:#6d4e39">' . wpautop( wptexturize( make_clickable( esc_html( $note ) ) ) ) . '</div>', '#f6ded4' ), 22 );
			echo lm_em_items( $o, 'Porudžbina' );
			echo lm_em_totals( $o );
			break;
		case 'customer_invoice':
			if ( $o->needs_payment() ) {
				echo lm_em_red( lm_em_btn( $o->get_checkout_payment_url(), 'PLATI PORUDŽBINU' ), 20 );
			}
			echo lm_em_items( $o );
			echo lm_em_totals( $o );
			echo lm_em_addresses( $o );
			break;
		case 'customer_new_account':
			$pw = ! empty( $vars['password_generated'] ) && ! empty( $vars['set_password_url'] );
			echo lm_em_red( lm_em_btn( $pw ? $vars['set_password_url'] : wc_get_page_permalink( 'myaccount' ), $pw ? 'POSTAVI LOZINKU' : 'OTVORI MOJ NALOG' ), 20 );
			$lista = '<h2 style="margin:0 0 12px;font-size:19px;font-weight:800;color:#6d4e39">Šta vam nalog donosi</h2>';
			foreach ( array( array( 'Lista želja', 'Sačuvajte ono što vam se dopada i podijelite listu linkom.' ), array( 'Sve porudžbine na jednom mjestu', 'Status, detalji i ranije kupovine.' ), array( 'Brža kupovina', 'Adresa i telefon su već upisani pri sljedećoj porudžbini.' ) ) as $bb ) {
				$lista .= '<div style="margin:0 0 10px">' . lm_em_box( '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">' . esc_html( $bb[0] ) . '</div><div style="font-size:14px;line-height:1.5">' . esc_html( $bb[1] ) . '</div>' ) . '</div>';
			}
			echo lm_em_red( $lista, 30 );
			break;
		case 'customer_reset_password':
			$url = add_query_arg( array( 'key' => isset( $vars['reset_key'] ) ? $vars['reset_key'] : '', 'id' => isset( $vars['user_id'] ) ? $vars['user_id'] : '', 'login' => rawurlencode( isset( $vars['user_login'] ) ? $vars['user_login'] : '' ) ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) );
			echo lm_em_red( lm_em_btn( $url, 'POSTAVI NOVU LOZINKU' ), 20 );
			echo lm_em_red( '<div style="font-size:14px;line-height:1.6;color:#8a7565">Ako niste vi tražili novu lozinku, zanemarite ovaj email — lozinka ostaje ista.</div>', 22 );
			break;
	}
	echo '</table>';
}

/* -------------------------------------------------------------------------
 * Emailovi butiku (20.10.2026): nova, otkazana i neuspjela porudžbina.
 * Do sada goli WooCommerce šablon (tabela bez dugmeta, adrese na dnu).
 * Sada: ko je kupac i kako da ga dobijete, kako ide (kurir/butik/bez dostave) i plaćanje,
 * napomena kupca istaknuta, artikli sa šifrom i dugme „Otvori porudžbinu“.
 * ---------------------------------------------------------------------- */

/** Kratko: dostava i plaćanje za butik. */
function lm_em_admin_nacin( $o ) {
	$dostava = lm_em_bez_dostave( $o ) ? 'bez dostave' : ( lm_em_pickup( $o ) ? 'preuzimanje u butiku' : 'kurir' );
	if ( lm_em_card( $o ) ) {
		$r        = (int) $o->get_meta( '_bankart_instalments' );
		$placanje = 'kartica' . ( $r > 1 ? ', ' . $r . ' rata' : '' );
	} elseif ( 'cod' === $o->get_payment_method() ) {
		$placanje = lm_em_bez_dostave( $o ) ? 'gotovina' : ( lm_em_pickup( $o ) ? 'plaća u butiku' : 'pouzeće' );
	} else {
		$placanje = mb_strtolower( $o->get_payment_method_title() );
	}
	return array( $dostava, $placanje );
}

/** Preheader za butik: kupac · iznos · dostava, plaćanje. */
function lm_em_admin_pre( $o ) {
	if ( ! $o ) {
		return '';
	}
	list( $d, $p ) = lm_em_admin_nacin( $o );
	return trim( $o->get_formatted_billing_full_name() ) . ' · ' . lm_em_money( $o, $o->get_total() ) . ' · ' . $d . ', ' . $p;
}

function lm_em_admin_body( $email, $o ) {
	$id = $email->id;
	$c  = lm_em_admin_copy( $id, $o );
	list( $dostava, $placanje ) = lm_em_admin_nacin( $o );
	$kupac = trim( $o->get_formatted_billing_full_name() );
	$tel   = trim( (string) $o->get_billing_phone() );
	$mail  = trim( (string) $o->get_billing_email() );
	$datum = $o->get_date_created() ? wc_format_datetime( $o->get_date_created(), 'j. F Y. \u H:i' ) : '';
	$iznos = lm_em_money( $o, $o->get_total() );
	switch ( $id ) {
		case 'cancelled_order':
			$txt = 'Porudžbina je otkazana i kupac je o tome dobio email.' . ( lm_em_card( $o ) ? ' Rezervacija iznosa na kartici se poništava.' : '' );
			break;
		case 'failed_order':
			$txt = 'Banka nije odobrila plaćanje karticom. Kupac je dobio email sa linkom da pokuša ponovo, a porudžbina ostaje sačuvana.';
			break;
		default:
			$txt = ucfirst( $dostava ) . ', ' . $placanje . ' · ' . $iznos . '.';
	}
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="' . lm_em_font() . 'color:#7a6250">';
	echo lm_em_hero( 'ZA BUTIK' . ( $datum ? ' · ' . mb_strtoupper( $datum ) : '' ), $c ? $c['h'] : 'Porudžbina #' . $o->get_order_number(), $txt );
	echo lm_em_red( lm_em_btns( array( array( $o->get_edit_order_url(), 'OTVORI PORUDŽBINU' ), $tel ? array( 'tel:' . preg_replace( '/[^0-9+]/', '', $tel ), 'POZOVI KUPCA', true ) : null ) ), 20 );

	$k = lm_em_label( 'KUPAC' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">' . esc_html( $kupac ) . '</div><div style="font-size:14px;line-height:1.6">'
		. ( $tel ? '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $tel ) ) . '" style="color:#8a4b47;font-weight:700;text-decoration:underline;white-space:nowrap">' . esc_html( $tel ) . '</a><br>' : '' )
		. ( $mail ? '<a href="mailto:' . esc_attr( $mail ) . '" style="color:#8a4b47;text-decoration:underline;word-break:break-all">' . esc_html( $mail ) . '</a>' : '' ) . '</div>';
	if ( lm_em_bez_dostave( $o ) ) {
		$d = lm_em_label( 'DOSTAVA' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">Bez dostave</div><div style="font-size:14px;line-height:1.5">' . ( lm_em_ima_rm( $o ) ? 'Prijava za Ready Mom' : 'Samo usluge' ) . '</div>';
	} elseif ( lm_em_pickup( $o ) ) {
		$d = lm_em_label( 'DOSTAVA' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">Preuzimanje u butiku</div><div style="font-size:14px;line-height:1.5">Kupac dolazi po porudžbinu.</div>';
	} else {
		$adr = $o->get_formatted_shipping_address() ? $o->get_formatted_shipping_address() : $o->get_formatted_billing_address();
		$d   = lm_em_label( 'DOSTAVA KURIROM' ) . '<div style="font-size:14px;line-height:1.6">' . wp_kses_post( $adr ) . '</div>';
	}
	echo lm_em_red( lm_em_dvije_kolone( lm_em_box( $k ), lm_em_box( $d ) ), 26 );
	if ( 'new_order' === $id ) {
		$t  = lm_em_money( $o, $o->get_total() );
		$pl = lm_em_card( $o ) ? 'Iznos <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong> je rezervisan na kartici.' : ( 'cod' === $o->get_payment_method() ? 'Za naplatu: <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>' : esc_html( $t ) );
		echo lm_em_red( lm_em_box( lm_em_label( 'PLAĆANJE' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">' . esc_html( ucfirst( $placanje ) ) . '</div><div style="font-size:14px;line-height:1.5">' . $pl . '</div>' ), 12 );
	}
	$note = $o->get_customer_note();
	if ( $note ) {
		echo lm_em_red( lm_em_box( lm_em_label( 'NAPOMENA KUPCA', '#8a4b47' ) . '<div style="font-size:15px;line-height:1.6;color:#6d4e39">' . nl2br( esc_html( $note ) ) . '</div>', '#f6ded4' ), 12 );
	}
	echo lm_em_items( $o, 'Artikli', true );
	echo lm_em_totals( $o );
	echo '</table>';
}
