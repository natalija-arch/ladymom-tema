<?php
/**
 * Lady Mom – zahtjev za povrat sa slikama
 * (Ranije Code Snippets #29, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Lady Mom – zahtjev za zamjenu / povrat sa fotografijama (12.10.2026).
// Forma: [lm_povrat_forma] na stranici „Povrat robe i sredstava“.
// Zahtjev se čuva u WooCommerce → Zahtjevi za povrat, šalje se email butiku (sa slikama) i potvrda kupcu.

define( 'LM_POVRAT_EMAIL', 'natalija@ladymom.me' );

/**
 * Primaoci zahtjeva u butiku: natalija@ladymom.me i adrese iz opcije `lm_butik_emailovi`
 * (20.10.2026: ladymom.mne@gmail.com, email djevojaka u butiku).
 */
function lm_povrat_primaoci() {
	$primaoci = array( LM_POVRAT_EMAIL );
	foreach ( (array) get_option( 'lm_butik_emailovi', array() ) as $e ) {
		if ( is_email( $e ) ) {
			$primaoci[] = $e;
		}
	}
	return array_values( array_unique( $primaoci ) );
}

// 1) Zapis zahtjeva u administraciji.
add_action( 'init', function () {
	register_post_type( 'lm_povrat', array(
		'labels'          => array(
			'name'          => 'Zahtjevi za povrat',
			'singular_name' => 'Zahtjev za povrat',
			'menu_name'     => 'Zahtjevi za povrat',
			'all_items'     => 'Zahtjevi za povrat',
			'edit_item'     => 'Zahtjev za povrat',
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'woocommerce',
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'    => true,
	) );
} );

function lm_povrat_url( $args = array() ) {
	$page = get_page_by_path( 'povrat-robe-i-sredstava' );
	$url  = $page ? get_permalink( $page ) : home_url( '/povrat-robe-i-sredstava/' );
	return add_query_arg( $args, $url ) . '#lm-povrat-forma';
}

// 2) Forma.
add_shortcode( 'lm_povrat_forma', function () {
	$u     = wp_get_current_user();
	$ime   = $u && $u->ID ? trim( $u->first_name . ' ' . $u->last_name ) : '';
	$email = $u && $u->ID ? $u->user_email : '';
	$tel   = $u && $u->ID ? get_user_meta( $u->ID, 'billing_phone', true ) : '';
	if ( $u && $u->ID && '' === $ime ) {
		$ime = trim( get_user_meta( $u->ID, 'billing_first_name', true ) . ' ' . get_user_meta( $u->ID, 'billing_last_name', true ) );
	}
	$st  = isset( $_GET['zahtjev'] ) ? sanitize_key( $_GET['zahtjev'] ) : '';
	$msg = '';
	if ( 'poslat' === $st ) {
		$msg = '<div class="lm-pf-ok" role="status"><strong>Zahtjev je poslat.</strong> Javićemo vam se sa uputstvom, najčešće istog ili sljedećeg radnog dana. Kopiju zahtjeva smo poslali i na vaš email.</div>';
	} elseif ( 'greska' === $st ) {
		$r    = isset( $_GET['r'] ) ? sanitize_key( $_GET['r'] ) : '';
		$txt  = array(
			'ime'    => 'Upišite ime i prezime.',
			'email'  => 'Upišite ispravnu email adresu.',
			'slika'  => 'Slike moraju biti JPG, PNG, WEBP ili HEIC, najviše 3 slike i do 8 MB po slici.',
			'cesto'  => 'Poslali ste više zahtjeva za kratko vrijeme. Pokušajte ponovo za sat vremena ili nas pozovite.',
			'server' => 'Zahtjev nije mogao biti sačuvan. Pozovite nas na 067 030 655 ili pišite na WhatsApp ili Viber.',
		);
		$msg = '<div class="lm-pf-err" role="alert">' . esc_html( isset( $txt[ $r ] ) ? $txt[ $r ] : $txt['server'] ) . '</div>';
	}
	ob_start();
	?>
<form class="lm-povrat-forma lm-pf-server" id="lm-povrat-forma" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<p class="lm-povrat-ft">Zahtjev za zamjenu ili povrat</p>
<?php echo $msg; // phpcs:ignore ?>
<input type="hidden" name="action" value="lm_povrat">
<div class="lm-pf-hp" aria-hidden="true"><label>Web sajt<input name="web" tabindex="-1" autocomplete="off"></label></div>
<div class="lm-pf-red">
<label>Ime i prezime<input name="ime" required autocomplete="name" value="<?php echo esc_attr( $ime ); ?>"></label>
<label>Email<input type="email" name="email" required autocomplete="email" value="<?php echo esc_attr( $email ); ?>"></label>
</div>
<div class="lm-pf-red">
<label>Telefon <span class="lm-pf-opc">(nije obavezno)</span><input type="tel" name="telefon" autocomplete="tel" value="<?php echo esc_attr( $tel ); ?>"></label>
<label>Broj porudžbine <span class="lm-pf-opc">(ako ga imate)</span><input name="broj" inputmode="numeric" placeholder="npr. 36012"></label>
</div>
<fieldset class="lm-pf-izbor"><legend>Šta želite?</legend>
<label><input type="radio" name="vrsta" value="zamjenu" checked> Zamjenu (druga veličina ili artikal)</label>
<label><input type="radio" name="vrsta" value="povrat novca"> Povrat novca</label>
</fieldset>
<label>Artikal i razlog<textarea name="poruka" rows="3" placeholder="npr. Haljina Mamalicious, treba mi veličina M umjesto S"></textarea></label>
<label class="lm-pf-slike">Fotografije artikla <span class="lm-pf-opc">(do 3 slike, nije obavezno)</span>
<input type="file" name="slike[]" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif" multiple>
<span class="lm-pf-pom">Ako je artikal oštećen ili pogrešan, slika nam pomaže da brže riješimo zahtjev.</span></label>
<button type="submit" class="button">Pošalji zahtjev</button>
<p class="lm-pf-nap">Zahtjev stiže butiku, a kopija vama na email. Troškove slanja kod zamjene ili povrata snosi kupac.</p>
</form>
<script>
(function(){var f=document.getElementById('lm-povrat-forma');if(!f)return;var b=f.querySelector('button[type=submit]');
f.addEventListener('submit',function(e){var fi=f.querySelector('input[type=file]');if(fi&&fi.files.length>3){e.preventDefault();alert('Možete priložiti najviše 3 slike.');return;}
for(var i=0;fi&&i<fi.files.length;i++){if(fi.files[i].size>8*1024*1024){e.preventDefault();alert('Slika „'+fi.files[i].name+'“ je veća od 8 MB.');return;}}
b.disabled=true;b.textContent='Šaljemo…';});})();
</script>
	<?php
	return ob_get_clean();
} );

// 3) Obrada.
function lm_povrat_obradi() {
	$back = function ( $args ) {
		wp_safe_redirect( lm_povrat_url( $args ) );
		exit;
	};
	if ( ! empty( $_POST['web'] ) ) { // zamka za robote
		$back( array( 'zahtjev' => 'poslat' ) );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-f:.]/i', '', wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'lm_povrat_ip_' . md5( $ip );
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 5 ) {
		$back( array( 'zahtjev' => 'greska', 'r' => 'cesto' ) );
	}
	$ime     = sanitize_text_field( wp_unslash( $_POST['ime'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$tel     = sanitize_text_field( wp_unslash( $_POST['telefon'] ?? '' ) );
	$broj    = preg_replace( '/[^0-9]/', '', wp_unslash( $_POST['broj'] ?? '' ) );
	$vrsta   = 'povrat novca' === ( $_POST['vrsta'] ?? '' ) ? 'povrat novca' : 'zamjenu';
	$poruka  = sanitize_textarea_field( wp_unslash( $_POST['poruka'] ?? '' ) );
	if ( '' === $ime ) {
		$back( array( 'zahtjev' => 'greska', 'r' => 'ime' ) );
	}
	if ( ! is_email( $email ) ) {
		$back( array( 'zahtjev' => 'greska', 'r' => 'email' ) );
	}

	// Provjera slika prije bilo kakvog snimanja.
	$fajlovi = array();
	if ( ! empty( $_FILES['slike']['name'] ) && is_array( $_FILES['slike']['name'] ) ) {
		$dozv = array( 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'heic' => 'image/heic', 'heif' => 'image/heif' );
		foreach ( $_FILES['slike']['name'] as $i => $name ) {
			if ( '' === $name || UPLOAD_ERR_NO_FILE === (int) $_FILES['slike']['error'][ $i ] ) {
				continue;
			}
			$tmp  = $_FILES['slike']['tmp_name'][ $i ];
			$size = (int) $_FILES['slike']['size'][ $i ];
			$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
			if ( UPLOAD_ERR_OK !== (int) $_FILES['slike']['error'][ $i ] || $size <= 0 || $size > 8 * 1024 * 1024 || ! isset( $dozv[ $ext ] ) || ! is_uploaded_file( $tmp ) ) {
				$back( array( 'zahtjev' => 'greska', 'r' => 'slika' ) );
			}
			if ( in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp' ), true ) && false === @getimagesize( $tmp ) ) {
				$back( array( 'zahtjev' => 'greska', 'r' => 'slika' ) );
			}
			$fajlovi[] = array( 'name' => sanitize_file_name( $name ), 'type' => $dozv[ $ext ], 'tmp_name' => $tmp, 'error' => 0, 'size' => $size );
		}
		if ( count( $fajlovi ) > 3 ) {
			$back( array( 'zahtjev' => 'greska', 'r' => 'slika' ) );
		}
	}

	// Porudžbina (ako broj odgovara porudžbini sa istim emailom).
	$order = null;
	if ( $broj && function_exists( 'wc_get_order' ) ) {
		$o = wc_get_order( (int) $broj );
		if ( $o && 0 === strcasecmp( $o->get_billing_email(), $email ) ) {
			$order = $o;
		}
	}

	$linije = array(
		'Ime i prezime: ' . $ime,
		'Email: ' . $email,
		'Telefon: ' . ( $tel ? $tel : '—' ),
		'Broj porudžbine: ' . ( $broj ? '#' . $broj . ( $order ? '' : ' (nije pronađena porudžbina sa ovim emailom)' ) : '—' ),
		'Želi: ' . $vrsta,
		'',
		'Artikal i razlog:',
		$poruka ? $poruka : '—',
	);
	$naslov = 'Zahtjev za ' . $vrsta . ' – ' . $ime . ( $broj ? ' – porudžbina #' . $broj : '' );

	$pid = wp_insert_post( array(
		'post_type'    => 'lm_povrat',
		'post_status'  => 'private',
		'post_title'   => $naslov,
		'post_content' => implode( "\n", $linije ),
	), true );
	if ( is_wp_error( $pid ) ) {
		$back( array( 'zahtjev' => 'greska', 'r' => 'server' ) );
	}
	update_post_meta( $pid, '_lm_email', $email );
	update_post_meta( $pid, '_lm_vrsta', $vrsta );
	if ( $order ) {
		update_post_meta( $pid, '_lm_order', $order->get_id() );
	}

	// Slike u medije (vezane za zahtjev) i kao prilog emailu.
	$prilozi = array();
	$linkovi = array();
	if ( $fajlovi ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$mimes_filter = function ( $m ) {
			$m['heic'] = 'image/heic';
			$m['heif'] = 'image/heif';
			return $m;
		};
		add_filter( 'upload_mimes', $mimes_filter );
		foreach ( $fajlovi as $n => $f ) {
			$f['name'] = 'povrat-' . $pid . '-' . ( $n + 1 ) . '.' . strtolower( pathinfo( $f['name'], PATHINFO_EXTENSION ) );
			$att = media_handle_sideload( $f, $pid, 'Povrat #' . $pid . ' – slika ' . ( $n + 1 ), array( 'test_form' => false ) );
			if ( ! is_wp_error( $att ) ) {
				$path = get_attached_file( $att );
				if ( $path && file_exists( $path ) ) {
					$prilozi[] = $path;
				}
				$linkovi[] = wp_get_attachment_url( $att );
			}
		}
		remove_filter( 'upload_mimes', $mimes_filter );
		if ( $linkovi ) {
			wp_update_post( array( 'ID' => $pid, 'post_content' => implode( "\n", $linije ) . "\n\nSlike:\n" . implode( "\n", $linkovi ) ) );
		}
	}

	if ( $order ) {
		$order->add_order_note( 'Kupac je poslao zahtjev za ' . $vrsta . ' (zahtjev #' . $pid . ').' . ( $poruka ? ' Razlog: ' . $poruka : '' ) );
	}

	// Email butiku.
	$admin = admin_url( 'post.php?post=' . $pid . '&action=edit' );
	$tijelo = implode( "\n", $linije ) . "\n\n" . ( $linkovi ? 'Slike (' . count( $linkovi ) . ') su u prilogu.' . "\n\n" : '' ) . 'Zahtjev u administraciji: ' . $admin . "\n\nOdgovorom na ovaj email pišete direktno kupcu.";
	$podaci = array( 'ime' => $ime, 'email' => $email, 'tel' => $tel, 'broj' => $broj, 'nadjena' => (bool) $order, 'vrsta' => $vrsta, 'poruka' => $poruka, 'slike' => count( $linkovi ), 'admin' => $admin );
	$html   = lm_povrat_email_html( true, $podaci );
	wp_mail( lm_povrat_primaoci(), $naslov, $html ? $html : $tijelo, array_merge( array( 'Reply-To: ' . $ime . ' <' . $email . '>' ), $html ? array( 'Content-Type: text/html; charset=UTF-8' ) : array() ), $prilozi );

	// Potvrda kupcu.
	$kupac = "Poštovani/a " . $ime . ",\n\nprimili smo vaš zahtjev za " . $vrsta . ( $broj ? ' (porudžbina #' . $broj . ')' : '' ) . ". Javićemo vam se sa uputstvom kako i gdje da pošaljete artikal. Molimo vas da robu ne šaljete prije dogovora.\n\nVaš zahtjev:\n" . implode( "\n", array_slice( $linije, 4 ) ) . "\n\nZa pitanja: 067 030 655 (telefon, WhatsApp, Viber).\n\nLady Mom\nBranka Deletića bb, Podgorica";
	$html = lm_povrat_email_html( false, $podaci );
	wp_mail( $email, 'Primili smo vaš zahtjev za ' . $vrsta, $html ? $html : $kupac, array_merge( array( 'Reply-To: ' . implode( ', ', lm_povrat_primaoci() ) ), $html ? array( 'Content-Type: text/html; charset=UTF-8' ) : array() ) );

	set_transient( $key, $cnt + 1, HOUR_IN_SECONDS );
	$back( array( 'zahtjev' => 'poslat' ) );
}
/**
 * Emailovi o zahtjevu u Lady Mom okviru (20.10.2026; ranije običan tekst).
 * $za_butik: email butiku (dugme „Otvori zahtjev“, odgovor ide kupcu) ili potvrda kupcu.
 * Vraća '' ako okvir emailova nije učitan, pa se šalje stari tekst.
 */
function lm_povrat_email_html( $za_butik, $d ) {
	if ( ! function_exists( 'lm_em_header' ) || ! function_exists( 'lm_em_dovrsi' ) ) {
		return '';
	}
	$host  = wp_parse_url( home_url(), PHP_URL_HOST );
	$ime1  = trim( strtok( $d['ime'], ' ' ) );
	$tel   = preg_replace( '/[^0-9+]/', '', (string) $d['tel'] );
	$GLOBALS['lm_em_pre']    = $za_butik ? $d['ime'] . ' · ' . ( $d['broj'] ? 'porudžbina #' . $d['broj'] : 'bez broja porudžbine' ) : 'Javićemo vam se sa uputstvom. Robu ne šaljite prije dogovora.';
	$GLOBALS['lm_em_razlog'] = array( $za_butik ? 'Zahtjev je poslat preko forme na ' . $host . '.' : 'Ovaj email ste dobili jer ste poslali zahtjev za ' . $d['vrsta'] . ' na ' . $host . '.', '' );
	$red = function ( $l, $v ) {
		return '<tr><td valign="top" style="padding:7px 12px 7px 0;font-size:13px;line-height:1.5;color:#8a7565;white-space:nowrap">' . esc_html( $l ) . '</td><td valign="top" style="padding:7px 0;font-size:15px;line-height:1.5;color:#6d4e39">' . $v . '</td></tr>';
	};
	$t  = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">';
	if ( $za_butik ) {
		$t .= $red( 'Kupac', '<strong>' . esc_html( $d['ime'] ) . '</strong>' );
		$t .= $red( 'Telefon', $d['tel'] ? '<a href="tel:' . esc_attr( $tel ) . '" style="color:#8a4b47;font-weight:700;text-decoration:underline;white-space:nowrap">' . esc_html( $d['tel'] ) . '</a>' : '—' );
		$t .= $red( 'Email', '<a href="mailto:' . esc_attr( $d['email'] ) . '" style="color:#8a4b47;text-decoration:underline;word-break:break-all">' . esc_html( $d['email'] ) . '</a>' );
	}
	$t .= $red( 'Porudžbina', $d['broj'] ? '#' . esc_html( $d['broj'] ) . ( $za_butik && ! $d['nadjena'] ? ' <span style="color:#8a7565">(nije pronađena porudžbina sa ovim emailom)</span>' : '' ) : '—' );
	$t .= $red( 'Želi', esc_html( ucfirst( $d['vrsta'] ) ) );
	$t .= '</table>';
	$t .= '<div style="font-size:13px;line-height:1.5;color:#8a7565;margin:10px 0 4px">Artikal i razlog</div><div style="font-size:15px;line-height:1.6;color:#6d4e39">' . ( $d['poruka'] ? nl2br( esc_html( $d['poruka'] ) ) : '—' ) . '</div>';
	if ( $za_butik && $d['slike'] ) {
		$t .= '<div style="font-size:14px;line-height:1.5;color:#7a6250;margin:12px 0 0">Slike (' . (int) $d['slike'] . ') su u prilogu.</div>';
	}

	ob_start();
	lm_em_header( '', null );
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="' . lm_em_font() . 'color:#7a6250">';
	if ( $za_butik ) {
		echo lm_em_hero( 'ZA BUTIK · ZAHTJEV ZA ' . mb_strtoupper( $d['vrsta'] ), 'Zahtjev za ' . $d['vrsta'] . ': ' . $d['ime'], 'Odgovorom na ovaj email pišete direktno kupcu.' );
		echo lm_em_red( lm_em_btns( array( array( $d['admin'], 'OTVORI ZAHTJEV' ), $tel ? array( 'tel:' . $tel, 'POZOVI KUPCA', true ) : null ) ), 20 );
		echo lm_em_red( lm_em_box( lm_em_label( 'ZAHTJEV' ) . $t ), 26 );
	} else {
		echo lm_em_hero( 'ZAHTJEV ZA ' . mb_strtoupper( $d['vrsta'] ), 'Primili smo vaš zahtjev' . ( $ime1 ? ', ' . $ime1 : '' ) . '.', 'Javićemo vam se sa uputstvom kako i gdje da pošaljete artikal. Molimo vas da robu ne šaljete prije dogovora.' );
		echo lm_em_red( lm_em_box( lm_em_label( 'VAŠ ZAHTJEV' ) . $t ), 26 );
	}
	echo '</table>';
	lm_em_footer( null, array( 'kontakt' => $za_butik ? false : 'Pitanje o zahtjevu?' ) );
	$html = ob_get_clean();
	unset( $GLOBALS['lm_em_pre'], $GLOBALS['lm_em_razlog'] );
	return lm_em_dovrsi( $html );
}

add_action( 'admin_post_lm_povrat', 'lm_povrat_obradi' );
add_action( 'admin_post_nopriv_lm_povrat', 'lm_povrat_obradi' );

// 4) Stilovi za nova polja.
add_action( 'wp_head', function () {
	if ( ! is_page( 'povrat-robe-i-sredstava' ) ) {
		return;
	}
	?>
<style id="lm-povrat-server-css">
.lm-pf-hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}
.lm-pf-opc{font-weight:400;color:var(--lm-tekst);font-size:13px}
.lm-pf-slike input[type=file]{display:block;width:100%;margin-top:6px;padding:12px;border:1.5px dashed var(--lm-linija,#e8d6d0);border-radius:10px;background:var(--lm-krem,#fdf2ec);font-size:14px;color:var(--lm-tekst);cursor:pointer}
.lm-pf-slike input[type=file]::file-selector-button{margin-right:12px;padding:8px 14px;border:0;border-radius:6px;background:var(--lm-roze-jaka,#a05c57);color:#fff;font-weight:700;font-size:12px;letter-spacing:.06em;text-transform:uppercase;cursor:pointer}
.lm-pf-pom{display:block;margin-top:6px;font-size:13px;font-weight:400;color:var(--lm-tekst)}
.lm-pf-ok,.lm-pf-err{border-radius:10px;padding:12px 14px;margin:0 0 16px;font-size:15px;line-height:1.5}
.lm-pf-ok{background:#d6ede9;color:#2f4f51}
.lm-pf-err{background:#f6ded4;color:#8a4b47}
</style>
	<?php
}, 31 );
