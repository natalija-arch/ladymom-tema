<?php
/**
 * Lady Mom – zaštite pri kupovini (Markovo odobrenje 18.10.2026).
 *
 * Artikal ili varijanta sa cijenom 0 (ili bez cijene) ne može da se kupi.
 * ERP ponekad pošalje novi barkod sa cijenom 0 (npr. Koala Perfect Head Maxi,
 * 18.10.2026). Podaci iz ERP-a se ne mijenjaju; mijenja se samo to da takav
 * artikal ne može u korpu. Čim ERP pošalje pravu cijenu, kupovina radi sama.
 */

defined( 'ABSPATH' ) || exit;

function lm_cijena_nula( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return false;
	}
	$cijena = $product->get_price( 'edit' );
	return '' !== (string) $cijena && (float) $cijena <= 0;
}

add_filter( 'woocommerce_is_purchasable', function ( $moze, $product ) {
	return $moze && ! lm_cijena_nula( $product );
}, 20, 2 );

add_filter( 'woocommerce_variation_is_purchasable', function ( $moze, $variation ) {
	return $moze && ! lm_cijena_nula( $variation );
}, 20, 2 );

/** Za artikal sa cijenom 0 umjesto „0,00 €“ piše „Cijena uskoro“. */
add_filter( 'woocommerce_get_price_html', function ( $html, $product ) {
	if ( $product->is_type( 'variable' ) ) {
		$cijene = $product->get_variation_prices( true );
		$prave  = array_filter( (array) ( $cijene['price'] ?? array() ), function ( $c ) {
			return (float) $c > 0;
		} );
		if ( $cijene['price'] && ! $prave ) {
			return '<span class="lm-cijena-uskoro">' . esc_html__( 'Cijena uskoro', 'woodmart-child' ) . '</span>';
		}
		// Varijante sa 0 ne ulaze u raspon cijena („0,00 – 65,00 €“).
		if ( $prave && count( $prave ) !== count( $cijene['price'] ) ) {
			$min = min( $prave );
			$max = max( $prave );
			$txt = $min === $max ? wc_price( $min ) : wc_format_price_range( $min, $max );
			return $txt . $product->get_price_suffix();
		}
		return $html;
	}
	return lm_cijena_nula( $product ) ? '<span class="lm-cijena-uskoro">' . esc_html__( 'Cijena uskoro', 'woodmart-child' ) . '</span>' : $html;
}, 20, 2 );
