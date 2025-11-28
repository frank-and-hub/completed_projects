<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Scholarship\Scholarship;
use App\Models\ScholarshipApplication\ScholarshipApplication;
use DB;
use Session;
use Artisan;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        return view('admin.dashboard');
    }


    public function post_dashboard(Request $req)
    {
        // dd($req->all());
        $currentDate = Carbon::now();
        $category = $req->category;
        $minority = $req->minority;
        $parents_or_guardian = $req->parents_or_guardian;
        $work_experence = $req->work_experence;
        $profession = $req->profession;
        $status = $req->status;
        $House_Type = $req->House_Type;
        $state = $req->state;
        $districts = $req->districts;
        $start_date = date('Y-m-d', strtotime($req->start_date));
        $end_date = date('Y-m-d', strtotime($req->end_date));
        $sponsor_name = $req->sponsor_name;
        $scholarship = $req->scholarship;
        $age_range = $req->age_range;
        $cumulative = $req->cumulative;
        $make_date = (int) ($req->date) ?? date('d');
        $month = (int) $req->month ?? date('m');
        $year = (int) $req->year ?? date('Y');
        $gender = $req->gender;
        $date = date('Y-m-d', strtotime($year . "-" . $month . "-" . $make_date));

        $data['totalNoOfScholersApplied'] = $this->baseQuery($req->all())
            // ->pluck('scholarship_applications.user_id','scholarship_applications.id');
            ->count('scholarship_applications.id');
        $amount_distribution = DB::table('amount_distribution')
            ->select(
                DB::raw('COUNT(DISTINCT user_id) as totalNoOfStudentsBenefited'),
                DB::raw('SUM(amount) as amountDistribution'),
                DB::raw('COUNT(id) as distributionamountpercentage'),
            )
            ->when(($scholarship > 0), function ($q) use ($scholarship) {
                $q->where('scholarship_id', $scholarship);
            })
            ->first();

        $data['totalNoOfStudentsBenefited'] = $amount_distribution->totalNoOfStudentsBenefited;
        $data['amountDistribution'] = $amount_distribution->amountDistribution;
        $data['distributionamountpercentage'] = $amount_distribution->distributionamountpercentage;

        // $data['totaldistributionamountpercentage'] = DB::table('amount_distribution')->count('id');
        // dd($data['totalNoOfScholersApplied'],$data['distributionamountpercentage']);

        $data['scholarshipTable'] = Scholarship::when($scholarship > 0, function ($q) use ($scholarship) {
            $q->where('id', $scholarship);
        })
            ->when($sponsor_name > 0, function ($q) use ($sponsor_name) {
                $q->where('company_id', $sponsor_name);
            })->get();

        $genderOther = $this->baseQuery($req->all())
            // ->select(\DB::raw('COUNT("users.id") as count'))
            ->where('users.gender', 'other')
            // ->get()
            // ->toArray();
            ->count(\DB::raw('users.id'));

        $genderMale = $this->baseQuery($req->all())
            // ->select(\DB::raw('COUNT("users.id") as count'))
            ->where('users.gender', 'male')
            // ->get()
            // ->toArray();
            ->count(\DB::raw('users.id'));

        $genderFemale = $this->baseQuery($req->all())
            // ->select(\DB::raw('COUNT("users.id") as count'))
            ->where('users.gender', 'female')
            // ->get()
            // ->toArray();
            ->count(\DB::raw('users.id'));

        $data['genderOther'] = $genderOther;        // $data['genderOther'] = $genderOther[0]->count;
        $data['genderMale'] = $genderMale;        // $data['genderMale'] = $genderMale[0]->count;
        $data['genderFemale'] = $genderFemale;        // $data['genderFemale'] = $genderFemale[0]->count;

        $data['totalgendercount'] = $this->baseQuery($req->all())
            ->count(\DB::raw('users.id'));

        // $data['totalgendercount'] = $data['genderOther'] + $data['genderMale'] + $data['genderFemale'];

        $data['apexScatter'] = ScholarshipApplication::whereHas('user', function ($query) use ($gender, $currentDate, $age_range, $state) {
            $query->where('users.role_id', 2)
                ->when($gender != 0, function ($q) use ($gender) {
                    $q->where('gender', $gender);
                })
                ->when($age_range != 50, function ($q) use ($currentDate, $age_range) {
                    $q->whereRaw("TIMESTAMPDIFF(YEAR, date_of_birth, '$currentDate') <= $age_range");
                })
                ->when($state > 0, function ($q) use ($state) {
                    $q->whereState($state);
                });
        })
            ->has('scholarship')
            ->whereDate('scholarship_applications.applied_at', '>=', $date)
            ->when(($start_date != '1970-01-01' && $end_date != '1970-01-01'), function ($q) use ($start_date, $end_date) {
                $q->whereBetween('scholarship_applications.applied_at', [$start_date, $end_date]);
            })
            ->with('user:id,date_of_birth,role_id', 'scholarship:id')
            ->get();

        $familyincome = [];
        $income = [];

        $family = $this->baseQuery($req->all())
            ->groupBy('guardian_details.annual_income')
            ->orderBy('guardian_details.annual_income', 'asc')
            ->select('guardian_details.annual_income')
            ->get();

        foreach ($family as $k => $v) {
            array_push($income, round($v->annual_income));
        }
        $dividedIncome = dividedIncome($income);
        $data['familyincome'] = averages($dividedIncome);

        $totalsocialgroup = $this->baseQuery($req->all())
            ->when($category != 0, function ($q) use ($category) {
                $q->where('students.category', 'like', '%' . $category . '%');
            })
            ->count(\DB::raw('students.id'));

        $sc = $this->baseQuery($req->all())
            ->where('students.category', 'sc')
            ->count(\DB::raw('students.id'));

        $st = $this->baseQuery($req->all())
            ->where('students.category', 'st')
            ->count(\DB::raw('students.id'));

        $obc = $this->baseQuery($req->all())
            ->whereIn('students.category', ['obc nc', 'obc c'])
            ->count(\DB::raw('students.id'));

        $gen = $this->baseQuery($req->all())
            ->where('students.category', 'general')
            ->count(\DB::raw('students.id'));

        $data['social_sc'] = number_format(calculatePercentage($sc, $totalsocialgroup), 2, '.');
        $data['social_st'] = number_format(calculatePercentage($st, $totalsocialgroup), 2, '.');
        $data['social_obc'] = number_format(calculatePercentage($obc, $totalsocialgroup), 2, '.');
        $data['social_gen'] = number_format(calculatePercentage($gen, $totalsocialgroup), 2, '.');
        $data['totalsocialgroup'] = $totalsocialgroup;

        $totalminority = $this->baseQuery($req->all())
            ->when($minority != 0, function ($q) use ($minority) {
                $q->whereNotNull('students.minority_group')
                    ->where('students.minority_group', 'LIKE', '%' . $minority . '%');
            })
            ->count(\DB::raw('students.id'));

        $muslim = $this->baseQuery($req->all())
            ->where('students.minority_group', 'muslim')
            ->count(\DB::raw('students.id'));

        $zoroastrians = $this->baseQuery($req->all())
            ->where('students.minority_group', 'zoroastrians')
            ->count(\DB::raw('students.id'));

        $buddhist = $this->baseQuery($req->all())
            ->where('students.minority_group', 'buddhist')
            ->count(\DB::raw('students.id'));

        $christian = $this->baseQuery($req->all())
            ->where('students.minority_group', 'christian')
            ->count(\DB::raw('students.id'));

        $sikh = $this->baseQuery($req->all())
            ->where('students.minority_group', 'sikh')
            ->count(\DB::raw('students.id'));

        $jain = $this->baseQuery($req->all())
            ->where('students.minority_group', 'jain')
            ->count(\DB::raw('students.id'));

        $data['minority_muslim'] = number_format(calculatePercentage($muslim, $totalminority), 2, '.');
        $data['minority_jain'] = number_format(calculatePercentage($jain, $totalminority), 2, '.');
        $data['minority_sikh'] = number_format(calculatePercentage($sikh, $totalminority), 2, '.');
        $data['minority_christian'] = number_format(calculatePercentage($christian, $totalminority), 2, '.');
        $data['minority_buddhist'] = number_format(calculatePercentage($buddhist, $totalminority), 2, '.');
        $data['minority_zoroastrians'] = number_format(calculatePercentage($zoroastrians, $totalminority), 2, '.');
        $data['totalminority'] = $totalminority;

        $apexScatter_hidden_array = $this->baseQuery($req->all())
            ->select([
                // DB::raw('AVG(total_applications) as avg_applications'), // Calculate the average of total applications
                DB::raw('COUNT(scholarship_applications.user_id) as total_applications'),
                DB::raw('TIMESTAMPDIFF(YEAR, users.date_of_birth, CURDATE()) as age')
            ])
            ->groupBy('age')
            ->get()
            ->toArray();

        $apexScatter_array = [];
        foreach ($apexScatter_hidden_array as $k => $v) {
            $apexScatter_array[$k][0] = (int) $v->total_applications;
            $apexScatter_array[$k][1] = (int) $v->age;
        }

        $data['apexScatter_hidden_array'] = json_encode($apexScatter_array);

        $compappprogression = DB::table('scholarship_applications')
            ->select(DB::raw('count(status) as compap_total'), 'status')
            ->groupBy('status')
            ->get();

        $data['compappprogression_count'] = $compappprogression_count = DB::table('scholarship_applications')->count('id');

        $compappprogression_msg = "";
        $compappprogression_p = "";
        foreach ($compappprogression as $k => $v) {
            // $compappprogression_msg .= '"' . ucwords(str_replace("_", " ", $v->status)) . ' ' . round(calculatePercentage($v->compap_total, $compappprogression_count)) . '%"' . ((count($compappprogression) != ($k + 1)) ? ', ' : '');
            $compappprogression_msg .= '"' . ucwords(str_replace("_", " ", $v->status)) . '"' . ((count($compappprogression) != ($k + 1)) ? ', ' : '');
            $compappprogression_p .= round(calculatePercentage($v->compap_total, $compappprogression_count)) . '' . ((count($compappprogression) != ($k + 1)) ? ', ' : '');
        }

        $data['compappprogression'] = "[" . $compappprogression_msg . "]";
        $data['compappprogressionp'] = "[" . $compappprogression_p . "]";
        // dd($data);
        return \Response::json(['view' => view('admin.dashboard_filter_data', $data)->render(), 'msg_type' => 'success']);
    }

    public function dashboard_scholarship(Request $request)
    {

        $companyId = $request->companyId;

        $data = Scholarship::where('company_id', $companyId)->select('scholarship_name', 'id', 'company_id')->get();
        return $data;
    }

    private function calculateAge($dob)
    {
        $today = Carbon::now();
        $birthdate = Carbon::parse($dob);
        $age = $today->diffInMonths($birthdate) / 12; // Calculate age in years

        return round($age, 1); // Return rounded age
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('admin.login'); // Redirect to the home page or any other desired page.
    }

    public function baseQuery($req)
    {
        $currentDate = Carbon::now();
        $category = $req['category'];
        $minority = $req['minority'];
        $parents_or_guardian = $req['parents_or_guardian'];
        $work_experence = $req['work_experence'];
        $profession = $req['profession'];
        $status = $req['status'];
        $House_Type = $req['House_Type'];
        $state = $req['state'];
        $districts = $req['districts'];
        $startDate = date('Y-m-d', strtotime($req['start_date']));
        $endDate = date('Y-m-d', strtotime($req['end_date']));
        $sponsor_name = $req['sponsor_name'];
        $scholarship = $req['scholarship'];
        $ageRange = $req['age_range'];
        $cumulative = $req['cumulative'];
        $make_date = ($req['date'] != 0) ? (int) $req['date'] : date('d');
        $month = ($req['month'] != 0) ? (int) $req['month'] : date('m');
        $year = ($req['year'] != 0) ? (int) $req['year'] : date('Y');
        $gender = $req['gender'];
        $date = date('Y-m-d', strtotime($year . "-" . $month . "-" . $make_date));

        $data = DB::table('scholarship_applications')
            ->join('users', 'scholarship_applications.user_id', '=', 'users.id')
            ->join('students', 'users.id', '=', 'students.user_id')
            ->join('guardian_details', 'students.id', '=', 'guardian_details.student_id')
            ->join('employment_details', 'students.id', '=', 'employment_details.student_id')
            ->join('address_details', 'students.id', '=', 'address_details.student_id')
            ->join('scholarships', 'scholarship_applications.scholarship_id', '=', 'scholarships.id')
            ->where('users.role_id', '=', 2)
            ->whereDate('scholarship_applications.applied_at', '<=', $date)
            ->whereDate('scholarship_applications.applied_at', '>', '2024-03-13')
            ->when(($scholarship > 0), function ($q) use ($scholarship) {
                $q->where('scholarship_applications.scholarship_id', $scholarship);
            })
            ->when(($sponsor_name > 0), function ($q) use ($sponsor_name) {
                $q->where('scholarships.company_id', $sponsor_name);
            })
            ->when($gender != 0, function ($q) use ($gender) {
                $q->where('users.gender', $gender);
            })
            ->when($ageRange != 50, function ($q) use ($currentDate, $ageRange) {
                $q->whereRaw("TIMESTAMPDIFF(YEAR, users.date_of_birth, '$currentDate') <= $ageRange");
            })
            ->when($state != 0, function ($q) use ($state) {
                $q->where('users.state', $state);
            })
            ->when($districts != 0, function ($q) use ($districts) {
                $q->whereNotNull('users.district')->where('users.district', $districts);
            })
            ->when($House_Type != 0, function ($q) use ($House_Type) {
                $q->where('address_details.type', '=', 'current')->where('address_details.house_type', 'like', '%' . $House_Type . '%');
            })
            ->distinct('users.id')
            ->when($profession != 0, function ($q) use ($profession) {
                $q->whereNotNull('users.user_type')->where('users.user_type', 'like', '%' . $profession . '%');
            })
            ->when($status != 0, function ($q) use ($status) {
                $q->whereNotNull('users.looking_for')->where('users.looking_for', 'like', '%' . $status . '%');
            })
            ->when($category != 0, function ($q) use ($category) {
                $q->where('students.category', 'like', '%' . $category . '%')
                    ->orwhere('students.category', $category);
            })
            ->when($parents_or_guardian != 0, function ($q) use ($parents_or_guardian) {
                $q->where('guardian_details.relationship', 'like', '%' . $parents_or_guardian . '%');
            })
            ->when($work_experence != 0, function ($q) use ($work_experence) {
                $q->where('employment_details.employment_type', 'like', '%' . $work_experence . '%');
            })
            ->when($minority != 0, function ($q) use ($minority) {
                $q->whereNotNull('students.minority_group')
                    ->where('students.minority_group', 'LIKE', '%' . $minority . '%');
            })
            ->when(($startDate != '1970-01-01' && $endDate != '1970-01-01'), function ($q) use ($startDate, $endDate) {
                $q->whereBetween('scholarship_applications.applied_at', [$startDate, $endDate]);
            });

        return $data;
    }

    public function deleteData($sql)
    {
        return Artisan::call($sql);
        // echo "<pre>"; 
        // print_r(session()->all());
        // echo "</pre>";



        // $a = DB::table('users')->whereIn('id', [
        //     49,50,51,52,53,63,64,67,68,69,71,72,73,74,75,76,77,81,86,87,88,91,96,99,101,102,103,104,105,106,110,112,113,116,117,118,119,120,121,122,125,129,145,146,147,149,150,154,155,156,157,158,163,164,166,168,169,171,172,173,174,175,176,177,179,180,182
        // ])->delete();
        // $b = DB::table('students')->get()->toArray();
        // $c = DB::table('scholarship_applications')->whereIn('user_id',[49,50,51,52,53,63,64,67,68,69,71,72,73,74,75,76,77,81,86,87,88,91,96,99,101,102,103,104,105,106,110,112,113,116,117,118,119,120,121,122,125,129,145,146,147,149,150,154,155,156,157,158,163,164,166,168,169,171,172,173,174,175,176,177,179,180,182])->delete();

        // dd($b);
    }
}
