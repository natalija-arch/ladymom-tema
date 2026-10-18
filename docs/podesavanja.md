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

## 14.10.2026 – brzina (PageSpeed mobilni prije: 71, LCP 5,5 s)

**WoodMart**
- Performance → Remove jQuery Migrate: uključeno

**LiteSpeed Cache → Page Optimization**
- CSS: Minify i Combine uključeni (na početnoj ostaje jedan CSS fajl)
- HTML Minify i Remove WordPress Emoji uključeni
- JS: Minify i Load JS Deferred uključeni
  - Izuzeci za defer: `gtm.js`, `analytics.js`
  - jQuery se od 14.10. uveče takođe učitava odloženo. Korpa, plaćanje i nalog se ne optimizuju (LiteSpeed ih ne kešira), pa se tamo ništa ne mijenja.
- Provjereno poslije izmjena:
  - dodavanje u korpu, plaćanje, slajder, pretraga i chat dugme rade
  - nema JS grešaka
  - nema vidljivih razlika u stilovima
- Povratak, ako nešto zapne: isključiti `Load JS Deferred`, pa `Purge All`. Ako zapne samo zbog jQuery-ja, vratiti `jquery.js` i `jquery.min.js` u izuzetke.

**Slike (jednokratno, bez koda na sajtu)**
- 44 slike iz biblioteke (uglavnom Instagram slike iz 2021) imale su samo jednu umanjenu verziju, pa je telefon skidao original (do 145 KB po slici).
- Napravljene su im sve veličine koje WordPress inače pravi pri uploadu. Pokrenuto je zvanično `wp_update_image_subsizes()`, a originali nisu dirani. Sad sve slike u biblioteci imaju komplet veličina.

**Kod u child temi (`inc/prodaja-i-brend.php`)**
- 11b: na početnoj se slikama ne daje `fetchpriority="high"`. Najveći element je pozadina slajdera, pa bi prva pločica kategorije inače otimala protok i skidala se u punoj veličini. Za ovo nema podešavanja ni u WordPress-u, ni u Elementoru, ni u WoodMart-u.
- Preconnect ide na `cdn.fontshare.com` (odatle stižu fontovi), a ne na `api.fontshare.com` (taj CSS LiteSpeed ugradi u svoj fajl).

**Sljedeće**
- QUIC.cloud za WebP i kritični CSS (nalog pravi Marko).
- Clash Display je lokalno od 18.10.2026: `wp-content/uploads/lm-fonts/clash-display-500.woff2` i `-600.woff2`, sa preload-om debljine 600. Fontshare se više ne učitava.
- Keš zaglavlja (CSS, JS, slike) je 7 dana iako je u LiteSpeed-u godinu dana. Vjerovatno ga postavlja hosting ili Cloudflare.

## 18.10.2026 – slike proizvoda

**WooCommerce → Izgled → Prilagodi → WooCommerce → Slike proizvoda**
- Isijecanje sličica: Prilagođeno, 3:4 (430 × 573). Ranije: „Bez isijecanja“, pa su kartice imale različitu visinu.
- WooCommerce sam pravi nove sličice u pozadini; one koje još nisu napravljene prave se pri prvom prikazu.

## Mobilni meni (18.10.2026)
- Dva WordPress menija: **Mobilni – Prodavnica** (520, tab „Prodavnica“, prvi) i **Mobilni – Lady Mom** (521, lokacija `mobile-menu`, tab „Lady Mom“). Stari „Mobile navigation“ (68) i „Categories“ (62) su ostali netaknuti.
- Header `header_515836` → Mobile menu: tabovi zamijenjeni (Prodavnica prvi), drill-down sa klizanjem, podmeni se otvara klikom na naziv, uključene kategorije u pretrazi, popularne pretrage i HTML blok **51396 „Pretraga – mobilni meni“** (brendovi + kategorije prije kucanja). Lista želja / nalog / poređenje isključeni iz menija (postoje u donjoj traci). Rezerva headera: opcija `lm_rezerva_whb_header_515836`.
- Izgled stavki ide preko CSS klasa na stavkama menija (`lm-m-veliki`, `lm-m-naslov`, `lm-m-chip`, `lm-m-sve`, `lm-m-akcija`, `lm-m-skola`, `lm-m-radnja`, `lm-m-dugme`), a CSS je u Theme Settings → Custom CSS, sekcija `lm-mob-meni` (zadnja u polju).
- Važno: kad se `custom_css` mijenja direktno u bazi, WoodMart ga ne prikaže dok se ne obriše opcija `xts-theme_settings_default-status` (tada ponovo generiše CSS). Snimanje kroz Theme Settings to radi samo.

## Prazne kategorije i brendovi se ne prikazuju (18.10.2026)
- `inc/aktivni-termini.php`: kategorija ili brend bez ijednog objavljenog artikla na stanju (ili na naručivanje) ne prikazuje se u WordPress menijima (mobilni i glavni), u mega meniju i pretrazi (WoodMart `wd/menu-list-item`), u HTML bloku pretrage na mobilnom, u traci brendova i na pločicama na početnoj (Elementor).
- Kategorija je aktivna i kad je aktivna neka podkategorija. Ništa se ne briše: čim ERP javi da je artikal na stanju, stavka se vrati.
- Spisak je u transientu `lm_aktivni_termini` (1 h); kad se promijeni zaliha/artikal/termin, računa se ponovo na kraju zahtjeva, i ako se spisak promijenio, briše se LiteSpeed keš.
- Nativne opcije nema: meniji i blokovi su ručno složene stavke, a „sakrij prazne“ u WordPressu broji i artikle kojih nema na stanju.

## Pozivi servera prema samom sajtu (18.10.2026)

Imunify Security (dodatak hostinga) blokira sa „429 Rate limit exceeded“ zahtjeve koje server šalje sam sebi preko Cloudflare-a. Zbog toga LiteSpeed crawler nije mogao da pročita sitemap. `inc/server-veza.php` za ladymom.me razrješava adresu direktno na IP servera (LiteSpeed → General → Server IP = 162.55.0.170). Ako se server ili IP promijeni, treba ažurirati to polje.

LiteSpeed crawler: uključen, sitemap `https://ladymom.me/sitemap_index.xml`. Sam zagrijava keš kategorija i proizvoda, pa prvo otvaranje nije sporo.

## Slike u WebP formatu (18.10.2026)

`inc/slike-webp.php`: WordPress kopije slika (sličice, slika proizvoda, srednje, velike) pravi kao WebP umjesto JPG (filter `image_editor_output_format`). Original iz ERP-a ostaje JPG i ne mijenja se. Emailovi koriste original, jer Outlook na računaru ne prikazuje WebP. Postojeće slike su jednom ponovo obrađene 18.10.; stare JPG kopije su ostale na disku (ne smetaju, mogu se kasnije očistiti).
