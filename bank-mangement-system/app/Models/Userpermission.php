<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Userpermission extends Model
{

	protected $table = 'user_permission';

	/**
	 * A permission can be applied to roles.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function roles()
	{
		return $this->belongsToMany(Role::class);
	}
}
