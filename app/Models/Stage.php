<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $casts = [
        'files' => 'array',
        'files_names' => 'array',
        'is_current' => 'boolean',
        'date' => 'date',
    ];

    protected $fillable = ['id', 'label', 'description', 'files', 'files_names', 'parent', 'sort', 'date', 'matter_id', 'is_current', 'stage_id'];

    // RELACJE

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }

    public function templateStage()
    {
        return $this->belongsTo(TemplateStage::class, 'stage_id');
    }

}
