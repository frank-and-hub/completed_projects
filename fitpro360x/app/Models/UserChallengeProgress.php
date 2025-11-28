<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChallengeProgress extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        $this->table = 'ft_user_challenge_progress';
        parent::__construct($attributes);
    }
    protected $fillable = [
        'user_id',
        'week_id',
        'day_id',
        'challenge_id',
        'status',
        'start_date',
        'end_date',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'

    ];

    /**
     * Get the user associated with this progress record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the exercise associated with this progress record.
     */
    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id', 'id');
    }

    /**
     * Get the workout program associated with this progress record.
     */
    public function workoutProgram()
    {
        return $this->belongsTo(Workout_Programs::class, 'workout_program_id', 'id');
    }

    /**
     * Get the week associated with this progress record.
     */
    public function week()
    {
        return $this->belongsTo(Workout_Week_Days::class, 'week');
    }

    /**
     * Get the day associated with this progress record.
     */
    public function day()
    {
        return $this->belongsTo(Workout_Week_Days::class, 'day_number');
    }
    public function exerciseProgress()
    {
        return $this->hasMany(UserExerciseProgressWorkout::class, 'progress_id');
    }
}
