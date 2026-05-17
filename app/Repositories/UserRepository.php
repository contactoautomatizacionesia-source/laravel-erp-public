<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Staff;
use App\Models\StaffDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Modules\Affiliate\Repositories\AffiliateRepository;
use Modules\GeneralSetting\Entities\BusinessSetting;
use App\Traits\ImageStore;
use App\Traits\Notification;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\OrderManage\Entities\CustomerNotification;
use Modules\SidebarManager\Entities\Backendmenu;
use Modules\SidebarManager\Entities\BackendmenuUser;
use App\Traits\SendMail;
use Modules\DigitalFolder\Repositories\DigitalFolderRepository;

class UserRepository implements  UserRepositoryInterface
{
    use ImageStore,Notification,SendMail;

    protected $digitalFolderRepository;

    public function __construct(DigitalFolderRepository $digitalFolderRepository)
    {
        $this->digitalFolderRepository = $digitalFolderRepository;
    }

    public function user()
    {
        return User::with('leaves','leaveDefines')->latest()->get();
    }

    public function all($relational_keyword = [])
    {
        if (count($relational_keyword) > 0) {
            return Staff::with(array_merge($relational_keyword, ['user.costCenter']))->whereHas('user', function($query){
                $query->where('id', '>', 1)->whereHas('role', function($q){
                    $q->where('type', 'admin')->orWhere('type', 'staff');
                });
            })->latest()->get();
        }else {
            return Staff::latest()->get();
        }

    }

    public function create(array $data)
    {
        $data['currency_id'] = app('general_setting')->currency;
        $data['lang_code'] = app('general_setting')->language_code;
        $data['currency_code'] = app('general_setting')->currency_code;
        $user = User::create($data);

        //affiliate user
        if(isModuleActive('Affiliate')){
            $affiliateRepo = new AffiliateRepository();
            $affiliateRepo->affiliateUser($user->id);
        }

        // User Notification Setting Create
        (new UserNotificationSetting())->createForRegisterUser($user->id);
        $this->typeId = EmailTemplateType::where('type','register_email_template')->first()->id;//register email templete typeid
        $notification = NotificationSetting::where('slug','register')->first();
        if ($notification) {
            $this->notificationSend($notification->id,$user->id);
        }
        if(BusinessSetting::where('type', 'email_verification')->first()->status != 1){
            $user->email_verified_at = date('Y-m-d H:m:s');
            $user->save();
        }
        else {
            $user->sendEmailVerificationNotification();
        }
        $staff = new Staff;
        $staff->user_id = $user->id;
        $staff->save();
        return $staff;
    }

    public function store(array $data)
    {
        $role = explode('-', $data['role_id']);
        $user = new User;
        $user->first_name = $data['first_name'];
        $user->last_name = isset($data['last_name'])?$data['last_name']:null;
        $user->email = $data['email'];
        $user->username = $data['phone'];
        $user->role_id = $role[0];
        if (isset($data['photo'])) {
            $data = Arr::add($data, 'avatar', $this->saveAvatar($data['photo'],165,165));
            $user->avatar = $data['avatar'];
        }
        $user->password = Hash::make($data['password']);
        $user->cost_center_id = $data['cost_center_id'] ?? null;
        if($user->save()){
            $staff = new Staff;
            $staff->user_id = $user->id;
            $staff->department_id = $data['department_id'];
            $staff->phone = $data['phone'];

            $staff->bank_name = $data['bank_name'];
            $staff->bank_branch_name = $data['bank_branch_name'];
            $staff->bank_account_name = $data['bank_account_name'];
            $staff->bank_account_no = $data['bank_account_number'];
            $staff->date_of_joining = Carbon::parse($data['date_of_joining'])->format('Y-m-d');
            $staff->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
            $staff->leave_applicable_date = Carbon::parse($data['leave_applicable_date'])->format('Y-m-d');
            $staff->address = $data['address'];
            $staff->type_document_id = $data['document_type'] ?? null;
            $staff->document_number = $data['document_number'] ?? null;
            $staff->cost_center = $data['cost_center'] ?? null;
            if($staff->save()){
                if(BusinessSetting::where('type', 'email_verification')->first()->status != 1){
                    $user->email_verified_at = date('Y-m-d H:m:s');
                    $user->save();
                }
                else {
                    // $user->sendEmailVerificationNotification();
                }
            }

            //affiliate user
            if(isModuleActive('Affiliate')){
                $affiliateRepo = new AffiliateRepository();
                $affiliateRepo->affiliateUser($user->id);
            }
            // User Notification Setting Create
            (new UserNotificationSetting())->createForRegisterUser($user->id);

            // Crear carpeta personal del staff: Master → Personal → {Usuario}
            $this->digitalFolderRepository->ensureStaffUserFolder($user);

            return $user;
        }
    }

