<?php
/**
 * Lady Mom – emailovi (4/4) dodatni emailovi u novom dizajnu (18.10.2026)
 *
 * Potvrda email adrese (WooCommerce), lista želja (WoodMart: ponovo na stanju,
 * na popustu, promotivni) i napuštena korpa (WoodMart). Do sada su išli na
 * engleskom u starom šablonu. Šabloni u woodmart-child/woocommerce/emails/
 * zovu lm_em_header() / lm_em_dodatni_body() / lm_em_footer().
 */

defined( 'ABSPATH' ) || exit;

function lm_em_dodatni_ids() {
	return array( 'customer_verify_email', 'woodmart_back_in_stock_email', 'woodmart_on_sale_products_email', 'woodmart_promotional_email', 'woodmart_abandoned_cart_email' );
}

function lm_em_je_dodatni( $email ) {
	return $email && is_object( $email ) && isset( $email->id ) && in_array( $email->id, lm_em_dodatni_ids(), true );
}

/** Ime kupca iz WoodMart emaila (lista želja: $email->user, korpa: $email->user_name). */
function lm_em_dodatni_ime( $email ) {
	if ( ! empty( $email->user ) && $email->user instanceof WP_User && $email->user->first_name ) {
		return trim( $email->user->first_name );
	}
	if ( ! empty( $email->user_name ) ) {
		$dio = explode( ' ', trim( (string) $email->user_name ) );
		return $dio[0];
	}
	return '';
}

/** Naslov, tekst i preheader. Vraća prazno 's'/'h' kad admin sam upiše naslov (promotivni email). */
function lm_em_dodatni_copy( $email ) {
	$i  = lm_em_dodatni_ime( $email );
	$iz = $i ? ', ' . $i : '';
	switch ( $email->id ) {
		case 'customer_verify_email':
			return array( 's' => 'Potvrdite email adresu za Lady Mom', 'h' => 'Potvrdite email adresu.', 'pre' => 'Jedan klik i ranije porudžbine su u vašem nalogu.',
				'txt' => 'Kad potvrdite adresu, sve porudžbine koje ste ranije napravili sa njom povezujemo sa vašim nalogom.' );
		case 'woodmart_back_in_stock_email':
			return array( 's' => 'Artikal sa vaše liste želja je ponovo na stanju', 'h' => 'Ponovo je na stanju' . $iz . '.', 'pre' => 'Artikal koji ste sačuvali ponovo možete poručiti.',
				'txt' => 'Artikal koji ste sačuvali na listi želja ponovo je dostupan. Količine su ograničene, pa ga rezervišite dok ga ima.' );
		case 'woodmart_on_sale_products_email':
			return array( 's' => 'Artikal sa vaše liste želja je na popustu', 'h' => 'Na popustu je' . $iz . '.', 'pre' => 'Artikli sa vaše liste želja su sada po nižoj cijeni.',
				'txt' => 'Artikli koje ste sačuvali na listi želja sada su po nižoj cijeni. Popust traje dok traju zalihe.' );
		case 'woodmart_promotional_email':
			// Naslov koji admin upiše pri slanju ostaje; WoodMart-ov engleski podrazumijevani se mijenja.
			$s = trim( (string) $email->get_option( 'subject' ) );
			$h = trim( (string) $email->get_option( 'heading' ) );
			$s_svoj = $s && $s !== $email->get_default_subject();
			$h_svoj = $h && $h !== $email->get_default_heading();
			return array( 's' => $s_svoj ? '' : 'Posebna ponuda za vas iz Lady Mom', 'h' => $h_svoj ? '' : 'Posebna ponuda za vas' . $iz . '.', 'pre' => '', 'txt' => '' );
		case 'woodmart_abandoned_cart_email':
			return array( 's' => 'Vaša korpa vas čeka' . $iz, 'h' => 'Vaša korpa vas čeka' . $iz . '.', 'pre' => 'Sačuvali smo artikle iz korpe. Porudžbinu završavate jednim klikom.',
				'txt' => 'Ostavili ste nekoliko artikala u korpi. Sačuvali smo ih za vas, a porudžbinu možete završiti jednim klikom.' );
	}
	return array( 's' => '', 'h' => '', 'pre' => '', 'txt' => '' );
}

/** Red sa artiklom: slika, naziv, cijena i dugme. */
function lm_em_dodatni_red( $product, $desno, $dugme = '' ) {
	$img = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
	$im  = $img ? '<img src="' . esc_url( $img ) . '" width="64" height="64" alt="" style="display:block;width:64px;height:64px;border-radius:10px;border:0">' : '<div style="width:64px;height:64px;border-radius:10px;background:#fdf2ec"></div>';
	return '<tr><td width="64" valign="middle" style="padding:12px 0;border-top:1px solid #f3e6de;width:64px"><a href="' . esc_url( $product->get_permalink() ) . '">' . $im . '</a></td>'
		. '<td valign="middle" style="padding:12px 14px;border-top:1px solid #f3e6de;' . lm_em_font() . '"><a href="' . esc_url( $product->get_permalink() ) . '" style="font-size:15px;font-weight:700;color:#6d4e39;text-decoration:none">' . esc_html( $product->get_name() ) . '</a>'
		. ( $dugme ? '<div style="margin-top:6px"><a href="' . esc_url( $dugme ) . '" style="font-size:13px;font-weight:700;color:#8a4b47">Pogledaj artikal →</a></div>' : '' ) . '</td>'
		. '<td align="right" valign="middle" style="padding:12px 0;border-top:1px solid #f3e6de;white-space:nowrap;' . lm_em_font() . '">' . $desno . '</td></tr>';
}

