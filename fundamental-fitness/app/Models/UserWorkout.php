<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWorkout extends Model
{
    use SoftDeletes;
    
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.user_workouts');
    }

    protected $fillable = [
        'user_id',
        'workout_frequency_id',
        'meso_id',
        'day_id',
        'week_id',
        'exercise_id',
        'level',
        'image',
        'video',
        'gif',
        'description',
        'execution_date'
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function meso()
    {
        return $this->belongsTo(MesoCycle::class, 'meso_id');
    }

    public function sets()
    {
        return $this->hasMany(UserProgress::class,  'user_workout_id','id')->withTrashed();
    }

    public function workout_frequency()
    {
        return $this->belongsTo(WorkoutFrequency::class, 'workout_frequency_id');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class, 'user_id', 'user_id')
            ->whereColumn(UserWorkout::class . '.meso_id', UserProgress::class . '.meso_id')
            ->whereColumn(UserWorkout::class . '.week_id', UserProgress::class . '.week_id')
            ->whereColumn(UserWorkout::class . '.day_id', UserProgress::class . '.day_id');
    }
}
