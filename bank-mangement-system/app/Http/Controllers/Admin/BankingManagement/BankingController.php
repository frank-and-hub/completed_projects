<?php 

namespace App\Http\Controllers\Admin\BankingManagement; 

use App\Http\Controllers\Admin\CommanController;

use App\Models\MemberIdProof;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validator;

use App\Models\SamraddhBank;

use App\Models\BankingLedger;

use App\Models\SamraddhCheque;

use App\Models\Branch;

use App\Models\AccountHeads;

use App\Models\CreditCard;

use App\Models\SamraddhBankAccount;

use App\Models\Designation;

use App\Models\Files;

use App\Models\SamraddhChequeIssue;

use App\Models\AllHeadTransaction;

use App\Models\BranchDaybook;

use App\Models\MemberTransaction;

use App\Models\SamraddhBankDaybook;

use App\Models\RentPayment;

use App\Models\RentLedger;

use App\Models\VendorBillPayment;

use App\Models\RentLiability;

use App\Models\RentLiabilityLedger;

use App\Models\EmployeeSalary;

use App\Models\EmployeeSalaryLeaser;

use App\Models\Vendor;

use App\Models\Employee;

use App\Models\EmployeeLedger;

use App\Models\VendorBill;

use App\Models\VendorLog;

use App\Models\CustomerTransaction;

use App\Models\AssociateTransaction;

use App\Models\Member;

use App\Models\BankingDueBillsLedger;

use App\Models\CommissionLeaserDetail;

use App\Models\AdvancedTransaction;

use App\Models\VendorTransaction;

use App\Models\BankingAdvancedLedger;

use App\Models\CreditCradTransaction;

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

