<?php

namespace Modules\GeneralSetting\Entities\Catalogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Illuminate\Support\Facades\DB;

abstract class BaseCatalog extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'system_catalogs';
    protected $guarded = ['id'];
    public $translatable = ['name'];
    protected $casts = ['meta' => 'array','is_active' => 'boolean'];

    protected $appends = ['display_name'];

    /**
     * Definición del tipo
     */
    abstract public static function getCatalogType(): string;

    /**
     * Global Scopes : Boot
     */
    protected static function booted()
    {
        // Al consultar: Filtrar siempre por el tipo del hijo
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', static::getCatalogType());
        });

        // Al crear: Inyectar el tipo automáticamente
        static::creating(function ($model) {
            $model->type = static::getCatalogType();
        });

        // PROTECCIÓN DE BORRADO
        static::deleting(function ($model) {
            // Se carga el mapa de relaciones del modelo hijo
            $references = static::getUsageReferences();

            foreach ($references as $table => $column) {
                // Consultamos si existe AL MENOS un registro usando este ID
                // DB::table para que sea ultra rápido y no cargue modelos
                $exists = DB::table($table)
                    ->where($column, $model->id)
                    // Si usas SoftDeletes en la otra tabla (users), descomenta la siguiente línea:
                    // ->whereNull('deleted_at') 
                    ->exists();

                if ($exists) {
                    throw new \Exception("No se puede eliminar: Este registro está siendo usado en la tabla '{$table}'.");
                }
            }
        });
    }

    /**
     * Retorna un array con las tablas y columnas donde se usa este catálogo.
     * Estructura: ['nombre_tabla' => 'nombre_columna']
     */
    public static function getUsageReferences(): array
    {
        return []; // Por defecto no valida nada si el hijo no lo define
    }

    // --- Atributo Virtual (Display Name) ---
    public function getDisplayNameAttribute()
    {
        return $this->name; // Por defecto retorna solo el nombre
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

}