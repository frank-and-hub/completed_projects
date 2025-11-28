<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'country_code',
        'phone',
        'type',
        'password',
        'subscription',
        'message_alert',
        'status',
        'country',
        'social_type',
        'social_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function otpVerification(): HasOne
    {
        return $this->hasOne(Otpverify::class);
    }

    public function searchproperty(): HasMany
    {
        return $this->hasMany(UserSearchProperty::class, 'user_id');
    }

    public function scheduletime(): HasOne
    {
        return $this->hasOne(UserScheduleTime::class, 'user_id');
    }

    /**
     * Get the user subscription details
     *
     * @return HasMany
     */
    public function user_subscription(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'user_id');
    }

    /**
     * Get the sent properties for the user.
     *
     * @return HasMany
     */
    public function sentProperties(): HasMany
    {
        return $this->hasMany(SentPropertyUser::class, 'user_id');
    }

    public function sentInternalProperties(): HasMany
    {
        return $this->hasMany(SentInternalPropertyUser::class, 'user_id');
    }

    public function user_schedule_time(): HasOne
    {
        return $this->hasOne(UserScheduleTime::class, 'user_id');
    }

    public function calendar()
    {
        return $this->hasMany(Calendar::class, 'user_id');
    }

    public function calendar2()
    {
        return $this->hasMany(Calendar::class, 'user_id')->orderByDesc('event_datetime');
    }

    public function update_plans()
    {
        $subscription = $this->active_subscription;
        $subscriptionAmount = number_format($subscription?->amount ?? 0, 2, '.', '');
        if (isset($subscription) && $subscriptionAmount == 0) {
            $planId = $subscription?->subscription_id;
            $plan = Plans::findOrFail($planId);
            $planAmount = number_format($plan->amount, 2, '.', '');
            if ($planAmount > 0) {
                $subscription->update([
                    'status' => 'expired',
                    'expired_at' => now()
                ]);
            }
        }
    }

    public function countries()
    {
        $country = $this->hasOne(Country::class, 'name', 'country');

        if (!$country) {
            $country = Country::where('name', 'South Africa')->first();
        }

        return $country;
    }

    public function getContactNoAttribute()
    {
        return '' . $this->attributes['country_code'] . '' . $this->attributes['phone'] . '';
    }

    public function getExpiredDateAttribute()
    {
        $data = $this->active_subscription;
        return $data?->expired_at ? convertToSouthAfricaTime($data->expired_at, 'Africa/Johannesburg', false) : null;
    }

    public function getActiveSubscriptionAttribute()
    {
        return $this->user_subscription()
            ->whereStatus('ongoing')
            ->whereIsActive(1)
            ->latest()
            ->first();
    }

    public function credit_report(): HasOne
    {
        return $this->hasOne(CreditReport::class, 'user_id');
    }

    public function user_employment(): HasOne
    {
        return $this->hasOne(UserEmployment::class, 'user_id');
    }
}
