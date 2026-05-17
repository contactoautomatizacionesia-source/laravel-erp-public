<?php

namespace App\Overrides;

use Closure;
use SpondonIt\Service\Middleware\ServiceMiddleware;

/**
 * Permite omitir la verificación de licencia UXSeven cuando
 * LICENSE_CHECK_ENABLED está en false (entornos de pruebas / staging local).
 *
 * Sigue el mismo patrón que LicenseCheckRepository:
 *   .env → LICENSE_CHECK_ENABLED=true   → validación normal (comportamiento real)
 *   .env → LICENSE_CHECK_ENABLED=false  → middleware transparente (pasa la request sin tocarla)
 *
 * El binding se registra en AppServiceProvider::register():
 *   $this->app->bind(ServiceMiddleware::class, LicenseServiceMiddleware::class);
 */
class LicenseServiceMiddleware extends ServiceMiddleware
{
    public function handle($request, Closure $next): mixed
    {
        if (!env('LICENSE_CHECK_ENABLED', false)) {
            return $next($request);
        }
        return parent::handle($request, $next);
    }
}
