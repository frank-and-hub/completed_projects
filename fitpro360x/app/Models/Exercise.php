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
        'exercise_name',
        'level',
        'location',
        'equipment',
        'image',
        'video',
        'description',
        'muscles_trained_id',
        'body_type_id',
    ];

    // Relationship to BodyType
    public function bodyType()
    {
        return $this->belongsTo(BodyType::class, 'body_type_id');
    }

    // Relationship to MuscleMaster
    public function muscle()
    {
        return $this->belongsTo(MuscleMaster::class, 'muscles_trained_id');
    }

    public function muscle_trained()
    {
        return $this->belongsToMany(
            MuscleMaster::class,
            config('tables.exercise_muscle_trained'),
            'exercise_id',
            'muscle_trained_id'
        );
    }
    
    public function muscles()
    {
        return $this->belongsToMany(MuscleMaster::class, config('tables.exercise_muscle_trained'), 'exercise_id', 'muscle_trained_id');
    }
}
