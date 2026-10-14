# Podešavanja umjesto koda

Spisak opcija koje su postavljene u adminu, a koje su prije radile kroz snippete. Ako se kod ponovo dodaje, prvo provjeriti ovdje.

## 14.10.2026

**Yoast SEO**
- Separator naslova: `–`
- Šabloni naslova:
  - proizvod: `%%title%% | Lady Mom Podgorica`
  - kategorija: `%%term_title%% – kupite online | Lady Mom`
  - brend: `%%term_title%% u Crnoj Gori – Lady Mom`
- Šabloni meta opisa: proizvod (`%%excerpt%%` + rečenica o dostavi), kategorija, brend, blog članak (`%%excerpt%%`)
- noindex (pa ni u sitemap-u): oznake, oznake proizvoda, HTML blokovi, slajderi, slajdovi, WoodMart layouti, arhive autora
- Podrazumijevana slika za dijeljenje: `lady-mom-butik-podgorica.jpg` (ID 35771)
- Po stranicama:
  - meta opis za Korpa, Plaćanje, Moj nalog, Lista želja, Načini plaćanja, Dostava i Blog (Blog ima i naslov);
  - noindex za Korpa, Plaćanje, Moj nalog, Lista želja, Poređenje, Stranica nije pronađena.

**WooCommerce**
- Prilagodi → WooCommerce → Plaćanje: firma i adresa 2 sakrivene, telefon obavezan
- Tekst kvačice za uslove: „Pročitao/la sam i prihvatam [terms].“
- Kuponi `readymom10` i `dobrodosla10`: „Isključi artikle na akciji“.
  - Novi kupon mora imati istu kvačicu.
  - Kupon koji treba da važi i na akciju je jednostavno nema.

**WoodMart**
- Fonts → Preload woodmart-font.woff2: uključeno
- Footer: pozadina `#3f6265`, tekst „Light“. Odatle je uklonjen CSS koji je bojio pozadinu footera.
- Header banner: pozadina `#4a7a7d`, tekst „Light“ (već je bilo podešeno). Uklonjen je CSS koji je ponavljao isto.
- Preset 152 „Lady Mom – bez newsletter popupa“ (Theme settings → Presets): na korpi, plaćanju, nalogu i stranici proizvoda je promo popup isključen. Zamjenjuje filter iz snippeta 26.

## 14.10.2026 – kod prebačen u child temu

- Objava: cPanel → Git Version Control → `ladymom-tema` (javni repozitorijum, HTTPS) → Deploy HEAD Commit.
- Opcija `lm_tema_kod` = 1: tema učitava `inc/`.
- Snippeti 5–11, 21–26 i 28–32 su isključeni i ostaju kao rezerva par dana. Brišu se kad se potvrdi da sve radi.
- Povratak na staro, ako zatreba: `lm_tema_kod` = 0, pa ponovo uključiti te snippete.
