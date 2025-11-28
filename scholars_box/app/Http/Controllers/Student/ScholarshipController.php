<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AddressDetail;
use App\Models\Document;
use App\Models\EducationDetail;
use App\Models\EmploymentDetail;
use App\Models\GuardianDetail;
use App\Models\Scholarship\Scholarship;
use App\Models\Scholarship\ScholarshipQuestionAnswers;
use App\Models\PersonalDetail;
use App\Models\Student;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ScholarshipApplication\ScholarshipApplication;
use App\Models\ScholarshipApplication\DocumentVerification;
use App\Models\ScholarshipApplication\ScholarshipApplicationDetail;
use App\Models\ScholarshipQuestionApplication;


class ScholarshipController extends Controller
{
    function index()
    {
        $user = Auth::user();
        if (isset($user)) {
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
        } else {
            $user = 'Guest';
        }
        return view('student.scholarship.index', compact('user'));
    }

    public function filter(Request $request)
    {

        $filter = $request->filter;
        $order = $request->order;
        $type = $request->type ?? 'column';

        $scholarships = Scholarship::whereStatus(1)
            ->with([
                'scholarshipQuestionApplication',
                'scholarshipQuestionApplication.scholarshipOptionsApplications',
                'apply_now',
                'company',
                'savescholorship'
            ])->where('status', '!=', 0)
            ->when($filter != null, function ($q) use ($filter) {
                $q->whereJsonContains('tag', $filter);
            });

        $data = $scholarships;
        $count = $data->count('id');

        // Order by features column first (descending), then apply additional ordering
        $scholarships = $scholarships->orderByDesc('is_featured')
            ->when($order != '', function ($q) use ($order) {
                $q->when($order == 'a_z', function ($innerQ) {
                    $innerQ->orderBy('scholarship_name');
                })->when($order == 'z_a', function ($innerQ) {
                    $innerQ->orderByDesc('scholarship_name');
                })->when($order == 'asc', function ($innerQ) {
                    $innerQ->orderBy('id');
                })->when($order == 'desc', function ($innerQ) {
                    $innerQ->orderByDesc('id');
                });
            });

        $scholarships = $scholarships->get();

        $response = [
            'view' => view('student.scholarship.Scholarships', compact('scholarships', 'type'))->render(),
            'count' => $count,
        ];

        return response()->json($response);
    }


