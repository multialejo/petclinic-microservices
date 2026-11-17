#!/usr/bin/env bash
# Tiempos de respuesta monolito (:8080) vs microservicios (gateway :8000, frontend :3000) con metrics/loadtest.py.
# Uso: ./metrics/latency.sh [peticiones=1000] [concurrencia=10]   -> escribe latency.csv en OUT
cd "$(dirname "$0")/.."
N=${1:-1000}; C=${2:-10}; OUT=${OUT:-../docs/fase5/latency.csv}
# El gateway limita a 20 req/s por IP (429): para medir latencia se relaja temporalmente y se restaura al salir.
CONF=gateway/nginx.conf; cp "$CONF" /tmp/nginx.conf.bak
restore() { cp /tmp/nginx.conf.bak "$CONF"; docker compose exec -T gateway nginx -s reload >/dev/null 2>&1; }
trap restore EXIT
# se escribe sobre el mismo archivo (no sed -i) para conservar el inodo del bind mount
sed -E 's/rate=[0-9]+r\/s/rate=100000r\/s/; s/burst=[0-9]+/burst=100000/' /tmp/nginx.conf.bak > "$CONF"
docker compose exec -T gateway nginx -s reload >/dev/null 2>&1 || { echo "No se pudo recargar el gateway"; exit 1; }
sleep 1
echo "escenario,sistema,url,peticiones,concurrencia,rps,p50_ms,p95_ms,p99_ms,errores" > "$OUT"

run() { # escenario sistema url [método] [cuerpo]
  local esc=$1 sis=$2 url=$3; shift 3
  python3 metrics/loadtest.py "$url" 200 "$C" "$@" >/dev/null   # warm-up (JVM / opcache)
  local r; r=$(python3 metrics/loadtest.py "$url" "$N" "$C" "$@")
  IFS=, read -r rps p50 p95 p99 err <<<"$r"
  printf "%-18s %-10s rps=%-8s p50=%-6s p95=%-6s p99=%-6s err=%s\n" "$esc" "$sis" "$rps" "$p50" "$p95" "$p99" "$err"
  echo "$esc,$sis,$url,$N,$C,$rps,$p50,$p95,$p99,$err" >> "$OUT"
}

run vets-listado        monolito  http://localhost:8080/vets.html
run vets-listado        gateway   http://localhost:8000/api/vets
run vets-listado        frontend  http://localhost:3000/vets.html
run owners-busqueda     monolito  "http://localhost:8080/owners?lastName=Davis"
run owners-busqueda     gateway   "http://localhost:8000/api/customers/owners?last_name=Davis"
run owners-busqueda     frontend  "http://localhost:3000/owners?lastName=Davis"
run owner-ficha         monolito  http://localhost:8080/owners/1
run owner-ficha         frontend  http://localhost:3000/owners/1
run visita-alta         gateway   http://localhost:8000/api/visits POST \
  '{"owner_id":1,"pet_id":1,"date":"2099-01-01","description":"carga"}'
echo "→ $OUT"
