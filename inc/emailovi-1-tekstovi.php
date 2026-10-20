<?php
/**
 * Lady Mom – emailovi (1/3) tekstovi i naslovi
 * (Ranije Code Snippets #22, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – emailovi kupcima (1/3): podaci, tekstovi, naslovi. Dizajn 9.10.2026.
// Šabloni u woodmart-child/woocommerce/emails/ zovu lm_em_* funkcije; bez ovih snippeta padaju na WooCommerce šablone.

function lm_em_ids() {
	return array( 'customer_processing_order', 'customer_on_hold_order', 'customer_completed_order', 'customer_failed_order', 'customer_cancelled_order', 'customer_refunded_order', 'customer_partially_refunded_order', 'customer_note', 'customer_invoice', 'customer_new_account', 'customer_reset_password' );
}
function lm_em_handles( $email ) {
	return $email && is_object( $email ) && isset( $email->id ) && in_array( $email->id, lm_em_ids(), true ) && function_exists( 'lm_em_body' );
}
function lm_em_media( $k ) {
	$m = get_option( 'lm_email_media', array() );
	return isset( $m[ $k ] ) ? (int) $m[ $k ] : 0;
}
function lm_em_font() {
	return "font-family:Raleway,'Helvetica Neue',Helvetica,Arial,sans-serif;";
}
function lm_em_order( $email ) {
	return ( $email && isset( $email->object ) && is_a( $email->object, 'WC_Order' ) ) ? $email->object : null;
}
function lm_em_pickup( $order ) {
	if ( ! $order ) return false;
	foreach ( $order->get_shipping_methods() as $s ) {
		if ( 0 === strpos( $s->get_method_id(), 'local_pickup' ) ) return true;
	}
	return false;
}
/** Porudžbina bez dostave (samo virtuelni artikli, npr. Ready Mom). */
function lm_em_bez_dostave( $order ) {
	if ( ! $order || $order->get_shipping_methods() ) return false;
	foreach ( $order->get_items() as $item ) {
		$pr = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : null;
		if ( ! $pr || ! $pr->is_virtual() ) return false;
	}
	return true;
}
/** Porudžbina sadrži Ready Mom program. */
function lm_em_ima_rm( $order ) {
	$rm = function_exists( 'lm_rm_proizvod_id' ) ? lm_rm_proizvod_id() : 0;
	if ( ! $order || ! $rm ) return false;
	foreach ( $order->get_items() as $item ) {
		if ( is_callable( array( $item, 'get_product_id' ) ) && (int) $item->get_product_id() === $rm ) return true;
	}
	return false;
}
function lm_em_card( $order ) {
	return $order && 0 === strpos( (string) $order->get_payment_method(), 'bankart' );
}
function lm_em_ime( $email ) {
	$o = lm_em_order( $email );
	if ( $o ) return trim( $o->get_billing_first_name() );
	if ( $email && ! empty( $email->user_login ) ) {
		$u = get_user_by( 'login', $email->user_login );
		if ( $u && $u->first_name ) return $u->first_name;
	}
	return '';
}
function lm_em_copy( $email ) {
	$id = $email && isset( $email->id ) ? $email->id : '';
	$o  = lm_em_order( $email );
	$p  = lm_em_pickup( $o );
	$k  = lm_em_card( $o );
	$i  = lm_em_ime( $email );
	$iz = $i ? ', ' . $i : '';
	$n  = $o ? $o->get_order_number() : '';
	$b  = lm_em_bez_dostave( $o );
	$rm = $b && lm_em_ima_rm( $o );
	$c  = array( 's' => '', 'h' => '', 'pre' => '', 'txt' => '' );
	// Bez dostave (Ready Mom, 20.10.2026): bez teksta o paketu i kuriru.
	if ( $b && 'customer_processing_order' === $id ) {
		return $rm
			? array( 's' => 'Hvala' . $iz . '! Prijava za Ready Mom je primljena', 'h' => 'Hvala' . $iz . '! Prijava je primljena.', 'pre' => 'Javićemo vam se sa terminom i detaljima programa.', 'txt' => 'Hvala na prijavi za Ready Mom. Javićemo vam se telefonom ili emailom sa terminom prvog predavanja i detaljima.' )
			: array( 's' => 'Hvala' . $iz . '! Porudžbina #' . $n . ' je primljena', 'h' => 'Hvala' . $iz . '! Porudžbina je primljena.', 'pre' => 'Javićemo vam se sa detaljima.', 'txt' => 'Javićemo vam se telefonom ili emailom sa detaljima.' );
	}
	if ( $b && 'customer_completed_order' === $id ) {
		return array( 's' => 'Porudžbina #' . $n . ' je završena', 'h' => 'Hvala' . $iz . '!', 'pre' => 'Porudžbina je završena.', 'txt' => 'Porudžbina #' . $n . ' je završena. Hvala što ste izabrali Lady Mom.' );
	}
	switch ( $id ) {
		case 'customer_processing_order':
			$c = array( 's' => 'Hvala' . $iz . '! Porudžbina #' . $n . ' je primljena', 'h' => 'Hvala' . $iz . '! Porudžbina je primljena.',
				'pre' => $p ? 'Pripremamo je u butiku. Ovdje je pregled porudžbine.' : 'Pripremamo je za slanje. Ovdje je pregled porudžbine.',
				'txt' => $p ? 'Pripremamo je u butiku. Kad bude spremna za preuzimanje, stiže vam još jedan email.' : 'Pripremamo je za slanje. Kad paket predamo kuriru, stiže vam još jedan email.' );
			break;
		case 'customer_on_hold_order':
			$c = array( 's' => 'Porudžbina #' . $n . ' je primljena', 'h' => 'Hvala' . $iz . '! Porudžbina je primljena.', 'pre' => 'Javićemo vam čim je potvrdimo.', 'txt' => 'Porudžbina čeka potvrdu. Javićemo vam čim je potvrdimo.' );
			break;
		case 'customer_completed_order':
			$c = $p
				? array( 's' => 'Porudžbina #' . $n . ' je spremna za preuzimanje', 'h' => 'Porudžbina je spremna' . $iz . '.', 'pre' => 'Čeka vas u butiku, Branka Deletića bb.', 'txt' => 'Čeka vas u butiku Lady Mom, Branka Deletića bb, Podgorica.' )
				: array( 's' => 'Paket #' . $n . ' je na putu', 'h' => 'Paket je na putu' . $iz . '.', 'pre' => 'Kurir The Vault, dostava 1–3 radna dana.', 'txt' => 'Predali smo ga kurirskoj službi The Vault. Dostava traje 1–3 radna dana.' );
			break;
		case 'customer_failed_order':
			$c = array( 's' => 'Plaćanje za porudžbinu #' . $n . ' nije prošlo', 'h' => 'Plaćanje nije prošlo.', 'pre' => 'Porudžbina je sačuvana i možete pokušati ponovo.', 'txt' => 'Banka nije odobrila plaćanje karticom. Porudžbina je sačuvana i možete pokušati ponovo, istom ili drugom karticom.' );
			break;
		case 'customer_cancelled_order':
			$c = array( 's' => 'Porudžbina #' . $n . ' je otkazana', 'h' => 'Porudžbina je otkazana.',
				'pre' => $k ? 'Ako je iznos bio rezervisan na kartici, rezervacija se poništava.' : 'Ako imate pitanja, pišite nam.',
				'txt' => 'Porudžbina #' . $n . ' je otkazana.' . ( $k ? ' Ako je iznos bio rezervisan na kartici, rezervacija se poništava; rok zavisi od vaše banke.' : '' ) );
			break;
		case 'customer_refunded_order':
		case 'customer_partially_refunded_order':
			$dio = ! empty( $email->partial_refund );
			$c = array( 's' => ( $dio ? 'Vratili smo dio iznosa za porudžbinu #' : 'Vratili smo novac za porudžbinu #' ) . $n, 'h' => $dio ? 'Vratili smo dio iznosa.' : 'Vratili smo novac.',
				'pre' => $k ? 'Rok knjiženja na kartici zavisi od banke.' : 'Pregled povrata je u nastavku.',
				'txt' => 'Povrat za porudžbinu #' . $n . ' je evidentiran.' . ( $k ? ' Rok knjiženja na kartici zavisi od banke.' : '' ) );
			break;
		case 'customer_note':
			$nt = isset( $email->customer_note ) ? wp_strip_all_tags( $email->customer_note ) : '';
			$c = array( 's' => 'Nova poruka o porudžbini #' . $n, 'h' => 'Imamo poruku za vas.', 'pre' => mb_substr( $nt, 0, 90 ), 'txt' => 'Dodali smo napomenu uz porudžbinu #' . $n . ':' );
			break;
		case 'customer_invoice':
			$c = array( 's' => 'Detalji porudžbine #' . $n, 'h' => 'Detalji porudžbine #' . $n, 'pre' => 'Pregled porudžbine i način plaćanja.',
				'txt' => ( $o && $o->needs_payment() ) ? 'Porudžbinu možete platiti putem dugmeta ispod.' : 'Ovdje je pregled vaše porudžbine.' );
			break;
		case 'customer_new_account':
			$c = array( 's' => 'Dobro došli u Lady Mom' . $iz, 'h' => 'Dobro došli u Lady Mom' . $iz . '.', 'pre' => 'Nalog je napravljen. Ovdje je šta vam donosi.',
				'txt' => 'Nalog je napravljen.' . ( ! empty( $email->user_email ) ? ' Prijavljujete se adresom ' . $email->user_email . '.' : '' ) );
			break;
		case 'customer_reset_password':
			$c = array( 's' => 'Postavite novu lozinku za Lady Mom', 'h' => 'Nova lozinka', 'pre' => 'Link važi 24 sata.',
				'txt' => 'Zatražena je nova lozinka za nalog ' . ( isset( $email->user_login ) ? $email->user_login : '' ) . '. Ako ste to bili vi, kliknite na dugme ispod. Link važi 24 sata.' );
			break;
	}
	return $c;
}

