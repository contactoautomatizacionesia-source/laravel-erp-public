<?php

namespace Modules\CycleClosure\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller;
use Modules\CycleClosure\Http\Requests\StoreCycleSettingRequest;
use Modules\CycleClosure\Services\CycleSettingService;
use Modules\UserActivityLog\Traits\LogActivity;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;

class CycleSettingController extends Controller
{
    public CycleSettingService $service;

    public function __construct(CycleSettingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $activeSetting = $this->service->getActive();

            // Ejecutor: SuperAdmin (role_id=1) , Admin (role_id=2) o Administrador (role_id=7)
            $executors = User::whereIn('role_id', [1, 2, 7])->where('is_active', 1)
                ->where('id', '!=', auth()->id())
                ->get(['id', 'first_name', 'last_name', 'email']);

            // Co-aprobador: Contador (role_id=27)
            $approvers = User::where('role_id', 27)->where('is_active', 1)
                ->where('id', '!=', auth()->id())
                ->get(['id', 'first_name', 'last_name', 'email']);

            return view('cycleclosure::settings.index', compact(
                'activeSetting',
                'executors',
                'approvers'
            ));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return back()->withErrors($e->getMessage());
        }
    }

    public function getData()
    {
        $settings = $this->service->repo->allForTable();

        return DataTables::of($settings)
            ->addColumn('period_type_label', fn($s) => __('cycleclosure::messages.period_' . $s->period_type))
            ->addColumn('execution_day_col', fn($s) => $s->execution_day ?? '—')
            ->addColumn('executor_name', fn($s) => optional($s->executor)->name ?? '—')
            ->addColumn('approver_name', fn($s) => optional($s->approver)->name ?? '—')
            ->addColumn('configured_by_name', fn($s) => optional($s->configurator)->name ?? '—')
            ->addColumn('status_badge', fn($s) => view('cycleclosure::settings.partials.status_badge', ['setting' => $s])->render())
            ->addColumn('saved_at', fn($s) => '<span title="' . $s->created_at->format('d/m/Y H:i:s') . '">'
                . $s->created_at->diffForHumans() . '</span>')
            ->rawColumns(['status_badge', 'saved_at'])
            ->make(true);
    }

    public function store(StoreCycleSettingRequest $request)
    {
        try {
            $this->service->store($request->validated());
            Toastr::success(__('cycleclosure::messages.setting_saved'), __('common.success'));
            return redirect()->route('cycle_closure.settings.index');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), __('common.error'));
            return back()->withInput();
        }
    }
}
