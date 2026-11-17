<?php

namespace App\Services;

use App\Exceptions\GatewayUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Cliente del API Gateway. El frontend no tiene base de datos: todo dato sale de aquí.
 *
 * Traducción de respuestas:
 *  - 2xx          -> array JSON
 *  - 404          -> abort(404)
 *  - 422          -> ValidationException con los `errors` de la API (se muestran junto a cada campo)
 *  - 5xx / caída  -> GatewayUnavailableException (página 503, o aviso si el llamador la captura)
 */
class GatewayClient
{
    public function get(string $path, array $query = []): array
    {
        return $this->send('get', $path, $query);
    }

    public function post(string $path, array $data): array
    {
        return $this->send('post', $path, $data);
    }

    public function put(string $path, array $data): array
    {
        return $this->send('put', $path, $data);
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.gateway.url'), '/'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(2)
            ->timeout((int) config('services.gateway.timeout', 8));
    }

    private function send(string $method, string $path, array $payload): array
    {
        try {
            /** @var Response $res */
            $res = $this->http()->{$method}('/'.ltrim($path, '/'), $payload);
        } catch (ConnectionException $e) {
            throw new GatewayUnavailableException('No se pudo conectar con el API Gateway.', previous: $e);
        }

        if ($res->successful()) {
            return $res->json() ?? [];
        }

        if ($res->status() === 404) {
            abort(404, (string) $res->json('message', 'No encontrado'));
        }

        if ($res->status() === 422) {
            throw ValidationException::withMessages((array) $res->json('errors', []));
        }

        throw new GatewayUnavailableException(
            (string) $res->json('message', 'Servicio no disponible')." (HTTP {$res->status()})"
        );
    }
}
