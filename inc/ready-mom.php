<?php
/**
 * Lady Mom – Ready Mom, edukativni program za trudnice (Markova odluka 19.10.2026).
 *
 * - Artikal (virtuelni, 129 €) se prodaje preko sajta; nije iz ERP-a.
 *   ID artikla: opcija `lm_ready_mom_proizvod`, stranica: opcija `lm_ready_mom_stranica`.
 * - Stranica škole (od 21.10.2026 /skola-za-trudnice/, ranije /ready-mom/) ima opis, slike, prijavu (artikal ide pravo na plaćanje)
 *   i dugmad za poziv, WhatsApp i Viber. Sadržaj je u kratkom kodu [lm_ready_mom].
 * - Stranica artikla vodi na ovu stranicu, da kupac uvijek vidi cijeli opis.
 * - Sve „škola“ veze na sajtu (traka na vrhu, mobilni meni, početna, slajd, O nama,
 *   Kontakt) vode na stranicu škole. Stare adrese preusmjerava inc/seo-dorade.php.
 * - 21.10.2026 (SEO): H1 „Škola za trudnice Ready Mom“, sadržaj članka o školi spojen ovdje, česta pitanja.
 * - 23.10.2026 (Marko): program traje 2 nedjelje, 6 predavanja, 6 stručnjaka (ranije 4 / 8 / 6); predavači po imenu
 *   i teme iz Natalijinog rasporeda (bez datuma), pokloni i pogodnosti partnera (lm_rm_predavanja(), lm_rm_partneri()).
 * - 22.10.2026 (AI pretrage): „Šta je Ready Mom?“, „Program“, „Česta pitanja“ i „Prijavite se“ počinju
 *   rečenicom koja sama kaže šta, koliko, gdje i koliko košta (klasa .lm-rm-uvod), da se može citirati
 *   i izvan stranice; „Kada upisati“ počinje cijelom rečenicom.
 */

defined( 'ABSPATH' ) || exit;

const LM_RM_TEL     = '+38267030655';
const LM_RM_TEL_TXT = '067 030 655';

function lm_rm_proizvod_id() {
	return (int) get_option( 'lm_ready_mom_proizvod' );
}

/** Link koji stavlja program u korpu i otvara plaćanje. */
function lm_rm_prijava_url() {
	$id = lm_rm_proizvod_id();
	$placanje = wc_get_page_permalink( 'checkout' );
	if ( ! $placanje || false !== strpos( $placanje, 'ready-mom' ) ) {
		$placanje = home_url( '/placanje/' );
	}
	return $id ? add_query_arg( 'add-to-cart', $id, $placanje ) : '#';
}

// Stranica artikla → stranica programa.
add_action( 'template_redirect', function () {
	$id = lm_rm_proizvod_id();
	$st = (int) get_option( 'lm_ready_mom_stranica' );
	if ( $id && $st && is_singular( 'product' ) && get_queried_object_id() === $id ) {
		wp_safe_redirect( get_permalink( $st ), 301 );
		exit;
	}
} );

/**
 * Predavanja (Natalijin raspored oktobarske grupe, 22.10.2026; datumi se ne prikazuju).
 * [tema, predavač, zvanje, struka (kratko), pitanja na koja predavanje odgovara, ID slike predavača]
 * Slike su isječene sa Natalijinih letaka (23.10.2026).
 */
