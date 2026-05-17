<?php

namespace Modules\GeneralSetting\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Product\Entities\Brand;
use Modules\GeneralSetting\Entities\DianSetting;

class DianSettingsController extends Controller
{
    public function index()
    {
        return view('generalsetting::dian.index');
    }
}