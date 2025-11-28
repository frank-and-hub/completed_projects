<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengePackages extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.challenge_packages');
    }
    protected $fillable = [
        'plan_name',
        'type',
        'duration',
        'amount',
        'description',
        'product_id',
        'active',
        'status',
    ];

    protected $casts = [
        'type' => 'integer',
        'duration' => 'integer',
        'amount' => 'integer',
        'active' => 'integer',
        'status' => 'integer',
    ];
}
