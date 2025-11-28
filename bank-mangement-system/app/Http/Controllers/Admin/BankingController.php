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
use App\Models\SamraddhBank;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBankAccount;
use App\Models\ReceivedCheque; 
use App\Models\CreditCard;
use App\Models\Receipt;
use App\Models\ReceiptAmount;
use App\Models\Grouploanmembers;
use App\Models\Memberloans;
use App\Models\Loans;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentspayments;
use App\Models\CorrectionRequests;
use App\Models\MemberTransaction;
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

class BankingController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index()
    {
		/*
		if(check_my_permission( Auth::user()->id,"167") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		*/
		
		$data['title']='Add Banking Transaction'; 
		$data['credit_card'] = CreditCard::where('status','1')->where('is_deleted','0')->get();
		$data['banks'] = SamraddhBank::where('status','1')->get();
		$data['branch'] = Branch::where('status','1')->get();
		
		$subHeadsIDS = AccountHeads::where('parent_id',13)->pluck('head_id')->toArray();
		$finalArr = $subHeadsIDS;
		
		if( count($subHeadsIDS) > 0 ){
			$subHeadsIDS2 = AccountHeads::whereIn('parent_id',$subHeadsIDS)->pluck('head_id')->toArray();
			$finalArr = array_merge($subHeadsIDS,$subHeadsIDS2);
		}
		
		$account_heads = AccountHeads::whereIn('head_id',$finalArr)->get();
		
		$data['account_heads'] = $account_heads;

        return view('templates.admin.banking.add_banking', $data);
    }
	
	
	public function get_banks_data(Request $request)
    {
		
		if(isset($request->bank_id)){
			$bank_id =  $request->bank_id;
			$accountNumbers = SamraddhBankAccount::where("bank_id", $bank_id)->where("status","1")->get();
			
			$cheque = ReceivedCheque::where("deposit_bank_id", $bank_id)->get();
			
			$data = array("accountNumbers" => $accountNumbers, "cheque" => $cheque);
			
			echo json_encode($data); die;
			
		}
		
    }
	
	
	public function get_cheque_data(Request $request)
    {
		
		if(isset($request->account_id)){
			$account_id =  $request->account_id;
			$cheque = SamraddhCheque::where("account_id", $account_id)->get();
			
			$data = array("cheque" => $cheque);
			
			echo json_encode($data); die;	
		}
		
    }
	
	
	
	
	
	public function create()
    {
		/*
		if(check_my_permission( Auth::user()->id,"167") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		*/
		
		$data['title']='Credit Card | Create'; 

        return view('templates.admin.credit_card.create', $data);
    }
	
	
	public function credit_card_save(Request $request)
    {
		
        $validator = Validator::make($request->all(), [
            'card_name' => 'required',
            'card_holder_name' => 'required',
			'credit_card_number' => 'required',
			'credit_card_account_number' => 'required',
			'credit_card_bank' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect('admin/credit-card')
                ->withErrors($validator)
                ->withInput();
        }
		
		// check credit card number already exist or not
		if($request->credit_card_id == ""){
			$checkCreditCard = CreditCard::where('credit_card_number',$request->credit_card_number)->get();
			if( count($checkCreditCard) > 0 ){
				return back()->with('alert', 'Credit Card Already Exist!');	die;
			}
		}

	    $data['card_name'] = $request->card_name;
	    $data['card_holder_name'] = $request->card_holder_name;
	    $data['credit_card_number'] = $request->credit_card_number;
	    $data['credit_card_account_number'] = $request->credit_card_account_number;
	    $data['credit_card_bank'] = $request->credit_card_bank;

		//print_r($request->all()); die;

		if($request->credit_card_id == ""){
			
			// Create
			$res = CreditCard::create($data);
			
			// Get Head ID and parent ID for credit card head
			$accountHeads = AccountHeads::where("sub_head","Credit Card")->get();
			if(count($accountHeads) > 0){
				$autoId =  $accountHeads[0]->id;
				$parent_id =  $accountHeads[0]->head_id;
				
				// Now Get last head ID
				$accountHeadsID = AccountHeads::where("status","0")->orderBy("head_id","desc")->first();
				if(isset($accountHeadsID->head_id)){
					$new_head_id = $accountHeadsID->head_id;
					$new_head_id = $new_head_id + 1;
				} else {
					$new_head_id = 0;
				}
				
				$accountHeadsArr = array("sub_head" => trim($request->credit_card_number),
										 "parent_id" => $parent_id,
										 "parentId_auto_id" => $autoId,
										 "labels" => "2",
										 "status" => "0",
										 "cr_nature" => "1",
										 "dr_nature" => "2",
										 "is_move" => "1",
										 "head_id" => $new_head_id
										); 
				$res = AccountHeads::create($accountHeadsArr);						
				
			}

			if ($res) {
				return redirect()->route('admin.credit-card')->with('success', 'Credit Card Created Successfully!');
			} else {
				return back()->with('alert', 'Problem With Creating Credit Card');
			}
		} else {
			
			// Update
			$res = CreditCard::where("id",$request->credit_card_id)->update($data);

			if ($res) {
				return redirect()->route('admin.credit-card')->with('success', 'Credit Card Updated Successfully!');
			} else {
				return back()->with('alert', 'Problem With Updating Credit Card');
			}
		}
		
	}
	
	
	
	public function credit_card_listing(Request $request)
    { 
        $currentdate = date('Y-m-d');

        if ($request->ajax()) {
			
			$search = $_POST['search']['value'];

			$where = '(card_name LIKE "%'.$search.'%" OR card_holder_name LIKE "%'.$search.'%" OR credit_card_number LIKE "%'.$search.'%" OR credit_card_account_number LIKE "%'.$search.'%" OR credit_card_bank LIKE "%'.$search.'%")';

			$data = CreditCard::where('status','1')->where('is_deleted','0')->whereRaw($where);
			
            $data1=$data->get();
            $count=count($data1);


            $data=$data->offset($_POST['start'])->limit($_POST['length'])->get(); 

            $totalCount = CreditCard::where('status',1)->where('is_deleted','0')->count();

                    

            $sno=$_POST['start'];
            $rowReturn = array(); 
			
            foreach ($data as $row)
            {  
                $sno++;

                $val['DT_RowIndex']=$sno;

                $val['card_name']=$row->card_name;

                $val['card_holder_name']=$row->card_holder_name;

                $val['credit_card_number']=$row->credit_card_number;

                $val['credit_card_account_number']=$row->credit_card_account_number;

                $val['credit_card_bank']=$row->credit_card_bank;
				
				$val['action']= "";
				
				$val['action'] .= '<a class="btn bg-dark legitRipple" href="admin/credit-card/edit/'.$row->id.'" title="Edit Credit Card"><i class="fa fa-edit"></i></a> <button class="btn bg-dark legitRipple deleteCreditCard" data-row-id="'.$row->id.'" title="Delete Credit Card"><i class="fa fa-trash"></i></button>';

				$rowReturn[] = $val; 
            } 

			$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

          return json_encode($output);

        }
    }
	
	
	public function edit($id)
    {
		/*
		if(check_my_permission( Auth::user()->id,"167") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		*/
		
		$data['title']='Edit Credit Card'; 
		$data['credit_details'] = CreditCard::where('id',$id)->get();

        return view('templates.admin.credit_card.create', $data);
    }
	
	
	
	
	
	public function delete_credit_card(Request $request)
    {
		$response = array();
		
		if(isset($request->credit_card_id) && $request->credit_card_id!= "" ){
			$credit_card_id = trim($request->credit_card_id);
			
			// check that user has any transaction
			$checkHeadsTransaction = AllHeadTransaction::where("head_id",126)->get();
			
			if( count($checkHeadsTransaction) > 0){
				
				$response["status"] = "0";
				$response["message"] = "Credit Card cannot be deleted. ";
				
			} else {
				
				$res = CreditCard::where("id",$request->credit_card_id)->update(array("is_deleted" => "1"));
				
				$response["status"] = "1";
				$response["message"] = "Credit Card Deleted Successfully.";
				
			}
			
		} else {
			$response["status"] = "0";
			$response["message"] = "Credit Card Id Not Found";
		}
		echo json_encode($response); die;
		
    }
	
	
}
