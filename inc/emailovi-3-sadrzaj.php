<?php
/**
 * Lady Mom – emailovi (3/3) sadržaj
 * (Ranije Code Snippets #24, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – emailovi kupcima (3/3): kartice, proizvodi, zbir i sadržaj po vrsti emaila.

function lm_em_money( $order, $amount ) {
	return wp_strip_all_tags( wc_price( $amount, array( 'currency' => $order->get_currency() ) ) );
}

function lm_em_payment_html( $order, $stage ) {
	$t = lm_em_money( $order, $order->get_total() );
	if ( lm_em_card( $order ) ) {
		$r = (int) $order->get_meta( '_bankart_instalments' );
		$h = 'Kartica' . ( $r > 1 ? ' · ' . $r . ' rata' : '' );
		$d = 'completed' === $stage ? 'Plaćeno karticom: <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>' : '<strong style="color:#6d4e39">' . esc_html( $t ) . '</strong> je rezervisano na kartici. Naplaćuje se kad porudžbinu pripremimo za slanje.';
	} elseif ( 'cod' === $order->get_payment_method() ) {
		if ( lm_em_pickup( $order ) ) { $h = 'Prilikom preuzimanja'; $d = 'U butiku plaćate <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>'; }
		else { $h = 'Pouzećem'; $d = 'Kuriru plaćate gotovinom <strong style="color:#6d4e39">' . esc_html( $t ) . '</strong>'; }
	} else {
		$h = $order->get_payment_method_title(); $d = esc_html( $t );
	}
	return lm_em_label( 'PLAĆANJE' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">' . esc_html( $h ) . '</div><div style="font-size:14px;line-height:1.5">' . $d . '</div>';
}

function lm_em_delivery_html( $order ) {
	$free = (float) $order->get_shipping_total() <= 0;
	$txt  = '1–3 radna dana · ' . ( $free ? 'besplatno, porudžbina je preko 100 €' : esc_html( lm_em_money( $order, (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() ) ) );
	return lm_em_label( 'DOSTAVA' ) . '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">Kurirska služba The Vault</div><div style="font-size:14px;line-height:1.5">' . $txt . '</div>';
}

function lm_em_pickup_box() {
	$map = 'https://www.google.com/maps/search/?api=1&query=Lady+Mom+prodavnica+za+trudnice+i+mame&query_place_id=ChIJU0tXHcnrTRMRuXXUSvfb4b8';
	return lm_em_box( lm_em_label( 'PREUZIMANJE U BUTIKU', '#8a4b47' )
		. '<div style="font-size:17px;font-weight:800;color:#6d4e39;margin:0 0 6px">Lady Mom, Branka Deletića bb</div>'
		. '<div style="font-size:14px;line-height:1.6;color:#6d4e39;margin:0 0 14px">81000 Podgorica<br>ponedjeljak–subota 9:30–21:00, nedjeljom ne radimo<br>Telefon: 067 030 655</div>'
		. '<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding:0 0 10px">' . lm_em_btn( $map, 'OTVORI MAPU' ) . '</td></tr><tr><td>' . lm_em_btn( 'tel:+38267030655', 'POZOVI BUTIK', true ) . '</td></tr></table>', '#f6ded4' );
}

function lm_em_cards( $order, $stage ) {
	if ( lm_em_pickup( $order ) ) {
		return '<tr><td style="padding:26px 40px 0">' . lm_em_pickup_box() . '</td></tr><tr><td style="padding:12px 40px 0">' . lm_em_box( lm_em_payment_html( $order, $stage ) ) . '</td></tr>';
	}
	return '<tr><td style="padding:26px 40px 0"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'
		. '<td width="50%" valign="top" style="padding-right:6px">' . lm_em_box( lm_em_delivery_html( $order ) ) . '</td>'
		. '<td width="50%" valign="top" style="padding-left:6px">' . lm_em_box( lm_em_payment_html( $order, $stage ) ) . '</td>'
		. '</tr></table></td></tr>';
}

function lm_em_items( $order, $title = 'Šta ste poručili' ) {
	$o = '<tr><td style="padding:30px 40px 0;' . lm_em_font() . '"><h2 style="margin:0 0 10px;font-size:19px;font-weight:800;color:#6d4e39">' . esc_html( $title ) . '</h2><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">';
	foreach ( $order->get_items() as $item ) {
		$p   = $item->get_product();
		$img = $p ? wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) : '';
		$im  = $img ? '<img src="' . esc_url( $img ) . '" width="64" height="64" alt="" style="display:block;width:64px;height:64px;border-radius:10px;object-fit:cover;border:0">' : '<div style="width:64px;height:64px;border-radius:10px;background:#fdf2ec"></div>';
		$meta = wp_strip_all_tags( wc_display_item_meta( $item, array( 'echo' => false, 'before' => '', 'after' => '', 'separator' => ' · ', 'label_before' => '', 'label_after' => ': ', 'autop' => false ) ) );
		$o .= '<tr><td width="64" valign="middle" style="padding:12px 0;border-top:1px solid #f3e6de;width:64px">' . $im . '</td>'
			. '<td valign="middle" style="padding:12px 14px;border-top:1px solid #f3e6de"><div style="font-size:15px;font-weight:700;color:#6d4e39">' . esc_html( $item->get_name() ) . '</div>'
			. ( $meta ? '<div style="font-size:13px;color:#8a7565;margin-top:3px">' . esc_html( $meta ) . '</div>' : '' ) . '</td>'
			. '<td align="right" valign="middle" style="padding:12px 0;border-top:1px solid #f3e6de;white-space:nowrap"><div style="font-size:15px;font-weight:700;color:#6d4e39">' . wp_kses_post( $order->get_formatted_line_subtotal( $item ) ) . '</div>'
			. '<div style="font-size:13px;color:#8a7565;margin-top:3px">' . (int) $item->get_quantity() . ' kom.</div></td></tr>';
	}
	return $o . '</table></td></tr>';
}

function lm_em_totals( $order ) {
	$row = function ( $l, $v, $b = false ) {
		return '<tr><td style="padding:5px 0;font-size:' . ( $b ? '17px;font-weight:800;color:#6d4e39' : '15px;color:#7a6250' ) . '">' . $l . '</td><td align="right" style="padding:5px 0;font-size:' . ( $b ? '24px;font-weight:800;color:#6d4e39' : '15px;color:#7a6250' ) . '">' . $v . '</td></tr>';
	};
	$o = '<tr><td style="padding:6px 40px 0;' . lm_em_font() . '"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #f3e6de">';
	$o .= '<tr><td colspan="2" style="height:10px;font-size:0;line-height:0">&nbsp;</td></tr>';
	$o .= $row( 'Međuzbir', esc_html( wp_strip_all_tags( $order->get_subtotal_to_display( false, 'incl' ) ) ) );
	if ( (float) $order->get_total_discount() > 0 ) $o .= $row( 'Popust', '−' . esc_html( lm_em_money( $order, (float) $order->get_total_discount() + (float) $order->get_discount_tax() ) ) );
	if ( lm_em_pickup( $order ) ) $o .= $row( 'Preuzimanje u butiku', '0,00 €' );
	elseif ( (float) $order->get_shipping_total() <= 0 ) $o .= $row( 'Dostava', '<span style="color:#3f6265;font-weight:700">Besplatna</span>' );
	else $o .= $row( 'Dostava', esc_html( lm_em_money( $order, (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() ) ) );
	foreach ( $order->get_refunds() as $r ) $o .= $row( 'Vraćeno', '−' . esc_html( lm_em_money( $order, $r->get_amount() ) ) );
	$o .= '<tr><td colspan="2" style="border-top:1px solid #f3e6de;height:8px;font-size:0;line-height:0">&nbsp;</td></tr>';
	$net = (float) $order->get_total() - (float) $order->get_total_refunded();
	$o .= $row( 'Ukupno', esc_html( lm_em_money( $order, $order->get_total_refunded() > 0 ? $net : $order->get_total() ) ), true );
	if ( (float) $order->get_total_tax() > 0 ) $o .= '<tr><td colspan="2" align="right" style="font-size:12px;color:#8a7565">uključuje ' . esc_html( lm_em_money( $order, $order->get_total_tax() ) ) . ' PDV-a</td></tr>';
	return $o . '</table></td></tr>';
}

function lm_em_addresses( $order ) {
	$left = lm_em_pickup( $order )
		? lm_em_label( 'KUPAC' ) . '<div style="font-size:14px;line-height:1.6">' . esc_html( $order->get_formatted_billing_full_name() ) . '<br>' . esc_html( $order->get_billing_phone() ) . '<br>' . esc_html( $order->get_billing_email() ) . '</div>'
		: lm_em_label( 'DOSTAVA NA ADRESU' ) . '<div style="font-size:14px;line-height:1.6">' . wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ) . '<br>' . esc_html( $order->get_billing_phone() ) . '</div>';
	$note  = $order->get_customer_note();
	$right = $note ? lm_em_label( 'VAŠA NAPOMENA' ) . '<div style="font-size:14px;line-height:1.6">' . esc_html( $note ) . '</div>' : '';
	return '<tr><td style="padding:28px 40px 0;' . lm_em_font() . 'color:#7a6250"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td width="50%" valign="top" style="padding-right:12px">' . $left . '</td><td width="50%" valign="top" style="padding-left:12px">' . $right . '</td></tr></table></td></tr>';
}

function lm_em_body( $email, $vars = array() ) {
	$o  = ( isset( $vars['order'] ) && is_a( $vars['order'], 'WC_Order' ) ) ? $vars['order'] : lm_em_order( $email );
	$id = $email->id;
	$c  = lm_em_copy( $email );
	$p  = lm_em_pickup( $o );
	$ey = $o ? 'PORUDŽBINA #' . $o->get_order_number() . ( $o->get_date_created() ? ' · ' . mb_strtoupper( wc_format_datetime( $o->get_date_created(), 'j. F Y.' ) ) : '' ) : 'VAŠ NALOG';
	$labels = $p ? array( 'Primljena', 'Pripremamo', 'Spremna' ) : array( 'Primljena', 'Pripremamo', 'Na putu' );
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="' . lm_em_font() . 'color:#7a6250">';
	echo lm_em_hero( $ey, $c['h'] ? $c['h'] : ( isset( $vars['email_heading'] ) ? $vars['email_heading'] : '' ), $c['txt'] );
	switch ( $id ) {
		case 'customer_processing_order':
		case 'customer_on_hold_order':
			echo lm_em_tracker( $labels, array( 'done', 'customer_on_hold_order' === $id ? 'todo' : 'current', 'todo' ) );
			echo lm_em_cards( $o, 'processing' );
			echo lm_em_items( $o );
			echo lm_em_totals( $o );
			echo lm_em_addresses( $o );
			break;
		case 'customer_completed_order':
			echo lm_em_tracker( array( 'Primljena', 'Pripremljena', $p ? 'Spremna' : 'Na putu' ), array( 'done', 'done', 'done' ) );
			if ( $p ) echo '<tr><td style="padding:26px 40px 0">' . lm_em_pickup_box() . '</td></tr>';
			echo '<tr><td style="padding:12px 40px 0">' . lm_em_box( lm_em_payment_html( $o, 'completed' ) ) . '</td></tr>';
			echo lm_em_items( $o, $p ? 'Vaša porudžbina' : 'U paketu' );
			echo lm_em_totals( $o );
			echo '<tr><td style="padding:28px 40px 0"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1.5px solid #f6ded4;border-radius:16px"><tr><td style="padding:20px 24px;' . lm_em_font() . '">'
				. '<div style="font-size:17px;font-weight:800;color:#6d4e39;margin:0 0 6px">Ne odgovara veličina?</div>'
				. '<div style="font-size:15px;line-height:1.55;color:#7a6250;margin:0 0 8px">Zamjena ili povrat je moguć u roku od 14 dana.</div>'
				. '<a href="' . esc_url( home_url( '/povrat-robe-i-sredstava/' ) ) . '" style="font-size:14px;font-weight:700;color:#8a4b47">Kako ide zamjena →</a></td></tr></table></td></tr>';
			break;
		case 'customer_failed_order':
			echo '<tr><td style="padding:18px 40px 0">' . lm_em_btn( $o->get_checkout_payment_url(), 'POKUŠAJ PONOVO' ) . '</td></tr>';
			echo '<tr><td style="padding:28px 40px 0">' . lm_em_box( lm_em_label( 'ČEŠĆI RAZLOZI' ) . '<div style="font-size:14px;line-height:1.7">· Nedovoljno sredstava ili dostignut dnevni limit<br>· Kartica nije omogućena za plaćanje preko interneta<br>· Kod iz SMS-a ili aplikacije banke nije potvrđen na vrijeme</div>' ) . '</td></tr>';
			echo lm_em_items( $o, 'Porudžbina' );
			echo lm_em_totals( $o );
			break;
		case 'customer_cancelled_order':
			echo lm_em_items( $o, 'Otkazana porudžbina' );
			echo lm_em_totals( $o );
			echo '<tr><td style="padding:26px 40px 0">' . lm_em_btn( home_url( '/prodavnica/' ), 'NASTAVI KUPOVINU', true ) . '</td></tr>';
			break;
		case 'customer_refunded_order':
		case 'customer_partially_refunded_order':
			echo lm_em_items( $o, 'Porudžbina' );
			echo lm_em_totals( $o );
			break;
		case 'customer_note':
			$note = isset( $vars['customer_note'] ) ? $vars['customer_note'] : ( isset( $email->customer_note ) ? $email->customer_note : '' );
			echo '<tr><td style="padding:22px 40px 0">' . lm_em_box( '<div style="font-size:16px;line-height:1.6;color:#6d4e39">' . wpautop( wptexturize( make_clickable( esc_html( $note ) ) ) ) . '</div>', '#f6ded4' ) . '</td></tr>';
			echo lm_em_items( $o, 'Porudžbina' );
			echo lm_em_totals( $o );
			break;
		case 'customer_invoice':
			if ( $o->needs_payment() ) echo '<tr><td style="padding:18px 40px 0">' . lm_em_btn( $o->get_checkout_payment_url(), 'PLATI PORUDŽBINU' ) . '</td></tr>';
			echo lm_em_items( $o );
			echo lm_em_totals( $o );
			echo lm_em_addresses( $o );
			break;
		case 'customer_new_account':
			$pw = ! empty( $vars['password_generated'] ) && ! empty( $vars['set_password_url'] );
			echo '<tr><td style="padding:18px 40px 0">' . lm_em_btn( $pw ? $vars['set_password_url'] : wc_get_page_permalink( 'myaccount' ), $pw ? 'POSTAVI LOZINKU' : 'OTVORI MOJ NALOG' ) . '</td></tr>';
			echo '<tr><td style="padding:30px 40px 0;' . lm_em_font() . '"><h2 style="margin:0 0 12px;font-size:19px;font-weight:800;color:#6d4e39">Šta vam nalog donosi</h2>';
			foreach ( array( array( 'Lista želja', 'Sačuvajte ono što vam se dopada i podijelite listu linkom.' ), array( 'Sve porudžbine na jednom mjestu', 'Status, detalji i ranije kupovine.' ), array( 'Brža kupovina', 'Adresa i telefon su već upisani pri sljedećoj porudžbini.' ) ) as $bb ) {
				echo '<div style="margin:0 0 10px">' . lm_em_box( '<div style="font-size:15px;font-weight:700;color:#6d4e39;margin:0 0 4px">' . esc_html( $bb[0] ) . '</div><div style="font-size:14px;line-height:1.5">' . esc_html( $bb[1] ) . '</div>' ) . '</div>';
			}
			echo '</td></tr>';
			break;
		case 'customer_reset_password':
			$url = add_query_arg( array( 'key' => isset( $vars['reset_key'] ) ? $vars['reset_key'] : '', 'id' => isset( $vars['user_id'] ) ? $vars['user_id'] : '', 'login' => rawurlencode( isset( $vars['user_login'] ) ? $vars['user_login'] : '' ) ), wc_get_endpoint_url( 'lost-password', '', wc_get_page_permalink( 'myaccount' ) ) );
			echo '<tr><td style="padding:18px 40px 0">' . lm_em_btn( $url, 'POSTAVI NOVU LOZINKU' ) . '</td></tr>';
			echo '<tr><td style="padding:22px 40px 0;' . lm_em_font() . 'font-size:14px;line-height:1.6;color:#8a7565">Ako niste vi tražili novu lozinku, zanemarite ovaj email — lozinka ostaje ista.</td></tr>';
			break;
	}
	echo '</table>';
}