foreach ( lm_em_ids() as $lm_em_id ) {
	add_filter( 'woocommerce_email_subject_' . $lm_em_id, function ( $s, $obj = null, $email = null ) {
		if ( $email && function_exists( 'lm_em_body' ) ) { $c = lm_em_copy( $email ); if ( $c['s'] ) return $c['s']; }
		return $s;
	}, 20, 3 );
	add_filter( 'woocommerce_email_heading_' . $lm_em_id, function ( $h, $obj = null, $email = null ) {
		if ( $email && function_exists( 'lm_em_body' ) ) { $c = lm_em_copy( $email ); if ( $c['h'] ) return $c['h']; }
		return $h;
	}, 20, 3 );
}

/**
 * Kad kupac odgovori na email o porudžbini ili nalogu, odgovor ide i butiku (20.10.2026):
 * natalija@ladymom.me i adrese iz opcije `lm_butik_emailovi` (ladymom.mne@gmail.com).
 */
function lm_em_odgovor_primaoci() {
	$p = array( 'natalija@ladymom.me' );
	foreach ( (array) get_option( 'lm_butik_emailovi', array() ) as $e ) {
		if ( is_email( $e ) ) {
			$p[] = $e;
		}
	}
	return array_values( array_unique( $p ) );
}
add_filter( 'woocommerce_email_headers', function ( $headers, $id = '', $object = null, $email = null ) {
	$kupcu = $email && is_object( $email ) && method_exists( $email, 'is_customer_email' ) ? $email->is_customer_email() : in_array( $id, lm_em_ids(), true );
	if ( ! $kupcu ) {
		return $headers;
	}
	// WooCommerce emailu kupcu sam stavlja „Reply-to: Lady Mom <natalija@…>“; zamjenjujemo ga.
	$headers = preg_replace( '/^Reply-to:[^\n]*\n?/im', '', (string) $headers );
	return $headers . 'Reply-To: ' . implode( ', ', lm_em_odgovor_primaoci() ) . "\r\n";
}, 20, 4 );

