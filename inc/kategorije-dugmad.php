<?php
/**
 * Lady Mom – podkategorije kao dugmad iznad artikala (pregled sajta 21.10.2026, stavka r3).
 *
 * Garderoba, Bebe i Prodavnica su prikazivale samo velike slike podkategorija (Prikaz:
 * „Podkategorije“), bez ijednog artikla. Sada (podešavanje kategorije) prikazuju artikle, a
 * podkategorije su red malih dugmadi iznad artikala, na telefonu i tabletu. Na desktopu
 * podkategorije već stoje u naslovu stranice (WoodMart), pa se dugmad tamo ne ponavljaju.
 *
 * Redoslijed je iz podešavanja kategorija (WooCommerce poredak, polje „order“), složen po
 * potražnji iz Search Console-a; isti redoslijed važi i za WoodMart listu u naslovu.
 * Prazne podkategorije (bez artikla na stanju) se ne prikazuju.
 *
 * @package woodmart-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'woocommerce_before_shop_loop',
	function () {
		if ( ! is_product_category() ) {
			return;
		}
		$kat = get_queried_object();
		if ( ! $kat instanceof WP_Term ) {
			return;
		}
		$djeca = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $kat->term_id,
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $djeca ) || empty( $djeca ) ) {
			return;
		}
		$linkovi = '';
		foreach ( $djeca as $d ) {
			// Broj vidljivih artikala (WooCommerce ga drži u meti); bez njega se oslanjamo na count.
			$vidljivo = get_term_meta( $d->term_id, 'product_count_product_cat', true );
			if ( '' !== $vidljivo && (int) $vidljivo < 1 ) {
				continue;
			}
			$link = get_term_link( $d );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$linkovi .= '<a class="lm-podkat-a" href="' . esc_url( $link ) . '">' . esc_html( $d->name ) . '</a>';
		}
		if ( '' === $linkovi ) {
			return;
		}
		echo '<nav class="lm-podkat" aria-label="' . esc_attr__( 'Podkategorije', 'woodmart-child' ) . '">' . $linkovi . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- složeno iznad sa esc_url/esc_html.
	},
	1
);

add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
			return;
		}
		?>
<style id="lm-podkat">
.lm-podkat{display:none}
@media (max-width:1024px){
	.lm-podkat{display:flex;gap:8px;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none;margin:0 -15px 16px;padding:2px 15px 4px;width:calc(100% + 30px);flex-basis:100%}
	.lm-podkat::-webkit-scrollbar{display:none}
	.lm-podkat-a{flex:0 0 auto;white-space:nowrap;padding:8px 14px;border-radius:999px;background:var(--lm-krem,#fdf2ec);border:1px solid #f0e0d6;color:var(--lm-smedja,#6d4e39)!important;font-size:14px;font-weight:600;line-height:1.2;text-decoration:none!important}
	.lm-podkat-a:active,.lm-podkat-a:focus-visible{background:#f6e3d9;outline:2px solid var(--lm-roze-jaka,#a05c57);outline-offset:1px}
}
</style>
		<?php
	},
	50
);
