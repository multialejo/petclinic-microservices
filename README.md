# PetClinic — Reingeniería a microservicios Laravel

Repositorio (monorepo) de la reingeniería de Spring PetClinic hacia microservicios en Laravel.
Contiene la infraestructura de la **Fase 3** y la migración de dominio de la **Fase 4** (Strangler Fig):
ms-vets, ms-customers, ms-visits y un frontend desacoplado. Informe: `../docs/fase4/README.md`.
La **Fase 5** (OpenAPI con Scramble, métricas y tabla comparativa) está en `../docs/fase5/README.md`;
los scripts viven en `metrics/` y `hurl-tests/09-*`. UI de cada API en `http://localhost:800{1,2,3}/docs/api`.

## Estructura

```
.
├── docker-compose.yml   # Orquesta los 3 microservicios (PHP-FPM + Nginx) + 3 bases MySQL aisladas
├── .env.example         # Variables de la raíz (HOST_UID/HOST_GID, puertos)
├── gateway/             # API Gateway Nginx (nginx.conf): punto único de entrada en :8000
├── frontend/            # Laravel + Blade SIN base de datos: solo consume el gateway (:3000)
├── ms-vets/             # Laravel 13 (API): vets, specialties, vet_specialties
├── ms-customers/        # Laravel 13 (API): owners, pets, types
└── ms-visits/           # Laravel 13 (API): visits
```

Cada `ms-*` es un proyecto Laravel estándar con:

- `php artisan install:api` ejecutado (`routes/api.php`; Sanctum instalado pero sin uso, ADR-05).
- Middleware `internal.token` (`app/Http/Middleware/VerifyInternalToken.php`) para rutas solo internas: exige la
  cabecera `X-Internal-Token` igual a `INTERNAL_API_TOKEN` (falla cerrado si no está configurado).
- Infraestructura Docker propia:
  - `Dockerfile`: PHP 8.4-FPM (alpine) solo con las extensiones necesarias (`pdo_mysql`, `bcmath`, `intl`,
    `zip`, `opcache`, `pcntl`; ms-visits además `redis`) + Composer, ejecutándose con el UID/GID del host.
  - `nginx.conf`: configuración del contenedor Nginx *sidecar* que atiende HTTP y pasa PHP por FastCGI a `ms-*-app:9000`.
  - `php.ini`: ajustes de PHP para desarrollo.
- `.env` apuntando a **su propia** base MySQL.

## Servicios y puertos

| Servicio       | URL en el host          | Hostname interno (red `petclinic`) | Contenedor PHP-FPM  | Base de datos  | Usuario BD  | Puerto BD (host) |
|----------------|-------------------------|------------------------------------|---------------------|----------------|-------------|------------------|
| **gateway**    | http://localhost:8000   | `http://gateway`                   | —                   | —              | —           | —                |
| **frontend**   | http://localhost:3000   | `http://frontend`                  | `frontend-app`      | — (sin BD)     | —           | —                |
| ms-vets        | http://localhost:8001   | `http://ms-vets`                   | `ms-vets-app`       | `db_vets`      | `vets`      | 33061            |
| ms-customers   | http://localhost:8002   | `http://ms-customers`              | `ms-customers-app`  | `db_customers` | `customers` | 33062            |
| ms-visits      | http://localhost:8003   | `http://ms-visits`                 | `ms-visits-app`     | `db_visits`    | `visits`    | 33063            |

Cada servicio son dos contenedores: `ms-*` (Nginx, expone el puerto y es el hostname al que llaman los demás
servicios) y `ms-*-app` (PHP-FPM, sin puerto publicado; ahí se ejecutan `artisan` y `composer`).

Las bases corren en contenedores MySQL 8.4 **separados** (`db-vets`, `db-customers`, `db-visits`), cada uno con
su propio volumen y usuario: un servicio no tiene credenciales ni acceso a la base de otro.
Contraseña de los usuarios de aplicación en desarrollo: `secret`.

Mensajería asíncrona (solo ms-visits): `redis` (Redis 7, sin puerto publicado ni persistencia) y
`ms-visits-worker`, que reutiliza la imagen de ms-visits ejecutando `php artisan queue:work redis`
(`QUEUE_CONNECTION=redis`, 3 intentos; los fallidos quedan en `failed_jobs`).

Entre servicios se usa el hostname interno con el token compartido, p. ej. desde ms-visits:
`Http::withHeaders(['X-Internal-Token' => config('services.customers.token')])->get(config('services.customers.url').'/api/pets/5')`.

