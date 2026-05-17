<?php

namespace Modules\ClubPoint\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClubPointWallet extends Model
{
    use HasFactory;
    protected $table = 'club_point_wallets';
    protected $fillable = ['wallet_point'];
}
