<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class WorkoutFrequency extends Model
{
    protected $table;
    use HasFactory, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.workout_frequencies');

    }

    public function mesoCycles()
    {
        return $this->hasMany(MesoCycle::class, 'workout_frequency_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'workout_frequency');
    } 
}
