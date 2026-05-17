<?php

namespace Modules\CycleClosure\Exceptions;

use RuntimeException;

class NoActiveSettingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No existe configuración de ciclo activa.');
    }
}
