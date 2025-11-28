<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;
use App\Models\Partner;
use App\Models\Blog;
use App\Models\Banner;
use App\Models\User;
use App\Models\Microsite;
use App\Models\termcondition;


use App\Models\Scholarship\Scholarship;
use App\Models\CompanyDetails;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use App\Mail\NotifyMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use App\Models\JobRequest;
use App\Models\micrositeFaq;

use App\Models\JoinNow;


use App\Mail\WelcomeMail;




class CommonController extends Controller
{
    public function faq()
    {
        $faqs = Faq::get();
        return view('student.faq', compact('faqs'));
    }

    public function index()
    {
        $data = Partner::get();
        $blogs = Blog::orderBy('id','DESC')->get();
        $banners = Banner::get(); 
        return view('welcome', compact('data', 'blogs', 'banners'));
    }

    public function newsLetter()
    {
        return view('student.newsletter');
    }
    public function studyMaterial()
    {
        return view('student.study-material');
    }

    // public function microSite($subdomain)
    // {

    //     $comapnyName = User::where('company_name', $subdomain)->first();
    //     $companyForSignUp = $comapnyName->company_name;

 
    //     if (empty($comapnyName)) {
            
    //         return redirect()->route('Student.scholarship.index');
    //     }

    //     $data = CompanyDetails::where('company_name', $comapnyName->id)->first();
    //     // dd('asdfd',$comapnyName->id ,'dasdsadsa');
    //     if (!$data) {
    //         return redirect()->route('Student.scholarship.index');
    //     }
    //     $scholarships = Scholarship::where('status', 1)->where('company_id', $data->company_name)
    //         ->with([
    //             'scholarshipQuestionApplication',
    //             'scholarshipQuestionApplication.scholarshipOptionsApplications',
    //             'apply_now'
    //         ])
    //         ->get();
    //     return view('site', compact('data', 'scholarships','companyForSignUp'));
    // }

    public function term(){
        $data = termcondition::first();
        return view('sitesnew.tandc', compact('data'));

    }



    public function microSite($subdomain)
    {
        

        $comapnyName = User::where('company_name', $subdomain)->first();

	
	 $companyForSignUp = $comapnyName->company_name;
      
       if (empty($comapnyName)) {  

         
            
            return redirect()->route('Student.scholarship.index');  
        }
      
        if($comapnyName->company_name === 'hyundai'){   
            $data = Microsite::where('company_id', $comapnyName->id)->first();
          
           if($data == null){ 
            return redirect()->route('Student.scholarship.index');

           }
           
           $faqs =micrositeFaq::get();
            $scholarships = Scholarship::where('company_id', $data->company_id)
            ->with([
                'scholarshipQuestionApplication',
                'scholarshipQuestionApplication.scholarshipOptionsApplications',
                'apply_now'
            ])
            ->get();

        return view('sitesnew.newsite', compact('data', 'scholarships','companyForSignUp','faqs'));


        }else{
            $data = CompanyDetails::where('company_name', $comapnyName->id)->first();

            if (!$data) {
                return redirect()->route('Student.scholarship.index');
            }
            $scholarships = Scholarship::where('status', 1)->where('company_id', $data->company_name)
                ->with([
                    'scholarshipQuestionApplication',
                    'scholarshipQuestionApplication.scholarshipOptionsApplications',
                    'apply_now'
                ])
                ->get();
            return view('site', compact('data', 'scholarships','companyForSignUp'));

        }

        // dd('asdfd',$comapnyName->id ,'dasdsadsa');
       
    }

    public function googleCalnder()
    {
        $event = new Event;
        $event->name = 'My Scholorship';
        $event->description = 'Event description';
        $event->startDateTime = Carbon::now();
        $event->endDateTime = Carbon::now()->addHour();
        $event->save();
        return redirect()->route('Student.scholarship.index')->with('success', 'Event Added Sucessfully !!');
    }

    public function txt_mail()
    {
        $userEmail = 'ravijhalani4@gmail.com';
        Mail::to($userEmail)->send(new NotifyMail());
    }

