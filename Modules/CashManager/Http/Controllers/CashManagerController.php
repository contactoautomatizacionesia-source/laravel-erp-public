<?php

namespace Modules\CashManager\Http\Controllers;

use Illuminate\Routing\Controller;

class CashManagerController extends Controller
{
    /**
     * Redirige al usuario a la sección correcta según sus permisos:
     *  - view_cash_operations  → Operaciones (cierre/arqueo)
     *  - manage_cash_assignments → Asignaciones
     *  - admin_cash_settings     → Configuraciones
     *
     * Si tiene varios permisos, la prioridad es: settings > assignments > operations.
     */
    public function index()
    {
        if (permissionCheck('admin_cash_settings')) {
            return redirect()->route('cash_manager.settings.index');
        }

        if (permissionCheck('manage_cash_assignments')) {
            return redirect()->route('cash_manager.assignments.index');
        }

        return redirect()->route('cash_manager.operations.index');
    }
}
