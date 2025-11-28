<?php 
namespace App\Http\Controllers\Admin\Expense; 
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
use App\Models\SamraddhBank;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBankClosing;
use App\Models\BranchCash;
use App\Models\SavingAccountTranscation;
use App\Models\SamraddhChequeIssue;
use App\Models\TransactionReferences;
use App\Models\Member;
use App\Models\Expense;
use App\Models\TdsDeposit;
use App\Models\InvestmentMonthlyYearlyInterestDeposits;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;
use Session;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Demand Advice DemandAdviceController
    |--------------------------------------------------------------------------
    |
    | This controller handles demand advice all functionlity.
*/
class ExpenseController extends Controller
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
       if(check_my_permission( Auth::user()->id,"164") != "1"){
          return redirect()->route('admin.dashboard');
        }
	    $data['title']='Expense Booking Form';
		$data['branches']=Branch::where('status',1)->get();
		$data['account_heads']=AccountHeads::where('parent_id',86)->get();
        $data['bank'] = \App\Models\SamraddhBank::where('status',1)->get();
        return view('templates.admin.expense.add_expense', $data);
    }
	
	public function get_indirect_expense()
    {
		$account_heads=AccountHeads::where('parent_id',86)->get();
        return response()->json($account_heads);
    }
	public function get_indirect_expense_sub_head(Request $request)
	{
		$account_heads=AccountHeads::where('parent_id',$request->head_id)->where('status',0)->get();
        $return_array = compact('account_heads');
        return json_encode($return_array); 
	}
	
	public function save(Request $request)
	{
		//dd($request->sub_head2);
		  $rules = [
            'account_head' => ['required'],
            'branch_id' => ['required'],
            'amount' => ['required'],
            
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
         $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
    		
            $entry_date = date("Y-m-d", strtotime(convertDate($request->created_at)));
            $entry_time = date("H:i:s", strtotime(convertDate($request->created_at)));
            
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($request->created_at))); 
            Session::put('created_at', $created_at);
            $billNo = random_int(0,5000);

            $billNumber = $billNo;
            $bill['bill_no'] = $billNumber;
            $bill['payment_mode'] = $request->payment_mode;
            $bill['bank_id'] = $request->bank_id;
            $bill['account_id'] = $request->account_id;
            $bill['bank_balance'] =  $request->bank_balance;
            $bill['branch_balance'] = $request->branch_total_balance;
            $bill['cheque_id'] = $request->cheque_id;
            $bill['utr_no'] = $request->utr_no;
            $bill['neft_charge'] = $request->neft_charge; 

            $billDetail = \App\Models\BillExpense::create($bill);
            
            $data['account_head_id'] = $request->account_head; 
            $data['sub_head1'] = $request->sub_head1;
            $data['sub_head2'] = $request->sub_head2;   
            $data['particular'] = $request->particular;
            $data['branch_id'] = $request->branch_id; 
            $data['payment_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
            $data['bill_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
            $data['amount'] = $request->amount;
            $data['bill_no'] = $billDetail->bill_no;
            $data['status'] = 0;
            $data['created_at'] = $request->created_at;  
            $data['updated_at'] = $request->created_at;   
            $expense_res = Expense::create($data);
			$expenseId=$expense_res->id;
			
			if ($request->hasFile('receipt')) 
            {
                
                    $mainFolder = storage_path().'/images/expense';
                    $file = $request->receipt;
                    $uploadFile = $file->getClientOriginalName();
                    $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
                    $file->move($mainFolder,$fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
					 $expenseUpdate = Expense::find($expenseId);             
					$expenseUpdate->receipt=$fname; 
					$expenseUpdate->save();
                    /*$res = Files::create($fData);
                    $file_id = $res->id;*/
			}
			if(isset($_POST['particular_more']))
            {
                foreach(($_POST['particular_more']) as $key=>$option)
                {
                  
                    
                     $dataExpenseMore=array();
                    $dataExpenseMore['account_head_id'] = $_POST['account_head_more'][$key]; 
                    $dataExpenseMore['sub_head1'] = $_POST['sub_head1_more'][$key];
                    if($_POST['sub_head2_more'][$key]!='')
                    {
                        $dataExpenseMore['sub_head2'] = $_POST['sub_head2_more'][$key]; 
                    } 
                    
                    $dataExpenseMore['particular'] = $_POST['particular_more'][$key]; 
                    $dataExpenseMore['branch_id'] = $request['branch_id']; 
                    $dataExpenseMore['payment_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
                    $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
					$dataExpenseMore['amount'] = $_POST['amount_more'][$key]; 
                    $dataExpenseMore['bill_no'] = $billDetail->bill_no;    

                    $dataExpenseMore['created_at'] = $request->created_at;   
                    $dataExpenseMore['updated_at'] = $request->created_at;  
					$dataExpenseMore['status'] = 1;					
                    $expense_res = Expense::create($dataExpenseMore);;
					$expenseIdMore=$expense_res->id;
                     $files = $request->file('receipt_more');
                    // die();
					if ($request->hasFile('receipt_more')) 
					{
                       
                        //dd($files);die();
                  
                        $mainFolder = storage_path().'/images/expense';
                        $file = $request['receipt_more'][$key];
                        $uploadFile = $file->getClientOriginalName();
                        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                        $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
                        $file->move($mainFolder,$fname);
                        $fData = [
                            'file_name' => $fname,
                            'file_path' => $mainFolder,
                            'file_extension' => $file->getClientOriginalExtension(),
                        ];
    					$expenseUpdate = Expense::find($expenseIdMore); 
                                 
    					$expenseUpdate->receipt=$fname; 
    					$expenseUpdate->save();
                     
				}
            }
          }
            DB::commit(); 
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        expenses_logs($billDetail->bill_no,NULL, "add",Auth::user()->id);

         return redirect()->route('admin.expense.expense_bill')->with('success', 'Expense Created  Successfully');
	}
	public function report_expense($bill_no){
        if(check_my_permission( Auth::user()->id,"165") != "1"){
          return redirect()->route('admin.dashboard');
        }
		   $data['title']='Expense Booking  Report';
		   $data['bill_no'] = $bill_no;   
           $data['bill_status'] = \App\Models\BillExpense::select('status')->where('is_deleted',0)->where('bill_no',$bill_no)->first();
        return view('templates.admin.expense.expense_report', $data);
	}
	public function expense_bill()
    {
        $data['title']='Expense Booking  Report';
        return view('templates.admin.expense.bill_expense_report', $data);
    }
	public function  expense_report_listing(Request $request){
		 $data = Expense::with('branch')->where('bill_no',$request->bill_no)->where('is_deleted',0)->orderBy('id','DESC')->get();
		 if($request->ajax()){
		  return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('branch_code', function($row){
                 
                return $row['branch']->branch_code;
            })
            ->rawColumns(['branch_code'])
            ->addColumn('branch_name', function($row){
                 
                return $row['branch']->name;
            })
            ->rawColumns(['branch_name'])
            ->addColumn('account_head', function($row){
                
				 if($row->account_head_id){
                return getAcountHeadNameHeadId($row->account_head_id);
				 }
            })
            ->rawColumns(['account_head'])
            ->addColumn('sub_head1', function($row){
				 if($row->sub_head1)
			   {	
                return getAcountHeadNameHeadId($row->sub_head1);;
			   }
			   else{
				   return 'N/A';
			   }
            })
            ->rawColumns(['sub_head1'])
            ->addColumn('sub_head2', function($row){
               if($row->sub_head2)
			   {
                return getAcountHeadNameHeadId($row->sub_head2);;
			   }
			   else{
				   return 'N/A';
			   }
            })
            ->rawColumns(['sub_head2'])
            ->addColumn('particular', function($row){
               
                return $row->particular;
            })
            ->rawColumns(['particular'])
             ->addColumn('receipt', function($row){
               
                if($row->receipt){
                //return $row->receipt;
                $url=URL::to("/core/storage/images/expense/".$row->receipt."");
                return '<a href="'.$url.'" target="blank">'.$row->receipt.'</a>';
            }
            })
            ->escapeColumns(['particular'])
           
            
            ->addColumn('amount', function($row){
                return $row->amount;
            })
            ->rawColumns(['amount'])
			->addColumn('bill_date', function($row){
                return date("d/m/Y", strtotime($row->bill_date));;
            })
            ->rawColumns(['bill_date'])
			->addColumn('payment_date', function($row){
                return date("d/m/Y", strtotime($row->payment_date));;
            })
            ->rawColumns(['payment_date'])
            
            ->make(true);
        }
	}

    public function bill_expense_report_listing(Request $request)
    {
        $data = \App\Models\BillExpense::with('expenses')->where('is_deleted',0)->orderBy('created_at','DESC')->get();


         if($request->ajax()){
          return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('branch_code', function($row){
                return getBranchDetail($row['expenses']->branch_id)->branch_code;
            })
            ->rawColumns(['branch_code'])
            ->addColumn('branch_name', function($row){
                 
                return getBranchDetail($row['expenses']->branch_id)->name;
            })
            ->rawColumns(['branch_name'])
            ->addColumn('bill_no', function($row){
     
                return $row->bill_no;
            })
            ->rawColumns(['bill_no'])
            ->addColumn('account_head', function($row){
                $mainHead = ' ';
                $subHead = ' ';
                $subHead2 = '';
                if($row['expenses']->account_head_id){
                    $mainHead = getAcountHeadNameHeadId($row['expenses']->account_head_id);
                    $des = $mainHead;
                }
                if($row['expenses']->sub_head1)
                {    
                    $subHead = getAcountHeadNameHeadId($row['expenses']->sub_head1);;
                    $des = $mainHead.'/'.$subHead;
                }
                if($row['expenses']->sub_head2)
                {
                    $subHead2 = getAcountHeadNameHeadId($row['expenses']->sub_head2);;
                    $des = $mainHead.'/'.$subHead.'/'.$subHead2 ;
                }
                return $mainHead. (($subHead  != '') ? '' : '/'.$subHead. (($subHead2 != '' ) ? '' : '/'.$subHead2));
               
            })
            ->rawColumns(['account_head'])
            ->addColumn('amount', function($row){
                $amount = \App\Models\Expense::where('bill_no',$row->bill_no)->sum('amount');
                return $amount;
            })
            ->rawColumns(['amount'])
            ->addColumn('total_expense', function($row){
                $totalExpense = \App\Models\Expense::where('bill_no',$row->bill_no)->count();
                return $totalExpense;
            })
            ->rawColumns(['total_expense'])
            ->addColumn('status', function($row){
                switch($row->status){
                    // case 0 : $status = "<span class='badge bg-warning'>Pending</span>"; break;
                    // case 1 : $status = "<span class='badge bg-success'>Approved</span>"; break;
                    // case 2 : $status = "<span class='badge bg-danger'>Deleted</span>"; break;
                    // default : $status = "N/A";
                    case 0 : $status = "Pending"; break;
                    case 1 : $status = "Approved"; break;
                    case 2 : $status = "Deleted"; break;
                    default : $status = "N/A";
                }
                return $status;
                })
            ->rawColumns(['status'])
           ->addColumn('action', function($row){
                $detailExpenseurl =  URL::to("admin/report/expense/".$row->bill_no."");  
                    $btn = '';
                  
                    
                    if($row->status == 0){
                        $btn .= '<a href="admin/expense/edit/'.$row->bill_no.'" class="dropdown-item" title="Edit Expense" target="_blank"><i class="icon-pencil5 mr-2"></i> Edit</a>';
                        $btn .= '<button class="dropdown-item delete_expense" data-row-id="'.$row->bill_no.'" title="delete_expense"><i class="icon-box mr-2"></i> Delete</button>';
                    }
                    if($row->status == 1){
                       
                        $btn .= '<button class="dropdown-item delete_expense" data-row-id="'.$row->bill_no.'" title="delete_approved_bill"><i class="icon-box mr-2"></i> Delete Approved Bill</button>';
                    }
                    $btn .= '<a class="dropdown-item"  href="'.$detailExpenseurl.'" ><i class="icon-snowflake mr-2"></i> Detail Expense</button>';

                    return '
                    <div class="list-icons">
                        <div class="dropdown">
                            <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                            <div class="dropdown-menu dropdown-menu-right">
                                '.$btn.'
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['action'])
                
            ->make(true);
        }
    }


    public function approve_expense(Request $request){
        $data = \App\Models\Expense::where('bill_no',$request->bill_no)->get();
        $amount = \App\Models\Expense::where('bill_no',$request->bill_no)->sum('amount');
        $data2 = \App\Models\BillExpense::with('expenses')->where('bill_no',$request->bill_no)->first();
       
        DB::beginTransaction();
        try {
            $daybookRef=CommanController::createBranchDayBookReferenceNew($amount,$data2->created_at);
            $response["status"] = '';
            $response["msg"]='';

           foreach ($data as $key => $value) {
              	
                // $bank_id = $data2->bank_id != ''  ? $data2->bank_id :NULL;
                // $bank_id_ac = $data2->bank_id != '' ? $data2->bank_id :NULL;

           		
                $des= $value->particular; 
                $created_by=1;
                $created_by_id=\Auth::user()->id;
                $created_by_name=\Auth::user()->username; 
                $ExpenseheadId = '';//Expense Head Id
                $headId = ''; // Head Id based on Payment Mode
                $neftHead = '';
                $branch_id = $value->branch_id;
                $type = 20;
                $sub_type = 201;
                $type_id = $value->bill_no;
                $type_transaction_id = $value->id;
                $opening_balance = $value->amount;
                $closing_balance = $value->amount;
                $amount = $value->amount;
                $payment_mode = $data2->payment_mode;
                $currency_code = 'INR';
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $v_no = NULL; 
                $v_date = NULL; 
                $ssb_account_id_from = NULL;

            	
                if($data2->cheque_id)
                {

                    $cheque = \App\Models\SamraddhCheque::select('cheque_no')->where('id',$data2->cheque_id)->first();
                    $bank_id =$data2->bank_id;
                    $bank_ac_id =$data2->bank_id;
                    $cheque_no = $cheque->cheque_no; 
                    $cheque_type = 1;
                	$cheque_id = $data2->cheque_id;

                }
                else{
                    $cheque_no = NULL;
                    $cheque_type = NULL;
                	$cheque_id = NULL;
                	$bank_id =$data2->bank_id;
                    $bank_ac_id =$data2->bank_id;

                }

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
                $tranId = NULL;  
                $ssb_account_id_to = NULL; 
                $cheque_bank_from_id = NULL; 
                $cheque_bank_ac_from_id = NULL; 
                $cheque_bank_to_name = NULL; 
                $cheque_bank_to_branch = NULL; 
                $cheque_bank_to_ac_no = NULL; 
                $cheque_bank_to_ifsc = NULL; 
                $transction_bank_from_id = NULL; 
                $transction_bank_from_ac_id = NULL; 
                $transction_bank_to_name = NULL; 
                $transction_bank_to_ac_no = NULL; 
                $transction_bank_to_branch = NULL; 
                $transction_bank_to_ifsc = NULL; 
                $branch_id_to=$value->branch_id;
                $branch_id_from=NULL;
               
                $jv_unique_id=$ssb_account_tran_id_to=$ssb_account_tran_id_from=NULL;
                $head3=NULL;
                $head4=NULL;
                $head5=NULL;
                if($value->account_head_id != '' && $value->sub_head1 != '' && $value->sub_head2 != '')
                {
                    $head5=$value->sub_head2;
                    $ExpenseheadId = $value->sub_head2;
                }
                elseif ($value->account_head_id != '' && $value->sub_head1 != '' && $value->sub_head2 == '') {
                    $ExpenseheadId = $value->sub_head1;
                    $head4=$value->sub_head1;
                } 
                elseif ($value->account_head_id != '' && $value->sub_head1 == '' && $value->sub_head2 == '') {
                    $ExpenseheadId = $value->account_head_id;
                     $head3=$value->account_head_id;

                }   
                // Payment Mode

                if($data2->payment_mode == 0)
                {
                    $headId = 28;

                }
                elseif($data2->payment_mode == 1 || $data2->payment_mode == 2)
                {
                    $headId = getSamraddhBank($data2->bank_id)->account_head_id;
                }
                // If NEFT Charge Exist
                
                $headName=getAcountHeadNameHeadId(86);

                if($head3>0){
                    //  $des.= ','.getAcountHeadNameHeadId($head3); 
                    $headName.= '/'.getAcountHeadNameHeadId($head3); 
                }

                if($head4>0){
                    // $des.= ','.getAcountHeadNameHeadId($head4); 
                    $headName.= '/'.getAcountHeadNameHeadId($head4); 
                }

                if($head5>0){
                    //  $des.= ','.getAcountHeadNameHeadId($head5); 
                    $headName.= '/'.getAcountHeadNameHeadId($head5); 
                } 
                $entry_date = date("Y-m-d", strtotime(convertDate($value->created_at)));
                $entry_time = date("H:i:s", strtotime(convertDate($value->created_at)));
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($value->created_at)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($value->created_at)));     
                $description_dr='Cash A/c DR '.$value->amount.'/-';  
                $description_cr=$headName.' A/c CR '.$value->amount.'/-'; 

                // Branch Daybook Entry 
                $brDaybook=CommanController::branchDaybookCreate($daybookRef,$branch_id,$type,$sub_type,$type_id,$associate_id=NULL,$member_id=Null,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance,$amount,$closing_balance,$des,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$type_transaction_id, $ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc);    

                // AllHeadTransaction Entry
               
                 $allTran1=CommanController::headTransactionCreate($daybookRef,$branch_id,$bank_id,$bank_ac_id,$ExpenseheadId,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                 $allTran1=CommanController::headTransactionCreate($daybookRef,$branch_id,$bank_id,$bank_ac_id,$headId,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                 if($data2->payment_mode == 1 || $data2->payment_mode == 2)
                 {	
                 	 	    
                     $bankcashDaybook=CommanController:: NewFieldAddSamraddhBankDaybookCreate($daybookRef,$bank_id,$bank_id,$type,$sub_type,$type_id,$associate_id=NULL,$memberId=NULL,$branch_id,$opening_balance,$amount,$closing_balance,$des,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                     if($data2->payment_mode == 1 )
                     {

                     	$update = \App\Models\SamraddhCheque::where('id',$data2->cheque_id)->update(['is_use'=>1]);

                     }
                     
                 }
                 $approvedDate =  date('Y-m-d H:i:s');;

                $branchClosing = CommanController::checkCreateBranchClosingDr($branch_id,$created_at,$value->amount,0);
            	$branchCash = CommanController::checkCreateBranchCashDR($branch_id,$created_at,$value->amount,0);

                $updateDaybookId = $data2->update(['daybook_refid'=>$daybookRef,'status'=>1]);
                
                $value->update(['status'=>1,'approve_date'=>$approvedDate]);
                $response["status"] = "1";
                $response["msg"] = "Expense Approved Successfully!";
			

            }

            //Neft Charge
            if(!is_null($data2->neft_charge))
            {	

            		
                $des= 'NEFT CHARGE on Bill' .$data2->bill_no ; 
                $created_by=1;
                $created_by_id=\Auth::user()->id;
                $created_by_name=\Auth::user()->username; 
                $ExpenseheadId = '';//Expense Head Id
                $headId = ''; // Head Id based on Payment Mode
                $neftHead = 92;
                $headId = getSamraddhBank($data2->bank_id)->account_head_id;
                $branch_id = $data2['expenses']->branch_id;
                $type = 20;
                $sub_type = 201;
                $type_id = $data2->bill_no;
                $type_transaction_id = $data2->bill_no;
                $opening_balance =  $data2->neft_charge;
                $closing_balance =  $data2->neft_charge;
                $amount =  $data2->neft_charge;
                $payment_mode = $data2->payment_mode;
                $currency_code = 'INR';
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $v_no = NULL; 
                $v_date = NULL; 
                $ssb_account_id_from = NULL;

            	
                if($data2->cheque_id)
                {

                    $cheque = \App\Models\SamraddhCheque::select('cheque_no')->where('id',$data2->cheque_id)->first();
                    $bank_id =$data2->bank_id;
                    $bank_ac_id =$data2->bank_id;
                    $cheque_no = $cheque->cheque_no; 
                    $cheque_type = 1;
                	$cheque_id = $data2->cheque_id;

                }
                else{
                    $cheque_no = NULL;
                    $cheque_type = NULL;
                	$cheque_id = NULL;
                	$bank_id =$data2->bank_id;
                    $bank_ac_id =$data2->bank_id;

                }

                $cheque_date = NULL; 
                $cheque_bank_from = NULL; 
                $cheque_bank_ac_from = NULL; 
                $cheque_bank_ifsc_from = NULL; 
                $cheque_bank_branch_from = NULL; 
                $cheque_bank_to = NULL; 
                $cheque_bank_ac_to = NULL;
                if($data2->utr_no)
                {
                	 $transction_no = $data2->utr_no;
                } 
               	else{
               		 $transction_no = NULL;
               	}

                $transction_bank_from = NULL; 
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL; 
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL; 
                $transction_bank_ac_to = NULL;
                $transction_date = NULL; 
                $tranId = NULL;  
                $ssb_account_id_to = NULL; 
                $cheque_bank_from_id = NULL; 
                $cheque_bank_ac_from_id = NULL; 
                $cheque_bank_to_name = NULL; 
                $cheque_bank_to_branch = NULL; 
                $cheque_bank_to_ac_no = NULL; 
                $cheque_bank_to_ifsc = NULL; 
                $transction_bank_from_id = NULL; 
                $transction_bank_from_ac_id = NULL; 
                $transction_bank_to_name = NULL; 
                $transction_bank_to_ac_no = NULL; 
                $transction_bank_to_branch = NULL; 
                $transction_bank_to_ifsc = NULL; 
                $branch_id_to=$data2['expenses']->branch_id;
                $branch_id_from=NULL;
               		
                $jv_unique_id=$ssb_account_tran_id_to=$ssb_account_tran_id_from=NULL;
                $head3=NULL;
                $head4=NULL;
                $head5=NULL;
               
                // Payment Mode

               	
                $entry_date = date("Y-m-d", strtotime(convertDate($data2->created_at)));
                $entry_time = date("H:i:s", strtotime(convertDate($data2->created_at)));
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($data2->created_at)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($data2->created_at)));     
                $description_dr='Cash A/c DR '.$amount.'/-';  
                $description_cr=$data2->bill_no.' A/c CR '.$amount.'/-';
            	 $brDaybook=CommanController::branchDaybookCreate($daybookRef,$branch_id,$type,$sub_type,$type_id,$associate_id=NULL,$member_id=Null,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance,$amount,$closing_balance,$des,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$type_transaction_id, $ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc);    

                // AllHeadTransaction Entry
               
               	

                 $allTran1=CommanController::headTransactionCreate($daybookRef,$branch_id,$bank_id,$bank_ac_id,$neftHead,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);
                 $allTran1=CommanController::headTransactionCreate($daybookRef,$branch_id,$bank_id,$bank_ac_id,$headId,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$des,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                     $bankcashDaybook=CommanController:: NewFieldAddSamraddhBankDaybookCreate($daybookRef,$bank_id,$bank_id,$type,$sub_type,$type_id,$associate_id=NULL,$memberId=NULL,$branch_id,$opening_balance,$amount,$closing_balance,$des,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$type_transaction_id,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);
                     
                 }
                
           	
            
            DB::commit();
        }
        catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        expenses_logs($request->bill_no,NULL, "approve",Auth::user()->id);
        echo json_encode($response); die;
    }

    public function edit($id){
		$title='Edit eXPENSE'; 
		
		$branches = Branch::where('status',1)->get();
		$account_heads = AccountHeads::where('parent_id',86)->get();
		$bank = \App\Models\SamraddhBank::where('status',1)->get();
        $billExpense = \App\Models\BillExpense::with('expenses')->where('bill_no',$id)->where('is_deleted',0)->first();
		$bank_ac = \App\Models\SamraddhBankAccount::where('status',1)->where('id',$billExpense->account_id)->get();
		$cheques = \App\Models\SamraddhCheque::select('cheque_no','id')->where('is_use',0)->get();
		$expenseData = \App\Models\Expense::select('id','bill_no','bill_date','particular','account_head_id','sub_head1','sub_head2','amount','branch_id')->where('bill_no',$id)->where('is_deleted',0)->get();
		
		$data=[
			'title'=> 'title',
			'branches'=> 'branches',
			'account_heads'=> 'account_heads',
			'bank'=> 'bank',
			'bank_ac'=> 'bank_ac',
			'expenseData'=> 'expenseData',
            'billExpense' =>'billExpense',
            'cheques' =>'cheques',
		];

        return view('templates.admin.expense.edit_expense', compact($data));
    }

    public function update(Request $request)
    {
       DB::beginTransaction();
        try {
            if($request->expensesId)
            {
                //foreach($request->expensesId as $key => $expense)
                $expensesIds = $request->expensesId;
                $particulars= $request->particular;
                $account_head_ids= $request->account_head;
                $subHeads1 = $request->sub_head1;
                $subHeads2= $request->sub_head2;
                $branchid= $request->branch_id;
                $bill_date= $request->expensesDate;
                $amounts= $request->amount;
                for($i=0; $i<count($expensesIds); $i++)    
                {  

                    $expensesId = $expensesIds[$i];
                    $particular = $particulars[$i];
                    $account_head_id = $account_head_ids[$i];
                    $subHead1 = $subHeads1[$i];
                    $subHead2 = $subHeads2[$i];
                    $branchid = $branchid;
                    $bill_date = $bill_date;
                    $amount = $amounts[$i];
                     $dataExpenseMore['account_head_id'] = $account_head_id;
                     $dataExpenseMore['sub_head1'] = $subHead1;                         
                     $dataExpenseMore['sub_head2'] = $subHead2;   
                     $dataExpenseMore['particular'] = $particular;
                     $dataExpenseMore['branch_id'] =$branchid; 
                     $dataExpenseMore['payment_date']=date("Y-m-d", strtotime( str_replace('/','-', $bill_date))); 
                     $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime( str_replace('/','-', $bill_date)));
                     $dataExpenseMore['amount'] = $amount;
                    $ExistExpenseUpdate = Expense::where('id',$expensesIds[$i])->update($dataExpenseMore);
                   
                }

            }
           
            $bill['payment_mode'] = $request->payment_mode;
            $bill['bank_id'] = $request->bank_id;
            $bill['account_id'] = $request->account_id;
            $bill['bank_balance'] =  $request->bank_balance;
            $bill['branch_balance'] = $request->branch_total_balance;
            $bill['cheque_id'] = $request->cheque_id;
            $bill['utr_no'] = $request->utr_no;
            $bill['neft_charge'] = $request->neft_charge; 
            $billExist = \App\Models\BillExpense::where('bill_no',$request->bill_no)->update($bill);
            if(isset($_POST['particular_more']))
            {
                    foreach(($_POST['particular_more']) as $key=>$option)
                    {
                        $dataExpenseMore=array();
                        $dataExpenseMore['account_head_id'] = $_POST['account_head_more'][$key]; 
                        $dataExpenseMore['sub_head1'] = $_POST['sub_head1_more'][$key];
                        if($_POST['sub_head2_more'][$key]!='')
                        {
                            $dataExpenseMore['sub_head2'] = $_POST['sub_head2_more'][$key]; 
                        } 
                        
                        $dataExpenseMore['particular'] = $_POST['particular_more'][$key]; 
                        $dataExpenseMore['branch_id'] = $request['branch_id']; 
                        $dataExpenseMore['payment_date'] =date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
                        $dataExpenseMore['bill_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['bill_date']))); ;  
                        $dataExpenseMore['amount'] = $_POST['amount_more'][$key]; 
                        $dataExpenseMore['bill_no'] = $request->bill_no;    
                        $dataExpenseMore['created_at'] = $request->created_at;   
                        $dataExpenseMore['updated_at'] = $request->created_at;  
                        $dataExpenseMore['status'] = 1;                 
                        $expense_res = Expense::create($dataExpenseMore);;
                        $expenseIdMore=$expense_res->id;
                       // expenses_logs($expense_res->id, $expense_res->account_head_id, $expense_res->branch_id, "update",1);
                         $files = $request->file('receipt_more');
                        if ($request->hasFile('receipt_more')) 
                        {
                            $mainFolder = storage_path().'/images/expense';
                            $file = $request['receipt_more'][$key];
                            $uploadFile = $file->getClientOriginalName();
                            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                            $fname = $filename.'_'.time().'.'.$file->getClientOriginalExtension();
                            $file->move($mainFolder,$fname);
                            $fData = [
                                'file_name' => $fname,
                                'file_path' => $mainFolder,
                                'file_extension' => $file->getClientOriginalExtension(),
                            ];
                            $expenseUpdate = Expense::find($expenseIdMore); 
                                     
                            $expenseUpdate->receipt=$fname; 
                            $expenseUpdate->save();
                         
                        }
                    }    
            }
                                        
        DB::commit(); 
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        expenses_logs($request->bill_no, NULL, "update",Auth::user()->id);    

         return redirect()->route('admin.expense.expense_bill')->with('success', 'Expense Created  Successfully');
    }


    /**
    * Delete Bill And Expense

    * @param bill id
    **/

    public function deleteBill(Request $request)
    {

        $response['status'] = '';
        $response['message'] = '';
        $billNo = $request->bill_no;
        $title = $request->title;
        if($title == 'delete_approved_bill')
        {
            $BillRecord = \App\Models\BillExpense::select('daybook_refid','payment_mode')->where('bill_no',$billNo)->first();
            $deleteBranchDaybook = \App\Models\BranchDaybook::where('daybook_ref_id',$BillRecord->daybook_refid)->update(['is_deleted'=>1]);
            $deleteAllheadTransaction = \App\Models\AllHeadTransaction::where('daybook_ref_id',$BillRecord->daybook_refid)->update(['is_deleted'=>1]);
            if($BillRecord->payment_mode == 1 || $BillRecord->payment_mode == 2)
            {
                $deleteSamraddhBankDaybook = \App\Models\SamraddhBankDaybook::where('daybook_ref_id',$BillRecord->daybook_refid)->update(['is_deleted'=>1]);
            }
            $deleteExpense = \App\Models\Expense::where('bill_no',$billNo)->update(['is_deleted'=>1]);
            $deleteBill = \App\Models\BillExpense::where('bill_no',$billNo)->update(['is_deleted'=>1]);
            $response['status'] = 1;
            $response['message'] = 'Approved Bill No - '.$billNo.' Deleted Successfully!';
            expenses_logs($request->bill_no,NULL, "bill_delete",Auth::user()->id);  
        }
        elseif($title == 'delete_expense')
        {
            $deleteExpense = \App\Models\Expense::where('bill_no',$billNo)->delete();
            $deleteBill = \App\Models\BillExpense::where('bill_no',$billNo)->delete();
            if($deleteExpense && $deleteBill)
            {
                $response['status'] = 1;
                $response['message'] = 'Bill No - '.$billNo.' Deleted Successfully!';
            }
            expenses_logs($request->bill_no,NULL, "delete",Auth::user()->id);  
        }
        

        echo json_encode($response); die;
    }
}