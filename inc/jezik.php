<?php
/**
 * Lady Mom – crnogorski jezik
 * (Ranije Code Snippets #5, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lady Mom – crnogorski jezik na sajtu.
 * Osnova je hrvatski prevod (WordPress, WooCommerce, Yoast, CF7). Ovaj kod:
 * 1) prevodi engleske tekstove teme WoodMart koji nemaju prevod,
 * 2) mijenja hrvatske riječi crnogorskim (košarica → korpa, narudžba → porudžbina, rujna → septembra…),
 * 3) postavlja oznaku jezika stranice na crnogorski (sr-Latn-ME).
 * Nove izraze dodajte u lm_cg_prevodi() ili lm_cg_rijeci().
 */

if ( ! function_exists( 'lm_cg_prevodi' ) ) {

	function lm_cg_prevodi() {
		return array(
			// Emailovi prodavnici (WooCommerce 11 – novi tekstovi, nema ih u hrvatskom prevodu).
			'You’ve received the following order from %s:' => 'Stigla je porudžbina od kupca %s:',
			'You’ve received a new order from %s:' => 'Stigla je nova porudžbina od kupca %s:',
			'Notification to let you know &mdash; order #%1$s belonging to %2$s has been cancelled:' => 'Obavještenje: porudžbina #%1$s, kupac %2$s, je otkazana:',
			'We’re getting in touch to let you know that order #%1$s from %2$s has been cancelled.' => 'Porudžbina #%1$s, kupac %2$s, je otkazana.',
			'Payment for order #%1$s from %2$s has failed. The order was as follows:' => 'Plaćanje za porudžbinu #%1$s, kupac %2$s, nije prošlo. Porudžbina:',
			'Unfortunately, the payment for order #%1$s from %2$s has failed. The order was as follows:' => 'Plaćanje za porudžbinu #%1$s, kupac %2$s, nije prošlo. Porudžbina:',
			'Order summary' => 'Pregled porudžbine',
			'[Order #%s]' => '[Porudžbina #%s]',
			'Order #%s' => 'Porudžbina #%s',
			'Product' => 'Proizvod',
			'Quantity' => 'Količina',
			'Price' => 'Cijena',
			'Free!' => 'Besplatno!',
			'Note:' => 'Napomena:',
			'Customer note' => 'Napomena kupca',
			'Billing address' => 'Adresa za naplatu',
			'Shipping address' => 'Adresa za dostavu',
			'Customer details' => 'Podaci o kupcu',
			'Shipping:' => 'Dostava:',
			'Total:' => 'Ukupno:',
			'Payment method:' => 'Način plaćanja:',
			'Discount:' => 'Popust:',
			'Refund:' => 'Povrat:',
			'Congratulations on the sale.' => 'Čestitamo na prodaji.',
			'Thanks for reading.' => 'Hvala.',
			'Skip to navigation'   => 'Preskoči na navigaciju',
			'Skip to main content' => 'Preskoči na glavni sadržaj',
			'Home'                 => 'Početna',
			'My Account'           => 'Moj nalog',
			'My account'           => 'Moj nalog',
			'Login / Register'     => 'Prijava / Registracija',
			'Login'                => 'Prijava',
			'Register'             => 'Registracija',
			'Sign in'              => 'Prijava',
			'Create an Account'    => 'Napravi nalog',
			'Create an account'    => 'Napravi nalog',
			'No account yet?'      => 'Nemate nalog?',
			'Wishlist'             => 'Lista želja',
			'My Wishlist'          => 'Moja lista želja',
			'My wishlist'          => 'Moja lista želja',
			'Wishlist products'    => 'Proizvodi na listi želja',
			'Your products wishlist' => 'Vaša lista želja',
			'Add to wishlist'      => 'Dodaj u listu želja',
			'Browse wishlist'      => 'Pogledaj listu želja',
			'Remove from wishlist' => 'Ukloni sa liste želja',
			'Toggle wishlist'      => 'Lista želja',
			'This wishlist is empty.' => 'Lista želja je prazna.',
			'Wishlist is empty.'   => 'Lista želja je prazna.',
			'Menu'                 => 'Meni',
			'Open mobile menu'     => 'Otvori meni',
			'Categories'           => 'Kategorije',
			'Categories:'          => 'Kategorije:',
			'Tags:'                => 'Oznake:',
			'New'                  => 'Novo',
			'Hot'                  => 'Popularno',
			'Sale'                 => 'Akcija',
			'Sold out'             => 'Rasprodato',
			'Out of stock'         => 'Nema na stanju',
			'In stock'             => 'Na stanju',
			'On sale'              => 'Na akciji',
			'Compare'              => 'Uporedi',
			'Toggle compare'       => 'Uporedi',
			'Compare list is empty.' => 'Lista za poređenje je prazna.',
			'Select all'           => 'Izaberi sve',
			'Deselect all'         => 'Poništi izbor',
			'Quick view'           => 'Brzi pregled',
			'Continue reading'     => 'Pročitaj više',
			'Read more'            => 'Pročitaj više',
			'Search'               => 'Pretraga',
			'Search for products'  => 'Šta tražite? Proizvod, brend ili savjet',
			'Search for posts'     => 'Pretražite blog',
			'Search results for: %s' => 'Rezultati pretrage za: %s',
			'Start typing to see products you are looking for.' => 'Počnite da kucate i prikazaćemo proizvode koje tražite.',
			'Close search form'    => 'Zatvori pretragu',
			'Clear search'         => 'Obriši pretragu',
			'Shopping cart'        => 'Korpa',
			'Shopping cart sidebar' => 'Korpa',
			'Cart'                 => 'Korpa',
			'My cart'              => 'Moja korpa',
			'View cart'            => 'Pogledaj korpu',
			'Subtotal'             => 'Međuzbir',
			'Subtotal:'            => 'Međuzbir:',
			'Checkout'             => 'Plaćanje',
			'Order complete'       => 'Porudžbina završena',
			'Before proceed to checkout you must add some products to your shopping cart.' => 'Da biste nastavili na plaćanje, dodajte proizvode u korpu.',
			'You will find a lot of interesting products on our "Shop" page.' => 'Mnogo lijepih proizvoda čeka vas u našoj prodavnici.',
			'Return to shop'       => 'Nazad u prodavnicu',
			'Continue shopping'    => 'Nastavi kupovinu',
			'Close'                => 'Zatvori',
			'Shop'                 => 'Prodavnica',
			'Products'             => 'Proizvodi',
			'More info'            => 'Više informacija',
			'Show sidebar'         => 'Prikaži filtere',
			'Show'                 => 'Prikaži',
			'Filters'              => 'Filteri',
			'Filter by %s'         => 'Filtriraj: %s',
			'Grid view %s'         => 'Prikaz: %s u redu',
			'Clear filters'        => 'Obriši filtere',
			'Clear all'            => 'Obriši sve',
			'Apply'                => 'Primijeni',
			'Load more products'   => 'Učitaj još proizvoda',
			'Load more posts'      => 'Učitaj još tekstova',
			'Load more'            => 'Učitaj još',
			'Size guide'           => 'Vodič za veličine',
			'Share:'               => 'Podijeli:',
			'Share'                => 'Podijeli',
			'Posted by'            => 'Autor',
			'On'                   => 'Objavljeno',
			'Back to list'         => 'Nazad na listu',
			'Related Posts'        => 'Povezani tekstovi',
			'Related posts'        => 'Povezani tekstovi',
			'Next'                 => 'Sljedeće',
			'Previous'             => 'Prethodno',
			'Scroll to top button' => 'Na vrh',
			'Not Found'            => 'Stranica nije pronađena',
			'It looks like nothing was found at this location. Maybe try a search?' => 'Na ovoj adresi nema ničega. Pokušajte sa pretragom.',
			'%d Comment'           => '%d komentar',
			'%d Comments'          => '%d komentara',
			'1 Comment'            => '1 komentar',
			'Share: '              => 'Podijeli: ',
			'Sidebar'              => 'Bočna traka',
			'Recent Posts'         => 'Najnoviji tekstovi',
			'Recent Comments'      => 'Najnoviji komentari',
			'Filter by price'      => 'Cijena',
			'Filter by color'      => 'Boja',
			'Filter by size'       => 'Veličina',
			'Filter by Brand'      => 'Brend',
			'Stock status'         => 'Dostupnost',
			'Top rated products'   => 'Najbolje ocijenjeno',
			'Click to enlarge'     => 'Klikni za uvećanje',
			'Back to products'     => 'Nazad na proizvode',
			'SKU:'                 => 'Šifra:',
			'Brand:'               => 'Brend:',
			'Previous product'     => 'Prethodni proizvod',
			'Next product'         => 'Sljedeći proizvod',
			'Sort by'              => 'Sortiraj',
			'Price filter'         => 'Cijena',
			'Logout'               => 'Odjava',
			'Log out'              => 'Odjava',
			'Password'             => 'Lozinka',
			'Log in'               => 'Prijavi se',
			'Lost your password?'  => 'Zaboravili ste lozinku?',
			'Remember me'          => 'Zapamti me',
			'Login sidebar'        => 'Prijava',
			'Or'                   => 'ili',
			'Email'                => 'Email',
			'Username or email address' => 'Korisničko ime ili email adresa',
			'Username or email'    => 'Korisničko ime ili email',
			'Email address'        => 'Email adresa',
			'I would like to receive marketing emails and updates.' => 'Želim da primam novosti i ponude emailom.',
			'Accept'               => 'Prihvatam',
			'Items'                => 'Proizvodi',
			'items'                => 'proizvoda',
			'item'                 => 'proizvod',
			'Yes'                  => 'Da',
			'No'                   => 'Ne',
			'Brand'                => 'Brend',
			'Brands:'              => 'Brendovi:',
			'Email:'               => 'Email:',
			'Header banner link'   => 'Ponuda',
			'Banner link'          => 'Ponuda',
			'Facebook social link' => 'Facebook',
			'Instagram social link' => 'Instagram',
			'X social link'        => 'X',
			'Pinterest social link' => 'Pinterest',
			'Linkedin social link' => 'LinkedIn',
			'Telegram social link' => 'Telegram',
			'Return To Shop'       => 'Nazad u prodavnicu',
			'Product was successfully added to your cart.' => 'Proizvod je dodat u korpu.',
			'View Cart'            => 'Pogledaj korpu',
			'Processing'           => 'Obrada',
			'Loading...'           => 'Učitavanje...',
			'View all results'     => 'Prikaži sve rezultate',
			'Share on Facebook'    => 'Podijeli na Facebooku',
			'Pin it'               => 'Sačuvaj na Pinterestu',
			'Share on X'           => 'Podijeli na X',
			'Download image'       => 'Preuzmi sliku',
			'Some files are too large. Allowed file size is 1 MB.' => 'Neke datoteke su prevelike. Dozvoljena veličina je 1 MB.',
			'Image is required.'   => 'Slika je obavezna.',
			'You are allowed to upload images only in png, jpeg formats.' => 'Možete dodati samo slike u png ili jpeg formatu.',
			'Added %s image(s)'    => 'Dodato slika: %s',
			'No matches found'     => 'Nema rezultata',
			'Add to cart'          => 'Dodaj u korpu',
			'Back to %s'           => '%s', // mobilni meni: strelica nazad + naziv nivoa (npr. „‹ Za mame“)
			'Back to menu'         => 'Nazad na meni',
			'Back to categories'   => 'Nazad na kategorije',
			'Search history'       => 'Istorija pretrage',
			'Popular requests'     => 'Najčešće pretrage',
			'No products found'    => 'Nema pronađenih proizvoda',
			'Blog posts'           => 'Savjeti sa bloga',
			'Clear'                => 'Obriši',
			'Previous slide'       => 'Prethodni slajd',
			'Next slide'           => 'Sljedeći slajd',
			'This is the first slide' => 'Ovo je prvi slajd',
			'This is the last slide' => 'Ovo je posljednji slajd',
			'On this page:'        => 'Na ovoj stranici:',
			'Do you really want to remove these products?' => 'Da li sigurno želite da uklonite ove proizvode?',
			'Please log in to rate reviews.' => 'Prijavite se da biste ocijenili recenzije.',
			'Compare products'     => 'Uporedi proizvode',
			'Remove from compare'  => 'Ukloni iz poređenja',
			'Add to compare'       => 'Dodaj u poređenje',
			'About brand'          => 'O brendu',
			'Shipping and Delivery' => 'Dostava i povrat',
			'Description'          => 'Opis',
			'Additional information' => 'Dodatne informacije',
			'Reviews'              => 'Recenzije',
			'Reviews (%d)'         => 'Recenzije (%d)',
			'Related products'     => 'Slični proizvodi',
			'You may also like&hellip;' => 'Možda će vam se svidjeti&hellip;',
			'Add to wishlist '     => 'Dodaj u listu želja',
			'Select options'       => 'Izaberi opcije',
			'Read more about &ldquo;%s&rdquo;' => 'Više o proizvodu &ldquo;%s&rdquo;',
			'Default sorting'      => 'Podrazumijevano sortiranje',
			'Sort by popularity'   => 'Po popularnosti',
			'Sort by average rating' => 'Po ocjeni',
			'Sort by latest'       => 'Najnovije',
			'Newness'              => 'Najnovije',
			'Sort by price: low to high' => 'Cijena: od najniže',
			'Sort by price: high to low' => 'Cijena: od najviše',
			'Show more'            => 'Prikaži više',
			'Show less'            => 'Prikaži manje',
			'Newsletter'           => 'Novosti',
			'Your email address'   => 'Vaša email adresa',
			'Sign up'              => 'Prijavi se',
			'Subscribe'            => 'Prijavi se',
			'Ask a question'       => 'Postavite pitanje',
			'Estimated delivery:'  => 'Očekivana dostava:',
			'Notify me when available' => 'Obavijesti me kad bude dostupno',
			'Copy link'            => 'Kopiraj link',
			'Copied!'              => 'Kopirano!',
			'Free shipping'        => 'Besplatna dostava',
			'%s Comment'           => '%s komentar',
			'%s Comments'          => '%s komentara',
			// Ostaci sa engleskog (pregled sajta 18.10.2026).
			'Back'                 => 'Nazad',
			'Go back'              => 'Nazad',
			'All'                  => 'Sve',
			'Show all blog results' => 'Prikaži sve rezultate sa bloga',
			'You can upload up to %s images to your review.' => 'Uz recenziju možete dodati do %s slike.',
			'You are allowed to upload images only in %s formats.' => 'Dozvoljene su samo slike u formatu %s.',
			'Current default group' => 'Trenutna glavna lista',
			'Default group'        => 'Glavna lista',
			'Title is empty!'      => 'Naziv je prazan!',
		);
	}

	function lm_cg_rijeci() {
		return array(
			// kod (hrvatski kôd)
			'Kôd' => 'Kod', 'kôd' => 'kod', 'Kôda' => 'Koda', 'kôda' => 'koda', 'kôdom' => 'kodom', 'kôdu' => 'kodu',
			// korpa
			'košarica' => 'korpa', 'košarice' => 'korpe', 'košarici' => 'korpi', 'košaricu' => 'korpu', 'košaricom' => 'korpom', 'košaricama' => 'korpama',
			// porudžbina
			'narudžba' => 'porudžbina', 'narudžbe' => 'porudžbine', 'narudžbi' => 'porudžbini', 'narudžbu' => 'porudžbinu', 'narudžbom' => 'porudžbinom', 'narudžbama' => 'porudžbinama',
			'naručiti' => 'poručiti', 'naruči' => 'poruči', 'naručite' => 'poručite', 'naručeno' => 'poručeno', 'naručili' => 'poručili',
			// plaćanje
			'blagajna' => 'plaćanje', 'blagajnu' => 'plaćanje', 'blagajni' => 'plaćanju', 'blagajne' => 'plaćanja',
			// kupovina
			'kupnja' => 'kupovina', 'kupnju' => 'kupovinu', 'kupnje' => 'kupovine', 'kupnji' => 'kupovini', 'kupnjom' => 'kupovinom',
			// ostalo
			'odaberi' => 'izaberi', 'odaberite' => 'izaberite', 'odabir' => 'izbor', 'odabrani' => 'izabrani', 'odabrane' => 'izabrane', 'odabrano' => 'izabrano',
			'spremi' => 'sačuvaj', 'spremite' => 'sačuvajte', 'spremljeno' => 'sačuvano', 'spremljene' => 'sačuvane', 'spremljena' => 'sačuvana',
			'zalihi' => 'stanju', 'zalihe' => 'stanja', 'zaliha' => 'stanje',
			'neobvezno' => 'opciono', 'obvezno' => 'obavezno', 'obvezna' => 'obavezna', 'obvezan' => 'obavezan', 'obvezni' => 'obavezni', 'obvezne' => 'obavezne',
			'tvrtka' => 'firma', 'tvrtke' => 'firme', 'tvrtku' => 'firmu', 'tvrtki' => 'firmi',
			'općina' => 'opština', 'općine' => 'opštine',
			'međuzbroj' => 'međuzbir', 'zbroj' => 'zbir',
			'e-pošta' => 'email', 'e-pošte' => 'emaila', 'e-poštu' => 'email', 'e-poštom' => 'emailom', 'e-pošti' => 'emailu',
			'uvjeti' => 'uslovi', 'uvjete' => 'uslove', 'uvjetima' => 'uslovima', 'uvjeta' => 'uslova',
			'prihvaćam' => 'prihvatam', 'zaprimljena' => 'primljena', 'zaprimili' => 'primili',
			'tjedan' => 'nedjelja', 'tjedna' => 'nedjelje', 'tjedno' => 'nedjeljno', 'tisuća' => 'hiljada',
			'upravljačka ploča' => 'kontrolna tabla', 'moj račun' => 'moj nalog', 'korisnički račun' => 'korisnički nalog', 'detalji računa' => 'podaci o nalogu', 'stvori račun' => 'napravi nalog', 'kreiraj račun' => 'napravi nalog', 'vaš račun' => 'vaš nalog', 'svoj račun' => 'svoj nalog', 'prijavi se u račun' => 'prijavi se na nalog',
			'nadzorna ploča' => 'kontrolna tabla', 'nadzorne ploče' => 'kontrolne table', 'nadzornu ploču' => 'kontrolnu tablu', 'korisničkog računa' => 'korisničkog naloga', 'svojim računom' => 'svojim nalogom', 'vašim računom' => 'vašim nalogom', 'adresu emaila' => 'email adresu', 'županija' => 'opština', 'županije' => 'opštine',
			'adresa e-pošte' => 'email adresa', 'nastavi s kupnjom' => 'nastavi sa kupovinom', 's kupnjom' => 'sa kupovinom',
			// mjeseci (padeži)
			'siječanj' => 'januar', 'siječnja' => 'januara', 'veljača' => 'februar', 'veljače' => 'februara', 'ožujak' => 'mart', 'ožujka' => 'marta',
			'travanj' => 'april', 'travnja' => 'aprila', 'svibanj' => 'maj', 'svibnja' => 'maja', 'lipanj' => 'jun', 'lipnja' => 'juna',
			'srpanj' => 'jul', 'srpnja' => 'jula', 'kolovoz' => 'avgust', 'kolovoza' => 'avgusta', 'rujan' => 'septembar', 'rujna' => 'septembra',
			'listopad' => 'oktobar', 'listopada' => 'oktobra', 'studeni' => 'novembar', 'studenoga' => 'novembra', 'studenog' => 'novembra', 'prosinac' => 'decembar', 'prosinca' => 'decembra',
		);
	}

	/** Zamjena hrvatskih riječi, čuva veliko početno slovo. */
	function lm_cg_zamijeni( $text ) {
		if ( ! is_string( $text ) || '' === $text || ! preg_match( '/[a-zčćšžđ]/iu', $text ) ) {
			return $text;
		}
		static $pattern = null, $map = null;
		if ( null === $pattern ) {
			$map = array();
			foreach ( lm_cg_rijeci() as $hr => $cg ) {
				$map[ mb_strtolower( $hr, 'UTF-8' ) ] = $cg;
			}
			$keys = array_keys( $map );
			usort( $keys, function ( $a, $b ) { return mb_strlen( $b ) - mb_strlen( $a ); } );
			$keys    = array_map( function ( $k ) { return preg_quote( $k, '/' ); }, $keys );
			$pattern = '/(?<![\p{L}\-])(' . implode( '|', $keys ) . ')(?![\p{L}])/iu';
		}
		return preg_replace_callback(
			$pattern,
			function ( $m ) use ( $map ) {
				$word  = $m[1];
				$lower = mb_strtolower( $word, 'UTF-8' );
				if ( ! isset( $map[ $lower ] ) ) {
					return $word;
				}
				$new = $map[ $lower ];
				if ( mb_strtoupper( $word, 'UTF-8' ) === $word && mb_strlen( $word ) > 1 ) {
					return mb_strtoupper( $new, 'UTF-8' );
				}
				$first = mb_substr( $word, 0, 1, 'UTF-8' );
				if ( mb_strtoupper( $first, 'UTF-8' ) === $first ) {
					return mb_strtoupper( mb_substr( $new, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $new, 1, null, 'UTF-8' );
				}
				return $new;
			},
			$text
		);
	}

	/** Prevod engleskog teksta iz mape (i kad ima razmak na kraju, npr. "Brand: "). */
	function lm_cg_engleski( $text ) {
		$map = lm_cg_prevodi();
		if ( isset( $map[ $text ] ) ) {
			return $map[ $text ];
		}
		$trim = trim( $text );
		if ( $trim !== $text && isset( $map[ $trim ] ) ) {
			return str_replace( $trim, $map[ $trim ], $text );
		}
		return $text;
	}

	/** Da li je ovo zahtjev koji vidi kupac (ne englesko sučelje administracije). */
	// AJAX zahtjevi sa sajta (mini korpa, pretraga, dodavanje u korpu) koriste jezik sajta,
	// a ne jezik profila prijavljenog admina.
	add_filter( 'determine_locale', function ( $locale ) {
		if ( wp_doing_ajax() ) {
			$ref = wp_get_referer();
			if ( $ref && false === strpos( $ref, '/wp-admin/' ) ) {
				return get_locale();
			}
		}
		return $locale;
	}, 5 );

	function lm_cg_aktivno() {
		// Emailovi (i kupcu i prodavnici) uvijek na crnogorskom, i kad se šalju iz administracije.
		if ( ! empty( $GLOBALS['lm_em_salje'] ) ) {
			return true;
		}
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}
		return 'en_US' !== determine_locale();
	}

	add_filter(
		'gettext',
		function ( $translation, $text, $domain ) {
			if ( ! lm_cg_aktivno() ) {
				return $translation;
			}
			if ( $translation === $text ) {
				return lm_cg_engleski( $text );
			}
			return lm_cg_zamijeni( $translation );
		},
		20,
		3
	);

	add_filter(
		'gettext_with_context',
		function ( $translation, $text, $context, $domain ) {
			if ( ! lm_cg_aktivno() ) {
				return $translation;
			}
			if ( false !== strpos( $context, 'abbreviation' ) ) {
				$kratko = array( 'Jan' => 'jan', 'Feb' => 'feb', 'Mar' => 'mar', 'Apr' => 'apr', 'May' => 'maj', 'Jun' => 'jun', 'Jul' => 'jul', 'Aug' => 'avg', 'Sep' => 'sep', 'Oct' => 'okt', 'Nov' => 'nov', 'Dec' => 'dec' );
				if ( isset( $kratko[ $text ] ) ) {
					return $kratko[ $text ];
				}
			}
			if ( $translation === $text ) {
				return lm_cg_engleski( $text );
			}
			return lm_cg_zamijeni( $translation );
		},
		20,
		4
	);

	add_filter(
		'ngettext',
		function ( $translation, $single, $plural, $number, $domain ) {
			if ( ! lm_cg_aktivno() ) {
				return $translation;
			}
			if ( $translation === $single || $translation === $plural ) {
				return lm_cg_engleski( $translation );
			}
			return lm_cg_zamijeni( $translation );
		},
		20,
		5
	);

	// Datumi: hrvatski (i engleski) nazivi mjeseci → crnogorski.
	function lm_cg_datum( $date ) {
		if ( ! lm_cg_aktivno() || ! is_string( $date ) ) {
			return $date;
		}
		$date = lm_cg_zamijeni( $date );
		return str_replace(
			array( 'January', 'February', 'March', 'April', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
			array( 'januar', 'februar', 'mart', 'april', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar' ),
			$date
		);
	}
	add_filter( 'date_i18n', 'lm_cg_datum', 20 );
	add_filter( 'wp_date', 'lm_cg_datum', 20 );

	// Naslovi widgeta i stavke menija koji su ostali na engleskom.
	add_filter(
		'widget_title',
		function ( $title ) {
			if ( ! lm_cg_aktivno() || ! is_string( $title ) ) {
				return $title;
			}
			$map = lm_cg_prevodi();
			$key = trim( $title );
			return isset( $map[ $key ] ) ? $map[ $key ] : $title;
		},
		20
	);
	add_filter(
		'nav_menu_item_title',
		function ( $title ) {
			if ( ! lm_cg_aktivno() || ! is_string( $title ) ) {
				return $title;
			}
			$map = lm_cg_prevodi();
			$key = trim( $title );
			return isset( $map[ $key ] ) ? $map[ $key ] : $title;
		},
		20
	);

	// Oznaka jezika stranice.
	add_filter(
		'language_attributes',
		function ( $output ) {
			if ( is_admin() ) {
				return $output;
			}
			return preg_replace( '/lang="[^"]*"/', 'lang="sr-Latn-ME"', $output );
		},
		20
	);
	add_filter(
		'wpseo_locale',
		function () {
			return 'sr_ME';
		}
	);
}

/**
 * Mjeseci i dani u datumima (blog, porudžbine, emailovi): WordPress radi na hrvatskom
 * zbog prevoda, pa je datum na blogu bio „27 ruj 2026“. WP_Locale se pravi prije teme,
 * zato se nazivi upisuju direktno (18.10.2026).
 */
add_action( 'after_setup_theme', function () {
	global $wp_locale;
	if ( ! $wp_locale instanceof WP_Locale ) {
		return;
	}
	$mj  = array( 'januar', 'februar', 'mart', 'april', 'maj', 'jun', 'jul', 'avgust', 'septembar', 'oktobar', 'novembar', 'decembar' );
	$gen = array( 'januara', 'februara', 'marta', 'aprila', 'maja', 'juna', 'jula', 'avgusta', 'septembra', 'oktobra', 'novembra', 'decembra' );
	$kr  = array( 'jan', 'feb', 'mar', 'apr', 'maj', 'jun', 'jul', 'avg', 'sep', 'okt', 'nov', 'dec' );
	foreach ( $mj as $i => $ime ) {
		$k = zeroise( $i + 1, 2 );
		$wp_locale->month[ $k ]                 = $ime;
		$wp_locale->month_genitive[ $k ]        = $gen[ $i ];
		$wp_locale->month_abbrev[ $ime ]        = $kr[ $i ];
		$wp_locale->month_abbrev[ $gen[ $i ] ]  = $kr[ $i ];
	}
	$dani = array( 'nedjelja', 'ponedjeljak', 'utorak', 'srijeda', 'četvrtak', 'petak', 'subota' );
	$dk   = array( 'ned', 'pon', 'uto', 'sri', 'čet', 'pet', 'sub' );
	foreach ( $dani as $i => $d ) {
		$wp_locale->weekday[ $i ]         = $d;
		$wp_locale->weekday_abbrev[ $d ]  = $dk[ $i ];
		$wp_locale->weekday_initial[ $d ] = mb_strtoupper( mb_substr( $d, 0, 1 ) );
	}
}, 1 );

/**
 * Ispravke nađene u end-to-end testu (19.10.2026):
 * – greška pri pogrešnom emailu je glasila „Email adresa Email adresa nije valjana.“;
 * – država u adresi (emailovi, porudžbina) je bila „Montenegro“.
 */
add_filter( 'gettext_woocommerce', function ( $prevod, $tekst ) {
	if ( '%s is not a valid email address.' === $tekst ) {
		return '%s nije ispravna.';
	}
	if ( '%s is not a valid phone number.' === $tekst ) {
		return '%s nije ispravan.';
	}
	return $prevod;
}, 30, 2 );
add_filter( 'woocommerce_countries', function ( $drzave ) {
	if ( is_array( $drzave ) && isset( $drzave['ME'] ) ) {
		$drzave['ME'] = 'Crna Gora';
	}
	return $drzave;
}, 30 );

/**
 * Sortiranje u prodavnici (end-to-end test 20.10.2026): padajuća lista je dolazila sa
 * hrvatskim prevodom („Zadano sortiranje“, „Poredaj od zadnjeg“), a WoodMart lista sa
 * „Newness“. Sortiranje po ocjeni je uklonjeno jer artikli nemaju recenzije.
 */
add_filter( 'woocommerce_catalog_orderby', function ( $opcije ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $opcije;
	}
	$nazivi = array(
		'menu_order' => 'Preporučeno',
		'popularity' => 'Najpopularnije',
		'date'       => 'Najnovije',
		'price'      => 'Cijena: od najniže',
		'price-desc' => 'Cijena: od najviše',
	);
	unset( $opcije['rating'] );
	foreach ( $opcije as $k => $v ) {
		if ( isset( $nazivi[ $k ] ) ) {
			$opcije[ $k ] = $nazivi[ $k ];
		}
	}
	return $opcije;
}, 99 );
