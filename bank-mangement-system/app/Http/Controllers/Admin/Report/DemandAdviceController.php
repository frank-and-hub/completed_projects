<?php 

namespace App\Http\Controllers\Admin\DemandAdvice; 



use App\Http\Controllers\Admin\CommanController;

use App\Models\MemberIdProof;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validator;

use App\Models\Memberloans;

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

use App\Models\SamraddhBank;

use App\Models\SamraddhCheque;

use App\Models\SamraddhBankClosing;

use App\Models\BranchCash;

use App\Models\SavingAccountTranscation;

use App\Models\SamraddhChequeIssue;

use App\Models\TransactionReferences;

use App\Models\Member;

use App\Models\TdsDeposit;

use App\Models\InvestmentMonthlyYearlyInterestDeposits;

use App\Models\MemberInvestmentInterest;

use App\Models\MemberInvestmentInterestTds;

use App\Models\Form15G;

use App\Models\AllHeadTransaction;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use URL;

use DB;

use Session;

use App\Http\Traits\IsLoanTrait;


/*

    |---------------------------------------------------------------------------

    | Admin Panel -- Demand Advice DemandAdviceController

    |--------------------------------------------------------------------------

    |

    | This controller handles demand advice all functionlity.

*/

class DemandAdviceController extends Controller

{

    /**

     * Create a new controller instance.

     * @return void

     */
        use IsLoanTrait;



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

        $data['title']='Demand Advice Listing';

