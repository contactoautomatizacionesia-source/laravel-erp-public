<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status'
    ];

    public function staffs()
    {
        return $this->hasMany(Staff::class, 'type_document_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
