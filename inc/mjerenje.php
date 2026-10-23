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
 * 6. GA4 begin_checkout na stranici plaćanja. Site Kit šalje samo add_to_cart i
 *    purchase, pa se početak plaćanja u GA4 nije bilježio.
 * 7. GA4 view_item i add_to_cart na stranici artikla. Site Kit add_to_cart šalje samo
 *    za dugmad u listama artikala; „Dodaj u korpu“ na samom artiklu (WoodMart AJAX) nije
 *    slao ništa, pa GA4 nije vidio dodavanja u korpu (analiza 23.10.2026).
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
		<?php if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) : ?>
		dogadjaj('kupovina');
		<?php endif; ?>
	}
	// Događaji za Clarity (smart events i levak): dodavanje u korpu, kontakt, kupovina.
	function dogadjaj(ime) {
		try { if (typeof window.clarity === 'function') { window.clarity('event', ime); } } catch (e) {}
	}
	function vezi() {
		if (window.jQuery) {
			window.jQuery(document.body).on('added_to_cart', function () { dogadjaj('dodaj_u_korpu'); });
		}
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', vezi); } else { vezi(); }
	document.addEventListener('click', function (e) {
		var t = e.target && e.target.closest ? e.target : null;
		if (!t) { return; }
		if (t.closest('.single_add_to_cart_button:not(.disabled), .add_to_cart_button')) { dogadjaj('klik_dodaj_u_korpu'); }
		var a = t.closest('a[href]');
		if (a && (a.getAttribute('href') || '').replace(/[^0-9]/g, '').indexOf('67030655') !== -1) { dogadjaj('kontakt'); }
	}, true);
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
 * Statistika bez traženja pristanka (22.10.2026, Marko: „ublažiti za Crnu Goru,
 * statistika bez traženja pristanka“). Posjetilac koji još nije birao dobija
 * pristanak samo za statistiku (GA4, Clarity); marketing (Meta piksel, Google Ads)
 * i dalje čeka „Prihvati“. „Odbij“ ili isključena Statistika u Podešavanjima
 * upisuju „deny“ i to se poštuje. Site Kit (Consent Mode) i Clarity čitaju
 * pristanak preko WP Consent API-ja, a Complianz ga čuva u kolačiću cmplz_statistics.
 * Povratak: ukloniti ovaj blok (posjetioci kojima je već upisano „allow“ ostaju
 * takvi dok ne promijene izbor ili kolačić ne istekne).
 */
add_action(
	'wp_head',
	function () {
		if ( is_admin() || ! function_exists( 'wp_has_consent' ) || ! defined( 'CMPLZ_VERSION' ) ) {
			return;
		}
		?>
<script id="lm-statistika-podrazumijevano">
(function () {
	// Complianz čuva izbor u cmplz_statistics, a WP Consent API (iz kojeg čitaju
	// Site Kit i Clarity) u wp_consent_statistics. Postavljamo oba, osim ako je
	// posjetilac statistiku odbio.
	function vrijednost(ime) {
		var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + ime + '=([^;]*)'));
		return m ? m[1] : null;
	}
	var c = vrijednost('cmplz_statistics');
	var w = vrijednost('wp_consent_statistics');
	if (c === 'deny' || w === 'deny') { return; }
	var trajanje = ';path=/;max-age=31536000;SameSite=Lax';
	if (c === null) { document.cookie = 'cmplz_statistics=allow' + trajanje; }
	if (w === null) { document.cookie = 'wp_consent_statistics=allow' + trajanje; }
})();
</script>
		<?php
	},
	0
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

/*
 * 6. GA4 begin_checkout na stranici plaćanja.
 * Ide preko istog Google taga koji učitava Site Kit (gtag, uz Consent Mode), sa artiklima
 * u obliku kao kod Site Kit-a: ID glavnog proizvoda, naziv, kategorija, varijanta, cijena,
 * količina. Jednom po sadržaju korpe u sesiji pregledača, da se osvježavanje stranice ne
 * broji ponovo. Za prijavljene administratore Site Kit ne učitava gtag, pa se ni ovo ne šalje.
 */
