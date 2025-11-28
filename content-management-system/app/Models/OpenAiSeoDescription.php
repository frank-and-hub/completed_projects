<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpenAiSeoDescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'open_ai_seo_descriptions';

    protected $fillable = [
        'descriptions',
        'city',
        'state',
        'country',
        'feature_slug',
        'propmt',
    ];
}