        return view('templates.admin.demand-advice.demand-advice-listing', $data);

    }



    /**

     * Display the specified resource.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function demandAdviceListing(Request $request)

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



            $data=DemandAdvice::with('expenses','branch');

            

            



            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['date_from'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));

                    if($arrFormData['date_to'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereHas('expenses', function ($query) use ($startDate,$endDate) {

                        $query->whereBetween(\DB::raw('DATE(demand_advices_expenses.created_at)'), [$startDate, $endDate]);              

                    });

                }



                if($arrFormData['filter_branch'] !=''){

                    $branchId=$arrFormData['filter_branch'];

                    $data=$data->where('branch_id','=',$branchId);

                }



                if($arrFormData['advice_type'] !=''){

                    $advice_id=$arrFormData['advice_type'];

                    $data=$data->where('payment_type','=',$advice_id);

                }

            }



            $data = $data->orderby('id','DESC')->get();



            return Datatables::of($data)

            ->addIndexColumn()

            ->addColumn('payment_type', function($row){

                if($row->payment_type == 0){

                    $payment_type = 'Expenses';

                }elseif($row->payment_type == 1){

                    $payment_type = 'Maturity';

                }elseif($row->payment_type == 2){

                    $payment_type = 'Prematurity';

                }elseif($row->payment_type == 3){

                    $payment_type = 'Death Help';

                }else{

                    $payment_type = '';

                }

                return $payment_type;

            })

            ->rawColumns(['payment_type'])

            ->addColumn('sub_payment_type', function($row){

                if($row->sub_payment_type == 0 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'Fresh Expense';

                }elseif($row->sub_payment_type == 1 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'TA advance / Imprest';

                }elseif($row->sub_payment_type == 2 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'Advanced Salary';

                }elseif($row->sub_payment_type == 3 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'Advanced Rent';

                }else{

                    $sub_payment_type = '';

                }

                return $sub_payment_type;

            })

            ->rawColumns(['sub_payment_type'])

            ->addColumn('branch', function($row){

                $branch = $row['branch']->name;

                return $branch;

            })

            ->rawColumns(['branch'])



            ->addColumn('status', function($row){

                if($row->status == 0){

                    $status = 'Pending';

                }else{

                    $status = 'Approved';        

                }

                

                return $status;

            })

            ->rawColumns(['status'])



            ->addColumn('created_at', function($row){

                return date("d/m/Y", strtotime( $row->created_at));

            })

            ->rawColumns(['created_at'])

            

            ->addColumn('action', function($row){

                $vurl = URL::to("admin/demand-advice/view/".$row->id."");

                $url = URL::to("admin/demand-advice/edit-demand-advice/".$row->id."");

                $deleteurl = URL::to("admin/delete-demand-advice/".$row->id."");

                /*$approveurl = URL::to("admin/approve-demand-advice/".$row->id."");*/



                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';



                $btn .= '<a class="dropdown-item" href="'.$vurl.'"><i class="fas fa-eye"></i>View</a>';

                



                if($row->is_mature == 1 && $row->status == 0){
                    $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }

                /*$btn .= '<a class="dropdown-item" href="'.$approveurl.'"><i class="fas fa-thumbs-up"></i>Approve</a>';*/

                $btn .= '<a class="dropdown-item delete-demand-advice" href="'.$deleteurl.'"><i class="fas fa-trash-alt"></i>Delete</a>';



                $btn .= '</div></div></div>';          

                return $btn;

            })

            ->rawColumns(['action'])

            ->make(true);

        }

    }



    /**

     * Display a listing of the account heads.

     *

     * @return \Illuminate\Http\Response

     */

    public function report()

    {

        if(check_my_permission( Auth::user()->id,"84") != "1"){

          return redirect()->route('admin.dashboard');

        }

        $data['title']='Demand Advice Report';

        return view('templates.admin.demand-advice.report', $data);

    }



    /**

     * Display the specified resource.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function reportListing(Request $request)

    { 

        if ($request->ajax() && check_my_permission( Auth::user()->id,"84") == "1") {



            $arrFormData = array();   

            if(!empty($_POST['searchform']))

            {

                foreach($_POST['searchform'] as $frm_data)

                {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];

                }

            }



            $data=DemandAdvice::with('expenses','branch');

            

            if(Auth::user()->branch_id>0){

             $id=Auth::user()->branch_id;

             $data=$data->where('branch_id','=',$id);

            }



            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['date_from'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));

                    if($arrFormData['date_to'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);     

                }



                if(isset($arrFormData['filter_branch']) && $arrFormData['filter_branch'] !=''){

                    $branchId=$arrFormData['filter_branch'];

                    $data=$data->where('branch_id','=',$branchId);

                }



                if($arrFormData['advice_type'] !=''){

                    $advice_id=$arrFormData['advice_type'];

                    $advice_type_id=$arrFormData['expense_type'];



                    if($advice_id == 0 || $advice_id == 1 || $advice_id == 2){

                        if($advice_type_id != ''){

                            $data=$data->where('payment_type','=',$advice_id)->where('sub_payment_type',$advice_type_id);

                        }else{

                            $data=$data->where('payment_type','=',$advice_id);        

                        }

                    }elseif($advice_id == 3){

                        $data=$data->where('payment_type','=',3)->where('death_help_catgeory','=',0); 

                    }elseif($advice_id == 4){

                        $data=$data->where('payment_type','=',3)->where('death_help_catgeory','=',1); 

                    }
                    elseif($advice_id == 5){

                        $data=$data->where('payment_type','=',4); 

                    }

                }



                if($arrFormData['voucher_number'] !=''){

                    $voucher_number=$arrFormData['voucher_number'];

                    $data=$data->where('voucher_number','=',$voucher_number);

                }



                if($arrFormData['status'] !=''){

                    $status=$arrFormData['status'];

                    $data=$data->where('status','=',$status);

                }

            }


            $data1=$data->get();
			$count=$data1->count();
			
            $data=$data->offset($_POST['start'])->limit($_POST['length'])->orderBy('created_at','DESC')->get();	
			
			$totalCount = count($data);
			
			$sno=$_POST['start'];
            $rowReturn = array(); 
            foreach ($data as $row)
            {
                $sno++;
                $val['DT_RowIndex']=$sno;
                if(isset($row['branch']->name))
                {
                  $val['branch_name']=$row['branch']->name; 
                }
                else{
                    $val['branch_name']='N/A';
                }
                 if(isset($row['branch']->branch_code))
                {
                  $val['branch_code']=$row['branch']->branch_code; 
                }
                else{
                    $val['branch_code']='N/A';
                }
                if(isset($row['branch']->sector))
                {
                  $val['sector']=$row['branch']->sector; 
                }
                else{
                    $val['sector']='N/A';
                }
                if(isset($row['branch']->regan))
                {
                  $val['regan']=$row['branch']->regan; 
                }
                else{
                    $val['regan']='N/A';
                }
                if(isset($row['branch']->zone))
                {
                  $val['zone']=$row['branch']->zone; 
                }
                else{
                    $val['zone']='N/A';
                }
                
                
                $member_name='N/A';
                $member_id='N/A';
                 if(isset($row->investment_id))
                {
                    $member_id = getInvestmentDetails($row->investment_id)->member_id;
                    if($member_id)
                    {
                        $member_name = getMemberData($member_id)->first_name.' '.getMemberData($member_id)->last_name;        

                    }
                  
                }
               
                    $val['name']=$member_name; 
                    $nominee_name='N/A';
                    if($member_id)
                    {
                        $nominee_name = getMemberNomineeDetail($member_id);  
                        if(isset($nominee_name->name))  
                        {
                            $nominee_name = $nominee_name->name;
                        }    

                    }
               
                        $val['nominee_name']= $nominee_name;
                  
                $loanDetail =  $this->getData(new Memberloans(),$member_id);
                
              
                $val['is_loan'] = $loanDetail;
                 if(isset($row->investment_id))
                {
                    $associate_id = getInvestmentDetails($row->investment_id)->member_id;
                    $associate_code='N/A';
                    if($associate_id)
                    {
                       $associate_code = getMemberData($associate_id)->associate_code; 
                    }
                    
                  $val['associate_code']=$associate_code; 
                }
                else{
                    $val['associate_code']='N/A';
                }
                if(isset($row->tds_amount))
                {
                  $val['tds_amount']=round($row->tds_amount); 
                }
                else{
                    $val['tds_amount']='N/A';
                }
                if($row->id)
				{
					if($row->payment_mode == 2 )
				{
					$transaction = AllHeadTransaction::where('head_id',92)->where('type_id',$row->id)->first();;

                    if($transaction)
                    {
                      
						      
							 $val['neft_charge'] =round($transaction->amount);
						
						 
					
                }
                    
					 else{
						 $val['neft_charge'] ='N/A';
					 }
				}
				else{
					$val['neft_charge'] ='N/A';	
				}
					
				}
				else{
					$val['neft_charge'] ='N/A';	
				}
				
                

                
                if(isset($row->investment_id))
                {
                  $associate_id = getInvestmentDetails($row->investment_id)->member_id;
                  $associate_code ='N/A';
                  $associate_name='N/A';
                    if($associate_id)
                    {
                        $associate_code = getMemberData($associate_id)->associate_code;

                        $associate_name =Member::where('associate_no',$associate_code)->first();
                        if(isset($associate_name->first_name)  && isset($associate_name->last_name))
                        {
                            $associate_name = $associate_name->first_name.' '.$associate_name->last_name;
                        }
                        elseif(isset($associate_name->first_name) ){
                            $associate_name = $associate_name->first_name;
                        } 
                         else{
                        $associate_name='N/A';
                        }
                    
                    }


                    
                }
                else{
                    $associate_name='N/A';
                }
                  $val['associate_name']=$associate_name;
                
                
                
                $opening_date='N/A';
                if(isset($row->payment_type))
                {
                    
                       if($row->investment_id)
                        {
                         $date = getInvestmentDetails($row->investment_id);
                         if($date)
                         {
                            $opening_date = date("d/m/Y", strtotime( $date->created_at));   
                         }
                        else
                        {
                             $opening_date = 'None';
                        }
                      
                    

                    
                }
                  $val['ac_opening_date']=$opening_date; 
                }
                else{
                    if($row->opening_date)
                    {
                        $opening_date = date("d/m/Y", strtotime( $row->opening_date));
    
                    }
                    else{
                       $opening_date = "N/A";                  
                    }
                    $val['ac_opening_date']=$opening_date; 
                }
                $type='';
                 if($row->payment_type == 0){

                    $type =  'Expenses';

                }elseif($row->payment_type == 1){

                    $type =  'Maturity';

                }elseif($row->payment_type == 2){

                    $type =  'Prematurity';

                }elseif($row->payment_type == 3){

                    if($row->sub_payment_type == '4'){

                        $type =  'Death Help';

                    }elseif($row->sub_payment_type == '5'){

                        $type =  'Death Claim';

                    }

                }
                  elseif($row->payment_type == 4)
                {
                    $type =  "Emergency Maturity";
                }
                $val['advice_type'] = $type;
                 $sub_type ='';
                if($row->sub_payment_type == '0'){

                    $sub_type =  'Fresh Exprense';

                }elseif($row->sub_payment_type == '1'){

                    $sub_type =  'TA Advanced';

                }elseif($row->sub_payment_type == '2'){

                    $sub_type =  'Advanced salary';

                }elseif($row->sub_payment_type == '3'){

                    $sub_type =  'Advanced Rent';

                }elseif($row->sub_payment_type == '4'){

                    $sub_type =  'N/A';

                }elseif($row->sub_payment_type == '5'){

                    $sub_type =  'N/A';

                }else{

                    $sub_type =  'N/A';

                }
                 $val['expense_type'] = $sub_type;
                 
                $val['date'] = date("d/m/Y", strtotime( $row->date));
                
                $val['voucher_number'] = $row->voucher_number;
                
                 if($row)
				{
					if($row->payment_mode == 0)
					{
						$mode = "Cash";
					}
					if($row->payment_mode == 1)
					{
						$mode = "Cheque";
					}
					if($row->payment_mode == 2)
					{
						$mode = "Online Transfer";
					}
					if($row->payment_mode == 3)
					{
						$mode = "SSB Transfer";
					}
				
				}
				else{
					$mode = "N/A";
				}
                
                $val['payment_mode'] = $mode;
                
                if(isset($row->investment_id))
                {
                   $total_amount = Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');
                }
                else{
                    $total_amount='N/A';
                }
                  $val['total_amount']=$total_amount;
                  
                  if(isset($row->maturity_amount_payable))
                {
                   $maturity_amount_payable =round($row->maturity_amount_payable);
                }
                else{
                    $maturity_amount_payable='N/A';
                }
                  $val['total_payable_amount']=round($maturity_amount_payable); 
                
                if($row->payment_type == 4)
                {
                     if($row->investment_id)
                {
                     $data = getInvestmentDetails($row->investment_id);
                  
                  $account =  $data->account_number;
                    
                }
                }
                else{
                   if($row->account_number){
                   $account = $row->account_number;
                }else{
                   $account =  'N/A';
                } 
                }
             $val['account_number'] =  $account;
             
             if(isset($row->investment_id))
                {
                    $member_id = getInvestmentDetails($row->investment_id)->member_id;
                    
                    $ac = SavingAccount::where('member_id',$member_id)->first();
                    if($ac){
                    $val['ssb_account_number']= $ac->account_no;
                    }
                    else{
                         $val['ssb_account_number']='N/A';
                    }
                }
                else{
                    $val['ssb_account_number']='N/A';
                }
                
                
                 if(isset($row->bank_account_number))
                {
                     $val['bank_account_number'] =  $row->bank_account_number;
                }
                else{
                     $val['bank_account_number'] =  "N/A";
                }
                
                if(isset($row->bank_ifsc))
                {
                     $val['ifsc_code'] =  $row->bank_ifsc;
                }
                else{
                     $val['ifsc_code'] =  "N/A";
                }
                
                 if($row->is_print == 0){
                    $print = 'Yes';
                }else{
                    $print = 'No';        
                }
                 $val['print'] = $print;
                 
                  if($row->status == 0){

                    $status = 'Pending';

                }else{

                    $status = 'Approved';        

                }
                 $val['status'] = $status;
                 $vurl = URL::to("admin/demand-advice/view/".$row->id."");

                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                if($row->status == 1){
					if(Auth::user()->id!= "13"){
                    $btn .= '<a class="dropdown-item" href="'.$vurl.'"><i class="icon-pencil7 mr-2"></i>Print</a>';
					}
                }

                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val; 

                
            }
           
            
    
   
         }

    // }
     $output = array( "draw" => $_POST['draw'], "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $rowReturn);
        return json_encode($output);
  }


    /**

     * Display the specified resource.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function taAdvancedListing(Request $request)

    { 

        if ($request->ajax() && check_my_permission( Auth::user()->id,"87") == "1") {

            $arrFormData = array();   

            if(!empty($_POST['searchform']))

            {

                foreach($_POST['searchform'] as $frm_data)

                {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];

                }

            }

            $data=DemandAdvice::with('expenses','branch')->where('payment_type',0)->where('sub_payment_type',1)->where('status',1)->where('ta_advanced_adjustment',1);            

            if(Auth::user()->branch_id>0){

             $id=Auth::user()->branch_id;

             $data=$data->where('branch_id','=',$id);

            }

            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['date_from'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));

                    if($arrFormData['date_to'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereBetween('date', [$startDate, $endDate]);

                }



                if($arrFormData['employee_code'] !=''){

                    $employee_code=$arrFormData['employee_code'];

                    $data=$data->where('employee_code','=',$employee_code);

                }



                if($arrFormData['ta_advanced_employee_name'] !=''){

                    $employee_name=$arrFormData['ta_advanced_employee_name'];

                    $data=$data->where('employee_name','=',$employee_name);

                }

            }
            // else{

            //     $data=$data->where('branch_id',0);

            // }

            $data = $data->orderby('id','DESC')->get();

            return Datatables::of($data)

            ->addIndexColumn()

            ->addColumn('payment_type', function($row){

                if($row->payment_type == 0){

                    $payment_type = 'Expenses';

                }elseif($row->payment_type == 1){

                    $payment_type = 'Maturity';

                }elseif($row->payment_type == 2){

                    $payment_type = 'Prematurity';

                }elseif($row->payment_type == 3){

                    $payment_type = 'Death Help';

                }else{

                    $payment_type = '';

                }

                return $payment_type;

            })

            ->rawColumns(['payment_type'])

            ->addColumn('sub_payment_type', function($row){

                if($row->sub_payment_type == 0 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'Fresh Expense';

                }elseif($row->sub_payment_type == 1 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'TA advance / Imprest';

                }elseif($row->sub_payment_type == 2 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'Advanced Salary';

                }elseif($row->sub_payment_type == 3 && $row->sub_payment_type != ''){

                    $sub_payment_type = 'Advanced Rent';

                }else{

                    $sub_payment_type = '';

                }

                return $sub_payment_type;

            })

            ->rawColumns(['sub_payment_type'])

            ->addColumn('branch', function($row){

                $branch = $row['branch']->name;

                return $branch;

            })

            ->rawColumns(['branch'])



            ->addColumn('employee_code', function($row){

                $employee_code = $row->employee_code;

                return $employee_code;

            })

            ->rawColumns(['employee_code'])

            ->addColumn('employee_name', function($row){

                $employee_name = $row->employee_name;

                return $employee_name;

            })

            ->rawColumns(['employee_name'])

            ->addColumn('advanced_amount', function($row){

                $advanced_amount = $row->advanced_amount;

                return $advanced_amount;

            })

            ->rawColumns(['advanced_amount'])



            ->addColumn('status', function($row){

                if($row->status == 0){

                    $status = 'Pending';

                }else{

                    $status = 'Approved';        

                }

                

                return $status;

            })

            ->rawColumns(['status'])



            ->addColumn('created_at', function($row){

                return date("d/m/Y", strtotime( $row->date));

            })

            ->rawColumns(['created_at'])

            ->addColumn('action', function($row){

                $url = URL::to("admin/demand-advice/adjust-ta-advanced/".$row->id."");

                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Adjestment</a>';

                $btn .= '</div></div></div>';          

                return $btn;

            })

            ->rawColumns(['action'])

            ->make(true);

        }

    }



    /**

     * Add Rent Liability View.

     * Route: /member/passbook

     * Method: get 

     * @return  array()  Response

     */

    public function addAdvice()

    {

       if(check_my_permission( Auth::user()->id,"133") != "1"){

          return redirect()->route('admin.dashboard');

        }

        $data['title'] = 'Add Demand Advice';

        $data['expenseCategories'] = AccountHeads::select('id','sub_head')->where('parent_id',4)->where('status',0)->get();

        $data['expenseSubCategories'] = AccountHeads::select('id','sub_head','parent_id')->whereIn('parent_id', array(14,86))->whereNotIn('id', array(37,40,53,87,88,92))->where('status',0)->get();

        $data['liabilityHeads'] = array('');

        $data['rentOwners'] = RentLiability::select('id','owner_name')->where('status',0)->get();

        return view('templates.admin.demand-advice.add-demand-advice',$data);

    }

    /**

     * Save Demand Advice.

     * Route: /save-account-head

     * Method: get 

     * @return  array()  Response

     */

    public function saveAdvice(Request $request)

    {

        $rules = [

            'paymentType' => 'required',

            'branch' => 'required',

            //'date' => 'required',

            //'expenseType' => 'required',

        ];

        $customMessages = [

            'required' => 'The :attribute field is required.'

        ];

        $this->validate($request, $rules, $customMessages);

        //echo "<pre>"; print_r($request->all()); die;

        DB::beginTransaction();

        try {

            $voucherRecord = DemandAdvice::whereNotNull('mi_code')->orderby('id', 'desc')->first('mi_code');

            if($voucherRecord){

                $miCodeAdd = $voucherRecord->mi_code+1;

            }else{

                $miCodeAdd=1; 

            }

            $miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);

            $voucherNumber='32'.date("Y").''.date('m').''.$miCode;

            if($request->paymentType == 0 && $request->expenseType == 0){

                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'created_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;
                
                //Log Start
                $encodeDate = json_encode($daData);
                $arrs = array("demandAdviceId" => $demandAdviceId, "type" => "10", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Type Demand Advice – Creation", "data" => $encodeDate);
                DB::table('user_log')->insert($arrs);
                //Log End

                foreach ($request->fresh_expense as $key => $value) {

                    if(isset($value['bill_photo'])){

                        $mainFolder = storage_path().'/images/demand-advice/expense';

                        $file = $value['bill_photo'];

                        $uploadFile = $file->getClientOriginalName();

                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                        $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                        $file->move($mainFolder,$fname);

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



                    $feData = [

                        'demand_advice_id' => $demandAdviceId,

                        'category' => $value['expenseCategory'],

                        'subcategory1' => $value['expenseSubCategory1'],

                        'subcategory2' => $value['expenseSubCategory2'],

                        'subcategory3' => $value['expenseSubCategory3'],

                        'party_name' => $value['party_name'],

                        'particular' => $value['particular'],

                        'mobile_number' => $value['mobile_number'],

                        'amount' => $value['amount'],

                        'bill_number' => $value['billNumber'],

                        'bill_file_id' => $file_id,

                        'status' => 0,

                        'created_at' => $request->created_at,

                    ];

                    $demandAdvice = DemandAdviceExpense::create($feData);

                }            

            }elseif($request->paymentType == 0 && $request->expenseType == 1){

                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'employee_code' => $request->ta_employee_code,

                    'employee_id' => $request->ta_employee_id,

                    'employee_name' => $request->ta_employee_name,

                    'particular' => $request->ta_particular,

                    'advanced_amount' => $request->ta_advance_amount,

                    'created_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;

            }elseif($request->paymentType == 0 && $request->expenseType == 2){

                

                if(isset($request->advanced_salary_letter_photo)){

                    $mainFolder = storage_path().'/images/demand-advice/advancedsalary';

                    $file = $request->advanced_salary_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

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



                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'employee_code' => $request->advanced_salary_employee_code,

                    'employee_id' => $request->advanced_salary_employee_id,

                    'employee_name' => $request->advanced_salary_employee_name,

                    'mobile_number' => $request->advanced_salary_mobile_number,

                    'amount' => $request->advanced_salary_amount,

                    'letter_photo_id' => $file_id,

                    'narration' => $request->advanced_salary_narration,

                    'ssb_account' => $request->advanced_salary_ssb_account,

                    'bank_name' => $request->advanced_salary_bank_name,

                    'bank_account_number' => $request->advanced_salary_bank_account_number,

                    'bank_ifsc' => $request->advanced_salary_ifsc_code,

                    'created_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;

            }elseif($request->paymentType == 0 && $request->expenseType == 3){



                $ownersName = RentLiability::where('id',$request->advanced_rent_party_name)->first('owner_name');

                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'employee_code' => $request->advanced_salary_employee_code,

                    'employee_id' => $request->advanced_rent_employee_id,

                    'employee_name' => $request->advanced_rent_employee_name,

                    'owner_id' => $request->advanced_rent_party_name,

                    'owner_name' => $ownersName->owner_name,

                    'mobile_number' => $request->advanced_rent_mobile_number,

                    'amount' => $request->advanced_rent_amount,

                    'narration' => $request->advanced_rent_narration,

                    'ssb_account' => $request->advanced_rent_ssb_account,

                    'bank_name' => $request->advanced_rent_bank_name,

                    'bank_account_number' => $request->advanced_rent_bank_account_number,

                    'bank_ifsc' => $request->advanced_rent_ifsc_code,

                    'created_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;

            }elseif($request->paymentType == 2 && $request->maturity_prematurity_type == 0){

                

                if(isset($request->maturity_letter_photo)){

                    $mainFolder = storage_path().'/images/demand-advice/maturity-prematurity';

                    $file = $request->maturity_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

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



                $daData = [

                    'payment_type' => 1,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->maturity_prematurity_date))),

                    'letter_photo_id' => $file_id,

                    'investment_id' => $request->maturity_investmnet_id,

                    'account_number' => $request->maturity_account_number,

                    'opening_date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->maturity_opening_date))),

                    'plan_name' => $request->maturity_plan_name,

                    'tenure' => $request->maturity_tenure,

                    'account_holder_name' => $request->maturity_account_holder_name,

                    'father_name' => $request->maturity_father_name,

                    'maturity_prematurity_category' => $request->maturity_category,

                    'maturity_prematurity_amount' => $request->maturity_amount,

                    'mobile_number' => $request->maturity_mobile_number,

                    'ssb_account' => $request->maturity_ssb_account,

                    'bank_account_number' => $request->maturity_bank_account_number,

                    'bank_ifsc' => $request->maturity_ifsc_code,

                    'created_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;

            }elseif($request->paymentType == 2 && $request->maturity_prematurity_type == 1){

                if(isset($request->prematurity_letter_photo)){

                    $mainFolder = storage_path().'/images/demand-advice/maturity-prematurity';

                    $file = $request->prematurity_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

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

                $daData = [

                    'payment_type' => 2,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->maturity_prematurity_date))),

                    'letter_photo_id' => $file_id,

                    'investment_id' => $request->prematurity_investmnet_id,

                    'account_number' => $request->prematurity_account_number,

                    'opening_date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->prematurity_opening_date))),

                    'plan_name' => $request->prematurity_plan_name,

                    'tenure' => $request->prematurity_tenure,

                    'account_holder_name' => $request->prematurity_account_holder_name,

                    'father_name' => $request->prematurity_father_name,

                    'maturity_prematurity_category' => $request->prematurity_category,

                    'maturity_prematurity_amount' => $request->prematurity_amount,

                    'mobile_number' => $request->prematurity_mobile_number,

                    'ssb_account' => $request->prematurity_ssb_account,

                    'bank_account_number' => $request->prematurity_bank_account_number,

                    'bank_ifsc' => $request->prematurity_ifsc_code,

                    'created_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;

            }elseif($request->paymentType == 4){

                if(isset($request->death_help_letter_photo)){

                    $mainFolder = storage_path().'/images/demand-advice/death-help';

                    $file = $request->death_help_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

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



                if($request->death_help_category == 0){

                    $subType = 4;

                }elseif($request->death_help_category == 1){

                    $subType = 5;

                }



                $daData = [

                    'payment_type' => 3,

                    'sub_payment_type' => $subType,

                    'branch_id' => $request->branch,

                    'mi_code' => $miCode,

                    'voucher_number' => $voucherNumber,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->death_help_date))),

                    'death_certificate_id' => $file_id,

                    'investment_id' => $request->death_help_investmnet_id,

                    'account_number' => $request->death_help_account_number,

                    'opening_date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->death_help_opening_date))),

                    'plan_name' => $request->death_help_plan_name,

                    'tenure' => $request->death_help_tenure,

                    'account_holder_name' => $request->death_help_account_holder_name,

                    'death_help_catgeory' => $request->death_help_category,

                    'deno' => $request->death_help_deno,

                    'deposited_amount' => $request->death_help_deposited_amount,

                    //'death_claim_amount' => $request->death_help_death_claim_amount,

                    'nominee_name' => $request->death_help_nominee_name,

                    'naominee_member_id' => $request->death_help_nominee_member_id,

                    'mobile_number' => $request->death_help_mobile_number,

                    'ssb_account' => $request->death_help_ssb_account,

                    'created_at' => $request->created_at,

                ];



                $demandAdvice = DemandAdvice::create($daData);

                $demandAdviceId = $demandAdvice->id;

            }

      

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        if ($demandAdviceId) {

            return redirect()->route('admin.demand.application')->with('success', 'Demand Advice Added Successfully!');

        } else {

            return back()->with('alert', 'Problem With Creating Demand Advice');

        }

    }



    /**

     * Demand Advice View.

     * Route: /member/passbook

     * Method: get sss

     * @return  array()  Response

     */

    public function viewAdvice($id)

    {

        $data['title'] = 'View Demand Advice';

        $data['row'] = DemandAdvice::with('investment','expenses','branch')->where('id',$id)->first();

        return view('templates.admin.demand-advice.print-demand-advice', $data);

    }



    /**

     * Demand Advice View.

     * Route: /member/passbook

     * Method: get sss

     * @return  array()  Response

     */

    public function viewTaAdvanced()

    {

        if(check_my_permission( Auth::user()->id,"87") != "1"){

          return redirect()->route('admin.dashboard');

        }

        $data['title'] = 'View TA advance and Imprest Advice';

        return view('templates.admin.demand-advice.view_ta_advanced', $data);

    }



    /**

     * Edit user View.

     * Route: /member/passbook

     * Method: get sss

     * @return  array()  Response

     */

    public function editAdvice($id)

    {

        $data['title'] = 'Edit Demand Advice';

        $data['expenseCategories'] = AccountHeads::select('id','sub_head')->where('parent_id',4)->where('status',0)->get();

        $data['expenseSubCategories'] = AccountHeads::select('id','sub_head','parent_id')->whereIn('parent_id', array(14,86))->whereNotIn('id', array(37,40,53,87,88,92))->where('status',0)->get();

        $data['liabilityHeads'] = array('');

        $data['rentOwners'] = RentLiability::select('id','owner_name')->where('status',0)->get();

        $data['subCategory1'] = AccountHeads::where('parent_id',86)->get();

        $subCategory2 = array();
        $i = 0;
        foreach ($data['subCategory1'] as $value1) {
            $record1 = AccountHeads::where('parent_id',$value1->head_id)->get();
            foreach ($record1 as $value2) {
                $record2 = AccountHeads::where('head_id',$value2->head_id)->first();
                if($record2){
                    $subCategory2[$i] = $record2;
                    $i++;
                }
            }
        }
        $data['subCategory2'] = $subCategory2;

        $subCategory3 = array();
        foreach ($subCategory2 as $value3) {
            $record3 = AccountHeads::where('parent_id',$value3->head_id)->get();

            foreach ($record3 as $value4) {
                $record4 = AccountHeads::where('head_id',$value4->head_id)->first();
                if($record4){
                    $subCategory3[$i] = $record4;
                    $i++;
                }
            }
        }
        $data['subCategory3'] = $subCategory3;

        $data['demandAdvice'] = DemandAdvice::with('expenses','branch')->where('id',$id)->first();

        $data['investmentDetails'] = Memberinvestments::with('plan','member','ssb','memberBankDetail')->where('id',$data['demandAdvice']->investment_id)->first();

        return view('templates.admin.demand-advice.edit-demand-advice', $data);

    }



    /**

     * Edit user View.

     * Route: /member/passbook

     * Method: get sss

     * @return  array()  Response

     */

    public function adjustTaAdvanced($id)

    {

        $data['title'] = 'Edit Demand Advice';

        $data['expenseCategories'] = AccountHeads::select('id','sub_head')->where('id',86)->where('status',0)->get();

        $data['expenseSubCategories'] = AccountHeads::select('id','sub_head','parent_id')->whereIn('parent_id', array(86))->whereNotIn('id', array(37,40,53,87,88,92))->where('status',0)->get();

        $data['demandAdvice'] = DemandAdvice::with('expenses','branch')->where('id',$id)->first();

        $data['cBanks'] = SamraddhBank::with('bankAccount')->where("status","1")->get();  

        $data['cheques'] = SamraddhCheque::select('cheque_no','account_id')->where('status',1)->get();

        return view('templates.admin.demand-advice.adjust_ta_advanced', $data);

    }



    /**

     * Update the specified demand advice.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function updateAdvice(Request $request)

    {

        $rules = [

            'paymentType' => 'required',

            'branch' => 'required',

            //'date' => 'required',

            //'expenseType' => 'required',

        ];

        $customMessages = [

            'required' => 'The :attribute field is required.'

        ];



        $this->validate($request, $rules, $customMessages);  

        //echo "<pre>"; print_r($request->all()); die;

        DB::beginTransaction();

        try {

            if($request->paymentType == 0 && $request->expenseType == 0){

                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'updated_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

                foreach ($request->fresh_expense as $key => $value) {

                    if($value['id'] == ''){

                        if(isset($value['bill_photo'])){

                            $mainFolder = storage_path().'/images/demand-advice/expense';

                            $file = $value['bill_photo'];

                            $uploadFile = $file->getClientOriginalName();

                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                            $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                            $file->move($mainFolder,$fname);

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



                        $feData = [

                            'demand_advice_id' => $request->demand_advice_id,

                            'category' => $value['expenseCategory'],

                            //'subcategory' => $value['expenseSubCategory'],

                            'subcategory1' => $value['expenseSubCategory1'],

                            'subcategory2' => $value['expenseSubCategory2'],

                            'subcategory3' => $value['expenseSubCategory3'],

                            'party_name' => $value['party_name'],

                            'particular' => $value['particular'],

                            'mobile_number' => $value['mobile_number'],

                            'amount' => $value['amount'],

                            'bill_number' => $value['billNumber'],

                            'bill_file_id' => $file_id,

                            'created_at' => $request->created_at,

                        ];

                        $demandAdvice = DemandAdviceExpense::create($feData);

                    }else{



                        if(isset($value['bill_photo']) && isset($value['file_id'])){

                            $hiddenFileId = $value['file_id'];

                            $mainFolder = storage_path().'/images/demand-advice/expense';

                            $file = $value['bill_photo'];

                            $uploadFile = $file->getClientOriginalName();

                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                            $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                            $file->move($mainFolder,$fname);

                            $data = [

                                'file_name' => $fname,

                                'file_path' => $mainFolder,

                                'file_extension' => $file->getClientOriginalExtension(),

                            ];

                            $fileRes = Files::find($hiddenFileId);  

                            $fileRes->update($data);



                            $feData = [

                                'category' => $value['expenseCategory'],

                                'subcategory' => $value['expenseSubCategory'],

                                'party_name' => $value['party_name'],

                                'particular' => $value['particular'],

                                'mobile_number' => $value['mobile_number'],

                                'amount' => $value['amount'],

                                'bill_number' => $value['billNumber'],

                            ];



                        }elseif(isset($value['bill_photo'])){

                            $mainFolder = storage_path().'/images/demand-advice/expense';

                            $file = $value['bill_photo'];

                            $uploadFile = $file->getClientOriginalName();

                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                            $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                            $file->move($mainFolder,$fname);

                            $fData = [

                                'file_name' => $fname,

                                'file_path' => $mainFolder,

                                'file_extension' => $file->getClientOriginalExtension(),

                            ];

                            $res = Files::create($fData);

                            $file_id = $res->id;



                            $feData = [

                                'category' => $value['expenseCategory'],

                                //'subcategory' => $value['expenseSubCategory'],

                                'subcategory1' => $value['expenseSubCategory1'],

                                'subcategory2' => $value['expenseSubCategory2'],

                                'subcategory3' => $value['expenseSubCategory3'],

                                'party_name' => $value['party_name'],

                                'particular' => $value['particular'],

                                'mobile_number' => $value['mobile_number'],

                                'amount' => $value['amount'],

                                'bill_file_id' => $file_id,

                                'bill_number' => $value['billNumber'],

                            ];

                        }else{

                           $feData = [

                                'category' => $value['expenseCategory'],

                                //'subcategory' => $value['expenseSubCategory'],

                                'subcategory1' => $value['expenseSubCategory1'],

                                'subcategory2' => $value['expenseSubCategory2'],

                                'subcategory3' => $value['expenseSubCategory3'],

                                'party_name' => $value['party_name'],

                                'particular' => $value['particular'],

                                'mobile_number' => $value['mobile_number'],

                                'amount' => $value['amount'],

                                'bill_number' => $value['billNumber'],

                            ]; 

                        }

                        $DemandAdviceExpense = DemandAdviceExpense::find($value['id']);

                        $DemandAdviceExpense->update($feData);

                    } 

                }    

            }elseif($request->paymentType == 0 && $request->expenseType == 1){

                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'employee_id' => $request->ta_employee_id,

                    'employee_name' => $request->ta_employee_name,

                    'particular' => $request->ta_particular,

                    'advanced_amount' => $request->ta_advance_amount,

                    'updated_at' => $request->created_at,

                ];



                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

            }elseif($request->paymentType == 0 && $request->expenseType == 2){

                

                if(isset($request->advanced_salary_letter_photo) && $request->old_advanced_salary_letter_photo == ''){

                    $mainFolder = storage_path().'/images/demand-advice/advancedsalary';

                    $file = $request->advanced_salary_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $fData = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $res = Files::create($fData);

                    $file_id = $res->id;

                }elseif(isset($request->advanced_salary_letter_photo) && $request->old_advanced_salary_letter_photo != ''){



                    $hiddenFileId = $request->old_advanced_salary_letter_photo;

                    $mainFolder = storage_path().'/images/demand-advice/advancedsalary';

                    $file = $request->advanced_salary_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $data = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $fileRes = Files::find($hiddenFileId);  

                    $fileRes->update($data);



                    $file_id = $request->old_advanced_salary_letter_photo;

                }elseif($request->advanced_salary_letter_photo == ''){

                    $file_id = $request->old_advanced_salary_letter_photo;

                }



                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'employee_id' => $request->advanced_salary_employee_id,

                    'employee_name' => $request->advanced_salary_employee_name,

                    'mobile_number' => $request->advanced_salary_mobile_number,

                    'amount' => $request->advanced_salary_amount,

                    'letter_photo_id' => $file_id,

                    'narration' => $request->advanced_salary_narration,

                    'ssb_account' => $request->advanced_salary_ssb_account,

                    'bank_name' => $request->advanced_salary_bank_name,

                    'bank_account_number' => $request->advanced_salary_bank_account_number,

                    'bank_ifsc' => $request->advanced_salary_ifsc_code,

                    'updated_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

            }elseif($request->paymentType == 0 && $request->expenseType == 3){

                $daData = [

                    'payment_type' => $request->paymentType,

                    'sub_payment_type' => $request->expenseType,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->date))),

                    'employee_id' => $request->advanced_rent_employee_id,

                    'employee_name' => $request->advanced_rent_employee_name,

                    'owner_name' => $request->advanced_rent_party_name,

                    'mobile_number' => $request->advanced_rent_mobile_number,

                    'amount' => $request->advanced_rent_amount,

                    'narration' => $request->advanced_rent_narration,

                    'ssb_account' => $request->advanced_rent_ssb_account,

                    'bank_name' => $request->advanced_rent_bank_name,

                    'bank_account_number' => $request->advanced_rent_bank_account_number,

                    'bank_ifsc' => $request->advanced_rent_ifsc_code,

                    'updated_at' => $request->created_at,

                ];

                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

            }elseif($request->paymentType == 2 && $request->maturity_prematurity_type == 0){

                if(isset($request->maturity_letter_photo) && $request->old_maturity_letter_photo == ''){

                    $mainFolder = storage_path().'/images/demand-advice/maturity-prematurity';

                    $file = $request->maturity_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $fData = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $res = Files::create($fData);

                    $file_id = $res->id;

                }elseif(isset($request->maturity_letter_photo) && $request->old_maturity_letter_photo != ''){



                    $hiddenFileId = $request->old_maturity_letter_photo;

                    $mainFolder = storage_path().'/images/demand-advice/maturity-prematurity';

                    $file = $request->maturity_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $data = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $fileRes = Files::find($hiddenFileId);  

                    $fileRes->update($data);



                    $file_id = $request->old_maturity_letter_photo;

                }elseif($request->maturity_letter_photo == ''){

                    $file_id = $request->old_maturity_letter_photo;

                }



                $daData = [

                    'payment_type' => 1,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->maturity_prematurity_date))),

                    'letter_photo_id' => $file_id,

                    'investment_id' => $request->maturity_investmnet_id,

                    'account_number' => $request->maturity_account_number,

                    'opening_date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->maturity_opening_date))),

                    'plan_name' => $request->maturity_plan_name,

                    'tenure' => $request->maturity_tenure,

                    'account_holder_name' => $request->maturity_account_holder_name,

                    'father_name' => $request->maturity_father_name,

                    'maturity_prematurity_category' => $request->maturity_category,

                    'maturity_prematurity_amount' => $request->maturity_amount,

                    'mobile_number' => $request->maturity_mobile_number,

                    'ssb_account' => $request->maturity_ssb_account,

                    'bank_account_number' => $request->maturity_bank_account_number,

                    'bank_ifsc' => $request->maturity_ifsc_code,

                    'updated_at' => $request->created_at,

                ];



                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

            }elseif($request->paymentType == 2 && $request->maturity_prematurity_type == 1){

                if(isset($request->prematurity_letter_photo) && $request->old_prematurity_letter_photo == ''){

                    $mainFolder = storage_path().'/images/demand-advice/maturity-prematurity';

                    $file = $request->prematurity_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $fData = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $res = Files::create($fData);

                    $file_id = $res->id;

                }elseif(isset($request->prematurity_letter_photo) && $request->old_prematurity_letter_photo != ''){



                    $hiddenFileId = $request->old_prematurity_letter_photo;

                    $mainFolder = storage_path().'/images/demand-advice/maturity-prematurity';

                    $file = $request->prematurity_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $data = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $fileRes = Files::find($hiddenFileId);  

                    $fileRes->update($data);



                    $file_id = $request->old_prematurity_letter_photo;

                }elseif($request->prematurity_letter_photo == ''){

                    $file_id = $request->old_prematurity_letter_photo;

                }



                 $daData = [

                    'payment_type' => 2,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->maturity_prematurity_date))),

                    'letter_photo_id' => $file_id,

                    'investment_id' => $request->prematurity_investmnet_id,

                    'account_number' => $request->prematurity_account_number,

                    'opening_date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->prematurity_opening_date))),

                    'plan_name' => $request->prematurity_plan_name,

                    'tenure' => $request->prematurity_tenure,

                    'account_holder_name' => $request->prematurity_account_holder_name,

                    'father_name' => $request->prematurity_father_name,

                    'maturity_prematurity_category' => $request->prematurity_category,

                    'maturity_prematurity_amount' => $request->prematurity_amount,

                    'mobile_number' => $request->prematurity_mobile_number,

                    'ssb_account' => $request->prematurity_ssb_account,

                    'bank_account_number' => $request->prematurity_bank_account_number,

                    'bank_ifsc' => $request->prematurity_ifsc_code,

                    'updated_at' => $request->created_at,

                ];



                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

            }elseif($request->paymentType == 4){

                if(isset($request->death_help_letter_photo) && $request->old_death_help_letter_photo == ''){

                    $mainFolder = storage_path().'/images/demand-advice/death-help';

                    $file = $request->death_help_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $fData = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $res = Files::create($fData);

                    $file_id = $res->id;

                }elseif(isset($request->death_help_letter_photo) && $request->old_death_help_letter_photo != ''){



                    $hiddenFileId = $request->old_death_help_letter_photo;

                    $mainFolder = storage_path().'/images/demand-advice/death-help';

                    $file = $request->death_help_letter_photo;

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

                    $data = [

                        'file_name' => $fname,

                        'file_path' => $mainFolder,

                        'file_extension' => $file->getClientOriginalExtension(),

                    ];

                    $fileRes = Files::find($hiddenFileId);  

                    $fileRes->update($data);



                    $file_id = $request->old_death_help_letter_photo;

                }elseif($request->death_help_letter_photo == ''){

                    $file_id = $request->old_death_help_letter_photo;

                }



                if($request->death_help_category == 0){

                    $subType = 4;

                }elseif($request->death_help_category == 1){

                    $subType = 5;

                }



                $daData = [

                    'payment_type' => 3,

                    'sub_payment_type' => $subType,

                    'branch_id' => $request->branch,

                    'date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->death_help_date))),

                    'death_certificate_id' => $file_id,

                    'investment_id' => $request->death_help_investmnet_id,

                    'account_number' => $request->death_help_account_number,

                    'opening_date' => date("Y-m-d", strtotime( str_replace('/', '-', $request->death_help_opening_date))),

                    'plan_name' => $request->death_help_plan_name,

                    'tenure' => $request->death_help_tenure,

                    'account_holder_name' => $request->death_help_account_holder_name,

                    'deno' => $request->death_help_deno,

                    'deposited_amount' => $request->death_help_deposited_amount,

                    //'death_claim_amount' => $request->death_help_death_claim_amount,

                    'nominee_name' => $request->death_help_nominee_name,

                    'naominee_member_id' => $request->death_help_nominee_member_id,

                    'mobile_number' => $request->death_help_mobile_number,

                    'ssb_account' => $request->death_help_ssb_account,

                    'updated_at' => $request->created_at,

                ];



                $demandAdvice = DemandAdvice::find($request->demand_advice_id);

                $demandAdvice->update($daData);

            }

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback();

            return back()->with('alert', $ex->getMessage());

        }

        if ($request->demand_advice_id) {

            return redirect()->route('admin.demand.application')->with('success', 'Update was Successful!');

        } else {

            return back()->with('alert', 'An error occured');

        }

    }

    /**

     * Save Demand Advice.

     * Route: /save-account-head

     * Method: get 

     * @return  array()  Response

     */

    public function updateTaAdvanced(Request $request)

    {

        DB::beginTransaction();

        try {

            //echo $request->adjustment_level; die;

            $ssbArray = array();

            $entryTime = date("H:i:s");

            Session::put('created_at', date("Y-m-d", strtotime(convertDate($request->payment_date))));

            $request['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_date)));

            foreach ($request->ta_expense as $key => $value) {

                if(isset($value['bill_photo'])){

                    $mainFolder = storage_path().'/images/demand-advice/expense';

                    $file = $value['bill_photo'];

                    $uploadFile = $file->getClientOriginalName();

                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);

                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();

                    $file->move($mainFolder,$fname);

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

                $feData = [

                    'demand_advice_id' => $request->demand_advice_id,

                    'category' => $value['expenseCategory'],

                    'subcategory' => $value['expenseSubCategory'],

                    'amount' => $value['amount'],

                    'bill_number' => $value['billNumber'],

                    'bill_file_id' => $file_id,

                    'status' => 0,

                    'created_at' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                ];

                $demandAdvice = DemandAdviceExpense::create($feData);

            }            

            $response = DemandAdvice::where('id', $request->demand_advice_id)->update(['ta_advanced_adjustment' => 0]);

            $demandAdviceTaAdvanced = DemandAdvice::with('employee')->where('id',$request->demand_advice_id)->first();    

            $taAdvanced = DemandAdviceExpense::where('demand_advice_id',$request->demand_advice_id)->get(); 

            $sumAmount = DemandAdviceExpense::where('demand_advice_id',$request->demand_advice_id)->sum('amount'); 

            $request['branch_id'] = $demandAdviceTaAdvanced->branch_id;

            $employeeAdvancedSalary = $demandAdviceTaAdvanced['employee']->advance_payment-$demandAdviceTaAdvanced->advanced_amount;

            $employeeCurrentBalance = $demandAdviceTaAdvanced['employee']->current_balance-$demandAdviceTaAdvanced->advanced_amount;

            $advancedSalaryUpdate = Employee::where('id',$demandAdviceTaAdvanced->employee_id)->update(['advance_payment' => $employeeAdvancedSalary,'current_balance' => $employeeCurrentBalance]);

            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('account_no',$demandAdviceTaAdvanced['employee']->ssb_account)->first();
            
            if($request->amount_mode == 2){

                if($request->mode == 3){

                    SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);

                    SamraddhChequeIssue::create([

                        'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                        'type' =>6,

                        'sub_type' =>62,

                        'type_id' =>$request->demand_advice_id,

                        'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                        'status' => 1,

                    ]);

                }

            }


            if($request->amount_mode == 1 && $ssbAccountDetails == ''){
                array_push($ssbArray,$value);
            }else{

                if($request->amount_mode == 0 || $request->amount_mode == ''){

                    $branch_id = $request->branch_id;

                    $type = 13;

                    $sub_type = 132;

                    $jv_unique_id = NULL;

                    $type_id = $demandAdviceTaAdvanced->id;

                    $type_transaction_id = $request->demand_advice_id;

                    $associate_id = NULL;

                    if($ssbAccountDetails){

                        $member_id = $ssbAccountDetails['ssbMember']->id;

                        $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                        $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                    }else{
                        $member_id = $demandAdviceTaAdvanced['employee']->id;

                        $amount_to_id =$demandAdviceTaAdvanced['employee']->id;

                        $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                    }

                    $branch_id_to = NULL;

                    $branch_id_from = $request->branch_id;


                    if($request->adjustment_level == 0){

                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $taAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $opening_balance = $demandAdviceTaAdvanced->advanced_amount;

                        $amount = $demandAdviceTaAdvanced->advanced_amount;

                        $closing_balance = $demandAdviceTaAdvanced->advanced_amount;

                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$amount.' - To Cash A/C Cr '.$amount;

                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$amount;

                        $description_cr = 'To Cash A/C Cr '.$amount;

                        $payment_type = 'DR';

                    }elseif($request->adjustment_level == 1){

                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $taAmount = $sumAmount;

                        $opening_balance = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $amount = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $closing_balance = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$amount.' - To Cash A/C Cr '.$amount;

                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$amount;

                        $description_cr = 'To Cash A/C Cr '.$amount;

                        $payment_type = 'DR';

                    }elseif($request->adjustment_level == 2){

                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $taAmount = $sumAmount;

                        $opening_balance = $demandAdviceTaAdvanced->advanced_amount-$sumAmount;

                        $amount = $demandAdviceTaAdvanced->advanced_amount-$sumAmount;

                        $closing_balance = $demandAdviceTaAdvanced->advanced_amount-$sumAmount;

                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$sumAmount.' - To Cash A/C Cr '.$sumAmount;

                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$sumAmount;

                        $description_cr = 'To Cash A/C Cr '.$sumAmount;

                        $payment_type = 'CR';

                    }

                    $payment_mode = 0;

                    $currency_code = 'INR';

                    $amount_from_id = $request->branch_id;

                    $amount_from_name = getBranchDetail($demandAdviceTaAdvanced->branch_id)->name;

                    $v_no = NULL;

                    $v_date = NULL;

                    $ssb_account_id_from = NULL;

                    $ssb_account_id_to = NULL;

                    $cheque_type = NULL;

                    $cheque_id = NULL;

                    $cheque_no = NULL;

                    $cheque_date = NULL;

                    $cheque_bank_to_name = NULL;

                    $cheque_bank_to_branch = NULL;

                    $cheque_bank_to_ac_no = NULL;

                    $cheque_bank_from = NULL;

                    $cheque_bank_from_id = NULL;

                    $cheque_bank_ac_from_id = NULL;

                    $cheque_bank_ac_from = NULL;

                    $cheque_bank_ifsc_from = NULL;

                    $cheque_bank_branch_from = NULL;

                    $cheque_bank_to = NULL;

                    $cheque_bank_ac_to = NULL;

                    $cheque_bank_to_ifsc = NULL;

                    $transction_no = NULL;

                    $transction_bank_from = NULL;

                    $transction_bank_from_id = NULL;

                    $transction_bank_ac_from = NULL;

                    $transction_bank_ifsc_from = NULL;

                    $transction_bank_branch_from = NULL;

                    $transction_bank_to = NULL;

                    $transction_bank_ac_to = NULL;

                    $transction_date = NULL;

                    $entry_date = NULL;

                    $entry_time = NULL;

                    $created_by = 1;

                    $created_by_id = Auth::user()->id;

                    $is_contra = NULL;

                    $contra_id = NULL;

                    $created_at = NULL;

                    $bank_id = NULL;

                    $bank_ac_id = NULL;

                    $transction_bank_to_name = NULL;

                    $transction_bank_to_ac_no = NULL;

                    $transction_bank_to_branch = NULL;

                    $transction_bank_to_ifsc = NULL;

                    $to_bank_name = NULL;

                    $to_bank_branch = NULL;

                    $to_bank_ac_no = NULL;

                    $to_bank_ifsc = NULL;

                    $to_bank_id = NULL;

                    $to_bank_account_id = NULL;

                    $from_bank_name = NULL;

                    $from_bank_branch = NULL;

                    $from_bank_ac_no = NULL;

                    $from_bank_ifsc = NULL;

                    $from_bank_id = NULL;

                    $from_bank_ac_id = NULL;

                    $transaction_date = NULL;

                    $transaction_charge = NULL;

                    $cheque_id = NULL;

                    $ssb_account_tran_id_to = NULL;

                    $ssb_account_tran_id_from = NULL;

                    $transction_bank_from_ac_id = NULL;

                    $saToTranctionId = NULL;

                }elseif($request->amount_mode == 1){

                    $transction_bank_from_ac_id = NULL;

                    $saToTranctionId = NULL;

                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                    $vno = "";

                    for ($i = 0; $i < 10; $i++) {

                        $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                    }        

                    $branch_id = $demandAdviceTaAdvanced['employee']->branch_id;

                    $type = 13;

                    $sub_type = 132;

                    if($ssbAccountDetails){

                        $member_id = $ssbAccountDetails['ssbMember']->id;

                        $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                        $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                    }else{
                        $member_id = $demandAdviceTaAdvanced['employee']->id;

                        $amount_to_id =$demandAdviceTaAdvanced['employee']->id;

                        $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                    }

                    $type_id = $demandAdviceTaAdvanced->id;

                    $type_transaction_id = $demandAdviceTaAdvanced->id;

                    $associate_id = NULL;

                    $branch_id_to = NULL;

                    $branch_id_from = NULL;

                    if($request->adjustment_level == 0){

                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $taAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $opening_balance = $demandAdviceTaAdvanced->advanced_amount;

                        $amount = $demandAdviceTaAdvanced->advanced_amount;

                        $closing_balance = $demandAdviceTaAdvanced->advanced_amount;

                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$amount.' - To '.$demandAdviceTaAdvanced->employee_name.' Cr '.$amount;

                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$amount;

                        $description_cr = 'To '.$demandAdviceTaAdvanced->employee_name.' Cr '.$amount;

                    }elseif($request->adjustment_level == 1){

                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $taAmount = $sumAmount;

                        $opening_balance = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $amount = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $closing_balance = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$sumAmount.' - '.$demandAdviceTaAdvanced['employee']->ssb_account.' A/C Cr '.$sumAmount;

                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$sumAmount;

                        $description_cr = $demandAdviceTaAdvanced['employee']->ssb_account.' A/C Cr '.$sumAmount;

                    }

                    $payment_type = 'CR';

                    $payment_mode = 3;

                    $currency_code = 'INR';

                    $amount_from_id = NULL;

                    $amount_from_name = NULL;

                    $v_no = $vno;

                    $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                    $jv_unique_id = NULL;

                    $ssb_account_id_from = NULL;

                    $cheque_type = NULL;

                    $cheque_id = NULL;

                    $cheque_no = NULL;

                    $cheque_date = NULL;

                    $cheque_bank_to_name = NULL;

                    $cheque_bank_to_branch = NULL;

                    $cheque_bank_from = NULL;

                    $cheque_bank_from_id = NULL;

                    $cheque_bank_ac_from = NULL;

                    $cheque_bank_ac_from_id = NULL;

                    $cheque_bank_to_ac_no = NULL;

                    $cheque_bank_ifsc_from = NULL;

                    $cheque_bank_branch_from = NULL;

                    $cheque_bank_to = NULL;

                    $cheque_bank_ac_to = NULL;

                    $cheque_bank_to_ifsc = NULL;

                    $transction_no = NULL;

                    $transction_bank_from = NULL;

                    $transction_bank_from_id = NULL;

                    $transction_bank_ac_from = NULL;

                    $transction_bank_ifsc_from = NULL;

                    $transction_bank_branch_from = NULL;

                    $transction_bank_from_ac_id = NULL;

                    $transction_bank_to = NULL;

                    $transction_bank_ac_to = NULL;

                    $transction_date = NULL;

                    $entry_date = NULL;

                    $entry_time = NULL;

                    $created_by = 1;

                    $created_by_id = Auth::user()->id;

                    $is_contra = NULL;

                    $contra_id = NULL;

                    $created_at = NULL;

                    $bank_id = NULL;

                    $bank_ac_id = NULL;

                    $transction_bank_to_name = NULL;

                    $transction_bank_to_ac_no = NULL;

                    $transction_bank_to_branch = NULL;

                    $transction_bank_to_ifsc = NULL;

                    $ssb_account_id_to = $demandAdviceTaAdvanced['employee']->ssb_id;

                    $ssb_account_tran_id_to = NULL;

                    $ssb_account_tran_id_from = NULL;

                    $to_bank_name = NULL;

                    $to_bank_branch = NULL;

                    $to_bank_ac_no = NULL;

                    $to_bank_ifsc = NULL;

                    $to_bank_id = NULL;

                    $to_bank_account_id = NULL;

                    $from_bank_name = NULL;

                    $from_bank_branch = NULL;

                    $from_bank_ac_no = NULL;

                    $from_bank_ifsc = NULL;

                    $from_bank_id = NULL;

                    $from_bank_ac_id = NULL;

                    $transaction_date = NULL;

                    $transaction_charge = NULL;

                    $cheque_id = NULL;

                }elseif($request->amount_mode == 2){

                    $saToTranctionId = NULL;

                    $branch_id = $demandAdviceTaAdvanced['employee']->branch_id;

                    $type = 13;

                    $sub_type = 132;

                    $type_id = $demandAdviceTaAdvanced->id;

                    $type_transaction_id = $request->demand_advice_id;

                    $associate_id = NULL;

                    if($ssbAccountDetails){

                        $member_id = $ssbAccountDetails['ssbMember']->id;

                        $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                        $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                    }else{
                        $member_id = $demandAdviceTaAdvanced['employee']->id;

                        $amount_to_id =$demandAdviceTaAdvanced['employee']->id;

                        $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                    }

                    $branch_id_to = NULL;

                    $branch_id_from = $request->branch_id;

                    if($request->adjustment_level == 1){

                        $taAdvancedAmount = $demandAdviceTaAdvanced->advanced_amount;

                        $taAmount = $sumAmount;

                        $opening_balance = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $amount = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $closing_balance = $sumAmount-$demandAdviceTaAdvanced->advanced_amount;

                        $description = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$sumAmount.' - To Bank A/C Cr '.$sumAmount;

                        $description_dr = getAcountHeadNameHeadId($value['expenseSubCategory']).' A/C Dr '.$sumAmount;

                        $description_cr = 'To Bank A/C Cr '.$sumAmount;

                        $payment_type = 'DR';

                    }

                    $payment_type = 'DR';

                    $currency_code = 'INR';

                    $amount_from_id = $request->branch_id;

                    $amount_from_name = getBranchDetail($demandAdviceTaAdvanced->branch_id)->name;

                    $v_no = NULL;

                    $v_date = NULL;

                    $jv_unique_id = NULL;

                    $ssb_account_id_from = NULL;

                    if($request->mode == 3){

                        $cheque_type = 1;

                        $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                        $cheque_no = $request->cheque_number;

                        $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                        $cheque_bank_from = $transction_bank_from_ac_id = $request->bank;

                        $cheque_bank_from_id = $request->bank;

                        $cheque_bank_ac_from = $request->bank_account_number;

                        $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                        $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                        $cheque_bank_branch_from = NULL;

                        $cheque_bank_to = NULL;

                        $cheque_bank_ac_to = NULL;

                        $cheque_bank_to_name = NULL;

                        $cheque_bank_to_branch = NULL;

                        $cheque_bank_to_ac_no = NULL;

                        $cheque_bank_to_ifsc = NULL;

                        $transction_no = NULL;

                        $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                        $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                        $transction_bank_ac_from = $request->bank_account_number;

                        $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                        $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                        $transction_bank_branch_from = NULL;

                        $transction_bank_to = NULL;

                        $transction_bank_ac_to = NULL;

                        $payment_mode = 1;

                        $transction_bank_to_name = NULL;

                        $transction_bank_to_ac_no = NULL;

                        $transction_bank_to_branch = NULL;

                        $transction_bank_to_ifsc = NULL;

                        $transaction_charge = NULL;

                        SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);

                        SamraddhChequeIssue::create([

                            'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                            'type' =>6,

                            'sub_type' =>62,

                            'type_id' =>$demandAdviceTaAdvanced->id,

                            'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                            'status' => 1,

                        ]);

                    }elseif($request->mode == 4){

                        $cheque_id = NULL;

                        $cheque_type = NULL;

                        $cheque_no = NULL;

                        $cheque_date = NULL;

                        $cheque_bank_from = NULL;

                        $cheque_bank_from_id = NULL;

                        $cheque_bank_ac_from = NULL;

                        $cheque_bank_ifsc_from = NULL;

                        $cheque_bank_branch_from = NULL;

                        $cheque_bank_to = NULL;

                        $cheque_bank_ac_to = NULL;

                        $cheque_bank_ac_from_id = NULL;

                        $cheque_bank_to_name = NULL;

                        $cheque_bank_to_branch = NULL;

                        $cheque_bank_to_ac_no = NULL;

                        $cheque_bank_to_ifsc = NULL;

                        $transction_no = $request->utr_number;

                        $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                        $transction_bank_from_id = $transction_bank_from_ac_id = getSamraddhBank($request->bank)->id;

                        $transction_bank_ac_from = $request->bank_account_number;

                        $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                        $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                        $transction_bank_branch_from = NULL;

                        $transction_bank_to = NULL;

                        $transction_bank_ac_to = NULL;

                        $transction_bank_to_name = NULL;

                        $transction_bank_to_ac_no = NULL;

                        $transction_bank_to_branch = NULL;

                        $transction_bank_to_ifsc = NULL;

                        $payment_mode = 2;

                        $transaction_charge = $request->neft_charge;

                    }

                    $transction_date = NULL;

                    $entry_date = NULL;

                    $entry_time = NULL;

                    $created_by = 1;

                    $created_by_id = Auth::user()->id;

                    $is_contra = NULL;

                    $contra_id = NULL;

                    $created_at = NULL;

                    $bank_id = NULL;

                    $bank_ac_id = NULL;

                    $bank_id = $request->bank;

                    $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                    $ssb_account_id_to = NULL;

                    $ssb_account_tran_id_to = NULL;

                    $ssb_account_tran_id_from = NULL;

                    $to_bank_name = $demandAdviceTaAdvanced['employee']->bank_name;

                    $to_bank_branch = $demandAdviceTaAdvanced['employee']->bank_name;

                    $to_bank_ac_no = $demandAdviceTaAdvanced['employee']->bank_account_no;

                    $to_bank_ifsc = $demandAdviceTaAdvanced['employee']->bank_ifsc_code;

                    $to_bank_id = NULL;

                    $to_bank_account_id = NULL;

                    $from_bank_name = getSamraddhBank($request->bank)->bank_name;

                    $from_bank_branch = getSamraddhBankAccount($request->bank_account_number)->branch_name;

                    $from_bank_ac_no = $request->bank_account_number;

                    $from_bank_ifsc = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                    $from_bank_id = $request->bank;

                    $from_bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                    $transaction_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                }

                $dayBookRef = CommanController::createBranchDayBookReference($amount);

                $this->employeeSalaryLeaser($demandAdviceTaAdvanced->employee_id,$branch_id,6,$type_id,$employeeCurrentBalance,NULL,$demandAdviceTaAdvanced->advanced_amount,'TA Advanced amount A/C Dr '.$demandAdviceTaAdvanced->advanced_amount.'',$currency_code,'Dr',$payment_mode,1,date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at))),$updated_at=NULL,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transction_no,$transaction_date,$transaction_charge);

                $this->employeeLedgerBackDateDR($demandAdviceTaAdvanced->employee_id,$request->created_at,$demandAdviceTaAdvanced->advanced_amount);

                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$demandAdviceTaAdvanced->advanced_amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,72,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAdvancedAmount,$taAdvancedAmount,$taAdvancedAmount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,29,72,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAdvancedAmount,$taAdvancedAmount,$taAdvancedAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                
                if($request->amount_mode == 0 && ($request->adjustment_level == 1 || $request->adjustment_level == 2)){

                    if($payment_type == 'CR'){
                        $pType = 'DR';
                    }else{
                        $pType = 'CR';
                    }

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$pType,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                }

                if($request->amount_mode == 0 && $request->adjustment_level == 1){

                    $updateBranchCash = $this->updateBranchCashDr($branch_id,$request->created_at,$amount,0);

                    $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id,$request->created_at,$amount,0);       

                }elseif($request->amount_mode == 0 && $request->adjustment_level == 2){

                    $updateBranchCash = $this->updateBranchCashCr($branch_id,$request->created_at,$amount,0);

                    $updateBranchClosing = $this->updateBranchClosingCashCr($branch_id,$request->created_at,$amount,0);

                }

                if($request->amount_mode == 1 && $request->adjustment_level == 1){

                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('account_no',$demandAdviceTaAdvanced['employee']->ssb_account)->first();

                    $paymentMode = 4;

                    $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                    $ssb['saving_account_id']=$ssbAccountDetails->id;

                    $ssb['account_no']=$ssbAccountDetails->account_no;

                    if($request['pay_file_charge'] == 0){

                        $ssb['opening_balance']=$amount+$ssbAccountDetails->balance;

                        $ssb['deposit']=$amount;

                    }else{

                        $ssb['opening_balance']=$amount+$ssbAccountDetails->balance; 

                        $ssb['deposit']=$amount;

                    }



                    $ssb['branch_id']=$demandAdviceTaAdvanced['employee']->branch_id;

                    $ssb['type']=11;

                

                    $ssb['withdrawal']=0;

                    $ssb['description']=$description;

                    $ssb['currency_code']='INR';

                    $ssb['payment_type']='CR';

                    $ssb['payment_mode']=3;

                    $ssb['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                    $ssbAccountTran = SavingAccountTranscation::create($ssb);

                    $saTranctionId = $ssbAccountTran->id;

                    $saToId = $ssbAccountDetails->id;

                    $saToTranctionId = $ssbAccountTran->id;

                    $balance_update=$amount+$ssbAccountDetails->balance;

                    

                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                    $ssbBalance->balance=$balance_update;

                    $ssbBalance->save();



                    $data['saving_account_transaction_id']=$saTranctionId;

                    $data['investment_id']=$demandAdviceTaAdvanced['employee']->id;

                    $data['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                    $satRef = TransactionReferences::create($data);

                    $satRefId = $satRef->id;



                    $amountArraySsb = array('1'=>$amount);

                    

                    $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');



                    $description = $description;

                    $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$request->maturity_amount_payable+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR'); 

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saToTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                    /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saToTranctionId,NULL);*/ 

                    $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToTranctionId,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);  

                }

                foreach ($taAdvanced as $key => $expvalue) {

                    $head1 = 4;

                    $head2 = 86;

                    $head3 = $expvalue->subcategory;

                    $head4 = NULL;

                    $head5 = NULL;

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head3,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAmount,$taAmount,$taAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saToTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$taAmount,$taAmount,$taAmount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/ 

                }

                if($request->amount_mode == 2){

                    if($request->mode == 4){

                        $bankAmount = $amount+$request->neft_charge;

                    }else{

                        $bankAmount = $amount;

                    }

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saToTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                    $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$opening_balance,$bankAmount,$closing_balance,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);

                }

                if($request->amount_mode == 2 && $request->mode == 4){

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saToTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,$head4=NULL,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                    $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,$request->neft_charge);

                } 

            }

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }


        if(count($ssbArray) > 0){
            return redirect()->route('admin.damandadvice.viewtaadvanced')->with('success', 'Ta Advanced Successfully Adjustment AND '.$ssbString.' demand advice not have any ssb account!');
        }else{
            return redirect()->route('admin.damandadvice.viewtaadvanced')->with('success', 'Ta Advanced Successfully Adjustment!');
        }

    }



    /**

     * Update status.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function approveDemandAdvice($id)

    {

        $demandStatus = DemandAdvice::select('status')->where('id',$id)->first();

        $adata = DemandAdvice::findOrFail($id);

        if($demandStatus->status == 0){

            $adata->status = 1;

        }else{

            $adata->status = 0;

        }

        $adata=$adata->save();



        if ($adata) {

            return redirect()->route('admin.demand.advices')->with('success', 'Demand advice approved successfully!');

        } else {

            return back()->with('alert', 'Problem with update demand advice');

        }

    }



    /**

     * Delete demand advice.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function delete($id)

    {

        $demandAdvice = DemandAdvice::select('payment_type')->where('id',$id)->first();

        if($demandAdvice->payment_type == 0){

            $deleteExpense = DemandAdviceExpense::where('demand_advice_id',$id)->delete();

            $deleteDemandAdvice = DemandAdvice::where('id',$id)->delete();

        }elseif($demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 3 || $demandAdvice->payment_type == 4){

            $deleteDemandAdvice = DemandAdvice::where('id',$id)->delete();

        }

        return back()->with('success', 'Demand advice deleted successfully!');

    }

    /**

     * Delete demand advice.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function deleteMultiple(Request $request)

    {

        $sRecord = explode(',', $request['select_deleted_records']);  

        foreach ($sRecord as $key => $value) {

            $demandAdvice = DemandAdvice::select('payment_type')->where('id',$value)->first();



            if($demandAdvice->payment_type == 0){

                $deleteExpense = DemandAdviceExpense::where('demand_advice_id',$value)->delete();

                $deleteDemandAdvice = DemandAdvice::where('id',$value)->delete();

            }elseif($demandAdvice->payment_type == 1 || $demandAdvice->payment_type == 2 || $demandAdvice->payment_type == 3){

                $deleteDemandAdvice = DemandAdvice::where('id',$value)->delete();

            }

        }      

        return back()->with('success', 'Demand advice deleted successfully!');

    }

    /**

     * Get employee code by employee name.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    public function getSsbDetails(Request $request)

    {

        $account_number = $request->val;

        $ssbDetails = SavingAccount::with('ssbMember')->where('account_no',$account_number)->first();

        $return_array = compact('ssbDetails');

        return json_encode($return_array); 

    }

    /**

     * Get employee details by employee code.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    public function getEmployeeDetails(Request $request)

    {

        $employee_code = $request->employee_code;

        $employeeDetails = Employee::select('id','employee_name','mobile_no','ssb_account','bank_name','bank_account_no','bank_ifsc_code')->where('employee_code',$employee_code)->where('status',1)->get();

        $resCount = count($employeeDetails);

        $return_array = compact('employeeDetails','resCount');

        return json_encode($return_array); 

    }

    /**

     * Get owner details by owner name.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    public function getOwnerDetails(Request $request)

    {

        $ownerId = $request->val;

        $ownerDetails = RentLiability::select('owner_mobile_number','owner_ssb_number','owner_bank_name','owner_bank_account_number','owner_bank_ifsc_code')->where('id',$ownerId)->where('status',0)->first();

        $return_array = compact('ownerDetails');

        return json_encode($return_array); 

    }

    /**

     * Get investment details by account number.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    // public function getInvestmentDetails(Request $request)

    // {

    //     $investmentAccount = $request->val;

    //     $type = $request->type;

    //     $subtype = $request->subtype;

    //     $cDate = date("Y-m-d");

    //     if($type == 4 && $subtype == 0){

    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->where('plan_id',6)->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();


    //         if($investmentDetails){

    //             $message = '';

    //             $status = 200;

    //         }else{

    //             $message = 'Record Not Found!';

    //             $status = 400;

    //         }



    //     }elseif($type == 4 && $subtype == 1){

    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();

    //         if($investmentDetails){

    //             $message = '';

    //             $status = 200;

    //         }else{

    //             $message = 'Record Not Found!';

    //             $status = 400;

    //         }



    //     }elseif($type == 2 && $subtype == 0){


    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first(); 

    //         if($investmentDetails){

    //             $maturityDate =  date('Y-m-d', strtotime($investmentDetails->created_at. ' + '.($investmentDetails->tenure).' year'));

    //             $currentDate=date_create($cDate);

    //             $diff = strtotime($maturityDate) - strtotime($cDate);
    //             $daydiff = abs(round($diff / 86400));

    //             if($cDate < $maturityDate && $daydiff > 5){

    //                 $message = 'Record Not Match With Maturity Conditions!';

    //                 $status = 500;

    //             }else{

    //                 $message = '';

    //                 $status = 200;

    //             }

    //         }else{

    //             $message = 'Record Not Found!';

    //             $status = 400;

    //         }

    //     }elseif($type == 2 && $subtype == 1){

    //         $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereIn('plan_id',[4,5])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first(); 



    //         if($investmentDetails){

    //             $message = '';

    //             $status = 200;

    //         }else{

    //             $message = 'Record Not Found!';

    //             $status = 400;

    //         }

    //     }  


    //     if($investmentDetails && $status == 200){

    //         $demandAdviceRecord = DemandAdvice::where('investment_id',$investmentDetails->id)->count();

    //         if($demandAdviceRecord > 0){
    //             $isDefaulter = 0;
    //             $finalAmount = 0;
    //             $message = 'Already request created for this paln!';

    //             $status = 500;

    //         }else{

    //             $mInvestment = $investmentDetails;

    //             $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));

    //             $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->orderby('created_at', 'asc')->get();



    //             $keyVal = 0;

    //             $cInterest = 0;

    //             $regularInterest = 0; 

    //             $total = 0;

    //             $collection = 0;

    //             $monthly = array(10,11);

    //             $daily = array(7);

    //             $preMaturity = array(4,5);

    //             $fixed = array(8,9);

    //             $samraddhJeevan = array(2,6);

    //             $moneyBack = array(3);

    //             $totalDeposit = 0;

    //             $totalInterestDeposit = 0;


                
    //             if(in_array($mInvestment->plan_id, $monthly)){

    //                 if($investmentData){

    //                     $investmentMonths = $mInvestment->tenure*12;
    //                     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

    //                     for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cuurentInterest = $mInvestment->interest_rate;
    //                             $totalDeposit = $totalInvestmentAmount;

    //                             $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');

    //                             $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');

    //                             $d1 = explode('-',$mInvestment->created_at);
    //                             $d2 = explode('-',$nDate);

    //                             $ts1 = strtotime($mInvestment->created_at);
    //                             $ts2 = strtotime($nDate);

    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);

    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);

    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

    //                             if($cMonth > $d2[1] && $cYear > $d2[0]){

    //                                 if($previousRecord){
    //                                     $previousDate = explode('-',$previousRecord);
    //                                     $previousMonth = $previousDate[1];
    //                                     if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
    //                                         $defaulterInterest = 1.50;
    //                                         $isDefaulter = 1;
                                            
    //                                     }else{
    //                                         $defaulterInterest = 0;
    //                                         $isDefaulter = 0;
    //                                     }
    //                                 }else{
    //                                     $defaulterInterest = 0;
    //                                     $isDefaulter = 0;
    //                                 }
    //                             }else{
    //                                 $defaulterInterest = 0;
    //                                 $isDefaulter = 1;
    //                             }

    //                             $cfAmount = Memberinvestments::where('id',$val->id)->first();
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
    //                                 $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
    //                                 Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                 $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');

    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
    //                                 if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                 
    //                                     $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                     $collection = (int) $totalDeposit+(int) $pendingAmount;

    //                                 }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

    //                                 }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }

    //                                 $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                    $aviAmount = $checkAmount; 

    //                                    $total = $total+$checkAmount;
    //                                     if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                     $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');

    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0; 
    //                                     $addInterest = 0; 
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }
    //                     }

    //                     $interstAmount = round($totalInterestDeposit);

    //                     if($request->type == 2){
    //                         $finalAmount = round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100));                         
    //                     }else{
    //                         $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                         $investAmount = $totalDeposit;
    //                     }
    //                 }else{

    //                     $interstAmount =  0;

    //                     $isDefaulter = 1;

    //                     $finalAmount = 0;

    //                     $investAmount = 0;
    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $daily)){

    //                 if($investmentData){

    //                         $cMonth = date('m');

    //                         $cYear = date('Y');

    //                         $cuurentInterest = $mInvestment->interest_rate;

    //                         $tenureMonths = $mInvestment->tenure*12;

    //                         $i = 0;

    //                         for ($i = 0; $i <= $tenureMonths; $i++){

    //                             /*$integer = $i+1;
    //                             $createdMonth = date("m", strtotime($mInvestment->created_at));
    //                             $createdYear = date("Y", strtotime($mInvestment->created_at)); 
    //                             if($createdMonth > $integer){
    //                                 $month = $createdMonth+$i;
    //                                 $year = $createdYear;
    //                             }elseif($integer == $createdMonth){
    //                                 $month = 1;
    //                                 $year = $createdYear+1;
    //                             }elseif(($i+1) > $createdMonth){
    //                                 $month = ($integer-$createdMonth)+1;
    //                                 $year = $createdYear+1;
    //                             }*/
    //                             //$month = date("m", strtotime("".$i." month", strtotime($mInvestment->created_at))); 
    //                             //$year = date("Y", strtotime("".$i." month", strtotime($mInvestment->created_at)));  

    //                             $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($mInvestment->created_at))); 

    //                             $implodeArray = explode('-',$newdate);

    //                             $year = $implodeArray[0];
    //                             //$month = $implodeArray[1];

    //                             $cdate = $mInvestment->created_at;
    //                             $cexplodedate = explode('-',$mInvestment->created_at);
    //                             if(($cexplodedate[1]+$i) > 12){
    //                                 $month = ($cexplodedate[1]+$i)-12;
    //                             }else{
    //                                 $month = $cexplodedate[1]+$i;
    //                             }

    //                             if(($i+1) == 13){
    //                                 $fRecord = Daybook::where('investment_id', $mInvestment->id)
    //                                 ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();
    //                                 if($fRecord){
    //                                     $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
    //                                 }else{
    //                                    $total = Daybook::where('investment_id', $mInvestment->id)
    //                                 ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit'); 
    //                                 }
    //                             }else{
    //                                 $total = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                             }

                                

    //                             $totalDeposit = $totalDeposit+$total;



    //                             $countDays = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->count();



    //                             /*if($cMonth > $month && $cYear > $year){

    //                                 if($countDays < 25 && ($mInvestment->deposite_amount*25) > $total){

    //                                     $defaulterInterest = 1.50;

    //                                     $isDefaulter = 1;

    //                                 }else{

    //                                     $defaulterInterest = 0;

    //                                     $isDefaulter = 0;

    //                                 }

    //                             }else{

    //                                 $defaulterInterest = 0;

    //                                 $isDefaulter = 0;

    //                             }*/

    //                             if(($mInvestment->deposite_amount*25) > $total){

    //                                 $defaulterInterest = 1.50;

    //                                 $isDefaulter = 1;

    //                             }else{

    //                                 $defaulterInterest = 0;

    //                                 $isDefaulter = 0;

    //                             }

    //                             if($tenureMonths == 12){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
    //                             }elseif($tenureMonths == 24){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
    //                             }elseif($tenureMonths == 36){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
    //                             }elseif($tenureMonths == 60){
    //                                 $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
    //                             }
    //                             if(($tenureMonths-$i) == 0){
    //                                 $interest = 0;
    //                             }
    //                             $totalInterestDeposit = $totalInterestDeposit+$interest;

    //                         }

    //                     $interstAmount = round($totalInterestDeposit);

    //                     $finalAmount = round($totalDeposit+$totalInterestDeposit);

    //                     $investAmount = $totalDeposit;

    //                 }else{

    //                     $interstAmount =  0;

    //                     $finalAmount = 0;

    //                     $isDefaulter = 1;

    //                     $investAmount = 0;

    //                 }
    //             }elseif(in_array($mInvestment->plan_id, $preMaturity)){
    //                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                 if($investmentData){
    //                     $cDate = date('Y-m-d');
    //                     $ts1 = strtotime($mInvestment->created_at);
    //                     $ts2 = strtotime($cDate);
    //                     $year1 = date('Y', $ts1);
    //                     $year2 = date('Y', $ts2);
    //                     $month1 = date('m', $ts1);
    //                     $month2 = date('m', $ts2);
    //                     $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

    //                     if($mInvestment->plan_id == 4){
    //                         if($monthDiff >= 0 && $monthDiff <= 36){
    //                             $cuurentInterest = 8;
    //                         }else if($monthDiff >= 37 && $monthDiff <= 48){
    //                             $cuurentInterest = 8.25;
    //                         }else if($monthDiff >= 49 && $monthDiff <= 60){
    //                             $cuurentInterest = 8.50;
    //                         }else if($monthDiff >= 61 && $monthDiff <= 72){
    //                             $cuurentInterest = 8.75;
    //                         }else if($monthDiff >= 73 && $monthDiff <= 84){
    //                             $cuurentInterest = 9;
    //                         }else if($monthDiff >= 85 && $monthDiff <= 96){
    //                             $cuurentInterest = 9.50;
    //                         }else if($monthDiff >= 97 && $monthDiff <= 108){
    //                             $cuurentInterest = 10;
    //                         }else if($monthDiff >= 109 && $monthDiff <= 120){
    //                             $cuurentInterest = 11;
    //                         }else{
    //                             $cuurentInterest = 11;
    //                         }
    //                     }elseif($mInvestment->plan_id == 5){
    //                         if($monthDiff >= 0 && $monthDiff <= 12){
    //                             $cuurentInterest = 5;
    //                         }else if($monthDiff >= 12 && $monthDiff <= 24){
    //                             $cuurentInterest = 6;
    //                         }else if($monthDiff >= 24 && $monthDiff <= 36){
    //                             $cuurentInterest = 6.50;
    //                         }else if($monthDiff >= 36 && $monthDiff <= 48){
    //                             $cuurentInterest = 7;
    //                         }else if($monthDiff >= 48 && $monthDiff <= 60){
    //                             $cuurentInterest = 9;
    //                         }else{
    //                             $cuurentInterest = 9;
    //                         }
    //                     }


    //                     if($mInvestment->plan_id == 4){
    //                         /*if($cDate < $maturity_date && $monthDiff != 120){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;

    //                         }*/

    //                         $defaulterInterest = 0;
    //                         $isDefaulter = 0;

    //                         $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                         $year = $monthDiff / 12;
    //                         $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);
    //                     }else{
    //                         if($cDate < $maturity_date && $monthDiff != 60){
    //                             $defaulterInterest = 1.50;
    //                             $isDefaulter = 1;
    //                         }else{
    //                             $defaulterInterest = 0;
    //                             $isDefaulter = 0;

    //                         }
                            
    //                         $irate = ($cuurentInterest-$defaulterInterest) / 1;
    //                         $year = $monthDiff / 12;
    //                         $maturity=0;
    //                         $freq = 4;
    //                         for($i=1; $i<=$monthDiff;$i++){
    //                             $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
    //                             $maturity = $maturity+$rmaturity;
    //                         }
    //                         if($maturity > ($mInvestment->deposite_amount*$monthDiff)){
    //                             $result =  $maturity-($mInvestment->deposite_amount*$monthDiff);
    //                         }else{
    //                             $result =  $maturity;
    //                         }
    //                     }

    //                     $interstAmount = round($result);
    //                     $finalAmount = round($totalInvestmentAmount+$interstAmount);
    //                     $investAmount = $totalInvestmentAmount;
    //                 }else{
    //                     $interstAmount =  0;
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                 } 
    //             }elseif(in_array($mInvestment->plan_id, $fixed)){
    //                 if($investmentData){

    //                         $cDate = date('Y-m-d');

    //                         $cYear = date('Y');

    //                         $cuurentInterest = $mInvestment->interest_rate;



    //                         if($cDate < $maturity_date){

    //                             $defaulterInterest = 1.50;

    //                             $isDefaulter = 1;

    //                         }else{

    //                             $defaulterInterest = 0;

    //                             $isDefaulter = 0;

    //                         }



    //                     $irate = ($cuurentInterest-$defaulterInterest) / 1;

    //                     $year = $mInvestment->tenure*12;

    //                     $interstAmount =  round(( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount));

    //                     $finalAmount = round($mInvestment->deposite_amount+$interstAmount);

    //                     $investAmount = $mInvestment->deposite_amount;

    //                 }else{

    //                     $interstAmount =  0;

    //                     $finalAmount = 0;

    //                     $isDefaulter = 1;

    //                     $investAmount = 0;

    //                 }            
    //             }elseif(in_array($mInvestment->plan_id, $samraddhJeevan)){

    //                 if($investmentData){
    //                     $cDate = date('Y-m-d');
    //                     $investmentMonths = $mInvestment->tenure*12;
    //                     $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

    //                     $isDefaulter = 0;

    //                     if($cDate >= $maturity_date){

    //                         $defaulterInterest = 6;

    //                         $isDefaulter = 0;

    //                         $depositAmount = ($mInvestment->deposite_amount*12)*$mInvestment->tenure;

    //                         $result = $defaulterInterest*($depositAmount) / 100;

    //                         $interstAmount = number_format((float)$result, 2, '.', '');

    //                         $finalAmount = round($depositAmount+$result);

    //                         $investAmount = $depositAmount;
    //                     }elseif($cDate < $maturity_date){
    //                         for ($i=1; $i <= $investmentMonths ; $i++){

    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             if($mInvestment->plan_id == 2){
    //                                 $cuurentInterest = $val->interest_rate;
    //                             }elseif($mInvestment->plan_id == 6){
    //                                 $cuurentInterest = 11;
    //                             }
    //                             $totalDeposit = $totalInvestmentAmount;

    //                             $d1 = explode('-',$val->created_at);
    //                             $d2 = explode('-',$nDate);

    //                             $ts1 = strtotime($val->created_at);
    //                             $ts2 = strtotime($nDate);

    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);

    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);

    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

    //                             $cfAmount = Memberinvestments::where('id',$val->id)->first();
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
    //                                 $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
    //                                 Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 12 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');
    //                                 $totalInterestDeposit = $totalInterestDeposit+($interest);
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
    //                                 if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                 
    //                                     $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
    //                                     $collection = (int) $totalDeposit+(int) $pendingAmount;

    //                                 }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

    //                                 }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

    //                                     Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
    //                                     $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
    //                                 }


    //                                 $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                    $aviAmount = $checkAmount; 

    //                                    $total = $total+$checkAmount;
    //                                     if($monthDiff % 12 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');

    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0; 
    //                                     $a = 0;
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+($interest);
    //                             }
    //                         }
    //                         $interstAmount = round($totalInterestDeposit);
    //                         $finalAmount = round($totalDeposit+$totalInterestDeposit);
    //                         $investAmount = $totalDeposit;
    //                     }
    //                 }else{
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                     $interstAmount = 0;
    //                 } 
    //             }elseif(in_array($mInvestment->plan_id, $moneyBack)){
    //                 if($investmentData){
    //                     $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
    //                     $years = floor($diff / (365*60*60*24));

    //                     if($cDate >= $maturity_date){
    //                         $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                         $maturityAmount = getMoneyBackAmount($mInvestment->id);

    //                         $fAmount = $maturityAmount->available_amount;
    //                         $isDefaulter = 0;
    //                         $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');

    //                         $interstAmount = ($fAmount+$refundAmount)-$totalInvestmentAmount;
    //                         $finalAmount = $fAmount-$interstAmount;
    //                         $investAmount = $finalAmount;

    //                     }elseif($cDate < $maturity_date){
                            
    //                         $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
    //                         $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();

    //                         $investmentMonths = $mInvestment->tenure*12;

                            

    //                         if($mInvestment->last_deposit_to_ssb_date){

    //                             $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
    //                             $ts1 = strtotime($sDate);
    //                             $ts2 = strtotime($cDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $investmentMonths = $monthDiff;

    //                             $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>=',$mInvestment->last_deposit_to_ssb_date)->sum('deposit');
    //                         }else{
    //                             $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

    //                             $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
    //                             $ts1 = strtotime($sDate);
    //                             $ts2 = strtotime($cDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $investmentMonths = $monthDiff;
    //                         }

                            

    //                         for ($i=1; $i <= $investmentMonths ; $i++){
    //                             $val = $mInvestment;
    //                             $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
    //                             $cMonth = date('m');
    //                             $cYear = date('Y');
    //                             $cuurentInterest = $mInvestment->interest_rate;
    //                             $totalDeposit = $totalInvestmentAmount;
    //                             $ts1 = strtotime($mInvestment->created_at);
    //                             $ts2 = strtotime($nDate);
    //                             $year1 = date('Y', $ts1);
    //                             $year2 = date('Y', $ts2);
    //                             $month1 = date('m', $ts1);
    //                             $month2 = date('m', $ts2);
    //                             $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
    //                             $defaulterInterest = 0;
    //                             if($val->deposite_amount*$monthDiff <= $totalDeposit){
    //                                 $aviAmount = $val->deposite_amount;
    //                                 $total = $total+$val->deposite_amount;
    //                                 if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                     $total = $total+$regularInterest;
    //                                     $cInterest = $regularInterest;
    //                                 }else{
    //                                     $total = $total;
    //                                     $cInterest = 0;
    //                                 }
    //                                 $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                 $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                 $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                 $interest = number_format((float)$a, 2, '.', '');

    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
    //                                 $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
    //                                 if($checkAmount > 0){
    //                                    $aviAmount = $checkAmount; 

    //                                    $total = $total+$checkAmount;
    //                                     if($monthDiff % 3 == 0 && $monthDiff != 0){
    //                                         $total = $total+$regularInterest;
    //                                         $cInterest = $regularInterest;
    //                                     }else{
    //                                         $total = $total;
    //                                         $cInterest = 0;
    //                                     }
    //                                     $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
    //                                     $addInterest = ($cuurentInterest-$defaulterInterest);
    //                                     $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
    //                                     $interest = number_format((float)$a, 2, '.', '');

    //                                 }else{
    //                                     $aviAmount = 0;
    //                                     $total = 0;
    //                                     $cuurentInterest = 0;
    //                                     $interest = 0; 
    //                                     $addInterest = 0; 
    //                                 }
    //                                 $totalInterestDeposit = $totalInterestDeposit+$interest;
    //                             }
    //                         }

    //                         if($depositInterest){
    //                             $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
    //                             $fAmount = round($totalDeposit+$totalInterestDeposit+$availableAmountFd);
    //                         }else{
    //                             $fAmount = round($totalDeposit+$totalInterestDeposit);
    //                         }
                            
                            
    //                         $isDefaulter = 0;
    //                         $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');

    //                         if($refundAmount){
    //                             $interstAmount = round(($fAmount+$refundAmount)-$totaldepositAmount);
    //                             $finalAmount = $fAmount-$interstAmount;
    //                             $investAmount = $finalAmount;
    //                         }else{
    //                             $interstAmount = $fAmount-$totaldepositAmount;
    //                             $finalAmount = $fAmount-$interstAmount;
    //                             $investAmount = $finalAmount;
    //                         }
    //                     }
    //                 }else{
    //                     $finalAmount = 0;
    //                     $isDefaulter = 1;
    //                     $investAmount = 0;
    //                     $interstAmount = 0;
    //                 } 
    //             }    

    //         }

    //     } else{
    //             $isDefaulter = 0;
    //             $finalAmount = 0;
    //     }

    //     $return_array = compact('investmentDetails','isDefaulter','finalAmount','message','status');

    //     return json_encode($return_array); 

    // }

    public function getInvestmentDetails(Request $request)

    {

        $investmentAccount = $request->val;

        $type = $request->type;

        $subtype = $request->subtype;

        $cDate = date("Y-m-d");

        if($type == 4 && $subtype == 0){

            $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->where('plan_id',6)->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();


            if($investmentDetails){

                $message = '';

                $status = 200;

            }else{

                $message = 'Record Not Found!';

                $status = 400;

            }



        }elseif($type == 4 && $subtype == 1){

            $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first();

            if($investmentDetails){

                $message = '';

                $status = 200;

            }else{

                $message = 'Record Not Found!';

                $status = 400;

            }



        }elseif($type == 2 && $subtype == 0){


            $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereNotIn('plan_id',[1,6])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first(); 

            if($investmentDetails){

               $existInvestment = \App\Models\Loaninvestmentmembers::where('plan_id',$investmentDetails->id)->first();

               if($existInvestment)
               {
                    $checkloanClose = Memberloans::where('id',$existInvestment->member_loan_id)->where('status','!=',3)->first();

                   
                   if($existInvestment && $checkloanClose)
                   {
                         $message = 'Deposite Against Loan Please Close the Loan!';

                        $status = 500;
                   }

                    else{

                        $maturityDate =  date('Y-m-d', strtotime($investmentDetails->created_at. ' + '.($investmentDetails->tenure).' year'));

                        $currentDate=date_create($cDate);

                        $diff = strtotime($maturityDate) - strtotime($cDate);
                        $daydiff = abs(round($diff / 86400));

                        if($cDate < $maturityDate && $daydiff > 5555555555555555){

                            $message = 'Record Not Match With Maturity Conditions!';

                            $status = 500;

                        }else{

                            $message = '';

                            $status = 200;

                        }


                    }
               }
              
                
            }else{

                $message = 'Record Not Found!';

                $status = 400;

            }

        }elseif($type == 2 && $subtype == 1){

            $investmentDetails = Memberinvestments::with('investmentNomiees','plan','member','ssb','memberBankDetail','investmentNomiees')->where('account_number',$investmentAccount)->where('account_number', 'not like', "%R-%")->whereIn('plan_id',[4,5])->where('is_mature',1)->where('investment_correction_request',0)->where('renewal_correction_request',0)->first(); 



            if($investmentDetails){

               $existInvestment = \App\Models\Loaninvestmentmembers::where('plan_id',$investmentDetails->id)->first();
               if($existInvestment)
               {
                    $checkloanClose = Memberloans::where('id',$existInvestment->member_loan_id)->where('status','!=',3)->first();

                   
                   if($existInvestment && $checkloanClose)
                   {
                        $message = 'Loan Ongoing on this Plan!';

                        $status = 500;
                   }
                   else{
                    $message = '';

                    $status = 200;
                } 
               }
                  
                

            }else{

                $message = 'Record Not Found!';

                $status = 400;

            }

        }  


        if($investmentDetails ){

            $demandAdviceRecord = DemandAdvice::where('investment_id',$investmentDetails->id)->count();

            if($demandAdviceRecord > 0){
                $isDefaulter = 0;
                $finalAmount = 0;
                $message = 'Already request created for this paln!';

                $status = 500;

            }else{

                $mInvestment = $investmentDetails;

                $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));

                $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->orderby('created_at', 'asc')->get();



                $keyVal = 0;

                $cInterest = 0;

                $regularInterest = 0; 

                $total = 0;

                $collection = 0;

                $monthly = array(10,11);

                $daily = array(7);

                $preMaturity = array(4,5);

                $fixed = array(8,9);

                $samraddhJeevan = array(2,6);

                $moneyBack = array(3);

                $totalDeposit = 0;

                $totalInterestDeposit = 0;


                
                if(in_array($mInvestment->plan_id, $monthly)){

                    if($investmentData){

                        $investmentMonths = $mInvestment->tenure*12;
                        $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                        for ($i=1; $i <= $investmentMonths ; $i++){
                                $val = $mInvestment;
                                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
                                $cMonth = date('m');
                                $cYear = date('Y');
                                $cuurentInterest = $mInvestment->interest_rate;
                                $totalDeposit = $totalInvestmentAmount;

                                $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');

                                $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');

                                $d1 = explode('-',$mInvestment->created_at);
                                $d2 = explode('-',$nDate);

                                $ts1 = strtotime($mInvestment->created_at);
                                $ts2 = strtotime($nDate);

                                $year1 = date('Y', $ts1);
                                $year2 = date('Y', $ts2);

                                $month1 = date('m', $ts1);
                                $month2 = date('m', $ts2);

                                $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                                if($cMonth > $d2[1] && $cYear > $d2[0]){

                                    if($previousRecord){
                                        $previousDate = explode('-',$previousRecord);
                                        $previousMonth = $previousDate[1];
                                        if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
                                            $defaulterInterest = 1.50;
                                            $isDefaulter = 1;
                                            
                                        }else{
                                            $defaulterInterest = 0;
                                            $isDefaulter = 0;
                                        }
                                    }else{
                                        $defaulterInterest = 0;
                                        $isDefaulter = 0;
                                    }
                                }else{
                                    $defaulterInterest = 0;
                                    $isDefaulter = 1;
                                }

                                $cfAmount = Memberinvestments::where('id',$val->id)->first();
                                if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                    $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                                    $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                                    Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

                                    $aviAmount = $val->deposite_amount;
                                    $total = $total+$val->deposite_amount;
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
                                }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                                    $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                                    if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                 
                                        $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                        $collection = (int) $totalDeposit+(int) $pendingAmount;

                                    }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                        $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

                                    }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                        $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                                    }

                                    $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
                                    if($checkAmount > 0){
                                       $aviAmount = $checkAmount; 

                                       $total = $total+$checkAmount;
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

                                    }else{
                                        $aviAmount = 0;
                                        $total = 0;
                                        $cuurentInterest = 0;
                                        $interest = 0; 
                                        $addInterest = 0; 
                                    }
                                    $totalInterestDeposit = $totalInterestDeposit+$interest;
                                }
                        }

                        $interstAmount = round($totalInterestDeposit);

                        if($request->type == 2){
                            $finalAmount = round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100));                         
                        }else{
                            $finalAmount = round($totalDeposit+$totalInterestDeposit);
                            $investAmount = $totalDeposit;
                        }
                    }else{

                        $interstAmount =  0;

                        $isDefaulter = 1;

                        $finalAmount = 0;

                        $investAmount = 0;
                    }
                }elseif(in_array($mInvestment->plan_id, $daily)){

                    if($investmentData){

                            $cMonth = date('m');

                            $cYear = date('Y');

                            $cuurentInterest = $mInvestment->interest_rate;

                            $tenureMonths = $mInvestment->tenure*12;

                            $i = 0;

                            for ($i = 0; $i <= $tenureMonths; $i++){

                                /*$integer = $i+1;
                                $createdMonth = date("m", strtotime($mInvestment->created_at));
                                $createdYear = date("Y", strtotime($mInvestment->created_at)); 
                                if($createdMonth > $integer){
                                    $month = $createdMonth+$i;
                                    $year = $createdYear;
                                }elseif($integer == $createdMonth){
                                    $month = 1;
                                    $year = $createdYear+1;
                                }elseif(($i+1) > $createdMonth){
                                    $month = ($integer-$createdMonth)+1;
                                    $year = $createdYear+1;
                                }*/
                                //$month = date("m", strtotime("".$i." month", strtotime($mInvestment->created_at))); 
                                //$year = date("Y", strtotime("".$i." month", strtotime($mInvestment->created_at)));  

                                $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($mInvestment->created_at))); 

                                $implodeArray = explode('-',$newdate);

                                $year = $implodeArray[0];
                                //$month = $implodeArray[1];

                                $cdate = $mInvestment->created_at;
                                $cexplodedate = explode('-',$mInvestment->created_at);
                                if(($cexplodedate[1]+$i) > 12){
                                    $month = ($cexplodedate[1]+$i)-12;
                                }else{
                                    $month = $cexplodedate[1]+$i;
                                }

                                if(($i+1) == 13){
                                    $fRecord = Daybook::where('investment_id', $mInvestment->id)
                                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();
                                    if($fRecord){
                                        $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
                                    }else{
                                       $total = Daybook::where('investment_id', $mInvestment->id)
                                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit'); 
                                    }
                                }else{
                                    $total = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->sum('deposit');
                                }

                                

                                $totalDeposit = $totalDeposit+$total;



                                $countDays = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->count();



                                /*if($cMonth > $month && $cYear > $year){

                                    if($countDays < 25 && ($mInvestment->deposite_amount*25) > $total){

                                        $defaulterInterest = 1.50;

                                        $isDefaulter = 1;

                                    }else{

                                        $defaulterInterest = 0;

                                        $isDefaulter = 0;

                                    }

                                }else{

                                    $defaulterInterest = 0;

                                    $isDefaulter = 0;

                                }*/

                                if(($mInvestment->deposite_amount*25) > $total){

                                    $defaulterInterest = 1.50;

                                    $isDefaulter = 1;

                                }else{

                                    $defaulterInterest = 0;

                                    $isDefaulter = 0;

                                }

                                if($tenureMonths == 12){
                                    $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
                                }elseif($tenureMonths == 24){
                                    $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
                                }elseif($tenureMonths == 36){
                                    $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
                                }elseif($tenureMonths == 60){
                                    $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
                                }
                                if(($tenureMonths-$i) == 0){
                                    $interest = 0;
                                }
                                $totalInterestDeposit = $totalInterestDeposit+$interest;

                            }

                        $interstAmount = round($totalInterestDeposit);

                        $finalAmount = round($totalDeposit+$totalInterestDeposit);

                        $investAmount = $totalDeposit;

                    }else{

                        $interstAmount =  0;

                        $finalAmount = 0;

                        $isDefaulter = 1;

                        $investAmount = 0;

                    }
                }elseif(in_array($mInvestment->plan_id, $preMaturity)){
                    $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
                    if($investmentData){
                        $cDate = date('Y-m-d');
                        $ts1 = strtotime($mInvestment->created_at);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                        if($mInvestment->plan_id == 4){
                            if($monthDiff >= 0 && $monthDiff <= 36){
                                $cuurentInterest = 8;
                            }else if($monthDiff >= 37 && $monthDiff <= 48){
                                $cuurentInterest = 8.25;
                            }else if($monthDiff >= 49 && $monthDiff <= 60){
                                $cuurentInterest = 8.50;
                            }else if($monthDiff >= 61 && $monthDiff <= 72){
                                $cuurentInterest = 8.75;
                            }else if($monthDiff >= 73 && $monthDiff <= 84){
                                $cuurentInterest = 9;
                            }else if($monthDiff >= 85 && $monthDiff <= 96){
                                $cuurentInterest = 9.50;
                            }else if($monthDiff >= 97 && $monthDiff <= 108){
                                $cuurentInterest = 10;
                            }else if($monthDiff >= 109 && $monthDiff <= 120){
                                $cuurentInterest = 11;
                            }else{
                                $cuurentInterest = 11;
                            }
                        }elseif($mInvestment->plan_id == 5){
                            if($monthDiff >= 0 && $monthDiff <= 12){
                                $cuurentInterest = 5;
                            }else if($monthDiff >= 12 && $monthDiff <= 24){
                                $cuurentInterest = 6;
                            }else if($monthDiff >= 24 && $monthDiff <= 36){
                                $cuurentInterest = 6.50;
                            }else if($monthDiff >= 36 && $monthDiff <= 48){
                                $cuurentInterest = 7;
                            }else if($monthDiff >= 48 && $monthDiff <= 60){
                                $cuurentInterest = 9;
                            }else{
                                $cuurentInterest = 9;
                            }
                        }


                        if($mInvestment->plan_id == 4){
                            /*if($cDate < $maturity_date && $monthDiff != 120){
                                $defaulterInterest = 1.50;
                                $isDefaulter = 1;
                            }else{
                                $defaulterInterest = 0;
                                $isDefaulter = 0;

                            }*/

                            $defaulterInterest = 0;
                            $isDefaulter = 0;

                            $irate = ($cuurentInterest-$defaulterInterest) / 1;
                            $year = $monthDiff / 12;
                            $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);
                        }else{
                            if($cDate < $maturity_date && $monthDiff != 60){
                                $defaulterInterest = 1.50;
                                $isDefaulter = 1;
                            }else{
                                $defaulterInterest = 0;
                                $isDefaulter = 0;

                            }
                            
                            $irate = ($cuurentInterest-$defaulterInterest) / 1;
                            $year = $monthDiff / 12;
                            $maturity=0;
                            $freq = 4;
                            for($i=1; $i<=$monthDiff;$i++){
                                $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
                                $maturity = $maturity+$rmaturity;
                            }
                            if($maturity > ($mInvestment->deposite_amount*$monthDiff)){
                                $result =  $maturity-($mInvestment->deposite_amount*$monthDiff);
                            }else{
                                $result =  $maturity;
                            }
                        }

                        $interstAmount = round($result);
                        $finalAmount = round($totalInvestmentAmount+$interstAmount);
                        $investAmount = $totalInvestmentAmount;
                    }else{
                        $interstAmount =  0;
                        $finalAmount = 0;
                        $isDefaulter = 1;
                        $investAmount = 0;
                    } 
                }elseif(in_array($mInvestment->plan_id, $fixed)){
                    if($investmentData){

                            $cDate = date('Y-m-d');

                            $cYear = date('Y');

                            $cuurentInterest = $mInvestment->interest_rate;



                            if($cDate < $maturity_date){

                                $defaulterInterest = 1.50;

                                $isDefaulter = 1;

                            }else{

                                $defaulterInterest = 0;

                                $isDefaulter = 0;

                            }



                        $irate = ($cuurentInterest-$defaulterInterest) / 1;

                        $year = $mInvestment->tenure*12;

                        $interstAmount =  round(( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount));

                        $finalAmount = round($mInvestment->deposite_amount+$interstAmount);

                        $investAmount = $mInvestment->deposite_amount;

                    }else{

                        $interstAmount =  0;

                        $finalAmount = 0;

                        $isDefaulter = 1;

                        $investAmount = 0;

                    }            
                }elseif(in_array($mInvestment->plan_id, $samraddhJeevan)){

                    if($investmentData){
                        $cDate = date('Y-m-d');
                        $investmentMonths = $mInvestment->tenure*12;
                        $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                        $isDefaulter = 0;

                        if($cDate >= $maturity_date){

                            $defaulterInterest = 6;

                            $isDefaulter = 0;

                            $depositAmount = ($mInvestment->deposite_amount*12)*$mInvestment->tenure;

                            $result = $defaulterInterest*($depositAmount) / 100;

                            $interstAmount = number_format((float)$result, 2, '.', '');

                            $finalAmount = round($depositAmount+$result);

                            $investAmount = $depositAmount;
                        }elseif($cDate < $maturity_date){
                            for ($i=1; $i <= $investmentMonths ; $i++){

                                $val = $mInvestment;
                                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
                                $cMonth = date('m');
                                $cYear = date('Y');
                                $cMonth = date('m');
                                $cYear = date('Y');
                                if($mInvestment->plan_id == 2){
                                    $cuurentInterest = $val->interest_rate;
                                }elseif($mInvestment->plan_id == 6){
                                    $cuurentInterest = 11;
                                }
                                $totalDeposit = $totalInvestmentAmount;

                                $d1 = explode('-',$val->created_at);
                                $d2 = explode('-',$nDate);

                                $ts1 = strtotime($val->created_at);
                                $ts2 = strtotime($nDate);

                                $year1 = date('Y', $ts1);
                                $year2 = date('Y', $ts2);

                                $month1 = date('m', $ts1);
                                $month2 = date('m', $ts2);

                                $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                                $cfAmount = Memberinvestments::where('id',$val->id)->first();
                                if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                    $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                                    $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                                    Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

                                    $aviAmount = $val->deposite_amount;
                                    $total = $total+$val->deposite_amount;
                                    if($monthDiff % 12 == 0 && $monthDiff != 0){
                                        $total = $total+$regularInterest;
                                        $cInterest = $regularInterest;
                                    }else{
                                        $total = $total;
                                        $cInterest = 0;
                                    }
                                    $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                                    $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                                    $interest = number_format((float)$a, 2, '.', '');
                                    $totalInterestDeposit = $totalInterestDeposit+($interest);
                                }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                                    $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                                    if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                 
                                        $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                        $collection = (int) $totalDeposit+(int) $pendingAmount;

                                    }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                        $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

                                    }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                        $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                                    }


                                    $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
                                    if($checkAmount > 0){
                                       $aviAmount = $checkAmount; 

                                       $total = $total+$checkAmount;
                                        if($monthDiff % 12 == 0 && $monthDiff != 0){
                                            $total = $total+$regularInterest;
                                            $cInterest = $regularInterest;
                                        }else{
                                            $total = $total;
                                            $cInterest = 0;
                                        }
                                        $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                                        $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                                        $interest = number_format((float)$a, 2, '.', '');

                                    }else{
                                        $aviAmount = 0;
                                        $total = 0;
                                        $cuurentInterest = 0;
                                        $interest = 0; 
                                        $a = 0;
                                    }
                                    $totalInterestDeposit = $totalInterestDeposit+($interest);
                                }
                            }
                            $interstAmount = round($totalInterestDeposit);
                            $finalAmount = round($totalDeposit+$totalInterestDeposit);
                            $investAmount = $totalDeposit;
                        }
                    }else{
                        $finalAmount = 0;
                        $isDefaulter = 1;
                        $investAmount = 0;
                        $interstAmount = 0;
                    } 
                }elseif(in_array($mInvestment->plan_id, $moneyBack)){
                    if($investmentData){
                        $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
                        $years = floor($diff / (365*60*60*24));

                        if($cDate >= $maturity_date){
                            $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
                            $maturityAmount = getMoneyBackAmount($mInvestment->id);

                            $fAmount = $maturityAmount->available_amount;
                            $isDefaulter = 0;
                            $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');

                            $interstAmount = ($fAmount+$refundAmount)-$totalInvestmentAmount;
                            $finalAmount = $fAmount-$interstAmount;
                            $investAmount = $finalAmount;

                        }elseif($cDate < $maturity_date){
                            
                            $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
                            $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();

                            $investmentMonths = $mInvestment->tenure*12;

                            

                            if($mInvestment->last_deposit_to_ssb_date){

                                $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
                                $ts1 = strtotime($sDate);
                                $ts2 = strtotime($cDate);
                                $year1 = date('Y', $ts1);
                                $year2 = date('Y', $ts2);
                                $month1 = date('m', $ts1);
                                $month2 = date('m', $ts2);
                                $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                                $investmentMonths = $monthDiff;

                                $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>=',$mInvestment->last_deposit_to_ssb_date)->sum('deposit');
                            }else{
                                $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                                $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
                                $ts1 = strtotime($sDate);
                                $ts2 = strtotime($cDate);
                                $year1 = date('Y', $ts1);
                                $year2 = date('Y', $ts2);
                                $month1 = date('m', $ts1);
                                $month2 = date('m', $ts2);
                                $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                                $investmentMonths = $monthDiff;
                            }

                            

                            for ($i=1; $i <= $investmentMonths ; $i++){
                                $val = $mInvestment;
                                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
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
                                if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                    $aviAmount = $val->deposite_amount;
                                    $total = $total+$val->deposite_amount;
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
                                }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                                    $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                                    if($checkAmount > 0){
                                       $aviAmount = $checkAmount; 

                                       $total = $total+$checkAmount;
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

                                    }else{
                                        $aviAmount = 0;
                                        $total = 0;
                                        $cuurentInterest = 0;
                                        $interest = 0; 
                                        $addInterest = 0; 
                                    }
                                    $totalInterestDeposit = $totalInterestDeposit+$interest;
                                }
                            }

                            if($depositInterest){
                                $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                $fAmount = round($totalDeposit+$totalInterestDeposit+$availableAmountFd);
                            }else{
                                $fAmount = round($totalDeposit+$totalInterestDeposit);
                            }
                            
                            
                            $isDefaulter = 0;
                            $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');

                            if($refundAmount){
                                $interstAmount = round(($fAmount+$refundAmount)-$totaldepositAmount);
                                $finalAmount = $fAmount-$interstAmount;
                                $investAmount = $finalAmount;
                            }else{
                                $interstAmount = $fAmount-$totaldepositAmount;
                                $finalAmount = $fAmount-$interstAmount;
                                $investAmount = $finalAmount;
                            }
                        }
                    }else{
                        $finalAmount = 0;
                        $isDefaulter = 1;
                        $investAmount = 0;
                        $interstAmount = 0;
                    } 
                }    

            }

        } else{
                $isDefaulter = 0;
                $finalAmount = 0;
        }

        $return_array = compact('investmentDetails','isDefaulter','finalAmount','message','status');

        return json_encode($return_array); 

    }

    /**

     * Get investment details by account number.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    public function getMemberDetails(Request $request)

    {

        $mId = $request->val;

        $mDetails = Member::with('savingAccount')->where('member_id',$mId)->first();

        $count = Member::with('savingAccount')->where('member_id',$mId)->count();

        $return_array = compact('mDetails','count');

        return json_encode($return_array); 

    }

    /*Call death help maturity page*/

     public function demandAdvicematurity()

    {

        if(check_my_permission( Auth::user()->id,"85") != "1"){

          return redirect()->route('admin.dashboard');

        }   

        $data['title']='Maturity Management';

        return view('templates.admin.demand-advice.demand_advice_maturity', $data);

    }

    /**

     * Display the specified resource.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function demandAdvicematurityList(Request $request)

    { 

        if ($request->ajax() && check_my_permission( Auth::user()->id,"85") == "1") {



            $arrFormData = array();   

            if(!empty($_POST['searchform']))

            {

                foreach($_POST['searchform'] as $frm_data)

                {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];

                }

            }



            $data=DemandAdvice::with('investment','expenses','branch')->where('is_mature',1);

            

            if(Auth::user()->branch_id>0){

             $id=Auth::user()->branch_id;

             $data=$data->where('branch_id','=',$id);

            }



            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['date_from'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));

                    if($arrFormData['date_to'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));

                    }

                    else

                    {

                        $endDate='';

                    }

                    $data=$data->whereBetween('date', [$startDate, $endDate]);

                }



                if(isset($arrFormData['filter_branch']) && $arrFormData['filter_branch'] !=''){

                    $branchId=$arrFormData['filter_branch'];

                    $data=$data->where('branch_id','=',$branchId);

                }



                if($arrFormData['advice_type'] !=''){

                    $advice_id=$arrFormData['advice_type'];

                    if($advice_id == 0 || $advice_id == 1 || $advice_id == 2){

                        $data=$data->where('payment_type','=',$advice_id);

                    }elseif($advice_id == 3){

                        $data=$data->where('payment_type','=',3)->where('death_help_catgeory','=',0); 

                    }elseif($advice_id == 4){

                        $data=$data->where('payment_type','=',3)->where('death_help_catgeory','=',1); 

                    }

                }

                

            }else{

                $data=$data->where('payment_type',5);

            }



            $data = $data->orderby('id','DESC')->get();



            return Datatables::of($data)

            ->addIndexColumn()



            ->addColumn('branch_name',function($row){

                 return $row['branch']->name;

            })

            ->rawColumns(['branch_name'])

            

            ->addColumn('branch_code', function($row){

                $branch_code = $row['branch']->branch_code;

                return $branch_code;

            })

            ->rawColumns(['branch_code'])

            ->addColumn('sector', function($row){

                $sector = $row['branch']->sector;

                return $sector;

            })

            ->rawColumns(['sector'])

            ->addColumn('regan', function($row){

                $regan = $row['branch']->regan;

                return $regan;

            })

            ->rawColumns(['regan'])

            ->addColumn('zone', function($row){

                $zone = $row['branch']->zone;

                return $zone;

            })

            ->rawColumns(['zone'])

            ->addColumn('date', function($row){

                return date("d/m/Y", strtotime( $row->date));

            })

            ->rawColumns(['date'])

            ->addColumn('member_name', function($row){

                if($row['investment']){

                    return getMemberData($row['investment']->member_id)->first_name.' '.getMemberData($row['investment']->member_id)->last_name;

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['member_name'])

            ->addColumn('maturity_amount_tds', function($row){

                return '<input type="text" name="maturity_amount_tds['.$row->id.']" class="'.$row->investment_id.'_maturity_amount_tds maturity_amount_tds" value="" style="width:80%" readonly> &#8377 <input type="hidden" name="tds_interest['.$row->id.']" class="'.$row->investment_id.'_tds_interest"><input type="hidden" name="tds_interest_on_amount['.$row->id.']" class="'.$row->investment_id.'_tds_interest_on_amount">';

            })

            ->escapeColumns(['loan'])

            ->addColumn('maturity_amount_till_date', function($row){

                return '<input type="text" name="maturity_amount_till_date['.$row->id.']" class="'.$row->investment_id.'_maturity_amount_till_date maturity_amount_till_date" value="" style="width:80%" readonly> &#8377';

            })

            ->escapeColumns(['loan'])

            ->addColumn('maturity_amount_payable', function($row){

                return '<input type="text" name="maturity_amount_payable['.$row->id.']" class="'.$row->investment_id.'_maturity_amount_payable maturity_amount_payable" data-investment-id="'.$row->investment_id.'" value="" style="width:80%"> &#8377';

            })

            ->escapeColumns(['loan'])



            ->addColumn('voucher_number', function($row){

                return $row->voucher_number;

            })

            ->rawColumns(['voucher_number'])

            ->addColumn('mobile_number', function($row){

                return $row->mobile_number;

            })

            ->rawColumns(['mobile_number'])

            ->addColumn('account_number', function($row){
                if($row->account_number){
                    return $row->account_number;
                }else{
                    return 'N/A';
                }

            })

            ->addColumn('status', function($row){

                if($row->status == 0){

                    $status = 'Pending';

                }else{

                    $status = 'Approved';        

                }

                

                return $status;

            })

            ->rawColumns(['status'])



            ->addColumn('calculate_maturity', function($row){

                $url = URL::to("admin/demand-advice/edit-demand-advice/".$row->id."");

                $btn = '';

                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                if($row->is_mature == 1 && $row->status == 0){
                    $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }

                if($row->is_mature == 1){

                    $btn .= '<a class="dropdown-item '.$row->investment_id.'-calculate-maturity calculate-maturity" href="javascript:void(0);" data-val="0" data-payment-type="'.$row->payment_type.'" data-sub-payment-type="'.$row->sub_payment_type.'" data-id="'.$row->investment_id.'"  data-advice-id="'.$row->id.'"><i class="fas fa-recycle"></i>Calculate Maturity</a>';
                }

                $btn .= '</div></div></div>'; 
      
                return $btn;

            })

            ->rawColumns(['calculate_maturity'])



            ->make(true);

        }

    }

    /**

     * Get investment details by id.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    public function getInvestmentData(Request $request)

    {

        $investmentId = $request->investmentId;

        $paymentType = $request->paymentType;

        $subPaymentType = $request->subPaymentType;

        $demandId = $request->demandId;

        $demadAdvice=DemandAdvice::where('id',$demandId)->first();

        $mInvestment = Memberinvestments::where('id',$investmentId)->first();

        $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));

        $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$investmentId)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->orderby('created_at', 'asc')->get();

        $view = view("templates.admin.demand-advice.maturity_calculation",compact('investmentData','mInvestment','maturity_date','paymentType','subPaymentType','demadAdvice'))->render();

        return response()->json(['html'=>$view]);

    }

    /**

     * Get investment details by id.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return JSON

     */

    public function saveInvestmentMaturityAmount(Request $request)

    {

        $demandAdviceIds = $request->demand_advice_id;

        DB::beginTransaction();

        try {

            foreach ($demandAdviceIds as $key => $value) {

                if($request->maturity_amount_till_date[$key]){

                    $daData = [

                        'maturity_amount_till_date' => $request->maturity_amount_till_date[$key],

                        //'maturity_amount_payable' => $request->maturity_amount_payable[$key],

                        'maturity_amount_payable' => $request->maturity_amount_payable[$key]-$request->maturity_amount_tds[$key],

                        'tds_percentage' => $request->tds_interest[$key],

                        'tds_per_amount' => $request->tds_interest_on_amount[$key],

                        'tds_amount' => $request->maturity_amount_tds[$key],

                        'final_amount' => $request->maturity_amount_payable[$key]-$request->maturity_amount_tds[$key],

                        'is_mature' => 0,

                    ];



                    $demandAdvice = DemandAdvice::find($key);

                    $demandAdvice->update($daData);
                }

            }

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        return back()->with('success', 'Successfully created maturity');

    }

    /**

     * Display application listing of the account heads.

     *

     * @return \Illuminate\Http\Response

     */

    public function application()

    {

        if(check_my_permission( Auth::user()->id,"86") != "1"){

          return redirect()->route('admin.dashboard');

        }

        $data['title']='Demand Advice Application';

        return view('templates.admin.demand-advice.application', $data);

    }

    /**

     * Display the specified resource.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function applicationListing(Request $request)

    { 

        if ($request->ajax() && check_my_permission( Auth::user()->id,"86") == "1") {



            $arrFormData = array();   

            if(!empty($_POST['searchform']))

            {

                foreach($_POST['searchform'] as $frm_data)

                {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];

                }

            }



            $data=DemandAdvice::with('expenses','branch');

            

            if(Auth::user()->branch_id>0){

             $id=Auth::user()->branch_id;

             $data=$data->where('branch_id','=',$id);

            }



            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')

              {

                if($arrFormData['date_from'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));

                    if($arrFormData['date_to'] !=''){

                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));

                    }

                    else

                    {

                        $endDate='';

                    }



                    $data=$data->whereBetween('date', [$startDate, $endDate]);         

                }



                if(isset($arrFormData['filter_branch']) && $arrFormData['filter_branch'] !=''){

                    $branchId=$arrFormData['filter_branch'];

                    $data=$data->where('branch_id','=',$branchId);

                }



                if($arrFormData['advice_type'] !=''){

                    $advice_id=$arrFormData['advice_type'];

                    $advice_type_id=$arrFormData['expense_type'];



                    if($advice_id == 1 || $advice_id == 2 || $advice_id == 3 || $advice_id == 4){

                        $data=$data->where('is_mature',0);

                    }



                    if($advice_id == 0 || $advice_id == 1 || $advice_id == 2){

                        if($advice_type_id != ''){

                            $data=$data->where('payment_type','=',$advice_id)->where('sub_payment_type',$advice_type_id);

                        }else{

                            $data=$data->where('payment_type','=',$advice_id);        

                        }

                    }elseif($advice_id == 3){

                        $data=$data->where('payment_type','=',3)->where('death_help_catgeory','=',0); 

                    }elseif($advice_id == 4){

                        $data=$data->where('payment_type','=',3)->where('death_help_catgeory','=',1); 

                    }

                }

            }else{

                $data=$data->where('payment_type',1);

            }



            $data = $data->where('status',0)->orderby('id','DESC')->get();



            return Datatables::of($data)

            ->addIndexColumn()

            ->addIndexColumn()

            ->addColumn('checkbox', function($row){

                $branch = '<input type="checkbox" name="demand_advice_record" value="'.$row->id.'" id="demand_advice_record">';

                return $branch;

            })

            ->escapeColumns(['branch_name'])

            ->addColumn('branch_name',function($row){

                 return $row['branch']->name;

            })

            ->rawColumns(['branch_name'])

            

            ->addColumn('branch_code', function($row){

                $branch_code = $row['branch']->branch_code;

                return $branch_code;

            })

            ->rawColumns(['branch_code'])

           

            ->addColumn('zone', function($row){

                $sector = $row['branch']->sector;

                return $sector;

            })

            ->rawColumns(['zone'])
            ->addColumn('account_number', function($row){
                if($row->account_number){
                    return $row->account_number;
                }elseif($row->investment_id)
                {
                    $account_number = getInvestmentDetails($row->investment_id)->account_number;
                   return $account_number;
                }

            })
             ->rawColumns(['account_number'])
             ->addColumn('member_name', function($row){

                 if($row->investment_id)
                {
                    $associate_id = getInvestmentDetails($row->investment_id)->member_id;
                    $member_name = getMemberData($associate_id)->first_name.' '.getMemberData($associate_id)->last_name;
                }
                else{
                    $member_name="N/A";
                }

                
                return $member_name;

            })

            ->rawColumns(['member_name'])

            ->addColumn('associate_code', function($row){

                if($row->investment_id)
                {
                    $associate_id = getInvestmentDetails($row->investment_id)->associate_id;
                    $associate_code = getMemberData($associate_id)->associate_no;
                }
                else{
                    $associate_code="N/A";
                }

                
                return $associate_code;

            })

            ->rawColumns(['associate_code'])

               ->addColumn('is_loan', function($row){
                
                    $member_id = getInvestmentDetails($row->investment_id)->associate_id;
                    $loanDetail =  $this->getData(new Memberloans(),$member_id);
              
               
                
                return $loanDetail;

            })

            ->rawColumns(['is_loan'])
			
			  ->addColumn('associate_name', function($row){

               if($row->investment_id)
                {
                    $associate_id = getInvestmentDetails($row->investment_id)->associate_id;
                    $associate_name = getMemberData($associate_id)->first_name.' '.getMemberData($associate_id)->last_name;
                }
                else{
                    $associate_name="N/A";
                }

                
                return $associate_name;

            })

            ->rawColumns(['associate_name'])

            ->addColumn('total_amount', function($row){                
                if($row->investment_id){
                    $investmentAmount = Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');

                    return round($investmentAmount).' &#8377';
                }else{
                     return $row['expenses']->sum('amount');
                }
            })
            ->escapeColumns(['zone'])

            ->addColumn('tds_amount', function($row){ 
                if($row->tds_amount){         
                    return round($row->tds_amount).' &#8377';
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['zone'])

            ->addColumn('total_payable_amount', function($row){    
                if($row->maturity_amount_payable){          
                    return round($row->maturity_amount_payable+$row->tds_amount).' &#8377';
                }else{
                     return $row['expenses']->sum('amount');
                }
            })
            ->escapeColumns(['zone'])

            ->addColumn('final_amount', function($row){  
                if($row->final_amount){
                    return round($row->final_amount).' &#8377';
                }elseif($row->maturity_amount_payable){  
                    return round($row->maturity_amount_payable-$row->tds_amount).' &#8377';
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['zone'])

            ->addColumn('date', function($row){

                return date("d/m/Y", strtotime( $row->date));

            })

            ->rawColumns(['date'])
            ->addColumn('created_at', function($row){

                 if($row->investment_id){
                    $investmentDetail = getInvestmentDetails($row->investment_id)->created_at;
                   return date("d/m/Y", strtotime($investmentDetail));
                }

            })

            ->rawColumns(['created_at'])


            ->addColumn('advice_type', function($row){

                if($row->payment_type == 0){

                    return 'Expenses';

                }elseif($row->payment_type == 1){

                    return 'Maturity';

                }elseif($row->payment_type == 2){

                    return 'Prematurity';

                }elseif($row->payment_type == 3){

                    if($row->sub_payment_type == '4'){

                        return 'Death Help';

                    }elseif($row->sub_payment_type == '5'){

                        return 'Death Claim';

                    }

                }

            })

            ->rawColumns(['advice_type'])

            ->addColumn('expense_type', function($row){

                if($row->sub_payment_type == '0'){

                    return 'Fresh Exprense';

                }elseif($row->sub_payment_type == '1'){

                    return 'TA Advanced';

                }elseif($row->sub_payment_type == '2'){

                    return 'Advanced salary';

                }elseif($row->sub_payment_type == '3'){

                    return 'Advanced Rent';

                }elseif($row->sub_payment_type == '4'){

                    return 'N/A';

                }elseif($row->sub_payment_type == '5'){

                    return 'N/A';

                }else{

                    return 'N/A';

                }

            })

            ->rawColumns(['expense_type'])



            ->addColumn('voucher_number', function($row){

                return $row->voucher_number;

            })

            ->rawColumns(['voucher_number'])

            ->addColumn('advice_number', function($row){

                return '';

            })

            ->rawColumns(['advice_number'])

            /*->addColumn('owner_name', function($row){

                return $row->owner_name;

            })

            ->rawColumns(['owner_name'])

            ->addColumn('particular', function($row){

                return $row->particular;

            })

            ->rawColumns(['particular'])

            ->addColumn('mobile_number', function($row){

                return $row->mobile_number;

            })

            ->rawColumns(['mobile_number'])*/

            

            ->addColumn('status', function($row){

                if($row->status == 0){

                    $status = 'Pending';

                }else{

                    $status = 'Approved';        

                }

                

                return $status;

            })

            ->rawColumns(['status'])



            ->addColumn('action', function($row){

                $url = URL::to("admin/demand-advice/edit-demand-advice/".$row->id."");

                $deleteurl = URL::to("admin/delete-demand-advice/".$row->id."");



                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                if($row->is_mature == 1 && $row->status == 0){
                    $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                }

                $btn .= '<a class="dropdown-item delete-demand-advice" href="'.$deleteurl.'"><i class="fas fa-trash-alt"></i>Delete</a>';



                $btn .= '</div></div></div>';          

                return $btn;

            })

            ->rawColumns(['action'])



            ->make(true);

        }

    }

    /**

     * Display rent payable listing.

     *

     * @param  \App\Reservation  $reservation

     * @return \Illuminate\Http\Response

     */

    public function approveDemandAdviceView(Request $request)

    {

        if($request['selected_records'] && isset($request['selected_records'])){

            $sRecord = explode(',', $request['selected_records']);

            $data['demandAdvice'] = DemandAdvice::with('investment','expenses','branch')->whereIn('id',$sRecord)->get();

            $data['selectedRecords'] = $request['selected_records'];

        }else{

           $data['demandAdvice'] = array(); 

           $data['selectedRecords'] = 0;

        }



        $data['cBanks'] = SamraddhBank::with('bankAccount')->where("status","1")->get();  

        $data['cheques'] = SamraddhCheque::select('cheque_no','account_id')->where('status',1)->get();

        $data['assets_category'] = AccountHeads::whereIn('parent_id',[9])->where('status',0)->get();
        $head_ids = array(9);
        $subHeadsIDS = AccountHeads::where('head_id',9)->where('status',0)->pluck('head_id')->toArray();
          
            if( count($subHeadsIDS) > 0 ){
                $head_id=  array_merge($head_ids,$subHeadsIDS);
                
               $return_array= get_change_sub_account_head($head_ids,$subHeadsIDS,true);
                  
               }

               foreach ($return_array as $key => $value) {
               
                $ids[] = $value;
                   
               }
               
                

        $data['assets_subcategory'] = AccountHeads::whereIn('parent_id',$ids)->where('status',0)->get();



        if($data['demandAdvice'][0]->payment_type == 0 && $data['demandAdvice'][0]->sub_payment_type == 0){

            $data['title']='Demand Advice | Approve';

            $data['type'] = 0;

            $data['subType'] = 0;

            return view('templates.admin.demand-advice.fresh_expense_approve', $data);

        }if($data['demandAdvice'][0]->payment_type == 4){

            $data['title']='Emergency Maturity I Transfer';

            $data['type'] = $data['demandAdvice'][0]->payment_type;

            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;

            return view('templates.admin.demand-advice.approve-emergancy-maturity', $data);

        }else{

            $data['title']='Demand Advice | Approve';

            $data['type'] = $data['demandAdvice'][0]->payment_type;

            $data['subType'] = $data['demandAdvice'][0]->sub_payment_type;

            return view('templates.admin.demand-advice.approve', $data);

        }

    }

    public function getBankDayBookAmount(Request $request)

    {

        $fromBankId = $request->fromBankId;

        $date = date("Y-m-d", strtotime(convertDate($request->date)));

        $bankRes = SamraddhBankClosing::where('bank_id',$fromBankId)->whereDate('entry_date',$date)/*->orderBy('entry_date', 'desc')*/->first();

        if($bankRes){

            $bankDayBookAmount = (int)$bankRes->balance;

        }else{

            $bankRes = SamraddhBankClosing::where('bank_id',$fromBankId)->whereDate('entry_date','<',$date)->orderby('entry_date','DESC')->first();

            $bankDayBookAmount = (int)$bankRes->balance;

        }

        $return_array = compact('bankDayBookAmount');

        return json_encode($return_array);

    }

    // Edit Branch to ho

    public function getBranchDayBookAmount(Request $request)

    {

        $branch_id = $request->branch_id;

        $date = date("Y-m-d", strtotime(convertDate($request->date)));

        $microLoanRes = BranchCash::select('balance','loan_balance')->where('branch_id',$branch_id)->whereDate('entry_date',$date)/*->orderBy('entry_date', 'desc')*/->first();

        if($microLoanRes){

            $loanDayBookAmount = (int)$microLoanRes->loan_balance;

            $microDayBookAmount = (int)$microLoanRes->balance;

        }else{

            $microLoan = BranchCash::select('balance','loan_balance')->where('branch_id',$branch_id)->whereDate('entry_date','<',$date)->orderby('entry_date','DESC')->first();



            $loanDayBookAmount = (int)$microLoan->loan_balance;

            $microDayBookAmount = (int)$microLoan->balance;

        }



        $return_array = compact('microDayBookAmount','loanDayBookAmount');

        return json_encode($return_array);

    }

    public function approvePayment(Request $request)

    {

        DB::beginTransaction();

        try {

            $entryTime = date("H:i:s");

            $ssbArray = array();

            $demandAdviceIds = explode(',', $request->selected_fresh_expense_records);
            

            $encodeDate = json_encode($demandAdviceIds);
            $arrs = array("investmentId" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "All Payment ID", "data" => $encodeDate);


            foreach ($demandAdviceIds as $key => $value) {

                if($request->type == 0 && $request->subtype == 0){

                    $encodeDate = $value;
                    $arrs = array("load_id" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Demand Advice Application – Payment ", "data" => $encodeDate);
                    DB::table('user_log')->insert($arrs); 

                    $demandAdvice = DemandAdvice::with('employee')->where('id',$value)->first();

                    Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date))));

                    $request['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date)));

                    $request['branch_id'] = $demandAdvice->branch_id;

                    $demandAdviceExpenses = DemandAdviceExpense::where('demand_advice_id',$value)->get(); 

                    if($request->amount_mode == 2){

                        if($request->mode == 3){

                            SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);



                            SamraddhChequeIssue::create([

                                'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                'type' =>6,

                                'sub_type' =>61,

                                'type_id' =>$value,

                                'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                'status' => 1,

                            ]);

                        }

                    }

                    foreach ($demandAdviceExpenses as $key => $expvalue) {

                        $dayBookRef = CommanController::createBranchDayBookReference($expvalue->amount);

                        if($request->amount_mode == 0){

                            $branch_id = $request->branch_id;

                            $type = 13;

                            $sub_type = 131;

                            $jv_unique_id = NULL;

                            $type_id = $expvalue->id;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            $member_id = NULL;

                            $branch_id_to = NULL;

                            $branch_id_from = $request->branch_id;

                            $opening_balance = $expvalue->amount;

                            $amount = $expvalue->amount;

                            $closing_balance = $expvalue->amount;

                            /*if($request->is_assests == 0){

                                $description = getAcountHeadNameHeadId($request->assets_subcategory).' Purchase';

                            }elseif($request->is_assests == 1){

                                $description = getAcountHeadNameHeadId($expvalue->subcategory).' Purchase';

                            }*/

                            $description = $expvalue->particular;

                            $description_dr = $expvalue->party_name.' A/C Dr '.$expvalue->amount;

                            $description_cr = 'To Cash A/C Cr '.$expvalue->amount;

                            $payment_type = 'DR';

                            $payment_mode = 0;

                            $currency_code = 'INR';

                            $amount_to_id = NULL;

                            $amount_to_name = $expvalue->party_name;

                            $amount_from_id = $request->branch_id;

                            $amount_from_name = getBranchDetail($request->branch_id)->name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                        }elseif($request->amount_mode == 1){

                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                            $vno = "";

                            for ($i = 0; $i < 10; $i++) {

                                $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                            }        

                            $branch_id = $request->branch_id;

                            $type = 13;

                            $sub_type = 131;

                            $type_id = $expvalue->id;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            $jv_unique_id = NULL;

                            $member_id = NULL;

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $opening_balance = $expvalue->amount;

                            $amount = $expvalue->amount;

                            $closing_balance = $expvalue->amount;

                            /*if($request->is_assests == 0){

                                $description = getAcountHeadNameHeadId($request->assets_subcategory).' Purchase';

                            }elseif($request->is_assests == 1){

                                $description = getAcountHeadNameHeadId($expvalue->subcategory).' Purchase';

                            }*/

                            $description = $expvalue->particular;

                            $description_dr = $expvalue->party_name.' A/C Dr '.$expvalue->amount;

                            $description_cr = 'To Cash A/C Cr '.$expvalue->amount;

                            $payment_type = 'CR';

                            $payment_mode = 3;

                            $currency_code = 'INR';

                            $amount_to_id = NULL;

                            $amount_to_name = $expvalue->party_name;

                            $amount_from_id = NULL;

                            $amount_from_name = NULL;

                            $v_no = $vno;

                            $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                        }elseif($request->amount_mode == 2){

                            $branch_id = $request->branch_id;

                            $type = 13;

                            $sub_type = 131;

                            $jv_unique_id = NULL;

                            $ssb_account_id_to = NULL;

                            $type_id = $expvalue->id;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            $member_id = NULL;

                            $branch_id_to = NULL;

                            $branch_id_from = $request->branch_id;

                            $opening_balance = $expvalue->amount;

                            $amount = $expvalue->amount;

                            $closing_balance = $expvalue->amount;

                            /*if($request->is_assests == 0){

                                $description = getAcountHeadNameHeadId($request->assets_subcategory).' Purchase';

                            }elseif($request->is_assests == 1){

                                $description = getAcountHeadNameHeadId($expvalue->subcategory).' Purchase';

                            }*/

                            $description = $expvalue->particular;

                            $description_dr = $expvalue->party_name.' A/C Dr '.$expvalue->amount;

                            $description_cr = 'To Cash A/C Cr '.$expvalue->amount;

                            $payment_type = 'DR';

                            $currency_code = 'INR';

                            $amount_to_id = NULL;

                            $amount_to_name = $expvalue->party_name;

                            $amount_from_id = $request->bank;

                            $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            if($request->mode == 3){

                                $cheque_type = 1;

                                $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                                $cheque_no = $request->cheque_number;

                                $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                                $cheque_bank_from = $request->bank;

                                $cheque_bank_from_id = $request->bank;

                                $cheque_bank_ac_from = $request->bank_account_number;

                                $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = NULL;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $payment_mode = 1;

                                $day_book_payment_mode = 1;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);

                                SamraddhChequeIssue::create([

                                    'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                    'type' =>6,

                                    'sub_type' =>61,

                                    'type_id' =>$value,

                                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                    'status' => 1,

                                ]);

                            }elseif($request->mode == 4){

                                $cheque_id = NULL;

                                $cheque_type = NULL;

                                $cheque_no = NULL;

                                $cheque_date = NULL;

                                $cheque_bank_from = NULL;

                                $cheque_bank_from_id = NULL;

                                $cheque_bank_ac_from = NULL;

                                $cheque_bank_ifsc_from = NULL;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_from_id = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $cheque_bank_ac_to = NULL;

                                $transction_no = $request->utr_number;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $payment_mode = 2;

                                $day_book_payment_mode = 3;

                            }

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $bank_id = $request->bank;

                            $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                        }

                        $ssb_account_tran_id_to = NULL;
                        $ssb_account_tran_id_from = NULL;

                        $response = DemandAdvice::where('id', $value)->update(['status' => 1,'payment_mode'=> $payment_mode]);

                        if($request->is_assests == 0){

                           $head1 = 2;

                           $head2 = 9;

                           $head3 = $request->assets_category;

                           $head4 = $request->assets_subcategory;

                           $head5 = NULL;

                           $head = $request->assets_subcategory;

                           DemandAdviceExpense::where('id',$expvalue->id)->update(['is_assets' => 0,'assets_category'=>$head3,'assets_subcategory'=>$head4,'current_balance'=>$expvalue->amount,'purchase_date'=>date("Y-m-d", strtotime(convertDate($request->payment_date)))]);

                        }elseif($request->is_assests == 1){

                            $head1 = 4;

                            $head2 = 86;

                            $head3 = $expvalue->subcategory1;

                            $head4 = NULL;

                            $head5 = NULL;

                            if($expvalue->subcategory3){
                                $head = $expvalue->subcategory3;
                            }elseif($expvalue->subcategory2){
                                $head = $expvalue->subcategory2;
                            }elseif($expvalue->subcategory1){
                                $head = $expvalue->subcategory1;
                            }

                            DemandAdviceExpense::where('id',$expvalue->id)->update(['is_assets' => 1,'current_balance'=>$expvalue->amount,'purchase_date'=>date("Y-m-d", strtotime(convertDate($request->payment_date)))]);

                        }



                        if($request->amount_mode == 0){

                            $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                           /* $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            $updateBranchCash = $this->updateBranchCashDr($branch_id,$request->created_at,$amount,0);

                            $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id,$request->created_at,$amount,0); 

                        }

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/ 

                        if($request->amount_mode == 2){ 

                            if($request->amount_mode == 2 && $request->mode == 4){

                                $bankAmount = $amount+$request->neft_charge;

                            }else{

                                $bankAmount = $amount;

                            }

                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bankAmount,$bankAmount,$bankAmount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,0);
                        }  

                    }

                    /*if($request->amount_mode == 0){

                        $branch_id = $request->branch_id;

                        $type = 13;

                        $sub_type = 131;

                        $type_id = $value;

                        $type_transaction_id = $value;

                        $associate_id = NULL;

                        $member_id = NULL;

                        $branch_id_to = NULL;

                        $branch_id_from = $request->branch_id;

                        $description = 'NEFT Charge '.$request->neft_charge;

                        $payment_mode = 0;

                        $currency_code = 'INR';

                        $amount_to_id = NULL;

                        $amount_to_name = 'Frsh Expense';

                        $amount_from_id = $request->branch_id;

                        $amount_from_name = getBranchDetail($request->branch_id)->name;

                        $v_no = NULL;

                        $v_date = NULL;

                        $ssb_account_id_from = NULL;

                        $cheque_no = NULL;

                        $cheque_date = NULL;

                        $cheque_bank_from = NULL;

                        $cheque_bank_ac_from = NULL;

                        $cheque_bank_ifsc_from = NULL;

                        $cheque_bank_branch_from = NULL;

                        $cheque_bank_to = NULL;

                        $cheque_bank_ac_to = NULL;

                        $transction_no = NULL;

                        $transction_bank_from = NULL;

                        $transction_bank_ac_from = NULL;

                        $transction_bank_ifsc_from = NULL;

                        $transction_bank_branch_from = NULL;

                        $transction_bank_to = NULL;

                        $transction_bank_ac_to = NULL;

                        $transction_date = NULL;

                        $entry_date = NULL;

                        $entry_time = NULL;

                        $created_by = 1;

                        $created_by_id = Auth::user()->id;

                        $is_contra = NULL;

                        $contra_id = NULL;

                        $created_at = NULL;

                        $bank_id = NULL;

                        $bank_ac_id = NULL;

                        $transction_bank_to_name = NULL;

                        $transction_bank_to_ac_no = NULL;

                        $transction_bank_to_branch = NULL;

                        $transction_bank_to_ifsc = NULL;

                    }elseif($request->amount_mode == 1){

                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                        $vno = "";

                        for ($i = 0; $i < 10; $i++) {

                            $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                        }        



                        $branch_id = $request->branch_id;

                        $type = 13;

                        $sub_type = 131;

                        $type_id = $value;

                        $type_transaction_id = $value;

                        $associate_id = NULL;

                        $member_id = NULL;

                        $branch_id_to = NULL;

                        $branch_id_from = NULL;

                        

                        $description = 'NEFT Charge '.$request->neft_charge;



                        $payment_type = 'DR';

                        $payment_mode = 3;

                        $currency_code = 'INR';

                        $amount_to_id = NULL;

                        $amount_to_name = 'Frsh Expense';

                        $amount_from_id = NULL;

                        $amount_from_name = NULL;

                        $v_no = $vno;

                        $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                        $ssb_account_id_from = NULL;

                        $cheque_no = NULL;

                        $cheque_date = NULL;

                        $cheque_bank_from = NULL;

                        $cheque_bank_ac_from = NULL;

                        $cheque_bank_ifsc_from = NULL;

                        $cheque_bank_branch_from = NULL;

                        $cheque_bank_to = NULL;

                        $cheque_bank_ac_to = NULL;

                        $transction_no = NULL;

                        $transction_bank_from = NULL;

                        $transction_bank_ac_from = NULL;

                        $transction_bank_ifsc_from = NULL;

                        $transction_bank_branch_from = NULL;

                        $transction_bank_to = NULL;

                        $transction_bank_ac_to = NULL;

                        $transction_date = NULL;

                        $entry_date = NULL;

                        $entry_time = NULL;

                        $created_by = 1;

                        $created_by_id = Auth::user()->id;

                        $is_contra = NULL;

                        $contra_id = NULL;

                        $created_at = NULL;

                        $bank_id = NULL;

                        $bank_ac_id = NULL;

                        $transction_bank_to_name = NULL;

                        $transction_bank_to_ac_no = NULL;

                        $transction_bank_to_branch = NULL;

                        $transction_bank_to_ifsc = NULL;

                    }elseif($request->amount_mode == 2){



                        $branch_id = $request->branch_id;

                        $type = 13;

                        $sub_type = 131;

                        $type_id = $value;

                        $type_transaction_id = $value;

                        $associate_id = NULL;

                        $member_id = NULL;

                        $branch_id_to = NULL;

                        $branch_id_from = $request->branch_id;

                        $description = 'NEFT Charge '.$request->neft_charge;

                        $payment_type = 'DR';

                        $currency_code = 'INR';

                        $amount_to_id = NULL;

                        $amount_to_name = 'Frsh Expense';

                        $amount_from_id = $request->bank;

                        $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                        $v_no = NULL;

                        $v_date = NULL;

                        $ssb_account_id_from = NULL;

            

                        if($request->mode == 3){

                            $cheque_no = $request->cheque_number;

                            $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                            $cheque_bank_from = $request->bank;

                            $cheque_bank_ac_from = $request->bank_account_number;

                            $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                            $transction_bank_ac_from = $request->bank_account_number;

                            $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $payment_mode = 1;

                        }elseif($request->mode == 4){

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $transction_no = $request->utr_number;

                            $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                            $transction_bank_ac_from = $request->bank_account_number;

                            $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $payment_mode = 2;

                        }



                        $transction_date = NULL;

                        $entry_date = NULL;

                        $entry_time = NULL;

                        $created_by = 1;

                        $created_by_id = Auth::user()->id;

                        $is_contra = NULL;

                        $contra_id = NULL;

                        $created_at = NULL;

                        $bank_id = NULL;

                        $bank_ac_id = NULL;

                        $bank_id = $request->bank;

                        $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                    }*/

                    if($request->amount_mode == 0){

                        $branch_id = $request->branch_id;

                        $type = 13;

                        $sub_type = 131;

                        $jv_unique_id = NULL;

                        $type_id = $expvalue->id;

                        $type_transaction_id = $value;

                        $associate_id = NULL;

                        $member_id = NULL;

                        $branch_id_to = NULL;

                        $branch_id_from = $request->branch_id;

                        $opening_balance = $expvalue->amount;

                        $amount = $expvalue->amount;

                        $closing_balance = $expvalue->amount;

                        /*if($request->is_assests == 0){

                            $description = getAcountHeadNameHeadId($request->assets_subcategory).' Purchase';

                        }elseif($request->is_assests == 1){

                            $description = getAcountHeadNameHeadId($expvalue->subcategory).' Purchase';

                        }*/

                        $description = $expvalue->particular;

                        $description_dr = $expvalue->party_name.' A/C Dr '.$expvalue->amount;

                        $description_cr = 'To Cash A/C Cr '.$expvalue->amount;

                        $payment_type = 'DR';

                        $payment_mode = 0;

                        $currency_code = 'INR';

                        $amount_to_id = NULL;

                        $amount_to_name = $expvalue->party_name;

                        $amount_from_id = $request->branch_id;

                        $amount_from_name = getBranchDetail($request->branch_id)->name;

                        $v_no = NULL;

                        $v_date = NULL;

                        $ssb_account_id_from = NULL;

                        $ssb_account_id_to = NULL;

                        $cheque_type = NULL;

                        $cheque_id = NULL;

                        $cheque_no = NULL;

                        $cheque_date = NULL;

                        $cheque_bank_to_name = NULL;

                        $cheque_bank_to_branch = NULL;

                        $cheque_bank_to_ac_no = NULL;

                        $cheque_bank_from = NULL;

                        $cheque_bank_from_id = NULL;

                        $cheque_bank_ac_from_id = NULL;

                        $cheque_bank_ac_from = NULL;

                        $cheque_bank_ifsc_from = NULL;

                        $cheque_bank_branch_from = NULL;

                        $cheque_bank_to = NULL;

                        $cheque_bank_ac_to = NULL;

                        $cheque_bank_to_ifsc = NULL;

                        $transction_no = NULL;

                        $transction_bank_from = NULL;

                        $transction_bank_from_id = NULL;

                        $transction_bank_ac_from = NULL;

                        $transction_bank_ifsc_from = NULL;

                        $transction_bank_branch_from = NULL;

                        $transction_bank_to = NULL;

                        $transction_bank_ac_to = NULL;

                        $transction_bank_from_ac_id = NULL;

                        $transction_date = NULL;

                        $entry_date = NULL;

                        $entry_time = NULL;

                        $created_by = 1;

                        $created_by_id = Auth::user()->id;

                        $is_contra = NULL;

                        $contra_id = NULL;

                        $created_at = NULL;

                        $bank_id = NULL;

                        $bank_ac_id = NULL;

                        $transction_bank_to_name = NULL;

                        $transction_bank_to_ac_no = NULL;

                        $transction_bank_to_branch = NULL;

                        $transction_bank_to_ifsc = NULL;

                    }elseif($request->amount_mode == 1){

                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                        $vno = "";

                        for ($i = 0; $i < 10; $i++) {

                            $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                        }        



                        $branch_id = $request->branch_id;

                        $type = 13;

                        $sub_type = 131;

                        $type_id = $expvalue->id;

                        $type_transaction_id = $value;

                        $associate_id = NULL;

                        $jv_unique_id = NULL;

                        $member_id = NULL;

                        $branch_id_to = NULL;

                        $branch_id_from = NULL;

                        $opening_balance = $expvalue->amount;

                        $amount = $expvalue->amount;

                        $closing_balance = $expvalue->amount;

                        /*if($request->is_assests == 0){

                            $description = getAcountHeadNameHeadId($request->assets_subcategory).' Purchase';

                        }elseif($request->is_assests == 1){

                            $description = getAcountHeadNameHeadId($expvalue->subcategory).' Purchase';

                        }*/

                        $description = $expvalue->particular;

                        $description_dr = $expvalue->party_name.' A/C Dr '.$expvalue->amount;

                        $description_cr = 'To Cash A/C Cr '.$expvalue->amount;

                        $payment_type = 'CR';

                        $payment_mode = 3;

                        $currency_code = 'INR';

                        $amount_to_id = NULL;

                        $amount_to_name = $expvalue->party_name;

                        $amount_from_id = NULL;

                        $amount_from_name = NULL;

                        $v_no = $vno;

                        $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                        $ssb_account_id_from = NULL;

                        $ssb_account_id_to = NULL;

                        $cheque_type = NULL;

                        $cheque_id = NULL;

                        $cheque_no = NULL;

                        $cheque_date = NULL;

                        $cheque_bank_to_name = NULL;

                        $cheque_bank_to_branch = NULL;

                        $cheque_bank_from = NULL;

                        $cheque_bank_from_id = NULL;

                        $cheque_bank_ac_from = NULL;

                        $cheque_bank_ac_from_id = NULL;

                        $cheque_bank_to_ac_no = NULL;

                        $cheque_bank_ifsc_from = NULL;

                        $cheque_bank_branch_from = NULL;

                        $cheque_bank_to = NULL;

                        $cheque_bank_ac_to = NULL;

                        $cheque_bank_to_ifsc = NULL;

                        $transction_no = NULL;

                        $transction_bank_from = NULL;

                        $transction_bank_from_id = NULL;

                        $transction_bank_ac_from = NULL;

                        $transction_bank_ifsc_from = NULL;

                        $transction_bank_branch_from = NULL;

                        $transction_bank_from_ac_id = NULL;

                        $transction_bank_to = NULL;

                        $transction_bank_ac_to = NULL;

                        $transction_date = NULL;

                        $entry_date = NULL;

                        $entry_time = NULL;

                        $created_by = 1;

                        $created_by_id = Auth::user()->id;

                        $is_contra = NULL;

                        $contra_id = NULL;

                        $created_at = NULL;

                        $bank_id = NULL;

                        $bank_ac_id = NULL;

                        $transction_bank_to_name = NULL;

                        $transction_bank_to_ac_no = NULL;

                        $transction_bank_to_branch = NULL;

                        $transction_bank_to_ifsc = NULL;

                    }elseif($request->amount_mode == 2){

                        $branch_id = $request->branch_id;

                        $type = 13;

                        $sub_type = 131;

                        $jv_unique_id = NULL;

                        $ssb_account_id_to = NULL;

                        $type_id = $expvalue->id;

                        $type_transaction_id = $value;

                        $associate_id = NULL;

                        $member_id = NULL;

                        $branch_id_to = NULL;

                        $branch_id_from = $request->branch_id;

                        $opening_balance = $expvalue->amount;

                        $amount = $expvalue->amount;

                        $closing_balance = $expvalue->amount;

                        /*if($request->is_assests == 0){

                            $description = getAcountHeadNameHeadId($request->assets_subcategory).' Purchase';

                        }elseif($request->is_assests == 1){

                            $description = getAcountHeadNameHeadId($expvalue->subcategory).' Purchase';

                        }*/

                        $description = $expvalue->particular;

                        $description_dr = $expvalue->party_name.' A/C Dr '.$expvalue->amount;

                        $description_cr = 'To Cash A/C Cr '.$expvalue->amount;

                        $payment_type = 'DR';

                        $currency_code = 'INR';

                        $amount_to_id = NULL;

                        $amount_to_name = $expvalue->party_name;

                        $amount_from_id = $request->bank;

                        $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                        $v_no = NULL;

                        $v_date = NULL;

                        $ssb_account_id_from = NULL;

                        $ssb_account_id_to = NULL;

                        if($request->mode == 3){

                            $cheque_type = 1;

                            $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                            $cheque_no = $request->cheque_number;

                            $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                            $cheque_bank_from = $request->bank;

                            $cheque_bank_from_id = $request->bank;

                            $cheque_bank_ac_from = $request->bank_account_number;

                            $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                            $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                            $transction_bank_ac_from = $request->bank_account_number;

                            $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $payment_mode = 1;

                            $day_book_payment_mode = 1;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);

                            SamraddhChequeIssue::create([

                                'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                'type' =>6,

                                'sub_type' =>61,

                                'type_id' =>$value,

                                'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                'status' => 1,

                            ]);

                        }elseif($request->mode == 4){

                            $cheque_id = NULL;

                            $cheque_type = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $cheque_bank_ac_to = NULL;

                            $transction_no = $request->utr_number;

                            $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                            $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                            $transction_bank_ac_from = $request->bank_account_number;

                            $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $payment_mode = 2;

                            $day_book_payment_mode = 3;

                        }



                        $transction_date = NULL;

                        $entry_date = NULL;

                        $entry_time = NULL;

                        $created_by = 1;

                        $created_by_id = Auth::user()->id;

                        $is_contra = NULL;

                        $contra_id = NULL;

                        $created_at = NULL;

                        $bank_id = NULL;

                        $bank_ac_id = NULL;

                        $bank_id = $request->bank;

                        $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                    }

                    if($request->amount_mode == 2 && $request->mode == 4){

                        $dayBookRef = CommanController::createBranchDayBookReference($request->neft_charge);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,$head4=NULL,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                        $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance(0,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,$request->neft_charge);

                    }

                }elseif($request->type == 0 && $request->subtype == 1){

                    $encodeDate = $value;
                    $arrs = array("load_id" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Demand Advice Application – Payment ", "data" => $encodeDate);
                    DB::table('user_log')->insert($arrs); 

                    $demandAdviceTaAdvanced = DemandAdvice::with('employee')->where('id',$value)->first(); 

                    Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date))));

                    $request['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date)));

                    $request['branch_id'] = $demandAdviceTaAdvanced->branch_id;



                    $employeeAdvancedSalary = $demandAdviceTaAdvanced['employee']->advance_payment+$demandAdviceTaAdvanced->advanced_amount;

                    $employeeCurrentBalance = $demandAdviceTaAdvanced['employee']->current_balance+$demandAdviceTaAdvanced->advanced_amount;

                    $advancedSalaryUpdate = Employee::where('id',$demandAdviceTaAdvanced->employee_id)->update(['advance_payment' => $employeeAdvancedSalary,'current_balance' => $employeeCurrentBalance]);

                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('account_no',$demandAdviceTaAdvanced['employee']->ssb_account)->first();

                    if($request->amount_mode == 1 && $ssbAccountDetails == ''){
                        array_push($ssbArray,$value);
                    }else{

                        if($request->amount_mode == 0){

                            $branch_id = $request->branch_id;

                            $type = 13;

                            $sub_type = 132;

                            $jv_unique_id = NULL;

                            $type_id = $value;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            if($ssbAccountDetails){

                                $member_id = $ssbAccountDetails['ssbMember']->id;

                                $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            }else{
                                $member_id = $demandAdviceTaAdvanced['employee']->id;

                                $amount_to_id =$demandAdviceTaAdvanced['employee']->id;

                                $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                            }

                            $branch_id_to = NULL;

                            $branch_id_from = $request->branch_id;

                            $opening_balance = $demandAdviceTaAdvanced->advanced_amount;

                            $amount = $demandAdviceTaAdvanced->advanced_amount;

                            $closing_balance = $demandAdviceTaAdvanced->advanced_rent_amount;

                            $description = $demandAdviceTaAdvanced->employee_name.' A/C Dr '.$demandAdviceTaAdvanced->advanced_amount.' - To Cash A/C Cr '.$request->advanced_amount;

                            $description_dr = $demandAdviceTaAdvanced->employee_name.' A/C Dr '.$demandAdviceTaAdvanced->advanced_amount;

                            $description_cr = 'To Cash A/C Cr '.$demandAdviceTaAdvanced->advanced_amount;

                            $payment_type = 'DR';

                            $payment_mode = 0;

                            $currency_code = 'INR';

                            $amount_from_id = $request->branch_id;

                            $amount_from_name = getBranchDetail($request->branch_id)->name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $to_bank_name = NULL;

                            $to_bank_branch = NULL;

                            $to_bank_ac_no = NULL;

                            $to_bank_ifsc = NULL;

                            $to_bank_id = NULL;

                            $to_bank_account_id = NULL;

                            $from_bank_name = NULL;

                            $from_bank_branch = NULL;

                            $from_bank_ac_no = NULL;

                            $from_bank_ifsc = NULL;

                            $from_bank_id = NULL;

                            $from_bank_ac_id = NULL;

                            $cheque_id = NULL;

                            $transaction_date = NULL;

                            $transaction_charge = NULL;

                        }elseif($request->amount_mode == 1){

                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                            $vno = "";

                            for ($i = 0; $i < 10; $i++) {

                                $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                            }        



                            $branch_id = $demandAdviceTaAdvanced['employee']->branch_id;

                            $type = 13;

                            $sub_type = 132;

                            $type_id = $value;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            if($ssbAccountDetails){

                                $member_id = $ssbAccountDetails['ssbMember']->id;

                                $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            }else{
                                $member_id = $demandAdviceTaAdvanced['employee']->id;

                                $amount_to_id =$demandAdviceTaAdvanced['employee']->id;

                                $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                            }

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $opening_balance = $demandAdviceTaAdvanced->advanced_amount;

                            $amount = $demandAdviceTaAdvanced->advanced_amount;

                            $closing_balance = $demandAdviceTaAdvanced->advanced_rent_amount;

                            $description = $demandAdviceTaAdvanced->employee_name.' A/C Dr '.$demandAdviceTaAdvanced->advanced_amount.' - To Cash A/C Cr '.$request->advanced_amount;

                            $description_dr = $demandAdviceTaAdvanced->employee_name.' A/C Dr '.$demandAdviceTaAdvanced->advanced_amount;

                            $description_cr = 'To Cash A/C Cr '.$demandAdviceTaAdvanced->advanced_amount;

                            $payment_type = 'CR';

                            $payment_mode = 3;

                            $currency_code = 'INR';

                            $amount_from_id = NULL;

                            $amount_from_name = NULL;

                            $jv_unique_id = NULL;

                            $v_no = $vno;

                            $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            $ssb_account_id_from = NULL;

                            //$ssb_account_id_to = $ssbAccountDetails->id;

                            $cheque_type = NULL;

                            //$cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $ssb_account_id_to = $demandAdviceTaAdvanced['employee']->ssb_id;

                            $to_bank_name = NULL;

                            $to_bank_branch = NULL;

                            $to_bank_ac_no = NULL;

                            $to_bank_ifsc = NULL;

                            $to_bank_id = NULL;

                            $to_bank_account_id = NULL;

                            $from_bank_name = NULL;

                            $from_bank_branch = NULL;

                            $from_bank_ac_no = NULL;

                            $from_bank_ifsc = NULL;

                            $from_bank_id = NULL;

                            $from_bank_ac_id = NULL;

                            $cheque_id = NULL;

                            $transaction_date = NULL;

                            $transaction_charge = NULL;

                        }elseif($request->amount_mode == 2){

                            $branch_id = $demandAdviceTaAdvanced['employee']->branch_id;

                            $type = 13;

                            $sub_type = 132;

                            $type_id = $value;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            if($ssbAccountDetails){

                                $member_id = $ssbAccountDetails['ssbMember']->id;

                                $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            }else{
                                $member_id = $demandAdviceTaAdvanced['employee']->id;

                                $amount_to_id =$demandAdviceTaAdvanced['employee']->id;

                                $amount_to_name = $demandAdviceTaAdvanced['employee']->employee_name;
                            }

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $opening_balance = $demandAdviceTaAdvanced->advanced_amount;

                            $amount = $demandAdviceTaAdvanced->advanced_amount;

                            $closing_balance = $demandAdviceTaAdvanced->advanced_rent_amount;

                            $description = $demandAdviceTaAdvanced->employee_name.' A/C Dr '.$demandAdviceTaAdvanced->advanced_amount.' - To Cash A/C Cr '.$request->advanced_amount;

                            $description_dr = $demandAdviceTaAdvanced->employee_name.' A/C Dr '.$demandAdviceTaAdvanced->advanced_amount;

                            $description_cr = 'To Cash A/C Cr '.$demandAdviceTaAdvanced->advanced_amount;

                            $payment_type = 'DR';

                            $currency_code = 'INR';

                            $amount_from_id = $request->bank;

                            $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $jv_unique_id = NULL;

                            if($request->mode == 3){

                                $cheque_type = 1;

                                $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                                $cheque_no = $request->cheque_number;

                                $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                                $cheque_bank_from = $request->bank;

                                $cheque_bank_from_id = $request->bank;

                                $cheque_bank_ac_from = $request->bank_account_number;

                                $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $transction_no = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $payment_mode = 1;

                                $transaction_charge = NULL;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $day_book_payment_mode = 1;

                                SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);



                                SamraddhChequeIssue::create([

                                    'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                    'type' =>6,

                                    'sub_type' =>62,

                                    'type_id' =>$value,

                                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                    'status' => 1,

                                ]);

                            }elseif($request->mode == 4){

                                $cheque_type = NULL;

                                $cheque_id = NULL;

                                $cheque_no = NULL;

                                $cheque_date = NULL;

                                $cheque_bank_from = NULL;

                                $cheque_bank_from_id = NULL;

                                $cheque_bank_ac_from = NULL;

                                $cheque_bank_ifsc_from = NULL;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_ac_from_id = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = $request->utr_number;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $day_book_payment_mode = 3;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $payment_mode = 2;

                                $transaction_charge = $request->neft_charge;

                            }



                            $to_bank_name = $demandAdviceTaAdvanced['employee']->bank_name;

                            $to_bank_branch = $demandAdviceTaAdvanced['employee']->bank_name;

                            $to_bank_ac_no = $demandAdviceTaAdvanced['employee']->bank_account_no;

                            $to_bank_ifsc = $demandAdviceTaAdvanced['employee']->bank_ifsc_code;

                            $to_bank_id = NULL;

                            $to_bank_account_id = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = $request->bank;

                            $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $ssb_account_id_to = NULL;

                            $from_bank_name = getSamraddhBank($request->bank)->bank_name;

                            $from_bank_branch = getSamraddhBankAccount($request->bank_account_number)->branch_name;

                            $from_bank_ac_no = $request->bank_account_number;

                            $from_bank_ifsc = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $from_bank_id = $request->bank;

                            $from_bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $transaction_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                        }

                        $response = DemandAdvice::where('id', $value)->update(['status' => 1,'payment_mode'=> $payment_mode]);

                        $dayBookRef = CommanController::createBranchDayBookReference($amount);

                        $this->employeeSalaryLeaser($demandAdviceTaAdvanced->employee_id,$branch_id,5,$type_id,$employeeCurrentBalance,$amount,$withdrawal=NULL,'TA Advanced amount A/C Cr '.$amount.'',$currency_code,'CR',$payment_mode,1,date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at))),$updated_at=NULL,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transction_no,$transaction_date,$transaction_charge);

                        $this->employeeLedgerBackDateCR($demandAdviceTaAdvanced->employee_id,$request->created_at,$amount);

                        if($request->amount_mode == 1){

                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('account_no',$demandAdviceTaAdvanced['employee']->ssb_account)->first();

                            $paymentMode = 4;

                            $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                            $ssb['saving_account_id']=$ssbAccountDetails->id;

                            $ssb['account_no']=$ssbAccountDetails->account_no;

                            if($request['pay_file_charge'] == 0){

                                $ssb['opening_balance']=$demandAdviceTaAdvanced->advanced_amount+$ssbAccountDetails->balance;

                                $ssb['deposit']=$demandAdviceTaAdvanced->advanced_amount;

                            }else{

                                $ssb['opening_balance']=$demandAdviceTaAdvanced->advanced_amount+$ssbAccountDetails->balance; 

                                $ssb['deposit']=$demandAdviceTaAdvanced->advanced_amount;

                            }



                            $ssb['branch_id']=$demandAdviceTaAdvanced['employee']->branch_id;

                            $ssb['type']=10;

                        

                            $ssb['withdrawal']=0;

                            $ssb['description']=$description;

                            $ssb['currency_code']='INR';

                            $ssb['payment_type']='CR';

                            $ssb['payment_mode']=3;

                            $ssb['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                            $ssbAccountTran = SavingAccountTranscation::create($ssb);

                            $saTranctionId = $ssbAccountTran->id;

                            $saToId = $ssbAccountDetails->id;
                            if(isset($ssbAccountTran->id))
                            {
                                $saTranctionToId = $ssbAccountTran->id;
                            }
                            else{
                                $saTranctionToId =NULL;
                            }
                            


                            $balance_update=$demandAdviceTaAdvanced->advanced_amount+$ssbAccountDetails->balance;

                            

                            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                            $ssbBalance->balance=$balance_update;

                            $ssbBalance->save();



                            $data['saving_account_transaction_id']=$saTranctionId;

                            $data['investment_id']=$demandAdviceTaAdvanced['employee']->id;

                            $data['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                            $satRef = TransactionReferences::create($data);

                            $satRefId = $satRef->id;



                            $amountArraySsb = array('1'=>$demandAdviceTaAdvanced->advanced_amount);

                            

                            $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');



                            $description = $description;

                            $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$request->maturity_amount_payable+$ssbAccountDetails->balance,$demandAdviceTaAdvanced->advanced_amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/



                            $memberTransaction = $this->createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                        }else{
                            $ssb_account_tran_id_to = NULL;
                            $ssb_account_tran_id_from = NULL;
                            $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$demandAdviceTaAdvanced->advanced_amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                            $saToId = NULL;
                            $saTranctionToId = NULL;
                            $saTranctionId = NULL;
                        }

                        if($request->amount_mode == 0){



                                $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                                $updateBranchCash = $this->updateBranchCashDr($branch_id,$request->created_at,$amount,0);

                                $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id,$request->created_at,$amount,0); 

                        }

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,72,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,29,72,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/ 

                        if($request->amount_mode == 2){

                            if($request->amount_mode == 2 && $request->mode == 4){

                                $bankAmount = $amount+$request->neft_charge;

                            }else{

                                $bankAmount = $amount;

                            }



                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bankAmount,$bankAmount,$bankAmount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);



                            if($request->amount_mode == 2 && $request->mode == 4){

                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge+$amount,$request->neft_charge+$amount,$request->neft_charge+$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge+$amount,$request->neft_charge+$amount,$request->neft_charge+$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }else{

                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }

                        }

                        if($request->amount_mode == 2 && $request->mode == 4){

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,$head4=NULL,$head5=NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,$request->neft_charge);
                        } 
                    }

                }elseif($request->type == 1 || $request->type == 2 || $request->type == 3 || $request->type == 4){

                    $encodeDate = $value;
                    $arrs = array("load_id" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Demand Advice Application – Payment ", "data" => $encodeDate);
                    DB::table('user_log')->insert($arrs); 
                    

                    $demandAdviceExpenses = DemandAdvice::with('investment')->where('id',$value)->first();  

                    Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date))));

                    $request['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date)));

                    $request['branch_id'] = $demandAdviceExpenses->branch_id;


                    $keyVal = 0;

                    $cInterest = 0;

                    $regularInterest = 0; 

                    $total = 0;

                    $collection = 0;

                    $monthly = array(10,11);

                    $daily = array(7);

                    $preMaturity = array(4,5);

                    $fixed = array(8,9);

                    $samraddhJeevan = array(2,6);

                    $moneyBack = array(3);

                    $totalDeposit = 0;

                    $totalInterestDeposit = 0;

                    $mInvestment = $demandAdviceExpenses['investment'];

                    $maturity_date =  date('Y-m-d', strtotime($mInvestment->created_at. ' + '.($mInvestment->tenure).' year'));

                    $investmentData = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','<=',$maturity_date)->orderby('created_at', 'asc')->get();

                    if (strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false  && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {
                        $getMbTrsAmount = getMbTrsAmount($demandAdviceExpenses['investment']->id);

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

                        $totalInvestmentAmount = $getMbTrsAmount->mb_fd_amount;

                        for ($i=1; $i <= $investmentMonths ; $i++){

                            $mInvestment = $demandAdviceExpenses['investment'];
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

                        $eliAmount = round($totalInvestmentAmount+$totalInterestDeposit);
                        $iseliAmount = round($totalInvestmentAmount+$totalInterestDeposit);

                        $moneyBackAmount = $getMbTrsAmount->mb_amount;
                    }elseif(strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false ){
                        $eliAmount = 0;
                        $moneyBackAmount = 0;
                        //$iseliAmount = investmentEliAmount($mInvestment->id);
                        $iseliAmount = 0;
                    }else{
                        $moneyBackAmount = 0;
                        $eliAmount = 0;
                        $iseliAmount = 0;
                    }

                    /*if(in_array($mInvestment->plan_id, $monthly)){

                        if($investmentData){

                            $investmentMonths = $mInvestment->tenure*12;
                            $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');



                            for ($i=1; $i <= $investmentMonths ; $i++){
                                    $val = $mInvestment;
                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
                                    $cMonth = date('m');
                                    $cYear = date('Y');
                                    $cuurentInterest = $mInvestment->interest_rate;
                                    $totalDeposit = $totalInvestmentAmount;

                                    $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');

                                    $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');

                                    $d1 = explode('-',$mInvestment->created_at);
                                    $d2 = explode('-',$nDate);

                                    $ts1 = strtotime($mInvestment->created_at);
                                    $ts2 = strtotime($nDate);

                                    $year1 = date('Y', $ts1);
                                    $year2 = date('Y', $ts2);

                                    $month1 = date('m', $ts1);
                                    $month2 = date('m', $ts2);

                                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                                    if($cMonth > $d2[1] && $cYear > $d2[0]){

                                        if($previousRecord){
                                            $previousDate = explode('-',$previousRecord);
                                            $previousMonth = $previousDate[1];
                                            if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
                                                $defaulterInterest = 1.50;
                                                $isDefaulter = 1;
                                                
                                            }else{
                                                $defaulterInterest = 0;
                                                $isDefaulter = 0;
                                            }
                                        }else{
                                            $defaulterInterest = 0;
                                            $isDefaulter = 0;
                                        }
                                    }else{
                                        $defaulterInterest = 0;
                                        $isDefaulter = 1;
                                    }

                                    if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                        $aviAmount = $val->deposite_amount;
                                        $total = $total+$val->deposite_amount;
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
                                    }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                                        $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                                        if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                     
                                            $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                            $collection = (int) $totalDeposit+(int) $pendingAmount;

                                        }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                            $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

                                        }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                            $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                                        }

                                        $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
                                        if($checkAmount > 0){
                                           $aviAmount = $checkAmount; 

                                           $total = $total+$checkAmount;
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

                                        }else{
                                            $aviAmount = 0;
                                            $total = 0;
                                            $cuurentInterest = 0;
                                            $interest = 0; 
                                            $addInterest = 0; 
                                        }
                                        $totalInterestDeposit = $totalInterestDeposit+$interest;
                                    }
                            }

                            $interstAmount = round($totalInterestDeposit);

                            if($request->type == 2){
                                $finalAmount = round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100));                      
                            }else{
                                $finalAmount = round($totalDeposit+$totalInterestDeposit);
                            }
                            $investAmount = $totalDeposit;

                        }else{

                            $interstAmount =  0;

                            $isDefaulter = 1;

                            $finalAmount = 0;

                            $investAmount = 0;

                        }
                    }elseif(in_array($mInvestment->plan_id, $daily)){

                        if($investmentData){

                                $cMonth = date('m');

                                $cYear = date('Y');

                                $cuurentInterest = $mInvestment->interest_rate;

                                $tenureMonths = $mInvestment->tenure*12;

                                $i = 0;

                                for ($i = 0; $i <= $tenureMonths; $i++){

                                    $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($mInvestment->created_at))); 

                                    $implodeArray = explode('-',$newdate);

                                    $year = $implodeArray[0];
                                    //$month = $implodeArray[1];

                                    $cdate = $mInvestment->created_at;
                                    $cexplodedate = explode('-',$mInvestment->created_at);
                                    if(($cexplodedate[1]+$i) > 12){
                                        $month = ($cexplodedate[1]+$i)-12;
                                    }else{
                                        $month = $cexplodedate[1]+$i;
                                    }

                                    if(($i+1) == 13){
                                        $fRecord = Daybook::where('investment_id', $mInvestment->id)
                                        ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();
                                        if($fRecord){
                                            $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
                                        }else{
                                           $total = Daybook::where('investment_id', $mInvestment->id)
                                        ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit'); 
                                        }
                                    }else{
                                        $total = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->sum('deposit');
                                    }

                                    $totalDeposit = $totalDeposit+$total;

                                    $countDays = Daybook::where('investment_id', $mInvestment->id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->whereIn('transaction_type', [2,4])->count();

                                    if(($mInvestment->deposite_amount*25) > $total){

                                        $defaulterInterest = 1.50;

                                        $isDefaulter = 1;

                                    }else{

                                        $defaulterInterest = 0;

                                        $isDefaulter = 0;

                                    }

                                    if($tenureMonths == 12){
                                        $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
                                    }elseif($tenureMonths == 24){
                                        $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
                                    }elseif($tenureMonths == 36){
                                        $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
                                    }elseif($tenureMonths == 60){
                                        $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
                                    }
                                    if(($tenureMonths-$i) == 0){
                                        $interest = 0;
                                    }
                                    $totalInterestDeposit = $totalInterestDeposit+$interest;

                                }

                            $interstAmount = round($totalInterestDeposit);

                            $finalAmount = round($totalDeposit+$totalInterestDeposit);

                            $investAmount = $totalDeposit;

                        }else{

                            $interstAmount =  0;

                            $finalAmount = 0;

                            $isDefaulter = 1;

                            $investAmount = 0;

                        }
                    }elseif(in_array($mInvestment->plan_id, $preMaturity)){
                        $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>=',$mInvestment->created_at)->sum('deposit');
                        if($investmentData){
                            $cDate = date('Y-m-d');
                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                            if($mInvestment->plan_id == 4){
                                if($monthDiff >= 0 && $monthDiff <= 36){
                                    $cuurentInterest = 8;
                                }else if($monthDiff >= 37 && $monthDiff <= 48){
                                    $cuurentInterest = 8.25;
                                }else if($monthDiff >= 49 && $monthDiff <= 60){
                                    $cuurentInterest = 8.50;
                                }else if($monthDiff >= 61 && $monthDiff <= 72){
                                    $cuurentInterest = 8.75;
                                }else if($monthDiff >= 73 && $monthDiff <= 84){
                                    $cuurentInterest = 9;
                                }else if($monthDiff >= 85 && $monthDiff <= 96){
                                    $cuurentInterest = 9.50;
                                }else if($monthDiff >= 97 && $monthDiff <= 108){
                                    $cuurentInterest = 10;
                                }else if($monthDiff >= 109 && $monthDiff <= 120){
                                    $cuurentInterest = 11;
                                }else{
                                    $cuurentInterest = 11;
                                }
                            }elseif($mInvestment->plan_id == 5){
                                if($monthDiff >= 0 && $monthDiff <= 12){
                                    $cuurentInterest = 5;
                                }else if($monthDiff >= 12 && $monthDiff <= 24){
                                    $cuurentInterest = 6;
                                }else if($monthDiff >= 24 && $monthDiff <= 36){
                                    $cuurentInterest = 6.50;
                                }else if($monthDiff >= 36 && $monthDiff <= 48){
                                    $cuurentInterest = 7;
                                }else if($monthDiff >= 48 && $monthDiff <= 60){
                                    $cuurentInterest = 9;
                                }else{
                                    $cuurentInterest = 9;
                                }
                            }

                            if($mInvestment->plan_id == 4){

                                $defaulterInterest = 0;
                                $isDefaulter = 0;

                                $irate = ($cuurentInterest-$defaulterInterest) / 1;
                                $year = $monthDiff / 12;
                                $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);
                            }else{
                                if($cDate < $maturity_date && $monthDiff != 60){
                                    $defaulterInterest = 1.50;
                                    $isDefaulter = 1;
                                }else{
                                    $defaulterInterest = 0;
                                    $isDefaulter = 0;

                                }
                                
                                $irate = ($cuurentInterest-$defaulterInterest) / 1;
                                $year = $monthDiff / 12;
                                $maturity=0;
                                $freq = 4;
                                for($i=1; $i<=$monthDiff;$i++){
                                    $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
                                    $maturity = $maturity+$rmaturity;
                                }
                                if($maturity > ($mInvestment->deposite_amount*$monthDiff)){
                                    $result =  $maturity-($mInvestment->deposite_amount*$monthDiff);
                                }else{
                                    $result =  $maturity;
                                }
                            }

                            $interstAmount = round($result);
                            $finalAmount = round($totalInvestmentAmount+$interstAmount);
                            $investAmount = $totalInvestmentAmount;
                        }else{
                            $interstAmount =  0;
                            $finalAmount = 0;
                            $isDefaulter = 1;
                            $investAmount = 0;
                        } 
                    }elseif(in_array($mInvestment->plan_id, $fixed)){

                        if($investmentData){

                                $cDate = date('Y-m-d');

                                $cYear = date('Y');

                                $cuurentInterest = $mInvestment->interest_rate;



                                if($cDate < $maturity_date){

                                    $defaulterInterest = 1.50;

                                    $isDefaulter = 1;

                                }else{

                                    $defaulterInterest = 0;

                                    $isDefaulter = 0;

                                }



                            $irate = ($cuurentInterest-$defaulterInterest) / 1;

                            $year = $mInvestment->tenure*12;

                            $interstAmount =  round(( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount));

                            $finalAmount = round($mInvestment->deposite_amount+$interstAmount);

                            $investAmount = $mInvestment->deposite_amount;

                        }else{

                            $interstAmount =  0;

                            $finalAmount = 0;

                            $isDefaulter = 1;

                            $investAmount = 0;

                        }            
                    }elseif(in_array($mInvestment->plan_id, $samraddhJeevan)){

                        if($investmentData){
                            $cDate = $demandAdviceExpenses->date;
                            $investmentMonths = $mInvestment->tenure*12;
                            $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                            if($cDate >= $maturity_date){

                                $defaulterInterest = 6;

                                $isDefaulter = 0;

                                $depositAmount = ($mInvestment->deposite_amount*12)*$mInvestment->tenure;

                                $result = $defaulterInterest*($depositAmount) / 100;

                                $interstAmount = round($result);

                                $finalAmount = round($depositAmount+$result);

                                $investAmount = $depositAmount;
                            }elseif($cDate < $maturity_date){
                                for ($i=1; $i <= $investmentMonths ; $i++){

                                    $val = $mInvestment;
                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
                                    $cMonth = date('m');
                                    $cYear = date('Y');
                                    $cMonth = date('m');
                                    $cYear = date('Y');

                                    if($mInvestment->plan_id == 2){
                                        $cuurentInterest = $val->interest_rate;
                                    }elseif($mInvestment->plan_id == 6){
                                        $cuurentInterest = 11;
                                    }

                                    $totalDeposit = $totalInvestmentAmount;

                                    $d1 = explode('-',$val->created_at);
                                    $d2 = explode('-',$nDate);

                                    $ts1 = strtotime($val->created_at);
                                    $ts2 = strtotime($nDate);

                                    $year1 = date('Y', $ts1);
                                    $year2 = date('Y', $ts2);

                                    $month1 = date('m', $ts1);
                                    $month2 = date('m', $ts2);

                                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                                    $cfAmount = Memberinvestments::where('id',$val->id)->first();
                                    if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                        $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                                        $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

                                        $aviAmount = $val->deposite_amount;
                                        $total = $total+$val->deposite_amount;
                                        if($monthDiff % 12 == 0 && $monthDiff != 0){
                                            $total = $total+$regularInterest;
                                            $cInterest = $regularInterest;
                                        }else{
                                            $total = $total;
                                            $cInterest = 0;
                                        }
                                        $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                                        $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                                        $interest = number_format((float)$a, 2, '.', '');
                                        $totalInterestDeposit = $totalInterestDeposit+($interest);
                                    }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                                        $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;

                                        if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
                     
                                            $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                            $collection = (int) $totalDeposit+(int) $pendingAmount;

                                        }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                            $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

                                        }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                            $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                                        }

                                        $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
                                        if($checkAmount > 0){
                                           $aviAmount = $checkAmount; 

                                           $total = $total+$checkAmount;
                                            if($monthDiff % 12 == 0 && $monthDiff != 0){
                                                $total = $total+$regularInterest;
                                                $cInterest = $regularInterest;
                                            }else{
                                                $total = $total;
                                                $cInterest = 0;
                                            }
                                            $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                                            $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                                            $interest = number_format((float)$a, 2, '.', '');

                                        }else{
                                            $aviAmount = 0;
                                            $total = 0;
                                            $cuurentInterest = 0;
                                            $interest = 0; 
                                            $a = 0;
                                        }
                                        $totalInterestDeposit = $totalInterestDeposit+($interest);
                                    }
                                }
                                $interstAmount = round($totalInterestDeposit);
                                $finalAmount = round($totalDeposit+$totalInterestDeposit);
                                $investAmount = $totalDeposit;
                            }
                        }else{
                            $finalAmount = 0;
                            $isDefaulter = 1;
                            $investAmount = 0;
                            $interstAmount = 0;
                        } 
                    }elseif(in_array($mInvestment->plan_id, $moneyBack)){
                        if($investmentData){
                            $cDate = $demandAdviceExpenses->date;
                            $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
                            $years = floor($diff / (365*60*60*24));
                            if($cDate >= $maturity_date){

                                if (strpos($mInvestment->account_number, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {

                                 $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>',$mInvestment->last_deposit_to_ssb_date)->whereIn('transaction_type', [2,4])->sum('deposit');

                                }else{
                                    $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
                                }

                                //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                                $maturityAmount = getMoneyBackAmount($mInvestment->id);
                                $fAmount = $maturityAmount->available_amount;
                                $isDefaulter = 0;
                                $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');

                                $interstAmount = ($fAmount+$refundAmount)-$totalInvestmentAmount;
                                $finalAmount = $fAmount-$interstAmount;
                                $investAmount = $finalAmount;
                            }elseif($cDate < $maturity_date){

                                if (strpos($mInvestment->account_number, 'R-') !== false && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {

                                    $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
                                    $ts1 = strtotime($sDate);
                                    $ts2 = strtotime($cDate);
                                    $year1 = date('Y', $ts1);
                                    $year2 = date('Y', $ts2);
                                    $month1 = date('m', $ts1);
                                    $month2 = date('m', $ts2);
                                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                                    $investmentMonths = $monthDiff;

                                    $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>',$mInvestment->last_deposit_to_ssb_date)->whereIn('transaction_type', [2,4])->sum('deposit');

                                }else{

                                    $totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                                    $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
                                    $ts1 = strtotime($sDate);
                                    $ts2 = strtotime($cDate);
                                    $year1 = date('Y', $ts1);
                                    $year2 = date('Y', $ts2);
                                    $month1 = date('m', $ts1);
                                    $month2 = date('m', $ts2);
                                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                                    $investmentMonths = $monthDiff;
                                }

                                //$totaldepositAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                                $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();
                                $investmentMonths = $mInvestment->tenure*12;

                                $totalInvestmentAmount = $totaldepositAmount;

                                for ($i=1; $i <= $investmentMonths ; $i++){
                                    $val = $mInvestment;
                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
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
                                    if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                        $aviAmount = $val->deposite_amount;
                                        $total = $total+$val->deposite_amount;
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
                                    }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                                        $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                                        if($checkAmount > 0){
                                           $aviAmount = $checkAmount; 

                                           $total = $total+$checkAmount;
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

                                        }else{
                                            $aviAmount = 0;
                                            $total = 0;
                                            $cuurentInterest = 0;
                                            $interest = 0; 
                                            $addInterest = 0; 
                                        }
                                        $totalInterestDeposit = $totalInterestDeposit+$interest;
                                    }
                                }
                                if($depositInterest){
                                    $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                    $fAmount = round($totalDeposit+$totalInterestDeposit+$availableAmountFd);
                                }else{
                                    $fAmount = round($totalDeposit+$totalInterestDeposit);
                                }
                            
                            
                                $isDefaulter = 0;
                                $refundAmount = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->sum('yearly_deposit_amount');

                                if($refundAmount){
                                    $interstAmount = ($fAmount+$refundAmount)-$totaldepositAmount;
                                    $finalAmount = $fAmount-$interstAmount;
                                    $investAmount = $finalAmount;
                                }else{
                                    $interstAmount = $fAmount-$totaldepositAmount;
                                    $finalAmount = $fAmount-$interstAmount;
                                    $investAmount = $finalAmount;
                                }
                            }
                        }else{
                            $finalAmount = 0;
                            $isDefaulter = 1;
                            $investAmount = 0;
                            $interstAmount = 0;
                        } 
                    } 

                    $sumAmount = $finalAmount;
                    $investAmount = $investAmount+$iseliAmount;*/

                    $sumAmount = $demandAdviceExpenses->maturity_amount_payable;
                    if($demandAdviceExpenses->maturity_prematurity_amount){
                        $investAmount = $demandAdviceExpenses->maturity_prematurity_amount;
                    }else{
                        $investAmount = Daybook::where('investment_id',$demandAdviceExpenses['investment']->id)->whereIn('transaction_type',[2,4])->sum('deposit');
                    }
                    
                    if($investAmount == 0){
                       $investAmount = $demandAdviceExpenses['investment']->deposite_amount+$iseliAmount;
                       $interstAmount = round($demandAdviceExpenses->maturity_amount_payable-$investAmount);
                    }

                    if($investAmount < $demandAdviceExpenses->maturity_amount_payable){
                        $extraInterest = 0;
                        $interstAmount = $demandAdviceExpenses->maturity_amount_payable-$investAmount;
                    }elseif($investAmount > $demandAdviceExpenses->maturity_amount_payable){
                        $extraInterest = 0;
                        $interstAmount = 0;
                    }elseif($investAmount == $demandAdviceExpenses->maturity_amount_payable){
                        $extraInterest = 0;
                        $interstAmount = 0;
                    }

                    $tdsAmountonInterest = $demandAdviceExpenses->tds_amount;
                    $generatedInterest = MemberInvestmentInterest::where('investment_id',$demandAdviceExpenses['investment']->id)->sum('interest_amount');
                    $checkAmount = (($interstAmount+$extraInterest+$tdsAmountonInterest)-round($generatedInterest));
                    
                    /*if($demandAdviceExpenses->maturity_amount_till_date < $demandAdviceExpenses->maturity_amount_payable){
                        $extraInterest = $demandAdviceExpenses->maturity_amount_payable-$demandAdviceExpenses->maturity_amount_till_date;
                    }elseif($demandAdviceExpenses->maturity_amount_till_date > $demandAdviceExpenses->maturity_amount_payable){
                        $extraInterest = 0;
                        $interstAmount = $demandAdviceExpenses->maturity_amount_payable-$investAmount;
                    }elseif($demandAdviceExpenses->maturity_amount_till_date == $demandAdviceExpenses->maturity_amount_payable){
                        $extraInterest = 0;
                    }

                    if (strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false && $request->type == 4  && $mInvestment->plan_id != 3) {

                        $investAmount = Daybook::where('investment_id',$demandAdviceExpenses['investment']->id)->whereIn('transaction_type',[2,4])->sum('deposit');
                        $interstAmount = $demandAdviceExpenses->maturity_amount_payable-$investAmount;
                        $extraInterest = 0;
                    }*/

                    if (strpos($demandAdviceExpenses['investment']->account_number, 'R-') !== false  && $mInvestment->plan_id == 3 && $mInvestment->last_deposit_to_ssb_date != '') {

                        //$investTotalAmount = Daybook::where('investment_id',$demandAdviceExpenses['investment']->id)->whereIn('transaction_type',[2,4])->sum('deposit');
                        $interstAmount = ($demandAdviceExpenses->maturity_amount_payable+$iseliAmount+$moneyBackAmount)-$investAmount;
                        $getMbTrsAmount = getMbTrsAmount($demandAdviceExpenses['investment']->id); 
                        $fdAmount = $getMbTrsAmount->mb_fd_amount;
                        $fdInterest = $iseliAmount-$fdAmount;
                        $extraInterest = $extraInterest+$fdInterest;

                    }

                    /************* TDS **************/
                    /*$checkYear = date("Y", strtotime(convertDate($demandAdviceExpenses->date)));
                    $generatedInterest = MemberInvestmentInterest::where('investment_id',$demandAdviceExpenses['investment']->id)->sum('interest_amount');
                    $checkAmount = (($interstAmount+$extraInterest)-round($generatedInterest));

                    $formG = Form15G::where('member_id',$demandAdviceExpenses['investment']->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
                    if($formG){
                        $tdsAmount = 0;
                        $tdsPercentage = 0;
                    }else{
                        $diff = abs(strtotime($demandAdviceExpenses->date) - strtotime(getMemberData($demandAdviceExpenses['investment']->member_id)));
                        $ageYears = floor($diff / (365*60*60*24));
                        if($ageYears >= 60){
                           $tdsDetail = TdsDeposit::where('type',2)->where('start_date','<',$demandAdviceExpenses->date)->first();
                        }else{
                            $penCard = get_member_id_proof($demandAdviceExpenses['investment']->member_id,5);
                            if($penCard){
                                $tdsDetail = TdsDeposit::where('type',1)->where('start_date','<',$demandAdviceExpenses->date)->first();
                            }else{
                                $tdsDetail = TdsDeposit::where('type',5)->where('start_date','<',$demandAdviceExpenses->date)->first();
                            }
                        }

                        if($tdsDetail){
                            $tdsAmount = $tdsDetail->tds_amount;
                            $tdsPercentage = $tdsDetail->tds_per;
                        }else{
                            $tdsAmount = 0;
                            $tdsPercentage = 0;
                        }
                    }

                    if($checkAmount > $tdsAmount){
                        $tdsAmountonInterest = $tdsPercentage*$interstAmount/100;
                    }else{
                        $tdsAmountonInterest = 0;
                    }*/

                    if(round($checkAmount) > 0){
                        MemberInvestmentInterest::create([
                            'member_id' => $demandAdviceExpenses['investment']->member_id,
                            'investment_id' => $demandAdviceExpenses['investment']->id,
                            'plan_type' => $demandAdviceExpenses['investment']->plan_id,
                            'branch_id' => $demandAdviceExpenses['investment']->branch_id,
                            'deposite_amount' => $investAmount,
                            'interest_amount' => $checkAmount,
                            'date' =>$demandAdviceExpenses->date,
                            'time' =>$entryTime,
                            'created_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($demandAdviceExpenses->date))),
                        ]); 

                    }

                    if(round($tdsAmountonInterest) > 0){

                        $getLastRecord = MemberInvestmentInterestTds::where('member_id',$demandAdviceExpenses['investment']->member_id)->where('investment_id',$demandAdviceExpenses['investment']->id)->orderby('id','desc')->first();
                        MemberInvestmentInterestTds::create([
                            'member_id' => $demandAdviceExpenses['investment']->member_id,
                            'investment_id' => $demandAdviceExpenses['investment']->id,
                            'plan_type' => $demandAdviceExpenses['investment']->plan_id,
                            'branch_id' => $demandAdviceExpenses['investment']->branch_id,
                            'interest_amount' => $checkAmount,
                            'date_from' => date("Y-m-d ".$entryTime."", strtotime(convertDate($demandAdviceExpenses->date))),
                            'date_to' => date("Y-m-d ".$entryTime."", strtotime(convertDate($demandAdviceExpenses->date))),
                            'tdsamount_on_interest' => $tdsAmountonInterest,
                            'tds_amount' => $demandAdviceExpenses->tds_per_amount,
                            'tds_percentage' => $demandAdviceExpenses->tds_percentage,
                            'created_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($demandAdviceExpenses->date))),
                        ]);
                    }
                    /************* TDS **************/

                    Memberinvestments::where('id', $demandAdviceExpenses->investment_id)->update(['is_mature' => 0,'maturity_payable_amount'=>$demandAdviceExpenses->maturity_amount_payable,'maturity_payable_interest'=>$interstAmount,'tds_per'=>$demandAdviceExpenses->tds_percentage,'tds_amount'=>$demandAdviceExpenses->tds_per_amount,'tds_deduct_amount'=>$demandAdviceExpenses->tds_amount,'investment_interest_date'=>$demandAdviceExpenses->date,'investment_interest_tds_date'=>$demandAdviceExpenses->date]);

                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$demandAdviceExpenses['investment']->member_id)->first();

                    if($request->amount_mode == 1 && $ssbAccountDetails == ''){
                        array_push($ssbArray,$value);
                    }else{

                        if($request->amount_mode == 0){

                            $branch_id = $request->branch_id;

                            $type = 13;

                            $jv_unique_id = NULL;

                            if($request->type == 1){

                                $sub_type = 133;

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Maturity';

                                $description_dr = 'Maturity Amount A/C Dr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            }elseif($request->type == 2){

                                $sub_type = 134;

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' PreMaturity';

                                

                                $description_dr = 'PreMaturity Amount A/C Dr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            }elseif($request->type == 3){



                                if($demandAdviceExpenses->death_help_catgeory == 0){

                                    $sub_type = 135;

                                    $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Help';

                                    $description_dr = 'Death Help Amount A/C Dr  '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                                }elseif($demandAdviceExpenses->death_help_catgeory == 1){

                                    $sub_type = 136;

                                    $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Claim';

                                    $description_dr = 'Death Claim Amount A/C Dr  '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                                }

                            }elseif($request->type == 4){

                                $sub_type = 137;

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Emergancy Maturity';

                                $description_dr = 'Emergancy Maturity Amount A/C Dr  '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);
                                
                                $encodeDate = $value;
                                $arrs = array("load_id" => 0, "type" => "14", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Emergency Maturity – Payment of maturity", "data" => $encodeDate);
                                DB::table('user_log')->insert($arrs); 

                            }



                            $type_id = $value;
                            
                            $encodeDate = $value;
                            $arrs = array("load_id" => 0, "type" => "14", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Other Maturity – Payment of maturity", "data" => $encodeDate);
                            DB::table('user_log')->insert($arrs); 

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            $member_id = $demandAdviceExpenses['investment']->member_id;

                            $branch_id_to = NULL;

                            $branch_id_from = $request->branch_id;

                            $opening_balance = $demandAdviceExpenses->maturity_amount_payable+$iseliAmount;

                            $amount = $demandAdviceExpenses->maturity_amount_payable+$iseliAmount;

                            $closing_balance = $demandAdviceExpenses->maturity_amount_payable+$iseliAmount;

                            $description_cr = 'To Cash A/C Cr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            $payment_type = 'DR';

                            $payment_mode = 0;

                            $day_book_payment_mode = 0;

                            $currency_code = 'INR';

                            $amount_to_id =$demandAdviceExpenses['investment']->member_id;

                            $amount_to_name = getMemberData($demandAdviceExpenses['investment']->member_id)->first_name.' '.getMemberData($demandAdviceExpenses['investment']->member_id)->last_name;

                            $amount_from_id = $request->branch_id;

                            $amount_from_name = getBranchDetail($request->branch_id)->name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                        }elseif($request->amount_mode == 1){

                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                            $vno = "";

                            for ($i = 0; $i < 10; $i++) {

                                $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                            }        



                            $branch_id = $demandAdviceExpenses['investment']->branch_id;

                            $type = 13;

                            if($request->type == 1){

                                $sub_type = 133;

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Maturity';

                                $description_dr = 'Maturity Amount A/C Dr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            }elseif($request->type == 2){

                                $sub_type = 134;

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Help';

                                $description_dr = 'PreMaturity Amount A/C Dr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            }elseif($request->type == 3){



                                if($demandAdviceExpenses->death_help_catgeory == 0){

                                    $sub_type = 135;

                                    $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Help';

                                    $description_dr = 'Death Help Amount A/C Dr  '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                                }elseif($demandAdviceExpenses->death_help_catgeory == 1){

                                    $sub_type = 136;

                                    $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Claim';

                                    $description_dr = 'Death Claim Amount A/C Dr  '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                                }

                            }elseif($request->type == 4){

                                $sub_type = 137;

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Emergancy Maturity';

                                $description_dr = 'Emergancy Maturity Amount A/C Dr  '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);
                                
                                $encodeDate = $value;
                                $arrs = array("load_id" => 0, "type" => "14", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Emergency Maturity – Payment of maturity", "data" => $encodeDate);
                                DB::table('user_log')->insert($arrs); 

                            }

                            $type_id = $value;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            $member_id = $demandAdviceExpenses['investment']->member_id;

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $opening_balance = $demandAdviceExpenses->maturity_amount_payable+$iseliAmount;

                            $amount = $demandAdviceExpenses->maturity_amount_payable+$iseliAmount;

                            $closing_balance = $demandAdviceExpenses->maturity_amount_payable+$iseliAmount;

                            $description_cr = 'To Cash A/C Cr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            $payment_type = 'CR';

                            $payment_mode = 3;

                            $day_book_payment_mode = 4;

                            $currency_code = 'INR';

                            $amount_to_id =$demandAdviceExpenses['investment']->member_id;

                            $amount_to_name = getMemberData($demandAdviceExpenses['investment']->member_id)->first_name.' '.getMemberData($demandAdviceExpenses['investment']->member_id)->last_name;

                            $amount_from_id = NULL;

                            $amount_from_name = NULL;

                            $jv_unique_id = NULL;

                            $v_no = $vno;

                            $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = $ssbAccountDetails->id;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                        }elseif($request->amount_mode == 2){

                            $branch_id = $demandAdviceExpenses['investment']->branch_id;

                            $type = 13;

                            if($request->type == 1){

                                $sub_type = 133;

                                $chequeType = 63;

                            }elseif($request->type == 2){

                                $sub_type = 134;

                                $chequeType = 64;

                            }elseif($request->type == 3){

                                if($demandAdviceExpenses->death_help_catgeory == 0){

                                    $sub_type = 135;


                                }elseif($demandAdviceExpenses->death_help_catgeory == 1){

                                    $sub_type = 136;

                                }

                                $chequeType = 65;

                            }elseif($request->type == 4){

                                $sub_type = 137;

                                $chequeType = 66;

                            }

                            $type_id = $value;

                            $type_transaction_id = $value;

                            $associate_id = NULL;

                            $member_id = $demandAdviceExpenses['investment']->member_id;

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $jv_unique_id = NULL;

                            /*$opening_balance = $demandAdviceExpenses->maturity_amount_payable+$request->neft_charge;

                            $amount = $demandAdviceExpenses->maturity_amount_payable+$request->neft_charge;

                            $closing_balance = $demandAdviceExpenses->maturity_amount_payable+$request->neft_charge;*/

       
                            $opening_balance = ($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            $amount = ($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            $closing_balance = ($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);


                            if($request->type == 1){

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Maturity';

                            }elseif($request->type == 2){



                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' PreMaturity';

                            }elseif($request->type == 3){

                                if($demandAdviceExpenses->death_help_catgeory == 0){

                                    $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Help';

                                }elseif($demandAdviceExpenses->death_help_catgeory == 1){

                                    $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Death Claim';

                                }

                            }elseif($request->type == 4){

                                $description = getPlanDetail($demandAdviceExpenses['investment']->plan_id)->name.' Emergancy Maturity';
                                
                                $encodeDate = $value;
                                $arrs = array("load_id" => 0, "type" => "14", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Emergency Maturity – Payment of maturity", "data" => $encodeDate);
                                DB::table('user_log')->insert($arrs); 

                            }

                            $description_dr = 'A/C Dr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            $description_cr = 'To Cash A/C Cr '.($demandAdviceExpenses->maturity_amount_payable+$iseliAmount);

                            $payment_type = 'DR';

                            $currency_code = 'INR';

                            $amount_to_id =$demandAdviceExpenses['investment']->member_id;

                            $amount_to_name = getMemberData($demandAdviceExpenses['investment']->member_id)->first_name.' '.getMemberData($demandAdviceExpenses['investment']->member_id)->last_name;

                            $amount_from_id = $request->bank;

                            $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            if($request->mode == 3){

                                $cheque_type = 1;

                                $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                                $cheque_no = $request->cheque_number;

                                $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                                $cheque_bank_from = $request->bank;

                                $cheque_bank_from_id = $request->bank;

                                $cheque_bank_ac_from = $request->bank_account_number;

                                $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = NULL;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $payment_mode = 1;

                                $day_book_payment_mode = 1;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;



                                SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);



                                SamraddhChequeIssue::create([

                                    'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                    'type' =>6,

                                    'sub_type' =>$chequeType,

                                    'type_id' =>$value,

                                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                    'status' => 1,

                                ]);

                            }elseif($request->mode == 4){

                                $cheque_id = NULL;

                                $cheque_type = NULL;

                                $cheque_no = NULL;

                                $cheque_date = NULL;

                                $cheque_bank_from = NULL;

                                $cheque_bank_from_id = NULL;

                                $cheque_bank_ac_from = NULL;

                                $cheque_bank_ifsc_from = NULL;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_ac_from_id = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = $request->utr_number;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $payment_mode = 2;

                                $day_book_payment_mode = 3;

                            }

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $bank_id = $request->bank;

                            $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                        }

                        $response = DemandAdvice::where('id', $value)->update(['status' => 1,'payment_mode'=> $payment_mode]);

                        $dayBookRef = CommanController::createBranchDayBookReference($amount);

                        if($request->amount_mode == 1){

                            $paymentMode = 4;

                            $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                            $ssb['saving_account_id']=$ssbAccountDetails->id;

                            $ssb['account_no']=$ssbAccountDetails->account_no;

                            /*if($request['pay_file_charge'] == 0){

                                $ssb['opening_balance']=($demandAdviceExpenses->maturity_amount_payable)+($ssbAccountDetails->balance-$eliAmount);

                                $ssb['deposit']=($demandAdviceExpenses->maturity_amount_payable-$eliAmount);
                                

                            }else{

                                $ssb['opening_balance']=$demandAdviceExpenses->maturity_amount_payable+$ssbAccountDetails->balance; 

                                $ssb['deposit']=$demandAdviceExpenses->maturity_amount_payable;

                            }*/

                            $ssb['opening_balance']=($demandAdviceExpenses->maturity_amount_payable)+($ssbAccountDetails->balance+$eliAmount);

                            $ssb['deposit']=($demandAdviceExpenses->maturity_amount_payable+$eliAmount);

                            $ssb['branch_id']=$demandAdviceExpenses['investment']->branch_id;

                            $ssb['type']=10;

                        

                            $ssb['withdrawal']=0;

                            $ssb['description']='Redemption amount received from A/C No. '.$demandAdviceExpenses['investment']->account_number;

                            $ssb['currency_code']='INR';

                            $ssb['payment_type']='CR';

                            $ssb['payment_mode']=3;

                            $ssb['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            $ssbAccountTran = SavingAccountTranscation::create($ssb);

                            $saTranctionId = $ssbAccountTran->id;

                            $saToId = $ssbAccountDetails->id;
                            $saTranctionToId = $ssbAccountTran->id;

                            $ssb_account_tran_id_to = $ssbAccountTran->id;

                            $balance_update=($demandAdviceExpenses->maturity_amount_payable)+($ssbAccountDetails->balance+$eliAmount);

                            

                            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                            $ssbBalance->balance=$balance_update;

                            $ssbBalance->save();



                            $data['saving_account_transaction_id']=$saTranctionId;

                            $data['investment_id']=$demandAdviceExpenses['investment']->id;

                            $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            $satRef = TransactionReferences::create($data);

                            $satRefId = $satRef->id;



                            $amountArraySsb = array('1'=>$demandAdviceExpenses->maturity_amount_payable+$eliAmount);

                            

                            $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');



                            $description = 'Redemption amount received from A/C No. '.$demandAdviceExpenses['investment']->account_number;

                            $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,$demandAdviceExpenses['investment']->associate_id,$ssbAccountDetails->member_id,($demandAdviceExpenses->maturity_amount_payable+$eliAmount)+$ssbAccountDetails->balance,($demandAdviceExpenses->maturity_amount_payable+$eliAmount),$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR'); 

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionId,NULL,NULL,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,NULL,NULL,$cheque_bank_to,$cheque_bank_ac_to,NULL,NULL,NULL,NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,NULL,NULL,$transction_bank_to,$transction_bank_ac_to,NULL,NULL,NULL,NULL,$transction_date,$created_by,$created_by_id);


                            /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/ 


                            $memberTransaction = $this->createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,($demandAdviceExpenses->maturity_amount_payable+$eliAmount),$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL,NULL,$cheque_type,$cheque_id);

                        }else{
                            $saToId = NULL;
                            $saTranctionToId = NULL;
                            $ssb_account_tran_id_to = NULL;
                        }

                        $ssb_account_tran_id_from = NULL;

                        if($demandAdviceExpenses['investment']->plan_id == 2){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 59;

                            $head5 = 80;

                            $head = 80;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 3){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 59;

                            $head5 = 85;

                            $head = 85;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 4){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 57;

                            $head5 = 79;

                            $head = 79;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 5){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 59;

                            $head5 = 81;

                            $head = 81;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 6){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 59;

                            $head5 = 84;

                            $head = 84;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 7){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 58;

                            $head5 = NULL;

                            $head = 58;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 8){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 57;

                            $head5 = 78;

                            $head = 78;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 9){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 57;

                            $head5 = 77;

                            $head = 77;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 10){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 59;

                            $head5 = 83;

                            $head = 83;

                        }elseif($demandAdviceExpenses['investment']->plan_id == 11){

                            $head1 = 1;

                            $head2 = 8;

                            $head3 = 20;

                            $head4 = 59;

                            $head5 = 82;

                            $head = 82;

                        }

                        if($request->amount_mode == 0){

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/



                                $updateBranchCash = $this->updateBranchCashDr($branch_id,$request->created_at,$amount,0);

                                $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id,$request->created_at,$amount,0); 

                        }

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$investAmount,$investAmount,$investAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                        /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$investAmount,$investAmount,$investAmount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/ 

                        if(($interstAmount+$extraInterest) > 0){
                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($interstAmount+$extraInterest),($interstAmount+$extraInterest),($interstAmount+$extraInterest),'INTEREST ON DEPOSITS','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
                        }

                        if($tdsAmountonInterest > 0){

                            $tdsTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,62,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$tdsAmountonInterest,$tdsAmountonInterest,$tdsAmountonInterest,'Tds on interest on deposit','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            //$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,22,62,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$tdsAmountonInterest,$tdsAmountonInterest,$tdsAmountonInterest,'Tds on interest on deposit','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,NULL,NULL,NULL);
                        }

                        /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,14,36,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($interstAmount+$extraInterest),($interstAmount+$extraInterest),($interstAmount+$extraInterest),'INTEREST ON DEPOSITS','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL); */

                        /************** Investment Transactions *********************/
                        $interestArraySsb=array('1'=>($interstAmount+$extraInterest));
                        $investAmountArraySsb=array('1'=>$investAmount);

                        $interestTransaction = CommanController::createTransaction(NULL,15,$demandAdviceExpenses['investment']->id,$demandAdviceExpenses['investment']->member_id,$branch_id,getBranchCode($branch_id)->branch_code,$interestArraySsb,$day_book_payment_mode,$amount_from_name,$amount_from_id,$demandAdviceExpenses['investment']->account_number,$cheque_no,$transction_bank_ac_from,getBranchDetail($branch_id)->name,date("Y-m-d", strtotime(convertDate($request->payment_date))),$transction_no,$bank_id,NULL,'CR');

                        $investAmountTransaction = CommanController::createTransaction(NULL,16,$demandAdviceExpenses['investment']->id,$demandAdviceExpenses['investment']->member_id,$branch_id,getBranchCode($branch_id)->branch_code,$investAmountArraySsb,$day_book_payment_mode,$amount_from_name,$amount_from_id,$demandAdviceExpenses['investment']->account_number,$cheque_no,$transction_bank_ac_from,getBranchDetail($branch_id)->name,date("Y-m-d", strtotime(convertDate($request->payment_date))),$transction_no,$bank_id,NULL,'DR');

                        if($cheque_no) {
                           $chequeId = getSamraddhChequeData($cheque_no)->id;
                        }else{
                            $chequeId = NULL;
                        }

                        if($transction_bank_ac_from){
                            $bankAccountId = getSamraddhBankAccount($transction_bank_ac_from)->id;
                        }else{
                            $bankAccountId = NULL;
                        }

                        $totalbalance = $demandAdviceExpenses['investment']->current_balance+$interstAmount+$extraInterest;

                        $sResult = Memberinvestments::find($demandAdviceExpenses['investment']->id); 
                        $investData['current_balance'] = $totalbalance;
                        $sResult->update($investData);

                        $Interestdescription = 'Bonus amount received';

                        if(($interstAmount+$extraInterest) > 0){
                            $interestDayBook = CommanController::createDayBookNew($interestTransaction,NULL,16,$demandAdviceExpenses['investment']->id,NULL,$demandAdviceExpenses['investment']->member_id,$totalbalance,($interstAmount+$extraInterest),$withdrawal=0,$Interestdescription,NULL,$branch_id,getBranchCode($branch_id)->branch_code,$interestArraySsb,$day_book_payment_mode,$amount_from_name,$amount_from_id,$demandAdviceExpenses['investment']->account_number,$cheque_no,$transction_bank_from,getBranchDetail($branch_id)->name,date("Y-m-d", strtotime(convertDate($request->payment_date))),$transction_no,$bank_id,NULL,'CR',$chequeId,$transction_bank_ac_from,$bankAccountId,$transction_bank_ac_from,$bankAccountId);
                        }

                        if($request->amount_mode == 0){

                            $investAmountdescription = 'Redemption amount transfer cash';

                        }elseif($request->amount_mode == 1){

                            if(getMemberSsbAccountDetail($demandAdviceExpenses['investment']->member_id)){
                                $investAmountdescription = 'Redemption amount transfer to saving account '.getMemberSsbAccountDetail($demandAdviceExpenses['investment']->member_id)->account_no;
                            }else{
                                $investAmountdescription = 'Redemption amount transfer to saving account';
                            }

                        }elseif($request->amount_mode == 2){

                            if($request->mode == 3){

                                $cheque_no = $request->cheque_number;

                                $investAmountdescription = 'Redemption amount transfer to Bank through the cheque ('.$transction_bank_from.', '.$transction_bank_ac_from.', '.$transction_bank_ifsc_from.', '.$cheque_no.')';

                            }elseif($request->mode == 4){

                                $cheque_no = NULL;

                                $investAmountdescription = 'Redemption amount transfer to Bank through online ('.$transction_bank_from.', '.$transction_bank_ac_from.', '.$transction_bank_ifsc_from.', '.$transction_no.')';

                            }

                        }

                        

                        $reinvest = 'R-';
                        if (strpos($reinvest, 'R-') !== false) {
                            $totalInvestbalance = 0;
                        }else{
                            $totalInvestbalance = getInvestmentDetails($demandAdviceExpenses['investment']->id)->current_balance-($investAmount+$interstAmount+$extraInterest);
                        }

                        $sResult = Memberinvestments::find($demandAdviceExpenses['investment']->id); 
                        $investData['current_balance'] = $totalInvestbalance;
                        $sResult->update($investData);

                        //$investAmountDayBook = CommanController::createDayBookNew($investAmountTransaction,NULL,17,$demandAdviceExpenses['investment']->id,NULL,$demandAdviceExpenses['investment']->member_id,$totalInvestbalance,0,($investAmount+$interstAmount+$extraInterest),$investAmountdescription,NULL,$branch_id,getBranchCode($branch_id)->branch_code,$investAmountArraySsb,$day_book_payment_mode,$amount_from_name,$amount_from_id,$demandAdviceExpenses['investment']->account_number,$cheque_no,$transction_bank_from,getBranchName($branch_id)->name,date("Y-m-d", strtotime(convertDate($request->payment_date))),$transction_no,$bank_id,NULL,'DR',$chequeId,$transction_bank_ac_from,$bankAccountId,$transction_bank_ac_from,$bankAccountId);

                        $investAmountDayBook = CommanController::createDayBookNew($investAmountTransaction,NULL,17,$demandAdviceExpenses['investment']->id,NULL,$demandAdviceExpenses['investment']->member_id,$totalInvestbalance,0,$amount,$investAmountdescription,NULL,$branch_id,getBranchCode($branch_id)->branch_code,$investAmountArraySsb,$day_book_payment_mode,$amount_from_name,$amount_from_id,$demandAdviceExpenses['investment']->account_number,$cheque_no,$transction_bank_from,getBranchDetail($branch_id)->name,date("Y-m-d", strtotime(convertDate($request->payment_date))),$transction_no,$bank_id,NULL,'DR',$chequeId,$transction_bank_ac_from,$bankAccountId,$transction_bank_ac_from,$bankAccountId);
                        /************** Investment Transactions *********************/

                        if($request->amount_mode == 2){

                            if($request->amount_mode == 2 && $request->mode == 4){

                                $bankAmount = $amount+$request->neft_charge;

                            }else{

                                $bankAmount = $amount;

                            }



                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bankAmount,$bankAmount,$bankAmount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);



                            if($request->amount_mode == 2 && $request->mode == 4){

                                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($amount+$request->neft_charge),($amount+$request->neft_charge),($amount+$request->neft_charge),$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                    /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }else{

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }
                        }

                        if($request->amount_mode == 2 && $request->mode == 4){

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/


                            $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,$request->neft_charge);
                        } 

                        if($request->amount_mode != 1){

                            $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,($demandAdviceExpenses->maturity_amount_payable+$iseliAmount),$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                        }
                    }

                }elseif($request->type == 0 && $request->subtype == 2){

                    $encodeDate = $value;
                    $arrs = array("load_id" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Demand Advice Application – Payment ", "data" => $encodeDate);
                    DB::table('user_log')->insert($arrs); 

                    $advancedSalary = DemandAdvice::with('employee')->where('id',$value)->first(); 

                    Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date))));

                    $request['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date)));

                    $request['branch_id'] = $advancedSalary->branch_id;



                    $employeeAdvancedSalary = $advancedSalary['employee']->advance_payment+$advancedSalary->amount;

                    $employeeCurrentBalance = $advancedSalary['employee']->current_balance+$advancedSalary->amount;

                    $advancedSalaryUpdate = Employee::where('id',$advancedSalary->employee_id)->update(['advance_payment' => $employeeAdvancedSalary,'current_balance' => $employeeCurrentBalance]);

                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('account_no',$advancedSalary['employee']->ssb_account)->first();

                    if($request->amount_mode == 1 && $ssbAccountDetails == ''){
                        array_push($ssbArray,$value);
                    }else{

                        if($request->amount_mode == 0){

                            $branch_id = $request->branch_id;

                            $type = 13;

                            $sub_type = 138;

                            $jv_unique_id = NULL;

                            $type_id = $advancedSalary->id;

                            $type_transaction_id = $advancedSalary->id;

                            $associate_id = NULL;

                            if($ssbAccountDetails){

                                $member_id = $ssbAccountDetails['ssbMember']->id;

                                $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            }else{
                                $member_id = $advancedSalary['employee']->id;

                                $amount_to_id =$advancedSalary['employee']->id;

                                $amount_to_name = $advancedSalary['employee']->employee_name;
                            }

                            $branch_id_to = NULL;

                            $branch_id_from = $request->branch_id;

                            $opening_balance = $advancedSalary->amount;

                            $amount = $advancedSalary->amount;

                            $closing_balance = $advancedSalary->amount;

                            $description = $advancedSalary->employee_name.' Plan A/C Dr '.$advancedSalary->amount.' - To Cash A/C Cr '.$advancedSalary->amount;

                            $description_dr = $advancedSalary->employee_name.' Plan A/C Dr';

                            $description_cr = 'To Cash A/C Cr '.$advancedSalary->amount;

                            $payment_type = 'DR';

                            $payment_mode = 0;

                            $currency_code = 'INR';

                            $amount_from_id = $request->branch_id;

                            $amount_from_name = getBranchDetail($request->branch_id)->name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            $ssb_account_id_to = NULL;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $employee_id = $advancedSalary->employee_id;

                            $to_bank_name = NULL;

                            $to_bank_branch = NULL;

                            $to_bank_ac_no = NULL;

                            $to_bank_ifsc = NULL;

                            $to_bank_id = NULL;

                            $to_bank_account_id = NULL;

                            $from_bank_name = NULL;

                            $from_bank_branch = NULL;

                            $from_bank_ac_no = NULL;

                            $from_bank_ifsc = NULL;

                            $from_bank_id = NULL;

                            $from_bank_ac_id = NULL;

                            $transaction_date = NULL;

                            $transaction_charge = NULL;

                            $cheque_id = NULL;

                        }elseif($request->amount_mode == 1){

                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                            $vno = "";

                            for ($i = 0; $i < 10; $i++) {

                                $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                            }        

                            $branch_id = $advancedSalary['employee']->branch_id;

                            $type = 13;

                            $sub_type = 138;

                            $type_id = $advancedSalary->id;

                            $type_transaction_id = $advancedSalary->id;

                            $associate_id = NULL;

                            if($ssbAccountDetails){

                                $member_id = $ssbAccountDetails['ssbMember']->id;

                                $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            }else{
                                $member_id = $advancedSalary['employee']->id;

                                $amount_to_id =$advancedSalary['employee']->id;

                                $amount_to_name = $advancedSalary['employee']->employee_name;
                            }

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $jv_unique_id = NULL;

                            $opening_balance = $advancedSalary->amount;

                            $amount = $advancedSalary->amount;

                            $closing_balance = $advancedSalary->amount;

                            $description = $advancedSalary->employee_name.' Plan A/C Dr '.$advancedSalary->amount.' - To '.$advancedSalary['employee']->ssb_account.' A/C Cr '.$advancedSalary->amount;

                            $description_dr = $advancedSalary->employee_name.' Plan A/C Dr';

                            $description_cr = 'To '.$advancedSalary['employee']->ssb_account.' A/C Cr '.$advancedSalary->amount;

                            $payment_type = 'CR';

                            $payment_mode = 3;

                            $currency_code = 'INR';


                            $amount_from_id = NULL;

                            $amount_from_name = NULL;

                            $v_no = $vno;

                            $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            $ssb_account_id_from = NULL;

                            $cheque_type = NULL;

                            $cheque_id = NULL;

                            $cheque_no = NULL;

                            $cheque_date = NULL;

                            $cheque_bank_to_name = NULL;

                            $cheque_bank_to_branch = NULL;

                            $cheque_bank_from = NULL;

                            $cheque_bank_from_id = NULL;

                            $cheque_bank_ac_from = NULL;

                            $cheque_bank_ac_from_id = NULL;

                            $cheque_bank_to_ac_no = NULL;

                            $cheque_bank_ifsc_from = NULL;

                            $cheque_bank_branch_from = NULL;

                            $cheque_bank_to = NULL;

                            $cheque_bank_ac_to = NULL;

                            $cheque_bank_to_ifsc = NULL;

                            $transction_no = NULL;

                            $transction_bank_from = NULL;

                            $transction_bank_from_id = NULL;

                            $transction_bank_ac_from = NULL;

                            $transction_bank_ifsc_from = NULL;

                            $transction_bank_branch_from = NULL;

                            $transction_bank_from_ac_id = NULL;

                            $transction_bank_to = NULL;

                            $transction_bank_ac_to = NULL;

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $transction_bank_to_name = NULL;

                            $transction_bank_to_ac_no = NULL;

                            $transction_bank_to_branch = NULL;

                            $transction_bank_to_ifsc = NULL;

                            $ssb_account_id_to = $advancedSalary['employee']->ssb_id;

                            $to_bank_name = NULL;

                            $to_bank_branch = NULL;

                            $to_bank_ac_no = NULL;

                            $to_bank_ifsc = NULL;

                            $to_bank_id = NULL;

                            $to_bank_account_id = NULL;

                            $from_bank_name = NULL;

                            $from_bank_branch = NULL;

                            $from_bank_ac_no = NULL;

                            $from_bank_ifsc = NULL;

                            $from_bank_id = NULL;

                            $from_bank_ac_id = NULL;

                            $transaction_date = NULL;

                            $transaction_charge = NULL;

                            $cheque_id = NULL;

                        }elseif($request->amount_mode == 2){

                            $branch_id = $advancedSalary['employee']->branch_id;

                            $type = 13;

                            $sub_type = 138;

                            $type_id = $advancedSalary->id;

                            $type_transaction_id = $advancedSalary->id;

                            $associate_id = NULL;

                            if($ssbAccountDetails){

                                $member_id = $ssbAccountDetails['ssbMember']->id;

                                $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            }else{
                                $member_id = $advancedSalary['employee']->id;

                                $amount_to_id =$advancedSalary['employee']->id;

                                $amount_to_name = $advancedSalary['employee']->employee_name;
                            }

                            $branch_id_to = NULL;

                            $branch_id_from = NULL;

                            $jv_unique_id = NULL;

                            $opening_balance = $advancedSalary->amount;

                            $amount = $advancedSalary->amount;

                            $closing_balance = $advancedSalary->amount;

                            $description = $advancedSalary->employee_name.' Plan A/C Dr '.$advancedSalary->amount.' - To '.$advancedSalary['employee']->ssb_account.' A/C Cr '.$advancedSalary->amount;

                            $description_dr = $advancedSalary->employee_name.' Plan A/C Dr';

                            $description_cr = 'To '.$advancedSalary['employee']->ssb_account.' A/C Cr '.$advancedSalary->amount;

                            $payment_type = 'DR';

                            $currency_code = 'INR';

                            $amount_from_id = $request->bank;

                            $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                            $v_no = NULL;

                            $v_date = NULL;

                            $ssb_account_id_from = NULL;

                            if($request->mode == 3){

                                $cheque_type = 1;

                                $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                                $cheque_no = $request->cheque_number;

                                $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                                $cheque_bank_from = $request->bank;

                                $cheque_bank_from_id = $request->bank;

                                $cheque_bank_ac_from = $request->bank_account_number;

                                $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = NULL;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $payment_mode = 1;

                                $transaction_charge = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;



                                SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);



                                SamraddhChequeIssue::create([

                                    'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                    'type' =>6,

                                    'sub_type' =>67,

                                    'type_id' =>$value,

                                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                    'status' => 1,

                                ]);

                            }elseif($request->mode == 4){

                                $cheque_id = NULL;

                                $cheque_type = NULL;

                                $cheque_no = NULL;

                                $cheque_date = NULL;

                                $cheque_bank_from = NULL;

                                $cheque_bank_from_id = NULL;

                                $cheque_bank_ac_from = NULL;

                                $cheque_bank_ifsc_from = NULL;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_from_id = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $cheque_bank_ac_to = NULL;

                                $transction_no = $request->utr_number;

                                $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                $transction_bank_ac_from = $request->bank_account_number;

                                $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transction_bank_branch_from = getSamraddhBankAccount($request->bank_account_number)->branch_name;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $payment_mode = 2;

                                $transaction_charge = $request->neft_charge;

                            }

                            $transction_date = NULL;

                            $entry_date = NULL;

                            $entry_time = NULL;

                            $created_by = 1;

                            $created_by_id = Auth::user()->id;

                            $is_contra = NULL;

                            $contra_id = NULL;

                            $created_at = NULL;

                            $bank_id = NULL;

                            $bank_ac_id = NULL;

                            $bank_id = $request->bank;

                            $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $ssb_account_id_to = NULL;

                            $to_bank_name = $advancedSalary['employee']->bank_name;

                            $to_bank_branch = $advancedSalary['employee']->bank_name;

                            $to_bank_ac_no = $advancedSalary['employee']->bank_account_no;

                            $to_bank_ifsc = $advancedSalary['employee']->bank_ifsc_code;

                            $to_bank_id = NULL;

                            $to_bank_account_id = NULL;

                            $from_bank_name = getSamraddhBank($request->bank)->bank_name;

                            $from_bank_branch = getSamraddhBankAccount($request->bank_account_number)->branch_name;

                            $from_bank_ac_no = $request->bank_account_number;

                            $from_bank_ifsc = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                            $from_bank_id = $request->bank;

                            $from_bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                            $transaction_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                        }



                        $response = DemandAdvice::where('id', $value)->update(['status' => 1,'payment_mode'=> $payment_mode]);



                        $dayBookRef = CommanController::createBranchDayBookReference($amount);



                        $this->employeeSalaryLeaser($advancedSalary->employee_id,$branch_id,2,$type_id,$employeeCurrentBalance,$amount,$withdrawal=NULL,'Advanced salary amount A/C Cr '.$amount.'',$currency_code,'CR',$payment_mode,1,date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at))),$updated_at=NULL,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transction_no,$transaction_date,$transaction_charge);

                        $this->employeeLedgerBackDateCR($advancedSalary->employee_id,$request->created_at,$amount);

                        if($request->amount_mode == 1){

                            $paymentMode = 4;

                            $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                            $ssb['saving_account_id']=$ssbAccountDetails->id;

                            $ssb['account_no']=$ssbAccountDetails->account_no;

                            $ssb['opening_balance']=$amount+$ssbAccountDetails->balance;

                            $ssb['deposit']=$amount;

                            $ssb['branch_id']=$advancedSalary['employee']->branch_id;

                            $ssb['type']=10;

                            $ssb['withdrawal']=0;

                            $ssb['description']=$description;

                            $ssb['currency_code']='INR';

                            $ssb['payment_type']='CR';

                            $ssb['payment_mode']=3;

                            $ssb['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                            $ssbAccountTran = SavingAccountTranscation::create($ssb);

                            $saTranctionId = $ssbAccountTran->id;

                            $saToId = $ssbAccountTran->id;

                            $saTranctionToId = $ssbAccountTran->id;

                            $balance_update=$amount+$ssbAccountDetails->balance;

                            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                            $ssbBalance->balance=$balance_update;

                            $ssbBalance->save();

                            $data['saving_account_transaction_id']=$saTranctionId;

                            $data['type']=2;

                            $data['type_id']=$advancedSalary->id;

                            $data['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                            $satRef = TransactionReferences::create($data);

                            $satRefId = $satRef->id;

                            $amountArraySsb = array('1'=>$amount);

                            $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                            $description = $description;

                            $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,$associate_id=NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR'); 

                            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/

                            $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saTranctionToId,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                        }else{
                            $saToId = NULL;
                            $saTranctionToId = NULL;
                        }

                        if($request->amount_mode == 0){



                            $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            $updateBranchCash = $this->updateBranchCashDr($branch_id,$request->created_at,$amount,0);

                            $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id,$request->created_at,$amount,0); 

                        }

                        $allTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,73,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,29,73,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/  

                        if($request->amount_mode == 2){



                            if($request->amount_mode == 2 && $request->mode == 4){

                                $bankAmount = $amount+$request->neft_charge;

                            }else{

                                $bankAmount = $amount;

                            }



                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bankAmount,$bankAmount,$bankAmount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);

                            if($request->amount_mode == 2 && $request->mode == 4){

                                $allTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge+$amount,$request->neft_charge+$amount,$request->neft_charge+$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge+$amount,$request->neft_charge+$amount,$request->neft_charge+$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }else{

                                $allTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }

                        }

                        if($request->amount_mode == 2 && $request->mode == 4){

                            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);    

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,$request->neft_charge);

                        } 
                    }

                }elseif($request->type == 0 && $request->subtype == 3){
                    
                    $encodeDate = $value;
                    $arrs = array("load_id" => 0, "type" => "13", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Demand Advice Application – Payment ", "data" => $encodeDate);
                    DB::table('user_log')->insert($arrs); 
                    

                    $response = DemandAdvice::where('id', $value)->update(['status' => 1]);

                    $advancedOwner = DemandAdvice::with('owner')->where('id',$value)->first(); 

                    Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date))));

                    $request['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->payment_date)));

                    $request['branch_id'] = $advancedOwner->branch_id;



                    $ownerAdvancedRent = $advancedOwner['owner']->advance_payment+$advancedOwner->amount;

                    $ownerCurrentRent = $advancedOwner['owner']->current_balance+$advancedOwner->amount;

                    RentLiability::where('id',$advancedOwner->owner_id)->update(['advance_payment' => $ownerAdvancedRent,'current_balance' => $ownerCurrentRent]);

                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('id',$advancedOwner['owner']->owner_ssb_id)->first();

                    if($request->amount_mode == 1 && $ssbAccountDetails == ''){
                        array_push($ssbArray,$value);
                    }else{
 
                            if($request->amount_mode == 0){

                                $branch_id = $request->branch_id;

                                $type = 13;

                                $sub_type = 139;

                                $type_id = $advancedOwner->id;

                                $type_transaction_id = $advancedOwner->id;

                                $associate_id = NULL;

                                if($ssbAccountDetails){

                                    $member_id = $ssbAccountDetails['ssbMember']->id;

                                    $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                    $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                                }else{
                                    $member_id = $advancedOwner['owner']->id;

                                    $amount_to_id =$advancedOwner['owner']->id;

                                    $amount_to_name = $advancedOwner['owner']->owner_name;
                                }

                                $branch_id_to = NULL;

                                $branch_id_from = $request->branch_id;

                                $opening_balance = $advancedOwner->amount;

                                $amount = $advancedOwner->amount;

                                $closing_balance = $advancedOwner->amount;

                                $description = $advancedOwner->owner_name.' Plan A/C Dr '.$advancedOwner->amount.' - To Cash A/C Cr '.$advancedOwner->amount;

                                $description_dr = $advancedOwner->owner_name.' Plan A/C Dr';

                                $description_cr = 'To Cash A/C Cr '.$advancedOwner->amount;

                                $payment_type = 'DR';

                                $payment_mode = 0;

                                $currency_code = 'INR';

                                $amount_from_id = $request->branch_id;

                                $amount_from_name = getBranchDetail($request->branch_id)->name;

                                $v_no = NULL;

                                $v_date = NULL;

                                $jv_unique_id = NULL;

                                $ssb_account_id_from = NULL;

                                $ssb_account_id_to = NULL;

                                $cheque_type = NULL;

                                $cheque_id = NULL;

                                $cheque_no = NULL;

                                $cheque_date = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_from = NULL;

                                $cheque_bank_from_id = NULL;

                                $cheque_bank_ac_from_id = NULL;

                                $cheque_bank_ac_from = NULL;

                                $cheque_bank_ifsc_from = NULL;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = NULL;

                                $transction_bank_from = NULL;

                                $transction_bank_from_id = NULL;

                                $transction_bank_ac_from = NULL;

                                $transction_bank_ifsc_from = NULL;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_bank_from_ac_id = NULL;

                                $transction_date = NULL;

                                $entry_date = NULL;

                                $entry_time = NULL;

                                $created_by = 1;

                                $created_by_id = Auth::user()->id;

                                $is_contra = NULL;

                                $contra_id = NULL;

                                $created_at = NULL;

                                $bank_id = NULL;

                                $bank_ac_id = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $ssb_account_id_to = NULL;

                                $to_bank_name = NULL;

                                $to_bank_branch = NULL;

                                $to_bank_ac_no = NULL;

                                $to_bank_ifsc = NULL;

                                $to_bank_id = NULL;

                                $to_bank_account_id = NULL;

                                $from_bank_name = NULL;

                                $from_bank_branch = NULL;

                                $from_bank_ac_no = NULL;

                                $from_bank_ifsc = NULL;

                                $from_bank_id = NULL;

                                $from_bank_ac_id = NULL;

                                $transaction_date = NULL;

                                $transaction_charge = NULL;

                                $ssb_account_tran_id_from=NULL;

                                $cheque_id = NULL;

                            }elseif($request->amount_mode == 1){

                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

                                $vno = "";

                                for ($i = 0; $i < 10; $i++) {

                                    $vno .= $chars[mt_rand(0, strlen($chars)-1)];

                                }        



                                $branch_id = $advancedOwner['owner']->branch_id;

                                $type = 13;

                                $sub_type = 139;

                                $type_id = $advancedOwner->id;

                                $type_transaction_id = $advancedOwner->id;

                                $associate_id = NULL;

                                if($ssbAccountDetails){

                                    $member_id = $ssbAccountDetails['ssbMember']->id;

                                    $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                    $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                                }else{
                                    $member_id = $advancedOwner['owner']->id;

                                    $amount_to_id =$advancedOwner['owner']->id;

                                    $amount_to_name = $advancedOwner['owner']->owner_name;
                                }

                                $jv_unique_id = NULL;

                                $branch_id_to = NULL;

                                $branch_id_from = NULL;

                                $opening_balance = $advancedOwner->amount;

                                $amount = $advancedOwner->amount;

                                $closing_balance = $advancedOwner->amount;

                                $description = $advancedOwner->owner_name.' Plan A/C Dr '.$advancedOwner->amount.' - To Cash A/C Cr '.$advancedOwner->amount;

                                $description_dr = $advancedOwner->owner_name.' Plan A/C Dr';

                                $description_cr = 'To Cash A/C Cr '.$advancedOwner->amount;

                                $payment_type = 'CR';

                                $payment_mode = 3;

                                $currency_code = 'INR';

                                $amount_from_id = NULL;

                                $amount_from_name = NULL;

                                $v_no = $vno;

                                $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                                $ssb_account_id_from = NULL;

                                $cheque_type = NULL;

                                $cheque_id = NULL;

                                $cheque_no = NULL;

                                $cheque_date = NULL;

                                $cheque_bank_to_name = NULL;

                                $cheque_bank_to_branch = NULL;

                                $cheque_bank_from = NULL;

                                $cheque_bank_ac_from = NULL;

                                $cheque_bank_from_id = NULL;

                                $cheque_bank_branch_from = NULL;

                                $cheque_bank_to = NULL;

                                $cheque_bank_ac_to = NULL;

                                $cheque_bank_ac_from_id = NULL;

                                $cheque_bank_to_ac_no = NULL;

                                $cheque_bank_to_ifsc = NULL;

                                $transction_no = NULL;

                                $transction_bank_from = NULL;

                                $transction_bank_from_id = NULL;

                                $transction_bank_ac_from = NULL;

                                $transction_bank_ifsc_from = NULL;

                                $transction_bank_from_ac_id = NULL;

                                $transction_bank_branch_from = NULL;

                                $transction_bank_to = NULL;

                                $transction_bank_ac_to = NULL;

                                $transction_date = NULL;

                                $entry_date = NULL;

                                $entry_time = NULL;

                                $created_by = 1;

                                $created_by_id = Auth::user()->id;

                                $is_contra = NULL;

                                $contra_id = NULL;

                                $created_at = NULL;

                                $bank_id = NULL;

                                $bank_ac_id = NULL;

                                $transction_bank_to_name = NULL;

                                $transction_bank_to_ac_no = NULL;

                                $transction_bank_to_branch = NULL;

                                $transction_bank_to_ifsc = NULL;

                                $ssb_account_id_to = $advancedOwner['owner']->owner_ssb_id;

                                $to_bank_name = NULL;

                                $to_bank_branch = NULL;

                                $to_bank_ac_no = NULL;

                                $to_bank_ifsc = NULL;

                                $to_bank_id = NULL;

                                $to_bank_account_id = NULL;

                                $from_bank_name = NULL;

                                $from_bank_branch = NULL;

                                $from_bank_ac_no = NULL;

                                $from_bank_ifsc = NULL;

                                $from_bank_id = NULL;

                                $from_bank_ac_id = NULL;

                                $transaction_date = NULL;

                                $transaction_charge = NULL;

                                $cheque_id = NULL;

                            }elseif($request->amount_mode == 2){

                                $branch_id = $advancedOwner['owner']->branch_id;

                                $type = 13;

                                $sub_type = 139;

                                $type_id = $advancedOwner->id;

                                $type_transaction_id = $advancedOwner->id;

                                $associate_id = NULL;

                                if($ssbAccountDetails){

                                    $member_id = $ssbAccountDetails['ssbMember']->id;

                                    $amount_to_id =$ssbAccountDetails['ssbMember']->id;

                                    $amount_to_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                                }else{
                                    $member_id = $advancedOwner['owner']->id;

                                    $amount_to_id =$advancedOwner['owner']->id;

                                    $amount_to_name = $advancedOwner['owner']->owner_name;
                                }

                                $branch_id_to = NULL;

                                $branch_id_from = NULL;

                                $opening_balance = $advancedOwner->amount;

                                $amount = $advancedOwner->amount;

                                $closing_balance = $advancedOwner->amount;

                                $description = $advancedOwner->owner_name.' Plan A/C Dr '.$advancedOwner->amount.' - To Cash A/C Cr '.$advancedOwner->amount;

                                $description_dr = $advancedOwner->owner_name.' Plan A/C Dr';

                                $description_cr = 'To Cash A/C Cr '.$advancedOwner->amount;

                                $payment_type = 'DR';

                                $currency_code = 'INR';

                                $amount_from_id = $request->bank;

                                $amount_from_name = getSamraddhBank($request->bank)->bank_name;

                                $v_no = NULL;

                                $v_date = NULL;

                                $ssb_account_id_from = NULL;

                                $jv_unique_id = NULL;

                                $ssb_account_id_to = NULL;
                                if($request->mode == 3){

                                    $cheque_type = 1;

                                    $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                                    $cheque_no = $request->cheque_number;

                                    $cheque_date = getSamraddhChequeData($request->cheque_number)->cheque_create_date;

                                    $cheque_bank_from = $request->bank;

                                    $cheque_bank_ac_from = $request->bank_account_number;

                                    $cheque_bank_from_id = $request->bank;

                                    $cheque_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                    $cheque_bank_ac_from_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                    $cheque_bank_branch_from = NULL;

                                    $cheque_bank_to = NULL;

                                    $cheque_bank_ac_to = NULL;

                                    $cheque_bank_to_name = NULL;

                                    $cheque_bank_to_branch = NULL;

                                    $cheque_bank_to_ac_no = NULL;

                                    $cheque_bank_to_ifsc = NULL;

                                    $transction_no = NULL;

                                    $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                    $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                    $transction_bank_ac_from = $request->bank_account_number;

                                    $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                    $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                    $transction_bank_branch_from = NULL;

                                    $transction_bank_to = NULL;

                                    $transction_bank_ac_to = NULL;

                                    $payment_mode = 1;

                                    $day_book_payment_mode = 1;

                                    $transction_bank_to_name = NULL;

                                    $transction_bank_to_ac_no = NULL;

                                    $transction_bank_to_branch = NULL;

                                    $transction_bank_to_ifsc = NULL;

                                    $transaction_charge = NULL;

                                    $cheque_id = getSamraddhChequeData($request->cheque_number)->id;

                                    SamraddhCheque::where('cheque_no',$request->cheque_number)->update(['status' => 3,'is_use'=>1]);

                                    SamraddhChequeIssue::create([

                                        'cheque_id' => getSamraddhChequeData($request->cheque_number)->id,

                                        'type' =>6,

                                        'sub_type' =>68,

                                        'type_id' =>$value,

                                        'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request->created_at))),

                                        'status' => 1,

                                    ]);


                                }elseif($request->mode == 4){

                                    $cheque_id = NULL;

                                    $cheque_type = NULL;

                                    $cheque_no = NULL;

                                    $cheque_date = NULL;

                                    $cheque_bank_from = NULL;

                                    $cheque_bank_from_id = NULL;

                                    $cheque_bank_ac_from = NULL;

                                    $cheque_bank_ifsc_from = NULL;

                                    $cheque_bank_branch_from = NULL;

                                    $cheque_bank_to = NULL;

                                    $cheque_bank_ac_from_id = NULL;

                                    $cheque_bank_to_name = NULL;

                                    $cheque_bank_to_branch = NULL;

                                    $cheque_bank_to_ac_no = NULL;

                                    $cheque_bank_to_ifsc = NULL;

                                    $cheque_bank_ac_to = NULL;

                                    $transction_no = $request->utr_number;

                                    $transction_bank_from = getSamraddhBank($request->bank)->bank_name;

                                    $transction_bank_from_id = getSamraddhBank($request->bank)->id;

                                    $transction_bank_ac_from = $request->bank_account_number;

                                    $transction_bank_ifsc_from = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                    $transction_bank_from_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                    $transction_bank_branch_from = NULL;

                                    $transction_bank_to = NULL;

                                    $transction_bank_ac_to = NULL;

                                    $transction_bank_to_name = NULL;

                                    $transction_bank_to_ac_no = NULL;

                                    $transction_bank_to_branch = NULL;

                                    $transction_bank_to_ifsc = NULL;

                                    $payment_mode = 2;

                                    $day_book_payment_mode = 3;

                                    $transaction_charge = $request->neft_charge;

                                }

                                $transction_date = NULL;

                                $entry_date = NULL;

                                $entry_time = NULL;

                                $created_by = 1;

                                $created_by_id = Auth::user()->id;

                                $is_contra = NULL;

                                $contra_id = NULL;

                                $created_at = NULL;

                                $bank_id = NULL;

                                $bank_ac_id = NULL;

                                $bank_id = $request->bank;

                                $bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $ssb_account_id_to = NULL;

                                $to_bank_name = $advancedOwner['owner']->owner_bank_name;

                                $to_bank_branch = NULL;

                                $to_bank_ac_no = $advancedOwner['owner']->owner_bank_account_number;

                                $to_bank_ifsc = $advancedOwner['owner']->owner_bank_ifsc_code;

                                $to_bank_id = NULL;

                                $to_bank_account_id = NULL;

                                $from_bank_name = getSamraddhBank($request->bank)->bank_name;

                                $from_bank_branch = getSamraddhBankAccount($request->bank_account_number)->branch_name;

                                $from_bank_ac_no = $request->bank_account_number;

                                $from_bank_ifsc = getSamraddhBankAccount($request->bank_account_number)->ifsc_code;

                                $from_bank_id = $request->bank;

                                $from_bank_ac_id = getSamraddhBankAccount($request->bank_account_number)->id;

                                $transaction_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at)));

                            }
                            $response = DemandAdvice::where('id', $value)->update(['status' => 1,'payment_mode'=> $payment_mode]);

                            $dayBookRef = CommanController::createBranchDayBookReference($amount);

                            $this->RentLiabilityLedger($advancedOwner->owner_id,2,$type_id,$ownerCurrentRent,$amount,$withdrawal=NULL,'Advanced rent amount A/C Cr '.$amount.'',$currency_code,'CR',$payment_mode,1,date("Y-m-d ".$entryTime."", strtotime(convertDate($request->created_at))),$updated_at=NULL,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transction_no,$transaction_date,$transaction_charge);

                            $this->rentLedgerBackDateCR($advancedOwner->owner_id,$request->created_at,$amount);

                            if($request->amount_mode == 1){

                                $paymentMode = 4;

                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                                $ssb['saving_account_id']=$ssbAccountDetails->id;

                                $ssb['account_no']=$ssbAccountDetails->account_no;

                                $ssb['opening_balance']=$amount+$ssbAccountDetails->balance;

                                $ssb['deposit']=$amount;

                                $ssb['branch_id']=$advancedOwner['owner']->branch_id;

                                $ssb['type']=10;

                                $ssb['withdrawal']=0;

                                $ssb['description']=$description;

                                $ssb['currency_code']='INR';

                                $ssb['payment_type']='CR';

                                $ssb['payment_mode']=3;

                                $ssb['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                                $ssbAccountTran = SavingAccountTranscation::create($ssb);

                                $saTranctionId = $ssbAccountTran->id;

                                $saToId = $ssbAccountDetails->id;

                                $saTranctionToId = $ssbAccountTran->id;

                                $balance_update=$amount+$ssbAccountDetails->balance;

                                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

                                $ssbBalance->balance=$balance_update;

                                $ssbBalance->save();

                                $data['saving_account_transaction_id']=$saTranctionId;

                                $data['type']=3;

                                $data['type_id']=$advancedOwner->id;

                                $data['created_at']=date("Y-m-d", strtotime(convertDate($request->created_at)));

                                $satRef = TransactionReferences::create($data);

                                $satRefId = $satRef->id;

                                $amountArraySsb = array('1'=>$amount);

                                $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                $description = $description;

                                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,$associate_id=NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->created_at))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id); 

                                /*$allTransaction = $this->createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NULL);*/ 

                                $memberTransaction = $this->createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                            }else{
                                $saToId = NULL;
                                $saTranctionToId = NULL;
                            }

                            if($request->amount_mode == 0){
                             
                               
                                $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id,$cheque_type,$cheque_id);
                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from=NULL,$ssb_account_id_to=NULL,$saTranctionToId,$ssb_account_tran_id_from=NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                                $updateBranchCash = $this->updateBranchCashDr($branch_id,$request->created_at,$amount,0);

                                $updateBranchClosing = $this->updateBranchClosingCashDr($branch_id,$request->created_at,$amount,0); 

                            }

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,74,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from=NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,29,74,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$saToId,$saTranctionToId,NNULL);*/  

                        if($request->amount_mode == 2){

                            if($request->amount_mode == 2 && $request->mode == 4){

                                $bankAmount = $amount+$request->neft_charge;

                            }else{

                                $bankAmount = $amount;

                            }
                            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bankAmount,$bankAmount,$bankAmount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$saTranctionToId,$ssb_account_tran_id_from=NULL);

                            if($request->amount_mode == 2 && $request->mode == 4){

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge+$amount,$request->neft_charge+$amount,$request->neft_charge+$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$saTranctionToId,$ssb_account_tran_id_from=NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
                                                            dd("fghgfh");

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge+$amount,$request->neft_charge+$amount,$request->neft_charge+$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }else{

                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($request->bank)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from=NULL,$ssb_account_id_to=NULL,$saTranctionToId,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,27,getSamraddhBank($request->bank)->account_head_id,NUll,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            }

                        }



                        if($request->amount_mode == 2 && $request->mode == 4){

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,142,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$saTranctionToId,$ssb_account_tran_id_from=NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,92,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->neft_charge,$request->neft_charge,$request->neft_charge,'NEFT Charge A/c Cr '.$request->neft_charge.'','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/

                            $updateBackDateloanBankBalance = CommanController::updateBackDateloanBankBalance($amount,$request->bank,getSamraddhBankAccount($request->bank_account_number)->id,$request->created_at,$request->neft_charge);

                        } 
                    }
                }

            } 

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        if(count($ssbArray) > 0){
            $ssbString = implode(",",$ssbArray);

            if($request->type == 4){        

                return redirect()->route('admin.emergancymaturity.index')->with('success', 'Approved emergancy maturity successfully AND '.$ssbString.' demand advice not have any ssb account!');

            }else{

               return redirect()->route('admin.demand.application')->with('success', 'Approved demand advice successfully AND '.$ssbString.' demand advice not have any ssb account!'); 

            }

        }else{
            if($request->type == 4){        

                return redirect()->route('admin.emergancymaturity.index')->with('success', 'Approved emergancy maturity successfully!');

            }else{

               return redirect()->route('admin.demand.application')->with('success', 'Approved demand advice successfully!'); 

            }
        }       

    }

    public static function updateBranchCashCr($branch_id,$date,$amount,$type)

    { 

        $entryDate = date("Y-m-d", strtotime(convertDate($date)));

        $entryTime = date("H:i:s", strtotime(convertDate($date)));

        $currentDateRecord = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date',$entryDate)->first();

        

        if($currentDateRecord){

            $Result = \App\Models\BranchCash::find($currentDateRecord->id);            

            if($type == 0){ 

                $data['balance']=$currentDateRecord->balance+$amount;

            }elseif($type == 1){

                $data['loan_balance']=$currentDateRecord->loan_balance+$amount; 

            } 

            $data['updated_at']=$date;

            $Result->update($data);

            

            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



            if($getNextBranchRecord){

                foreach ($getNextBranchRecord as $key => $value) {

                    $sResult = \App\Models\BranchCash::find($value->id);

                    if($type == 1){

                        $sData['loan_opening_balance']=$value->loan_closing_balance; 

                        $sData['loan_balance']=$value->loan_balance+$amount; 

                        if($value->closing_balance > 0){

                            $sData['loan_closing_balance']=$value->loan_closing_balance+$amount;   

                        } 

                    }elseif($type == 0){

                        $sData['opening_balance']=$value->closing_balance; 

                        $sData['balance']=$value->balance+$amount;    

                        if($value->closing_balance > 0){

                            $sData['closing_balance']=$value->closing_balance+$amount;   

                        }        

                    }

                    $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                    $sResult->update($sData);

                }

            }



        }else{

            $oldDateRecord = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date','<',$entryDate)->orderby('entry_date','DESC')->first(); 



            if($oldDateRecord)

            {

                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);

                $data1['closing_balance']=$oldDateRecord->balance; 

                $data1['loan_closing_balance']=$oldDateRecord->loan_balance;   

                $Result1->update($data1);

                $insertid1 = $oldDateRecord->id;



                $data1RecordExists = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



                if($type == 0){                    

                    $data['balance']=$oldDateRecord->balance+$amount;  

                }else{

                    $data['balance']=$oldDateRecord->balance;  

                }

                if($type == 1){                    

                    $data['loan_balance']=$oldDateRecord->loan_balance+$amount;   

                }

                else{

                    $data['loan_balance']=$oldDateRecord->loan_balance;  

                }

                $data['opening_balance']=$oldDateRecord->balance;

                

                $data['loan_opening_balance']=$oldDateRecord->loan_balance;

                



                if($data1RecordExists){

                    if($type == 0){                    

                        $data['closing_balance']=$oldDateRecord->balance+$amount;  

                    }else{

                        $data['closing_balance']=$oldDateRecord->balance;  

                    }

                    if($type == 1){                    

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance+$amount;   

                    }

                    else{

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance;  

                    }



                    foreach ($data1RecordExists as $key => $value) {

                        $sResult = \App\Models\BranchCash::find($value->id);

                        if($type == 1){

                            $sData['loan_opening_balance']=$value->loan_closing_balance; 

                            $sData['loan_balance']=$value->loan_balance+$amount; 

                            if($value->closing_balance > 0){

                                $sData['loan_closing_balance']=$value->loan_closing_balance+$amount;   

                            } 

                        }elseif($type == 0){

                            $sData['opening_balance']=$value->closing_balance; 

                            $sData['balance']=$value->balance+$amount;    

                            if($value->closing_balance > 0){

                                $sData['closing_balance']=$value->closing_balance+$amount;   

                            }        

                        }

                        $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                        $sResult->update($sData);

                    }

                }else{

                    $data['closing_balance']=0;

                    $data['loan_closing_balance']=0;

                }



                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));



                $transcation = \App\Models\BranchCash::create($data);

                $insertid = $transcation->id;

            }

            else

            {

                if($type == 0){                    

                    $data['balance']=$amount;  

                }

                else                {

                    $data['balance']=0; 

                }

                if($type == 1){                    

                    $data['loan_balance']=$amount;  

                }

                else{

                   $data['loan_balance']=0;   

                }



                $data['opening_balance']=0;

                $data['closing_balance']=0;

                $data['loan_opening_balance']=0;

                $data['loan_closing_balance']=0;



                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchCash::create($data);

                $insertid = $transcation->id;

            }

            

        }

        return true;

    }



    public static function updateBranchClosingCashCr($branch_id,$date,$amount,$type)

    { 

        $entryDate = date("Y-m-d", strtotime(convertDate($date)));

        $entryTime = date("H:i:s", strtotime(convertDate($date)));

        $currentDateRecord = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date',$entryDate)->first();

  

        if($currentDateRecord){

            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);

            if($type == 0){

                $data['balance']=$currentDateRecord->balance+$amount;  

            }elseif($type == 1){

                $data['loan_balance']=$currentDateRecord->loan_balance+$amount;

            }          

            $data['updated_at']=$date;

            $Result->update($data);

            

            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



            if($getNextBranchClosingRecord){

                foreach ($getNextBranchClosingRecord as $key => $value) {

                    $sResult = \App\Models\BranchClosing::find($value->id);

                    if($type == 1){

                        $sData['loan_opening_balance']=$value->loan_closing_balance; 

                        $sData['loan_balance']=$value->loan_balance+$amount; 

                        if($value->closing_balance > 0){

                            $sData['loan_closing_balance']=$value->loan_closing_balance+$amount;   

                        } 

                    }elseif($type == 0){

                        $sData['opening_balance']=$value->closing_balance; 

                        $sData['balance']=$value->balance+$amount;    

                        if($value->closing_balance > 0){

                            $sData['closing_balance']=$value->closing_balance+$amount;   

                        }        

                    }

                    $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                    $sResult->update($sData);

                }

            }



        }else{

            $oldDateRecord = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date','<',$entryDate)->orderby('entry_date','DESC')->first();

            if($oldDateRecord)

            {

                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);

                $data1['closing_balance']=$oldDateRecord->balance; 

                $data1['loan_closing_balance']=$oldDateRecord->loan_balance;                   

                $Result1->update($data1);

                $insertid1 = $oldDateRecord->id;



                $data1RecordExists = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



                if($type == 0){                    

                    $data['balance']=$oldDateRecord->balance+$amount;  

                }else{

                    $data['balance']=$oldDateRecord->balance;  

                }

                if($type == 1){                    

                    $data['loan_balance']=$oldDateRecord->loan_balance+$amount;   

                }

                else{

                    $data['loan_balance']=$oldDateRecord->loan_balance;  

                }

                $data['opening_balance']=$oldDateRecord->balance;

                $data['loan_opening_balance']=$oldDateRecord->loan_balance;



                if($data1RecordExists){

                    if($type == 0){                    

                        $data['closing_balance']=$oldDateRecord->balance+$amount;  

                    }else{

                        $data['closing_balance']=$oldDateRecord->balance;  

                    }

                    if($type == 1){                    

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance+$amount;   

                    }

                    else{

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance;  

                    }



                    foreach ($data1RecordExists as $key => $value) {

                        $sResult = \App\Models\BranchClosing::find($value->id);

                        if($type == 1){

                            $sData['loan_opening_balance']=$value->loan_closing_balance; 

                            $sData['loan_balance']=$value->loan_balance+$amount; 

                            if($value->closing_balance > 0){

                                $sData['loan_closing_balance']=$value->loan_closing_balance+$amount;   

                            } 

                        }elseif($type == 0){

                            $sData['opening_balance']=$value->closing_balance; 

                            $sData['balance']=$value->balance+$amount;    

                            if($value->closing_balance > 0){

                                $sData['closing_balance']=$value->closing_balance+$amount;   

                            }        

                        }

                        $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                        $sResult->update($sData);

                    }

                }else{

                    $data['closing_balance']=0;

                    $data['loan_closing_balance']=0;     

                }

                





                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchClosing::create($data);

                $insertid = $transcation->id;

            }

            else

            {

                if($type == 0){                    

                    $data['balance']=$amount;  

                }

                else                {

                    $data['balance']=0; 

                }

                if($type == 1){                    

                    $data['loan_balance']=$amount;  

                }

                else{

                   $data['loan_balance']=0;   

                }



                $data['opening_balance']=0;

                $data['closing_balance']=0;

                $data['loan_opening_balance']=0;

                $data['loan_closing_balance']=0;



                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchClosing::create($data);

                $insertid = $transcation->id;

            }

            

        }

        return true;

    }



    public static function updateBranchCashDr($branch_id,$date,$amount,$type)

    { 

        $entryDate = date("Y-m-d", strtotime(convertDate($date)));

        $entryTime = date("H:i:s", strtotime(convertDate($date)));

        $currentDateRecord = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date',$entryDate)->first();

        

        if($currentDateRecord){

            $Result = \App\Models\BranchCash::find($currentDateRecord->id);            

            if($type == 0){ 

                $data['balance']=$currentDateRecord->balance-$amount;

            }elseif($type == 1){

                $data['loan_balance']=$currentDateRecord->loan_balance-$amount; 

            } 

            $data['updated_at']=$date;

            $Result->update($data);

            

            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



            if($getNextBranchRecord){

                foreach ($getNextBranchRecord as $key => $value) {

                    $sResult = \App\Models\BranchCash::find($value->id);

                    if($type == 1){

                        $sData['loan_opening_balance']=$value->loan_closing_balance; 

                        $sData['loan_balance']=$value->loan_balance-$amount; 

                        if($value->closing_balance > 0){

                            $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;   

                        } 

                    }elseif($type == 0){

                        $sData['opening_balance']=$value->closing_balance; 

                        $sData['balance']=$value->balance-$amount;    

                        if($value->closing_balance > 0){

                            $sData['closing_balance']=$value->closing_balance-$amount;   

                        }        

                    }

                    $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                    $sResult->update($sData);

                }

            }



        }else{

            $oldDateRecord = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date','<',$entryDate)->orderby('entry_date','DESC')->first(); 



            if($oldDateRecord)

            {

                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);

                $data1['closing_balance']=$oldDateRecord->balance; 

                $data1['loan_closing_balance']=$oldDateRecord->loan_balance;   

                $Result1->update($data1);

                $insertid1 = $oldDateRecord->id;



                $data1RecordExists = \App\Models\BranchCash::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



                if($type == 0){                    

                    $data['balance']=$oldDateRecord->balance-$amount;  

                }else{

                    $data['balance']=$oldDateRecord->balance;  

                }

                if($type == 1){                    

                    $data['loan_balance']=$oldDateRecord->loan_balance-$amount;   

                }

                else{

                    $data['loan_balance']=$oldDateRecord->loan_balance;  

                }

                $data['opening_balance']=$oldDateRecord->balance;

                

                $data['loan_opening_balance']=$oldDateRecord->loan_balance;

                



                if($data1RecordExists){

                    if($type == 0){                    

                        $data['closing_balance']=$oldDateRecord->balance-$amount;  

                    }else{

                        $data['closing_balance']=$oldDateRecord->balance;  

                    }

                    if($type == 1){                    

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;   

                    }

                    else{

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance;  

                    }



                    foreach ($data1RecordExists as $key => $value) {

                        $sResult = \App\Models\BranchCash::find($value->id);

                        if($type == 1){

                            $sData['loan_opening_balance']=$value->loan_closing_balance; 

                            $sData['loan_balance']=$value->loan_balance-$amount; 

                            if($value->closing_balance > 0){

                                $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;   

                            } 

                        }elseif($type == 0){

                            $sData['opening_balance']=$value->closing_balance; 

                            $sData['balance']=$value->balance-$amount;    

                            if($value->closing_balance > 0){

                                $sData['closing_balance']=$value->closing_balance-$amount;   

                            }        

                        }

                        $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                        $sResult->update($sData);

                    }

                }else{

                    $data['closing_balance']=0;

                    $data['loan_closing_balance']=0;

                }



                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));



                $transcation = \App\Models\BranchCash::create($data);

                $insertid = $transcation->id;

            }

            else

            {

                if($type == 0){                    

                    $data['balance']=$amount;  

                }

                else                {

                    $data['balance']=0; 

                }

                if($type == 1){                    

                    $data['loan_balance']=$amount;  

                }

                else{

                   $data['loan_balance']=0;   

                }



                $data['opening_balance']=0;

                $data['closing_balance']=0;

                $data['loan_opening_balance']=0;

                $data['loan_closing_balance']=0;



                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchCash::create($data);

                $insertid = $transcation->id;

            }

            

        }

        return true;

    }



    public static function updateBranchClosingCashDr($branch_id,$date,$amount,$type)

    { 

        $entryDate = date("Y-m-d", strtotime(convertDate($date)));

        $entryTime = date("H:i:s", strtotime(convertDate($date)));

        $currentDateRecord = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date',$entryDate)->first();

  

        if($currentDateRecord){

            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);

            if($type == 0){

                $data['balance']=$currentDateRecord->balance-$amount;  

            }elseif($type == 1){

                $data['loan_balance']=$currentDateRecord->loan_balance-$amount;

            }          

            $data['updated_at']=$date;

            $Result->update($data);

            

            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



            if($getNextBranchClosingRecord){

                foreach ($getNextBranchClosingRecord as $key => $value) {

                    $sResult = \App\Models\BranchClosing::find($value->id);

                    if($type == 1){

                        $sData['loan_opening_balance']=$value->loan_closing_balance; 

                        $sData['loan_balance']=$value->loan_balance-$amount; 

                        if($value->closing_balance > 0){

                            $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;   

                        } 

                    }elseif($type == 0){

                        $sData['opening_balance']=$value->closing_balance; 

                        $sData['balance']=$value->balance-$amount;    

                        if($value->closing_balance > 0){

                            $sData['closing_balance']=$value->closing_balance-$amount;   

                        }        

                    }

                    $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                    $sResult->update($sData);

                }

            }



        }else{

            $oldDateRecord = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date','<',$entryDate)->orderby('entry_date','DESC')->first();

            if($oldDateRecord)

            {

                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);

                $data1['closing_balance']=$oldDateRecord->balance; 

                $data1['loan_closing_balance']=$oldDateRecord->loan_balance;                   

                $Result1->update($data1);

                $insertid1 = $oldDateRecord->id;



                $data1RecordExists = \App\Models\BranchClosing::where('branch_id',$branch_id)->whereDate('entry_date','>',$entryDate)->orderby('entry_date','ASC')->get();



                if($type == 0){                    

                    $data['balance']=$oldDateRecord->balance-$amount;  

                }else{

                    $data['balance']=$oldDateRecord->balance;  

                }

                if($type == 1){                    

                    $data['loan_balance']=$oldDateRecord->loan_balance-$amount;   

                }

                else{

                    $data['loan_balance']=$oldDateRecord->loan_balance;  

                }

                $data['opening_balance']=$oldDateRecord->balance;

                $data['loan_opening_balance']=$oldDateRecord->loan_balance;



                if($data1RecordExists){

                    if($type == 0){                    

                        $data['closing_balance']=$oldDateRecord->balance-$amount;  

                    }else{

                        $data['closing_balance']=$oldDateRecord->balance;  

                    }

                    if($type == 1){                    

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;   

                    }

                    else{

                        $data['loan_closing_balance']=$oldDateRecord->loan_balance;  

                    }



                    foreach ($data1RecordExists as $key => $value) {

                        $sResult = \App\Models\BranchClosing::find($value->id);

                        if($type == 1){

                            $sData['loan_opening_balance']=$value->loan_closing_balance; 

                            $sData['loan_balance']=$value->loan_balance-$amount; 

                            if($value->closing_balance > 0){

                                $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;   

                            } 

                        }elseif($type == 0){

                            $sData['opening_balance']=$value->closing_balance; 

                            $sData['balance']=$value->balance-$amount;    

                            if($value->closing_balance > 0){

                                $sData['closing_balance']=$value->closing_balance-$amount;   

                            }        

                        }

                        $sData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($entryDate)));

                        $sResult->update($sData);

                    }

                }else{

                    $data['closing_balance']=0;

                    $data['loan_closing_balance']=0;     

                }

                





                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchClosing::create($data);

                $insertid = $transcation->id;

            }

            else

            {

                if($type == 0){                    

                    $data['balance']=$amount;  

                }

                else                {

                    $data['balance']=0; 

                }

                if($type == 1){                    

                    $data['loan_balance']=$amount;  

                }

                else{

                   $data['loan_balance']=0;   

                }



                $data['opening_balance']=0;

                $data['closing_balance']=0;

                $data['loan_opening_balance']=0;

                $data['loan_closing_balance']=0;



                $data['branch_id']=$branch_id;

                $data['entry_date']=$entryDate;

                $data['entry_time']=$entryTime;

                $data['type']=$type;

                $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchClosing::create($data);

                $insertid = $transcation->id;

            }

            

        }

        return true;

    }



    public static function employeeSalaryLeaser($employee_id,$branch_id,$type,$type_id,$opening_balance,$deposit,$withdrawal,$description,$currency_code,$payment_type,$payment_mode,$status,$created_at,$updated_at,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no,$transaction_date,$transaction_charge)

    { 

        $data['employee_id']=$employee_id;

        $data['branch_id']=$branch_id;

        $data['type']=$type;

        $data['type_id']=$type_id;

        $data['opening_balance']=$opening_balance;

        $data['deposit']=$deposit;

        $data['withdrawal']=$withdrawal;

        $data['description']=$description;

        $data['currency_code']=$currency_code;

        $data['payment_type']=$payment_type;

        $data['payment_mode']=$payment_mode;

        $data['status']=$status;

        $data['created_at']=$created_at;

        $data['v_no']=$v_no;

        $data['v_date']=$v_date;

        $data['ssb_account_id_to']=$ssb_account_id_to;

        $data['ssb_account_id_from']=$ssb_account_id_from;

        $data['to_bank_name']=$to_bank_name;

        $data['to_bank_branch']=$to_bank_branch;

        $data['to_bank_ac_no']=$to_bank_ac_no;

        $data['to_bank_ifsc'] = $to_bank_ifsc;

        $data['to_bank_id']=$to_bank_id;

        $data['to_bank_account_id']=$to_bank_account_id;

        $data['from_bank_name']=$from_bank_name;

        $data['from_bank_branch']=$from_bank_branch;

        $data['from_bank_ac_no']=$from_bank_ac_no;

        $data['from_bank_ifsc']=$from_bank_ifsc;

        $data['from_bank_id']=$from_bank_id;

        $data['from_bank_ac_id']=$from_bank_ac_id;

        $data['cheque_id']=$cheque_id;

        $data['cheque_no']=$cheque_no;

        $data['cheque_date']=$cheque_date;

        $data['transaction_no']=$transaction_no;

        $data['transaction_date']=$transaction_date;

        $data['transaction_charge']=$transaction_charge;

        $transcation = \App\Models\EmployeeLedger::create($data);

        return $transcation->id;

    }

    public static function employeeLedgerBackDateCR($employee_id,$date,$amount)
    { 
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");

        $getNextrecord=\App\Models\EmployeeLedger::where('employee_id',$employee_id)->whereDate('created_at','>',$entryDate)->orderby('created_at','ASC')->get(); 

        if($getNextrecord)
        {
            foreach($getNextrecord as $v1)
            {
                $ResultNext1 = \App\Models\EmployeeLedger::find($v1->id);
                $nextData1['opening_balance']=$v1->opening_balance+$amount;  
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }

    public static function employeeLedgerBackDateDR($employee_id,$date,$amount)
    { 
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");

        $getNextrecord=\App\Models\EmployeeLedger::where('employee_id',$employee_id)->whereDate('created_at','>',$entryDate)->orderby('created_at','ASC')->get(); 

        if($getNextrecord)
        {
            foreach($getNextrecord as $v1)
            {
                $ResultNext1 = \App\Models\EmployeeLedger::find($v1->id);
                $nextData1['opening_balance']=$v1->opening_balance-$amount;  
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }

    public static function RentLiabilityLedger($rent_liability_id,$type,$type_id,$opening_balance,$deposit,$withdrawal,$description,$currency_code,$payment_type,$payment_mode,$status,$created_at,$updated_at,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no,$transaction_date,$transaction_charge)

    { 

        $data['rent_liability_id']=$rent_liability_id;

        $data['type']=$type;

        $data['type_id']=$type_id;

        $data['opening_balance']=$opening_balance;

        $data['deposit']=$deposit;

        $data['withdrawal']=$withdrawal;

        $data['description']=$description;

        $data['currency_code']=$currency_code;

        $data['payment_type']=$payment_type;

        $data['payment_mode']=$payment_mode;

        $data['status']=$status;

        $data['created_at']=$created_at;

        $data['v_no']=$v_no;

        $data['v_date']=$v_date;

        $data['ssb_account_id_to']=$ssb_account_id_to;

        $data['ssb_account_id_from']=$ssb_account_id_from;

        $data['to_bank_name']=$to_bank_name;

        $data['to_bank_branch']=$to_bank_branch;

        $data['to_bank_ac_no']=$to_bank_ac_no;

        $data['to_bank_ifsc'] = $to_bank_ifsc;

        $data['to_bank_id']=$to_bank_id;

        $data['to_bank_account_id']=$to_bank_account_id;

        $data['from_bank_name']=$from_bank_name;

        $data['from_bank_branch']=$from_bank_branch;

        $data['from_bank_ac_no']=$from_bank_ac_no;

        $data['from_bank_ifsc']=$from_bank_ifsc;

        $data['from_bank_id']=$from_bank_id;

        $data['from_bank_ac_id']=$from_bank_ac_id;

        $data['cheque_id']=$cheque_id;

        $data['cheque_no']=$cheque_no;

        $data['cheque_date']=$cheque_date;

        $data['transaction_no']=$transaction_no;

        $data['transaction_date']=$transaction_date;

        $data['transaction_charge']=$transaction_charge;

        $transcation = \App\Models\RentLiabilityLedger::create($data);

        return $transcation->id;

    }

    public static function rentLedgerBackDateCR($rent_liability_id,$date,$amount)
    { 
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord=\App\Models\RentLiabilityLedger::where('rent_liability_id',$rent_liability_id)->whereDate('created_at','>',$entryDate)->orderby('created_at','ASC')->get(); 
        if($getNextrecord)
        {
            foreach($getNextrecord as $v1)
            {
                $ResultNext1 = \App\Models\RentLiabilityLedger::find($v1->id);
                $nextData1['opening_balance']=$v1->opening_balance+$amount;  
                 $ResultNext1->update($nextData1);
                 $insertid = $ResultNext1->id;
            }
        }
        return true;
    }


    public function print_demand_advice()
    {
        $data['title']='Print Demand Advice ';

        return view('templates.admin.demand-advice.print-demand-advice', $data);
    }

    public function printDemandAdvice(Request $request)
    {
        $id = $request->demandId;
        $demand = DemandAdvice::where('id', $id)->update(['is_print' => 0]);
        $return_array = compact('demand');
        return json_encode($return_array); 
    }

    public function createAllTransaction($daybook_ref_id,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from)
    { 
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id']=$daybook_ref_id;
        $data['branch_id']=$branch_id;
        $data['bank_id']=$bank_id;
        $data['bank_ac_id']=$bank_ac_id;
        $data['head1']=$head1;
        $data['head2']=$head2;
        $data['head3']=$head3;
        $data['head4']=$head4;
        $data['head5']=$head5;
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['associate_id']=$associate_id;
        $data['member_id']=$member_id;
        $data['branch_id_to']=$branch_id_to;
        $data['branch_id_from']=$branch_id_from;
        $data['opening_balance']=$opening_balance;
        $data['amount']=$amount;
        $data['closing_balance']=$closing_balance;
        $data['description']=$description;
        $data['payment_type']=$payment_type;
        $data['payment_mode']=$payment_mode;
        $data['currency_code']=$currency_code;
        $data['amount_to_id']=$amount_to_id;
        $data['amount_to_name']=$amount_to_name;
        $data['amount_from_id']=$amount_from_id;
        $data['amount_from_name']=$amount_from_name;
        $data['v_no']=$v_no;
        $data['v_date']=$v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date']=$cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from']=$transction_bank_ifsc_from;
        $data['transction_bank_branch_from']=$transction_bank_branch_from;
        $data['transction_bank_to']=$transction_bank_to;
        $data['transction_bank_ac_to']=$transction_bank_ac_to;
        $data['transction_date']=$transction_date;
        $data['entry_date']=date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time']=date("H:i:s");
        $data['created_by']=$created_by;
        $data['created_by_id']=$created_by_id;

        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['ssb_account_tran_id_to']=$ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from']=$ssb_account_tran_id_from;

        $data['created_at']=date("Y-m-d ".$t."", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllTransaction::create($data);
        return true;
    }

    public static function createMemberTransaction($daybook_ref_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$account_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id)
    { 
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id']=$daybook_ref_id;
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['associate_id']=$associate_id;
        $data['member_id']=$member_id;
        $data['branch_id']=$branch_id;
        $data['bank_id']=$bank_id;
        $data['account_id']=$account_id;
        $data['amount']=$amount;
        $data['description']=$description;
        $data['payment_type']=$payment_type;
        $data['payment_mode']=$payment_mode;
        $data['currency_code']=$currency_code;
        $data['amount_to_id']=$amount_to_id;
        $data['amount_to_name']=$amount_to_name;
        $data['amount_from_id']=$amount_from_id;
        $data['amount_from_name']=$amount_from_name;
        $data['v_no']=$v_no;
        $data['v_date']=$v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date']=$cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from']=$transction_bank_ifsc_from;
        $data['transction_bank_branch_from']=$transction_bank_branch_from;
        $data['transction_bank_to']=$transction_bank_to;
        $data['transction_bank_ac_to']=$transction_bank_ac_to;
        $data['transction_date']=date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date']=date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time']=date("H:i:s");
        $data['created_by']=$created_by;
        $data['created_by_id']=$created_by_id;
        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['ssb_account_tran_id_to']=$ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from']=$ssb_account_tran_id_from;
        $data['created_at']=date("Y-m-d ".$t."", strtotime(convertDate($globaldate)));
        $data['jv_unique_id']=$jv_unique_id;
        $data['cheque_type']=$cheque_type;
        $data['cheque_id']=$cheque_id;
        $transcation = \App\Models\MemberTransaction::create($data);
        return true;
    }
	
	
	
	public function get_head_details(Request $request)
    {
		$response = array();
		
        $head_id = $request->head_id;
		$data_row_id = $request->data_row_id;
		
		$data_row_id = (int)$data_row_id + 1;
		
		$accountHead = AccountHeads::where('parent_id',$head_id)->where('status',0)->get();
		
		if(count($accountHead) > 0){
			
			$html = '<div class="col-md-4 is-assets MainHead'.$data_row_id.'"><div class="form-group row"><label class="col-form-label col-lg-12">Asset Categories<sup>*</sup></label><div class="col-lg-12 error-msg"><select class="form-control assets_category" id="assets_category'.$data_row_id.'" name="assets_category'.$data_row_id.'" data-row-id="'.$data_row_id.'"><option value="">---- Please Select ----</option>';
									
									foreach($accountHead as $val){
										$html .= '<option value="'.$val->head_id.'" >'.$val->sub_head.'</option>';
									}
									
			$html .=	'</select></div></div></div>';
			
			$response["status"] = "1";
			$response["heads"] = $html;
			
		} else {
			$response["status"] = "0";
			$response["heads"] = "";
		}
		
		echo json_encode($response);
    }

    public function checkAccountNumber(Request $request)
    {
        $account = $request->account;
        $data = Memberinvestments::where('account_number',$account);
        // $mi_code = array();
        if(!is_null(Auth::user()->branch_ids)){
                 $id=Auth::user()->branch_ids;
                 $data=$data->whereIn('branch_id',explode(",",$id));
                 $data = $data->first();
                 if($data)
                 {
                    $message = '';
                    $status = 200;
                 }
                 else{
                        $message = 'Account Number is not related to this branch!';
                        $status = 500;
                 }
             }
                // foreach($data as $branch)
         //         {
                    
         //           $branch_code[] = $branch->branch_code;
         //           // $mi_code[] = 
                      
         //         }
         //        $strt_account  =   substr($account,0,4);
         //        $reinvest_strt_account  =   substr($account,2,4);
         //        //dd($branch_code);
         //         if(!(in_array($strt_account,$branch_code)))
         //         {
         //            $message = 'Account Number is not related to this branch!';
         //            $status = 500;
         //         } 
         //         // if(!(in_array($reinvest_strt_account,$branch_code)))
         //         // {
         //         //    $message = 'Account Number is not related to this branch!';
         //         //    $status = 500;
         //         // }  
         //         else{
         //                $message = '';
         //            $status = 200;
         //         } 
         //   } 
            else{
                $message = '';
                $status = 200;
         }         
        $return_array = compact('message','status');
         return json_encode($return_array);
    }

    public function getTds(Request $request)

    {

        $investmentId = $request->investmentId;

        $payableAmount = $request->payableAmount;

        $cYear = date('Y');

        $cDate = date('Y-m-d');

        $mInvestment = Memberinvestments::where('id',$investmentId)->first();

        $investmentTds = \App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

        $existsInterst = \App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

        $checkYear = $cYear;

        $formG = \App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();
        if($formG){
            $tdsAmount = 0;
            $tdsPercentage = 0;
            $investmentTds = 0;
        }else{

            $memberData = getMemberData($mInvestment->member_id);
            $diff = abs(strtotime($cDate) - strtotime($memberData['dob']));
            $years = floor($diff / (365*60*60*24));

            if($years >= 60){
               $tdsDetail = \App\Models\TdsDeposit::where('type',2)->where('start_date','<',$cDate)->first();
            }else{
                $penCard = get_member_id_proof($mInvestment->member_id,5);

                if($penCard){
                    $tdsDetail = \App\Models\TdsDeposit::where('type',1)->where('start_date','<',$cDate)->first();
                }else{
                    $tdsDetail = \App\Models\TdsDeposit::where('type',5)->where('start_date','<',$cDate)->first();
                }
            }

            if($tdsDetail){
                $tdsAmount = $tdsDetail->tds_amount;
                $tdsPercentage = $tdsDetail->tds_per;

                $deposit=Daybook::where('investment_id',$mInvestment->id)->where('account_no',$mInvestment->account_number)->where('transaction_type','>',1)->whereNotIn('transaction_type', [3,5,6,7,8,9,10,11,12,13,14,15,19])->sum('deposit');

                $withdrawal=Daybook::where('investment_id',$mInvestment->id)->where('account_no',$mInvestment->account_number)->where('transaction_type','>',1)->whereNotIn('transaction_type', [3,5,6,7,8,9,10,11,12,13,14,15,19])->sum('withdrawal');

                $investmentAmount = $deposit-$withdrawal;

                if($payableAmount > $investmentAmount){
                    $currentInterst = $payableAmount-$investmentAmount;
                }else{
                    $currentInterst = 0;
                }

                if($currentInterst > $tdsAmount){
                    $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                    $investmentTds = round(($investmentTds+$tdsAmountonInterest),2);
                }else{
                    $investmentTds = 0;
                }

            }else{
                $tdsAmount = 0;
                $tdsPercentage = 0;
                $investmentTds = 0;
            } 
        }

        $tdsPercentageAmount = $tdsAmount;

        $return_array = compact('tdsPercentageAmount','tdsPercentage','investmentTds');

        return json_encode($return_array); 

    }

}

