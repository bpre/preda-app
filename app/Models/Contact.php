<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory, HasUuids;

    protected $casts = ['id' => 'string'];
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['category', 'type', 'first_name', 'last_name', 'label', 'sort_name', 'organization', 'organization_short', 'sex', 'email', 'phone', 'address', 'zip_code', 'city', 'krs', 'profession', 'lawfirm_id', 'pesel'];

    // RELACJE

    public function hasAnyRelation()
    {
        return $this->former_bank_credits()->exists()
            || $this->current_bank_credits()->exists()
            || $this->court_lawsuits()->exists()
            || $this->judge_lawsuits()->exists()
            || $this->opponent_lawyer_matters()->exists()
            || $this->contact_credits()->exists()
            || $this->contact_deals()->exists()
            || $this->recipient_letters()->exists()
            || $this->sender_letters()->exists()
            || $this->contact_lawfirm()->exists()
            || $this->lawfirm_contacts()->exists();
    }

    public function departaments()
    {
        return $this->hasMany(Departament::class, 'contact_id');
    }

    public function former_bank_credits()
    {
        return $this->hasMany(Credit::class, 'former_bank');
    }

    public function current_bank_credits()
    {
        return $this->hasMany(Credit::class, 'current_bank');
    }

    public function court_lawsuits()
    {
        return $this->hasMany(Lawsuit::class, 'court_id');
    }

    public function judge_lawsuits()
    {
        return $this->hasMany(Lawsuit::class, 'judge_id');
    }

    public function opponent_lawyer_matters()
    {
        return $this->hasMany(Matter::class, 'opponent_lawyer_id');
    }

    public function opponent_lawfirm_matters()
    {
        return $this->hasMany(Matter::class, 'opponent_lawfirm_id');
    }

    public function sender_letters() {
        return $this->hasMany(Letter::class, 'sender_id');
    }

    public function lawfirm_contacts() {
        return $this->hasMany(Contact::class, 'lawfirm_id');
    }

    // RELACJE - REV

    // umowy kredytowe kontaktu
    public function contact_credits() {
        return $this->belongsToMany(Credit::class)->withPivot('credit_id');
    }

    public function contact_deals() {
        return $this->belongsToMany(Deal::class)->withPivot('deal_id');
    }

    public function recipient_letters() {
        return $this->belongsToMany(Letter::class)->withPivot('letter_id');
    }

    public function opponent_matters() {
        return $this->belongsToMany(Matter::class)->withPivot('opponent_lawfirm_id');
    }

    public function contact_lawfirm() {
        return $this->belongsTo(Contact::class, 'lawfirm_id')->whereIn('category', array('Kancelaria', 'Departament prawny'));
    }

    public function contact_departament() {
        return $this->belongsTo(Departament::class, 'departament_id');
    }

    public function matters(): BelongsToMany
    {
        return $this->belongsToMany(Matter::class, 'contact_matter')
            ->withPivot('receives_notifications')
            ->withTimestamps();
    }
    public function letterNotifications(): HasMany
    {
        return $this->hasMany(LetterNotification::class, 'contact_id', 'id');
    }

    // STAŁE

    const CATEGORIES = array(
        array('label' => 'Bank', 'value' => 'Bank'),
        array('label' => 'Biegły', 'value' => 'Biegły'),
        array('label' => 'Departament prawny', 'value' => 'Departament prawny'),
        array('label' => 'Kancelaria', 'value' => 'Kancelaria'),
        array('label' => 'Komornik', 'value' => 'Komornik'),
        array('label' => 'Kredytobiorca', 'value' => 'Kredytobiorca'),
        array('label' => 'Pełnomocnik', 'value' => 'Pełnomocnik'),
        array('label' => 'Sąd', 'value' => 'Sąd'),
        array('label' => 'Sędzia', 'value' => 'Sędzia'),
        array('label' => 'Świadek', 'value' => 'Świadek'),
        array('label' => 'Inny (osoba)', 'value' => 'Inny'),
        array('label' => 'Inny (organizacja)', 'value' => 'Inna'),
    );
    const S_CATEGORIES = array(
        'Bank' => 'Bank',
        'Biegły' => 'Biegły',
        'Departament prawny' => 'Departament prawny',
        'Kancelaria' => 'Kancelaria',
        'Komornik' => 'Komornik',
        'Kredytobiorca' => 'Kredytobiorca',
        'Pełnomocnik' => 'Pełnomocnik',
        'Sąd' => 'Sąd',
        'Sędzia' => 'Sędzia',
        'Świadek' => 'Świadek',
        'Inny (osoba)' => 'Inny',
        'Inny (organizacja)' => 'Inna',
    );
    const ORGANIZATIONS = array('Bank', 'Departament prawny', 'Kancelaria', 'Sąd', 'Inny (organizacja)');

    const SEX = array(
        'K' => 'Kobieta',
        'M' => 'Mężczyzna'
    );
    const TYTUL_ZAWODOWY = array(
        'Adwokat' => 'Adwokat',
        'Radca prawny' => 'Radca prawny'
    );

    public function adr(): Attribute
    {
        return new Attribute(
            get: function( $originalValue ){
                if($this->address && $this->city)
                {
                    return $this->address . ', ' .$this->zip_code. ' ' .$this->city;
                }
                else
                {
                    return null;
                }
          });
    }

}
