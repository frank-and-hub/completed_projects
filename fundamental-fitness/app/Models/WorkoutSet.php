<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkoutSet extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.workout_sets');
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class, 'workout_id');
    }

    public function user_workout()
    {
        return $this->belongsTo(Workout::class, 'workout_id');
    }


    public function exercise()
    {
        return $this->hasOneThrough(
            Exercise::class,   // Final model
            Workout::class,    // Intermediate model
            'id',              // Foreign key on Workout table (Workout.id)
            'id',              // Foreign key on Exercise table (Exercise.id)
            'workout_id',      // Local key on WorkoutSet table (workout_id)
            'exercise_id'      // Local key on Workout table (exercise_id)
        );
    }
}