// Naši emailovi nose stil u samom kodu – WooCommerce CSS (npr. margin na slikama) se ne primjenjuje na njih.
add_filter( 'woocommerce_email_styles', function ( $css, $email = null ) {
	if ( $email && lm_em_handles( $email ) ) return 'img{border:0;outline:none;text-decoration:none}a{color:#a05c57}';
	return $css;
}, 99, 2 );

/**
 * Jezik emailova kad se šalju iz administracije (npr. otkazivanje porudžbine).
 * WooCommerce emailove prodavnici šalje na jeziku profila admina (engleski), a kod
 * emailova kupcu prevod mjeseci i riječi nije bio uključen u administraciji („listopad“).
 * Od trenutka kad WooCommerce traži primaoca do slanja: jezik sajta + crnogorski prevod.
 */
function lm_em_pocni( $recipient, $object = null, $email = null ) {
	if ( $recipient && is_object( $object ) && empty( $GLOBALS['lm_em_salje'] ) ) {
		$GLOBALS['lm_em_salje'] = ( determine_locale() !== get_locale() && switch_to_locale( get_locale() ) ) ? 2 : 1;
	}
	return $recipient;
}
add_action( 'woocommerce_email', function ( $mailer ) {
	foreach ( (array) $mailer->emails as $e ) {
		if ( isset( $e->id ) ) {
			add_filter( 'woocommerce_email_recipient_' . $e->id, 'lm_em_pocni', 1, 3 );
		}
	}
} );
add_action( 'woocommerce_email_sent', function () {
	if ( ! empty( $GLOBALS['lm_em_salje'] ) ) {
		if ( 2 === $GLOBALS['lm_em_salje'] ) {
			restore_previous_locale();
		}
		$GLOBALS['lm_em_salje'] = 0;
	}
}, 99 );

