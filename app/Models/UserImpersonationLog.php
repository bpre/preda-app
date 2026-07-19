<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserImpersonationLog extends Model
{
    protected $fillable = [
        'impersonator_id',
        'impersonated_user_id',
        'started_at',
        'ended_at',
        'start_url',
        'return_url',
        'stop_url',
        'ip_address',
        'user_agent',
        'handoff_token_hash',
        'handoff_expires_at',
        'handoff_consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'handoff_expires_at' => 'datetime',
            'handoff_consumed_at' => 'datetime',
        ];
    }

    public function impersonator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonator_id');
    }

    public function impersonatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'impersonated_user_id');
    }
}
