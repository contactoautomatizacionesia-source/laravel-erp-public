<?php

namespace Modules\Affiliate\Http\Controllers;

use App\Models\User;
use App\Traits\ImageStore;
use App\Traits\Notification;
use App\Traits\Otp;
use App\Traits\SendMail;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Exception;
use Modules\FormBuilder\Repositories\FormBuilderRepositories;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\Marketing\Entities\ReferralCode;
use Modules\Marketing\Entities\ReferralCodeSetup;
use Modules\Marketing\Entities\ReferralUse;
use Modules\UserActivityLog\Traits\LogActivity;
use Nwidart\Modules\Facades\Module;


class AffiliateAuthController extends Controller
{
    use Notification, Otp, SendMail ,RegistersUsers;


    protected $affiliateRepo;

    public function __construct(AffiliateRepository $affiliateRepo)
    {
        $this->middleware(['guest', 'maintenance_mode'])->except(['indexPage']);
        $this->middleware(['prohibited_demo_mode'])->only('register');
        $this->affiliateRepo = $affiliateRepo;
    }

//    public function indexPage()
//    {
//        try{
//            return view('affiliate::index_page');
//        }catch(Exception $e){
//            Toastr::error($e->getMessage(), 'Error!!');
//            return response()->json(['error' => $e->getMessage()],503);
//        }
//    }

    public function showRegistrationFrom()
    {
        try{
            $data = [];
            if(Module::has('FormBuilder')){
                if(Schema::hasTable('custom_forms')){
                    $formBuilderRepo = new FormBuilderRepositories();
                    $data['row'] = $formBuilderRepo->find(1);
                    if($data['row']->form_data){
                        $data['form_data'] = json_decode($data['row']->form_data);
                    }
                }
            }
            return view('affiliate::auth.registration',$data);
        }catch(Exception $e){
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }
    }

    protected function redirectTo()
    {
        return '/affiliate/my_affiliate';

    }
    protected function validator(array $data)
    {

        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email', 'check_unique_phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'referral_code' => ['sometimes', 'nullable', Rule::exists('referral_codes', 'referral_code')->where('status', 1)]
        ],
        [
            'password.min' => 'The password field minimum 8 character.'
        ]);
    }

    protected function roleId()
    {

        return 4;
    }

    protected function othersFieldValue($data)
    {
        return json_encode($data);
    }

    public function create($data)
    {
        $c_data = [];
        if($data->has('custom_field')){
            foreach (json_decode($data['custom_field']) as  $key => $f){
                if($data->hasFile($f)){
                    $file = ImageStore::saveImage($data[$f], 165, 165);
                    $c_data[$f] = $file;
                }else{
                    $c_data[$f] = $data[$f];
                }
            }
        }

        $field = $data['email'];
        if (is_numeric($field)) {
            $phone = $data['email'];
        } elseif (filter_var($field, FILTER_VALIDATE_EMAIL)) {
            $email = $data['email'];
        }
        $user =  User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => isset($phone) ? $phone : NULL,
            'email' => isset($email) ? $email : NULL,
            'verify_code' => \Illuminate\Support\Str::random(40),
            'password' => Hash::make($data['password']),
            'role_id' => $this->roleId(),
            'phone' => isset($phone) ? $phone : NULL,
            'affiliate_request' => 1,
            'accept_affiliate_request' => affiliateConfig('admin_approval_need') == 1 ? 0 : 1,
            'others' => $this->othersFieldValue($c_data),
            'currency_id' => app('general_setting')->currency,
            'lang_code' => app('general_setting')->language_code,
            'currency_code' => app('general_setting')->currency_code,
        ]);

        //affiliate user
        if(isModuleActive('Affiliate')){
            $affiliateRepo = new AffiliateRepository();
            $affiliateRepo->affiliateUser($user->id);
        }

        // User Notification Setting Create
        (new UserNotificationSetting)->createForRegisterUser($user->id);
        $this->typeId = EmailTemplateType::where('type', 'register_email_template')->first()->id; //register email templete typeid
        $this->adminNotificationUrl = '/affiliate/users';
        $this->routeCheck = 'affiliate.users.index';
        $notification = NotificationSetting::where('slug','register')->first();
        if ($notification) {
            $this->notificationSend($notification->id, $user->id);
        }

        //for email verification
        if(!isModuleActive('Otp') && !otp_configuration('otp_activation_for_customer') && $email != null){
            if (app('business_settings')->where('type', 'email_verification')->first()->status == 1) {
                $code = '<a class="btn btn-success" href="' . url('/verify?code=') . $user['verify_code'] . '">Click Here To Verify Your Account</a>';
                $this->sendVerificationMail($user, $code);
            }
        }

        if (isset($data['referral_code'])) {
            $referralData = ReferralCodeSetup::first();
            $referralExist = ReferralCode::where('referral_code', $data['referral_code'])->first();
            if ($referralExist) {
                $referralExist->update(['total_used' => $referralExist->total_used + 1]);
                ReferralUse::create([
                    'user_id' => $user->id,
                    'referral_code' => $data['referral_code'],
                    'discount_amount' => $referralData->amount
                ]);
            }
        }
        return $user;
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        try{
            if (isModuleActive('Otp') && otp_configuration('otp_activation_for_customer')) {
                try {
                    if (!$this->sendOtp($request)) {
                        Toastr::error(__('otp.something_wrong_on_otp_send'), __('common.error'));
                        return back();
                    }
                    return view(theme('auth.otp'), compact('request'));
                } catch (Exception $e) {
                    LogActivity::errorLog($e->getMessage());
                    Toastr::error(__('otp.something_wrong_on_otp_send'), __('common.error'));
                    return back();
                }
            }
            DB::beginTransaction();

            event(new Registered($user = $this->create($request)));
            DB::commit();
            $this->guard()->login($user);

            Toastr::success(__('auth.successfully_registered'), __('common.success'));
            LogActivity::addLoginLog(Auth::user()->id, Auth::user()->first_name . ' - logged in at : ' . Carbon::now());
            return $this->registered($request, $user) ?: redirect($this->redirectPath());
        }catch(Exception $e){
            DB::rollBack();
            Toastr::error($e->getMessage(), 'Error!!');
            return response()->json(['error' => $e->getMessage()],503);
        }

    }




}
