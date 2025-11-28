<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectorAccount extends Model
{
    protected $table = "collector_account";
    protected $guarded = [];

    public function member_collector()
    {
        return $this->belongsTo(Member::class, 'associate_id')
            ->select('id', 'first_name', 'last_name', 'associate_no', 'associate_code')
            ->whereStatus(1);
    }

    public function memberinvestments_collector()
    {
        return $this->belongsTo(Memberinvestments::class, 'type_id');
    }

    public function memberloans_collector()
    {
        return $this->belongsTo(Memberloans::class, 'type_id');
    }

    public function grouploans_collector()
    {
        return $this->belongsTo(Grouploans::class, 'type_id');
    }
    public function CollectorAccount()
    {
        return $this->belongsTo(CollectorAccount::class, 'type_id', 'id');
    }
}