    public function loginwithotp()
    {
        return view('fileotp');
    }

    public function loginwithotpmobile(Request $request)
    {
      
        
        $mobile_number = $request->mobileNmber;
        $user = User::where('phone_number', $mobile_number)->first();
        // dd($user);
      

        if ($user) {
            $otp = rand(100000, 999999);
            $request->session()->put('otp', $otp);
            $url = 'https://m1.sarv.com/api/v2.0/sms_campaign.php';

            
            $response = Http::get($url, [
                'token' => '112938154665d2e35b37cd21.85175120',
                'user_id' => '25848267',
                'route' => 'TR',
                'template_id' => '13627',
                'sender_id' => 'IVPLSB',
                'language' => 'EN',
                'template' => "$otp is your ScholarsBox verification code. The code is valid for 10 minutes.",
                'contact_numbers' => $mobile_number, 
            ]);

            // $user->remember_token = $otp;
            // $user->save();
            $d = view('verify_otp', compact('user'));

        } else {
            // dd('sadsa'); 
            // return redirect()->back()->with('warning', 'Mobile Number not found.');
            // return response()->json(['message' => 'Mobile Number not fou   nd!!','data'=> 1]);  
            // return redirect()->back()->with('Mobile Number Not Found');
            return redirect()->back()->with('message', 'Mobile number not found'); 
        }
        
        return $d;




        // Process $response as needed

    }

