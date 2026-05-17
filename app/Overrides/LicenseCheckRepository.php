<?php

namespace App\Overrides;

use SpondonIt\Service\Repositories\InitRepository;

/**
 * Permite controlar la validación de licencia mediante la variable de entorno
 * LICENSE_CHECK_ENABLED. Si no está definida o es false, la verificación se omite
 * completamente, manteniendo el sistema 100% funcional.
 *
 * .env → LICENSE_CHECK_ENABLED=true   → activa la validación (comportamiento normal)
 * .env → LICENSE_CHECK_ENABLED=false  → desactiva la validación (staging / local)
 */
class LicenseCheckRepository extends InitRepository
{
    public function check(): void
    {
        if (!env('LICENSE_CHECK_ENABLED', false)) {
            return;
        }

        parent::check();
    }

    public function apiCheck(): bool
    {
        if (!env('LICENSE_CHECK_ENABLED', false)) {
            return true;
        }

        return parent::apiCheck();
    }
}
