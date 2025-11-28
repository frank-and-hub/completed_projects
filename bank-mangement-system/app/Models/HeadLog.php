<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeadLog extends Model
{
    protected $table = 'head_logs';
    protected $guarded = [];


    public function parent()
    {
        return $this->belongsTo(AccountHeads::class, 'parent_id', 'head_id');
    }

    public function getCompaniesAttribute()
    {
        // Assuming company_id is a JSON column in the database
        $companyIds = json_decode($this->attributes['company_id'], true);

        // Fetch companies using the array of company IDs
        return Companies::whereIn('id', $companyIds)->get();
    }

   
}