function lm_rm_predavanja() {
	return array(
		array( 'Spremna za porođaj: šta se dešava?', 'Prim. dr Gordana Globarević Vukćević', 'specijalista ginekologije i akušerstva', 'Ginekolog', array( 'Da li trudnoća teče kako treba i kada treba da se zabrinete?', 'Kako prepoznati početak porođaja?', 'Prirodni porođaj, carski rez i epidural: kada i kako?', 'Kako donositi informisane odluke sa ljekarima?' ) , 51549 ),
		array( 'Od prvog bola do bebe u naručju', 'Tanja Lazarević', 'ginekološko-akušerska sestra, babica', 'Babica', array( 'Kada je porođaj zaista počeo i kada krenuti u porodilište?', 'Šta se dešava kad uđete u porodilište, korak po korak?', 'Kako babica vodi porođaj i pomaže bebi da dođe na svijet?', 'Šta očekivati u prvim satima nakon rođenja?' ) , 51550 ),
		array( 'Zlatni sat i dojenje bez mitova', 'Milica Đoković Vojinović', 'pedijatrijska sestra', 'Pedijatrijska sestra', array( 'Zašto je „zlatni sat“ važan za bebu i mamu?', 'Pravilan hvat: kako znati da beba dobro sisa?', 'Kako prepoznati da beba dobija dovoljno mlijeka?', 'Šta raditi kod bola, ragada ili zastoja mlijeka?' ) , 51551 ),
		array( 'Novorođenče okom pedijatra: šta je normalno?', 'Slađana Račić', 'specijalista pedijatrije', 'Pedijatar', array( 'Koji su obavezni pregledi i vakcine u prvim mjesecima?', 'Kako izgleda normalan rast, san i ishrana bebe?', 'Koji znaci traže hitnu pedijatrijsku pomoć?' ) , 51552 ),
		array( 'Prvi dani sa bebom kod kuće', 'Dragana Marković', 'patronažna sestra', 'Patronažna sestra', array( 'Kako uspostaviti rutinu u prvim nedjeljama?', 'Šta patronažna sestra provjerava u kućnim posjetama?', 'Kako pronaći mir i sigurnost u novoj svakodnevici?', 'Šta je zaista važno u njezi bebe i mame?' ) , 51553 ),
		array( 'Kako ostati tim: nas dvoje i beba', 'mr Željka Stevović', 'psihoterapeut transakcione analize', 'Psihoterapeut', array( 'Zašto mama i tata ne prolaze isti put ka roditeljstvu?', 'Kako tata može biti prava podrška tokom trudnoće?', 'Kako ostati tim kad dođu umor i stres?', 'Šta se dešava sa odnosom kad beba dođe kući?' ) , 51554 ),
	);
}

/** Pokloni i pogodnosti partnera za polaznice (Natalijin pregled „Svi pokloni naših partnera“). [partner, pogodnost] */
function lm_rm_partneri() {
	return array(
		array( 'Lady Mom', '20 % popusta na kupovinu u prodavnici' ),
		array( 'Vegapharm', 'savjeti farmaceuta o gvožđu i njezi porodilje i bebe, 10 % popusta na 10 kupovina' ),
		array( 'NLB banka', 'poklon predavanje „Medena štednja“ i posebne pogodnosti banke' ),
		array( 'Uniqa osiguranje', '10–30 % popusta na sve vrste osiguranja' ),
		array( 'Bio Save', '500 € popusta na pakete čuvanja matičnih ćelija' ),
		array( 'MediKid', 'prvi pedijatrijski pregled bebe besplatno' ),
		array( 'Oblak i sunce', '10–20 % popusta u prodavnici' ),
		array( 'Viva Walk', '15 % popusta na barefoot obuću' ),
		array( 'Origo', '10 % popusta na prvu kupovinu' ),
		array( 'Anastasija Milić', '20 % popusta na trudničko i 10 % na newborn fotografisanje' ),
	);
}

/**
 * Česta pitanja na stranici škole (i u podacima za Google, inc/seo-dorade.php).
 * Samo činjenice koje su već potvrđene na stranici; termin, cijena i mjesto iz istog izvora.
 */
