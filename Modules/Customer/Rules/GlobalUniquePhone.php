<?php

namespace Modules\Customer\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User; 
use Modules\Customer\Entities\CustomerProfile;

class GlobalUniquePhone implements Rule
{
    protected $ignoreUserId;

    public function __construct($ignoreUserId = null)
    {
        $this->ignoreUserId = $ignoreUserId;
    }

    public function passes($attribute, $value)
    {
        // 1. Verificar en tabla USERS (Columna phone)
        $existsInUsers = User::where('phone', $value)
            ->when($this->ignoreUserId, function($q) {
                return $q->where('id', '!=', $this->ignoreUserId);
            })->exists();

        if ($existsInUsers) return false;

        // 2. Verificar en tabla CUSTOMER_PROFILES (whatsapp, phone_calls, phone_office)
        $existsInProfiles = CustomerProfile::where(function ($query) use ($value) {
                $query->where('whatsapp', $value)
                      ->orWhere('phone_calls', $value)
                      ->orWhere('phone_office', $value);
            })
            ->when($this->ignoreUserId, function($q) {
                return $q->where('user_id', '!=', $this->ignoreUserId);
            })->exists();

        return !$existsInProfiles;
    }

    public function message()
    {
        return 'This phone number is already in use by another customer.';
    }
}