<?php
/**
 * Lady Mom – molba za ocjenu artikala poslije isporuke (24.10.2026, stavka n2).
 *
 * - Kad porudžbina sa novog sajta pređe u „Završeno“, za 10 dana se zakaže jedan email
 *   (Action Scheduler) sa artiklima iz porudžbine i dugmetom „Ocijeni“ za svaki.
 * - Link nosi potpis porudžbine (`?ocjena=`): samo uz njega artikal bez recenzija prikazuje
 *   formu za ocjenu (inc/pregled-dorade.php r9 je inače skriva), polja ime i email su
 *   popunjena iz porudžbine, a ocjena se objavljuje odmah i označava kao „kupac“.
 *   Ocjena bez potpisa čeka odobrenje u administraciji (zaštita od spama).
 * - Uključuje se opcijom `lm_molba_ocjena` = 1 (Marko, poslije probnog emaila). Dok je 0,
 *   ništa se ne zakazuje i ništa se ne šalje.
 * - Ne odnosi se na stare porudžbine (prije 17.10.2026, zatvorene 24.10.) ni na test porudžbine.
 */

defined( 'ABSPATH' ) || exit;

define( 'LM_MOLBA_DANA', 10 );
define( 'LM_MOLBA_OD', 1792281600 ); // 18.10.2026 00:00 UTC: samo porudžbine novog sajta (posljednja stara je #50960 od 17.10.)

function lm_molba_ukljuceno() {
	return '1' === (string) get_option( 'lm_molba_ocjena', '0' );
}

/** Potpis za link u emailu: id porudžbine + HMAC sa ključem porudžbine. */
function lm_molba_token( $order ) {
	return $order->get_id() . '.' . substr( hash_hmac( 'sha256', 'ocjena|' . $order->get_id() . '|' . $order->get_order_key(), wp_salt( 'auth' ) ), 0, 20 );
}

/** Porudžbina iz potpisa, ili null. */
function lm_molba_porudzbina_iz_tokena( $token ) {
	if ( ! is_string( $token ) || ! preg_match( '/^(\d+)\.([a-f0-9]{20})$/', $token, $m ) ) {
		return null;
	}
	$o = wc_get_order( (int) $m[1] );
	if ( ! $o instanceof WC_Order || ! hash_equals( lm_molba_token( $o ), $token ) ) {
		return null;
	}
	return $o;
}

/** Da li je artikal (ili njegova varijanta) u porudžbini. */
function lm_molba_artikal_u_porudzbini( $order, $product_id ) {
	foreach ( $order->get_items() as $item ) {
		if ( (int) $item->get_product_id() === (int) $product_id ) {
			return true;
		}
	}
	return false;
}

/** Artikli za email: glavni artikli iz porudžbine, bez Ready Mom programa, najviše 6. */
function lm_molba_artikli( $order ) {
	$out = array();
	foreach ( $order->get_items() as $item ) {
		$p = wc_get_product( $item->get_product_id() );
		if ( ! $p || 'publish' !== $p->get_status() || $p->is_virtual() || isset( $out[ $p->get_id() ] ) ) {
			continue;
		}
		$out[ $p->get_id() ] = $p;
	}
	return array_slice( $out, 0, 6, true );
}

/* ---------- Zakazivanje ---------- */

add_action( 'woocommerce_order_status_completed', function ( $order_id ) {
	if ( ! lm_molba_ukljuceno() || ! function_exists( 'as_schedule_single_action' ) ) {
		return;
	}
	$o = wc_get_order( $order_id );
	if ( ! $o || $o->get_meta( '_lm_test' ) || $o->get_meta( '_lm_molba_ocjena' ) || ! is_email( $o->get_billing_email() ) ) {
		return;
	}
	$d = $o->get_date_created();
	if ( ! $d || $d->getTimestamp() < LM_MOLBA_OD || ! lm_molba_artikli( $o ) ) {
		return;
	}
	if ( ! as_next_scheduled_action( 'lm_molba_za_ocjenu', array( (int) $order_id ), 'lady-mom' ) ) {
		as_schedule_single_action( time() + LM_MOLBA_DANA * DAY_IN_SECONDS, 'lm_molba_za_ocjenu', array( (int) $order_id ), 'lady-mom' );
	}
}, 30 );

add_action( 'lm_molba_za_ocjenu', 'lm_molba_posalji' );

