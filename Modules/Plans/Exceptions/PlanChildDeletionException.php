<?php

namespace Modules\Plans\Exceptions;

use DomainException;

class PlanChildDeletionException extends DomainException
{
    public static function assignedToEntrepreneurs(int $count): self
    {
        return new self('No se puede eliminar: el nivel está asignado actualmente a ' . $count . ' empresario(s).');
    }

    public static function hasHistory(int $count): self
    {
        return new self('No se puede eliminar: el nivel tiene ' . $count . ' registro(s) en el historial de planes.');
    }
}
