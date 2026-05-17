<?php

namespace Modules\RolePermission\Http\Controllers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\RolePermission\Entities\Permission;
use Modules\RolePermission\Entities\Role;
use Modules\UserActivityLog\Traits\LogActivity;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('maintenance_mode');
        $this->middleware('prohibited_demo_mode')->only('store');
    }
    public function index(Request $request)
    {
        try{
            $role = Role::with('permissions')->find($request['id']);
            if($role){
                if(!isModuleActive('MultiVendor') && $role->type == 'seller'){
                    return redirect(route('permission.roles.index'));
                }
                if($role->id == 1 || $role->type == 'customer'){
                    return redirect(route('permission.roles.index'));
                }
                if ($role->type == 'seller') {
                    $PermissionList = Permission::whereIn('module_id',['2','11','12','17','19','24','31','32','25','15','29','28','35','37','44','45','49','50','52','54','56'])->get();
                    $subModuleList = $PermissionList->where('type', 2)->whereIn('id', ['489','498','317','318', '514','346','505','506','507','508',
                    '509','510','511','163','164','165','166','167','154','155','156','157','158','159','160','161','17','18','19','20','21',
                    '22','23','24','25','492','493','494','495','532','533','534','535','536','569','571','574','609','615','625','619','620','621','624','364','681','687','679','690','706','707','708','711','712','713','725','728','729','730','731','740','741','742','751','752','753','754','743','744','745','747','1001','1002','1003','1004','1005','1006','1007']);
                }elseif($role->type == 'staff' || $role->type == 'admin') {
                    $PermissionList = Permission::whereNotIn('module_id',['11','12','29','2','35','37'])->get();
                    $subModuleList = $PermissionList->where('type',2);
                }
                $data['role'] =  $role;
                $data['MainMenuList'] = $this->sortMainMenuBySidebar($PermissionList->where('type',1));
                $data['SubMenuList'] = $subModuleList;
                $data['ActionList'] = $PermissionList->where('type',3);
                $data['PermissionList'] =  $PermissionList;

                return view('rolepermission::permission',$data);
            }else{
                return redirect(route('permission.roles.index'));
            }

        }catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }

    private function sortMainMenuBySidebar($mainMenuList)
    {
        $routes = $mainMenuList->pluck('route')->filter()->values()->toArray();

        if (empty($routes)) {
            return $mainMenuList;
        }

        // Obtener backendmenus que coincidan con las rutas de los módulos principales
        $menus = DB::table('backendmenus')
            ->whereIn('route', $routes)
            ->get(['id', 'route', 'parent_id', 'position']);

        // Obtener las secciones padre para calcular orden global
        $parentIds = $menus->pluck('parent_id')->filter()->unique()->values()->toArray();
        $parents = DB::table('backendmenus')
            ->whereIn('id', $parentIds)
            ->get(['id', 'parent_id', 'position']);

        // Para cada sección, necesitamos también su posición de sección raíz
        $sectionIds = $parents->pluck('parent_id')->filter()->unique()->values()->toArray();
        $sections = DB::table('backendmenus')
            ->whereIn('id', $sectionIds)
            ->get(['id', 'position']);

        $sectionPositions = $sections->keyBy('id');
        $parentPositions = $parents->keyBy('id');

        // Construir mapa route → orden global: section_pos*10000 + parent_pos*100 + item_pos
        $sortMap = [];
        foreach ($menus as $menu) {
            $itemPos = $menu->position;
            $parent = $menu->parent_id ? $parentPositions->get($menu->parent_id) : null;
            $parentPos = $parent ? $parent->position : 0;
            $section = ($parent && $parent->parent_id) ? $sectionPositions->get($parent->parent_id) : null;
            $sectionPos = $section ? $section->position : 0;

            $sortMap[$menu->route] = ($sectionPos * 10000) + ($parentPos * 100) + $itemPos;
        }

        return $mainMenuList->sortBy(function ($permission) use ($sortMap) {
            return $sortMap[$permission->route] ?? PHP_INT_MAX;
        })->values();
    }

    private function buildPermissionNames(array $ids): string
    {
        if (empty($ids)) {
            return '';
        }

        $mainMenuType = 1;
        $subMenuType = 2;
        $actionType = 3;

        $permissions = Permission::whereIn('id', $ids)->get();
        $parentIds = $permissions->pluck('parent_id')->filter()->unique();
        $moduleIds = $permissions->pluck('module_id')->unique();

        $allParentPermissions = Permission::whereIn('id', $parentIds)
            ->orWhere(function ($query) use ($moduleIds, $mainMenuType) {
                $query->whereIn('module_id', $moduleIds)
                    ->where('type', $mainMenuType);
            })
            ->get();

        $parentPermissions = $allParentPermissions->keyBy('id');
        $mainMenuPermissions = $allParentPermissions->where('type', $mainMenuType)->keyBy('module_id');

        // IDs de hijos para saber qué padres omitir
        $childrenParentIds = $permissions->pluck('parent_id')->unique()->toArray();
        $grouped = [];

        foreach ($permissions as $permission) {
            $mainMenu = $mainMenuPermissions->get($permission->module_id);
            $groupName = $mainMenu ? __($mainMenu->translation) : __('common.others');

            if (!isset($grouped[$groupName])) {
                $grouped[$groupName] = [];
            }

            if ($permission->type == $subMenuType) {
                // REGLA DE ORO: Si este submenú tiene acciones (hijos) en esta misma lista,
                // NO lo agregamos solo, para evitar "Ticket, Ticket > Crear"
                if (!in_array($permission->id, $childrenParentIds)) {
                    $grouped[$groupName][] = __($permission->translation);
                }
            } elseif ($permission->type == $actionType) {
                $parent = $parentPermissions->get($permission->parent_id);
                $parentName = $parent ? __($parent->translation) : '';
                $childName = __($permission->translation);

                // Evitamos "Ticket > Ticket" -> Simplificamos a "Ticket"
                if ($parentName == $childName) {
                    $grouped[$groupName][] = $parentName;
                } else {
                    // Si el padre existe y no es el módulo raíz, usamos el prefijo
                    $prefix = ($parent && $parent->type != $mainMenuType) ? $parentName . ' > ' : '';
                    $grouped[$groupName][] = $prefix . $childName;
                }
            }
        }

        $output = [];
        foreach ($grouped as $module => $actions) {
            if (!empty($actions)) {
                // Limpiamos duplicados exactos que hayan quedado
                $cleanActions = array_unique(array_filter($actions));
                sort($cleanActions);

                $output[] = PHP_EOL . "[$module: { " . implode(', ', $cleanActions) . " }]";
            }
        }

        return implode('', $output);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => "required",
            'module_id' => "required|array"
        ]);

        if($validator->fails()){
           Toastr::error(__('common.operation_failed'));
            return redirect()->back();
        }

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($request->role_id);

            $currentPermissionIds = $role->permissions->pluck('id')->toArray();
            $newPermissionIds = array_map('intval', array_unique($request->module_id));

            // Identifica que permisos se agregaron y cuales se removieron
            $addedPermissionIds = array_diff($newPermissionIds, $currentPermissionIds);
            $removedPermissionIds = array_diff($currentPermissionIds, $newPermissionIds);

            $role->permissions()->sync($newPermissionIds);

            if (!empty($addedPermissionIds) || !empty($removedPermissionIds)) {
                $addedNames   = $this->buildPermissionNames($addedPermissionIds);
                $removedNames = $this->buildPermissionNames($removedPermissionIds);

                $logMessage = __('permission.permissions_updated_for_role', ['attribute' => $role->name]);

                if ($addedNames) {
                    $logMessage .=  '. ' . __('permission.permissions_added', ['attribute' => $addedNames]);
                }

                if ($removedNames) {
                    $logMessage .= '. ' . __('permission.permissions_deleted', ['attribute' => $removedNames]);
                }

                LogActivity::successLog($logMessage);
            }

            DB::commit();
            Toastr::success(__('hr.permission_given_successfully'), __('common.success'));
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'), __('common.error'));
            return redirect()->back();
        }
    }
}
