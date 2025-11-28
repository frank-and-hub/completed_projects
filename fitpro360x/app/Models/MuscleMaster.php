<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MuscleMaster extends Model
{
    protected $table;
    use HasFactory, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.muscle_trained');

    }

    protected $fillable = [
        'name',
        'image',
    ];

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, config('tables.exercise_muscle_trained'), 'muscle_trained_id', 'exercise_id');
    }

}
