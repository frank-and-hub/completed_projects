<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin_Meal_Entries extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.admin_meal_entries');
    }

    protected $fillable = [
        'workout_program_id',
        'meal_id',
        'diet_preference',
    ];

    /**
     * If you have an Admin user model, define the relationship:
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Optionally add attribute labels or mappings:
     */
    public static function dietLabels()
    {
        return [
            1 => 'Veg',
            2 => 'Non-Veg',
            3 => 'Keto',
            4 => 'Vegan',
        ];
    }

    public static function typeLabels()
    {
        return [
            1 => 'Breakfast',
            2 => 'Lunch',
            3 => 'Dinner',
        ];
    }

    public function meal()
    {
        return $this->belongsTo(MealsPlan::class, 'meal_id');
    }
     public function userworkout()
    {
        return $this->belongsTo(UserWorkoutPlan::class, 'workout_program_id');
    }
     public function option()
    {
        return $this->belongsTo(QuestionsOption::class, 'option_id', 'id');
    }
     public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');
    }
    public function questionAnswerUser()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'meal_id', 'id');
    }
}
