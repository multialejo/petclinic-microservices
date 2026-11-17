#!/usr/bin/env python3
"""Generador de carga mínimo (stdlib): N peticiones con C hilos. Imprime CSV: rps,p50_ms,p95_ms,p99_ms,errores."""
import sys, time, json, urllib.request, urllib.error
from concurrent.futures import ThreadPoolExecutor

def main():
    url, n, c = sys.argv[1], int(sys.argv[2]), int(sys.argv[3])
    method = sys.argv[4] if len(sys.argv) > 4 else "GET"
    body = sys.argv[5].encode() if len(sys.argv) > 5 else None

    class NoRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, *a, **k): return None
    opener = urllib.request.build_opener(NoRedirect)

    def one(_):
        req = urllib.request.Request(url, data=body, method=method,
                                     headers={"Content-Type": "application/json", "Accept": "application/json"} if body else {})
        t = time.perf_counter()
        try:
            with opener.open(req, timeout=30) as r: code = r.status; r.read()
        except urllib.error.HTTPError as e: code = e.code
        except Exception: code = 0
        return (time.perf_counter() - t) * 1000, code

    start = time.perf_counter()
    with ThreadPoolExecutor(max_workers=c) as ex: res = list(ex.map(one, range(n)))
    wall = time.perf_counter() - start
    lat = sorted(r[0] for r in res)
    pct = lambda p: lat[min(len(lat) - 1, int(len(lat) * p / 100))]
    err = sum(1 for r in res if r[1] not in (200, 201))
    print(f"{n / wall:.1f},{pct(50):.1f},{pct(95):.1f},{pct(99):.1f},{err}")

main()
