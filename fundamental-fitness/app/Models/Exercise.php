<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    protected $table;
    use HasFactory, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.exercises');
    }

    protected $fillable = [
        'name',
        'status'
    ];

    public function workouts()
    {
        return $this->hasMany(Workout::class, 'exercise_id');
    }

    public function watchers()
    {
        return $this->belongsToMany(User::class, WatchedVideos::class)
            ->withPivot('video_count')
            ->withTimestamps();
    }
}
