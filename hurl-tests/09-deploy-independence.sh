#!/usr/bin/env bash
# Independencia de despliegue: se apaga ms-vets, se verifica que customers/visits siguen vivos y
# se redespliega solo ms-vets mientras se sondea ms-customers (debe haber 0 respuestas no-200).
cd "$(dirname "$0")"; cd ..
H=(hurl --test --variables-file hurl-tests/vars.env)
trap 'docker compose start ms-vets ms-vets-app >/dev/null 2>&1' EXIT

echo "▶ Deteniendo ms-vets..."; docker compose stop ms-vets ms-vets-app >/dev/null
"${H[@]}" hurl-tests/09-vets-down.hurl && echo "✔ customers y visits operan sin ms-vets"
docker compose start ms-vets ms-vets-app >/dev/null; sleep 3

echo "▶ Redesplegando solo ms-vets (reconstrucción + reinicio) mientras se sondea ms-customers..."
probe() { while [ ! -f /tmp/.redeploy_done ]; do
  curl -s -o /dev/null -w '%{http_code}\n' http://localhost:8000/api/customers/types; sleep 0.1; done; }
rm -f /tmp/.redeploy_done
probe > /tmp/.probe_codes & P=$!
start=$(date +%s.%N)
docker compose up -d --build --force-recreate ms-vets-app ms-vets >/dev/null 2>&1
end=$(date +%s.%N); touch /tmp/.redeploy_done; wait $P; rm -f /tmp/.redeploy_done
total=$(wc -l < /tmp/.probe_codes); bad=$(grep -vc '^200$' /tmp/.probe_codes)
printf "Redespliegue de ms-vets: %.1f s | sondeos a ms-customers: %s | no-200: %s\n" "$(awk "BEGIN{print $end - $start}")" "$total" "$bad"
rm -f /tmp/.probe_codes
[ "$bad" -eq 0 ] && echo "✔ 0 caídas en ms-customers durante el redespliegue de ms-vets"
