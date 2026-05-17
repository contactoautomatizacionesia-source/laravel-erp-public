<?php

namespace Modules\RolePermission\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Routing\Controller;
use Modules\RolePermission\Entities\Role;
use Modules\RolePermission\Http\Requests\RoleFormRequest;
use Modules\RolePermission\Repositories\RoleRepository;
use Modules\UserActivityLog\Traits\LogActivity;
use Illuminate\Http\Request;
use App\Exceptions\CustomHandledException;

class RoleController extends Controller
{
    protected $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->middleware(['auth','maintenance_mode']);
        $this->middleware('prohibited_demo_mode')->only('store','update','destroy');
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        try{
            $data['roleList'] = $this->roleRepository->allWithUserCount();
            return view('rolepermission::role', $data);
        }catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.operation_failed'));
            return back();
        }
    }
    public function create()
    {
        return view('rolepermission::create');
    }
    public function store(RoleFormRequest $request)
    {
        try {
            $this->roleRepository->create($request->except("_token"));
            LogActivity::successLog('New Role - ('.$request->name.') has been created.');
            Toastr::success(__('common.created_successfully'), __('common.success'));
            return redirect()->route('permission.roles.index');
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage().' - Error has been detected for Role creation');
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }
    public function show($id)
    {
        return view('rolepermission::show');
    }
    public function edit(Role $role)
    {
        try {
            $roleList = $this->roleRepository->allWithUserCount();
            return view('rolepermission::role', compact('roleList', 'role'));
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.operation_failed'));
            return redirect()->back();
        }
    }
    public function update(RoleFormRequest $request, $id)
    {
        try {
            $role = $this->roleRepository->update($request->except("_token"), $id);
            if($role === true){
                LogActivity::successLog($request->name.'- has been updated.');
                Toastr::success(__('common.updated_successfully'), __('common.success'));
                return redirect(url('/hr/role-permission/roles'));
            }else{
                Toastr::error('Default Role Is Not Editable.');
                return redirect(url('/hr/role-permission/roles'));
            }

        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage().' - Error has been detected for Role update');
            Toastr::error(__('common.error_message'), __('common.error'));
            return redirect()->route('permission.roles.index');
        }
    }

    public function updateHolidayAllowed(Request $request, $id)
    {
        $validatedData = $request->validate([
            'holiday_allowed' => 'required|boolean',
        ]);

        try {

            $roleAffected = $this->roleRepository->updateHolidayAllowed($validatedData['holiday_allowed'], $id);

            LogActivity::successLog(__('hr.holiday_allowed_updated', [
                'role' => $roleAffected->name,
                'status' => $validatedData['holiday_allowed'] ? __('common.active') : __('common.inactive')
            ]));
            // 4. Manejo de respuesta Dual (AJAX o Web)
            if ($request->ajax()) {
                return response()->json([
                    'success' => __('common.updated_successfully'),
                    'status' => $validatedData['holiday_allowed']
                ], 200);
            }
            Toastr::success(__('common.updated_successfully'), __('common.success'));
            return redirect(url('/hr/role-permission/roles'));

        } catch (\Exception $e) {
            // LANZAMOS la excepción personalizada y el Handler hace el trabajo
            throw new CustomHandledException($e, 'update_holiday_allowed', ['operation' => __('hr.holiday_allowed_updated'), 'attribute' => 'rol', 'id' => $id]);
        }
    }

    public function destroy($id)
    {
        try {
            $role = $this->roleRepository->delete($id);
            if($role == 'not_possible'){
                Toastr::error(__('hr.delete_not_possible_role_has_user'), __('common.error'));
                return redirect()->back();
            }elseif($role == 'default_role'){
                Toastr::error('Default Role Is Not Deletable.');
                return redirect()->back();
            }
            LogActivity::successLog('A Role has been destroyed.');
            Toastr::success(__('common.deleted_successfully'), __('common.success'));
            return redirect()->back();
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage().' - Error has been detected for Role Destroy');
            return redirect()->back();
        }
    }

    public function checkDuplicate(Request $request)
    {
        $exists = Role::where('name', $request->name)
                    ->when($request->id, function($q) use($request){
                        return $q->where('id', '!=', $request->id);
                    })->exists();

        if ($exists) {
            return response()->json([
                'exists' => true,
                'message' => __('validation.unique', ['attribute' => __('common.name')])
            ], 200);
        }

        return response()->json(['exists' => false], 200);
    }
}
