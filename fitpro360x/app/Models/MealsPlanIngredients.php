<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MealsPlanIngredients extends Model
{
    protected $table;
    use HasFactory, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.meal_ingredients');

    }

    protected $fillable = [
        'meal_plans_id',
        'ingredient',
        'quantity',

    ];

    public function meal()
    {
        return $this->belongsTo(MealsPlan::class);
    }


}
