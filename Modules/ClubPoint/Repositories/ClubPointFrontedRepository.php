<?php

namespace Modules\ClubPoint\Repositories;
use Carbon\Carbon;

use App\Models\OrderPackageDetail;
use Modules\ClubPoint\Entities\ClubPointWallet;

class ClubPointFrontedRepository
{
    public function getAll(){
    
        return ClubPointWallet::find(1);
    }
}