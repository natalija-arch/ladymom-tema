<?php
/**
 * Snippet #21: Lady Mom – preusmjerenja starih adresa
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

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
	);
	foreach ( $baze as $stara => $nova ) {
		if ( 0 === strpos( $put, $stara ) ) {
			$ostatak = substr( $put, strlen( $stara ) );
			wp_safe_redirect( home_url( $nova . $ostatak ) . $upit, 301 );
			exit;
		}
	}
}, 1 );
