<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssociateTree extends Model
{
    protected $table = "associate_trees";
    protected $guarded = []; 

    public function subcategory(){

        return $this->hasMany(AssociateTree::class, 'parent_id');
    }
    public function subcategory1(){
       
        return $this->hasMany(AssociateTree::class, 'id', 'parent_id');
    }
    public function member(){
       
        return $this->belongsTo(Member::class);
    }
}
