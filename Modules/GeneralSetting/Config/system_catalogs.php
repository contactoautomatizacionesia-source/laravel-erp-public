<?php

use Modules\GeneralSetting\Entities\Catalogs\Gender;
use Modules\GeneralSetting\Entities\Catalogs\Bank;
use Modules\GeneralSetting\Entities\Catalogs\CivilStatus;
use Modules\GeneralSetting\Entities\Catalogs\ContractType;
use Modules\GeneralSetting\Entities\Catalogs\EconomicActivity;
use Modules\GeneralSetting\Entities\Catalogs\LeadSource;
use Modules\GeneralSetting\Entities\Catalogs\BankAccountType;
use Modules\GeneralSetting\Entities\Catalogs\InventoryOutReason;
use Modules\GeneralSetting\Entities\Catalogs\Novelty;
use Modules\GeneralSetting\Entities\Catalogs\Profession;
use Modules\GeneralSetting\Entities\Catalogs\ObservationType;
use Modules\GeneralSetting\Entities\Catalogs\CashDiscrepancyType;

return [
    'gender' => [
        'model' => Gender::class,
        'label' => 'Géneros',
        'has_code' => false,
    ],
    'civil_status' => [
        'model' => CivilStatus::class,
        'label' => 'Estados Civiles',
        'has_code' => false,
    ],
    'bank' => [
        'model' => Bank::class,
        'label' => 'Bancos',
        'has_code' => false,
    ],
    'bank_account_type' => [
        'model' => BankAccountType::class,
        'label' => 'Tipos de Cuenta Bancaria',
        'has_code' => false,
    ],
    'contract_type' => [
        'model' => ContractType::class,
        'label' => 'Tipos de Contrato',
        'has_code' => false,
    ],
    'lead_source' => [
        'model' => LeadSource::class,
        'label' => 'Fuentes de Prospecto (Leads)',
        'has_code' => false,
    ],
    'profession' => [
        'model' => Profession::class,
        'label' => 'Profesiones',
        'has_code' => false,
    ],
    'economic_activity' => [
        'model' => EconomicActivity::class,
        'label' => 'Actividades Económicas',
        'has_code' => true,
    ],
    'inventory_out_reason' => [
        'model' => InventoryOutReason::class,
        'label' => 'Razones de Salida de Inventario',
        'has_code' => false,
    ],
    'inventory_observation_type' => [
        'model'    => ObservationType::class,
        'label'    => 'Tipos de Observación (Conteo de Inventario)',
        'has_code' => false,
    ],
    'novelty' => [
        'model'    => Novelty::class,
        'label'    => 'Novedades (Transferencia de inventario)',
        'has_code' => false,
    ],
    'cash_discrepancy_type' => [
        'model'    => CashDiscrepancyType::class,
        'label'    => 'Tipos de Novedad de Caja',
        'has_code' => true,
    ],
];
