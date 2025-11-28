<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Workout_Programs extends Model
{
    protected $table;
    protected $fillable = [
        'title',
        'workout_name',
        'goal',
        'location',
        'duration_weeks',
        'level',
        'description',
        'image',
        'status'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.workout_programs');
    }

    // Primary relationship to weeks
    public function weeks()
    {
        return $this->hasMany(Workout_Week_Days::class, 'workout_program_id')
            ->orderBy('week')
            ->orderBy('day_number');
    }

    // Relationship to days through weeks
    public function days()
    {
        return $this->hasMany(Workout_Week_Days::class, 'week');
    }

    // Relationship to exercises
    public function exercises()
    {
        return $this->hasManyThrough(
            Workout_Program_Exercises::class,
            Workout_Week_Days::class,
            'workout_program_id',
            'workout_week_days_id'
        );
    }

    // User progress relationship
    public function userProgress()
    {
        return $this->hasOne(UserProgress::class, 'workout_program_id')
            ->where('user_id', Auth::id());
    }

    // Accessor for location
    public function getLocationNameAttribute()
    {
        return match ($this->location) {
            1 => 'Home',
            2 => 'Gym',
            default => 'Unknown',
        };
    }

    // Get formatted data (if needed for other purposes)
    public function getFormattedData()
    {
        return $this->load(['weeks.days.exercises.exercise'])->weeks->map(function ($week) {
            return [
                'week_id' => $week->id,
                'week_number' => $week->week_number,
                'days' => $week->days->map(function ($day) {
                    return [
                        'day_id' => $day->day_number,
                        'is_rest_day' => (bool)$day->is_rest_day,
                        'exercises' => $day->exercises->map(function ($exercise) {
                            return [
                                'id' => $exercise->id,
                                'name' => $exercise->exercise->exercise_name ?? 'Unknown',
                                'sets' => $exercise->sets,
                                'reps' => $exercise->reps,
                                'rest_seconds' => $exercise->rest_seconds,
                                'order' => $exercise->order,
                                // Include other exercise fields as needed
                            ];
                        })->sortBy('order')->values()
                    ];
                })->sortBy('day_id')->values()
            ];
        });
    }

    public function getFormattedWeeksData()
    {
    return $this->weeks->groupBy('week')->map(function($days, $week) {
        // pree($days);
            return [
                'week' => $week,
            'days' => $days->map(function($day) {
                    return [
                        'day' => $day->day_number,
                        'is_rest_day' => $day->is_rest_day,
                    'exercises' => $day->exercises->map(function($exercise) {
                            return [
                                'exercise_id' => $exercise->exercise_id,
                            'name' => $exercise->exercise->exercise_name?? 'Unknown',
                                'reps' => $exercise->reps,
                                'sets' => $exercise->sets,
                                'rest_time' => $exercise->rest_seconds,
                            'level' => $exercise->exercise->level?? '-',
                            'location' => $exercise->exercise->location??'-',
                                'body_part' => $exercise->exercise->bodyType->name ?? '',
                                'order' => $exercise->order
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        })->values()->toArray();
    }
    // Get current day workout
    public function getCurrentDayWorkout()
    {
        if (!$this->userProgress) {
            return null;
        }

        return Workout_Week_Days::where('week', $this->userProgress->week)
            ->where('day_number', $this->userProgress->day_id)
            ->with(['exercises.exercise'])
            ->first();
    }
}
