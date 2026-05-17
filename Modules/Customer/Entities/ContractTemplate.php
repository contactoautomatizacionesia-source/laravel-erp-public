<?php

namespace Modules\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Brand;

/**
 * Representa una plantilla de contrato por brand.
 *
 * Cada fila define:
 *  - qué brand emite el contrato          (brand_id → brands.id)
 *  - qué tipo de contrato es              (contract_type — lista cerrada)
 *  - qué vista Blade lo renderiza         (blade_view)
 *  - el prefijo del nombre de archivo PDF (filename_prefix)
 *
 * ContractBuilderService itera los registros activos para generar
 * el array $contratos que consume ProtecdataService::iniciarLote().
 */
class ContractTemplate extends Model
{
    protected $table = 'contract_templates';

    protected $fillable = [
        'brand_id',
        'contract_type',
        'blade_view',
        'filename_prefix',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    //  Tipos de contrato permitidos (lista cerrada — mirrors la migración enum)
    // -------------------------------------------------------------------------

    /** Contrato de registro / vinculación inicial del empresario. */
    public const TYPE_REGISTER = 'REGISTER';

    /** Lista completa de valores válidos para contract_type. */
    public const CONTRACT_TYPES = [
        self::TYPE_REGISTER => 'Registro',
    ];

    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