/** Slanje (iz rasporeda). Provjere se ponavljaju, jer se za 10 dana mnogo šta promijeni. */
function lm_molba_posalji( $order_id ) {
	$o = wc_get_order( $order_id );
	if ( ! lm_molba_ukljuceno() || ! $o || 'completed' !== $o->get_status() || $o->get_meta( '_lm_molba_ocjena' ) || $o->get_total_refunded() > 0 ) {
		return;
	}
	$artikli = lm_molba_artikli( $o );
	if ( ! $artikli || ! is_email( $o->get_billing_email() ) ) {
		return;
	}
	if ( lm_molba_mail( $o, $artikli, $o->get_billing_email() ) ) {
		$o->update_meta_data( '_lm_molba_ocjena', time() );
		$o->save();
		$o->add_order_note( 'Kupcu je poslata molba za ocjenu artikala (jednom po porudžbini).' );
	}
}

/** Email u dizajnu ostalih emailova. $ime za probu, inače ime iz porudžbine. */
function lm_molba_mail( $o, $artikli, $na, $ime = null ) {
	if ( ! function_exists( 'lm_em_sve_ucitano' ) || ! lm_em_sve_ucitano() ) {
		return false;
	}
	$ime   = null !== $ime ? $ime : trim( (string) $o->get_billing_first_name() );
	$token = lm_molba_token( $o );
	$broj  = $o->get_order_number();
	$host  = wp_parse_url( home_url(), PHP_URL_HOST );

	// Obraćanje sa „ti“ (Marko, 24.10.2026, n4), bez glagola po rodu (kupuju i očevi i bake).
	$GLOBALS['lm_em_pre']    = 'Jedan klik po artiklu. Tvoja ocjena pomaže drugim mamama da izaberu model i veličinu.';
	$GLOBALS['lm_em_razlog'] = array( 'Ovaj email ti šaljemo jer imaš porudžbinu na ' . $host . '. Za ovu porudžbinu ga šaljemo samo jednom.', '' );

	$redovi = '';
	foreach ( $artikli as $p ) {
		$url  = add_query_arg( 'ocjena', $token, get_permalink( $p->get_id() ) ) . '#reviews';
		$slik = $p->get_image_id() ? lm_em_slika_url( $p->get_image_id() ) : '';
		$redovi .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 12px;border:1.5px solid #f6ded4;border-radius:16px"><tr>'
			. ( $slik ? '<td width="84" valign="middle" style="padding:12px 0 12px 12px"><a href="' . esc_url( $url ) . '"><img src="' . esc_url( $slik ) . '" width="72" alt="' . esc_attr( $p->get_name() ) . '" style="display:block;width:72px;height:auto;border:0;border-radius:10px"></a></td>' : '' )
			. '<td valign="middle" class="lm-box-td" style="padding:14px 16px;' . lm_em_font() . '"><div style="font-size:15px;font-weight:700;line-height:1.4;color:#6d4e39;margin:0 0 10px">' . esc_html( $p->get_name() ) . '</div>'
			. lm_em_btn( $url, 'OCIJENI', true ) . '</td></tr></table>';
	}

	ob_start();
	lm_em_header( '', null );
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="' . lm_em_font() . 'color:#7a6250">';
	echo lm_em_hero( 'PORUDŽBINA #' . $broj, 'Kako ti odgovara' . ( $ime ? ', ' . $ime : '' ) . '?', 'Prošlo je desetak dana otkako ti je stigla porudžbina. Kad isprobaš artikle, ocijeni ih na sajtu: tvoje iskustvo pomaže drugim mamama da izaberu pravi model i veličinu.' ); // phpcs:ignore
	echo lm_em_red( $redovi, 24 ); // phpcs:ignore
	echo lm_em_red( '<div style="font-size:14px;line-height:1.6;color:#8a7565">Ocjena traje minut: zvjezdice i par riječi, ako želiš. Ne treba prijava na nalog.</div>', 8 ); // phpcs:ignore
	echo '</table>';
	lm_em_footer( null, array( 'kontakt' => 'Nešto nije u redu sa porudžbinom?' ) );
	$html = lm_em_dovrsi( ob_get_clean() );
	unset( $GLOBALS['lm_em_pre'], $GLOBALS['lm_em_razlog'] );

	return wp_mail(
		$na,
		'Kako ti odgovara porudžbina #' . $broj . '?',
		$html,
		array( 'Content-Type: text/html; charset=UTF-8', 'Reply-To: ' . ( defined( 'LM_POVRAT_EMAIL' ) ? LM_POVRAT_EMAIL : get_option( 'admin_email' ) ) )
	);
}

/* ---------- Stranica artikla: forma za ocjenu uz potpis iz emaila ---------- */

