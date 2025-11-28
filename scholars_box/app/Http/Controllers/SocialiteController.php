<?php

namespace App\Http\Controllers;

use App\Models\EducationDetail;
use App\Models\GuardianDetail;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmploymentDetail;
use App\Models\Role;
use Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SocialiteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        $socialuser = Socialite::driver('google')->stateless()->user();
   

        // Find the user based on their email or create a new one
        $user = User::firstOrNew(['email' => $socialuser->getEmail()]);

        // Update or set the user details
        $user->first_name = $socialuser->user['given_name'];
        $user->last_name = $socialuser->user['family_name']??'';
        $user->social_id = $socialuser->getId();
        $user->social_type = "GOOGLE";

        // Save the user's profile image
        $user->avatar = $socialuser->getAvatar(); // Get the avatar URL

        $user->role_id = Role::where('name', 'student')->value('id');

        // Save the user to the database (this will create or update as necessary)
        $user->save();

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = new Student([
                'user_id' => $user->id,
            ]);
        }

        $student->save();

        EmploymentDetail::firstOrNew([
            'student_id' => $student->id,
        ]);

        GuardianDetail::firstOrNew([
            'student_id' => $student->id,
        ]);

        // Login the user
        Auth::login($user);

        // Redirect based on whether the user was newly created or not
        return redirect(route('Student.dashboard'));
    }
}
