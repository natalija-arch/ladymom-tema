<?php
/**
 * Lady Mom – Ready Mom, edukativni program za trudnice (Markova odluka 19.10.2026).
 *
 * - Artikal (virtuelni, 129 €) se prodaje preko sajta; nije iz ERP-a.
 *   ID artikla: opcija `lm_ready_mom_proizvod`, stranica: opcija `lm_ready_mom_stranica`.
 * - Stranica /ready-mom/ ima opis, slike, prijavu (artikal ide pravo na plaćanje)
 *   i dugmad za poziv, WhatsApp i Viber. Sadržaj je u kratkom kodu [lm_ready_mom].
 * - Stranica artikla vodi na ovu stranicu, da kupac uvijek vidi cijeli opis.
 * - Sve „škola“ veze na sajtu (traka na vrhu, mobilni meni, početna, slajd, O nama,
 *   Kontakt, članak bloga) vode na /ready-mom/ umjesto na poziv.
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
	return $id ? add_query_arg( 'add-to-cart', $id, wc_get_checkout_url() ) : '#';
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
			<p class="lm-rm-nad">Škola za trudnice · Podgorica i online</p>
			<h1 class="lm-rm-naslov">Ready Mom</h1>
			<p class="lm-rm-pod">Edukativni program za trudnice: priprema za porođaj i prve mjesece sa bebom, uz stručnjake koji se time bave svaki dan.</p>
			<ul class="lm-rm-cinjenice">
				<li><strong>2</strong> nedjelje</li>
				<li><strong>6</strong> predavanja</li>
				<li><strong>8+</strong> stručnjaka</li>
				<li>uživo ili <strong>online</strong></li>
			</ul>
			<div class="lm-rm-kupi">
				<span class="lm-rm-cijena"><?php echo wp_kses_post( $cijena ); ?></span>
				<a class="lm-rm-dugme" href="<?php echo $prijava; // phpcs:ignore ?>">Prijavi se i plati</a>
			</div>
			<p class="lm-rm-mala">Plaćanje karticom ili pouzećem. Poslije prijave vas zovemo sa terminom naredne grupe.</p>
			<?php echo $kontakt; // phpcs:ignore ?>
		</div>
		<div class="lm-rm-hero-slika"><?php echo $slika; // phpcs:ignore ?></div>
	</section>

	<section class="lm-rm-blok">
		<h2>Šta je Ready Mom?</h2>
		<p>Ready Mom je intenzivan program od dvije nedjelje i šest predavanja. Vodi vas redom: od posljednjih nedjelja trudnoće, preko porođaja, do prvih dana i nedjelja kod kuće sa bebom. Puno je praktičnih savjeta, primjera iz prakse i vremena za vaša pitanja.</p>
	</section>

	<section class="lm-rm-blok">
		<h2>Ko drži predavanja</h2>
		<ul class="lm-rm-predavaci">
			<li><strong>Ginekolog</strong><span>posljednji trimestar, znaci porođaja i kada krenuti u porodilište</span></li>
			<li><strong>Babica</strong><span>tok porođaja, disanje, položaji i šta vas čeka u porodilištu</span></li>
			<li><strong>Pedijatri</strong><span>prvi pregledi, njega novorođenčeta, san, kolike i kada se javiti ljekaru</span></li>
			<li><strong>Patronažna sestra</strong><span>prvi dani kod kuće, kupanje, pupak i dojenje u praksi</span></li>
			<li><strong>Psihoterapeut</strong><span>emocije u trudnoći i nakon porođaja, podrška partnera i briga o sebi</span></li>
		</ul>
	</section>

	<section class="lm-rm-dvije">
		<div class="lm-rm-kartica">
			<h3>Gdje</h3>
			<p>Uživo u prostoru Family Nest, naselje New City, Podgorica. Ako ste van Podgorice ili vam je lakše od kuće, program možete pratiti online. Partneri su dobrodošli.</p>
		</div>
		<div class="lm-rm-kartica">
			<h3>Kada upisati</h3>
			<p>Najbolje u drugoj polovini trudnoće, otprilike od 25. do 36. nedjelje. Tada vam je sve aktuelno, a imate vremena da naučeno primijenite. Grupe se popunjavaju, pa se prijavite na vrijeme.</p>
		</div>
	</section>

	<section class="lm-rm-kraj">
		<h2>Prijavite se za narednu grupu</h2>
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
.lm-rm-blok h2,.lm-rm-kraj h2{font-size:28px;margin:0 0 14px}
.lm-rm-blok p{font-size:16px;line-height:1.7;margin:0}
.lm-rm-predavaci{margin:0;padding:0;list-style:none;display:grid;gap:10px}
.lm-rm-predavaci li{margin:0;padding:14px 18px;border:1px solid #f3e7e0;border-radius:12px;display:flex;flex-direction:column;gap:3px}
.lm-rm-predavaci strong{color:#6d4e39;font-size:16px}
.lm-rm-dvije{display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:960px;margin:20px auto}
.lm-rm-kartica{padding:24px 26px;background:#fdf2ec;border-radius:14px}
.lm-rm-kartica h3{margin:0 0 8px;font-size:21px}
.lm-rm-kartica p{margin:0;line-height:1.65}
.lm-rm-kraj{margin:34px auto 0;padding:36px 24px;text-align:center;border-top:1px solid #f3e7e0}
.lm-rm-kraj .lm-rm-kontakt{justify-content:center}
@media (max-width:900px){.lm-rm-hero{grid-template-columns:1fr;gap:24px;padding-top:18px}.lm-rm-hero-slika{order:-1}.lm-rm-hero-slika img{border-radius:160px 160px 12px 12px}.lm-rm-naslov{font-size:34px}.lm-rm-dvije{grid-template-columns:1fr}}
@media (max-width:520px){.lm-rm-dugme{width:100%}.lm-rm-kupi{gap:10px}.lm-rm-k{flex:1 1 auto;justify-content:center}.lm-rm-blok h2,.lm-rm-kraj h2{font-size:23px}}
</style>
	<?php
}, 46 );
