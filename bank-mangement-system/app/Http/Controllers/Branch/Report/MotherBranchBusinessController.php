<?php

namespace App\Http\Controllers\Branch\Report;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Daybook;
use App\Models\Branch;
use App\Models\Transcation;
use App\Models\Memberinvestments;
use App\Models\Plans;
use App\Models\AccountHeads;
use App\Http\Controllers\Admin\CommanController;
use App\Models\Member;
use App\Models\Companies;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
use DateTime;

/*

    |---------------------------------------------------------------------------

    | Admin Panel -- Report Management MotherBranchBusinessController

    |--------------------------------------------------------------------------

    |

    | This controller handles admin_business report all functionlity.

*/

class MotherBranchBusinessController extends Controller

{

    /**

     * Create a new controller instance.

     * @return void

     */



    public function __construct()

    {

        // check user login or not

        $this->middleware('auth');
    }



    /**

     * Show Daybook report.

     * Route: /admin/report/admin_business

     * Method: get 

     * @return  array()  Response

     */

    //Admin Business Report (AMAN !! 17-05)
    public function index()
    {
        if (!in_array('Mother Branch Business Report', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Report | Mother Branch Business  Report';
        $data['branch'] = Branch::where('status', 1)->get();

        return view('templates.branch.report.mother_branch_business', $data);
    }

    public function mother_branch_business_listing(Request $request)
    {
        $currentdate = date('Y-m-d');
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['created_at'] = $request->created_at;
            $companyId = $arrFormData['company_id'] = $request->company_id;
            $companyname = Companies::where('id', $companyId)->first('name');
            $customplan = getPlanIDCustom();
            $branch_id = $getBranchId->id;
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes' && $companyId != '')
            {
                $startDate = DateTime::createFromFormat("d/m/Y", $arrFormData['start_date']);
                $startDate = $startDate->format("Y-m-d");
                $endDate = DateTime::createFromFormat("d/m/Y", $arrFormData['end_date']);
                $endDate = $endDate->format("Y-m-d");
                $data = DB::select('call motherBranchReports(?,?,?,?,?,?)', [$startDate, $endDate, $companyId, $branch_id, $page_number=1, $page_size = 10]); 
                $datac = $data;
                $data1 = count($data);
                $count=$data1;
                $totalCount = $count;
                $sno=$_POST['start'];
                $rowReturn = array(); 
                $limit = 10;
                $offset = 0;

                $counter = 0;
                $token = session()->get('_token');

                $Cache = Cache::put('mother_report' . $token, $datac);
                Cache::put('mother_report_count' . $token, $count);
                foreach ($data as $row)
                {  
                if ($counter >= $offset && $counter < ($offset + $limit)) {
                    $sno++;
                    $val['DT_RowIndex']=$sno;
                    $val['company_name']= $companyname->name?? 'All company';
                    $val['branch']=$row->name;   
                    $val['branch_code']=$row->branch_code;
                    $val['daily_new_ac']=$row->dnccac;
                    $val['daily_deno_sum']="&#x20B9;".$row->dnccamt;

                    $val['daily_renew_ac']=$row->drenac;
                    $val['daily_renew']="&#x20B9;".$row->drenamt; 

                    $val['monthly_ncc_ac']=$row->mnccac;
                    $val['monthly_ncc_amt']="&#x20B9;".$row->mnccamt;

                    $val['monthly_renew_ac']=$row->mrenac;
                    $val['monthly_renew_amt']="&#x20B9;".$row->mrenamt; 

                    $val['fd_new_ac']= $row->fnccac;
                    $val['fd_deno_sum']="&#x20B9;".$row->fnccamt;

                    $val['ssb_ncc_ac'] = $row->snccac;
                    $val['ssb_ncc_amt'] = $row->sncc;

                    $val['ssb_ren_ac'] = $row->ssbren_ac;
                    $val['ssb_ren_amt'] = "&#x20B9;".number_format((float) $row->ssbren, 2, '.', '');;

                    $val['other_mi']="&#x20B9;".$row->MI;                   
                    $val['other_stn'] = "&#x20B9;".$row->STN;

                    $val['new_mi_joining']=$row->member_acn;
                    $val['new_associate_joining']=$row->asso_ac; 

                    $val['banking_ac'] = $row->sumbanking_ac;
                    $val['banking_amt'] = "&#x20B9;".number_format((float) $row->sumbankingamt, 2, '.', '');
                   
                    $val['total_withdrawal']= "&#x20B9;".number_format((float) $row->ssbw, 2, '.', '');
                    $val['total_payment']= "&#x20B9;".number_format((float) $row->MaturityPayment, 2, '.', '');
                    
                    $val['ncc'] = $row->ncc;
                    $val['ncc_ssb'] = $row->ncc_ssb;

                    $val['tcc'] = $row->tcc;
                    $val['tcc_ssb'] = $row->tcc_ssb;                    

                    $val['loan_ac_no']= $row->loan_ac_no; 
                    $val['loan_amt']= "&#x20B9;".number_format((float)$row->loan_amt, 2, '.', '');

                    $val['loan_recv_ac_no']= $row->loan_recv_ac_no; 
                    $val['loan_recv_amt']= "&#x20B9;".number_format((float)$row->loan_recv_amt, 2, '.', '');
                    
                    $val['loan_aginst_ac_no']= $row->loan_aginst_ac_no; 
                    $val['loan_aginst_amt']= "&#x20B9;".$row->loan_aginst_amt;
                    
                    $val['loan_aginst_recv_ac_no']= $row->loan_aginst_recv_ac_no; 
                    $val['loan_aginst_recv_amt']= "&#x20B9;".number_format((float)$row->loan_aginst_recv_amt, 2, '.', '');


                    $val['cash_in_hand']=$row->cash_in_hand;                   
                        $rowReturn[] = $val; 
                    } elseif ($counter >= ($offset + $limit)) {
                        // If we have reached the limit, break out of the loop
                        break;
                    }
                    $counter++;
                } 
                //  print_r($rowReturn);die;
                $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
                return json_encode($output);
            } else {
                $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0,);
                return json_encode($output);
            }
        }
    }
    public function motherBranchBusinessReportExport(Request $request)
    {
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data  = Cache::get('mother_report' . $token);
        $count = Cache::get('mother_report_count' . $token);
        $input = $request->all();
        $companyId = $request['company'];
        $companyname = Companies::where('id', $companyId)->first('name');
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/MotherReport" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/MotherReport" . $file . ".csv";
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
        $rowReturn = [];
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        $counter = 0;
        $offset = 0;
        foreach ($record as $row) {
                
                $sno++;
                // $branch_id =  $_POST['branchid'];
                $val['S/N'] = $sno;
                $val['Company Name'] = $companyname->name;
                $val['BR Name'] = $row->name;
                $val['BR Code'] = $row->branch_code;
                $val['Region'] = $row->regan;
                $val['Sector'] = $row->sector;
                $val['Zone'] = $row->zone;
                $val['Daily NCC - No. A/C'] = $row->dnccac;
                $val['Daily NCC - Amt'] = $row->dnccamt;
                $val['Daily Renew - No. A/C'] = $row->drenac;
                $val['Daily Renew - Amt'] = $row->drenamt;
                $val['Monthly NCC - No. A/C']=$row->mnccac;
                $val['Monthly NCC - Amt']=$row->mnccamt;

                    $val['Monthly Renew- No. A/C']=$row->mrenac;
                    $val['Monthly Renew- Amt']=$row->mrenamt; 

                    $val['FD NCC - No. A/C']= $row->fnccac;
                    $val['FD NCC - Amt']=$row->fnccamt;

                    $val['SSB NCC - No. A/C'] = $row->snccac;
                    $val['SSB NCC - Amt'] = $row->sncc;

                    $val['SSB Renew- No. A/C'] = $row->ssbren_ac;
                    $val['SSB Renew- Amt'] = number_format((float) $row->ssbren, 2, '.', '');

                    $val['Other MI']=$row->MI;                   
                    $val['Other STN'] = $row->STN;

                    $val['New MI Joining - No. A/C']=$row->member_acn;
                    $val['New Associate Joining - No. A/C']=$row->asso_ac; 

                    $val['Banking - No. A/C'] = $row->sumbanking_ac;
                    $val['Banking - Amt'] = number_format((float) $row->sumbankingamt, 2, '.', '');
                   
                    $val['Total Payment - Withdrawal']= number_format((float) $row->ssbw, 2, '.', '');
                    $val['Total Payment - Payment']= number_format((float) $row->MaturityPayment, 2, '.', '');
                    
                    $val['NCC'] = $row->ncc;
                    $val['NCC SSB'] = $row->ncc_ssb;

                    $val['TCC'] = $row->tcc;
                    $val['TCC SSB'] = $row->tcc_ssb;                    

                    $val['Loan - No. A/C']= $row->loan_ac_no; 
                    $val['Loan - Amt']= number_format((float)$row->loan_amt, 2, '.', '');

                    $val['Loan Recovery - No. A/C']= $row->loan_recv_ac_no; 
                    $val['Loan Recovery - Amt']= number_format((float)$row->loan_recv_amt, 2, '.', '');
                    
                    $val['Loan Against Investment - No. A/C']= $row->loan_aginst_ac_no; 
                    $val['Loan Against Investment - Amt']= $row->loan_aginst_amt;
                    
                    $val['Loan Against Investment Recovery - No. A/C']= $row->loan_aginst_recv_ac_no; 
                    $val['Loan Against Investment Recovery - Amt']= number_format((float)$row->loan_aginst_recv_amt, 2, '.', '');


                    $val['Cash in hand']=$row->cash_in_hand; 
                if (!$headerDisplayed) {
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                fputcsv($handle, $val);
            
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
    // public function mother_branch_business_listing(Request $request)
    // { 
    //     $currentdate = date('Y-m-d');
    //     if ($request->ajax()) {

    //         // fillter array 
    //         $arrFormData = array();   

    //         $arrFormData['start_date'] = $request->start_date;
    //         $arrFormData['end_date'] = $request->end_date;
    //         $arrFormData['branch_id'] = $request->branch_id;  
    //         $arrFormData['is_search'] = $request->is_search;
    //         $arrFormData['created_at'] = $request->created_at;
    //         $arrFormData['zone'] = $request->zone; 
    //         $arrFormData['region'] = $request->region; 
    //         $arrFormData['sector'] = $request->sector; 
    //         $customplan = getPlanIDCustom();

    //         if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
    //         {

    //             $data = Branch::select('id','name','branch_code','sector','zone','regan')->where('status',1);

    //             if($arrFormData['start_date'] !=''){
    //                 $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
    //             }
    //             else{
    //                 $startDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
    //             }
    //             if($arrFormData['end_date'] !=''){
    //                 $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
    //             }
    //             else {
    //                 $endDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
    //             }


    //             if($arrFormData['created_at']!=''){
    //                 $created_at=date("Y-m-d ", strtotime(convertDate($arrFormData['created_at'])));
    //             }
    //             else {
    //                 $created_at='';
    //             }
    //             if($arrFormData['branch_id']!='') {
    //                 $branch_id=$arrFormData['branch_id'];
    //                 $data =$data->where('id',$branch_id);
    //             }
    //             else {
    //                 $branch_id='';
    //             }

    //             if(Auth::user()->branch_id>0){
    //                 $branch_id=Auth::user()->branch_id;
    //             }

    //             /******* fillter query End ****/
    //             $count=$data->count();
    //             $data=$data->orderby('created_at','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
    //             $planDaily= $customplan['710']; //getPlanID(7,10)->id;
    //             $dailyId=array($planDaily);
    //             $planSSB= $customplan['703']; //getPlanID('703')->id;  
    //             $planKanyadhan= $customplan['709']; //getPlanID('709')->id;
    //             $planMB= $customplan['708']; //getPlanID('708')->id;
    //             $planFRD= $customplan['707']; //getPlanID('707')->id;
    //             $planJeevan= $customplan['713']; //getPlanID('713')->id;  
    //             $planRD= $customplan['704']; //getPlanID('704')->id;
    //             $planBhavhishya= $customplan['718']; //getPlanID('718')->id;
    //             $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
    //             $planMI= $customplan['712']; //getPlanID('712')->id;
    //             $planFFD= $customplan['705']; //getPlanID('705')->id;
    //             $planFD= $customplan['706']; //getPlanID('706')->id;
    //             $fdId=array($planMI,$planFFD,$planFD);

    //             // $renewalData=\App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number','old_branch_id');}])->whereHas('investment', function ($query)  {
    //             //      $query->where('plan_id',7);
    //             // })->where('is_eli', '!=',1)->where('transaction_type',4)->where('payment_type','!=','DR')->where('is_deleted', 0)->get()->groupBy('branch_id');
    //             // dd( $renewalData[5]->sum('deposit'));

    //             $totalCount = $count;     
    //             $sno=$_POST['start'];
    //             $rowReturn = array(); 
    //             foreach ($data as $row)
    //             {  

    //                 $currentdate = date('Y-m-d');
    //                 if($endDate == '')
    //                 {
    //                     $branchId = $_POST['branchid'];

    //                     $globalDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchId);

    //                     $endDate =date("Y-m-d", strtotime(convertDate($globalDate)));

    //                 }
    //                 if($startDate == '')
    //                 {
    //                     $branchId = $_POST['branchid'];

    //                     $globalDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$branchId);
    //                     $startDate = date("Y-m-d", strtotime(convertDate($globalDate)));

    //                 }
    //                 $sno++;
    //                 $branch_id = $row->id;
    //                 $val['DT_RowIndex']=$sno;
    //                 $val['branch']= $row->name;
    //                 $val['branch_code']=$row->branch_code;
    //                 $val['sector_name']=$row->sector;
    //                 $val['region_name']=$row->regan;
    //                 $val['zone_name']=$row->zone;
    //                 $val['daily_new_ac']=motherBusinessInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily);
    //                 $val['daily_ncc']="&#x20B9;".dailyNCCmotherBusinessSum($startDate,$endDate,$branch_id,$planDaily);
    //                 $val['daily_renewal']="&#x20B9;".motherdailymonthlyRenewalAmountSum($startDate,$endDate,$dailyId,$branch_id);
    //                 $val['monthly_new_ac']=branchBusinessInvestNewAcCountType($startDate,$endDate,$monthlyId,$branch_id);
    //                 $val['monhtly_ncc']="&#x20B9;".monthlyNCCmotherBusinessInvestNewDenoSumType($startDate,$endDate,$monthlyId,$branch_id);
    //                 $val['monthly_renewal']="&#x20B9;".motherdailymonthlyRenewalAmountSum($startDate,$endDate,$monthlyId,$branch_id);
    //                 $val['fd_new_ac']=branchBusinessInvestNewAcCountType($startDate,$endDate,$fdId,$branch_id);
    //                 $val['fd_ncc']="&#x20B9;".number_format((float)monthlyNCCmotherBusinessInvestNewDenoSumType($startDate,$endDate,$fdId,$branch_id), 2, '.', '');
    //                 $val['ssb_new_ac']=totalSSbAccountByType($startDate,$endDate,$branch_id,1);
    //                 $val['ssb_ncc']="&#x20B9;".number_format((float)MothertotalSSbAmtByType($startDate,$endDate,$branch_id,1), 2, '.', '');
    //                 $val['ssb_renew_ac']=totalSSbAccountByType($startDate,$endDate,$branch_id,2);
    //                 $val['ssb_renewal']="&#x20B9;".number_format((float)MothertotalSSbAmtByType($startDate,$endDate,$branch_id,2), 2, '.', '');

    //                 $stnAmount =totalOtherMiByType($startDate,$endDate,$branch_id,1,12);
    //                 $stationaryAmount = totalOtherStatinarySTN($startDate,$endDate,$branch_id,21);
    //                 $totalStnAmount = $stnAmount + $stationaryAmount;

    //                 $val['member_stn'] = "&#x20B9;".$totalStnAmount;
    //                 $val['new_member']=totalMijoining($startDate,$endDate,$branch_id);

    //                 $mi_memberfee = 11;
    //                 //$stn_memberfee = 12;
    //                 $val['member_fee']="&#x20B9;".totalMemberFeeBranchBusiness($startDate,$endDate,$branch_id,1,$mi_memberfee);

    //                 $val['new_associates']=getTotalBranchWiseNewAssociates($startDate,$endDate,$branch_id);
    //                 $val['total_ni_ac']= totalNiACByCount($startDate,$endDate,$branch_id,7);
    //                 $val['total_ni_amount']= "&#x20B9;".number_format((float)totalNiACByBranch($startDate,$endDate,$branch_id,7), 2, '.', '');;
    //                 $val['maturity']= "&#x20B9;".number_format((float)BranchWisetotalPaymentForMaturityDaybook($startDate,$endDate,$branch_id,17), 2, '.', '');
    //                 $val['expenses']="&#x20B9;".number_format((float)BranchWiseExpenseExcludingIndirectExpense(4,$startDate,$endDate,$branch_id), 2, '.', '');
    //                 $val['ssb_withdrawal']= "&#x20B9;".number_format((float)getMotherBranchWiseSSBWithdrawal($startDate,$endDate,$branch_id,5), 2, '.', '');

    //                 $daily_deno_sum = dailyNCCmotherBusinessSum($startDate,$endDate,$branch_id,$planDaily);
    //                 $monthly_deno_sum =monthlyNCCmotherBusinessInvestNewDenoSumType($startDate,$endDate,$monthlyId,$branch_id);
    //                 $fd_deno_sum = number_format((float)monthlyNCCmotherBusinessInvestNewDenoSumType($startDate,$endDate,$fdId,$branch_id), 2, '.', '');


    //                 $daily_renew = motherdailymonthlyRenewalAmountSum($startDate,$endDate,$dailyId,$branch_id);

    //                 $monthly_renew = motherdailymonthlyRenewalAmountSum($startDate,$endDate,$monthlyId,$branch_id);

    //                 $ssb_deno_sum = number_format((float)MothertotalSSbAmtByType($startDate,$endDate,$branch_id,1), 2, '.', '');

    //                 $ssb_renew = number_format((float)MothertotalSSbAmtByType($startDate,$endDate,$branch_id,2), 2, '.', '');

    //                 $ncc= $daily_deno_sum + $monthly_deno_sum + $fd_deno_sum;
    //                 $ncc_ssb= $daily_deno_sum + $monthly_deno_sum + $fd_deno_sum + $ssb_deno_sum;
    //                 $tcc= $daily_deno_sum + $monthly_deno_sum + $fd_deno_sum + $daily_renew + $monthly_renew;
    //                 $tcc_ssb = $daily_deno_sum + $monthly_deno_sum + $fd_deno_sum + $ssb_deno_sum + $daily_renew + $monthly_renew + $ssb_renew;

    //                 $val['ncc']= "&#x20B9;".number_format((float)$ncc, 2, '.', '');

    //                 $val['ncc_ssb']= "&#x20B9;".number_format((float)$ncc_ssb, 2, '.', '');

    //                 $val['tcc']= "&#x20B9;".number_format((float)$tcc, 2, '.', '');

    //                 $val['tcc_ssb']= "&#x20B9;".number_format((float)$tcc_ssb, 2, '.', '');

    //                 $passbookprintfee = BranchwisePassbookPrintSum($startDate,$endDate,$branch_id);

    //                 $val['duplicate_passbook_fee']= "&#x20B9;".number_format((float)$passbookprintfee,2,'.','');

    //                 $val['commission']= "&#x20B9;".number_format((float)GetRentSalaryCommissionnBRanchWise(87,$startDate,$endDate,$branch_id,NULL,4), 2, '.', '');

    //                 $val['rent']= "&#x20B9;".number_format((float)GetRentSalaryCommissionnBRanchWise(53,$startDate,$endDate,$branch_id,NULL,10), 2, '.', '');

    //                 $val['salary']= "&#x20B9;".number_format((float)GetRentSalaryCommissionnBRanchWise(37,$startDate,$endDate,$branch_id,NULL,12), 2, '.', '');

    //                 $val['money_back']= "&#x20B9;".number_format((float)branchBusinessMoneyBack($startDate,$endDate,$branch_id,18), 2, '.', '');

    //                 $val['mis_payment']= "&#x20B9;".number_format((float)BranchwiseMISPaymentSum($startDate,$endDate,$branch_id,8), 2, '.', '');

    //                 $val['new_loan']=loanSancationAccount($startDate,$endDate,$branch_id);

    //                 $val['loan_amount']="&#x20B9;".number_format((float)loanSancationAmt($startDate,$endDate,$branch_id), 2, '.', '');

    //                 $val['loan_recovery']="&#x20B9;".number_format((float)loanRecoverAmt($startDate,$endDate,$branch_id), 2, '.', '');

    //                 $val['micro_end_date']= "&#x20B9;".getMicroEndDate($created_at,$endDate,$branch_id,0);

    //                 $val['load_end_date']=  "&#x20B9;".getLoadEndDate($created_at,$endDate,$branch_id,1);

    //                 $getBranchOpening_cash =getBranchOpeningDetail($branch_id);
    //                 $balance_cash =0;
    //                 $C_balance_cash =0;
    //                 $currentdate = date('Y-m-d');
    //                 if($getBranchOpening_cash->date==$startDate)
    //                 {
    //                     $balance_cash =$getBranchOpening_cash ->total_amount;
    //                     if($endDate == '')
    //                     {
    //                     $endDate=$currentdate;
    //                     }

    //                 }
    //                 if($getBranchOpening_cash->date<$startDate)
    //                 {
    //                     if($getBranchOpening_cash->date != '')
    //                     {
    //                             $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate,$getBranchOpening_cash->date,$getBranchOpening_cash->total_amount,$branch_id);
    //                     }
    //                     else{
    //                         $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate, $currentdate,$getBranchOpening_cash->total_amount,$branch_id);
    //                     }
    //                     $balance_cash =$getBranchTotalBalance_cash;
    //                     if($endDate == '')
    //                     {
    //                         $endDate=$currentdate;
    //                     }
    //                 }
    //                 $getTotal_DR=getBranchTotalBalanceAllTranDR($startDate,$endDate,$branch_id);
    //                 $getTotal_CR=getBranchTotalBalanceAllTranCR($startDate,$endDate,$branch_id);
    //                 $totalBalance=$getTotal_CR-$getTotal_DR;
    //                 $C_balance_cash =$balance_cash+$totalBalance;

    //                 $val['cash_closing_balance']=  "&#x20B9;".$C_balance_cash;
    //                 $rowReturn[] = $val; 
    //             } 
    //             //  print_r($rowReturn);die;
    //             $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
    //             return json_encode($output);
    //         }
    //         else{
    //             $output = array( "draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0, );
    //             return json_encode($output);
    //         }
    //     }
    // }
}
