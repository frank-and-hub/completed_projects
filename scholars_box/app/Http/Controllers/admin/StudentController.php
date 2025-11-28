<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ScholarshipApplication\ScholarshipApplication;
use DB;
use App\Exports\ExportUsers;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\JoinNow;
use App\Models\Scholarship\Scholarship;
use App\Mail\studentMail;
use App\Models\Student;
use App\Models\GuardianDetail;
use App\Models\EducationDetail;
use Illuminate\Support\Facades\Mail;
use App\Models\Assesment;
use App\Models\AmountDistribution;
use App\Models\Resource;
use App\Models\Scholarshipstatus;
use Carbon\Carbon;



use Illuminate\Support\Facades\Log; // Add this line to import the Log facade
use App\Models\Webinar;

use Validator;





use App\Exports\UsersExport;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::
            // ->join('scholarship_applications', 'users.id', '=', 'scholarship_applications.user_id')
            // ->join('scholarships', 'scholarship_applications.scholarship_id', '=', 'scholarships.id')
            // ->when(auth()->user()->role_id == '3',function($q){
            //     $q->where('scholarships.company_id',auth()->user()->id);
            // })
            with('draft')
            ->where('role_id', '=', 2)
            // ->where('users.role_id', '=', 2)
            ->orderBy('id', 'desc')
            ->select('first_name','last_name','email','phone_number','created_at','microsite','site_name','id')

            ->get();

     
        $scholarshipid = '';
        $scholarships = Scholarship::get();




        return view('admin.student.list', compact('students', 'scholarships', 'scholarshipid'));
    }
    public function studentsFilter(Request $request)
    {

        $scholarshipid = '';
        if (isset($request->scholarship_name) && $request->award == 'on') {
            // $data = ScholarshipApplication::with('user')->where('scholarship_id',$request->scholarship_name)->get();

            // $data = AmountDistribution::with('user')->where('scholarship_id', $request->scholarship_name)->get();
            $data = AmountDistribution::with(['user' => function($query) {
                $query->select('id', 'email')->distinct();
            }])
            ->where('scholarship_id', $request->scholarship_name)
            ->get()
            ->unique('user.email');
        
            $scholarshipid = $request->scholarship_name;
            $scholarships = Scholarship::get();
        } elseif (!isset($request->award) && $request->scholarship_name != null) {
            // $data = ScholarshipApplication::with('user')->where('scholarship_id', $request->scholarship_name)->get();
            $data = ScholarshipApplication::with('user')
            ->where('scholarship_id', $request->scholarship_name)
            ->get()
            ->unique(function ($item) {
                return $item->user->email; // Ensure unique emails
            });
            $scholarshipid = $request->scholarship_name;


            $scholarships = Scholarship::get();
        } elseif ($request->award == 'on' && $request->scholarship_name == null) {
            // $data = AmountDistribution::with('user')->get();

            $data = AmountDistribution::with('user')
    ->get()
    ->unique(function ($item) {
        return $item->user->email; // Ensure unique emails
    });
            $scholarships = Scholarship::get();


        }

        if (isset($request->search)) {
            $names = explode(' ', $request->input('search'));

            // $data = ScholarshipApplication::whereHas('user', function ($query) use ($names) {
            //     $query->whereIn('first_name', $names);
            // })->get();
            $data = ScholarshipApplication::whereHas('user', function ($query) use ($names) {
                $query->whereIn('first_name', $names);
            })->with('user')->get();
            
            // Ensure unique user emails
            $data = $data->unique(function ($item) {
                return $item->user->email; // Ensure unique emails
            });
            $scholarships = Scholarship::get();

            // $data now contains all ScholarshipApplication records where the first_name of the related user matches any of the names in the $names array.
        }


        


        return view('admin.student.list', compact('data', 'scholarships', 'scholarshipid'));



    }
    public function delete($id)
    {
        DB::table('users')->where('id', $id)->delete();
        DB::table('students')->where('user_id', $id)->delete();
        $DB = DB::table('scholarship_applications')->where('user_id', $id)->delete();
        if ($DB == 1) {
            return back()->with('success', 'User deletd successfully ');
        } else {
            return back()->with('error', 'Something went wrong !');

        }
    }

    public function importExport()
    {
        return view('import');
    }

    public function export()
    {
        return Excel::download(new ExportUsers, 'users.xlsx');
    }

    public function import()
    {

        try {
            Excel::import(new UsersImport, request()->file('file'));
            return redirect()->back()->with('success', 'Your data imported successfully!');
        } catch (\Exception $e) {
            // Log or handle the exception appropriately
            return redirect()->back()->with('error', $e->getMessage());

        }


        return back();
    }

    public function request(Request $request)
    {
        $students = JoinNow::orderBy('id', 'ASC')->get(); 


       
        return view('admin.allrequests.listing', compact('students'));
    }

    public function requestDelete(Request $request){
        JoinNow::truncate();

    }

    

    public function requestfilter(Request $request)
    { 
      
        $students = JoinNow::where('type', $request->filtervalue)->get();

        return view('admin.allrequests.listing', compact('students'));

    }

    public function requestDetails($id)
    {
        $students = JoinNow::find($id);

       
        return view('admin.allrequests.details', compact('students'));
    }
    


    public function sendemailstudent(Request $request)
    {

        $studentIds = str_replace(['[', ']', ' '], '', $request->input('student_ids'));
        $userIds = explode(',', $studentIds);


        // Loop through user_ids and save data to the database
        foreach ($userIds as $userId) {

            if (User::where('id', $userId)->exists()) {
                $savedata = User::where('id', $userId)->first();
                $userEmail = $savedata->email;

                $mailContent = $request->who_can_apply_info; // Example content
                $mailSubject = $request->subject; // Example subject
                $mailData = ['content' => $mailContent, 'subject' => $mailSubject];

                // Debug output to check which user is being processed
                Log::info("Sending email to user ID: $userId, Email: $userEmail");

                // Attempt to send email
                Mail::to($userEmail)->send(new studentMail($mailData));
            } else {
                // Handle case where user doesn't exist
                // This could be logged or further action could be taken
                // For example, skipping this user or showing a warning
            }
        }

        return response()->json(['message' => 'Data saved successfully']);
    }

    public function assesmentSend(Request $request)
    {



        // Retrieve the user_ids as an array
        $studentIds = str_replace(['[', ']', ' '], '', $request->input('student_ids'));
        $userIds = explode(',', $studentIds);

        // Loop through user_ids and save data to the database
        foreach ($userIds as $userId) {
            // Ensure user exists before creating notification
            if (User::where('id', $userId)->exists()) {

                $savedata = new Assesment();
                $savedata->users_id = $userId;
                $savedata->links = $request->link;
                $savedata->button_name = $request->title;

                $savedata->save();


            } else {

                // Handle case where user doesn't exist
                // This could be logged or further action could be taken
                // For example, skipping this user or showing a warning
                // You might want to remove this else block if all users are expected to exist
            }


        }
        return response()->json(['message' => 'Data saved successfully']);

    }

    public function sendResourse(Request $request)
    {

 

        try {
            $studentIds = str_replace(['[', ']', ' '], '', $request->input('student_ids'));
        $userIds = explode(',', $studentIds);


            $file = $request->file('document');

            if ($file) {
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('receipts'), $fileName);
                $filenames = $fileName;

            }

            // Loop through user_ids and save data to the database
            foreach ($userIds as $userId) {
                // Ensure user exists before creating notification
                if (User::where('id', $userId)->exists()) {
                    $savedata = new Resource();
                    $savedata->user_id = $userId;
                    $savedata->scholarship_id = $request->scholarship_name; // assuming the scholarship_id is what you want to save, not the name
                    $savedata->doc_name = $request->document_name; 
                    $savedata->resource = $filenames;
                    $savedata->save();
                } else {
                    // Handle case where user doesn't exist
                    // This could be logged or further action could be taken
                    // For example, skipping this user or showing a warning
                    // You might want to remove this else block if all users are expected to exist
                    // Log::warning("User with ID $userId does not exist.");
                }
            }

            return response()->json(['message' => 'Data saved successfully']);
        } catch (\Exception $e) {
            // Log the exception message
            \Log::error('Error saving resource: ' . $e->getMessage());

            // Return a JSON response with an error message
            return response()->json(['error' => 'An errocxzcr occurred while saving data. Please try again later.'], 500);
        }
    }


    public function updatemultiStatus(Request $request)
    {
        $userIds = explode(',', $request->input('student_ids'));


 

        // Loop through user_ids and save data to the database
        foreach ($userIds as $userId) {
            // Ensure user exists before creating notification
            if (User::where('id', $userId)->exists()) {

                // $savedata = User::where('id', $userId)->first();

                // $savedata = new ScholarshipApplication();
                //      $savedata->user_id = $userId;
                //      $savedata->scholarship_id = $request->scholarship_name;
                //      $savedata->descrition = $request->description;
                //      $savedata->status = $request->application_status;


                //      $savedata->save();
                // dd($request->all());
                $scholarshipStatus = ScholarshipApplication::where('scholarship_id', $request->scholarship_name)->where('user_id', $userId)->first();
                // $scholarshipStatus = new ScholarshipApplication();
        
                $scholarshipStatus->status = $request->application_status;
                $scholarshipStatus->descrition = $request->description;
                $scholarshipStatus->scholarship_id = $request->scholarship_name;
                $scholarshipStatus->user_id = $userId;
                $scholarshipStatus->button = $request->title;
                $scholarshipStatus->link = $request->link;
                $scholarshipStatus->extra1 = $request->extra1;
                $scholarshipStatus->update();

                $scholarshipStatusNew = new Scholarshipstatus();
                $scholarshipStatusNew->scholarship_application_id = $scholarshipStatus->id;
                $scholarshipStatusNew->status = $request->application_status;
                $scholarshipStatusNew->descss = $request->description;
                $scholarshipStatusNew->button = $request->title;
                $scholarshipStatusNew->link = $request->link;
                $scholarshipStatusNew->extra1 = $request->extra1;
                $scholarshipStatusNew->save();


                $recentIds = Scholarshipstatus::select(DB::raw('MAX(id) as id'))
                ->groupBy('scholarship_application_id')
                ->pluck('id')
                ->toArray();
        
            // Update the 'button' column to be empty for all records except those with the recent IDs
            Scholarshipstatus::whereNotIn('id', $recentIds)
            ->update(['button' => null,'extra1'=> null]);



            } else {

                // Handle case where user doesn't exist
                // This could be logged or further action could be taken
                // For example, skipping this user or showing a warning
                // You might want to remove this else block if all users are expected to exist
            }
        }

        return response()->json(['message' => 'Data saved successfully']);
    }


    public function addMultipleWebinars(Request $request)
    {
   
        $studentIds = str_replace(['[', ']', ' '], '', $request->input('student_ids'));
        $userIds = explode(',', $studentIds);

     

        // Loop through user_ids and save data to the database
        foreach ($userIds as $userId) {
            // Ensure user exists before creating notification
            if (User::where('id', $userId)->exists()) {

                // $savedata = User::where('id', $userId)->first();

                // $savedata = new ScholarshipApplication();
                //      $savedata->user_id = $userId;
                //      $savedata->scholarship_id = $request->scholarship_name;
                //      $savedata->descrition = $request->description;
                //      $savedata->status = $request->application_status;


                //      $savedata->save();
                // dd($request->all());
                $webinar = new Webinar();
                // $scholarshipStatus = new ScholarshipApplication();
        
                $webinar->title = $request->title;
                $webinar->sch_id = $request->scholarship_name;
                $webinar->assignBy = $request->assignBy;
                $webinar->title2 = $request->title2;
                $webinar->date = $request->date;
                $webinar->link = $request->link;
                $webinar->start_time = $request->start_time;
                $webinar->end_Time = $request->end_Time;
                $webinar->student_id = $userId;
                $webinar->extra1 = $request->button_title;
                $webinar->save();




              
        
            // Update the 'button' column to be empty for all records except those with the recent IDs
           



            } else {

                // Handle case where user doesn't exist
                // This could be logged or further action could be taken
                // For example, skipping this user or showing a warning
                // You might want to remove this else block if all users are expected to exist
            }
        }

        return response()->json(['message' => 'Data saved successfully']);
    }

    

    public function deletemultipleApplicants(Request $request)
    {
        $studentIds = str_replace(['[', ']', ' '], '', $request->input('student_ids'));
        $userIds = explode(',', $studentIds);
        $successCount = 0;
        $failureCount = 0;
    
        foreach ($userIds as $userId) {
            // Ensure user exists before attempting to delete
            if (User::where('id', $userId)->exists()) {
                
                
                $deleted = DB::table('scholarship_applications')->where('user_id', $userId)->where('scholarship_id',$request->scholarship_id)->delete();
    
                if ($deleted == 1) {
                    $successCount++;
                } else {
                    $failureCount++;
                }
            } else {
                $failureCount++;
            }
        }
    
        if ($successCount > 0 && $failureCount == 0) {
            return back()->with('success', 'All users deleted successfully.');
        } elseif ($successCount > 0 && $failureCount > 0) {
            return back()->with('warning', "$successCount users deleted successfully, but $failureCount users could not be deleted.");
        } else {
            return back()->with('error', 'Something went wrong, no users were deleted.');
        }
    }


    public function importTest(Request $request)
    {
        try {
            // Validate the uploaded file (you can add validation if needed)
    
            // Handle the file upload
            $file = $request->file('csv_file');
            $filePath = $file->getRealPath();
    
            // Open and read the file
            $file = fopen($filePath, 'r');
            $header = fgetcsv($file);
    
            // Loop through the file and update data in the database
            while ($row = fgetcsv($file)) {
                $data = array_combine($header, $row);
    
                // Fetch scholarship and user IDs
                $sch_id = Scholarship::where('scholarship_name', $data['Scholarship Name'])->value('id');
                $user_id = User::where('email', $data['User Email'])->value('id');
    
                if ($sch_id && $user_id) {
                    // Update existing scholarship application status
                    $scholarshipStatus = ScholarshipApplication::where('scholarship_id', $sch_id)
                        ->where('user_id', $user_id)
                        ->first(); // Use first() to get the model instance
    
                    if ($scholarshipStatus) {
                        $scholarshipStatus->update([
                            'status' => $data['Status'],
                            'description' => $data['Description'], // Fixed typo here from 'descrition' to 'description'
                            'button' => $data['Button Name'],
                            'link' => $data['Link'],
                        ]);
    
                        // Now $scholarshipStatus->id should be accessible
                        $scholarshipStatusId = $scholarshipStatus->id;
    
                        // Create new scholarship status entry
                        $scholarshipStatusNew = new Scholarshipstatus();
                        $scholarshipStatusNew->scholarship_application_id = $scholarshipStatusId;
                        $scholarshipStatusNew->status = $data['Status'];
                        $scholarshipStatusNew->descss = $data['Description']; // Fixed typo here from 'descss' to 'description'
                        $scholarshipStatusNew->button = $data['Button Name'];
                        $scholarshipStatusNew->link = $data['Link'];
                        $scholarshipStatusNew->save();
                    } else {
            return back()->with('error', 'Scholarship or User ID not found for Scholarship Name');

                        // Handle case where ScholarshipApplication was not found
                        // Possibly throw an exception or handle error accordingly
                        throw new \Exception("Scholarship Application not found for Scholarship ID: $sch_id and User ID: $user_id");
                    }
    
                    // Update the 'button' column to be empty for all records except those with the recent IDs
                    $recentIds = Scholarshipstatus::select(DB::raw('MAX(id) as id'))
                        ->groupBy('scholarship_application_id')
                        ->pluck('id')
                        ->toArray();
    
                    Scholarshipstatus::whereNotIn('id', $recentIds)
                        ->update(['button' => null,'extra1'=> null]);
                } else {

            return back()->with('error', 'Scholarship or User ID not found for Scholarship Name');

                    // Handle case where scholarship or user ID was not found
                    // You can log an error or handle this case based on your application's logic
                    throw new \Exception("Scholarship or User ID not found for Scholarship Name: {$data['Scholarship Name']} and User Email: {$data['User Email']}");
                }
            }
    
            fclose($file);
    
            return back()->with('success', 'CSV data updated successfully.');
        } catch (\Exception $e) {
            // Log the exception or handle it as per your application's error handling strategy
            return back()->with('error', $e->getMessage());
            
        }
    }

    public function importdatacsv(Request $request)
    {
        try {
            // Handle the file upload
            $file = $request->file('csvdata_file');
            $filePath = $file->getRealPath();
    
            // Open and read the file into an array
            $file = fopen($filePath, 'r');
            $header = fgetcsv($file); // Get the CSV headers
            $rows = [];
            
            // Collect all rows from the CSV
            while ($row = fgetcsv($file)) {
                $rows[] = array_combine($header, $row); // Combine header with row values
            }
            fclose($file);
    
            // Iterate through the CSV data using foreach
            foreach ($rows as $data) {
             
                // Create new user (scholarship applicant)
                $scholarshipApplicant = new User();
                $scholarshipApplicant->role_id = 2;
                $scholarshipApplicant->first_name = $data['first_name'];
                $scholarshipApplicant->last_name = $data['last_name'];
                $scholarshipApplicant->email = $data['email'];
                $scholarshipApplicant->phone_number = $data['phone_number'];
                $scholarshipApplicant->date_of_birth = Carbon::createFromFormat('d-m-Y', $data['date_of_birth'])->format('Y-m-d');
                $scholarshipApplicant->gender = $data['gender'];
                $scholarshipApplicant->state = $data['state'];
                $scholarshipApplicant->password = bcrypt($data['password']); // Hash the password
                $scholarshipApplicant->save();
  
                // Create student record linked to the user
                $studentApplicant = new Student();
                $studentApplicant->user_id = $scholarshipApplicant->id; // Link to user
                $studentApplicant->is_minority = $data['is_minority'];
                $studentApplicant->category = $data['Category'];
                $studentApplicant->minority_group = $data['minority_group'];
                $studentApplicant->is_pwd_category = $data['is_pwd_category'];
                $studentApplicant->pwd_percentage = $data['pwd_percentage'];
                $studentApplicant->is_army_veteran_category = $data['is_army_veteran_category'];
                $studentApplicant->save();
    
                // Create guardian detail linked to the user
                $guardApplicant = new GuardianDetail();
                $guardApplicant->student_id = $studentApplicant->id; // Correct ID
                $guardApplicant->occupation = $data['Guardian Occupation'];
                $guardApplicant->annual_income = $data['Family Annual Income'];
                $guardApplicant->save();
    
                // Create education detail linked to the user
                $educationDetail = new EducationDetail();
                $educationDetail->student_id = $studentApplicant->id; // Correct ID
                $educationDetail->level = '10th';
                $educationDetail->grade = $data['10th Percentage'];
                $educationDetail->save();
   
                // // Fetch scholarship by name
                $scholarshipDetail = Scholarship::where('scholarship_name', $data['scholarship name'])->first();
    
                if ($scholarshipDetail) {
                    // Create a new scholarship application
                    $savescho = new ScholarshipApplication();
                    $savescho->user_id = $scholarshipApplicant->id; // Link to user
                    $savescho->scholarship_id = $scholarshipDetail->id;
                    $savescho->status = 'application_submitted';
                    $savescho->applied_at = now(); // Set current date and time
                    $savescho->save();
                } else {
                    return back()->with('error', 'Scholarship not found for the name ' . $data['scholarship name']);
                }
            }
    
            return back()->with('success', 'CSV data updated successfully.');
        } catch (\Exception $e) {

            dd($e->getMessage(),$e->getLine());
            // Handle errors and log them
            return back()->with('error', $e->getMessage());
        }
    }
    
    
    
    public function deleteResourse(){
        Resource::truncate();
    }
    
}