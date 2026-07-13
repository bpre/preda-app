<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MatterGeneratedDocument extends Model
{
    use HasUuids;

    public const TYPE_CONTRACT_ANALYSIS = 'contract_analysis';

    public const TYPE_CERTIFICATE_REQUEST = 'certificate_request';

    protected $fillable = [
        'matter_id',
        'credit_id',
        'type',
        'filename',
        'disk',
        'path',
        'mime_type',
        'size',
        'attach_to_client_mail',
        'generated_at',
    ];

    protected $casts = [
        'id' => 'string',
        'attach_to_client_mail' => 'boolean',
        'generated_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::deleted(function (MatterGeneratedDocument $document): void {
            if (filled($document->path)) {
                Storage::disk($document->disk ?: 'local')->delete($document->path);
            }
        });
    }

    public static function typeLabels(): array
    {
        return [
            self::TYPE_CONTRACT_ANALYSIS => 'Analiza umowy',
            self::TYPE_CERTIFICATE_REQUEST => 'Wniosek o wydanie zaświadczenia',
        ];
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }

    public function credit(): BelongsTo
    {
        return $this->belongsTo(Credit::class, 'credit_id');
    }

    public function downloadFilename(): string
    {
        $filename = trim((string) $this->filename);

        if ($filename === '') {
            $filename = self::typeLabels()[$this->type] ?? 'Dokument';
        }

        return Str::endsWith(Str::lower($filename), '.pdf')
            ? $filename
            : $filename.'.pdf';
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn (): string => self::typeLabels()[$this->type] ?? $this->type);
    }
}
