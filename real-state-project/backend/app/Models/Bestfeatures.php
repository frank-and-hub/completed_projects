<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bestfeatures extends Model
{
    use HasFactory,HasUuids;
    protected $table = 'best_features';
    protected $fillable = ['heading','description','image','status'];
}
