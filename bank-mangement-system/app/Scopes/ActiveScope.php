<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope{
    public function apply(Builder $builder ,Model $model)
    {
        $builder->where('status',1);
    } 

    public function remove(Builder $builder,Model $model)
    {
        $query = $builder->getQuery();
        $query->wheres =  collect($query->wheres)->reject(function($where){
            return $where['status'] == 1;
        })->values()->all();
    }
    
}