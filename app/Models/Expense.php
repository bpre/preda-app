<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, HasUuids;

    public const CATEGORY_GENERAL = 'ogolne';

    public const CATEGORY_RENT = 'czynsz';

    public const CATEGORY_MEDIA = 'media';

    public const CATEGORY_OFFICE = 'biuro';

    public const CATEGORY_MARKETING = 'marketing';

    public const CATEGORY_TRAVEL = 'dojazdy';

    protected $fillable = [
        'label',
        'category',
        'amount',
        'branch_id',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    public static function categoryOptions(): array
    {
        return [
            self::CATEGORY_GENERAL => 'Ogólne',
            self::CATEGORY_RENT => 'Czynsz',
            self::CATEGORY_MEDIA => 'Media',
            self::CATEGORY_OFFICE => 'Biuro',
            self::CATEGORY_MARKETING => 'Marketing',
            self::CATEGORY_TRAVEL => 'Dojazdy',
        ];
    }

    // RELACJE

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}
