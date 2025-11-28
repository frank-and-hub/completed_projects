<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'internal_propertie_id',
        'user_id',
        'admin_id',
        'sent_internal_property_user_id',
        'event_datetime',
        'title',
        'description',
        'link',
        'status'
    ];

    protected $casts = [
        'event_datetime' => "datetime"
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RESCHEDULE = 're-schedule';


    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status_info()
    {
        return $this->hasMany(CalendarStatus::class, 'calendar_id');
    }

    public function property()
    {
        return $this->belongsTo(InternalProperty::class, 'internal_propertie_id');
    }

    public function isExpiry()
    {
        return ($this->status === self::STATUS_PENDING) ? Carbon::now('UTC')->isBefore($this->event_datetime) : 0;
    }
}
