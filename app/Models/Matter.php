<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matter extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'userinfo' => 'array',
        'is_matter' => 'boolean',
        'is_archived' => 'boolean',
        'is_chf' => 'boolean',
    ];
    // protected $keyType = 'string';
    // public $incrementing = false;

    protected $fillable = ['label', 'lawyer_id', 'category', 'gdrive', 'status', 'is_archived', 'userinfo',
        'is_matter', 'branch', 'branch_id', 'opponent_lawfirm_id', 'opponent_departamant_id', 'start', 'end',
        'current_template_stage_id'];

    // RELACJE

    public function scopeChfMatter(Builder $query): Builder
    {
        return $query
            ->where('is_chf', true)
            ->where('is_matter', true)
            ->where('category', 'CHF');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('end');
    }

    public function scopeForBranch(Builder $query, Branch|string $branch): Builder
    {
        return $query->where('branch_id', $branch instanceof Branch ? $branch->getKey() : $branch);
    }

    public function branchUnit(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function hasAnyRelation()
    {
        return $this->credits()->exists()
            || $this->letters()->exists()
            || $this->lawsuits()->exists()
            || $this->deals()->exists()
            || $this->payments()->exists();
        // || $this->activities()->exists();
    }

    public function matterUser(): HasMany
    {
        return $this->hasMany(MatterUser::class, 'matter_id', 'id');
    }

    public function offers()
    {
        return $this->hasMany(Offer::class, 'matter_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'matter_id');
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'matter_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'matter_id');
    }

    public function letters()
    {
        return $this->hasMany(Letter::class, 'matter_id');
    }

    public function lawsuits()
    {
        return $this->hasMany(Lawsuit::class, 'matter_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'matter_id');
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'matter_id');
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(TemplateStage::class, 'current_template_stage_id');
    }

    public function currentStageRecord()
    {
        return $this->hasOne(Stage::class, 'matter_id')->where('is_current', true);
    }

    // RELACJE - REV

    public function lawyer()
    {
        return $this->belongsTo(User::class, 'lawyer_id')->where('is_lawyer', 1);
    }

    public function opponent_lawyer()
    {
        return $this->belongsTo(Contact::class, 'opponent_lawyer_id');
    }

    public function opponent_lawfirm()
    {
        return $this->belongsTo(Contact::class, 'opponent_lawfirm_id'); // ->where('category', 'Kancelaria')->orWhere('category', 'Departament prawny');
    }

    public function opponent_departament()
    {
        return $this->belongsTo(Departament::class, 'opponent_departament_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_matter')
            ->withPivot('receives_notifications')
            ->withTimestamps();
    }

    public function notificationRecipients(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'contact_matter')
            ->withPivot('receives_notifications')
            ->withTimestamps()
            ->wherePivot('receives_notifications', true)
            ->whereNotNull('contacts.email')
            ->where('contacts.email', '!=', '');
    }

    public function contactMatters(): HasMany
    {
        return $this->hasMany(ContactMatter::class, 'matter_id', 'id');
    }

    public function notificationContactMatters(): HasMany
    {
        return $this->hasMany(ContactMatter::class, 'matter_id', 'id')
            ->where('receives_notifications', true)
            ->whereHas('contact', fn ($query) => $query
                ->whereNotNull('email')
                ->where('email', '!=', ''));
    }

    // STAŁE

    const STATUS = [
        ['label' => 'Otwarta', 'value' => 'Otwarta', 'color' => 'green'],
        ['label' => 'Zamknięta', 'value' => 'Zamknięta', 'color' => 'red'],
    ];

    const KATEGORIA = [
        ['label' => 'CHF', 'value' => 'CHF', 'color' => 'green'],
        ['label' => 'Sprawy inne', 'value' => 'Sprawy inne', 'color' => 'red'],
    ];
    // const S_STATUS = array('szansa' => 'Szansa', 'otwarta' => 'Otwarta', 'zamknięta' => 'Zamknięta');
    // const S_KATEGORIA = array('CHF' => 'CHF', 'Sprawy inne' => 'Sprawy inne');

}
