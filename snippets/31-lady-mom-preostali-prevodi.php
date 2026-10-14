<?php
/**
 * Snippet #31: Lady Mom – preostali prevodi
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Lady Mom – preostali prevodi (12.10.2026). Tekstovi koje snippet 5 i prevodi teme nisu pokrili.

function lm_prevodi2() {
	return array(
		// blog
		'Newer'                                => 'Novije',
		'Older'                                => 'Starije',
		'Newer posts'                          => 'Noviji članci',
		'Older posts'                          => 'Stariji članci',
		'Post thumbnail'                       => 'Slika članka',
		'Archive by Category "%s"'             => 'Kategorija: %s',
		'Archive by category "%s"'             => 'Kategorija: %s',
		'Archive by Tag "%s"'                  => 'Oznaka: %s',
		'Posts by %s'                          => 'Članci autora %s',
		'Search Results for: %s'               => 'Rezultati pretrage za: %s',
		'Search Results for: '                 => 'Rezultati pretrage za: ',
		'Search results for: '                 => 'Rezultati pretrage za: ',
		'Search Results for:'                  => 'Rezultati pretrage za:',
		// lista želja
		'My wishlist'                          => 'Moja lista želja',
		'Add new wishlist'                     => 'Napravi novu listu',
		'Product was successfully added to your wishlist.' => 'Proizvod je dodat u listu želja.',
		'Back to shop'                         => 'Nazad u prodavnicu',
		// prodavnica i korpa
		'Popularity'                           => 'Najpopularnije',
		'Average rating'                       => 'Najbolje ocijenjeno',
		'Price: low to high'                   => 'Cijena: od najniže',
		'Price: high to low'                   => 'Cijena: od najviše',
		'Decrease quantity'                    => 'Smanji količinu',
		'Increase quantity'                    => 'Povećaj količinu',
		'Sticky add to cart'                   => 'Dodaj u korpu',
		'Remove this item'                     => 'Ukloni ovaj proizvod',
		// forme i slajder
		'Contact form'                         => 'Forma',
		'Go to slide {{index}}'                => 'Idi na slajd {{index}}',
		'days'                                 => 'dana',
		'min'                                  => 'min',
		'Some files are too large. Allowed file size is 1 MB.' => 'Neke slike su prevelike. Najveća dozvoljena veličina je 1 MB.',
		'You can upload up to 3 images to your review.'        => 'Uz recenziju možete priložiti do 3 slike.',
		'You are allowed to upload images only in png, jpeg formats.' => 'Možete priložiti samo slike u PNG ili JPEG formatu.',
	);
}

add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $t;
	}
	static $map = null;
	if ( null === $map ) {
		$map = lm_prevodi2();
	}
	// „days“ i „min“ samo iz teme (odbrojavanje), da se ne mijenja ništa drugo.
	if ( ( 'days' === $text || 'min' === $text ) && 'woodmart' !== $domain ) {
		return $t;
	}
	return isset( $map[ $text ] ) ? $map[ $text ] : $t;
}, 40, 3 );

add_filter( 'gettext_with_context', function ( $t, $text, $context, $domain ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $t;
	}
	$map = lm_prevodi2();
	if ( isset( $map[ $text ] ) && 'days' !== $text && 'min' !== $text ) {
		return $map[ $text ];
	}
	return $t;
}, 40, 4 );

// Avatar autora na blogu: bez engleskog alt teksta.
add_filter( 'get_avatar', function ( $html ) {
	return str_replace( array( "alt='author-avatar'", 'alt="author-avatar"' ), 'alt=""', $html );
} );