function lm_rm_pitanja() {
	$id     = lm_rm_proizvod_id();
	$p      = $id ? wc_get_product( $id ) : null;
	$cijena = $p ? wp_strip_all_tags( wc_price( wc_get_price_to_display( $p ) ) ) : '129,00 €';
	return array(
		array( 'Koliko traje škola za trudnice Ready Mom?', 'Dvije nedjelje. Za to vrijeme je šest predavanja, po tri nedjeljno, uveče od 18:30 do 20:00.' ),
		array( 'Ko drži predavanja?', 'Šest stručnjaka: ginekolog prim. dr Gordana Globarević Vukćević, babica Tanja Lazarević, pedijatrijska sestra Milica Đoković Vojinović, pedijatar Slađana Račić, patronažna sestra Dragana Marković i psihoterapeut mr Željka Stevović. Svaku temu objašnjava osoba koja se njome bavi svaki dan.' ),
		array( 'Šta polaznice dobijaju od partnera programa?', 'Poklone i popuste partnera: 20 % popusta u Lady Mom prodavnici, besplatan prvi pedijatrijski pregled bebe u MediKid-u, 500 € popusta na čuvanje matičnih ćelija u Bio Save-u, popuste Uniqa osiguranja, Viva Walk-a, Oblaka i sunca, Origa i fotografkinje Anastasije Milić, savjete farmaceuta Vegapharma i poklon predavanje NLB banke „Medena štednja“.' ),
		array( 'Kada je najbolje upisati školu za trudnice?', 'U drugoj polovini trudnoće, otprilike od 25. do 36. nedjelje.' ),
		array( 'Može li partner da dođe na predavanja?', 'Da, partneri su dobrodošli.' ),
		array( 'Mogu li pratiti školu ako nisam iz Podgorice?', 'Da. Program možete pratiti online, iz bilo kog grada.' ),
		array( 'Gdje se održavaju predavanja uživo?', 'U prostoru Family Nest – Centar za trudnoću i roditeljstvo, Branka Deletića bb (naselje New City), Podgorica.' ),
		array( 'Koliko košta i kako se plaća?', 'Cijena programa je ' . $cijena . '. Možete platiti karticom pri prijavi na sajtu ili gotovinom.' ),
		array( 'Kada počinje naredna grupa?', 'Poslije prijave vas zovemo sa terminom naredne grupe. Za termine možete nas pozvati ili pisati na WhatsApp i Viber: ' . LM_RM_TEL_TXT . '.' ),
	);
}

