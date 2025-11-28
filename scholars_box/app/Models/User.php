<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;
use App\Models\CountryData\State;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'social_id',
        'social_type',
        'email',
        'avatar',
        'phone_number',
        'date_of_birth',
        'gender',
        'state',
        'user_type',
        'looking_for',
        'whatsapp_number',
        'aadhar_card_number',
        'email_verified_at',
        'password',
        'remember_token',
        'company_name',
        'microsite',
        'site_name'
        // Add other columns here
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
        'password' => 'hashed',
    ];


    const GENDER = [
        'male' => "Male",
        'female' => "Female"
    ];

    // student
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($role)
    {
        return true;
    }
    public function per()
    {
        return $this->hasMany(Permission::class,'user_id','id');
    }
    // creating age attribute
    public function getAgeAttribute()
    {
        $dateOfBirth = $this->attributes['date_of_birth'];
        // Calculate age using the calculateAge helper function
        return calculateAge($dateOfBirth);
    }
    public function guardian_details()
    {
        return $this->hasMany(GuardianDetail::class,'user_id','id');
    }

    public function state()
    {
        return $this->belongsTo(State::class,'state','name');
    }

    public function AmountDistribution(){
        return $this->hasMany(AmountDistribution::class,'user_id','id');

    }

    // <!--probile complate -->


    public function isFirstNameComplete()
    {
        return $this->first_name !== null && $this->first_name !== '';
    }

    public function isLastNameComplete()
    {
        return $this->last_name !== null && $this->last_name !== '';
    }

    public function isEmailComplete()
    {
        return $this->email !== null && $this->email !== '';
    }

    public function isAvatarComplete()
    {
        return $this->avatar !== null && $this->avatar !== '';
    }

    public function isPhoneNumberComplete()
    {
        return $this->phone_number !== null && $this->phone_number !== '';
    }



    public function isDateOfBirthComplete()
    {
        return $this->date_of_birth !== null && $this->date_of_birth !== '';
    }

    public function isGenderComplete()
    {
        return $this->gender !== null && $this->gender !== '';
    }

    public function isStateComplete()
    {
        return $this->state !== null && $this->state !== '';
    }

    public function isDistrictComplete()
    {
        return $this->district !== null && $this->district !== '';
    }




    
    public function isUserTypeComplete()
    {
        return $this->user_type !== null && $this->user_type !== '';
    }

    public function isLookingComplete()
    {
        return $this->looking_for !== null && $this->looking_for !== '';
    }

    public function isWhatsappNumberComplete()
    {
        return $this->whatsapp_number !== null && $this->whatsapp_number !== '';
    }

    public function isWAddharComplete()
    {
        return $this->aadhar_card_number !== null && $this->aadhar_card_number !== '';
    }


    public function profileCompletionPercentage()
    {
        $totalFields = 13;    // Total number of fields in the users table
        $completeFields = 0;

        // Check completeness of each field 
        $completeFields += $this->isFirstNameComplete() ? 1 : 0;
        $completeFields += $this->isLastNameComplete() ? 1 : 0;
        $completeFields += $this->isEmailComplete() ? 1 : 0;
        $completeFields += $this->isAvatarComplete() ? 1 : 0;
        $completeFields += $this->isPhoneNumberComplete() ? 1 : 0;
        $completeFields += $this->isDateOfBirthComplete() ? 1 : 0;
        $completeFields += $this->isGenderComplete() ? 1 : 0;
        $completeFields += $this->isStateComplete() ? 1 : 0;
        $completeFields += $this->isDistrictComplete() ? 1 : 0;
        $completeFields += $this->isUserTypeComplete() ? 1 : 0;
        $completeFields += $this->isLookingComplete() ? 1 : 0;
        $completeFields += $this->isWhatsappNumberComplete() ? 1 : 0;
        $completeFields += $this->isWAddharComplete() ? 1 : 0;


        if ($this->student) {
            $totalFields += 1; // Assuming you have 1 field in the students table
            $completeFields += $this->student->category() ? 1 : 0;
            // Add similar checks for other student fields
        }


        $completionPercentage = ($completeFields / $totalFields) * 100;

        $completionPercentage = intval($completionPercentage);

        return $completionPercentage;
    }

    public function draft()
    {
        return $this->hasOne(Draft::class, 'student_id', 'id');
    }
}
