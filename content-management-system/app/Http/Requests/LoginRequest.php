<?php

namespace App\Http\Requests;

use App\Exceptions\EmailNotVerifiedException;
use App\Exceptions\PasswordNotSetException;
use App\Exceptions\PhoneNotVerifiedException;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // return [

        //     'email' => ['required', 'string', 'email'],
        //     'password' => ['required', 'string'],
        // ];

        return [
            'emailorusername'=>['required','string'],
            'password' => ['required','string'],
        ];
    }


    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate($onlyRoles = []): void
    {
    $emailorusername = $this->get('emailorusername');
 
        if (!empty($onlyRoles)) {
             $user = ($this->isEmail($emailorusername)) ? User::where("email", $this->emailorusername)->first():User::where("username", $this->emailorusername)->first();
            if ($user && !$user->hasRole($onlyRoles)) {
                throw ValidationException::withMessages([
                    'emailorusername' => __('auth.login_not_available'),

                ]);
            }
        }
            $this->ensureIsNotRateLimited();
            $credentials = $this->getCredentials();
            if (!Auth::attempt($credentials,$this->boolean('remember'))) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'emailorusername' => __('auth.invalid_credentials'),
                ]);
            }
            if ($user && !$user->email_verified_at) {
                throw new EmailNotVerifiedException(user: $user);
            }
            // if($this->isEmail($emailorusername))
            // {  
           
            // }
        RateLimiter::clear($this->throttleKey());
    }

    public function getCredentials()
    {
        $username = $this->get('emailorusername');
        if ($this->isEmail($username)) {
            return [
                'email' => $username,
                'password' => $this->get('password')
            ];
        }
        return [
            'username' => $username,
            'password' => $this->get('password')
        ];
    }

    private function isEmail($param)
    {
        $factory = $this->container->make(ValidationFactory::class);

        return ! $factory->make(
            ['username' => $param],
            ['username' => 'email']
        )->fails();
    }

    public function ensureIsNotRateLimited()
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }

   
}