add_action(
	'woocommerce_after_checkout_form',
	function () {
		if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}

		$cist_tekst = function ( $tekst ) {
			return html_entity_decode( wp_strip_all_tags( (string) $tekst ), ENT_QUOTES, 'UTF-8' );
		};

		$artikli = array();
		$vrijednost = 0.0;
		foreach ( WC()->cart->get_cart() as $stavka ) {
			$proizvod = isset( $stavka['data'] ) ? $stavka['data'] : null;
			if ( ! $proizvod instanceof WC_Product ) {
				continue;
			}
			$glavni_id = $proizvod->is_type( 'variation' ) ? $proizvod->get_parent_id() : $proizvod->get_id();
			$kolicina  = max( 1, (int) $stavka['quantity'] );
			$cijena    = round( (float) $proizvod->get_price(), 2 );

			$artikal = array(
				'item_id'   => (string) $glavni_id,
				'item_name' => $cist_tekst( $proizvod->get_title() ),
				'price'     => $cijena,
				'quantity'  => $kolicina,
			);
			$kategorije = wc_get_product_terms( $glavni_id, 'product_cat', array( 'number' => 1 ) );
			if ( ! empty( $kategorije ) ) {
				$artikal['item_category'] = $cist_tekst( $kategorije[0]->name );
			}
			if ( $proizvod->is_type( 'variation' ) ) {
				$opis_varijante = array();
				foreach ( (array) $proizvod->get_attributes() as $atribut => $vrsta ) {
					if ( '' !== (string) $vrsta ) {
						$opis_varijante[] = str_replace( 'attribute_', '', (string) $atribut ) . ': ' . $vrsta;
					}
				}
				if ( $opis_varijante ) {
					$artikal['item_variant'] = $cist_tekst( implode( ', ', $opis_varijante ) );
				}
			}

			$artikli[]   = $artikal;
			$vrijednost += $cijena * $kolicina;
		}
		if ( ! $artikli ) {
			return;
		}

		$podaci = array(
			'currency' => get_woocommerce_currency(),
			'value'    => round( $vrijednost, 2 ),
			'items'    => $artikli,
		);
		$kuponi = WC()->cart->get_applied_coupons();
		if ( $kuponi ) {
			$podaci['coupon'] = implode( ',', $kuponi );
		}
		?>
<script id="lm-ga4-pocetak-placanja">
(function () {
	var podaci = <?php echo wp_json_encode( $podaci ); ?>;
	var kljuc = <?php echo wp_json_encode( 'lm_bc_' . WC()->cart->get_cart_hash() ); ?>;
	try { if (window.sessionStorage && window.sessionStorage.getItem(kljuc)) { return; } } catch (e) {}
	function posalji() {
		if (typeof window.gtag !== 'function') { return false; }
		window.gtag('event', 'begin_checkout', podaci);
		try { window.sessionStorage.setItem(kljuc, '1'); } catch (e) {}
		return true;
	}
	if (!posalji()) { window.addEventListener('load', posalji); }
})();
</script>
		<?php
	}
);

