#!/usr/bin/env bash
# Demuestra el rate limit del gateway (20 req/s, ráfaga 40; no aplica a /health): una ráfaga debe producir 429.
codes=$(for i in $(seq 1 200); do curl -s -o /dev/null -w '%{http_code}\n' localhost:8000/api/vets & done; wait)
echo "$codes" | sort | uniq -c
echo "$codes" | grep -q 429 && echo "✔ 429 Too Many Requests observado" || { echo "✘ no se observó 429"; exit 1; }
