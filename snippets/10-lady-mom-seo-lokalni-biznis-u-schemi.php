<?php
/**
 * Snippet #10: Lady Mom – SEO: lokalni biznis u schemi
 * Aktivan: da | scope: global | prioritet: 10
 * (Izvezeno iz dodatka Code Snippets – izvor je baza sajta.)
 */

// Lady Mom – SEO: lokalni biznis u schemi (Yoast Local SEO je plaćen).
// Naslovi, meta opisi, sitemap, noindex i slika za dijeljenje su u Yoast podešavanjima (14.10.2026).

// 6) Lokalni biznis (butik) u strukturiranim podacima.
add_filter( 'wpseo_schema_organization', function ( $data ) {
	$data['@type']      = array( 'Organization', 'ClothingStore' );
	$data['name']       = 'Lady Mom';
	$data['description'] = 'Butik za trudnice i mame u Podgorici – trudnička garderoba, oprema za dojenje, jastuci, kozmetika i oprema za bebe.';
	$data['telephone']  = '+38267030655';
	$data['email']      = 'natalija@ladymom.me';
	$data['priceRange'] = '€€';
	$data['currenciesAccepted'] = 'EUR';
	$data['paymentAccepted']    = 'Gotovina, Visa, Mastercard';
	$data['image']      = home_url( '/wp-content/uploads/2026/09/lady-mom-butik-podgorica.jpg' );
	$data['address']    = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'Branka Deletića bb',
		'addressLocality' => 'Podgorica',
		'postalCode'      => '81000',
		'addressCountry'  => 'ME',
	);
	$data['openingHoursSpecification'] = array(
		array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
			'opens'     => '09:30',
			'closes'    => '21:00',
		),
	);
	$data['areaServed'] = array( '@type' => 'Country', 'name' => 'Crna Gora' );
	$data['sameAs']     = array( 'https://www.facebook.com/ladymom.me', 'https://www.instagram.com/ladymom.me' );
	$data['hasMap']     = 'https://www.google.com/maps/search/?api=1&query=Lady+Mom+prodavnica+za+trudnice+i+mame&query_place_id=ChIJU0tXHcnrTRMRuXXUSvfb4b8';
	return $data;
} );
