<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EliMoneybackInvestments extends Model {
    protected $table = "eli_moneyback_investments";
    protected $guarded = [];
    
    // public function investment() {
    //     return $this->hasOne(Memberinvestments::class, 'id','investment_id');
    // }
}
