<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\{User,Login};
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Rules\{GEmail};
use Route;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        die();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return redirect()->back();
        die();
        $request = [
			'name' => ['required','string','max:255'],
            'email' => ['required','email','unique:users','max:255', new GEmail],
			'password' => ['required','min:8'],
		];
		$rules = [
			'name.required' => 'The name field is required.',
			'name.string' => 'The name must be a string.',
			'name.max' => 'The name may not be greater than :max characters.',
			'email.required' => 'The email field is required.',
			'email.email' => 'Please enter a valid email address.',
			'email.unique' => 'This email is already taken.',
			'email.max' => 'The email may not be greater than :max characters.',
			'password.required' => 'The password field is required.',
			'password.min' => 'The password must be at least :min characters.',
		];
		$validator = Validator::make($data , $request, $rules);
		if ($validator->fails()) {	
			$return = New User;
		}else{
            DB::beginTransaction();
            try
            {
                $create = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                ];
                $return = User::create($create);
                $insert = [
                    'user_id'=>$return->id,
                    'email'=>$data['email'],
                    'password'=>'',
                ];
                Login::create($insert);
                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
            // User::whereId(1)->update(['role'=>'1']);
		}
        return $return;
    }
}

