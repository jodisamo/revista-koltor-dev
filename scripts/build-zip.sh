#!/usr/bin/env bash
# Genera dist/revista-koltor-dev-<versión>.zip listo para subir en
# Apariencia → Temas → Añadir nuevo → Subir tema (WordPress ofrece
# "Reemplazar el actual por el subido" si el tema ya está instalado).
#
# Empaqueta lo que está en git (HEAD), no la carpeta de trabajo: así el zip
# nunca lleva cambios a medias ni archivos sueltos. Haz commit antes.
set -euo pipefail

cd "$(dirname "$0")/.."

slug="revista-koltor-dev"
version="$(sed -n 's/^Version:[[:space:]]*//p' style.css | tr -d '\r')"
out="dist/${slug}-${version}.zip"

if [ -n "$(git status --porcelain)" ]; then
	echo "Aviso: hay cambios sin commit; el zip usa el último commit (HEAD), no incluye esos cambios." >&2
fi

mkdir -p dist
rm -f "$out"
git archive --format=zip --prefix="${slug}/" -o "$out" HEAD -- . ':(exclude)scripts' ':(exclude).gitignore' ':(exclude).gitattributes'

echo "Listo: $out ($(du -h "$out" | cut -f1))"
