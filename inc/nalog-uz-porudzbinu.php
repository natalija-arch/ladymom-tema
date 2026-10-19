<?php
/**
 * Lady Mom – svaki kupac dobija nalog, bez dodatnog koraka (Markova odluka 18.10.2026,
 * poređenje sa Fashion&Friends, gdje je nalog obavezan prije plaćanja).
 *
 * - Na vrhu plaćanja je „Nastavi sa Google nalogom“ za goste.
 * - Gost popuni adresu kao i do sada. Kad porudžbina nastane:
 *   ako za taj email nema naloga, nalog se napravi sam (WooCommerce šalje email
 *   sa linkom za postavljanje lozinke), a porudžbina i ranije porudžbine sa istim
 *   emailom se vežu za nalog; ako nalog postoji, porudžbina se samo veže za njega.
 * - Ništa se ne šalje ERP-u drugačije: mijenja se samo kupac na porudžbini na sajtu.
 */

defined( 'ABSPATH' ) || exit;

/** Veže porudžbinu gosta za nalog (postojeći ili novi). */
function lm_nalog_uz_porudzbinu( $order ) {
	if ( ! $order instanceof WC_Order || $order->get_customer_id() ) {
		return;
	}
	$email = sanitize_email( $order->get_billing_email() );
	if ( ! is_email( $email ) ) {
		return;
	}
	$korisnik = get_user_by( 'email', $email );
	$novi     = false;
	if ( ! $korisnik ) {
		// Lozinku kupac postavlja sam preko linka iz emaila „Vaš nalog“.
		add_filter( 'pre_option_woocommerce_registration_generate_password', 'lm_vrati_yes' );
		add_filter( 'pre_option_woocommerce_registration_generate_username', 'lm_vrati_yes' );
		$uid = wc_create_new_customer(
			$email,
			'',
			'',
			array(
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
			)
		);
		remove_filter( 'pre_option_woocommerce_registration_generate_password', 'lm_vrati_yes' );
		remove_filter( 'pre_option_woocommerce_registration_generate_username', 'lm_vrati_yes' );
		if ( is_wp_error( $uid ) ) {
			$order->add_order_note( 'Nalog nije napravljen automatski: ' . $uid->get_error_message() );
			return;
		}
		$novi = true;
	} else {
		$uid = $korisnik->ID;
		// Ne vežemo porudžbine za naloge osoblja (administrator, menadžer prodavnice).
		if ( user_can( $uid, 'edit_shop_orders' ) ) {
			return;
		}
	}

	$order->set_customer_id( $uid );
	$order->save();

	if ( $novi ) {
		$kupac = new WC_Customer( $uid );
		foreach ( array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country', 'state', 'phone', 'email' ) as $polje ) {
			$m = 'get_billing_' . $polje;
			$s = 'set_billing_' . $polje;
			if ( method_exists( $order, $m ) && method_exists( $kupac, $s ) ) {
				$kupac->$s( $order->$m() );
			}
		}
		foreach ( array( 'first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'postcode', 'country', 'state', 'phone' ) as $polje ) {
			$m = 'get_shipping_' . $polje;
			$s = 'set_shipping_' . $polje;
			if ( method_exists( $order, $m ) && method_exists( $kupac, $s ) ) {
				$kupac->$s( $order->$m() );
			}
		}
		$kupac->save();
		wc_update_new_customer_past_orders( $uid );
		$order->add_order_note( 'Kupcu je automatski napravljen nalog na sajtu; link za lozinku je poslat emailom.' );
	}
}

function lm_vrati_yes() {
	return 'yes';
}

add_action( 'woocommerce_checkout_order_processed', function ( $order_id, $data, $order ) {
	lm_nalog_uz_porudzbinu( $order );
}, 20, 3 );
add_action( 'woocommerce_store_api_checkout_order_processed', 'lm_nalog_uz_porudzbinu', 20 );

/** Plaćanje: Google na vrhu za goste i rečenica da nalog stiže sam. */
add_action( 'woocommerce_before_checkout_form', function () {
	if ( is_user_logged_in() ) {
		return;
	}
	$google = function_exists( 'woodmart_get_opt' ) && woodmart_get_opt( 'goo_app_id' ) && woodmart_get_opt( 'goo_app_secret' ) && class_exists( 'WOODMART_CORE\Inc\Auth', false );
	?>
	<div class="lm-brza-kupovina">
		<?php if ( $google ) : ?>
			<a class="lm-google-dugme" href="<?php echo esc_url( add_query_arg( 'social_auth', 'google', wc_get_page_permalink( 'myaccount' ) ) . '&is_checkout=1' ); ?>">Nastavi sa Google nalogom</a>
			<p class="lm-brza-ili"><span>ili popunite podatke ispod</span></p>
		<?php endif; ?>
		<p class="lm-nalog-napomena">Uz porudžbinu dobijate nalog na sajtu, za praćenje porudžbina i bržu sljedeću kupovinu. Link za lozinku stiže na vaš email.</p>
	</div>
	<style id="lm-brza-kupovina">
	.lm-brza-kupovina{max-width:560px;margin:0 auto 26px}
	.lm-google-dugme{display:flex;align-items:center;justify-content:center;gap:10px;min-height:48px;padding:10px 16px;background:#fff;color:#3c4043!important;border:1px solid #dadce0;border-radius:6px;font-size:15px;font-weight:600;box-shadow:0 1px 2px rgba(60,64,67,.12);text-decoration:none!important}
	.lm-google-dugme:hover{background:#f8f9fa;box-shadow:0 1px 4px rgba(60,64,67,.22)}
	.lm-google-dugme:before{content:"";width:20px;height:20px;flex:0 0 20px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 48 48'%3E%3Cpath fill='%23EA4335' d='M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z'/%3E%3Cpath fill='%234285F4' d='M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z'/%3E%3Cpath fill='%23FBBC05' d='M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z'/%3E%3Cpath fill='%2334A853' d='M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z'/%3E%3C/svg%3E") center/contain no-repeat}
	.lm-brza-ili{display:flex;align-items:center;gap:12px;margin:16px 0 10px;font-size:13px;color:var(--wd-text-color,#777)}
	.lm-brza-ili:before,.lm-brza-ili:after{content:"";flex:1;border-top:1px solid var(--brdcolor-gray-300,#e5e5e5)}
	.lm-nalog-napomena{margin:0;font-size:13px;line-height:1.5;color:var(--wd-text-color,#777);text-align:center}
	</style>
	<?php
}, 6 );

/**
 * Stranica „Porudžbina primljena“ (ispravka 19.10.2026, end-to-end test):
 * pošto se porudžbina gosta sada veže za nalog, WooCommerce je umjesto detalja
 * tražio prijavu („Prijavite se na svoj nalog kako biste vidjeli ovu porudžbinu“).
 * Link sa tajnim ključem porudžbine je dovoljan dokaz, kao i za goste.
 */
add_filter( 'woocommerce_order_received_verify_known_shoppers', '__return_false' );
