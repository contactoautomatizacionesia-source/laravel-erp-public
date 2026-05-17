<?php

namespace Modules\Otp\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpConfiguration extends Model
{
    use HasFactory;

    protected $fillable = ['key','value'];
}