    public function applyForm(Request $request)
    {
        $scholarshipId = $request->input('scholarship_id');
        $validator = Validator::make($request->all(), [
            'occupation' => 'nullable|string',
            'graduation_institute' => 'nullable|string',
            'graduation_institute_type' => 'nullable|string',
            'graduation_institute_district' => 'nullable|string',
            'graduation_institute_state' => 'nullable|string',
            'graduation_course_name' => 'nullable|string',
            'graduation_specialisation' => 'nullable|string',
            'graduation_grade_type' => 'nullable|string',
            'graduation_grade' => 'nullable|string',
            'graduation_start_date' => 'nullable|date',
            'graduation_end_date' => 'nullable|date',

            'guardian_name' => 'nullable|string',
            'guardian_relationship' => 'nullable|string',
            'guardian_occupation' => 'nullable|string',
            'number_of_siblings' => 'nullable|string',
            'guardian_phone_number' => 'nullable|string',
            'annual_income' => 'nullable|string',

            'current_house_type' => 'nullable|string',
            'current_address' => 'nullable|string',
            "current_state" => 'nullable|string',
            "current_district" => 'nullable|string',
            "current_pincode" => 'nullable|string',

            "permanent_house_type" => 'nullable|string',
            "permanent_address" => 'nullable|string',
            "permanent_state" => 'nullable|string',
            "permanent_district" => 'nullable|string',
            "permanent_pincode" => 'nullable|string',

            "is_pm_same_as_current" => 'nullable|string',
            "current_citizenship" => 'nullable|string',

            'employment_type' => 'nullable|string',
            'company_name' => 'nullable|string',
            'designation' => 'nullable|string',
            'joining_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'job_role' => 'nullable|string',
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

        try {
            DB::beginTransaction(); // Start a database transaction

            $this->saveAuthUserData($request, $user);

            $student = $this->saveStudentsUserData($request, $user);

            $this->saveStudentsGraduationData($request, $user, $student->id);

            $student->is_pm_same_as_current = $request->has('is_pm_same_as_current') ? 1 : 0;

            $student->current_citizenship = $request->has('current_citizenship') ?? null;

            $student->save();

            $this->saveStudentsGuardianDetailData($request, $user, $student->id);

            $this->saveStudentsCurrentAddressDetailData($request, $student->id);

            $this->savePermanentAddressDetailData($request, $student->id, $student->is_pm_same_as_current);

            $this->saveEmployeDetailData($request, $student->id);

            $e = ScholarshipApplication::whereUserId($user->id)
                ->whereScholarshipId($scholarshipId)
                ->first();

            if ($e) {
                return response()->json(['errors' => ['message' => 'Already applied for this scholarship']], 400);
            }

            $scholarshipApplication = $this->createNewScholarshipApplication($request, $user->id);

            // Code for DocumentVerification
            $this->studentDocumentVerification($student->id, $scholarshipApplication);

            $this->saveScholarshipQuestionAnswers($request, $student, $scholarshipId);

            // If everything is successful, commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Something went wrong, so roll back the transaction
            DB::rollBack();
            // Handle the exception or log it
            dd($e->getLine(), $e->getMessage());
        }
        return response()->json(['message' => 'Applied successfully']);
    }

    public function saveAuthUserData(Request $request, $user)
    {
        // Update user's personal details
        $user->first_name = $request->filled('first_name') ? $request->first_name : ($user->first_name ?? NULL);
        $user->last_name = $request->filled('last_name') ? $request->last_name : ($user->last_name ?? NULL);
        $user->email = $request->filled('email') ? $request->email : ($user->email ?? NULL);
        $user->phone_number = $request->filled('phone_number') ? $request->phone_number : ($user->phone_number ?? NULL);
        $user->date_of_birth = $request->filled('dob') ? $request->dob : ($user->date_of_birth ?? NULL);
        $user->whatsapp_number = $request->filled('whatsapp_number') ? $request->whatsapp_number : ($user->whatsapp_number ?? NULL);
        $user->gender = $request->filled('gender') ? $request->gender : ($user->gender ?? NULL);
        $user->aadhar_card_number = $request->filled('aadhar_card_number') ? $request->aadhar_card_number : ($user->aadhar_card_number ?? NULL);
        // Update other fields as needed
        $user->save();
    }

    public function saveStudentsUserData(Request $request, $user)
    {
        $studentData = [
            'is_minority' => (isset($request->is_minority) && ($request->is_minority == 'on')) ? 1 : 0,
            'minority_group' => (($request->has('is_minority')) && ($request->input('is_minority') == 'on')) ? $request->minority_group : 'N/A',
            'category' => $request->input('category', null),
            'other_reservation' => $request->input('other_reservation', null),
            'is_pwd_category' => $request->has('is_pwd_category') ? 1 : 0,
            'pwd_percentage' => (($request->has('is_pwd_category')) && ($request->input('is_pwd_category') == 'on')) ? $request->input('pwd_percentage', null) : null,
            'is_army_veteran_category' => $request->has('is_army_veteran_category') ? 1 : 0,
            'army_veteran_data' => $request->input('army_veteran_data', null),
            'occupation' => $request->input('occupation', null),
        ];

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            $student = new Student([
                'user_id' => $user->id,
            ]);
        }
        $student->fill($studentData);
        $student->save();
        return $student;
    }

    public function getUserData($data){

        return getAllUsersData($data);
    }

    public function saveStudentsGraduationData(Request $request, $user, $id)
    {
        $educationDetails = [
            'graduation' => [
                'level' => 'graduation',
                'pursuing' => 'graduation',
                'institute_name' => $request->input('graduation_institute', null),
                'institute_type' => $request->input('graduation_institute_type', null),
                'district' => $request->input('graduation_institute_district', null),
                'state' => $request->input('graduation_institute_state', null),
                'course_name' => $request->input('graduation_course_name', null),
                'specialisation' => $request->input('graduation_specialisation', null),
                'grade_type' => $request->input('graduation_grade_type', null),
                'grade' => $request->input('graduation_grade', null),
                'start_date' => $request->input('graduation_start_date', null),
                'end_date' => $request->input('graduation_end_date', null),
            ],
        ];

        foreach ($educationDetails as $detailType => $data) {
            $educationDetail = EducationDetail::firstOrNew([
                'student_id' => $id,
                'level' => $data['level'],
            ]);
            foreach ($data as $key => $value) {
                if ($key !== 'level') {
                    $educationDetail->$key = $value;
                }
            }
            $educationDetail->save();
        }
    }

    public function saveStudentsGuardianDetailData(Request $request, $user, $id)
    {
        $g = GuardianDetail::where('student_id', $id)->first();

        if (!$g) {
            $g = new GuardianDetail([
                'student_id' => $id,
            ]);
        }

        $g->name = $request->input('guardian_name', null);
        $g->relationship = $request->input('guardian_relationship', null);
        $g->occupation = $request->input('guardian_occupation', null);
        $g->phone_number = $request->input('guardian_phone_number', null);
        $g->number_of_siblings = $request->input('number_of_siblings', null);
        $g->annual_income = $request->input('annual_income', null);

        $g->save();
    }

    public function saveStudentsCurrentAddressDetailData(Request $request, $id)
    {
        $s = AddressDetail::whereStudentId($id)
            ->whereType('current')
            ->first();

        if (!$s) {
            $s = new AddressDetail([
                'student_id' => $id,
                'type' => 'current'
            ]);
        }

        $s->house_type = $request->input('current_house_type', null);
        $s->address = $request->input('current_address', null);
        $s->state = $request->input('current_state', null);
        $s->district = $request->input('current_district', null);
        $s->pincode = $request->input('current_pincode', null);
        $s->type = 'current';

        $s->save();
    }

    public function savePermanentAddressDetailData(Request $request, $id, $is_pm_same_as_current)
    {
        $p = AddressDetail::whereStudentId($id)
            ->whereType('permanent')
            ->first();

        if (!$p) {
            $p = new AddressDetail([
                'student_id' => $id,
                'type' => 'permanent'
            ]);
        }

        if ($is_pm_same_as_current) {
            $p->house_type = $request->input('current_house_type', null);
            $p->address = $request->input('current_address', null);
            $p->state = $request->input('current_state', null);
            $p->district = $request->input('current_district', null);
            $p->pincode = $request->input('current_pincode', null);
        } else {
            $p->house_type = $request->input('permanent_house_type', null);
            $p->address = $request->input('permanent_address', null);
            $p->state = $request->input('permanent_state', null);
            $p->district = $request->input('permanent_district', null);
            $p->pincode = $request->input('permanent_pincode', null);
        }

        $p->save();
    }

    public function saveEmployeDetailData(Request $request, $id)
    {
        // Get the authenticated user
        $e = EmploymentDetail::where('student_id', $id)->first();

        if (!$e) {
            $e = new EmploymentDetail([
                'student_id' => $id,
            ]);
        }

        $e->employment_type = $request->employment_type ?? null;
        $e->company_name = $request->input('company_name', null);
        $e->designation = $request->input('designation', null);
        $e->joining_date = $request->input('joining_date', null);
        $e->end_date = $request->input('end_date', null);
        $e->job_role = $request->input('job_role', null);
        $e->save();
    }

    public function createNewScholarshipApplication(Request $request, $id)
    {
        // Code for ScholarshipApplication
        $s = [
            'user_id' => $id,
            'scholarship_id' => $request->input('scholarship_id'),
            'status' => 'application_submitted',
            'applied_at' => now(),
        ];
        $d = ScholarshipApplication::create($s);
        return $d;
    }

    public function studentDocumentVerification($id, $s)
    {
        $studentDocuments = Document::where('student_id', $id)->get();

        foreach ($studentDocuments as $studentDocument) {
            $documentVerification = DocumentVerification::whereApplicationId($s->id)->first();
            if (!$documentVerification) {
                $documentVerification = new DocumentVerification();
                $documentVerification->application_id = $s->id;
            }

            $documentVerification->document_type = $studentDocument->document_type;
            $documentVerification->document = $studentDocument->document;

            $documentVerification->save();
        }
    }

    public function questions(Request $request)
    {
        $id = $request->id;
        $scholarships = Scholarship::where('status', 1)->whereId($id)
            ->with([
                'scholarshipQuestionApplication',
                'scholarshipQuestionApplication.scholarshipOptionsApplications',
                'apply_now'
            ])
            ->get();
        $district = \App\Models\CountryData\District::whereStatus('active')->get();
        $state = \App\Models\CountryData\State::whereStatus('active')->get();
        $user = auth()->user();
        $draft = \App\Models\Draft::where('student_id', $user->id)->where('scholarship_id', (int) $id)->first();
        $personal_details = view('student.scholarship.personal_details', compact('scholarships', 'user', 'draft'))->render();
        $family_details = view('student.scholarship.family_details', compact('scholarships', 'user', 'state', 'district', 'draft'))->render();
        // $education_details = view('student.scholarship.education_details', compact('scholarships','user','state','district','draft'))->render();
        // $attach_your_documents = view('student.scholarship.attach_your_documents', compact('scholarships','user','draft'))->render();
        $que = view('student.scholarship.questions', compact('scholarships', 'draft'))->render();
        return response()->json([
            'que' => $que ?? '',
            'personal_details' => $personal_details ?? '',
            'family_details' => $family_details ?? '',
            // 'education_details'=>$education_details??'',
            // 'attach_your_documents'=>$attach_your_documents??''
        ]);

    }

    public function questions_doc(Request $request)
    {
        try {
            $studentId = auth()->user()->student->id;
            $reqDocs = json_decode($request->reqDoc);
            $documents = \App\Models\Document::where('student_id', $studentId)
                ->where('document_type', '!=', 'que')
                ->pluck('document_type')
                ->toArray();

            $missingDocs = array_diff($reqDocs, $documents);
            if (empty($missingDocs)) {
                $response = [
                    'data' => true,
                    'msg' => ''
                ];
            } else {
                $t = [];
                foreach ($missingDocs as $k => $v) {
                    $t[$k] = Document::$documentTypes[$v] ?? 'Other Documents';
                }
                $missingDocsText = implode(', ', $t);
                $response = [
                    'data' => false,
                    'msg' => "To apply for the scholarship, kindly add your documents: $missingDocsText into the student dashboard. (Student Profile)."
                ];
            }
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'data' => false,
                // 'msg' => 'An error occurred while processing your request.'
                'msg' => "" . $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile() . " "
            ]);
        }
    }

    public function saveScholarshipQuestionAnswers(Request $request, $student, $scholarshipsId)
    {
        ScholarshipQuestionAnswers::where('student_id',$student->id)->where('scholarship_id',$scholarshipsId)->delete();
        if ($request->radio) {
            foreach ($request->radio as $x => $r) {
                $dataA['scholarship_radio_question_id'] = $x ?? null;
                $dataA['scholarship_radio_options_answer'] = $r[0] ?? null;
                $dataA['student_id'] = $student->id;
                $dataA['scholarship_id'] = $scholarshipsId;
                ScholarshipQuestionAnswers::insert($dataA);
            }
        }
        if ($request->checkbox) {
            foreach ($request->checkbox as $y => $s) {
                $dataB['scholarship_checkbox_question_id'] = $y ?? null;
                $dataB['scholarship_checkbox_options_answer'] = $s[0] ?? null;
                $dataB['student_id'] = $student->id;
                $dataB['scholarship_id'] = $scholarshipsId;
                ScholarshipQuestionAnswers::insert($dataB);
            }
        }
        if ($request->textarea) {
            foreach ($request->textarea as $z => $t) {
                $dataC['scholarship_textarea_question_id'] = $z ?? null;
                $dataC['scholarship_textarea_options_answer'] = $t[0] ?? null;
                $dataC['student_id'] = $student->id;
                $dataC['scholarship_id'] = $scholarshipsId;
                ScholarshipQuestionAnswers::insert($dataC);
            }
        }
    }

    public function detail($id)
    {

        $scholarships = Scholarship::where('status', 1)->where('slug', $id)
            ->with([
                'scholarshipQuestionApplication',
                'scholarshipQuestionApplication.scholarshipOptionsApplications',
                'apply_now',
                'company',
                'savescholorship'
            ])->first();


        return view('student.scholarship.detail', compact('scholarships'));
    }

    public function applyFormDraft(Request $req)
    {
        try {
            $data = [
                'student_id' => Auth::user()->id,
                'scholarship_id' => $req->scholarship_id ?? '',
                'phone_number' => $req->phone_number ?? '',
                'dob' => $req->dob ?? '',
                'whatsapp_number' => $req->whatsapp_number ?? '',
                'gender' => $req->gender ?? '',
                'aadhar_card_number' => $req->aadhar_card_number ?? '',
                'is_minority' => $req->is_minority ? '1' : '0',
                'minority_group' => $req->minority_group ?? '',
                'category' => $req->category ?? '',
                'other_reservation' => $req->other_reservation ?? '',
                'is_pwd_category' => $req->is_pwd_category ? '1' : '0',
                'pwd_percentage' => $req->pwd_percentage ?? '',
                'is_army_veteran_category' => $req->is_army_veteran_category ? '1' : '0',
                'guardian_name' => $req->guardian_name ?? '',
                'guardian_relationship' => $req->guardian_relationship ?? '',
                'guardian_occupation' => $req->guardian_occupation ?? '',
                'guardian_phone_number' => $req->guardian_phone_number ?? '',
                'number_of_siblings' => $req->number_of_siblings ?? '',
                'annual_income' => $req->annual_income ?? '',
                'current_house_type' => $req->current_house_type ?? '',
                'current_address' => $req->current_address ?? '',
                'current_state' => $req->current_state ?? '',
                'current_district' => $req->current_district ?? '',
                'current_pincode' => $req->current_pincode ?? '',
                'is_pm_same_as_current' => $req->is_pm_same_as_current ? '1' : '0',
                'permanent_house_type' => $req->is_pm_same_as_current ? $req->current_house_type : ($req->permanent_house_type ?? ''),
                'permanent_address' => $req->is_pm_same_as_current ? $req->current_address : ($req->permanent_address ?? ''),
                'permanent_state' => $req->is_pm_same_as_current ? $req->current_state : ($req->permanent_state ?? ''),
                'permanent_district' => $req->is_pm_same_as_current ? $req->current_district : ($req->permanent_district ?? ''),
                'permanent_pincode' => $req->is_pm_same_as_current ? $req->current_pincode : ($req->permanent_pincode ?? ''),
                'current_citizenship' => $req->current_citizenship ?? ''
            ];
            $exists = \App\Models\Draft::where('student_id', $req->student_id)
                ->where('scholarship_id', $req->scholarship_id)
                ->exists();

            if ($exists) {
                \App\Models\Draft::where('student_id', $req->student_id)
                    ->where('scholarship_id', $req->scholarship_id)
                    ->delete();
            }
            // dd($data);
            $create = \App\Models\Draft::insert($data);
        } catch (\Exception $ex) {
            dd($ex->getMessage());
        }
        return $create;
    }

}