    public function verfiyotp(Request $request)
    {

        try {
            $otp = $request->oto;
            $storedOtp = $request->session()->get('otp');

            if ($otp == $storedOtp) {
                $user = User::where('phone_number', $request->input('mobile_numer'))->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();

                // OTP is valid, log in the user
                Auth::login($user); // Replace $user with the actual user instance


                // Clear the OTP from session
                $request->session()->forget('otp');

                // return view('student.dashboard', compact('user'));
            return redirect()->route('Student.dashboard');

            }

            // If OTP is invalid, throw a validation exception
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP. Please try again.'],
            ]);

        } catch (ValidationException $e) {
            // Handle validation exception
            
            return redirect()->route('Student.otp.login')->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            

            // Handle other exceptions if needed
            return redirect()->route('Student.otp.login')->with('error', 'An unexpected error occurred. Please try again.');
        }

    }

    public function sendWelcomeEmail()
    {
        $userEmail = 'rj14jhalani@gmail.com';

        try {
            Mail::to($userEmail)->send(new WelcomeMail());
            return "Welcome email sent successfully!";
        } catch (\Exception $e) {
            // Log or handle the exception appropriately
            return "Failed to send welcome email: " . $e->getMessage();
        }
    }

    public function searchBlog(Request $request)
    {


        $blogs = Blog::where('blog_title', 'like', '%' . $request->search . '%')->orwhere('teg', $request->search)->orderBy('id','DESC')->get(); 
   

        return view('student.blog', compact('blogs'));
    }
    // public function newslettermail(Request $request) 

    // {
    //     // dd($request->all());
    //     $usermail = JoinNow::where('email',$request->email)->first();

    //     if($usermail){
    //         return response()->json(['message' => 'You have already Subscribed  to our newsletter. Thank you for staying connected!!','data'=> 0]); 
    //     }
 
    //     $daat = new JoinNow();
    //     $daat->name = $request->name ?? '';
    //     $daat->email = $request->email;
    //     $daat->working_no = $request->working_no ?? '';
    //     $daat->alternative_no = $request->alternative_no??'';
    //     $daat->category = $request->category??'';
    //     $daat->subject = $request->subject ??'';
    //     $daat->message = $request->message ??'';
    //     $daat->type = 3;
       
      
    //    $daat->save();

    //     $userEmail = $request->email;
        
    //     $mailContent = 'newsletter';
        
    //     $mailSubject = 'Thank you for Subscribing to ScholarsBox!' ;
        
    //     try {
    //         Mail::to($userEmail)->send(new WelcomeMail(['content' => $mailContent, 'subject' => $mailSubject]));
    //         return response()->json(['message' => 'You have subscribed to our newsletter. Thank you for staying connected!!']);
    //     } catch (\Exception $e) {
           
    //         return "Failed to send welcome email: " . $e->getMessage();
    //     }
    // }

    public function newslettermail(Request $request) 
{
    // Validation for Gmail domain emails
    $rules = [
        'email' => [
            'required',
            'email',
            function ($attribute, $value, $fail) {
                if (!preg_match('/@gmail\.com$/', $value)) {
                    $fail('The ' . $attribute . ' must be a Gmail address.');
                }
            },
        ],
        'name' => 'nullable|string',
        'working_no' => 'nullable|string',
        'alternative_no' => 'nullable|string',
        'category' => 'nullable|string',
        'subject' => 'nullable|string',
        'message' => 'nullable|string',
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Check if the email is already subscribed
    $usermail = JoinNow::where('email', $request->email)->first();

    if ($usermail) {
        return response()->json(['message' => 'You have already subscribed to our newsletter. Thank you for staying connected!', 'data' => 0]); 
    }

    // Create a new subscription
    $daat = new JoinNow();
    $daat->name = $request->name ?? '';
    $daat->email = $request->email;
    $daat->working_no = $request->working_no ?? '';
    $daat->alternative_no = $request->alternative_no ?? '';
    $daat->category = $request->category ?? '';
    $daat->subject = $request->subject ?? '';
    $daat->message = $request->message ?? '';
    $daat->type = 3;
    $daat->save();

    // Send a welcome email
    $userEmail = $request->email;
    $mailContent = 'newsletter';
    $mailSubject = 'Thank you for Subscribing to ScholarsBox!';

    try {
        Mail::to($userEmail)->send(new WelcomeMail(['content' => $mailContent, 'subject' => $mailSubject]));
        return response()->json(['message' => 'You have subscribed to our newsletter. Thank you for staying connected!']);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to send welcome email: ' . $e->getMessage()], 500);
    }
}


    public function contactusmail(Request $request){

        $usermail = JoinNow::where('email',$request->email)->first();

      
 
        $daat = new JoinNow();
        $daat->name = $request->name ?? '';
        $daat->email = $request->email;
        $daat->working_no = $request->working_no ?? '';
        $daat->alternative_no = $request->alternative_no??'';
        $daat->category = $request->category??'';
        $daat->subject = $request->subject ??'';
        $daat->message = $request->message ??'';
        $daat->type = 2;
       
      
       $daat->save();

        $userEmail = $request->email;
        
        $mailContent = 'contact us';
        
        $mailSubject = 'Thank you forom ScholarsBox!' ;
        
        try {
            Mail::to($userEmail)->send(new WelcomeMail(['content' => $mailContent, 'subject' => $mailSubject]));
            return response()->json(['message' => 'Your Information Saved Sucessfully !!']);
        } catch (\Exception $e) {
           
            return "Failed to send welcome email: " . $e->getMessage();
        }
    }
    

    public function savejoinnow(Request $request){

        
        $daat = new JoinNow();
        $daat->name = $request->FirstName;
        $daat->email = $request->email;
        $daat->working_no = $request->PhoneNo;
        $daat->alternative_no = $request->AlternateNo??'';
        $daat->category = $request->category;
        $daat->subject = $request->subject;
        $daat->message = $request->message;
        $daat->type = 1;
        if ($request->hasFile('resume')) {
            $image = $request->file('resume');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName); // Save the image to the 'uploads' directory
            $daat->resume = $imageName;
        }
      
       $daat->save();
       $userEmail = $request->email;
       $mail_content = 'You Information saved sucessfully !! Our team member will contact you soon..';
       $mailSubject = 'Welcome to ScholarsBox!' ;
       try {
        //    Mail::to($userEmail)->send(new WelcomeMail(['content' => $mail_content]));
           Mail::to($userEmail)->send(new WelcomeMail(['content' => $mail_content, 'subject' => $mailSubject]));

           return response()->json(['message' => 'Email sent successfully']);
       } catch (\Exception $e) {
           // Log or handle the exception appropriately
           return "Failed to send welcome email: " . $e->getMessage();
       }
        return response()->json(['message' => 'Your Information Saved Sucessfully !!']);

    }


    public function searchdata(Request $request)
{
    // $searchTerm = $request->input('search');
    // $directory = resource_path('views/student');
    // $results = $this->searchInDirectory($directory, $searchTerm);

    $searchTerm = $request->input('search');

    $data = Scholarship::where('status', 1)
    ->where(function ($query) use ($searchTerm) {
        $query->where('scholarship_name', 'like', '%' . $searchTerm . '%')
              ->orWhere('short_desc', 'like', '%' . $searchTerm . '%')
              ->orWhere('scholarship_info', 'like', '%' . $searchTerm . '%')
              ->orWhere('sponsor_info', 'like', '%' . $searchTerm . '%')
              ->orWhere('who_can_apply_info', 'like', '%' . $searchTerm . '%')
              ->orWhere('how_to_apply_info', 'like', '%' . $searchTerm . '%')
              ->orWhere('faqs', 'like', '%' . $searchTerm . '%');
    })
    ->get();


    // return view('search', compact('results', 'searchTerm','data'));
    return view('search', compact('data','searchTerm'));
}

