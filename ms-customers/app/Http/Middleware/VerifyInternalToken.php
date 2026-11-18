<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege rutas solo internas (servicio -> servicio) con el secreto compartido
 * INTERNAL_API_TOKEN, enviado en la cabecera X-Internal-Token.
 *
 * Falla cerrado: si el token no está configurado, ninguna petición pasa.
 * El gateway vacía esta cabecera en las peticiones externas.
 */
class VerifyInternalToken
{
    public const HEADER = 'X-Internal-Token';

    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.internal.token');
        $given = (string) $request->header(self::HEADER, '');

        if ($expected === '' || $given === '' || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
