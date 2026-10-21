<?php
/**
 * Lady Mom – „Povezani proizvodi“ na stranici artikla (pregled sajta 21.10.2026, stavka r10; Marko potvrdio).
 *
 * WooCommerce bira nasumično iz svih kategorija artikla, pa i iz širokih („Mame“, „Prodavnica“):
 * uz helanke su izlazili jastuk, pojas tri puta (isti naziv, druga boja) i pumpice.
 * Ovdje prvo idu artikli iz iste podkategorije, pa iz iste grupe (npr. Garderoba), a u okviru toga
 * isti brend i najprodavaniji. Isti naziv (isti model u drugoj boji) se prikazuje samo jednom,
 * a artikal koji kupac gleda se ne ponavlja u drugoj boji.
 *
 * „Često se kupuje uz“ su WoodMart paketi (Proizvodi → Frequently bought together), bez popusta:
 * popusti dolaze iz ERP-a.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Naziv za poređenje: bez boje u zagradi na kraju i bez razlika u crticama i razmacima.
 *
 * @param int $id ID artikla.
 * @return string
 */
function lm_povezani_naziv( $id ) {
	$n = mb_strtolower( html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ), 'UTF-8' );
	$n = preg_replace( '/\s*\([^)]*\)\s*$/u', '', $n );
	$n = preg_replace( '/[\s\x{2013}\x{2014}-]+/u', ' ', $n );
	return trim( (string) $n );
}

/**
 * Povezani proizvodi: ista podkategorija (+4), isti brend (+2), pa najprodavaniji; bez duplikata naziva.
 *
 * @param int[] $ids        ID-jevi koje je izabrao WooCommerce.
 * @param int   $product_id Artikal.
 * @param array $args       limit, excluded_ids.
 * @return int[]
 */
function lm_povezani_proizvodi( $ids, $product_id, $args ) {
	$limit    = ( isset( $args['limit'] ) && (int) $args['limit'] > 0 ) ? (int) $args['limit'] : 8;
	$iskljuci = array_map( 'intval', ! empty( $args['excluded_ids'] ) ? (array) $args['excluded_ids'] : array() );
	$iskljuci[] = (int) $product_id;

	$kat = wp_get_post_terms( $product_id, 'product_cat' );
	if ( is_wp_error( $kat ) || ! $kat ) {
		return $ids;
	}
	// Podkategorija = kategorija artikla koja nije roditelj nijednoj drugoj njegovoj kategoriji.
	// Šira grupa = njen roditelj, ako nije sam vrh („Mame“, „Bebe“ su preširoki).
	$roditelji = array_map( 'intval', wp_list_pluck( $kat, 'parent' ) );
	$uze       = array();
	$sire      = array();
	foreach ( $kat as $t ) {
		if ( in_array( (int) $t->term_id, $roditelji, true ) ) {
			continue;
		}
		$uze[] = (int) $t->term_id;
		if ( $t->parent && count( get_ancestors( $t->parent, 'product_cat', 'taxonomy' ) ) >= 2 ) {
			$sire[] = (int) $t->parent;
		}
	}
	if ( ! $uze ) {
		return $ids;
	}

	$brend = taxonomy_exists( 'product_brand' ) ? wp_get_post_terms( $product_id, 'product_brand', array( 'fields' => 'ids' ) ) : array();
	$brend = is_wp_error( $brend ) ? array() : array_map( 'intval', $brend );

	$porez = array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => array_values( array_unique( array_merge( $uze, $sire ) ) ),
		),
	);
	$vid        = wc_get_product_visibility_term_ids();
	$nevidljivi = array();
	if ( ! empty( $vid['exclude-from-catalog'] ) ) {
		$nevidljivi[] = $vid['exclude-from-catalog'];
	}
	if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! empty( $vid['outofstock'] ) ) {
		$nevidljivi[] = $vid['outofstock'];
	}
	if ( $nevidljivi ) {
		$porez[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'term_taxonomy_id',
			'terms'    => $nevidljivi,
			'operator' => 'NOT IN',
		);
	}

	$kandidati = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 150,
			'fields'         => 'ids',
			'post__not_in'   => $iskljuci,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => $porez, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);
	if ( ! $kandidati ) {
		return $ids;
	}
	update_meta_cache( 'post', $kandidati );
	update_object_term_cache( $kandidati, 'product' );

	$red = array();
	foreach ( $kandidati as $i => $id ) {
		$bod = has_term( $uze, 'product_cat', $id ) ? 4 : 0;
		if ( $brend && has_term( $brend, 'product_brand', $id ) ) {
			$bod += 2;
		}
		$red[] = array( (int) $id, $bod, (int) get_post_meta( $id, 'total_sales', true ), $i );
	}
	usort(
		$red,
		function ( $a, $b ) {
			return array( $b[1], $b[2], $a[3] ) <=> array( $a[1], $a[2], $b[3] );
		}
	);

	$vidjeni = array( lm_povezani_naziv( $product_id ) => true );
	$izlaz   = array();
	$dodaj   = function ( $id ) use ( &$vidjeni, &$izlaz, $iskljuci ) {
		$id = (int) $id;
		if ( in_array( $id, $izlaz, true ) || in_array( $id, $iskljuci, true ) ) {
			return;
		}
		$n = lm_povezani_naziv( $id );
		if ( isset( $vidjeni[ $n ] ) ) {
			return;
		}
		$vidjeni[ $n ] = true;
		$izlaz[]       = $id;
	};
	foreach ( $red as $r ) {
		$dodaj( $r[0] );
		if ( count( $izlaz ) >= $limit ) {
			break;
		}
	}
	// Ako u podkategoriji i grupi nema dovoljno, dopuni WooCommerce-ovim izborom (bez duplikata).
	foreach ( (array) $ids as $id ) {
		if ( count( $izlaz ) >= $limit ) {
			break;
		}
		$dodaj( $id );
	}
	return $izlaz;
}
add_filter( 'woocommerce_related_products', 'lm_povezani_proizvodi', 20, 3 );

// Redoslijed iznad je namjeran, pa ga WooCommerce ne miješa.
add_filter( 'woocommerce_product_related_posts_shuffle', '__return_false' );
