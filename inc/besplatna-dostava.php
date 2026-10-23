<?php
/**
 * Lady Mom – besplatna dostava preko 100 €
 * (Ranije Code Snippets #6, prebačeno u child temu.)
 */

defined( 'ABSPATH' ) || exit;

// Kad je dostupna besplatna dostava (porudzbina od 100 EUR), sakrij placenu dostavu.
add_filter( 'woocommerce_package_rates', function ( $rates ) {
	$free = array();
	foreach ( $rates as $rate_id => $rate ) {
		if ( 'free_shipping' === $rate->method_id ) {
			$free[ $rate_id ] = $rate;
		}
	}
	if ( empty( $free ) ) {
		return $rates;
	}
	// Licno preuzimanje (ako se doda) ostaje kao opcija.
	foreach ( $rates as $rate_id => $rate ) {
		if ( lm_je_metoda_preuzimanja( $rate->method_id ) ) {
			$free[ $rate_id ] = $rate;
		}
	}
	return $free;
}, 100 );
