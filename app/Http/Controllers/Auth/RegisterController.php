<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\TypeDocument;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Rules\RealEmail;
use App\Traits\ImageStore;
use App\Traits\Notification;
use App\Traits\Otp;
use App\Traits\SendMail;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\FormBuilder\Repositories\FormBuilderRepositories;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\Marketing\Entities\ReferralCodeSetup;
use Modules\Marketing\Entities\ReferralUse;
use Modules\Marketing\Entities\ReferralCode;
use Modules\UserActivityLog\Traits\LogActivity;
use Nwidart\Modules\Facades\Module;
use Exception;
use Modules\FrontendCMS\Entities\LoginPage;
use Modules\GeneralSetting\Entities\NotificationSetting;
use App\Rules\RealEmaill;
use Modules\Customer\Http\Requests\RegisterCustomerRequest;
use Modules\GeneralSetting\Entities\Catalogs\CivilStatus;
use Modules\GeneralSetting\Entities\Catalogs\EconomicActivity;
use Modules\GeneralSetting\Entities\Catalogs\Gender;
use Modules\GeneralSetting\Entities\Catalogs\LeadSource;
use Modules\GeneralSetting\Entities\Catalogs\Profession;
use Modules\Customer\Services\CustomerService;
use Illuminate\Support\Facades\Log;
use Modules\GeneralSetting\Entities\Catalogs\CountryPhoneCode;
use Modules\Marketing\Services\ReferralCodeService;
use App\Services\IdentityReader\IdentityReaderService;

class RegisterController extends Controller
{
    use Notification, Otp, SendMail, RegistersUsers;
    protected $customerService;
    protected $referralCodeService;

    protected function redirectTo()
    {
        if (app('business_settings')->where('type', 'email_verification')->first()->status == 1) {
            return '/user-email-verify';
        }
        if(session()->has('from_checkout')){
            $next_url = session()->get('from_checkout');
            session()->forget('from_checkout');
            return $next_url;
        }
        return '/profile/dashboard';
    }

    public function __construct(CustomerService  $customerService, ReferralCodeService $referralCodeService)
    {
        $this->middleware(['guest', 'maintenance_mode'])->except(['scanId']);
        $this->middleware(['prohibited_demo_mode'])->only('register');

        $this->customerService = $customerService;
        $this->referralCodeService = $referralCodeService;
    }

