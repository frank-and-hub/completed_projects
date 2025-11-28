<?php



namespace App\Http\Controllers\Admin\Reinvest;



use App\Models\Reinvest;

use App\Models\ReinvestMemberNominee;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Input;

use Illuminate\Support\Str;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Admin\CommanController;

use App\Http\Controllers\Branch\CommanTransactionsController;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;

use Validator;

use App\Models\User;

use App\Models\Member;

use App\Models\ReinvestMember;

use App\Models\Memberinvestments;

use App\Models\SavingAccount;

use App\Models\Relations;

use App\Models\SavingAccountTranscation;

use App\Models\Investmentplantransactions;

use App\Models\Memberinvestmentsnominees;

use App\Models\Memberinvestmentspayments;

use App\Models\Transcation;

use App\Models\TranscationLog;

use App\Models\Daybook;

use App\Models\Branch;

use Carbon\Carbon;

use DB;

use URL;

use Image;

use Session;

use App\Models\Plans;

use App\Models\ReinvestData;

use Yajra\DataTables\DataTables;



class ReinvestController extends Controller

{

    /**

     * Instantiate a new controller instance.

     *

     * @return void

     */    

    public function __construct()

    {   

        $this->middleware('auth');

    }



    public function index()

    {

        if(check_my_permission( Auth::user()->id,"73") != "1"){

		  return redirect()->route('admin.dashboard');

		}

		

		/*$data = Member::with('branch')->where('member_id','!=','9999999')->orderby('id','DESC')->get();

		

		echo '<pre>';

		print_r($data);

		exit;*/

		

		

		$data['title'] = "Reinvest Listing";

        return view('templates.admin.reinvest.index', $data);

    }



	public function reInvestListing(Request $request)