add_shortcode( 'lm_ready_mom', function () {
	$id = lm_rm_proizvod_id();
	$p  = $id ? wc_get_product( $id ) : null;
	$cijena = $p ? wc_price( wc_get_price_to_display( $p ) ) : '129,00 €';
	$poruka = rawurlencode( 'Zdravo! Zanima me Ready Mom edukativni program za trudnice.' );
	$wa     = 'https://wa.me/' . ltrim( LM_RM_TEL, '+' ) . '?text=' . $poruka;
	$vb     = 'viber://chat?number=' . rawurlencode( LM_RM_TEL );
	$tel    = 'tel:' . LM_RM_TEL;
	$slika  = wp_get_attachment_image( 35725, 'large', false, array( 'class' => 'lm-rm-foto', 'loading' => 'eager', 'alt' => 'Predavanje u školi za trudnice Ready Mom u Podgorici' ) );
	$prijava = esc_url( lm_rm_prijava_url() );

	$kontakt = '<div class="lm-rm-kontakt">'
		. '<a class="lm-rm-k lm-rm-k-tel" href="' . esc_url( $tel ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg><span>Pozovi ' . esc_html( LM_RM_TEL_TXT ) . '</span></a>'
		. '<a class="lm-rm-k lm-rm-k-wa" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1.5A10.4 10.4 0 0 0 3 17.2L1.5 22.5l5.4-1.4A10.5 10.5 0 1 0 12 1.5zm0 19a8.6 8.6 0 0 1-4.4-1.2l-.3-.2-3.2.8.9-3.1-.2-.3A8.6 8.6 0 1 1 12 20.5zm4.7-6.4c-.3-.1-1.5-.7-1.8-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.6.1a7 7 0 0 1-3.5-3c-.3-.5.3-.4.7-1.3.1-.2 0-.3 0-.5l-.8-2c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7.5-.1 1.5-.6 1.8-1.2.2-.6.2-1.1.1-1.2l-.5-.4z"/></svg><span>WhatsApp</span></a>'
		. '<a class="lm-rm-k lm-rm-k-vb" href="' . esc_url( $vb, array( 'viber', 'https' ) ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3c-4.4 0-8 2.9-8 7.3 0 2.5 1.1 4.6 3 6V21l3.2-2.3c.6.1 1.2.2 1.8.2 4.4 0 8-2.9 8-7.6S16.4 3 12 3z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M9.6 8.4c.3 1.7 1.5 3.5 3.5 4.7.5.3 1 .1 1.3-.3l.3-.6-1.3-.8-.6.5c-.7-.4-1.3-1-1.7-1.7l.5-.6-.8-1.3-.6.3c-.4.2-.6.6-.6 1z"/></svg><span>Viber</span></a>'
		. '</div>';

	ob_start();
	?>
<div class="lm-rm">
	<section class="lm-rm-hero">
		<div class="lm-rm-hero-tekst">
			<p class="lm-rm-nad">Podgorica i online</p>
			<h1 class="lm-rm-naslov">Škola za trudnice Ready Mom</h1>
			<p class="lm-rm-pod">Priprema za porođaj i prve mjesece sa bebom: šest predavanja sa šest stručnjaka za dvije nedjelje, uživo u Podgorici ili online.</p>
			<ul class="lm-rm-cinjenice">
				<li><strong>2</strong> nedjelje</li>
				<li><strong>6</strong> predavanja</li>
				<li><strong>6</strong> stručnjaka</li>
				<li>uživo ili <strong>online</strong></li>
			</ul>
			<div class="lm-rm-kupi">
				<span class="lm-rm-cijena"><?php echo wp_kses_post( $cijena ); ?></span>
				<a class="lm-rm-dugme" href="<?php echo $prijava; // phpcs:ignore ?>">Prijavi se i plati</a>
			</div>
			<p class="lm-rm-mala">Plaćanje karticom ili gotovinom. Poslije prijave vas zovemo sa terminom naredne grupe.</p>
			<?php echo $kontakt; // phpcs:ignore ?>
		</div>
		<div class="lm-rm-hero-slika"><?php echo $slika; // phpcs:ignore ?></div>
	</section>

	<section class="lm-rm-blok">
		<h2>Šta je Ready Mom?</h2>
		<p class="lm-rm-uvod">Ready Mom je škola za trudnice u Podgorici koja vas priprema za porođaj i prve mjesece sa bebom: dvije nedjelje, šest predavanja i šest stručnjaka, uživo ili online.</p>
		<p>Trudnoća donosi mnogo pitanja. Kako da znam da je porođaj počeo? Šta da ponesem u porodilište? Kako da podojim bebu i šta da radim kad beba plače, a ne znam zašto? U Ready Mom školi za trudnice na ta pitanja odgovaraju stručnjaci koji se time bave svaki dan.</p>
		<p>Program vas vodi redom: od posljednjih nedjelja trudnoće, preko porođaja, do prvih dana i nedjelja kod kuće sa bebom. Puno je praktičnih savjeta, primjera iz prakse i vremena za vaša pitanja.</p>
	</section>

	<section class="lm-rm-blok lm-rm-sir">
		<h2>Program: ko drži predavanja i šta se uči</h2>
		<p class="lm-rm-uvod">Ready Mom traje dvije nedjelje i ima šest predavanja, uživo u Podgorici ili online, a drži ih šest stručnjaka: ginekolog, babica, pedijatrijska sestra, pedijatar, patronažna sestra i psihoterapeut.</p>
		<ol class="lm-rm-program">
			<?php foreach ( lm_rm_predavanja() as $i => $pr ) : ?>
			<li>
				<p class="lm-rm-br"><?php echo esc_html( ( $i + 1 ) . '. predavanje · ' . $pr[3] ); ?></p>
				<h3><?php echo esc_html( $pr[0] ); ?></h3>
				<div class="lm-rm-ko">
					<?php echo ! empty( $pr[5] ) ? wp_get_attachment_image( $pr[5], 'thumbnail', false, array( 'class' => 'lm-rm-lice', 'loading' => 'lazy', 'alt' => $pr[1] ) ) : ''; // phpcs:ignore ?>
					<p><strong><?php echo esc_html( $pr[1] ); ?></strong><br><?php echo esc_html( $pr[2] ); ?></p>
				</div>
				<ul><?php foreach ( $pr[4] as $pit ) : ?><li><?php echo esc_html( $pit ); ?></li><?php endforeach; ?></ul>
			</li>
			<?php endforeach; ?>
		</ol>
		<p class="lm-rm-napomena">Svako predavanje traje sat i po, od 18:30 do 20:00, a na kraju je vrijeme za vaša pitanja.</p>
	</section>

	<section class="lm-rm-blok lm-rm-sir lm-rm-pokloni">
		<h2>Pokloni i pogodnosti partnera</h2>
		<p class="lm-rm-uvod">Uz program, polaznice Ready Mom škole dobijaju poklone i popuste partnera programa.</p>
		<ul class="lm-rm-partneri">
			<?php foreach ( lm_rm_partneri() as $pa ) : ?>
			<li><strong><?php echo esc_html( $pa[0] ); ?></strong><span><?php echo esc_html( $pa[1] ); ?></span></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="lm-rm-dvije">
		<div class="lm-rm-kartica">
			<h2>Uživo u Podgorici ili online</h2>
			<p>Predavanja se održavaju uživo u prostoru Family Nest – Centar za trudnoću i roditeljstvo, Branka Deletića bb (naselje New City), Podgorica. Ako ste iz drugog grada, imate obaveze ili vam je lakše od kuće, program možete pratiti online. Partneri su dobrodošli: porođaj i prvi dani sa bebom su timski posao, pa je dobro da oboje znate šta vas čeka.</p>
		</div>
		<div class="lm-rm-kartica">
			<h2>Kada upisati školu za trudnice</h2>
			<p>Školu za trudnice najbolje je upisati u drugoj polovini trudnoće, otprilike od 25. do 36. nedjelje. Tada vam je sve aktuelno, a imate vremena da naučeno primijenite: da <a href="<?php echo esc_url( home_url( '/sta-spakovati-u-porodiliste/' ) ); ?>">spakujete torbu za porodilište</a>, nabavite <a href="<?php echo esc_url( home_url( '/kategorija/prodavnica/mame/oprema/dojenje/' ) ); ?>">opremu za dojenje</a> i pripremite dom za bebu. Grupe se popunjavaju, pa se prijavite na vrijeme.</p>
		</div>
	</section>

	<section class="lm-rm-blok lm-rm-pitanja">
		<h2>Česta pitanja o školi za trudnice</h2>
		<p class="lm-rm-uvod">Škola za trudnice Ready Mom košta <?php echo wp_kses_post( wp_strip_all_tags( $cijena ) ); ?> za cijeli program od dvije nedjelje i šest predavanja, a predavanja se drže uživo u prostoru Family Nest u Podgorici (Branka Deletića bb) ili online.</p>
		<?php foreach ( lm_rm_pitanja() as $q ) : ?>
		<details>
			<summary><?php echo esc_html( $q[0] ); ?></summary>
			<p><?php echo wp_kses_post( $q[1] ); ?></p>
		</details>
		<?php endforeach; ?>
	</section>

	<section class="lm-rm-kraj">
		<h2>Prijavite se za narednu grupu</h2>
		<p class="lm-rm-uvod">Prijava za školu Ready Mom košta <?php echo wp_kses_post( wp_strip_all_tags( $cijena ) ); ?> i plaća se karticom na sajtu ili gotovinom, a poslije prijave vas zovemo sa terminom naredne grupe.</p>
		<div class="lm-rm-kupi lm-rm-kupi-centar">
			<span class="lm-rm-cijena"><?php echo wp_kses_post( $cijena ); ?></span>
			<a class="lm-rm-dugme" href="<?php echo $prijava; // phpcs:ignore ?>">Prijavi se i plati</a>
		</div>
		<p class="lm-rm-mala">Imate pitanje o terminima ili programu? Pišite nam ili nas pozovite.</p>
		<?php echo $kontakt; // phpcs:ignore ?>
	</section>
</div>
	<?php
	return ob_get_clean();
} );

add_action( 'wp_head', function () {
	$st = (int) get_option( 'lm_ready_mom_stranica' );
	if ( ! $st || ! is_page( $st ) ) {
		return;
	}
	?>
<style id="lm-ready-mom">
.lm-rm{max-width:1100px;margin:0 auto;padding:10px 0 30px;color:#7a6250}
.lm-rm h1,.lm-rm h2,.lm-rm h3{color:#6d4e39}
.lm-rm-hero{display:grid;grid-template-columns:1.05fr .95fr;gap:44px;align-items:center;padding:34px 0 40px}
.lm-rm-nad{margin:0 0 10px;font-size:13px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#a05c57}
.lm-rm-naslov{margin:0 0 12px;font-size:44px;line-height:1.05}
.lm-rm-pod{margin:0 0 20px;font-size:17px;line-height:1.6}
.lm-rm-cinjenice{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 24px;padding:0;list-style:none}
.lm-rm-cinjenice li{margin:0;padding:7px 14px;border-radius:99px;background:#fdf2ec;font-size:14px}
.lm-rm-cinjenice strong{color:#6d4e39}
.lm-rm-kupi{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
.lm-rm-kupi-centar{justify-content:center}
.lm-rm-cijena{font-size:30px;font-weight:700;color:#a05c57}
.lm-rm-cijena .woocommerce-price-suffix{display:none}
.lm-rm-cijena .amount,.lm-rm-cijena bdi{font-size:30px!important;font-weight:700!important;color:#a05c57!important}
.lm-rm-dugme{display:inline-flex;align-items:center;justify-content:center;min-height:50px;padding:12px 28px;border-radius:6px;background:#a05c57;color:#fff!important;font-size:14px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;text-decoration:none!important}
.lm-rm-dugme:hover{background:#8a4b47}
.lm-rm-mala{margin:12px 0 16px;font-size:14px}
.lm-rm-kontakt{display:flex;flex-wrap:wrap;gap:10px}
.lm-rm-k{display:inline-flex;align-items:center;gap:8px;min-height:44px;padding:9px 16px;border:1.5px solid #cf9b95;border-radius:99px;color:#6d4e39!important;font-size:14px;font-weight:600;text-decoration:none!important;background:#fff}
.lm-rm-k:hover{background:#fdf2ec}
.lm-rm-k svg{width:19px;height:19px;fill:currentColor}
.lm-rm-k-wa svg{color:#25a35a}.lm-rm-k-vb svg{color:#7360f2}.lm-rm-k-tel svg{color:#a05c57}
.lm-rm-hero-slika img{width:100%;height:auto;aspect-ratio:4/3;object-fit:cover;border-radius:220px 220px 14px 14px}
.lm-rm-blok{max-width:760px;margin:0 auto;padding:26px 0}
.lm-rm-blok.lm-rm-sir{max-width:960px}.lm-rm-sir>h2,.lm-rm-sir>.lm-rm-uvod{max-width:760px}
.lm-rm-blok h2,.lm-rm-kraj h2{font-size:28px;margin:0 0 14px}
.lm-rm-blok p{font-size:16px;line-height:1.7;margin:0}
.lm-rm-blok .lm-rm-uvod{margin:0 0 16px}
.lm-rm-kraj .lm-rm-uvod{max-width:620px;margin:0 auto 18px;font-size:16px;line-height:1.7}
.lm-rm-program{margin:0;padding:0;list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:14px}
.lm-rm-program>li{margin:0;padding:18px 20px;border:1px solid #f3e7e0;border-radius:14px;background:#fff}
.lm-rm-br{margin:0 0 4px!important;font-size:12px!important;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#a05c57}
.lm-rm-program h3{margin:0 0 6px;font-size:19px;line-height:1.3}
.lm-rm-ko{display:flex;align-items:center;gap:12px;margin:0 0 10px}.lm-rm-ko p{margin:0!important;font-size:15px!important;line-height:1.45!important}
.lm-rm-lice{width:64px!important;height:64px!important;flex:0 0 64px;border-radius:50%;object-fit:cover}
.lm-rm-program ul{margin:0;padding-left:18px}
.lm-rm-program ul li{margin:0 0 3px;font-size:14.5px;line-height:1.5}
.lm-rm-blok .lm-rm-napomena{margin-top:14px;font-size:14.5px}
.lm-rm-partneri{margin:0;padding:0;list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:10px}
.lm-rm-partneri li{margin:0;padding:12px 16px;border-radius:12px;background:#fdf2ec;display:flex;flex-direction:column;gap:2px;font-size:14.5px;line-height:1.5}
.lm-rm-partneri strong{color:#6d4e39;font-size:15.5px}
.lm-rm-predavaci{margin:0;padding:0;list-style:none;display:grid;gap:10px}
.lm-rm-predavaci li{margin:0;padding:14px 18px;border:1px solid #f3e7e0;border-radius:12px;display:flex;flex-direction:column;gap:3px}
.lm-rm-predavaci strong{color:#6d4e39;font-size:16px}
.lm-rm-dvije{display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:960px;margin:20px auto}
.lm-rm-kartica{padding:24px 26px;background:#fdf2ec;border-radius:14px}
.lm-rm-kartica h3{margin:0 0 8px;font-size:21px}
.lm-rm-kartica p{margin:0;line-height:1.65}
.lm-rm-kartica h2{margin:0 0 8px;font-size:21px}.lm-rm-kartica a,.lm-rm-pitanja a{color:#6d4e39;text-decoration:underline;text-underline-offset:3px}.lm-rm-blok p+p{margin-top:12px}
.lm-rm-pitanja details{border-bottom:1px solid #f3e7e0;padding:14px 0}.lm-rm-pitanja summary{cursor:pointer;font-weight:700;color:#6d4e39;font-size:16px;list-style:none;display:flex;justify-content:space-between;gap:14px}.lm-rm-pitanja summary::-webkit-details-marker{display:none}.lm-rm-pitanja summary:after{content:"+";color:#a05c57;font-size:20px;line-height:1}.lm-rm-pitanja details[open] summary:after{content:"–"}.lm-rm-pitanja details p{margin:8px 0 0}
.lm-rm-kraj{margin:34px auto 0;padding:36px 24px;text-align:center;border-top:1px solid #f3e7e0}
.lm-rm-kraj .lm-rm-kontakt{justify-content:center}
@media (max-width:900px){.lm-rm-hero{grid-template-columns:1fr;gap:24px;padding-top:18px}.lm-rm-hero-slika{order:-1}.lm-rm-hero-slika img{border-radius:160px 160px 12px 12px}.lm-rm-naslov{font-size:34px}.lm-rm-dvije{grid-template-columns:1fr}}
@media (max-width:700px){.lm-rm-program,.lm-rm-partneri{grid-template-columns:1fr}}
@media (max-width:520px){.lm-rm-dugme{width:100%}.lm-rm-kupi{gap:10px}.lm-rm-k{flex:1 1 auto;justify-content:center}.lm-rm-blok h2,.lm-rm-kraj h2{font-size:23px}}
/* Telefon (pregled 21.10., r11): cijena i „Prijavi se i plati“ u prvom ekranu – niža slika, dugme prije činjenica */
@media (max-width:520px){.lm-rm{padding-top:0}.lm-rm-hero{gap:14px;padding:4px 0 26px}.lm-rm-hero-slika img{aspect-ratio:16/9;border-radius:120px 120px 12px 12px}.lm-rm-hero-tekst{display:flex;flex-direction:column}.lm-rm-hero-tekst>.lm-rm-nad{order:1;margin-bottom:6px}.lm-rm-hero-tekst>.lm-rm-naslov{order:2;font-size:30px;margin-bottom:8px}.lm-rm-hero-tekst>.lm-rm-pod{order:3;font-size:16px;margin-bottom:14px}.lm-rm-hero-tekst>.lm-rm-kupi{order:4}.lm-rm-hero-tekst>.lm-rm-mala{order:5;margin:8px 0 14px}.lm-rm-hero-tekst>.lm-rm-cinjenice{order:6;margin-bottom:16px}.lm-rm-hero-tekst>.lm-rm-kontakt{order:7}}
</style>
	<?php
}, 46 );
