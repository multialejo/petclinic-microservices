# PetClinic — Reingeniería a microservicios Laravel

Repositorio base (monorepo) para la reingeniería de Spring PetClinic hacia microservicios en Laravel.
Corresponde a la **Fase 3 (infraestructura base con Docker)** del planteamiento. Todavía no contiene
lógica de dominio: eso se agrega en la Fase 4, a partir del diseño de las Fases 1 y 2.

## Estructura

```
.
├── docker-compose.yml   # Orquesta los 3 microservicios (PHP-FPM + Nginx) + 3 bases MySQL aisladas
├── .env.example         # Variables de la raíz (HOST_UID/HOST_GID, puertos)
├── gateway/             # API Gateway Nginx (nginx.conf): punto único de entrada en :8000
├── frontend/            # Frontend desacoplado (tecnología pendiente, Fase 4)
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

# Generar APP_KEY (solo si el .env es nuevo) y migrar cada base
for s in ms-vets ms-customers ms-visits; do
  docker compose exec $s-app php artisan key:generate
  docker compose exec $s-app php artisan migrate
done
```

Comprobación rápida: `curl -I http://localhost:8001/up` (y 8002, 8003) debe devolver `200`, y
`curl http://localhost:8000/health` (gateway) `{"status":"ok"}`. El cliente (frontend, Postman) debe usar
siempre el gateway (`http://localhost:8000/api/...`); los puertos 8001–8003 quedan para depuración. Ver `gateway/README.md`.

## Comandos útiles

```bash
docker compose exec ms-vets-app php artisan <comando>  # artisan en un servicio
docker compose exec ms-vets-app composer <comando>     # composer en un servicio
docker compose exec ms-vets-app php artisan test       # tests de un servicio
docker compose logs -f ms-visits ms-visits-app         # logs (Nginx + PHP-FPM)
docker compose logs -f ms-visits-worker                # jobs procesados por el worker
docker compose restart ms-visits-worker                # tras cambiar jobs/listeners (queue:work cachea el código)
docker compose exec ms-visits-app php artisan queue:failed   # jobs fallidos
docker compose stop ms-vets ms-vets-app                # apagar un servicio (prueba de independencia)
docker compose down                                   # detener todo (añadir -v para borrar las bases)
```

## Pendiente (decisiones de Fases 2 y 4)

> Las decisiones de la Fase 2 ya están tomadas en `../docs/fase2/README.md`:
> - gateway **Nginx** en el puerto 8000;
> - token interno `X-Internal-Token` (Sanctum sin uso);
> - Redis + worker en ms-visits;
> - contrato REST y seeders.
>
> La lista de abajo queda como checklist de implementación.

- [x] **API Gateway**: Nginx en `gateway/nginx.conf`, servicio `gateway` en `docker-compose.yml` (puerto 8000).
- **Frontend**: Blade/Livewire, Vue/React o Next.js → `frontend/`.
- [x] **Autenticación entre servicios**: token interno `INTERNAL_API_TOKEN` + middleware `internal.token` en los 3 servicios.
- [x] **Redis** + `ms-visits-worker` para colas/eventos asíncronos (el job `VisitCreated` se implementa en la Fase 4).
- Modelos, migraciones, controladores y seeders de cada dominio (Fase 4).
