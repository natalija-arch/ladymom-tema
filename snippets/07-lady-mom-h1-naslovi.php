<?php
/**
 * Snippet #7: Lady Mom – H1 naslovi
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Nevidljiv H1 naslov (za Google i citace ekrana) na stranicama koje nemaju vidljiv H1.
add_action( 'wp_body_open', function () {
	$h1 = '';
	if ( is_front_page() ) {
		$h1 = 'Lady Mom – butik za trudnice i mame u Podgorici';
	} elseif ( is_page( 'kontakt' ) ) {
		$h1 = 'Kontakt – Lady Mom butik, Podgorica';
	}
	if ( $h1 ) {
		echo '<h1 class="screen-reader-text lm-sr-h1">' . esc_html( $h1 ) . '</h1>';
	}
} );
