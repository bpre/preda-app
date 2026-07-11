<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CHFPotentialMatter extends Matter
{

    protected $table = 'matters';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->where('category', 'CHF')
                ->where('is_matter', 0)
                ->withAggregate('currentStage', 'sort')
                ->orderByRaw('COALESCE(current_stage_sort, -1)')
                ->orderBy('label')
                ->orderBy('id');
        });
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'matter_id');
    }

}
