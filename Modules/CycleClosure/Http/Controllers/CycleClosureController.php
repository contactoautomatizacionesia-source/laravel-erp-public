<?php

namespace Modules\CycleClosure\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CycleClosure\Http\Requests\ApproveCycleRequest;
use Modules\CycleClosure\Repositories\CycleLogRepository;
use Modules\CycleClosure\Services\CycleClosureService;
use Modules\UserActivityLog\Traits\LogActivity;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\Facades\DataTables;

class CycleClosureController extends Controller
{
    public CycleClosureService $service;
    public CycleLogRepository $logRepo;

    public function __construct(CycleClosureService $service, CycleLogRepository $logRepo)
    {
        $this->service = $service;
        $this->logRepo = $logRepo;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VISUALIZACIÓN
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        try {
            return view('cycleclosure::cycles.index');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return back()->withErrors($e->getMessage());
        }
    }

    public function getData()
    {
        $cycles = $this->service->repo->getAll();

        return DataTables::of($cycles)
            ->addIndexColumn()
            ->addColumn('period', fn($c) => $c->period_label)
            ->addColumn('executed_at_col', function ($c) {
                if (! $c->executed_at) {
                    return '<span class="badge_3">' . __('cycleclosure::messages.scheduled') . '</span>';
                }
                return '<span title="' . $c->executed_at->format('d/m/Y H:i:s') . '">'
                    . $c->executed_at->diffForHumans() . '</span>';
            })
            ->addColumn('executor_name', fn($c) => optional($c->executor)->name ?? '—')
            ->addColumn('co_approver_name', fn($c) => optional($c->coApprover)->name
                ?? '<span class="text-muted">' . __('cycleclosure::messages.pending') . '</span>')
            ->addColumn('status_badge', fn($c) => view('cycleclosure::cycles.partials.status_badge', ['cycle' => $c])->render())
            ->addColumn('actions', fn($c) => view('cycleclosure::cycles.partials.actions', ['cycle' => $c])->render())
            ->rawColumns(['executed_at_col', 'co_approver_name', 'status_badge', 'actions'])
            ->make(true);
    }

    public function show(int $id)
    {
        try {
            $cycle = $this->service->repo->findById($id);
            abort_if(! $cycle, 404);
            return view('cycleclosure::cycles.show', compact('cycle'));
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return back()->withErrors($e->getMessage());
        }
    }

    public function getLogsData(int $id)
    {
        $logs = $this->logRepo->getDataTableByCycle($id);

        return DataTables::of($logs)
            ->addIndexColumn()
            ->addColumn('phase_label', fn($l) => $l->phase ? __('cycleclosure::messages.phase_' . $l->phase) : '—')
            ->addColumn('level_badge', fn($l) => view('cycleclosure::cycles.partials.level_badge', ['log' => $l])->render())
            ->addColumn('user_name', fn($l) => optional($l->user)->name ?? '—')
            ->addColumn('created_at_col', fn($l) => '<span title="' . $l->created_at->format('d/m/Y H:i:s') . '">'
                . $l->created_at->diffForHumans() . '</span>')
            ->rawColumns(['level_badge', 'created_at_col'])
            ->make(true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESCARGA
    // ─────────────────────────────────────────────────────────────────────────

    public function downloadActa(int $id)
    {
        try {
            $cycle = $this->service->repo->findById($id);
            abort_if(! $cycle || $cycle->status !== 'closed', 404);
            abort_if(! $cycle->act_path || ! file_exists(storage_path('app/' . $cycle->act_path)), 404);

            return response()->download(
                storage_path('app/' . $cycle->act_path),
                'Acta-Cierre-' . $cycle->period_label . '.pdf'
            );
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), __('common.error'));
            return back();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCIONES DEL EJECUTOR (solo cuando needs_review)
    // ─────────────────────────────────────────────────────────────────────────

    public function approveByExecutor(int $id)
    {
        try {
            $cycle = $this->service->approveByExecutor($id);
            Toastr::success(__('cycleclosure::messages.executor_approved'), __('common.success'));
            return redirect()->route('cycle_closure.show', $cycle->id);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), __('common.error'));
            return back();
        }
    }

    public function cancelByExecutor(int $id)
    {
        try {
            $this->service->cancelByExecutor($id);
            Toastr::success(__('cycleclosure::messages.cycle_cancelled'), __('common.success'));
            return redirect()->route('cycle_closure.index');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), __('common.error'));
            return back();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCIONES DEL CO-APROBADOR (solo cuando pending_approval)
    // ─────────────────────────────────────────────────────────────────────────

    public function approveByCoapprover(int $id, ApproveCycleRequest $request)
    {
        try {
            $cycle = $this->service->approveByCoapprover($id);
            Toastr::success(__('cycleclosure::messages.cycle_approved'), __('common.success'));
            return redirect()->route('cycle_closure.show', $cycle->id);
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), __('common.error'));
            return back();
        }
    }

    public function rejectByCoapprover(int $id, ApproveCycleRequest $request)
    {
        try {
            $this->service->rejectByCoapprover($id);
            Toastr::success(__('cycleclosure::messages.cycle_rejected'), __('common.success'));
            return redirect()->route('cycle_closure.index');
        } catch (Exception $e) {
            LogActivity::errorLog($e->getMessage());
            Toastr::error($e->getMessage(), __('common.error'));
            return back();
        }
    }
}
