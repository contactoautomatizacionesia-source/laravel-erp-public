<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\OrderManage\Entities\CustomerNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class InventoryAlertController extends Controller
{
    public function index($id = null)
    {
        // Obtenemos solo las notificaciones de tipo inventario para el usuario actual
        // Filtramos solo las alertas de inventario (min y overstock)
        $inventoryTypes = ['overstock_alert', 'low_stock_alert', 'empty_stock_alert'];
        $selectedAlert = null;

        $alerts = CustomerNotification::where('customer_id', Auth::user()->id)
            ->whereIn('notification_type', $inventoryTypes)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Solo pasamos el objeto si existe el ID (para apertura automática desde notificación)
        $selectedAlert = $id ? CustomerNotification::where('customer_id', Auth::id())->find($id) : null;

        return view('product::inventory_alerts.index', compact('alerts', 'selectedAlert'));
    }

    public function showModal($id)
    {
        $alert = CustomerNotification::where('customer_id', Auth::user()->id)->findOrFail($id);

        // Validación y actualización automática de estado
        if ($alert->read_status == 0) {
            $alert->update(['read_status' => 1]);
        }

        // Retornamos una vista pequeña (parcial)
        return view('product::inventory_alerts.modal_content', compact('alert'));
    }
}
