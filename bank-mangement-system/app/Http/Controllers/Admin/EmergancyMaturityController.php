<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\CommanController;
use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Files;
use App\Models\AccountHeads;
use App\Models\SubAccountHeads;
use App\Models\DemandAdviceExpense;
use App\Models\DemandAdvice;
use App\Models\SavingAccount;
use App\Models\Employee;
use App\Models\RentLiability;
use App\Models\Memberinvestments;
use App\Models\Branch;
use App\Models\Daybook;
use App\Models\InvestmentBalance;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBankClosing;
use App\Models\BranchCash;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\Plans;
use App\Models\TdsDeposit;
use App\Models\EliMoneybackInvestments;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;
use Session;
use App\Services\ImageUpload;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Demand Advice DemandAdviceController
    |--------------------------------------------------------------------------
    |
    | This controller handles demand advice all functionlity.
*/
class EmergancyMaturityController extends Controller
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
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(check_my_permission( Auth::user()->id,"90") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		/*$data=DemandAdvice::with('investment','branch')->where('payment_type',4)->where('status',0)->orderby('id','DESC');
			if(Auth::user()->branch_id >0){
			   $branch_id=Auth::user()->branch_id;
		       $data = $data->whereHas('investment', function ($query) use ($branch_id) {
			    $query->where('member_investments.branch_id',$branch_id);
			   });
		    }
			$data = $data->get();
			echo '<pre>';
			print_r($data->toArray());
			exit;*/
		$data['title']='Emergency Maturity Listing';
        return view('templates.admin.emergancy-maturity.listing', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function emergancyListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if(!empty($_POST['searchform']))
            {
                foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data=DemandAdvice::has('company')->select('id','tds_amount','account_holder_name','maturity_amount_till_date','maturity_amount_payable','final_amount','mobile_number','ssb_account','bank_name','bank_account_number','bank_ifsc','letter_photo_id','date','branch_id','investment_id','company_id')
            ->with(['investment' => function($q){
                        $q->select('id','created_at','plan_id','tenure','member_id','deposite_amount','customer_id','current_balance','account_number')
                        ->with(['member' => function($q){
                            $q->select('id','first_name','last_name','member_id');
                        } ])
                        ->with(['memberCompany' => function($q){
                            $q->select('id','member_id','customer_id');
                        } ])
                        ->with(['plan' => function($q){
                            $q->select('id','name');
                        } ]);
                    } ])
            ->with(['branch' => function($q){ $q->select('id','name'); } ])
            ->with(['company' => function($q){ $q->select('id','name'); } ])
            ->with(['getFileDataCustom' => function($q){ $q->select('id','file_name'); } ])
            ->where('payment_type',4)->where('status',0)->where('is_deleted',0)->orderby('id','DESC');
			// if(Auth::user()->branch_id >0){
			//    $branch_id=Auth::user()->branch_id;
		    //    $data = $data->whereHas('investment', function ($query) use ($branch_id) {
			//     $query->where('member_investments.branch_id',$branch_id);
			//    });
		    // }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                    $branchId = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branchId);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0) {
                    $companyId = $arrFormData['company_id'];
                    // $data = $data->getCompanyRecords('CompanyId', $companyId);
                    $data = $data->where('company_id', $companyId);
                }
            } 
            else{
                $data = $data->where('company_id',1);
            }
			$count = $data->count('id');
            $totalCount = $count;
            $data = $data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $sno=$_POST['start'];
            $rowReturn = array();
            foreach ($data as $row)
            {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['Company_name'] = $row['company']->name;
                $val['Branch_name'] = $row['branch']->name;
                $val['checkbox'] = '<input type="checkbox" name="emergancy_maturity_record" value="'.$row->id.'" id="emergancy_maturity_record">';
                $val['opening_date'] = date("d/m/Y", strtotime($row['investment']->created_at));
                $val['account_number'] = $row['investment']->account_number ;
                $pName = $row['investment']['plan']; //Plans::where('id',$row['investment']->plan_id)->first('name');
                $val['plan_name'] = $pName->name;
                $val['tenure'] = $row['investment']->tenure;
                $val['customer_id'] = $row['investment']['member']->member_id;
                $val['member_id'] = $row['investment']['memberCompany'] ? $row['investment']['memberCompany']->member_id :'N/A';
                $account_holder_name = $row['investment']['member']->first_name.' '.$row['investment']['member']->last_name ;
                //getMemberData($row['investment']->member_id)->first_name.' '.getMemberData($row['investment']->member_id)->last_name;
                $val['account_holder_name'] = $account_holder_name;
                /*
                if($row['investment']->current_balance){
                    $deposit_amount = round($row['investment']->current_balance).' &#8377';
                    $val['deposit_amount'] = $deposit_amount;
                }else{
                    $val['deposit_amount'] = '';
                }
                */
                if($row['investment']){
                    $deposit_amount = round($row->balance).' &#8377';
                    $val['deposit_amount'] = $deposit_amount;
                }else{
                    $val['deposit_amount'] = 'N/A';
                }
                if($row->tds_amount){
                    $tds_amount = round($row->tds_amount).' &#8377';
                    $val['tds_amount'] = $tds_amount;
                }else{
                    $val['tds_amount'] = 'N/A';
                }
                if($row->maturity_amount_till_date){
                    $maturity_amount = round($row->maturity_amount_till_date).' &#8377';
                    $val['maturity_amount'] = $maturity_amount;
                }else{
                    $val['maturity_amount'] = 'N/A';
                }
                if($row->maturity_amount_payable){
                    $maturity_amount_payable = round($row->maturity_amount_payable+$row->tds_amount).' &#8377';
                    $val['maturity_amount_payable'] = $maturity_amount_payable;
                }else{
                    $val['maturity_amount_payable'] = 'N/A';
                }
                if($row->final_amount){
                    $val['final_amount'] = round($row->final_amount).' &#8377';
                }elseif($row->maturity_amount_payable){
                    $val['final_amount'] = round($row->maturity_amount_payable-$row->tds_amount).' &#8377';
                }else{
                    $val['final_amount'] = 'N/A';
                }
                $val['mobile_number'] = $row->mobile_number??'N/A';
                $val['ssb_account'] = $row->ssb_account??'N/A';
                $val['bank_name'] = $row->bank_name??'N/A';
                $val['bank_account'] = $row->bank_account_number??'N/A';
                $val['ifsc'] = $row->bank_ifsc??'N/A';
                if($row->letter_photo_id){
                    $fileName = $row['getFileDataCustom']->file_name; //getFirstFileData($row->letter_photo_id)->file_name;
                    // $val['letter_photo'] = '<a href="core/storage/images/emergancy-maturity/'.$fileName.'" target="blank">'.$fileName.'</a>';
                    $val['letter_photo'] = '<a href="'.ImageUpload::generatePreSignedUrl('emergancy-maturity/' . $fileName).'" target="blank">'.$fileName.'</a>';
                }else{
                    $val['letter_photo'] = 'N/A';
                }
                $val['payment_date'] = date("d/m/Y", strtotime( $row->date));
                $rowReturn[] = $val;
            }
        $output = array("branch_id"=>Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
        return json_encode($output);
        }
    }
    /**
     * Adds emergency maturity data to the system.
     *
     * @throws Some_Exception_Class description of exception
     * @return Some_Return_Value
     */
    public function add()
    {
		if(check_my_permission( Auth::user()->id,"89") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Emergency Maturity';
        $data['expenseCategories'] = AccountHeads::select('id','sub_head')->where('parent_id',4)->get();
        $data['expenseSubCategories'] = AccountHeads::select('id','sub_head','parent_id')->whereIn('parent_id', array(14,86))->get();
        $data['liabilityHeads'] = array('');
        $data['rentOwners'] = RentLiability::select('id','owner_name')->get();
        $data['branches'] = Branch::select('id','name','branch_code')->where('status',1)->get();
        //$data['tds'] = TdsDeposit::where('id',1)->first();
        return view('templates.admin.emergancy-maturity.add',$data);
    }
    /**
     * Get investment details by account number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getInvestmentDetails(Request $request)
    {
        $investmentAccount = $request->val;
        $finalAmount = 0;
        $mInvestment = Memberinvestments::with('plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();
        if($mInvestment){
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), 33);
            $to = ($globaldate >= $mInvestment->maturity_date) ? \Carbon\Carbon::parse($mInvestment->maturity_date) : \Carbon\Carbon::parse($globaldate);
            $from = \Carbon\Carbon::parse($mInvestment->created_at);
            $investmentMonths = $to->diffInMonths($from,true);
            $bankList =  \App\Models\CompanyBranch::with('branch:id,name,branch_code')->where('company_id', $mInvestment->company_id)->where('status',1)->get(['id', 'branch_id','status','company_id']);
            $demandAdviceRecord = DemandAdvice::where('investment_id',$mInvestment->id)->where('is_deleted',0)->count();
                if($mInvestment->plan->plan_category_code == 'S')
                {
                    $IsReinvests = 1;
                    $finalAmount = 0;
                    $eliMbAmount = 0;
                    $eliAmount = 0;
                    $message = 'Emergency Maturity Permission Not Allowed for SSB Account!';
                    $status = 400;
                }
                else if($demandAdviceRecord > 0){
                    $message = 'Already request created for this paln!';
                    $status = 500;
                    $IsReinvests = 1;
                    $finalAmount = 0;
                    $eliMbAmount = 0;
                    $eliAmount = 0;
                    $tdsAmount = 0;
                    $tdsPercentage = 0;
                    $investmentTds = 0;
                }else{
                    $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));
                    $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->where('is_deleted',0)->orderby('created_at', 'asc')->get();
                    $keyVal = 0;
                    $cInterest = 0;
                    $regularInterest = 0;
                    $total = 0;
                    $monthly = array(10,11,6,2);
                    $daily = array(7);
                    $preMaturity = array(4,5);
                    $fixed = array(8,9);
                    // $samraddhJeevan = array(2,6);
                    $moneyBack = array(3);
                    $totalDeposit = 0;
                    $collection = 0;
                    $totalInterestDeposit = 0;
                    $cDate = date('Y-m-d');
                    // if(in_array($mInvestment->plan_id, $monthly)){
                    //     if($investmentData){
                    //         $investmentMonths = $mInvestment->tenure*12;
                    //         $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //         for ($i=1; $i <= $investmentMonths ; $i++){
                    //                 $val = $mInvestment;
                    //                 $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                    //                 $cMonth = date('m');
                    //                 $cYear = date('Y');
                    //                 $cuurentInterest = $mInvestment->interest_rate;
                    //                 $totalDeposit = $totalInvestmentAmount;
                    //                 $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->where('is_deleted',0)->max('created_at');
                    //                 $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->where('is_deleted',0)->sum('deposit');
                    //                 $d1 = explode('-',$mInvestment->created_at);
                    //                 $d2 = explode('-',$nDate);
                    //                 $ts1 = strtotime($mInvestment->created_at);
                    //                 $ts2 = strtotime($nDate);
                    //                 $year1 = date('Y', $ts1);
                    //                 $year2 = date('Y', $ts2);
                    //                 $month1 = date('m', $ts1);
                    //                 $month2 = date('m', $ts2);
                    //                 $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    //                 if($cMonth > $d2[1] && $cYear > $d2[0]){
                    //                     if($previousRecord){
                    //                         $previousDate = explode('-',$previousRecord);
                    //                         $previousMonth = $previousDate[1];
                    //                         if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
                    //                             $defaulterInterest = 1.50;
                    //                             $isDefaulter = 1;
                    //                         }else{
                    //                             $defaulterInterest = 0;
                    //                             $isDefaulter = 0;
                    //                         }
                    //                     }else{
                    //                         $defaulterInterest = 0;
                    //                         $isDefaulter = 0;
                    //                     }
                    //                 }else{
                    //                     $defaulterInterest = 0;
                    //                     $isDefaulter = 1;
                    //                 }
                    //                 $cfAmount = Memberinvestments::where('id',$val->id)->first();
                    //                 if($val->deposite_amount*$monthDiff <= $totalDeposit){
                    //                     $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                    //                     $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                    //                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                    //                     $aviAmount = $val->deposite_amount;
                    //                     $total = $total+$val->deposite_amount;
                    //                     if($monthDiff % 3 == 0 && $monthDiff != 0){
                    //                         $total = $total+$regularInterest;
                    //                         $cInterest = $regularInterest;
                    //                     }else{
                    //                         $total = $total;
                    //                         $cInterest = 0;
                    //                     }
                    //                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                    //                     $addInterest = ($cuurentInterest-$defaulterInterest);
                    //                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                    //                     $interest = number_format((float)$a, 2, '.', '');
                    //                     $totalInterestDeposit = number_format((float)$totalInterestDeposit+$interest, 2, '.', '');
                    //                 }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                    //                     $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                    //                     if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                    //                         $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                    //                         Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                    //                         $collection = (int) $totalDeposit+(int) $pendingAmount;
                    //                     }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){
                    //                         Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                    //                         $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                    //                     }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){
                    //                         Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                    //                         $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                    //                     }
                    //                     $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
                    //                     if($checkAmount > 0){
                    //                        $aviAmount = $checkAmount;
                    //                        $total = $total+$checkAmount;
                    //                         if($monthDiff % 3 == 0 && $monthDiff != 0){
                    //                             $total = $total+$regularInterest;
                    //                             $cInterest = $regularInterest;
                    //                         }else{
                    //                             $total = $total;
                    //                             $cInterest = 0;
                    //                         }
                    //                         $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                    //                         $addInterest = ($cuurentInterest-$defaulterInterest);
                    //                         $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                    //                         $interest = number_format((float)$a, 2, '.', '');
                    //                     }else{
                    //                         $aviAmount = 0;
                    //                         $total = 0;
                    //                         $cuurentInterest = 0;
                    //                         $interest = 0;
                    //                         $addInterest = 0;
                    //                     }
                    //                     $totalInterestDeposit = number_format((float)$totalInterestDeposit+$interest, 2, '.', '');
                    //                 }
                    //         }
                    //         if($request->type == 2){
                    //             $finalAmount = round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100));
                    //         }else{
                    //             $finalAmount = round($totalDeposit+$totalInterestDeposit);
                    //         }
                    //         /******** TDS Start***********/
                    //     }else{
                    //         $finalAmount = 0;
                    //     }
                    // }elseif(in_array($mInvestment->plan_id, $daily)){
                    //     if($investmentData){
                    //             $cMonth = date('m');
                    //             $cYear = date('Y');
                    //             $cuurentInterest = $mInvestment->interest_rate;
                    //             $tenureMonths = $mInvestment->tenure*12;
                    //             $i = 0;
                    //             for ($i = 0; $i <= $tenureMonths; $i++){
                    //                 /*$integer = $i+1;
                    //                 $createdMonth = date("m", strtotime($mInvestment->created_at));
                    //                 $createdYear = date("Y", strtotime($mInvestment->created_at));
                    //                 if($createdMonth > $integer){
                    //                     $month = $createdMonth+$i;
                    //                     $year = $createdYear;
                    //                 }elseif($integer == $createdMonth){
                    //                     $month = 1;
                    //                     $year = $createdYear+1;
                    //                 }elseif(($i+1) > $createdMonth){
                    //                     $month = ($integer-$createdMonth)+1;
                    //                     $year = $createdYear+1;
                    //                 }*/
                    //                 //$month = date("m", strtotime("".$i." month", strtotime($mInvestment->created_at)));
                    //                 //$year = date("Y", strtotime("".$i." month", strtotime($mInvestment->created_at)));
                    //                 $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($mInvestment->created_at)));
                    //                 $implodeArray = explode('-',$newdate);
                    //                 $year = $implodeArray[0];
                    //                 //$month = $implodeArray[1];
                    //                 $cdate = $mInvestment->created_at;
                    //                 $cexplodedate = explode('-',$mInvestment->created_at);
                    //                 if(($cexplodedate[1]+$i) > 12){
                    //                     $month = ($cexplodedate[1]+$i)-12;
                    //                 }else{
                    //                     $month = $cexplodedate[1]+$i;
                    //                 }
                    //                 if(($i+1) == 13){
                    //                     $fRecord = Daybook::where('investment_id', $mInvestment->id)
                    //                     ->whereMonth('created_at', $month)->whereYear('created_at', $year)->where('is_deleted',0)->first();
                    //                     if($fRecord){
                    //                         $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->where('is_deleted',0)->sum('deposit');
                    //                     }else{
                    //                        $total = Daybook::where('investment_id', $mInvestment->id)
                    //                     ->whereMonth('created_at', $month)->whereYear('created_at', $year)->where('is_deleted',0)->sum('deposit');
                    //                     }
                    //                 }else{
                    //                     $total = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //                 }
                    //                 $totalDeposit = $totalDeposit+$total;
                    //                 $countDays = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->whereMonth('created_at', $month)->whereYear('created_at', $year)->where('is_deleted',0)->count();
                    //                 /*if($cMonth > $month && $cYear > $year){
                    //                     if($countDays < 25 && ($mInvestment->deposite_amount*25) > $total){
                    //                         $defaulterInterest = 1.50;
                    //                         $isDefaulter = 1;
                    //                     }else{
                    //                         $defaulterInterest = 0;
                    //                         $isDefaulter = 0;
                    //                     }
                    //                 }else{
                    //                     $defaulterInterest = 0;
                    //                     $isDefaulter = 0;
                    //                 }*/
                    //                 if(($mInvestment->deposite_amount*25) > $total){
                    //                     $defaulterInterest = 1.50;
                    //                     $isDefaulter = 1;
                    //                 }else{
                    //                     $defaulterInterest = 0;
                    //                     $isDefaulter = 0;
                    //                 }
                    //                 if($tenureMonths == 12){
                    //                     $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
                    //                 }elseif($tenureMonths == 24){
                    //                     $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
                    //                 }elseif($tenureMonths == 36){
                    //                     $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
                    //                 }elseif($tenureMonths == 60){
                    //                     $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
                    //                 }
                    //                 if(($tenureMonths-$i) == 0){
                    //                     $interest = 0;
                    //                 }
                    //                 $totalInterestDeposit = $totalInterestDeposit+$interest;
                    //             }
                    //         $finalAmount = round($totalDeposit+$totalInterestDeposit);
                    //         /******** TDS Start***********/
                    //         /*$investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');
                    //         $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');
                    //         $checkYear = $cYear;
                    //         $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
                    //         if($formG){
                    //             $tdsAmount = 0;
                    //             $tdsPercentage = 0;
                    //             $investmentTds = 0;
                    //         }else{
                    //             $memberData = getMemberData($mInvestment->member_id);
                    //             $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
                    //             $years = floor($diff / (365*60*60*24));
                    //             if($years >= 60){
                    //                $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
                    //             }else{
                    //                 $penCard = get_member_id_proof($mInvestment->member_id,5);
                    //                 if($penCard){
                    //                     $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
                    //                 }else{
                    //                     $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
                    //                 }
                    //             }
                    //             if($tdsDetail){
                    //                 $tdsAmount = $tdsDetail->tds_amount;
                    //                 $tdsPercentage = $tdsDetail->tds_per;
                    //                 $currentInterst = $totalInterestDeposit-$existsInterst;
                    //                 if($currentInterst > $tdsAmount){
                    //                     $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                    //                     $investmentTds = round(($investmentTds+$tdsAmountonInterest),2);
                    //                 }else{
                    //                     $investmentTds = 0;
                    //                 }
                    //             }else{
                    //                 $tdsAmount = 0;
                    //                 $tdsPercentage = 0;
                    //                 $investmentTds = 0;
                    //             }
                    //         }*/
                    //         /******** TDS Start***********/
                    //     }else{
                    //         $finalAmount = 0;
                    //     }
                    // }elseif(in_array($mInvestment->plan_id, $preMaturity)){
                    //     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //     if($investmentData){
                    //         $cDate = date('Y-m-d');
                    //         $ts1 = strtotime($mInvestment->created_at);
                    //         $ts2 = strtotime($cDate);
                    //         $year1 = date('Y', $ts1);
                    //         $year2 = date('Y', $ts2);
                    //         $month1 = date('m', $ts1);
                    //         $month2 = date('m', $ts2);
                    //         $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    //         if($mInvestment->plan_id == 4){
                    //             if($monthDiff >= 0 && $monthDiff <= 36){
                    //                 $cuurentInterest = 8;
                    //             }else if($monthDiff >= 37 && $monthDiff <= 48){
                    //                 $cuurentInterest = 8.25;
                    //             }else if($monthDiff >= 49 && $monthDiff <= 60){
                    //                 $cuurentInterest = 8.50;
                    //             }else if($monthDiff >= 61 && $monthDiff <= 72){
                    //                 $cuurentInterest = 8.75;
                    //             }else if($monthDiff >= 73 && $monthDiff <= 84){
                    //                 $cuurentInterest = 9;
                    //             }else if($monthDiff >= 85 && $monthDiff <= 96){
                    //                 $cuurentInterest = 9.50;
                    //             }else if($monthDiff >= 97 && $monthDiff <= 108){
                    //                 $cuurentInterest = 10;
                    //             }else if($monthDiff >= 109 && $monthDiff <= 120){
                    //                 $cuurentInterest = 11;
                    //             }else{
                    //                 $cuurentInterest = 11;
                    //             }
                    //         }elseif($mInvestment->plan_id == 5){
                    //             if($monthDiff >= 0 && $monthDiff <= 12){
                    //                 $cuurentInterest = 5;
                    //             }else if($monthDiff >= 12 && $monthDiff <= 24){
                    //                 $cuurentInterest = 6;
                    //             }else if($monthDiff >= 24 && $monthDiff <= 36){
                    //                 $cuurentInterest = 6.50;
                    //             }else if($monthDiff >= 36 && $monthDiff <= 48){
                    //                 $cuurentInterest = 7;
                    //             }else if($monthDiff >= 48 && $monthDiff <= 60){
                    //                 $cuurentInterest = 9;
                    //             }else{
                    //                 $cuurentInterest = 9;
                    //             }
                    //         }
                    //         if($mInvestment->plan_id == 4){
                    //             /*if($cDate < $maturity_date && $monthDiff != 120){
                    //                 $defaulterInterest = 1.50;
                    //                 $isDefaulter = 1;
                    //             }else{
                    //                 $defaulterInterest = 0;
                    //                 $isDefaulter = 0;
                    //             }*/
                    //             $defaulterInterest = 1.50;
                    //             $isDefaulter = 0;
                    //             $irate = ($cuurentInterest-$defaulterInterest) / 1;
                    //             $year = $monthDiff / 12;
                    //             $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);
                    //         }else{
                    //             if($cDate < $maturity_date && $monthDiff != 60){
                    //                 $defaulterInterest = 1.50;
                    //                 $isDefaulter = 1;
                    //             }else{
                    //                 $defaulterInterest = 0;
                    //                 $isDefaulter = 0;
                    //             }
                    //             $irate = ($cuurentInterest-$defaulterInterest) / 1;
                    //             $year = $monthDiff / 12;
                    //             $maturity=0;
                    //             $freq = 4;
                    //             for($i=1; $i<=$monthDiff;$i++){
                    //                 $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
                    //                 $maturity = $maturity+$rmaturity;
                    //             }
                    //             $result =  $maturity-$totalInvestmentAmount;
                    //         }
                    //         $finalAmount = round($totalInvestmentAmount+$result);
                    //         /******** TDS Start***********/
                    //         /*$investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');
                    //         $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');
                    //         $checkYear = date("Y", strtotime(convertDate($cDate)));
                    //         $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
                    //         if($formG){
                    //             $tdsAmount = 0;
                    //             $tdsPercentage = 0;
                    //             $investmentTds = 0;
                    //         }else{
                    //             $memberData = getMemberData($mInvestment->member_id);
                    //             $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
                    //             $years = floor($diff / (365*60*60*24));
                    //             if($years >= 60){
                    //                $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
                    //             }else{
                    //                 $penCard = get_member_id_proof($mInvestment->member_id,5);
                    //                 if($penCard){
                    //                     $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
                    //                 }else{
                    //                     $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
                    //                 }
                    //             }
                    //             if($tdsDetail){
                    //                 $tdsAmount = $tdsDetail->tds_amount;
                    //                 $tdsPercentage = $tdsDetail->tds_per;
                    //                 $currentInterst = $result-$existsInterst;
                    //                 if($currentInterst > $tdsAmount){
                    //                     $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                    //                     $investmentTds = round(($investmentTds+$tdsAmountonInterest),2);
                    //                 }else{
                    //                     $investmentTds = 0;
                    //                 }
                    //             }else{
                    //                 $tdsAmount = 0;
                    //                 $tdsPercentage = 0;
                    //                 $investmentTds = 0;
                    //             }
                    //         }*/
                    //         /******** TDS Start***********/
                    //     }else{
                    //         $finalAmount = 0;
                    //         $defaulterInterest = 0;
                    //     }
                    // }elseif(in_array($mInvestment->plan_id, $fixed)){
                    //     if($investmentData){
                    //             $cDate = date('Y-m-d');
                    //             $cYear = date('Y');
                    //             $cuurentInterest = $mInvestment->interest_rate;
                    //             if($cDate < $maturity_date){
                    //                 $defaulterInterest = 1.50;
                    //                 $isDefaulter = 1;
                    //             }else{
                    //                 $defaulterInterest = 0;
                    //                 $isDefaulter = 0;
                    //             }
                    //         $irate = ($cuurentInterest-$defaulterInterest) / 1;
                    //         $year = 10;
                    //         $interstAmount =  ( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount);
                    //         $finalAmount = round($mInvestment->deposite_amount+$interstAmount);
                    //         /******** TDS Start***********/
                    //         /*$investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');
                    //         $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');
                    //         $checkYear = date("Y", strtotime(convertDate($cDate)));
                    //         $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
                    //         if($formG){
                    //             $tdsAmount = 0;
                    //             $tdsPercentage = 0;
                    //             $investmentTds = 0;
                    //         }else{
                    //             $memberData = getMemberData($mInvestment->member_id);
                    //             $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
                    //             $years = floor($diff / (365*60*60*24));
                    //             if($years >= 60){
                    //                $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
                    //             }else{
                    //                 $penCard = get_member_id_proof($mInvestment->member_id,5);
                    //                 if($penCard){
                    //                     $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
                    //                 }else{
                    //                     $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
                    //                 }
                    //             }
                    //             if($tdsDetail){
                    //                 $tdsAmount = $tdsDetail->tds_amount;
                    //                 $tdsPercentage = $tdsDetail->tds_per;
                    //                 $currentInterst = $interstAmount-$existsInterst;
                    //                 if($currentInterst > $tdsAmount){
                    //                     $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                    //                     $investmentTds = round(($investmentTds+$tdsAmountonInterest),2);
                    //                 }else{
                    //                     $investmentTds = 0;
                    //                 }
                    //             }else{
                    //                 $tdsAmount = 0;
                    //                 $tdsPercentage = 0;
                    //                 $investmentTds = 0;
                    //             }
                    //         }*/
                    //         /******** TDS Start***********/
                    //     }else{
                    //         $finalAmount = 0;
                    //     }
                    // }elseif(in_array($mInvestment->plan_id, $moneyBack)){
                    //     if($investmentData){
                    //         $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
                    //         $years = floor($diff / (365*60*60*24));
                    //         if($cDate >= $maturity_date){
                    //             if (strpos($investmentAccount, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                    //                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>',$mInvestment->last_deposit_to_ssb_date)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //             }else{
                    //                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //             }
                    //             $maturityAmount = getMoneyBackAmount($mInvestment->id);
                    //             if($maturityAmount){
                    //                 $finalAmount = $maturityAmount->available_amount;
                    //             }else{
                    //                 $finalAmount = $totalInvestmentAmount;
                    //             }
                    //         }elseif($cDate < $maturity_date){
                    //             $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //             $depositInterest = \App\Models\InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();
                    //             //$investmentMonths = $mInvestment->tenure*12;
                    //            if (strpos($mInvestment->account_number, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                    //                 $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
                    //                 $ts1 = strtotime($sDate);
                    //                 $ts2 = strtotime($cDate);
                    //                 $year1 = date('Y', $ts1);
                    //                 $year2 = date('Y', $ts2);
                    //                 $month1 = date('m', $ts1);
                    //                 $month2 = date('m', $ts2);
                    //                 $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    //                 $investmentMonths = $monthDiff;
                    //                 $totalInvestmentAmount = \App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>',$mInvestment->last_deposit_to_ssb_date)->where('is_deleted',0)->sum('deposit');
                    //             }else{
                    //                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->where('is_deleted',0)->sum('deposit');
                    //                 $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
                    //                 $ts1 = strtotime($sDate);
                    //                 $ts2 = strtotime($cDate);
                    //                 $year1 = date('Y', $ts1);
                    //                 $year2 = date('Y', $ts2);
                    //                 $month1 = date('m', $ts1);
                    //                 $month2 = date('m', $ts2);
                    //                 $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    //                 $investmentMonths = $monthDiff;
                    //             }
                    //             for ($i=1; $i <= $investmentMonths ; $i++){
                    //                 $val = $mInvestment;
                    //                 $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                    //                 $cMonth = date('m');
                    //                 $cYear = date('Y');
                    //                 $cuurentInterest = $mInvestment->interest_rate;
                    //                 $totalDeposit = $totalInvestmentAmount;
                    //                 $ts1 = strtotime($mInvestment->created_at);
                    //                 $ts2 = strtotime($nDate);
                    //                 $year1 = date('Y', $ts1);
                    //                 $year2 = date('Y', $ts2);
                    //                 $month1 = date('m', $ts1);
                    //                 $month2 = date('m', $ts2);
                    //                 $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    //                 $defaulterInterest = 0;
                    //                 if($val->deposite_amount*$monthDiff <= $totalDeposit){
                    //                     $aviAmount = $val->deposite_amount;
                    //                     $total = $total+$val->deposite_amount;
                    //                     if($monthDiff % 3 == 0 && $monthDiff != 0){
                    //                         $total = $total+$regularInterest;
                    //                         $cInterest = $regularInterest;
                    //                     }else{
                    //                         $total = $total;
                    //                         $cInterest = 0;
                    //                     }
                    //                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                    //                     $addInterest = ($cuurentInterest-$defaulterInterest);
                    //                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                    //                     $interest = number_format((float)$a, 2, '.', '');
                    //                     $totalInterestDeposit = $totalInterestDeposit+$interest;
                    //                 }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                    //                     $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                    //                     if($checkAmount > 0){
                    //                        $aviAmount = $checkAmount;
                    //                        $total = $total+$checkAmount;
                    //                         if($monthDiff % 3 == 0 && $monthDiff != 0){
                    //                             $total = $total+$regularInterest;
                    //                             $cInterest = $regularInterest;
                    //                         }else{
                    //                             $total = $total;
                    //                             $cInterest = 0;
                    //                         }
                    //                         $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                    //                         $addInterest = ($cuurentInterest-$defaulterInterest);
                    //                         $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                    //                         $interest = number_format((float)$a, 2, '.', '');
                    //                     }else{
                    //                         $aviAmount = 0;
                    //                         $total = 0;
                    //                         $cuurentInterest = 0;
                    //                         $interest = 0;
                    //                         $addInterest = 0;
                    //                     }
                    //                     $totalInterestDeposit = $totalInterestDeposit+$interest;
                    //                 }
                    //             }
                    //             if($depositInterest){
                    //                 $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                    //             }else{
                    //                 $availableAmountFd = 0;
                    //             }
                    //             $finalAmount = round($totalDeposit+$totalInterestDeposit+$availableAmountFd);
                    //             /******** TDS Start***********/
                    //             /*$investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');
                    //             $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');
                    //             $checkYear = date("Y", strtotime(convertDate($cDate)));
                    //             $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
                    //             if($formG){
                    //                 $tdsAmount = 0;
                    //                 $tdsPercentage = 0;
                    //                 $investmentTds = 0;
                    //             }else{
                    //                 $memberData = getMemberData($mInvestment->member_id);
                    //                 $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
                    //                 $years = floor($diff / (365*60*60*24));
                    //                 if($years >= 60){
                    //                    $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
                    //                 }else{
                    //                     $penCard = get_member_id_proof($mInvestment->member_id,5);
                    //                     if($penCard){
                    //                         $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
                    //                     }else{
                    //                         $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
                    //                     }
                    //                 }
                    //                 if($tdsDetail){
                    //                     $tdsAmount = $tdsDetail->tds_amount;
                    //                     $tdsPercentage = $tdsDetail->tds_per;
                    //                     $currentInterst = $totalInterestDeposit-$existsInterst;
                    //                     if($currentInterst > $tdsAmount){
                    //                         $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                    //                         $investmentTds = round(($investmentTds+$tdsAmountonInterest),2);
                    //                     }else{
                    //                         $investmentTds = 0;
                    //                     }
                    //                 }else{
                    //                     $tdsAmount = 0;
                    //                     $tdsPercentage = 0;
                    //                     $investmentTds = 0;
                    //                 }
                    //             }*/
                    //             /******** TDS Start***********/
                    //         }
                    //     }else{
                    //         $finalAmount = 0;
                    //     }
                    // }
                    $mInvestment = $mInvestment;
                    $planCategory = $mInvestment->plan->plan_category_code;
                        $interestData = getplanroi($mInvestment->plan_id);
                        $checkRoi = getRoi($interestData,$investmentMonths,$mInvestment);
                        $ActualInterest =  $checkRoi['ActualInterest'];
                        if(!$checkRoi['roiExist'])
                        {
                            $message = 'Maturity Setting Not Updated for this Plan!';
                            $status = 400;
                        }
                        // if ($mInvestment->plan->plan_category_code == 'D' ||  $mInvestment->plan->plan_category_code == 'M') {
                            $result = maturityCalculation($mInvestment, 'demand_create',$investmentMonths,$ActualInterest);
                            $isDefaulter = $result['defaulter'];
                            $finalAmount = $result['final_amount'];
                        // }
                    if (strpos($investmentAccount, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                        $IsReinvests = 0;
                        $eliMB = EliMoneybackInvestments::where('account_number',$investmentAccount)->first();
                        if($eliMB){
                            $getMbTrsAmount = getMbTrsAmount($mInvestment->id);
                            $cInterest = 0;
                            $regularInterest = 0;
                            $total = 0;
                            $totalDeposit = 0;
                            $totalInterestDeposit = 0;
                            $date1 = $getMbTrsAmount->created_at;
                            $date2 = date("Y-m-d");
                            $ts1 = strtotime($date1);
                            $ts2 = strtotime($date2);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);
                            $totalInvestmentAmount = $eliMB->mb_fd_amount;
                            for ($i=1; $i <= $investmentMonths ; $i++){
                                $nDate =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.$i.' months'));
                                $cMonth = date('m');
                                $cYear = date('Y');
                                $cuurentInterest = $mInvestment->interest_rate;
                                $totalDeposit = $totalInvestmentAmount;
                                $ts1 = strtotime($mInvestment->created_at);
                                $ts2 = strtotime($nDate);
                                $year1 = date('Y', $ts1);
                                $year2 = date('Y', $ts2);
                                $month1 = date('m', $ts1);
                                $month2 = date('m', $ts2);
                                $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                                $defaulterInterest = 0;
                                $aviAmount = $totalDeposit;
                                $total = $total+$aviAmount;
                                if($monthDiff % 3 == 0 && $monthDiff != 0){
                                    $total = $total+$regularInterest;
                                    $cInterest = $regularInterest;
                                }else{
                                    $total = $total;
                                    $cInterest = 0;
                                }
                                $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                                $addInterest = ($cuurentInterest-$defaulterInterest);
                                $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                                $interest = number_format((float)$a, 2, '.', '');
                                $totalInterestDeposit = $totalInterestDeposit+$interest;
                            }
                            $eliMbAmount = round($totalInvestmentAmount+$totalInterestDeposit);
                            $eliAmount = 0;
                        }else{
                            $eliAmount = investmentEliAmount($mInvestment->id);
                            $eliMbAmount = 0;
                        }
                    }else{
                        $IsReinvests = 1;
                        $eliMbAmount = 0;
                        $eliAmount = 0;
                    }
                    $message = '';
                    $status = 200;
                }
            //}
        }else{
            $IsReinvests = 1;
            $finalAmount = 0;
            $eliMbAmount = 0;
            $eliAmount = 0;
            $bankList = null;
            $message = 'Record Not Found!';
            $status = 400;
        }
        $investmentDetails = $mInvestment;
        if($investmentDetails){
            /*if (strpos($investmentAccount, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                $investmentAmount = Daybook::where('investment_id',$investmentDetails->id)->whereDate('created_at','>',$investmentDetails->last_deposit_to_ssb_date)->whereIn('transaction_type',[2,4])->sum('deposit');
            }else{
                $investmentAmount = Daybook::where('investment_id',$investmentDetails->id)->whereDate('created_at','<=',$investmentDetails->created_at)->whereIn('transaction_type',[2,4])->sum('deposit');
                if($investmentAmount == 0){
                    $investmentAmount = $mInvestment->deposite_amount;
                }
            }*/
            $deposit=Daybook::where('investment_id',$investmentDetails->id)->where('account_no',$investmentAccount)->where('transaction_type','>',1)->whereNotIn('transaction_type', [3,5,6,7,8,9,10,11,12,13,14,15,19])->where('is_deleted',0)->sum('deposit');
            $withdrawal=Daybook::where('investment_id',$investmentDetails->id)->where('account_no',$investmentAccount)->where('transaction_type','>',1)->whereNotIn('transaction_type', [3,5,6,7,8,9,10,11,12,13,14,15,19])->where('is_deleted',0)->sum('withdrawal');
            $investmentAmount = $deposit-$withdrawal;
            /*if (strpos($investmentAccount, 'R-') !== false) {
                $investmentAmount = Daybook::where('investment_id',$investmentDetails->id)/*->whereDate('created_at','>=',$investmentDetails->created_at)*//*->whereIn('transaction_type',[2,4])->sum('deposit');*/
            /*}else{
                $investmentAmount = Daybook::where('investment_id',$investmentDetails->id)->whereDate('created_at','>=',$investmentDetails->created_at)->whereIn('transaction_type',[2,4])->sum('deposit');
            }*/
        }else{
            $investmentAmount = 0;
        }
        $finalAmount= number_format((float)$finalAmount, 2, '.', '');
        if(isset($investmentDetails) && isset($investmentDetails['current_balance'])){
            $investmentDetails['current_balance'] = InvestmentBalance::where('investment_id',$investmentDetails['id'])->value('totalBalance');
        }
        $return_array = compact('investmentDetails','finalAmount','message','status','investmentAmount','eliMbAmount','eliAmount','IsReinvests','bankList');
        return json_encode($return_array);
    }
    /**
     * Save Demand Advice.
     * Route: /save-account-head
     * Method: get
     * @return  array()  Response
     */
    public function save(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->investment as $key => $value) {
                if(isset($value['bill_photo'])){
                    $mainFolder = 'emergancy-maturity';
                    $file = $value['bill_photo'];
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
                    ImageUpload::upload($file, $mainFolder,$fname);
                    // $file->move($mainFolder,$fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $res = Files::create($fData);
                    $file_id = $res->id;
                }else{
                    $file_id = NULL;
                }
                $chars = "0123456789";
                $vno = "";
                for ($i = 0; $i < 7; $i++) {
                    $vno .= $chars[mt_rand(0, strlen($chars)-1)];
                    }
                $date = date("Y");
                $month = date("m");
                $depositeAmount = Daybook::where('investment_id', $value['id'])->whereIn('transaction_type',[2,4,18])->where('is_deleted',0)->sum('deposit');
                $withdrawal = Daybook::where('investment_id', $value['id'])->where('is_deleted',0)->sum('withdrawal');
                 $amount =    $depositeAmount-$withdrawal;
                $daData = [
                    'branch_id' =>$request->branch_id,
                    'payment_type' => 4,
                    'is_mature' =>0,
                    'investment_id' => $value['id'],
                    'account_number' => $value['account_number'],
                    'mobile_number' => $value['mobilenumber'],
                    'ssb_account' => $value['ssbaccount'],
                    'bank_name' => $value['bankname'],
                    'bank_account_number' => $value['bankaccount'],
                    'bank_ifsc' => $value['ifsc'],
                    'maturity_amount_till_date' => $value['maturityAmount'],
                    //'maturity_amount_payable' => $value['maturityPayable'],
                    'maturity_amount_payable' => $value['tdsFinalAmount'],
                    'letter_photo_id' => $file_id,
                    'tds_percentage' => $value['tdsPer'],
                    'tds_per_amount' => $value['tdsPerAmount'],
                    'tds_amount' => $value['tdsAmount'],
                    'final_amount' => $value['tdsFinalAmount'],
                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $value['paymentDate']))),
                    'created_at' => $request->created_at,
                    //'voucher_number' => $date.$month.$vno,
                    'interestAmount' => (($value['tdsFinalAmount'] - $amount) < 0) ? 0 : $value['tdsFinalAmount'] - $amount,
                    'company_id' => $value['company_id'],
                ];
                $demandAdvice = DemandAdvice::create($daData);
            }
        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($demandAdvice) {
            return redirect()->route('admin.emergancymaturity.index')->with('success', 'Emergency Maturity Added Successfully!');
        } else {
            return back()->with('alert', 'Problem With Creating Emergency Maturity');
        }
    }
    // public function getTds(Request $request)
    // {
    //     $investmentAccount = $request->investmentAccount;
    //     $interestRateAmount = $request->interestRateAmount;
    //     $cYear = date('Y');
    //     $cDate = date('Y-m-d');
    //     $mInvestment = Memberinvestments::where('account_number',$investmentAccount)->first();
    //     $investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');
    //     $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');
    //     $checkYear = $cYear;
    //     $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
    //     if($formG){
    //         $tdsAmount = 0;
    //         $tdsPercentage = 0;
    //         $investmentTds = 0;
    //     }else{
    //         $memberData = getMemberData($mInvestment->member_id);
    //         $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
    //         $years = floor($diff / (365*60*60*24));
    //         if($years >= 60){
    //            $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
    //         }else{
    //             $penCard = get_member_id_proof($mInvestment->member_id,5);
    //             if($penCard){
    //                 $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
    //             }else{
    //                 $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
    //             }
    //         }
    //         if($tdsDetail){
    //             $tdsAmount = $tdsDetail->tds_amount;
    //             $tdsPercentage = $tdsDetail->tds_per;
    //             $currentInterst = $interestRateAmount;
    //             if($currentInterst > $tdsAmount){
    //                 $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
    //                 $investmentTds = round(($investmentTds+$tdsAmountonInterest),2);
    //             }else{
    //                 $investmentTds = 0;
    //             }
    //         }else{
    //             $tdsAmount = 0;
    //             $tdsPercentage = 0;
    //             $investmentTds = 0;
    //         }
    //     }
    //     $tdsPercentageAmount = $tdsAmount;
    //     $return_array = compact('tdsPercentageAmount','tdsPercentage','investmentTds');
    //     return json_encode($return_array);
    // }
     public function getTds(Request $request)
    {
        $financialYear = date('Y',strtotime($request->globaldate));
        $investmentAccount = $request->investmentAccount;
        $interestRateAmount = $request->interestRateAmount;
        $mInvestment = Memberinvestments::where('account_number',$investmentAccount)->first();
        $startFincancialYear = $financialYear.'04-01';
        $endYear  = date('Y',strtotime('+1 year',strtotime($startFincancialYear)));
        $lastdateFinancialYear =  $endYear.'03-31';
        $result = tdsCalculate($interestRateAmount,$mInvestment,$financialYear,NULL,$startFincancialYear,$lastdateFinancialYear);
        $tdsPercentageAmount = $result['tdsAmount'] ?? 0;
        $tdsPercentage = $result['tdsPercentage'] ?? 0;
        $investmentTds = $result['tdsAmount'] ?? 0;
        // $cYear = date('Y');
        // $cDate = date('Y-m-d');
        // $investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');
        // $sumAmount = \App\Models\MemberInvestmentInterest::where('member_id',$mInvestment->member_id)->whereBetween('date',[$startFincancialYear,$lastdateFinancialYear])->sum('interest_amount');
        // $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->whereBetween('date',[$startFincancialYear,$lastdateFinancialYear])->sum('interest_amount');
        // $checkYear = $cYear;
        // $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
        // if($formG){
        //     $tdsAmount = 0;
        //     $tdsPercentage = 0;
        //     $investmentTds = 0;
        // }else{
        //     $memberData = getMemberData($mInvestment->member_id);
        //     $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
        //     $years = floor($diff / (365*60*60*24));
        //     if($years >= 60){
        //        $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
        //     }else{
        //         $penCard = get_member_id_proof($mInvestment->member_id,5);
        //         if($penCard){
        //             $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
        //         }else{
        //             $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
        //         }
        //     }
        //     if($tdsDetail){
        //         if($sumAmount > $tdsDetail->tds_amount )
        //         {
        //                 if($tdsDetail){
        //                 $tdsAmount = $tdsDetail->tds_amount;
        //                 $tdsPercentage = $tdsDetail->tds_per;
        //                 $currentInterst = $interestRateAmount ;
        //                     $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
        //                     $investmentTds = round(($tdsAmountonInterest),2);
        //                 }else{
        //                 $tdsAmount = 0;
        //                 $tdsPercentage = 0;
        //                 $investmentTds = 0;
        //                 }
        //         }
        //         else if($tdsDetail){
        //             $tdsAmount = $tdsDetail->tds_amount;
        //             $tdsPercentage = $tdsDetail->tds_per;
        //             $currentInterst = $interestRateAmount + $sumAmount ;
        //             if($currentInterst > $tdsAmount){
        //                 $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
        //                 $investmentTds = round(($tdsAmountonInterest),2);
        //             }else{
        //                 $investmentTds = 0;
        //             }
        //         }else{
        //             $tdsAmount = 0;
        //             $tdsPercentage = 0;
        //             $investmentTds = 0;
        //         }
        //     } else{
        //             $tdsAmount = 0;
        //             $tdsPercentage = 0;
        //             $investmentTds = 0;
        //         }
        // }
        // $tdsPercentageAmount = $tdsAmount;
         $return_array = compact('tdsPercentageAmount','tdsPercentage','investmentTds');
         return json_encode($return_array);
    }
}