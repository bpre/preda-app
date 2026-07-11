<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departament extends Model
{
    use HasFactory, HasUuids;
    protected $casts = ['id' => 'string'];
    // protected $keyType = 'string';

    // public $incrementing = false;
    protected $fillable = ['label', 'email', 'phone', 'address', 'zip_code', 'city', 'contact_id'];

    // RELACJE - REV

    public function contact() {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function matter() {
        return $this->belongsTo(Matter::class, 'opponent_departament_id');
    }

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
