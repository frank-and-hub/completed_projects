<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\AdminResetPasswordNotification;
use App\Models\UserWorkoutPlan;
use App\Models\UserSubscription;
use App\Notifications\MealReminderNotification;
use App\Notifications\WorkoutReminderNotification;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    // protected $table = 'ft_users';

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.users');
    }

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'profile_photo',
        'device_type',
        'device_id',
        'forgot_token',
        'role',
        'language',
        'status',
        'last_token_id',
        'notifications_enabled'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'forgot_token',
    ];

    protected $casts = [
        'language' => 'integer',
        'status' => 'boolean',
        'is_profile_completed' => 'boolean',
    ];

    public function activeWorkoutPlan()
    {
        return $this->hasOne(UserWorkoutPlan::class, 'user_id')
            ->where('is_active', 1)
            ->with('workoutProgram');
    }
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function subscription()
    {
        return $this->hasOne(UserSubscription::class)->orderBy('subscribed_at', 'desc');
    }

    public function workoutPlans()
    {
        return $this->hasMany(UserWorkoutPlan::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(QuestionAnswerUser::class, 'user_id');
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPasswordNotification($token, $this->fullname));
    }

    protected static function booted()
    {
        static::deleted(function ($user) {
            // Delete related verification entries when user is soft-deleted
            Verification::where('value', $user->email)->delete();
        });
    }
    public function subscriptions()
{
    return $this->hasMany(UserSubscription::class, 'user_id');
}

}
