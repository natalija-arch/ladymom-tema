<?php
/**
 * Lady Mom – podaci koje Meta for WooCommerce šalje u Meta katalog (oglasi na Facebooku i Instagramu).
 *
 * Markova odluka 22.10.2026 („sredi to kvalitetno“). Pregled kataloga je pokazao:
 * - varijante (sva garderoba) idu sa brendom „Lady Mom“ jer dodatak brend traži na samoj
 *   varijanti, a brend (taksonomija product_brand) je na glavnom proizvodu;
 * - veličina i boja se ne šalju jer se atributi zovu „Veličina“ i „Boja“ (pa_velicina, pa_boja),
 *   a dodatak prepoznaje samo engleske nazive (size, color);
 * - opisi imaju HTML znakove („&scaron;“ umjesto „š“), a kad ERP ne pošalje opis, opis je samo naziv.
 *
 * Mijenja se samo ono što ide Meti (zvanični filter dodatka), proizvodi na sajtu i podaci iz
 * ERP-a ostaju isti. Isti izvori kao Google feed (inc/google-feed.php), da oba kataloga govore isto.
 */

defined( 'ABSPATH' ) || exit;

/** Tekst bez HTML-a i HTML znakova, u jednom redu. */
function lm_meta_cist_tekst( $s ) {
	$s = wp_strip_all_tags( strip_shortcodes( (string) $s ) );
	$s = html_entity_decode( $s, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	return trim( preg_replace( '/\s+/u', ' ', $s ) );
}

add_filter(
	'facebook_for_woocommerce_integration_prepare_product',
	function ( $data, $id ) {
		if ( ! is_array( $data ) ) {
			return $data;
		}
		// Dodatak filteru za varijantu šalje ID glavnog proizvoda; ID same varijante je u external_variant_id.
		$vid = ! empty( $data['external_variant_id'] ) ? (int) $data['external_variant_id'] : (int) $id;
		$p   = wc_get_product( $vid ) ?: wc_get_product( $id );
		if ( ! $p ) {
			return $data;
		}
		$glavni = $p->is_type( 'variation' ) ? wc_get_product( $p->get_parent_id() ) : $p;
		if ( ! $glavni ) {
			return $data;
		}

		// Brend sa glavnog proizvoda (kao u Google feedu).
		$brend = wp_get_post_terms( $glavni->get_id(), 'product_brand', array( 'fields' => 'names' ) );
		$brend = $brend && ! is_wp_error( $brend ) ? html_entity_decode( $brend[0], ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : '';
		if ( '' !== $brend ) {
			$data['brand'] = mb_substr( $brend, 0, 100 );
		}

		// Veličina i boja varijante.
		if ( $p->is_type( 'variation' ) ) {
			foreach ( $p->get_attributes() as $tax => $slug ) {
				if ( ! $slug ) {
					continue;
				}
				$term = get_term_by( 'slug', $slug, $tax );
				$ime  = $term ? $term->name : (string) $slug;
				$ime  = html_entity_decode( $ime, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
				if ( 'pa_velicina' === $tax && '' !== $ime ) {
					$data['size'] = mb_strtoupper( $ime );
				} elseif ( 'pa_boja' === $tax && '' !== $ime && ! ( function_exists( 'lm_je_default' ) && lm_je_default( $ime ) ) ) {
					$data['color'] = $ime;
				}
			}
		}

		// Opis: čist tekst; kad ERP nije poslao opis, rečenica od brenda, naziva i kategorije.
		$kat  = function_exists( 'lm_feed_kategorija' ) ? lm_feed_kategorija( $glavni->get_id() ) : '';
		$opis = '';
		if ( function_exists( 'lm_feed_opis' ) ) {
			$opis = lm_meta_cist_tekst( lm_feed_opis( $glavni, $brend, $kat ) );
		} elseif ( isset( $data['description'] ) ) {
			$opis = lm_meta_cist_tekst( $data['description'] );
		}
		if ( '' !== $opis ) {
			$data['description'] = mb_substr( $opis, 0, 5000 );
		}
		if ( isset( $data['short_description'] ) && '' !== (string) $data['short_description'] ) {
			$data['short_description'] = lm_meta_cist_tekst( $data['short_description'] );
		}

		// Pol i uzrast za garderobu i Google kategorija, kao u Google feedu.
		if ( $kat && function_exists( 'lm_feed_pol_uzrast' ) ) {
			$pu = lm_feed_pol_uzrast( $kat );
			if ( $pu ) {
				$data['gender']    = $pu[0];
				$data['age_group'] = $pu[1];
			}
		}
		if ( empty( $data['google_product_category'] ) && $kat && function_exists( 'lm_feed_google_kategorija' ) ) {
			$gk = lm_feed_google_kategorija( $kat );
			if ( $gk ) {
				$data['google_product_category'] = $gk;
			}
		}

		// Razred marže (a/b/c/n), ista neutralna oznaka kao u Google feedu: za skupove proizvoda u oglasima.
		if ( function_exists( 'lm_feed_razred_marze' ) ) {
			$data['custom_label_0'] = lm_feed_razred_marze( $p );
		}

		return $data;
	},
	20,
	2
);
