<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Workout_Week_Days extends Model
{
    //

    protected $table;
    protected $fillable = [
        'week',
        'day_number',
        'workout_program_id',
        'is_rest_day',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = ['day_number'];
    protected $casts = [
        'is_rest_day' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.workout_week_days');
    }

    // Check if it's a rest day
    // public function isRestDay()
    // {
    //     return $this->is_rest_day === 1;
    // }

    public function progress()
    {
        return $this->hasOne(UserProgress::class, 'day_id');
    }

    public function week()
    {
        return $this->belongsTo(Workout_Programs::class, 'workout_program_id');
    }

    // public function day()
    // {
    //     return $this->belongsTo(Workout_Week_Days::class, 'day_number', 'day_number');
    // }

    // public function workoutProgram()
    // {
    //     return $this->belongsTo(Workout_Programs::class, 'workout_program_id', 'id');
    // }

     public function program()
    {
        return $this->belongsTo(Workout_Programs::class, 'workout_program_id');
    }

    public function days()
    {
        return $this->hasMany(Workout_Week_Days::class, 'week');
    }

    public function getProgressAttribute()
    {
        return $this->progress()->first();
    }

    public function getIsActiveAttribute()
    {
        return $this->progress()->first()?->is_active ?? false;
    }

    public function getStatusAttribute()
    {
        return $this->progress()->first()?->status ?? false;
    }

    public function getExercisesAttribute()
    {
        return $this->exercises()->get();
    }

    public function getWeekAttribute()
    {
        return $this->attributes['week'] ?? null;
    }

    public function getDayAttribute()
    {
        return $this->attributes['day_number'] ?? null;
    }

    public function getWorkoutProgramAttribute()
    {
        return $this->workoutProgram()->first();
    }

    public function getWeekNumberAttribute()
    {
        return $this->attributes['week'] ?? null;
    }

    public function getDayNumberAttribute()
    {
        return $this->attributes['day_number'] ?? null;
    }

    // public function getIsRestDayAttribute()
    // {
    //     return $this->is_rest_day === 1;
    // } 

    public function getFormattedExercisesAttribute()
    {
        return $this->exercises()->map(function ($exercise) {
            return [
                'id' => $exercise->id,
                'name' => $exercise->exercise->exercise_name ?? '',
                'reps' => $exercise->reps,
                'sets' => $exercise->sets,
                'rest_time' => $exercise->rest_seconds,
                'level' => $exercise->exercise->level ?? '',
                'location' => $exercise->exercise->location ?? '',
                'body_part' => $exercise->exercise->bodyType->name ?? '',
                'order' => $exercise->order
            ];
        })->toArray();
    }

    public function getFormattedWeekAttribute()
    {
        return [
            'id' => $this->id,
            'week_number' => $this->attributes['week'] ?? null,
            'day_number' => $this->attributes['day_number'] ?? null,
            'is_rest_day' => $this->is_rest_day,
            'exercises' => $this->formatted_exercises
        ];
    }

    public function getFormattedDayAttribute()
    {
        return [
            'id' => $this->id,
            'day_number' => $this->attributes['day_number'] ?? null,
            'is_rest_day' => $this->is_rest_day,
            'exercises' => $this->formatted_exercises
        ];
    }

    public function getFormattedWorkoutProgramAttribute()
    {
        $program = $this->workoutProgram()->first();
        return [
            'id' => $this->workout_program_id,
            'title' => $program->title ?? '',
            'goal' => $program->goal ?? ''
        ];
    }
    public function exercises()
    {
        return $this->hasMany(Workout_Program_Exercises::class, 'workout_week_days_id');
        // or whatever your relationship is
    }



    public function userProgress()
    {
        return $this->hasOne(UserProgress::class, 'workout_program_id')
            ->where('user_id', Auth::user()->id)
            ->where('week_id', $this->week)
            ->where('day_id', $this->day_number);
    }
}
