<?php

namespace App\Http\Controllers\CommonController\Investment;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Models\Memberinvestments;
use App\Models\Plans;

use Carbon;

use URL;

use Auth;

class InvestmentReportController extends Controller
{

    /**
     * Report Daily Deposite
     * Emi Pending wills how
     **/



    public function dailyReport()
    {

        if (Auth::user()->role_id != 3) {
            if (check_my_permission(Auth::user()->id, "255") != "1") {

                return redirect()->route('admin.dashboard');
            }
        } else {
            if (!in_array('Daily Due Report', auth()->user()->getPermissionNames()->toArray())) {

                return redirect()->route('branch.dashboard');
            }
        }


        $data['title'] = 'Investment Management || Daily Due Report';
        $data['slug'] = 'daily';
        return view('templates/CommonViews/Investment.dailyduereport', $data);
    }


    /**
     * Report Monthly Deposite
     * Emi Pending wills how
     **/


    public function MonthlyReport()
    {


        if (Auth::user()->role_id != 3) {
            if (check_my_permission(Auth::user()->id, "256") != "1") {

                return redirect()->route('admin.dashboard');
            }
        } else {
            if (!in_array('Monthly Due Report', auth()->user()->getPermissionNames()->toArray())) {

                return redirect()->route('branch.dashboard');
            }
        }

        $data['title'] = 'Investment Management || Monthly Due Report';
        $data['slug'] = 'monthly';
        $data['plan'] = Plans::select('id', 'name', 'plan_category_code')->where('plan_category_code', "M")->get();

        return view('templates/CommonViews/Investment.dailyduereport', $data);
    }

    /**
     *Common function for daily and monthly 
     * 
     **/

    public static function getRecords($dailyDepositeRecord, $currentDate)
    {


        switch ($dailyDepositeRecord['plan']['plan_category_code']) {
            case 'D':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = strtotime($dailyDepositeRecord->created_at);

                $CURRENTDATE = strtotime($currentDate);

                $totalBetweendays = abs($investCreatedDate - $CURRENTDATE);
                $totalBetweendays = ceil(floatval($totalBetweendays / 86400));
                $totalAmount = ($totalBetweendays ) * $dailyDepositeRecord->deposite_amount;
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('is_deleted',0)->where('account_no', $dailyDepositeRecord->account_number)->sum('deposit');
                // dd($getRenewalReceivedAmount,$totalAmount);

                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi =  $pendingEmiAMount / $dailyDepositeRecord->deposite_amount;
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
            case 'M':
        
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord->created_at);
                $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
                $totalAmount = ($totalBetweenmonth + 1) * $dailyDepositeRecord->deposite_amount;
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord->account_number)->where('is_deleted', 0)->sum('deposit');


                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;

