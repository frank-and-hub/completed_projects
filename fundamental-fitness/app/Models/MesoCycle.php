<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MesoCycle extends Model
{
    use SoftDeletes;
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.meso_cycles');
    }

    protected $fillable = [
        'name',
        'workout_frequency_id',
        'week_number',
        'notes',
    ];

    public function workout_frequency()
    {
        return $this->belongsTo(WorkoutFrequency::class, 'workout_frequency_id');
    }
    public function weeks()
    {
        return $this->hasMany(Week::class, 'mesho_id');
    }

    public function days()
    {
        return $this->hasManyThrough(Day::class, Week::class, 'mesho_id', 'week_id');
    }

    public function workouts()
    {
        return $this->hasMany(Workout::class, 'meso_id');
    }
}
