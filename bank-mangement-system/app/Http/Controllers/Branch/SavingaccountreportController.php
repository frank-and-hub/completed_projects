<?php
namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Cache;
use DB;
use URL;
use Session;
use Carbon\Carbon;

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
        if (!in_array('SSB Deposit List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = "Saving Listing";
        return view('templates.branch.investment_management.saving-account-report.savingaccountreport-listing', $data);
    }
    /**
     * Fetch invest listing data.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function savingaccountreportListing(Request $request)
    {
        if ($request->ajax()) {
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
           
            $pid = 1;
            $res = \App\Models\Memberinvestments::select('id')->distinct()->where('plan_id', $pid)->get();
            $member_investment_ids = array_column($res->toArray(), 'id');
            /******* fillter query start ****/

            $companyId = $arrFormData['company_id'];
            $data = \App\Models\SavingAccountTranscation::
                where('branch_id', $branch_id)
                ->with([
                    'dbranch:id,name,branch_code,sector,zone', 
                    'associateMember:id,associate_no,first_name,last_name', 
                    'company:id,name',
                    'MemberCompany'
                    ])
                
                // ->where('type', 2)
                
                /* 
                ->with(['savingAc' => function ($q) {
                    $q->select('id', 'member_id','customer_id')->with(['ssbMember:id,member_id','ssbMemberCustomer:id,member_id,customer_id']);
                }])
                */
                ->with(['savingAc' => function ($q) {
                    $q->select('id', 'member_id','customer_id')
                    ->with([
                        'ssbMember:id,member_id',
                        'ssbMemberCustomer:id,member_id,customer_id',
                        'ssbMemberCustomer.member:id,member_id,first_name,last_name'
                    ]);
                }])
                ->when(isset($arrFormData['is_search']) and $arrFormData['is_search'] == 'yes', function ($query) use ($arrFormData, $branch_id) {
                    if (!empty($arrFormData['start_date'])) {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                        $query->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                    }
                    if (!empty($arrFormData['company_id'])) {
                        $companyId = $arrFormData['company_id'];
                        $query->whereHas('company', function ($query) use ($companyId) {
                            $query->whereCompanyId($companyId);
                        });
                    }
                    if (isset($arrFormData['amount']) && $arrFormData['amount'] != '') {
                        $amount = $arrFormData['amount'];
                        if($amount=='deposit'){
                            $query = $query->where('payment_type', 'CR')->where('type',2);
                        }elseif($amount=='withdrawal'){
                            $query = $query->where('payment_type', 'DR');
                        }
                    }
                    if (!empty($arrFormData['associate_code'])) {
                        $associateCode = $arrFormData['associate_code'];
                        $query->whereHas('associateMember', function ($query) use ($associateCode) {
                            $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                        });
                    }
                    if (isset($arrFormData['name']) and !empty($arrFormData['name'])) {
                        $name = $arrFormData['name'];
                        $query->whereHas('member', function ($query) use ($name) {
                            $query->where('members.first_name', 'LIKE', '%' . $name . '%')
                                ->orWhere('members.last_name', 'LIKE', '%' . $name . '%')
                                ->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                        });
                    }
                });

            /******* fillter query End ****/
            $count = $data->orderby('id', 'DESC')->count();
            $dataexport = $data->orderby('id', 'DESC')->get()->toArray();
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = \App\Models\SavingAccountTranscation::with(['company', 'savingAc', 'dbranch', 'associateMember'])
                ->where('type', 2)
                ->where('branch_id', $branch_id)->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            $amountTypes = ['deposit' => 'deposit','withdrawal' => 'withdrawal'];
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
                $val['created_at'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
                $val['tran_by'] = ($row['is_app'] == 1 ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software'));
                $val['company'] = $row->company->name ?? 'N/A';
                $val['branch'] = $row['dbranch']['name'];            
                $val['customer_id'] = $row['savingAc'] ? $row['savingAc']['ssbMemberCustomer'] ? $row['savingAc']['ssbMemberCustomer']['member']->member_id  ?? 'N/A' : 'N/A' : 'N/A'; 
                $val['member_id'] = $row['savingAc'] ? $row['savingAc']['ssbMemberCustomer'] ? $row['savingAc']['ssbMemberCustomer']->member_id ?? 'N/A' : 'N/A' : 'N/A' ;
                $val['account_number'] = $row['account_no'];
                $memberData = $row['savingAc'] ? memberFieldDataStatus(array("id", "first_name", "last_name"), $row['savingAc']['member_id'], 'id') : '';
                $name = '';
                if (!empty($memberData) && isset($memberData[0]['first_name'])) {
                    $name = $memberData[0] ? $memberData[0]['first_name'] . ' ' . $memberData[0]['last_name']??'' : 'N/A';
                }    
                $val['member'] = $row['savingAc'] ? $row['savingAc']['ssbMemberCustomer']['member']->first_name.' '.$row['savingAc']['ssbMemberCustomer']['member']->last_name??'':'N/A';

                $amount = $arrFormData['amount']??'';
                if($amount != null){
                    $val['amount'] = $row[$amountTypes[$amount]] ?? 0.00;
                }else{
                    $val['amount'] = 0.00;
                }
                $val['associate_code'] = $row['associateMember'] ? $row['associateMember']['associate_no'] : 'N/A';
                $val['associate_name'] = $row['associateMember'] ? $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name']??'' : 'N/A';
                
                $val['payment_mode'] = isset($paymentModes[$row->payment_mode]) ? $paymentModes[$row->payment_mode] : "N/A";

               
                $rowReturn[] = $val;
            }
            $token = Session::get('_token');
            Cache::put('Savingaccountreport_datalistBranch' . $token, $dataexport);
            Cache::put('Savingaccountreport_datalistBranchCount' . $token, count($dataexport));
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    public function export(Request $request)
    {
        $token = Session::get('_token');
        $file = Session::get('_fileName');
        $data = Cache::get('Savingaccountreport_datalistBranch' . $token);
        $count = Cache::get('Savingaccountreport_datalistBranchCount' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/SavingListingExport_".$file.".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/SavingListingExport_".$file.".csv";
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
        // $rowReturn = [];
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        $paymentModes = [
            0 => "Cash",
            1 => "Cheque",
            2 => "DD",
            3 => "Online",
            4 => "By Saving Account",
            5 => "From Loan Amount"
        ];
        $amountTypes = ['deposit' => 'deposit', 'withdrawal' => 'withdrawal'];
        foreach ($record as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Created At'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
            $val['Transaction By'] = ($row['is_app'] == 1 ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software'));
            $val['Company Name'] = $row['company']['name'] ?? 'N/A';
            $val['Branch'] = $row['dbranch']['name'];
            $val['Customer Id'] = $row['saving_ac']['ssb_member_customer']['member']['member_id'] ?? 'N/A';
            $val['Member Id'] = $row['saving_ac']['ssb_member_customer']['member_id'] ?? 'N/A';
            $val['Account Number'] = $row['account_no'];
            $memberData = memberFieldDataStatus(array("id", "first_name", "last_name"), $row['saving_ac']['member_id'], 'id');
            $name = '';
            if (!empty($memberData) && isset($memberData[0]['first_name'])) {
                $name = $memberData[0]['first_name'] . ' ' . $memberData[0]['last_name'];
            }
            $val['Member'] = $row['saving_ac']['ssb_member_customer']['member'] ? $row['saving_ac']['ssb_member_customer']['member']['first_name'] . ' ' . $row['saving_ac']['ssb_member_customer']['member']['last_name'] : 'N/A';
            $amount = $input['amount']??'';                    
            $val['Amount'] = $row[$amountTypes[$amount]] ?? 0.00;
            $val['Associate Code'] = $row['associate_member'] ? $row['associate_member']['associate_no'] : 'N/A';
            $val['Associate Name'] = $row['associate_member'] ? $row['associate_member']['first_name'] . ' ' . $row['associate_member']['last_name']??'': 'N/A' ;
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