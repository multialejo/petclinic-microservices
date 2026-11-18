# API Gateway (Nginx)

Punto único de entrada de la API en **http://localhost:8000** (`GATEWAY_PORT`). Es un proxy inverso
Nginx 1.27 sin lógica de negocio: enruta, aplica CORS y rate limiting, y normaliza los errores de
infraestructura a JSON. No compone respuestas (la composición la hace el frontend).

Configuración: [`nginx.conf`](nginx.conf), montado como `/etc/nginx/conf.d/default.conf` en el servicio
`gateway` de `docker-compose.yml`.

## Rutas

| Pública | Upstream | Reescritura |
|---|---|---|
| `/api/customers/{resto}` | `http://ms-customers/api/{resto}` | se quita `customers/` |
| `/api/vets[/…]` | `http://ms-vets/api/vets[/…]` | tal cual |
| `/api/visits[/…]` | `http://ms-visits/api/visits[/…]` | tal cual |
| `/health` | (el propio gateway) | `200 {"status":"ok"}`, usado por el healthcheck |
| cualquier otra | — | `404 {"message":"Not Found"}` |

El query string se reenvía sin cambios: `GET /api/customers/owners?last_name=Davis` →
`http://ms-customers/api/owners?last_name=Davis`.

## Comportamiento

| Aspecto | Regla |
|---|---|
| Servicio caído / timeout (502, 503, 504) | `503 {"message":"Service unavailable"}`. Las demás rutas siguen funcionando. |
| Errores de Laravel (404, 422, …) | pasan sin cambios (ya son JSON: el gateway fuerza `Accept: application/json`) |
| Rate limit | 20 req/s por IP, ráfaga 40 → `429 {"message":"Too Many Requests"}` |
| CORS | solo orígenes `http(s)://localhost:*` y `127.0.0.1:*`; métodos `GET, POST, PUT, OPTIONS`; preflight `OPTIONS` → 204 en el gateway |
| CORS de Laravel | se ocultan los `Access-Control-*` de los servicios (`proxy_hide_header`) para no duplicar headers |
| `X-Internal-Token` | se vacía en toda petición externa (solo lo usan los servicios entre sí) |
| Trazabilidad | `X-Request-Id` generado por el gateway, enviado al upstream y devuelto al cliente; `X-Forwarded-For/Proto` |
| Host y prefijo | reenvía `Host` con puerto (`$http_host`) y, solo en `/api/customers/*`, `X-Forwarded-Prefix: /api/customers` para que los enlaces de paginación de ms-customers apunten a la URL pública |
| Timeouts | conexión 2 s, lectura 10 s |

Los upstreams se resuelven en cada petición (`resolver 127.0.0.11` + variables en `proxy_pass`), por eso el
servicio `gateway` **no tiene `depends_on`**: arranca y responde aunque algún `ms-*` esté apagado.

Para cambiar los orígenes permitidos (p. ej. el frontend en otro host), editar el `map $http_origin` al
inicio de `nginx.conf`. Todos los `add_header` están a nivel `server` a propósito: una `location` con su
propio `add_header` dejaría de heredar los de CORS.

## Pruebas manuales

```bash
docker compose exec gateway nginx -t && docker compose exec gateway nginx -s reload   # tras editar nginx.conf

curl -i localhost:8000/health
curl -i localhost:8000/api/customers/owners?last_name=Davis
curl -i -X OPTIONS -H 'Origin: http://localhost:3000' localhost:8000/api/vets       # 204 + CORS

# Independencia: con ms-vets apagado, /api/vets → 503 JSON y el resto sigue respondiendo
docker compose stop ms-vets ms-vets-app
curl -i localhost:8000/api/vets
docker compose start ms-vets-app ms-vets

# Rate limit
seq 300 | xargs -P 100 -I{} curl -s -o /dev/null -w '%{http_code}\n' localhost:8000/api/vets | sort | uniq -c
```
