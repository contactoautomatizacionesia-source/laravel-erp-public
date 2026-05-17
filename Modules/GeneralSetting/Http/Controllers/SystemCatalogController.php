<?php

namespace Modules\GeneralSetting\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\Facades\DataTables;

class SystemCatalogController extends Controller
{
    private function getConfigs()
    {
        return config('generalsetting.system_catalogs') ?? require module_path('GeneralSetting', 'Config/system_catalogs.php');
    }

    public function index()
    {
        $catalogs = $this->getConfigs();
        return view('generalsetting::catalogs.index', compact('catalogs'));
    }

    public function datatable(Request $request)
    {
        $type = $request->type;
        $configs = $this->getConfigs();

        if (!isset($configs[$type])) {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }

        $modelClass = $configs[$type]['model']; 

        $query = $modelClass::query(); 

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $currentName = e($row->name); 
                $currentCode = e($row->code);
            
                return '
                    <div class="dropdown CRM_dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                            '. __('common.select') .'
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="#" class="dropdown-item btn-edit" 
                                data-id="'.$row->id.'" 
                                data-name="'.$currentName.'" 
                                data-code="'.$currentCode.'"
                                data-active="'.$row->is_active.'">
                                '.__('common.edit').'
                            </a>
                            <a href="#" class="dropdown-item btn-delete" data-id="'.$row->id.'">
                                '.__('common.delete').'
                            </a>
                        </div>
                    </div>
                ';
            })
            ->editColumn('name', function ($row) {
                return $row->name;
            })
            ->editColumn('is_active', function ($row) {
                return $row->is_active 
                    ? '<span class="badge badge-success">Activo</span>' 
                    : '<span class="badge badge-danger">Inactivo</span>';
            })
            ->rawColumns(['action', 'is_active'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $configs = $this->getConfigs();
        $type = $request->type;

        if (!isset($configs[$type])) abort(404);

        $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:50',
        ]);

        $modelClass = $configs[$type]['model'];
            
        $record = $modelClass::findOrNew($request->id);

        $record->code = $request->code;
        $record->is_active = $request->is_active ?? 1;

        $record->setTranslation('name', app()->getLocale(), $request->name);

        $record->save();

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, $id)
    {
        $configs = $this->getConfigs();
        $type = $request->type;
        
        if (!isset($configs[$type])) abort(404);

        $modelClass = $configs[$type]['model'];

        try {
            $record = $modelClass::findOrFail($id);
            $record->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}