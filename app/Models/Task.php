<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'is_private' => 'boolean',
        'done_at' => 'datetime',
        'not_show_before' => 'date',
        'status' => 'string',
        'priority' => 'string'
    ];

    protected $fillable = [
        'label', 'matter_id', 'priority', 'is_private', 'created_by', 'assigned_to',  'not_show_before', 'done_at', 'category'
    ];

    // RELACJE

    public function task_creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'subject')->latest();
    }


}
