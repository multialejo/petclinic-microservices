# Pruebas manuales con Hurl (demo de la exposición)

Peticiones contra el **gateway** (`http://localhost:8000`) que demuestran el comportamiento del sistema de microservicios.

## Requisitos
- [Hurl](https://hurl.dev) instalado (`hurl --version`).
- Stack arriba y con datos seed (ver README raíz, "Puesta en marcha"):
  `docker compose up -d --build` y `docker compose exec <ms>-app php artisan migrate:fresh --seed` en los tres servicios.

## Ejecución
```bash
./run-demo.sh                       # 01..06 en orden
HURL_VERBOSE=1 ./run-demo.sh        # mostrando cada petición/respuesta (ideal para exponer)
hurl --test --variables-file vars.env 05-visits-cu08.hurl   # un solo archivo
./07-resilience.sh                  # detiene ms-customers y lo vuelve a levantar
./08-rate-limit.sh                  # ráfaga de 200 peticiones -> 429
```

## Guion sugerido
| Archivo | Demuestra |
|---|---|
| `01-gateway.hurl` | Punto único de entrada, health, 404 JSON, reescritura de rutas, CORS |
| `02-vets.hurl` | ms-vets: veterinarios, especialidades, paginación |
| `03-customers.hurl` | ms-customers: dueños, mascotas, tipos, búsqueda, 404 |
| `04-owner-pet-crud.hurl` | Alta/edición con validaciones (422), duplicados, scoping por dueño |
| `05-visits-cu08.hurl` | **Flujo principal**: agendar visita (ms-visits → ms-customers síncrono + evento asíncrono) |
| `06-visits-errors.hurl` | Mascota inexistente (404), mascota de otro dueño / fecha pasada (422) |
| `07-resilience.sh` | Aislamiento de fallos: con ms-customers caído, vets sigue 200 y el resto responde 503 controlado |
| `08-rate-limit.sh` | Rate limiting del gateway (429) |

Las pruebas son repetibles: crean sus propios datos y usan fechas fijas (`vars.env`).
`04`/`05` dejan dueños de demo en la BD; `migrate:fresh --seed` los limpia.
