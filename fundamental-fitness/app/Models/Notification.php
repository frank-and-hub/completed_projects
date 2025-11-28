<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes, HasUuids;
    protected $table;

    // public function __construct(array $attributes = [])
    // {
    //     parent::__construct($attributes);
    //     $this->table = config('tables.notifications');
    // }

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $dates = ['read_at', 'deleted_at', 'created_at', 'updated_at'];
    protected $fillable = [
        'id',
        'user_id',
        'type',
        'message',
        'meta',
        'title',
        'read_at',
    ];
    protected $casts = [
        'meta'       => 'array',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnRead()
    {
        $this->update(['read_at' => null]);
    }

    public function isRead()
    {
        return !is_null($this->read_at);
    }
}
