<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, HasUuids, HasRoles, SoftDeletes, HasApiTokens;
    // protected $guard = 'admin';
    protected $guard_name = 'admin';

    protected $table = 'admins';

    protected $fillable = [
        'name',
        'email',
        'dial_code',
        'phone',
        'password',
        'status',
        // 'request_type',
        'password_text',
        'admin_id',
        'is_whatsapp_notification',
        'subscription',
        'country',
        'timeZone',
    ];

    protected $hidden = [
        'password',
        'password_text'
    ];

    public function designation()
    {
        $role = $this->getRoleNames()->first();

        return match ($role) {
            'admin' => 'Super Admin',
            'privatelandlord' => 'Landlord',
            default => ucwords($role)
        };
    }

    /**
     * This file defines the Admin model class.
     *
     *
     * The Admin model is used to interact with the 'admins' table in the database.
     * It contains properties and methods specific to the admin functionality.
     */
    public function is_deleteByAdmin()
    {
        $role = $this->getRoleNames()->first();
        $auth_role = auth()->user()->getRoleNames()->first();

        if ($this->property()->first()) {
            return false;
        }

        switch ($role) {
            case 'agent':
                if ($auth_role == 'admin') {
                    return false;
                }
                break;
            case 'agency':
                if ($this->agency_agents()->first()) {
                    return false;
                }
                break;
            case 'privatelandlord':
                return false;
                break;
            default;
                return true;
        }
        return true;
    }

    public function privateLandlord(): HasOne
    {
        return $this->hasOne(PrivateLandlord::class);
    }

    public function agencyRegister(): HasOne
    {
        return $this->hasOne(AgencyRegister::class);
    }

    public function property()
    {
        return $this->hasMany(InternalProperty::class, 'admin_id');
    }

    public function otpVerification()
    {
        return $this->hasOne(OtpVerification::class, 'admin_id');
    }

    public function media()
    {
        return $this->hasMany(AdminMedia::class, 'admin_id');
    }

    public function image()
    {
        return $this->media()->where('type', 'image')->orderByDesc('id');
    }

    public function lastImage()
    {
        return $this->image()->first()->path;
    }

    ///// agent og agency
    public function agency_agents()
    {
        return $this->hasMany(Admin::class, 'admin_id');
    }

    public function agent_agency()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function adminScheduleTime()
    {
        return $this->hasOne(AdminScheduleTime::class, 'admin_id');
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class, 'admin_id');
    }

    public function sendInternalPropertyUser()
    {
        return $this->hasMany(SentInternalPropertyUser::class, 'admin_id');
    }

    public function admin_subscription(): HasMany
    {
        return $this->hasMany(AdminSubscription::class, 'admin_id');
    }

    // admin sub user subscription check and update if it's expired
    public function isAvailableSubscription()
    {
        $role = $this->getRoleNames()->first();
        if ($role == 'agent') {
            $admin = Admin::findOrFail($this->admin_id);
            $sub = $admin->admin_subscription();
        } else {
            $sub = $this->admin_subscription();
        }
        $sub = $sub->whereStatus('ongoing')
            ->orderByDesc('created_at')
            ->first();

        if ($sub) {
            if (!in_array($this->email, ['agent@gmail.com', 'agency@gmail.com'])) {
                // Check if property limits are reached
                if ($sub->can_add_property && $sub->total_property >= $sub->can_add_property) {
                    $this->expiredAdminPlan($sub);
                    return null;
                }

                // Check if subscription has expired based on date
                if ($sub->expired_at) {
                    $expired_at = Carbon::parse($sub->expired_at);
                    if (Carbon::now()->greaterThanOrEqualTo($expired_at) && $role != 'privatelandlord') {
                        $this->expiredAdminPlan($sub);
                        return null;
                    }
                }
            }
            return $sub->fresh(); // Return the updated subscription object
        }

        return null;
    }

    private function expiredAdminPlan($sub)
    {
        $sub->status = 'expired'; // Fixed the assignment operator
        $sub->save();
        $this->subscription = 0;
        $this->save();
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'admin_id');
    }

    public function external_api()
    {
        // return $this->hasOne(ExternalPropertyUser::class, 'agency_id');
        return $this->belongsToMany(ExternalPropertyUser::class, 'admin_external_property_users_pivot', 'admin_id', 'external_property_users_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function getSelectedAgencyAttribute()
    {
        return $this->external_api()->exists();
    }

    public function getSelectedAgencyApiStatusAttribute()
    {
        return $this->external_api?->first()?->pivot?->status ?? null;
    }

    public function getContactNoAttribute()
    {
        return '' . $this->attributes['dial_code'] . '' . $this->attributes['phone'] . '';
    }

    public function getPropertyCountAttribute()
    {
        $propertyCount = $this->property()->count();

        $agencyAgentPropertyCount = $this->agency_agents()
            ->with('property')
            ->get()
            ->sum(function ($agencyAgent) {
                return $agencyAgent->property()->count('id');
            });

        $agentAgencyPropertyCount = $this->agent_agency ? $this->agent_agency->property()->count() : 0;

        return $propertyCount + $agencyAgentPropertyCount + $agentAgencyPropertyCount;
    }

    public function getUserRoleAttribute()
    {
        $role = $this->getRoleNames()->first();
        $return = 'guest';
        switch ($role) {
            case 'agent':
                $return = 'agent';
                break;
            case 'agency':
                $return = 'agency';
                break;
            case 'privatelandlord':
                $return = 'landlord';
                break;
            default;
                $return = 'supper_admin';
        }
        return $return;
    }
}
