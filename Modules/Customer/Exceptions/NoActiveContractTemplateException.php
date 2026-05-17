<?php

namespace Modules\Customer\Exceptions;

class NoActiveContractTemplateException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No hay plantillas de contrato activas.');
    }
}