/** Porudžbina iz linka na ovoj stranici artikla (ili null). */
function lm_molba_sa_linka() {
	static $o = false;
	if ( false !== $o ) {
		return $o;
	}
	$o = null;
	if ( isset( $_GET['ocjena'] ) && function_exists( 'is_product' ) && is_product() ) { // phpcs:ignore
		$kand = lm_molba_porudzbina_iz_tokena( sanitize_text_field( wp_unslash( $_GET['ocjena'] ) ) ); // phpcs:ignore
		if ( $kand && lm_molba_artikal_u_porudzbini( $kand, get_queried_object_id() ) ) {
			$o = $kand;
		}
	}
	return $o;
}

// Stranica sa ličnim podacima u formi se ne kešira.
add_action( 'template_redirect', function () {
	if ( isset( $_GET['ocjena'] ) ) { // phpcs:ignore
		do_action( 'litespeed_control_set_nocache', 'Lady Mom: forma za ocjenu iz emaila' );
		nocache_headers();
	}
}, 1 );

// Tab sa formom ostaje i kad artikal nema recenziju (r9 ga inače skida), i otvoren je prvi.
add_filter( 'woocommerce_product_tabs', function ( $tabovi ) {
	if ( ! lm_molba_sa_linka() || ! function_exists( 'wc_get_product' ) ) {
		return $tabovi;
	}
	if ( ! isset( $tabovi['reviews'] ) && wc_review_ratings_enabled() ) {
		$tabovi['reviews'] = array(
			'title'    => 'Ocijeni artikal',
			'priority' => 1,
			'callback' => 'comments_template',
		);
	} elseif ( isset( $tabovi['reviews'] ) ) {
		$tabovi['reviews']['priority'] = 1;
	}
	return $tabovi;
}, 99 );

// Ime i email iz porudžbine + skriveni potpis u formi.
add_filter( 'woocommerce_product_review_comment_form_args', function ( $args ) {
	$o = lm_molba_sa_linka();
	if ( ! $o ) {
		return $args;
	}
	$ime   = trim( $o->get_billing_first_name() . ' ' . mb_substr( (string) $o->get_billing_last_name(), 0, 1 ) . ( $o->get_billing_last_name() ? '.' : '' ) );
	$email = $o->get_billing_email();
	if ( isset( $args['fields']['author'] ) ) {
		$args['fields']['author'] = preg_replace( '/(<input[^>]+name="author"[^>]*?)value="[^"]*"/', '$1', $args['fields']['author'] );
		$args['fields']['author'] = str_replace( 'name="author"', 'name="author" value="' . esc_attr( $ime ) . '"', $args['fields']['author'] );
	}
	if ( isset( $args['fields']['email'] ) ) {
		$args['fields']['email'] = preg_replace( '/(<input[^>]+name="email"[^>]*?)value="[^"]*"/', '$1', $args['fields']['email'] );
		$args['fields']['email'] = str_replace( 'name="email"', 'name="email" value="' . esc_attr( $email ) . '"', $args['fields']['email'] );
	}
	$args['comment_field'] = '<input type="hidden" name="lm_ocjena" value="' . esc_attr( lm_molba_token( $o ) ) . '">' . ( $args['comment_field'] ?? '' );
	return $args;
} );

// Ocjena sa ispravnim potpisom za taj artikal: objavljuje se odmah i ide kao „kupac“.
// Ostale ocjene artikala čekaju odobrenje (zaštita od spama; forma je inače sakrivena).
add_filter( 'pre_comment_approved', function ( $odobreno, $podaci ) {
	if ( empty( $podaci['comment_post_ID'] ) || 'product' !== get_post_type( (int) $podaci['comment_post_ID'] ) || 'spam' === $odobreno || 'trash' === $odobreno ) {
		return $odobreno;
	}
	$token = isset( $_POST['lm_ocjena'] ) ? sanitize_text_field( wp_unslash( $_POST['lm_ocjena'] ) ) : ''; // phpcs:ignore
	$o     = $token ? lm_molba_porudzbina_iz_tokena( $token ) : null;
	if ( $o && lm_molba_artikal_u_porudzbini( $o, (int) $podaci['comment_post_ID'] ) ) {
		$GLOBALS['lm_molba_ocjena_kupca'] = $o->get_id();
		return $o->get_meta( '_lm_test' ) ? 0 : 1; // ocjena iz probnog emaila ne izlazi na sajt sama
	}
	return current_user_can( 'moderate_comments' ) ? $odobreno : 0;
}, 20, 2 );

add_action( 'comment_post', function ( $comment_id ) {
	if ( empty( $GLOBALS['lm_molba_ocjena_kupca'] ) ) {
		return;
	}
	update_comment_meta( $comment_id, 'verified', 1 );
	update_comment_meta( $comment_id, '_lm_porudzbina', (int) $GLOBALS['lm_molba_ocjena_kupca'] );
}, 20 );
