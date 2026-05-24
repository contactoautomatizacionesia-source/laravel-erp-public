<?php

namespace Modules\Setup\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
class State extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts  = [
        "id" => "integer",
        "country_id" => "integer",
        "name" => "string",
        "status" => "integer",

    ];
    public $translatable = ['name'];
    public function country(){
        return $this->belongsTo(Country::class,'country_id','id');
    }

    public function cities(){
        return $this->hasMany(City::class,'state_id','id')->orderBy('name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
