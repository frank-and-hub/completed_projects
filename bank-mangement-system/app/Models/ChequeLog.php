<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin;
class ChequeLog extends Model
{
    protected $table = 'cheque_logs';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(Admin::class,'created_by_id','id');
    }
    public function branch()
    {
        return $this->belongsTo(User::class,'created_by_id','id');
    }
    
}
