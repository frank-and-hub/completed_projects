<?php 
namespace App\Http\Controllers\Admin; 

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AccountHeads;
use App\Models\AllHeadTransaction;
use App\Models\Member; 
use App\Models\Branch;
use App\Models\DebitCard; 
use App\Models\Receipt;
use App\Models\ReceiptAmount;
use App\Models\Grouploanmembers;
use App\Models\Memberloans;
use App\Models\Loans;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentspayments;
use App\Models\CorrectionRequests;
use App\Models\MemberTransaction;
use App\Models\DebitCardLog;
use App\Models\SavingAccount;
use App\Models\Employee;
use App\Models\DebitCardTransaction;
use App\Models\SavingAccountTranscation;

use App\Http\Controllers\Admin\CommanController;
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

use Illuminate\Validation\Rule;

class DebitCardController extends Controller{
    public function __construct(){
        // check user login or not
        $this->middleware('auth');
    }

    public function index(){
		if(check_my_permission( Auth::user()->id,"246") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['title']='Debit Cards List ';
		$data['branch'] = Branch::select('id','name')->where('status',1)->get();
        return view('templates.admin.debit_card.index', $data);
    }
	
	public function create(){
		if(check_my_permission( Auth::user()->id,"247") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['br_data'] = Branch::select('id', 'name')->get();
		
		$data['title']='Debit Card | Create'; 
        return view('templates.admin.debit_card.create', ['br_data' => $data['br_data'], 'title' => $data['title']]);
    }
	
	
	public function debit_card_save(Request $request){
        try {
			$validator = Validator::make($request->all(), [
				'card_no' => 'required|digits:16',
				'from_month' => 'required',
				'from_year' => 'required',
				'to_month' => 'required',
				'to_year' => 'required',
				'card_charge' => 'required',
				'ref_no' => 'required',
				'emp_code' => 'required',
				'card_type' => 'required'
			]);
	
			if ($validator->fails()) {
				return redirect('admin/debit-card/create')
					->withErrors($validator)
					->withInput();
			}
			
			// check debit card number already exist or not
			if($request->from_year == date('Y') && $request->from_month > date('m')){
				return back()->with('alert', 'From Month should be Current Month or Less than Current Month!');	
				die;
			}
			
			if($request->to_year == date('Y') && $request->to_month < date('m')){
				return back()->with('alert', 'To Month should be Current Month or Greater than Current Month!');	
				die;
			}
			
			if($request->card_type == 1){
				$debit_details = DebitCard::where('card_no',$request->card_no)->first();
				/*$ssb_Detail = SavingAccount::where('id',$debit_details->ssb_id)->first();
				if($ssb_Detail != NULL){
					return back()->with('alert', 'SSB Account Number Already Exist!');	die;
				}*/
				
				if( $debit_details != NULL ){
					return back()->with('alert', 'Debit Card Already Exist!');	die;
				}
			}
			else{
				$debit_details = DebitCard::where('card_no',$request->card_no)
					->where(function($q){
						$q->orWhere('status', '=' ,2)
						->orWhere('is_block', '=', 2);
					})->first();
				
				if( $debit_details != NULL ){
					return back()->with('alert', 'Debit Card Already Exist!');	die;
				}
			}
			
			$created_by = 1;
			$created_by_id = \Auth::user()->id;
			$created_by_name = \Auth::user()->username;
			
			$member_id = $request->member_id;
			$ssb_id = $request->ssb_id;
			$card_no = $request->card_no;
			$emp_id = $request->emp_id;
			
			$data['card_no'] = $card_no;
			$data['valid_from_month'] = $request->from_month;
			$data['valid_from_year'] = $request->from_year;
			$data['valid_to_month'] = $request->to_month;
			$data['valid_to_year'] = $request->to_year;
			$data['card_charge'] = $request->card_charge;
			$data['payment_mode'] = $request->payment_mode;
			$data['valid_to_year'] = $request->to_year;
			$data['ssb_id'] = $ssb_id;
			$data['member_id'] = $member_id;
			$data['employee_id'] = $emp_id;
			$data['reference_no'] = $request->ref_no;
			$data['issue_date'] = date('Y-m-d');
			$data['issue_time'] = date('H:i:s');
			$data['card_type'] = $request->card_type;
			$data['created_by'] = $created_by;
			$data['created_by_id'] = $created_by_id;
			//$data['ssb_ac'] = $request->ssb_ac;
			//$data['branch_id'] = $request->branch_id;
			//$data['branch_id_ssb'] = $request->branch_id_ssb;
			$amount = $request['card_charge'];
			
			
			$data['branch_id'] = ($request->branch_id != null) ? $request->branch_id : $request->branch_id_ssb;
			//dd($request->all());
			
			// ICICI Bank Beneficiary Register Api 
			
			if($request->user_name&& $member_id&& $card_no){				
				$data['cib_status'] = 0;
				$res = DebitCard::create($data);
	
				$desc = 'Debit Card Charge '.$amount;
				$entry_date = date("Y-m-d");
				$entry_time = date("H:i:s"); 
				$currency_code = 'INR';
				$paymentMode = 4;
				
				$comman_controller = new CommanController;
					

				if($res){
					
					
					// //SavingAccount::where("account_no",$request->ssb_ac)->decrement('balance', $request->card_charge);
					// $ssb_tr_data = SavingAccountTranscation::where('saving_account_id',$ssb_id)
					// 				->orderBy('created_at', 'DESC')->first();
					// $ssb_Detail = SavingAccount::with('ssbMember')->where('account_no',$request['ssb_ac'])->first();
					// //$ssb_Detail = getMemberSsbAccountDetail($request->member_id);
	
					
					// $ssb_tr_id = $comman_controller->ssbTransaction_new($ssb_Detail->id,$ssb_Detail->account_no,$ssb_tr_data->opening_balance, $amount, $desc, 'INR', 'DR', $paymentMode, 14, $request->branch_id_ssb);
					
					// $satRefId = $comman_controller->createTransactionReferences($ssb_tr_id,$ssb_Detail->member_investments_id);
					
					// $amountArraySsb=array('1'=>$amount);
					// $amount_deposit_by_name = $ssb_Detail['ssbMember']->first_name.' '.$ssb_Detail['ssbMember']->last_name;
	
					// /*$ssbCreateTran = $comman_controller->createTransaction($satRefId,22,$ssb_Detail->id,$ssb_Detail->member_id,$request['branch_id_ssb'],$request['branch_code'],$amountArraySsb,$paymentMode,$amount_deposit_by_name,$ssb_Detail->id,$request['ssb_ac'],$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d"),$online_payment_id = NULL,$online_payment_by = NULL,$ssb_Detail->id,'DR');
						
						
					// $createDayBook = $comman_controller->createDayBook($ssbCreateTran,$satRefId,23,$ssb_Detail->member_investments_id,0,$ssb_Detail->member_id,$ssb_Detail->balance,0,$amount,$desc,$request['ssb_account_number'],$request['branch_id_ssb'],$request['branch_code'],$amountArraySsb,$paymentMode,$amount_deposit_by_name,$ssb_Detail->member_investments_id,$request['ssb_ac'],$cheque_dd_no = NULL,NULL,NULL,date("Y-m-d"),$online_payment_id = NULL,$online_payment_by = NULL,$ssb_Detail->id,'DR');*/
					
					// $dayBookRef = $comman_controller->createBranchDayBookReference($amount);
					
					// $created_at = date("Y-m-d H:i:s");
					// $updated_at = date("Y-m-d H:i:s"); 
					// $payment_type = 'DR';
					// $type = 29;
					// $sub_type = 291; //Debit Card Charge type
					// $daybook_ref_id = $dayBookRef;
					
					// $head_id = AccountHeads::where('sub_head','Debit card')->first()->head_id;
					
					// $type_id = $ssb_Detail->id;
					
					// $amount_to_id = $amount_to_name = $amount_from_id = $amount_from_name = $v_no = $v_date = $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = $is_contra = $contra_id = $cheque_bank_from_id = $cheque_bank_ac_from_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = NULL;
					
					// $brDaybook=CommanController::branchDayBookNew($daybook_ref_id,$request['branch_id_ssb'],$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$desc,$description_dr=NULL,$description_cr=NULL,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
					
					// //$paymentMode = ($request->payment_mode == 1) ? 3 : 0;
					
					// $allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$request['branch_id_ssb'],$bank_id=NULL,$bank_ac_id=NULL,$head_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$desc,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
					// $allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$request['branch_id_ssb'],$bank_id=NULL,$bank_ac_id=NULL,56,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$desc,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
					// $memTran=CommanController::memberTransactionNew($daybook_ref_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$request['branch_id_ssb'],$bank_id=NULL,$account_id=NULL,$amount,$desc,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
					
					// debit_card_logs($res->id,$daybook_ref_id, $member_id, $ssb_id, $card_no, "add", $emp_id, 1);
					return redirect()->route('admin.debit-card')->with('success','Debit Card Created Successfully!');
				} 
				else{
					return back()->with('alert', 'Problem With Creating Credit Card');
				}
			}
			else{
				return back()->with('alert', 'Error: SomeThing Went wrong');
			}
		} 
		catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
	}
	
	
	
	public function debit_card_listing(Request $request){ 
        if(check_my_permission( Auth::user()->id,"246") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$currentdate = date('Y-m-d');
        if ($request->ajax()) {
			if(!empty($_POST['searchform'])){
				foreach($_POST['searchform'] as $frm_data){
					$arrFormData[$frm_data['name']] = $frm_data['value'];
            	}
			}
			
			$search = $_POST['search']['value'];
			$where = '(debit_card.card_no LIKE "%'.$search.'%")';
			
			//$data = DebitCard::whereRaw($where);
			//$data=$data->offset($_POST['start'])->limit($_POST['length'])->get();
			
			//'id','payment_mode','card_no','br_name','branch_code','card_type','valid_from_month','valid_from_year','valid_to_month','valid_to_year','account_no','first_name','last_name','approve_date','reference_no','employee_code','employee_name','status','ssb_id','is_block'
			if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
			{
					$data = DebitCard::has('company')->whereRaw($where)
					->select('debit_card.*', 't2.account_no', 't3.member_id as member_code', 't3.first_name', 't3.last_name', 't4.employee_code', 't4.employee_name', 't5.name as br_name', 't5.branch_code')
					->leftjoin('saving_accounts AS t2', 'debit_card.ssb_id', '=', 't2.id')
					->leftjoin('members AS t3', 'debit_card.member_id', '=', 't3.id')
					->leftjoin('employees AS t4', 'debit_card.employee_id', '=', 't4.id')
					->leftjoin('branch as t5', 'debit_card.branch_id', '=', 't5.id');
				
				
				
					if($arrFormData['card_no'] !=''){
						$card_no=$arrFormData['card_no'];
						$data=$data->where('debit_card.card_no','=',$card_no);
					}
					
					if($arrFormData['status'] !=''){
						$status=$arrFormData['status'];
						
						if($status > 2){
							switch($status){
								case 3: $sta_val = 2; break;
								case 4: $sta_val = 1; break;
							}
							$data=$data->where('debit_card.is_block','=',$sta_val);
						}
						elseif($status == 1){
							$data=$data->where('debit_card.status','=',$status)->where('debit_card.is_block','=',NULL);
						}
						elseif($status == 2){
							$data=$data->where('debit_card.status','=',$status)->where('debit_card.is_block','=',NULL);
						}
						else{
							$data=$data->where('debit_card.status','=',$status);
						}
					}
					if($arrFormData['branch_id'] !=''){
						$branch_id=$arrFormData['branch_id'];
						$data=$data->where('debit_card.branch_id','=',$branch_id);
					}

					if($arrFormData['ssb_ac'] !=''){
						$ssb_ac = $arrFormData['ssb_ac'];
						$data=$data->where('t2.account_no','=',$ssb_ac);
					}
				
				
				$count = $data->count('debit_card.id');
				$totalCount = $count;

				$data=$data->offset($_POST['start'])->limit($_POST['length'])->get();
						

				$sno=$_POST['start'];
				$rowReturn = array(); 
				
				foreach ($data as $row){
					$sno++;
					$payment_mode = ($row->payment_mode != 1) ? 'Cash' : 'SSB';		
					$val['DT_RowIndex']=$sno;
					$val['issue_date']=date('d/m/Y', strtotime($row->issue_date));
					$val['card_no']=$row->card_no;
					$val['br_name']=$row->br_name;
					$val['branch_code']=$row->branch_code;
					$val['card_type'] = $row->card_type == 1 ? "New" : "Reissue";
					$val['valid_from']=$row->valid_from_month."/".$row->valid_from_year;
					$val['valid_to']=$row->valid_to_month."/".$row->valid_to_year;
					$val['mem_ssb_ac']=$row->account_no;
					$val['mem_name']=$row->first_name." ".$row->last_name;
					$val['app_date']=$row->approve_date == NULL ? '----' : date('d/m/Y H:i:s', strtotime($row->approve_date));
					$val['ref_no']=$row->reference_no;
					$val['emp_code']=$row->employee_code;
					$val['emp_name']=$row->employee_name;
					
					
					$val['action']= "";
					
					switch($row->status){
						case 0 : $status = "<span class='badge bg-warning'>Pending</span>"; break;
						case 1 : $status = "<span class='badge bg-success'>Approved</span>"; break;
						case 2 : $status = "<span class='badge bg-danger'>Rejected</span>"; break;
					}
					
					$block_btn = '';
					if($row->status == 1){
						$block_btn = '<button class="dropdown-item reject_block" data-row-id="'.$row->id.'" data-type="3" title="Block Debit Card"><i class="icon-snowflake mr-2"></i> Block</button>';
						switch($row->is_block){
							case 1 : $block_btn = '<button class="dropdown-item reject_block" data-row-id="'.$row->id.'" data-type="3" title="Block Debit Card"><i class="icon-snowflake mr-2"></i> Block</button>';
									$status = "<span class='badge bg-success'>Approved</span>";
							break;
							case 2 : 
									$block_btn = '<button class="dropdown-item actionDebitCardgg" data-row-id="'.$row->id.'" data-type="4" title="Unblock Debit Card"><i class="icon-snowflake mr-2"></i> Approve</button>';	
									$status = "<span class='badge bg-danger'>Blocked</span>";
							break;
						}
					}
					
					$val['status'] = $status;
					$url1 = URL::to("admin/debit-card/edit/".$row->id."");  
					
					$btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
					
					if($row->status == 0 && ($row->status != 1 or $row->status != 2)){
						/*$btn .= '<a class="dropdown-item" href="'.$url1.'" title="Debit Card Edit">
							<i class="icon-pencil5  mr-2"> Edit</i></a>';*/
						$btn .= '<button onclick=location.href="'.$url1.'" class="dropdown-item" title="Edit Debit Card"><i class="icon-pencil5 mr-2"></i> Edit</button>';	
						//$btn .= '<button class="dropdown-item deleteDebitCard" data-row-id="'.$row->id.'" title="Delete Debit Card"><i class="icon-box mr-2"></i> Delete</button>';
					}
					
					$globaldate = Session::get('created_at');
					$app_rej_btn = '<button class="dropdown-item actionDebitCard" data-date="'.$globaldate.'" data-row-id="'.$row->id.'" data-type="1" title="Approve Debit Card"><i class="icon-snowflake mr-2"></i> Approvedd</button>
					<button class="dropdown-item reject_block" data-row-id="'.$row->id.'" data-type="2" title="Reject Debit Card"><i class="icon-pulse2 mr-2"></i> Reject</button>';
					
					if($row->status == 1 or $row->status == 2){
						$app_rej_btn = "";
					}
					/*if($row->status == 1){
						$rej_btn = '<button class="dropdown-item actionDebitCard" data-row-id="'.$row->id.'" data-type="2" title="Reject Debit Card"><i class="icon-pulse2 mr-2"></i> Reject</button>';
						$app_btn = '<button class="dropdown-item actionDebitCard" data-row-id="'.$row->id.'" data-type="1" title="Approve Debit Card"><i class="icon-snowflake mr-2"></i> Approve</button>';
					}*/
					/*$actionclass = "actionDebitCard";
					if($row->status == 1 or $row->status == 2){
						$actionclass = "";
					}
					$btn .= '<button class="dropdown-item '.$actionclass.'" data-row-id="'.$row->id.'" data-type="1" title="Approve Debit Card"><i class="icon-snowflake mr-2"></i> Approve</button>';
					$btn .= '<button class="dropdown-item '.$actionclass.'" data-row-id="'.$row->id.'" data-type="2" title="Reject Debit Card"><i class="icon-pulse2 mr-2"></i> Reject</button>';*/
					
					$btn .= $app_rej_btn;
					
					$btn .= $block_btn;
					
					if($row->status == 1){
						$btn .= '<a href="admin/debit-card/card-history/'.$row->id.'" class="dropdown-item" title="View Transactions" target="_blank"><i class="icon-eye mr-2"></i> View Transactions</a>';
					}
					
					$btn .= '<a href="admin/debit-card/ssb-history/'.$row->ssb_id.'" class="dropdown-item" title="View Account Details" target="_blank"><i class="icon-eye mr-2"></i> View Account Details</a>';
					
					$btn .= '</div></div></div>';
					
					/*if(check_my_permission( Auth::user()->id,"178") == "1"){
						$val['action'] .= '<a class="btn bg-dark legitRipple" href="admin/debit-card/edit/'.$row->id.'" title="Edit Debit Card"><i class="fa fa-edit"></i></a> &nbsp';
					}
					if(check_my_permission( Auth::user()->id,"179") == "1"){
						$val['action'] .= '<button class="btn bg-dark legitRipple deleteCreditCard" data-row-id="'.$row->id.'" title="Delete Debit Card"><i class="fa fa-trash"></i></button> &nbsp';
					}*/
					$val['action']=$btn;
					$rowReturn[] = $val; 
				} 
				$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

				return json_encode($output);
			}
			else
			{
				$output = array(
				   
					"draw" =>0,
					"recordsTotal" => 0,
					"recordsFiltered" =>0,
					"data" => 0,
				);
				return json_encode($output);
			}   
        }
	}
	
	
	public function ssb_detail_show(Request $request){ 
		$debit_card_charge = 400;
		$ssb_min_bal = 500+$debit_card_charge;
		if($request->ajax()) {
			$ssb_ac = $request->ssb_ac;
			$ssbDetals=SavingAccount::
				select('saving_accounts.id', 'saving_accounts.member_id', 'saving_accounts.associate_id', 'saving_accounts.branch_id', DB::raw('DATE_FORMAT(saving_accounts.created_at, "%d/%m/%Y") as create_date'), 'saving_accounts.account_no', 't2.first_name', 't2.last_name', 't2.member_id AS member_code', 't2.photo', 't3.opening_balance', 't4.branch_code', 't4.name AS branch_name', 't5.first_name AS ass_name', 't5.member_id AS ass_code')
				->leftjoin('members AS t2', 'saving_accounts.member_id', '=', 't2.id')
				->leftjoin('saving_account_transctions AS t3', 'saving_accounts.id', '=', 't3.saving_account_id')
				->leftjoin('branch AS t4', 'saving_accounts.branch_id', '=', 't4.id')
				->leftjoin('members AS t5', 'saving_accounts.associate_id', '=', 't5.id')
				->where('saving_accounts.account_no',$ssb_ac)
				->orderBy('t3.id', 'DESC')
				->get();
				$state =Branch::where('id',$ssbDetals[0]->branch_id)->pluck('state_id')->toArray();

			$debit_cnt = DebitCard::
						leftjoin('saving_accounts as t2', 'debit_card.ssb_id', '=', 't2.id')
						->where('t2.account_no',$ssb_ac)
						->where('debit_card.is_block',1)
						->Where('debit_card.status','!=','2')->count();	
			/*$debit_cnt = DebitCard::
						leftjoin('saving_accounts as t2', 'debit_card.ssb_id', '=', 't2.id')
						->where('t2.account_no',$ssb_ac)
						->where(function($q){
							$q->orWhere('debit_card.is_block',NULL)
							->orWhere('debit_card.is_block', '=' ,1)
							->orWhere('debit_card.status','!=','2');
						})
						
						->orderBy('debit_card.id', 'DESC')->limit(1)->count();*/
					
			$debit_exist = DebitCard::
				leftjoin('saving_accounts as t2', 'debit_card.ssb_id', '=', 't2.id')
				->where('t2.account_no',$ssb_ac)->count();
				$head_id = AccountHeads::where('sub_head','Debit card')->first()->head_id;
				$stateid = 33;
        		 $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
				//$globaldate = date('Y-m-d',strtotime('2022-07-01'));
				$getHeadSetting = \App\Models\HeadSetting::where('head_id',$head_id)->first(); 
				$getGstSetting = \App\Models\GstSetting::where('state_id',$state[0])->where('applicable_date','<=',$globaldate)->exists();
		
				$gstAmount = 0;  
				
				//Gst Insuramce 
				if(isset($getHeadSetting->gst_percentage) &&  $getGstSetting )
				{
					if($state[0] == 33)
					{
						$gstAmount =  ceil(($debit_card_charge*$getHeadSetting->gst_percentage)/100)/2;
					
						$IntraState = true;
					}
					else{
						$gstAmount =  ceil($debit_card_charge*$getHeadSetting->gst_percentage)/100;
					
						$IntraState = false;
					}
					$percentage = $getHeadSetting->gst_percentage;
					
				}
				else{
					$gstAmount =0;
					$IntraState = '';
					$percentage =0;
				}
			//dd($query);
			//dd(count($ssbDetals));	
			/*$ssbDetals=DB::table('saving_accounts AS t1')->where('account_no',$ssb_ac)
				->leftjoin('members AS t2', 't1.member_id', '=', 't2.id')
				->select('t1.id', 't1.member_id', 't1.associate_id', 't1.branch_id', 't1.created_at', 't2.first_name', 't2.member_id')
				->get();*/	
			//dd($ssbDetals);

            $count=count($ssbDetals);
			$output = array("data" => $ssbDetals, "tot_cnt" => $count, "debit_card_charge" => $debit_card_charge, "debit_cnt" => $debit_cnt, "ssb_min_bal" => $ssb_min_bal, "debit_exist" => $debit_exist,"gstAmount"=>$gstAmount,'IntraState'=>$IntraState,'percentage'=>$percentage);
			//$output = $ssbDetals;
			echo json_encode($output);
		}
	}
	
	public function delete_debit_card(Request $request){
		$response = array();
		if(isset($request->table_id) && $request->table_id != "" ){
			$data = DebitCard::where("id",$request->table_id)->first();
			
			debit_card_logs($data->id,NUll, $data->member_id, $data->ssb_id, $data->card_no,"delete", $data->employee_id,1);
			
			DebitCard::where("id",$request->table_id)->delete();

			$response["status"] = "1";
			$response["message"] = "Debit Card Deleted Successfully.";
				
			
		} else {
			$response["status"] = "0";
			$response["message"] = "Debit Card Id Not Found";
		}
		echo json_encode($response); die;
    }
	
	public function approve_reject_debit_card(Request $request){
		$dates = date('Y-m-d',strtotime(convertDate($request->date)));
		
		Session::put('created_at', $dates);

		$debit_card_charge = 400;
		$response = array();
		DB::beginTransaction();
        try {
		if(isset($request->table_id) && $request->table_id != "" ){
			$data = DebitCard::where("id",$request->table_id)->first();

			$msg = $status = $type = '';
			switch($request->type){
				case 1 : 
						$msg = "Debit Card Approved Successfully."; $type = "approve";
						$datas1 = array('status' => 1, 'approve_date' => date('Y-m-d H:i:s'));
				break;
				case 2 : 
						$msg = "Debit Card Rejected Successfully."; $type = "reject";
						$datas1 = array('status' => 2, 'reason' => $request->reason, 'reject_block_date' => date('Y-m-d H:i:s'));
				break;
				case 3 : 
						$msg = "Debit Card Blocked Successfully."; $type = "block";
						$datas1 = array('is_block' =>2, 'reason' => $request->reason, 'reject_block_date' => date('Y-m-d H:i:s'));
				break;
				case 4 : 
						$msg = "Debit Card Approved Successfully."; $type = "unblock";
						$datas1 = array('is_block' => 1,'status' => 1);
				break;
			}
			
			debit_card_logs($data->id,NULL, $data->member_id, $data->ssb_id, $data->card_no, $type, $data->employee_id,1);
			
			$res = DebitCard::where('id', $request->table_id)->update($datas1);
			$head_id = AccountHeads::where('sub_head','Debit card')->first()->head_id;
	
			
			$amount = $debit_card_charge;
			$desc = 'Refund Debit Card Charges on Reject Request '.$amount;
			$descA = ' Debit Card Issue Charges '.$amount;
			$entry_date = date("Y-m-d");
			$entry_time = date("H:i:s"); 
			$currency_code = 'INR';
			$paymentMode = 4;
			$currency_code = 'INR';
			$comman_controller = new CommanController;

			$created_by = 1;
			$created_by_id = \Auth::user()->id;
			$created_by_name = \Auth::user()->username;
			$created_at = date("Y-m-d H:i:s");
			$updated_at = date("Y-m-d H:i:s"); 
			if($res){
				if($request->type == 2){
					$comman_controller = new CommanController;
					$ssb_tr_data = SavingAccountTranscation::where('saving_account_id',$data->ssb_id)
									->orderBy('id', 'DESC')->first();
					
					$ssb_Detail = SavingAccount::where('id',$data->ssb_id)->first();
					
					$ssb_tr_id = $comman_controller->ssbTransaction_new($ssb_Detail->id,$ssb_Detail->account_no,$ssb_tr_data->opening_balance, $amount, $desc, 'INR', 'CR', 4, 14, $request->table_id);
					
					$satRefId = $comman_controller->createTransactionReferences($ssb_tr_id,$ssb_Detail->member_investments_id);
					
					$amountArraySsb=array('1'=>$amount);
					$amount_deposit_by_name = $ssb_Detail['ssbMember']->first_name.' '.$ssb_Detail['ssbMember']->last_name;
	
					$dayBookRef = $comman_controller->createBranchDayBookReference($amount);
					
					$created_by = 1;
					$created_by_id = \Auth::user()->id;
					$created_by_name = \Auth::user()->username;
					$created_at = date("Y-m-d H:i:s");
					$updated_at = date("Y-m-d H:i:s"); 
					$payment_type = 'DR';
					$type = 29;
					$sub_type = 291; //Debit Card Charge type
					$daybook_ref_id = $dayBookRef;
					
					
					$type_id = $ssb_Detail->id;
					
					$amount_to_id = $amount_to_name = $amount_from_id = $amount_from_name = $v_no = $v_date = $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = $is_contra = $contra_id = $cheque_bank_from_id = $cheque_bank_ac_from_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = NULL;
					
					$brDaybook=CommanController::branchDayBookNew($daybook_ref_id,$data->branch_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$desc,$description_dr=NULL,$description_cr=NULL,'CR',4,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
					
					//$paymentMode = ($request->payment_mode == 1) ? 3 : 0;
					
					$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$data->branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$desc,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
					$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$data->branch_id,$bank_id=NULL,$bank_ac_id=NULL,56,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$desc,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
					$memTran=CommanController::memberTransactionNew($daybook_ref_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$data->branch_id,$bank_id=NULL,$account_id=NULL,$amount,$desc,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
				}
				
				$response["status"] = "1";
				$response["message"] = $msg;
			}
		
			if($request->type == 1)
			{
				//SavingAccount::where("account_no",$request->ssb_ac)->decrement('balance', $request->card_charge);
					$ssb_id = $data->ssb_id;
					$ssb_tr_data = SavingAccountTranscation::where('saving_account_id',$ssb_id)
									->orderBy('created_at', 'DESC')->first();
					$ssb_Detail = SavingAccount::with(['ssbMember','savingBranch'])->where('account_no',$ssb_tr_data->account_no)->first();
					//$ssb_Detail = getMemberSsbAccountDetail($request->member_id);
	
					
					$ssb_tr_id = $comman_controller->ssbTransaction_new($ssb_Detail->id,$ssb_Detail->account_no,$ssb_tr_data->opening_balance, $amount, $descA, 'INR', 'DR', $paymentMode, 14, $request->branch_id_ssb);
					$stateid = getBranchState($ssb_Detail['savingBranch']->name);
        			$globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
					$getHeadSetting = \App\Models\HeadSetting::where('head_id',$head_id)->first(); 
					$getGstSetting = \App\Models\GstSetting::where('state_id',$ssb_Detail->savingBranch->state_id)->where('applicable_date','<=',$globaldate)->exists();
					$gstAmount = 0;  
					$getGstSettingno = \App\Models\GstSetting::select('id','gst_no')->where('state_id',$ssb_Detail->savingBranch->state_id)->where('applicable_date','<=',$globaldate)->first();
				
					if(isset($getHeadSetting->gst_percentage) &&  $getGstSetting )
					{
						if($ssb_Detail['savingBranch']->state_id == 33)
						{
							$gstAmount =  ceil(($amount*$getHeadSetting->gst_percentage)/100)/2;
							$cgstHead = 171;
							$sgstHead = 172;
							$IntraState = true;
						}
						else{
							
							$gstAmount =  ceil($amount*$getHeadSetting->gst_percentage)/100;
							$cgstHead = 170;
							$IntraState = false;
						}
						
						
					}
					else{
						$IntraState = false;
					}
					
					if($gstAmount > 0){
						if($IntraState)
						{
							$ssb_tr_id_cgst = $comman_controller->ssbTransaction_new($ssb_Detail->id,$ssb_Detail->account_no,$ssb_tr_data->opening_balance - ($amount ), $gstAmount, 'Debit CGST charge', 'INR', 'DR', $paymentMode, 15, $request->branch_id_ssb,$entry_date);
							$ssb_tr_id_sgst = $comman_controller->ssbTransaction_new($ssb_Detail->id,$ssb_Detail->account_no,$ssb_tr_data->opening_balance-($amount + $gstAmount ), $gstAmount, 'Debit SGST charge', 'INR', 'DR', $paymentMode, 16, $request->branch_id_ssb,$entry_date);
						}
						else{
							$ssb_tr_id_igst = $comman_controller->ssbTransaction_new($ssb_Detail->id,$ssb_Detail->account_no,$ssb_tr_data->opening_balance - ($amount + $gstAmount), $gstAmount, 'Debit IGST charge', 'INR', 'DR', $paymentMode, 17, $request->branch_id_ssb,$entry_date);
						}
					}
					
					
					$satRefId = $comman_controller->createTransactionReferences($ssb_tr_id,$ssb_Detail->member_investments_id);
					
					$amountArraySsb=array('1'=>$amount);
					$amount_deposit_by_name = $ssb_Detail['ssbMember']->first_name.' '.$ssb_Detail['ssbMember']->last_name;
					


					//Gst Insuramce 
					/*$ssbCreateTran = $comman_controller->createTransaction($satRefId,22,$ssb_Detail->id,$ssb_Detail->member_id,$request['branch_id_ssb'],$request['branch_code'],$amountArraySsb,$paymentMode,$amount_deposit_by_name,$ssb_Detail->id,$request['ssb_ac'],$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d"),$online_payment_id = NULL,$online_payment_by = NULL,$ssb_Detail->id,'DR');
						
						
					$createDayBook = $comman_controller->createDayBook($ssbCreateTran,$satRefId,23,$ssb_Detail->member_investments_id,0,$ssb_Detail->member_id,$ssb_Detail->balance,0,$amount,$desc,$request['ssb_account_number'],$request['branch_id_ssb'],$request['branch_code'],$amountArraySsb,$paymentMode,$amount_deposit_by_name,$ssb_Detail->member_investments_id,$request['ssb_ac'],$cheque_dd_no = NULL,NULL,NULL,date("Y-m-d"),$online_payment_id = NULL,$online_payment_by = NULL,$ssb_Detail->id,'DR');*/
						
					$dayBookRef = $comman_controller->createBranchDayBookReference($amount);
					
					$created_at = date("Y-m-d H:i:s");
					$updated_at = date("Y-m-d H:i:s"); 
					$payment_type = 'DR';
					$type = 29;
					$sub_type = 291; //Debit Card Charge type
					$daybook_ref_id = $dayBookRef;
					
					$head_id = AccountHeads::where('sub_head','Debit card')->first()->head_id;
					
					$type_id = $ssb_Detail->id;
					
					$amount_to_id = $amount_to_name = $amount_from_id = $amount_from_name = $v_no = $v_date = $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = $is_contra = $contra_id = $cheque_bank_from_id = $cheque_bank_ac_from_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = NULL;
					
					$brDaybook=CommanController::branchDayBookNew($daybook_ref_id,$ssb_Detail['branch_id'],$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$descA,$description_dr=NULL,$description_cr=NULL,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
					
					//$paymentMode = ($request->payment_mode == 1) ? 3 : 0;
					
					$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,$head_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$descA,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
					$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,56,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$descA,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
					$memTran=CommanController::memberTransactionNew($daybook_ref_id,$type,$sub_type,$type_id,$ssb_tr_id,$associate_id=NULL,$ssb_Detail->member_id,$ssb_Detail['branch_id'],$bank_id=NULL,$account_id=NULL,$amount,$descA,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
					if($gstAmount > 0 )
					{
						
						if($IntraState)
						{
							$descA = ($getHeadSetting->gst_percentage/2).'CGST on Debit Card';
							$descB =  ($getHeadSetting->gst_percentage/2).'SGST on Debit Card';

							$brDaybook=CommanController::branchDayBookNew($daybook_ref_id,$ssb_Detail['branch_id'],29,293,$type_id,$ssb_tr_id_cgst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,'Debit Card Cgst Charge',$description_dr=NULL,$description_cr=NULL,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);

							$brDaybook=CommanController::branchDayBookNew($daybook_ref_id,$ssb_Detail['branch_id'],29,294,$type_id,$ssb_tr_id_sgst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descB,$description_dr=NULL,$description_cr=NULL,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
					
	
						
	
							$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,$cgstHead,29,293,$type_id,$ssb_tr_id_cgst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descA,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
							$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,56,29,294,$type_id,$ssb_tr_id_sgst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descA,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

							$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,$sgstHead,29,294,$type_id,$ssb_tr_id_sgst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descB,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
					
							$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,56,29,294,$type_id,$ssb_tr_id_sgst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descB,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

							$memTran=CommanController::memberTransactionNew($daybook_ref_id,29,293,$type_id,$ssb_tr_id_cgst,$associate_id=NULL,$ssb_Detail->member_id,$ssb_Detail['branch_id'],$bank_id=NULL,$account_id=NULL,$gstAmount,$descA,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);

							$memTran=CommanController::memberTransactionNew($daybook_ref_id,29,294,$type_id,$ssb_tr_id_sgst,$associate_id=NULL,$ssb_Detail->member_id,$ssb_Detail['branch_id'],$bank_id=NULL,$account_id=NULL,$gstAmount,$descB,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
		
							
						}
	
						else{
	
							$descA =  ($getHeadSetting->gst_percentage).'IGST on Debit Card';
							
							$brDaybook=CommanController::branchDayBookNew($daybook_ref_id,$ssb_Detail['branch_id'],29,295,$type_id,$ssb_tr_id_igst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,'Debit Card Igst Charge',$description_dr=NULL,$description_cr=NULL,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
						
						
							
	

							$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,$cgstHead,29,295,$type_id,$ssb_tr_id_igst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descA,'CR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
							
							$allTranSSB=CommanController::createAllHeadTransaction($daybook_ref_id,$ssb_Detail['branch_id'],$bank_id=NULL,$bank_ac_id=NULL,56,29,295,$type_id,$ssb_tr_id_igst,$associate_id=NULL,$ssb_Detail->member_id,$branch_id_to=NULL,$branch_id_from=NULL,$gstAmount,$gstAmount,$gstAmount,$descA,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
							

							$memTran=CommanController::memberTransactionNew($daybook_ref_id,29,295,$type_id,$ssb_tr_id_igst,$associate_id=NULL,$ssb_Detail->member_id,$ssb_Detail['branch_id'],$bank_id=NULL,$account_id=NULL,$gstAmount,$descA,'DR',3,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
							
						}
					

						$createdGstTransaction = CommanController::gstTransaction($dayBookId = $daybook_ref_id,$getGstSettingno->gst_no,(!isset($ssb_Detail->gst_no)) ? NULL :$ssb_Detail->gst_no,$amount,$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState== true ? $gstAmount : 0),($IntraState == true) ? $amount + $gstAmount + $gstAmount :$amount + $gstAmount,203,$entry_date,'DC203',$ssb_Detail->id,$ssb_Detail['branch_id']);  

						
					} 
			}
		} 
		else {
			$response["status"] = "0";
			$response["message"] = "Debit Card Id Not Found";
		}
		echo json_encode($response); 
		DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage());
		}
    }
	
	
	public function edit($id){
		$title='Edit Debit Card'; 
		$debit_details = 
			DebitCard::
				select('debit_card.*', 't2.account_no', DB::raw('DATE_FORMAT(t2.created_at, "%d/%m/%Y") as create_date'), 't3.first_name', 't3.member_id AS member_code', 't3.photo', 't4.branch_code', 't4.name AS branch_name', 't5.first_name AS ass_name', 't5.member_id AS ass_code', 't6.opening_balance', 't7.employee_code', 't7.employee_name', 't7.id as emp_id')
				->leftjoin('saving_accounts AS t2', 'debit_card.ssb_id', '=', 't2.id')
				->leftjoin('members AS t3', 'debit_card.member_id', '=', 't3.id')
				->leftjoin('branch AS t4', 'debit_card.branch_id', '=', 't4.id')
				->leftjoin('members AS t5', 't2.associate_id', '=', 't5.id')
				->leftjoin('saving_account_transctions AS t6', 't2.id', '=', 't6.saving_account_id')
				->leftjoin('employees AS t7', 'debit_card.employee_id', '=', 't7.id')
				->where('debit_card.id',$id)
				->orderBy('t6.id', 'DESC')
				->get();
				
				$ssbDetail = SavingAccount::where('id',$debit_details[0]->ssb_id)->first();
				
				$head_id = AccountHeads::where('sub_head','Debit card')->first()->head_id;
				 $stateid = 33;
        		 $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
				//$globaldate = date('Y-m-d',strtotime('2022-07-01'));
				$getHeadSetting = \App\Models\HeadSetting::where('head_id',$head_id)->first(); 
				$state =Branch::where('id',$ssbDetail->branch_id)->pluck('state_id')->toArray();
				$getGstSetting = \App\Models\GstSetting::where('state_id',$state[0])->where('applicable_date','<=',$globaldate)->exists();
			
				$gstAmount = 0;  
				$debit_card_charge = 400;
				//Gst Insuramce 
				if(isset($getHeadSetting->gst_percentage) &&  $getGstSetting )
				{
					if($state[0] == 33)
					{
						$gstAmount =  ceil(($debit_card_charge*$getHeadSetting->gst_percentage)/100)/2;
					
						$IntraState = true;
					}
					else{
						$gstAmount =  ceil($debit_card_charge*$getHeadSetting->gst_percentage)/100;
					
						$IntraState = false;
					}
					$percentage = $getHeadSetting->gst_percentage;
					
				}
				else{
					$gstAmount =0;
					$IntraState = '';
					$percentage =0;
				}
		//dd($data);
		/*if(check_my_permission( Auth::user()->id,"177") != "1"){
		  return redirect()->route('admin.dashboard');
		}*/
		$br_data = Branch::select('id', 'name')->get();
        return view('templates.admin.debit_card.edit', compact('debit_details', 'br_data', 'title','gstAmount','IntraState','percentage'));
    }
	
	public function debit_card_update(Request $request){
		$validator = Validator::make($request->all(), [
            //'card_no' => 'required|digits:16',
            'from_month' => 'required',
			'from_year' => 'required',
			'to_month' => 'required',
			'to_year' => 'required',
			'card_charge' => 'required',
			'card_type' => 'required',
			'ref_no' => 'required',
			'emp_code' => 'required',
			'card_no' => 'required|unique:debit_card,card_no,'.$request->table_id,
			
        ]);

        if ($validator->fails()) {
            return redirect('admin/debit-card/edit/'.$request->table_id)
                ->withErrors($validator)->withInput();
        }
		
		if($request->from_year == date('Y') && $request->from_month > date('m')){
			return back()->with('alert', 'From Month should be Current Month or Less than Current Month!');	
			die;
		}
		
		if($request->to_year == date('Y') && $request->to_month < date('m')){
			return back()->with('alert', 'To Month should be Current Month or Greater than Current Month!');	
			die;
		}
		// check debit card number already exist or not
		$debit_details = DebitCard::where('card_no',$request->card_no)->where('id', '!=', $request->table_id)->get();
		
		if( count($debit_details) > 0){
			return back()->with('alert', 'Debit Card Already Exist!');	die;
		}
		
		$branch_id = ($request->branch_id != null) ? $request->branch_id : $request->branch_id_ssb;
		
		$member_id = $request->member_id;
		$ssb_id = $request->ssb_id;
		$card_no = $request->card_no;
		$emp_id = $request->emp_id;
		
		$debitdata = 
			array("card_no" => trim($card_no),
				"valid_from_month" => $request->from_month,
				"valid_from_year" => $request->from_year,
				"valid_to_month" => $request->to_month,
				"valid_to_year" => $request->to_year,
				"card_charge" => $request->card_charge,
				"payment_mode" => $request->payment_mode,
				"branch_id" => $branch_id,
				"ssb_id" => $ssb_id,
				"member_id" => $member_id,
				"employee_id" => $request->emp_id,
				"reference_no" => $request->ref_no,
				"card_type" => $request->card_type,
				"updated_at" => date('Y-m-d H:i:s')
			);
			
			
		$res = DebitCard::where("id",$request->table_id)->update($debitdata);
		
		if($res){
			debit_card_logs($request->table_id,Null, $member_id, $ssb_id, $card_no, "update", $emp_id,1);
			
			return redirect()->route('admin.debit-card')->with('success', 'Debit Card Updated Successfully!');
		} 

		else{
			return back()->with('alert', 'Problem With Updating Credit Card');
		}
	}
	
	public function emp_detail_show(Request $request){ 
		if($request->ajax()) {
			$emp_code = $request->emp_code;
			if($emp_code){
				$res = Employee::where("employee_code",$emp_code)->first();
				
				if($res != NULL){
					$data["employee_name"] = $res->employee_name;
					$data["employee_id"] = $res->id;
					$data["status"] = "1";
					//$output = array("data" => $data);
				}
				else{
					$data["status"] = "0";
				}
			}
			else{
				$data["status"] = "0";
			}
			return json_encode($data);
		}
	}
	
	public function card_history(){ 
		if(check_my_permission( Auth::user()->id,"248") != "1"){
		  return redirect()->route('admin.dashboard');
		}
        $data['title']='Card Payment History'; 
        return view('templates.admin.debit_card.view_transaction', $data);
	}
	
	public function card_history1($id=0){ 
		if(check_my_permission( Auth::user()->id,"248") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		if($id > 0){
			$data['page_id'] = $id;
		}
        $data['title']='Card Payment History'; 
        return view('templates.admin.debit_card.view_transaction', $data);
	}
	
	public function card_tr_history(Request $request, $id = 0){ 
	//dd($id);
		if ($request->ajax()){
			if(!empty($_POST['searchform'])){
				foreach($_POST['searchform'] as $frm_data){
					$arrFormData[$frm_data['name']] = $frm_data['value'];
            	}
			}
			
			$search = $_POST['search']['value'];
			$where = '(t2.card_no LIKE "%'.$search.'%")';
			//->whereRaw($where)
			if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes'){
				$data = DebitCardTransaction::select('debit_card_transaction.*', 't2.card_no', 't3.account_no')
					->leftjoin('debit_card as t2', 'debit_card_transaction.debit_card_id', '=', 't2.id')
					->leftjoin('saving_accounts as t3', 't2.ssb_id', '=', 't3.id');
					
			
					if($arrFormData['card_no'] !=''){
						$card_no=$arrFormData['card_no'];
						$data=$data->where('t2.card_no','=',$card_no);
					}
					if($arrFormData['ssb_ac'] !=''){
						$ssb_ac=$arrFormData['ssb_ac'];
						$data=$data->where('t3.account_no','=',$ssb_ac);
					}
					if($arrFormData['page_id'] != ''){
						$page_id=$arrFormData['page_id'];
						$data=$data->where('debit_card_transaction.debit_card_id','=',$page_id);
					}
            
			
					if(isset($id) && $id > 0){
						$data=$data->where('debit_card_transaction.id','=',$id);
					}

					$count = $data->count('debit_card_transaction.id');
					$totalCount = $count;

					$data=$data->offset($_POST['start'])->limit($_POST['length'])->get();
					
					$sno=$_POST['start'];
					$rowReturn = array(); 
					
					foreach ($data as $row){  
						$sno++;
						
						switch($row->status){
							case 0 : $status = "<span class='badge bg-success'>Pending</span>"; break;
							case 1 : $status = "<span class='badge bg-success'>Approved</span>"; break;
							case 2 : $status = "<span class='badge bg-danger'>Denied</span>"; break;
						}
						
						$val['DT_RowIndex'] = $sno;
						$val['card_no'] = $row->card_no;
						$val['account_no'] = $row->account_no;
						$val['amount'] = $row->amount;
						$val['payment_type'] = $row->payment_type;
						$val['status'] = $status;
						$val['entry_date'] = date('d/m/Y', strtotime($row->entry_date));
						
						$rowReturn[] = $val; 
					} 
					$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

				return json_encode($output);
			}
			else
			{
				$output = array(
					
					"draw" =>0,
					"recordsTotal" => 0,
					"recordsFiltered" =>0,
					"data" => 0,
				);
				return json_encode($output);
			}    
        }
	}
	
	public function card_tr_history_by_id($id){ 
		$data['title']='Debit Cards List '; 
        return view('templates.admin.debit_card.index', $data);
	}
	
	public function card_ssb_history($id){
		if(check_my_permission( Auth::user()->id,"246") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$ssb_id = $id;
		$title='Debit Cards SSB History '; 
		
		$debit_data = DebitCard::select('debit_card.*', 't2.account_no')
			->leftjoin('saving_accounts as t2', 'debit_card.ssb_id', '=', 't2.id')
			->where('ssb_id','=',$ssb_id)
			->get();
		
        return view('templates.admin.debit_card.view_debitcard_by_ssb', compact('debit_data', 'title'));
    }
}
