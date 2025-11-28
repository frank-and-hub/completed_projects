<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticeboard extends Model
{
	protected $table = "noticeboards";
	protected $fillable = ['title', 'file_id', 'start_date', 'end_date'];
	use SoftDeletes;

	public function files() {
		//return $this->hasMany('App\Comment');
		return $this->hasMany(Files::class,'notice_id');
	}

}
