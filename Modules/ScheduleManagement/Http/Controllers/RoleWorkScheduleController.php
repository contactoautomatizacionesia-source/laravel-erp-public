<?php

namespace Modules\ScheduleManagement\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\ScheduleManagement\Services\RoleWorkScheduleService;
use Modules\RolePermission\Services\RoleService;
use Brian2694\Toastr\Facades\Toastr;
use Modules\RolePermission\Entities\Role;
use Modules\UserActivityLog\Traits\LogActivity;

class RoleWorkScheduleController extends Controller
{
    protected $roleWorkScheduleService;
    protected $roleService;

    public function __construct(
        RoleWorkScheduleService $roleWorkScheduleService,
        RoleService $roleService
    ) {
        $this->roleWorkScheduleService = $roleWorkScheduleService;
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        try {
            if ($request->has('id')) {
                $roleId = $request->get('id');
                $role = Role::find($roleId);

                if ($role) {
                    $data['role_to_assign'] = $role;
                    $data['RoleWorkScheduleList'] = $this->roleWorkScheduleService->getAllSchedulesWithAssignment($roleId);
                } else {
                    Toastr::error('Rol no encontrado');
                    return redirect()->route('role_work_schedule.index');
                }
            } else {
                $data['RoleWorkScheduleList'] = $this->roleWorkScheduleService->getAll();
            }

            $data['roles'] = $this->roleService->normalRoles();

            return view('schedulemanagement::index', $data);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(): View
    {
        return view('schedulemanagement::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'day_type' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ]);

        try {
            $this->roleWorkScheduleService->create($request->except('_token'));
            LogActivity::successLog(__('role_work_schedule.created'));

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('role_work_schedule.created')]);
            }

            Toastr::success(__('common.created_successfully'));
            return back();
        } catch (\Exception $e) {
            // RESTAURADO: Log de error y Toastr
            LogActivity::errorLog($e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    public function assign(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer',
            'schedules' => 'nullable|array'
        ]);

        try {
            $roleId = $request->role_id;

            if ($request->has('schedules')) {
                foreach ($request->schedules as $category => $scheduleId) {
                    // Si el usuario manipuló el HTML y envió el dummy de Holiday aquí, lo saltamos
                    if ($category === 'HOLIDAY') continue;

                    if (!empty($scheduleId)) {
                        // CASO 1: Viene un ID -> ASIGNAR (Esto ya funcionaba)
                        $this->roleWorkScheduleService->assignRole($roleId, $scheduleId);
                    } else {
                        // CASO 2: Viene vacío -> DESVINCULAR (Esto faltaba)
                        // Le decimos al servicio: "Quítale cualquier horario de tipo $category a este rol"
                        $this->roleWorkScheduleService->unassignScheduleFromRole($roleId, $category);
                    }
                }
            }

            LogActivity::successLog(__('role_work_schedule.assign_schedule_to') . ' ' . $request->role_name);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('role_work_schedule.schedules_assigned_correctly')]);
            }

            Toastr::success(__('role_work_schedule.schedules_assigned_correctly'));
            return back();
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(int $id): View
    {
        return view('schedulemanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(int $id): View
    {
        return view('schedulemanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, int $id)
    {
        try {
            $this->roleWorkScheduleService->update($request->except(['_token', '_method']), $id);
            LogActivity::successLog(__('role_work_schedule.updated'));

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => __('common.updated_successfully')]);
            }

            Toastr::success(__('common.updated_successfully'));
            return back();
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());

            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            Toastr::error(__('common.error_message'));
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(int $id)
    {
        try {
            $this->roleWorkScheduleService->destroy($id);
            LogActivity::successLog(__('role_work_schedule.deleted'));

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => __('common.deleted_successfully')]);
            }

            Toastr::success(__('common.deleted_successfully'));
            return back();
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());

            if (request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return back();
        }
    }
}