    protected function validator(array $data)
    {
        if (env('NOCAPTCHA_FOR_REG') == "true" && app('theme')->folder_path == 'amazy') {
            $g_recaptcha = 'required';
        }else{
            $g_recaptcha = 'nullable';
        }
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
           $email = ['required', 'string', 'max:255','email',new RealEmail(),'unique:users,email'];
        }elseif (preg_match("/^\\+?\\d{1,4}?[-.\\s]?\\(?\\d{1,3}?\\)?[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,4}[-.\\s]?\\d{1,9}$/",$data['email'])) {
            $email = ['required', 'string','min:7', 'max:16','unique:users,phone'];
        }else {
            $email = ['required', 'string', 'max:255','email',new RealEmail()];
        }

        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'email' => $email,
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'g-recaptcha-response' =>$g_recaptcha,
            'referral_code' => ['sometimes', 'nullable', Rule::exists('referral_codes', 'referral_code')->where('status', 1)]
        ],
        [
            'password.min' => 'The password field minimum 8 character.',
            'g-recaptcha-response.required' => 'The google recaptcha field is required.',
        ]);
    }

    protected function othersFieldValue($data)
    {
        return json_encode($data);
    }

    public function create($data)
    {
        // 1. Preparación y validación de datos
        $validatedData = $data instanceof \Illuminate\Http\Request ? $data->validated() : $data;

        $manualActivationEnabled = manualActivation();
        $validatedData['is_active'] = $manualActivationEnabled ? 0 : 1;
        $validatedData['approval_status'] = $manualActivationEnabled
            ? User::APPROVAL_STATUS_PENDING
            : User::APPROVAL_STATUS_APPROVED;
        $validatedData['approval_reason'] = $manualActivationEnabled
            ? __('auth.initial_pending_manual_approval_reason')
            : __('auth.initial_auto_approved_reason');

        if (!empty($validatedData['referral_code'])) {
            $referralOwner = ReferralCode::where('referral_code', $validatedData['referral_code'])
                ->where('status', 1)
                ->first();
            if ($referralOwner) {
                $validatedData['representative_id'] = $referralOwner->user_id;
            }
        }
        if (empty($validatedData['representative_id'])) {
            $validatedData['representative_id'] = $this->getRootRepresentativeId();
        }

        // 2. Creación del usuario

        $user = $this->customerService->registerCustomer($validatedData, auth()->id());

        // 3. Tareas posteriores al registro (Afiliados, Notificaciones, Referidos, Emails)
        $this->processAffiliateAndNotifications($user);

        if (!empty($validatedData['referral_code'])) {
            $this->processReferralUsage($user, $validatedData['referral_code']);
        }

        $this->handleEmailVerification($user);

        return $user;
    }

    /**
     * Extrae la lógica del módulo de afiliados y configuraciones de notificaciones
     */
    private function processAffiliateAndNotifications($user)
    {
        if (isModuleActive('Affiliate')) {
            $affiliateRepo = new AffiliateRepository();
            $affiliateRepo->affiliateUser($user->id);
        }

        (new UserNotificationSetting)->createForRegisterUser($user->id);
        
        $this->typeId = EmailTemplateType::where('type', 'register_email_template')->first()->id;
        $this->adminNotificationUrl = '/customer/active-customer-list';
        $this->routeCheck = 'cusotmer.list.get-data';
        
        $notification = NotificationSetting::where('slug', 'register')->first();
        if ($notification) {
            $this->notificationSend($notification->id, $user->id);
        }
    }

    /**
     * Extrae la lógica para actualizar el uso y contabilidad de códigos referidos
     */
    private function processReferralUsage($user, $referralCode)
    {
        $referralExist = ReferralCode::where('referral_code', $referralCode)->first();
        if ($referralExist) {
            $referralData = ReferralCodeSetup::first();
            $referralExist->update(['total_used' => $referralExist->total_used + 1]);
            
            ReferralUse::create([
                'user_id' => $user->id,
                'referral_code' => $referralCode,
                'discount_amount' => $referralData ? $referralData->amount : 0
            ]);
        }
    }

    /**
     * Extrae la lógica de comprobación y envío del correo de verificación
     */
    private function handleEmailVerification($user)
    {
        $email = $user->email;
        $verificationSetting = app('business_settings')->where('type', 'email_verification')->first();

        if ($verificationSetting && $verificationSetting->status == 1 && $email != null && (!isModuleActive('Otp') || !otp_configuration('otp_activation_for_customer'))) {
            // Send verification mail only if OTP activation is not forced for customers
            $code = '<a class="btn btn-success" href="' . url('/verify?code=') . $user->verify_code . '">Click Here To Verify Your Account</a>';
            $this->sendVerificationMail($user, $code);
        }
    }
    private function getRootRepresentativeId(): ?int
    {
        // 1. Identificar al Empresario Root por su correo
        $rootId = config('networktree.root_user_id');
        $rootEmail = config('networktree.root_user_email');

        $rootUser = User::where('id', $rootId)->orWhere('email', $rootEmail)->first();
        
        if (!$rootUser) {
            Log::warning('Root user not found for default representative assignment.');
            return null;
        }

        return $rootUser->id;
    }


    public function register(RegisterCustomerRequest $request)
    {
        try {
            if(manualActivation()){
                event(new Registered($user = $this->create($request)));
                $this->newUserRegistradEmailSend('new_user_registration_template',$user);
                if(!empty(app('general_setting')->registration_success_url)){
                    $url =app('general_setting')->registration_success_url;
                    return  redirect()->to($url);
                }else{
                    Toastr::success(__('auth.successfully_registered_activation'), __('common.success'));
                    return redirect()->to('/');
                }
            }


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
            $authRepos = new AuthRepository();
            $user_exist = $authRepos->getRegister($request->all());

            if($user_exist){
                $prev_session_id = session()->getId();
                $buy_it_now = session()->get('buy_it_now');
                $this->guard()->login($user_exist);
                $this->dataUpdateWhenLogin($prev_session_id, $buy_it_now);
                Toastr::success(__('auth.successfully_registered'), __('common.success'));
                LogActivity::addLoginLog(Auth::user()->id, Auth::user()->first_name . ' - logged in at : ' . Carbon::now());
                return $this->registered($request, $user_exist) ?: redirect($this->redirectPath());
            }

            event(new Registered($user = $this->create($request)));
            $prev_session_id = session()->getId();
            $buy_it_now = session()->get('buy_it_now');
            $this->guard()->login($user);
            $this->dataUpdateWhenLogin($prev_session_id, $buy_it_now);
            Toastr::success(__('auth.successfully_registered'), __('common.success'));
            LogActivity::addLoginLog(Auth::user()->id, Auth::user()->first_name . ' - logged in at : ' . Carbon::now());
            return $this->registered($request, $user) ?: redirect($this->redirectPath());

        } catch (\Exception $e) {
            Log::error("Error en registro: " . $e->getMessage());
            LogActivity::errorLog($e->getMessage());
        }
    }

    public function showRegistrationForm(Request $request)
    {
        $typeDocuments = TypeDocument::active()->orderBy('name')->get(['name', 'id']);
        $genders = Gender::active()->orderBy('sort_order')->get(['name', 'id']);
        $professions = Profession::active()->orderBy('name')->get(['name', 'id']);
        $leadSources = LeadSource::active()->orderBy('sort_order')->get(['name', 'id']);
        $economicActivities = EconomicActivity::active()->orderBy('code')->get();
        $maritalStatus = CivilStatus::active()->orderBy('name')->get(['name', 'id']);
        $countryPhoneCodes = CountryPhoneCode::active()->orderBy('sort_order')->get(['name', 'id']);
        $referralCode = $request->query('referral_code');

        $isReferralCodeExist = $referralCode
            ? $this->referralCodeService->validateExistenceByReferralCode($referralCode)
            : false;

        $referralCode = $isReferralCodeExist ? $referralCode : null;

        if(url()->previous() == url('/checkout') || url()->previous() == url('/checkout?checkout_type=YnV5X2l0X25vdw==')){
            session()->put('from_checkout',url()->previous());
        }

        $registerPolicy = app('general_setting')->register_policy ?? null;

        return view(theme('auth.register'), compact('countryPhoneCodes','typeDocuments','maritalStatus', 'genders', 'professions', 'leadSources', 'economicActivities', 'referralCode', 'registerPolicy'));
    }

    public function scanId(Request $request, IdentityReaderService $identityReader)
    {
        $result = $identityReader->processImage(
            frontalFile:     $request->file('cedula_frente'),
            frontalFilePath: $request->input('cedula_frente_path'),
            reversoFile:     $request->file('cedula_reverso'),
        );

        if ($result['status'] === 200) {
            return response()->json([
                'status' => 200,
                'data'   => $result['data'],
            ]);
        }

        if (($result['error_code'] ?? null) === 'BACK_ID_NOT_FOUND') {
            return response()->json([
                'status'      => 422,
                'error_code'  => 'BACK_ID_NOT_FOUND',
                'savedFrente' => $result['savedFrente'] ?? null,
                'message'     => __('auth.identity_reader_back_required'),
            ], 422);
        }

        $httpStatus = $result['status'] >= 500 ? 500 : 422;

        return response()->json([
            'status'     => $result['status'],
            'error_code' => $result['error_code'] ?? 'UNEXPECTED_ERROR',
            'message'    => __('auth.identity_reader_' . strtolower($result['error_code'] ?? 'unexpected_error')),
        ], $httpStatus);
    }

    private function dataUpdateWhenLogin($prev_session_id, $buy_it_now){
        if($buy_it_now == 'yes'){
            session()->put('but_it_now', 'yes');
        }
        $carts = Cart::where('session_id', $prev_session_id)->get();
        if ($carts->count()) {
            foreach ($carts as $key => $cartItem) {
                $cartData = Cart::where('product_id', $cartItem->product_id)->where('user_id', auth()->id())->where('seller_id', $cartItem->seller_id)->where('shipping_method_id', $cartItem->shipping_method_id)->where('product_type',$cartItem->product_type)->first();
                if ($cartData) {
                    $cartData->update([
                        'qty' => $cartItem->qty,
                        'total_price' => $cartItem->total_price,
                        'is_select' => 1
                    ]);
                    $cartItem->delete();
                } else {
                    $cartItem->update([
                        'user_id' => auth()->id(),
                        'session_id' => null
                    ]);
                }
            }
        }
    }
}






