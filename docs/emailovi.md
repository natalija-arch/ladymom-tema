# Lady Mom – emailovi kupcima (ugrađeno 9.10.2026, dopuna 18.10.2026)

Dizajn sa kanvasa „Lady Mom — emailovi kupcima“ (artifact 1LNXPUX5NaZrU3C8hMVwG6), odobren 9.10. Marko je pisao da njegove izmjene na kanvasu treba zanemariti.

## Kako radi
- **Child tema** (ranije Code Snippets 22–24): `inc/emailovi-1-tekstovi.php` (tekstovi i naslovi), `inc/emailovi-2-okvir.php` (zaglavlje i footer), `inc/emailovi-3-sadrzaj.php` (sadržaj), `inc/emailovi-4-dodatni.php` (dodatni emailovi, 18.10.). Sve funkcije počinju sa `lm_em_`.
- **Šabloni** u `woodmart-child/woocommerce/emails/`: `email-header.php`, `email-footer.php`, 10 šablona `customer-*.php` i (18.10.) `customer-verify-email.php`, `back-in-stock.php`, `on-sale-product.php`, `promotional.php`, `abandoned-cart.php`. Ako funkcija ne postoji, pada na originalni šablon (nema greške).
- Napomena: WooCommerce akcija `woocommerce_email_header` ne prosljeđuje `$email`, zato body šabloni direktno zovu `lm_em_header`.
- `woocommerce_email_styles` za naše emailove vraća samo osnovni CSS (core CSS je dodavao margin na slike).
- WooCommerce ukloni sve sa `display:none` (HtmlPruner), pa je preheader sakriven sa `max-height:0; opacity:0`.
- Slike: opcija `lm_email_media` → `zaglavlje` = 35979 (`email-zaglavlje.png`, 1200×224), `logo_bijeli` = 35980 (256 px, za footer). Slike proizvoda: veličina `thumbnail` (150×150, kvadrat – Gmail ne podržava object-fit).
- Admin emailovi (nova porudžbina, otkazana, neuspjela – za Nataliju) ostaju WooCommerce šablon, sa brend bojama i naslovima na crnogorskom.

## Emailovi u novom dizajnu
| Email | Naslov | Stanje |
|---|---|---|
| Porudžbina primljena | Hvala, {ime}! Porudžbina #{broj} je primljena | uključen |
| Na čekanju | Porudžbina #{broj} je primljena | uključen |
| Završena – kurir | Paket #{broj} je na putu | uključen |
| Završena – butik | Porudžbina #{broj} je spremna za preuzimanje | uključen |
| Neuspjelo plaćanje | Plaćanje za porudžbinu #{broj} nije prošlo | uključen |
| Otkazana | Porudžbina #{broj} je otkazana | uključen |
| Povrat / djelimičan povrat | Vratili smo novac / dio iznosa za porudžbinu #{broj} | uključen |
| Poruka uz porudžbinu | Nova poruka o porudžbini #{broj} | uključen |
| Detalji porudžbine (ručno) | Detalji porudžbine #{broj} | isključen |
| Novi nalog | Dobro došli u Lady Mom, {ime} | uključen |
| Nova lozinka | Postavite novu lozinku za Lady Mom | uključen |
| Potvrda email adrese (WooCommerce, 18.10.) | Potvrdite email adresu za Lady Mom | uključen |
| Napuštena korpa (WoodMart, 18.10.) | Vaša korpa vas čeka, {ime} | uključen, šalje se 12 h poslije, bez kupona |
| Lista želja – ponovo na stanju (WoodMart, 18.10.) | Artikal sa vaše liste želja je ponovo na stanju | uključen 18.10. (Markova odluka), WoodMart provjerava svaki sat |
| Lista želja – na popustu (WoodMart, 18.10.) | Artikal sa vaše liste želja je na popustu | uključen 18.10. (Markova odluka), WoodMart provjerava svaki sat |
| Lista želja – promotivni (WoodMart, ručno slanje) | Posebna ponuda za vas iz Lady Mom, ako admin ne upiše svoj naslov | uključen |

Emailovi liste želja idu samo kupcima koji su na listi želja uključili obavještenja (tabela `woodmart_marketing_consent`; 18.10. jedna saglasnost).

Naslove i tekstove mijenjaju `lm_em_copy()` i `lm_em_dodatni_copy()` (filteri imaju prednost nad podešavanjima u WooCommerce → Emailovi). Dodatni sadržaj („Additional content“) iz podešavanja se ne prikazuje.

Sadržaj po emailu: nadnaslov (broj i datum), naslov, traka statusa (kurir: Primljena · Pripremamo · Na putu; butik: … · Spremna), kartice Dostava/Plaćanje (pouzeće kurir/butik, kartica = rezervacija, NLB rate iz `_bankart_instalments`), blok preuzimanja sa „Otvori mapu“ i „Pozovi butik“, proizvodi, zbir sa PDV-om, adresa i napomena, „Pitanje o porudžbini?“ (WhatsApp + telefon), Family Nest blok (samo u „primljena“ i „novi nalog“), tirkizni footer sa adresom, linkovima i Tangerine Limited d.o.o. · PIB. Emailovi liste želja i korpe imaju u footeru razlog slanja i link „Ne želim više ove emailove“.

Napomena (18.10.): u promotivnom emailu `get_option('subject')` iz filtera naslova ponovo ulazi u isti filter, pa se čita `$email->settings[...]` direktno.

## Test 9.10.2026
Poslato 14 emailova na markohidic26@gmail.com (3 porudžbine: kurir/pouzeće 140 €, butik 40 €, kartica na 6 rata 84,50 €; + na čekanju, poslata kurir i butik, neuspjela, otkazana, poruka, detalji, djelimičan povrat 20 €, novi nalog, nova lozinka). Testne porudžbine i proizvodi su u otpadu.

## Test 18.10.2026
Dodatnih 5 emailova renderovano na sajtu sa pravim artiklima (bez slanja) i pregledano u pregledaču. Pri prelasku 17.10. emailovi kupcima za uvezene porudžbine nisu poslati (bili su isključeni; log `transactional-emails-2026-10-17`). Napuštenih korpi do 18.10. nije bilo, pa engleski email korpe nije otišao nikome.

Na 360 px širine email staje bez horizontalnog skrola (najuži red: kartice Dostava/Plaćanje).
