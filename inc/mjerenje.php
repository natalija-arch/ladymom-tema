<?php
/**
 * Mjerenje za oglase (Meta piksel i GA4).
 *
 * 1. WoodMart dodaje u korpu svojim AJAX pozivom (woodmart_ajax_add_to_cart),
 *    koji ne okida woocommerce_ajax_added_to_cart. Zato Meta for WooCommerce
 *    nije slao AddToCart iz pregledača, nego samo sa servera. Ovdje okidamo tu
 *    akciju sa istim podacima, pa dodatak sam ubaci svoj fragment sa AddToCart
 *    (isti event_id kao serverski, pa se ne broji dvaput).
 * 2. Serverski (CAPI) događaji se ne šalju za botove i alate za keš
 *    (LiteSpeed crawler, QUIC.cloud, Google, Bing...). Zvanični filter dodatka.
 * 3. Upiti kao konverzije: klik na telefon, WhatsApp i Viber, i poslata CF7 forma
 *    (GA4: contact, generate_lead, sign_up; Meta: Contact). CF7 Lead za Metu
 *    šalje sam dodatak.
 * 4. Microsoft Clarity (mape klikova i snimci posjeta), projekat z173rgv3iv.
 *    Ne učitava se za prijavljene administratore. Uz traku za kolačiće čeka
 *    saglasnost za statistiku.
 * 5. Meta piksel čeka saglasnost za marketing (zvanični „hold“ dodatka Meta for
 *    WooCommerce), kad je aktivan WP Consent API (Complianz).
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

/*
 * 1. AddToCart iz pregledača za WoodMart AJAX dodavanje u korpu.
 * Prioritet 50: poslije serverskog događaja dodatka (40), koji u sesiju upiše event_id.
 */
add_action(
	'woocommerce_add_to_cart',
	function ( $cart_item_key, $product_id, $quantity, $variation_id ) {
		static $vec_uradjeno = false;

		if ( $vec_uradjeno || ! wp_doing_ajax() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$akcija = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( 'woodmart_ajax_add_to_cart' !== $akcija ) {
			return;
		}
		$vec_uradjeno = true;

		// Dodatak čita product_id i quantity iz $_POST (WoodMart šalje add-to-cart).
		$_POST['product_id'] = (int) ( $variation_id ? $variation_id : $product_id );
		$_POST['quantity']   = (int) $quantity;

		do_action( 'woocommerce_ajax_added_to_cart', (int) $product_id );
	},
	50,
	4
);

/*
 * 2. Botovi i alati za keš ne šalju serverske događaje u Metu.
 * Dodatak već prepoznaje „crawler“ i Meta botove; ovo su ostali koje vidimo na sajtu.
 */
add_filter(
	'wc_facebook_crawler_user_agent_patterns',
	function ( $obrasci ) {
		return array_merge(
			(array) $obrasci,
			array(
				'bot/',
				'bot;',
				'bot)',
				'spider',
				'slurp',
				'lscache',
				'litespeed',
				'quic.cloud',
				'headlesschrome',
				'lighthouse',
				'pagespeed',
				'python-requests',
				'python-urllib',
				'curl/',
				'wget/',
				'go-http-client',
				'facebookexternalhit',
			)
		);
	}
);

// Zahtjev bez User-Agent-a nije posjetilac.
add_filter(
	'wc_facebook_is_crawler_request',
	function ( $je_bot, $user_agent ) {
		return $je_bot || '' === trim( (string) $user_agent );
	},
	10,
	2
);

/*
 * 3. Upiti: telefon, WhatsApp, Viber i CF7 forme.
 */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		?>
<script id="lm-mjerenje-upiti">
(function () {
	function posalji(ga, meta, podaci) {
		try { if (typeof window.gtag === 'function') { window.gtag('event', ga, podaci); } } catch (e) {}
		try {
			if (!meta) { return; }
			// FacebookSignals čuva događaj dok kupac ne prihvati marketinške kolačiće.
			if (window.FacebookSignals && typeof window.FacebookSignals.trackEvent === 'function') {
				window.FacebookSignals.trackEvent(meta, podaci);
			} else if (typeof window.fbq === 'function') {
				window.fbq('track', meta, podaci);
			}
		} catch (e) {}
	}
	document.addEventListener('click', function (e) {
		var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
		if (!a) { return; }
		var h = (a.getAttribute('href') || '').toLowerCase();
		// Samo linkovi ka broju butika (067 030 655), ne dugmad za dijeljenje.
		if (h.replace(/[^0-9]/g, '').indexOf('67030655') === -1) { return; }
		var nacin = '';
		if (h.indexOf('tel:') === 0) { nacin = 'telefon'; }
		else if (h.indexOf('whatsapp') !== -1 || h.indexOf('wa.me/') !== -1) { nacin = 'whatsapp'; }
		else if (h.indexOf('viber') !== -1) { nacin = 'viber'; }
		if (nacin) { posalji('contact', 'Contact', { method: nacin }); }
	}, true);
	document.addEventListener('wpcf7mailsent', function (e) {
		var id = e && e.detail ? String(e.detail.contactFormId || '') : '';
		if (id === '35763') {
			posalji('sign_up', null, { method: 'newsletter' });
		} else {
			posalji('generate_lead', null, { form_id: id });
		}
	}, false);
})();
</script>
		<?php
	},
	50
);

