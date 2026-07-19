<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stage extends Model
{
    use HasFactory;

    protected $casts = [
        'files' => 'array',
        'files_names' => 'array',
        'is_current' => 'boolean',
        'date' => 'date',
        'current_stage_set_at' => 'datetime',
        'last_edited_at' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'label',
        'description',
        'files',
        'files_names',
        'parent',
        'sort',
        'date',
        'matter_id',
        'is_current',
        'stage_id',
        'current_stage_set_by',
        'current_stage_set_at',
        'last_edited_by',
        'last_edited_at',
    ];

    // RELACJE

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }

    public function templateStage()
    {
        return $this->belongsTo(TemplateStage::class, 'stage_id');
    }

    public function currentStageSetter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_stage_set_by');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

}
