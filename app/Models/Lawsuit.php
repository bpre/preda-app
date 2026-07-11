<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lawsuit extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string'
    ];
    protected $keyType = 'string';
    public $incrementing = false;

    public $fillable = ['start_date', 'end_date', 'instance', 'signature', 'matter_id', 'court_id', 'judge_id'];

    // RELACJE - REV

    public function court()
    {
        return $this->belongsTo(Contact::class, 'court_id')->where('category', 'Sąd');
    }
    public function departament()
    {
        return $this->belongsTo(Departament::class, 'departament_id');
    }

    public function judge()
    {
        return $this->belongsTo(Contact::class, 'judge_id')->where('category', 'Sędzia');
    }

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }
}
