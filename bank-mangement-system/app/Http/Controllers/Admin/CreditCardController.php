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

class CreditCardController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index()
    {
		
		if(check_my_permission( Auth::user()->id,"181") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		
		$data['title']='Credit Cards List '; 
		//$data['account_heads'] = AccountHeads::where('labels','>',1)->where('status',0)->get();

        return view('templates.admin.credit_card.index', $data);
    }
	
	
	
	public function create()
    {
		
		if(check_my_permission( Auth::user()->id,"177") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		
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
			$checkCreditCard = CreditCard::where('credit_card_number',$request->credit_card_number)->where('is_deleted','0')->get();
			if( count($checkCreditCard) > 0 ){
				return back()->with('alert', 'Credit Card Already Exist!');	die;
			}
		}
		if($request->credit_card_id!= ""){
			$checkCreditCard = CreditCard::where('credit_card_number',$request->credit_card_number)->where("id","!=",$request->credit_card_id)->get();
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
				$label =  $accountHeads[0]->labels+1;
				
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
										 "labels" => $label,
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
			
			if( ($request->credit_card_head_id!= "") &&  ($request->credit_card_head_id > 0) ){
				$accountHeadsArr = array("sub_head" => trim($request->credit_card_number)
										); 
				$res1 = AccountHeads::where("head_id",$request->credit_card_head_id)->update($accountHeadsArr);
			}
			
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

			$data = CreditCard::select('id','card_name','card_holder_name','credit_card_number','credit_card_account_number','credit_card_bank','status')->where('status','1')->where('is_deleted','0')->whereRaw($where);
			
            //$data1=$data->get();
            

            $count=$data->count('id');
            $data=$data->offset($_POST['start'])->limit($_POST['length'])->get(); 

            $totalCount = $count;

                    

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
				
				if(check_my_permission( Auth::user()->id,"178") == "1"){
					$val['action'] .= '<a class="btn bg-dark legitRipple" href="admin/credit-card/edit/'.$row->id.'" title="Edit Credit Card"><i class="fa fa-edit"></i></a> &nbsp';
				}
				if(check_my_permission( Auth::user()->id,"179") == "1"){
					$val['action'] .= '<button class="btn bg-dark legitRipple deleteCreditCard" data-row-id="'.$row->id.'" title="Delete Credit Card"><i class="fa fa-trash"></i></button> &nbsp';
				}
				if(check_my_permission( Auth::user()->id,"180") == "1"){
					$val['action'] .= '<a class="btn bg-dark legitRipple" href="admin/credit-card/view_transaction/'.$row->id.'" title="View Transaction"><i class="fas fa-credit-card"></i></a> &nbsp';
				}

				$rowReturn[] = $val; 
            } 

			$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

          return json_encode($output);

        }
    }
	
	
	public function edit($id)
    {
		
		if(check_my_permission( Auth::user()->id,"177") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['title']='Edit Credit Card'; 
		$data['credit_details'] = CreditCard::where('id',$id)->get();
		
		$credit_details = CreditCard::where('id',$id)->first();
		$cardNumber = $credit_details->credit_card_number;
		// Now Get Their Head id
		$getCardHeadID = AccountHeads::where("sub_head", $cardNumber)->first();
		$data['credit_card_head_id'] = $getCardHeadID->head_id;

        return view('templates.admin.credit_card.create', $data);
    }
	
	
	
	
	
	public function delete_credit_card(Request $request)
    {
		$response = array();
		if(isset($request->credit_card_id) && $request->credit_card_id!= "" ){
			$credit_card_name = CreditCard::where("id",$request->credit_card_id)->first();

			$credit_card_id = trim($request->credit_card_id);
			
			// check that user has any transaction
			$head_id = AccountHeads::where('sub_head',$credit_card_name->credit_card_number)->first();
			if(isset($head_id->head_id))
			{
				$checkHeadsTransaction = AllHeadTransaction::where("head_id",$head_id->head_id)->get();
			}
			if( count($checkHeadsTransaction) > 0){
				
				$response["status"] = "0";
				$response["message"] = "Credit Card cannot be deleted. ";
				
			} else {
				
				$res = CreditCard::where("id",$request->credit_card_id)->delete();
				if(isset($credit_card_name->credit_card_number))
				{
					$res = AccountHeads::where("sub_head",$credit_card_name->credit_card_number)->delete();
				}
				
				
				$response["status"] = "1";
				$response["message"] = "Credit Card Deleted Successfully.";
				
			}
			
		} else {
			$response["status"] = "0";
			$response["message"] = "Credit Card Id Not Found";
		}
		echo json_encode($response); die;
		
    }
	
	
	
	public function view_transaction($id)
    {
		if(check_my_permission( Auth::user()->id,"180") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['title']='View Credit Card Transactions'; 
		$credit_details = CreditCard::where('id',$id)->first();
		$cardNumber = $credit_details->credit_card_number;
		$data['credit_card_id'] = $id;
		
		// Now Get Their Head id
		$getCardHeadID = AccountHeads::where("sub_head", $cardNumber)->first();
		$data['credit_card_head_id'] = $getCardHeadID->head_id;

        return view('templates.admin.credit_card.view_transaction', $data);
    }
	
	
	
	public function credit_card_transaction_listing(Request $request)
    { 
		$sub_head_id = $_POST['credit_card_head_id'];
		
		$accounthead =   AccountHeads::where('head_id',$sub_head_id)->first();
		
		$data = AllHeadTransaction::with('branch','member');
		if($sub_head_id!= ""){
			$data=$data->where('all_head_transaction.head_id',$sub_head_id);
		}
		$data1 = $data;
		$count= $data1->count();

		$data=$data->orderby('all_head_transaction.id','asc')->offset($_POST['start'])->limit($_POST['length'])->get();
              
        $totalCount = AllHeadTransaction::count();
		
		
		
		// Now For totals
		if($_POST['pages'] == 1){
			$totalAmount  = 0;
		} else {
			$totalAmount  = $_POST['total'];
		}
		$dataCR = AllHeadTransaction::with('branch','member');
		if($sub_head_id!= ""){
			$dataCR=$dataCR->where('all_head_transaction.head_id',$sub_head_id);
		}
		if($_POST['pages'] == "1"){
			$length = ($_POST['pages']) * $_POST['length'];
		} else {
			$length = ($_POST['pages']-1) * $_POST['length'];
		}
		$dataCR = $dataCR->offset(0)->limit($length)->get();
		if($accounthead->cr_nature == 1)
		{	
			$totalDR = $dataCR->where('payment_type','DR')->sum('amount');
			$totalCR = $dataCR->where('payment_type','CR')->sum('amount');
			$totalAmountssssss = $totalCR - $totalDR;
		} else{
			$totalDR = $dataCR->where('payment_type','DR')->sum('amount');
			$totalCR = $dataCR->where('payment_type','CR')->sum('amount');
			$totalAmountssssss = $totalDR - $totalCR;
		}
		if($_POST['pages'] == "1"){
			$totalAmountssssss = 0;
		}
		

        $sno=$_POST['start'];
        $rowReturn = array();
        foreach ($data as $row)
        {  
            $sno++;
            $val['DT_RowIndex']=$sno;
            $val['created_date']=date("d/m/Y", strtotime(convertDate($row->created_at)));

            $data2=Branch::where('id',$row->branch_id)->first('name');
			
			if(isset($data2->name)){
				$val['branch_name']=$data2->name;
			} else {
				$val['branch_name']= "";
			}
            $val['head_name']=getAcountHead($row->head_id);
            if($row->payment_mode == 0){
            	$val['payment_mode']='Cash';
            }elseif($row->payment_mode == 1){
            	$val['payment_mode']='Cheque';
            }elseif($row->payment_mode == 2){
            	$val['payment_mode']='Online Transfer';	
            }elseif($row->payment_mode == 3){
            	$val['payment_mode']='SSB/GV Transfer';
            }elseif($row->payment_mode == 4){
            	$val['payment_mode']='Auto Transfer(ECS)';
            }elseif($row->payment_mode == 5){
            	$val['payment_mode']='By loan amount';
            }elseif($row->payment_mode == 6){
            	$val['payment_mode']='JV Module';
            }elseif($row->payment_mode == 7){
            	$val['payment_mode']='Credit Card';
            }
            
			/*
            if($row->payment_type == 'DR'){
            	$val['debit']=$row->amount;
            	$val['credit']=0;
            }elseif($row->payment_type == 'CR'){
            	$val['debit']=0;
            	$val['credit']=$row->amount;
            }
			*/
			
			if($row->payment_type == 'CR')
			{
				$credit = $row->amount;
				$val['credit']= number_format((float)$row->amount, 2, '.', '');;
			}
			else{
				$credit=0;
				  $val['credit']=0;
			}
			if($row->payment_type == 'DR')
			{
				$debit =$row->amount;	
				 $val['debit']= number_format((float)$row->amount, 2, '.', '');;
			}
			else{
				$debit =0;
				$val['debit']=0;
			}
            
			
			if($accounthead->cr_nature == 1)
			{
				$total =(float)$credit  - (float)$debit ;
				$totalAmountssssss = $totalAmountssssss + $total;
			}else{
				$total = (float)$debit - (float)$credit;
				$totalAmountssssss = $totalAmountssssss + $total;
			}

			$val['balance'] =number_format((float)$totalAmountssssss, 2, '.', ''); 
			
			$val['description']=$row->description;
			
            $rowReturn[] = $val; 
      	} 
      	$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, 'total' =>$totalAmountssssss);

      	return json_encode($output);  
        
    }
	
	
}
