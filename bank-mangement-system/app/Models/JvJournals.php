<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JvJournals extends Model {
    protected $table = "jv_journals";
    protected $guarded = [];

    public function jvJournalHeads() {
        return $this->hasMany(JvJournalHeads::class,'jv_journal_id');
    }
    public function Branch() {
        return $this->belongsTo(Branch::class, 'branch_id');
    }


    /**
     * Get the company this model belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Companies>
     */
    public function company()
    {
        return $this->belongsTo(Companies::class,'company_id');
    }
}
