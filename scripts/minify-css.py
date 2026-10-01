#!/usr/bin/env python3
"""Minificador de CSS conservador para el zip de producción.

Quita comentarios y espacios sobrantes sin reescribir nada más: no toca el
interior de cadenas ("..." / '...'), ni los espacios dentro de calc()/
color-mix() -- donde un espacio alrededor de + y - es obligatorio --, ni
las comas de las listas. Lo usa scripts/build-zip.sh; en el repositorio se
edita siempre el CSS legible.

Uso: python3 scripts/minify-css.py entrada.css salida.css
"""
import re
import sys


def minify(css):
    out, i, n = [], 0, len(css)
    # 1. Comentarios fuera de cadenas.
    while i < n:
        c = css[i]
        if c in '"\'':
            j = i + 1
            while j < n and css[j] != c:
                j += 2 if css[j] == '\\' else 1
            out.append(css[i:j + 1])
            i = j + 1
        elif css.startswith('/*', i):
            end = css.find('*/', i + 2)
            i = n if end == -1 else end + 2
        else:
            out.append(c)
            i += 1
    css = ''.join(out)
    # 2. Espacios: colapsar y quitar alrededor de { } ; , : > solo donde es seguro.
    css = re.sub(r'\s+', ' ', css)
    css = re.sub(r'\s*([{};])\s*', r'\1', css)
    css = re.sub(r';}', '}', css)
    css = re.sub(r'\s*,\s*', ',', css)
    css = re.sub(r'\s*>\s*', '>', css)
    # ":" solo en declaraciones (propiedad: valor), nunca en selectores como "a :hover".
    css = re.sub(r'([{;])\s*([-\w]+)\s*:\s*', r'\1\2:', css)
    return css.strip() + '\n'


if __name__ == '__main__':
    src, dst = sys.argv[1], sys.argv[2]
    with open(src, encoding='utf-8') as f:
        data = f.read()
    result = minify(data)
    with open(dst, 'w', encoding='utf-8') as f:
        f.write(result)
    print(f'{src}: {len(data.encode()) // 1024} KB -> {len(result.encode()) // 1024} KB')
