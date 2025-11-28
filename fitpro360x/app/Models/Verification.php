<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Verification extends Model
{
    use SoftDeletes;
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.verifications');
    }

     protected $fillable = [
        'device_id',    
        'value',       // Email or phone number
        'type',        // 1 for email, 2 for phone
        'status',      // 1 for verified, 0 for not verified
        'created_at',  // Timestamp for when the record was created
        'updated_at',  // Timestamp for when the record was last updated
        'deleted_at',  // Timestamp for soft deletion
        'expires_at',  // Timestamp for when the verification expires
        'otp_type',
        'otp',

    ];

    /**
     * Check if the given value is verified for a specific device and type.
     *
     * @param  string  $value      The email or phone number.
     * @param  mixed   $deviceId   The device identifier.
     * @param  int     $type       The type (e.g., 1 for email, 2 for phone).
     * @return bool
     */
    public static function isVerified($value, $deviceId, $type)
    {
        $record = self::where('value', $value)
            ->where('device_id', $deviceId)
            ->where('type', $type)
            ->where('status')
            ->orderBy('created_at', 'desc')
            ->first();

        return $record !== null;
    }
    // public static function isVerified($type, $deviceId, $checkVerified = 1)
    // {
    //     $record = self::where('type', $type)
    //         ->where('device_id', $deviceId)
    //         ->where('status', $checkVerified)
    //         ->orderBy('created_at', 'desc')
    //       ->exists();

    //         return $record !== null;
    // }
    
    
}
