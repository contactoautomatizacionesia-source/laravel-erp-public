<?php

namespace Modules\Customer\Repositories;
use App\Traits\ImageStore;
use App\Models\Order;
use App\Models\UserApprovalHistory;
use Modules\Customer\Entities\CustomerAddress;
use App\Models\User;
use App\Traits\Notification;
use App\Traits\SendMail;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Customer\Entities\CustomerFinancialProfile;
use Modules\Customer\Entities\CustomerProfile;
use Modules\Customer\Imports\CustomerImport;
use Modules\GeneralSetting\Entities\EmailTemplateType;
use Modules\GeneralSetting\Entities\NotificationSetting;
use Modules\GeneralSetting\Entities\UserNotificationSetting;
use Modules\Marketing\Entities\ReferralCode;
use Modules\Marketing\Entities\ReferralCodeSetup;
use Modules\Marketing\Entities\ReferralUse;
use Modules\OrderManage\Entities\CustomerNotification;

class CustomerRepository
{
    use Notification, SendMail, ImageStore;

    const ROLE_CUSTOMER = 4;

    public function getAll()
    {
        return User::with('wallet_balances', 'orders')->whereHas('role', function($query){
            return $query->where('type', 'customer');
        })->latest();
    }

    public function find($id)
    {
        return User::with('wallet_balances', 'orders', 'customerAddresses')->findOrFail($id);
    }

