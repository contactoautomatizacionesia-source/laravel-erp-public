<?php

namespace Modules\Customer\Exceptions;

class ProtecdataCallbackException extends \RuntimeException
{
    public static function invalidPayload(): self
    {
        return new self('payload inválido', 400);
    }

    public static function processNotFound(): self
    {
        return new self('proceso no encontrado', 404);
    }

    public static function domainNotAllowed(string $parsedHost): self
    {
        return new self("URL del PDF no permitida: {$parsedHost}");
    }

    public static function downloadFailed(int $httpStatus): self
    {
        return new self("Fallo al descargar PDF desde Azure (HTTP {$httpStatus})");
    }

    public static function storageFailed(string $path): self
    {
        return new self("Fallo al guardar PDF en disco: {$path}");
    }
}
