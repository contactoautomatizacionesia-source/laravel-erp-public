<?php

namespace Modules\ScheduleManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\RolePermission\Entities\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleWorkSchedule extends Model
{
    protected $table = "role_work_schedules";

    protected $fillable = [
        'schedule_code',
        'day_type',
        'start_time',
        'end_time',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'id'            => 'integer',
        'schedule_code' => 'string',
        'day_type'      => 'string',
        'start_time'    => 'datetime:H:i:s',
        'end_time'      => 'datetime:H:i:s',
        'is_active'     => 'boolean',
        'created_by'    => 'integer',
        'updated_by'    => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Generación automática del código único al crear el registro
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Ejecutamos dentro de una transacción con bloqueo
            DB::transaction(function () use ($model) {
                // lockForUpdate() evita que otros lean este registro hasta que termine el insert
                $latest = self::lockForUpdate()->orderBy('id', 'desc')->first();

                $number = $latest ? ((int) substr($latest->schedule_code, 4)) + 1 : 1;
                $model->schedule_code = 'SCH-' . str_pad($number, 3, '0', STR_PAD_LEFT);
            });

            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            // Registro de quién modifica el horario
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * A work schedule belongs to a role.
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_schedule_assignments',
            'schedule_id',
            'role_id'
        )->withTimestamps();
    }

    public static function getCategories()
    {
        return [
            'WEEKDAYS' => __('common.monday_to_friday'),
            'SATURDAY' => __('common.saturday'),
            'SUNDAY'   => __('common.sunday'),
            'HOLIDAY' => __('common.holidays'),
        ];
    }
}
