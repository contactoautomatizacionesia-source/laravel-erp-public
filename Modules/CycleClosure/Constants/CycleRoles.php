<?php

namespace Modules\CycleClosure\Constants;

/**
 * Role IDs relevant to the CycleClosure module.
 * These match the roles.id values in the database.
 */
class CycleRoles
{
    /** Can configure and execute cycle closures */
    const SUPER_ADMIN   = 1;
    const ADMIN         = 2;
    const ADMINISTRATOR = 7;

    /** Can act as co-approver (Contador) */
    const CONTADOR = 27;

    /** Roles allowed as executor (needs_review approval) */
    const EXECUTOR_ROLES = [self::SUPER_ADMIN, self::ADMIN, self::ADMINISTRATOR];
}
