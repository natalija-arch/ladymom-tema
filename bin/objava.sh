#!/bin/bash
# Objava child teme woodmart-child na ladymom.me. Pokreće je .cpanel.yml pri svakoj objavi
# (cron na serveru svakih ~5 minuta povuče main; vidi README).
#
# 1) Provjera sintakse svih PHP fajlova. Ako ijedan ima grešku, ništa se ne kopira i objava staje.
# 2) Kopiranje: inc/ i šabloni emailova, pa style.css, pa functions.php posljednji, da novi
#    functions.php nikad ne traži fajl koji još nije stigao. Svaki fajl se kopira pod privremenim
#    imenom i onda preimenuje, da server nikad ne učita pola fajla.
# 3) Fajlovi kojih više nema u repozitorijumu sklanjaju se iz inc/ i woocommerce/emails/ u
#    /home/ladymomm/lm-objava-uklonjeno/<datum-vrijeme>/ (ne brišu se; Marko ih briše kad hoće).
set -u
PATH="/usr/local/bin:/usr/bin:/bin:${PATH:-}"
shopt -s nullglob

CILJ="${LM_CILJ:-/home/ladymomm/public_html/wp-content/themes/woodmart-child}"
SKLONI="${LM_SKLONI:-/home/ladymomm/lm-objava-uklonjeno}"

PHP=""
for p in ${LM_PHP:-} /opt/alt/php83/usr/bin/php /usr/local/bin/php /usr/bin/php; do
	if [ -x "$p" ]; then PHP="$p"; break; fi
done
if [ -z "$PHP" ]; then
	echo "GREŠKA: nema PHP-a za provjeru sintakse. Objava prekinuta, ništa nije kopirano."
	exit 1
fi

echo "Provjera sintakse: $PHP"
LOSE=0
for f in functions.php inc/*.php woocommerce/emails/*.php; do
	if ! izlaz=$("$PHP" -n -l "$f" 2>&1); then
		echo "GREŠKA u $f: $izlaz"
		LOSE=1
	fi
done
if [ "$LOSE" -ne 0 ]; then
	echo "Objava prekinuta: ništa nije kopirano."
	exit 1
fi

kopiraj() {
	cp -f "$1" "$2.lm-novo" && mv -f "$2.lm-novo" "$2"
}

mkdir -p "$CILJ/inc" "$CILJ/woocommerce/emails" || exit 1
for f in inc/*.php woocommerce/emails/*.php style.css; do
	kopiraj "$f" "$CILJ/$f" || { echo "GREŠKA pri kopiranju $f. Objava prekinuta (functions.php nije kopiran)."; exit 1; }
done
kopiraj functions.php "$CILJ/functions.php" || { echo "GREŠKA pri kopiranju functions.php."; exit 1; }

VRIJEME="$(date +%Y%m%d-%H%M%S)"
for d in inc woocommerce/emails; do
	for f in "$CILJ/$d"/*.php; do
		b="$(basename "$f")"
		if [ ! -e "$d/$b" ]; then
			mkdir -p "$SKLONI/$VRIJEME/$d" && mv -f "$f" "$SKLONI/$VRIJEME/$d/$b" && echo "Sklonjeno (nema ga u repozitorijumu): $d/$b"
		fi
	done
done

echo "Objava gotova."
exit 0
