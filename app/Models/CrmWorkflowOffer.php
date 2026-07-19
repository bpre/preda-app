<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CrmWorkflowOffer extends Model
{
    use HasUuids;

    protected $fillable = [
        'label',
        'disk',
        'path',
        'original_name',
        'is_active',
        'sort',
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function clientMessages(): HasMany
    {
        return $this->hasMany(CrmClientMessage::class, 'crm_workflow_offer_id');
    }

    public function hasFile(): bool
    {
        return filled($this->path)
            && Storage::disk($this->disk ?: 'local')->exists($this->path);
    }

    /**
     * @return array{path: string, as: string, mime: string}|null
     */
    public function attachment(): ?array
    {
        if (! $this->hasFile()) {
            return null;
        }

        return [
            'path' => Storage::disk($this->disk ?: 'local')->path($this->path),
            'as' => $this->original_name ?: 'Oferta.pdf',
            'mime' => 'application/pdf',
        ];
    }
}
