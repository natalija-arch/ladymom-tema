#!/usr/bin/env python3
"""Pravi .po fajlove za Loco Translate iz prevodi/ti_prevodi.py (n4/n45, 24.10.2026).
Izlaz: prevodi/loco/<putanja>.po; na serveru ih prevodi/ugradi.php kompajlira u
wp-content/languages/loco/ (Loco ih učitava prije sistemskih hrvatskih prevoda)."""
import os, sys, collections
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', 'prevodi'))
from ti_prevodi import P

PUT = {'woocommerce': 'plugins/woocommerce-hr.po', 'default': 'hr.po',
       'complianz-gdpr': 'plugins/complianz-gdpr-hr.po', 'contact-form-7': 'plugins/contact-form-7-hr.po',
       'woodmart': 'themes/woodmart-hr.po'}

def q(s):
    return '"' + s.replace('\\', '\\\\').replace('"', '\\"').replace('\n', '\\n').replace('\t', '\\t') + '"'

po = collections.defaultdict(list)
vidjeno = set()
for dom, ctx, mid, pl, tr in P:
    k = (dom, ctx, mid)
    if k in vidjeno:
        sys.exit('DUPLIKAT: %r' % (k,))
    vidjeno.add(k)
    r = []
    if ctx: r.append('msgctxt ' + q(ctx))
    r.append('msgid ' + q(mid))
    if pl:
        r.append('msgid_plural ' + q(pl))
        for i, f in enumerate(tr): r.append('msgstr[%d] %s' % (i, q(f)))
    else:
        r.append('msgstr ' + q(tr))
    po[dom].append('\n'.join(r))

ZAG = ('msgid ""\nmsgstr ""\n"Project-Id-Version: Lady Mom – prevodi na ti\\n"\n"Language: hr\\n"\n'
       '"MIME-Version: 1.0\\n"\n"Content-Type: text/plain; charset=UTF-8\\n"\n"Content-Transfer-Encoding: 8bit\\n"\n'
       '"Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);\\n"\n'
       '"X-Domain: {dom}\\n"\n')
baza = os.path.join(os.path.dirname(__file__), '..', 'prevodi', 'loco')
for dom, ulazi in po.items():
    f = os.path.join(baza, PUT[dom]); os.makedirs(os.path.dirname(f), exist_ok=True)
    open(f, 'w', encoding='utf-8').write('# Generisano iz prevodi/ti_prevodi.py (bin/prevodi.py). Ne mijenjati ručno ovdje.\n' + ZAG.format(dom=dom) + '\n' + '\n\n'.join(ulazi) + '\n')
    print(dom, len(ulazi), PUT[dom])
