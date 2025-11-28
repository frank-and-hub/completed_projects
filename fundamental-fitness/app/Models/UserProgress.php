<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProgress extends Model
{
    use SoftDeletes;
    
    protected $table;

    public function __construct(array $attributes = [])
    {
        $this->table = config('tables.user_progress');
        parent::__construct($attributes);
    }
    protected $fillable = [
        'user_id',
        'meso_id',
        'set_id',
        'week_id',
        'day_id',
        'exercise_id',
        'weight',
        'rpe',
        'reps',
        'status',
        'is_active',
        'user_workout_id',
        'completed_at',
        'processed_count'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'completed_at' => 'date'

    ];

    /**
     * Get the user associated with this progress record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

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
        return $this->belongsTo(WorkoutSet::class, 'set_id')->withTrashed();
    }

    public function workout()
    {
        return $this->belongsTo(UserWorkout::class, 'user_workout_id', 'id');
    }

    public function getSetNumberAttribute()
    {
        return optional($this->sets()->first())->set_number ?? 0;
    }

    public function getRestAttribute()
    {
        return optional($this->sets()->first())->rest ?? 0;
    }

    public function getRestUnitAttribute()
    {
        return optional($this->sets()->first())->rest_unit ?? 0;
    }

    public function getRpePercentageAttribute()
    {
        return optional($this->sets()->first())->rpe_percentage ?? 0;
    }
}
