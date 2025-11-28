<?php

namespace App\Models\CountryData;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id', 'name', 'status'
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }
}
