<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CrmWorkflowSetting extends Model
{
    protected $fillable = [
        'name',
        'default_offer_disk',
        'default_offer_path',
        'default_offer_original_name',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Ustawienia workflow CRM',
                'default_offer_disk' => 'local',
            ],
        );
    }

    public function hasDefaultOffer(): bool
    {
        return filled($this->default_offer_path)
            && Storage::disk($this->default_offer_disk ?: 'local')->exists($this->default_offer_path);
    }

    /**
     * @return array{path: string, as: string, mime: string}|null
     */
    public function defaultOfferAttachment(): ?array
    {
        if (! $this->hasDefaultOffer()) {
            return null;
        }

        return [
            'path' => Storage::disk($this->default_offer_disk ?: 'local')->path($this->default_offer_path),
            'as' => $this->default_offer_original_name ?: 'Oferta.pdf',
            'mime' => 'application/pdf',
        ];
    }
}
