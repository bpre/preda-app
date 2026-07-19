<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateStage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'template_stages';

    protected $fillable = [
        'id',
        'key',
        'preferred_action_key',
        'category',
        'label',
        'parent',
        'sort',
        'parent_sort',
        'is_lead_default',
        'is_chf_default',
        'is_active',
    ];

    protected $casts = [
        'is_lead_default' => 'boolean',
        'is_chf_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public $timestamps = false;

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'stage_id');
    }
}
