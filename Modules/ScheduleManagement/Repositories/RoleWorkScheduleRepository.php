<?php

namespace Modules\ScheduleManagement\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\ScheduleManagement\Entities\RoleWorkSchedule;

class RoleWorkScheduleRepository
{
    public function getAll(): Collection
    {
        return RoleWorkSchedule::all();
    }

    public function getById(int $id): RoleWorkSchedule
    {
        return RoleWorkSchedule::findOrFail($id);
    }

    public function create(array $data): RoleWorkSchedule
    {
        return RoleWorkSchedule::create($data);
    }

    public function update(array $data, int $id): bool
    {
        $record = RoleWorkSchedule::findOrFail($id);
        return $record->update($data);
    }

    public function destroy(int $id): bool
    {
        $roleWorkSchedule = RoleWorkSchedule::findOrFail($id);

        // Verificamos si hay usuarios activos con ese rol asignado a este horario
        // para evitar dejar huérfanos o inconsistencias
        $hasRelatedUsers = DB::table('users')
            ->where('is_active', true)
            ->whereIn('role_id', function ($query) use ($id) {
                $query->select('role_id')
                    ->from('role_schedule_assignments')
                    ->where('schedule_id', $id);
            })
            ->exists();

        if ($hasRelatedUsers) {
            throw new \Exception(__('role_work_schedule.errors.user_found'));
        }

        return $roleWorkSchedule->delete();
    }

    public function getAllSchedulesWithAssignment(int $roleId)
    {
        $allSchedules = RoleWorkSchedule::where('is_active', true)->get();

        // Obtenemos los IDs de los horarios que el rol ya tiene asignados
        $assignedScheduleIds = DB::table('role_schedule_assignments')
            ->where('role_id', $roleId)
            ->pluck('schedule_id')
            ->toArray();

        return $allSchedules->map(function ($schedule) use ($assignedScheduleIds) {
            $schedule->is_assigned = in_array($schedule->id, $assignedScheduleIds);
            return $schedule;
        });
    }

    public function assignRole(int $roleId, int $scheduleId)
    {
        $newSchedule = RoleWorkSchedule::findOrFail($scheduleId);
        $category = $newSchedule->day_type;

        return DB::transaction(function () use ($roleId, $scheduleId, $category) {
            // Buscar si el rol ya tiene UN horario asignado para ESTA categoría
            $existingAssignment = RoleWorkSchedule::where('day_type', $category)
                ->whereHas('roles', function ($q) use ($roleId) {
                    $q->where('role_id', $roleId);
                })->first();

            // Si existe y es diferente al nuevo, lo desvinculamos (Update lógico)
            if ($existingAssignment && $existingAssignment->id !== $scheduleId) {
                DB::table('role_schedule_assignments')
                    ->where('role_id', $roleId)
                    ->where('schedule_id', $existingAssignment->id)
                    ->delete();
            }

            // Vincular el nuevo horario (si no estaba ya vinculado)
            return DB::table('role_schedule_assignments')->updateOrInsert(
                ['role_id' => $roleId, 'schedule_id' => $scheduleId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        });
    }

    /**
     * Elimina la asignación de horarios de una categoría específica (ej: WEEKDAYS) para un rol.
     * Esta es la función clave para la deselección.
     */
    public function unassignScheduleFromRole(int $roleId, string $dayType): void
    {
        // Buscamos todos los IDs de horarios que sean de ese tipo (ej: todos los WEEKDAYS)
        $scheduleIdsWithType = RoleWorkSchedule::where('day_type', $dayType)->pluck('id');

        if ($scheduleIdsWithType->isNotEmpty()) {
            DB::table('role_schedule_assignments')
                ->where('role_id', $roleId)
                ->whereIn('schedule_id', $scheduleIdsWithType)
                ->delete();
        }
    }

    public function isRoleAllowedNow(int $roleId): bool
    {

        // Si TIENE configuración, validamos que esté dentro del rango actual
        // Carbon identifica la Zona Horaria configurada
        $currentHour = Carbon::now();
        // Formato de 24h para comparación interna en BD (H:i:s)
        $currentTime = $currentHour->format('H:i:s');
        // Determinar el tipo de día
        $dayType = match ($currentHour->dayOfWeek) {
            Carbon::SATURDAY => 'SATURDAY',
            Carbon::SUNDAY   => 'SUNDAY',
            default          => 'WEEKDAYS',
        };
        // Validamos si existe un horario activo para el rol en este momento.
        // Si no existe ninguna configuración asignada al rol, permitimos el paso (retorna true).
        $hasSchedules = RoleWorkSchedule::whereHas('roles', function ($q) use ($roleId) {
            $q->where('role_id', $roleId);
        })->where('is_active', true)->exists();

        if (!$hasSchedules) {
            return true;
        }
        // Validación de rangos (Soporta múltiples rangos como 8-12:30 y 14-18)
        return RoleWorkSchedule::where('is_active', true)
            ->where('day_type', $dayType)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->whereHas('roles', function ($query) use ($roleId) {
                $query->where('role_id', $roleId);
            })
            ->exists();
    }
}
