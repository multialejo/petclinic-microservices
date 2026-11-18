# Frontend — PetClinic (Laravel + Blade)

Interfaz ligera de la Fase 4, Iteración 4. Reemplaza las vistas Thymeleaf del monolito.

- **No tiene base de datos.** `DB_CONNECTION=null`, las sesiones viven en una cookie cifrada y la caché en archivo.
- **Consume exclusivamente el API Gateway** (`GATEWAY_URL=http://gateway`). La llamada sale del servidor, así que no depende de CORS.
- **Se ve igual que el monolito.** Las vistas Blade son un port 1:1 de las plantillas Thymeleaf
  (`spring-petclinic/src/main/resources/templates`), con el mismo HTML, clases, textos en inglés y mensajes flash.
  Usan los mismos recursos estáticos, copiados tal cual en `public/`:
  - `public/resources/` (petclinic.css con Bootstrap 5.3.8, fuentes, imágenes);
  - `public/webjars/` (Font Awesome 4.7.0 y `bootstrap.bundle.min.js` 5.3.8, en las mismas rutas que el monolito).
- Mismas URLs y nombres de campo que el monolito: `/owners?lastName=`, `firstName`, `birthDate`, `type` (nombre del tipo), etc.
  Los controladores los traducen al contrato snake_case de la API, y `GatewayClient` devuelve los errores 422
  con los nombres del formulario.
- Página de error de `error.html` ("Something happened...") para 404, 500 (`/oups`, como `CrashController`) y 503.

Servicio publicado en http://localhost:3000 (`frontend` = Nginx sidecar; `frontend-app` = PHP-FPM).

## Piezas

| Archivo | Responsabilidad |
|---|---|
| `app/Services/GatewayClient.php` | Único punto de salida HTTP. 404 → `abort(404)`; 422 → `ValidationException` (errores junto al campo); 5xx o caída → `GatewayUnavailableException` |
| `app/Exceptions/GatewayUnavailableException.php` | Gateway caído; `bootstrap/app.php` la muestra con `error.blade.php` (503) |
| `app/Http/Controllers/*` | CU-01…09 (owners, pets, visits, vets) |
| `resources/views/*` | Blade + Bootstrap 5 (CDN) |

## Comportamientos de UI del As-Is (docs/fase2 §4)

| Comportamiento | Dónde |
|---|---|
| Búsqueda con 0 resultados → formulario con aviso; con 1 → ficha; página fuera de rango → página 1 | `OwnerController@index` |
| Ficha = `GET /api/customers/owners/{id}` + `GET /api/visits?pet_ids=…`; la segunda llamada se omite si el owner no tiene pets | `OwnerController@show` |
| ms-visits caído → la ficha se muestra igual, con el aviso en el `#error-message` de la plantilla original | `OwnerController@show` |
| Fecha de visita por defecto y mínima = mañana | `VisitController@create` |
| "Previous Visits" muestra las visitas reales (en el monolito sale siempre vacía por un bug de la plantilla) | `visits/form.blade.php` |
| Éxito al crear o editar → redirige a la ficha del owner con un mensaje | controladores |

## Comandos

```bash
docker compose exec frontend-app php artisan test   # tests con Http::fake() (no hace falta el backend)
docker compose logs -f frontend frontend-app
```
