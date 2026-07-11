<?php

namespace App\Models\Website;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $table = 'website_posts';

    protected $casts = [
        'is_published' => 'boolean',
        'alternative_slugs' => 'array',
    ];

    protected $fillable = [
        'title',
        'date',
        'slug',
        'content',
        'excerpt',
        'metatitle',
        'metadescription',
        'reviewed_at',
        'modified_at',
        'is_published',
        'author_id',
        'category',
        'alternative_slugs'
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
