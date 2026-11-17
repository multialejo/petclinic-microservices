<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // El gateway publica /api/customers/{resto} y lo reenvía como /api/{resto}, indicando el prefijo
        // quitado en X-Forwarded-Prefix. Con esto los enlaces de paginación (`links`, `meta.path`)
        // apuntan a la URL pública. Sin la cabecera (acceso directo o desde otro servicio) no cambia nada.
        Paginator::currentPathResolver(function () {
            /** @var Request $request */
            $request = $this->app['request'];
            $prefix = (string) $request->header('X-Forwarded-Prefix', '');

            if ($prefix === '' || ! preg_match('#^/[A-Za-z0-9/_-]+$#', $prefix) || ! str_starts_with($request->path(), 'api/')) {
                return $request->url();
            }

            return $request->getSchemeAndHttpHost().rtrim($prefix, '/').'/'.substr($request->path(), 4);
        });
    }
}