function lm_em_dodatni_cijena( $product, $popust = false ) {
	$sad = '<div style="font-size:15px;font-weight:700;color:#6d4e39">' . wp_strip_all_tags( wc_price( wc_get_price_to_display( $product ) ) ) . '</div>';
	if ( $popust && $product->is_on_sale() && $product->get_regular_price() ) {
		$sad .= '<div style="font-size:13px;color:#8a7565;margin-top:3px;text-decoration:line-through">' . wp_strip_all_tags( wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ) ) . '</div>';
	}
	return $sad;
}

function lm_em_dodatni_lista( $naslov, $redovi ) {
	return '<tr><td style="padding:30px 40px 0;' . lm_em_font() . '"><h2 style="margin:0 0 10px;font-size:19px;font-weight:800;color:#6d4e39">' . esc_html( $naslov ) . '</h2><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">' . $redovi . '</table></td></tr>';
}

function lm_em_dodatni_body( $email, $vars = array() ) {
	$c = lm_em_dodatni_copy( $email );
	$h = $c['h'] ? $c['h'] : ( isset( $vars['email_heading'] ) ? $vars['email_heading'] : '' );
	echo '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="' . lm_em_font() . 'color:#7a6250">';
	switch ( $email->id ) {
		case 'customer_verify_email':
			echo lm_em_hero( 'VAŠ NALOG', $h, $c['txt'] );
			if ( ! empty( $vars['user_email'] ) ) {
				echo '<tr><td style="padding:18px 40px 0">' . lm_em_box( lm_em_label( 'ADRESA ZA POTVRDU' ) . '<div style="font-size:16px;font-weight:700;color:#6d4e39">' . esc_html( $vars['user_email'] ) . '</div>' ) . '</td></tr>';
			}
			echo '<tr><td style="padding:22px 40px 0">' . lm_em_btn( isset( $vars['verify_url'] ) ? $vars['verify_url'] : wc_get_page_permalink( 'myaccount' ), 'POTVRDI EMAIL ADRESU' ) . '</td></tr>';
			echo '<tr><td style="padding:22px 40px 0;' . lm_em_font() . 'font-size:14px;line-height:1.6;color:#8a7565">Ako niste vi tražili ovaj email, samo ga zanemarite — ništa se ne mijenja.</td></tr>';
			break;

		case 'woodmart_back_in_stock_email':
		case 'woodmart_on_sale_products_email':
			$popust = 'woodmart_on_sale_products_email' === $email->id;
			echo lm_em_hero( 'VAŠA LISTA ŽELJA', $h, $c['txt'] );
			$redovi = '';
			foreach ( (array) ( isset( $vars['product_lists'] ) ? $vars['product_lists'] : array() ) as $p ) {
				if ( $p instanceof WC_Product ) {
					$redovi .= lm_em_dodatni_red( $p, lm_em_dodatni_cijena( $p, $popust ), $p->get_permalink() );
				}
			}
			if ( $redovi ) {
				echo lm_em_dodatni_lista( $popust ? 'Sada po nižoj cijeni' : 'Ponovo na stanju', $redovi );
			}
			echo '<tr><td style="padding:26px 40px 0">' . lm_em_btn( function_exists( 'woodmart_get_wishlist_page_url' ) ? woodmart_get_wishlist_page_url() : home_url( '/lista-zelja/' ), 'OTVORI LISTU ŽELJA' ) . '</td></tr>';
			break;

		case 'woodmart_promotional_email':
			echo lm_em_hero( 'LADY MOM', $h, '' );
			$sadrzaj = isset( $vars['email_content'] ) ? $vars['email_content'] : '';
			echo '<tr><td style="padding:14px 40px 0;' . lm_em_font() . 'font-size:16px;line-height:1.6;color:#7a6250">' . wp_kses_post( $sadrzaj ) . '</td></tr>';
			break;

		case 'woodmart_abandoned_cart_email':
			echo lm_em_hero( 'VAŠA KORPA', $h, $c['txt'] );
			$obj    = isset( $email->object ) ? $email->object : null;
			$redovi = '';
			if ( $obj && ! empty( $obj->_cart ) && is_object( $obj->_cart ) ) {
				$valuta = isset( $obj->_user_currency ) ? $obj->_user_currency : '';
				foreach ( $obj->_cart->get_cart_contents() as $stavka ) {
					$p = isset( $stavka['data'] ) ? $stavka['data'] : null;
					if ( ! $p instanceof WC_Product ) {
						continue;
					}
					$iznos   = isset( $stavka['line_subtotal'] ) ? (float) $stavka['line_subtotal'] : 0;
					if ( wc_tax_enabled() ) {
						$iznos = 'incl' === get_option( 'woocommerce_tax_display_cart' ) ? wc_get_price_including_tax( $p, array( 'qty' => $stavka['quantity'] ) ) : wc_get_price_excluding_tax( $p, array( 'qty' => $stavka['quantity'] ) );
					}
					$desno   = '<div style="font-size:15px;font-weight:700;color:#6d4e39">' . wp_strip_all_tags( wc_price( $iznos, $valuta ? array( 'currency' => $valuta ) : array() ) ) . '</div><div style="font-size:13px;color:#8a7565;margin-top:3px">' . (int) $stavka['quantity'] . ' kom.</div>';
					$redovi .= lm_em_dodatni_red( $p, $desno );
				}
			}
			if ( $redovi ) {
				echo lm_em_dodatni_lista( 'U vašoj korpi', $redovi );
			}
			$kupon = isset( $vars['coupon'] ) ? $vars['coupon'] : null;
			if ( $kupon && is_object( $kupon ) ) {
				$vr = 'percent' === $kupon->get_discount_type() ? $kupon->get_amount() . '%' : wp_strip_all_tags( wc_price( $kupon->get_amount() ) );
				echo '<tr><td style="padding:22px 40px 0">' . lm_em_box( lm_em_label( 'POPUST ZA VAS' ) . '<div style="font-size:16px;color:#6d4e39">' . esc_html( $vr ) . ' popusta uz kod <strong>' . esc_html( strtoupper( $kupon->get_code() ) ) . '</strong></div>', '#f6ded4' ) . '</td></tr>';
			}
			$link = isset( $vars['recover_button_link'] ) ? $vars['recover_button_link'] : wc_get_cart_url();
			echo '<tr><td style="padding:26px 40px 0">' . lm_em_btn( $link, 'ZAVRŠI PORUDŽBINU' ) . '</td></tr>';
			echo '<tr><td style="padding:22px 40px 0;' . lm_em_font() . 'font-size:14px;line-height:1.6;color:#8a7565">Imate pitanje o veličini ili artiklu? Pišite nam na Viber ili WhatsApp: <a href="https://wa.me/38267030655" style="color:#8a4b47;font-weight:700">067 030 655</a>.</td></tr>';
			break;
	}
	echo '</table>';
}

