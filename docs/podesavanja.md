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

## Telefon, Google i Newsletter (18.10.2026 uveče)

- WoodMart podešavanja (rezerva u opciji `lm_rezerva_woodmart_opcije_1810b`): `categories_under_title` isključeno (bez kategorija ispod naziva na karticama), `attr_after_short_desc` isključeno (tabela osobina se ne ponavlja iznad izbora boje), `shop_filters` isključeno (nema drugog panela „Filteri“). U bočni panel filtera dodat widget „Sortiraj“ (`woodmart-woocommerce-sort-by-2`) na vrh.
- `inc/telefon-i-seo.php`:
  - kategorija 104 „Prodavnica ERP“ se kupcu prikazuje kao „Prodavnica“ (filter `get_term` samo van admina i REST-a, plus putanje WooCommerce i Yoast); u bazi i za ERP naziv ostaje isti;
  - telefon: na stranici proizvoda skrivena putanja i strelice iznad naziva, logo brenda 26 px; u korpi polje za kupon sklopljeno u link „Imate kupon?“;
  - meta opis proizvoda bez kratkog opisa: „Brend – naziv. Kategorija u Lady Mom butiku…“; ručni Yoast opis ima prednost;
  - GTIN iz barkoda (SKU) u podacima o proizvodu; kod varijabilnih artikala svaka varijanta je posebna ponuda sa barkodom, cijenom i dostupnošću;
  - prijava iz popupa (CF7 forma 35763, kod DOBRODOSLA10) upisuje adresu u dodatak Newsletter kao potvrđenu.
- Dodatak Newsletter 9.4.7: pošiljalac natalija@ladymom.me, odgovor na natalija@ladymom.me, 100 emailova na sat, šalje preko WP Mail SMTP (Gmail). Mailchimp for WP isključen.
- Yoast: tekst početne stranice u putanji „Početna“.

## Tabovi na početnoj se učitavaju unaprijed (18.10.2026)

`inc/pocetna-tabovi.php`. WoodMart drugi tab („Na akciji“) traži od servera tek na klik (oko 1,3 s). Skripta isti zahtjev pošalje u pozadini kad se početna učita, pa je klik trenutan. Sadržaj se pravi svježe pri svakom otvaranju početne, pa se novi artikli na akciji vide odmah. Ako pozadinski zahtjev ne uspije, klik radi kao ranije.

## Google Merchant Center – spisak proizvoda (18.10.2026)

`inc/google-feed.php`, adresa `https://ladymom.me/?lm_feed=google`. RSS/XML po Google specifikaciji: svaka varijanta je poseban red (`item_group_id` = artikal), cijena sa PDV-om, akcija, zalihe, slika (original JPG), brend, GTIN iz barkoda (SKU), boja, veličina, putanja kategorije bez „Prodavnica ERP“. Čuva se 2 sata, briše se kad se proizvod promijeni. Merchant Center nalog 5416418334 (natalija@ladymom.me), država Crna Gora; jezik proizvoda za sada „English“ jer Google još ne nudi naš jezik.

## Prijava preko Google naloga (18.10.2026)

WoodMart Social login: ključ u Theme Settings (Google App ID / Secret), Google Cloud projekat „Lady Mom“, klijent „ladymom.me prijava (WoodMart)“, redirect `https://ladymom.me/moj-nalog/google/oauth2callback`. `inc/google-prijava.php`: dugme je prvo u formi za prijavu, sa Google znakom i natpisom „Nastavi sa Google nalogom“, razdjelnik „ili emailom i lozinkom“.

## Nalog uz porudžbinu (18.10.2026)

`inc/nalog-uz-porudzbinu.php`. Gost plaća bez registracije; kad porudžbina nastane, nalog se napravi sam od emaila (email „Dobro došli u Lady Mom“ sa linkom za lozinku) ili se porudžbina veže za postojeći nalog sa istim emailom (ne za naloge osoblja). Na vrhu plaćanja: „Nastavi sa Google nalogom“ i napomena o nalogu. WooCommerce opcija „Allow customers to create an account during checkout“ isključena (rezerva `lm_rezerva_signup_checkout` = yes), jer je kvačica „Napravi nalog?“ sad suvišna.

## „Default“ boja se ne prikazuje (18.10.2026)

`inc/bez-default.php`. ERP šalje boju „Default“ (pa_boja 417) za artikle bez boje. Skriva se u tabeli osobina, korpi, porudžbinama i emailovima, nazivu varijante, filteru boja, izboru na proizvodu i Google feedu. Podatak se ne mijenja.

## Brzina početne (19.10.2026)

PageSpeed (telefon, 7:33): 67/100, FCP 2,0 s, LCP 10,4 s (simulacija), TBT 180 ms, CLS 0. Stvarni korisnici (CrUX, većinom stari sajt): LCP 2,0 s, INP 70 ms, CLS 0,14, TTFB 1,3 s.
- LiteSpeed JS Combine uključen (`optm-js_comb` = 1, rezerva `lm_rezerva_litespeed_js_1910`): početna sa 99 na 5 skripti. Korpa i plaćanje izuzeti iz optimizacije (`optm-exc` = /placanje/, /korpa/) zbog Bankart-a.
- `inc/brzina.php`: blockUI i js-cookie dobijaju `defer` (bili render-blocking).
- Otvoreno: Google/Facebook skripte tek na dodir (čeka Markovo da), Cloudflare Email Obfuscation (Marko), QUIC.cloud kritični CSS (s5), memcached na hostingu.

## End-to-end test, ispravke 20.10.2026.

- **Plaćanje karticom za nove kupce** (`inc/nalog-uz-porudzbinu.php`): nalog se pravi tek kad porudžbina pređe u obradu (pouzeće odmah, kartica posle potvrde Bankarta). Test #51484: Bankart forma se otvara bez prijave.
- **Telefon** (`inc/placanje-provjere.php`): mora imati 8–15 cifara.
- **Plaćanje gotovinom** (`inc/placanje-provjere.php`): kod preuzimanja u butiku piše „Plaćanje u butiku“, za korpu bez dostave (Ready Mom) „Plaćanje gotovinom“; isti tekst na stranici „Porudžbina primljena“.
- **Emailovi bez dostave** (`inc/emailovi-1/2/3`): za Ready Mom i druge virtuelne artikle nema teksta o paketu, kuriru, adresi dostave ni trake „Na putu“; kupac Ready Mom ne dobija poziv na Ready Mom.
- **Sortiranje** (`inc/jezik.php`): crnogorski nazivi (Preporučeno, Najpopularnije, Najnovije, Cijena…); sortiranje po ocjeni uklonjeno.
- **„Još samo N komada“** (`inc/prodaja-i-brend.php`): oznaka isključena (u katalogu i na artiklu), jer otkriva zalihu. Vraća se filterom `lm_prikazi_preostalo`.
- **Moj nalog** (`inc/jezik.php`): „poveznicu“ → „link“, „Ponovno pošalji“ → „Pošalji ponovo“, „nisi …?“ → „niste …?“.
- **Podešavanja 20.10.** (WooCommerce, prethodne vrijednosti u opciji `lm_backup_privacy_text`): tekst o privatnosti na plaćanju i registraciji je glasio „…opisane u našoj politika privatnosti“, sada „…u našoj politici privatnosti“ sa linkom; tab „Preuzimanja“ u Mom nalogu uklonjen (Advanced → Account endpoints → Downloads prazno), jer nema artikala za preuzimanje.
- **Emailovi za butik (20.10.)**: `ladymom.mne@gmail.com` (djevojke u butiku) dobija iste emailove kao natalija@ladymom.me: nova, otkazana i neuspjela porudžbina (WooCommerce → Podešavanja → Emailovi → Primalac) i zahtjeve za povrat/zamjenu (opcija `lm_butik_emailovi`, `inc/povrat-forma.php`).
- **Dizajn emailova (20.10.)** (`inc/emailovi-1..4`, `inc/povrat-forma.php`): veća dugmad (52 px, na telefonu cijele širine), na telefonu bočno 20 px i kolone jedna ispod druge, tamni režim (paleta u `lm_em_tamne_boje()`, klase dodaje `lm_em_dovrsi()` na filteru `woocommerce_mail_content`). Emailovi butiku (nova, otkazana, neuspjela porudžbina) i zahtjev za povrat (butik i potvrda kupcu) su u istom okviru. Logo u footeru emaila je na tirkiznoj pločici, medij 51495 (`lm_email_media['logo_footer']`, stara opcija u `lm_backup_email_media_20_10`).
- **Nazivi (20.10., Marko)**: firma Tangerine Limited d.o.o., kurirska služba The Vault d.o.o. (emailovi, Dostava, Uslovi korišćenja).
- **Poruke kupaca (20.10.)**: kontakt forma (CF7 1572 „Kontakt forma“, primalac u podešavanju forme; stara vrijednost u opciji `lm_backup_cf7_1572_recipient`) stiže na natalija@ladymom.me i ladymom.mne@gmail.com. Kad kupac odgovori na bilo koji email kupcu (porudžbina, nalog, lista želja, napuštena korpa, potvrda zahtjeva za povrat), „Reply-To“ sadrži obje adrese (`lm_em_odgovor_primaoci()` u `inc/emailovi-1-tekstovi.php`, `lm_povrat_primaoci()` u `inc/povrat-forma.php`).

## Odobreni predlozi iz testa kupovine (20.10.2026)

