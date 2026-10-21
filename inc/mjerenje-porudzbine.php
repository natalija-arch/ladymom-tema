<?php
/**
 * Kupovine u GA4 i Meti: bez test porudžbina, a otkazane se oduzimaju.
 *
 * 1. Test porudžbine (natalija+test…@ladymom.me, oznaka `_lm_test` iz inc/erp.php) se ne
 *    šalju kao kupovina. Site Kit i Meta for WooCommerce preskaču porudžbinu koja već ima
 *    njihovu oznaku „poslato“, pa je postavljamo unaprijed.
 * 2. Kad se porudžbina koja je otišla u GA4 otkaže ili vrati, GA4 dobija događaj `refund`
 *    (zvanični Measurement Protocol). GA4 ga oduzima od „Purchase revenue“; broj kupovina
 *    ostaje. Radi tek kad Marko upiše API secret u Podešavanja → Opšte (opcija
 *    `lm_ga4_mp_secret`, nije u kodu).
 * 3. Jednom, kad se secret prvi put sačuva: povrat za test kupovine od 20.10.2026 koje su
 *    već u GA4 (porudžbine su obrisane iz WooCommerce-a).
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/* 1. Test porudžbine: oznake „već poslato“ za Site Kit i Metu. */
function lm_mjerenje_preskoci_test( $order ) {
	if ( ! $order instanceof WC_Order || ! function_exists( 'lm_je_test_email' ) || ! lm_je_test_email( $order->get_billing_email() ) ) {
		return;
	}
	$order->update_meta_data( '_googlesitekit_ga_purchase_event_tracked', '1' );
	$order->update_meta_data( '_meta_purchase_tracked_browser', 'yes' );
	$order->update_meta_data( '_meta_purchase_tracked_server', 'yes' );
	$order->save_meta_data();
}
// Prioritet 6: poslije oznake `_lm_test` (5), prije Meta Purchase događaja (10).
add_action(
	'woocommerce_new_order',
	function ( $order_id, $order = null ) {
		lm_mjerenje_preskoci_test( $order instanceof WC_Order ? $order : wc_get_order( $order_id ) );
	},
	6,
	2
);
add_action( 'woocommerce_checkout_order_created', 'lm_mjerenje_preskoci_test', 6 );

/* 2. Povrat u GA4 preko Measurement Protocol-a. */
function lm_ga4_mp_posalji_povrat( $transaction_id, $iznos, $valuta = 'EUR' ) {
	$secret = trim( (string) get_option( 'lm_ga4_mp_secret', '' ) );
	$sk     = get_option( 'googlesitekit_analytics-4_settings' );
	$mid    = is_array( $sk ) && ! empty( $sk['measurementID'] ) ? $sk['measurementID'] : '';
	if ( '' === $secret || '' === $mid ) {
		return false;
	}
	$url  = add_query_arg(
		array(
			'measurement_id' => rawurlencode( $mid ),
			'api_secret'     => rawurlencode( $secret ),
		),
		'https://www.google-analytics.com/mp/collect'
	);
	$telo = array(
		'client_id' => 'lm.' . preg_replace( '/[^0-9]/', '', (string) $transaction_id ) . '.1',
		'events'    => array(
			array(
				'name'   => 'refund',
				'params' => array(
					'transaction_id' => (string) $transaction_id,
					'value'          => round( (float) $iznos, 2 ),
					'currency'       => $valuta,
				),
			),
		),
	);
	$odgovor = wp_remote_post(
		$url,
		array(
			'timeout' => 5,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $telo ),
		)
	);
	return ! is_wp_error( $odgovor ) && wp_remote_retrieve_response_code( $odgovor ) < 300;
}

function lm_ga4_povrat_za_porudzbinu( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order || $order->get_meta( '_lm_test' ) ) {
		return;
	}
	// Samo porudžbine koje su otišle u GA4 kao kupovina, i samo jednom.
	if ( '1' !== (string) $order->get_meta( '_googlesitekit_ga_purchase_event_tracked' ) || $order->get_meta( '_lm_ga4_povrat' ) ) {
		return;
	}
	if ( lm_ga4_mp_posalji_povrat( $order->get_id(), $order->get_total(), $order->get_currency() ) ) {
		$order->update_meta_data( '_lm_ga4_povrat', current_time( 'mysql' ) );
		$order->save_meta_data();
		$order->add_order_note( 'Google Analytics: kupovina oduzeta (povrat), jer je porudžbina otkazana.' );
	}
}
add_action( 'woocommerce_order_status_cancelled', 'lm_ga4_povrat_za_porudzbinu' );
add_action( 'woocommerce_order_status_refunded', 'lm_ga4_povrat_za_porudzbinu' );

/* Polje za API secret: Podešavanja → Opšte. Vrijednost se ne ispisuje na stranici. */
add_action(
	'admin_init',
	function () {
		register_setting(
			'general',
			'lm_ga4_mp_secret',
			array(
				'type'              => 'string',
				'sanitize_callback' => function ( $nova ) {
					$nova = trim( sanitize_text_field( (string) $nova ) );
					// Prazno polje = zadrži postojeći secret.
					return '' === $nova ? (string) get_option( 'lm_ga4_mp_secret', '' ) : $nova;
				},
			)
		);
		add_settings_field(
			'lm_ga4_mp_secret',
			'GA4 Measurement Protocol – API secret',
			function () {
				$ima = '' !== (string) get_option( 'lm_ga4_mp_secret', '' );
				printf(
					'<input type="password" name="lm_ga4_mp_secret" id="lm_ga4_mp_secret" value="" class="regular-text" autocomplete="new-password" placeholder="%s"><p class="description">%s</p>',
					esc_attr( $ima ? 'Sačuvano (ostavite prazno da ostane isto)' : '' ),
					esc_html( 'Google Analytics → Admin → Data streams → Lady Mom → Measurement Protocol API secrets → Create. Služi da se otkazane porudžbine oduzmu u GA4.' )
				);
			},
			'general'
		);
	}
);

/* 3. Jednom: povrat za test kupovine od 20.10.2026 koje su već u GA4. */
add_action(
	'update_option_lm_ga4_mp_secret',
	'lm_ga4_stari_test_povrati'
);
add_action(
	'add_option_lm_ga4_mp_secret',
	'lm_ga4_stari_test_povrati'
);
function lm_ga4_stari_test_povrati() {
	if ( get_option( 'lm_ga4_test_povrati_2010' ) ) {
		return;
	}
	$test = array(
		'51485' => 116.10, // Ready Mom, gotovina, sa kuponom.
		'51487' => 9.00,   // Preuzimanje u butiku, gotovina.
		'51492' => 129.00, // Ready Mom.
	);
	$poslato = array();
	foreach ( $test as $id => $iznos ) {
		if ( lm_ga4_mp_posalji_povrat( $id, $iznos ) ) {
			$poslato[] = $id;
		}
	}
	if ( count( $poslato ) === count( $test ) ) {
		update_option( 'lm_ga4_test_povrati_2010', current_time( 'mysql' ), false );
	}
}
