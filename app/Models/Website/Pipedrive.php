<?php

namespace App\Models\Website;

use App\Enums\Website\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pipedrive extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'pipedrives';
    protected $casts = [
        'id' => 'string',
        'reviewed' => 'datetime',
        'review_status' => ReviewStatus::class,
    ];
    protected $fillable = [ 'matter_id', 'gdrive', 'name', 'email', 'sex', 'phone', 'city',
    'bank', 'year', 'amount', 'currency', 'stage', 'first', 'reviewed', 'review_status', 'remove_request', 'offer_request', 'banku'];


    // public function matter()
    // {
    //     return $this->belongsTo(Matter::class, 'matter_id');
    // }

}

