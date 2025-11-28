<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class WaterConsumptionType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table;
    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.water_consumption_types');

    }


}
