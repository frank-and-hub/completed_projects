<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AddressDetail;
use App\Models\Document;
use App\Models\EducationDetail;
use App\Models\EmploymentDetail;
use App\Models\GuardianDetail;
use App\Mail\studentMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Student;
use App\Models\User;
use App\Models\Scholarship\Scholarship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Webinar;
use App\Models\AmountDistribution;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Blog;
use App\Models\AboutUs;
use App\Models\SaveScholorship;
use Illuminate\Support\Facades\Storage;
use App\Models\ContactUs;
use App\Models\Contact;
use App\Models\Event;
use DB;
use App\Models\ScholarshipApplication\ScholarshipApplication;
use App\Models\CmsPage;
use App\Models\Notification;
use App\Models\Study;
use App\Models\Resource;
use App\Models\Assesment; 
use App\Models\JoinNow; 






class StudentController extends Controller
{
    public function showDashboard(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $suggested_scholoarships = Scholarship::whereIn('id',[56,57,54])->where('status', '!=', 0)->get();
            return view('student.dashboard', compact('user', 'suggested_scholoarships'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function showDashboardredirect() {
        return view('student.redirection');

    }

    public function showRegisterredirect(){
       
        return view('student.registerRedirection');

    }

    public function showOTPredirect(){
        return view('student.otpRedirection');

    }

    public function doUserPersonalDetailUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:users,email,' . auth()->id(),
            'phone_number' => 'unique:users,phone_number,' . auth()->id(),
            // Add validation rules for other fields
        ], [
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'The email address is already in use.',
            'phone_number.unique' => 'The phone number is already in use.',
            // Add custom error messages for other fields
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Get the authenticated user
        $user = Auth::user();
    
        $user = User::find($user->id);
    
        if (!$user) {
            return response()->json(['errors' => ['user' => 'User not found']], 404);
        }
    
        DB::beginTransaction();
        try {
            // Update user's personal details
            $phoneNumberUpdated = $request->filled('phone_number') && $request->phone_number !== $user->phone_number;
    
            $user->first_name = $request->filled('first_name') ? $request->first_name : $user->first_name;
            $user->last_name = $request->filled('last_name') ? $request->last_name : $user->last_name;
            $user->email = $request->filled('email') ? $request->email : $user->email;
            $user->phone_number = $request->filled('phone_number') ? $request->phone_number : $user->phone_number;
            $user->date_of_birth = $request->filled('date_of_birth') ? $request->date_of_birth : $user->date_of_birth;
            $user->whatsapp_number = $request->filled('whatsapp_number') ? $request->whatsapp_number : $user->whatsapp_number;
            $user->gender = $request->filled('gender') ? $request->gender : $user->gender;
            $user->aadhar_card_number = $request->filled('aadhar_card_number') ? $request->aadhar_card_number : $user->aadhar_card_number;
            $user->user_type = $request->filled('user_type') ? $request->user_type : $user->user_type;
            $user->looking_for = $request->filled('looking_for') ? $request->looking_for : $user->looking_for;
            // Update other fields as needed
    
            if ($phoneNumberUpdated) {
                $user->mobile_verified = 500; // Assuming 0 means unverified
            }
    
            $user->save();
    
            $studentData = [
                'is_minority' => (isset($request->is_minority) && ($request->is_minority == 'on')) ? 1 : 0,
                'minority_group' => (isset($request->is_minority) && ($request->is_minority == 'on')) ? $request->input('minority_group', null) : null,
                'category' => $request->input('category', null),
                'other_reservation' => $request->input('other_reservation', null),
                'is_pwd_category' => $request->has('is_pwd_category') ? 1 : 0,
                'pwd_percentage' => $request->input('pwd_percentage', null),
                'is_army_veteran_category' => $request->has('is_army_veteran_category') ? 1 : 0,
                'army_veteran_data' => $request->input('army_veteran_data', null),
            ];
    
            $student = Student::where('user_id', $user->id)->first();
    
            if (!$student) {
                $student = new Student([
                    'user_id' => $user->id,
                ]);
            }
    
            $student->fill($studentData);
            $student->save();
    
            $msg = ['success' => 'User personal details updated successfully'];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $msg = ['error' => 'User personal details not updated yet!'];
            dd($e->getLine(), $e->getMessage());
        }
    
        return response()->json($msg);
    }
    

    public function updateEducationDetail(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'occupation' => 'string',
            'level' => 'nullable|string',
            'is_education_pursuing' => 'nullable|string',
            'education_institute' => 'nullable|string',
            'education_institute_other' => 'nullable|string',
            'education_institute_type' => 'nullable|string',
            'education_institute_district' => 'nullable|string',
            'state_id' => 'nullable|string',
            'education_course_name' => 'nullable|string',
            'education_course_other' => 'nullable|string',
            'education_specialisation' => 'nullable|string',
            'education_grade_type' => 'nullable|string',
            'education_grade' => 'nullable|string',
            'education_start_date' => 'nullable|date',
            'education_end_date' => 'nullable|date',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Fetch or create the Student model
        $student = Student::firstOrNew([
            'user_id' => $user->id,
        ]);

        // Update occupation in the Student model
        $student->occupation = $request->occupation ?? null;
        $student->save();

        // Prepare the education details data
        $educationDetailData = [
            'level' => $request->input('level'),
            'other_level' => $request->input('other_level'),
            'institute_name' => $request->input('education_institute'),
            'education_institute_other' => $request->input('education_institute_other'),
            'institute_type' => $request->input('education_institute_type'),
            'district' => $request->input('education_institute_district'),
            'state' => $request->input('state_id'),
            'course_name' => $request->input('education_course_name'),
            'education_course_other' => $request->input('education_course_other'),
            'specialisation' => $request->input('education_specialisation'),
            'grade_type' => $request->input('education_grade_type'),
            'grade' => $request->input('education_grade'),
            'start_date' => $request->input('education_start_date'),
            'end_date' => $request->input('education_end_date'),
            'pursuing' => $request->is_education_pursuing ? 1 : 0,
        ];
        // Fetch or create the EducationDetail model
        $educationDetail = EducationDetail::firstOrNew([
            'student_id' => $student->id,
            'level' => $request->input('level'),
        ]);
        // dd($educationDetailData);
        // Update the EducationDetail model
        $educationDetail->fill($educationDetailData);
        $educationDetail->save();

        // Send the success response
        return response()->json(['message' => 'User education details updated successfully']);
    }

    public function deleteEducationDetailByID(Request $request, $id)
    {
        $user = Auth::user();

        // Fetch or create the Student model
        $student = Student::firstOrNew([
            'user_id' => $user->id,
        ]);

        $student->occupation = $request->input('occupation', null);
        $student->save();


        // Find the existing EducationDetail model by ID
        $educationDetail = EducationDetail::find($id);
        if (!$educationDetail) {
            return response()->json(['message' => 'Education detail not found'], 404);
        }

        // Authorization check: Ensure the education detail belongs to the authenticated user
        if ($educationDetail->student_id != $student->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $educationDetail->delete();

        return response()->json(['message' => 'User education details deleted successfully']);
    }
    public function updateEducationDetailByID(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'occupation' => 'nullable|string',
            'level' => 'nullable|string',
            'is_education_pursuing' => 'nullable|string',
            'education_institute' => 'nullable|string',
            'education_institute_other' => 'nullable|string',
            'education_institute_type' => 'nullable|string',
            'education_institute_district' => 'nullable|string',
            'state_id' => 'nullable|string',
            'education_course_name' => 'nullable|string',
            'education_course_other' => 'nullable|string',
            'education_specialisation' => 'nullable|string',
            'education_grade_type' => 'nullable|string',
            'education_grade' => 'nullable|string',
            'education_start_date' => 'nullable|date',
            'education_end_date' => 'nullable|date',
            'employment_type' => 'nullable|string',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Fetch or create the Student model
        $student = Student::firstOrNew([
            'user_id' => $user->id,
        ]);

        // Update occupation in the Student model
        $student->occupation = $request->input('occupation', null);
        $student->save();

        // Prepare the education details data
        $educationDetailData = [
            'level' => $request->input('level'),
            'other_level' => $request->input('other_level'),
            'pursuing' => $request->input('is_education_pursuing'),
            'institute_name' => $request->input('education_institute'),
            'education_institute_other'=>$request->input('education_institute_other'),
            'institute_type' => $request->input('education_institute_type'),
            'district' => $request->input('education_institute_district'),
            'state' => $request->input('state_id'),
            'course_name' => $request->input('education_course_name'),
            'education_course_other' => $request->input('education_course_other'),
            'specialisation' => $request->input('education_specialisation'),
            'grade_type' => $request->input('education_grade_type'),
            'grade' => $request->input('education_grade'),
            'start_date' => $request->input('education_start_date'),
            'end_date' => $request->input('education_end_date'),
        ];

        // Find the existing EducationDetail model by ID
        $educationDetail = EducationDetail::find($id);
        if (!$educationDetail) {
            return response()->json(['message' => 'Education detail not found'], 404);
        }

        // Authorization check: Ensure the education detail belongs to the authenticated user
        if ($educationDetail->student_id != $student->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Update the EducationDetail model
        $educationDetail->fill($educationDetailData);
        $educationDetail->save();

        // Send the success response
        return response()->json(['message' => 'User education details updated successfully']);
    }

    public function updateFamilyDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guardian_name' => 'nullable|string',
            'guardian_relationship' => 'nullable|string',
            'guardian_occupation' => 'nullable|string',
            'number_of_siblings' => 'nullable|string',
            'guardian_phone_number' => 'nullable|string',
            'annual_income' => 'nullable|string',

            'current_house_type' => 'nullable|string',
            'current_address' => 'nullable|string',
            "current_state" => 'required|string',
            "current_district" => 'required|string',
            "current_pincode" => 'nullable|string',

            "permanent_house_type" => 'nullable|string',
            "permanent_address" => 'nullable|string',
            "permanent_state" => 'nullable|string',
            "permanent_district" => 'nullable|string',
            "permanent_pincode" => 'nullable|string',

            "is_pm_same_as_current" => 'nullable|string',
            "current_citizenship" => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['user' => 'User not found']], 404);
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = new Student([
                'user_id' => $user->id,
            ]);
        }
        DB::beginTransaction();
        try {
            $student->is_pm_same_as_current = $request->has('is_pm_same_as_current') ? 1 : 0;
            $student->current_citizenship = $request->has('current_citizenship') ?? null;

            $student->save();

            $guardianDetail = GuardianDetail::where('student_id', $student->id)->first();

            if (!$guardianDetail) {
                $guardianDetail = new GuardianDetail([
                    'student_id' => $student->id,
                ]);
            }

            $guardianDetail->name = $request->input('guardian_name', null);
            $guardianDetail->relationship = $request->input('guardian_relationship', null);
            $guardianDetail->occupation = $request->input('guardian_occupation', null);
            $guardianDetail->phone_number = $request->input('guardian_phone_number', null);
            $guardianDetail->number_of_siblings = $request->input('number_of_siblings', null);
            $guardianDetail->annual_income = $request->input('annual_income', null);
            $guardianDetail->income_type = $request->input('income_type', null);

            $guardianDetail->save();


            $currentAddressDetail = AddressDetail::where([['student_id', '=', $student->id], ['type', '=', 'current']])->first();

            if (!$currentAddressDetail) {
                $currentAddressDetail = new AddressDetail([
                    'student_id' => $student->id,
                    'type' => 'current'
                ]);
            }

            $currentAddressDetail->house_type = $request->input('current_house_type', null);
            $currentAddressDetail->address = $request->input('current_address', null);
            $currentAddressDetail->state = $request->input('current_state', null);
            $currentAddressDetail->district = $request->input('current_district', null);
            $currentAddressDetail->pincode = $request->input('current_pincode', null);
            $currentAddressDetail->type = 'current';

            $currentAddressDetail->save();

            $permanentAddressDetail = AddressDetail::where([['student_id', '=', $student->id], ['type', '=', 'permanent']])->first();

            if (!$permanentAddressDetail) {
                $permanentAddressDetail = new AddressDetail([
                    'student_id' => $student->id,
                    'type' => 'permanent'
                ]);
            }

            if ($student->is_pm_same_as_current) {
                $permanentAddressDetail->house_type = $request->input('current_house_type', null);
                $permanentAddressDetail->address = $request->input('current_address', null);
                $permanentAddressDetail->state = $request->input('current_state', null);
                $permanentAddressDetail->district = $request->input('current_district', null);
                $permanentAddressDetail->pincode = $request->input('current_pincode', null);
            } else {
                $permanentAddressDetail->house_type = $request->input('permanent_house_type', null);
                $permanentAddressDetail->address = $request->input('permanent_address', null);
                $permanentAddressDetail->state = $request->input('permanent_state', null);
                $permanentAddressDetail->district = $request->input('permanent_district', null);
                $permanentAddressDetail->pincode = $request->input('permanent_pincode', null);
            }
            $permanentAddressDetail->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
        return response()->json(['message' => 'User address details updated successfully']);
    }

    public function updateWorkDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employment_type' => 'nullable|string',
            'company_name' => 'nullable|string',
            'designation' => 'nullable|string',
            'joining_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'job_role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['user' => 'User not found']], 404);
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = new Student([
                'user_id' => $user->id,
            ]);
            $student->save();
        }

        $employeDetail = false;

        if (!$employeDetail) {
            $employeDetail = new EmploymentDetail([
                'student_id' => $student->id,
            ]);
        }

        $employeDetail->employment_type = $request->employment_type ?? null;
        $employeDetail->company_name = $request->input('company_name', null);
        $employeDetail->designation = $request->input('designation', null);
        $employeDetail->joining_date = $request->input('joining_date', null);
        $employeDetail->working_currently = $request->working_currently ? '1' : '0';
        $employeDetail->end_date = $request->working_currently ? $request->input('end_date', null) : null;
        $employeDetail->job_role = $request->input('job_role', null);

        $employeDetail->save();

        return response()->json(['message' => 'User employment details updated successfully']);
    }

    public function updateDocument(Request $request)
    {



        $rules = [
            'document_type' => 'required',
            'document' => 'required', // Example: PDF, DOC, DOCX files allowed
        ];



        // Check if document_type is "other," and if so, require other_document_name
        if ($request->input('document_type') === 'other') {
            $rules['other_document_name'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $documentType = $validatedData['document_type'];
        $file = $validatedData['document'];
        $otherDocumentName = !empty($validatedData['other_document_name']) ? $validatedData['other_document_name'] : '';

        $fileName = date('Y_m_d_H_i_s') . str_replace(' ', '_', strtolower($file->getClientOriginalName()));
        $filePath = $file->storeAs('student/documents', $fileName, 'public');

        // Assuming you have a student ID
        $studentId = auth()->user()->student->id;

        // Check if a document of the same type exists for the student
        $existingDocument = Document::where('student_id', $studentId)
            ->where('document_type', $documentType)
            ->first();

        if ($existingDocument) {
            // Update the existing document's information
            $existingDocument->document = $filePath;
            $existingDocument->scholarship_id = $request->sch_id ?? '';
            $existingDocument->save();
        } else {
            // Create a new document
            $document = new Document([
                'document_type' => $documentType,
                'other_document_name' => $otherDocumentName,
                'document' => $filePath
            ]);

            $document->student_id = $studentId;
            $document->scholarship_id = $request->sch_id ?? '';

            $document->save();
        }

        return response()->json(['message' => 'Document uploaded successfully']);
    }

    public function updateQuestionDocument(Request $req)
    {

        $rules = [
            'scholarship_id' => 'required',
            'document' => 'required',
            'question' => 'required',
        ];

        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $documentType = $validatedData['document_type'] = 'que';
        $file = $validatedData['document'];


        $fileName = date("Y_m_d_h_i_s") . str_replace(' ', '_que_', strtolower($file->getClientOriginalName()));
        $filePath = $file->storeAs('student/documents/que', $fileName, 'public');

        // Assuming you have a student ID
        $studentId = auth()->user()->student->id;
        $sch_id = $req->scholarship_id;
        // Check if a document of the same type exists for the student
        $existingDocument = Document::where('student_id', $studentId)
            ->where('document_type', $documentType)
            ->where('scholarship_id', $sch_id)
            ->where('other_document_name', $req->question)
            ->first();

        if ($existingDocument) {
            // Update the existing document's information
            $existingDocument->document = $filePath;
            $existingDocument->scholarship_id = $sch_id ?? '';
            $existingDocument->save();
        } else {
            // Create a new document
            $document = new Document([
                'document_type' => $documentType,
                'other_document_name' => $req->question,
                'document' => $filePath
            ]);
            $document->student_id = $studentId;
            $document->scholarship_id = $sch_id ?? '';
            $document->save();
        }

        return response()->json(['message' => 'Document uploaded successfully']);

    }

    public function getUserDocuments()
    {
        $studentId = auth()->user()->student->id;

        $documents = Document::where('student_id', $studentId)->where('document_type', '!=', 'que')->get();

        // Attach human-readable document name to each document object
        foreach ($documents as $document) {
            $document->humanReadableType = Document::$documentTypes[$document->document_type] ?? $document->document_type;

            $document->document = asset('storage/' . $document->document);
        }

        return response()->json($documents);
    }

    public function getEducationDetail()
    {
        $studentId = auth()->user()->student->id;
        $user = Auth::user();
        $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
        $educationDetails = EducationDetail::where('student_id', $studentId)->get();
        $district = \App\Models\CountryData\District::whereStatus('active')->get();
        return view('student.education_form', compact('educationDetails', 'user', 'district'));

        // return response()->json($educationDetails);
    }

    public function getEmployementDetails()
    {
        $studentId = auth()->user()->student->id;

        $employmentDetails = EmploymentDetail::where('student_id', $studentId)->get();

        return view('student.employment_form', compact('employmentDetails'));
    }
    public function deleteEmploymentDetailByID(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['user' => 'User not found']], 404);
        }

        $student = Student::where('user_id', $user->id)->first();

        $employeDetail = EmploymentDetail::find($id);
        if (!$employeDetail) {
            return response()->json(['message' => 'employment detail not found'], 404);
        }

        if ($employeDetail->student_id != $student->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $employeDetail->delete();

        return response()->json(['message' => 'User employment details deleted successfully']);
    }
    public function updateEmploymentDetailByID(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'employment_type' => 'nullable|string',
            'company_name' => 'nullable|string',
            'designation' => 'nullable|string',
            'joining_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'job_role' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json(['errors' => ['user' => 'User not found']], 404);
        }

        $student = Student::where('user_id', $user->id)->first();

        $employeDetail = EmploymentDetail::find($id);
        if (!$employeDetail) {
            return response()->json(['message' => 'Education detail not found'], 404);
        }

        // Authorization check: Ensure the education detail belongs to the authenticated user
        if ($employeDetail->student_id != $student->id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $employeDetail->employment_type = $request->employment_type ?? null;
        $employeDetail->company_name = $request->input('company_name', null);
        $employeDetail->designation = $request->input('designation', null);
        $employeDetail->joining_date = $request->input('joining_date', null);
        $employeDetail->end_date = $request->input('end_date', null);
        $employeDetail->job_role = $request->input('job_role', null);

        $employeDetail->save();

        return response()->json(['message' => 'User employment details updated successfully']);
    }

    public function destroyUserDocument($id)
    {
        $document = Document::findOrFail($id);

        // Delete the document file from storage if needed
        // $document->deleteFile();

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }

    public function index()
    {
        $blogs = Blog::orderBy('id','DESC')->get(); 
        
        return view('student.blog', compact('blogs'));
    }

    public function blogDetails($id)
    {
        $blogDetails = Blog::where('slug', $id)->first();
        $Allblogs = Blog::orderByDesc('created_at')->get();
        return view('student.blogDetails', compact('blogDetails', 'Allblogs'));

    }

    public function avatar(Request $request)
    {
        $id = auth()->user()->id;
        $imagePath = '';
        DB::beginTransaction();
        try {
            if (!empty($_FILES)) {
                if ($_FILES['avatar']['name'] != "") {
                    $imageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->avatar->getClientOriginalExtension();
                    request()->avatar->move(public_path('img/profile/'), $imageName);
                    $imagePath = 'img/profile/' . $imageName;
                    $input['path'] = $imagePath;
                }
            }
            User::whereId($id)->update(['avatar' => $imagePath]);
            DB::commit();
            $msg = ['message' => 'image uploaded successfully!'];
        } catch (\Exception $e) {
            DB::rollback();
            $msg = ['error' => 'image not uploaded !' . $e->getLine() . ' - ' . $e->getMessage()];
        }
        return response()->json($msg);
    }

    public function stateDistrict(Request $request)
    {
        $stateName = $request->stateId;
        $state = \App\Models\CountryData\State::whereStatus('active')->whereName($stateName)->value('id');
        $data = \App\Models\CountryData\District::whereStatus('active')->where('state_id', $state)->get(['id', 'name','state_id'])->toArray();
        return $data;
    }

    public function saved(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            // $user = User::where('id', $user->id)->with('student')->first();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();

            $scholarships = SaveScholorship::with('savescholorship')->where('userid', auth()->user()->id)->get();
            $scholarshipsCount = SaveScholorship::with('savescholorship')->where('userid', auth()->user()->id)->count();

            return view('student.saved', compact('user', 'scholarships', 'scholarshipsCount'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function awarded(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)
                ->with([
                    'student',
                    'student.educationDetails',
                    'student.employmentDetails',
                    'student.guardianDetails',
                    'student.addressDetails',
                    'student.documents'
                ])
                ->first();

            $amounts = Scholarship::with([
                'distributionAmount' => function ($query) use ($user) {
                    $query->where('user_id', $user->id); 
                }
            ])
                ->whereHas('distributionAmount', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
            // dd($amounts->toAsdsrray());

            $assessment = Assesment::where('users_id', $user->id)->latest()->first();
if($assessment != null){
    return view('student.awarded', compact('user', 'amounts','assessment'));

}

            return view('student.awarded', compact('user', 'amounts'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function applied(Request $request)
    {

        if (Auth::check()) {
            $user = Auth::user();
            // $user = User::where('id', $user->id)->with('student')->first();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            //   dd($user->id);
            $scholarship = ScholarshipApplication::with(['user', 'scholarship', 'applicationStatuus'])->where('user_id', $user->id)->get();

            return view('student.applied', compact('user', 'scholarship'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function resourse(){
        $user = Auth::user();
        // $user = User::where('id', $user->id)->with('student')->first();
        $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();

        $resourse = Resource::with('Scholarship')->where('user_id',$user->id)->orderBy('id','DESC')->get();  
        $scholarships = Scholarship::get();
        $webinar = Webinar::where('student_id',$user->id)->orderBy('id','DESC')->get();   
       
        return view('student.resource', compact('user','resourse','scholarships','webinar')); 

    }

    public function resoursefilter(Request $request){
        $user = Auth::user();
        $scho_id = $request->scholarship_id;
        // $user = User::where('id', $user->id)->with('student')->first();
        $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();

        $resourse = Resource::with('Scholarship')->where('user_id',$user->id)->where('scholarship_id',$request->scholarship_id)->orderBy('id','DESC')->get();  
        $scholarships = Scholarship::get();
       
        return view('student.resource', compact('user','resourse','scholarships','scho_id'));

    }

    public function contact_us(Request $request)
    {
        //  dd('dsadsa');
        // $user = Auth::user();
        // $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
        // $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
        $contact = Contact::whereId('1')->first();
        return view('student.contact-us', compact('contact'));

    }

    public function about_us(Request $request)
    {

        // $user = Auth::user();
        // $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
        // $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
        $about = AboutUs::whereId('1')->first();
        return view('student.about-us', compact('about'));

    }

    // public function contactUs(Request $request)
    // {
    //     $data = [
    //         'f_name' => $request->FirstName,
    //         'l_name' => $request->LastName,
    //         'email' => $request->EmailAddress,
    //         'number' => $request->PhoneNo,
    //         'message' => $request->message,
    //         'status' => '1',
    //     ];
    //     ContactUs::create($data);
    //     return response()->json(['message' => 'Data received successfully']);

    // }

    public function contactUs(Request $request)
    {

        $newss  = new JoinNow();
         $newss->name = $request->name;
        
         $newss->message = $request->message;
         $newss->working_no = $request->PhoneNo;
         $newss->email = $request->email;
         $newss->type = 2;
    $newss->save();
       
    return response()->json(['message' => 'We have sucessfully received your request!!']);
        // return response()->json(['message' => 'Data received successfully']);

    }
    public function gallery(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.gallery', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function article_details(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.article-details', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function comment_form(Request $request)
    {
        dd($request->all());
    }

    public function events(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.events', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function event_detail(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.events-detail', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function newsletter(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.newsletter', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function newsletter_detail(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.newsletter-detail', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function newsletterSubscribe(Request $req)
    {
        $rules = [
            'email' => 'required|email'
        ];
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        } else {
            try {
                // code is panding
                $msg = 'We will start sending you newsletters soon.';
            } catch (\Exception $ex) {
                $msg = $ex->getMessage();
            }
            return json_encode($msg);
        }

    }

    public function podcast(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.podcasts', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function podcast_detail(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.podcast-detail', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function study_material(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            $study = Study::get();

            return view('student.study-material', compact('user', 'scholarship', 'about', 'study'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function consultancy_services(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.consultancy-services', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function education_loans(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.education-loans', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function terms_conditions(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.terms-conditions', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function privacy_policy(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.privacy-policy', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function refund_policy(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
            $scholarship = ScholarshipApplication::with(['user', 'scholarship'])->whereUserId($user->id)->get();
            $about = AboutUs::whereId('1')->first();
            return view('student.refund-policy', compact('user', 'scholarship', 'about'));
        } else {
            return redirect()->route('Student.login'); // Redirect to the login page
        }
    }

    public function cmsPages($slug)
    {
        $getPage = CmsPage::where('slug', $slug)->first();
        return view('student.cms', compact('getPage'));


    }

    public function getInvolved()
    {
        return view('student.get-involved');
    }

    public function saveScholorship(Request $request)
    {
        $data = SaveScholorship::where('schId', $request->scholarshipId)->where('userid', auth()->user()->id)->first();
        if (!empty($data)) {
            $saveData = SaveScholorship::find($data->id);

            $saveData->delete();

            return response()->json(['message' => 'Unsaved successfully']);
        }
        try {
            $saveData = new SaveScholorship();
            $saveData->schId = $request->scholarshipId;
            $saveData->userid = auth()->user()->id;
            $saveData->save();

            return response()->json(['message' => 'Scholarship saved successfully', 'data' => 1]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error saving scholarship', 'details' => $e->getMessage()], 500);
        }
    }

    public function notification()
    {
        $user = Auth::user();
        $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();
        $notifications = Notification::where('user_id', auth()->user()->id)->where('author_name',1)->get();

        return view('student.notification', compact('user', 'notifications'));
    }

    public function filterNotification(Request $request)
    {



        $user = Auth::user();
        $orderby = $request->input('orderby');
        $notifications = Notification::select([
            'id',
            'teg',
            'title',
            'description',
            'author_name',
            'created_at',
            \DB::raw('DATE_FORMAT(created_at, "%d %b %Y") as formattedDate'),
            \DB::raw('DATE_FORMAT(created_at, "%h:%i %p") as formattedTime')
        ])
            ->where('user_id', auth()->user()->id)
            ->when(!empty($orderby), function ($query) use ($orderby) {
                return $query->where('teg', $orderby);
            })
            ->get();
        $user = User::where('id', $user->id)->with(['student', 'student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->first();

        return response()->json(['notifications' => $notifications]);
    }

    public function download()
    {
        // $path = storage_path("app/public/recipt/{$filename}");

        // return response()->download($path, $filename);
    }

    public function like(Blog $blog)
    {
        $user = Auth::user();

        $like = new Like(['user_id' => $user->id]);
        $blog->likes()->save($like);

        return response()->json(['success' => true]);
    }

    public function unlike(Blog $blog)
    {
        $user = Auth::user();

        $blog->likes()->where('user_id', $user->id)->delete();

        return response()->json(['success' => true]);
    }

    public function verifyOTp(Request $request){
        

        $userEmailOtp = $request->email_verified;
        $userMobileOtp = $request->mobile_verified;

        $systermEmailOtp =  $request->session()->get('gmail-otp');
        $systermMobileOtp =  $request->session()->get('mobile-otp');
            

        if($userEmailOtp == $systermEmailOtp && $userMobileOtp == $systermMobileOtp){
            $userId = auth()->user()->id;
            $user = User::find($userId);

            $user->mobile_verified = 1;
            $user->email_verified = 1;
            $user->update();
        }

        return redirect()->route('Student.dashboard')->with('status', 'Profile updated!');
       

    }

    public function sendOpt(Request $request){

        $randomNumber = mt_rand(1000, 9999);
        $userEmail = auth()->user()->email;
        $subject = 'ScholarBox OTP';
        $mailContent = 'Your OTP is '. $randomNumber;
        
       
        $mailData = ['content' => $mailContent, 'subject' => $subject];

        Mail::to($userEmail)->send(new studentMail($mailData));
        

        $otp = rand(100000, 999999);
        $request->session()->put('gmail-otp', $randomNumber);
        $request->session()->put('mobile-otp', $otp);


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
                'contact_numbers' => auth()->user()->phone_number ?? '', 
            ]);

           
    }

    public function sendOtp(Request $request){

        $randomNumber = mt_rand(1000, 9999);
        // $userEmail = auth()->user()->email;
        // $subject = 'ScholarBox OTP';
        // $mailContent = 'Your OTP is '. $randomNumber;
        
       
        // $mailData = ['content' => $mailContent, 'subject' => $subject];

        // Mail::to($userEmail)->send(new studentMail($mailData));
        

        $otp = rand(100000, 999999);
        // $request->session()->put('gmail-otp', $randomNumber);
        $request->session()->put('mobile-otp', $otp);


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
                'contact_numbers' => auth()->user()->phone_number ?? '', 
            ]);

           
    }


    public function saveMobileNumber(Request $request){
        $validator = Validator::make($request->all(), [
            
            'phone_number' => 'required|unique:users,phone_number',
            
        ], [
            
            'phone_number.unique' => 'The phone number is already in use.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with('message', 'Mobile number not found');

        }
        
        $user = User::find(auth()->user()->id);
       
        $user->phone_number = $request->phone_number;
        $user->update();
        return redirect()->route('Student.dashboard')->with('status', 'Profile updated!');




    }

    public function saveMobileNumberOTP(Request $request)
    {
        $userMobileOtp = $request->otp;
        $systermMobileOtp = $request->session()->get('mobile-otp');
    
        if ($userMobileOtp == $systermMobileOtp) {
            $userId = auth()->user()->id;
            $user = User::find($userId);
    
            $user->mobile_verified = 1;
            $user->update();
    
            return redirect()->route('Student.dashboard')->with('status', 'Profile updated!');
        } else {
            return redirect()->route('Student.dashboard')->withErrors(['otp' => 'The OTP does not match. Otp resend. Please try again.']);
        }
    }
    

    public function deleteJoinNow(){
        JoinNow::truncate();
    }


    public function uploadDocuments(Request $request){
       

    
        $documentType = $request->document_type = $request->doc_type;
        $file = $request->new_documents;


        $fileName = date("Y_m_d_h_i_s") . str_replace(' ', '_que_', strtolower($file->getClientOriginalName()));
        $filePath = $file->storeAs('student/documents/que', $fileName, 'public');
        $document = new Document([
            'document_type' => $documentType,
            // 'other_document_name' => $otherDocumentName,
            'document' => $filePath
        ]);
        $studentId = auth()->user()->student->id;
        $document->student_id = $studentId;
        $document->extra = 'extra';
        $document->scholarship_id = $request->sch_id ?? '';

        $document->save();
        return response()->json(['message' => 'Document uploaded successfully']);
    }

    public function saveScholarshipAount(Request $request){
        $scholarshipId = $request->input('id');
        $checked = $request->input('checked');
       
        $saveAck = AmountDistribution::where('scholarship_id', $scholarshipId)
        ->where('user_id', auth()->id())
        ->update(['ack' => 1]);
      
        return response()->json(['message' => 'Your Response saved sucessfully']);
    }
}
