<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Scholarship\Scholarship;
use App\Models\ScholarshipQuestionApplication;
use App\Models\Scholarshipstatus;
use App\Models\ApplyNowForm;
use App\Models\ContactScholorship;
use DB;
use App\Models\AmountDistribution;
use App\Models\ScholarshipOptionsApplications;
use App\Models\ScholarshipApplication\ScholarshipApplication;
use App\Models\User;
use App\Models\SaveText;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Document;

use App\Exports\ScholarshipApplicationExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\CountryData\State;

class ScholarshipController extends Controller
{
    public function __construct()
    {
        $this->menu = '1';
    }
    public function index()
    {
        $path = user_permission($this->menu, 'view');
        if (!$path) {
            $scholarships = Scholarship::with(['company:id,company_name'])
                ->when(in_array(auth()->user()->role_id, ['3']), function ($q) {
                    $q->where('company_id', auth()->user()->id);
                })
                ->get();

            return view("admin.scholarship.list", compact('scholarships'));
        } else {
            return redirect()->route($path);
        }
    }

    public function add()
    {
        $data['company'] = User::whereRoleId('3')->pluck('company_name', 'id');
        $path = user_permission($this->menu, 'add');
        if (!$path) {
            return view("admin.scholarship.add", $data);
        } else {
            return redirect()->route($path);
        }
    }

