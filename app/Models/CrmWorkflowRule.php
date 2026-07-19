<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrmWorkflowRule extends Model
{
    use HasUuids;

    protected $fillable = [
        'key',
        'name',
        'trigger_stage_key',
        'suggested_action_key',
        'delay_days',
        'blocking_stage_keys',
        'reason',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'id' => 'string',
        'delay_days' => 'integer',
        'blocking_stage_keys' => 'array',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected $keyType = 'string';

    public $incrementing = false;
}
