<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixDoubleApprovalPermissionModule extends Migration
{
    /**
     * Run the migrations.
     *
     * Corrige el module_id del permiso Double Approval para que pertenezca
     * al módulo ClubPoint (module_id = 51) en lugar del valor dinámico
     * incorrecto asignado en la migración original.
     *
     * @return void
     */
    public function up()
    {
        // Obtener el module_id de ClubPoint buscando un permiso raíz del módulo
        $clubPoint = DB::table('permissions')
            ->where('route', 'clubpoint')
            ->first();

        if (! $clubPoint) {
            throw new \RuntimeException('No se encontró el permiso raíz de ClubPoint (route: clubpoint.club_point). Verifica que la migración del módulo ClubPoint haya sido ejecutada.');
        }

        DB::table('permissions')
            ->where('route', 'double_approval.index')
            ->update([
                'module_id' => $clubPoint->module_id,
                'module'    => $clubPoint->module,
                'parent_id' => $clubPoint->id,
                'type'      => 2,
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restaura el module_id al valor que tenía antes (max+1 al momento
        // de la migración original). Como ese valor es dinámico y ya no es
        // reproducible, se deja el módulo original de ClubPoint para evitar
        // dejar el registro en un estado inconsistente.
    }
}
