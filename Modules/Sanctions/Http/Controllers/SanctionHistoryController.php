<?php

namespace Modules\Sanctions\Http\Controllers;

use Illuminate\Routing\Controller;

class SanctionHistoryController extends Controller
{
    /**
     * Historial de Fallos
     */
    public function index()
    {
        return view('sanctions::history.index');
    }
}