- **Kuponi** DOBRODOSLA10 i READYMOM10: Ready Mom (#51472) je u „Isključeni proizvodi“.
- **Ready Mom van listi proizvoda**: vidljivost artikla „Sakriveno“ (ne vidi se u prodavnici, kategorijama, „Novo u ponudi“ ni pretrazi); kupuje se sa /ready-mom/.
- **Ready Mom plaćanje** (`inc/placanje-provjere.php`): kad korpa nema dostavu, samo ime, prezime, telefon i email; naslov „Vaši podaci“.
- **NLB rate** (`inc/prodaja-i-brend.php`): napomena tek od 30 € (artikal, korpa, plaćanje); druga NLB napomena ispod „Poruči“ uklonjena (`inc/ux-dodaci.php`).
- **„Pitajte nas“**: „za veličinu ili savjet“ samo kad artikal ima veličinu, inače „Pitajte nas za savjet“.
- **Sortiranje** (`inc/prodavnica-dorade.php` + WooCommerce podešavanje): podrazumijevano „Najpopularnije“; „Preporučeno“ (ručni redoslijed) izbačeno.
- **Traka kategorija na /prodavnica/**: kategorije ispod korijena „Prodavnica“ umjesto „Bez kategorije / Prodavnica“.
- **Stranica proizvoda**: galerija 1:1 kad je glavna slika kvadratna (klasa `lm-galerija-kvadrat`); u srcset-u samo WebP kad postoji.
- **Plaćanje na telefonu** (`inc/placanje-telefon.php`): kompaktan vrh (nalog i kupon u jednom redu, „Unesite kod“), napomena na link, artikli sklopljeni od 2 artikla, bez trake prednosti i velikog footera; mini korpa sa glavnim dugmetom „Plaćanje“. Tekst pristanka za napuštenu korpu skraćen u WoodMart podešavanju (stari u opciji `lm_backup_recover_consent_text`).
- **Poruke o kuponu** (`inc/jezik.php`): „Kupon „…“ ne važi za izabrane proizvode.“ i „Kupon ne važi za artikle na akciji.“ (bilo „…nije primjenjiv…“).
- **Korpa (20.10.)**: „Izračunaj dostavu“ uklonjeno (WooCommerce → Podešavanja → Dostava → Opcije → kalkulator dostave u korpi isključen; stara vrijednost u opciji `lm_backup_shipping_calc`).
- **Korpa (20.10.)**: bez rečenice „Opcije dostave biće ažurirane na plaćanju.“ (`woocommerce_shipping_estimate_html`, `inc/prodavnica-dorade.php`).

## Pregled telefona i Lady Mom stila (20.10.2026)

- **`inc/dugmad-stil.php`**: količina (−/broj/+) u jednom bijelom okviru umjesto punih roze dugmića; „Dodaj u korpu“ do kraja reda na telefonu; dugme za prikaz lozinke bez pozadine; Google ikonica u ravni sa tekstom; puni nazivi u donjoj traci; Česta pitanja u Raleway (bio Lato); roze ikonice za dijeljenje članka; „Pročitaj više“ kao roze link; polja zaobljena 6 px; zvjezdica obaveznog polja roze; veće roze kvačice; WhatsApp/Viber sa roze okvirom; veličine u filteru kao jednaka dugmad; tekst u blokovima #7a6250.
- **Podešavanja** (stare vrijednosti u opciji `lm_backup_stil_20_10`): sve veličine (`pa_velicina`) imaju „not_dropdown“, pa se na proizvodu biraju dugmadima, a ne padajućom listom (nove veličine dobijaju isto, `inc/prodavnica-dorade.php`); filter Boja prikazuje naziv uz tačkicu; newsletter popup (CF7 35763) `acceptance_as_validation: on`, pa dugme nije izblijeđeno; dijeljenje: Facebook, Pinterest, WhatsApp, Viber (bez X, LinkedIn, Telegram); početna, „Omiljeno kod mama“: kartice proizvoda kao u ostalim listama (`product_hover` = icons).
- **Ready Mom**: „Plaćanje karticom ili gotovinom“ (bilo „pouzećem“).
- **Kartice proizvoda (20.10.)**: WoodMart → Products → „Product title lines limit“ = dva reda i „Quick shop“ za artikle sa varijantama isključen (ikonica korpe vodi na stranicu artikla umjesto prozora preko slike, koji je na telefonu izlazio iz kartice); stare vrijednosti u `lm_backup_kartice_20_10`. U `inc/dugmad-stil.php` naslov ima tačnu visinu dva reda sa „…“, pa su cijene u istoj ravni u svim listama.
- **QUIC.cloud i kritični CSS (20.10.)**: Marko je povezao QUIC.cloud (bez njihovog CDN-a; CDN ostaje Cloudflare). LiteSpeed → Page Optimization → CSS: „Load CSS Asynchronously“ uključeno (kritični CSS sa QUIC.cloud-a), jedan kritični CSS po vrsti stranice (`optm-ccss_sep_posttype` prazno), početna posebno (`optm-ccss_sep_uri` = `^/$`). Stare vrijednosti u opciji `lm_backup_ccss_20_10`. Besplatni nivo: 2 kritična CSS-a dnevno, 20 mjesečno; red: početna (gotovo 20.10.), proizvod, prodavnica, kategorije/brendovi, stranice, članak, blog. Dok vrsta stranice nema kritični CSS, CSS se učitava kao ranije. Povratak: isključiti „Load CSS Asynchronously“ i Purge All.
- **Slike u headeru lijeno** (`inc/brzina.php`, 20.10.): slike iz panela pretrage, mega menija i mobilnog menija dobijaju `loading="lazy"` i ne troše WordPress-ov limit prvih slika „odmah“ (bile su ~7 slika, ~120 KB, na svakoj stranici prije slike slajdera i glavne slike proizvoda).

## SEO i škola za trudnice (21.10.2026)

Analiza: dokument „SEO analiza ladymom.me“ (claude.ai, 21.10.). Marko: „napravi sve što možeš“.

- **`inc/seo-dorade.php`** (novi):
  - stare adrese škole (`/ready-mom/`, `/skola-za-trudnice-ready-mom-podgorica/`, `/kategorija/skola-za-trudnice/`) → stranica škole, 301;
  - šema Product na stranici proizvoda (WooCommerce je pravi na `woocommerce_single_product_summary`, koju WoodMart prikaz ne pokreće); poziv se pravi na akciji `wp`. Artikal sa varijantama koji nije na stanju nema šemu (WooCommerce tada izlazi ranije);
  - kategorije: H1 iz term meta `lm_h1`, tekst ispod artikala iz term meta `lm_tekst` (naziv i opis kategorije ostaju iz ERP-a);
  - „Default artikal“ iz ERP-a: noindex i van sitemapa;
  - meta opis proizvoda bez Yoast opisa: cijele rečenice (do 100 znakova) + rečenica o dostavi;
  - stranica škole: JSON-LD Course (Family Nest, Branka Deletića bb; uživo i online) i FAQPage iz `lm_rm_pitanja()`.
- **Stranica škole** (#51473): adresa `/skola-za-trudnice/` (bila `/ready-mom/`), H1 „Škola za trudnice Ready Mom“, program po predavačima, mjesto Family Nest – Centar za trudnoću i roditeljstvo, 8 čestih pitanja (`lm_rm_pitanja()` u `inc/ready-mom.php`). Sadržaj članka #35708 spojen ovdje; članak je nacrt (rezerva `lm_backup_seo_c_35708`). „Škola za trudnice“ u glavnom meniju (stavka #51498), mobilnom meniju i footeru.
- **Trajanje programa: 4 nedjelje** (Marko, 21.10.; bilo „dvije nedjelje“): `inc/ready-mom.php`, opis u Course šemi, opis i kratak opis artikla #51472 (rezerva `lm_backup_rm_trajanje_51472`). *(Zamijenjeno 23.10.2026: 2 nedjelje, 6 predavanja, 6 stručnjaka – vidi „Škola: 2 nedjelje…“.)*
- **Kategorije**: Yoast naslov i opis za 11 glavnih kategorija (`WPSEO_Taxonomy_Meta::set_values`, oba ključa u jednom pozivu), `lm_h1` i `lm_tekst` za iste; novi Yoast šablon opisa ostalih kategorija. Stare vrijednosti u `lm_backup_seo_21_10`.
- **Preusmjerenja** (`inc/preusmjerenja.php`): `/featured_item/` i `/elementor-46675/` → početna; `/brand/…` → `/marka/…`; `/kategorija-proizvode/…` → `/kategorija/…`; `/shop(-2)/page/N/` → `/prodavnica/page/N/`; `/boja/…` (404) → `/prodavnica/`.
- **Blog**: interni linkovi u starim člancima ka školi i kategorijama (rezerve `lm_backup_seo_c_<ID>` i `lm_backup_seo_m_<meta_id>`); Yoast naslovi za 4 duga naslova. 9 novih članaka kao nacrti #51499–#51507 (napomena za objavu u meta `_lm_nacrt_napomena` i na to-do listi, g8).
- **Ocjena na Google-u**: opcija `lm_google_recenzije` (`lady_mom`, `family_nest` – linkovi za ocjenu); kutija „Ocijenite nas na Google-u“ u emailu `customer_completed_order` („Spremno za preuzimanje“ i „Paket je na putu“), osim za porudžbine bez dostave.

## Mini korpa, cijena artikla (21.10.2026)

- `inc/placanje-telefon.php`: filter `woocommerce_widget_cart_item_quantity` umjesto „3 × 19,00 €“ ispisuje ukupno za artikal (`get_product_subtotal`), a kad je više od jednog komada ispod sitno „po 19,00 €“. Polje za količinu i cijena su u istom redu, cijena desno; na uskom telefonu cijena pređe u red ispod. Dugmad količine u mini korpi 30 px, polje 32 px.
- `inc/dugmad-stil.php`: okvir količine važi samo za `div.quantity`. Ranije je pravilo `.quantity` stavljalo okvir i oko teksta cijene u mini korpi (`span.quantity`).

## Mjerenje za oglase (21.10.2026)

Marko: „odradi sva podešavanja koja treba za sve ove platforme“ (Meta, Google Ads, GA4, Merchant Center). Plan i lista: dokument „Lady Mom — oglasi, AI i mjerenje“ i artifact „Lady Mom oglasi“.

- **`inc/mjerenje.php`** (novi):
  - WoodMart AJAX dodavanje u korpu (`woodmart_ajax_add_to_cart`) ne okida `woocommerce_ajax_added_to_cart`, pa Meta for WooCommerce nije slao AddToCart iz pregledača. Na `woocommerce_add_to_cart` (prioritet 50, poslije serverskog događaja dodatka) postavljamo `product_id`/`quantity` u `$_POST` i okidamo tu akciju; dodatak sam ubaci fragment sa AddToCart i istim `event_id` (bez dupliranja).
  - Filter `wc_facebook_crawler_user_agent_patterns` (zvanični, dodatak 3.3+): botovi, LiteSpeed crawler, QUIC.cloud, headless, Lighthouse/PageSpeed, curl/wget/python ne šalju serverske (CAPI) događaje. Prazan User-Agent = bot (`wc_facebook_is_crawler_request`). Razlog: ~90 % serverskih PageView i ~99 % Search događaja (28 dana do 21.10.) nije imalo par iz pregledača.
  - Upiti: klik na telefon, WhatsApp, Viber (samo linkovi sa brojem butika, ne dijeljenje) → GA4 `contact` + Meta `Contact`; CF7 poslata forma → GA4 `generate_lead`, newsletter popup (CF7 35763) → GA4 `sign_up`. CF7 Lead za Metu šalje sam dodatak.
- **Microsoft Clarity** (`inc/mjerenje.php`): projekat `z173rgv3iv` (Marko napravio 21.10.), zvanični kod u `wp_head`, ne za prijavljene administratore. Kad stigne traka za kolačiće, Clarity ide pod saglasnost.
- **`inc/google-feed.php`**: `g:gender` i `g:age_group` za garderobu (Mame > Garderoba: female/adult; Bebe > Bodi: unisex/infant; Bebe > Naočare za sunce po uzrastu iz potkategorije). Usluge (virtuelni artikli, Ready Mom) su već bile van feeda.
- **GA4**: sajt šalje u svojstvo 337396867 (G-BN6QSS18LM, Site Kit, Google tag GT-KDQMQTQP) od 18.10. Svojstvo 343127380 „ladymom.me“ je primalo stari sajt do 17.10. `G-EQDN3BWDSD` u izvoru stranice je Site Kit-ova telemetrija za prijavljenog admina, nije druga oznaka.

## Početna, faza 1 (21.10.2026, nacrt)

- Nacrt: stranica „Početna (nacrt 21.10.)“ (#51509, opcija `lm_pocetna_nacrt`), kopija početne #3228 sa svim meta poljima. Prava početna nije mijenjana; njen `_elementor_data` prije izmjena je u meta `_lm_rezerva_prije_c` na #3228.
- Raspored: slajder, kategorije, proizvodi u tabovima, Spectra, Koala, škola i butik, brendovi, blog, Instagram. Sekcija „Novo u ponudi“ (lmn0001) je ukinuta i postala tab.
- Kategorije (lmc0001): WoodMart *Product categories*, karusel (6 / 4 / 2,5 vidljivih), ID-jevi 106, 115, 107, 112, 110, 124, 125, 127, 150, 141, `orderby` = `include`, `order` = ASC (redoslijed kako su upisani). Izbor po pregledima u GA4 17.–21.10. i artiklima na stanju.
- Proizvodi (76d6c0d): tabovi Najprodavanije, Novo (svi artikli, najnoviji prvi), Na akciji; po 8 artikala (bilo je 54 u tabu Na akciji); boja tabova #a05c57; dugme „Pogledaj sve“ (lmo0004) → /prodavnica/.
- Koala (lmhs000): *Image Hotspot* umjesto slike, jedna tačka na pojasu sa tekstom iz opisa artikla i linkom na kategoriju pojaseva.
- Škola (lms0008): *List* sa 4 stavke (4 nedjelje i 8 predavanja, 6 stručnjaka, uživo ili online, partneri dobrodošli). *(Zamijenjeno 23.10.2026: 2 nedjelje, 6 predavanja, 6 stručnjaka – vidi „Škola: 2 nedjelje…“.)*
- Brendovi (lmr0004): bez Bubbija (0 artikala), 3 logotipa na telefonu. Blog (38198c0): dizajn „Small images“, 3 članka, kategorije Savjeti za mame i Edukacija. Instagram: slike `woocommerce_thumbnail` umjesto pune veličine.
- Razmaci sekcija na telefonu 34 px (bilo 42). Izgled: `inc/pocetna-dizajn.php` (samo početna i nacrt).

## Traka za kolačiće i Consent Mode (21.10.2026)

Marko odobrio (lista „Lady Mom oglasi“, m3). Backup prije: All-in-One WP Migration → Export → File (2 GB, ostao na serveru u Backups).

- **Dodaci** (wordpress.org): Complianz 7.5.6 i WP Consent API 2.1.0. Bez registracije kod Complianz-a (website scan i cookiedatabase.org nisu uključeni).
- **Complianz čarobnjak**: EU (GDPR), znači opt-in; zemlja ME; nalozi kupaca = da; statistika = Google Analytics (Site Kit); Consent Mode u Complianz-u = ne (radi ga Site Kit); treće strane = Google Maps (mapa na /kontakt/ čeka saglasnost za marketing); oglasni kodovi = da; dokumenti: politika kolačića i privatnosti = postojeća stranica „Politika privatnosti“ (#3).
- **Integracija „Facebook for WooCommerce“ u Complianz-u isključena** (`complianz_options_integrations['facebook-for-woocommerce'] = 0`). Ona blokira svaki kod sa `fbq`, pa bi blokirala i naše GA4 događaje i fragmente dodatka. Umjesto toga koristimo zvanični „hold“ dodatka Meta for WooCommerce (vidi niže).
- **Traka** (Complianz → Consent Banner, baner #1): crnogorski tekstovi (Kolačići na sajtu; Prihvati / Odbij / Podešavanja / Sačuvaj izbor; kategorije Neophodni, Statistika, Marketing; Podešavanja sakrivena), boje sajta (dugme #6d4e39, odbij #fdf2ec, prekidači #4a7a7d). Blokator kolačića uključen.
- **Site Kit Consent Mode v2**: `googlesitekit_consent_mode` = enabled. Site Kit za „denied“ koristi fiksnu listu EEA/UK/CH (podešavanje regiona ne čita), pa Crnu Goru dodajemo filterom `googlesitekit_consent_defaults` u `inc/mjerenje.php`. Saglasnost Site Kit čita preko WP Consent API-ja (Complianz).
- **`inc/mjerenje.php`**:
  - Meta piksel: filter `facebook_signals_held` (novi posjetilac bez kolačića `wc_facebook_signals_state` je zadržan) i rani kod u `wp_head` koji postavi kolačić na `held`. Na saglasnost za marketing (`wp_listen_for_consent_change`) poziva `fbwcsignal.release()`: dodatak tada pokrene piksel i pošalje sačuvane događaje; na povlačenje `fbwcsignal.hold()`. Sve radi samo kad postoji `wp_has_consent` (WP Consent API).
  - Meta `Contact` ide preko `FacebookSignals.trackEvent` (čuva se dok traje hold).
  - Clarity se učitava tek uz saglasnost za statistiku.
- **Povratak**: deaktivirati Complianz i WP Consent API (kod u temi se sam isključi jer nema `wp_has_consent`); kolačić `wc_facebook_signals_state=held` kod posjetilaca dodatak tada i dalje poštuje, pa poslije deaktivacije treba vratiti i filter (ili ga ukloniti iz `inc/mjerenje.php`).
- **Politika privatnosti (#3)**, 21.10. (Marko: „uradi“): pasus o kolačićima zamijenjen odjeljkom „Kolačići (cookies)“ (kategorije Neophodni / Statistika / Marketing sa imenima alata, Consent Mode, i `[cmplz-manage-consent]` za promjenu izbora, jer se na telefonu dugme „Kolačići“ ne prikazuje). Usput uklonjen pokvaren ugniježđeni link na okov.me. Stari tekst je u revizijama stranice.
- Poruka WooCommerce-a u korpi sa količinom na stanju („imamo N na stanju“) ostaje kako jeste (Marko: „nemoj“, 21.10.).
- **Izgled kolačića** (21.10., Marko: „ružno, uklopi u dizajn“): `inc/kolacici.php` — kategorije u traci i na Politici privatnosti u krem okviru, dugme za opis samo strelica (ranije ga je hvatao globalni stil dugmadi), na stranici prekidači u tirkiznoj umjesto običnih checkboxova.
- **Blog na početnoj, desktop** (21.10., Marko: „loše izgleda, smanji ga“): `inc/pocetna-dizajn.php` — tri kompaktne kartice u redu (slika lijevo, naslov desno), na tabletu dvije; sekcija ~140 px umjesto ~1400 px (widget je bio u jednoj koloni sa velikim slikama).
- **Filteri u prodavnici** (21.10., Marko: „loše izgleda, povećaj logotipe brendova, sredi boje“, i na telefonu): `inc/filteri-stil.php` — Boja u jednoj koloni sa kružićem; dvobojne i šarene boje iz ERP-a (bez boje u WoodMart-u) dobijaju kružić po nazivu (lista u `lm_filter_boje_bez_swatcha()`); „default“ sakriven. Brend: logotipi u karticama, dvije kolone, visina do 40 px.
- **Filter Veličina** (21.10., Marko, slika panela na telefonu: „ovo loše izgleda“): widget `woodmart-woocommerce-layered-nav-6` prebačen sa „double“ na „inline“ (rezerva svih widgeta filtera u opciji `lm_backup_filteri_21_10`), pa su veličine u jednom redu umjesto u dvije kolone. `inc/dugmad-stil.php`: dugmad 38 px, izabrana veličina puna roze sa bijelim slovima, bez okvira od 2 px i tamne crte ispod. Boja i Brend su u `inc/filteri-stil.php`.
- **GA4** (21.10., Marko: „sredi nalog, makni stare, podesi dashboard“): glavno svojstvo 337396867 — čuvanje podataka o događajima 2 → 14 mjeseci; `contact` označen kao ključni događaj (`generate_lead` i `sign_up` kad se pojave); Reports snapshot: Sesije / Dodato u korpu / Kupovine / Prihod, najprodavaniji artikli, izvori sesija, korisnici po gradu, ključni događaji, Google pretraga po upitu, stranice, uređaji. Veze (Google Ads 547-423-4044, Merchant Center, Search Console) su ispravne. Prazna svojstva 343282984 („www.ladymom.me - GA4“) i 388788730 („ladymom.me - GA4“, vremenska zona LA, USD) nijesu dobijala podatke — za brisanje (Marko, Admin → Property details → Move to Trash). 343127380 („ladymom.me“) ostaje: istorija starog sajta avg–okt 2026.
- **Kupovine u GA4 i Meti** (21.10., Marko: „pokazuje purchases koje smo testirali i otkazane“): `inc/mjerenje-porudzbine.php`.
  - Test porudžbine (natalija+test…) dobijaju unaprijed oznake `_googlesitekit_ga_purchase_event_tracked` i `_meta_purchase_tracked_browser/_server`, pa ih Site Kit i Meta ne šalju kao kupovinu.
  - Otkazana ili vraćena porudžbina koja je otišla u GA4 → `refund` preko Measurement Protocol-a (jednom, `_lm_ga4_povrat`, bilješka na porudžbini). GA4 to oduzima od „Purchase revenue“; broj kupovina („Purchases“) se ne mijenja.
  - API secret upisuje Marko: Podešavanja → Opšte → „GA4 Measurement Protocol – API secret“ (opcija `lm_ga4_mp_secret`, nije u kodu). Pri prvom čuvanju jednom se šalje povrat za test kupovine iz GA4 od 20.10.: 51485 (116,10 €), 51487 (9 €), 51492 (129 €) — `lm_ga4_test_povrati_2010`.
  - Porudžbine iznad #50960 (testovi 19–20.10. i #51479) više ne postoje u WooCommerce-u (obrisane trajno, ne mojom akcijom).
- **UTM pravila (m8)** (21.10.): tabela u dokumentu „Lady Mom — oglasi, AI i mjerenje“ (odjeljak „UTM linkovi“). QR kodovi: butik → `/prodavnica/?utm_source=butik&utm_medium=qr&utm_campaign=kasa`, letak → `/skola-za-trudnice/?utm_source=letak&utm_medium=qr&utm_campaign=ready-mom`. GA4: grupa kanala „Lady Mom kanali“ (podrazumijevana + „QR i letci“ = medium qr, „Poruke“ = medium poruka), postavljena kao glavna.
- `inc/seo-dorade.php`: preusmjerenje starih adresa škole (/ready-mom/ …) sada zadržava parametre (UTM) — ranije su se gubili.
- **Clarity brojevi** (21.10., Marko izabrao „sajt sam čuva podatke“; Windsor besplatni paket je pun 3/3): `inc/clarity-podaci.php`. Token: Podešavanja → Opšte → „Microsoft Clarity – API token“ (`lm_clarity_api_token`). Dnevno u 05:20 UTC (WP cron `lm_clarity_dnevno`) 4 poziva Data Export API-ja (ukupno, Device, Channel, URL), posljednja 24 sata; čuva se u `lm_clarity_d_GGGGMMDD`, spisak u `lm_clarity_dani`, 400 dana. Pregled: Alati → Clarity brojevi (i dugme „Povuci sada“, najviše jednom na sat). Ponedjeljkom 05:40 UTC email „[Clarity] sedmični brojevi …“ na natalija@ladymom.me (`lm_clarity_sedmicno`), iz kojeg čita sedmični izvještaj.
- **Clarity levak „Kupovina“** (21.10.): Proizvod (URL sadrži /proizvod/) → Plaćanje (/placanje/) → Porudžbina primljena (order-received). Clarity → Dashboard → Funnels.
- Sedmični izvještaj (zakazani zadatak, ponedjeljak) od 21.10. čita i Clarity email; pomjeren na 8:23.
- **Clarity događaji** (21.10.): `inc/mjerenje.php` šalje `clarity('event', …)`: `dodaj_u_korpu` (WooCommerce `added_to_cart`), `klik_dodaj_u_korpu`, `kontakt` (telefon/WhatsApp/Viber), `kupovina` (stranica order-received). Služe za smart events i levak u Clarity-ju. Clarity maskiranje: Balanced + elementi `.woocommerce-customer-details`, `.woocommerce-MyAccount-content`, `.woocommerce-order-overview` (adresa, email i podaci naloga se ne snimaju).

## SEO pravila (21.10.2026, skill „static-site-seo“; Marko: „ajde“)
Provjera cijelog sitemapa (502 adrese) po SEO pravilima, pa popravke.
- **`inc/seo-pravila.php`** (novo):
  - Jedno pravilo za noindex i sitemap: `lm_seo_bez_indeksa_postovi()` (Default artikli + artikal Ready Mom #51472) i `lm_seo_bez_indeksa_kategorije()` (korijen „Prodavnica“ #104, „Bez kategorije“ #23, „Škola za trudnice“). Isto pravilo puni `wpseo_robots` i `wpseo_exclude_from_sitemap_by_post_ids` / `…_by_term_ids`. Filteri za Default artikle iz `inc/seo-dorade.php` su prebačeni ovdje.
  - Naslovi do ~60 znakova: artikal „Naziv | Lady Mom Podgorica“ → „| Lady Mom“ → samo naziv; artikli istog naziva (ERP pravi poseban artikal za svaku boju) dobijaju boju („…, zelena“). Kategorije bez ručnog Yoast naslova: „Bodi za bebe – kupite online | Lady Mom Podgorica“, „Naočare za sunce: 0-2 godine – …“, „Sve za trudnice i mame – …“. Brendovi: „Spectra u Crnoj Gori – butik za trudnice i mame | Lady Mom“ (kraće ako ne staje). Ostalo: predug naslov gubi „– Lady Mom“. Dužina se mjeri bez HTML entiteta (Yoast separator je `&ndash;`).
  - Meta opisi do 160 znakova: artikli = rečenice iz opisa (do 110) + boja + „Besplatna dostava po Crnoj Gori od 100 €.“ / „Plaćanje pouzećem ili karticom.“ / „Besplatno preuzimanje u butiku u Podgorici.“ dok staju (`lm_seo_opis_artikla()`, poziva se iz `seo-dorade.php` i `telefon-i-seo.php`). Kategorije bez ručnog opisa isto. Sve ostalo preko 160 se skraćuje na cijele rečenice.
  - Alt slike artikla: ERP šalje ime fajla („20024227_1.jpg“); na prikazu ide naziv artikla (+ boja). Podatak u bazi ostaje.
  - H1 podkategorija „0-2 godine“, „Odrasli“ → „Naočare za sunce: 0-2 godine“ (ručni `lm_h1` ima prednost).
- **Yoast podešavanja**: šabloni bez engleskog „Archives“ (oznake, formati, WoodMart tipovi; arhiva proizvoda „Prodavnica“). Kategorije bloga: naslov „%%term_title%% – Lady Mom blog za trudnice i mame“, opis „%%term_title%%: članci i savjeti za trudnice i mame na Lady Mom blogu. Lady Mom je butik za trudnice, mame i bebe u Podgorici, Branka Deletića bb.“
- **Ručni Yoast naslovi/opisi**: Prodavnica #47, početna #3228 (opis 160), Dostava #35897, Načini plaćanja #35821, Povrat #11, Vodič kroz veličine #50984, Uslovi #35948, Politika privatnosti #3, članci #50961 (prava na porođaju) i #50979 (ishrana).
- **Interni linkovi**: Politika privatnosti je linkovala `https://www.ladymom.me/` → `https://ladymom.me/`. Pogrešan link `/povratrobe-i-sredstava/` (radio preko preusmjerenja) ispravljen u Kontakt #4439, tabu „Dostava i povrat“ #28948, Načini plaćanja, Dostava i WoodMart blokovima; rezerva u opciji `lm_backup_povratrobe_21_10`.
- **Provjereno**: nepostojeće adrese vraćaju pravi 404; `http://`, `www.` i stare adrese (/ready-mom/, /shop/, /shop-2/, /brand/…, /featured_item/, artikal Ready Mom, kategorija škole) vraćaju 301; robots.txt ne blokira OAI-SearchBot ni GPTBot; svaka adresa iz sitemapa vraća 200 bez preusmjerenja, sa canonical na sebe i jednim H1.
- **Povratak**: izbaciti `seo-pravila.php` iz liste u `functions.php` (pa vratiti dva filtera za Default artikle u `seo-dorade.php` iz Git istorije).

## Mjerenje početka plaćanja (21.10.2026, Marko: „sredi prvo sve što treba“ za oglase)
- **GA4 `begin_checkout`**: `inc/mjerenje.php`, odjeljak 6. Site Kit šalje samo `add_to_cart` i `purchase` (provjereno u izvornom kodu Site Kit-a), pa se početak plaćanja u GA4 nije bilježio (0 od prelaska).
  - Hook `woocommerce_after_checkout_form` (plaćanje je klasični `[woocommerce_checkout]`, stranica #9 `/placanje/`), preko Site Kit `gtag` i Consent Mode-a.
  - Artikli kao kod Site Kit-a: `item_id` = ID glavnog proizvoda, naziv, prva kategorija, varijanta, cijena, količina; `value` = zbir cijena × količina; `coupon` ako postoji.
  - Jednom po sadržaju korpe u sesiji pregledača (`sessionStorage` ključ `lm_bc_<cart hash>`).
  - Povratak: obrisati odjeljak 6 iz `inc/mjerenje.php`.
- **Meta `InitiateCheckout`**: dodatak ga ubacuje na `/placanje/`, ali Meta ga ne prima, jer je izvor ladymom.me u Meti svrstan u „Health & wellness condition“ (Events Manager → Datasets → Manage categories): neki standardni događaji se blokiraju, AddToCart i Purchase stižu samo preko Conversions API-ja. Ne rješava se kodom.
- **Google Ads**: za licitiranje se broji samo kupovina iz GA4 („Lady Mom (web) purchase“); auto-tagging uključen. Stara konverzija „Begin checkout (Page load ladymom.me/checkout)“ ne radi od 17.10. (plaćanje je sada `/placanje/`), ali nije u ciljevima naloga.

## Pregled sajta 21.10.2026: stavke koje je Marko potvrdio u to-do listi
Kod: `inc/pretraga.php`, `inc/kategorije-dugmad.php`, `inc/pregled-dorade.php`, `inc/povezani-proizvodi.php`, dopune u `jezik.php`, `kolacici.php`, `ready-mom.php`, `ux-dodaci.php`, `google-feed.php`, `preusmjerenja.php`, `emailovi-4-dodatni.php` i šabloni `woocommerce/emails/waitlist-*.php`.
- **r1** WoodMart `cookies_info` = 0 (druga traka za kolačiće; Complianz ostaje). Rezerva `lm_backup_wd_cookies_21_10`.
- **r2 Pretraga**: upiti o školi vode na `/skola-za-trudnice/`, u padajućoj pretrazi samo Ready Mom; u rezultatima podrazumijevano „relevance“; naprijed nazivi sa cijelom riječju ili sinonimom (filter `posts_search_orderby`, prioritet 30, poslije WoodMart-ovog na 20); sinonimi važe i za nazive kategorija, brendova i atributa; pretraga po **opisu** kategorije isključena („nosiljke“ 157 → 8, „pojas“ 89 → 17). WoodMart „Search synonyms“ prepisan u oblik koji WoodMart čita: `pogrešno = tačno, tačno 2` (68 redova; red bez `=` WoodMart preskače). Rezerve: `lm_backup_pregled_r2_r3` (prije svega), `lm_backup_sinonimi_21_10_v2` (stari oblik sa zarezima).
- **r3** Garderoba (#106), Bebe (#130), Prodavnica (#104): `display_type` = products; redoslijed podkategorija po potražnji (term meta `order`); podkategorije kao dugmad iznad artikala na telefonu/tabletu (`kategorije-dugmad.php`). Rezerva `lm_backup_pregled_r2_r3`.
- **r5** Bankart opis „Visa i Mastercard.“ (rezerva `lm_backup_bankart_opis_21_10`); red 3D Secure u `<span>`; „Poređano“; Vodič kroz veličine „izbor“.
- **r6 Lista čekanja** (WoodMart waitlist): uključena i za goste, saglasnost na crnogorskom (rezerva `lm_backup_waitlist_21_10`). Rasprodate varijante se mogu izabrati samo u formi na stranici artikla (`pre_option_woocommerce_hide_out_of_stock_items` oko `woocommerce_single_product_summary` 29–31); kad je izabrana rasprodata veličina, „Dodaj u korpu“ se sklanja. Emailovi (potvrda prijave, prijava potvrđena, ponovo na stanju) u dizajnu ostalih emailova. Spisak prijavljenih: Proizvodi → Waitlists. Probne prijave (natalija+test-waitlist…) odjavljene.
- **r10** „Često se kupuje uz“: WoodMart paketi #51522–#51527 bez popusta (popusti iz ERP-a), glavni artikli imaju meta `woodmart_fbt_bundles_id`. Povezani proizvodi: ista podkategorija, pa grupa, isti brend i najprodavaniji, bez istog naziva (`povezani-proizvodi.php`).
- **r11** Škola na telefonu: dugme „Prijavi se i plati“ u prvom ekranu (`ready-mom.php`, ≤520 px).
- **r13** „sa PDV-om“ samo na stranici artikla, korpi i plaćanju.
- **r14** Traka za kolačiće na telefonu manja: Prihvati i Odbij jedno pored drugog, „Podešavanja“ kao link.
- **r16** Korpa na telefonu: traka sa ukupnim iznosom i „Kreni na plaćanje“; bez donje trake na korpi i plaćanju.
- **r17** Stranica „Česta pitanja“ #51528 (`/cesta-pitanja/`, samo odgovori koji već postoje na sajtu), sklopiva pitanja (blok „Details“), podaci za Google (FAQPage) se prave iz blokova. Objavljena 22.10. (Marko: „publishuj … i postavi je u footer“); `/cesta-pitanja/` više ne vodi na Dostavu (pravilo u `preusmjerenja.php` važi samo dok stranica nije objavljena). Link u footeru: WoodMart HTML blok „Lady Mom footer“ #35698, lista „Korisni linkovi“, poslije „Povrat robe i sredstava“; rezerva sadržaja bloka `lm_backup_footer_21_10`. Meni na telefonu „Mobilni – Lady Mom“ (#521): stavka „Česta pitanja“ #51531 između „Vodič kroz veličine“ i „Kontakt“ (Marko: „postavi“); stari redoslijed u `lm_backup_meni_mobilni_22_10`.
- **r18** „Šifra: N/A“ skrivena dok šifra nije poznata; veličine istim redom kao kod izbora; `viber://` dozvoljen; meni „Mobilni – Lady Mom“, stavka #51394 → `viber://chat?number=%2B38267030655` (rezerva `lm_backup_viber_meni_21_10`); Lady Mom znak kao slika autora.
- **r20** REST spisak korisnika samo za prijavljene; `?author=N` (Yoast „disable-author“ šalje na početnu, bez korisničkog imena); `DISALLOW_FILE_EDIT`; bez `X-Powered-By`. Listanje foldera: Marko u cPanel-u (Indexes → No Indexing).
- **r21** Automatska ažuriranja isključena za Elementor, WooCommerce, WoodMart Core i velike verzije WordPress-a; LiteSpeed „Purge All On Upgrade“ uključeno; LiteSpeed 7.9.1 → 7.9.2 (21.10. u 23:31, uz backup AIOWPM od 21.10. 15:21); poslije provjereni početna, artikal, korpa, plaćanje, kontakt, kategorija, pretraga. Rezerva `lm_backup_pregled_r21_r22_r25`.
- **r22** Pingback isključen, WooCommerce ne šalje podatke o korišćenju, zaostali zadaci Google for WooCommerce otkazani, najviše 10 revizija. Brisanje MC4WP i rezervnih tema: Marko.
- **r25** Yoast: Facebook i Instagram u podacima o firmi, autori bez arhive; opis za Politiku privatnosti.
- **r26** U podacima artikla (schema.org Offer): dostava 4,50 € (0 € od 100 €), Crna Gora, 0–1 + 1–3 dana; povrat 14 dana poštom. Feed: `g:google_product_category` po kategorijama sajta. Dostava i povrat u Merchant Center-u: Marko.
- **r27** Mjerenje brzine zakazano za 22.10. ujutru.
- Primijećeno: REST zahtjev za sadržaj stranice Kontakt (#4439, Elementor) vraća 500 (memorija 256 MB); stranica za kupce radi. Ostale stranice preko REST-a rade.

## Google Search kampanje i automatska pravila (21.–22.10.2026)
Lista „Lady Mom oglasi“, stavke k4 i z3 (Marko odobrio, „odradi ovo sa liste“). Ovo je u nalozima za oglase, ne u kodu sajta.
- Sve kampanje su **pauzirane**. Uključuje ih Marko kad pogleda tekstove i odredi budžet (stavka k1).
- **Google Ads nalog 547-423-4044 vodi novac u USD** (valuta naloga se ne može promijeniti). Iznosi su preračunati po kursu oko 1,12 $ za 1 €.
- „Search – Kategorije (Lady Mom)“ (24387535387): 6,70 $ dnevno (oko 6 €), Maximize clicks sa plafonom 0,50 $ po kliku, Crna Gora (2499), jezici sr/hr/en, 10 isključujućih riječi (polovno, olx, besplatno, posao, kroj, šablon…). Grupe i odredišta: Grudnjaci za dojenje → `/kategorija/prodavnica/mame/garderoba/ves/`, Spectra izdajalice → `/marka/spectra/`, Jastuk za trudnice → `/kategorija/prodavnica/mame/oprema/jastuci/`, Koala pojas → `/proizvod/koala-pojas-za-trudnice/`, Farmerke za trudnice → `/kategorija/prodavnica/mame/garderoba/farmerice-i-pantalone/`. U svakoj grupi 7–11 ključnih riječi (fraza i tačno podudaranje) i jedan RSA (10 naslova, 3 opisa).
- „Search – Škola za trudnice (Ready Mom)“ (24381664523): 3,35 $ dnevno, plafon 0,56 $, isključene beograd, novi sad, niš, zagreb, sarajevo, banja luka, split, besplatno, posao; 11 ključnih riječi; → `/skola-za-trudnice/`. Brojevi u oglasu su sa stranice škole (4 nedjelje, 8 predavanja, 6 stručnjaka). *(Zamijenjeno 23.10.2026: 2 nedjelje, 6 predavanja, 6 stručnjaka – vidi „Škola: 2 nedjelje…“.)*
- „Search – Brend Lady Mom“ (24387569899): 1,70 $ dnevno, plafon 0,28 $, isključene posao i oglasi; 8 ključnih riječi; → početna.
- Proširenja na nivou kampanje: poziv 067 030 655; callouts (Besplatna dostava od 100€, Butik u Podgorici, Plaćanje pouzećem, Rate NLB karticama, Dostava širom Crne Gore; za školu 8 predavanja, 6 stručnjaka, Uživo ili online, Program od 4 nedjelje); sitelinkovi Garderoba za trudnice, Oprema za dojenje, Škola za trudnice (kod škole Jastuci za trudnice), Kontakt i butik; strukturirani isječak „Brands“: Spectra, Haakaa, Koala Babycare, Mamalicious.
- Google ne dozvoljava broj telefona u tekstu oglasa: prvi RSA u 4 grupe (opis sa brojem) je pauziran i zamijenjen verzijom „v2“ bez broja; broj se prikazuje preko proširenja za poziv. Ključnu riječ „ves za trudnice“ Google nije prihvatio (politika „Health in personalized ads“), „veš za trudnice“ jeste.
- **Automatska pravila u Google Ads-u** (Alati → Grupne radnje → Pravila; rezultat na email natalija@ladymom.me):
  - „Lady Mom – pauziraj ključne riječi bez prodaje“: svaki dan 6–7 h, zadnjih 30 dana: trošak > 25 $ (oko 22 €, prelomni trošak po kupovini uz ROAS 2,2) i konverzija < 1 → ključna riječ se pauzira.
  - „Lady Mom – budžet potrošen do podneva“: svaki dan 12–13 h, podaci za danas: email ako je kampanja do podneva potrošila bar oko pola dnevnog budžeta. Google ne može da poredi trošak sa budžetom, pa ima 5 nivoa: budžet < 3 $ → trošak ≥ 1,5 $; 3–6 $ → ≥ 3 $; 6–12 $ → ≥ 6 $; 12–25 $ → ≥ 12 $; od 25 $ → ≥ 25 $.
- **Automatska pravila u Meti** (Ads Manager → Automated rules, nalog 417968963841429; obavještenje na Facebook-u):
  - „Lady Mom – isključi oglas bez rezultata“: svaki dan u ponoć, zadnjih 7 dana bez današnjeg: potrošeno > 22 € i rezultata < 1 → oglas se isključuje. Rezultat je cilj kampanje (kupovina kod prodajnih, prijava kod kampanje za školu).
  - „Lady Mom – budžet potrošen do podneva“: svaki dan u 12 h, skupovi oglasa sa dnevnim budžetom: potrošeno > 90 % budžeta → samo obavještenje. Za budžet na nivou kampanje Meta nema taj pokazatelj; pravilo sa iznosom se dodaje kad se zna budžet (k1).
- U nalogu postoji staro proširenje za poziv sa brojem +382 67 300 342 (iz starih kampanja). Nove kampanje imaju svoj broj (067 030 655) na nivou kampanje, koji ima prednost.

## Škola: ispravljeni brojevi na slajdu i u meniju (22.10.2026)
Marko potvrdio: škola Ready Mom traje **4 nedjelje, 8 predavanja, 6 stručnjaka** (stari podaci sa letka „2 nedjelje, 6 predavanja, 8+ stručnjaka“ više ne važe). *(Zamijenjeno 23.10.2026: 2 nedjelje, 6 predavanja, 6 stručnjaka – vidi „Škola: 2 nedjelje…“.)*
- **Slajd 4 „Škola“** (slajd #35678, slajder na početnoj): nove slike `2026/10/lm-slajd4-skola-desktop-v5.webp` (#51532, 2400×1400) i `lm-slajd4-skola-mobilni-v5.webp` (#51533, 800×1040). Iste kao v4, promijenjene su samo brojke: desktop „8 · 6 · 4“ (Clash Display 600, 74 px), telefon „8 predavanja · 6 stručnjaka“ (slova prenesena iz iste slike). Zamijenjene u `_elementor_page_settings` slajda (`wd_bg_image`, `wd_bg_image_mobile`), Elementor CSS slajda regenerisan. Rezerva starih vrijednosti: opcija `lm_backup_slajd4_v4`; v4 slike ostaju u Medijima.
- **Meni na telefonu**, opis ispod stavke „Škola“ (Theme Settings → Custom CSS, sekcija `lm-mob-meni`, `li.lm-m-skola>a:after`): „8 predavanja sa 6 stručnjaka, uživo ili online. Prijave: 067 030 655“ (bilo „6 predavanja sa stručnjacima…“). Promijenjeno u opciji `xts-woodmart-options` (`custom_css`) i u WoodMart-ovom generisanom CSS-u: novi fajl `2026/10/xts-theme_settings_default-1792622911.css` (stari ostao), `xts-theme_settings_default-file-data` pokazuje na njega. Rezerve: `lm_backup_custom_css_22_10`, `lm_backup_xts_file_data_22_10`.
- LiteSpeed kritični CSS (`wp-content/litespeed/ccss/`, 3 fajla) je imao stare slike i tekst; zamijenjeno direktno u keš-fajlovima (kad se CCSS sljedeći put generiše, uzima nove izvore). Poslije toga Purge All. Provjereno na početnoj kao gost: nema v4 slika ni starog teksta.
- Ostali tekstovi o školi (stranica škole, artikal #51472, Česta pitanja, početna, šema Course/FAQ, oglasi) već su imali 4 / 8 / 6.

## AI pretrage: prve rečenice i lokacija (22.10.2026, Marko: „kreni sa ove tri stvari“)
Provjera AI pretraga je u projektnom dokumentu „seo-status“ (odjeljak „AI pretrage (AEO), provjera 22.10.2026“). Prva rečenica ispod naslova treba sama da kaže ko, šta, gdje i koliko, da je AI može citirati izvan stranice.
- **O nama (#4400, Elementor)**: H1 widget „Butik za trudnice i mame u Podgorici“ (WoodMart Title #5d57672) dobio je polje „Text“ (`after_title`): „Lady Mom je butik za trudnice, mame i bebe u Podgorici, u ulici Branka Deletića bb, i online prodavnica sa dostavom po cijeloj Crnoj Gori. Butik radi od ponedjeljka do subote, od 9:30 do 21:00.“ Sačuvano preko Elementor Document API (revizija i post_content se prave sami). Rezerva: opcija `lm_backup_onama_22_10` (stari `_elementor_data` i `post_content`).
- **Stranica škole (`inc/ready-mom.php`)**: „Program“ i „Česta pitanja“ počinju rečenicom `.lm-rm-uvod` (4 nedjelje, 8 predavanja, 6 stručnjaka; cijena iz artikla, Family Nest, Branka Deletića bb, uživo ili online).
- **Šema butika (`inc/seo-schema.php`)**: `geo` 42.440672, 19.2304 (Google profil istog mjesta, place_id iz `hasMap`). Napomena: Google profil vodi adresu „Branka Deletića 50, zgrada Kips gradnje, lamela H“, a sajt „Branka Deletića bb“.

## Statistika bez traženja pristanka i čišćenje oglasa (22.10.2026, Marko: „ublažiti za Crnu Goru, statistika bez traženja pristanka“, „odradi ove tri stavke“)
Razlog: od trake za kolačiće (21.10. ~16h) GA4 je pao sa ~200 na ~11 sesija dnevno, Meta PageView sa stotina na sat na par desetina dnevno, a Search Console je ostao isti (nije pad posjeta). Marko zna da je ovo pravno pitanje i da ga treba provjeriti sa pravnikom za zaštitu podataka.
- **`inc/mjerenje.php`**: rani skript `lm-statistika-podrazumijevano` u `wp_head` (prioritet 0) upiše `cmplz_statistics=allow` i `wp_consent_statistics=allow` (godinu dana; Site Kit i Clarity čitaju WP Consent API kolačić `wp_consent_*`, ne Complianz-ov) posjetiocu koji još nije birao. Site Kit (Consent Mode preko WP Consent API-ja) tada daje `analytics_storage` = granted, a Clarity se učitava. Marketing (Meta piksel „hold“, `ad_storage`) i dalje čeka „Prihvati“. „Odbij“ ili isključena Statistika upisuju „deny“ i to se poštuje. Filter koji dodaje ME u „denied“ listu Site Kit-a ostaje (podrazumijevano za oglase).
- **Traka (Complianz → Consent Banner → Texts → Cookie message)**: „Koristimo kolačiće da bi sajt radio (korpa, plaćanje, prijava na nalog) i za statistiku posjeta, da bismo sajt poboljšali. Uz vašu saglasnost koristimo ih i za oglase. Izbor možete promijeniti u svakom trenutku.“ Stari tekst: „… Uz vašu saglasnost koristimo ih i da pratimo posjete i prikazujemo oglase. …“
- **Politika privatnosti (#3)**, odjeljak „Kolačići (cookies)“: statistika radi dok je posjetilac ne isključi; Meta piksel i Google Ads tek uz pristanak za marketing. Stari tekst je u revizijama stranice.
- **Povratak**: ukloniti blok iz `inc/mjerenje.php` i vratiti tekstove; posjetioci kojima je već upisano „allow“ zadržavaju ga dok ne promijene izbor.
- **Meta**: arhivirane 34 stare pauzirane kampanje (2022 – jul 2026, bez troška). Pauzirane ostaju Garderoba, Koala Rodjendan, Spectra – rodjendan i 4 kampanje koje Meta ne da arhivirati jer im je obrisan reel (Haljine ljeto 2024, Haakaa februar mjesec darivanja, 2× New Traffic Campaign).
- **Za Marka (brisanje)**: GA4 svojstva 388788730 i 343282984 (Move to Trash), prazan Meta katalog 4181049515483761 „Lady Mom Catalog“, oglasni nalog 603870486694736 (Deactivate).
- **Dnevna provjera oglasa** (zakazani zadatak): zna za traku; pad GA4 javlja samo ako Search Console ne pada, i ako statistika ne proradi poslije 24.10.

## AI pretrage, drugi krug (22.10.2026 uveče, Marko: „kreni“)
- **Stranica škole (`inc/ready-mom.php`)**: i „Šta je Ready Mom?“ i „Prijavite se za narednu grupu“ počinju rečenicom `.lm-rm-uvod` (škola u Podgorici, 4 nedjelje, 8 predavanja, 6 stručnjaka, uživo ili online; cijena iz artikla, kartica ili gotovina, poziv sa terminom). „Kada upisati“ počinje cijelom rečenicom (činjenice iste: 25.–36. nedjelja).
- **`inc/ai-botovi.php`** (novo): sajt svako jutro (03:05 UTC, cron `lm_ai_botovi_dnevno`) sačuva zbir statistike botova dodatka Imunify Security za prethodni dan (tabela `wp_imunify_bot_hourly` čuva samo oko dva dana). Opcija po danu `lm_ai_botovi_d_GGGGMMDD`, spisak `lm_ai_botovi_dani`, 400 dana. Ponedjeljkom 05:50 UTC (`lm_ai_botovi_sedmicno`) email „[AI botovi] sedmični brojevi ladymom.me …“ na natalija@ladymom.me: po grupama (ChatGPT pretraga, ChatGPT kad korisnik pita, Perplexity, Claude, Google, Bing i Copilot, Apple, Meta, Amazon…) pušteno / odbijeno / najviše u minuti, po danima, i upozorenje ako zaštita odbija botove pretrage. Pregled i dugme „Pošalji sedmični email sada“: Alati → AI botovi. Samo čita statistiku; podešavanja zaštite se ne mijenjaju. Prvi sačuvan dan: 21.10. (nepotpun, statistika počinje 21.10. u 03:00).
- **GA4 (svojstvo 337396867)**: u glavnoj grupi kanala „Lady Mom kanali“ Google-ov kanal „AI Assistant“ preimenovan u **„AI pretrage“** i proširen: uz „Default channel group = AI Assistant“ i „Source partially matches regex `chatgpt|openai|perplexity|copilot\.microsoft|gemini\.google|bard\.google|claude\.ai|deepseek|mistral\.ai|meta\.ai|grok\.com|phind`“. Pomjeren na 8. mjesto (poslije Paid Other, prije Display), iznad Organic i Referral, da ga Referral ne preuzme. Opis grupe dopunjen.

## Feed: razred marže u custom_label_0 (22.10.2026, Marko: ERP izvoz nabavnih cijena, odluka „a) završi upis“)
- **Nabavne cijene** iz ERP izvoza „Pregled izlaza za period“ 01.01.–22.10.2026 (kolona „Prosj. nab. cijena“, bez PDV-a) su u opciji `lm_nabavne_cijene` (autoload off): `['izvor', 'datum', 'c' => [SKU => nabavna]]`, 1.040 barkodova (samo roba, bez usluga iz kategorije „Mame“). Upisane preko jednokratnog pokretača, provjereno brojem, zbirom i md5 cijele mape. Cijene nijesu u Gitu ni u feedu.
- **`inc/google-feed.php`**: svaka stavka dobija `<g:custom_label_0>` sa razredom marže na trenutnu cijenu (akcijsku kad je akcija) bez PDV-a: `a` ≥ 50 %, `b` 35–50 %, `c` < 35 %, `n` bez nabavne cijene. Oznake su neutralne jer je feed javan. Ključ keša feeda je `lm_google_feed_v2`; keš se briše i kad se opcija promijeni.
- **Mjesečno**: novi ERP izvoz se upisuje u istu opciju (stavka z4); bez toga novi artikli dobijaju `n`.
- Po stanju 22.10.: 252 artikla `a`, 82 `b`, 35 `c`, 3 `n` (od 372 u feedu). Za Shopping kampanju (k3) podjela grupa proizvoda po custom_label_0.

## Brzina i pristupačnost početne (22.10.2026 uveče, Marko: „odradi ova tri“)
Mjerenje (stavka r27, 18:10) je pokazalo telefon 54–61, desktop 78–98, pristupačnost 88. Provjera kroz PageSpeed izvještaje (podaci za svaki prolaz) i privremene varijante početne (`?lmx=…`, fajl `inc/test-brzina.php`, uklonjen istog dana) pokazala je:
- Prvi slajd **ne čeka** JavaScript slajdera (prvobitna pretpostavka je bila pogrešna). PageSpeed-ov telefon prvi kadar prikaže tek na ~1,35 s ili ~2,4 s od početka, u svakoj varijanti (i bez kritičnog CSS-a, i sa pravom `<img>` slikom, i bez gornje trake). Zato se u LCP računa sve što krene prije toga.
- Varijanta **bez ijedne skripte** dala je telefon **89** (LCP 2,8 s). Najveći teret su skripte: spojeni JS sajta 213 KB, Google tag 192 KB, Meta piksel 116 KB, Clarity; uz to su krenuli i REST pozivi Contact Form 7 (1,4–2,4 s na serveru) i `get_refreshed_fragments`.
- Telefon je skidao i **desktop sliku slajdera** (118 KB, visok prioritet): `<link rel=preload media=…>` je bio ispisan prije `<meta name="viewport">`, pa je telefon media upit ocijenio na širini 980 px. To važi i za prave telefone, ne samo za PageSpeed.

Urađeno:
- **Preload prve slike**: WoodMart-ovo podešavanje **Preload LCP image** (Theme Settings → Performance, `preload_lcp_image` = uključeno); za početnu #3228 slike desktop #35991 i telefon #35995, vrsta „background“, veličina „full“ (post meta `_woodmart_preload_image*` i Elementor page settings `wd__woodmart_preload_image*`). WoodMart ga ispisuje poslije viewport-a. Sopstveni preload iz `inc/prodaja-i-brend.php` uklonjen. Rezerva: opcija `lm_backup_lcp_22_10`. Sliku Marko mijenja dugmetom „LCP“ u admin traci.
- **Slajdovi 2–4** (`inc/brzina.php`): pozadina tek kad slajd dobije `woodmart-loaded` (WoodMart-ovo pravilo iz `el-slider-block.css`, ovdje i za Elementor slajder; stil sa `data-no-optimize`, da ga LiteSpeed ne prebaci u CSS koji stiže kasnije). WoodMart klasu dodaje na prvi dodir/skrol i pri promjeni slajda; skripta je dodaje svim slajdovima 2 s poslije `load`.
- **Slike artikala i kategorija** (`inc/brzina.php`, `inc/prodaja-i-brend.php`): za `woocommerce_thumbnail` `sizes="(max-width: 767px) 50vw, (max-width: 1024px) 34vw, 300px"` (bilo „100vw do 430 px“, pa je telefon uzimao 768×1024 za pločicu od ~140 px); na početnoj su sve te slike lijene (dobijaju i `sizes="auto"`).
- **Pristupačnost** (100 i na telefonu i na desktopu): tekst „Pretraga“ u dugmetu pretrage samo vizuelno sakriven (`inc/ui-dorade.php`); poruke u gornjoj traci na telefonu visine 24 px i bez preklapanja u smjeni (`@keyframes lmTop`); oznake na karticama (`.product-label`, `.onsale`, `.lm-badge-top`…) slovo `--lm-smedja-tamna` (kontrast 4,53:1); pločice kategorija (`inc/pristupacnost.php`, novo): slika bez duplog linka, link preko pločice dobija ime kategorije umjesto „Product category slug“.

Rezultat (PageSpeed, dva prolaza poslije objave): telefon 62 i 79, LCP 6,0 s i 2,6 s (prije 54–61, LCP 9,4–10,4 s), pristupačnost 100; desktop 73 i 99, LCP 0,6–0,8 s, pristupačnost 100. Telefon i dalje skače ~15 poena između prolaza (najviše zbog TBT-a, tj. skripti). Prije LCP-a se na telefonu skida 1,1 MB umjesto 1,5 MB; desktop slika i slajdovi 2–4 više ne.

Ostaje (Markova odluka):
- Google tag, Meta piksel i Clarity učitati tek na prvu interakciju ili par sekundi poslije učitavanja (LiteSpeed „JS Delayed Includes“). Najveći dobitak na telefonu, ali posjete bez ijednog dodira se ne bi brojale.
- HTML početne i kategorija keširati na Cloudflare-u (Cache Rule sa izuzetkom za korpu, plaćanje, nalog i prijavljene). Stvarni kupci imaju TTFB 1,2 s.
- Spori nekeširani zahtjevi na serveru (CF7 REST 1,4–2,4 s, admin-ajax do 5 s): object cache na hostingu.
- `inc/test-brzina.php` je ostao na serveru (objava ne briše fajlove), ne učitava se; može se obrisati u cPanel-u.

## Meta katalog – brend, veličina, boja, opis (22.10.2026)
- `inc/meta-katalog.php`, filter `facebook_for_woocommerce_integration_prepare_product` (Meta for WooCommerce). Mijenja samo podatke koji idu Meti, ne proizvode.
- Brend iz `product_brand` glavnog proizvoda (varijante su išle kao „Lady Mom“).
- Veličina (`pa_velicina`) i boja (`pa_boja`, bez „Default“) varijante – dodatak prepoznaje samo `size`/`color`.
- Opis kao u Google feedu (`lm_feed_opis`), bez HTML-a i HTML znakova; pol/uzrast i Google kategorija kao u feedu; `custom_label_0` = razred marže (a/b/c/n).
- Meta podatke osvježava pri sljedećoj sinhronizaciji proizvoda (izmjena iz ERP-a ili Meta for WooCommerce → Sync).

## Škola: 2 nedjelje, 6 predavanja, 6 stručnjaka; predavači i pokloni partnera (23.10.2026)
Marko (23.10., poslije Natalijinog maila „SKOLICA – SVI MATERIJALI ZA SAJT“): škola traje **2 nedjelje, 6 predavanja, 6 stručnjaka**, cijena 129 €, predavači po imenu, **bez datuma**, bez advokata, bez e-knjige. Ovo mijenja odluku od 22.10. (4 / 8 / 6).
- **Stranica škole** (`inc/ready-mom.php`): brojke u naslovu, činjenicama, uvodima i čestim pitanjima. Novi „Program“: 6 kartica (tema, predavač i zvanje, pitanja sa Natalijinih letaka) iz `lm_rm_predavanja()`; napomena „sat i po, 18:30–20:00“. Novi odjeljak „Pokloni i pogodnosti partnera“ iz `lm_rm_partneri()` (Lady Mom 20 %, Vegapharm, NLB „Medena štednja“, Uniqa 10–30 %, Bio Save 500 €, MediKid besplatan prvi pregled, Oblak i sunce, Viva Walk 15 %, Origo 10 %, Anastasija Milić). Novo pitanje „Šta polaznice dobijaju od partnera programa?“.
- **Šema Course** (`inc/seo-dorade.php`): opis 2 / 6 / 6, `syllabusSections` za 6 predavanja, `instructor` (6 osoba) i `courseWorkload` PT9H u oba CourseInstance-a.
- **Baza** (rezerva svega: opcija `lm_backup_skola_23_10`): artikal #51472 (opis i kratki opis), Česta pitanja #51528, početna #3228 (lista u kartici škole, Elementor + post_content), Yoast opis #51473 i #35708, nacrti bloga #35708, #51499–#51503.
- **Meni na telefonu**: „6 predavanja sa 6 stručnjaka, uživo ili online…“ u WoodMart Custom CSS, u generisanom `2026/10/xts-theme_settings_default-1792622911.css` (staro: `.prije-23-10`) i u 5 fajlova LiteSpeed kritičnog CSS-a.
- **Slajd 4**: `lm-slajd4-skola-desktop-v6.webp` (#51547, „6 · 6 · 2“) i `lm-slajd4-skola-mobilni-v6.webp` (#51548, „6 predavanja · 6 stručnjaka · uživo i online“), sastavljeni od v5 i v4 bez novog teksta (GD, isti font). Rezerva v5: `lm_backup_slajd4_v5`.
- Slike predavača sa letaka nijesu dodate: Drive linkovi iz Natalijinog foldera traže prijavu, pa ih server ne može preuzeti.
- Ostaje van sajta: Google Ads oglas i callouts za školu („8 predavanja“, „Program od 4 nedjelje“) treba promijeniti u Google Ads-u.
- **Dopuna 23.10. noću** (Marko: „skini slike sa Drive-a… mobilna je baš loša“): slike predavača isječene sa Natalijinih letaka (Drive „READY MOM“ → raspored) i dodate u Medije #51549–#51554 (`ready-mom-predavac-*.webp`, 240×240); na stranici škole okrugla slika uz ime u svakoj kartici, a u šemi Course `image` predavača. Slajd 4 na telefonu: `lm-slajd4-skola-mobilni-v7.webp` (#51555) – gornja fotografija zamijenjena oštrim kadrom sa časa u Family Nest-u (video iz foldera „Slike i video snimci sa skolica / April“), tekst ispod isti kao v6. Rezerva: `lm_backup_slajd4_v6`. Desktop slajd (v6) nije mijenjan.
- **Google Ads** (Marko potvrdio): u grupi „Škola za trudnice Ready Mom“ (kampanja „LM Search – Crna Gora“) novi RSA „Ready Mom v3 – 2 nedjelje, 6 predavanja“ (829299539200, aktivan), stara dva (829276991677, 829337589494) pauzirana; callouts na nivou grupe: „6 predavanja“, „6 stručnjaka“, „Program od 2 nedjelje“, „Uživo ili online“, „Pokloni partnera“.
- **Dostava samo po Crnoj Gori** (Marko „da“, 23.10.): na artiklu (traka povjerenja, `inc/ux-dodaci.php`) „Dostavljamo samo na teritoriji Crne Gore, za 1–3 radna dana“; u footeru (WoodMart → Footer → Copyrights) dodato „Dostava samo na teritoriji Crne Gore.“ Rezerva: `lm_backup_copyrights_23_10`. Razlog: oko 9 % posjeta je iz Srbije (Google pretraga proizvoda po imenu).
- Google Ads „Search – Brend Lady Mom“ (24387569899): Locations → Location options prebačeno na „Presence: People in or regularly in your included locations“ (23.10., iz Markovog Chrome-a; Windsor API tu opciju nema). Sve kampanje sada ciljaju samo ljude u Crnoj Gori.
- 23.10.: ispravka – filter za varijantu dobija ID glavnog proizvoda, pa se varijanta čita iz `external_variant_id` (veličina, boja i razred marže po varijanti). Brend i opis su se već osvježili u Meta katalogu.

## 23.10.2026 – REST indeks i Elementor MCP

- `/wp-json/` je vraćao 500 (PHP: „Allowed memory size of 268435456 bytes exhausted“ u class-wp-rest-server.php). Elementor MCP zato nije mogao da se poveže.
- `inc/brzina.php`: na `rest_api_init` memory_limit 512M (samo REST zahtjevi).
- Za Marka: OPcache nije uključen (cPanel → Select PHP Version → Extensions → opcache). MCP konfiguracija u Claude Desktop mora pokazivati na ladymom.me, ne dev.ladymom.me.
- **ERP uzbuna** (`inc/erp-uzbuna.php`): WP cron `lm_erp_uzbuna_provjera` svakih 15 min čita `last_access` API ključa „TiramisuERP“. Ako ERP ćuti > 60 min → email „[ERP] Sinhronizacija sa sajtom je stala“; kad proradi → „[ERP] Sinhronizacija ponovo radi“. Primaoci u opciji `lm_erp_uzbuna_primaoci` (zarez), stanje u `lm_erp_uzbuna_stanje`. ERP normalno šalje ~800 zahtjeva na sat, 0–24h.
- **/staging/** (stara WP Staging kopija starog sajta iz 2023, baza ladymomm_prod, prefiks wpstg0_): u `public_html/staging/.htaccess` dodato `Require all denied` i isključen Wordfence `auto_prepend_file` (fajl više ne postoji, pa je svaki zahtjev davao PHP Fatal). Kopija: `.htaccess.prije-23-10`. Folder briše Marko.
- WP-Cron „could_not_set“ (~1 dnevno): trka dva istovremena cron pokretanja, zakazani zadaci se ne gube (provjereno 23.10.: nijedan zakasnio, Action Scheduler bez zaostataka). Ne dira se.
- **OPcache** uključen 23.10. (cPanel → Select PHP Version → Extensions → opcache, 128 MB). Memorija na početku zahtjeva pala sa ~216 MB na ~94 MB.
- **Backup**: JetBackup 5 na hostingu pravi inkrementalni backup cijelog naloga (fajlovi + baze) otprilike svaka 2 dana na eksterni „Storage 4“; 62 tačke vraćanja (23.10.). Vraćanje: cPanel → JetBackup 5 → Restore & Download.
- **Staging** (23.10.): Softaculous staging kopija na `https://ladymom.me/kopija/` (folder `public_html/kopija`, baza `ladymomm_kopija`, Softaculous insid izvora 26_31558). Zaštita iz `inc/kopija-zastita.php` radi (gost dobija 403). Prijava: `/kopija/wp-admin/` istim nalogom. Osvježavanje kopije: Softaculous → WordPress Management → ladymom.me → Staging (prvo obrisati staru kopiju). Na živi sajt se ništa ne prebacuje preko Softaculous „Push to Live“ – izmjene idu kroz Git.
- **Zaštita kopija** (`inc/kopija-zastita.php`, učitava se uvijek iz `functions.php`): kad `home` nije `https://ladymom.me` – bez emailova (`pre_wp_mail`), `DISABLE_WP_CRON`, blokirani spoljni HTTP pozivi, front samo za admina (ostali 403), noindex, bez Google taga i Meta piksela, crvena admin traka „KOPIJA SAJTA“.

## 23.10.2026 – Search Console: returnFees u povratu

- Search Console (Merchant listings) je javljao nekritičnu grešku „Missing field returnFees (in offers.hasMerchantReturnPolicy)“ na 30 artikala.
- `inc/pregled-dorade.php` (`lm_schema_dostava_povrat`): u `hasMerchantReturnPolicy` dodato `returnFees` = `ReturnFeesCustomerResponsibility`, jer stranica „Povrat robe i sredstava“ kaže da kupac snosi direktne troškove vraćanja robe. Ako se uslovi promijene (besplatan povrat), vrijednost ide na `FreeReturn`.
- U Search Console pokrenuta validacija (Validate fix).

## Put kupca na telefonu (23.10.2026)
- Clarity 23.10.: 58 % posjeta iz Instagram aplikacije; na kategorijama prvi ekran bez ijednog proizvoda (naslov, opis, putanja, broj rezultata + traka za kolačiće 308 px); 36 % ne skroluje ni četvrtinu.
- `inc/put-kupca-mobilni.php` (prodavnica, kategorije, brendovi, do 768 px): manji naslov, bez opisa/putanje/broja rezultata na vrhu (ostaju u HTML-u).
- Isti fajl: CLS 0,89 (PageSpeed mobilni) – bočna traka sa filterima je do učitavanja punog CSS-a (LiteSpeed async) stajala iznad proizvoda; sad je od početka sakrivena lijevo kao kod WoodMart-a (`:not(.wd-opened)`).
- `inc/kolacici.php`: traka za kolačiće na telefonu bez naslova/logotipa, manji tekst i dugmad (~200 px umjesto 308). Pravila pristanka ista.
- Oba `<style>` imaju `data-no-optimize="1"`: LiteSpeed CSS Combine je inline stilove prebacivao u kombinovani CSS koji stiže asinhrono, pa bi pravila važila tek posle učitavanja (provjereno 23.10. na stranici za goste). Svaki novi stil koji mora važiti od prvog prikaza treba ovaj atribut.
- Do učitavanja punog CSS-a dugme „nazad“ je bilo iznad naslova, a „Prikaži po“ ispod filtera (proizvodi ~100 px niže pa skok); sad odmah u WoodMart-ovom izgledu za telefon. Proba: srcdoc stranice za goste bez punog CSS-a pa sa njim, prvi proizvodi na istom mjestu (±1 px).
- Rezultat (PageSpeed mobilni, nosiljke, 23.10. ~20 h): CLS 0,89 → 0,37 → 0. LCP ostaje ~8 s (sljedeća stavka). Na telefonu (390×844) prvi red proizvoda sa cijenama na prvom ekranu; panel sa filterima se otvara normalno.
- 23.10. ~20 h sajt je ~15 s vraćao „Error establishing a database connection“ – poklopilo se sa preimenovanjem baze (sekcija ispod), pa se sam vratio.

## 23.10.2026 – čišćenje starog sajta i preimenovanje baze

- **Baza živog sajta preimenovana**: `ladymomm_dev` → `ladymomm_sajt` (i korisnik baze `ladymomm_dev` → `ladymomm_sajt`), cPanel UAPI `Mysql/rename_database` + `rename_user`, `wp-config.php` DB_NAME/DB_USER izmijenjeni. Prekid ~2 s. Kopija starog wp-config: `/home/ladymomm/backup-konfig/wp-config.php.prije-preimenovanja-23-10`.
- **Premješteno u `/home/ladymomm/_za_brisanje/`** (van public_html, ne servira se; trajno briše Marko): `stari_sajt`, `public_html/staging` (stara WP Staging kopija), `public_html/staging.ladymom.me`, `public_html/wordpress` (prazan), `public_html/dev` (prazan), dva All-in-One backup-a dev sajta (13. i 14.10.). Backup produkcije od 21.10. ostaje u ai1wm-backups.
- Ostaje Marku: baza `ladymomm_prod` + korisnik `ladymomm_prod` (stari sajt), Softaculous zapisi dvije „ladymom.me/staging“ instalacije (Remove from Softaculous), folder `_za_brisanje`.
- cPanel Cron Jobs: prvi red (`https://ladymom.me/wp-cron.php?doing_wp_cron`) nema komandu (wget/curl), pa ne radi ništa; WP-Cron radi preko posjeta.

## 23.10.2026 – posle analize kupovina (Marko: „odradi a b d“)
- **a) Pouzeće prvo na plaćanju**: WooCommerce → Plaćanja, redoslijed `cod` = 1 (Mastercard/VISA ostaje 4). Novi kupac na plaćanju vidi „Plaćanje pouzećem“ prvo i izabrano (provjereno kao gost). Razlog: 82 % porudžbina u 2026. plaćeno pouzećem. Povratak: `PUT /wc/v3/payment_gateways/cod {"order":10}`.
- **b) GA4 `view_item` i `add_to_cart` sa stranice artikla** (`inc/mjerenje.php`, sekcija 7). Site Kit add_to_cart šalje samo za dugmad u listama (data-product_id); „Dodaj u korpu“ na artiklu (WoodMart AJAX) nije slao ništa. Sad: `view_item` pri učitavanju artikla, `add_to_cart` na `added_to_cart` kad dugme nema data-product_id i u formi artikla (cijena iz izabrane varijante, `item_variant` „pa_boja: crna, pa_velicina: lxl“). Provjereno kao gost: jedan događaj po dodavanju, u listama i dalje samo Site Kit-ov (bez duplog brojanja), bez JS grešaka.
- **d) Prazan tab „Recenzije (0)“** se ne prikazuje (`inc/pregled-dorade.php`, r9): tab ostaje samo za artikle sa bar jednom odobrenom recenzijom. Recenzije su i dalje uključene, ali dok artikal nema nijednu, nema ni forme za ocjenu.
- Poslije objave: Purge All – LSCache (samo keš stranica).
- **Oglasi na kategoriju (Marko: „sredi sve reklame da vode na kategoriju koja se pominje“)**: Meta reel oglasi „→ proizvod“ (Cuddle Band leopard 120250376171760266, Haakaa Gen 2 120250376174400266) pauzirani; aktivni ostaju isti reelovi na kategoriju (Cuddle Band → Ergonomske nosiljke, Haakaa → Dojenje, pojas → Pojasevi, torba → Torbe i neseseri) i Cache Cœur −30 % → `/marka/cache-coeur/`. Podsjetnik koji bi te reelove na kategoriju večeras pauzirao je isključen. Google Search: svaka grupa već vodi na svoju kategoriju (provjereno). Oglas „-30 % Cache Cœur“ koji vodi na početnu nije u našim oglasnim nalozima (417968963841429 i 603870486694736) – mijenja ga onaj ko ga je napravio.
- **Uslovi na plaćanju** (23.10.; tekst privatnosti, uslova i upozorenja ujednačen na 13 px, tekst uslova ostaje smeđ i kad je greška, crveno je samo upozorenje i okvir, `inc/placanje-uslovi.php`): kad je jedina greška neoznačeno polje uslova (`#terms`), stranica se ne vraća na vrh nego skroluje do polja, polje dobija crveni okvir i poruku ispod; gornja lista grešaka se tada sakriva. Sa više grešaka ostaje standardno WooCommerce ponašanje.
- **Brend filter, izabrani brend** (23.10., `inc/filteri-stil.php`): WoodMart je izabranu karticu blijedio (opacity .5) i dodavao dvostruki prsten; sada tanak okvir boje `--lm-roze-jaka`, logotip pune boje, okrugli „×“ u uglu, bez broja.

## 24.10.2026 – to-do lista (Marko: „lista“)

- **Forma za povrat** (`inc/povrat-forma.php`, n40): potvrda kupcu samo kad broj porudžbine i email (ili prijavljeni kupac) odgovaraju porudžbini, na email iz porudžbine, bez teksta iz forme (ime iz porudžbine). Inače zahtjev stiže samo butiku, a stranica kaže „Zahtjev je poslat“ bez obećanja kopije (`?zahtjev=primljen`). Token `lm_t`/`lm_s` (HMAC sa vremenom; JS ga osvježi preko `admin-ajax.php?action=lm_povrat_token` jer je stranica u kešu), bar 3 s od otvaranja, najviše 9 dana star. Dužine: ime 80, email 100, telefon 30, broj 10, poruka 1500. 5 zahtjeva/sat po IP-u i 30/sat ukupno. Slike po sadržaju (getimagesize; HEIC po `ftyp`). Poruke umjesto `alert`. Test: lažni zahtjev (example.com) samo butiku; pravi sa test porudžbinom #51607 (otkazana, `_lm_test`) potvrda bez upisanog teksta; prebrzo i lažan token odbijeni; test zahtjevi u otpadu.
- **Objava** (`bin/objava.sh`, `.cpanel.yml`, n32): prije kopiranja `php -n -l` za sve PHP fajlove (`/opt/alt/php83/usr/bin/php`); greška = ništa se ne kopira. Kopiranje preko privremenog imena + `mv`; inc/ i šabloni, pa style.css, pa functions.php posljednji. Fajlovi iz `inc/` i `woocommerce/emails/` kojih nema u repozitorijumu se sklanjaju u `/home/ladymomm/lm-objava-uklonjeno/<vrijeme>/` (prva objava 24.10. 00:40: `test-brzina.php`, `cf7-php82.php`). Rezultat objave: `/home/ladymomm/.cpanel/logs/vc_*_git_deploy.log`. GitHub provjera dodatno: `bash -n` skripte i da svaki fajl iz spiska u functions.php postoji.
- **Učitavanje koda** (`functions.php`, `inc/zajednicko.php`, n43): `inc/` se učitava uvijek (opcija `lm_tema_kod` se više ne čita), samo kad je WooCommerce aktivan. `zajednicko.php` prvi: `lm_je_default()`, `lm_em_slika_url()`, `lm_em_sve_ucitano()`. Fajl koji nedostaje ili baci grešku (i ParseError) preskače se; admin vidi upozorenje, a natalija@ladymom.me dobija „[Sajt] Dio koda sajta nije učitan“ (najviše jednom u 12 h po grešci). Kad dio emailova nije učitan, `lm_em_handles()` vraća false, pa WooCommerce šalje svoj šablon.
- **Slike na kategorijama** (`inc/slike-webp.php`, n15): na prodavnici, kategorijama, brendovima i pretrazi prve 4 slike liste `loading="eager"`, prva `fetchpriority="high"`; slike iz headera (panel pretrage i mega meni, 12 slika artikala) ostaju lijene i ne broje se (ranije su one zauzimale prva mjesta: flag `lm_u_headeru` iz `inc/brzina.php`). `inc/seo-pravila.php`: alt koji je samo broj (ERP: „1“, „2“, 34 slike) → naziv artikla.
- **Keš poslije ERP izmjena** (`inc/kes.php`, `inc/aktivni-termini.php`, n11): na `woocommerce_product_object_updated_props` sa cijenom/akcijom/zalihom → `litespeed_purge_post` (artikal + stranice po LiteSpeed Purge pravilima). Promjena spiska aktivnih kategorija/brendova: odmah `litespeed_purge_url` za te termine i početnu, `litespeed_purge_all` najviše jednom u 30 min (`lm_kes_puni_zadnji`, zakazano `lm_kes_obrisi_sve_zakazano`; prethodni skup u `lm_aktivni_termini_skup`). Dnevnici do 30.11.: `lm_kes_puni_log` (cijeli keš, hook i URI bez parametara) i `lm_kes_artikal_log` (artikli). Napomena: LiteSpeed „Purge All Hooks“ sadrži i `create_term`, `edit_terms`, `delete_term`.
- **ERP log** (n12): `lm_erp_log_do` = 1796079599 (30.11.2026 23:59 Podgorica). Logovi prethodnih dana se sažimaju u `.log.gz` pri prvom upisu novog dana (`lm_erp_log_sazmi`). Stanje 24.10.: 11 fajlova, 90 MB.
- **LiteSpeed → Page Optimization → CSS → Load CSS Asynchronously: isključeno** (`optm-css_async` = false, 24.10. 01:03). Kritični CSS (QUIC.cloud) se pravi za računar, pa je na telefonu puni CSS stizao kasnije i pomjerao sadržaj (CLS 0,24 u skoro svakom mjerenju, LCP 7–8 s). PageSpeed mobilni poslije: Helanke 95 / 85, Nosiljke 93 / 91, početna 89, artikal (ATEA) 92; CLS 0–0,003, LCP 2,4–3,2 s. Prije (async): Helanke 58–84, Nosiljke 56–87, CLS 0,24, LCP 2,0–8,0 s. Povratak: `\LiteSpeed\Conf::cls()->update_confs(['optm-css_async' => true])` + Purge All.
- **Google Ads** (n30): sitelink „Škola za trudnice“ u LM Search – Crna Gora „8 predavanja, 6 stručnjaka“ → „6 predavanja, 6 stručnjaka“; u Search – Brend Lady Mom „Ready Mom: 8 predavanja“ → „Ready Mom: 6 predavanja“ (Assets → Sitelink → Edit). **Shopping** (n29): LM Shopping – Crna Gora „Eligible“, 296 artikala odobreno, 0 prikaza (17.–23.10.), ručni CPC 0,20 $ (Search prosjek ~0,45 $); Crna Gora je od jula 2026. zemlja za Shopping oglase. Odluka o ponudi čeka Marka.
- **Merchant Center** (r26, samo pregled): dostava CG, 4,50 €, besplatno od 100 €, 1–4 radna dana (0–1 + 1–3, do 14 h); povrat 14 dana, trošak kupac, poštom, 369 artikala; 366/369 odobreno.
- **Dostava u kodu** (n51): prag besplatne dostave i cijena dostave čitaju se iz WooCommerce → Podešavanja → Dostava (zona Crna Gora: Besplatna dostava → Minimalni iznos 100; Fiksna cijena 3,71900826 bez PDV-a + 21 % = 4,50 €) preko `lm_dostava_prag()`, `lm_dostava_cijena()` i `lm_dostava_tekst()` u `inc/zajednicko.php`; rezerva 100 € i 4,50 €. Koriste ih traka povjerenja na artiklu, predlozi u korpi do besplatne dostave, meta opisi, podaci za Google (OfferShippingDetails) i email („besplatno za porudžbine od 100 €“, a samo „besplatno“ kad je dostava besplatna iz drugog razloga). Iznosi upisani u sadržaj (ako se podešavanje promijeni, mijenjaju se ručno): stranice Dostava (#35897), Uslovi korišćenja (#35948), Česta pitanja (#51528), Načini plaćanja (#35821), Kontakt (#4439, i Elementor); blokovi „Prednosti – traka iznad footera“ (#35813) i „Vodič za veličine – uvod“ (#827); tab artikla „Dostava i povrat“ (#28948); WoodMart opcije (traka u headeru) i Yoast šabloni/opisi kategorija.
- **Preuzimanje u butiku**: jedna provjera, `lm_je_metoda_preuzimanja()` / `lm_je_preuzimanje()` / `lm_porudzbina_preuzimanje()` u `inc/zajednicko.php` (local_pickup i pickup_location), koriste je korpa i plaćanje (`ux-dodaci.php`, `placanje-provjere.php`, `besplatna-dostava.php`) i emailovi (`lm_em_pickup()`).
- **Sitnice iz revizije** (n51): uklonjena stara oznaka „Još samo N komada“ (`lm_low_stock_qty`, `lm_stock_note`; količina se kupcu ne prikazuje); jezik za Yoast samo u `telefon-i-seo.php` (`sr_RS`, uklonjen drugi filter `sr_ME` iz `jezik.php`); stil Lady Mom emailova na jednom mjestu (`emailovi-1`, i za dodatne emailove); dugme u porukama koje zamjenjuju `alert` tamnije (`#5e4f55`, kontrast ~7:1); kopija sajta u log upisuje broj primalaca, ne adrese. Brojevi škole u starijim zapisima ovog dnevnika označeni su kao zamijenjeni 23.10.
- **Emailovi** (n49): šabloni u `woocommerce/emails/` imaju `@version` kao WooCommerce originali (WooCommerce → Status → Šabloni javlja kad se original promijeni; WoodMart šabloni nemaju verziju). Lady Mom emailovi kupcu i butiku pozivaju `woocommerce_email_before_order_table`, `…_after_order_table` i `…_order_meta`, preko kojih dodaci i banke dodaju podatke; ispis ide u poseban okvir (`lm_em_akcije_dodataka()` u `emailovi-3-sadrzaj.php`). Na tim akcijama su sada samo WooCommerce `order_meta` i WoodMart lijeno učitavanje; WoodMart-ovo „paljenje“ lijenog učitavanja se tu preskače (uključilo bi ga do kraja zahtjeva, a u WoodMart-u je isključeno). Ako dorada HTML-a (`lm_em_dovrsi()`) ne uspije, šalje se email bez dorade za tamni režim, umjesto praznog.
- **Nalog uz porudžbinu** (n48, `inc/nalog-uz-porudzbinu.php`): kad gost poruči sa emailom koji već ima nalog, porudžbina se više ne veže odmah za taj nalog. Dobija `_lm_veza_ceka` (ID naloga) i bilješku, korisnik `_lm_ceka_veza` = 1; kad se taj kupac prijavi (lozinka ili Google), na prvom zahtjevu se njegove porudžbine sa istim emailom vežu za nalog (bilješka „Porudžbina je vezana za nalog kupca poslije prijave.“). Nov nalog za nov email radi kao i ranije.
- **Molba za ocjenu** (n2, `inc/molba-za-ocjenu.php`, novo): 10 dana poslije statusa „Završeno“ jedan email po porudžbini (Action Scheduler `lm_molba_za_ocjenu`, grupa `lady-mom`) sa do 6 artikala i dugmetom „Ocijeni“ → artikal `?ocjena=<potpis>#reviews`. Uz ispravan potpis artikal prikazuje formu i kad nema recenzija (tab „Ocijeni artikal“), ime i email su popunjeni, ocjena se objavljuje odmah sa oznakom kupca (`verified`, `_lm_porudzbina`); iz test porudžbine (`_lm_test`) čeka odobrenje. Ostale ocjene artikala čekaju odobrenje (Komentari → Na čekanju). Samo porudžbine od 18.10.2026, bez test porudžbina, bez vraćenog novca. **Isključeno** dok Marko ne odobri probni email: opcija `lm_molba_ocjena` = 1 uključuje. Tekst sa „ti“ (n4).
- **Brzina koda** (n45, dio): tabovi „Novo“ i „Na akciji“ na početnoj (`inc/pocetna-tabovi.php`) traže se od servera tek kad se sekcija sa tabovima približi ekranu (IntersectionObserver, 300 px), a ne poslije svakog učitavanja početne: svaki takav zahtjev je nekešni admin-ajax od ~0,8 s i ~35 KB (mjereno 24.10.: 842 i 801 ms). Klik ostaje trenutan. REST memorija 512 MB (`inc/brzina.php`) samo za zahtjeve sa prijavom (kolačić prijavljenog, Authorization zaglavlje, `consumer_key`), da ERP, MCP i admin rade kao do sada, a gosti i botovi ostaju na 256 MB. Ostaje: prevodi iz 14 filtera u Loco Translate i stalni CSS u `style.css` (veći posao, uz staging).
- **Test porudžbina #51607** (24.10.): napravljena programski za test forme za povrat, odmah otkazana, `_lm_test` = 1 (ERP je ne vidi), email natalija+test-povrat@ladymom.me.
- **Test porudžbine #51610 i #51611** (24.10.): #51610 (Spectra mjerač + ventili, 14 €, natalija+test-ocjena@ladymom.me) za probni email molbe za ocjenu; #51611 (bez artikala, natalija+test-gost3@ladymom.me) za provjeru vezivanja za nalog poslije prijave (n48: prije prijave nije vezana, poslije prijave korisnika #232 vezana, bilješke upisane). Obje `_lm_test`, napravljene kao „Na čekanju plaćanja“ (bez skidanja zaliha) i odmah otkazane. Probni emailovi svih vrsta porudžbina (n49, 11 emailova, naslov „[PROBA n49] …“) poslati na natalija+test-n49@ladymom.me za #51607; svi u Lady Mom dizajnu, bez greške, a WoodMart lijeno učitavanje poslije slanja ostaje isključeno.
