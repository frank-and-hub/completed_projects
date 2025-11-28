<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserExerciseProgressWorkout extends Model
{
    // use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_exercise_progress_workout');
    }

    protected $fillable = [
        'progress_id',
        'user_id',
        'exercise_id',
        'is_completed'
    ];
}
