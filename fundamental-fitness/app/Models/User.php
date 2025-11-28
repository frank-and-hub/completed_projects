<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\AdminResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('tables.users');
    }

    protected $fillable = [
        'fullname',
        'email',
        'phone',
        'password',
        'profile_photo',
        'device_type',
        'device_id',
        'login_type',
        'forgot_token',
        'role',
        'language',
        'status',
        'last_token_id',
        'workout_frequency',
        'notifications_enabled',
        'meso_start_date',
        'is_subscribe',
        'social_id'
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
        'is_subscribe' => 'boolean',
        'notifications_enabled' => 'boolean',
    ];


    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPasswordNotification($token, $this->fullname));
    }

    protected static function booted()
    {
        static::deleted(function ($user) {
            $user?->currentAccessToken()?->delete();
            $user?->tokens()?->delete();
            Verification::where('value', $user->email)->delete();
        });
    }

    protected $appends = ['active_pointer'];

    public function getActivePointerAttribute()
    {
        return get_active_pointer($this->id, $this->meso_start_date);
    }

    public function work_out_frequency()
    {
        return $this->belongsTo(WorkoutFrequency::class, 'workout_frequency');
    }

    public function user_workouts()
    {
        return $this->hasMany(UserWorkout::class);
    }

    public function getIsFrequencySetAttribute()
    {
        return ($this->workout_frequency && $this->work_out_frequency) ? true : false;
    }

    public function watchedExercises()
    {
        return $this->belongsToMany(Exercise::class, WatchedVideos::class)
            ->withPivot('video_count')
            ->withTimestamps();
    }

    public function getTotalWatchedVideoCountAttribute()
    {
        return $this->watchedExercises()->sum(WatchedVideos::class . '.video_count');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function getFirstExecutionDateAttribute()
    {
        $execution_date = UserWorkout::where('user_id', $this->id)
            ->where('meso_id', 1)
            ->where('week_id', 1)
            ->where('day_id', 1)
            ->orderByDesc('execution_date')
            ->value('execution_date');

        if (!$execution_date) {
            return true;
        }

        return $$execution_date <= now();
    }

    public function user_notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Reset all user-related data safely.
     */
    public function resetData($bool = true): void
    {
        if ($bool) {
            $this->update([
                'fullname'        => $this->fullname . ' ~deleted',
                'email'           => $this->email . ' ~deleted',
                'last_token_id'   => null,
            ]);
        }        
        $this->update([
            'meso_start_date' => Carbon::now(),
            'is_subscribe' => 0,
        ]);
        Cache::forget('is_eligible_for_reset_' . $this->id);
        $this?->user_workouts()->forceDelete();
        $this?->progress()->forceDelete();
        $this?->user_notifications()?->forceDelete();
    }
}
