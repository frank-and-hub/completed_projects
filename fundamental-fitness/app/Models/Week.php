<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Week extends Model
{
    use SoftDeletes;
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.week');
    }

    public function mesho()
    {
        return $this->belongsTo(MesoCycle::class, 'mesho_id');
    }

    public function days()
    {
        return $this->hasMany(Day::class, 'week_id'); // explicit key
    }
}
