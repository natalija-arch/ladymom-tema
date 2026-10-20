<?php
/**
 * Lady Mom – preusmjerenja starih adresa
 * (Ranije Code Snippets #21, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lady Mom – preusmjerenja sa starih (engleskih) adresa na nove.
 * Stare adrese ostaju u Google indeksu i u linkovima koje su ljudi sačuvali,
 * pa moraju trajno da vode na nove. 301 = trajno preusmjerenje.
 */
add_action( 'template_redirect', function () {
	if ( is_admin() || wp_doing_ajax() ) { return; }

	$put = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
	if ( ! $put ) { return; }
	$put = '/' . trim( $put, '/' ) . '/';
	$upit = isset( $_SERVER['QUERY_STRING'] ) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

	// 1) Stranice sa fiksnim adresama.
	$fiksno = array(
		'/shop/'                   => '/prodavnica/',
		'/cart/'                   => '/korpa/',
		'/checkout/'               => '/placanje/',
		'/wishlist/'               => '/lista-zelja/',
		'/compare/'                => '/poredjenje/',
		'/privacy-policy/'         => '/politika-privatnosti/',
		'/povratrobe-i-sredstava/' => '/povrat-robe-i-sredstava/',
		'/home-lingerie/'          => '/',
		'/about-us-3/'             => '/o-nama/',
		'/about-us/'               => '/o-nama/',
		'/contact-us/'             => '/kontakt/',
		// Stranice sa starog ladymom.me (do 18.10.2026).
		'/shop-2/'                 => '/prodavnica/',
		'/checkout-2/'             => '/placanje/',
		'/home/'                   => '/',
		'/thank-you-page/'         => '/',
		'/isporuka/'               => '/dostava/',
		'/cesta-pitanja/'          => '/dostava/',
		'/zamjena-artikla/'        => '/povrat-robe-i-sredstava/',
		'/zasto-kupovati-kod-nas/' => '/o-nama/',
		'/about/'                  => '/o-nama/',
		'/kontaktirajte-nas/'      => '/kontakt/',
		'/contact/'                => '/kontakt/',
		'/customer-support/'       => '/kontakt/',
		'/legal-privacy/'          => '/politika-privatnosti/',
		// Duplikat kategorije bloga spojen 18.10.2026.
		'/category/edukacija-bs/'  => '/category/edukacija/',
		// Stare adrese iz Search Console koje su vraćale 404 (21.10.2026).
		'/featured_item/'          => '/',
		'/elementor-46675/'        => '/',
	);
	if ( isset( $fiksno[ $put ] ) ) {
		wp_safe_redirect( home_url( $fiksno[ $put ] ) . $upit, 301 );
		exit;
	}

	// 2) Adrese sa nastavkom: baza se mijenja, ostatak ostaje.
	$baze = array(
		'/my-account/'       => '/moj-nalog/',
		'/product/'          => '/proizvod/',
		'/product-category/' => '/kategorija/',
		'/product-tag/'      => '/oznaka/',
		// Baze sa starog ladymom.me.
		'/kategorija-proizvoda/' => '/kategorija/',
		'/proizvod-tag/'         => '/oznaka/',
		'/brand/'                => '/marka/',
		// Greška u adresi sa starog sajta (kategorija-proizvode).
		'/kategorija-proizvode/' => '/kategorija/',
	);
	// 3) Stranice prodavnice sa starih adresa (/shop/page/2/, /shop-2/page/3/).
	if ( preg_match( '#^/shop(?:-2)?/page/(\d+)/$#', $put, $m ) ) {
		wp_safe_redirect( home_url( '/prodavnica/page/' . (int) $m[1] . '/' ) . $upit, 301 );
		exit;
	}
	// 4) Stare stranice boja (/boja/siva/) više ne postoje; vode na prodavnicu.
	if ( 0 === strpos( $put, '/boja/' ) && is_404() ) {
		wp_safe_redirect( home_url( '/prodavnica/' ), 301 );
		exit;
	}

	foreach ( $baze as $stara => $nova ) {
		if ( 0 === strpos( $put, $stara ) ) {
			$ostatak = substr( $put, strlen( $stara ) );
			wp_safe_redirect( home_url( $nova . $ostatak ) . $upit, 301 );
			exit;
		}
	}
}, 1 );

/**
 * Porudžbine sa starog sajta su prenesene pod novim brojevima (stari broj je u
 * meta polju `_lm_stari_broj`). Link „Pogledaj porudžbinu“ iz starih emailova
 * (/moj-nalog/view-order/STARI/) vodi na istu porudžbinu pod novim brojem.
 */
add_action( 'template_redirect', function () {
	if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'view-order' ) ) {
		return;
	}
	$broj = absint( get_query_var( 'view-order' ) );
	if ( ! $broj || $broj > 48099 ) {
		return;
	}
	$postoji = wc_get_order( $broj );
	if ( $postoji && 'trash' !== $postoji->get_status() ) {
		return;
	}
	$nadjene = wc_get_orders( array(
		'limit'      => 1,
		'return'     => 'ids',
		'meta_key'   => '_lm_stari_broj', // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_value' => $broj, // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( $nadjene ) {
		wp_safe_redirect( wc_get_endpoint_url( 'view-order', $nadjene[0], wc_get_page_permalink( 'myaccount' ) ), 301 );
		exit;
	}
}, 5 );
