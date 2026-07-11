<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CHFMatter extends Matter
{

    protected $table = 'matters';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($query) {
            $query->where('category', 'CHF')
                ->withAggregate('currentStage', 'sort')
                ->orderByRaw('COALESCE(current_stage_sort, -1)')
                ->orderBy('label')
                ->orderBy('id');
        });
    }

}
