<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout_Program_Exercises extends Model
{
    //

    protected $table;
    protected $fillable = [
        'workout_week_days_id', // Add this line
        'exercise_id', 
        'day_id',
        'sets',
        'reps',
        //'rest_seconds',
        'rest_seconds',
        'order'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.workout_program_exercises');
       
    }

    // Relationship with exercise (assuming ft_ms_exercises exists)
    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function exerciseDetails()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function muscles()
    {
        return $this->belongsToMany(MuscleMaster::class, config('tables.exercise_muscle_trained'), 'exercise_id', 'muscle_trained_id');
    }
}