/*
 * 7. GA4 view_item i add_to_cart na stranici artikla (Marko, 23.10.2026: „odradi b“).
 * Site Kit šalje add_to_cart samo kad dugme ima data-product_id (liste artikala). Na samom
 * artiklu WoodMart dodaje u korpu AJAX-om i događaj added_to_cart dobija dugme bez
 * data-product_id, pa Site Kit ne šalje ništa (provjereno 23.10.). view_item Site Kit ne šalje.
 * Artikal je u istom obliku kao kod begin_checkout (6): ID glavnog proizvoda, naziv, kategorija,
 * varijanta (atribut: vrijednost), cijena sa PDV-om, količina. Ide preko Site Kit-ovog gtag-a
 * (Consent Mode važi i ovdje); za prijavljene administratore gtag se ne učitava.
 */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		$proizvod = wc_get_product( get_queried_object_id() );
		if ( ! $proizvod instanceof WC_Product ) {
			return;
		}

		$cist_tekst = function ( $tekst ) {
			return html_entity_decode( wp_strip_all_tags( (string) $tekst ), ENT_QUOTES, 'UTF-8' );
		};

		$artikal = array(
			'item_id'   => (string) $proizvod->get_id(),
			'item_name' => $cist_tekst( $proizvod->get_title() ),
			'price'     => round( (float) wc_get_price_to_display( $proizvod ), 2 ),
		);
		$kategorije = wc_get_product_terms( $proizvod->get_id(), 'product_cat', array( 'number' => 1 ) );
		if ( ! empty( $kategorije ) ) {
			$artikal['item_category'] = $cist_tekst( $kategorije[0]->name );
		}
		$brend = wp_get_post_terms( $proizvod->get_id(), 'product_brand', array( 'fields' => 'names' ) );
		if ( $brend && ! is_wp_error( $brend ) ) {
			$artikal['item_brand'] = $cist_tekst( $brend[0] );
		}
		?>
<script id="lm-ga4-artikal">
(function () {
	var artikal = <?php echo wp_json_encode( $artikal ); ?>;
	var valuta = <?php echo wp_json_encode( get_woocommerce_currency() ); ?>;
	var varijanta = null;
	function kopija(o) { var n = {}; for (var k in o) { if (Object.prototype.hasOwnProperty.call(o, k)) { n[k] = o[k]; } } return n; }
	function zaokruzi(x) { return Math.round(x * 100) / 100; }
	function posalji(ime, podaci) {
		if (typeof window.gtag !== 'function') { return false; }
		try { window.gtag('event', ime, podaci); } catch (e) {}
		return true;
	}

	// Pregled artikla, jednom po učitavanju stranice.
	var stavka = kopija(artikal);
	stavka.quantity = 1;
	var pregled = { currency: valuta, value: artikal.price, items: [stavka] };
	if (!posalji('view_item', pregled)) {
		window.addEventListener('load', function () { posalji('view_item', pregled); });
	}

	// Dodavanje u korpu sa samog artikla (dugme u formi ili ljepljiva traka, koja klikne isto dugme).
	var vezano = false;
	function vezi() {
		var $ = window.jQuery;
		if (vezano || !$) { return; }
		vezano = true;
		$(document.body).on('found_variation', 'form.variations_form', function (e, v) { varijanta = v || null; });
		$(document.body).on('reset_data', 'form.variations_form', function () { varijanta = null; });
		$(document.body).on('added_to_cart', function (e, fragmenti, hash, $dugme) {
			// Dugmad u listama (data-product_id) šalje Site Kit; ovdje samo forma artikla.
			if (!$dugme || !$dugme.length || $dugme.data('product_id')) { return; }
			var $forma = $dugme.closest('form.cart');
			if (!$forma.length) { return; }
			var kolicina = parseInt($forma.find('input.qty').val(), 10) || 1;
			var s = kopija(artikal);
			s.quantity = kolicina;
			if (varijanta && !isNaN(parseFloat(varijanta.display_price))) {
				s.price = zaokruzi(parseFloat(varijanta.display_price));
			}
			var opis = [];
			$forma.find('select[name^="attribute_"]').each(function () {
				if (this.value) { opis.push(this.name.replace('attribute_', '') + ': ' + this.value); }
			});
			if (opis.length) { s.item_variant = opis.join(', '); }
			posalji('add_to_cart', { currency: valuta, value: zaokruzi(s.price * kolicina), items: [s] });
		});
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', vezi); } else { vezi(); }
	window.addEventListener('load', vezi);
})();
</script>
		<?php
	},
	60
);