    public function find($id)
    {
        return Staff::with('user', 'typeDocument')->findOrFail($id);
    }

    public function findUser($id)
    {
        return User::findOrFail($id);
    }

    public function findDocument($id)
    {
        return StaffDocument::where('staff_id', $id)->get();
    }

    public function update(array $data, $id)
    {
        $role = explode('-', $data['role_id']);
        $user = User::findOrFail($id);
        $staff = $user->staff;

        // Captura de estados originales para comparación
        $oldUserData = $user->getOriginal();
        $oldStaffData = $staff->getOriginal();

        if (isset($data['photo'])) {
            $this->deleteImage($user->avatar);
            $data = Arr::add($data, 'avatar', $this->saveAvatar($data['photo'],165,165));
            $user->avatar = $data['avatar'];
        }

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->username = $data['phone'];
        $user->role_id = $role[0];
        $user->password = isset($data['password'])?Hash::make($data['password']):$user->password;
        $user->cost_center_id = $data['cost_center_id'] ?? null;
        $result = $user->save();
        if($result){
            $staff->user_id = $user->id;
            $staff->department_id = $data['department_id'];

            $staff->bank_name = $data['bank_name'];
            $staff->bank_branch_name = $data['bank_branch_name'];
            $staff->bank_account_name = $data['bank_account_name'];
            $staff->bank_account_no = $data['bank_account_number'];
            $staff->date_of_joining = Carbon::parse($data['date_of_joining'])->format('Y-m-d');
            $staff->leave_applicable_date = Carbon::parse($data['leave_applicable_date'])->format('Y-m-d');
            $staff->date_of_birth = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
            $staff->address = $data['address'];

            $staff->phone = $data['phone'];
            $staff->type_document_id = $data['document_type'] ?? null;
            $staff->document_number = $data['document_number'] ?? null;
            $staff->cost_center = $data['cost_center'] ?? null;

            $staff->save();
        }

        // Procesar cambios detallados para la bitácora
        $allChanges = [];

        // Cambios en User
        foreach ($user->getChanges() as $key => $value) {
            if (!in_array($key, ['updated_at', 'password', 'avatar'])) {
                $allChanges[] = __("common.{$key}") . ": '" . ($oldUserData[$key] ?? 'N/A') . "' " . __("common.to") . " '{$value}'";
            }
        }
        
        // Cambios en Staff
        foreach ($staff->getChanges() as $key => $value) {
            if (!in_array($key, ['updated_at'])) {
                $allChanges[] = __("common.{$key}") . ": '" . ($oldStaffData[$key] ?? 'N/A') . "' " . __("common.to") . " '{$value}'";
            }
        }

        return ['user' => $user, 'changes' => $allChanges];
    }

