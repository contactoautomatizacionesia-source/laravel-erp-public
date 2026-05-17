<?php

namespace Modules\Sanctions\Http\Controllers;

use Illuminate\Routing\Controller;

class SanctionSettingsController extends Controller
{
    /**
     * Configuración
     */
    public function index()
    {
        return view('sanctions::settings.index');
    }
}
