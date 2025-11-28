<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contactus extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'contact_us';
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
    ];
}
