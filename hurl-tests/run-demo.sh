#!/usr/bin/env bash
# Ejecuta las pruebas manuales Hurl en orden. HURL_VERBOSE=1 para ver peticiones/respuestas.
cd "$(dirname "$0")"
OPTS=(--test --variables-file vars.env --color)
[ -n "$HURL_VERBOSE" ] && OPTS+=(--very-verbose)
for f in 0[1-6]-*.hurl; do
  echo; echo "▶ $f"; hurl "${OPTS[@]}" "$f" || exit 1
done
echo; echo "✔ Todas las pruebas pasaron"
