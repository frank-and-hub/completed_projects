<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignNoticeToBranch extends Model
{
	protected $table = "notice_branches";
	protected $fillable = ['notice_id', 'branch_id'];
}