    public function save(Request $request)
    {
        $path = user_permission($this->menu, 'add');
        if (!$path) {
            // Validate the form data
            $validatedData = $request->validate([
                'company_id' => 'string',
                'scholarship_name' => 'string',
                'published_date' => 'date',
                'end_date' => 'date',
                'short_desc' => 'string',
                'scholarship_info' => 'string',
                'sponsor_info' => 'string',
                'who_can_apply_info' => 'string',
                'how_to_apply_info' => 'string',
                'faqs' => 'string',
                'min_age' => 'string',
                'contact_details' => 'string',
                'avatar' => 'required',
                'education_req' => 'required',
            ]);
            if (!empty($_FILES)) {
                if ($_FILES['avatar']['name'] != "") {
                    $imageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->avatar->getClientOriginalExtension();
                    request()->avatar->move(public_path('img/profile/'), $imageName);
                    $imagePath = 'img/profile/' . $imageName;
                } else {
                    $imagePath = $request->avatar_hidden;
                }
            } else {
                $imagePath = $request->avatar_hidden;
            }
            // Add 'status' to the validated data
            // $validatedData['status'] = in_array(auth()->user()->role_id, ['3','4']) ? 2 : 1 ;
            $title = $request->input('scholarship_name');
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;
            while (Scholarship::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $dateString = $request->end_date;
            $date = \DateTime::createFromFormat('d-m-Y', $dateString);
            $date->modify('+1 day');
            $updatedDate = $date->format('d-m-Y');


            $validatedData['status'] = 1;
            $validatedData['avatar'] = $imagePath;
            $validatedData['tag'] = json_encode($request->tag);
            $validatedData['application_processs'] = $request->application_processs;
            $validatedData['education_req'] = json_encode($request->education_req);
            $validatedData['comany_link'] = $request->scholarship_link;
            $validatedData['is_featured'] = $request->has('is_featured') ? 1 : 0;
            $validatedData['is_scholarsip'] = $request->has('is_scholarsip') ? 1 : 0;
            $validatedData['contact_details'] = $request->looking_for;
            $validatedData['slug'] = $slug;
            $validatedData['end_date'] = $updatedDate;

            // Create a new scholarship record and save it to the database

            $newScholarship = Scholarship::create($validatedData);

            foreach ($request->input('contact_names') as $key => $name) {
                ContactScholorship::create([
                    'name' => $name,
                    'scholarship_id' => $newScholarship->id,
                    'email' => $request->input('contact_emails.' . $key),
                    'phone' => $request->input('contact_phones.' . $key),
                ]);
            }

            return redirect()->route('admin.scholarship.list')->with('success', 'Scholarship form submitted successfully !');
            // return redirect()->route('admin.scholarship.apply_now.form',$newScholarship->id)->with('success', 'Scholarship form submitted successfully!');
        } else {
            return redirect()->route($path);
        }
    }

    public function approve($id)
    {

        Scholarship::whereId($id)->update(['status' => '1']);
        return redirect()->route('scholarship.index')->with('success', 'Scholarship is Approved Now');
    }
    public function apply_now($id)
    {
        $path = user_permission($this->menu, 'add');
        if (!$path) {
            $data['scholarship'] = Scholarship::whereId($id)->first();
            $data['form'] = ApplyNowForm::where('scholarship_id', $id)->first();
            return view('admin.scholarship.form', $data);
        } else {
            return redirect()->route($path);
        }
    }
    public function apply_now_form(Request $request)
    {
        $data = [];
        $input = $request->all();
        $appyNow = ApplyNowForm::where('scholarship_id', $request->scholarship_id)->first();
        try {
            foreach ($input as $key => $val) {
                if (in_array($key, ['user_id', 'scholarship_id'])) {
                    $data[$key] = $val;
                } else if ($key == '_token') {
                    // $a = '';
                } else if ($key == 'docs') {
                    $data[$key] = json_encode($val);
                } else {
                    $data[$key] = ($val == 'on' ? '1' : '0');
                }
            }
            if ($appyNow) {
                $appyNow->delete();
                ApplyNowForm::create($data);
                return redirect()->route('admin.scholarship.list')->with('success', 'Scholarship form Updated successfully!');
            } else {
                ApplyNowForm::create($data);
                return redirect()->route('admin.scholarship.list')->with('success', 'Scholarship form submitted successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.scholarship.list')->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $company = User::whereRoleId('3')->pluck('company_name', 'id');
        $path = user_permission($this->menu, 'edit');
        if (!$path) {
        } else {
            return redirect()->route($path);
        }
        $scholarship = Scholarship::find($id);
        $scholarshipContactDetails = ContactScholorship::where('scholarship_id', $id)->get();
        return view("admin.scholarship.edit", compact('scholarship', 'company', 'scholarshipContactDetails'));
    }

    public function update(Request $request, $id)
    {
        $path = user_permission($this->menu, 'edit');
        if (!$path) {
            // Validate the form data
            $validatedData = $request->validate([
                'end_date' => 'date',
                'company_id' => 'string',
                'scholarship_name' => 'string',
                'published_date' => 'date',
                'short_desc' => 'string',
                'scholarship_info' => 'string',
                'sponsor_info' => 'string',
                'who_can_apply_info' => 'string',
                'how_to_apply_info' => 'string',
                'faqs' => 'string',
                'min_age' => 'string',
                'contact_details' => 'string',
                'education_req' => 'required',
            ]);

            // Handle file upload
            if (!empty($_FILES)) {
                if ($_FILES['avatar']['name'] != "") {
                    $imageName = date('Y_m_d_H_i_s_a') . '_' . time() . '.' . request()->avatar->getClientOriginalExtension();
                    request()->avatar->move(public_path('img/profile/'), $imageName);
                    $imagePath = 'img/profile/' . $imageName;
                } else {
                    $imagePath = $request->avatar_hidden;
                }
            } else {
                $imagePath = $request->avatar_hidden;
            }

            // Find the scholarship record by ID
            $scholarship = Scholarship::findOrFail($id);

            // Check if 'end_date' needs to be updated
            $newEndDate = \DateTime::createFromFormat('d-m-Y', $request->end_date);
            $currentEndDate = \DateTime::createFromFormat('d-m-Y', $scholarship->end_date);

            // Check if either date could not be parsed correctly
            if ($newEndDate && $currentEndDate) {
                $newEndDateFormatted = $newEndDate->format('d-m-Y');
                $currentEndDateFormatted = $currentEndDate->format('d-m-Y');

                // Compare and update 'end_date' only if it has changed
                if ($newEndDateFormatted !== $currentEndDateFormatted) {
                    $newEndDate->modify('+1 day');
                    $validatedData['end_date'] = $newEndDate->format('d-m-Y');
                } else {
                    unset($validatedData['end_date']); // Remove 'end_date' from the data to be updated
                }
            } elseif ($newEndDate) {
                // If only the new end date is valid, consider it as a change
                $newEndDate->modify('+1 day');
                $validatedData['end_date'] = $newEndDate->format('d-m-Y');
            } else {
                unset($validatedData['end_date']); // Remove 'end_date' from the data to be updated
            }

            $title = $request->input('scholarship_name');
            $slug = Str::slug($title);
            $originalSlug = $slug;
            $counter = 1;
            while (Scholarship::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $validatedData['avatar'] = $imagePath;
            $validatedData['application_processs'] = $request->application_processs;
            $validatedData['education_req'] = json_encode($request->education_req);
            $validatedData['tag'] = json_encode($request->tag);
            $validatedData['comany_link'] = $request->scholarship_link;
            $validatedData['is_featured'] = $request->has('is_featured') ? 1 : 0;
            $validatedData['is_scholarsip'] = $request->has('is_scholarsip') ? 1 : 0;
            $validatedData['contact_details'] = $request->looking_for;
            $validatedData['slug'] = $slug;
            $validatedData['status'] = $request->status;

            // Update the scholarship details
            $scholarship->update($validatedData);

            // Update contact scholarship details
            ContactScholorship::where('scholarship_id', $scholarship->id)->delete();
            foreach ($request->input('contact_names') as $key => $name) {
                ContactScholorship::create([
                    'name' => $name,
                    'scholarship_id' => $scholarship->id,
                    'email' => $request->input('contact_emails.' . $key),
                    'phone' => $request->input('contact_phones.' . $key),
                ]);
            }

            // Redirect back to the scholarship list page or show a success message
            return redirect()->route('admin.scholarship.list')->with('success', 'Scholarship details updated successfully');
        } else {
            return redirect()->route($path);
        }
    }

    public function view($id)
    {
        $path = user_permission($this->menu, 'view');
        if (!$path) {
            $scholarship = Scholarship::find($id);
            $scholarshipContactDetails = ContactScholorship::where('scholarship_id', $scholarship->id)->get();
            return view("admin.scholarship.view", compact('scholarship', 'scholarshipContactDetails'));
        } else {
            return redirect()->route($path);
        }
    }

    public function delete($id)
    {
        $path = user_permission($this->menu, 'delete');
        if (!$path) {
            $scholarship = Scholarship::whereId($id)->delete();
            DB::table('scholarship_applications')->where('scholarship_id', $id)->delete();
            DB::table('scholarship_question_applications')->where('scholarship_id', $id)->delete();
            if ($scholarship == 1) {
                return redirect()->route('admin.scholarship.list')->with('success', 'Scholarship Deleted successfully');
            }
        } else {
            return redirect()->route($path);
        }
    }

    public function applicationForm($id)
    {
        $path = user_permission($this->menu, 'add');
        if (!$path) {
            return view("admin.scholarship.application", compact('id'));
        } else {
            return redirect()->route($path);
        }
    }

    // public function applicationStore(Request $request)
    // {
    //     $path = user_permission($this->menu,'add');
    //     if(!$path){
    //         $saveQuestion = new ScholarshipQuestionApplication();
    //         $saveQuestion->scholarship_id = $request->scholarship_id;
    //         $saveQuestion->type = $request->type;
    //         $saveQuestion->question = $request->question;

    //         if ($saveQuestion->save()) {
    //             $options = [
    //                 'option_1' => $request->input('option_1'),
    //                 'option_2' => $request->input('option_2'),
    //                 'option_3' => $request->input('option_3'),
    //                 'option_4' => $request->input('option_4'),
    //                 // Add more options as needed
    //             ];

    //             foreach ($options as $key => $value) {
    //                 if ($value !== null) { // Check if the value exists in the request
    //                     ScholarshipOptionsApplications::create([
    //                         'keys_name' => $key,
    //                         'options' => $value,
    //                         'scholarship_question_id' => $saveQuestion->id,
    //                     ]);
    //                 }
    //             }

    //             // return redirect()->back()->with('success', 'Your question added successfully!');
    //             return redirect()->route('admin.scholarship.application_questions',$request->scholarship_id)->with('success', 'Scholarship Deleted successfully');
    //         }
    //     }else{
    //         return redirect()->route($path);
    //     }

    // }

    public function applicationStore(Request $request)
    {
        $path = user_permission($this->menu, 'add');
        if (!$path) {
            $saveQuestion = new ScholarshipQuestionApplication();
            $saveQuestion->scholarship_id = $request->scholarship_id;
            $saveQuestion->type = $request->type;
            $saveQuestion->question = $request->question;

            if ($saveQuestion->save()) {
                $options = [
                    'option_1' => $request->input('option_1'),
                    'option_2' => $request->input('option_2'),
                    'option_3' => $request->input('option_3'),
                    'option_4' => $request->input('option_4'),
                    // Add more options as needed
                ];

                foreach ($options as $key => $value) {
                    if ($value !== null) { // Check if the value exists in the request
                        ScholarshipOptionsApplications::create([
                            'keys_name' => $key,
                            'options' => $value,
                            'scholarship_question_id' => $saveQuestion->id,
                        ]);
                    }
                }

                // return redirect()->back()->with('success', 'Your question added successfully!');
                return redirect()->route('admin.scholarship.application_questions', $request->scholarship_id)->with('success', 'Scholarship Deleted successfully');
            }
        } else {
            return redirect()->route($path);
        }
    }


    public function applicationQuestions($id)
    {

        $questions = ScholarshipQuestionApplication::with('options')->where('scholarship_id', $id)->get();

        return view('admin.scholarship.questions', compact('questions', 'id'));
    }

    public function applicants($scholarship_id)
    {

        $id = $scholarship_id;
        $applicantsDetails = ScholarshipApplication::with(['user', 'scholarship'])->where('scholarship_id', $scholarship_id)->get();


        $states = State::get();
        return view('admin.scholarship.applicant_list', compact('applicantsDetails', 'states', 'id', 'scholarship_id'));
    }

    public function exportApplicants($scholarship_id)
    {
        return Excel::download(new ScholarshipApplicationExport($scholarship_id), 'applicants.xlsx');
    }

    public function updateStatus(Request $request)
    {
        $path = user_permission($this->menu, 'edit');
        if (!$path) {
            $scholarshipStatus = ScholarshipApplication::find($request->sch_id);
            $scholarshipStatus->status = $request->status;
            $scholarshipStatus->update();
            return response()->json(['message' => 'Success'], 200);
        } else {
            return redirect()->route($path);
        }
    }

    public function updateMultipleStatus(Request $request)
    {
        $customStatus = $request->input('customStatus');
        $status = $request->input('status');
        $userIds = explode(',', $request->input('user_id'));
        $studentId = $request->input('student_id');

        foreach ($userIds as $userId) {
            // Save data for each user ID
            DB::table('scholarship_applications')->insert([
                'descrition' => $customStatus,
                'status' => $status,
                'user_id' => $userId,
                'scholarship_id' => $studentId,
                // Add any other fields you need to save
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    public function applicants_details($id, $scholarship_id = null)
    {
        $applicantsDetail = User::with(['student.educationDetails', 'student.employmentDetails', 'student.guardianDetails', 'student.addressDetails', 'student.documents'])->find($id);
        $applicantsDetails = ScholarshipApplication::when(!empty($scholarship_id), function ($que) use ($scholarship_id) {
            $que->where('scholarship_id', (int)$scholarship_id)
            ->with([
                'scholarship:id,scholarship_name',
                'scholarship.scholarshipQuestionApplication:id,scholarship_id,type,question'
            ]);
        })->where('user_id', $applicantsDetail->id)->first();
        if (!empty($scholarship_id)) {
            return view('admin.scholarship.applicants_details', compact('applicantsDetail', 'scholarship_id', 'applicantsDetails'));
        } else {
            return view('admin.scholarship.applicants_details', compact('applicantsDetail', 'applicantsDetails'));
        }
    }

    public function applicants_filter(Request $request)
    {
        $id = $request->id;
        $states = State::get();
        $scholarship_id  = $request->input('id');


        $applicantsDetails = ScholarshipApplication::with(['user', 'scholarship'])
            ->when($request->filled('application_status'), function ($query) use ($request) {
                $query->where('status', $request->input('application_status'));
            })
            ->when($request->filled('gender'), function ($query) use ($request) {
                $query->whereHas('user', function ($userQuery) use ($request) {
                    $userQuery->where('gender', $request->input('gender'));
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('user.student', function ($studentQuery) use ($request) {
                    $studentQuery->where('category', $request->input('category'));
                });
            })
            ->when($request->filled('state'), function ($query) use ($request) {
                $query->whereHas('user', function ($studentQuery) use ($request) {
                    $studentQuery->where('state', $request->input('state'));
                });
            })
            ->when($request->filled('min_income') || $request->filled('max_income'), function ($query) use ($request) {
                $query->whereHas('user.student.guardianDetails', function ($studentQuery) use ($request) {
                    if ($request->filled('min_income')) {
                        $studentQuery->where('annual_income', '>=', $request->input('min_income'));
                    }

                    if ($request->filled('max_income')) {
                        $studentQuery->where('annual_income', '<=', $request->input('max_income'));
                    }
                });
            })

            ->where('scholarship_id', $request->input('id'))
            ->get();

        return view('admin.scholarship.applicant_list', compact('applicantsDetails', 'states', 'id', 'scholarship_id'));
    }

    public function verifydoc(Request $request)
    {
        $documenys = Document::find($request->id);
        if ($documenys->verified == 0 || $documenys->verified == null) {
            $documenys->verified = 1;
        } else {
            $documenys->verified = 0;
        }
        $documenys->update();

        return response()->json(['message' => 'Success'], 200);
    }

    public function updateScholorshipStatus(Request $request)
    {

        $path = user_permission($this->menu, 'edit');
        if (!$path) {
            $scholarshipStatus = ScholarshipApplication::where('scholarship_id', $request->sch_id)->where('user_id', $request->student_id)->first();
            $scholarshipStatus->status = $request->status;
            $scholarshipStatus->descrition = $request->customStatus;
            $scholarshipStatus->scholarship_id = $request->sch_id;
            $scholarshipStatus->user_id = $request->student_id;
            $scholarshipStatus->update();

            $scholarshipStatusNew = new Scholarshipstatus();
            $scholarshipStatusNew->scholarship_application_id = $scholarshipStatus->id;
            $scholarshipStatusNew->status =  $request->status;
            $scholarshipStatusNew->descss = $request->customStatus;
            $scholarshipStatusNew->save();

            $recentIds = Scholarshipstatus::select(DB::raw('MAX(id) as id'))
                ->groupBy('scholarship_application_id')
                ->pluck('id')
                ->toArray();

            // Update the 'button' column to be empty for all records except those with the recent IDs
            Scholarshipstatus::whereNotIn('id', $recentIds)
                ->update(['button' => null]);


            return response()->json(['message' => 'Success'], 200);
        } else {
            return redirect()->route($path);
        }
    }

    public function applicantDisbursal($user_id, $scholarship_id)
    {

        $distribution = AmountDistribution::where('user_id', $user_id)->where('scholarship_id', $scholarship_id)->get();

        return view('admin.scholarship.disbursal', compact('user_id', 'scholarship_id', 'distribution'));
    }

    public function marquee()
    {
        $path = user_permission($this->menu, 'edit');
        $data['marquee'] = DB::table('marque')->first();
        if (!$path) {
            return view("admin.scholarship.marquee", $data);
        } else {
            return redirect()->route($path);
        }
    }

    public function updateMarquee(Request $request)
    {
        $path = user_permission($this->menu, 'edit');
        if (!$path) {
            $marque = DB::table('marque')->whereId(1)->update([
                'description' => $request->description,
                'created_by' => auth()->user()->first_name,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            return redirect()->route('admin.scholarship.list')->with('Success', 'Marquee Updated Successfully.');
        } else {
            return redirect()->route($path);
        }
    }

    public function saveDisbursed(Request $request)
    {
        // Validate the request data
        $request->validate([
            'user_id' => 'required',
            'sch_id' => 'required',
            'amount' => 'array',
            'account_number' => 'array',
            'account_holder_name' => 'array',
            // 'receipt' => 'required',
        ]);
        try {
            DB::beginTransaction();
            // Access the data
            $userId = $request->user_id;
            $scholarshipId = $request->sch_id;

            AmountDistribution::where('user_id', $userId)
                ->where('scholarship_id', $scholarshipId)
                ->delete();
            $insert = [];

            for ($row = 0; $row <= (count($request->amount) - 1); $row++) {
                if (!empty($_FILES)) {
                    if ($_FILES['receipt']['name'][$row] != "") {
                        $fileName = time() . '.' . $request['receipt'][$row]->getClientOriginalExtension();
                        $request['receipt'][$row]->move(public_path('receipts'), $fileName);
                        $path = 'receipts/' . $fileName;
                    } else {
                        $path = $request['hidden_receipt_file'][$row];
                    }
                } else {
                    $path = $request['hidden_receipt_file'][$row];
                }
                $insert[] = [
                    'user_id' => $userId,
                    'scholarship_id' => $scholarshipId,
                    'amount' => $request['amount'][$row],
                    'account_number' => $request['account_number'][$row],
                    'account_holder_name' => $request['account_holder_name'][$row],
                    'receipt' => $path,
                    'created_at' => Carbon::now(),
                ];
            }
            AmountDistribution::insert($insert);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()]);
        }
        return response()->json(['message' => 'Data saved successfully']);
    }

    public function createMoving()
    {
        $gettext = SaveText::first();
        return view('admin.scholarship.movietext', compact('gettext'));
    }

    public function createMovingStore(Request $request)
    {

        $gettext = SaveText::first();

        $gettext->texts = $request->texts;
        $gettext->save();
        return redirect()->route('admin.scholarship.moving.textdd')->with('success', 'Added successfully');
    }
    public function deleteQuestions($id)
    {
        $deleteQuestion = ScholarshipQuestionApplication::find($id);
        $deleteQuestion->delete();
        return redirect()->back()->with('success', 'Your question Deleted successfully!');
    }

    public function applicantNotification($userid, $schid = null)
    {
        $applicantsDetails = Notification::where('user_id', $userid)->get();
        //    $applicantsDetails = Notification::where('user_id',$userid)->orwhere('sh_id',$schid)->get();
        return view('admin.scholarship.notification', compact('userid', 'schid', 'applicantsDetails'));
    }

    public function applicantNotificationSave(Request $request)
    {

        $savedata = new Notification();
        $savedata->title = $request->title;
        $savedata->teg = $request->teg;
        $savedata->description = $request->descrription;
        $savedata->author_name = 1;
        $savedata->user_id = $request->userid;
        $savedata->sh_id = $request->schid;
        $savedata->save();
        return redirect()->back()->with('success', 'Your Notification Saved successfully!');
    }

    public function deleteNotification($id)
    {

        $notification = Notification::find($id);
        $notification->delete();
        return redirect()->back()->with('success', 'Your Notification Deleted successfully!');
    }

    public function applicantNotificationmultiselectSave(Request $request)
    {

        // Retrieve form data
        $title = $request->input('title');
        $tag = $request->input('teg'); // Correct the input name
        $description = $request->input('descrription'); // Correct the input name

        // Retrieve the user_ids as an array


        $studentIds = str_replace(['[', ']', ' '], '', $request->input('student_ids'));
        $userIds = explode(',', $studentIds);

        // Loop through user_ids and save data to the database
        foreach ($userIds as $userId) {
            // Ensure user exists before creating notification
            if (User::where('id', $userId)->exists()) {

                $savedata = new Notification();
                $savedata->user_id = $userId;
                $savedata->title = $title;
                $savedata->teg = $tag;
                $savedata->description = $description;
                $savedata->author_name = 1;
                $savedata->save();
            }
        }

        return response()->json(['message' => 'Data saved successfully']);
    }


    public function deleteApplicant($userid, $shid)
    {

        $applicantsDetailsDetel = ScholarshipApplication::where('scholarship_id', $shid)->where('user_id', $userid)->delete();
        return redirect()->back()->with('success', 'Applicant deleted sucessfully!');
    }


    public function applicantDetele($user_id, $schId)
    {

        $deleteApplicat = ScholarshipApplication::where('user_id', $user_id)->where('scholarship_id', $schId)->first();
        $deleteApplicat->delete();
        return redirect()->back()->with('success', 'Applicant Deleted Successfully!');
    }
}