`INTERNAL_API_TOKEN` se define **una sola vez** en el `.env` de la raíz y docker-compose lo inyecta como variable
de entorno en todos los contenedores PHP (tiene prioridad sobre `ms-*/.env`). El gateway vacía esa cabecera en
las peticiones externas.

## Requisitos

Solo Docker (con Docker Compose v2). No hace falta PHP ni Composer en el host.

## Puesta en marcha

```bash
cp .env.example .env            # ajustar HOST_UID/HOST_GID con `id -u` / `id -g`
sed -i "s/^INTERNAL_API_TOKEN=.*/INTERNAL_API_TOKEN=$(openssl rand -hex 32)/" .env   # secreto interno
docker compose build            # imágenes PHP-FPM propias (~160 MB c/u)

# Solo la primera vez después de clonar: instalar dependencias y preparar cada servicio
for s in ms-vets ms-customers ms-visits; do
  docker compose run --rm --no-deps $s-app composer install
  [ -f $s/.env ] || cp $s/.env.example $s/.env
done

docker compose up -d

# Generar APP_KEY (solo si el .env es nuevo), migrar cada base y cargar los datos de PetClinic
docker compose run --rm --no-deps frontend-app composer install
[ -f frontend/.env ] || cp frontend/.env.example frontend/.env
docker compose exec frontend-app php artisan key:generate
for s in ms-vets ms-customers ms-visits; do
  docker compose exec $s-app php artisan key:generate
  docker compose exec $s-app php artisan migrate:fresh --seed   # ids originales de data.sql
done
docker compose restart ms-visits-worker
```

Comprobación rápida: `curl -I http://localhost:8001/up` (y 8002, 8003) debe devolver `200`, y
`curl http://localhost:8000/health` (gateway) `{"status":"ok"}`, `curl http://localhost:8000/api/vets` devuelve
los 6 veterinarios y http://localhost:3000 abre la interfaz. El cliente (frontend, Postman) debe usar
siempre el gateway (`http://localhost:8000/api/...`); los puertos 8001–8003 quedan para depuración. Ver `gateway/README.md`.
Colección Postman de CU-01…09: `../docs/fase4/petclinic.postman_collection.json`.

## API (vía gateway)

| CU | Método y ruta |
|---|---|
| CU-01 / 02 | `GET` / `POST /api/customers/owners` (`?last_name=&page=`) |
| CU-03 / 04 | `GET` / `PUT /api/customers/owners/{id}` + `GET /api/visits?pet_ids=1,2` |
| CU-05 / 06 | `POST /api/customers/owners/{id}/pets`, `GET /api/customers/pets/{id}`, `PUT /api/customers/owners/{o}/pets/{p}` |
| CU-07 | `GET /api/customers/types` |
| CU-08 | `POST /api/visits` (`owner_id, pet_id, date, description`): verifica el pet en ms-customers y encola `VisitCreated` |
| CU-09 | `GET /api/vets?page=` |

## Comandos útiles

```bash
docker compose exec ms-vets-app php artisan <comando>  # artisan en un servicio
docker compose exec ms-vets-app composer <comando>     # composer en un servicio
docker compose exec ms-vets-app php artisan test       # tests de un servicio (también frontend-app)
docker compose logs -f ms-visits ms-visits-app         # logs (Nginx + PHP-FPM)
docker compose logs -f ms-visits-worker                # jobs procesados por el worker
docker compose restart ms-visits-worker                # tras cambiar jobs/listeners (queue:work cachea el código)
docker compose exec ms-visits-app php artisan queue:failed   # jobs fallidos
docker compose stop ms-vets ms-vets-app                # apagar un servicio (prueba de independencia)
docker compose down                                   # detener todo (añadir -v para borrar las bases)
```

## Estado (checklist Fases 3 y 4)

- [x] **API Gateway**: Nginx en `gateway/nginx.conf` (puerto 8000).
- [x] **Autenticación entre servicios**: token interno `INTERNAL_API_TOKEN` + middleware `internal.token`.
- [x] **Redis** + `ms-visits-worker`: evento `VisitCreated` → auditoría (`visit_audits`) + notificación simulada.
- [x] **ms-vets** (Iteración 1), **ms-customers** (Iteración 2), **ms-visits** (Iteración 3): migraciones, modelos,
  API Resources, FormRequests, seeders y tests.
- [x] **Frontend** (Iteración 4): Laravel + Blade sin BD en `frontend/` (puerto 3000).
- [ ] Fase 5: OpenAPI con `dedoc/scramble`, tabla comparativa de métricas.
