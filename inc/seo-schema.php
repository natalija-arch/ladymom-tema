<?php
/**
 * Lady Mom – SEO: lokalni biznis u schemi
 * (Ranije Code Snippets #10, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

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
	// Slika butika (24.10.2026, c9): fotografija unutrašnjosti sa Google profila (objavio vlasnik), medij #51612.
	// Ranije je ovdje bila fotografija sa slajdera (2026/09/lady-mom-butik-podgorica.jpg).
	$data['image']      = array(
		home_url( '/wp-content/uploads/2026/10/lady-mom-butik-podgorica-unutra.jpg' ),
		home_url( '/wp-content/uploads/2026/09/lady-mom-butik-podgorica.jpg' ),
	);
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
	// Lokacija butika na mapi (22.10.2026): koordinate iz Google profila istog mjesta (place_id iz hasMap).
	$data['geo']        = array(
		'@type'     => 'GeoCoordinates',
		'latitude'  => 42.440672,
		'longitude' => 19.2304,
	);
	return $data;
} );
