#!/usr/bin/env python3
"""Descarga las tipografías del tema desde Google Fonts a assets/fonts/.

Genera un CSS por emparejamiento (redonda.css, elegant.css, modern.css) con
las reglas @font-face apuntando a los .woff2 locales. Solo los alfabetos
latin y latin-ext (español, portugués, inglés…): el navegador descarga
latin-ext únicamente si la página usa algún carácter de ese rango.

Uso (desde la raíz del tema): python3 scripts/fetch-fonts.py
Los emparejamientos deben coincidir con kdv_typography_pairings() en
includes/core/template-tags.php. Las licencias (SIL OFL 1.1) van en
assets/fonts/OFL.txt.
"""
import hashlib
import re
import urllib.request

UA = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0 Safari/537.36'
PAIRINGS = {
    'redonda': 'family=Baloo+2:wght@500;600;700;800&family=Noto+Sans:wght@400;500;600;700',
    'elegant': 'family=Playfair+Display:wght@600;700;800&family=Lora:wght@400;500;600',
    'modern': 'family=Poppins:wght@600;700;800&family=Inter:wght@400;500;600;700',
}


def get(url):
    return urllib.request.urlopen(urllib.request.Request(url, headers={'User-Agent': UA}), timeout=30).read()


for key, query in PAIRINGS.items():
    css = get('https://fonts.googleapis.com/css2?' + query + '&display=swap').decode()
    out = [f'/* {key}: fuentes servidas desde el propio tema (Google Fonts, licencia SIL OFL 1.1). Solo latin y latin-ext. Generado por scripts/fetch-fonts.py */']
    seen = {}
    for subset, block in re.findall(r'/\* ([a-z-]+) \*/\s*(@font-face\s*\{[^}]+\})', css):
        if subset not in ('latin', 'latin-ext'):
            continue
        url = re.search(r'url\((https://[^)]+\.woff2)\)', block).group(1)
        family = re.search(r"font-family: '([^']+)'", block).group(1)
        if url not in seen:
            name = family.lower().replace(' ', '-') + '-' + subset + '-' + hashlib.md5(url.encode()).hexdigest()[:6] + '.woff2'
            with open('assets/fonts/' + name, 'wb') as f:
                f.write(get(url))
            seen[url] = name
        out.append('/* ' + subset + ' */\n' + block.replace(url, seen[url]))
    with open(f'assets/fonts/{key}.css', 'w') as f:
        f.write('\n'.join(out) + '\n')
    print(key, len(seen), 'archivos')
