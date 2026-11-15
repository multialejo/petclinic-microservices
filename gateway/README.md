# API Gateway

Punto único de entrada. Enrutará `/api/vets/*`, `/api/customers/*` y `/api/visits/*`
al microservicio correspondiente y gestionará CORS / rate limiting.

**Pendiente (Fase 2):** elegir la tecnología (Nginx como reverse proxy, KrakenD o Kong)
y agregar el servicio `gateway` al `docker-compose.yml` de la raíz.
