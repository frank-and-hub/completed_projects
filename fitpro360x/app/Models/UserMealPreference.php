<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMealPreference extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ft_user_meal_preferences';

    protected $fillable = [
        'user_id',
        'diet_id',
    ];

    // Relationships (optional but recommended)

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mealPlan()
    {
        return $this->belongsTo(MealsPlan::class, 'diet_id');
    }
}
