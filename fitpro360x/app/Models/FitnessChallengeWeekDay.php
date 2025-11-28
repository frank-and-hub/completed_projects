<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FitnessChallengeWeekDay extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ft_fitness_challenges_week_days';

    protected $fillable = [
        'week',
        'day_number',
        'fitness_challenge_id',
        'is_rest_day',
    ];

    protected $casts = [
        'is_rest_day' => 'boolean',
    ];

    /**
     * Relationship with the fitness challenge
     */
    public function fitnessChallenge()
    {
        return $this->belongsTo(FitnessChallenge::class, 'fitness_challenge_id');
    }

    /**
     * Relationship with exercises for this day
     */
    public function exercises()
    {
        return $this->hasMany(FitnessChallengeExercise::class, 'fitness_challenges_week_days_id');
    }
}
