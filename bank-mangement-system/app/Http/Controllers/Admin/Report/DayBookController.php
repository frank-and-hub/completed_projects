<?php
namespace App\Http\Controllers\Admin\Report;
use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Daybook;
use App\Models\ReceivedVoucher;
use App\Models\Branch;
use App\Models\BranchCash;
use App\Models\Transcation;
use App\Models\Memberinvestments;
use App\Models\Plans;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\EmployeeSalary;
use App\Models\AccountHeads;
use App\Models\AllTransaction;
use App\Models\SamraddhBankDaybook ;
use App\Models\BranchDaybook ;
use App\Models\SamraddhBank;
use App\Models\LoanDayBooks;
use App\Models\MemberTransaction;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;
use App\Models\Member;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
use App\Http\Traits\BalanceSheetTrait;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class DayBookController extends Controller
{
    use BalanceSheetTrait;
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
     * Route: /admin/report/daybook
     * Method: get
     * @return  array()  Response
     */
   //Branch Business Report (AMAN !! 15-05)
     public function day_bookReport()
    {
        if(check_my_permission( Auth::user()->id,"128") != "1"){
          return redirect()->route('admin.dashboard');
        }
        $data['title']='Report | DayBook  Report';
        if(Auth::user()->branch_id>0){
          $id=Auth::user()->branch_id;
          $data['branch'] = Branch::where('status',1)->where('id','=',$id)->get();
         }else{
          $data['branch'] = Branch::where('status',1)->get();
         }
        return view('templates.admin.report.day_book', $data);
    }
    public function print_day_bookReport()
    {
        $data['title']='Report | DayBook  Report';
         $startDate = '';
        $endDate = '';
        $branch_id='';
		if(isset($_GET['from_date']))
		{
			$startDate=$_GET['from_date'];
		}
		if(isset($_GET['to_date']))
		{
			$endDate=$_GET['to_date'];
		}
		if(isset($_GET['branch']))
		{
		  $branch_id=$_GET['branch'];
		}
        $planDaily=getPlanID('710')->id;
		$dailyId=array($planDaily);
		$planSSB=getPlanID('703')->id;
		$planKanyadhan=getPlanID('709')->id;
		$planMB=getPlanID('708')->id;
		$planFRD=getPlanID('707')->id;
		$planJeevan=getPlanID('713')->id;
		$planRD=getPlanID('704')->id;
		$planBhavhishya=getPlanID('718')->id;
		$monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
		$planMI=getPlanID('712')->id;
		$planFFD=getPlanID('705')->id;
		$planFD=getPlanID('706')->id;
		$fdId=array($planMI,$planFFD,$planFD);
		$dailyId=array($planDaily);
		$tenure = array(1,3,5,7,10);
		$data['branch_id'] = $branch_id;
		$data['start_date'] = $startDate;
		$data['end_date'] = $endDate;
		$data['cash_in_hand_cr'] = BranchDaybook::where('description_dr','not like','%Eli Amount%')->where('payment_mode',0)->where('branch_id',$branch_id)->where('payment_type','CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
		$data['cash_in_hand_dr'] =BranchDaybook::where('payment_mode',0)->where('branch_id',$branch_id)->where('payment_type','DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
		$data['cheque_cr'] = BranchDaybook::whereIn('payment_mode',[1])->where('payment_type','CR')->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
		$data['cheque_dr'] = BranchDaybook::whereIn('payment_mode',[1])->where('payment_type','DR')->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
		$data['bank_cr'] = BranchDaybook::whereIn('payment_mode',[1,2])->where('payment_type','CR')->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
		$data['bank_dr'] = BranchDaybook::whereIn('payment_mode',[1,2])->where('payment_type','DR')->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
		$existsopening = BranchCash::where('branch_id',$branch_id)->where('entry_date', $startDate)->exists();
		if($existsopening)
		{
		  $cashInhandOpening =   BranchCash::where('branch_id',$branch_id)->where('entry_date', $startDate)->orderBy('entry_date','DESC')->first();
		  $data['cashInhandOpening'] =number_format((float)$cashInhandOpening->opening_balance , 2, '.', '') ;
		}
		else{
		   $cashInhandOpening =   BranchCash::where('branch_id',$branch_id)->where('entry_date','<', $startDate)->orderBy('entry_date','DESC')->first();
		   $data['cashInhandOpening'] =number_format((float)  $cashInhandOpening ->closing_balance , 2, '.', '');
		}
		   $cashInhandclosing= BranchCash::where('branch_id',$branch_id)->where('entry_date','<=', $endDate)->orderBy('entry_date','DESC')->first();
		   $data['cashInhandclosing'] =number_format((float)$cashInhandclosing->balance , 2, '.', '')  ;
         $data['current_daily_new_ac_tenure12'] = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'12');
		$data['current_daily_new_ac_tenure24'] = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'24');
		$data['current_daily_new_ac_tenure36'] = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'36');
		 $data['current_daily_new_ac_tenure60'] = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'60');
		$data['monthly_new_ac_tenure12'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'12');
		$data['monthly_new_ac_tenure36'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'36');
		$data['monthly_new_ac_tenure60'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'60');
		$data['monthly_new_ac_tenure84'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'84');
		$data['monthly_new_ac_tenure120'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'120');
		 $data['monthly_new_ac_tenurekanyadan'] = branchBusinessInvestTenureKanyadhan($startDate,$endDate,$branch_id,$monthlyId,$tenure);
		$data['monthly_new_ac_amt_sum_tenure12'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'12');
		$data['monthly_new_ac_amt_sum_tenure36'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'36');
		$data['monthly_new_ac_amt_sum_tenure60'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'60');
		$data['monthly_new_ac_amt_sum_tenure84'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'84');
		$data['monthly_new_ac_amt_sum_tenure120'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'120');
		$data['monthly_new_ac_amt_sum_tenurekanyadan'] = branchBusinessInvestKanyadhanTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,$tenure);
		$data['current_daily_new_ac_amt_sum_tenure12'] = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'12');
		$data['current_daily_new_ac_amt_sum_tenure24'] = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'24');
		$data['current_daily_new_ac_amt_sum_tenure36'] = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'36');
		$data['current_daily_new_ac_amt_sum_tenure60'] = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'60');
		$data['monthly_new_fd_ac_tenure12'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'12');
		$data['monthly_new_fd_ac_tenure18'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'18');
		$data['monthly_new_fd_ac_tenure48'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'48');
		$data['monthly_new_fd_ac_tenure60'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'60');
		$data['monthly_new_fd_ac_tenure72'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'72');
		$data['monthly_new_fd_ac_tenure96'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'96');
		$data['monthly_new_fd_ac_tenure120'] = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'120');
		$data['monthly_new_fd_sum_ac_tenure12'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'12');
		$data['monthly_new_fd_sum_ac_tenure18'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'18');
		$data['monthly_new_fd_sum_ac_tenure48'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'48');
		$data['monthly_new_fd_sum_ac_tenure60'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'60');
		$data['monthly_new_fd_sum_ac_tenure72'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'72');
		$data['monthly_new_fd_sum_ac_tenure96'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'96');
		$data['monthly_new_fd_sum_ac_tenure120'] = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'120');
		$data['file_chrg_total'] = Daybook::whereIn('transaction_type',['6,10'])->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted',0)->count();
		$data['file_chrg_amount_total'] = Daybook::whereIn('transaction_type',['6,10'])->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');;
		$data['mi_total'] = MemberTransaction::where('type',1)->where('sub_type',11)->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->count();
		$data['mi_amount_total'] = MemberTransaction::where('type',1)->where('sub_type',11)->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');;
		$data['stn_total'] = MemberTransaction::where(function ($q){
			$q->where('type',1)->where('sub_type',12)
			->orwhere('type',21);
		})->where('branch_id',$branch_id)->where('is_deleted',0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->count();
		$data['stn_amount_total'] = MemberTransaction::where(function ($q){
			$q->where('type',1)->where('sub_type',12)
			->orwhere('type',21);
		})->where('branch_id',$branch_id)->where('is_deleted',0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
		 $data['other_total__income_account'] = getExpenseHeadaccountCount(3,1,$startDate,$endDate,$branch_id);
		$data['other_total__expense_account'] = getExpenseHeadaccountCount(4,1,$startDate,$endDate,$branch_id);
		$data['other_total__income_amount'] = headTotalNew(3,$startDate,$endDate,$branch_id);
		$data['other_total__expense_amount'] = headTotalNew(4,$startDate,$endDate,$branch_id);
		 $data['investment_stationary_chrg_account'] = getInvestmentStationarychrgAccount($startDate,$endDate,$branch_id);
		$data['investment_stationary_chrg_amount'] = getInvestmentStationarychrgAmount($startDate,$endDate,$branch_id);
		 $data['loan_total_account'] = LoanDayBooks::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted',0)->count();
		$data['loan_total_amount'] = LoanDayBooks::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted',0)->sum('deposit');
		 $data['received_voucher_account'] =ReceivedVoucher::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count(); ;
		$data['received_voucher_amount'] = ReceivedVoucher::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
	   $renew_emi_recovery_12_Id = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'12');
		$renew_emi_recovery_24_Id = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'24');
		$renew_emi_recovery_36_Id  = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'36');
	   $renew_emi_recovery_60_Id  = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'60');
	   $data['current_renew_emi_recovery_12'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'12',$dailyId);
		$data['current_renew_emi_recovery_24'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'24',$dailyId);
		$data['current_renew_emi_recovery_36'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'36',$dailyId);
		$data['current_renew_emi_recovery_60'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'60',$dailyId);
		 $data['current_renew_emi_recovery_amnt_12'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'12',$dailyId);
		$data['current_renew_emi_recovery_amnt_24'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'24',$dailyId);
		$data['current_renew_emi_recovery_amnt_36'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'36',$dailyId);
		$data['current_renew_emi_recovery_amnt_60'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'60',$dailyId);
		$renew_emi_monthly_recovery_12_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'12');
		$renew_emi_monthly_recovery_84_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'84');
		$renew_emi_monthly_recovery_36_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'36');
		$renew_emi_monthly_recovery_60_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'60');
		$renew_emi_monthly_recovery_120_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'120');
		 $renew_emi_monthly_recovery_kanyadhan  = getmemberinvestementKanyadhanId($startDate,$endDate,$branch_id,$monthlyId,$tenure);
		$data['monthly_renew_emi_recovery_12'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'12',$monthlyId);
		$data['monthly_renew_emi_recovery_84'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'84',$monthlyId);
		$data['monthly_renew_emi_recovery_36'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'36',$monthlyId);
		$data['monthly_renew_emi_recovery_60'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'60',$monthlyId);
		 $data['monthly_renew_emi_recovery_120'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'120',$monthlyId);
		$data['monthly_renew_emi_recovery_acnt_kanyadhan'] = getmemberinvestement_emi_recoverKanyadhan($startDate,$endDate,$branch_id,$renew_emi_monthly_recovery_kanyadhan);
		 $data['monthly_renew_emi_recovery_amnt_12'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'12',$monthlyId);
		$data['monthly_renew_emi_recovery_amnt_84'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'84',$monthlyId);
		$data['monthly_renew_emi_recovery_amnt_36'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'36',$monthlyId);
		$data['monthly_renew_emi_recovery_amnt_60'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'60',$monthlyId);
		 $data['monthly_renew_emi_recovery_amnt_120'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'120',$monthlyId);
		 $data['monthly_renew_emi_recovery_amnt_sum'] = getmemberinvestement_emi_recoverKanyadhan_sum($startDate,$endDate,$branch_id,$renew_emi_monthly_recovery_kanyadhan);
		$renew_emi_fd_recovery_12_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'12');
		$renew_emi_fd_recovery_18_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'18');
		$renew_emi_fd_recovery_48_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'48');
		$renew_emi_fd_recovery_60_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'60');
		$renew_emi_fd_recovery_72_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'72');
		$renew_emi_fd_recovery_96_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'96');
		$renew_emi_fd_recovery_120_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'120');
		 $data['fd_renew_emi_recovery_12'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'12',$fdId);
		$data['fd_renew_emi_recovery_18'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'18',$fdId);
		$data['fd_renew_emi_recovery_48'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'48',$fdId);
		$data['fd_renew_emi_recovery_60'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'60',$fdId);
		$data['fd_renew_emi_recovery_72'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'72',$fdId);
		$data['fd_renew_emi_recovery_96'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'96',$fdId);
		$data['fd_renew_emi_recovery_120'] = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'120',$fdId);
		$data['fd_renew_emi_recovery_amnt_12'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'12',$fdId);
		$data['fd_renew_emi_recovery_amnt_18'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'18',$fdId);
		$data['fd_renew_emi_recovery_amnt_48'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'48',$fdId);
		$data['fd_renew_emi_recovery_amnt_60'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'60',$fdId);
		 $data['fd_renew_emi_recovery_amnt_72'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'72',$fdId);
		 $data['fd_renew_emi_recovery_amnt_96'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'96',$fdId);
		 $data['fd_renew_emi_recovery_amnt_120'] = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'120',$fdId);
		// Payment
		 // Current
		 $data['current_mature_account_12']  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'12');
		 $data['current_mature_account_24']  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'24');
		 $data['current_mature_account_36']  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'36');
		 $data['current_mature_account_60']  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'60');
		 $data['current_mature_amnt_12']  = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'12');
		 $data['current_mature_amnt_24']  = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'24');
		 $data['current_mature_amnt_36']  = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'36');
		 $data['current_mature_amnt_60']  = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'60');
		 // Monthly
		 $data['monthly_mature_ac_tenure12'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'12');
		$data['monthly_mature_ac_tenure36'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'36');
		$data['monthly_mature_ac_tenure60'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'60');
		$data['monthly_mature_ac_tenure84'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'84');
		$data['monthly_mature_ac_tenure120'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'120');
		$data['monthly_mature_fd_ac_tenure_kanyadhan'] = matureInvestTenureKanyadhanNewAcCountType($startDate,$endDate,$branch_id,$fdId,$tenure);
		$data['monthly_mature_ac_amt_sum_tenure12'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'12');
		$data['monthly_mature_ac_amt_sum_tenure36'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'36');
		$data['monthly_mature_ac_amt_sum_tenure60'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'60');
		$data['monthly_mature_ac_amt_sum_tenure84'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'84');
		$data['monthly_mature_ac_amt_sum_tenure120'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'120');
		 $data['monthly_mature_fd_ac_tenure_kanyadhan_amnt'] = matureInvestTenureKanyadhanAmount($startDate,$endDate,$branch_id,$fdId,$tenure);
		// FD
		$data['monthly_mature_fd_ac_tenure12'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'12');
		$data['monthly_mature_fd_ac_tenure18'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'18');
		$data['monthly_mature_fd_ac_tenure48'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'48');
		$data['monthly_mature_fd_ac_tenure60'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'60');
		$data['monthly_mature_fd_ac_tenure72'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'72');
		$data['monthly_mature_fd_ac_tenure96'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'96');
		$data['monthly_mature_fd_ac_tenure120'] = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'120');
		$data['monthly_mature_fd_sum_ac_tenure12'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'12');
		$data['monthly_mature_fd_sum_ac_tenure18'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'18');
		$data['monthly_mature_fd_sum_ac_tenure48'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'48');
		$data['monthly_mature_fd_sum_ac_tenure60'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'60');
		$data['monthly_mature_fd_sum_ac_tenure72'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'72');
		$data['monthly_mature_fd_sum_ac_tenure96'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'96');
		$data['monthly_mature_fd_sum_ac_tenure120'] = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'120');
		$data['samraddhData'] = DB::table('branch_daybook')->select('branch_daybook.*','branch_daybook.created_at as record_created_date','branch_daybook.payment_mode as branch_payment_mode','branch_daybook.payment_type as branch_payment_type','branch_daybook.member_id as branch_member_id','branch_daybook.associate_id as branch_associate_id','member_investments.id','branch_daybook.id as btid')->leftjoin('member_investments','member_investments.id','branch_daybook.type_id')->where('branch_daybook.branch_id',$branch_id)->whereBetween('branch_daybook.entry_date',[$startDate, $endDate])->where('branch_daybook.is_deleted',0)->orderBy('branch_daybook.entry_date','ASC')->get();
		$data['bank'] = SamraddhBank::with('bankAccount')->get();
		// sourab code
			$aa = BranchDaybookAmount($startDate,$endDate,$branch_id);
			$cash_in_hand['DR']=0;
			$cash_in_hand['CR']=0;
			if(array_key_exists('0_CR', $aa))
			{
				$cash_in_hand['CR'] = $aa['0_CR'] ; 
			}
			if(array_key_exists('0_DR', $aa))
			{
			   $cash_in_hand['DR'] = $aa['0_DR'] ; 
			}
			$getBranchOpening_cash =getBranchOpeningDetail($branch_id);
			$balance_cash =0;
			$C_balance_cash =0;
			$currentdate = date('Y-m-d');
			if($getBranchOpening_cash->date==$startDate)
			{
			  $balance_cash =$getBranchOpening_cash ->total_amount;
			  if($endDate == '')
				  {
					$endDate=$currentdate;
				  }
			}
			if($getBranchOpening_cash->date<$startDate)
			{
				if($getBranchOpening_cash->date != '')
				{
					  $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate,$getBranchOpening_cash->date,$getBranchOpening_cash->total_amount,$branch_id);
				}
				else{
					 $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate,$currentdate,$getBranchOpening_cash->total_amount,$branch_id);
					
				}
			 
			  $balance_cash =$getBranchTotalBalance_cash;
			  $data['balance_cash'] = $balance_cash;
			  if($endDate == '')
				  {
					$endDate=$currentdate;
				  }
			}
			$getTotal_DR=getBranchTotalBalanceAllTranDR($startDate,$endDate,$branch_id);
			$getTotal_CR=getBranchTotalBalanceAllTranCR($startDate,$endDate,$branch_id);
			$totalBalance=$getTotal_CR-$getTotal_DR;
			$data['C_balance_cash'] =$balance_cash+$totalBalance;
			//sourab code 					 
        return view('templates.admin.report.print_day_book',$data);
    }
    public function day_filterbookReport(Request $request)
    {
         if ($request->ajax()) {
        if($request['start_date'] !=''){
            $startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
        }
        else{
            $startDate='';
        }
        if($request['end_date'] !=''){
             $endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));
        }
        else {
            $endDate='';
        }
        if($request['branch']!='') {
            $branch_id=$request['branch'];
        }
        else {
            $branch_id='';
        }
		
         if(isset($request['is_search']) && $request['is_search'] == 'yes')
            {
            $cash_in_hand['CR']= BranchDaybook::where('description_dr','not like','%Eli Amount%')->where('payment_mode',0)->where('branch_id',$branch_id)->where('payment_type','CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
            $existsopening = BranchCash::where('branch_id',$branch_id)->where('entry_date', $startDate)->exists();
            if($existsopening)
            {
              $cashInhandOpening =   BranchCash::where('branch_id',$branch_id)->where('entry_date', $startDate)->orderBy('entry_date','DESC')->first();
              $cashInhandOpening = $cashInhandOpening->opening_balance ;
            }
            else{
               $cashInhandOpening =   BranchCash::where('branch_id',$branch_id)->where('entry_date','<=', $startDate)->orderBy('entry_date','DESC')->first();
               if($cashInhandOpening)
               {
                 $cashInhandOpening = number_format((float)  $cashInhandOpening ->closing_balance , 2, '.', '');
               }
                else{
                    $cashInhandOpening =0;
                }
            }
            $cashInhandclosing = BranchCash::where('branch_id',$branch_id)->where('entry_date','<=', $endDate)->orderBy('entry_date','DESC')->first();
            if(isset($cashInhandclosing->balance))
            {
                 $cashInhandclosing = $cashInhandclosing->balance ;
            }
            else{
                 $cashInhandclosing =  0;
            }
            $aa = BranchDaybookAmount($startDate,$endDate,$branch_id);
            $cheque['CR']=0;
            $cheque['DR']=0;
            $cash_in_hand['DR']=0;
            $cash_in_hand['CR']=0;
            if(array_key_exists('0_CR', $aa))
            {
                $cash_in_hand['CR'] = $aa['0_CR'] ;
            }
            if(array_key_exists('0_DR', $aa))
            {
               $cash_in_hand['DR'] = $aa['0_DR'] ;
            }
            if(array_key_exists('1_CR', $aa))
            {
                $cheque['CR'] = $aa['1_CR'] ;
            }
            if(array_key_exists('1_DR', $aa))
            {
               $cheque['DR'] = $aa['1_DR'] ;
            }
            $bank['CR'] =0;
            $bank['DR'] =0;
            if(array_key_exists('2_CR', $aa) && array_key_exists('1_CR', $aa))
            {
                $bank['CR'] = $aa['1_CR'] + $aa['2_CR'];
            }
            if(array_key_exists('2_DR', $aa) && array_key_exists('1_DR', $aa))
            {
               $bank['DR'] = $aa['1_DR'] + $aa['2_DR'];
            }
            $SamraddhBank = SamraddhBank::with('bankAccount')->get();
         }
           return \Response::json(['view' => view('templates.admin.report.filtered_sheet_day_book' ,['cashInhand' => $cash_in_hand,'cashInhandOpening'=>$cashInhandOpening,'cashInhandclosing'=>$cashInhandclosing,'cheque'=>$cheque,'samraddh'=>$SamraddhBank,'end_date' => $endDate,'branch_id' => $branch_id,'bank'=>$bank,'start_date'=>$startDate])->render(),'msg_type'=>'success']);
    }
    }
    public function transaction_list(Request $request)
    {
        $records = '';
        if($request->page == 0)
        {
           $i = 1;
        }
        else{
             $i = $request->index;
        }
        if($request['start_date'] !=''){
            $startDate=date("Y-m-d", strtotime(convertDate($request->start_date)));
        }
        else{
            $startDate='';
        }
        if($request['end_date'] !=''){
             $endDate=date("Y-m-d ", strtotime(convertDate($request->end_date)));
        }
        else {
            $endDate='';
        }
        if($request['branch']!='') {
            $branch_id=$request->branch;
        }
        else {
            $branch_id='';
        }
        $rowReturn = array();
        $offset = $request->limit * $request->page;
        $limit = $request->limit;
        // $offset = $offset - 1;
        $data =  BranchDaybook::with(['member_investment'=>function($q){
            $q->select('id','account_number','plan_id','member_id','associate_id')->with('member')->with('ssb')->with('plan')->with('associateMember');
        }])
          ->when('type'==5,function($q){
            return $q->with(['member_loan'=>function($q){
            $q->select('id','applicant_id','loan_type')->with('loanMember');
        }]);
        })->when('type'==5,function($q){
           $q->with(['group_member_loan'=>function($q){
            $q->select('id','applicant_id','loan_type','member_loan_id','member_id')->with('loanMember');
        }]);
        })
        ->with(['demand_advice'=>function($q){
            $q->select('id','investment_id','employee_name')->with(['investment'=>function($q){
                $q->select('id','account_number','plan_id','member_id','account_number')->with('plan')
                ->with('member');
            }])->with(['expenses'=>function($qa){
                    $qa->select('id')->with('advices');
                }]);
        }])
        ->with('member')->with('receivedvoucherbytype_id')->with('receivedvoucherbytype_transaction_id')
        ->with(['SavingAccountTranscation'=>function($q){
            $q->with(['savingAc'=>function($q){
                $q->with('ssbMember')->with('associate');
            }]);
        }])
        ->when('type'==7,function($q){
            return $q->with(['SamraddhBank'=>function($q){
            $q->with('bankAccount');
        }]);
        })
        ->when('type'==15,function($q){
            return $q->with(['VoucherSamraddhBank'=>function($q){
            $q->with('bankAccount');
        }]);
        })
        ->when('type'==15,function($q){
            return $q->with(['VoucherSamraddhBankbank_ac_id'=>function($q){
            $q->with('bankAccount');
        }]);
        })->when('type'==1,function($q){
            return $q ->with('memberMemberId');
        })->when('type'==2,function($q){
            return $q ->with('memberMemberId');
        })->with('accountHead')->with(['loan_from_bank'=>function($q){
            $q->with('loan_emi');
        }])->with('company_bound')->with(['bill_expense'=>function($q){
            $q->with('head')->with('subb_head')->with('subb_head2');
        }])
        ->with('BillExpense')
        ->with(['EmployeeSalaryBytype_id'=>function($q){
            $q->with('salary_employee');
        }])
        ->with(['RentPayment'=>function($q){
            $q->with('rentLib');
        }])
        ->with(['RentLiabilityLedger'=>function($q){
            $q->with('rentLib');
        }])
        ->with(['EmployeeSalary'=>function($q){
            $q->with('salary_employee');
        }])->with('associateMember')->with('SavingAccountTranscationtype_trans_id')
        ->where('branch_id',$branch_id)->whereBetween('entry_date',[$startDate, $endDate])->where('is_deleted',0)->orderBy('entry_date','ASC')->offset($offset)->limit($limit)->get();
        $balance=0;
        $types = getTransactionTypeCustom();
		$transactionCreatedBy = array();
        $transactionCreatedBy[0] = 'Software';
        $transactionCreatedBy[1] = 'Associate App';
        $transactionCreatedBy[2] = 'E-Passbook App';
        $payment_mode = array();
        // for ($i=0; $i <= 6; $i++) {
        //     // code...
        // }
        $payment_mode[0]= 'CASH';
        $payment_mode[1] = 'CHEQUE';
        $payment_mode[2] = 'ONLINE TRANSFER';
        $payment_mode[3] = 'SSB';
        $payment_mode[4] = 'AUTO TRANSFER';
        $payment_mode[5] = 'Loan';
        $payment_mode[6] = 'JV';
        $payment_mode[8] = 'SSb Debit Cron';
        foreach ($data as $index => $value) {
             if($value->type != 21)
            {
               if(array_key_exists($value->type.'_'.$value->sub_type,$types))
                {
                    $type = $types[$value->type.'_'.$value->sub_type];
                }
            }
            if($value->type == 21 && $value->sub_type == '')
            {
                 $record = ReceivedVoucher::where('id',$value->type_id)->first();
                 if($record )
                 {
                     $type= $record->particular;
                 }
                else{
                    $type="N/A";
                }
            }
             if($value->type == 22 || $value->type == 23)
            {
                 if($value->sub_type == 222)
                {
                    $type = $value->description;
                }
            }
            // Member Name, Member Account and Member Id
             //$memberData = getMemberInvestment($value->type_id);
            //$loanData = getLoanDetail($value->type_id);
            //$groupLoanData = getGroupLoanDetail($value->type_id);
            //$DemandAdviceData = \App\Models\DemandAdvice::where('id',$value->type_id)->first();
            //$freshExpenseData = \App\Models\DemandAdviceExpense::where('id',$value->type_id)->first();
            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name ='N/A';
             $a_name ='N/A';
            // if($value->payment_mode == 6)
            // {
            //     $rentPaymentDetail=\App\Models\RentLiabilityLedger::with('rentLib')->where('id',$value->type_transaction_id)->first();
            //     $salaryDetail=\App\Models\EmployeeSalary::with('salary_employee')->where('id',$value->type_transaction_id)->first();
            // }
            // else{
            //     $rentPaymentDetail=\App\Models\RentPayment::with('rentLib')->where('id',$value->type_id)->first();
            //     $salaryDetail=\App\Models\EmployeeSalary::with('salary_employee')->where('id',$value->type_id)->first();
            // }
             if($value->payment_mode == 6)
            {
                $rentPaymentDetail=$value['RentLiabilityLedger'];
                $salaryDetail=$value['EmployeeSalary'];
            }
            else{
                $rentPaymentDetail=$value['RentPayment'];
                $salaryDetail=$value['EmployeeSalaryBytype_id'];
            }
            if($value->type==14)
            {
              if($value->sub_type == 144)
              {
                 $voucherDetail=$value['receivedvoucherbytype_transaction_id'];
              }
              else{
                 $voucherDetail=$value['receivedvoucherbytype_id'];
              }
            }
            if(($value->type == 1 || $value->type == 2))
            {
                // $m_id = getMemberCustom($value->type_id);
                $memberName =$value['member']->first_name. ' '.$value['member']->last_name ;
                $memberId =$value['member']->member_id;
                $memberAccount = $value['member']->member_id; ;
            }
            elseif($value->type ==3)
            {
                if($value->sub_type ==38)
                {
                    $record = Daybook::where('id',$value->type_transaction_id)->first();
                    $memberAccount = $record->account_no;
                    // $planDetail = getInvestmentAccount($record->member_id,$record->account_no);
                    $plan_name =$value['member_investment']['plan']->name;
                    // $m_id = $value['memberMemberTyprTransactionId']->member_id;
                    $memberName =$value['member_investment']['member']->first_name. ' '.$value['member_investment']['member']->last_name ;
                    $memberId =$value['member_investment']['member']->member_id;
                }else{
                    if( $value['member_investment'])
                    {
                        $memberAccount = $value['member_investment']->account_number;
                    }
                }
              $plan_name =$value['member_investment']['plan']->name;
              $memberName =$value['member_investment']['member']->first_name. ' '.$value['member_investment']['member']->last_name ;
               $memberId =$value['member_investment']->account_number;
               $memberAccount = $value['member_investment']->account_number;
               $a_name =   $value['member_investment']['associateMember']->first_name. ' '.$value['member_investment']['associateMember']->last_name ;
            }
            elseif($value->type ==4 )
            {
                if(isset($value['SavingAccountTranscation'])){
                    $memberAccount = $value['SavingAccountTranscation']->account_no;
                    if(isset($value['memberMemberId']))
                    {
                        $memberName =$value['memberMemberId']->first_name. ' '.$value['memberMemberId']->last_name ;
                    }
                   // $memberId =$value['member_investment']['member']->member_id;
                    $plan_name ='Saving Account';
                }
                if($value->sub_type == 42)
                {
                    $memberAccount = $value['SavingAccountTranscationtype_trans_id']->account_no;
                    if(isset($value['memberMemberId']))
                    {
                        $memberName =$value['memberMemberId']->first_name. ' '.$value['memberMemberId']->last_name ;
                    }
                   // $memberId =$value['member_investment']['member']->member_id;
                    $plan_name ='Saving Account';
                }
            }
            elseif($value->type ==5)
            {
                if($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57 ||  $value->sub_type == 525  || $value->sub_type == 511  || $value->sub_type == 513  || $value->sub_type == 515 ||  $value->sub_type == 528 ||  $value->sub_type == 529 ||  $value->sub_type == 530 ||  $value->sub_type == 531 || $value->sub_type ==527 || $value->sub_type == 532)
                 {
                     if($value['member_loan'])
                     {
                        //$m_id = getMemberCustom($value['member_loan']->applicant_id);
                        $memberName =$value['member_loan']['loanMember']->first_name. ' '.$value['member_loan']['loanMember']->last_name ;
                        $memberAccount =$value['member_loan']->account_number ;
                        if($value['member_loan']->loan_type==1)
                         {
                            $plan_name ='Personal Loan(PL)';
                         }
                         if($value['member_loan']->loan_type==2)
                         {
                            $plan_name ='Staff Loan(SL)';
                         }
                         if($value['member_loan']->loan_type==4)
                         {
                            $plan_name ='Loan against Investment plan(DL)';
                         }
                     }
                 }
                 elseif($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58  || $value->sub_type == 512  || $value->sub_type == 514  || $value->sub_type == 516 || $value->sub_type==518 ){
                     if($value['group_member_loan'])
                     {
                        $memberName =$value['group_member_loan']['loanMember']->first_name. ' '.$value['group_member_loan']['loanMember']->last_name ;
                     }
                }
             }
            elseif($value->type ==6)
            {
                if(isset($salaryDetail['ledger_employee']->employee_name))
                {
                    $memberName = $salaryDetail['ledger_employee']->employee_name;
                    $memberAccount = $salaryDetail['ledger_employee']->employee_code;
                }
                elseif(isset($salaryDetail['salary_employee']->employee_name))
                {
                    $memberName = $salaryDetail['salary_employee']->employee_name;
                    $memberAccount = $salaryDetail['salary_employee']->employee_code;
                }
            }
            elseif($value->type ==7)
            {
               //$memberName = SamraddhBank::where('id',$value->transction_bank_to)->first();
               $memberName =  $value['SamraddhBank']->bank_name;
               //$memberAccount = getSamraddhBankAccountId($value->transction_bank_to);
               $memberAccount = $value['SamraddhBank']['bankAccount']->account_no;
            }
            elseif($value->type ==9)
            {
                //$m_id = getMemberCustom($value->type_id);
                $memberName =$value['memberMemberId']->first_name. ' '.$value['memberMemberId']->last_name ;
                $memberId =$value['memberMemberId']->member_id;
            }
            elseif($value->type ==10)
            {
              if($rentPaymentDetail['rentLib'])
              {
                if($rentPaymentDetail)
                {
                   $memberName = $rentPaymentDetail['rentLib']->owner_name;
                }
              }
                $memberAccount = 'N/A';
            }
             elseif($value->type ==11)
            {
              if($value['demand_advice'])
              {
                $memberName = $value['demand_advice']->employee_name;
                $memberAccount = 'N/A';
                }
            }
            elseif($value->type ==12)
            {
              if(isset($salaryDetail['salary_employee']->employee_name))
              {
                 $memberName = $salaryDetail['salary_employee']->employee_name;
                  $memberAccount = $salaryDetail['salary_employee']->employee_code;
              }
            }
            elseif($value->type ==13)
            {
                if($value->sub_type == 131  || $value->sub_type == 132 )
                {
                  if(isset($value['demand_advice']['expenses'] ))
                  {
                    // $memberAccount = $value['demand_advice']['expenses']['advices']->voucher_number;
                    $memberAccount ='N/A';
                    $memberId = 'N/A';//$value['demand_advice']['expenses']->bill_number;
                  }
                }
                if($value->sub_type == 133 )
                {
                 //$detail = getMemberInvestment($DemandAdviceData->investment_id);
                 if(ISSET($value['demand_advice']['investment']['member']))
                 {
                   //$m_id = getMemberCustom($detail->member_id);
                   $memberName =$value['demand_advice']['investment']['member']->first_name. ' '.$value['demand_advice']['investment']['member']->last_name ;
                   $memberAccount = $value['demand_advice']['investment']->account_number;
                 }
                 // $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                  $plan_name =$value['demand_advice']['investment']['plan']->name;
                }
                if($value->sub_type == 134 )
                {
                 // $detail = getMemberInvestment($DemandAdviceData->investment_id);
                  if(ISSET($value['demand_advice']['investment']->account_number))
                  {
                    $memberAccount = $value['demand_advice']['investment']->account_number;
                  }
                   if(ISSET($value['demand_advice']['investment']['member']))
                  {
                    //$m_id = getMemberCustom($value['demand_advice']['investment']->member_id);
                    $memberName =$value['demand_advice']['investment']['member']->first_name. ' '.$value['demand_advice']['investment']['member']->last_name ;
                  }
                  //$plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                   $plan_name =$value['member_investment']['plan']->name;
                }
                if($value->sub_type == 135 )
                {
                  //$detail = getMemberInvestment($DemandAdviceData->investment_id);
                  if(ISSET($value['demand_advice']['investment']->account_number))
                  {
                    $memberAccount = $value['demand_advice']['investment']->account_number;
                  }
                   if(ISSET($value['demand_advice']['investment']['member']->member_id))
                  {
                    //$m_id = getMemberCustom($value['demand_advice']['investment']->member_id);
                    $memberName =$value['demand_advice']['investment']['member']->first_name. ' '.$value['demand_advice']['investment']['member']->last_name ;
                  }
                  //$plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                   $plan_name =$value['member_investment']['plan']->name;
                }
                if($value->sub_type == 136 )
                {
                  //$detail = getMemberInvestment($DemandAdviceData->investment_id);
                  if(ISSET($value['demand_advice']['investment']->account_number))
                  {
                    $memberAccount = $value['demand_advice']['investment']->account_number;
                  }
                   if(ISSET($value['demand_advice']['investment']['member']))
                  {
                    //$m_id = getMemberCustom($value['demand_advice']['investment']->member_id);
                    $memberName =$value['demand_advice']['investment']['member']->first_name. ' '.$value['demand_advice']['investment']['member']->last_name ;
                  }
                  //$plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                   $plan_name =$value['member_investment']['plan']->name;
                }
                if($value->sub_type == 137 )
                {
                  //$detail = getMemberInvestment($DemandAdviceData->investment_id);
                  if(ISSET($value['demand_advice']['investment']->account_number))
                  {
                    $memberAccount = $value['demand_advice']['investment']->account_number;
                  }
                   if(ISSET($value['demand_advice']['investment']['member']))
                  {
                    //$m_id = getMemberCustom($detail->member_id);
                    $memberName =$value['demand_advice']['investment']['member']->first_name. ' '.$value['demand_advice']['investment']['member']->last_name ;
                  }
                  // $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                   $plan_name =$value['demand_advice']['investment']['plan']->name;
                  //$memberName = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                }
                if($value->sub_type == 142 )
                {
                  if($freshExpenseData)
                  {
                    $memberName = $value['demand_advice']['expenses']->party_name;
                    $memberAccount ='N/A';
                    $memberId = 'N/A';;
                  }
                }
            }
             elseif($value->type ==14)
            {
              if($value != '')
              {
                if($voucherDetail->type == 1 || $voucherDetail->type == 2 || $voucherDetail->type ==5)
                {
                    if($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2)
                  {
                       // $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                       // $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                       $memberAccount = $value['VoucherSamraddhBank']['bankAccount']->account_no;
                        if(isset($memberAccount))
                        {
                            $memberAccount = $memberAccount.'('.$value['VoucherSamraddhBank']->bank_name.')';
                        }
                  }
                }
                if($voucherDetail->type == 3 )
                {
                  $memberId =  getEmployeeData($voucherDetail->employee_id)->employee_code;
                  if($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2)
                  {
                   $memberAccount = $value['VoucherSamraddhBank']['bankAccount']->account_no;
                        if(isset($memberAccount))
                        {
                            $memberAccount = $memberAccount.'('.$value['VoucherSamraddhBank']->bank_name.')';
                        }
                  }
                }
                if($voucherDetail->type == 4 )
                {
                  $memberAccount =  $value['VoucherSamraddhBankbank_ac_id']['bankAccount']->account_no;
                }
              }
            }
             elseif($value->type ==15 || $value->type ==16)
            {
              $memberName =$value['accountHead']->sub_head;
              $memberAccount ="N/A";
            }
            elseif($value->type ==17)
            {
                if($value->sub_type == 171)
                {
                    //$detail =\App\Models\LoanFromBank::where('daybook_ref_id',$value->daybook_ref_id   )->first();
                    if($value['loan_from_bank'])
                    {
                       $memberAccount = $value['loan_from_bank']->loan_account_number;
                       $memberName =$value['loan_from_bank']->bank_name;
                    }
                }
                else if($value->sub_type==172)
                {
                    $detail =\App\Models\LoanEmi::where('id',$value->type_transaction_id   )->first();
                    if($detail)
                    {
                       $memberAccount = \App\Models\LoanFromBank::where('id',$detail->loan_bank_account   )->first();
                       $memberAccount =$memberAccount->loan_account_number;
                       $memberName =$detail->loan_bank_name;
                    }
                }
            }
            elseif($value->type ==21)
            {
                $memberAccount =$value->memberMemberId->member_id;
                $memberName =$value->memberMemberId->first_name.' '. $value->memberMemberId->last_name;
            }
            elseif($value->type ==29)
            {
                $memberAccount =$value->debitcard->account_no;
                $memberName =$value->debitcard->ssbMember->first_name.' '. $value->debitcard->ssbMember->last_name;
                $plan_name = 'Saving Account';
            }
             elseif($value->type ==30)
            {
                if($value->sub_type == 301)
                {
                    //$detail =\App\Models\CompanyBound::where('daybook_ref_id',$value->daybook_ref_id   )->first();
                    if($value['company_bound'])
                    {
                       $memberAccount = $value['company_bound']->fd_no;
                       $memberName =$value['company_bound']->bank_name;
                    }
                }
                else if($value->sub_type==302)
                {
                    $detail =\App\Models\CompanyBoundTransaction::where('daybook_ref_id',$value->daybook_ref_id   )->first();
                    if($detail)
                    {
                       $record = \App\Models\CompanyBound::where('id',$detail->bound_id   )->first();
                       $memberAccount =$record->fd_no;
                       $memberName =$record->bank_name;
                    }
                }
            }
            if($value->sub_type==43 || $value->sub_type==41)
            {
                $associate_code = $value['SavingAccountTranscation']['savingAc']['ssbMember'];
                if(isset($associate_code->associate_id))
                {
                    // dd($value['SavingAccountTranscation']['savingAc']['associate']);
                    $associate_name =$value['SavingAccountTranscation']['savingAc']['associate']->first_name;
                }
            }
            if($value->type==13  || $value->sub_type ==35 || $value->sub_type ==37 || $value->sub_type ==33 || $value->sub_type==34 || $value->type == 21)
            {
                if(isset($value['memberMemberId']->associate_code)){
                    $associate_code = $value['memberMemberId']->associate_code;
                    $associate_name = Member::where('associate_no',$associate_code)->first();
                }
            }
             if($value->type == 20)
            {
                if(isset($value['bill_expense']->bill_no))
                {
                    $memberAccount='Bill No.'.$value['bill_expense']->bill_no;
                    $memberName = $value['BillExpense']->party_name;
                    $mainHead =  $value['bill_expense']['head']->sub_head;
                }
                $name = \App\Models\BillExpense::where('daybook_refid',$value->daybook_ref_id)->first();
                if(isset($name->party_name))
                {
                         $memberName = $value['BillExpense']->party_name;
                }
                if(isset($value['bill_expense'])){
                    $mainHead =  $value['bill_expense']['head']->sub_head;
                    }else{
                    $mainHead='N/A';
                }
                $record = \App\Models\Expense::where('bill_no',$value->type_id)->first();
            if(isset($record)){
                if($record->account_head_id && $record->sub_head1 && $record->sub_head2)
                {
                   if(isset($value['bill_expense'])){
                   $subHead =  $value['bill_expense']['subb_head']->sub_head;
                   $subHead2 =  $value['bill_expense']['subb_head2']->sub_head;
                    }else{
                    $subHead=' ';
                    $subHead2=' ';
                    }
                   $plan_name = 'INDIRECT EXPENSE /'.$mainHead.'/'.$subHead.'/'.$subHead2;
                }
                elseif($record->account_head_id && $record->sub_head1 )
                {
                    if(isset($value['bill_expense'])){
                        $subHead =  $value['bill_expense']['subb_head']->sub_head;
                    }else{
                    $subHead=' ';
                    }
                   $plan_name = 'INDIRECT EXPENSE /'.$mainHead.'/'.$subHead;
                }
                elseif($record->account_head_id)
                {
                    $plan_name = 'INDIRECT EXPENSE /'.$mainHead;
                }
               }else{
                    $plan_name = ' ';
               }
            }
            // Associate
            if($value->sub_type==43 || $value->sub_type==41 || $value->type == 13 || $value->sub_type ==35  || $value->sub_type ==37 || $value->sub_type ==33 || $value->sub_type==34 || $value->type == 21)
            {
                //dd($value['SavingAccountTranscation']['savingAc']['associate']);
                if(isset($value['SavingAccountTranscation']['savingAc']['associate']->first_name))
                {
                    $a_name =$value['SavingAccountTranscation']['savingAc']['associate']->first_name;
                }
            }
            else{
                    if($value->associate_id)
                    {
                        // $m_id = getMemberCustom($value->associate_id);
                        if(isset($value['associateMember']->first_name))
                        {
                             $a_name =$value['associateMember']->first_name.' '.$value['associateMember']->last_name.'('.$value['associateMember']->associate_no.')';
                        }
                    }
                }
            // Payment Type
            $cr_amount = 0;
            $dr_amnt = 0;
            if($value->payment_type == 'CR')
            {
                $cr_amount = number_format((float)$value->amount, 2, '.', '');
            }
            if($value->payment_type == 'DR'){
               $dr_amnt =  number_format((float)$value->amount, 2, '.', '');
            }
            // Balance
            if($value->payment_mode == 0 && $value->sub_type != 30 )
            {
                $balance = number_format((float)$balance, 2, '.', '');
            }
            // Ref Number
            if($value->payment_mode == 0 )
            {
                $ref_no = 'N/A';
            }
            elseif($value->payment_mode == 1)
            {
                 $ref_no = $value->cheque_no;
            }
            elseif($value->payment_mode == 2)
            {
                 $ref_no = $value->transction_no;
            }
            elseif($value->payment_mode == 3)
             {
                $ref_no = $value->v_no;
             }
            elseif($value->payment_mode == 6)
             {
                $ref_no = $value->jv_unique_id;
            }
            else{
                $ref_no = 'N/A';
            }
            // Payment Mode
            if(strpos($value->description_dr, 'Eli Amount'))
            {
                $pay_mode = 'ELI';
            }
            else
            {
              $pay_mode=  $payment_mode[$value->payment_mode];
            }
            if($value->entry_date){
                $date =date("d/m/Y", strtotime(convertDate($value->entry_date)));
            }
            else
            {
                $date = 'N/A';
            }
            // tag
            $tag='';
            if($value->type = 3)
            {
                if ($value->sub_type == 31) {
                    $tag='N';
                }
                if ($value->sub_type == 32) {
                       $tag='R';
                }
            }
            if($value->type == 4)
            {
                if($value->sub_type == 41)
                {
                  $tag='N';
                }
                if ($value->sub_type == 42) {
                      $tag='R';
                }
                if ($value->sub_type == 43) {
                        $tag='W';
                }
            }
            if($value->type == 5)
            {
                    if($value->sub_type == 51 || $value->sub_type == 54)
                {
                    $tag='LD';
                }
                if ($value->sub_type == 52 || $value->sub_type == 55) {
                    $tag='L';
                }
            }
            if($value->type == 7)
            {
                $tag='B';
            }
            if($value->type ==13)
            {
                if($value->sub_type == 131)
                {
                    $tag='E';
                }
                if ($value->sub_type == 133 || $value->sub_type == 134 || $value->sub_type == 135 || $value->sub_type == 136 || $value->sub_type == 137) {
                    $tag='M';
                }
            }
             if($value->payment_type == 'CR')
                {
                  if($value->payment_mode == 0)
                  {
                      $balance=$balance+$value->amount;
                  }
                }
                if($value->payment_type == 'DR')
                {
                  if($value->payment_mode == 0)
                  {
                      $balance=$balance-$value->amount;
                  }
                }
            $records = "<tr>
                        <td>".$i."</td>
                        <td>".$date."</td>
                        <td>".$value->id."</td>
						<td>".$transactionCreatedBy[($value->is_app)?$value->is_app:0] . "</td> 
                        <td>".$memberAccount."</td>
                        <td>".$plan_name."</td>
                        <td>".$memberName."</td>
                        <td>".$a_name."</td>
                        <td>".$type."</td>
                        <td>".$value->description_cr."</td>
                        <td>".$value->description_dr."</td>
                        <td>". $cr_amount."</td>
                        <td>".$dr_amnt."</td>
                        <td>".$balance."</td>
                        <td>".$ref_no."</td>
                        <td>".$pay_mode."</td>
                        <td>".$tag."</td>
                    </tr>";
                    $rowReturn[] = $records;
                   $i =  $i+1;
        }
        $sNo =array($i) ;
          return response()->json(['data'=>$rowReturn,'sno'=>$sNo,'msg'=>'success']);
    }
}