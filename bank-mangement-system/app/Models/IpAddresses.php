<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpAddresses extends Model
{
	protected $table = "ip_addresses";
	protected $fillable = ['user_id', 'ip_address'];
}
