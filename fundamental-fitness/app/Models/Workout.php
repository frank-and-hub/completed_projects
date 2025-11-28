<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workout extends Model
{
    use SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.workouts');
    }

    protected $fillable = [
        'workout_frequency_id',
        'meso_id',
        'day_id',
        'exercise_id',
        'level',
        'image',
        'video',
        'gif',
        'description',
    ];

     // 🔗 Relations
     public function meso()
     {
         return $this->belongsTo(MesoCycle::class, 'meso_id');
     }

     public function workout_frequency()
     {
         return $this->belongsTo(WorkoutFrequency::class, 'workout_frequency_id');
     }

     public function exercise()
     {
         return $this->belongsTo(Exercise::class, 'exercise_id');
     }

     public function sets()
     {
         return $this->hasMany(WorkoutSet::class, 'workout_id')->orderBy('set_number');
     }
}