    public function updateProfile(array $data, $id)
    {
        $user = User::findOrFail($id);
        if (isset($data['avatar'])) {
            $user->avatar = $this->saveAvatar($data['avatar'],60,60);
        }
        $user->name = $data['name'];
        if (array_key_exists('password',$data))
            $user->password = Hash::make($data['password']);
        $result = $user->save();
        $staff = $user->staff;
        if($result){
            $staff->phone = $data['phone'];
            if ($user->role_id != 1) {
                $staff->bank_name = $data['bank_name'];
                $staff->bank_branch_name = $data['bank_branch_name'];
                $staff->bank_account_name = $data['bank_account_name'];
                $staff->bank_account_no = $data['bank_account_no'];
                $staff->address = $data['address'];
            }

            $staff->save();
        }
        return $staff;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        $user->is_active = 0; 
        $user->save();

        // AVATAR: Suspendemos la eliminación física para permitir restauración completa
        /* if (File::exists(public_path($user->avatar))) {
             File::delete(public_path($user->avatar));
        } */
        // DOCUMENTOS: Aplicamos Soft Delete sin borrar archivos del disco
        if(count($user->staff->documents) > 0){
            foreach($user->staff->documents as $doc){
                // IMPORTANTE: No usamos File::delete() para poder restaurar luego
                /* if (File::exists(public_path($doc->documents))) {
                     File::delete(public_path($doc->documents));
                } */
                $doc->delete(); // Esto ahora solo pondrá la fecha en deleted_at
            }
        }
        $notification_setting_ids = UserNotificationSetting::where('user_id', $user->id)->pluck('id')->toArray();
        $notification_ids = CustomerNotification::where('customer_id', $user->id)->pluck('id')->toArray();
        UserNotificationSetting::destroy($notification_setting_ids);
        CustomerNotification::destroy($notification_ids);
        $backend_muneuuser_ids = BackendmenuUser::where('user_id', $user->id)->pluck('id')->toArray();
        $backend_menu_ids = Backendmenu::where('user_id', $user->id)->pluck('id')->toArray();
        BackendmenuUser::destroy($backend_muneuuser_ids);
        Backendmenu::destroy($backend_menu_ids);
        $user->staff->delete();
        $user->delete();

        return $user;
    }

    public function statusUpdate($data)
    {
        $user = User::find($data['id']);
        $user->is_active = $data['status'];

        // Si el estado es 0 (Inactivo), guardamos la causal enviada
        if ($data['status'] == 0) {
            $user->inactive_reason = $data['causal'] ?? null;
        } else {
            // Si se reactiva, es buena práctica limpiar el motivo anterior
            $user->inactive_reason = null;
        }
        $user->save();

        if($data['status'] == 1 && manualActivation())        {
            $this->userActivationMailSend('user_activation_template',$user);
        }
        return true;
    }

    public function deleteStaffDoc($id)
    {
        $document = StaffDocument::findOrFail($id);
        if (File::exists(public_path($document->documents))) {
            File::delete(public_path($document->documents));
        }
        $document->delete();
        return true;
    }

    public function normalUser()
    {
        return User::where('id',Auth::id())->orwhere('role_id',3)->get();
    }

    public function staffImgDelete($id){
        $user = User::where('id',$id)->firstOrFail();
        ImageStore::deleteImage($user->avatar);
        $user->avatar = null;
        $user->save();
        return 1;

    }

    public function getTrashedOnly()
    {
        return Staff::onlyTrashed()
            ->with(['user' => function($query) {
                $query->withTrashed();
            }])
            ->get();
    }

    public function restore($id)
    {
        // Buscamos el staff en la papelera
        $staff = Staff::onlyTrashed()->findOrFail($id);
        // Buscamos el usuario asociado en la papelera
        $user = User::onlyTrashed()->findOrFail($staff->user_id);

        // Restaurar documentos asociados si existen
        StaffDocument::withTrashed()->where('staff_id', $staff->id)->restore();

        // Restaurar ambos modelos
        $staff->restore();
        $user->restore();

        return $user; // Retornamos el usuario para la bitácora del controlador
    }
}
