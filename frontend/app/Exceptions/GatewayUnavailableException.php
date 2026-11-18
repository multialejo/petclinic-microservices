<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * El gateway (o el microservicio detrás) no está disponible: error de conexión, timeout o 5xx/503.
 * Si nadie la captura (la ficha del owner sí la captura para las visitas), bootstrap/app.php la
 * muestra con la página de error del monolito (error.blade.php) y estado 503.
 */
class GatewayUnavailableException extends RuntimeException {}
