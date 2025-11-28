<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseMuscleTrained extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.exercise_muscle_trained');
       
    }

}
