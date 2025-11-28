<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractStatus extends Model
{

    use HasUuids;

    protected $table = 'contract_statuses';

    protected $fillable = [
        'user_id',
        'admin_id',
        'contract_id',
        'contract_path',
        'status'
    ];

    const STATUS_TENANT_PENDING = 0;
    const STATUS_APPROVAL_PENDING = 1;
    const STATUS_AGENCY_APPROVED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_AGENCY_PENDING = 4;
    const STATUS_COMPLETED = 5;

    const CONTRACT_STATUS = [
        'tenant_pending'     => self::STATUS_TENANT_PENDING,
        'approval_pending'   => self::STATUS_APPROVAL_PENDING,
        'agency_pending'     => self::STATUS_AGENCY_PENDING,
        'approved'           => self::STATUS_AGENCY_APPROVED,
        'rejected'           => self::STATUS_REJECTED,
        'completed'          => self::STATUS_COMPLETED,
    ];

    // Corrected method to return flipped CONTRACT_STATUS
    public static function STATUS_ARRAY()
    {
        return array_flip(self::CONTRACT_STATUS);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}
