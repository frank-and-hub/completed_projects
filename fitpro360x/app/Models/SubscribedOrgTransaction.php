<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscribedOrgTransaction extends Model
{
    //

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.subscribed_org_trans');
       
    }
       public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
