<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SentInternalPropertyUser extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'user_id',
        'internal_property_id',
        'search_id',
        'status',
        'message_id',
        'admin_id',
        'credit_reports_status',
        'notes'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';

    /**
     * Get the user that owns the SentPropertyUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the property that owns the SentPropertyUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property()
    {
        return $this->belongsTo(InternalProperty::class, 'internal_property_id');
    }

    /**
     * Get the search that owns the SentPropertyUser
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function search()
    {
        return $this->belongsTo(UserSearchProperty::class, 'search_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