/** Razlog i odjava u footeru za emailove liste želja i korpe. */
function lm_em_dodatni_razlog( $email, $vars = array() ) {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	switch ( $email->id ) {
		case 'customer_verify_email':
			return array( 'Ovaj email ste dobili jer je ova adresa upisana na ' . $host . '.', '' );
		case 'woodmart_abandoned_cart_email':
			return array( 'Ovaj email ste dobili jer ste započeli porudžbinu na ' . $host . '.', isset( $vars['unsubscribe_link'] ) ? $vars['unsubscribe_link'] : '' );
		default:
			$odjava = ( ! empty( $email->user ) && function_exists( 'woodmart_get_unsubscribe_link' ) ) ? woodmart_get_unsubscribe_link( $email->user->ID ) : '';
			return array( 'Ovaj email ste dobili jer ste uključili obavještenja za listu želja na ' . $host . '.', $odjava );
	}
}

/** Naslov i tema emaila. */
foreach ( lm_em_dodatni_ids() as $lm_em_did ) {
	add_filter( 'woocommerce_email_subject_' . $lm_em_did, function ( $s, $obj = null, $email = null ) {
		if ( $email && lm_em_je_dodatni( $email ) ) {
			$c = lm_em_dodatni_copy( $email );
			if ( $c['s'] ) {
				return $c['s'];
			}
		}
		return $s;
	}, 20, 3 );
	add_filter( 'woocommerce_email_heading_' . $lm_em_did, function ( $h, $obj = null, $email = null ) {
		if ( $email && lm_em_je_dodatni( $email ) ) {
			$c = lm_em_dodatni_copy( $email );
			if ( $c['h'] ) {
				return $c['h'];
			}
		}
		return $h;
	}, 20, 3 );
}
unset( $lm_em_did );

add_filter( 'woocommerce_email_styles', function ( $css, $email = null ) {
	if ( $email && lm_em_je_dodatni( $email ) ) {
		return 'img{border:0;outline:none;text-decoration:none}a{color:#a05c57}';
	}
	return $css;
}, 99, 2 );

/** Šablon: zajednički za sve dodatne emailove. Vraća false ako ne može (pada na original). */
function lm_em_dodatni_sablon( $email, $vars ) {
	if ( ! lm_em_je_dodatni( $email ) || ! function_exists( 'lm_em_header' ) ) {
		return false;
	}
	$GLOBALS['lm_em_razlog'] = lm_em_dodatni_razlog( $email, $vars );
	lm_em_header( isset( $vars['email_heading'] ) ? $vars['email_heading'] : '', $email );
	lm_em_dodatni_body( $email, $vars );
	lm_em_footer( $email );
	unset( $GLOBALS['lm_em_razlog'] );
	return true;
}
