<?php

namespace App\Models;

use App\Models\ContactCredit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Credit extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'details' => 'array'
    ];

    protected $appends = ['label'];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['number', 'date', 'former_bank', 'current_bank', 'details'];

    // RELACJE

    public function hasAnyRelation()
    {
        return $this->credit_deals()->exists();
    }

    // kontakty przypisane do umowy kredytowej
    public function contactCredit(): HasMany
    {
        return $this->hasMany(ContactCredit::class)->with('contact');
    }

    // RELACJE - REV

    public function credit_contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class);
    }

    public function credit_deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class);
    }

    public function former_banks()
    {
        return $this->belongsTo(Contact::class, 'former_bank')->where('category', 'Bank');
    }
    public function current_banks()
    {
        return $this->belongsTo(Contact::class, 'current_bank')->where('category', 'Bank');
    }
    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }



    // ATRYBUTY

    public function label(): Attribute
    {
        return new Attribute(
            get: function( $originalValue ){
                return $this->number.' ('.$this->date.')';
          });
    }



    // ACCESSORY

    public function getCreditCurrencyAttribute(): ?string
    {
        $details = $this->details ?? [];

        $params = collect($details)->firstWhere('type', 'Parametry umowy');
        $kwota = data_get($params, 'data.waluta');

        return is_string($kwota) && trim($kwota) !== '' ? $kwota : null;
    }

    public function getCreditAmountRawAttribute(): ?string
    {
        $details = $this->details ?? [];

        $params = collect($details)->firstWhere('type', 'Parametry umowy');
        $kwota = data_get($params, 'data.kwota');

        return is_string($kwota) && trim($kwota) !== '' ? $kwota : null;
    }

    public function getCreditAmountCurrencyAttribute(): ?string
    {
        $raw = trim((string) $this->credit_amount_raw);

        if ($raw === '') {
            return null;
        }

        // PLN bywa zapisywane jako: "zł", "zl", "zł.", "PLN"
        if (preg_match('/(?:^|[\s,.;])z[łl]\.?(?:$|[\s,.;])/iu', $raw)) {
            return 'PLN';
        }

        // Najczęściej na końcu: "18.713,85 CHF"
        if (preg_match('/\b([A-Z]{3})\b/u', $raw, $m)) {
            return $m[1];
        }

        return null;
    }

    public function getCreditAmountValueAttribute(): ?float
    {
        $raw = $this->credit_amount_raw;

        if (! $raw) {
            return null;
        }

        // usuń walutę i inne znaki poza cyframi + separatorami
        $s = str_replace("\u{00A0}", ' ', $raw); // NBSP -> spacja
        $s = preg_replace('/[^\d,.\- ]/u', '', $s);
        $s = preg_replace('/\s+/', '', $s); // wywal spacje

        if ($s === '' || $s === '-') {
            return null;
        }

        // PL format: kropki = tysiące, przecinek = dziesiętne
        // 18.713,85 -> 18713.85
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);

        return is_numeric($s) ? (float) $s : null;
    }

}
