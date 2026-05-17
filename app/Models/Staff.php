<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Modules\Setup\Entities\Department;
use Modules\Setup\Entities\IntroPrefix;

class Staff extends Model
{
    use HasFactory, SoftDeletes; // Activa la eliminación pasiva
    protected $table = 'staff';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withDefault();
    }
    public function documents(){
        return $this->hasMany(StaffDocument::class, 'staff_id', 'id');
    }

    public function typeDocument()
    {
        return $this->belongsTo(TypeDocument::class, 'type_document_id');
    }

    public static function boot()
    {
        $into_prefix = IntroPrefix::find(8);

        parent::boot();
        static::created(function ($model) use($into_prefix) {
            $id = sprintf('%05d', $model->id);
            $model->employee_id =  $into_prefix ? $into_prefix->prefix.'-3'.$id : 'EMP-'.$id;
            $model->save();
        });
    }
}
