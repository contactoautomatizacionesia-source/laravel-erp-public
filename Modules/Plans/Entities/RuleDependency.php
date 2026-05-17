<?php

namespace Modules\Plans\Entities;

use Illuminate\Database\Eloquent\Model;

class RuleDependency extends Model
{
    protected $table = 'rule_dependencies';

    protected $fillable = [
        'parent_rule_id',
        'child_rule_id',
        'operator',
        'order_index',
    ];

    protected $casts = [
        'order_index' => 'integer',
    ];

    public function parentRule()
    {
        return $this->belongsTo(Rule::class, 'parent_rule_id');
    }

    public function childRule()
    {
        return $this->belongsTo(Rule::class, 'child_rule_id');
    }
}
