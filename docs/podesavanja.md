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