	{

		if ($request->ajax() && check_my_permission( Auth::user()->id,"73") == "1") {



			$data = Member::select('id','member_id','branch_id','reinvest_old_account_number','re_date','first_name','last_name','mobile_no','associate_code','associate_id','is_block','status')->with(['branch'=>function($q){
				$q->select('id','name','branch_code','zone','sector','regan');
			},'children'=>function($q){
				$q->select('id','associate_code','first_name','last_name');
			},'memberReinvestment'=>function($q){
				$q->select('id');
			}])->whereNotNull('reinvest_old_account_number')->where('member_id','!=','9999999')->where('is_block', 1);

			

			if(Auth::user()->branch_id>0){

			 $id=Auth::user()->branch_id;

		     $data=$data->where('branch_id','=',$id);

		    }
		    $data1 = $data->count('id');
		    $count = $data1;
		    $data = $data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
			
			$totalCount = $count;
		    $sno = $_POST['start'];
            $rowReturn = array();

            foreach ($data as $row)
            {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['join_date'] = date("d/m/Y", strtotime($row->re_date));
                $val['account_number'] = $row->reinvest_old_account_number;
                $val['branch'] = $row['branch']->name;
                $val['branch_code'] = $row['branch']->branch_code;
                $val['sector'] = $row['branch']->sector;
                $val['regan'] = $row['branch']->regan;
                $val['zone'] = $row['branch']->zone;
                $val['member_id'] = $row->member_id;
                $val['name'] = $row->first_name.' '.$row->last_name;
                $val['email'] = $row->email;
                $val['mobile_no'] = $row->mobile_no;
                $val['associate_code'] = $row->associate_code;
                $val['associate_name'] = $row['children']->first_name.' '.$row['children']->last_name;//getSeniorData($row->associate_id,'first_name').' '.getSeniorData($row->associate_id,'last_name');
                
                if($row->is_block==1)
                {
	                $status = 'Blocked';

                }
                else
                {
	                if($row->status==1)
	                {
		                 $status = 'Active';

	                }
	                else
	                {
		                 $status = 'Inactive';

	                }
                 }

                $val['status'] = $status;

                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                 if($row->is_block==1){

                 	$raNumbers= explode(',', $row->reinvest_old_account_number);

                 	foreach ($raNumbers as $key => $value) {

                 		$aCount = $row['memberReinvestment']->count();

                 		if($aCount == 0){

		                    if ( $value ) {

			                    $url = URL::to("admin/approve-reinvestment/".$row->id."/".$value."");

			                    $btn .= '<a class="dropdown-item" href="'.$url.'" title="Member Detail"><i class="icon-eye-blocked2  mr-2"></i>Approve-'.$value.'</a>  ';

			                    $editurl = URL::to("admin/edit-reinvestment/".$row->id."/".$value."");

			                    $btn .= '<a class="dropdown-item" href="'.$editurl.'" title="Edit"><i class="icon-pencil5  mr-2"></i>Edit-'.$value.'</a>';

		                    } else {

			                    $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Investment Not Created"><i class="icon-pencil5  mr-2"></i>Investment Not Created</a>';

		                    }

                 		}

                 	}

                 }

                 $btn .= '</div></div>';

                $val['action'] = $btn;                
                $rowReturn[] = $val;
            }

            $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn );
            return json_encode($output);


		}

	}



	public function editReinvestment($id,$anumber)

	{

		$data['id'] = $id;

		$data['anumber'] = $anumber;

		$rnAccount = Member::with('memberReinvestment')->where('id',$id)->first();



		$rnAccountDetails = ReinvestData::where('account_number',$anumber)->get();

		$arrayResults = json_decode($rnAccountDetails, true);

		//echo "<pre>"; print_r($rnAccount); die;

		$fData = '';

		$ftData = '';

		foreach ($arrayResults as $key => $value) {

			if($value['form_type'] == 'reinvest_plane'){

				$fData = json_decode($value['form_data'], true);	

			}elseif($value['form_type'] == 'reinvest_transaction'){

				$ftData = json_decode($value['form_data'], true);

				$data['rtoDetails'] = $ftData;

			}elseif($value['form_type'] == 'free_transaction'){

				$ftData = json_decode($value['form_data'], true);

				$data['rtoDetails'] = array();

			}

		}



		$data['branch'] = Branch::where('status',1)->get();

        $data['state']=stateList();  

        $data['occupation']=occupationList();

        $data['religion']=religionList();

        $data['specialCategory']=specialCategoryList();

        $data['idTypes']=idTypeList();

        $data['relations']=relationsList();

		$data['title'] = "Edit Reinvest";

		$data['memberDetail'] = $rnAccount;

		$data['rnDetails'] = $fData;

		$data['rtDetails'] = $ftData;

		$data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id',$id)->first();

        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$id)->first();

        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id',$id)->first(); 

        return view('templates.admin.reinvest.editreinvestment', $data);

	}



	/**

     * Get plan form in edit by plan name.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function editPlanForm(Request $request)

    {

        $pName = $request->pName;

        $aNumber = $request->aNumber;

        $data['action'] = 'edit';

        $rData=ReinvestData::where('account_number',$aNumber)->where('form_type','reinvest_plane')->first();

        $iArray = json_decode($rData['form_data'],true);



        $iArray['deposite_amount'] = $iArray['amount'];

        $iArray['payment_mode'] = $iArray['payment-mode'];

        $iArray['interest_rate'] = $iArray['interest-rate'];

        $iArray['maturity_amount'] = $iArray['maturity-amount'];

        $iArray['open_date'] = $iArray['open-date'];

        $iArray['cheque_number'] = $iArray['cheque-number'];

        $iArray['plan_name'] = $iArray['plan-name'];

        $iArray['bank_name'] = $iArray['bank-name'];

        $iArray['branch_name'] = $iArray['branch-name'];

        $iArray['cheque_date'] = $iArray['cheque-date'];

        $iArray['transaction_id'] = $iArray['transaction-id'];

        $data['investments'] = (object) $iArray;

        $data['relations'] = Relations::all();

        return view('templates.admin.reinvest.'.$pName.'.edit-'.$pName.'',$data);

    }



	public function approveReinvestment($id,$anumber)

	{



		$rnAccount = ReinvestData::where('account_number',$anumber)->get();

		$arrayResults = json_decode($rnAccount, true);

		$nArray = '';

		$fData = '';

		$ftData = '';

		foreach ($arrayResults as $key => $value) {

			if($value['form_type'] == 'reinvest_plane'){

				$nArray = json_decode($value['form_data'], true);	

			}elseif($value['form_type'] == 'reinvest_transaction'){

				$ftData = json_decode($value['form_data'], true);

			}elseif($value['form_type'] == 'free_transaction'){

				$ftData = '';

			}

		}



		

		if (array_key_exists("payment-mode",$nArray)){

			if($nArray['payment-mode'] == ''){

	        	$replacements = array('payment-mode' => 0);

	        	$fData = array_replace($nArray, $replacements);

	        }else{

	        	$fData = $nArray;

	        }

	    }else{

	    	$fData = $nArray;

	    	$fData['payment-mode'] = 0;

	    }

	   

		DB::beginTransaction();

		try {

				$type = 'create';

				$plantype = $fData['plan_type'];

	            //get login user branch id(branch manager)pass auth id       

	            $branch_id=$fData['branch_id'];



	            $branchdetail = Branch::where('id',$branch_id)->first('name');



	            $bName = $branchdetail->name;



	            $getBranchCode=getBranchCode($branch_id);

	            $branchCode=$getBranchCode->branch_code;

	            //get login user branch id(branch manager)pass auth id

	            $faCode=getPlanCode($fData['investmentplan']);

	            $planId=$fData['investmentplan'];

	            $investmentMiCode=getInvesmentMiCode($planId,$branch_id);

	            

	            if(!empty($investmentMiCode))

	            {

	                $miCodeAdd=$investmentMiCode->mi_code+1;

	            }

	            else

	            {

	               $miCodeAdd=1; 

	            }

	            //$miCodeAdd=$investmentMiCode->mi_code;

	            $miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);



	            $miCodeBig   =str_pad($miCodeAdd, 7, '0', STR_PAD_LEFT); 

	            

	            $passbook =  '720'.$branchCode.$miCodeBig;

	            $certificate ='719'.$branchCode.$miCodeBig;

	    

	            // Invesment Account no 

	            $investmentAccount=$fData['account_number_for_reinvest'];

	           

	            $data = $this->getData($fData,$type,$miCode,$investmentAccount,$branch_id);



	            if($faCode==705 || $faCode==706 || $faCode==712) {

	              $data['certificate_no']=$certificate;

	            }

	            else

	            {

	              if($faCode!=703)

	              {

	                $data['passbook_no']=$passbook;

	              }

	            }

	            $data['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($fData['open-date'])));



	            $sAccount = $this->getSavingAccountDetails($fData['memberAutoId']);

	    

	            if(count($sAccount) > 0){

	              $ssbAccountNumber = $sAccount[0]->account_no;

	              $ssbId = $sAccount[0]->id;

	              $ssbBalance = $sAccount[0]->balance;

	            }else{

	              $ssbAccountNumber = '';

	              $ssbId = '';

	              $ssbBalance = '';

	            }

	    		$res = Memberinvestments::create($data);

	    		$insertedid = $res->id;

				

				// add log

				$encodeDate = json_encode($data);

				$arrs = array("investmentId" => $insertedid, "type" => "6", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Reinvest – Any type of plan reinvest (Create)", "data" => $encodeDate);

				DB::table('user_log')->insert($arrs);

				// end log

				

	    		$amountArray=array('1'=>$fData['amount']);

	    		$member_name = getMemberDetails($fData['associatemid']);
                if(isset($member_name->first_name))
                {
                $amount_deposit_by_name = $member_name->first_name.' '.$member_name->last_name;

                }
                else{
                $amount_deposit_by_name = '';

                }



	    		if($plantype != 'samraddh-kanyadhan-yojana'){

	    			$fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

	                $res = Memberinvestmentsnominees::create($fNominee); 



	                if($fData['second_nominee_add']==1){

	                  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

	                  $res = Memberinvestmentsnominees::create($sNominee);

	                }

	    		}



	            /*switch ($plantype) {

	              case "samraddh-kanyadhan-yojana":

		                $description = 'SK Account opening';             

		                if($fData['payment-mode']==3){



		                  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

		                  $res = SavingAccountTranscation::create($savingTransaction); 

		                  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

		                  $sAccountId = $ssbId;

		                  $sAccountAmount = $ssbBalance-$fData['amount'];

		   

		                  $sResult = SavingAccount::find($sAccountId);

		                  $sData['balance'] = $sAccountAmount;

		                  $sResult->update($sData);

		    

		                  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

		                  $sAccountNumber = $ssbAccountNumber;

		                }else{

		                  $sAccountNumber = NULL;

		                  $satRefId = NULL;

		                  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

		                }

		    

		                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



		                $transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

		                $res = Investmentplantransactions::create($transaction);

		    

		                $paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

		                $res = Memberinvestmentspayments::create($paymentData); 

	              break;

	              case "special-samraddh-money-back":

		                $description = 'EMB Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

		                $res = Memberinvestmentsnominees::create($fNominee); 



		                if($fData['second_nominee_add']==1){

		                  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

		                  $res = Memberinvestmentsnominees::create($sNominee);

		                }

		 

		                if($fData['payment-mode']==3){

		                  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

		                  $res = SavingAccountTranscation::create($savingTransaction); 

		                  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

		                  $sAccountId = $ssbId;

		                  $sAccountAmount = $ssbBalance-$fData['amount'];

		                  $sResult = SavingAccount::find($sAccountId);

		                  $sData['balance'] = $sAccountAmount;

		                  $sResult->update($sData);



		                  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

		                  $sAccountNumber = $ssbAccountNumber;

		                }else{

		                  $sAccountNumber = NULL;

		                  $satRefId = NULL;

		                  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

		                }

		    

		                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



		                $transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

		                $res = Investmentplantransactions::create($transaction);

		    

		                $paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

		                $res = Memberinvestmentspayments::create($paymentData); 

	              break;

	              case "flexi-fixed-deposit":

		                $description = 'FFD Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}

		 

		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData);

	              break;

	              case "fixed-recurring-deposit":

		                $description = 'FRD Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData);

	              break;

	              case "samraddh-jeevan":

		                $description = 'SJ Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData);

	              break;

	              case "daily-deposit":

		                $description = 'SDD Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData); 

	              break;

	              case "monthly-income-scheme":

		                $description = 'MIS Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData); 

	              break;

	              case "fixed-deposit":

		                $description = 'SFD Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData); 

	              break;

	              case "recurring-deposit":

		                $description = 'ERD Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData);  

	              break;

	              case "samraddh-bhavhishya":

		                $description = 'SB Account opening';



		                $fNominee = $this->getFirstNomineeData($fData,$insertedid,$type);

						$res = Memberinvestmentsnominees::create($fNominee); 



						if($fData['second_nominee_add']==1){

						  $sNominee = $this->getSecondNomineeData($fData,$insertedid,$type);

						  $res = Memberinvestmentsnominees::create($sNominee);

						}





		                if($fData['payment-mode']==3){

						  $savingTransaction = $this->savingAccountTransactionData($fData,$insertedid,$type);

						  $res = SavingAccountTranscation::create($savingTransaction); 

						  $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);

						  $sAccountId = $ssbId;

						  $sAccountAmount = $ssbBalance-$fData['amount'];

						  $sResult = SavingAccount::find($sAccountId);

						  $sData['balance'] = $sAccountAmount;

						  $sResult->update($sData);



						  $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$fData['memberAutoId'],$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');

						  $sAccountNumber = $ssbAccountNumber;

						}else{

						  $sAccountNumber = NULL;

						  $satRefId = NULL;

						  $ssbCreateTran = $this->commonTransactionLogData($satRefId,$fData,$insertedid,$type);

						}



						$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$fData['amount'],$fData['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,$fData['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');



						$transaction = $this->transactionData($satRefId,$fData,$insertedid,$fData['amount'],$ssbCreateTran);

						$res = Investmentplantransactions::create($transaction);



						$paymentData = $this->getPaymentMethodData($fData,$insertedid,$type);

						$res = Memberinvestmentspayments::create($paymentData); 

	              break;

	            }*/
	           
	            if($ftData && isset($ftData)){

	            	unset($ftData['closing_Balance_reinvest']);

					unset($ftData['opening_Balance_reinvest']);

					unset($ftData['total_reinvest_amount']);

					unset($ftData['collection_reinvest_amount']);

					unset($ftData['payment_mode']);



		            foreach (array_filter($ftData) as $key => $value) {

		            	Session::put('created_at', date("Y-m-d", strtotime(convertDate($value['amount_date']))));

						$amountArraySsb=array('1'=>$value['amount']);

						if($fData['payment-mode'] == 4){

			                $ssbAccountAmount = $ssbBalance-$value['amount'];

			                $ssb_id = $ssbId;

			                $sResult = SavingAccount::find($ssb_id);

			                $sData['balance'] = $ssbAccountAmount;

			                $sResult->update($sData);

			                $ssb['saving_account_id']=$ssb_id;

			                $ssb['account_no']=$investmentAccount;

			                $ssb['opening_balance']=$ssbAccountAmount;

			                $ssb['deposit']=NULL;

			                $ssb['withdrawal']=$value['amount'];

			                //$ssb['description']=''.$accountNumber.'/Auto debit';  

			                $ssb['description'] = 'Cash withdrawal';                

			                $ssb['currency_code']='INR';

			                $ssb['payment_type']='CR';

			                $ssb['payment_mode']=$fData['payment-mode'];

			                $ssbAccountTran = SavingAccountTranscation::create($ssb);

			                $satRefId = CommanController::createTransactionReferences($ssbAccountTran->id,$insertedid);

			            }else{

			                $satRefId = NULL;

			            }



			            if($key == 0){

			            	$cDescription = 'Opening balance'; 

	                   	}else{

	                   		if($fData['investmentplan']==7){

			                  $cDescription = 'SDD Collection';  

			                }elseif($fData['investmentplan']==10){

			                  $cDescription = 'ERD collection';  

			                }elseif($fData['investmentplan']==5){

			                  $cDescription = 'FRD collection';  

			                }elseif($fData['investmentplan']==3){

			                  $cDescription = 'SMB collection';  

			                }elseif($fData['investmentplan']==2){

			                  $cDescription = 'SK collection';  

			                }elseif($fData['investmentplan']==6){

			                  $cDescription = 'SJ collection';  

			                }elseif($fData['investmentplan']==4){

			                  $cDescription = 'FFD collection';  

			                }

	                   	}



			            /*if($fData['investmentplan']==7){

		                  $cDescription = 'SDD Collection';  

		                }elseif($fData['investmentplan']==10){

		                  $cDescription = 'ERD collection';  

		                }elseif($fData['investmentplan']==5){

		                  $cDescription = 'FRD collection';  

		                }elseif($fData['investmentplan']==3){

		                  $cDescription = 'SMB collection';  

		                }elseif($fData['investmentplan']==2){

		                  $cDescription = 'SK collection';  

		                }elseif($fData['investmentplan']==6){

		                  $cDescription = 'SJ collection';  

		                }elseif($fData['investmentplan']==4){

		                  $cDescription = 'FFD collection';  

		                }*/



		                



		                $investmentDetail = $this->getInvestmentPlanDetail($insertedid);

		                if($key==0)

		                {

		                	if($investmentDetail){

		                        $sResult = Memberinvestments::find($insertedid); 

		                        $totalbalance = $investmentDetail->current_balance+$value['amount'];

			                    $investData['current_balance'] = $totalbalance;

		                        $sResult->update($investData);

		                    }else{

		                        $totalbalance = '';

		                    }

		                }

		                else

		                {

		                	if($investmentDetail){

		                        $sResult = Memberinvestments::find($insertedid); 

		                        $totalbalance = $investmentDetail->current_balance+$value['amount'];

			                    $investData['current_balance'] = $totalbalance;

		                    }else{

		                        $totalbalance = '';

		                    }

		                }

		                $createTransaction = CommanController::createTransaction($satRefId,4,$insertedid,$fData['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR'); 

		                $transactionData['is_renewal'] = 0;

	                    $transactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) );

	                    $updateTransaction = Transcation::find($createTransaction);

	                    $updateTransaction->update($transactionData);

	                    TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0,'created_at' => date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) )]);

	                   	if($key == 0){

	                   		$elistatus = 1;

	                   	}else{

	                   		$elistatus = 0;

	                   	}

	                   	$createDayBook = CommanController::createReinvestDayBook($createTransaction,$satRefId,4,$insertedid,$fData['associatemid'],$fData['memberAutoId'],$totalbalance,$value['amount'],$withdrawal=0,$cDescription,$ref=NULL,$branch_id,$branchCode,$amountArraySsb,$fData['payment-mode'],$amount_deposit_by_name,$fData['memberAutoId'],$investmentAccount,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR',$elistatus); 

	                   	$daybookData['is_renewal'] = 0;

		                $daybookData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) );

		                $dayBook = Daybook::find($createDayBook);

		                $dayBook->update($daybookData);

		                /************* Head Implement **************/

	                   	if($fData['plan_type'] == 'saving-account'){

	                   		$planHead = NULL;

	                   	}elseif($fData['plan_type'] == 'samraddh-kanyadhan-yojana'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 59;

	                   		$head5 = 80;

	                   		$head_id = 80;

	                   	}elseif($fData['plan_type'] == 'special-samraddh-money-back'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 59;

	                   		$head5 = 85;

	                   		$head_id = 85;

	                   	}elseif($fData['plan_type'] == 'flexi-fixed-deposit'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 57;

	                   		$head5 = 79;

	                   		$head_id = 79;

	                   	}elseif($fData['plan_type'] == 'fixed-recurring-deposit'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 59;

	                   		$head5 = 83;

	                   		$head_id = 83;

	                   	}elseif($fData['plan_type'] == 'samraddh-jeevan'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 59;

	                   		$head5 = 84;

	                   		$head_id = 84;

	                   	}elseif($fData['plan_type'] == 'daily-deposit'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 58;

	                   		$head5 = NULL;

	                   		$head_id = 58;

	                   	}elseif($fData['plan_type'] == 'monthly-income-scheme'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 57;

	                   		$head5 = 78;

	                   		$head_id = 78;

	                   	}elseif($fData['plan_type'] == 'fixed-deposit'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 57;

	                   		$head5 = 77;

	                   		$head_id = 77;

	                   	}elseif($fData['plan_type'] == 'recurring-deposit'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 59;

	                   		$head5 = 83;

	                   		$head_id = 83;

	                   	}elseif($fData['plan_type'] == 'samraddh-bhavhishya'){

	                   		$head1 = 1;

	                   		$head2 = 8;

	                   		$head3 = 20;

	                   		$head4 = 59;

	                   		$head5 = 82;

	                   		$head_id = 82;

	                   	}

	                   	if($elistatus == 1){

	                   		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

			                $v_no = "";

			                for ($i = 0; $i < 10; $i++) {

			                    $v_no .= $chars[mt_rand(0, strlen($chars)-1)];

			                } 



	                   		$dayBookRef = CommanController::createBranchDayBookReference($value['amount'],$value['amount_date']);

                            $associaateNAme = '';
                            if($fData['associatemid']){
                              $associaateNAme =   getMemberData($fData['associatemid'])->first_name.' '.getMemberData($fData['associatemid'])->last_name;
                            }

			                $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,3,30,$insertedid,$createDayBook,$fData['associatemid'],$fData['memberAutoId'],$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'Eli Amount Dr '.$value['amount'].'','Eli Amount Dr '.$value['amount'].'','To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$v_no,date("Y-m-d", strtotime(convertDate($value['amount_date']))),$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,$value['amount_date'],$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
			                

			                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head_id,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$jv_unique_id=NULL,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),1,Auth::user()->id);

			                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1,$head2,$head3,$head4,$head5,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],getMemberData($fData['associatemid'])->first_name.' '.getMemberData($fData['associatemid'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$value['amount_date']);*/

			                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,89,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','DR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$jv_unique_id=NULL,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),1,Auth::user()->id);

			                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,89,$head4=NULL,$head5=NULL,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],getMemberData($fData['associatemid'])->first_name.' '.getMemberData($fData['associatemid'])->last_name,$v_no,date("Y-m-d", strtotime(convertDate($value['amount_date']))),$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$value['amount_date']);*/

			                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$bank_id=NULL,$account_id=NULL,$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$v_no,date("Y-m-d", strtotime(convertDate($value['amount_date']))),$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$entry_time=NULL,2,Auth::user()->id,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);



	                   	}else{

	                   		$dayBookRef = CommanController::createBranchDayBookReference($value['amount'],$value['amount_date']);



			                $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,3,30,$insertedid,$createDayBook,$fData['associatemid'],$fData['memberAutoId'],$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'Cash A/C Dr '.$value['amount'].'','Cash A/C Dr '.$value['amount'].'','To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$entry_time=NULL,2,Auth::user()->id,$is_contra=NULL,$contra_id=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);


			                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head_id,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$jv_unique_id=NULL,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),1,Auth::user()->id);


			                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1,$head2,$head3,$head4,$head5,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],getMemberData($fData['associatemid'])->first_name.' '.getMemberData($fData['associatemid'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$value['amount_date']);*/

			                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,28,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','DR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$jv_unique_id=NULL,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),1,Auth::user()->id);

			                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,28,71,$head5=NULL,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$branch_id_from=NULL,$value['amount'],$value['amount'],$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],getMemberData($fData['associatemid'])->first_name.' '.getMemberData($fData['associatemid'])->last_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,2,Auth::user()->id,$value['amount_date']);*/

			                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,3,30,$insertedid,$createDayBook,$fData['associatemid'],$member_id=NULL,$branch_id,$bank_id=NULL,$account_id=NULL,$value['amount'],'To '.$investmentAccount.' A/C Cr '.$value['amount'].'','CR',0,'INR',$branch_id,$bName,$fData['associatemid'],$associaateNAme,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$entry_time=NULL,2,Auth::user()->id,date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) ),$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);

			                $createBankCash = CommanTransactionsController::createBankCash($branch_id,date('Y-m-d'),$value['amount'],0);

			                $createBankCash = CommanTransactionsController::createBankClosing($branch_id,date('Y-m-d'),$value['amount'],0);

	                   	}

		                /************* Head Implement **************/

	                   	if($key>0)

	                   	{

	                   		/*-------------------------------  Commission  Section Start ------------------------------------*/

	                        $dateForRenew=date("Y-m-d", strtotime( str_replace('/','-',$value['amount_date'] ) ) );

	                        $Commission=getMonthlyWiseRenewalNew($insertedid,$value['amount'],$dateForRenew); 

	                        foreach ($Commission as  $val) {

	                            $tenureMonth=$investmentDetail->tenure*12;

	                             $commission =CommanController:: commissionDistributeInvestmentRenew($fData['associatemid'],$insertedid,3,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);

	                             $commission_collection =CommanController::commissionCollectionInvestmentRenew($fData['associatemid'],$insertedid,5,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);

	                        /*----- ------  credit business start ---- ---------------*/  

	                            $creditBusiness =CommanController::associateCreditBusiness($fData['associatemid'],$insertedid,1,$val['amount'],$val['month'],$investmentDetail->plan_id,$tenureMonth,$createDayBook);

	                        /*----- ------  credit business end ---- ---------------*/        

	                        }

	                    /*-----------------------------  Commission  Section End -------------------------------------*/

	                    	if($investmentDetail){

		                        $sResult = Memberinvestments::find($insertedid); 

		                        $totalbalance = $investmentDetail->current_balance+$value['amount'];

			                    $investData['current_balance'] = $totalbalance;

		                        $sResult->update($investData);

		                    }else{

		                        $totalbalance = '';

		                    }



	                   	}

	                   	$transaction = $this->transactionData($satRefId,$fData,$insertedid,$value['amount'],$createTransaction);

	                	$ipTransaction = Investmentplantransactions::create($transaction); 

	                	$ipTransactionData['is_renewal'] = 0;

	                    $ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$value['amount_date'] ) ) );

	                    $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);

	                    $updateipTransaction->update($ipTransactionData);	

					}

				}

				$mDetail = Member::select('reinvest_old_account_number')->where('id',$id)->first();

				$aNumbersArray = explode(',', $mDetail->reinvest_old_account_number);

				$existsAccount = countInvestmentExists($mDetail->reinvest_old_account_number);

				if(count($aNumbersArray) == $existsAccount){

					$mData['is_block'] = 0;

		        	$m = Member::find($id);

		        	$m->update($mData);

				}

				

        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        if ($insertedid) {

            return back()->with('success', 'Saved Successfully!');

        } else {

            return back()->with('alert', 'Problem With Register New Plan');

        }

	}



	/**

	 * @param Request $request

	 * @return Create member for reinvest and waiting for approve from admin

	 */

	public function update(Request $request)

	{

		//echo "<pre>"; print_r($request->all()); die;

		$rules = [

			'form_no' => ['required','numeric'],

			'first_name' => ['required'],

		];



		$customMessages = [

			'required' => 'Please enter :attribute.',

			'unique' => ' :Attribute already exists.'



		];

		$this->validate($request, $rules, $customMessages);



		DB::beginTransaction();

		try {



			$data = $this->getMemberData($request->all(),'create');

			$data['reinvest_approve_status']=0;

	        $member = Member::find($request['mId']);

	        $member->update($data);	

			$memberId = $request['mId'];



			// upload signature or profile picture

			$signature_filename='';

			$photo_filename='';

			if ($request->hasFile('signature')) {

				$signature_image = $request->file('signature');

				$signature_filename = $memberId.'_'.time().'.'.$signature_image->getClientOriginalExtension();

				$signature_location = 'asset/profile/member_signature/' . $signature_filename;



				Image::make($signature_image)->resize(300,300)->save($signature_location);



			}



			if ($request->hasFile('photo')) {

				$photo_image = $request->file('photo');

				$photo_filename = $memberId.'_'.time().'.'.$photo_image->getClientOriginalExtension();

				$photo_location = 'asset/profile/member_avatar/' . $photo_filename;



				Image::make($photo_image)->resize(300,300)->save($photo_location);



			}

			//echo $signature_filename.'--'.$photo_filename; die;

			if($signature_filename != ''){

				$memberUpdate = Member::find($memberId);

				$memberUpdate->signature=$signature_filename;

				$memberUpdate->save();	

			}



			if($photo_filename != ''){

				$memberUpdate = Member::find($memberId);

				$memberUpdate->photo=$photo_filename;

				$memberUpdate->save();	

			}



			$mbId = \App\Models\MemberBankDetail::select('id')->where('member_id',$memberId)->first();

			$mnId = \App\Models\MemberNominee::select('id')->where('member_id',$memberId)->first();

			$mipId = \App\Models\MemberIdProof::select('id')->where('member_id',$memberId)->first();



			$dataBank = $this->getBankData($request->all(),'create',$memberId);

			if($mbId && isset($mbId)){

				$bankInfoSave = \App\Models\MemberBankDetail::find($mbId['id']);

          		$bankInfoSave->update($dataBank); 

			}else{

				$bankInfoSave = \App\Models\MemberBankDetail::create($dataBank);

			}

			

			// Save nominee information

			$dataNominee = $this->getNomineeData($request->all(),'create',$memberId);

			if($mnId && isset($mnId)){

	           	$dataNomineeSave = \App\Models\MemberNominee::find($mnId['id']);

	           	$dataNomineeSave->update($dataNominee);

	        }else{

				$dataNominee = \App\Models\MemberNominee::create($dataNominee);

			}



			// Save Id proofs

			$dataIdProof = $this->getIdProofData($request->all(),'create',$memberId);

			if($mipId && isset($mipIds)){

	           	$idProofInfoSave = \App\Models\MemberIdProof::find($mipId['id']);

	           	$idProofInfoSave->update($dataIdProof);

	       	}else{

				$dataNominee = \App\Models\MemberIdProof::create($dataIdProof);

			}



           	



		    $reAccountNumbers = explode(',', $request['aNumber']);



		    foreach ($reAccountNumbers as $key => $value) {



		    	$redfId = ReinvestData::select('id')->where('account_number',$value)->where('form_type','reinvest_plane')->first();

		    	

		    	if($redfId){

			    	$allData['form_type'] = $request->plan_from_type;

				    $allData['account_number'] = $value;

				    $allData['form_data'] = json_encode($request->all());

					$reinvestData = ReinvestData::find($redfId['id']);

	           		$reinvestData->update($allData);

	           	}



           		if( $request->transaction_from_type == 'reinvest_transaction') {

			    	if ( isset( $request->ramount ) ) {

					    $rdata = array();

					    $redsId = ReinvestData::select('id')->where('account_number',$value)->where('form_type','reinvest_transaction')->first();

					    foreach ( $request->ramount as $key => $ramount ) {

						    $innerData = array();

						    if ( $ramount ) {

							    $innerData['amount_date'] = $request->renewal_date[$key];

							    $innerData['amount'] = $ramount;

							    $rdata[] = $innerData;

						    }

					    }

					    if($redsId){

					    	$rdata['closing_Balance_reinvest'] = $request->closing_Balance_reinvest;

						    $rdata['opening_Balance_reinvest'] = $request->opening_Balance_reinvest;

						    $rdata['total_reinvest_amount'] = $request->total_reinvest_amount;

						    $rdata['collection_reinvest_amount'] = $request->collection_reinvest_amount;

						    $rdata['payment_mode'] = $request->payment_mode;

						    $alltData['form_data'] = json_encode($rdata);

						    $reinvesttData = ReinvestData::find($redsId['id']);

		           			$reinvesttData->update($alltData);	

					    }

				    }

			    }

		    }

		    

			DB::commit();

		} catch (\Exception $ex) {

			DB::rollback();

			return back()->with('alert', $ex->getMessage());

		}



		if ($reinvestData) {

			return redirect()->route('admin.reinvest')->with('success', 'Update was Successful!');

        } else {

            return back()->with('alert', 'An error occured');

        }

	}



	/**

     * Get investment plans data to store.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getData($fData,$type,$miCode,$investmentAccount,$branch_id)

    {

        $plantype = $fData['plan_type'];

        $faCode=getPlanCode($fData['investmentplan']);

        switch ($plantype) {

          case "saving-account":

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            /*if($request['hidden_primary_account']){

              $data['primary_account'] = $request['primary_account'];

            }*/

            break;

          case "samraddh-kanyadhan-yojana":

            $data['guardians_relation'] = $fData['guardian-ralationship'];

            $data['daughter_name'] = $fData['daughter-name'];

            $data['phone_number'] = $fData['phone-number'];

            $data['dob'] = date("Y-m-d", strtotime( str_replace('/', '-', $fData['dob'])));

            $data['age'] = $fData['age'];

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            $data['tenure'] = $fData['tenure'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

          case "special-samraddh-money-back":

            $data['ssb_account_number'] = $fData['ssbacount'];

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            $data['tenure'] = $fData['tenure'];

            break;

          case "flexi-fixed-deposit":

            $tenure = 120;

            if($tenure == 12){

              $tenurefacode = $faCode.'001';

            }elseif($tenure == 24){

              $tenurefacode = $faCode.'002';

            }elseif($tenure == 36){

              $tenurefacode = $faCode.'003';

            }elseif($tenure == 48){

              $tenurefacode = $faCode.'004';

            }elseif($tenure == 60){

              $tenurefacode = $faCode.'005';

            }elseif($tenure == 72){

              $tenurefacode = $faCode.'006';

            }elseif($tenure == 84){

              $tenurefacode = $faCode.'007';

            }elseif($tenure == 96){

              $tenurefacode = $faCode.'008';

            }elseif($tenure == 108){

              $tenurefacode = $faCode.'009';

            }elseif($tenure == 120){

              $tenurefacode = $faCode.'010';

            }

            $data['tenure'] = 120/12;

            $data['tenure_fa_code'] = $tenurefacode;

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

          case "fixed-recurring-deposit":

            $data['tenure'] = $fData['tenure']/12;

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            //$data['due_amount'] = $request['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

          case "samraddh-jeevan":

            $data['tenure'] = $fData['tenure']/12;

            $data['ssb_account_number'] = $fData['ssbacount'];

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            //$data['due_amount'] = $request['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

          case "daily-deposit":

            $tenure = $fData['tenure'];

            if($tenure == 12){

              $tenurefacode = $faCode.'001';

            }elseif($tenure == 24){

              $tenurefacode = $faCode.'002';

            }elseif($tenure == 36){

              $tenurefacode = $faCode.'003';

            }elseif($tenure == 48){

              $tenurefacode = $faCode.'004';

            }elseif($tenure == 60){

              $tenurefacode = $faCode.'005';

            }

            $data['tenure'] = $fData['tenure']/12;

            $data['tenure_fa_code'] = $tenurefacode;

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            //$data['due_amount'] = $request['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['interest_rate'] = $fData['interest-rate'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            break;

          case "monthly-income-scheme":

            $data['ssb_account_number'] = $fData['ssbacount'];

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['tenure'] = $fData['tenure']/12;

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest_rate'];

            break;

          case "fixed-deposit":

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['tenure'] = $fData['tenure']/12;

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

          case "recurring-deposit":

            $tenure = $fData['tenure'];

            if($tenure == 36){

              $tenurefacode = $faCode.'002';

            }elseif($tenure == 60){

              $tenurefacode = $faCode.'003';

            }elseif($tenure == 84){

              $tenurefacode = $faCode.'004';

            }else{

              $tenurefacode = $faCode.'001';

            }

            $data['tenure'] = $fData['tenure']/12;

            $data['tenure_fa_code'] = $tenurefacode;

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            //$data['due_amount'] = $request['amount'];

            $data['payment_mode'] = $fData['payment-mode'];

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

          case "samraddh-bhavhishya":

            $data['deposite_amount'] = $fData['amount'];

            //$data['current_balance'] = $fData['amount'];

            $data['tenure'] = $fData['tenure']/12;

            $data['maturity_amount'] = $fData['maturity-amount'];

            $data['interest_rate'] = $fData['interest-rate'];

            break;

        }

        if($type=='create'){

            if($plantype=='saving-account'){

              $data['ssb_account_number'] = $investmentAccount;

            }



            $data['mi_code'] = $miCode;

            $data['account_number'] = $investmentAccount;

            $data['plan_id'] = $fData['investmentplan'];

            $data['form_number'] = $fData['form_number'];

            $data['member_id'] = $fData['memberAutoId'];

            $data['associate_id'] = $fData['associatemid'];

            $data['branch_id'] = $branch_id;

        }

        return $data;

    }



    /**

     * Get member data to save.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getMemberData($request,$type)

    {



        if($type=='create')

        {

            $data['re_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['application_date'] ) ) );

        }

        $data['form_no'] = $request['form_no'];

        $data['first_name'] = $request['first_name'];

        $data['associate_id'] = $request['associate_id'];

        $data['associate_code'] = $request['associate_code'];

        $data['last_name'] = $request['last_name'];

        $data['mobile_no'] = $request['mobile_no'];

        $data['email'] = $request['email'];

        $data['dob'] = date("Y-m-d", strtotime( str_replace('/','-', $request['dob'] ) ) );

        $data['age'] = $request['age'];

        if(isset($request['gender']))

        {

            $data['gender'] = $request['gender'];

        }

        $data['annual_income'] = $request['annual_income'];

        $data['occupation_id'] = $request['occupation'];

        if(isset($request['marital_status']))

        {

            $data['marital_status'] = $request['marital_status'];

        }

        if ( isset($request['anniversary_date']) && $request['anniversary_date'] != null ) {

            $data['anniversary_date'] = date("Y-m-d", strtotime(convertDate($request['anniversary_date'])));

        } else {

            $data['anniversary_date'] = null;

        }

        $data['father_husband'] = $request['f_h_name'];

        $data['address'] = $request['address'];

        $data['state_id'] = $request['state_id'];

        $data['district_id'] = $request['district_id'];

        $data['city_id'] = $request['city_id'];

        $data['village'] = $request['village_name'];

        $data['pin_code'] = $request['pincode'];

        $data['religion_id'] = $request['religion'];

        $data['mother_name'] = $request['mother_name'];

        $data['special_category_id'] = $request['special_category'];

        $data['status'] = 1;

        $data['is_block'] = 1;

        $data['reinvest_opening_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['application_date'] ) ) );

        return $data;

    }



    /**

     * Get saving account id.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getSavingAccountDetails($mId)

    {

        $getDetails = SavingAccount::where('member_id',$mId)->get();

        return $getDetails;

    }



    /**

     * Get savving account transaction log data to store.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function savingAccountTransactionData($fData,$investmentId,$type)

    {

        $sAccount = $this->getSavingAccountDetails($fData['memberAutoId']);

        if(count($sAccount) > 0){

          $ssbAccountNumber = $sAccount[0]->account_no;

          $ssbId = $sAccount[0]->id;

          $ssbBalance = $sAccount[0]->balance;

        }else{

          $ssbAccountNumber = '';

          $ssbId = '';

          $ssbBalance = '';

        }

        $data['saving_account_id'] = $ssbId;

        $data['account_no'] = $ssbAccountNumber;

        $data['opening_balance'] = $ssbBalance-$fData['amount'];

        $data['withdrawal'] = $fData['amount'];

        $data['description'] = $ssbAccountNumber.'/Auto debit';

        $data['currency_code'] = 'INR';

        $data['payment_type'] = 'DR';

        $data['payment_mode'] = 3;

        //$data['reference_no'] = '';

        $data['status'] = 1;

        return $data;

    }



    /**

     * Get comman transaction log data to store.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function commonTransactionLogData($satRefId,$fData,$investmentId,$type)

    {

        $amount_deposit_by_name = substr($fData['member_name'], 0, strpos($fData['member_name'], "-"));

        $getBranchId=getUserBranchId(Auth::user()->id);

        $branch_id=$getBranchId->id;

        $getBranchCode=getBranchCode($branch_id);

        $branchCode=$getBranchCode->branch_code;

        $sAccount = $this->getSavingAccountDetails($fData['memberAutoId']);

        $investmentAccount = $this->getInvestmentAccountNumber($investmentId);

        if(count($sAccount) > 0){

          $ssbAccountNumber = $sAccount[0]->account_no;

          $ssbId = $sAccount[0]->id;

        }else{

          $ssbAccountNumber = '';

          $ssbId = '';

        }

        $amountArraySsb=array('1'=>$fData['amount']);



        switch ($fData['payment-mode']) {

          case "0":

            $createTransaction = CommanController::createTransaction($satRefId=NULL,2,$investmentId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,0,$amount_deposit_by_name,$fData['memberAutoId'],$fData['account_number_for_reinvest'],$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$ssbId,'CR');

            break;

          case "1":

            $createTransaction = CommanController::createTransaction($satRefId=NULL,2,$investmentId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,1,$amount_deposit_by_name,$fData['memberAutoId'],$fData['account_number_for_reinvest'],$fData['cheque-number'],$fData['bank-name'],$fData['branch-name'],$fData['cheque-date'],$fData['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');

            break;

          case "2":

            $createTransaction = CommanController::createTransaction($satRefId=NULL,2,$investmentId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,3,$amount_deposit_by_name,$fData['memberAutoId'],$fData['account_number_for_reinvest'],$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$ssbId,'CR');

            break;

          case "3":

            $createTransaction = CommanController::createTransaction($satRefId=NULL,2,$investmentId,$fData['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,4,$amount_deposit_by_name,$fData['memberAutoId'],$fData['account_number_for_reinvest'],$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$ssbId,'CR');

            break;

        }

        return $createTransaction;

    }



    /**

     * Get investment plans transaction data to store.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function transactionData($satRefId,$fData,$investmentId,$value,$transactionId)

    {

        $getBranchId=getUserBranchId(Auth::user()->id);

        $branch_id=$getBranchId->id;

        $getBranchCode=getBranchCode($branch_id);

        $branchCode=$getBranchCode->branch_code;

        $sAccount = $this->getSavingAccountDetails($fData['memberAutoId']);

        $data['transaction_ref_id'] = $satRefId;

        $data['investment_id'] = $investmentId;

        $data['plan_id'] = $fData['investmentplan'];

        $data['member_id'] = $fData['memberAutoId'];

        $data['branch_id'] = $branch_id;

        $data['branch_code'] = $branchCode;

        $data['deposite_amount'] = $value;

        $data['deposite_date'] = date("Y-m-d", strtotime( str_replace('/','-',$fData['cheque-date'] ) ) );

        if($fData['plan_type']=='saving-account')

        {

        	$data['payment_mode'] = 0;

        }

        else

        {

        	$data['payment_mode'] = $fData['payment-mode'];

        }

        

        if(count($sAccount)>0)

        {

        	$data['saving_account_id'] = $sAccount[0]->id;

        }

        else{

        	$data['saving_account_id'] = NULL;

        }

        $data['transaction_id'] = $transactionId;

        return $data;

    }



    /**

     * Get investment plans payment method data to store.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getPaymentMethodData($fData,$investmentId,$type)

    {

        switch ($fData['payment-mode']) {

          case "1":

            $data['cheque_number'] = $fData['cheque-number'];

            $data['bank_name'] = $fData['bank-name'];

            $data['branch_name'] = $fData['branch-name'];

            $data['cheque_date'] = date("Y-m-d", strtotime( str_replace('/','-',$fData['cheque-date'] ) ) );

            break;

          default:

            $data['transaction_id'] = $fData['transaction-id'];

           $data['transaction_date'] = date("Y-m-d", strtotime( str_replace('/','-',$fData['date'] ) ) );

        }

        $data['investment_id'] = $investmentId;

        return $data;

    }



    /**

     * Get investment plans first nominee data to store.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getFirstNomineeData($fData,$investmentId,$type)

    {

        $data = [

            'investment_id' => $investmentId,

            'nominee_type' => 0,

            'name' => $fData['fn_first_name'],

            //'second_name' => $fData['fn_second_name'],

            'relation' => $fData['fn_relationship'],

            'gender' => $fData['fn_gender'],

            'dob' => date("Y-m-d", strtotime( str_replace('/','-',$fData['fn_dob'] ) ) ),

            'age' => $fData['fn_age'],

            'percentage' => $fData['fn_percentage'],

            //'phone_number' => $fData['fn_mobile_number'],

        ];

        return $data;

    }



    /**

     * Get investment plans second nominee data to store.

     *

     * @param  \Illuminate\Http\Request  $fData

     * @return \Illuminate\Http\Response

     */

    public function getSecondNomineeData($fData,$investmentId,$type)

    {

        $data = [

            'investment_id' => $investmentId,

            'nominee_type' => 1,

            'name' => $fData['sn_first_name'],

            //'second_name' => $fData['sn_second_name'],

            'relation' => $fData['sn_relationship'],

            'gender' => $fData['sn_gender'],

            'dob' => date("Y-m-d", strtotime( str_replace('/','-',$fData['sn_dob'] ) ) ),

            'age' => $fData['sn_age'],

            'percentage' => $fData['sn_percentage'],

            //'phone_number' => $request['sn_mobile_number'],

        ];

        return $data;

    }



    /**

     * Get member bank information for update.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getBankData($request,$type,$memberId)

    {

        $data['member_id'] = $memberId;

        $data['bank_name'] = $request['bank_name'];

        $data['branch_name'] = $request['bank_branch_name'];

        $data['account_no'] = $request['bank_account_no'];

        $data['ifsc_code'] = $request['bank_ifsc'];

        $data['address'] = $request['bank_branch_address'];

        return $data;

    }



    /**

     * Get member nominee detail for update.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getNomineeData($request,$type,$memberId)

    {

        if($type=='create')

        {

            $data['member_id'] = $memberId;

        }

        $data['name'] = $request['nominee_first_name'];

        $data['relation'] = $request['nominee_relation'];

        if(isset($request['nominee_gender']))

        {

            $data['gender'] = $request['nominee_gender'];

        }

        if(isset($request['nominee_dob'])  && $request['nominee_dob']!='')

        {

            $data['dob'] = date("Y-m-d", strtotime( str_replace('/','-', $request['nominee_dob'] ) ) );

        }

        $data['age'] = $request['nominee_age'];

        $data['mobile_no'] = $request['nominee_mobile_no'];

        if(isset($request['is_minor']) && !empty($request['is_minor'])){

            $data['is_minor'] = $request['is_minor'];

            $data['parent_name'] = $request['parent_nominee_name'];

            $data['parent_no'] = $request['parent_nominee_mobile_no'];

        }

        else

        {

            $data['is_minor'] = 0;

        }

        return $data;

    }



    /**

     * Get member Id proof for update.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getIdProofData($request,$type,$memberId)

    {

        if($type=='create')

        {

            $data['member_id'] = $memberId;

        }

        $data['first_id_type_id'] = $request['first_id_type'];

        $data['first_id_no'] = $request['first_id_proof_no'];

        $data['first_address'] = $request['first_address_proof'];

        $data['second_id_type_id'] = $request['second_id_type'];

        $data['second_id_no'] = $request['second_id_proof_no'];

        $data['second_address'] = $request['second_address_proof'];

        return $data;

    }



    /**

     * Get investment plan account number.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getInvestmentAccountNumber($id)

    {

        $getId = Memberinvestments::select('account_number')->where('id',$id)->get();

        return $getId[0]->account_number;

    }



    /**

     * Get investment plan detail.

     *

     * @param  \Illuminate\Http\Request  $request

     * @return \Illuminate\Http\Response

     */

    public function getInvestmentPlanDetail($id)

    {

        $getDetails = Memberinvestments::where('id',$id)->first();

        return $getDetails;

    }

}

