<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateStage extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'template_stages';

    protected $fillable = ['id', 'category', 'label', 'parent', 'sort', 'parent_sort', 'is_lead_default', 'is_chf_default', 'is_active'];

    protected $casts = [
        'is_lead_default' => 'boolean',
        'is_chf_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public $timestamps = false;
}
