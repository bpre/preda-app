<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['date', 'description', 'is_visible_for_client', 'client_visible_from', 'matter_id', 'created_by'];

}
