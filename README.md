# Lady Mom – child tema i prilagođeni kod

Repozitorijum za sajt **ladymom.me** (razvoj na `dev.ladymom.me`). Tema je WoodMart, a ovo je njena child tema `woodmart-child`.

## Šta je gdje

| Folder | Šta je | Gdje živi na sajtu |
|---|---|---|
| `/` (`style.css`, `functions.php`, `woocommerce/`) | child tema | `wp-content/themes/woodmart-child/` |
| `snippets/` | izvoz dodatka Code Snippets: kopija za istoriju i pregled | baza sajta (Snippets) |
| `theme-options/` | WoodMart → Theme Settings → Custom CSS i Custom JS | baza sajta (podešavanja teme) |

Dok se kod iz `snippets/` ne prebaci u child temu, izvor istine za njega je baza sajta. Poslije svake izmjene snippeta pravi se novi izvoz i commit, da istorija bude potpuna.

## Pravila održavanja

1. **Originalni fajlovi se ne diraju.** Ni WordPress, ni WoodMart, ni dodaci.
2. **Sve izmjene idu kroz child temu i zvanične hookove i filtere**, ne kroz izmjenu postojećeg koda.
3. **Nijedan prilagođeni kod bez verzionisanja.** Svaka izmjena ide u ovaj repozitorijum, a prije izmjena se pravi backup sajta.
4. **Bez skripti iz neprovjerenih izvora.** Samo zvanična dokumentacija (WordPress, WooCommerce, WoodMart) i kod koji je pregledan.
5. **Redovna ažuriranja**, uvijek ovim redom: backup, pa test na staging verziji (`dev.ladymom.me`), pa tek onda živi sajt.
6. **Prvo podešavanja, pa kod.** Ako tema, WooCommerce, Yoast, Loco Translate ili Elementor imaju opciju, koristi se opcija, a ne kod.