class BankingController extends Controller

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
		if(check_my_permission( Auth::user()->id,"175") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
        $data['title']='Banking Management';
        $data['banks']=SamraddhBank::select('id','bank_name')->where('status',1)->get();
        $data['branches']=Branch::select('id','name','branch_code')->where('status',1)->get();
        return view('templates.admin.banking_management.index', $data);
    }

    public function create()
    {
		
		if(check_my_permission( Auth::user()->id,"185") != "1" && check_my_permission( Auth::user()->id,"186") != "1" && check_my_permission( Auth::user()->id,"187") != "1" && check_my_permission( Auth::user()->id,"188") != "1" && check_my_permission( Auth::user()->id,"189") != "1" && check_my_permission( Auth::user()->id,"190") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		
		if(isset($_GET["banking_type"]) && $_GET["banking_type"]!= ""){
			$banking_type = trim($_GET["banking_type"]);
		} else {
			$banking_type = "";
		}

		$data['banking_type']= $banking_type;
		if($banking_type == 'Expense'){
			if(check_my_permission( Auth::user()->id,"185") != "1"){
			  return redirect()->route('admin.dashboard');
			}
			$data['title']='Money Out | Expense Payment';
		} else if($banking_type == 'Payment'){
			if(check_my_permission( Auth::user()->id,"186") != "1"){
			  return redirect()->route('admin.dashboard');
			}
			$data['title']='Money Out | Payment';
		} else if($banking_type == 'Card'){
			if(check_my_permission( Auth::user()->id,"187") != "1"){
			  return redirect()->route('admin.dashboard');
			}
			$data['title']='Money Out | Card Payment';
		}  else if($banking_type == 'Receive'){
			if(check_my_permission( Auth::user()->id,"189") != "1"){
			  return redirect()->route('admin.dashboard');
			}
			$data['title']='Money In | Receive Payment';
		} elseif($banking_type == 'Income'){
			if(check_my_permission( Auth::user()->id,"190") != "1"){
			  return redirect()->route('admin.dashboard');
			}
			$data['title']='Money In | Other income';
		}else{
			if(check_my_permission( Auth::user()->id,"186") != "1"){
			  return redirect()->route('admin.dashboard');
			}
			$data['title']=$banking_type.' Payment';
		}
        
		$data['banks']=SamraddhBank::where('status',1)->get();
        $data['branches']=Branch::where('status',1)->get();
		$data['expence_heads']= AccountHeads::where('parent_id',86)->get();
		$data['credit_cards']= CreditCard::where('status','1')->get();
		$data['indirect_income_heads']= AccountHeads::where('parent_id',3)->get();
        return view('templates.admin.banking_management.create', $data);
    }
	
	public function getAccountNumberOfBank(Request $request)
    {
		$bankID = $request->banks_id;
		if($bankID!= ""){
			$accounts = SamraddhBankAccount::where('bank_id',$bankID)->get()->toArray();
			echo json_encode($accounts);
		}
    }
	
	public function getChequeNumberOfBank(Request $request)
    {
		//$bank_id = $request->bank_id;
		$account_id = $request->account_id;
		if($account_id!= ""){
			$accounts = SamraddhCheque::where('status',1)->where('account_id',$account_id)->get()->toArray();
			echo json_encode($accounts);
		}
    }
	
	public function getBankDayBookAccount(Request $request)
	{
		$fromBankId = $request->fromBankId;

        $bankRes = SamraddhBankClosing::where('bank_id',$fromBankId)->orderBy('entry_date', 'desc')->first();

        if($bankRes){
        	$bankDayBookAmount = $bankRes->balance;
        	$bankDayBookLoanAmount = $bankRes->loan_balance;
        }else{
        	$bankRes = SamraddhBankClosing::where('bank_id',$fromBankId)->orderby('entry_date','DESC')->first();
            $bankDayBookAmount = $bankRes->balance;
            $bankDayBookAmount = $bankRes->balance;
        	$bankDayBookLoanAmount = $bankRes->loan_balance;
        }
        $return_array = compact('bankDayBookAmount','bankDayBookLoanAmount');
        return json_encode($return_array);
	}
	
	public function save(Request $request)
	{
		
		DB::beginTransaction();

        try {

			$data = array();
			$data['type'] = $request->subtype;
			$data['banking_type'] = $request->type;

			if($request->type == 1){

				if(isset($request->expense_receipt)){
		            $mainFolder = storage_path().'/images/banking';
		            $file = $request->expense_receipt;
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
				
				$data['file_id'] = $file_id;
				if(isset($request->expense_account) && $request->expense_account!= ""){
					$data['expense_account'] = $request->expense_account;
				}
				if(isset($request->expense_account1) && $request->expense_account1!= ""){
					$data['expense_account1'] = $request->expense_account1;
				}
				if(isset($request->expense_account2) && $request->expense_account2!= ""){
					$data['expense_account2'] = $request->expense_account2;
				}
				if(isset($request->expense_account3) && $request->expense_account3!= ""){
					$data['expense_account3'] = $request->expense_account3;
				}
				if(isset($request->expense_date) && $request->expense_date!= ""){
					$data['date'] = date("Y-m-d", strtotime(convertDate($request->expense_date)));
				}
				if(isset($request->expense_amount) && $request->expense_amount!= ""){
					$data['amount'] = $request->expense_amount;
				}
				if(isset($request->expense_description) && $request->expense_description!= ""){
					$data['description'] = $request->expense_description;
				}
				if(isset($request->expense_mode) && $request->expense_mode!= ""){
					$data['payment_mode'] = $request->expense_mode;
				}
				if(isset($request->expense_bank_id) && $request->expense_bank_id!= ""){
					$data['bank_id'] = $request->expense_bank_id;
				}
				if(isset($request->expense_account_no) && $request->expense_account_no!= ""){
					$data['account_no'] = $request->expense_account_no;
				}
				if(isset($request->expense_paid_via) && $request->expense_paid_via!= ""){
					$data['paid_via'] = $request->expense_paid_via;
				}
				if(isset($request->expense_cheque_no) && $request->expense_cheque_no!= ""){
					$data['cheque_no'] = $request->expense_cheque_no;
				}
				if(isset($request->expense_utr) && $request->expense_utr!= ""){
					$data['neft_utr_no'] = $request->expense_utr;
				}
				if(isset($request->expense_neft) && $request->expense_neft!= ""){
					$data['neft_charge'] = $request->expense_neft;
				}
				if(isset($request->expense_branch_id) && $request->expense_branch_id!= ""){
					$data['branch_id'] = $request->expense_branch_id;
				}
				if(isset($request->expense_date) && $request->expense_date!= ""){
					$data['created_at'] = date("Y-m-d", strtotime(convertDate($request->expense_date)));
				}
				$res = BankingLedger::create($data);	
				$bookingid = $res->id;
				if($data['payment_mode'] == 1 && $data['paid_via'] == 2)
				{
					if($data['neft_charge'] != '' )
					{
						$request['neft_charge'] = 1;
					}
					
					
				}
				else{
						$request['neft_charge'] = 0;
				}
				$request['refund'] = 0;
				$request['advanced'] = 0;
				$this->HeadEntry($request->all(),$bookingid,$request->type);
			}elseif($request->type == 2){
				if($request->payment_account_payment == 1){
					if(isset($request->payment_account_payment) && $request->payment_account_payment!= ""){
						$data['account_type'] = $request->payment_account_payment;
					}
					if(isset($request->payemnt_vendor_type) && $request->payemnt_vendor_type!= ""){
						$data['vendor_type'] = $request->payemnt_vendor_type;
					}
					if(isset($request->payment_vendor_name) && $request->payment_vendor_name!= ""){
						$data['vendor_type_id'] = $request->payment_vendor_name;
					}
					if(isset($request->payment_vendor_date) && $request->payment_vendor_date!= ""){
						$data['date'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
					}

					if(isset($request->vendor_payment_amount) && $request->vendor_payment_amount!= ""){
						$data['amount'] = $request->vendor_payment_amount;
					}

					$advancedAmount = ($request->vendor_payment_amount-$request->vendor_total_amount);

					if(isset($advancedAmount) && $advancedAmount > 0){
						$data['advanced_amount'] = $advancedAmount;
					}

					if(isset($request->vendor_payment_mode) && $request->vendor_payment_mode!= ""){
						$data['payment_mode'] = $request->vendor_payment_mode;
					}
					if(isset($request->payment_vendor_bank_id) && $request->payment_vendor_bank_id!= ""){
						$data['bank_id'] = $request->payment_vendor_bank_id;
					}
					if(isset($request->payment_vendor_bank_account_number) && $request->payment_vendor_bank_account_number!= ""){
						$data['account_no'] = $request->payment_vendor_bank_account_number;
					}
					if(isset($request->payment_vendor_paid_via) && $request->payment_vendor_paid_via!= ""){
						$data['paid_via'] = $request->payment_vendor_paid_via;
					}
					if(isset($request->payment_vendor_cheque_no) && $request->payment_vendor_cheque_no!= ""){
						$data['cheque_no'] = $request->payment_vendor_cheque_no;
					}
					if(isset($request->vendor_utr) && $request->vendor_utr!= ""){
						$data['neft_utr_no'] = $request->vendor_utr;
					}
					if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
						$data['neft_charge'] = $request->vendor_neft;
					}
					if(isset($request->payment_branch_id) && $request->payment_branch_id!= ""){
						$data['branch_id'] = $request->payment_branch_id;
					}

					if(isset($request->payment_vendor_description) && $request->payment_vendor_description!= ""){
						$data['description'] = $request->payment_vendor_description;
					}

					if(isset($request->payment_vendor_date) && $request->payment_vendor_date!= ""){
						$data['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
					}

					$res = BankingLedger::create($data);
					$bookingid = $res->id;

					if($request->payemnt_vendor_type == 0){
						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['rent_liability_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['rent_liability_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$rentPaymentResult = RentPayment::where('rent_liability_id', $request->payment_vendor_name)->where('status',0)->get();

						foreach($rentPaymentResult as $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->rent_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['rent_liability_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 0;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->id;
							$dueBillsdata['amount'] = ($val->rent_amount-$val->transferred_amount);
							$dueBillsdata['pay_amount'] = $request->rent_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);

						}
					}elseif($request->payemnt_vendor_type == 1){

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['employee_salary_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['employee_salary_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$employeePaymentResult = EmployeeSalary::where('employee_id', $request->payment_vendor_name)->where('status',0)->get();

						foreach($employeePaymentResult as $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->salary_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['employee_salary_id'] = $val->employee_id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 1;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->employee_id;
							$dueBillsdata['pay_amount'] = $request->salary_payment_amount[$val->id];
							$dueBillsdata['pay_amount'] = $request->salary_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);
						}
					}elseif($request->payemnt_vendor_type == 2){

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['fuelamount'] = 0;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['associate_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['fuelamount'] = 0;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['associate_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$associateLedgerResult=CommissionLeaserDetail::where('member_id', $request->payment_vendor_name)->where('status',2)->get();

						foreach($associateLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->associate_commission_payment_amount[$val->id];
							$request['fuelamount'] = $request->associate_fuel_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['associate_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 2;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->id;
							$dueBillsdata['amount'] = $val->amount-$request->associate_commission_payment_amount[$val->id];
							$dueBillsdata['pay_amount'] = $request->associate_payment_amount[$val->id];
							$dueBillsdata['fule_amount'] = $val->fuel-$request->associate_fuel_payment_amount[$val->id];
							$dueBillsdata['pay_fuel_amount'] = $request->associate_fuel_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);
						}
					}elseif($request->payemnt_vendor_type == 3){

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['vendor_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['vendor_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$vendorLedgerResult=VendorBill::where('vendor_id', $request->payment_vendor_name)->where('status',0)->get();

						foreach($vendorLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->vendor_pending_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['vendor_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 3;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->id;
							$dueBillsdata['pay_amount'] = $request->vendor_pending_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);
						}
					}	
				}elseif($request->payment_account_payment == 2){

					$advancedAmount = $request->payment_customer_amount-$request->customer_total_amount;

					if($advancedAmount){

						$data['account_type'] = 2;
						$data['vendor_type'] = 5;
						if(isset($request->payment_customer_name) && $request->payment_customer_name!= ""){
							$data['vendor_type_id'] = $request->payment_customer_name;
						}
						if(isset($request->payment_customer_date) && $request->payment_customer_date!= ""){
							$data['date'] = date("Y-m-d", strtotime(convertDate($request->payment_customer_date)));
						}

						if(isset($request->payment_customer_amount) && $request->payment_customer_amount!= ""){
							$data['amount'] = $request->payment_customer_amount;
						}

						if(isset($advancedAmount) && $advancedAmount!= ""){
							$data['customer_refund_payment'] = $advancedAmount;
						}

						if(isset($request->customer_payment_mode) && $request->customer_payment_mode!= ""){
							$data['payment_mode'] = $request->customer_payment_mode;
						}
						if(isset($request->payment_customer_bank_id) && $request->payment_customer_bank_id!= ""){
							$data['bank_id'] = $request->payment_customer_bank_id;
						}
						if(isset($request->payment_customer_bank_account_number) && $request->payment_customer_bank_account_number!= ""){
							$data['account_no'] = $request->payment_customer_bank_account_number;
						}
						if(isset($request->payment_customer_paid_via) && $request->payment_customer_paid_via!= ""){
							$data['paid_via'] = $request->payment_customer_paid_via;
						}
						if(isset($request->payment_customer_cheque_no) && $request->payment_customer_cheque_no!= ""){
							$data['cheque_no'] = $request->payment_customer_cheque_no;
						}
						if(isset($request->customer_utr) && $request->customer_utr!= ""){
							$data['neft_charge'] = $request->customer_utr;
						}
						if(isset($request->customer_neft) && $request->customer_neft!= ""){
							$data['neft_charge'] = $request->customer_neft;
						}
						if(isset($request->customer_branch_id) && $request->customer_branch_id!= ""){
							$data['branch_id'] = $request->customer_branch_id;
						}

						if(isset($request->payment_customer_description) && $request->payment_customer_description!= ""){
							$data['description'] = $request->payment_customer_description;
						}

						$res = BankingLedger::create($data);
						$bookingid = $res->id;
						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->customer_branch_id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->customer_neft) && $request->customer_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->customer_branch_id;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}
	
					}

					$bankingLedgerResult=BankingLedger::where('vendor_type', 4)->where('vendor_type_id',$request->payment_customer_name)->where('advanced_payment_status',0)->where('amount','>',0)->get();

					foreach($bankingLedgerResult as $key => $val){
						$request['advanced'] = 0;
						$request['amount'] = $request->cus_payment_amount[$val->id];
						$request['customer_branch_id'] = $request->customer_branch_id;
						$request['banking_ledger_id'] = $val->id;
						$request['neft_charge'] = 1;
						$request['refund'] = 0;
						$this->HeadEntry($request->all(),$val->id,$request->type);
					}
				}
			}elseif($request->type == 3){
			
				if(isset($request->credit_card_id) && $request->credit_card_id!= ""){
					$data['credit_card_id'] = $request->credit_card_id;
				}

				if(isset($request->credit_card_payment_date) && $request->credit_card_payment_date!= ""){
					$data['date'] = date("Y-m-d", strtotime(convertDate($request->credit_card_payment_date)));
				}

				if(isset($request->credit_card_amount) && $request->credit_card_amount!= ""){
					$data['amount'] = $request->credit_card_amount;
				}

				if(isset($request->credit_card_mode) && $request->credit_card_mode!= ""){
					$data['payment_mode'] = $request->credit_card_mode;
				}
				if(isset($request->credit_card_bank_id) && $request->credit_card_bank_id!= ""){
					$data['bank_id'] = $request->credit_card_bank_id;
				}
				if(isset($request->credit_card_account_number) && $request->credit_card_account_number!= ""){
					$data['account_no'] = $request->credit_card_account_number;
				}
				if(isset($request->credit_card_customer_paid_via) && $request->credit_card_customer_paid_via!= ""){
					$data['paid_via'] = $request->credit_card_customer_paid_via;
				}
				if(isset($request->credit_card_customer_cheque_no) && $request->credit_card_customer_cheque_no!= ""){
					$data['cheque_no'] = $request->credit_card_customer_cheque_no;
				}
				if(isset($request->credit_card_utr) && $request->credit_card_utr!= ""){
					$data['neft_utr_no'] = $request->credit_card_utr;
				}
				if(isset($request->credit_card_neft) && $request->credit_card_neft!= ""){
					$data['neft_charge'] = $request->credit_card_neft;
				}
				if(isset($request->credit_card_branch_id) && $request->credit_card_branch_id!= ""){
					$data['branch_id'] = $request->credit_card_branch_id;
				}

				if(isset($request->credit_card_description) && $request->credit_card_description!= ""){
					$data['description'] = $request->credit_card_description;
				}

				if(isset($request->credit_card_payment_date) && $request->credit_card_payment_date!= ""){
					$data['created_at'] = date("Y-m-d", strtotime(convertDate($request->credit_card_payment_date)));
				}

				$res = BankingLedger::create($data);
				$bookingid = $res->id;
				//$this->HeadEntry($request->all(),$bookingid,$request->type);

				$creditCardResult = CreditCradTransaction::where('credit_card_id', $request->credit_card_id)->whereIN('status', [0,1])->where('payment_type','CR')->get();

				foreach($creditCardResult as $val){
					$dueBillsdata['banking_id'] = $bookingid;
					$dueBillsdata['type'] = 6;
					$dueBillsdata['vendor_type_id'] = $request->credit_card_id;
					$dueBillsdata['type_id'] = $val->id;
					$dueBillsdata['amount'] = ($val->total_amount-$val->used_amount);
					$dueBillsdata['pay_amount'] = $request->credit_card_payment_amount[$val->id];
					$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->credit_card_payment_date)));
					BankingDueBillsLedger::create($dueBillsdata);

					$request['advanced'] = 0;
					$request['amount'] = $request->credit_card_payment_amount[$val->id];
					$request['credit_transaction_id'] = $val->id;
					$request['neft_charge'] = 1;
					$request['refund'] = 0;
					$this->HeadEntry($request->all(),$bookingid,$request->type);
				}
			}elseif($request->type == 4){

				if($request->receive_payment_account_type == 1){
					$advancedAmount = $request->vendor_received_payment_amount-$request->vendor_received_total_amount;
					if(isset($request->receive_payment_account_type) && $request->receive_payment_account_type!= ""){
						$data['account_type'] = $request->receive_payment_account_type;
					}
					if(isset($request->received_payment_vendor_type) && $request->received_payment_vendor_type!= ""){
						$data['vendor_type'] = $request->received_payment_vendor_type;
					}
					if(isset($request->received_payment_vendor_name) && $request->received_payment_vendor_name!= ""){
						$data['vendor_type_id'] = $request->received_payment_vendor_name;
					}
					if(isset($request->received_payment_vendor_date) && $request->received_payment_vendor_date!= ""){
						$data['date'] = date("Y-m-d", strtotime(convertDate($request->received_payment_vendor_date)));
					}
					if(isset($request->vendor_received_payment_amount) && $request->vendor_received_payment_amount!= ""){
						$data['amount'] = $request->vendor_received_payment_amount;
					}

					if(isset($advancedAmount) && $advancedAmount > 0){
						$data['advanced_amount'] = $advancedAmount;
					}

					if(isset($request->vendor_received_payment_mode) && $request->vendor_received_payment_mode!= ""){
						$data['payment_mode'] = $request->vendor_received_payment_mode;
					}
					if(isset($request->received_payment_vendor_bank_id) && $request->received_payment_vendor_bank_id!= ""){
						$data['bank_id'] = $request->received_payment_vendor_bank_id;
					}
					if(isset($request->received_payment_vendor_bank_account_number) && $request->received_payment_vendor_bank_account_number!= ""){
						$data['account_no'] = $request->received_payment_vendor_bank_account_number;
					}
					if(isset($request->received_payment_vendor_paid_via) && $request->received_payment_vendor_paid_via!= ""){
						$data['paid_via'] = $request->received_payment_vendor_paid_via;
					}
					if(isset($request->received_payment_vendor_cheque_no) && $request->received_payment_vendor_cheque_no!= ""){
						$data['cheque_no'] = $request->received_payment_vendor_cheque_no;
					}
					if(isset($request->received_payment_vendor_utr) && $request->received_payment_vendor_utr!= ""){
						$data['neft_utr_no'] = $request->received_payment_vendor_utr;
					}
					if(isset($request->received_payment_vendor_neft) && $request->received_payment_vendor_neft!= ""){
						$data['neft_charge'] = $request->received_payment_vendor_neft;
					}
					if(isset($request->received_payment_branch_id) && $request->received_payment_branch_id!= ""){
						$data['branch_id'] = $request->received_payment_branch_id;
					}
					if(isset($request->received_payment_vendor_description) && $request->received_payment_vendor_description!= ""){
						$data['description'] = $request->received_payment_vendor_description;
					}

					$res = BankingLedger::create($data);
					$bookingid = $res->id;

					if($advancedAmount > 0){
						$request['advanced'] = $advancedAmount;
						$request['amount'] = $advancedAmount;
						$request['received_payment_branch_id'] = $request->received_payment_branch_id;
						$request['banking_ledger_id'] = $request->received_payment_vendor_name;
						$request['neft_charge'] = 1;
						$request['refund'] = 0;
						$this->HeadEntry($request->all(),$bookingid,$request->type);
					}

					if(isset($request->received_payment_vendor_neft) && $request->received_payment_vendor_neft!= ""){
						$request['advanced'] = $advancedAmount;
						$request['amount'] = $request->received_payment_vendor_neft;
						$request['received_payment_branch_id'] = $request->received_payment_branch_id;
						$request['banking_ledger_id'] = $request->received_payment_vendor_name;
						$request['neft_charge'] = 0;
						$request['refund'] = 0;
						$this->HeadEntry($request->all(),$bookingid,$request->type);
					}



					if($request->received_payment_vendor_type == 0){
						$bankingLedgerResult = BankingLedger::where('vendor_type', 0)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['amount'] = $request->rent_advanced_payment_amount[$val->id];
							$request['advanced'] = 0;
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 0;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}elseif($request->received_payment_vendor_type == 1){
						$bankingLedgerResult = BankingLedger::where('vendor_type', 1)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->salary_advanced_payment_amount[$val->id];
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 1;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}elseif($request->received_payment_vendor_type == 2){
						$bankingLedgerResult = BankingLedger::where('vendor_type', 2)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->associate_advanced_payment_amount[$val->id];
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 2;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}elseif($request->received_payment_vendor_type == 3){

						$bankingLedgerResult=BankingLedger::where('vendor_type', 3)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->vendor_advanced_payment_amount[$val->id];
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 3;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}

				}elseif($request->receive_payment_account_type == 2){

					$refundAmount = $request->received_customer_total_amount;
					$advancedAmount = $request->received_customer_payment_amount-$request->received_customer_total_amount;

					//if($advancedAmount){
						$data['account_type'] = 2;
						$data['vendor_type'] = 4;
						if(isset($request->received_payment_customer_name) && $request->received_payment_customer_name!= ""){
							$data['vendor_type_id'] = $request->received_payment_customer_name;
						}
						if(isset($request->received_payment_customer_date) && $request->received_payment_customer_date!= ""){
							$data['date'] = date("Y-m-d", strtotime(convertDate($request->received_payment_customer_date)));
						}

						if(isset($advancedAmount) && $advancedAmount!= ""){
							$data['amount'] = $advancedAmount;
							$data['customer_advanced_payment'] = $advancedAmount;
						}

						$data['amount'] = $request->received_customer_payment_amount-$request->received_customer_total_amount;
						$data['customer_advanced_payment'] = $request->received_customer_payment_amount-$request->received_customer_total_amount;

						if(isset($request->received_payment_customer_mode) && $request->received_payment_customer_mode!= ""){
							$data['payment_mode'] = $request->received_payment_customer_mode;
						}
						if(isset($request->received_payment_customer_bank_id) && $request->received_payment_customer_bank_id!= ""){
							$data['bank_id'] = $request->received_payment_customer_bank_id;
						}
						if(isset($request->received_payment_customer_bank_account_number) && $request->received_payment_customer_bank_account_number!= ""){
							$data['account_no'] = $request->received_payment_customer_bank_account_number;
						}
						if(isset($request->received_payment_customer_paid_via) && $request->received_payment_customer_paid_via!= ""){
							$data['paid_via'] = $request->received_payment_customer_paid_via;
						}
						if(isset($request->received_payment_customer_cheque_no) && $request->received_payment_customer_cheque_no!= ""){
							$data['cheque_no'] = $request->received_payment_customer_cheque_no;
						}
						if(isset($request->received_payment_customer_utr) && $request->received_payment_customer_utr!= ""){
							$data['neft_utr_no'] = $request->received_payment_customer_utr;
						}
						if(isset($request->received_payment_customer_neft) && $request->received_payment_customer_neft!= ""){
							$data['neft_charge'] = $request->received_payment_customer_neft;
						}
						if(isset($request->received_payment_customer_branch_id) && $request->received_payment_customer_branch_id!= ""){
							$data['branch_id'] = $request->received_payment_customer_branch_id;
						}
						if(isset($request->received_payment_customer_description) && $request->received_payment_customer_description!= ""){
							$data['description'] = $request->received_payment_customer_description;
						}
						$res = BankingLedger::create($data);
						$bookingid = $res->id;

						/*if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->received_payment_customer_branch_id;
							$request['neft_charge'] = 1;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}*/

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['refund'] = 0;
							$request['amount'] = $request->received_customer_payment_amount-$request->received_customer_total_amount;
							$request['customer_branch_id'] = $request->received_payment_customer_branch_id;
							$request['banking_ledger_id'] = 0;
							$request['neft_charge'] = 1;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						/*if(isset($request->received_payment_customer_neft) && $request->received_payment_customer_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['refund'] = $refundAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->payment_branch_id;
							$request['banking_ledger_id'] = 0;
							$request['neft_charge'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}*/
					//}

					$bankingLedgerResult=BankingLedger::where('vendor_type', 5)->where('vendor_type_id',$request->received_payment_customer_name)->whereNull('customer_advanced_payment')->where('customer_refund_payment','>',0)->where('advanced_payment_status',0)->get();

					foreach($bankingLedgerResult as $key => $val){
						$request['advanced'] = 0;
						$request['refund'] = 0;
						$request['amount'] = $request->received_cus_payment_amount[$val->id];
						$request['customer_branch_id'] = $request->received_payment_customer_branch_id;
						$request['banking_ledger_id'] = $val->id;
						$request['neft_charge'] = 1;
						$this->HeadEntry($request->all(),$val->id,$request->type);
					}
				}
			}elseif($request->type == 5){
				if(isset($request->income_head_id) && $request->income_head_id!= ""){
					$data['expense_account'] = $request->income_head_id;
				}
				if(isset($request->income_head_id1) && $request->income_head_id1!= ""){
					$data['expense_account1'] = $request->income_head_id1;
				}
				if(isset($request->income_head_id2) && $request->income_head_id2!= ""){
					$data['expense_account2'] = $request->income_head_id2;
				}
				if(isset($request->income_head_id3) && $request->income_head_id3!= ""){
					$data['expense_account3'] = $request->income_head_id3;
				}
				if(isset($request->indirect_income_date) && $request->indirect_income_date!= ""){
					$data['date'] = date("Y-m-d", strtotime(convertDate($request->indirect_income_date)));
				}
				if(isset($request->indirect_income_amount) && $request->indirect_income_amount!= ""){
					$data['amount'] = $request->indirect_income_amount;
				}
				if(isset($request->indirect_income_description) && $request->indirect_income_description!= ""){
					$data['description'] = $request->indirect_income_description;
				}
				if(isset($request->indirect_income_mode) && $request->indirect_income_mode!= ""){
					$data['payment_mode'] = $request->indirect_income_mode;
				}
				if(isset($request->indirect_income_bank_id) && $request->indirect_income_bank_id!= ""){
					$data['bank_id'] = $request->indirect_income_bank_id;
				}
				if(isset($request->indirect_income_account_no) && $request->indirect_income_account_no!= ""){
					$data['account_no'] = $request->indirect_income_account_no;
				}
				if(isset($request->indirect_income_paid_via) && $request->indirect_income_paid_via!= ""){
					$data['paid_via'] = $request->indirect_income_paid_via;
				}
				if(isset($request->indirect_income_cheque_no) && $request->indirect_income_cheque_no!= ""){
					$data['cheque_no'] = $request->indirect_income_cheque_no;
				}
				if(isset($request->indirect_income_utr) && $request->indirect_income_utr!= ""){
					$data['neft_utr_no'] = $request->indirect_income_utr;
				}
				if(isset($request->indirect_income_neft) && $request->indirect_income_neft!= ""){
					$data['neft_charge'] = $request->indirect_income_neft;
				}
				if(isset($request->indirect_income_branch_id) && $request->indirect_income_branch_id!= ""){
					$data['branch_id'] = $request->indirect_income_branch_id;
				}
				$res = BankingLedger::create($data);
				$bookingid = $res->id;
				$request['neft_charge'] = 0;
				$request['refund'] = 0;
				$this->HeadEntry($request->all(),$bookingid,$request->type);
			}

		DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }
		
		if ($res) {
			return redirect()->route('admin.banking.index')->with('success', 'Banking Ledger Created Successfully!');
		} else {
			return back()->with('alert', 'Problem With Banking Ledger');
		}	
	}
	
    public function innerListing()
    { 
		if(check_my_permission( Auth::user()->id,"182") != "1"){
		  return redirect()->route('admin.dashboard');
		}
	
		if(isset($_GET["type"]) && $_GET["type"]!= "" && isset($_GET["id"]) && $_GET["id"]!= ""){
			$type = trim($_GET["type"]);
			$typeId = trim($_GET["id"]);

			if($type == 'bank'){
				$data['title']='Banking Transaction Listing';
				$data['records'] = BankingLedger::where('bank_id',$typeId)->orderby('id','desc')->get();
			}elseif($type == 'branch'){
				$data['title']='Branch Transaction Listing';
				$data['records'] = BankingLedger::where('branch_id',$typeId)->orderby('id','desc')->get();
			}

			$data['type'] = $_GET["type"];
			$data['id'] = $_GET["id"];
		}

        return view('templates.admin.banking_management.innerlisting', $data);
    }

    public function ajaxInnerListing(Request $request)
    { 

        if ($request->ajax()) {

            /******* fillter query start ****/ 

            $arrFormData = array();   

            if(!empty($_POST['searchform']))
            {
                foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }

            $data=BankingLedger::where('type','!=',3);

            if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
             {
             	if($arrFormData['start_date'] !=''){
                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if($arrFormData['end_date'] !=''){
                        $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    }
                    else
                    {
                        $endDate='';
                    }
                    $data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                }

                if($arrFormData['listingttype'] !=''){
                    $type=$arrFormData['listingttype'];
                    $id=$arrFormData['listingttypeid'];
                    if($type == 'bank'){
                    	$data=$data->where('bank_id',$id);	
                    }elseif($type == 'branch'){
                    	$data=$data->where('branch_id',$id);
                    }
                }
            }

            $data = $data->orderby('id','desc')->get();

            return Datatables::of($data)

            ->addIndexColumn() 

           ->addColumn('date', function($row){
                $date = date("d/m/Y", strtotime(convertDate($row->date)));
                return $date;
            })
            ->rawColumns(['date'])

            ->addColumn('type', function($row){
                if($row->type == 1){
		        	$type = 'Money IN';
		        }elseif($row->type == 2){
		        	$type = 'Money Out';
		        }else{
		        	$type = 'N/A';
		        }
                return $type;
            })
            ->rawColumns(['type'])

            ->addColumn('subtype', function($row){
            	if($row->banking_type == 1){
		            if($row->expense_account3){
		            	$type = 'Expense/'.getAcountHead($row->expense_account3);
		            }elseif($row->expense_account2){
		            	$type = 'Expense/'.getAcountHead($row->expense_account2);
		            }elseif($row->expense_account1){
		            	$type = 'Expense/'.getAcountHead($row->expense_account1);
		            }elseif($row->expense_account){
		            	$type = 'Expense/'.getAcountHead($row->expense_account);
		            }
		        }elseif($row->banking_type == 2){
		        	$type = 'Payment';
		        }elseif($row->banking_type == 3){
		        	$type = 'Card Payment';
		        }elseif($row->banking_type == 4){
		        	$type = 'Receive';
		        }elseif($row->banking_type == 5){
		        	if($row->expense_account3){
		            	$type = 'Income/'.getAcountHead($row->expense_account3);
		            }elseif($row->expense_account2){
		            	$type = 'Income/'.getAcountHead($row->expense_account2);
		            }elseif($row->expense_account1){
		            	$type = 'Income/'.getAcountHead($row->expense_account1);
		            }elseif($row->expense_account){
		            	$type = 'Income/'.getAcountHead($row->expense_account);
		            }
		        }
                return $type;
            })
            ->rawColumns(['subtype'])

            ->addColumn('amount', function($row){

                $amount = ($row->amount+$row->neft_charge);

                return $amount;
            })
            ->rawColumns(['amount'])
            ->addColumn('action', function($row){ 

            	if($row->type == 2){
            		$advancedStatus = getAdvancedEntry($row->vendor_type,$row->id,$row->vendor_type_id);
            	}else{
            		$advancedStatus = getNextAdvancedEntry($row->vendor_type,$row->id,$row->vendor_type_id);
            	}
				
				if( (check_my_permission( Auth::user()->id,"191") == "1") || (check_my_permission( Auth::user()->id,"192") == "1") ){

					if($advancedStatus == 0){
					
						 $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">'; 

						//$url = URL::to("admin/hr/designation/delete/".$row->id."");  

						$url1 = URL::to("admin/banking/edit/".$row->id."");  

						$ur2 = URL::to("admin/banking/delete/".$row->id."");   
						if(check_my_permission( Auth::user()->id,"191") == "1"){
							$btn .= '<a class="dropdown-item" href="'.$url1.'" title="Edit"><i class="icon-pencil7  mr-2"></i> Edit</a>  '; 
						}						
						if(check_my_permission( Auth::user()->id,"192") == "1"){
							$btn .= '<a class="dropdown-item delete-transaction" href="'.$ur2.'" title="Delete" ><i class="icon-trash-alt  mr-2"></i> Delete</a>  ';
						}

						$btn .= '</div></div></div>';  
					}else{
						$btn = 'N/A';
					}

					return $btn;
					
				}

            })

            ->rawColumns(['action'])

            ->make(true);

        }

    }

    public function edit($id)

    {
		if(check_my_permission( Auth::user()->id,"191") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
        $data['banks']=SamraddhBank::where('status',1)->get();
        $data['branches']=Branch::where('status',1)->get();
        $data['bankingLedger'] = BankingLedger::with('relatedRecord','relatedAdvancedRecord')->where('id',$id)->first();

        $data['relatedRecordAmount'] = BankingDueBillsLedger::where('banking_id',$id)->sum('pay_amount');
        $data['relatedRecordAdvancedAmount'] = BankingAdvancedLedger::where('banking_id',$id)->sum('pay_amount');

        if($data['bankingLedger']->banking_type == 1){
			$data['title']='Money Out | Edit Expense Payment';
		}elseif($data['bankingLedger']->banking_type == 2){
			$data['title']='Money Out | Edit Payment';
		}elseif($data['bankingLedger']->banking_type == 3){
			$data['title']='Money Out | Edit Card Payment';
		}elseif($data['bankingLedger']->banking_type == 4){
			$data['title']='Money In | Edit Received';
		}elseif($data['bankingLedger']->banking_type == 5){
			$data['title']='Money In | Edit Other income';
		}

        if($data['bankingLedger']){
	        if($data['bankingLedger']->banking_type == 1){
	        	$data['heads']= AccountHeads::where('parent_id',86)->get();
	        }elseif($data['bankingLedger']->banking_type == 5){
	        	$data['heads']= AccountHeads::whereIN('parent_id',[12,13])->get();
	        }else{
	        	$data['heads']= '';
	        }
			
			$data['credit_cards']= CreditCard::where('status','1')->get();
			$data['indirect_income_heads']= AccountHeads::where('parent_id',3)->get();
	          
	        $data['accounts'] = SamraddhBankAccount::where('bank_id',$data['bankingLedger']->bank_id)->first();
	        $data['cheques'] = SamraddhCheque::where('id',$data['bankingLedger']->cheque_no)->get();

	        $subCategory2 = array();
	        $i = 0;
	        if($data['heads']){
		        foreach ($data['heads'] as $value1) {
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
		    }

	        $subCategory3 = array();
	        if($subCategory2){
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
		    }

	        $subCategory4 = array();
	        if($subCategory3){
		        foreach ($subCategory3 as $value5) {
		            $record5 = AccountHeads::where('parent_id',$value5->head_id)->get();

		            foreach ($record3 as $value6) {
		                $record6 = AccountHeads::where('head_id',$value6->head_id)->first();
		                if($record6){
		                    $subCategory4[$i] = $record6;
		                    $i++;
		                }
		            }
		        }
		        $data['subCategory4'] = $subCategory4;
		    }

	        $subCategory5 = array();
	        if($subCategory4){
		        foreach ($subCategory4 as $value7) {
		            $record7 = AccountHeads::where('parent_id',$value7->head_id)->get();

		            foreach ($record3 as $value8) {
		                $record8 = AccountHeads::where('head_id',$value8->head_id)->first();
		                if($record8){
		                    $subCategory5[$i] = $record8;
		                    $i++;
		                }
		            }
		        }
		        $data['subCategory5'] = $subCategory5;
		    }

        	return view('templates.admin.banking_management.edit', $data);
        }else{
        	return redirect()->route('admin.banking.index')->with('success', 'Record Not Found');
        }
    }

    public function update(Request $request)

    {
        DB::beginTransaction();

        try {

        	//Rent Ledger delete    
            $rentLeaser = RentLiabilityLedger::where('banking_id',$request->id)->delete(); 

            //Employee Ledger delete    
            $employeeLeaser = EmployeeLedger::where('banking_id',$request->id)->delete(); 

            // employee ledger delete
            $vendorbillPayment = VendorBillPayment::where('banking_id',$request->id)->delete();

            // customer ledger delete
            $customerTransaction = CustomerTransaction::where('banking_id',$request->id)->delete();

            // associate ledger delete
            $associateTransaction = AssociateTransaction::where('banking_id',$request->id)->delete();

            // advanced ledger delete
            $associateTransaction = AdvancedTransaction::where('banking_id',$request->id)->delete();

            // vendor ledger delete
            $associateTransaction = VendorTransaction::where('banking_id',$request->id)->delete();

            // vendor ledger delete
            $creditCardTransaction = CreditCradTransaction::where('banking_id',$request->id)->delete();

            //All head transaction
            $headData = AllHeadTransaction::whereIN('type',[26])->where('type_id',$request->id)->delete();   

            // Branch Day Book delete
            $branchDayBook = BranchDaybook::whereIN('type',[26])->where('type_id',$request->id)->delete();

            // Member Transaction delete
            $memberTransaction = MemberTransaction::whereIN('type',[26])->where('type_id',$request->id)->delete();
        
            // Samradhh Bank Day Book delete
            $samraddhBankDaybook = SamraddhBankDaybook::whereIN('type',[26])->where('type_id',$request->id)->delete();

            $bankingResult = BankingLedger::find($request['id']);

            $relatedRecord = BankingDueBillsLedger::where('banking_id',$request['id'])->get();

            if(count($relatedRecord) > 0){
	            foreach($relatedRecord as $rec){
	            	if($rec->type == 0){
	            		$rentPaymentResult = RentPayment::where('id', $rec->type_id)->first();
	            		$rentPaymentUpdate = RentPayment::find($rentPaymentResult->id);
		                $rentPaymentData['transferred_amount'] = ($rentPaymentResult->transferred_amount-$rec->pay_amount);
		                $rentPaymentData['status'] = 0;
		                $rentPaymentUpdate->update($rentPaymentData);
		            }elseif($rec->type == 1){
	            		$employeePaymentResult = EmployeeSalary::where('id', $rec->type_id)->first();
	            		$employeePaymentUpdate = EmployeeSalary::find($employeePaymentResult->id);
		                $employeePaymentData['transferred_salary'] = ($employeePaymentResult->transferred_salary-$rec->pay_amount);
		                $employeePaymentData['status'] = 0;
		                $employeePaymentUpdate->update($employeePaymentData);
		            }elseif($rec->type == 2){
	            		$commissionPaymentResult = CommissionLeaserDetail::where('id', $rec->type_id)->first();
	            		$commissionPaymentUpdate = commissionPaymentResult::find($commissionPaymentResult->id);
		                $commissionPaymentData['transferred_amount'] = ($commissionPaymentResult->transferred_amount-$rec->pay_amount);
		                $commissionPaymentData['transferred_fuel_amount'] = ($commissionPaymentResult->transferred_fuel_amount-$rec->pay_amount);
		                $commissionPaymentData['status'] = 2;
		                $commissionPaymentUpdate->update($commissionPaymentData);
		            }elseif($rec->type == 3){
	            		$vendorPaymentResult = VendorBill::where('id', $rec->type_id)->first();
	            		$vendorPaymentUpdate = VendorBill::find($vendorPaymentResult->id);
		                $vendorPaymentData['transferd_amount'] = ($vendorPaymentResult->transferred_amount-$rec->pay_amount);
		                $vendorPaymentData['balance'] = ($vendorPaymentResult->balance+$rec->pay_amount);
		                $vendorPaymentData['status'] = 0;
		                $vendorPaymentUpdate->update($vendorPaymentData);
		            }elseif($rec->type == 6){
	            		$cCardPaymentResult = CreditCradTransaction::where('id', $rec->type_id)->first();
	            		$cCardPaymentUpdate = CreditCradTransaction::find($cCardPaymentResult->id);
		                $cCardPaymentData['transferd_amount'] = ($cCardPaymentResult->used_amount-$rec->pay_amount);
		                $cCardPaymentData['status'] = 0;
		                $cCardPaymentUpdate->update($cCardPaymentData);
		            }
	            }
            	BankingDueBillsLedger::where('banking_id',$request['id'])->delete();
            }else{
            	$relatedAdvancedRecord = BankingAdvancedLedger::where('banking_id',$request['id'])->get();

            	if($relatedAdvancedRecord){
            		foreach ($relatedAdvancedRecord as $key => $value) {
            			if($value->type == 0){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 1){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 2){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 3){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 4){
            				
            			}
            		}
            		BankingAdvancedLedger::where('banking_id',$request['id'])->delete();
            	}
            }

            $data = array();
			$data['type'] = $request->subtype;
			$data['banking_type'] = $request->type;

            if($request->type == 1){

				if(isset($request->expense_receipt)){
		            $mainFolder = storage_path().'/images/banking';
		            $file = $request->expense_receipt;
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
		            $file_id = $request->expense_file_id;
		        }
				
				$data['file_id'] = $file_id;
				if(isset($request->expense_account) && $request->expense_account!= ""){
					$data['expense_account'] = $request->expense_account;
				}
				if(isset($request->expense_account1) && $request->expense_account1!= ""){
					$data['expense_account1'] = $request->expense_account1;
				}
				if(isset($request->expense_account2) && $request->expense_account2!= ""){
					$data['expense_account2'] = $request->expense_account2;
				}
				if(isset($request->expense_account3) && $request->expense_account3!= ""){
					$data['expense_account3'] = $request->expense_account3;
				}
				if(isset($request->expense_date) && $request->expense_date!= ""){
					$data['date'] = date("Y-m-d", strtotime(convertDate($request->expense_date)));
				}
				if(isset($request->expense_amount) && $request->expense_amount!= ""){
					$data['amount'] = $request->expense_amount;
				}
				if(isset($request->expense_description) && $request->expense_description!= ""){
					$data['description'] = $request->expense_description;
				}
				if(isset($request->expense_mode) && $request->expense_mode!= ""){
					$data['payment_mode'] = $request->expense_mode;
				}
				if(isset($request->expense_bank_id) && $request->expense_bank_id!= ""){
					$data['bank_id'] = $request->expense_bank_id;
				}
				if(isset($request->expense_account_no) && $request->expense_account_no!= ""){
					$data['account_no'] = $request->expense_account_no;
				}
				if(isset($request->expense_paid_via) && $request->expense_paid_via!= ""){
					$data['paid_via'] = $request->expense_paid_via;
				}
				if(isset($request->expense_cheque_no) && $request->expense_cheque_no!= ""){
					$data['cheque_no'] = $request->expense_cheque_no;
				}
				if(isset($request->expense_utr) && $request->expense_utr!= ""){
					$data['neft_utr_no'] = $request->expense_utr;
				}
				if(isset($request->expense_neft) && $request->expense_neft!= ""){
					$data['neft_charge'] = $request->expense_neft;
				}
				if(isset($request->expense_branch_id) && $request->expense_branch_id!= ""){
					$data['branch_id'] = $request->expense_branch_id;
				}
				if(isset($request->expense_date) && $request->expense_date!= ""){
					$data['created_at'] = date("Y-m-d", strtotime(convertDate($request->expense_date)));
				}
				$res = BankingLedger::create($data);	
				$bookingid = $res->id;
				$request['neft_charge'] = 0;
				$request['refund'] = 0;
				$request['advanced'] = 0;
				$this->HeadEntry($request->all(),$bookingid,$request->type);
			}elseif($request->type == 2){
				if($request->payment_account_payment == 1){
					if(isset($request->payment_account_payment) && $request->payment_account_payment!= ""){
						$data['account_type'] = $request->payment_account_payment;
					}
					if(isset($request->payemnt_vendor_type) && $request->payemnt_vendor_type!= ""){
						$data['vendor_type'] = $request->payemnt_vendor_type;
					}
					if(isset($request->payment_vendor_name) && $request->payment_vendor_name!= ""){
						$data['vendor_type_id'] = $request->payment_vendor_name;
					}
					if(isset($request->payment_vendor_date) && $request->payment_vendor_date!= ""){
						$data['date'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
					}

					if(isset($request->vendor_payment_amount) && $request->vendor_payment_amount!= ""){
						$data['amount'] = $request->vendor_payment_amount;
					}

					$advancedAmount = ($request->vendor_payment_amount-$request->vendor_total_amount);

					if(isset($advancedAmount) && $advancedAmount > 0){
						$data['advanced_amount'] = $advancedAmount;
					}

					if(isset($request->vendor_payment_mode) && $request->vendor_payment_mode!= ""){
						$data['payment_mode'] = $request->vendor_payment_mode;
					}
					if(isset($request->payment_vendor_bank_id) && $request->payment_vendor_bank_id!= ""){
						$data['bank_id'] = $request->payment_vendor_bank_id;
					}
					if(isset($request->payment_vendor_bank_account_number) && $request->payment_vendor_bank_account_number!= ""){
						$data['account_no'] = $request->payment_vendor_bank_account_number;
					}
					if(isset($request->payment_vendor_paid_via) && $request->payment_vendor_paid_via!= ""){
						$data['paid_via'] = $request->payment_vendor_paid_via;
					}
					if(isset($request->payment_vendor_cheque_no) && $request->payment_vendor_cheque_no!= ""){
						$data['cheque_no'] = $request->payment_vendor_cheque_no;
					}
					if(isset($request->vendor_utr) && $request->vendor_utr!= ""){
						$data['neft_utr_no'] = $request->vendor_utr;
					}
					if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
						$data['neft_charge'] = $request->vendor_neft;
					}
					if(isset($request->payment_branch_id) && $request->payment_branch_id!= ""){
						$data['branch_id'] = $request->payment_branch_id;
					}

					if(isset($request->payment_vendor_description) && $request->payment_vendor_description!= ""){
						$data['description'] = $request->payment_vendor_description;
					}

					if(isset($request->payment_vendor_date) && $request->payment_vendor_date!= ""){
						$data['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
					}

					$res = BankingLedger::create($data);
					$bookingid = $res->id;

					if($request->payemnt_vendor_type == 0){
						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['rent_liability_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['rent_liability_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$rentPaymentResult = RentPayment::where('rent_liability_id', $request->payment_vendor_name)->where('status',0)->get();

						foreach($rentPaymentResult as $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->rent_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['rent_liability_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 0;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->id;
							$dueBillsdata['pay_amount'] = $request->rent_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);

						}
					}elseif($request->payemnt_vendor_type == 1){

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['employee_salary_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['employee_salary_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$employeePaymentResult = EmployeeSalary::where('employee_id', $request->payment_vendor_name)->where('status',0)->get();

						foreach($employeePaymentResult as $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->salary_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['employee_salary_id'] = $val->employee_id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 1;
							$dueBillsdata['type_id'] = $val->employee_id;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['pay_amount'] = $request->salary_payment_amount[$val->id];
							$dueBillsdata['pay_amount'] = $request->salary_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);
						}
					}elseif($request->payemnt_vendor_type == 2){

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['fuelamount'] = 0;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['associate_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['fuelamount'] = 0;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['associate_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$associateLedgerResult=CommissionLeaserDetail::where('member_id', $request->payment_vendor_name)->where('status',2)->get();

						foreach($associateLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->associate_commission_payment_amount[$val->id];
							$request['fuelamount'] = $request->associate_fuel_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['associate_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 2;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->id;
							$dueBillsdata['amount'] = $val->amount-$request->associate_commission_payment_amount[$val->id];
							$dueBillsdata['pay_amount'] = $request->associate_payment_amount[$val->id];
							$dueBillsdata['fule_amount'] = $val->fuel-$request->associate_fuel_payment_amount[$val->id];
							$dueBillsdata['pay_fuel_amount'] = $request->associate_fuel_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);
						}
					}elseif($request->payemnt_vendor_type == 3){

						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['vendor_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->vendor_neft) && $request->vendor_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $request->vendor_neft;
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['vendor_ledger_id'] = $request->payment_vendor_name;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						$vendorLedgerResult=VendorBill::where('vendor_id', $request->payment_vendor_name)->where('status',0)->get();

						foreach($vendorLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->vendor_pending_payment_amount[$val->id];
							$request['payment_vendor_branch_id'] = $request->payment_branch_id;
							$request['vendor_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$dueBillsdata['banking_id'] = $bookingid;
							$dueBillsdata['type'] = 3;
							$dueBillsdata['vendor_type_id'] = $request->payment_vendor_name;
							$dueBillsdata['type_id'] = $val->id;
							$dueBillsdata['pay_amount'] = $request->vendor_pending_payment_amount[$val->id];
							$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
							BankingDueBillsLedger::create($dueBillsdata);
						}
					}	
				}elseif($request->payment_account_payment == 2){

					$advancedAmount = $request->payment_customer_amount-$request->customer_total_amount;

					if($advancedAmount){

						$data['account_type'] = 2;
						$data['vendor_type'] = 5;
						if(isset($request->payment_customer_name) && $request->payment_customer_name!= ""){
							$data['vendor_type_id'] = $request->payment_customer_name;
						}
						if(isset($request->payment_customer_date) && $request->payment_customer_date!= ""){
							$data['date'] = date("Y-m-d", strtotime(convertDate($request->payment_customer_date)));
						}

						if(isset($request->payment_customer_amount) && $request->payment_customer_amount!= ""){
							$data['amount'] = $request->payment_customer_amount;
						}

						if(isset($advancedAmount) && $advancedAmount!= ""){
							$data['customer_refund_payment'] = $advancedAmount;
						}

						if(isset($request->customer_payment_mode) && $request->customer_payment_mode!= ""){
							$data['payment_mode'] = $request->customer_payment_mode;
						}
						if(isset($request->payment_customer_bank_id) && $request->payment_customer_bank_id!= ""){
							$data['bank_id'] = $request->payment_customer_bank_id;
						}
						if(isset($request->payment_customer_bank_account_number) && $request->payment_customer_bank_account_number!= ""){
							$data['account_no'] = $request->payment_customer_bank_account_number;
						}
						if(isset($request->payment_customer_paid_via) && $request->payment_customer_paid_via!= ""){
							$data['paid_via'] = $request->payment_customer_paid_via;
						}
						if(isset($request->payment_customer_cheque_no) && $request->payment_customer_cheque_no!= ""){
							$data['cheque_no'] = $request->payment_customer_cheque_no;
						}
						if(isset($request->customer_utr) && $request->customer_utr!= ""){
							$data['neft_charge'] = $request->customer_utr;
						}
						if(isset($request->customer_neft) && $request->customer_neft!= ""){
							$data['neft_charge'] = $request->customer_neft;
						}
						if(isset($request->customer_branch_id) && $request->customer_branch_id!= ""){
							$data['branch_id'] = $request->customer_branch_id;
						}
						if(isset($request->payment_customer_description) && $request->payment_customer_description!= ""){
							$data['description'] = $request->payment_customer_description;
						}

						$res = BankingLedger::create($data);
						$bookingid = $res->id;
						if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->customer_branch_id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}

						if(isset($request->customer_neft) && $request->customer_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->customer_branch_id;
							$request['neft_charge'] = 0;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}
	
					}

					$bankingLedgerResult=BankingLedger::where('vendor_type', 4)->where('vendor_type_id',$request->payment_customer_name)->where('advanced_payment_status',0)->where('amount','>',0)->get();

					foreach($bankingLedgerResult as $key => $val){
						$request['advanced'] = 0;
						$request['amount'] = $request->cus_payment_amount[$val->id];
						$request['customer_branch_id'] = $request->customer_branch_id;
						$request['banking_ledger_id'] = $val->id;
						$request['neft_charge'] = 1;
						$request['refund'] = 0;
						$this->HeadEntry($request->all(),$val->id,$request->type);
					}
				}
			}elseif($request->type == 3){
			
				if(isset($request->credit_card_id) && $request->credit_card_id!= ""){
					$data['credit_card_id'] = $request->credit_card_id;
				}

				if(isset($request->credit_card_payment_date) && $request->credit_card_payment_date!= ""){
					$data['date'] = date("Y-m-d", strtotime(convertDate($request->credit_card_payment_date)));
				}

				if(isset($request->credit_card_amount) && $request->credit_card_amount!= ""){
					$data['amount'] = $request->credit_card_amount;
				}

				if(isset($request->credit_card_mode) && $request->credit_card_mode!= ""){
					$data['payment_mode'] = $request->credit_card_mode;
				}
				if(isset($request->credit_card_bank_id) && $request->credit_card_bank_id!= ""){
					$data['bank_id'] = $request->credit_card_bank_id;
				}
				if(isset($request->credit_card_account_number) && $request->credit_card_account_number!= ""){
					$data['account_no'] = $request->credit_card_account_number;
				}
				if(isset($request->credit_card_customer_paid_via) && $request->credit_card_customer_paid_via!= ""){
					$data['paid_via'] = $request->credit_card_customer_paid_via;
				}
				if(isset($request->credit_card_customer_cheque_no) && $request->credit_card_customer_cheque_no!= ""){
					$data['cheque_no'] = $request->credit_card_customer_cheque_no;
				}
				if(isset($request->credit_card_utr) && $request->credit_card_utr!= ""){
					$data['neft_utr_no'] = $request->credit_card_utr;
				}
				if(isset($request->credit_card_neft) && $request->credit_card_neft!= ""){
					$data['neft_charge'] = $request->credit_card_neft;
				}
				if(isset($request->credit_card_branch_id) && $request->credit_card_branch_id!= ""){
					$data['branch_id'] = $request->credit_card_branch_id;
				}

				if(isset($request->credit_card_description) && $request->credit_card_description!= ""){
					$data['description'] = $request->credit_card_description;
				}

				if(isset($request->credit_card_payment_date) && $request->credit_card_payment_date!= ""){
					$data['created_at'] = date("Y-m-d", strtotime(convertDate($request->credit_card_payment_date)));
				}

				$res = BankingLedger::create($data);
				$bookingid = $res->id;
				//$this->HeadEntry($request->all(),$bookingid,$request->type);

				$creditCardResult = CreditCradTransaction::where('credit_card_id', $request->credit_card_id)->whereIN('status', [0,1])->where('payment_type','CR')->get();

				foreach($creditCardResult as $val){
					$request['advanced'] = 0;
					$request['amount'] = $request->credit_card_payment_amount[$val->id];
					$request['credit_transaction_id'] = $val->id;
					$request['neft_charge'] = 1;
					$request['refund'] = 0;
					$this->HeadEntry($request->all(),$bookingid,$request->type);

					/*$dueBillsdata['banking_id'] = $bookingid;
					$dueBillsdata['type'] = 0;
					$dueBillsdata['type_id'] = $val->id;
					$dueBillsdata['amount'] = ($val->rent_amount-$val->transferred_amount);
					$dueBillsdata['pay_amount'] = $request->rent_payment_amount[$val->id];
					$dueBillsdata['created_at'] = date("Y-m-d", strtotime(convertDate($request->payment_vendor_date)));
					BankingDueBillsLedger::create($dueBillsdata);*/

				}
			}elseif($request->type == 4){
				if($request->receive_payment_account_type == 1){
					$advancedAmount = $request->vendor_received_payment_amount-$request->vendor_received_total_amount;
					if(isset($request->receive_payment_account_type) && $request->receive_payment_account_type!= ""){
						$data['account_type'] = $request->receive_payment_account_type;
					}
					if(isset($request->received_payment_vendor_type) && $request->received_payment_vendor_type!= ""){
						$data['vendor_type'] = $request->received_payment_vendor_type;
					}
					if(isset($request->received_payment_vendor_name) && $request->received_payment_vendor_name!= ""){
						$data['vendor_type_id'] = $request->received_payment_vendor_name;
					}
					if(isset($request->received_payment_vendor_date) && $request->received_payment_vendor_date!= ""){
						$data['date'] = date("Y-m-d", strtotime(convertDate($request->received_payment_vendor_date)));
					}
					if(isset($request->vendor_received_payment_amount) && $request->vendor_received_payment_amount!= ""){
						$data['amount'] = $request->vendor_received_payment_amount;
					}

					if(isset($advancedAmount) && $advancedAmount > 0){
						$data['advanced_amount'] = $advancedAmount;
					}

					if(isset($request->vendor_received_payment_mode) && $request->vendor_received_payment_mode!= ""){
						$data['payment_mode'] = $request->vendor_received_payment_mode;
					}
					if(isset($request->received_payment_vendor_bank_id) && $request->received_payment_vendor_bank_id!= ""){
						$data['bank_id'] = $request->received_payment_vendor_bank_id;
					}
					if(isset($request->received_payment_vendor_bank_account_number) && $request->received_payment_vendor_bank_account_number!= ""){
						$data['account_no'] = $request->received_payment_vendor_bank_account_number;
					}
					if(isset($request->received_payment_vendor_paid_via) && $request->received_payment_vendor_paid_via!= ""){
						$data['paid_via'] = $request->received_payment_vendor_paid_via;
					}
					if(isset($request->received_payment_vendor_cheque_no) && $request->received_payment_vendor_cheque_no!= ""){
						$data['cheque_no'] = $request->received_payment_vendor_cheque_no;
					}
					if(isset($request->received_payment_vendor_utr) && $request->received_payment_vendor_utr!= ""){
						$data['neft_utr_no'] = $request->received_payment_vendor_utr;
					}
					if(isset($request->received_payment_vendor_neft) && $request->received_payment_vendor_neft!= ""){
						$data['neft_charge'] = $request->received_payment_vendor_neft;
					}
					if(isset($request->received_payment_branch_id) && $request->received_payment_branch_id!= ""){
						$data['branch_id'] = $request->received_payment_branch_id;
					}
					if(isset($request->received_payment_vendor_description) && $request->received_payment_vendor_description!= ""){
						$data['description'] = $request->received_payment_vendor_description;
					}

					$res = BankingLedger::create($data);
					$bookingid = $res->id;

					if($advancedAmount > 0){
						$request['advanced'] = $advancedAmount;
						$request['amount'] = $advancedAmount;
						$request['received_payment_branch_id'] = $request->received_payment_branch_id;
						$request['banking_ledger_id'] = $request->received_payment_vendor_name;
						$request['neft_charge'] = 1;
						$request['refund'] = 0;
						$this->HeadEntry($request->all(),$bookingid,$request->type);
					}

					if(isset($request->received_payment_vendor_neft) && $request->received_payment_vendor_neft!= ""){
						$request['advanced'] = $advancedAmount;
						$request['amount'] = $request->received_payment_vendor_neft;
						$request['received_payment_branch_id'] = $request->received_payment_branch_id;
						$request['banking_ledger_id'] = $request->received_payment_vendor_name;
						$request['neft_charge'] = 0;
						$request['refund'] = 0;
						$this->HeadEntry($request->all(),$bookingid,$request->type);
					}

					if($request->received_payment_vendor_type == 0){
						$bankingLedgerResult = BankingLedger::where('vendor_type', 0)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['amount'] = $request->rent_advanced_payment_amount[$val->id];
							$request['advanced'] = 0;
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 0;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}elseif($request->received_payment_vendor_type == 1){
						$bankingLedgerResult = BankingLedger::where('vendor_type', 1)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->salary_advanced_payment_amount[$val->id];
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 1;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}elseif($request->received_payment_vendor_type == 2){
						$bankingLedgerResult = BankingLedger::where('vendor_type', 2)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->associate_advanced_payment_amount[$val->id];
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 2;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}elseif($request->received_payment_vendor_type == 3){

						$bankingLedgerResult=BankingLedger::where('vendor_type', 3)->where('vendor_type_id',$request->received_payment_vendor_name)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();

						foreach($bankingLedgerResult as $key => $val){
							$request['advanced'] = 0;
							$request['amount'] = $request->vendor_advanced_payment_amount[$val->id];
							$request['received_payment_branch_id'] = $request->received_payment_branch_id;
							$request['banking_ledger_id'] = $val->id;
							$request['neft_charge'] = 1;
							$request['refund'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);

							$bankingAdvancedData['banking_id'] = $bookingid;
							$bankingAdvancedData['banking_transaction_id'] = $val->id;
							$bankingAdvancedData['type'] = 3;
							$bankingAdvancedData['vendor_type_id'] = $request->received_payment_vendor_name;
							$bankingAdvancedData['amount'] = $val->advanced_amount;
							$bankingAdvancedData['pay_amount'] = $request->rent_advanced_payment_amount[$val->id];
							BankingAdvancedLedger::create($bankingAdvancedData);
						}
					}

				}elseif($request->receive_payment_account_type == 2){

					$refundAmount = $request->received_customer_total_amount;
					$advancedAmount = 0;
					//if($advancedAmount){
						$data['account_type'] = 2;
						$data['vendor_type'] = 4;
						if(isset($request->received_payment_customer_name) && $request->received_payment_customer_name!= ""){
							$data['vendor_type_id'] = $request->received_payment_customer_name;
						}
						if(isset($request->received_payment_customer_date) && $request->received_payment_customer_date!= ""){
							$data['date'] = date("Y-m-d", strtotime(convertDate($request->received_payment_customer_date)));
						}

						/*if(isset($advancedAmount) && $advancedAmount!= ""){
							$data['amount'] = $advancedAmount;
							$data['customer_advanced_payment'] = $advancedAmount;
						}*/

						$data['amount'] = $request->received_customer_payment_amount-$request->received_customer_total_amount;
						$data['customer_advanced_payment'] = $request->received_customer_payment_amount-$request->received_customer_total_amount;

						if(isset($request->received_payment_customer_mode) && $request->received_payment_customer_mode!= ""){
							$data['payment_mode'] = $request->received_payment_customer_mode;
						}
						if(isset($request->received_payment_customer_bank_id) && $request->received_payment_customer_bank_id!= ""){
							$data['bank_id'] = $request->received_payment_customer_bank_id;
						}
						if(isset($request->received_payment_customer_bank_account_number) && $request->received_payment_customer_bank_account_number!= ""){
							$data['account_no'] = $request->received_payment_customer_bank_account_number;
						}
						if(isset($request->received_payment_customer_paid_via) && $request->received_payment_customer_paid_via!= ""){
							$data['paid_via'] = $request->received_payment_customer_paid_via;
						}
						if(isset($request->received_payment_customer_cheque_no) && $request->received_payment_customer_cheque_no!= ""){
							$data['cheque_no'] = $request->received_payment_customer_cheque_no;
						}
						if(isset($request->received_payment_customer_utr) && $request->received_payment_customer_utr!= ""){
							$data['neft_utr_no'] = $request->received_payment_customer_utr;
						}
						if(isset($request->received_payment_customer_neft) && $request->received_payment_customer_neft!= ""){
							$data['neft_charge'] = $request->received_payment_customer_neft;
						}
						if(isset($request->received_payment_customer_branch_id) && $request->received_payment_customer_branch_id!= ""){
							$data['branch_id'] = $request->received_payment_customer_branch_id;
						}
						if(isset($request->received_payment_customer_description) && $request->received_payment_customer_description!= ""){
							$data['description'] = $request->received_payment_customer_description;
						}
						$res = BankingLedger::create($data);
						$bookingid = $res->id;

						/*if($advancedAmount){
							$request['advanced'] = $advancedAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->received_payment_customer_branch_id;
							$request['neft_charge'] = 1;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}*/

						$request['advanced'] = $advancedAmount;
						$request['refund'] = 0;
						$request['amount'] = $request->received_customer_payment_amount-$request->received_customer_total_amount;
						$request['customer_branch_id'] = $request->received_payment_customer_branch_id;
						$request['banking_ledger_id'] = 0;
						$request['neft_charge'] = 1;
						$this->HeadEntry($request->all(),$bookingid,$request->type);

						/*if(isset($request->received_payment_customer_neft) && $request->received_payment_customer_neft!= ""){
							$request['advanced'] = $advancedAmount;
							$request['refund'] = $refundAmount;
							$request['amount'] = $advancedAmount;
							$request['customer_branch_id'] = $request->payment_branch_id;
							$request['banking_ledger_id'] = 0;
							$request['neft_charge'] = 0;
							$this->HeadEntry($request->all(),$bookingid,$request->type);
						}*/
					//}

					$bankingLedgerResult=BankingLedger::where('vendor_type', 5)->where('vendor_type_id',$request->received_payment_customer_name)->whereNull('customer_advanced_payment')->where('customer_refund_payment','>',0)->where('advanced_payment_status',0)->get();

					foreach($bankingLedgerResult as $key => $val){
						$request['advanced'] = 0;
						$request['refund'] = $refundAmount;
						$request['amount'] = $request->received_cus_payment_amount[$val->id];
						$request['customer_branch_id'] = $request->received_payment_customer_branch_id;
						$request['banking_ledger_id'] = $val->id;
						$request['neft_charge'] = 1;
						$this->HeadEntry($request->all(),$val->id,$request->type);
					}
				}
			}elseif($request->type == 5){
				if(isset($request->income_head_id) && $request->income_head_id!= ""){
					$data['expense_account'] = $request->income_head_id;
				}
				if(isset($request->income_head_id1) && $request->income_head_id1!= ""){
					$data['expense_account1'] = $request->income_head_id1;
				}
				if(isset($request->income_head_id2) && $request->income_head_id2!= ""){
					$data['expense_account2'] = $request->income_head_id2;
				}
				if(isset($request->income_head_id3) && $request->income_head_id3!= ""){
					$data['expense_account3'] = $request->income_head_id3;
				}
				if(isset($request->indirect_income_date) && $request->indirect_income_date!= ""){
					$data['date'] = date("Y-m-d", strtotime(convertDate($request->indirect_income_date)));
				}
				if(isset($request->indirect_income_amount) && $request->indirect_income_amount!= ""){
					$data['amount'] = $request->indirect_income_amount;
				}
				if(isset($request->indirect_income_description) && $request->indirect_income_description!= ""){
					$data['description'] = $request->indirect_income_description;
				}
				if(isset($request->indirect_income_mode) && $request->indirect_income_mode!= ""){
					$data['payment_mode'] = $request->indirect_income_mode;
				}
				if(isset($request->indirect_income_bank_id) && $request->indirect_income_bank_id!= ""){
					$data['bank_id'] = $request->indirect_income_bank_id;
				}
				if(isset($request->indirect_income_account_no) && $request->indirect_income_account_no!= ""){
					$data['account_no'] = $request->indirect_income_account_no;
				}
				if(isset($request->indirect_income_paid_via) && $request->indirect_income_paid_via!= ""){
					$data['paid_via'] = $request->indirect_income_paid_via;
				}
				if(isset($request->indirect_income_cheque_no) && $request->indirect_income_cheque_no!= ""){
					$data['cheque_no'] = $request->indirect_income_cheque_no;
				}
				if(isset($request->indirect_income_utr) && $request->indirect_income_utr!= ""){
					$data['neft_utr_no'] = $request->indirect_income_utr;
				}
				if(isset($request->indirect_income_neft) && $request->indirect_income_neft!= ""){
					$data['neft_charge'] = $request->indirect_income_neft;
				}
				if(isset($request->indirect_income_branch_id) && $request->indirect_income_branch_id!= ""){
					$data['branch_id'] = $request->indirect_income_branch_id;
				}
				$res = BankingLedger::create($data);
				$bookingid = $res->id;
				$request['neft_charge'] = 0;
				$request['refund'] = 0;
				$this->HeadEntry($request->all(),$bookingid,$request->type);
			}

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        return redirect()->route('admin.banking.index')->with('success', 'Banking Ledger Updated Successfully!');

    }

    public function deleteAdvanced($id)

    {

        DB::beginTransaction();

        try {

            //Rent Ledger delete    
            $rentLeaser = RentLiabilityLedger::where('banking_id',$id)->delete(); 

            //Employee Ledger delete    
            $employeeLeaser = EmployeeLedger::where('banking_id',$id)->delete(); 

            // employee ledger delete
            $vendorbillPayment = VendorBillPayment::where('banking_id',$id)->delete();

            // customer ledger delete
            $customerTransaction = CustomerTransaction::where('banking_id',$id)->delete();

            // associate ledger delete
            $associateTransaction = AssociateTransaction::where('banking_id',$id)->delete();

            // advanced ledger delete
            $associateTransaction = AdvancedTransaction::where('banking_id',$id)->delete();

            //All head transaction
            $headData = AllHeadTransaction::whereIN('type',[26])->where('type_id',$id)->delete();   

            // Branch Day Book delete
            $branchDayBook = BranchDaybook::whereIN('type',[26])->where('type_id',$id)->delete();

            // Member Transaction delete
            $memberTransaction = MemberTransaction::whereIN('type',[26])->where('type_id',$id)->delete();
        
            // Samradhh Bank Day Book delete
            $samraddhBankDaybook = SamraddhBankDaybook::whereIN('type',[26])->where('type_id',$id)->delete();

            //Banking Ledger
            $bankingData = BankingLedger::where('id',$id)->delete();

            //Banking Ledger
            $bankingDueBillsData = BankingAdvancedLedger::where('banking_transaction_id',$id)->delete();

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        return back()->with('success', 'Transaction Deleted Successfully!');
    }

    public function delete($id)

    {

        DB::beginTransaction();

        try {

        	$bankingRecord = BankingLedger::where('id',$id)->first();

            //Rent Ledger delete    
            $rentLeaser = RentLiabilityLedger::where('banking_id',$id)->delete(); 

            //Employee Ledger delete    
            $employeeLeaser = EmployeeLedger::where('banking_id',$id)->delete(); 

            // employee ledger delete
            $vendorbillPayment = VendorBillPayment::where('banking_id',$id)->delete();

            // customer ledger delete
            $customerTransaction = CustomerTransaction::where('banking_id',$id)->delete();

            // associate ledger delete
            $associateTransaction = AssociateTransaction::where('banking_id',$id)->delete();

            // advanced ledger delete
            $associateTransaction = AdvancedTransaction::where('banking_id',$id)->delete();

            //All head transaction
            $headData = AllHeadTransaction::whereIN('type',[26])->where('type_id',$id)->delete();   

            // Branch Day Book delete
            $branchDayBook = BranchDaybook::whereIN('type',[26])->where('type_id',$id)->delete();

            // Member Transaction delete
            $memberTransaction = MemberTransaction::whereIN('type',[26])->where('type_id',$id)->delete();
        
            // Samradhh Bank Day Book delete
            $samraddhBankDaybook = SamraddhBankDaybook::whereIN('type',[26])->where('type_id',$id)->delete();

            if($bankingRecord->type == 2){

            	$relatedRecord = BankingDueBillsLedger::where('banking_id',$id)->get();

	            if(count($relatedRecord) > 0){
		            foreach($relatedRecord as $rec){
		            	if($rec->type == 0){
	            		$rentPaymentResult = RentPayment::where('id', $rec->type_id)->first();
	            		$rentPaymentUpdate = RentPayment::find($rentPaymentResult->id);
		                $rentPaymentData['transferred_amount'] = ($rentPaymentResult->transferred_amount-$rec->pay_amount);
		                $rentPaymentData['status'] = 0;
		                $rentPaymentUpdate->update($rentPaymentData);
		            }elseif($rec->type == 1){
	            		$employeePaymentResult = EmployeeSalary::where('id', $rec->type_id)->first();
	            		$employeePaymentUpdate = EmployeeSalary::find($employeePaymentResult->id);
		                $employeePaymentData['transferred_salary'] = ($employeePaymentResult->transferred_salary-$rec->pay_amount);
		                $employeePaymentData['status'] = 0;
		                $employeePaymentUpdate->update($employeePaymentData);
		            }elseif($rec->type == 2){
	            		$commissionPaymentResult = CommissionLeaserDetail::where('id', $rec->type_id)->first();
	            		$commissionPaymentUpdate = commissionPaymentResult::find($commissionPaymentResult->id);
		                $commissionPaymentData['transferred_amount'] = ($commissionPaymentResult->transferred_amount-$rec->pay_amount);
		                $commissionPaymentData['transferred_fuel_amount'] = ($commissionPaymentResult->transferred_fuel_amount-$rec->pay_amount);
		                $commissionPaymentData['status'] = 2;
		                $commissionPaymentUpdate->update($commissionPaymentData);
		            }elseif($rec->type == 3){
	            		$vendorPaymentResult = VendorBill::where('id', $rec->type_id)->first();
	            		$vendorPaymentUpdate = VendorBill::find($vendorPaymentResult->id);
		                $vendorPaymentData['transferd_amount'] = ($vendorPaymentResult->transferred_amount-$rec->pay_amount);
		                $vendorPaymentData['balance'] = ($vendorPaymentResult->balance+$rec->pay_amount);
		                $vendorPaymentData['status'] = 0;
		                $vendorPaymentUpdate->update($vendorPaymentData);
		            }elseif($rec->type == 6){
	            		$cCardPaymentResult = CreditCradTransaction::where('id', $rec->type_id)->first();
	            		$cCardPaymentUpdate = CreditCradTransaction::find($cCardPaymentResult->id);
		                $cCardPaymentData['transferd_amount'] = ($cCardPaymentResult->used_amount-$rec->pay_amount);
		                $cCardPaymentData['status'] = 0;
		                $cCardPaymentUpdate->update($cCardPaymentData);
		            }
		            }
	            	//Banking Ledger
            		$bankingDueBillsData = BankingDueBillsLedger::where('banking_id',$id)->delete();
	            }

            }elseif($bankingRecord->type == 1){

            	$relatedAdvancedRecord = BankingAdvancedLedger::where('banking_id',$id)->get();

            	if($relatedAdvancedRecord){
            		foreach ($relatedAdvancedRecord as $key => $value) {
            			if($value->type == 0){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 1){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 2){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 3){
            				$advancedAmountResult = BankingLedger::where('id', $value->banking_transaction_id)->first();
            				$amountUpdate = BankingLedger::find($value->banking_transaction_id);
            				$advancedAmountData['advanced_amount'] = ($advancedAmountResult->advanced_amount+$value->pay_amount);
		                	$advancedAmountData['advanced_payment_status'] = 0;
		                	$amountUpdate->update($advancedAmountData);
            			}elseif($value->type == 4){
            				
            			}
            		}
            		//Banking Ledger
            		$bankingDueBillsData = BankingAdvancedLedger::where('banking_id',$id)->delete();
            	}
            }

            //Banking Ledger
            $bankingData = BankingLedger::where('id',$id)->delete();

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        return back()->with('success', 'Transaction Deleted Successfully!');
    }


	 public function HeadEntry($request,$bookingId,$bookingtype)
	{
		// dd($bookingId,$bookingtype);

		$entryTime = date("H:i:s");
		if($bookingtype == 1){
			if($request['expense_account3']){
				$headId = $request['expense_account3'];
			}elseif($request['expense_account2']){
				$headId = $request['expense_account2'];
			}elseif($request['expense_account1']){
				$headId = $request['expense_account1'];
			}elseif($request['expense_account']){
				$headId = $request['expense_account'];
			}

			$payemntMode = $request['expense_mode'];
			$date = $request['expense_date'];

			Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));

			if($request['expense_mode'] == 2){

				$type = 26;
				$sub_type = 261;
				$jv_unique_id = NULL;
				$branch_id = $request['expense_branch_id'];
				$description = 'Booking Expense '.$request['expense_amount'];
				$description_cr = 'Dr '.getAcountHead($headId).' '.$request['expense_amount'].'';
	            $description_dr = 'Cr '.getBranchDetail($request['expense_branch_id'])->name.' '.$request['expense_amount'];
		        $type_id = $bookingId;
		        $type_transaction_id = NULL;
		        $associate_id = NULL;
		        $member_id = NULL;
		        $branch_id_to = NULL;
		        $branch_id_from = $request['expense_branch_id'];
		        $opening_balance = $request['expense_amount'];
		        $amount = $request['expense_amount'];
		        $closing_balance = $request['expense_amount'];
		        $payment_type = 'CR';
		        $head_payment_type = 'DR';
		        $payment_mode = 0;
		        $day_book_payment_mode = 0;
		        $currency_code = 'INR';
		        $amount_to_id =$headId;
		        $amount_to_name = getAcountHead($headId);
		        $amount_from_id =  $request['expense_branch_id'];
		        $amount_from_name = getBranchDetail($request['expense_branch_id'])->name;
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
		        $transction_date = date("Y-m-d", strtotime(convertDate($request['expense_date'])));
		        $entry_date = date("Y-m-d", strtotime(convertDate($request['expense_date'])));
		        $entry_time = date("H:i:s");
		        $created_by = 1;
		        $created_by_id = Auth::user()->id;
		        $is_contra = NULL;
		        $contra_id = NULL;
		        $created_at = date("Y-m-d", strtotime(convertDate($request['expense_date'])));
		        $bank_id = NULL;
		        $bank_ac_id = NULL;
		        $transction_bank_to_name = NULL;
		        $transction_bank_to_ac_no = NULL;
		        $transction_bank_to_branch = NULL;
		        $transction_bank_to_ifsc = NULL;
		        $neftcharge = 0;	   
		        $ssb_account_tran_id_to= NULL;
		        $ssb_account_tran_id_from=NULL;
	        }elseif($request['expense_mode'] == 1){
	            $branch_id = $request['expense_branch_id'];
	            $type = 26;
				$sub_type = 261;
	            $chequeType = 1;
	            $type_id = $bookingId;
	            $type_transaction_id = NULL;
	            $associate_id = NULL;
	            $member_id = NULL;
	            $branch_id_to = $request['expense_branch_id'];
	            $branch_id_from = NULL;
	            $jv_unique_id = NULL;
	            $opening_balance = $request['expense_amount'];
	            $amount = $request['expense_amount'];
	            $closing_balance = $request['expense_amount'];
	            
	            $description = 'Booking Expense '.$request['expense_amount'];
				$description_cr = 'Dr '.getAcountHead($headId).' '.$request['expense_amount'].'';
	            $description_dr = 'Cr '.getSamraddhBankAccountId($request['expense_account_no'])->account_no.' '.$request['expense_amount'];

	            $payment_type = 'CR';
	            $head_payment_type = 'DR';
	            $bank_charge_payment_type = 'DR';
	            $currency_code = 'INR';
	            $amount_to_id = $request['expense_bank_id'];
	            $amount_to_name = getSamraddhBank($request['expense_bank_id'])->bank_name;
	            $amount_from_id = $headId;
		        $amount_from_name = getAcountHead($headId);
	            $v_no = NULL;
	            $v_date = NULL;
	            $ssb_account_id_from = NULL;
	            $ssb_account_id_to = NULL;

	            if($request['expense_paid_via'] == 1){


	            	$getChequeRecord=SamraddhCheque::where('id',$request['expense_cheque_no'])->first();
	                $cheque_type = 1;
	                $cheque_id = $request['expense_cheque_no'];
	                $cheque_no = $getChequeRecord->cheque_no;
	                $cheque_date = $getChequeRecord->cheque_create_date;
	                $cheque_bank_from = $request['expense_bank_id'];
	                $cheque_bank_from_id = $request['expense_bank_id'];
	                $cheque_bank_ac_from = getSamraddhBankAccountId($request['expense_account_no'])->id;
	                $cheque_bank_ifsc_from = getSamraddhBankAccountId($request['expense_account_no'])->ifsc_code;
	                $cheque_bank_ac_from_id = getSamraddhBankAccountId($request['expense_account_no'])->id;
	                $cheque_bank_branch_from = NULL;
	                $cheque_bank_to = NULL;
	                $cheque_bank_ac_to = NULL;
	                $cheque_bank_to_name = NULL;
	                $cheque_bank_to_branch = NULL;
	                $cheque_bank_to_ac_no = NULL;
	                $cheque_bank_to_ifsc = NULL;
	                $transction_no = NULL;
	                $transction_bank_from = getSamraddhBank($request['expense_bank_id'])->bank_name;
	                $transction_bank_from_id = getSamraddhBank($request['expense_bank_id'])->id;
	                $transction_bank_ac_from = $request['expense_account_no'];
	                $transction_bank_ifsc_from = getSamraddhBankAccountId($request['expense_account_no'])->ifsc_code;
	                $transction_bank_from_ac_id = getSamraddhBankAccountId($request['expense_account_no'])->id;
	                $transction_bank_branch_from = NULL;
	                $transction_bank_to = NULL;
	                $transction_bank_ac_to = NULL;
	                $payment_mode = 1;
	                $day_book_payment_mode = 1;
	                $transction_bank_to_name = NULL;
	                $transction_bank_to_ac_no = NULL;
	                $transction_bank_to_branch = NULL;
	                $transction_bank_to_ifsc = NULL;
	                $neftcharge = 0;
	                SamraddhCheque::where('id',$request['expense_cheque_no'])->update(['status' => 3,'is_use'=>1]);
	                SamraddhChequeIssue::create([
	                    'cheque_id' => $request['expense_cheque_no'],
	                    'type' =>6,
	                    'sub_type' =>9,
	                    'type_id' =>$bookingId,
	                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['expense_date']))),
	                    'status' => 1,
	                ]);

	            }elseif($request['expense_paid_via'] == 2){
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
	                $transction_no = NULL;
	                $transction_bank_from = getSamraddhBank($request['expense_bank_id'])->bank_name;
	                $transction_bank_from_id = getSamraddhBank($request['expense_bank_id'])->id;
	                $transction_bank_ac_from = $request['expense_account_no'];
	                $transction_bank_ifsc_from = getSamraddhBankAccountId($request['expense_account_no'])->ifsc_code;
	                $transction_bank_from_ac_id = getSamraddhBankAccountId($request['expense_account_no'])->id;
	                $transction_bank_branch_from = NULL;
	                $transction_bank_to = NULL;
	                $transction_bank_ac_to = NULL;
	                $transction_bank_to_name = NULL;
	                $transction_bank_to_ac_no = NULL;
	                $transction_bank_to_branch = NULL;
	                $transction_bank_to_ifsc = NULL;
	                $payment_mode = 2;
	                $day_book_payment_mode = 3;
	                $neftcharge = $request['expense_neft'];
	            }
	        	$transction_date = date("Y-m-d", strtotime(convertDate($request['expense_date'])));
	            $entry_date = date("Y-m-d", strtotime(convertDate($request['expense_date'])));
	            $entry_time = date("H:i:s");
	            $created_by = 1;
	            $created_by_id = Auth::user()->id;
	            $is_contra = NULL;
	            $contra_id = NULL;
	            $created_at = date("Y-m-d", strtotime(convertDate($request['expense_date'])));
	            $bank_id = NULL;
	            $bank_id = $request['expense_bank_id'];
	            $bank_ac_id = getSamraddhBankAccountId($request['expense_bank_id'])->id;
	            $ssb_account_tran_id_to= NULL;
	            $ssb_account_tran_id_from=NULL;
	        }
		}elseif($bookingtype == 2){

			if($request['payment_account_payment'] == 1){

				if($request['payemnt_vendor_type'] == 0){
					$headId = 60;
				}elseif($request['payemnt_vendor_type'] == 1){
					$headId = 61;
				}elseif($request['payemnt_vendor_type'] == 2){
					$headId = 141;
				}elseif($request['payemnt_vendor_type'] == 3){
					$headId = 140;
				}

				$payemntMode = $request['vendor_payment_mode'];
				$date = $request['payment_vendor_date'];
				Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));

				if($request['vendor_payment_mode'] == 2){
					$type = 26;
					$sub_type = 262;
					$jv_unique_id = NULL;
					$branch_id = $request['payment_vendor_branch_id'];

					if($request['payemnt_vendor_type'] == 0){
						$rentResult=RentLiability::select('id','owner_name')->where('id',$request['payment_vendor_name'])->first();
						
						$description_cr = 'Dr '.$rentResult->owner_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $rentResult->owner_name;
				        $amount_from_id = $request['payment_vendor_branch_id'];
				        $amount_from_name = getBranchDetail($request['payment_vendor_branch_id'])->name;
				        $payment_type = 'CR';
			        	$head_payment_type = 'DR';
			        	$opening_balance = $request['amount'];
				        $amount = $request['amount'];
				        $closing_balance = $request['amount'];
					}elseif($request['payemnt_vendor_type'] == 1){
						$employeeResult=Employee::select('id','employee_name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$employeeResult->employee_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $employeeResult->employee_name;
				        $amount_from_id = $request['payment_vendor_branch_id'];
				        $amount_from_name = getBranchDetail($request['payment_vendor_branch_id'])->name;
				        $payment_type = 'CR';
			        	$head_payment_type = 'DR';
			        	$opening_balance = $request['amount'];
				        $amount = $request['amount'];
				        $closing_balance = $request['amount'];
					}elseif($request['payemnt_vendor_type'] == 2){
						$associateResult=Member::select('id','first_name','last_name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$associateResult->first_name.' '.$associateResult->last_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $associateResult->first_name.' '.$associateResult->last_name;
				        $amount_from_id = $request['payment_vendor_branch_id'];
				        $amount_from_name = getBranchDetail($request['payment_vendor_branch_id'])->name;
				        $payment_type = 'CR';
			        	$head_payment_type = 'DR';
			        	$opening_balance = $request['amount']+$request['fuelamount'];
				        $amount = $request['amount']+$request['fuelamount'];
				        $closing_balance = $request['amount']+$request['fuelamount'];
					}elseif($request['payemnt_vendor_type'] == 3){
						$vendorResult=Vendor::select('id','name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$vendorResult->name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $vendorResult->name;
				        $amount_from_id = $request['payment_vendor_branch_id'];
				        $amount_from_name = getBranchDetail($request['payment_vendor_branch_id'])->name;
				        $payment_type = 'CR';
			        	$head_payment_type = 'DR';
			        	$opening_balance = $request['amount'];
				        $amount = $request['amount'];
				        $closing_balance = $request['amount'];
					}
						
					$description = $request['payment_vendor_description'];	
			        $type_id = $bookingId;
			        	
			        $associate_id = NULL;
			        $member_id = NULL;
			        $branch_id_to = NULL;
			        $branch_id_from = $request['payment_vendor_branch_id'];
			        $payment_mode = 0;
			        $day_book_payment_mode = 0;
			        $currency_code = 'INR';
			        
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
			        $transction_date = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
			        $entry_date = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
			        $entry_time = date("H:i:s");
			        $created_by = 1;
			        $created_by_id = Auth::user()->id;
			        $is_contra = NULL;
			        $contra_id = NULL;
			        $created_at = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
			        $bank_id = NULL;
			        $bank_ac_id = NULL;
			        $transction_bank_to_name = NULL;
			        $transction_bank_to_ac_no = NULL;
			        $transction_bank_to_branch = NULL;
			        $transction_bank_to_ifsc = NULL;
			        $neftcharge = 0;	   
			        $ssb_account_tran_id_to= NULL;
			        $ssb_account_tran_id_from=NULL;

			        $from_bank_name=NULL;
			        $from_bank_id=NULL;
			        $from_bank_ac_no=NULL;
			        $from_bank_ifsc=NULL;
			        $from_bank_ac_id=NULL;
			        $to_bank_ifsc = NULL;
			        $account_id=NULL;
		        }elseif($request['vendor_payment_mode'] == 1){

		            $branch_id = $request['payment_vendor_branch_id'];
		            $type = 26;
					$sub_type = 262;
		            $chequeType = 1;
		            $type_id = $bookingId;
		            $associate_id = NULL;
		            $member_id = NULL;
		            $branch_id_to = $request['payment_vendor_branch_id'];
		            $branch_id_from = NULL;
		            $jv_unique_id = NULL;

		            if($request['payemnt_vendor_type'] == 0){
						$rentResult=RentLiability::select('id','owner_name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$rentResult->owner_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $rentResult->owner_name;
				        $amount_from_id = $request['payment_vendor_bank_id'];
				        $amount_from_name = getSamraddhBank($request['payment_vendor_bank_id'])->bank_name;
				        $payment_type = 'CR';
			            $head_payment_type = 'DR';
		            	$bank_charge_payment_type = 'DR';
		            	$opening_balance = $request['amount'];
		            	$amount = $request['amount'];
		            	$closing_balance = $request['amount'];
					}elseif($request['payemnt_vendor_type'] == 1){
						$employeeResult=Employee::select('id','employee_name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$employeeResult->employee_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $employeeResult->employee_name;
				        $amount_from_id = $request['payment_vendor_bank_id'];
				        $amount_from_name = getSamraddhBank($request['payment_vendor_bank_id'])->bank_name;
				        $payment_type = 'CR';
			            $head_payment_type = 'DR';
		            	$bank_charge_payment_type = 'DR';
		            	$opening_balance = $request['amount'];
		            	$amount = $request['amount'];
		            	$closing_balance = $request['amount'];
					}elseif($request['payemnt_vendor_type'] == 2){
						$associateResult=Member::select('id','first_name','last_name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$associateResult->first_name.' '.$associateResult->last_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $associateResult->first_name.' '.$associateResult->last_name;
				        $amount_from_id = $request['payment_vendor_branch_id'];
				        $amount_from_name = getBranchDetail($request['payment_vendor_branch_id'])->name;
				        $payment_type = 'CR';
			        	$head_payment_type = 'DR';
			        	$bank_charge_payment_type = 'DR';
			        	$opening_balance = $request['amount']+$request['fuelamount'];
		            	$amount = $request['amount']+$request['fuelamount'];
		            	$closing_balance = $request['amount']+$request['fuelamount'];
					}elseif($request['payemnt_vendor_type'] == 3){
						$vendorResult=Vendor::select('id','name')->where('id',$request['payment_vendor_name'])->first();
						$description_cr = 'Dr '.$vendorResult->name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['payment_vendor_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['payment_vendor_name'];
				        $amount_to_name = $vendorResult->name;
				        $amount_from_id = $request['payment_vendor_branch_id'];
				        $amount_from_name = getBranchDetail($request['payment_vendor_branch_id'])->name;
				        $payment_type = 'CR';
			        	$head_payment_type = 'DR';
			        	$bank_charge_payment_type = 'DR';
			        	$opening_balance = $request['amount'];
		            	$amount = $request['amount'];
		            	$closing_balance = $request['amount'];
					}

		           	$description = $request['payment_vendor_description'];	
		            $currency_code = 'INR';
		            $amount_to_id = $headId;
			        $amount_to_name = getAcountHead($headId);
		            $amount_from_id = $headId;
			        $amount_from_name = getSamraddhBank($request['payment_vendor_bank_id'])->bank_name;

		            $v_no = NULL;
		            $v_date = NULL;
		            $ssb_account_id_from = NULL;
		            $ssb_account_id_to = NULL;

		            if($request['payment_vendor_paid_via'] == 1){
		            	$getChequeRecord=SamraddhCheque::where('id',$request['payment_vendor_cheque_no'])->first();
		                $cheque_type = 1;
		                $cheque_id = $request['payment_vendor_cheque_no'];
		                $cheque_no = $getChequeRecord->cheque_no;
		                $cheque_date = $getChequeRecord->cheque_create_date;
		                $cheque_bank_from = $request['payment_vendor_bank_id'];
		                $cheque_bank_from_id = $request['payment_vendor_bank_id'];
		                $cheque_bank_ac_from = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->id;
		                $cheque_bank_ifsc_from = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->ifsc_code;
		                $cheque_bank_ac_from_id = $account_id = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->id;
		                $cheque_bank_branch_from = NULL;
		                $cheque_bank_to = NULL;
		                $cheque_bank_ac_to = NULL;
		                $cheque_bank_to_name = NULL;
		                $cheque_bank_to_branch = NULL;
		                $cheque_bank_to_ac_no = NULL;
		                $cheque_bank_to_ifsc = NULL;
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = getSamraddhBank($request['payment_vendor_bank_id'])->bank_name;
		                $transction_bank_from_id = $from_bank_id = getSamraddhBank($request['payment_vendor_bank_id'])->id;
		                $transction_bank_ac_from = $from_bank_ac_no = $request['payment_vendor_bank_account_number'];
		                $transction_bank_ifsc_from = $from_bank_ifsc = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->ifsc_code;
		                $transction_bank_from_ac_id = $from_bank_ac_id = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->id;
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = NULL;
		                $transction_bank_ac_to = NULL;
		                $payment_mode = 1;
		                $day_book_payment_mode = 1;
		                $transction_bank_to_name = NULL;
		                $transction_bank_to_ac_no = NULL;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = NULL;
		                $neftcharge = 0;
		                SamraddhCheque::where('id',$request['payment_vendor_cheque_no'])->update(['status' => 3,'is_use'=>1]);
		                SamraddhChequeIssue::create([
		                    'cheque_id' => $request['payment_vendor_cheque_no'],
		                    'type' =>6,
		                    'sub_type' =>9,
		                    'type_id' =>$bookingId,
		                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),
		                    'status' => 1,
		                ]);
		            }elseif($request['payment_vendor_paid_via'] == 2){
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
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = getSamraddhBank($request['payment_vendor_bank_id'])->bank_name;
		                $transction_bank_from_id = $account_id = $from_bank_id = getSamraddhBank($request['payment_vendor_bank_id'])->id;
		                $transction_bank_ac_from = $from_bank_ac_no = $request['payment_vendor_bank_account_number'];
		                $transction_bank_ifsc_from = $from_bank_ifsc = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->ifsc_code;
		                $transction_bank_from_ac_id = $from_bank_ac_id = getSamraddhBankAccountId($request['payment_vendor_bank_account_number'])->id;
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = NULL;
		                $transction_bank_ac_to = NULL;
		                $transction_bank_to_name = NULL;
		                $transction_bank_to_ac_no = NULL;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = NULL;
		                $payment_mode = 2;
		                $day_book_payment_mode = 3;
		                $neftcharge = $request['vendor_neft'];
		            }
		        	$transction_date = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
		            $entry_date = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
		            $entry_time = date("H:i:s");
		            $created_by = 1;
		            $created_by_id = Auth::user()->id;
		            $is_contra = NULL;
		            $contra_id = NULL;
		            $created_at = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
		            $bank_id = NULL;
		            $bank_id = $request['payment_vendor_bank_id'];
		            $bank_ac_id = getSamraddhBankAccountId($request['payment_vendor_bank_id'])->id;
		            $ssb_account_tran_id_to= NULL;
		            $ssb_account_tran_id_from=NULL;
		            $to_bank_ifsc = NULL;
		        }

		        if($request['payemnt_vendor_type'] == 0 && $request['neft_charge'] == 1){

                    //$advancedRent = $request['vendor_payment_amount']-$request['vendor_total_amount'];
                    $advancedRent = $request['advanced'];
                    
                    if($advancedRent){
                    	$rentLiabilityRecord = RentLiability::where('id',$request['payment_vendor_name'])->first();
	                    $rentLiability = RentLiability::find($request['payment_vendor_name']);
	                    $rentLiabilityData['advance_payment'] = ($rentLiabilityRecord->advance_payment+$advancedRent);
	                    $rentLiability->update($rentLiabilityData); 
	                }else{
	                    $rentPaymentRecord = RentPayment::where('id',$request['rent_liability_id'])->first();	
			        	$rentPaymentResult = RentPayment::find($rentPaymentRecord->id);
	                    $rentPaymentData['transferred_date'] = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
	                    $rentPaymentData['transferred_amount'] = ($rentPaymentRecord->transferred_amount+$request['amount']);
	                    $rentPaymentData['transfer_mode'] = $payment_mode;
	                    if($rentPaymentRecord->rent_amount == ($rentPaymentRecord->transferred_amount+$request['amount'])){
	                    	$rentPaymentData['status'] = 1;
	                    }
	                    $rentPaymentResult->update($rentPaymentData);

	                    $rentLedgerRecord = RentLedger::where('id',$rentPaymentRecord->ledger_id)->first();
	                    $rentLedgerResult = RentLedger::find($rentPaymentRecord->ledger_id);
	                    $rentLedgerPaymentData['transfer_amount'] = ($rentLedgerRecord->transfer_amount+$request['amount']);
	                    if($rentLedgerRecord->total_amount == ($rentLedgerRecord->transfer_amount+$request['amount'])){
	                    	$rentLedgerPaymentData['status'] = 1;
	                    }
	                    $rentLedgerResult->update($rentLedgerPaymentData);
	                }

                    if($request['advanced'] > 0){
                    	$ledgerDescription = 'Advanced Rent Payment';
                    	$rent_type_id = NULL;
                    	$pType = 'DR';
                    }else{
                    	$ledgerDescription = $description;
                    	$rent_type_id = $request['rent_liability_id'];
                    	$pType = 'DR';
                    }

                    $rentLedger = $this->RentLiabilityLedger($request['payment_vendor_name'],1,$rent_type_id,NULL,NULL,$request['amount'],$ledgerDescription,$currency_code,$pType,$payment_mode,1,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$updated_at=NULL,$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,$jv_type_id=NULL,$bookingId);

                    $vendorBillPayment = $this->createVendorBillPayemnt($branch_id,$request['payment_vendor_name'],$vendor_bill_id=NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_vendor_name'],$rentLedger,$rentResult->id,NULL,NULL,$request['amount'],$ledgerDescription,$currency_code,$pType,$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $rentLedger;

                    if($request['advanced'] > 0){

                    	$dayBookRef = CommanController::createBranchDayBookReference($amount);

                    	$this->createAdvancedTransaction(3,NULL,$request['payment_vendor_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Adavanced Rent','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );
					
				        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,74,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

				        $vendorBillPayment = $this->createVendorBillPayemnt($branch_id,$vendor_id=NULL,$vendor_bill_id=NULL,4,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_vendor_name'],$rentLedger,$rentResult->id,NULL,$request['advanced'],NULL,'Rent Advanced',$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);
					}/*else{
						$dayRentTypeBookRef = CommanController::createBranchDayBookReference($amount);
				    	$allHeadTransaction = CommanController::createAllHeadTransaction($dayRentTypeBookRef,$branch_id,$bank_id,$bank_ac_id,$rentLiabilityRecord->rent_type,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					}*/
				}elseif($request['payemnt_vendor_type'] == 1 && $request['neft_charge'] == 1){


                    $advancedSalary = $request['vendor_payment_amount']-$request['vendor_total_amount'];
                    if($request['advanced'] > 0){
					
                    	$employeeRecord = Employee::where('id',$request['payment_vendor_name'])->first();
	                    $employee = Employee::find($request['payment_vendor_name']);
	                    $employeeData['advance_payment'] = ($employeeRecord->advance_payment+$advancedSalary);
	                    $employee->update($employeeData);
  
	                }else{

	                	$employeePaymentRecord = EmployeeSalary::where('employee_id',$request['employee_salary_id'])->first();

			        	$salaryPaymentResult = EmployeeSalary::find($employeePaymentRecord->id);
	                    $salaryPaymentData['transferred_date'] = date("Y-m-d", strtotime(convertDate($request['payment_vendor_date'])));
	                    $salaryPaymentData['transferred_salary'] = ($employeePaymentRecord->transferred_salary+$request['amount']);
	                    $salaryPaymentData['transferred_in'] = $payment_mode;

	                    if($employeePaymentRecord->total_salary == ($employeePaymentRecord->transferred_salary+$request['amount'])){
	                    	$salaryPaymentData['status'] = 1;
	                    }
	                    $salaryPaymentResult->update($salaryPaymentData);

	                    $employeeSalaryLedgerRecord = EmployeeSalaryLeaser::where('id',$employeePaymentRecord->leaser_id)->first();
	                    $employeeLedgerResult = EmployeeSalaryLeaser::find($employeePaymentRecord->leaser_id);

	                    $employeeLedgerPaymentData['transfer_amount'] = ($employeeSalaryLedgerRecord->transfer_amount+$request['amount']);
	                    if($employeeSalaryLedgerRecord->total_amount == ($employeeSalaryLedgerRecord->transfer_amount+$request['amount'])){
	                    	$employeeLedgerPaymentData['status'] = 1;
	                    }
	                    $employeeLedgerResult->update($employeeLedgerPaymentData);
	                }

                    if($request['advanced'] > 0){
                    	$ledgerDescription = 'Advanced Salary Payment';
                    	$salary_type_id = NULL;
                    	$pType = 'DR';
                    }else{
                    	$ledgerDescription = $description;
                    	$salary_type_id = $request['employee_salary_id'];
                    	$pType = 'DR';
                    }

                    $employeeLedger = $this->createEmployeeLedgers($request['payment_vendor_name'],$branch_id,1,$salary_type_id,NULL,NULL,$request['amount'],$ledgerDescription,$currency_code,$pType,$payment_mode,1,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,$jv_type_id=NULL,$bookingId);

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,2,$salary_ledger_id=NULL,$salary_id=NULL,$request['payment_vendor_name'],NULL,$employeeLedger,$employeeResult->id,NULL,NULL,$request['amount'],$ledgerDescription,$currency_code,$pType,$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $employeeLedger;

                    if($request['advanced'] > 0){

                    	$dayBookRef = CommanController::createBranchDayBookReference($amount);

                    	$this->createAdvancedTransaction(4,NULL,$request['payment_vendor_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Advanced Salary','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );
					
				        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,73,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

				        $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,5,$salary_ledger_id=NULL,$salary_id=NULL,$request['payment_vendor_name'],NULL,$employeeLedger,$employeeResult->id,NULL,$request['advanced'],NULL,'Advanced Salary',$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);
					}
				}elseif($request['payemnt_vendor_type'] == 2 && $request['neft_charge'] == 1){

					$associatePaymentRecord = CommissionLeaserDetail::where('id',$request['associate_ledger_id'])->first();

                    if($request['advanced'] == 0){

			        	$associatePaymentResult = CommissionLeaserDetail::find($associatePaymentRecord->id);
	                    $associatePaymentData['transferred_amount'] = ($associatePaymentRecord->transferred_amount+$request['amount']);
	                    $associatePaymentData['transferred_fuel_amount'] = ($associatePaymentRecord->transferred_fuel_amount+$request['fuelamount']);
	                    if($associatePaymentRecord->amount == ($associatePaymentRecord->transferred_amount+$request['amount']) && $associatePaymentRecord->fuel == ($associatePaymentRecord->transferred_fuel_amount+$request['fuelamount'])){
	                    	$associatePaymentData['status'] = 2;
	                    }
	                    $associatePaymentData['status'] = 1;
	                    $associatePaymentResult->update($associatePaymentData);

	                    if($request['amount'] > 0){
                    		$this->createAssociateTransaction(1,NULL,$associatePaymentRecord->member_id,$request['associate_ledger_id'],$associatePaymentRecord->member_id,$branch_id,$bank_id,$account_id,$request['amount'],$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$transction_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );
                    	}elseif($request['fuelamount'] > 0){
                    		$this->createAssociateTransaction(2,NULL,$associatePaymentRecord->member_id,$request['associate_ledger_id'],$associatePaymentRecord->member_id,$branch_id,$bank_id,$account_id,$request['fuelamount'],$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$transction_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );
                    	}

                    }

                    if($request['advanced'] > 0){
                    	$ledgerDescription = 'Advanced Payment';
                    }else{
                    	$ledgerDescription = $description;
                    }

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,3,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,NULL,NULL,$request['payment_vendor_name'],NULL,NULL,$request['amount'],$ledgerDescription,$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $vendorBill;

                    if($request['advanced'] > 0){

                    	$dayBookRef = CommanController::createBranchDayBookReference($amount);

                    	$this->createAssociateTransaction(6,NULL,$associatePaymentRecord->member_id,NULL,$associatePaymentRecord->member_id,$branch_id,$bank_id,$account_id,$amount,'Associate Advanced','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$transction_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

                    	$this->createAdvancedTransaction(5,NULL,$associatePaymentRecord->member_id,$request['associate_ledger_id'],$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Associate Advanced','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

				        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,186,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

				        $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,6,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,NULL,NULL,$request['payment_vendor_name'],NULL,NULL,$request['advanced'],'Associate Advanced',$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);
					}
				}elseif($request['payemnt_vendor_type'] == 3 && $request['neft_charge'] == 1){

                    if($request['advanced'] == 0){

                    	$vendorPaymentRecord = VendorBill::where('id',$request['vendor_ledger_id'])->first();
			        	$vendorPaymentResult = VendorBill::find($vendorPaymentRecord->id);
	                    $vendorPaymentData['transferd_amount'] = ($vendorPaymentRecord->transferd_amount+$request['amount']);
	                    $vendorPaymentData['balance'] = ($vendorPaymentRecord->balance-$request['amount']);
	                    if($vendorPaymentRecord->payble_amount == ($vendorPaymentRecord->transferd_amount+$request['amount'])){
	                    	$vendorPaymentData['status'] = 2;
	                    }
	                    $vendorPaymentData['status'] = 1;
	                    $vendorPaymentResult->update($vendorPaymentData);
                    }

                    if($request['advanced'] > 0){
                    	$ledgerDescription = 'Advanced Payment';
                    }else{
                    	$ledgerDescription = $description;
                    }

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,$request['payment_vendor_name'],$vendor_bill_id=NULL,0,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_vendor_name'],NULL,NULL,NULL,NULL,$request['amount'],$ledgerDescription,$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $vendorBill;

                    if($request['advanced'] > 0){

                    	$dayBookRef = CommanController::createBranchDayBookReference($amount);

                    	$this->createAdvancedTransaction(1,NULL,$request['payment_vendor_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Vendor Advanced','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

                    	$this->createVendorTransaction(3,31,$request['payment_vendor_name'],$type_transaction_id,$request['payment_vendor_name'],$branch_id,$bank_id,$account_id,$amount,'Vendor Advanced','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$credit_node_id=NULL,$advance_id=NULL,$bookingId);
					
				        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,185,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

				        $vendorBill = $this->createVendorBillPayemnt($branch_id,$request['payment_vendor_name'],$vendor_bill_id=NULL,7,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,NULL,NULL,NULL,NULL,$request['advanced'],NULL,'Vendor Advanced',$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);
					}
				}
			}elseif($request['payment_account_payment'] == 2){
				$headId = 142;
				$payemntMode = $request['customer_payment_mode'];
				$date = $request['payment_customer_date'];
				Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));
				$customerResult=Vendor::select('id','name')->where('id',$request['payment_customer_name'])->first();
				if($request['customer_payment_mode'] == 2){
					$type = 26;
					if($request['advanced'] > 0){
						$sub_type = 265;
					}else{
						$sub_type = 266;
					}
					$jv_unique_id = NULL;
					$branch_id = $request['customer_branch_id'];
					
					$description_cr = 'Dr '.getBranchDetail($request['customer_branch_id'])->name.' '.$request['amount'].'';
		            $description_dr = 'Cr '.$customerResult->name.' '.$request['amount'];

		            $amount_to_id = $request['payment_customer_name'];
			        $amount_to_name = $customerResult->name;
			        $amount_from_id = $request['customer_branch_id'];
			        $amount_from_name = getBranchDetail($request['customer_branch_id'])->name;
					
					$description = $request['payment_customer_description'];	
			        $type_id = $bookingId;
			        
			        $associate_id = NULL;
			        $member_id = NULL;
			        $branch_id_to = $request['customer_branch_id'];
			        $branch_id_from = NULL;
			        $opening_balance = $request['amount'];
			        $amount = $request['amount'];
			        $closing_balance = $request['amount'];

			        $payment_type = 'CR';
		            $head_payment_type = 'DR';
	            	$bank_charge_payment_type = 'DR';

			        $payment_mode = 0;
			        $day_book_payment_mode = 0;
			        $currency_code = 'INR';
			        
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
			        $transction_date = date("Y-m-d", strtotime(convertDate($request['payment_customer_date'])));
			        $entry_date = date("Y-m-d", strtotime(convertDate($request['payment_customer_date'])));
			        $entry_time = date("H:i:s");
			        $created_by = 1;
			        $created_by_id = Auth::user()->id;
			        $is_contra = NULL;
			        $contra_id = NULL;
			        $created_at = date("Y-m-d", strtotime(convertDate($request['payment_customer_date'])));
			        $bank_id = NULL;
			        $bank_ac_id = NULL;
			        $transction_bank_to_name = NULL;
			        $transction_bank_to_ac_no = NULL;
			        $transction_bank_to_branch = NULL;
			        $transction_bank_to_ifsc = NULL;
			        $neftcharge = 0;	   
			        $ssb_account_tran_id_to= NULL;
			        $ssb_account_tran_id_from=NULL;

			        $from_bank_name=NULL;
			        $from_bank_id=NULL;
			        $from_bank_ac_no=NULL;
			        $from_bank_ifsc=NULL;
			        $from_bank_ac_id=NULL;
			        $to_bank_ifsc = NULL;
			        $account_id = NULL;
		        }elseif($request['customer_payment_mode'] == 1){

		            $branch_id = $request['customer_branch_id'];
		            $type = 26;
		            if($request['advanced'] > 0){
						$sub_type = 265;
					}else{
						$sub_type = 266;
					}
		            $chequeType = 1;
		            $type_id = $bookingId;
		            $associate_id = NULL;
		            $member_id = NULL;
		            $branch_id_to = $request['customer_branch_id'];
		            $branch_id_from = NULL;
		            $jv_unique_id = NULL;
		            $opening_balance = $request['amount'];
		            $amount = $request['amount'];
		            $closing_balance = $request['amount'];
		            
					$description_cr = 'Dr '.$customerResult->name.' '.$request['amount'].'';
		            $description_dr = 'Cr '.getBranchDetail($request['customer_branch_id'])->name.' '.$request['amount'];
		            $description = $request['payment_customer_description'];
		            $amount_to_id =$request['payment_customer_name'];
			        $amount_to_name = $customerResult->name;
			        $amount_from_id = $request['payment_customer_bank_id'];
			        $amount_from_name = getSamraddhBank($request['payment_customer_bank_id'])->bank_name;
					
	            	$payment_type = 'CR';
		            $head_payment_type = 'DR';
	            	$bank_charge_payment_type = 'DR';

		            $currency_code = 'INR';

		            $v_no = NULL;
		            $v_date = NULL;
		            $ssb_account_id_from = NULL;
		            $ssb_account_id_to = NULL;

		            if($request['payment_customer_paid_via'] == 1){
		            	$getChequeRecord=SamraddhCheque::where('id',$request['payment_customer_cheque_no'])->first();
		                $cheque_type = 1;
		                $cheque_id = $request['payment_customer_cheque_no'];
		                $cheque_no = $getChequeRecord->cheque_no;
		                $cheque_date = $getChequeRecord->cheque_create_date;
		                $cheque_bank_from = getSamraddhBank($request['payment_customer_bank_id'])->bank_name;
		                $cheque_bank_from_id = $request['payment_customer_bank_id'];
		                $cheque_bank_ac_from = getSamraddhBankAccountId($request['payment_customer_bank_account_number'])->account_no;
		                $cheque_bank_ifsc_from = getSamraddhBankAccountId($request['payment_customer_bank_account_number'])->ifsc_code;
		                $cheque_bank_ac_from_id = $request['payment_customer_bank_account_number'];
		                $cheque_bank_branch_from = NULL;


		                $cheque_bank_to = NULL;
		                $cheque_bank_ac_to = NULL;
		                $cheque_bank_to_name = NULL;
		                $cheque_bank_to_branch = NULL;
		                $cheque_bank_to_ac_no = NULL;
		                $cheque_bank_to_ifsc = NULL;
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = getSamraddhBank($request['payment_customer_bank_id'])->bank_name;
		                $transction_bank_from_id = $from_bank_id = $request['payment_customer_bank_id'];
		                $transction_bank_ac_from = $from_bank_ac_no = getSamraddhBankAccountId($request['payment_customer_bank_account_number'])->account_no;
		                $transction_bank_ifsc_from = $from_bank_ifsc = getSamraddhBankAccountId($request['payment_customer_bank_account_number'])->ifsc_code;
		                $transction_bank_from_ac_id = $account_id = $from_bank_ac_id = $request['payment_customer_bank_account_number'];
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = NULL;
		                $transction_bank_ac_to = NULL;
		                $payment_mode = 1;
		                $day_book_payment_mode = 1;
		                $transction_bank_to_name = NULL;
		                $transction_bank_to_ac_no = NULL;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = $to_bank_ifsc = NULL;
		                $neftcharge = 0;
		                SamraddhCheque::where('id',$request['payment_customer_cheque_no'])->update(['status' => 3,'is_use'=>1]);
		                SamraddhChequeIssue::create([
		                    'cheque_id' => $request['payment_customer_cheque_no'],
		                    'type' =>6,
		                    'sub_type' =>9,
		                    'type_id' =>$bookingId,
		                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),
		                    'status' => 1,
		                ]);
		            }elseif($request['payment_customer_paid_via'] == 2){
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
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = getSamraddhBank($request['payment_customer_bank_id'])->bank_name;
		                $transction_bank_from_id = $from_bank_id =$request['payment_customer_bank_id'];
		                $transction_bank_ac_from = $from_bank_ac_no = getSamraddhBankAccountId($request['payment_customer_bank_account_number'])->account_no;
		                $transction_bank_ifsc_from = $from_bank_ifsc = getSamraddhBankAccountId($request['payment_customer_bank_account_number'])->ifsc_code;
		                $transction_bank_from_ac_id = $account_id = $from_bank_ac_id = $request['payment_customer_bank_account_number'];
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = NULL;
		                $transction_bank_ac_to = NULL;
		                $transction_bank_to_name = NULL;
		                $transction_bank_to_ac_no = NULL;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = $to_bank_ifsc = NULL;
		                $payment_mode = 2;
		                $day_book_payment_mode = 3;
		                $neftcharge = $request['customer_neft'];
		            }
		        	$transction_date = date("Y-m-d", strtotime(convertDate($request['payment_customer_date'])));
		            $entry_date = date("Y-m-d", strtotime(convertDate($request['payment_customer_date'])));
		            $entry_time = date("H:i:s");
		            $created_by = 1;
		            $created_by_id = Auth::user()->id;
		            $is_contra = NULL;
		            $contra_id = NULL;
		            $created_at = date("Y-m-d", strtotime(convertDate($request['payment_customer_date'])));
		            $bank_id = $request['payment_customer_bank_id'];
		            $bank_ac_id = getSamraddhBankAccountId($request['payment_customer_bank_id'])->id;
		            $ssb_account_tran_id_to= NULL;
		            $ssb_account_tran_id_from=NULL;
		        }

		        if($request['neft_charge'] == 1){

			        if($request['advanced'] == 0){
						$bankingLedgerRecord = BankingLedger::where('id',$request['banking_ledger_id'])->first();
	                    $bankingLedgerResult = BankingLedger::find($request['banking_ledger_id']);
	                    $bankingLedgerPaymentData['customer_refund_payment'] = $request['amount'];
	                    if($bankingLedgerRecord->customer_advanced_payment == $request['amount']){
	                    	$bankingLedgerPaymentData['advanced_payment_status'] = 1;
	                    }
	                    $bankingLedgerResult->update($bankingLedgerPaymentData);	
					}

					if($request['advanced'] > 0){
                    	$ledgerDescription = 'Advanced Customer Payment';
                    }else{
                    	$ledgerDescription = $description;
                    }

					//$vendorBill = $this->createVendorBill($branch_id,$request['payment_customer_name'],5,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$bill_number=NULL,$discount_type=NULL,$total_tax_per=NULL,$total_tax_amount=NULL,$total_item=NULL,$total_item_amount=NULL,$total_discount_type=NULL,$total_discount_per=NULL,$total_discount_amount=NULL,$sub_amount=NULL,$tds_head=NULL,$tds_type=NULL,$tds_per=NULL,$tds_amount=NULL,$payble_amount=NULL,$due_amount=NULL,$amount,$amount,1,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$bookingId);

					$vendorLog = $this->createVendorLog($request['payment_customer_name'],$vendor_bill_id=NULL,'Vendor Contact Added',$ledgerDescription=NULL,1,Auth::user()->id,Auth::user()->username,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$bookingId);

	                $vendorBillPayment = $this->createVendorBillPayemnt($branch_id,$request['payment_customer_name'],NULL,0,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_customer_name'],NULL,NULL,NULL,NULL,$amount,$ledgerDescription,$currency_code,'DR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$transaction_charge=NULL,1,$bookingId);

	                $type_transaction_id = $vendorBillPayment;	

	                if($request['advanced'] > 0){

	                	$dayBookRef = CommanController::createBranchDayBookReference($amount);

	                	$this->createCustomerTransaction(1,1,$bookingId,NULL,$request['payment_customer_name'],$branch_id,$bank_id,$account_id,$amount,'Customer Advanced','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

	                	$this->createAdvancedTransaction(2,NULL,$request['payment_customer_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Customer Advanced','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );
					
				        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,179,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					}else{
						$this->createCustomerTransaction(1,1,$bookingId,NULL,$request['payment_customer_name'],$branch_id,$bank_id,$account_id,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );
					}
				}			
			}
		}elseif($bookingtype == 3){

			$ccRecord=CreditCard::where('id',$request['credit_card_id'])->first();
			$headRecord=AccountHeads::where('sub_head',$ccRecord->credit_card_number)->first();

			$headId = $headRecord->head_id;
			$payemntMode = $request['credit_card_mode'];
			$date = $request['credit_card_payment_date'];
			Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));
            $branch_id = $request['credit_card_branch_id'];
            $type = 26;
			$sub_type = 267;
            $chequeType = 1;
            $type_id = $bookingId;
            $type_transaction_id = NULL;
            $associate_id = NULL;
            $member_id = NULL;
            $branch_id_to = $request['credit_card_branch_id'];
            $branch_id_from = NULL;
            $jv_unique_id = NULL;
            $opening_balance = $request['amount'];
            $amount = $request['amount'];
            $closing_balance = $request['amount'];
            
            $description = $request['credit_card_description'];
			$description_cr = 'Dr  '.$request['amount'].'';
            $description_dr = 'Cr '.getSamraddhBankAccountId($request['credit_card_account_number'])->account_no.' '.$request['amount'];

            $payment_type = 'CR';
            $head_payment_type = 'DR';
            $bank_charge_payment_type = 'DR';
            $currency_code = 'INR';
            $amount_to_id = $request['credit_card_bank_id'];
            $amount_to_name = getSamraddhBank($request['credit_card_bank_id'])->bank_name;
            $amount_from_id = $headId;
	        $amount_from_name = getAcountHead($headId);
            $v_no = NULL;
            $v_date = NULL;
            $ssb_account_id_from = NULL;
            $ssb_account_id_to = NULL;

            if($request['credit_card_customer_paid_via'] == 1){
            	$getChequeRecord=SamraddhCheque::where('id',$request['credit_card_customer_cheque_no'])->first();
                $cheque_type = 1;
                $cheque_id = $request['credit_card_customer_cheque_no'];
                $cheque_no = $getChequeRecord->cheque_no;
                $cheque_date = $getChequeRecord->cheque_create_date;
                $cheque_bank_from = $request['credit_card_bank_id'];
                $cheque_bank_from_id = $request['credit_card_bank_id'];
                $cheque_bank_ac_from = getSamraddhBankAccountId($request['credit_card_account_number'])->id;
                $cheque_bank_ifsc_from = getSamraddhBankAccountId($request['credit_card_account_number'])->ifsc_code;
                $cheque_bank_ac_from_id = getSamraddhBankAccountId($request['credit_card_account_number'])->id;
                $cheque_bank_branch_from = NULL;
                $cheque_bank_to = NULL;
                $cheque_bank_ac_to = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_no = NULL;
                $transction_bank_from = getSamraddhBank($request['credit_card_bank_id'])->bank_name;
                $transction_bank_from_id = getSamraddhBank($request['credit_card_bank_id'])->id;
                $transction_bank_ac_from = $request['credit_card_account_number'];
                $transction_bank_ifsc_from = getSamraddhBankAccountId($request['credit_card_account_number'])->ifsc_code;
                $transction_bank_from_ac_id = $account_id = getSamraddhBankAccountId($request['credit_card_account_number'])->id;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $payment_mode = 1;
                $day_book_payment_mode = 1;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $neftcharge = 0;
                SamraddhCheque::where('id',$request['credit_card_customer_cheque_no'])->update(['status' => 3,'is_use'=>1]);
                SamraddhChequeIssue::create([
                    'cheque_id' => $request['credit_card_customer_cheque_no'],
                    'type' =>6,
                    'sub_type' =>9,
                    'type_id' =>$bookingId,
                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['credit_card_payment_date']))),
                    'status' => 1,
                ]);
            }elseif($request['credit_card_customer_paid_via'] == 2){
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
                $transction_no = NULL;
                $transction_bank_from = getSamraddhBank($request['credit_card_bank_id'])->bank_name;
                $transction_bank_from_id = getSamraddhBank($request['credit_card_bank_id'])->id;
                $transction_bank_ac_from = $request['credit_card_account_number'];
                $transction_bank_ifsc_from = getSamraddhBankAccountId($request['credit_card_account_number'])->ifsc_code;
                $transction_bank_from_ac_id = $account_id = getSamraddhBankAccountId($request['credit_card_account_number'])->id;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $payment_mode = 2;
                $day_book_payment_mode = 3;
                $neftcharge = $request['credit_card_neft'];
            }
        	$transction_date = date("Y-m-d", strtotime(convertDate($request['credit_card_payment_date'])));
            $entry_date = date("Y-m-d", strtotime(convertDate($request['credit_card_payment_date'])));
            $entry_time = date("H:i:s");
            $created_by = 1;
            $created_by_id = Auth::user()->id;
            $is_contra = NULL;
            $contra_id = NULL;
            $created_at = date("Y-m-d", strtotime(convertDate($request['credit_card_payment_date'])));
            $bank_id = NULL;
            $bank_id = $request['credit_card_bank_id'];
            $bank_ac_id = getSamraddhBankAccountId($request['credit_card_bank_id'])->id;
            $ssb_account_tran_id_to= NULL;
            $ssb_account_tran_id_from=NULL;

            $creditRecord = CreditCradTransaction::where('id',$request['credit_transaction_id'])->first();

            $cCard = CreditCradTransaction::find($creditRecord->id);
            $cCardData['used_amount'] = ($creditRecord->used_amount+$amount);
            if($creditRecord->total_amount == ($creditRecord->used_amount+$amount)){
            	$cCardData['status'] = 2;
            }else{
            	$cCardData['status'] = 1;
            }
            $cCard->update($cCardData);

            $this->createCreditCardTransaction(1,NULL,$bookingId,NULL,NULL,NULL,$request['credit_card_id'],($amount+$neftcharge),NULL,$branch_id,$bank_id,$account_id,($amount+$neftcharge),$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId);

            if($neftcharge > 0){
            	$dayBookRef = CommanController::createBranchDayBookReference($neftcharge);

            	$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,$sub_type,$type_id,NULL,$associate_id,$member_id,$branch_id_to,$branch_id_from,$neftcharge,$neftcharge,$neftcharge,'NEFT Charge A/c Cr '.$neftcharge.'',$bank_charge_payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
	        }
		}elseif($bookingtype == 4){
			if($request['receive_payment_account_type'] == 1){

				if($request['received_payment_vendor_type'] == 0){
					$headId = 74;
				}elseif($request['received_payment_vendor_type'] == 1){
					$headId = 73;
				}elseif($request['received_payment_vendor_type'] == 2){
					$headId = 186;
				}elseif($request['received_payment_vendor_type'] == 3){
					$headId = 185;
				}

				$payemntMode = $request['vendor_received_payment_mode'];
				$date = $request['received_payment_vendor_date'];
				Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));
				if($request['vendor_received_payment_mode'] == 2){
					$type = 26;
					$sub_type = 263;
					$jv_unique_id = NULL;
					$branch_id = $request['received_payment_branch_id'];

					if($request['received_payment_vendor_type'] == 0){
						$rentResult=RentLiability::select('id','owner_name')->where('id',$request['received_payment_vendor_name'])->first();
						
						$description_cr = 'Dr '.$rentResult->owner_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $rentResult->owner_name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getBranchDetail($request['received_payment_branch_id'])->name;
					}elseif($request['received_payment_vendor_type'] == 1){
						$employeeResult=Employee::select('id','employee_name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$employeeResult->employee_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $employeeResult->employee_name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getBranchDetail($request['received_payment_branch_id'])->name;
					}elseif($request['received_payment_vendor_type'] == 2){
						$associateResult=Member::select('id','first_name','last_name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$associateResult->first_name.' '.$associateResult->last_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $associateResult->first_name.' '.$associateResult->last_name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getBranchDetail($request['received_payment_branch_id'])->name;
					}elseif($request['received_payment_vendor_type'] == 3){
						$vendorResult=Vendor::select('id','name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$vendorResult->name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $vendorResult->name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getBranchDetail($request['received_payment_branch_id'])->name;
					}
						
					$description = $request['received_payment_vendor_description'];	
			        $type_id = $bookingId;
			        
			        $associate_id = NULL;
			        $member_id = NULL;
			        $branch_id_to = NULL;
			        $branch_id_from = $request['received_payment_branch_id'];
			        $opening_balance = $request['amount'];
			        $amount = $request['amount'];
			        $closing_balance = $request['amount'];
			        $payment_type = 'DR';
			        $head_payment_type = 'CR';
			        $payment_mode = 0;
			        $day_book_payment_mode = 0;
			        $currency_code = 'INR';
			        
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
			        $transction_date = date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date'])));
			        $entry_date = date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date'])));
			        $entry_time = date("H:i:s");
			        $created_by = 1;
			        $created_by_id = Auth::user()->id;
			        $is_contra = NULL;
			        $contra_id = NULL;
			        $created_at = date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date'])));
			        $bank_id = NULL;
			        $bank_ac_id = NULL;
			        $transction_bank_to_name = NULL;
			        $transction_bank_to_ac_no = NULL;
			        $transction_bank_to_branch = NULL;
			        $transction_bank_to_ifsc = NULL;
			        $neftcharge = 0;	   
			        $ssb_account_tran_id_to= NULL;
			        $ssb_account_tran_id_from=NULL;

			        $from_bank_name=NULL;
			        $from_bank_id=NULL;
			        $from_bank_ac_no=NULL;
			        $from_bank_ifsc=NULL;
			        $from_bank_ac_id=NULL;
			        $to_bank_ifsc=NULL;
			        $account_id=NULL;
		        }elseif($request['vendor_received_payment_mode'] == 1){

		            $branch_id = $request['received_payment_branch_id'];
		            $type = 26;
					$sub_type = 263;
		            $chequeType = 1;
		            $type_id = $bookingId;
		            $associate_id = NULL;
		            $member_id = NULL;
		            $branch_id_to = $request['received_payment_branch_id'];
		            $branch_id_from = NULL;
		            $jv_unique_id = NULL;
		            $opening_balance = $request['amount'];
		            $amount = $request['amount'];
		            $closing_balance = $request['amount'];
		            
		            if($request['received_payment_vendor_type'] == 0){
						$rentResult=RentLiability::select('id','owner_name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$rentResult->owner_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $rentResult->owner_name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getSamraddhBank($request['received_payment_branch_id'])->bank_name;
					}elseif($request['received_payment_vendor_type'] == 1){
						$employeeResult=Employee::select('id','employee_name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$employeeResult->employee_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $employeeResult->employee_name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getSamraddhBank($request['received_payment_branch_id'])->bank_name;
					}elseif($request['received_payment_vendor_type'] == 2){
						$associateResult=Member::select('id','first_name','last_name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$associateResult->first_name.' '.$associateResult->last_name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $associateResult->first_name.' '.$associateResult->last_name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getBranchDetail($request['received_payment_branch_id'])->name;
					}elseif($request['received_payment_vendor_type'] == 3){
						$vendorResult=Vendor::select('id','name')->where('id',$request['received_payment_vendor_name'])->first();
						$description_cr = 'Dr '.$vendorResult->name.' '.$request['amount'].'';
			            $description_dr = 'Cr '.getBranchDetail($request['received_payment_branch_id'])->name.' '.$request['amount'];
			            $amount_to_id =$request['received_payment_vendor_name'];
				        $amount_to_name = $vendorResult->name;
				        $amount_from_id = $request['received_payment_branch_id'];
				        $amount_from_name = getBranchDetail($request['received_payment_branch_id'])->name;
					}

					$description = $request['received_payment_vendor_description'];	
		            $payment_type = 'DR';
		            $head_payment_type = 'CR';
	            	$bank_charge_payment_type = 'DR';
		            $currency_code = 'INR';
		            $amount_to_id = $headId;
			        $amount_to_name = getAcountHead($headId);
		            $amount_from_id = $headId;
			        $amount_from_name = getSamraddhBank($request['received_payment_branch_id'])->bank_name;

		            $v_no = NULL;
		            $v_date = NULL;
		            $ssb_account_id_from = NULL;
		            $ssb_account_id_to = NULL;

		            if($request['received_payment_vendor_paid_via'] == 1){
		            	$getChequeRecord=SamraddhCheque::where('id',$request['received_payment_vendor_cheque_no'])->first();
		                $cheque_type = 1;
		                $cheque_id = $request['received_payment_vendor_cheque_no'];
		                $cheque_no = $getChequeRecord->cheque_no;
		                $cheque_date = $getChequeRecord->cheque_create_date;
		                $cheque_bank_from = $request['received_payment_branch_id'];
		                $cheque_bank_from_id = $request['received_payment_branch_id'];
		                $cheque_bank_ac_from = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->id;
		                $cheque_bank_ifsc_from = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->ifsc_code;
		                $cheque_bank_ac_from_id = $account_id = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->id;
		                $cheque_bank_branch_from = NULL;
		                $cheque_bank_to = NULL;
		                $cheque_bank_ac_to = NULL;
		                $cheque_bank_to_name = NULL;
		                $cheque_bank_to_branch = NULL;
		                $cheque_bank_to_ac_no = NULL;
		                $cheque_bank_to_ifsc = NULL;
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = getSamraddhBank($request['received_payment_branch_id'])->bank_name;
		                $transction_bank_from_id = $from_bank_id = getSamraddhBank($request['received_payment_branch_id'])->id;
		                $transction_bank_ac_from = $from_bank_ac_no = $request['received_payment_vendor_bank_account_number'];
		                $transction_bank_ifsc_from = $from_bank_ifsc = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->ifsc_code;
		                $transction_bank_from_ac_id = $from_bank_ac_id = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->id;
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = NULL;
		                $transction_bank_ac_to = NULL;
		                $payment_mode = 1;
		                $day_book_payment_mode = 1;
		                $transction_bank_to_name = NULL;
		                $transction_bank_to_ac_no = NULL;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = NULL;
		                $neftcharge = 0;
		                SamraddhCheque::where('id',$request['received_payment_vendor_cheque_no'])->update(['status' => 3,'is_use'=>1]);
		                SamraddhChequeIssue::create([
		                    'cheque_id' => $request['received_payment_vendor_cheque_no'],
		                    'type' =>6,
		                    'sub_type' =>9,
		                    'type_id' =>$bookingId,
		                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),
		                    'status' => 1,
		                ]);
		            }elseif($request['received_payment_vendor_paid_via'] == 2){
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
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = getSamraddhBank($request['received_payment_vendor_bank_id'])->bank_name;
		                $transction_bank_from_id = $from_bank_id = getSamraddhBank($request['received_payment_vendor_bank_id'])->id;
		                $transction_bank_ac_from = $from_bank_ac_no = $request['received_payment_vendor_bank_account_number'];
		                $transction_bank_ifsc_from = $from_bank_ifsc = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->ifsc_code;
		                $transction_bank_from_ac_id = $account_id = $from_bank_ac_id = getSamraddhBankAccountId($request['received_payment_vendor_bank_account_number'])->id;
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = NULL;
		                $transction_bank_ac_to = NULL;
		                $transction_bank_to_name = NULL;
		                $transction_bank_to_ac_no = NULL;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = NULL;
		                $payment_mode = 2;
		                $day_book_payment_mode = 3;
		                $neftcharge = $request['received_payment_vendor_neft'];
		            }
		        	$transction_date = date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date'])));
		            $entry_date = date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date'])));
		            $entry_time = date("H:i:s");
		            $created_by = 1;
		            $created_by_id = Auth::user()->id;
		            $is_contra = NULL;
		            $contra_id = NULL;
		            $created_at = date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date'])));
		            $bank_id = NULL;
		            $bank_id = $request['received_payment_vendor_bank_id'];
		            $bank_ac_id = getSamraddhBankAccountId($request['received_payment_vendor_bank_id'])->id;
		            $ssb_account_tran_id_to= NULL;
		            $ssb_account_tran_id_from=NULL;
		            $to_bank_ifsc=NULL;
		        }

		        if($request['received_payment_vendor_type'] == 0 && $request['neft_charge'] == 1){


                	$rentLiabilityRecord = RentLiability::where('id',$request['received_payment_vendor_name'])->first();
                    $rentLiability = RentLiability::find($request['received_payment_vendor_name']);
                    $rentLiabilityData['advance_payment'] = ($rentLiabilityRecord->advance_payment-$request['amount']);
                    $rentLiability->update($rentLiabilityData);
	                
                    $bankingLedgerRecord = BankingLedger::where('id',$request['banking_ledger_id'])->first();

                    $bankingLedgerResult = BankingLedger::find($request['banking_ledger_id']);
                    $bankingLedgerPaymentData['advanced_amount'] = $bankingLedgerRecord->advanced_amount-$request['amount'];
                    if($bankingLedgerRecord->advanced_amount == $request['amount']){
                    	$bankingLedgerPaymentData['advanced_payment_status'] = 1;
                    }
                    $bankingLedgerResult->update($bankingLedgerPaymentData);

                    $rentLedger = $this->RentLiabilityLedger($request['received_payment_vendor_name'],1,NULL,NULL,$request['amount'],NULL,$description,$currency_code,'CR',$payment_mode,1,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$updated_at=NULL,$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$transaction_charge=NULL,$jv_type_id=NULL,$bookingId);

                    /*$vendorBill = $this->createVendorBill($branch_id,$vendor_id,$bill_type,$bill_date,$bill_number,$discount_type,$total_tax_per,$total_tax_amount,$total_item,$total_item_amount,$total_discount_type,$total_discount_per,$total_discount_amount,$sub_amount,$tds_head,$tds_type,$tds_per,$tds_amount,$payble_amount,$due_amount,$transferd_amount,$balance,$status,$created_at);

                    $vendorLog = $this->createVendorLog($vendor_id,$vendor_bill_id,$title,$description,$created_by,$created_by_id,$created_by_name,$created_at);*/

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,4,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['received_payment_vendor_name'],$rentLedger,$rentResult->id,NULL,$request['amount'],NULL,'Rent Advanced Return',$currency_code,'CR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $rentLedger;

                    $this->createAdvancedTransaction(3,NULL,$request['received_payment_vendor_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Adavanced Rent Return','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

				}elseif($request['received_payment_vendor_type'] == 1 && $request['neft_charge'] == 1){
		        	$employeeRecord = Employee::where('id',$request['received_payment_vendor_name'])->first();
                    $employee = Employee::find($request['received_payment_vendor_name']);
                    $employeeData['advance_payment'] = ($employeeRecord->advance_payment-$request['amount']);
                    $employee->update($employeeData);
	                
                    $bankingLedgerRecord = BankingLedger::where('id',$request['banking_ledger_id'])->first();
                    $bankingLedgerResult = BankingLedger::find($request['banking_ledger_id']);
                    $bankingLedgerPaymentData['advanced_amount'] = $bankingLedgerRecord->advanced_amount-$request['amount'];
                    if($bankingLedgerRecord->advanced_amount == $request['amount']){
                    	$bankingLedgerPaymentData['advanced_payment_status'] = 1;
                    }
                    $bankingLedgerResult->update($bankingLedgerPaymentData);

                    $employeeLedger = $this->createEmployeeLedgers($request['received_payment_vendor_name'],$branch_id,1,NULL,NULL,$request['amount'],NULL,$description,$currency_code,'CR',$payment_mode,1,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$transaction_charge=NULL,$jv_type_id=NULL,$bookingId);

                    /*$vendorBill = $this->createVendorBill($branch_id,$vendor_id,$bill_type,$bill_date,$bill_number,$discount_type,$total_tax_per,$total_tax_amount,$total_item,$total_item_amount,$total_discount_type,$total_discount_per,$total_discount_amount,$sub_amount,$tds_head,$tds_type,$tds_per,$tds_amount,$payble_amount,$due_amount,$transferd_amount,$balance,$status,$created_at);

                    $vendorLog = $this->createVendorLog($vendor_id,$vendor_bill_id,$title,$description,$created_by,$created_by_id,$created_by_name,$created_at);*/

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,5,$salary_ledger_id=NULL,$salary_id=NULL,$request['received_payment_vendor_name'],NULL,$employeeLedger,$employeeResult->id,NULL,$request['amount'],NULL,'Salary Advanced Return',$currency_code,'CR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $employeeLedger;

                    $this->createAdvancedTransaction(4,NULL,$request['received_payment_vendor_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Adavanced Salary Return','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

				}elseif($request['received_payment_vendor_type'] == 2 && $request['neft_charge'] == 1){
	                
                    $bankingLedgerRecord = BankingLedger::where('id',$request['banking_ledger_id'])->first();
                    $bankingLedgerResult = BankingLedger::find($request['banking_ledger_id']);
                    $bankingLedgerPaymentData['advanced_amount'] = $bankingLedgerRecord->advanced_amount-$request['amount'];
                    if($bankingLedgerRecord->advanced_amount == $request['amount']){
                    	$bankingLedgerPaymentData['advanced_payment_status'] = 1;
                    }
                    $bankingLedgerResult->update($bankingLedgerPaymentData);

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,NULL,$vendor_bill_id=NULL,6,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,NULL,NULL,$request['received_payment_vendor_name'],NULL,$request['amount'],NULL,'Associate Advanced Return',$currency_code,'CR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $vendorBill;

                    $this->createAssociateTransaction(6,NULL,$request['received_payment_vendor_name'],NULL,$request['received_payment_vendor_name'],$branch_id,$bank_id,$account_id,$amount,'Associate Advanced Return','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$transction_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

                    $this->createAdvancedTransaction(5,NULL,$associatePaymentRecord->member_id,$request['received_payment_vendor_name'],$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Associate Advanced Return','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

				}elseif($request['received_payment_vendor_type'] == 3 && $request['neft_charge'] == 1){
	                
                    $bankingLedgerRecord = BankingLedger::where('id',$request['banking_ledger_id'])->first();
                    $bankingLedgerResult = BankingLedger::find($request['banking_ledger_id']);
                    $bankingLedgerPaymentData['advanced_amount'] = $bankingLedgerRecord->advanced_amount-$request['amount'];
                    if($bankingLedgerRecord->advanced_amount == $request['amount']){
                    	$bankingLedgerPaymentData['advanced_payment_status'] = 1;
                    }
                    $bankingLedgerResult->update($bankingLedgerPaymentData);

                    $vendorBill = $this->createVendorBillPayemnt($branch_id,$request['received_payment_vendor_name'],$vendor_bill_id=NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,NULL,NULL,NULL,NULL,$request['amount'],NULL,'Vendor Advanced Return',$currency_code,'CR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

                    $type_transaction_id = $vendorBill;

                    $this->createAdvancedTransaction(1,NULL,$request['received_payment_vendor_name'],$request['received_payment_vendor_name'],$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Vendor Advanced Return','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

                    $this->createVendorTransaction(3,31,$request['received_payment_vendor_name'],$type_transaction_id,$request['received_payment_vendor_name'],$branch_id,$bank_id,$account_id,$amount,'Vendor Advanced Return','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$credit_node_id=NULL,$advance_id=NULL,$bookingId);
				}
			}elseif($request['receive_payment_account_type'] == 2){
				$headId = 142;
				$payemntMode = $request['received_payment_customer_mode'];
				$date = $request['received_payment_customer_date'];
				Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));
				$customerResult=Vendor::select('id','name')->where('id',$request['received_payment_customer_name'])->first();
				if($request['received_payment_customer_mode'] == 2){
					$type = 26;
					if($request['advanced'] > 0){
						$sub_type = 265;
					}else{
						$sub_type = 266;
					}
					$jv_unique_id = NULL;
					$branch_id = $request['received_payment_customer_branch_id'];
					
					$description_cr = 'Dr '.getBranchDetail($request['received_payment_customer_branch_id'])->name.' '.$request['amount'].'';
		            $description_dr = 'Cr '.$customerResult->name.' '.$request['amount'];
		            $amount_to_id = $request['received_payment_customer_branch_id'];
			        $amount_to_name = getBranchDetail($request['received_payment_customer_branch_id'])->name;
			        $amount_from_id = $request['received_payment_customer_name'];
			        $amount_from_name = $customerResult->name;
					
					$description = $request['received_payment_customer_description'];	
			        $type_id = $bookingId;
			        
			        $associate_id = NULL;
			        $member_id = NULL;
			        $branch_id_to = $request['received_payment_customer_branch_id'];
			        $branch_id_from = NULL;
			        $opening_balance = $request['amount'];
			        $amount = $request['amount'];
			        $closing_balance = $request['amount'];

			        $payment_type = 'DR';
		            $head_payment_type = 'CR';
	            	$bank_charge_payment_type = 'DR';

			        $payment_mode = 0;
			        $day_book_payment_mode = 0;
			        $currency_code = 'INR';
			        
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
			        $transction_date = date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date'])));
			        $entry_date = date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date'])));
			        $entry_time = date("H:i:s");
			        $created_by = 1;
			        $created_by_id = Auth::user()->id;
			        $is_contra = NULL;
			        $contra_id = NULL;
			        $created_at = date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date'])));
			        $bank_id = NULL;
			        $bank_ac_id = NULL;
			        $transction_bank_to_name = NULL;
			        $transction_bank_to_ac_no = NULL;
			        $transction_bank_to_branch = NULL;
			        $transction_bank_to_ifsc = NULL;
			        $neftcharge = 0;	   
			        $ssb_account_tran_id_to= NULL;
			        $ssb_account_tran_id_from=NULL;

			        $from_bank_name=NULL;
			        $from_bank_id=NULL;
			        $from_bank_ac_no=NULL;
			        $from_bank_ifsc=NULL;
			        $from_bank_ac_id=NULL;
			        $to_bank_ifsc=NULL;
			        $account_id=NULL;
		        }elseif($request['received_payment_customer_mode'] == 1){

		            $branch_id = $request['received_payment_customer_branch_id'];
		            $type = 26;
					if($request['advanced'] > 0){
						$sub_type = 265;
					}else{
						$sub_type = 266;
					}
		            $chequeType = 1;
		            $type_id = $bookingId;
		            $associate_id = NULL;
		            $member_id = NULL;
		            $branch_id_to = $request['received_payment_customer_branch_id'];
		            $branch_id_from = NULL;
		            $jv_unique_id = NULL;
		            $opening_balance = $request['amount'];
		            $amount = $request['amount'];
		            $closing_balance = $request['amount'];

					$description_cr = 'Dr '.getBranchDetail($request['received_payment_customer_branch_id'])->name.' '.$request['amount'].'';
		            $description_dr = 'Cr '.$customerResult->name.' '.$request['amount'];
		            $amount_to_id =$request['received_payment_customer_bank_id'];
			        $amount_to_name = getSamraddhBank($request['received_payment_customer_bank_id'])->bank_name;
			        $amount_from_id = $request['received_payment_customer_name'];
			        $amount_from_name = $customerResult->name;
					$description = $request['received_payment_customer_description'];
					$type_id = $bookingId;

		            $payment_type = 'DR';
		            $head_payment_type = 'CR';
	            	$bank_charge_payment_type = 'DR';
		            $currency_code = 'INR';

		            $v_no = NULL;
		            $v_date = NULL;
		            $ssb_account_id_from = NULL;
		            $ssb_account_id_to = NULL;

		            if($request['received_payment_customer_paid_via'] == 1){
		            	$getChequeRecord=SamraddhCheque::where('id',$request['received_payment_customer_cheque_no'])->first();
		                $cheque_type = 1;
		                $cheque_id = $request['received_payment_customer_cheque_no'];
		                $cheque_no = $getChequeRecord->cheque_no;
		                $cheque_date = $getChequeRecord->cheque_create_date;
		                $cheque_bank_from = NULL;
		                $cheque_bank_from_id = NULL;
		                $cheque_bank_ac_from = NULL;
		                $cheque_bank_ifsc_from = NULL;
		                $cheque_bank_ac_from_id = NULL;
		                $cheque_bank_branch_from = NULL;


		                $cheque_bank_to = $to_bank_ifsc = $request['received_payment_customer_bank_id'];
		                $cheque_bank_ac_to = $account_id = $request['received_payment_customer_bank_account_number'];
		                $cheque_bank_to_name = getSamraddhBank($request['received_payment_customer_bank_id'])->bank_name;
		                $cheque_bank_to_branch = NULL;
		                $cheque_bank_to_ac_no = getSamraddhBankAccountId($request['received_payment_customer_bank_account_number'])->account_no;
		                $cheque_bank_to_ifsc = getSamraddhBankAccountId($request['received_payment_customer_bank_account_number'])->ifsc_code;
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = NULL;
		                $transction_bank_from_id = $from_bank_id = NULL;
		                $transction_bank_ac_from = $from_bank_ac_no = NULL;
		                $transction_bank_ifsc_from = $from_bank_ifsc = NULL;
		                $transction_bank_from_ac_id = $from_bank_ac_id = NULL;
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = $request['received_payment_customer_bank_id'];
		                $transction_bank_ac_to = $request['received_payment_customer_bank_account_number'];
		                $payment_mode = 1;
		                $day_book_payment_mode = 1;
		                $transction_bank_to_name = getSamraddhBank($request['received_payment_customer_bank_id'])->bank_name;
		                $transction_bank_to_ac_no = getSamraddhBankAccountId($request['received_payment_customer_bank_account_number'])->account_no;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = getSamraddhBankAccountId($request['received_payment_customer_bank_account_number'])->ifsc_code;
		                $neftcharge = 0;
		                SamraddhCheque::where('id',$request['received_payment_customer_cheque_no'])->update(['status' => 3,'is_use'=>1]);
		                SamraddhChequeIssue::create([
		                    'cheque_id' => $request['received_payment_customer_cheque_no'],
		                    'type' =>6,
		                    'sub_type' =>9,
		                    'type_id' =>$bookingId,
		                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),
		                    'status' => 1,
		                ]);
		            }elseif($request['received_payment_customer_paid_via'] == 2){
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
		                $transction_no = NULL;
		                $transction_bank_from = $from_bank_name = NULL;
		                $transction_bank_from_id = $from_bank_id =NULL;
		                $transction_bank_ac_from = $from_bank_ac_no = NULL;
		                $transction_bank_ifsc_from = $from_bank_ifsc = NULL;
		                $transction_bank_from_ac_id = $from_bank_ac_id = NULL;
		                $transction_bank_branch_from = NULL;
		                $transction_bank_to = $to_bank_ifsc = $request['received_payment_customer_bank_id'];
		                $transction_bank_ac_to = $account_id = $request['received_payment_customer_bank_account_number'];
		                $transction_bank_to_name = getSamraddhBank($request['received_payment_customer_bank_id'])->bank_name;
		                $transction_bank_to_ac_no = getSamraddhBankAccountId($request['received_payment_customer_bank_account_number'])->account_no;
		                $transction_bank_to_branch = NULL;
		                $transction_bank_to_ifsc = getSamraddhBankAccountId($request['received_payment_customer_bank_account_number'])->ifsc_code;
		                $payment_mode = 2;
		                $day_book_payment_mode = 3;
		                $neftcharge = $request['neft_charge'];
		            }
		        	$transction_date = date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date'])));
		            $entry_date = date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date'])));
		            $entry_time = date("H:i:s");
		            $created_by = 1;
		            $created_by_id = Auth::user()->id;
		            $is_contra = NULL;
		            $contra_id = NULL;
		            $created_at = date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date'])));
		            $bank_id = $request['received_payment_customer_bank_id'];
		            $bank_ac_id = getSamraddhBankAccountId($request['received_payment_customer_bank_id'])->id;
		            $ssb_account_tran_id_to= NULL;
		            $ssb_account_tran_id_from=NULL;
		        }

		        if($request['neft_charge'] == 1){

			        if($request['advanced'] == 0 && $request['banking_ledger_id'] > 0){
						$bankingLedgerRecord = BankingLedger::where('id',$request['banking_ledger_id'])->first();
	                    $bankingLedgerResult = BankingLedger::find($request['banking_ledger_id']);
	                    $bankingLedgerPaymentData['customer_advanced_payment'] = $request['amount'];
	                    if($bankingLedgerRecord->customer_refund_payment == $request['amount']){
	                    	$bankingLedgerPaymentData['advanced_payment_status'] = 1;
	                    }
	                    $bankingLedgerResult->update($bankingLedgerPaymentData);	
					}

					if($request['advanced'] > 0){
                    	$ledgerDescription = 'Advanced Customer Payment';
                    }else{
                    	$ledgerDescription = $description;
                    }

					//$vendorBill = $this->createVendorBill($branch_id,$request['received_payment_customer_name'],$bill_type=NULL,$bill_date=NULL,$bill_number=NULL,$discount_type=NULL,$total_tax_per=NULL,$total_tax_amount=NULL,$total_item=NULL,$total_item_amount=NULL,$total_discount_type=NULL,$total_discount_per=NULL,$total_discount_amount=NULL,$sub_amount=NULL,$tds_head=NULL,$tds_type=NULL,$tds_per=NULL,$tds_amount=NULL,$payble_amount=NULL,$due_amount=NULL,$request['received_customer_payment_amount'],$request['received_customer_payment_amount'],1,date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),$bookingId);

					$vendorLog = $this->createVendorLog($request['received_payment_customer_name'],$vendor_bill_id=NULL,'Vendor Contact Added',NULL,1,Auth::user()->id,Auth::user()->username,date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),$bookingId);

	                $vendorBillPayment = $this->createVendorBillPayemnt($branch_id,$request['received_payment_customer_name'],NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['received_payment_customer_name'],NULL,NULL,NULL,$amount,NULL,$ledgerDescription,$currency_code,'CR',$payment_mode,date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),$transaction_charge=NULL,1,$bookingId);

	                $type_transaction_id = $vendorBillPayment;

	                //if($request['refund'] > 0){

			        if($request['advanced'] > 0){

			        	$dayBookRef = CommanController::createBranchDayBookReference($request['advanced']);

			        	/*$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,179,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);*/

			        	$this->createCustomerTransaction(1,1,$bookingId,NULL,$request['received_payment_customer_name'],$branch_id,$bank_id,$account_id,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

                		$this->createAdvancedTransaction(2,NULL,$request['received_payment_customer_name'],NULL,$bill_id=NULL,$total_amount=NULL,$used_amount=NULL,$branch_id,$bank_id,$account_id,$amount,'Customer Advanced','CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

                		$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$headId,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$head_payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
			        }else{
			        	$dayBookRef = CommanController::createBranchDayBookReference($amount);

	                	$this->createCustomerTransaction(1,1,$bookingId,NULL,$request['received_payment_customer_name'],$branch_id,$bank_id,$account_id,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$bookingId );

	                	$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,179,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
			        }
					//}
	            }
			}
		}elseif($bookingtype == 5){

			if($request['income_head_id3']){
				$headId = $request['income_head_id3'];
			}elseif($request['income_head_id2']){
				$headId = $request['income_head_id2'];
			}elseif($request['income_head_id1']){
				$headId = $request['income_head_id1'];
			}elseif($request['income_head_id']){
				$headId = $request['income_head_id'];
			}

			$payemntMode = $request['indirect_income_mode'];
			$date = $request['indirect_income_date'];
			Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($date))));
			if($request['indirect_income_mode'] == 2){

				$type = 26;
				$sub_type = 264;
				$jv_unique_id = NULL;
				$branch_id = $request['indirect_income_branch_id'];
				$description = 'Banking Income '.$request['indirect_income_amount'];
				$description_cr = 'Dr '.getAcountHead($headId).' '.$request['indirect_income_amount'].'';
	            $description_dr = 'Cr '.getBranchDetail($request['indirect_income_branch_id'])->name.' '.$request['indirect_income_amount'];
		        $type_id = $bookingId;
		        $type_transaction_id = NULL;
		        $associate_id = NULL;
		        $member_id = NULL;
		        $branch_id_to = $request['indirect_income_branch_id'];
		        $branch_id_from = NULL;
		        $opening_balance = $request['indirect_income_amount'];
		        $amount = $request['indirect_income_amount'];
		        $closing_balance = $request['indirect_income_amount'];
		        $payment_type = 'DR';
		        $head_payment_type = 'CR';
		        $payment_mode = 0;
		        $day_book_payment_mode = 0;
		        $currency_code = 'INR';
		        $amount_to_id =$request['indirect_income_branch_id'];
		        $amount_to_name = $headId;
		        $amount_from_id = $headId;
		        $amount_from_name = getAcountHead($headId);
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
		        $transction_date = date("Y-m-d", strtotime(convertDate($request['indirect_income_date'])));
		        $entry_date = date("Y-m-d", strtotime(convertDate($request['indirect_income_date'])));
		        $entry_time = date("H:i:s");
		        $created_by = 1;
		        $created_by_id = Auth::user()->id;
		        $is_contra = NULL;
		        $contra_id = NULL;
		        $created_at = date("Y-m-d", strtotime(convertDate($request['indirect_income_date'])));
		        $bank_id = NULL;
		        $bank_ac_id = NULL;
		        $transction_bank_to_name = NULL;
		        $transction_bank_to_ac_no = NULL;
		        $transction_bank_to_branch = NULL;
		        $transction_bank_to_ifsc = NULL;
		        $neftcharge = 0;	   
		        $ssb_account_tran_id_to= NULL;
		        $ssb_account_tran_id_from=NULL;
	        }elseif($request['indirect_income_mode'] == 1){

	            $branch_id = $request['indirect_income_branch_id'];
	            $type = 26;
				$sub_type = 261;
	            $chequeType = 1;
	            $type_id = $bookingId;
	            $type_transaction_id = NULL;
	            $associate_id = NULL;
	            $member_id = NULL;
	            $branch_id_to = $request['indirect_income_branch_id'];
	            $branch_id_from = NULL;
	            $jv_unique_id = NULL;
	            $opening_balance = $request['indirect_income_amount'];
	            $amount = $request['indirect_income_amount'];
	            $closing_balance = $request['indirect_income_amount'];
	            
	            $description = 'Booking Expense '.$request['indirect_income_amount'];
				$description_cr = 'Dr '.getAcountHead($headId).' '.$request['indirect_income_amount'].'';
	            $description_dr = 'Cr '.getSamraddhBankAccountId($request['indirect_income_account_no'])->account_no.' '.$request['indirect_income_amount'];

	            $payment_type = 'DR';
	            $head_payment_type = 'CR';
	            $bank_charge_payment_type = 'DR';
	            $currency_code = 'INR';
	            $amount_to_id = $request['indirect_income_bank_id'];
	            $amount_to_name = getSamraddhBank($request['indirect_income_bank_id'])->bank_name;
	            $amount_from_id = $headId;
		        $amount_from_name = getAcountHead($headId);
	            $v_no = NULL;
	            $v_date = NULL;
	            $ssb_account_id_from = NULL;
	            $ssb_account_id_to = NULL;

	            if($request['indirect_income_paid_via'] == 1){
	            	$getChequeRecord=SamraddhCheque::where('id',$request['indirect_income_cheque_no'])->first();
	                $cheque_type = 1;
	                $cheque_id = $request['indirect_income_cheque_no'];
	                $cheque_no = $getChequeRecord->cheque_no;
	                $cheque_date = $getChequeRecord->cheque_create_date;
	                $cheque_bank_from = $request['indirect_income_bank_id'];
	                $cheque_bank_from_id = $request['indirect_income_bank_id'];
	                $cheque_bank_ac_from = getSamraddhBankAccountId($request['indirect_income_account_no'])->id;
	                $cheque_bank_ifsc_from = getSamraddhBankAccountId($request['indirect_income_account_no'])->ifsc_code;
	                $cheque_bank_ac_from_id = getSamraddhBankAccountId($request['indirect_income_account_no'])->id;
	                $cheque_bank_branch_from = NULL;
	                $cheque_bank_to = NULL;
	                $cheque_bank_ac_to = NULL;
	                $cheque_bank_to_name = NULL;
	                $cheque_bank_to_branch = NULL;
	                $cheque_bank_to_ac_no = NULL;
	                $cheque_bank_to_ifsc = NULL;
	                $transction_no = NULL;
	                $transction_bank_from = getSamraddhBank($request['indirect_income_bank_id'])->bank_name;
	                $transction_bank_from_id = getSamraddhBank($request['indirect_income_bank_id'])->id;
	                $transction_bank_ac_from = $request['indirect_income_account_no'];
	                $transction_bank_ifsc_from = getSamraddhBankAccountId($request['indirect_income_account_no'])->ifsc_code;
	                $transction_bank_from_ac_id = getSamraddhBankAccountId($request['indirect_income_account_no'])->id;
	                $transction_bank_branch_from = NULL;
	                $transction_bank_to = NULL;
	                $transction_bank_ac_to = NULL;
	                $payment_mode = 1;
	                $day_book_payment_mode = 1;
	                $transction_bank_to_name = NULL;
	                $transction_bank_to_ac_no = NULL;
	                $transction_bank_to_branch = NULL;
	                $transction_bank_to_ifsc = NULL;
	                $neftcharge = 0;
	                SamraddhCheque::where('id',$request['indirect_income_cheque_no'])->update(['status' => 3,'is_use'=>1]);
	                SamraddhChequeIssue::create([
	                    'cheque_id' => $request['indirect_income_cheque_no'],
	                    'type' =>6,
	                    'sub_type' =>9,
	                    'type_id' =>$bookingId,
	                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['indirect_income_date']))),
	                    'status' => 1,
	                ]);
	            }elseif($request['indirect_income_paid_via'] == 2){
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
	                $transction_no = NULL;
	                $transction_bank_from = getSamraddhBank($request['indirect_income_bank_id'])->bank_name;
	                $transction_bank_from_id = getSamraddhBank($request['indirect_income_bank_id'])->id;
	                $transction_bank_ac_from = $request['indirect_income_account_no'];
	                $transction_bank_ifsc_from = getSamraddhBankAccountId($request['indirect_income_account_no'])->ifsc_code;
	                $transction_bank_from_ac_id = getSamraddhBankAccountId($request['indirect_income_account_no'])->id;
	                $transction_bank_branch_from = NULL;
	                $transction_bank_to = NULL;
	                $transction_bank_ac_to = NULL;
	                $transction_bank_to_name = NULL;
	                $transction_bank_to_ac_no = NULL;
	                $transction_bank_to_branch = NULL;
	                $transction_bank_to_ifsc = NULL;
	                $payment_mode = 2;
	                $day_book_payment_mode = 3;
	                $neftcharge = $request['indirect_income_neft'];
	            }
	        	$transction_date = date("Y-m-d", strtotime(convertDate($request['indirect_income_date'])));
	            $entry_date = date("Y-m-d", strtotime(convertDate($request['indirect_income_date'])));
	            $entry_time = date("H:i:s");
	            $created_by = 1;
	            $created_by_id = Auth::user()->id;
	            $is_contra = NULL;
	            $contra_id = NULL;
	            $created_at = date("Y-m-d", strtotime(convertDate($request['indirect_income_date'])));
	            $bank_id = NULL;
	            $bank_id = $request['indirect_income_bank_id'];
	            $bank_ac_id = getSamraddhBankAccountId($request['indirect_income_bank_id'])->id;
	            $ssb_account_tran_id_to= NULL;
	            $ssb_account_tran_id_from=NULL;
	        }
		}
		//dd($payemntMode,$request['neft_charge']);
        $dayBookRef = CommanController::createBranchDayBookReference($amount);
        if($payemntMode == 2 && $request['neft_charge'] == 0){
        	$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,28,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

        	$allHeadTransaction2 = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$headId,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$head_payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

        	$branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

        	//$updateBranchCash = $this->updateBranchCashCr($branch_id,$date,$amount,0);

        	//$updateBranchClosing = $this->updateBranchClosingCashCr($branch_id,$date,$amount,0);
        }
        //dd($payemntMode, $request['neft_charge']);
        if($payemntMode == 1 ){

        	/*if($request['advanced'] == 0){
				$bankAmount = $amount;
			}elseif($request['advanced'] > 0){
				$bankAmount = $amount+$neftcharge;
			}*/
			if(isset($request['neft_charge']) && $request['neft_charge']== 1)
			{
				$bankAmount = $amount+$neftcharge;
				
			$branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,'Booking Expense '.$bankAmount,'Cr '.getSamraddhBankAccountId($request['expense_account_no'])->account_no.' '.$bankAmount,'Dr '.getAcountHead($headId).' '.$bankAmount.'','DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

			// $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

    		$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($bank_ac_id)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

    		$samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);
			}
			elseif($request['expense_cheque_no'] != ''){

        	/*if($request['advanced'] == 0){
				$bankAmount = $amount;
			}elseif($request['advanced'] > 0){
				$bankAmount = $amount+$neftcharge;
			}*/
			
			$bankAmount = $amount;

			$branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

    		$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,getSamraddhBank($bank_ac_id)->account_head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$bankAmount,$bankAmount,$bankAmount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

    		$samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef,$bank_id,$bank_ac_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$jv_unique_id,$cheque_type,$cheque_id,$ssb_account_tran_id_to,$ssb_account_tran_id_from);

    		$allHeadTransaction2 = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$headId,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,$head_payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,NULL,NULL,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);


	  	
			}
			

	  	}
	  	//dd($request['type']);
	  	//dd($request['neft_charge']);
	  	if($payemntMode == 1 && $neftcharge > 1 && $neftcharge !='' && $request['neft_charge'] == 1){

			$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,92,$type,$sub_type,$type_id,NULL,$associate_id,$member_id,$branch_id_to,$branch_id_from,$neftcharge,$neftcharge,$neftcharge,'NEFT Charge A/c Cr '.$neftcharge.'',$bank_charge_payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

			if($request['type'] == 2){
				if($request['payemnt_vendor_type'] == 0 && $request['payemnt_vendor_type'] !=''){

					$rentLedger = $this->RentLiabilityLedger($request['payment_vendor_name'],1,$request['payment_vendor_name'],$neftcharge,$neftcharge,$neftcharge,'Bank Charge',$currency_code,$payment_type,$payment_mode,1,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$updated_at=NULL,$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,$jv_type_id=NULL,$bookingId);

				}elseif($request['payemnt_vendor_type'] == 1 && $request['payemnt_vendor_type'] !=''){

					$employeeLedger = $this->createEmployeeLedgers($request['payment_vendor_name'],$branch_id,1,$request['payment_vendor_name'],$neftcharge,$neftcharge,$neftcharge,'Bank Charge',$currency_code,$payment_type,$payment_mode,1,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,$jv_type_id=NULL,$bookingId);

				}elseif($request['payemnt_vendor_type'] == 2 && $request['payemnt_vendor_type'] !=''){

					$vendorBill = $this->createVendorBillPayemnt($branch_id,$request['payment_vendor_name'],$vendor_bill_id=NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_vendor_name'],NULL,NULL,NULL,$neftcharge,NULL,'Bank Charge',$currency_code,$payment_type,$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

				}elseif($request['payemnt_vendor_type'] == 3 && $request['payemnt_vendor_type'] !=''){

					$vendorBill = $this->createVendorBillPayemnt($branch_id,$request['payment_vendor_name'],$vendor_bill_id=NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_vendor_name'],NULL,NULL,NULL,$neftcharge,NULL,'Bank Charge',$currency_code,$payment_type,$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc=NULL,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_vendor_date']))),$transaction_charge=NULL,1,$bookingId);

				}

				if($request['payment_account_payment'] == 2 && $request['payment_account_payment'] !=''){
					$vendorBillPayment = $this->createVendorBillPayemnt($branch_id,$request['payment_customer_name'],NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['payment_customer_name'],NULL,NULL,$neftcharge,$neftcharge,$neftcharge,'Bank Charge',$currency_code,$payment_type,$payment_mode,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['payment_customer_date']))),$transaction_charge=NULL,1,$bookingId);
				}
			}elseif($request['type'] == 4){
				if($request['receive_payment_account_type'] == 2 && $request['receive_payment_account_type'] !=''){
					$vendorBillPayment = $this->createVendorBillPayemnt($branch_id,$request['received_payment_customer_name'],NULL,1,$salary_ledger_id=NULL,$salary_id=NULL,$employee_id=NULL,$request['received_payment_customer_name'],NULL,NULL,$request['received_customer_payment_amount'],$request['received_customer_payment_amount'],$request['received_customer_payment_amount'],'Bank Charge',$currency_code,$payment_type,$payment_mode,date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name=NULL,$to_bank_branch=NULL,$to_bank_ac_no=NULL,$to_bank_ifsc,$to_bank_id=NULL,$to_bank_account_id=NULL,$from_bank_name,$from_bank_branch=NULL,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_id,$cheque_no,$cheque_date,$transaction_no=NULL,date("Y-m-d", strtotime(convertDate($request['received_payment_customer_date']))),$transaction_charge=NULL,1,$bookingId);
				}
			}
		}
		if(isset($request['neft_charge'])){
			if($request['advanced'] == 0 && $request['neft_charge'] == 1 && $request['refund'] == 0){
        	$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$headId,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$head_payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
        	}	
		}
	  	
	}


	public function createVendorBill($branch_id,$vendor_id,$bill_type,$bill_date,$bill_number,$discount_type,$total_tax_per,$total_tax_amount,$total_item,$total_item_amount,$total_discount_type,$total_discount_per,$total_discount_amount,$sub_amount,$tds_head,$tds_type,$tds_per,$tds_amount,$payble_amount,$due_amount,$transferd_amount,$balance,$status,$created_at,$banking_id)
	{
		$vendorBillData['branch_id'] = $branch_id;
		$vendorBillData['vendor_id'] = $vendor_id;
		$vendorBillData['bill_type'] = $bill_type;
		$vendorBillData['bill_date'] = $bill_date;
		$vendorBillData['bill_number'] = $bill_number;
		$vendorBillData['discount_type'] = $discount_type;
		$vendorBillData['total_tax_per'] = $total_tax_per;
		$vendorBillData['total_tax_amount'] = $total_tax_amount;
		$vendorBillData['total_item'] = $total_item;
		$vendorBillData['total_item_amount'] = $total_item_amount;
		$vendorBillData['total_discount_type'] = $total_discount_type;
		$vendorBillData['total_discount_per'] = $total_discount_per;
		$vendorBillData['total_discount_amount'] = $total_discount_amount;
		$vendorBillData['sub_amount'] = $sub_amount;
		$vendorBillData['tds_head'] = $tds_head;
		$vendorBillData['tds_type'] = $tds_type;
		$vendorBillData['tds_per'] = $tds_per;
		$vendorBillData['tds_amount'] = $tds_amount;
		$vendorBillData['payble_amount'] = $payble_amount;
		$vendorBillData['due_amount'] = $due_amount;
		$vendorBillData['transferd_amount'] = $transferd_amount;
		$vendorBillData['balance'] = $balance;
		$vendorBillData['status'] = $status;
		$vendorBillData['banking_id'] = $banking_id;
		$vendorBillData['created_at'] = $created_at;
		$vendorBill = VendorBill::create($vendorBillData);
		return $vendorBill->id;
	}

	public function createVendorBillPayemnt($branch_id,$vendor_id,$vendor_bill_id,$bill_type,$salary_ledger_id,$salary_id,$employee_id,$rent_owner_id,$rent_ledger_id,$rent_id,$balance,$deposit,$withdrawal,$description,$currency_code,$payment_type,$payment_mode,$payment_date,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id_company,$cheque_no_company,$cheque_id_vendor,$cheque_no_vendor,$cheque_date,$transaction_no,$transaction_date,$transaction_charge,$status,$bankingid)
	{
		$data['branch_id'] = $branch_id;
		$data['vendor_id'] = $vendor_id;
		$data['vendor_bill_id'] = $vendor_bill_id;
		$data['bill_type'] = $bill_type;
		$data['salary_ledger_id'] = $salary_ledger_id;
		$data['salary_id'] = $salary_id;
		$data['employee_id'] = $employee_id;
		$data['rent_owner_id'] = $rent_owner_id;
		$data['rent_ledger_id'] = $rent_ledger_id;
		$data['rent_id'] = $rent_id;
		$data['balance'] = $balance;
		$data['deposit'] = $deposit;
		$data['withdrawal'] = $withdrawal;
		$data['description'] = $description;
		$data['currency_code'] = $currency_code;
		$data['payment_type'] = $payment_type;
		$data['payment_mode'] = $payment_mode;
		$data['payment_date'] = $payment_date;
		$data['v_no'] = $v_no;
		$data['v_date'] = $v_date;
		$data['ssb_account_id_to'] = $ssb_account_id_to;
		$data['ssb_account_id_from'] = $ssb_account_id_from;
		$data['to_bank_name'] = $to_bank_name;
		$data['to_bank_branch'] = $to_bank_branch;
		$data['to_bank_ac_no'] = $to_bank_ac_no;
		$data['to_bank_ifsc'] = $to_bank_ifsc;
		$data['to_bank_id'] = $to_bank_id;
		$data['to_bank_account_id'] = $to_bank_account_id;
		$data['from_bank_name'] = $from_bank_name;
		$data['from_bank_branch'] = $from_bank_branch;
		$data['from_bank_ac_no'] = $from_bank_ac_no;
		$data['from_bank_ifsc'] = $from_bank_ifsc;
		$data['from_bank_id'] = $from_bank_id;
		$data['from_bank_ac_id'] = $from_bank_ac_id;
		$data['cheque_id_company'] = $cheque_id_company;
		$data['cheque_no_company'] = $cheque_no_company;
		$data['cheque_id_vendor'] = $cheque_id_vendor;
		$data['cheque_no_vendor'] = $cheque_no_vendor;
		$data['cheque_date'] = $cheque_date;
		$data['transaction_no'] = $transaction_no;
		$data['transaction_date'] = $transaction_date;
		$data['transaction_charge'] = $transaction_charge;
		$data['status'] = $status;
		$data['created_by'] = 1;
		$data['created_by_id'] = Auth::user()->id;
		$data['banking_id'] = $bankingid;
		$res = VendorBillPayment::create($data);
		return $res->id;
	}

	public function createVendorLog($vendor_id,$vendor_bill_id,$title,$description,$created_by,$created_by_id,$created_by_name,$created_at,$banking_id)
	{
		$data['vendor_id'] = $vendor_id;
		$data['vendor_bill_id'] = $vendor_bill_id;
		$data['title'] = $title;
		$data['description'] = $description;
		$data['created_by'] = $created_by;
		$data['created_by_id'] = $created_by_id;
		$data['created_by_name'] = $created_by_name;
		$data['banking_id'] = $banking_id;
		$data['created_at'] = $created_at;
		$res = VendorLog::create($data);
		return $res->id;
	}

	public static function RentLiabilityLedger($rent_liability_id,$type,$type_id,$opening_balance,$deposit,$withdrawal,$description,$currency_code,$payment_type,$payment_mode,$status,$created_at,$updated_at,$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no,$transaction_date,$transaction_charge,$jv_type_id,$bankingid)

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
        $data['jv_unique_id']=$jv_unique_id;
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
        $data['jv_journal_id']=$jv_type_id;
        $data['banking_id'] = $bankingid;
        $transcation = RentLiabilityLedger::create($data);
        return $transcation->id;
    }

    public static function createEmployeeLedgers($employee_id,$branch_id,$type,$type_id,$opening_balance,$deposit,$withdrawal,$description,$currency_code,$payment_type,$payment_mode,$status,$created_at,$jv_unique_id,$v_no,$v_date,$ssb_account_id_to,$ssb_account_id_from,$to_bank_name,$to_bank_branch,$to_bank_ac_no,$to_bank_ifsc,$to_bank_id,$to_bank_account_id,$from_bank_name,$from_bank_branch,$from_bank_ac_no,$from_bank_ifsc,$from_bank_id,$from_bank_ac_id,$cheque_id,$cheque_no,$cheque_date,$transaction_no,$transaction_date,$transaction_charge,$jv_type_id,$bankingid)

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
        $data['jv_unique_id']=$jv_unique_id;
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
        $data['jv_journal_id']=$jv_type_id;
        $data['banking_id'] = $bankingid;
        $transcation = EmployeeLedger::create($data);
        return $transcation->id;
    }

    public static function createCustomerTransaction($type,$sub_type,$type_id,$type_transaction_id,$customer_id,$branch_id,$bank_id,$account_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$banking_id )

    { 
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['customer_id']=$customer_id;
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
        $data['ssb_account_id_from']=$ssb_account_id_from;
        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_from_id']=$cheque_bank_from_id;
        $data['cheque_bank_ac_from_id']=$cheque_bank_ac_from_id;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['cheque_bank_to_name']=$cheque_bank_to_name;
        $data['cheque_bank_to_branch']=$cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no']=$cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc']=$cheque_bank_to_ifsc;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['banking_id'] = $banking_id;
        $transcation = CustomerTransaction::create($data);
        return $transcation->id;
    }

    public static function createAssociateTransaction($type,$sub_type,$type_id,$type_transaction_id,$associate_id,$branch_id,$bank_id,$account_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$banking_id )

    { 
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['associate_id']=$associate_id;
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
        $data['ssb_account_id_from']=$ssb_account_id_from;
        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_from_id']=$cheque_bank_from_id;
        $data['cheque_bank_ac_from_id']=$cheque_bank_ac_from_id;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['cheque_bank_to_name']=$cheque_bank_to_name;
        $data['cheque_bank_to_branch']=$cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no']=$cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc']=$cheque_bank_to_ifsc;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['banking_id'] = $banking_id;
        $transcation = AssociateTransaction::create($data);
        return $transcation->id;
    }

    public static function createAdvancedTransaction($type,$sub_type,$type_id,$type_transaction_id,$bill_id,$total_amount,$used_amount,$branch_id,$bank_id,$account_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$banking_id )

    { 
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['bill_id']=$bill_id;
        $data['total_amount']=$total_amount;
        $data['used_amount']=$used_amount;
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
        $data['ssb_account_id_from']=$ssb_account_id_from;
        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_from_id']=$cheque_bank_from_id;
        $data['cheque_bank_ac_from_id']=$cheque_bank_ac_from_id;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['cheque_bank_to_name']=$cheque_bank_to_name;
        $data['cheque_bank_to_branch']=$cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no']=$cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc']=$cheque_bank_to_ifsc;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['banking_id'] = $banking_id;
        $transcation = AdvancedTransaction::create($data);
        return $transcation->id;
    }

    public static function createVendorTransaction($type,$sub_type,$type_id,$type_transaction_id,$vendor_id,$branch_id,$bank_id,$account_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$credit_node_id,$advance_id,$banking_id)

    { 
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['vendor_id']=$vendor_id;
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
        $data['ssb_account_id_from']=$ssb_account_id_from;
        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_from_id']=$cheque_bank_from_id;
        $data['cheque_bank_ac_from_id']=$cheque_bank_ac_from_id;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['cheque_bank_to_name']=$cheque_bank_to_name;
        $data['cheque_bank_to_branch']=$cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no']=$cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc']=$cheque_bank_to_ifsc;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['credit_node_id'] = $credit_node_id;
        $data['advance_id'] = $advance_id;
        $data['banking_id'] = $banking_id;
        $transcation = VendorTransaction::create($data);
        return $transcation->id;
    }

    public static function createCreditCardTransaction($type,$sub_type,$type_id,$type_transaction_id,$bill_id,$vendor_id,$credit_card_id,$total_amount,$used_amount,$branch_id,$bank_id,$account_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id,$banking_id)

    { 
        $data['type']=$type;
        $data['sub_type']=$sub_type;
        $data['type_id']=$type_id;
        $data['type_transaction_id']=$type_transaction_id;
        $data['bill_id']=$bill_id;
        $data['vendor_id']=$vendor_id;
        $data['credit_card_id']=$credit_card_id;
        $data['total_amount']=$total_amount;
        $data['used_amount']=$used_amount;
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
        $data['ssb_account_id_from']=$ssb_account_id_from;
        $data['ssb_account_id_to']=$ssb_account_id_to;
        $data['cheque_no']=$cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from']=$cheque_bank_from;
        $data['cheque_bank_ac_from']=$cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from']=$cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from']=$cheque_bank_branch_from;
        $data['cheque_bank_from_id']=$cheque_bank_from_id;
        $data['cheque_bank_ac_from_id']=$cheque_bank_ac_from_id;
        $data['cheque_bank_to']=$cheque_bank_to;
        $data['cheque_bank_ac_to']=$cheque_bank_ac_to;
        $data['cheque_bank_to_name']=$cheque_bank_to_name;
        $data['cheque_bank_to_branch']=$cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no']=$cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc']=$cheque_bank_to_ifsc;
        $data['transction_no']=$transction_no;
        $data['transction_bank_from']=$transction_bank_from;
        $data['transction_bank_ac_from']=$transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['banking_id'] = $banking_id;
        $transcation = CreditCradTransaction::create($data);
        return $transcation->id;
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

    public function ledgerTransaction(Request $request)
    {
        $type = $request->type;
        $typeId = $request->typeId;
        if($type == 0){
        	$result=RentPayment::where('rent_liability_id', $typeId)->where('status',0)->get();
        }elseif($type == 1){
        	$result=EmployeeSalary::where('employee_id', $typeId)->where('status',0)->get();
        }elseif($type == 2){
        	$result=CommissionLeaserDetail::where('member_id', $typeId)->where('status',2)->get();
        }elseif($type == 3){
        	$result=VendorBill::where('vendor_id', $typeId)->where('status',0)->get();
        }elseif($type == 4){
        	$result=CreditCradTransaction::where('credit_card_id',$typeId)->whereIN('status', [0,1])->where('payment_type','CR')->get();
        }
		$return_array = compact('result','type');
        return json_encode($return_array);
    }

    public function advancedAmount(Request $request)
    {
    	$accountType = $request->accountType;
        $type = $request->type;
        $typeId = $request->typeId;



        if($accountType == 1 && $type == 0){
        	$result=BankingLedger::where('vendor_type', 0)->where('vendor_type_id',$typeId)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();
        }elseif($accountType == 1 && $type == 1){
        	$result=BankingLedger::where('vendor_type', 1)->where('vendor_type_id',$typeId)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();
        }elseif($accountType == 1 && $type == 2){
        	$result=BankingLedger::where('vendor_type', 2)->where('vendor_type_id',$typeId)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();
        }elseif($accountType == 1 && $type == 3){
        	$result=BankingLedger::where('vendor_type', 3)->where('vendor_type_id',$typeId)->where('advanced_payment_status',0)->where('advanced_amount','>',0)->get();
        }elseif($accountType == 2 && $type == 4){
        	$result=BankingLedger::where('vendor_type', 4)->where('vendor_type_id',$typeId)->where('advanced_payment_status',0)->get();
        }elseif($accountType == 2 && $type == 5){
        	$result=BankingLedger::where('vendor_type', 5)->where('vendor_type_id',$typeId)->whereNull('customer_advanced_payment')->where('customer_refund_payment','>',0)->where('advanced_payment_status',0)->get();
        }
		$return_array = compact('result','type');
        return json_encode($return_array);
    }

    public function getVendorCustomer(Request $request)

    {
        $input = $request->all();
        $result=Vendor::select('id','name')->where('name', 'LIKE', '%'.$input['query'].'%')
        ->where('type',1)->get();

        /**********************/
        $customer = [];

        if (count($result) > 0) {

            foreach ($result as $c) {
                $customer[] = array(
                    "id" => $c->id,
                    "text" => $c->name, ); 
            } 
        } 
        return response ()->json($customer);

    }

    public function getVendors(Request $request)

    {
        $input = $request->all();
        $result=Vendor::select('id','name')->where('name', 'LIKE', '%'.$input['query'].'%')
        ->where('type',0)->get();

        /**********************/
        $customer = [];

        if (count($result) > 0) {

            foreach ($result as $c) {
                $customer[] = array(
                    "id" => $c->id,
                    "text" => $c->name, ); 
            } 
        } 
        return response ()->json($customer);

    }
}



