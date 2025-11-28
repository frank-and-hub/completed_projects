<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminWorkoutSettings extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.admin_workout_plan');
       
    }

     protected $fillable = [
        'workout_program_id',
        'question_id',
        'option_id',
        'answer'
    ];


}
