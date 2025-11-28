<?php 
namespace App\Http\Controllers\Branch; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator; 
use App\Models\Branch; 
use App\Models\SavingAccount; 
use App\Models\Transcation;  
use App\Models\Memberinvestments;
use App\Models\Member; 
use App\Models\Plans;
use App\Models\MemberTransaction ;

use Yajra\DataTables\DataTables;
use Carbon\Carbon;

use Session;
use Image;
use Redirect;
use URL;
use DB;
/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class TransactionController  extends Controller
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
     * All Investment Listing.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function index($id)
    {
        
		if(!in_array('View Member Transactions', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		
		$data['title']='Transcation | Listing'; 
        $data['memberDetail'] = Member::where('id',$id)->first(['id','member_id','first_name','last_name']);
        
        return view('templates.branch.transcation.index', $data);
    }


     /**
     * Fetch accounts listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function transactionsListing(Request $request)
    { 

          if ($request->ajax()) { 
            $arrFormData = array();  
         //   print_r($_POST);die;
        if(!empty($_POST['searchform']))
        {
            foreach($_POST['searchform'] as $frm_data)
            {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        }
        $id=$arrFormData['member_id'];
      //  echo $arrFormData['branch_id'];die;

            $data = MemberTransaction::with(['memberTransaction','memberTransactionBranch'])->where('member_id',$id);
            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            { 
                if($arrFormData['start_date'] !=''){
                    $startDate=date("Y-m-d", strtotime(($arrFormData['start_date'])));

                    if($arrFormData['end_date'] !=''){
                    $endDate=date("Y-m-d", strtotime(($arrFormData['end_date'])));
                    }
                    else
                    {
                        $endDate='';
                    }
                    $data=$data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]); 
                }
            }
            $data=$data->orderBy('created_at', 'DESC')->get();
           //dd($data);
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('date', function($row){
                $date = date("d/m/Y", strtotime($row->entry_date));
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('member', function($row){
                if($row->memberTransaction)
                {
                    $member = $row->memberTransaction->first_name.' '.$row->memberTransaction->last_name;
                    return $member;
                }
               
            })
            ->rawColumns(['member'])
            ->addColumn('member_id', function($row){
                if($row->memberTransaction)
                {
                $member_id = $row->memberTransaction->member_id;
                return $member_id;
                }
            })
            ->rawColumns(['member_id'])
             ->addColumn('branch_name', function($row){
               
                if( $row->memberTransactionBranch)
                 {
                    $branch_code = $row->memberTransactionBranch->name;
                    return $branch_code; 
                 }
                 else{
                     return 'N/A';
                 } 
            })
            ->rawColumns(['branch_name'])
             ->addColumn('branch_code', function($row){ 
                if( $row->memberTransactionBranch)
                 {
                    $branch_code = $row->memberTransactionBranch->branch_code;
                    return $branch_code; 
                 }
                 else{
                     return 'N/A';
                 } 
            })
            ->rawColumns(['branch_code'])
             ->addColumn('sector', function($row){
                 if( $row->memberTransactionBranch)
                 {
                    $sector = $row->memberTransactionBranch->sector;
                    return $sector; 
                 }
                 else{
                     return 'N/A';
                 }
            })
            ->rawColumns(['sector'])
             ->addColumn('regan', function($row){
                 if($row->memberTransactionBranch)
                 {
                    $regan = $row->memberTransactionBranch->regan;
                    return $regan; 
                 }
                 else{
                     return 'N/A';
                 }
               
            })
            ->rawColumns(['regan'])
             ->addColumn('zone', function($row){
                 if($row->memberTransactionBranch)
                 {
                    $zone = $row->memberTransactionBranch->zone;
                    return $zone; 
                 }
                 else{
                     return 'N/A';
                 }
                
            })
            ->rawColumns(['zone'])
            ->addColumn('tran_type', function($row){
                $tran_type = '';
                
                if($row->type==1)

                {   

                  if($row->sub_type == 11)

                  {

                    $tran_type = "Member Register(MI Charge)";  

                  }

                  if($row->sub_type == 12)

                  {

                    $tran_type = "Member Register(STN Charge)";   

                  }

                     if($row->sub_type==13)
                    {
                        $tran_type = "Member  JV Entry ";
                    }
                    if($row->sub_type==14)
                    {
                        $tran_type = "Member TDS on Interest";
                    }

                } 

                if($row->type==2)

                {   

                    if($row->sub_type == 21)

                    {

                        $tran_type = "Associate Commission";  

                    }

                    if($row->sub_type==22)

                    {

                        $tran_type = "Associate  JV Commission ";
                    }

                    if($row->sub_type==23)
                    {

                        $tran_type = "Associate  JV Fuel Charge ";

                    }

                } 

                if($row->type==3)

                {   

                  if($row->sub_type == 30)

                  {

                    $tran_type = "R-Investment Register";  

                  }

                  if($row->sub_type == 31)

                  {

                    $tran_type = "Account opening";  

                  }

                  if($row->sub_type == 32)

                  {

                    $tran_type = "Renew";  

                  }

                  if($row->sub_type == 33)

                  {

                    $tran_type = "Passbook Print";  

                  }

                 if ($row->sub_type == 38)
                 {
                    $tran_type = 'JV Entry';
                 }
                 if ($row->sub_type == 39) 
                 {
                    $tran_type = 'JV Stationary Charge';
                 }
                 if ($row->sub_type == 311) 
                 {
                    $tran_type = 'JV Passbook Print';
                 }
                 if ($row->sub_type == 312) 
                 {
                    $tran_type = 'JV Certificate Print';
                 }

                  $tran_type = "Investment"; 

                } 

                 if($row->type==4)

                {   

                  if($row->sub_type == 41)

                  {

                    $tran_type = "Account opening";  

                  }

                  if($row->sub_type == 42)

                  {

                    $tran_type = "Deposit";  

                  }

                  if($row->sub_type == 43)

                  {

                    $tran_type = "Withdraw";  

                  }

                  if($row->sub_type == 44)

                  {

                    $tran_type = "Passbook Print";  

                  }

                  if($row->sub_type == 45)

                  {

                    $tran_type = "Commission";  

                  }

                  if($row->sub_type == 46)

                  {

                    $tran_type = "Fuel Charge";  

                  }
                 if ($row->sub_type == 412) {
                          $tran_type = 'SSB  JV Entry';
                    }
                   
                 if ($row->sub_type == 413) {
                          $tran_type = 'SSB JV Passbook Print';
                    }
                 if ($row->sub_type == 414) {
                          $tran_type = 'SSB JV Certificate Print';
                    }

                  $tran_type = "Saving Account"; 

                } 

                if($row->type==5)

                {   

                  if($row->sub_type == 51 ||$row->sub_type == 52 || $row->sub_type == 53  || $row->sub_type == 57)

                  {

                    $tran_type = "Loan";  

                  }

                  if($row->sub_type == 54 || $row->sub_type == 55 || $row->sub_type == 56 || $row->sub_type == 58)

                  {

                    $tran_type = "Group Loan";  

                  }
                  if ($row->sub_type == 511) {
                        $tran_type = 'Loan JV Loan';
                  }
                    if ($row->sub_type == 512) {
                        $tran_type = 'Loan JV  Group Loan';
                    }
                    if ($row->sub_type == 513) {
                        $tran_type = 'Loan JV Loan Panelty';
                    }
                    if ($row->sub_type == 514) {
                        $tran_type = 'Loan JV Group Loan Panelty';
                    }
                    if ($row->sub_type == 515) {
                        $tran_type = 'Loan JV Loan Emi';
                    }
                    if ($row->sub_type == 516) {
                        $tran_type = 'Loan JV Group Loan Emi';
                    }

                } 

                if($row->type==6)

                {   

                  if($row->sub_type == 61)

                  {

                    $tran_type = "Employee Salary";  

                  }
                  if($row->sub_type == 62)
                    {
                        $tran_type = "Employee JV Salary";
                    }

                } 

                if($row->type==7)

                {   

                  if($row->sub_type == 70)

                  {

                    $tran_type = "Branch Cash";  

                  }

                  if($row->sub_type == 71)

                  {

                    $tran_type = "Branch Cheque";  

                  }

                  if($row->sub_type == 72)

                  {

                    $tran_type = "Branch Online";  

                  }

                  if($row->sub_type == 73)

                  {

                    $tran_type = "Branch SSB";  

                  }

                } 

                if($row->type==8)

                {   

                  if($row->sub_type == 80)

                  {

                    $tran_type = "Bank Cash";  

                  }

                  if($row->sub_type == 81)

                  {

                    $tran_type = "Bank Cheque";  

                  }

                  if($row->sub_type == 82)

                  {

                    $tran_type = "Bank Online";  

                  }

                  if($row->sub_type == 83)

                  {

                    $tran_type = "Bank SSB";  

                  }

                } 

                if($row->type==9)

                {   

                  if($row->sub_type == 90)

                  {

                    $tran_type = "Commission TDS";  

                  }

                } 



                if($row->type == 10)

                {

                    if($row->sub_type == 101)

                    {

                        $tran_type = "Rent - Ledger";

                    }

                    elseif ($row->sub_type == 102) {

                        $tran_type = 'Rent - Payment';

                    }

                    elseif ($row->sub_type == 103) {

                          $tran_type = 'Rent - Security';

                    }

                    elseif ($row->sub_type == 104) {

                          $tran_type = 'Rent - Advance';

                    }

                    elseif ($row->sub_type == 105) {
                          $tran_type = 'Rent - JV Ledger';
                    }
                    elseif ($row->sub_type == 106) {
                          $tran_type = 'Rent - JV Security';
                    }

                }



                if($row->type == 11)

                {

                    $tran_type ="Demand";

                }



                 if($row->type ==12)

                {

                    if($row->sub_type == 121)

                    {

                        $tran_type = "Salary - Ledger";

                    }

                    elseif ($row->sub_type == 122) {

                        $tran_type = 'Salary - Transfer';

                    }

                    elseif ($row->sub_type == 123) {

                          $tran_type = 'Salary - Advance';

                    }

                }



                if($row->type ==13)

                {

                    if($row->sub_type == 131)

                    {

                        $tran_type = "Demand Advice - Fresh Expense";

                    }

                    elseif ($row->sub_type == 132) {

                        $tran_type = 'Demand Advice - Ta Advance';

                    }

                    elseif ($row->sub_type == 133) {

                          $tran_type = 'Demand Advice - Maturity';

                    }

                    elseif ($row->sub_type == 134) {

                          $tran_type = 'Demand Advice - Prematurity';

                    }

                    elseif ($row->sub_type == 135) {

                        $tran_type = 'Demand Advice - Death Help';

                    }

                    elseif ($row->sub_type == 136) {

                          $tran_type = 'Demand Advice - Death Claim';

                    }

                    elseif ($row->sub_type == 137) {

                          $tran_type = 'Demand Advice - EM';

                    }
                     elseif ($row->sub_type == 138) {
                          $tran_type =  'Demand Advice - JV Ta Advance';
                         
                    }

                }



                if($row->type == 14)

                {

                    if($row->sub_type == 141)

                    {

                        $tran_type = "Voucher - Director ";

                    }

                    elseif ($row->sub_type == 142) {

                        $tran_type = 'Voucher  - ShareHolder';

                    }

                    elseif ($row->sub_type == 143) {

                          $tran_type = 'Voucher  - Penal Interest';

                    }

                    elseif ($row->sub_type == 144) {

                          $tran_type = 'Voucher  - Bank';

                    }

                    elseif ($row->sub_type == 145) {

                        $tran_type = 'Voucher  - Eli Loan';

                    }

                }



                if($row->type == 15)

                {

                    if($row->sub_type == 151)

                    {

                        $tran_type = 'Director - Deposit';

                    }



                    elseif($row->sub_type == 152)

                    {

                        $tran_type = 'Director - Withdraw';

                    }

                    elseif($row->sub_type == 153)
                    {
                        $tran_type = 'Director - JV Deposit';
                    }

                }



                if($row->type == 16)

                {

                     if($row->sub_type == 161)

                    {

                        $tran_type = 'ShareHolder - Deposit';

                    }

                     elseif($row->sub_type == 162)

                    {

                        $tran_type = 'ShareHolder - Transfer';

                    }
                    elseif($row->sub_type == 163)
                    {
                        $tran_type = 'ShareHolder - JV Deposit';
                    }

                }

                

                if($row->type == 17)

                {

                     if($row->sub_type == 171)

                    {

                        $tran_type = 'Loan From Bank  - Create Loan';

                    }

                    elseif($row->sub_type == 172)

                    {

                        $tran_type = 'Loan From Bank  - Emi Payment';

                    }
                     elseif($row->sub_type == 173)
                    {
                        $tran_type = 'Loan From Bank  - JV Entry';
                    }

                }
                 if($row->type == 18)
                {
                     if($row->sub_type == 181)
                    {
                        $tran_type = 'Bank Charge  - Create';
                    }
                    
                }
                if($row->type == 19)
                {
                     if($row->sub_type == 191)
                    {
                        $tran_type = 'Assets  - Assets';
                    }
                    elseif($row->sub_type == 192)
                    {
                        $tran_type = 'Assets  - Depreciation';
                    }
                    
                }
                if($row->type == 20)
                {
                     if($row->sub_type == 201)
                    {
                        $tran_type = 'Expense Booking  - Create Expense';
                    }
                    
                    
                }
                 
                if($row->type == 21)

                {

                    
                    $tran_type = 'Stationery Charge';


                }
                   if($row->type == 22)
                {
                     if($row->sub_type == 222)
                    {
                        $tran_type = 'JV To Bank';
                    }
                    
                    
                }

                if($row->type == 23)
                {
                     if($row->sub_type == 232)
                    {
                        $tran_type = 'JV To Branch';
                    }
                    
                    
                }
                return $tran_type;
            })
            ->rawColumns(['tran_type'])
            ->addColumn('tran_account', function($row){
                $account= 'N/A';
                 //dd($row->type);
                if($row->type==3)
              {
                  $account = getInvestmentDetails($row->type_id)->account_number;
                 
                  
              }
              if($row->type==4)
              {
                    $account = getSsbAccountNumber($row->type_id);
                    if($account)
                    {
                    $account = $account->account_no;
                    }
                    else{
                        $account = '';
                    }
              }
              if($row->type==5)
              {
                  if($row->sub_type==54 || $row->sub_type==55 || $row->sub_type==56 || $row->sub_type==58)
                  {
                       $account = getGroupLoanDetail($row->type_id)->account_number;
                  }
                  elseif($row->sub_type==51 || $row->sub_type==52 || $row->sub_type==53 || $row->sub_type==57){
                      $account = getLoanDetail($row->type_id)->account_number;
                  }
              }
              
              return $account;
             
            })
            ->rawColumns(['tran_account'])
            ->addColumn('amount', function($row){
                $amount = $row->amount;
				$amount = number_format((float) $amount, 2, '.', '');
                return $amount;
            })
            ->rawColumns(['amount'])
            ->addColumn('payment_type', function($row){
                $payment_type = 'N/A';
                if($row->payment_type=='DR')
                {
                    $payment_type = 'Debit';
                }
                if($row->payment_type=='CR')
                {
                    $payment_type = 'Credit';
                }
                return $payment_type;
            })
            ->rawColumns(['payment_type'])
            ->addColumn('payment_mode', function($row){
                $payment_type = 'N/A'; 
                if($row->payment_mode==0)
                {
                    $payment_mode = 'Cash';
                }
                if($row->payment_mode==1)
                {
                    $payment_mode = 'Cheque';
                }
                if($row->payment_mode==2)
                {
                    $payment_mode = 'Online Transfer';
                }
                if($row->payment_mode==3)
                {
                    $payment_mode = 'SSBx';
                }
               if($row->payment_mode==6)
                {
                    $payment_mode = ' JV';
                }
               
                return $payment_mode;
            })
            ->rawColumns(['payment_mode']) 
            ->addColumn('detail', function($row){
                $detail = $row->description; 
                 return $detail;
            })
            ->rawColumns(['detail']) 
            
           /* ->addColumn('action', function($row){
                $url = URL::to("branch/member/passbook/cover/".$row->id);                
                $url2 = URL::to("branch/member/passbook/transaction/".$row->id.'/'.$row->plan_code);
                $url3 = URL::to("branch/member/passbook/certificate/".$row->id.'/'.$row->plan_code);
                if($row->plan_code==705 || $row->plan_code==706 || $row->plan_code==712)
                {
                    $btn = '<a class="" href="'.$url3.'" title="Certificate"><i class="fas fa-certificate text-default mr-2"></i></a>';
                }
                else
                {
                   $btn = '<a class="" href="'.$url.'" title="Passbook Cover"><i class="fas fa-book text-default mr-2"></i></a>';  
                }
                
                if($row->plan_code==703)
                {
                   $btn .= '<a class="" href="'.$url2.'" title="Passbook Transaction"><i class="fas fa-print text-default mr-2"></i></a>'; 
                } 

                return $btn;
            })
            ->rawColumns(['action'])
            */
            ->make(true);
        }
    }
    

}
