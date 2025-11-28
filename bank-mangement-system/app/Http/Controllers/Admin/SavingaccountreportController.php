<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Plans,Branch};
use DB;
use Session;
use URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SavingaccountreportController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the investment plans.
     *
     * @return \Illuminate\Http\Response
     */
    public function savingaccountreport()
    {
        if (check_my_permission(Auth::user()->id, "23") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Saving Listing";
        $data['branch'] = Branch::where('status', 1)->get(['id', 'name']);
        $data['plans'] = Plans::where('status', 1)->whereNotIn('id', array(1, 4, 8, 9))->get(['id', 'name']);
        $pid = 1;
        $res = \App\Models\Memberinvestments::select('id')->distinct()->where('plan_id', $pid)->get();
       
        return view('templates.admin.investment_management.saving-account-report.savingaccountreport-listing', $data);
    }
    /**
     * Fetch invest listing data.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function savingaccountreportListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "23") == "1") {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $pid = 1;
            $data = \App\Models\SavingAccountTranscation::select('id', 'created_at', 'deposit', 'withdrawal', 'payment_mode', 'account_no', 'saving_account_id', 'branch_id', 'associate_id', 'is_app', 'company_id')
                ->with([
                    'savingAc:id,member_id,customer_id',
                    'savingAc.ssbMember:id,member_id,first_name,last_name', 
                    'savingAc.ssbMemberCustomer:id,member_id,customer_id',
                    'savingAc.ssbMemberCustomer.member:id,member_id,first_name,last_name',
                    'company:id,name',
                    'dbranch:id,name,branch_code,sector,zone',
                    'associateMember:id,associate_no,first_name,last_name',
                ]);            
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {              
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                /******* fillter query start ****/
                if ($arrFormData['start_date'] != '') {
                    $startDate = $arrFormData['start_date'];
                    $endDate = $arrFormData['end_date'];
                    $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                    $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    if($id != '0'){
                        $data = $data->where('branch_id', '=', $id);
                    }
                }
                if ($arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    if($companyId != '0'){
                        $data = $data->whereHas('company', function ($query) use ($companyId) {
                            $query->whereId($companyId);
                        });
                    }
                }
                if (isset($arrFormData['amount']) && $arrFormData['amount'] != '') {
                    $amount = $arrFormData['amount'];
                    if($amount=='deposit'){
                        $data = $data->where('payment_type', 'CR')->where('type',2);
                    }elseif($amount=='withdrawal'){
                        $data = $data->where('payment_type', 'DR');
                    }
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->whereHas('associateMember', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if (isset($arrFormData['name']) and !empty($arrFormData['name'])) {
                    $name = $arrFormData['name'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                
                /******* fillter query End ****/
                $count = $data->count('id');
                //$count=count($data1);
                $dataexport = $data->orderby('id', 'DESC')->get()->toArray();
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                // $dataCount = \App\Models\SavingAccountTranscation::with(['company', 'savingAc', 'dbranch', 'associateMember'])
                //     ->where('type', 2);
                // if (Auth::user()->branch_id > 0) {
                //     $dataCount = $dataCount->where('branch_id', '=', Auth::user()->branch_id);
                // }
                // $totalCount = $dataCount->count('id');
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                $amountTypes = ['deposit' => 'deposit', 'withdrawal' => 'withdrawal'];
                $paymentModes = [
                    0 => "Cash",
                    1 => "Cheque",
                    2 => "DD",
                    3 => "Online",
                    4 => "By Saving Account",
                    5 => "From Loan Amount",
                    9 => "J/V"
                ];
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                    $val['tran_by'] = ($row['is_app'] == 1 ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software'));
                    $val['company'] = $row->company->name ?? 'N/A';
                    $val['branch'] = $row['dbranch']['name'];

                    $val['customer_id'] =$row['savingAc']['ssbMemberCustomer']['member']->member_id  ?? 'N/A'; 
                    $val['member_id'] = $row['savingAc']['ssbMemberCustomer']->member_id ?? 'N/A' ;
                    $val['account_number'] = $row['account_no'];
                    $memberData = $row['savingAc'] ? memberFieldDataStatus(["id", "first_name", "last_name"], $row['savingAc']['member_id'], 'id') : '';
                    $name = '';
                    if (!empty($memberData) && isset($memberData[0])) {
                        $name = $memberData[0] ? $memberData[0]['first_name'] . ' ' . $memberData[0]['last_name']??'' : '';
                    }    
                    $val['member'] = $row['savingAc'] ? $row['savingAc']['ssbMemberCustomer']['member']->first_name.' '.$row['savingAc']['ssbMemberCustomer']['member']->last_name??'' :'N/A';
    
                    $amount = $arrFormData['amount']??'';
                    if($amount != null){
                        $val['amount'] = $row[$amountTypes[$amount]] ?? 0.00;
                    }else{
                        $val['amount'] = 0.00;
                    }
                    $val['associate_code'] = $row['associateMember'] ? $row['associateMember']['associate_no'] : 'N/A';
                    $val['associate_name'] = $row['associateMember'] ? $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name']??'' : 'N/A';
                    
                    $val['payment_mode'] = isset($paymentModes[$row->payment_mode]) ? $paymentModes[$row->payment_mode] : "N/A";

                    /* $btn='';
                    $val['action']=$btn;*/
                    $rowReturn[] = $val;
                }
                $token = Session::get('_token');
                Cache::put('Savingaccountreport_datalistAdmin' . $token, $dataexport);
                Cache::put('Savingaccountreport_datalistAdminCount' . $token, $count);
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
                return json_encode($output);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    public function export(Request $request)
    {
        $_fileName = Session::get('_fileName');
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        /*
        $data = \App\Models\SavingAccountTranscation::
                select('id', 'created_at', 'deposit', 'withdrawal', 'payment_type', 'payment_mode', 'account_no', 'saving_account_id', 'branch_id', 'associate_id', 'is_app', 'company_id')
                ->with([
                    'savingAc' => function ($q) {
                        $q->select('id', 'member_id', 'customer_id')
                        ->with([
                            'ssbMember:id,member_id,first_name,last_name', 
                            'ssbMemberCustomer:id,member_id,customer_id',
                            'ssbMemberCustomer.member:id,member_id,first_name,last_name',
                        ]);
                    }
                ])
                ->with([
                    'company:id,name',
                    'dbranch:id,name,branch_code,sector,zone',
                    'associateMember:id,associate_no,first_name,last_name',
                ]);
                */
            $data = \App\Models\SavingAccountTranscation::select('id', 'created_at', 'deposit', 'withdrawal', 'payment_mode', 'account_no', 'saving_account_id', 'branch_id', 'associate_id', 'is_app', 'company_id')
                ->with([
                    'savingAc:id,member_id,customer_id',
                    'savingAc.ssbMember:id,member_id,first_name,last_name', 
                    'savingAc.ssbMemberCustomer:id,member_id,customer_id',
                    'savingAc.ssbMemberCustomer.member:id,member_id,first_name,last_name',
                    'company:id,name',
                    'dbranch:id,name,branch_code,sector,zone',
                    'associateMember:id,associate_no,first_name,last_name',
                ]); 

            if (isset($input['is_search']) && $input['is_search'] == 'yes') {

                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                /******* fillter query start ****/
                if ($input['start_date'] != '') {
                    $startDate = $input['start_date'];
                    $endDate = $input['end_date'];
                    $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                    $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if (isset($input['branch_id']) && $input['branch_id'] != '') {
                    $branch_id = $input['branch_id'];
                    if($branch_id != '0'){
                        $data = $data->where('branch_id', '=', $branch_id);
                    }
                }
                if (isset($input['amount']) && $input['amount'] != '') {
                    $amount = $input['amount'];
                    if($amount=='deposit'){
                        $data = $data->where('payment_type', 'CR')->where('type',2);
                    }elseif($amount=='withdrawal'){
                        $data = $data->where('payment_type', 'DR');
                    }
                }
                if ($input['company_id'] != '') {
                    $companyId = $input['company_id'];
                    if($companyId != '0'){
                        $data = $data->whereHas('company', function ($query) use ($companyId) {
                            $query->whereId($companyId);
                        });
                    }
                }
                
                if ($input['associate_code'] != '') {
                    $associateCode = $input['associate_code'];
                    $data = $data->whereHas('associateMember', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if (isset($input['name']) and !empty($input['name'])) {
                    $name = $input['name'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
            }

        /******* fillter query End ****/
        $count = $data->count('id');
        $data = $data->orderby('id', 'DESC')->get();

        $returnURL = URL::to('/') . "/asset/SavingListing-Export_".$_fileName.".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/SavingListing-Export_".$_fileName.".csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
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
        // $record = array_slice($data, $start, $limit);
        // $totalCount = count($record);
        $paymentModes = [
            0 => "Cash",
            1 => "Cheque",
            2 => "DD",
            3 => "Online",
            4 => "By Saving Account",
            5 => "From Loan Amount",
            9 => "J/V"
        ];
        $amountTypes = ['deposit' => 'deposit', 'withdrawal' => 'withdrawal'];
        foreach ($data->slice($start,$limit) as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Created At'] = date("d/m/Y", strtotime($row['created_at']));
            $val['Transaction By'] = ($row['is_app'] == 1 ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software'));
            $val['Company Name'] = $row['company']['name'] ?? 'N/A';
            $val['Branch'] = $row['dbranch'] ? $row['dbranch']['name'] : 'N/A';
            $val['Customer Id'] = $row['savingAc']['ssbMemberCustomer']['member']['member_id'] ?? 'N/A';
            $val['Member Id'] = $row['savingAc']['ssbMemberCustomer']['member_id'] ?? 'N/A';
            $val['Account Number'] = $row['account_no'];
            // $memberData = memberFieldDataStatus(array("id", "first_name", "last_name"), $row['savingAc']['member_id'], 'id');
            // $name = '';
            // if (!empty($memberData) && isset($memberData[0]['first_name'])) {
            //     $name = $memberData[0]['first_name'] . ' ' . $memberData[0]['last_name'];
            // }
            $val['Member'] = $row['savingAc'] ? $row['savingAc']['ssbMemberCustomer']['member'] ? $row['savingAc']['ssbMemberCustomer']['member']['first_name'] . ' ' . $row['savingAc']['ssbMemberCustomer']['member']['last_name'] : 'N/A' : 'N/A';
            $amount = $input['amount']??'';                    
            $val['Amount'] = $row[$amountTypes[$amount]] ?? 0.00;
            $val['Associate Code'] = $row['associateMember'] ? $row['associateMember']['associate_no'] : 'N/A';
            $val['Associate Name'] = $row['associateMember'] ? $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name']??'': 'N/A' ;
            $val['Payment Mode'] = isset($paymentModes[$row['payment_mode']]) ? $paymentModes[$row['payment_mode']] : "N/A";
            
            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result' => $result,
            'start' => $start,
            'limit' => $limit,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }
}