<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentPropertyUser extends Model
{
    use HasUuids, HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';

    protected $table = 'sent_property_users';

    protected $fillable = ['user_id', 'property_id', 'search_id', 'status', 'message_id', 'credit_reports_status'];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];


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
        return $this->belongsTo(Property::class, 'property_id');
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
}
