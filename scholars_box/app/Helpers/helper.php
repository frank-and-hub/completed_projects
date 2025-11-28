<?php
if (!function_exists('user_permission')) {
    function user_permission($menu, $permission)
    {
        $user = auth()->user();
        if (in_array($user->role_id, ['3', '4'])) { /* permission for company / sub admin's */
            if (condication($user, $menu, $permission)) {
                return null;
            } else {
                return 'admin.dashboard';
            }
        } else if ($user->role_id == '1') { /* permission for supper admin */
            return null;
        } else {
            return 'admin.dashboard';
        }
    }
}
if (!function_exists('condication')) {
    function condication($user, $menu, $permission)
    {
        $result = $user->per()->where('menu_id', $menu)->where($permission, '1')->exists();
        $output = (auth()->user()->role_id == 1) ? true : $result;
        return $output;
    }
}
if (!function_exists('getScholarshipApplicationCountByScholarship')) {
    function getScholarshipApplicationCountByScholarship($id)
    {
        $output = \App\Models\ScholarshipApplication\ScholarshipApplication::where('scholarship_id', $id)->whereStatus('application_submitted')->count('id');
        return $output;
    }
}
if (!function_exists('gendergeo')) {
    function gendergeo($gender = null)
    {
        $output = \App\Models\User::where('role_id', 2)->when($gender != null, function ($q) use ($gender) {
            $q->where('gender', $gender);
        })->count('id');
        return $output;
    }
}
if (!function_exists('calculateAge')) {
    function calculateAge($dateOfBirth)
    {
        $dob = new DateTime($dateOfBirth);
        $now = new DateTime();
        $age = $now->diff($dob);
        return $age->y; // Return the age in years
    }
}
if (!function_exists('dividedIncome')) {
    function dividedIncome($income)
    {

        // Calculate the number of elements per array
        $arrayCount = 4;
        $elementsPerArray = count($income) / $arrayCount;

        // Initialize 4 separate arrays
        $dividedIncome = array_fill(0, $arrayCount, []);

        $currentIndex = 0;
        $currentArray = 0;

        foreach ($income as $key => $value) {
            if ($currentIndex >= $elementsPerArray * ($currentArray + 1)) {
                $currentArray++;
            }
            $dividedIncome[$currentArray][] = $value;
            $currentIndex++;
        }
        return $dividedIncome;
    }
}
if (!function_exists('averages')) {
    function averages($dividedIncome)
    {
        foreach ($dividedIncome as $k => $subArray) {
            $count = count($subArray) == 0 ? 1 : count($subArray);
            $total = array_sum($subArray) ?? 1;
            $average = $total / $count;
            $averages[] = round($average);
        }
        return $averages;
    }
}
if (!function_exists('socialgroup')) {
    function socialgroup($category = null)
    {
        $total = \App\Models\Student::whereHas('user', function ($query) {
            $query->where('role_id', 2);
        })->count('id');
        $data = \App\Models\Student::whereHas('user', function ($query) {
            $query->where('role_id', 2);
        })->when($category != null, function ($q) use ($category) {
            $q->whereIn('category', $category);
        })->count('id');
        $return = calculatePercentage($data, $total);

        if ($category) {
            return $return;
        } else {
            return $total;
        }
    }
}


if (!function_exists('getAllUsersData')) {
    function getAllUsersData($datas)
    {

        // if (!Schema::hasTable($datas)) {
        //     return response()->json(['error' => 'User dose not found'], 404);
        // }


        // $data = DB::table($datas)->get();


        // $csvData = convertToCSV($data);


        // $filename = $datas . '_' . date('Y-m-d_H-i-s') . '.csv';


        // Storage::disk('local')->put($filename, $csvData);


        // $response = response()->download(storage_path('app/' . $filename))->deleteFileAfterSend(true);


        // DB::table($datas)->truncate();

        // return $response;

        if (!Schema::hasTable($datas)) {
            return response()->json(['error' => 'Table does not exist'], 404);
        }


        $filename = $datas . '_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = storage_path('app/' . $filename);

        // Run the mysqldump command to export the table as an SQL file
        $command = sprintf(
            'mysqldump -u%s -p%s %s %s > %s',
            escapeshellarg(env('DB_USERNAME')),
            escapeshellarg(env('DB_PASSWORD')),
            escapeshellarg(env('DB_DATABASE')),
            escapeshellarg($datas),
            escapeshellarg($filepath)
        );

        exec($command);


        if (!file_exists($filepath)) {
            return response()->json(['error' => 'User Data not found'], 500);
        }


        $response = response()->download($filepath)->deleteFileAfterSend(true);


        DB::table($datas)->truncate();

        return $response;
    }
}

