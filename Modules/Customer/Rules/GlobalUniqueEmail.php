<?php

namespace Modules\Customer\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;
use Modules\Customer\Entities\CustomerProfile;

class GlobalUniqueEmail implements Rule
{
    protected $ignoreUserId;

    public function __construct($ignoreUserId = null)
    {
        $this->ignoreUserId = $ignoreUserId;
    }

    public function passes($attribute, $value)
    {
        // 1. Verificar si existe como email PRINCIPAL en Users
        $existsInUsers = User::where('email', $value)
            ->when($this->ignoreUserId, function($q) {
                return $q->where('id', '!=', $this->ignoreUserId);
            })->exists();

        if ($existsInUsers) return false;

        // 2. Verificar si existe como email SECUNDARIO en CustomerProfile
        $existsInProfiles = CustomerProfile::where('secondary_email', $value)
            ->when($this->ignoreUserId, function($q) {
                return $q->where('user_id', '!=', $this->ignoreUserId);
            })->exists();

        return !$existsInProfiles;
    }

    public function message()
    {
        return 'This email is already registered in the system (either as primary or secondary).';
    }
}