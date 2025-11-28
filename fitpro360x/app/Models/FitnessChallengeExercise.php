<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FitnessChallengeExercise extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ft_fitness_challenges_exercises';

    protected $fillable = [
        'fitness_challenges_week_days_id',
        'day_id',
        'exercise_id',
        'reps',
        'sets',
        'difficulty_level',
        'location',
        'body_parts',
        'order',
        'rest_time'
    ];

    // protected $casts = [
    //     'body_parts' => 'array',
    // ];

    /**
     * Relationship with the week day
     */
    public function weekDay()
    {
        return $this->belongsTo(FitnessChallengeWeekDay::class, 'fitness_challenges_week_days_id');
    }

    /**
     * Relationship with the exercise
     */
    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }
}