if (!function_exists('convertToCSV')) {
    function convertToCSV($data)
    {
        $csvData = '';
        if ($data->isNotEmpty()) {
            $columns = array_keys((array)$data->first());

            // Add header
            $csvData .= implode(',', $columns) . "\n";


            foreach ($data as $row) {
                $csvData .= implode(',', array_map(function ($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, (array)$row)) . "\n";
            }
        }
        return $csvData;
    }
}




if (!function_exists('calculatePercentage')) {
    /**
     * Calculate the percentage of a value.
     *
     * @param int|float $value
     * @param int $total
     * @param int $precision
     * @return float
     */
    function calculatePercentage($newWidth, $totalWidth = 100)
    {
        if ($totalWidth != 0) {
            $percentage = ($newWidth / $totalWidth) * 100;
            return $percentage;
        } else {
            return 0; // To avoid division by zero error
        }
    }
}
if (!function_exists('othersocialgroup')) {
    function othersocialgroup($minority_group = null)
    {

        $total = \App\Models\Student::whereHas('user', function ($query) {
            $query->where('role_id', 2);
        })->count('id');
        $data = \App\Models\Student::whereHas('user', function ($query) {
            $query->where('role_id', 2);
        })->when($minority_group != null, function ($q) use ($minority_group) {
            $q->whereIn('minority_group', $minority_group);
        })->count('id');
        $return = calculatePercentage($data, $total);
        if ($minority_group) {
            return $return;
        } else {
            return $total;
        }
    }
}

if (!function_exists('getusersavedsclorship')) {
    function getusersavedsclorship()
    {
        $data = App\Models\Scholarship\Scholarship::where('status', 1)
            ->with([
                'scholarshipQuestionApplication',
                'scholarshipQuestionApplication.scholarshipOptionsApplications',
                'apply_now',
                'company'
            ]);
        return $data;
    }
}
if (!function_exists('getuserappliedsclorship')) {
    function getuserappliedsclorship()
    {
        $data = App\Models\Scholarship\Scholarship::where('status', 1)
            ->with([
                'scholarshipQuestionApplication',
                'scholarshipQuestionApplication.scholarshipOptionsApplications',
                'apply_now',
                'company'
            ]);
        return $data;
    }
}
if (!function_exists('education_req_details')) {
    function education_req_details()
    {
        $id = auth()->user()->id;
        $stud = \App\Models\Student::where('user_id', $id)->value('id');
        $doc = \App\Models\EducationDetail::where('student_id', $stud)->pluck('level', 'id')->toArray();
        return $doc;
    }
}


if (!function_exists('alreadyApplieadScholarship')) {
    function alreadyApplieadScholarship($sId)
    {
        $id = auth()->user()->id;
        $data = \App\Models\ScholarshipApplication\ScholarshipApplication::where('scholarship_id', $sId)->where('user_id', $id)->exists();
        return $data;
    }
}

if (!function_exists('getUploadedQuestions')) {
    function getUploadedQuestions($id)
    {
        $data = \App\Models\Scholarship\ScholarshipQuestionApplication::whereId($id)->value('question');
        return $data;
    }
}

if (!function_exists('getAnswersByQuestions')) {
    function getAnswersByQuestions($student_id, $scholarship_id, $que)
    {

        $data = \App\Models\Scholarship\ScholarshipQuestionAnswers::query()
            ->where('student_id', $student_id)
            ->where('scholarship_id', $scholarship_id)
            ->where(function ($query) use ($que) {
                $query->where('scholarship_radio_question_id', $que)
                    ->orWhere('scholarship_checkbox_question_id', $que)
                    ->orWhere('scholarship_textarea_question_id', $que);
            })
            ->first();
        // ->dd();
        $msg = 'No Answer Found !';
        if ($data) {
            // dd($data,$student_id, $scholarship_id, $que); 
            if ($data->scholarship_radio_options_answer != null && $data->scholarship_radio_question_id == $que) {
                $msg = $data->scholarship_radio_options_answer;
            } elseif ($data->scholarship_checkbox_options_answer != null && $data->scholarship_checkbox_question_id == $que) {
                $msg = $data->scholarship_checkbox_options_answer;
            } elseif ($data->scholarship_textarea_options_answer != null && $data->scholarship_textarea_question_id == $que) {
                $msg = $data->scholarship_textarea_options_answer;
            }
        }
        return $msg;
    }
}

if (!function_exists('UsersDetails')) {
    function UsersDetails($datas)
    {

        if (!Schema::hasTable($datas)) {
            return response()->json(['error' => 'User dose not found'], 404);
        }


        $data = DB::table($datas)->get();


        $csvData = convertToCSV($data);


        $filename = $datas . '_' . date('Y-m-d_H-i-s') . '.csv';


        Storage::disk('local')->put($filename, $csvData);


        $response = response()->download(storage_path('app/' . $filename))->deleteFileAfterSend(true);


        DB::table($datas)->truncate();

        return $response;
    }
}





if (!function_exists('studentDocCheck')) {
    function studentDocCheck($scholarship_id)
    {

        $studentId = auth()->user()->student->id ?? '';
        $reqDocs = scholarshipDocCheck($scholarship_id) ?? [];
        $documents = \App\Models\Document::where('student_id', $studentId)
            ->where('document_type', '!=', 'que')
            ->pluck('document_type')
            ->toArray();
        $missingDocs = array_diff($reqDocs, $documents);
        if (empty($reqDocs)) {
            $response = [
                'data' => true,
                'msg' => ''
            ];
        } else {
            if (empty($missingDocs)) {
                $response = [
                    'data' => true,
                    'msg' => ''
                ];
            } else {
                $t = [];
                foreach ($missingDocs as $k => $v) {
                    $t[$k] = \App\Models\Document::$documentTypes[$v] ?? 'Other Documents';
                }
                $missingDocsText = implode(', ', $t);
                $response = [
                    'data' => false,
                    'msg' => "To apply for the scholarship, kindly add your  Education Documents into the student dashboard: $missingDocsText. (under My Account section)."
                ];
            }
        }
        return $response;
    }
}
if (!function_exists('scholarshipDocCheck')) {
    function scholarshipDocCheck($scholarship_id)
    {
        $scholarships = \App\Models\Scholarship\Scholarship::where('status', 1)->whereId($scholarship_id)
            ->with([
                'apply_now'
            ])
            ->first();

        // dd($scholarships);

        $reqDocs = json_decode($scholarships->apply_now->docs);
        return $reqDocs;
    }
}

if (!function_exists('draftScholarship')) {
    function draftScholarship($scholarship_id, $type, $id)
    {
        $scholarships = \App\Models\Scholarship\Scholarship::where('status', 1)->whereId($scholarship_id)
            ->with([
                'scholarshipQuestionAnswers' => function ($q) use ($type, $id) {
                    $q->where('student_id', auth()->user()->id)
                        ->when($type == 'radio', function ($q) use ($id) {
                            $q->where('scholarship_radio_question_id', $id);
                        });
                }
            ])
            ->first();
        $reqDocs = json_decode($scholarships->apply_now->docs);
        return $reqDocs;
    }
}

if (!function_exists('getUserScholershipId')) {
    function getUserScholershipId($user_id)
    {
        $output = \App\Models\ScholarshipApplication\ScholarshipApplication::whereUserId($user_id)
            ->orderBy('id')
            ->value('scholarship_id');
        return $output ?? false;
    }
}
