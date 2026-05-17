<?php

namespace Modules\OrderManage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\OrderManage\Services\CancelReasonService;
use Brian2694\Toastr\Facades\Toastr;
use Modules\OrderManage\Http\Requests\CancelResonRequest;
use Modules\UserActivityLog\Traits\LogActivity;
use Modules\OrderManage\Exceptions\CancelReasonInUseException;

class CancelReasonController extends Controller
{
    protected $cancelReasonService;

    public function __construct(CancelReasonService $cancelReasonService){
        $this->middleware('maintenance_mode');
        $this->cancelReasonService = $cancelReasonService;
    }

    public function index()
    {
        $data['items'] = $this->cancelReasonService->getAll();
        return view('ordermanage::cancel_reasons.index', $data);
    }

    public function process_list()
    {
        $data['items'] = $this->cancelReasonService->getAll();
        return view('ordermanage::cancel_reasons.reason_list', $data);
    }

    public function store(CancelResonRequest $request)
    {
        try {
            $this->cancelReasonService->save($request->except("_token"));
            LogActivity::successLog('Reason added.');
            return response()->json(["message" => __('order.cancel_reason_stored_successfully')], 200);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(["message" => __('order.cancel_reason_store_error')], 503);
        }
    }

    public function update(CancelResonRequest $request, $id)
    {
        try {
            $this->cancelReasonService->update($request->except("_token"), $id);
            LogActivity::successLog('Reason updated.');
            return response()->json(["message" => __('order.cancel_reason_updated_successfully')], 200);
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage());
            return response()->json(["message" => __('order.cancel_reason_update_error')], 503);
        }
    }

    public function destroy($id)
    {
        try {
            $this->cancelReasonService->delete($id);
            LogActivity::successLog('A Reason has been destroyed.');
            Toastr::success(__('common.deleted_successfully'),__('common.success'));
            return back();
        } catch (CancelReasonInUseException $e) {
            LogActivity::errorLog($e->getMessage().' - Error has been detected for Cancel Reason.');
            Toastr::error($e->getMessage(), __('common.error'));
            return back();
        } catch (\Exception $e) {
            LogActivity::errorLog($e->getMessage().' - Error has been detected for Cancel Reason.');
            Toastr::error(__('common.error_message'), __('common.error'));
            return back();
        }
    }
}
