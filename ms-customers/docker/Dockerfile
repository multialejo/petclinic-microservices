# ms runtime: PHP 8.4-FPM (alpine) con solo las extensiones que usa el servicio.
# Nginx corre como contenedor sidecar aparte (ver nginx.conf y docker-compose.yml).
FROM php:8.4-fpm-alpine

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN apk add --no-cache icu-libs libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev linux-headers \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip opcache pcntl \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini $PHP_INI_DIR/conf.d/99-app.ini

# Usuario con el UID/GID del host: los archivos creados en el bind mount
# (artisan make:*, composer, logs) quedan con el dueño correcto.
RUN addgroup -g "$HOST_GID" app && adduser -D -u "$HOST_UID" -G app app

WORKDIR /var/www/html
USER app

EXPOSE 9000
CMD ["php-fpm"]
