<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MoneyBackSetting extends Model {
    use SoftDeletes;
    protected $table = "money_back_settings";
    protected $guarded = [];

}

