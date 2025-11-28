<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Roleplan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['role_id','is_free'];

    protected $table = 'role_plans';

    protected function casts(): array
    {
        return [
            'is_free'   => 'boolean'
        ];
    }

    public function role():BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