// Emailovi prodavnici: naslovi na crnogorskom.
function lm_em_admin_copy( $id, $o ) {
	if ( ! $o || ! is_a( $o, 'WC_Order' ) ) {
		return null;
	}
	$n    = $o->get_order_number();
	$kupac = trim( $o->get_billing_first_name() . ' ' . $o->get_billing_last_name() );
	$iznos = html_entity_decode( wp_strip_all_tags( wc_price( $o->get_total(), array( 'currency' => $o->get_currency() ) ) ), ENT_QUOTES, 'UTF-8' );
	switch ( $id ) {
		case 'new_order':
			return array( 's' => 'Nova porudžbina #' . $n . ( $kupac ? ' – ' . $kupac : '' ) . ' (' . $iznos . ')', 'h' => 'Nova porudžbina #' . $n );
		case 'cancelled_order':
			return array( 's' => 'Porudžbina #' . $n . ' je otkazana' . ( $kupac ? ' – ' . $kupac : '' ), 'h' => 'Porudžbina #' . $n . ' je otkazana' );
		case 'failed_order':
			return array( 's' => 'Neuspjela porudžbina #' . $n . ( $kupac ? ' – ' . $kupac : '' ), 'h' => 'Plaćanje za porudžbinu #' . $n . ' nije prošlo' );
	}
	return null;
}
foreach ( array( 'new_order', 'cancelled_order', 'failed_order' ) as $lm_em_aid ) {
	add_filter( 'woocommerce_email_subject_' . $lm_em_aid, function ( $s, $obj = null, $email = null ) {
		$c = $email ? lm_em_admin_copy( $email->id, $obj ) : null;
		return $c ? $c['s'] : $s;
	}, 20, 3 );
	add_filter( 'woocommerce_email_heading_' . $lm_em_aid, function ( $h, $obj = null, $email = null ) {
		$c = $email ? lm_em_admin_copy( $email->id, $obj ) : null;
		return $c ? $c['h'] : $h;
	}, 20, 3 );
}
unset( $lm_em_aid );
