<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UserWorkoutPlan extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_workout_plans');
    }
    protected $fillable = [
        'user_id',
        'workout_program_id',
        'start_date',
        'end_date',
        'current_week',
        'current_day',
        'is_completed',
        'is_active',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_completed' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user this plan belongs to
     */
    public function workoutProgram()
    {
        return $this->belongsTo(Workout_Programs::class, 'workout_program_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the admin who created this plan
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the plan is currently active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the current day's workout
     */
    public function getCurrentDayWorkout()
    {
        return $this->workoutProgram->weekDays()
            ->where('week', $this->current_week)
            ->where('day_number', $this->current_day)
            ->with('exercises.exerciseDetails')
            ->first();
    }

    /**
     * Mark a day as completed and progress to next
     */
    public function completeDay()
    {
        // If not the last day of week, increment day
        if ($this->current_day < 7) {
            $this->increment('current_day');
        }
        // If last day of week but not last week
        elseif ($this->current_week < $this->workoutProgram->duration_weeks) {
            $this->update([
                'current_day' => 1,
                'current_week' => $this->current_week + 1
            ]);
        }
        // If completed all weeks
        else {
            $this->update([
                'is_completed' => true,
                'is_active' => false
            ]);
        }

        return $this;
    }

    public function getActiveWorkoutPlan()
    {
        return $this->where('user_id', Auth::user()->id)->where('is_active', true)->first();
    }

    /**
     * Calculate progress percentage
     */
    public function getProgressAttribute()
    {
        $total_days = $this->workoutProgram->duration_weeks * 7;
        $completed_days = (($this->current_week - 1) * 7) + ($this->current_day - 1);

        return round(($completed_days / $total_days) * 100, 2);
    }

    public function meals()
    {
        return $this->hasMany(Admin_Meal_Entries::class, 'workout_program_id', 'workout_program_id');
    }
}
