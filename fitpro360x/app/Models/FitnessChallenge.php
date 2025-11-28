<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FitnessChallenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ft_fitness_challenges';

    protected $fillable = [
        'challenge_name',
        'goal',
        'duration_weeks',
        'plan_id',
        'image',
        'description',
    ];

    /**
     * Relationship with week days of the challenge
     */
    public function weekDays()
    {
        return $this->hasMany(FitnessChallengeWeekDay::class, 'fitness_challenge_id');
    }

    /**
     * Relationship with subscription package
     */
    public function subscriptionPackage()
    {
        return $this->belongsTo(SubscriptionPackages::class, 'plan_id');
    }

    public function getFormattedWeeksData()
{
    return $this->weekDays->groupBy('week')->map(function($days, $week) {
        return [
            'week' => $week,
            'days' => $days->map(function($day) {
                return [
                    'day' => $day->day_number,
                    'is_rest_day' => $day->is_rest_day,
                    'exercises' => $day->exercises->map(function($exercise) {
                        return [
                            'exercise_id' => $exercise->exercise_id,
                            'name' => $exercise->exercise->exercise_name,
                            'reps' => $exercise->reps,
                            'sets' => $exercise->sets,
                            'rest_time' => $exercise->rest_time,
                            'level' => $exercise->exercise->level,
                            'location' => $exercise->exercise->location,
                            'body_part' => $exercise->exercise->bodyType->name ?? '',
                            'order' => $exercise->order
                        ];
                    })->toArray()
                ];
            })->toArray()
        ];
    })->values()->toArray();
}
public function plan()
{
    return $this->belongsTo(ChallengePackages::class, 'plan_id');
}
}