    public function store($data){
        $email = $data['email'];
        $user = User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'username' => $email,
            'email' => $email,
            'phone' => $data['whatsapp'],
            'role_id' => 4,
            'is_active' => $data['status'],
            'is_verified' => 1,
            'verify_code' => \Illuminate\Support\Str::random(40),
            'password' => Hash::make($data['password']),
            
            // Defaults del sistema
            'currency_id' => app('general_setting')->currency,
            'lang_code' => app('general_setting')->language_code,
            'currency_code' => app('general_setting')->currency_code,
        ]);

        // -------------------------------------------------------
        // TABLA CUSTOMER_PROFILES
        // -------------------------------------------------------
        $profile = new CustomerProfile();
        $profile->user_id = $user->id;
        
        $profile->fill($data); 
        $profile->registration_date = $data['registration_date'] ?? now();
        $profile->representative_id = $data['representative'] ?? null;

        $profile->expiration_date = $data['expiration_date'] ?? null;

        if (isset($data['contract_type_id'])) {
            $profile->contract_type_id = $data['contract_type_id'];
        }

        // 4. Imágenes (rutas generadas en el servicio)
        $profile->front_id_image = $data['front_id_image_path'] ?? null;
        $profile->back_id_image = $data['back_id_image_path'] ?? null;
        
        // 5. Contacto extra
        $profile->phone_office = $data['phone_office'] ?? null;
        $profile->secondary_email = $data['secondary_email'] ?? null;
        
        $profile->save();

        // -------------------------------------------------------
        // CUSTOMER_ADDRESSES
        // -------------------------------------------------------
        CustomerAddress::create([
            'customer_id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'phone' => $data['whatsapp'],
            'address' => $data['address'],
            'country' => $data['country'], 
            'state' => $data['state'],
            'city' => $data['city'],
            'is_shipping_default' => 1,
            'is_billing_default' => 1
        ]);

        // -------------------------------------------------------
        // CUSTOMER_FINANCIAL_PROFILES
        // -------------------------------------------------------
        $financial = new CustomerFinancialProfile();
        $financial->user_id = $user->id;
        
        $financial->fill($data);
        
        $financial->rut_file = $data['rut_file_path'] ?? null;
        $financial->bank_id = $data['bank'] ?? null;
        $financial->bank_account_type_id = $data['account_type'] ?? null;
        
        $financial->save();
        

        // Configuración de notificaciones al crear usuario
        (new UserNotificationSetting)->createForRegisterUser($user->id);
        $this->typeId = EmailTemplateType::where('type', 'register_email_template')->first()->id; //register email templete typeid
        $notification = NotificationSetting::where('slug','register')->first();
        if ($notification) {
            $this->notificationSend($notification->id, $user->id);
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

    public function update($data, $id)
    {
        $user = User::findOrFail($id);
        $oldUserData = $user->getOriginal();
        
        $user->fill($data);

        if (isset($data['whatsapp'])) {
            $user->phone = $data['whatsapp'];
        }
        if (isset($data['status'])) {
            $user->is_active = $data['status'];
        }
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        if ($user->isDirty()) {
            $user->save();
        }

        $profile = $user->customerProfile; 
        if (!$profile) $profile = new CustomerProfile(['user_id' => $user->id]);
        $profile->fill($data);

        if (isset($data['representative'])) {
            $profile->representative_id = $data['representative'];
        }

        if ($profile->isDirty()) {
            $profile->save();
        }

        if (isset($data['address'])) {
            $address = CustomerAddress::where('customer_id', $user->id)
                        ->where('is_shipping_default', 1)
                        ->first();
            
            $addressData = [
                'name'    => $user->full_name, 
                'email'   => $user->email,
                'phone'   => $user->phone, 
                'address' => $data['address'],
                'country' => $data['country'] ?? null, 
                'state'   => $data['state'] ?? null,
                'city'    => $data['city'] ?? null,
            ];

            if ($address) {
                $address->update($addressData);
            } else {
                $addressData['customer_id'] = $user->id;
                $addressData['is_shipping_default'] = 1;
                $addressData['is_billing_default'] = 1;
                CustomerAddress::create($addressData);
            }
        }

        // --- FINANCIAL ---
        $financial = $user->customerFinancialProfile;
        if (!$financial) $financial = new CustomerFinancialProfile(['user_id' => $user->id]);

        $financial->fill($data);

        // Mapeos Manuales
        if (isset($data['bank'])) $financial->bank_id = $data['bank'];
        if (isset($data['account_type'])) $financial->bank_account_type_id = $data['account_type'];
        
        if ($financial->isDirty()) {
            $financial->save();
        }

        // --- LOGS---
        $changes = [];
        foreach ($user->getChanges() as $field => $newValue) {
            if (in_array($field, ['updated_at', 'password'])) continue;
            $oldValue = $oldUserData[$field] ?? 'N/A'; 
            $fieldName = __("common.{$field}");
            $changes[] = "{$fieldName}: '{$oldValue}' " . __("common.to") . " '{$newValue}'";
        }

        return [
            'user' => $user,
            'changes' => $changes
        ];
    }

    public function destroy($id){
        $customer = User::findOrFail($id); // Usamos findOrFail para asegurar que existe
        
        // Verificación de Órdenes
        $hasOrders = Order::where('customer_id', $id)->exists();
        if ($hasOrders) {
            return [
                'success' => false,
                'code' => 'HAS_ORDERS',
                'data' => ['name' => $customer->full_name, 'email' => $customer->email]
            ];
        }
        // Verificación de Billetera con Saldo real            
        if ($customer->CustomerCurrentWalletAmounts > 0) {
            return [
                'success' => false,
                'code' => 'HAS_WALLET_BALANCE',
                'data' => ['name' => $customer->full_name, 'email' => $customer->email]
            ];
        }
        // Si pasa las validaciones, procedemos con la eliminación pasiva
        $customer->is_active = 0;
        $customer->save();

        // Eliminación de relacionados
        CustomerAddress::where('customer_id', $id)->delete();
        CustomerNotification::where('customer_id', $id)->delete();
        UserNotificationSetting::where('user_id', $id)->delete();

        $customer->delete();
        return ['success' => true, 'code' => 'SUCCESS', 'data' => $customer];
    }
    public function imageDelete($data){
        $customer = User::find(auth()->user()->id);
        if (showImage($customer->avatar ) == $data['image']) {
            $this->deleteImage($customer->avatar);
        }
        $customer->update([
            'avatar' => ''
        ]);
        return true;
    }
    public function BulkUploadStore($data){
        Excel::import(new CustomerImport, $data['file']->store('temp'));
    }

    public function posCustomer()
    {
        return User::with('customerAddresses')->whereHas('role', function($query){
            return $query->where(['type' => 'customer', 'is_active' => 1]);
        })->orderBy('id','DESC')->get();
    }

    public function getCustomersByAjax($search){
        if($search == ''){
            $customer = User::select('id','first_name', 'last_name')->whereHas('role', function($query){
                return $query->where(['type' => 'customer', 'is_active' => 1]);
            })->orderBy('id', 'DESC')->paginate(10);
        }else{
            $customer = User::select('id','first_name', 'last_name')
                ->where('first_name', 'LIKE', "%{$search}%")
                ->orWhere('last_name', 'LIKE', "%{$search}%")->whereHas('role', function($query){
                    return $query->where(['type' => 'customer', 'is_active' => 1]);
                })->orderBy('id', 'DESC')
                ->paginate(10);
        }
        $response = [];
        foreach($customer as $customers){
            $response[]  =[
                'id'    =>'customer-'.$customers->id,
                'text'  =>$customers->first_name.' '.$customers->last_name
            ];
        }
        return  $response;
    }

    // Recuperar solo usuarios eliminados pasivamente
    public function getTrashedOnly() {
        return User::onlyTrashed()
        ->with('wallet_balances', 'orders')
        ->whereHas('role', function($query){
            return $query->where('type', 'customer');
        })->get();
    }

    // Restaurar un usuario específico
    public function restore($id) {
        $user = User::withTrashed()->findOrFail($id);
        // Usamos withTrashed() por si algunas ya estaban borradas antes del borrado masivo
        CustomerAddress::withTrashed()->where('customer_id', $id)->restore();
        $user->is_active = 1; // Lo reactivamos automáticamente al restaurar
        $user->save();
        $user->restore(); // Elimina la marca de deleted_at

        return $user;
    }

    public function searchActiveCustomers($search = null)
    {
        $query = User::query();

        $query->whereHas('role', function($q){
            $q->where('type', 'customer');
        })->where('is_active', 1);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $customers = $query->select('id', 'first_name', 'last_name', 'email')
            ->with('referralCode:user_id,referral_code,status')
            ->limit(20)
            ->get();

        return $customers->map(function($user) {
            $referralCode = $user->referralCode;
            return [
                'id'   => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'code' => ($referralCode && $referralCode->status == 1) ? $referralCode->referral_code : '',
            ];
        });
    }

    public function createCompleteCustomer(array $data, ?int $changedBy = null)
    {
        $username = $data['username'] ?? $data['whatsapp'];
        $approvalStatus = $data['approval_status'] ?? User::APPROVAL_STATUS_APPROVED;
        
        $user = User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $username . time(),
            'phone' => $data['whatsapp'],
            'password' => Hash::make($data['password']),
            'role_id' => self::ROLE_CUSTOMER,
            'is_active' => $data['is_active'] ?? 1, 
            'approval_status' => $approvalStatus,
            'verify_code' => \Illuminate\Support\Str::random(40),
            // Defaults del sistema
            'lang_code' => app('general_setting')->language_code ?? 'es',
            'currency_id' => app('general_setting')->currency ?? 1,
            'currency_code' => app('general_setting')->currency_code ?? 'USD',
        ]);

        $this->storeApprovalHistory(
            $user->id,
            null,
            $approvalStatus,
            $data['approval_reason'] ?? null,
            $changedBy
        );

        $profile = new CustomerProfile();
        $profile->user_id = $user->id;
        $profile->fill([
            // Paso 1
            'document_type_id' => $data['document_type_id'],
            'document_number' => $data['document_number'],
            'date_of_birth' => $data['date_of_birth'],
            'birth_city_id' => $data['birth_city_id'],
            'issue_date' => $data['issue_date'],
            'issue_city_id' => $data['issue_city_id'],
            'expiration_date' => $data['expiration_date'] ?? null,
            'secondary_email' => $data['secondary_email'] ?? null,
            
            // Paso 2
            'whatsapp' => $data['whatsapp'],
            'phone_calls' => $data['phone_calls'] ?? null,
            'phone_office' => $data['phone_office'] ?? null,
            'civil_status_id' => $data['civil_status_id'],
            'economic_activity_id' => $data['economic_activity_id'],
            'profession_id' => $data['profession_id'],
            'product_id' => $data['product_id'],
            'lead_source_id' => $data['lead_source_id'],
            'gender_id' => $data['gender_id'] ?? null,

            // Paso 3 (Rutas de imágenes)
            'front_id_image' => $data['front_id_image_path'] ?? null,
            'back_id_image' => $data['back_id_image_path'] ?? null,
            
            'registration_date' => now(),
            'representative_id' => $data['representative_id'] ?? null,
        ]);
        $profile->save();

        CustomerAddress::create([
            'customer_id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'phone' => $data['whatsapp'],
            'address' => $data['address'],
            
            'country' => $data['country'], 
            'state' => $data['state'],
            'city' => $data['city'],
            
            'postal_code' => $data['postal_code'] ?? null,
            'is_shipping_default' => 1,
            'is_billing_default' => 1
        ]);

        return $user;
    }

    public function updateApprovalStatus($id, $toStatus, $reason = null, $changedBy = null)
    {
        $user = User::whereHas('role', function ($query) {
            $query->where('type', 'customer');
        })->findOrFail($id);

        $fromStatus = $user->approval_status ?? User::APPROVAL_STATUS_APPROVED;
        $user->approval_status = $toStatus;
        $user->is_active = $toStatus === User::APPROVAL_STATUS_APPROVED ? 1 : 0;
        $user->save();

        $this->storeApprovalHistory(
            $user->id,
            $fromStatus,
            $toStatus,
            $reason,
            $changedBy
        );

        return $user;
    }

    private function storeApprovalHistory($userId, $fromStatus, $toStatus, $reason = null, $changedBy = null)
    {
        UserApprovalHistory::create([
            'user_id' => $userId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'changed_by' => $changedBy,
        ]);
    }

    public function updateCustomerFilePaths($user, array $paths)
    {
        // 1. Perfil General (Imágenes de ID)
        $profileUpdates = [];
        if (isset($paths['front'])) $profileUpdates['front_id_image'] = $paths['front'];
        if (isset($paths['back']))  $profileUpdates['back_id_image'] = $paths['back'];

        if (!empty($profileUpdates)) {
            $user->customerProfile()->update($profileUpdates);
        }

        // 2. Perfil Financiero (RUT)
        $financialUpdates = [];
        if (isset($paths['rut'])) $financialUpdates['rut_file'] = $paths['rut'];

        if (!empty($financialUpdates)) {
            $user->customerFinancialProfile()->update($financialUpdates);
        }
    }
}
