# Frontend — PetClinic (Laravel + Blade)

Interfaz ligera de la Fase 4, Iteración 4. Reemplaza las vistas Thymeleaf del monolito.

- **No tiene base de datos.** `DB_CONNECTION=null`, las sesiones viven en una cookie cifrada y la caché en archivo.
- **Consume exclusivamente el API Gateway** (`GATEWAY_URL=http://gateway`). La llamada sale del servidor, así que no depende de CORS.
- Mantiene las mismas URLs que el monolito: `/owners/find`, `/owners/{id}`, `/owners/{id}/pets/new`, `/vets.html`, etc.

Servicio publicado en http://localhost:3000 (`frontend` = Nginx sidecar; `frontend-app` = PHP-FPM).

## Piezas

| Archivo | Responsabilidad |
|---|---|
| `app/Services/GatewayClient.php` | Único punto de salida HTTP. 404 → `abort(404)`; 422 → `ValidationException` (errores junto al campo); 5xx o caída → `GatewayUnavailableException` |
| `app/Exceptions/GatewayUnavailableException.php` | Página 503 controlada si nadie la captura |
| `app/Http/Controllers/*` | CU-01…09 (owners, pets, visits, vets) |
| `resources/views/*` | Blade + Bootstrap 5 (CDN) |

## Comportamientos de UI del As-Is (docs/fase2 §4)

| Comportamiento | Dónde |
|---|---|
| Búsqueda con 0 resultados → formulario con aviso; con 1 → ficha; página fuera de rango → página 1 | `OwnerController@index` |
| Ficha = `GET /api/customers/owners/{id}` + `GET /api/visits?pet_ids=…`; la segunda llamada se omite si el owner no tiene pets | `OwnerController@show` |
| ms-visits caído → la ficha se muestra igual, con un aviso en lugar de las visitas | `OwnerController@show` |
| Fecha de visita por defecto = mañana | `VisitController@create` |
| Éxito al crear o editar → redirige a la ficha del owner con un mensaje | controladores |

## Comandos

```bash
docker compose exec frontend-app php artisan test   # tests con Http::fake() (no hace falta el backend)
docker compose logs -f frontend frontend-app
```
