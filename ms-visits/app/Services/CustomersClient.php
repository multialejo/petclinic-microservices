<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Cliente HTTP síncrono hacia ms-customers.
 *
 * | Resultado de la llamada                    | Respuesta de ms-visits |
 * |--------------------------------------------|------------------------|
 * | error de conexión / timeout (tras reintento)| 503                   |
 * | 5xx                                        | 503                    |
 * | 404                                        | 404 Pet not found      |
 * | otro 4xx                                   | 503 (se registra)      |
 */
class CustomersClient
{
    /**
     * @return array{id:int, name:string, owner_id:int, owner:array{id:int, first_name:string, last_name:string}}
     *
     * @throws HttpException 404 o 503
     */
    public function findPet(int $petId): array
    {
        try {
            $res = Http::withHeaders(['X-Internal-Token' => (string) config('services.customers.token')])
                ->acceptJson()
                ->connectTimeout(2)
                ->timeout(3)
                // 2 intentos en total; solo se reintenta por error de conexión, nunca por 404.
                // throw: false -> los 4xx/5xx NO lanzan excepción y se tratan abajo.
                ->retry(2, 200, fn ($e) => $e instanceof ConnectionException, throw: false)
                ->get(rtrim((string) config('services.customers.url'), '/')."/api/pets/{$petId}");
        } catch (ConnectionException $e) {
            Log::warning('ms-customers no responde', ['pet_id' => $petId, 'error' => $e->getMessage()]);
            abort(503, 'Customers service unavailable');
        }

        if ($res->serverError()) {
            abort(503, 'Customers service unavailable');
        }

        if ($res->notFound()) {
            abort(404, 'Pet not found');
        }

        if ($res->failed() || ! is_array($res->json('data'))) {
            Log::error('Respuesta inesperada de ms-customers', ['pet_id' => $petId, 'status' => $res->status()]);
            abort(503, 'Unexpected response from customers service');
        }

        return $res->json('data');
    }
}
