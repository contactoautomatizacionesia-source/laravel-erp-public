<?php

namespace App\Repositories;

use App\Models\Staff;

class StaffRepository
{
    public function findByUserId(int $userId)
    {
        return Staff::where('user_id', $userId)->first();
    }
}
