<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Auth\VerifyEmail as VerifyEmailNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'email_verified_at',
        'total_verify_image',
        'total_pending_image',
        'is_active',
        'image_id'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    /**
     * Create a new personal access token for the user.
     *
     * @param  string|null  $name
     * @param  array  $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createToken(string $name = null, array $abilities = ['*']): \Laravel\Sanctum\NewAccessToken
    {
        $request = request();

        $device_name = "Device " . ($this->tokens()->count() + 1);
        $token = $this->tokens()->create([
            'name' => $name ?? ($device_name) . ' ' . $request->header('Device-Model'),
            'token' => hash('sha256', $plainTextToken = \Illuminate\Support\Str::random(128)),
            'abilities' => $abilities,
            'ip' => $request->ip(),
            'device_type' => $request->get('device_type', 'web'),
            'device_data' => ['User-Agent' => $request->header('User-Agent'), 'time_zone' => $request->header('Timezone'), 'app_version' => $request->header('App-Version')],
        ]);

        return new \Laravel\Sanctum\NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public function image()
    {
        return $this->belongsTo(Media::class, "image_id");
    }

    public function sendEmailVerificationNotification(string $email = null)
    {
        $verification = $this->verifications()->where([
            "scope" => "verification",
            "verification_type" => "email",
            "verifying" => $email ?: $this->email,
            "status" => "pending",
        ])->where("valid_upto", ">", now())
            ->first();

        if (empty($verification)) {

            $digits = config('constant.otp_digits', 4);
            $otp = config('services.send_email')  ? rand(pow(10, $digits - 1), pow(10, $digits) - 1) :  substr('9876543210', 0, $digits);
            //  $otp = substr('9876543210', 0, $digits);

            $verification = Verification::create([
                "user_id" => $this->id,
                "scope" => "verification",
                "verification_type" => "email",
                "verifying" => $email ?: $this->email,
                "otp" => $otp,
                "valid_upto" => now()->addMinutes(15),
                "status" => "pending",
            ]);
        }

        if ($email) {
            (config('services.send_email')) ? Notification::route('mail', $email)->notify(new VerifyEmailNotification($verification)) : null;
        } else {
            (config('services.send_email')) ?   $this->notify(new VerifyEmailNotification($verification)) : null;
        }
    }

    public function verifyEmailOtp(string $otp, bool $mark_used = true, string $email = null): bool
    {
        $verification = $this->verifications()->where([
            "scope" => "verification",
            "verification_type" => "email",
            "verifying" => $email ?: $this->email,
            "status" => "pending",
        ])->where("valid_upto", ">", now())
            ->first();

        if (!empty($verification) && $verification->otp == $otp) {

            if (!$mark_used) {
                return true;
            }

            $verification->status = "used";
            $this->email_verified_at = now();

            try {
                DB::beginTransaction();
                $this->save();
                $verification->save();
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollback();

                throw $e;
            }

            return true;
        }

        return false;
    }

    public function sendPasswordResetNotification($token)
    {
        $verification = $this->verifications()->where([
            "scope" => "reset_password",
            "verification_type" => "email",
            "verifying" => $this->email,
            "status" => "pending",
        ])->where("valid_upto", ">", now())
            ->first();

        if (empty($verification)) {

            $digits = config('constant.otp_digits', 4);
            $otp = ((config('services.send_email'))) ? rand(pow(10, $digits - 1), pow(10, $digits) - 1) :  substr('9876543210', 0, $digits);

            $verification = Verification::create([
                "user_id" => $this->id,
                "scope" => "reset_password",
                "verification_type" => "email",
                "verifying" => $this->email,
                "link" => $token,
                "otp" => $otp,
                "valid_upto" => now()->addMinutes(15),
                "status" => "pending",
            ]);
        } else {
            $verification->link = $token;
            $verification->save();
        }

        // (config('services.send_email')) ?   $this->notify(new VerifyEmailNotification($verification)) : null;
        // ( config('services.send_email')) ? $this->notify(new ResetPasswordNotification($verification)) : null;
        if ($this->email) {
            (config('services.send_email')) ? Notification::route('mail', $this->email)->notify(new VerifyEmailNotification($verification)) : null;
        } else {
            (config('services.send_email')) ?   $this->notify(new VerifyEmailNotification($verification)) : null;
        }
    }

    /**
     *
     * @return Verification|bool
     */
    public function verifyResetPasswordOtp(string $otp, bool $mark_used = true): Verification|bool
    {
        $verification = $this->verifications()->where([
            "scope" => "reset_password",
            "verification_type" => "email",
            "verifying" => $this->email,
            "status" => "pending",
        ])->where("valid_upto", ">", now())
            ->first();

        if ($verification && $verification->otp == $otp) {

            if (!$mark_used) {
                return $verification;
            }

            $verification->status = "used";
            $verification->save();

            $this->email_verified_at = now();

            return $verification;
        }

        return false;
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class, 'user_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'user_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ParkLike::class, 'user_id');
    }

    /**
     * Get all of the comments for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookmarkType(): HasMany
    {
        return $this->hasMany(BookmarkType::class, 'user_id');
    }

    /**
     * Get all of the parksimage for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parkimages(): HasMany
    {
        return $this->hasMany(ParkImage::class, 'user_id');
    }


    public function parks(): HasMany
    {
        return $this->hasMany(Parks::class, 'created_by_id');
    }


    public function allParkImagesParks()
    {
        return $this->belongsToMany(Parks::class, 'park_images', 'user_id', 'park_id', 'id', 'id');
    }

    /**
     * Get all of the comments for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pendingimages(): HasMany
    {
        return $this->hasMany(Pendingimage::class, 'user_id');
    }
}
