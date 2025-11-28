<?php 
  
namespace App\Http\Controllers\Auth; 
  
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use DB; 
use Carbon\Carbon; 
use App\Models\User; 
use Mail; 
use Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

  
class ForgotPasswordController extends Controller
{
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showForgetPasswordForm()
      {
         return view('student.forgot-password');
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitForgetPasswordForm(Request $request)
      {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'User Not Found !!. Please try again!', 'value'=> 0]);
            }
    
            // Generate a random token
            $token = Str::random(64);
    
            // Insert the token into the password_resets table
            DB::table('password_resets')->insert([
                'email' => $request->email, 
                'token' => $token, 
                'created_at' => Carbon::now()
            ]);
    
            // Send the password reset email
            Mail::send('email.forgetPassword', ['token' => $token], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Password Reset Request for Your ScholarsBox Account');
            });
    
            // Redirect back with success message
            return response()->json(['success' => true, 'message' => 'We have e-mailed your password reset link!','value'=> 1]);
        } catch (\Exception $e) {
            
            return back()->withErrors(['error' => 'An error occurred while processing your request. Please try again.']);
        }
      }
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showResetPasswordForm($token) { 
         return view('student.forgetPasswordLink', ['token' => $token]);
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitResetPasswordForm(Request $request)
      {
       
          $request->validate([
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
          ]);
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
  
          if(!$updatePassword){
            return response()->json(['success' => false, 'message' => 'Details Not match!','value'=> 0]);

          }
  
          $user = User::where('email', $request->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
  
          return response()->json(['success' => true, 'message' => 'Password Changed Sucessfully !!!','value'=> 1]);
      }
}