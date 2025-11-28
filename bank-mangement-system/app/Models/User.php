<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = 'users';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function deposit()
    {
        return $this->hasMany('App\Model\Deposit', 'user_id');
    }

	public function role()
	{
		return $this->belongsTo('App\Models\Role');
	}

	public function scopeSuperAdmin($query)
	{
		return $query->where('role_id', 1)->orderBy('first_name', 'asc');
	}

	public function scopeAdmin($query)
	{
		return $query->where('role_id', 2)->orderBy('first_name', 'asc');
	}

	public function scopeManager($query)
	{
		return $query->where('role_id', 2)->orderBy('first_name', 'asc');
	}

    public function userEmployee()
    {
        return $this->hasOne('App\Models\UserEmployees', 'user_id');
    }

    public function branches()
    {
        return $this->belongsTo('App\Models\Branch', 'id','manager_id');
    }

    public function branch()
    {
        return $this->hasOne('App\Models\Branch', 'manager_id');
    }



}
