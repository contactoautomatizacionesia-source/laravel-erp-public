<?php

namespace Modules\GeneralSetting\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Brian2694\Toastr\Facades\Toastr;
use \Modules\GeneralSetting\Services\EmailTemplateService;
use Modules\OrderManage\Repositories\DeliveryProcessRepository;
use Modules\Refund\Repositories\RefundProcessRepository;
use Modules\GeneralSetting\Http\Requests\EmailTemplateRequest;
use App\Traits\SendMail;
use Modules\UserActivityLog\Traits\LogActivity;

class EmailTemplateController extends Controller
{
    use SendMail;
    protected $emailTemplateService;
    public function __construct(EmailTemplateService $emailTemplateService)
    {
        $this->middleware('maintenance_mode');
        $this->emailTemplateService = $emailTemplateService;
    }
    public function index()
    {
        $data['email_templates'] = $this->emailTemplateService->getEmailTemplates();
        return view('generalsetting::email_templates.index', $data);
    }
    public function create()
    {
        $data['email_template_types'] = $this->emailTemplateService->getEmailTemplateTypes();
        $orderDeliveryRepo = new DeliveryProcessRepository;
        $data['delivery_processes'] = $orderDeliveryRepo->getAll();
        $refundProcessRepo = new RefundProcessRepository;
        $data['refund_processes'] = $refundProcessRepo->getAll();
        return view('generalsetting::email_templates.create', $data);
    }
    public function store(EmailTemplateRequest $request)
    {
        try {
            if ($request->reciepnt_type == null) {
                Toastr::error(__('general_settings.please_select_valid_reciepent_to_create_template'));
                return back();
            }
            $this->emailTemplateService->createTemplate($request->except('_token'));
            Toastr::success(__('common.created_successfully'),__('common.success'));
            // Registra el guardado en el activity log
            LogActivity::successLog(__('general_settings.email_template_created', ['attribute' => $request->subject]));
            return redirect(route('email_templates.index'));
        } catch (\Illuminate\Database\QueryException $e) {
            // Registra el error de la guardado en el activity log
            LogActivity::errorLog(__('general_settings.email_template_create_db_error'));
            Toastr::error(__('common.error_message'));
            return back();
        } catch (\Exception $e) {
            // Registra el error del guardado en el activity log
            LogActivity::errorLog(__('general_settings.email_template_create_error', ['error' => $e->getMessage()]));
            Toastr::error(__('common.error_message'));
            return back();
        }
    }
    public function show($id)
    {
        try {
            $data['email_template'] = $this->emailTemplateService->find($id);
            return view('generalsetting::email_templates.edit', $data);
        } catch (\Illuminate\Database\QueryException $e) {
            // Registra el error de la visualizacion en el activity log
            LogActivity::errorLog(__('general_settings.email_template_show_db_error', ['attribute' => $id]));
            Toastr::error(__('common.error_message'));
            return back();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Registra el error de la visualizacion en el activity log
            LogActivity::errorLog(__('general_settings.email_template_show_not_found_error', ['attribute' => $id]));
            Toastr::error(__('common.error_message'));
            return back();
        } catch (\Exception $e) {
            // Registra el error de la visualizacion en el activity log
            LogActivity::errorLog(__('general_settings.email_template_show_error', ['attribute' => $id, 'error' => $e->getMessage()]));
            Toastr::error(__('common.error_message'));
            return back();
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $this->emailTemplateService->updateEmailTemplate($request->except('_token'), $id);
            Toastr::success(__('common.updated_successfully'),__('common.success'));
            // Registra la actualizacion en el activity log
            LogActivity::successLog(__('general_settings.email_template_updated', ['attribute' => $request->subject]));
            return redirect(route('email_templates.index'));
        } catch (\Illuminate\Database\QueryException $e) {
            // Registra el error de la actualizacion en el activity log
            LogActivity::errorLog(__('general_settings.email_template_update_db_error', ['attribute' => $request->subject]));
            Toastr::error(__('common.error_message'));
            return back();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Registra el error de la actualizacion en el activity log
            LogActivity::errorLog(__('general_settings.email_template_update_not_found_error', ['attribute' => $request->subject]));
            Toastr::error(__('common.error_message'));
            return back();
        } catch (\Exception $e) {
            // Registra el error de la actualizacion en el activity log
            LogActivity::errorLog(__('general_settings.email_template_update_error', ['attribute' => $request->subject, 'error' => $e->getMessage()]));
            Toastr::error(__('common.error_message'));
            return back();
        }
    }
    public function update_status(Request $request)
    {
        try {
            $result = $this->emailTemplateService->updateEmailTemplateStatus($request->except('_token'));
            if($result == 1){
                $template = $this->emailTemplateService->find($request->id);
                if ($template) {
                    LogActivity::successLog(__('general_settings.email_template_updated', ['attribute' => $template->subject]));
                }
            }
            return $this->reload_with_data($result);
        } catch (\Illuminate\Database\QueryException $e) {
            // Registra el error de la actualizacion del estado en el activity log
            LogActivity::errorLog(__('general_settings.email_template_update_status_db_error'));
            return 0;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Registra el error de la actualizacion del estado en el activity log
            LogActivity::errorLog(__('general_settings.email_template_update_status_not_found_error'));
            return 0;
        } catch (\Exception $e) {
            // Registra el error de la actualizacion del estado en el activity log
            LogActivity::errorLog(__('general_settings.email_template_update_status_error', ['error' => $e->getMessage()]));
            return 0;
        }
    }
    public function test_mail_send(Request $request)
    {
        try {
            $mail =  $this->sendMailTest($request->email, "Test Mail", $request->content);
            if($mail == true)
            {
                Toastr::success(__('general_settings.Mail has been sent Successfully'));
                return back();
            }
                Toastr::success(__('general_settings.Please Configure SMTP settings first'));
                return back();
        }catch(\Exception $e){
            LogActivity::errorLog($e->getMessage());
            Toastr::error(__('common.error_message'));
            return redirect()->back();
        }
    }
    private function reload_with_data($result){
        $email_templates = $this->emailTemplateService->getEmailTemplates();
        return response()->json([
            'msg' => $result,
            'list' => (string)view('generalsetting::email_templates.components.list', compact('email_templates'))
        ]);
    }
}