private function searchInDirectory($directory, $searchTerm)
{
    $results = [];
    $found = false;

    $files = File::allFiles($directory);

    foreach ($files as $file) {
        if ($found) break; 
        $content = file_get_contents($file->getPathname());

        
        $dom = new \DOMDocument();
        @$dom->loadHTML($content); 

        
        $tagsToSearch = ['p', 'h3']; 

        foreach ($tagsToSearch as $tag) {
            if ($found) break 2;
            $elements = $dom->getElementsByTagName($tag);

            foreach ($elements as $element) {
                
                if (stripos($element->nodeValue, $searchTerm) !== false) {
                    $results[] = [
                        'filename' => $file->getRelativePathname(),
                        'url' => route('Student.', str_replace('.blade.php', '', $file->getRelativePathname())),
                        'content' => $content,
                    ];
                    $found = true;
                    break 3;
                }
            }
        }

      
        preg_match_all("/\{\{\s*\$(\w+)\s*\}\}/", $content, $matches); 
        foreach ($matches[1] as $variableName) {
            if ($found) break 2; 
           
            if (stripos($content, "{{\$$variableName}}") !== false) {
                
                $variableValue = ''; 
                
                if (isset($blog) && property_exists($blog, $variableName)) {
                    $variableValue = $blog->$variableName;
                }
               
                if (stripos($variableValue, $searchTerm) !== false) {
                    $results[] = [
                        'filename' => $file->getRelativePathname(),
                        'url' => route('Student.', str_replace('.blade.php', '', $file->getRelativePathname())),
                        'content' => $variableValue,
                    ];
                    $found = true;
                    break 2; 
                }
            }
        }
    }

    return $results;
}



public function saveposition(Request $request){

    $saveposition = new JobRequest();
    $saveposition->first_name = $request->name ??'';
    $saveposition->last_name = $request->email ??'';
    $saveposition->filed1 = $request->working_no ??'';
    $saveposition->category = $request->category ??'';
    $saveposition->position = $request->position ??'';
    $saveposition->message = $request->message ??'';


    if ($request->hasFile('resume')) {
        $image = $request->file('resume');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads'), $imageName); // Save the image to the 'uploads' directory
    }

    $saveposition->filed2 = $imageName ??'';
    
    $saveposition->save();
    $mailSubject = 'Acknowledgement of Your Job Application';
    $mail_content = 'Position';
    Mail::to($request->email)->send(new WelcomeMail(['content' => $mail_content, 'subject' => $mailSubject]));

    return redirect()->back()->with('message', 'Applied Sucessfully !!');

}


}

    

