<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GstTransaction extends Model
{
    use SoftDeletes;
    protected $table = "gst_transactions";
    protected $guarded = [];
    protected $dates = ['deleted_at'];


    public function Heads()
    {
        return $this->belongsTo(AccountHeads::class, 'head_id', 'head_id');
    }
    /**
     * get Transaction details
     */
    public function firstTransaction($headId)
    {
        $trans = array();
        $trans['start'] = GstTransaction::select('invoice_number')->where('head_id', $headId)->orderBy('uid', 'ASC')->first();
        $trans['last'] = GstTransaction::select('invoice_number')->where('head_id', $headId)->orderBy('uid', 'desc')->first();
        $trans['count'] = GstTransaction::where('head_id', $headId)->count();
        return $trans;
    }

    public function memberDetails()
    {
        return $this->belongsTo(Member::class, 'type_id', 'id');
    }

    public function gstHeadrate()
    {
        return $this->belongsTo(HeadSetting::class, 'head_id', 'head_id');
    }
    public function company()
    {
        return $this->belongsTo(Companies::class);
    }
}