/*
 * 4. Microsoft Clarity (zvanični kod sa clarity.microsoft.com).
 * Sa trakom za kolačiće (WP Consent API) učitava se tek uz saglasnost za statistiku.
 */
const LM_CLARITY_ID = 'z173rgv3iv';

add_action(
	'wp_footer',
	function () {
		if ( is_admin() || current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
<script id="lm-clarity">
(function () {
	var ucitano = false;
	function ucitaj() {
		if (ucitano) { return; }
		ucitano = true;
		(function(c,l,a,r,i,t,y){
			c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
			t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
			y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
		})(window, document, "clarity", "script", <?php echo wp_json_encode( LM_CLARITY_ID ); ?>);
	}
	<?php if ( function_exists( 'wp_has_consent' ) ) : ?>
	function provjeri() {
		if (typeof window.wp_has_consent === 'function' && window.wp_has_consent('statistics')) { ucitaj(); }
	}
	document.addEventListener('wp_listen_for_consent_change', function (e) {
		var d = (e && e.detail) || {};
		if (d.statistics === 'allow') { ucitaj(); }
	});
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', provjeri); } else { provjeri(); }
	<?php else : ?>
	ucitaj();
	<?php endif; ?>
})();
</script>
		<?php
	},
	60
);

/*
 * Site Kit Consent Mode: podrazumijevano „denied“ važi samo za EEA/UK/CH (fiksna lista
 * u Site Kit-u). Dodajemo Crnu Goru, gdje su svi naši kupci (zvanični filter).
 */
add_filter(
	'googlesitekit_consent_defaults',
	function ( $podrazumijevano ) {
		if ( isset( $podrazumijevano['region'] ) && is_array( $podrazumijevano['region'] ) && ! in_array( 'ME', $podrazumijevano['region'], true ) ) {
			$podrazumijevano['region'][] = 'ME';
		}
		return $podrazumijevano;
	}
);

/*
 * 5. Meta piksel uz traku za kolačiće.
 * Meta for WooCommerce ima zvanični mehanizam „hold“ (kolačić wc_facebook_signals_state
 * i window.fbwcsignal): dok je zadržan, piksel se ne pokreće, a događaji se čuvaju i
 * pošalju tek kad kupac prihvati marketinške kolačiće. Bez kolačića dodatak smatra da
 * je saglasnost data, pa novog posjetioca ovdje zadržavamo dok ne odluči.
 * Radi samo kad je aktivan WP Consent API (Complianz), inače se ništa ne mijenja.
 */
add_filter(
	'facebook_signals_held',
	function ( $zadrzano ) {
		if ( $zadrzano || ! function_exists( 'wp_has_consent' ) || is_admin() ) {
			return $zadrzano;
		}
		return ! isset( $_COOKIE['wc_facebook_signals_state'] );
	}
);

add_action(
	'wp_head',
	function () {
		if ( is_admin() || ! function_exists( 'wp_has_consent' ) ) {
			return;
		}
		?>
<script id="lm-meta-saglasnost">
(function () {
	var K = 'wc_facebook_signals_state';
	if (!/(?:^|;\s*)wc_facebook_signals_state=/.test(document.cookie)) {
		document.cookie = K + '=held;path=/;max-age=31536000;SameSite=Lax';
	}
})();
</script>
		<?php
	},
	0
);

add_action(
	'wp_footer',
	function () {
		if ( is_admin() || ! function_exists( 'wp_has_consent' ) ) {
			return;
		}
		?>
<script id="lm-meta-saglasnost-promjena">
(function () {
	function stanje() {
		var m = document.cookie.match(/(?:^|;\s*)wc_facebook_signals_state=([^;]*)/);
		return m ? m[1] : null;
	}
	function uskladi(dozvoljeno) {
		var s = window.fbwcsignal;
		if (!s) { return; }
		if (dozvoljeno && stanje() !== 'active') { s.release(); }
		if (!dozvoljeno && stanje() !== 'held') { s.hold(); }
	}
	document.addEventListener('wp_listen_for_consent_change', function (e) {
		var d = (e && e.detail) || {};
		if (d.marketing === 'allow') { uskladi(true); }
		if (d.marketing === 'deny') { uskladi(false); }
	});
	function provjeri() {
		if (typeof window.wp_has_consent === 'function' && window.wp_has_consent('marketing')) { uskladi(true); }
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', provjeri); } else { provjeri(); }
})();
</script>
		<?php
	},
	60
);
