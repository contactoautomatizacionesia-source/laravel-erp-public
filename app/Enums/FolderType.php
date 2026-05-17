<?php

namespace App\Enums;

enum FolderType: string
{
    case Master    = 'Master';
    case Customers = 'Empresarios';
    case Staff     = 'Personal';
    case Register  = 'Registro';
    case Contracts = 'Contratos';
}