                    $pendingEmi = $dailyDepositeRecord->deposite_amount > 0 ? ($pendingEmiAMount / $dailyDepositeRecord->deposite_amount) : 0;
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi =  0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];

                break;
        }
    }

    public function dailyReportListing(Request $request)
    {
        $admin = (Auth::user()->role_id != 3) ? true : false;
        if ($admin == false) {
            $getBranchId = getUserBranchId(Auth::user()->id)->id;
        }
        if ($request->ajax()) {

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $dailyDepositeRecord = Memberinvestments::has('company')->select('id', 'created_at', 'account_number', 'deposite_amount', 'member_id', 'plan_id', 'associate_id', 'branch_id', 'tenure', 'customer_id', 'company_id')
                    ->with(['plan' => function ($q) {
                        $q->select('id', 'name', 'plan_category_code');
                    }])
                    ->with(['member' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'member_id', 'mobile_no');
                    }])
                    ->with('memberCompany:id,member_id')
                    ->with('company:id,name')
                    ->with(['associateMember' => function ($q) {
                        $q->select('id', 'first_name', 'last_name', 'associate_no');
                    }])
                    ->with(['branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }])->with(['investmentBalance' => function ($q){
                        $q->select('totalBalance','account_number');
                    }])
                    ->where('is_deleted', 0)->where('is_mature', 1);
                if ($request->slug == 'daily') {
                    $planCategoryCode = "D";
                    $dailyDepositeRecord = $dailyDepositeRecord->whereHas('plan', function ($query) use ($planCategoryCode) {
                        $query->where('plans.plan_category_code', $planCategoryCode);
                    });
                } elseif ($request->slug == 'monthly') {
                    $planCategoryCode = "M";
                    $dailyDepositeRecord = $dailyDepositeRecord->whereHas('plan', function ($query) use ($planCategoryCode) {
                        $query->where('plans.plan_category_code', $planCategoryCode);
                    });
                }
                $currentDate = Carbon\Carbon::now();
                $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
                //// marurity date ciondition 
                $dailyDepositeRecord = $dailyDepositeRecord->whereDate('maturity_date', '>=', $currentDate)->whereDate('created_at', '!=', $currentDate);

                if ($arrFormData['start_date'] != '') {

                    $startDate = $arrFormData['start_date'];
                    $currentDate = date("Y-m-d", strtotime(convertDate($startDate)));
                }
                if ($arrFormData['scheme_account_number'] != '') {

                    $sAccountNumber = $arrFormData['scheme_account_number'];

                    $dailyDepositeRecord = $dailyDepositeRecord->where('account_number', 'LIKE', '%' . $sAccountNumber . '%');
                }

                if ($arrFormData['associate_code'] != '') {

                    $associateCode = $arrFormData['associate_code'];

                    $dailyDepositeRecord = $dailyDepositeRecord->whereHas('associateMember', function ($query) use ($associateCode) {

                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }

                if ($request->slug == 'monthly') {
                    if ($arrFormData['plan'] != '') {
                        $plam = $arrFormData['plan'];
                        $dailyDepositeRecord = $dailyDepositeRecord->where('plan_id', $plam);
                    }
                }
                if ($arrFormData['company_id'] != '') {
                    if ($arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        if($company_id != '0') {
                            $dailyDepositeRecord = $dailyDepositeRecord->where('company_id', $company_id);
                        }
                    }
                }
                if ($arrFormData['customer_id'] != '') {

                    $customer_id = $arrFormData['customer_id'];


                    $dailyDepositeRecord = $dailyDepositeRecord->whereHas('member', function ($query) use ($customer_id) {

                        $query->where('members.member_id', $customer_id);
                    });
                }
                if ($admin == true) {
                    if ($arrFormData['branch_id'] != '') {
                        if ($arrFormData['branch_id'] > 0) {
                            $branch = $arrFormData['branch_id'];
                            if($branch != '0') {
                                $dailyDepositeRecord = $dailyDepositeRecord->where('branch_id', $branch);
                            }
                        }
                    }
                } else {
                    $dailyDepositeRecord = $dailyDepositeRecord->where('branch_id', $getBranchId);
                }




                $count = $dailyDepositeRecord->orderby('created_at', 'desc')->count('id');
                $data = $dailyDepositeRecord->orderby('created_at', 'desc')->offset($_POST['start'])->limit($_POST['length'])->get();


                $rowReturn = array();
                $totalCount = $count;
              

                foreach ($data as $sno => $row) {

                    $record = $this->getRecords($row, $currentDate);
                    if ($row->plan_id == 1) {
                        $tenure = 'N/A';
                    } else {
                        $tenure = $row->tenure . ' Year';
                    }

                    if ($row['associateMember']) {

                        $associate_Name = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
                    } else {

                        $associate_Name = "N/A";
                    }

                    if ($row['associateMember']) {

                        $associateNumber = $row['associateMember']['associate_no'];
                    } else {

                        $associateNumber = "N/A";
                    }

                    $sno++;

                    $val['DT_RowIndex'] = $sno;

                    $val['branch_name'] = $row['branch']->name . " (" . $row['branch']->branch_code . ")";
                    // $val['branch_code']=$row['branch']->branch_code;
                    $val['so_name'] = $row['branch']->sector;
                    $val['ro_name'] = $row['branch']->regan;
                    $val['zo_name'] = $row['branch']->zone;
                    $val['opening_date'] = date("d/m/Y", strtotime($row['created_at']));
                    $val['current_date'] = date("d/m/Y", strtotime($currentDate));

                    if (isset($row['member']->first_name)) {
                        $val['member'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                        $val['customer_id'] = $row['member']->member_id;
                    } else {
                        $val['member'] = 'N/A';
                        $val['member_id'] = 'N/A';
                    }

                    if (isset($row['memberCompany']->member_id)) {
                        $val['member_id'] = $row['memberCompany']->member_id;
                    } else {
                        $val['member_id'] = 'N/A';
                    }
                    if (isset($row['company']->name)) {
                        $val['company_name'] = $row['company']->name;
                    } else {
                        $val['company_name'] = 'N/A';
                    }

                    if ($row['member']->mobile_no) {
                        $val['mobile_no'] = $row['member']->mobile_no;
                    } else {
                        $val['mobile_no'] = 'N/A';
                    }
                    $val['balance'] = isset($row['investmentBalance']->totalBalance) ? $row['investmentBalance']->totalBalance : "N/A";  
                    $val['associate_code'] = $associateNumber;
                    $val['associate_name'] = $associate_Name;
                    $val['account_no'] = $row->account_number;
                    $val['plan_name'] = $row['plan']->name;
                    if ($row['plan']->plan_category_code == "S") {
                        $tenure = 'N/A';
                      } else {
                        $tenure = number_format((float)$row->tenure, 2, '.', '') . ' Year';
                      }
                    $val['tenure'] = $tenure;
                    $val['deno_amount'] = $row->deposite_amount;
                    if (isset($record['pendingEmi'])) {
                        $val['due_emi'] = $record['pendingEmi'];
                    } else {
                        $val['due_emi'] = 0;
                    }
                    if (isset($record['pendingEmiAMount'])) {
                        $val['due_emi_amount'] = $record['pendingEmiAMount'];
                    } else {
                        $val['due_emi_amount'] = 0;
                    }



                    $rowReturn[] = $val;
                }

                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn,);

                return json_encode($output);
            } else {
                $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0,);

                return json_encode($output);
            }
        }
    }

    public function export(Request $request)
    {
        $admin = (Auth::user()->role_id != 3) ? true : false;
        if ($admin == false) {
            $getBranchId = getUserBranchId(Auth::user()->id)->id;
        }

        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/due_report.csv";
        $fileName =  env('APP_EXPORTURL') . "/asset/due_report.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");

        $dailyDepositeRecord = Memberinvestments::has('company')->with('plan:id,name,plan_category_code', 'member:id,first_name,last_name,member_id,mobile_no', 'associateMember:id,first_name,last_name,associate_no', 'branch:id,name,branch_code,sector,regan,zone', 'company:id,name', 'memberCompany:id,member_id','investmentBalance:totalBalance,account_number')->where('is_deleted', 0)->where('is_mature', 1);
        if ($request->slug == 'daily') {
            $planCategoryCode = "D";
            $dailyDepositeRecord = $dailyDepositeRecord->whereHas('plan', function ($query) use ($planCategoryCode) {
                $query->where('plans.plan_category_code', $planCategoryCode);
            });
        } elseif ($request->slug == 'monthly') {
            $planCategoryCode = "M";
            $dailyDepositeRecord = $dailyDepositeRecord->whereHas('plan', function ($query) use ($planCategoryCode) {
                $query->where('plans.plan_category_code', $planCategoryCode);
            });
        }
        $currentDate = Carbon\Carbon::now();
        $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));

        if ($request['start_date'] != '') {

            $startDate = $request['start_date'];
            $currentDate = date("Y-m-d", strtotime(convertDate($startDate)));
        }

        // marurity date ciondition      
        $dailyDepositeRecord = $dailyDepositeRecord->whereDate('maturity_date', '>=', $currentDate)->whereDate('created_at', '!=', $currentDate);


        if ($request['scheme_account_number'] != '') {

            $sAccountNumber = $request['scheme_account_number'];
            $dailyDepositeRecord = $dailyDepositeRecord->where('account_number', 'LIKE', '%' . $sAccountNumber . '%');
        }
        if ($request['associate_code'] != '') {
            $associateCode = $request['associate_code'];
            $dailyDepositeRecord = $dailyDepositeRecord->whereHas('associateMember', function ($query) use ($associateCode) {
                $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
            });
        }
        if ($request['plan'] != '') {
            $plam = $request['plan'];
            $dailyDepositeRecord = $dailyDepositeRecord->where('plan_id', $plam);
        }

        if ($admin == true) {
            if ($request['branch_id'] != '') {
                if ($request['branch_id'] > 0) {
                    $branch = $request['branch_id'];
                    $dailyDepositeRecord = $dailyDepositeRecord->where('branch_id', $branch);
                }
            }
        } else {

            $dailyDepositeRecord = $dailyDepositeRecord->where('branch_id', $getBranchId);
        }

        if ($request['company_id'] != '') {
            if ($request['company_id'] > 0) {
                $company_id = $request['company_id'];
                $dailyDepositeRecord = $dailyDepositeRecord->where('company_id', $company_id);
            }
        }
        if ($request['customer_id'] != '') {

            $customer_id = $request['customer_id'];


            $dailyDepositeRecord = $dailyDepositeRecord->whereHas('member', function ($query) use ($customer_id) {

                $query->where('members.member_id', $customer_id);
            });
        }


        if ($request['investments_export'] == 0) {
            $totalResults = $dailyDepositeRecord->orderby('created_at', 'DESC')->count();
            $results = $dailyDepositeRecord->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();

            $result = 'next';
            if (($start + $limit) >= $totalResults) {
                $result = 'finished';
            }

            // if its a fist run truncate the file. else append the file
            if ($start == 0) {
                $handle = fopen($fileName, 'w');
            } else {
                $handle = fopen($fileName, 'a');
            }



            if (($start + $limit) >= $totalResults) {
                $result = 'finished';
            }
            // if its a fist run truncate the file. else append the file
            if ($start == 0) {
                $handle = fopen($fileName, 'w');
            } else {
                $handle = fopen($fileName, 'a');
            }
            if ($start == 0) {
                $headerDisplayed = false;
            } else {
                $headerDisplayed = true;
            }

            $sno = $_POST['start'];

            foreach ($results as  $row) {
                $record = $this->getRecords($row, $currentDate);
                if ($row->plan_id == 1) {
                    $tenure = 'N/A';
                } else {
                    $tenure = $row->tenure . ' Year';
                }

                if ($row['associateMember']) {

                    $associate_Name = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
                } else {

                    $associate_Name = "N/A";
                }

                if ($row['associateMember']) {

                    $associateNumber = $row['associateMember']['associate_no'];
                } else {

                    $associateNumber = "N/A";
                }

                $sno++;

                $val['s/n'] = $sno;
                $val['Company name'] = $row['company']->name;
                $val['Branch name'] = $row['branch']->name;
                $val['Branch code'] = $row['branch']->branch_code;
                $val['so_name'] = $row['branch']->sector;
                $val['ro_name'] = $row['branch']->regan;
                $val['zo_name'] = $row['branch']->zone;
                $val['opening_date'] = date("d/m/Y", strtotime($row['created_at']));
                $val['current_date'] = date("d/m/Y", strtotime($currentDate));
                if (isset($row['member']->first_name)) {
                    $val['member'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                    $val['customer_id'] = $row['member']->member_id;
                } else {
                    $val['member'] = 'N/A';
                    $val['customer_id'] = 'N/A';
                }

                if (isset($row['memberCompany']->member_id)) {
                    $val['member_id'] = $row['memberCompany']->member_id;
                } else {
                    $val['member_id'] = 'N/A';
                }

                if ($row['member']->mobile_no) {
                    $val['Mobile No'] = $row['member']->mobile_no;
                } else {
                    $val['Mobile No'] = 'N/A';
                }

                $val['associate code'] = $associateNumber;
                $val['associate name'] = $associate_Name;
                $val['account no'] = $row->account_number;
                $val['plan name'] = $row['plan']->name;
                if ($row['plan']->plan_category_code == "S") {
                    $tenure = 'N/A';
                  } else {
                    $tenure = number_format((float)$row->tenure, 2, '.', '') . ' Year';
                  }
                $val['tenure'] = $tenure;
                $val['balance'] = $row['investmentBalance'] ? $row['investmentBalance']->totalBalance:'N/A';
                $val['deno amount'] = $row->deposite_amount;

                if (isset($record['pendingEmi'])) {
                    $val['due emi'] = $record['pendingEmi'];
                } else {
                    $val['due emi'] = 0;
                }
                if (isset($record['pendingEmiAMount'])) {
                    $val['due emi amount'] = $record['pendingEmiAMount'];
                } else {
                    $val['due emi amount'] = 0;
                }

                if (!$headerDisplayed) {
                    // Use the keys from $data as the titles
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                // Put the data into the stream
                fputcsv($handle, $val);
            }
            fclose($handle);
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float)$percentage, 1, '.', '');

            // Output some stuff for jquery to use
            $response = array(
                'result'        => $result,
                'start'         => $start,
                'limit'         => $limit,
                'totalResults'  => $totalResults,
                'fileName' => $returnURL,
                'percentage' => $percentage
            );
            echo json_encode($response);
            // Make sure nothing else is sent, our file is done
            exit;
        }
    }
    public  function getmyplan(Request $request)
    {
        if ($request->company_id > 0) {
            $plan = Plans::where('company_id', $request->company_id)->where('plan_category_code', "M")->get(['id', 'name']);
        } else {
            $plan = Plans::where('plan_category_code', "M")->get(['id', 'name']);
        }

        $html = '<option value="">---Please Select Plan --- </option>';
        foreach ($plan as $val) {
            $html .= '<option value="' . $val->id . '">' . $val->name . '</option>';
        }
        print_r($html);
    }
}
// common