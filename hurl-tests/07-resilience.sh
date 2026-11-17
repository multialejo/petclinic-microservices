#!/usr/bin/env bash
# Demuestra aislamiento de fallos: con ms-customers caído, ms-vets sigue vivo y el gateway responde 503 JSON.
cd "$(dirname "$0")"; cd ..
H=(hurl --test --variables-file hurl-tests/vars.env)
trap 'docker compose start ms-customers ms-customers-app >/dev/null' EXIT
echo "▶ Deteniendo ms-customers..."; docker compose stop ms-customers ms-customers-app >/dev/null
"${H[@]}" hurl-tests/resilience-down.hurl
echo "▶ Levantando ms-customers..."; docker compose start ms-customers ms-customers-app >/dev/null
sleep 5
"${H[@]}" hurl-tests/05-visits-cu08.hurl && echo "✔ Recuperación verificada"
