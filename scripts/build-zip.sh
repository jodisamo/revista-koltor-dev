#!/usr/bin/env bash
# Genera dist/revista-koltor-dev-<versión>.zip listo para subir en
# Apariencia → Temas → Añadir nuevo → Subir tema (WordPress ofrece
# "Reemplazar el actual por el subido" si el tema ya está instalado).
#
# Empaqueta lo que está en git (HEAD), no la carpeta de trabajo: así el zip
# nunca lleva cambios a medias ni archivos sueltos. Haz commit antes.
# Añade assets/css/main.min.css (scripts/minify-css.py), que el tema usa en
# lugar de main.css cuando existe.
set -euo pipefail

cd "$(dirname "$0")/.."

slug="revista-koltor-dev"
version="$(sed -n 's/^Version:[[:space:]]*//p' style.css | tr -d '\r')"
out="$PWD/dist/${slug}-${version}.zip"

if [ -n "$(git status --porcelain)" ]; then
	echo "Aviso: hay cambios sin commit; el zip usa el último commit (HEAD), no incluye esos cambios." >&2
fi

tmp="$(mktemp -d)"
trap 'rm -rf "$tmp"' EXIT

git archive --format=tar --prefix="${slug}/" HEAD -- . ':(exclude)scripts' ':(exclude).gitignore' ':(exclude).gitattributes' | tar -x -C "$tmp"
python3 scripts/minify-css.py "$tmp/${slug}/assets/css/main.css" "$tmp/${slug}/assets/css/main.min.css"

mkdir -p dist
rm -f "$out"
( cd "$tmp" && zip -qr -X "$out" "$slug" )

echo "Listo: dist/${slug}-${version}.zip ($(du -h "$out" | cut -f1))"
