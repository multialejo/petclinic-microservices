<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use RuntimeException;

/**
 * El gateway (o el microservicio detrás) no está disponible: error de conexión, timeout o 5xx/503.
 * Si nadie la captura (p. ej. la ficha del owner sí la captura para las visitas), se muestra una página 503.
 */
class GatewayUnavailableException extends RuntimeException
{
    public function render(): Response
    {
        return response()->view('errors.unavailable', ['detail' => $this->getMessage()], 503);
    }
}
