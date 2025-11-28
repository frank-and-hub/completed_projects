<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealsPlan extends Model
{
    protected $table;
    use HasFactory, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.meal_plans');

    }

    protected $fillable = [
        'title',
        'image',
        'description',
        'type',
        'diet_preference',
        'protein',
        'carbs',
        'fat',

    ];

   // In App\Models\MealsPlan.php
public function ingredients()
{
    return $this->hasMany(MealsPlanIngredients::class, 'meal_plans_id');
}

}
