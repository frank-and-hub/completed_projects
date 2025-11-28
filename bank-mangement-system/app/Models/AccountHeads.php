<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountHeads extends Model {
    protected $table = "account_heads";
    protected $guarded = [];
    protected $casts = [
        'child_head' => 'json',
     ];
    public function headBelongs()
    {
        return  $this->belongsToJson(self::class,'child_head','head_id');
    }

    public function subcategory(){

        return $this->hasMany(AccountHeads::class, 'parent_id','head_id');
    }
    public function headcloses(){
       
        return $this->hasMany(HeadClosing::class,'head_id');
    }
    public function setSubHeadAttribute($value)
    {
        $this->attributes['sub_head'] = ucfirst($value);
    }

     // Define a recursive relationship for parent-child
     public function children()
     {
         return $this->hasMany(AccountHeads::class, 'parent_id', 'head_id');
     }
 
     // Define a recursive relationship for child-parent
     public function parent()
     {
         return $this->belongsTo(AccountHeads::class, 'parent_id', 'head_id');
     }

    
}
