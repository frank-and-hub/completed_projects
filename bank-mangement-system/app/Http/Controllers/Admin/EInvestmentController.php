<?php





namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Admin\CommanController;



use Illuminate\Http\Request;



use Auth;



use App\Models\Settings;



use App\Models\Memberinvestments;



use App\Models\Daybook;



use App\Http\Controllers\Controller;



use App\Models\Branch;



use App\Models\CashInHand;



use App\Models\EliMoneybackInvestments;



use App\Models\SavingAccount;



use App\Models\SavingAccountTranscation;



use App\Models\TransactionReferences;



use App\Models\InvestmentMonthlyYearlyInterestDeposits;



use Yajra\DataTables\DataTables;



use Carbon\Carbon;



use Session;



use DB;





class EInvestmentController extends Controller

{



	public function __construct()



	{



		$this->middleware('auth');



	}



	



	public function index(){



		



		$data['title'] = "ELI MIS | Money Back";



		$data['transaction'] = "";



		$data['record']= '';



		return view('templates.admin.e_investment.form',$data);



		



		



	}



	



	public function getReinvestAccountDetail(Request $request)

	{	

		$data['record']= Memberinvestments::with('ssb')->where('plan_id',3)->where('account_number',$request->account_no)->first();

		if($data['record']){

			$data['eliAmount'] = Daybook::select('deposit')->where('investment_id',$data['record']->id)->where('is_eli',1)->first();

			$data['count'] = 1;

			if($data['record']->is_mature == 0){

				$data['count'] = 3;

			}elseif($data['record']->mb_payment_settled == 0){

				$data['count'] = 2;

			}

		}else{

			$data['count'] = 0;

		}

		$data['title'] = "ELI MIS | Money Back";

		return response()->json($data);

	}



	



	public function transaction_list(Request $request)



	{



		if($request->ajax()){



			$arrFormData = array();   



            if(!empty($_POST['searchform']))



            {



                foreach($_POST['searchform'] as $frm_data)



                {



                    $arrFormData[$frm_data['name']] = $frm_data['value'];



                }



            }



			



			$data= Daybook::select('id','transaction_id','created_at','description','payment_mode','cheque_dd_no','reference_no','online_payment_id','withdrawal','deposit','opening_balance','account_no')->where('account_no',$arrFormData['account_no'])->get();



			



			return Datatables::of($data)



			->addIndexColumn()



			->addColumn('transaction_id',function($row){



				return $row->transaction_id;



			})


			->rawColumns(['transaction_id'])



			->addColumn('date',function($row){



				return date("d/m/Y", strtotime($row->created_at));



			})



			->rawColumns(['date'])



			->addColumn('description',function($row){



				return $row->description;



			})




			



			->rawColumns(['description'])



			->addColumn('ref_no',function($row){



				$ref_no = '';



				  if($row->payment_mode==1)



                      {



                      	$ref_no = $row->cheque_dd_no;



                      }



                      if($row->payment_mode==4 || $row->payment_mode==5 )



                      {



                      	$ref_no = $row->reference_no;



                      }



                      if($row->payment_mode==3)



                      {



                      	$ref_no = $row->online_payment_id;



                      }



                    



                    return $ref_no;



			})



			->rawColumns(['ref_no'])



			->addColumn('withdrawal',function($row){



				if($row->withdrawal > 0){



					return $row->withdrawal;



				}



			})



			->rawColumns(['withdrawal'])



			->addColumn('deposite',function($row){



				if($row->deposit > 0){



					return $row->deposit;



				}



			})



			->rawColumns(['deposite'])



			->addColumn('balance',function($row){



				return $row->opening_balance;



			})



			->rawColumns(['balance'])



			->make(true);



		}



	}



	public function saveReinvestMbData(Request $request)

	{

		DB::beginTransaction();

        try {



			$entryTime = date("H:i:s");

			$rules = [

	            'eli_amount' => 'required',

	            'ac_deno' => 'required',

	            'ac_opening_date' => 'required',

	            'mb_date' => 'required',

	            'mb_amount' => 'required',

	            'mb_transfer' => 'required',

	            'mb_inst' => 'required',

	            'mbfd_amount' => 'required',

	            'ssb_ac' => 'required',

	            'balance' => 'required',

	        ];



	        $customMessages = [



	            'required' => 'The :attribute field is required.'



	        ];



	        $this->validate($request, $rules, $customMessages);		



	        Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($request->mb_date))));

	        $mbData = [

	            'investment_id' => $request->investmentId,

	            'account_number' => $request->accountNumber,

	            'opening_date' => date("Y-m-d", strtotime(convertDate($request->ac_opening_date))),

	            'money_back_date' => date("Y-m-d", strtotime(convertDate($request->mb_date))),

	            'mb_amount' => $request->mb_amount,

	            'mb_trf' => $request->mb_transfer,

	            'mb_interest' => $request->mb_inst,

	            'mb_fd_amount' => $request->mbfd_amount,

	        ];

	        $mbInvestment = EliMoneybackInvestments::create($mbData);



			/*InvestmentMonthlyYearlyInterestDeposits::create([

			    'investment_id' => $request->investmentId,

			    'plan_type_id' =>3,

			    'fd_amount' => $request->total_amount+$request->mb_inst,

			    'available_amount_fd' => 0,

			    'yearly_deposit_amount' => $request->mb_amount,

			    'available_amount' => $request->mbfd_amount,

			    'date' =>date("Y-m-d", strtotime(convertDate($request->ac_opening_date))),

			]);*/



			$investmentDetails = Memberinvestments::with('ssb')->where('id',$request->investmentId)->first();

	        $ssbAccountDetails = SavingAccount::with('ssbMember')->where('account_no',$request->ssb_ac)->first();

			$mlResult = Memberinvestments::find($request->investmentId);

			$investmentData['current_balance'] = $investmentDetails->current_balance-$request->mb_amount;

	        $investmentData['last_deposit_to_ssb_amount'] = $request->mb_amount;
	        $investmentData['carry_forward_amount'] = $request->carryforwardamount;
	        $investmentData['last_deposit_to_ssb_date'] = date("Y-m-d", strtotime(convertDate($request->mb_date)));

	        $investmentData['mb_payment_settled'] = 0;

	        $mlResult->update($investmentData);

	        //Memberinvestments::where('id', $request->investmentId)->where('last_deposit_to_ssb_amount', $request->mb_amount)->where('last_deposit_to_ssb_date', date("Y-m-d", strtotime(convertDate($request->mb_date))))->update(['mb_payment_settled' => 0]);

	        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

		    $vno = "";

		    for ($k = 0; $k < 10; $k++) {

		        $vno .= $chars[mt_rand(0, strlen($chars)-1)];

		    }        



		    $branch_id = $investmentDetails->branch_id;

		    $type = 3;

		    $sub_type = 36;

		    $type_id = $request->investmentId;

		    $type_transaction_id = $request->investmentId;

		    $associate_id = NULL;

		    $member_id = $ssbAccountDetails->member_id;

		    $branch_id_to = NULL;

		    $branch_id_from = NULL;

		    $opening_balance = $request->mb_amount;

		    $amount = $request->mb_amount;

		    $closing_balance = $request->mb_amount;

		    $description = ($request->mb_amount).' Money Back amount '.number_format((float)$amount, 2, '.', '');

		    $description_dr = getMemberData($ssbAccountDetails->member_id)->first_name.' '.getMemberData($ssbAccountDetails->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');

		    $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

		    $payment_type = 'CR';

		    $payment_mode = 3;

		    $currency_code = 'INR';

		    $amount_to_id =$ssbAccountDetails->member_id;

		    $amount_to_name = getMemberData($ssbAccountDetails->member_id)->first_name.' '.getMemberData($ssbAccountDetails->member_id)->last_name;

		    $amount_from_id = NULL;

		    $amount_from_name = NULL;

		    $v_no = $vno;

		    $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->mb_date)));

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

		    $created_by_id = 1;

		    $is_contra = NULL;

		    $contra_id = NULL;

		    $created_at = NULL;

		    $bank_id = NULL;

		    $bank_ac_id = NULL;

		    $transction_bank_to_name = NULL;

		    $transction_bank_to_ac_no = NULL;

		    $transction_bank_to_branch = NULL;

		    $transction_bank_to_ifsc = NULL;

		    $jv_unique_id = NULL;

	        $cheque_type = NULL;

	        $cheque_id = NULL;

	        $cheque_bank_from_id = NULL;

	        $cheque_bank_ac_from_id = NULL;

	        $cheque_bank_to_name = NULL;

	        $cheque_bank_to_branch = NULL;

	        $cheque_bank_to_ac_no = NULL;

	        $cheque_bank_to_ifsc = NULL;

	        $transction_bank_to_name = NULL;

	        $transction_bank_to_ac_no = NULL;

	        $transction_bank_to_branch = NULL;

	        $transction_bank_to_ifsc = NULL;

	        $transction_bank_from_id = NULL;

	        $transction_bank_from_ac_id = NULL;

	        $ssb_account_tran_id_from = NULL;

		    if($request->mb_amount > 0){

			    /*$record2=SavingAccountTranscation::where('account_no',$request->ssb_ac)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($request->mb_date))))->get();  

		        foreach ($record2 as $key => $value) {

		            $nsResult = SavingAccountTranscation::find($value->id);

		            $sResult['opening_balance']=$value->opening_balance+$request->mb_amount;

		            $sResult['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->mb_date)));

		            $nsResult->update($sResult);

		        }*/

		        $record1=SavingAccountTranscation::where('account_no',$request->ssb_ac)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($request->mb_date))))->first();

		        $description = ($request->ssb_ac).' Money Back amount '.number_format((float)$request->mb_amount, 2, '.', '');

		        $ssb['saving_account_id']=$ssbAccountDetails->id;

		        $ssb['account_no']=$ssbAccountDetails->account_no;

		        if($record1){

		        	$ssb['opening_balance']=$record1->opening_balance+$request->mb_amount;

		        }else{

		        	$ssb['opening_balance']=$request->balance+$request->mb_amount;

		        }

		        //$ssb['opening_balance']=$record1->opening_balance+$request->mb_amount;

		        $ssb['branch_id']=$investmentDetails->branch_id;

		        $ssb['type']=10;

		        $ssb['deposit']=$request->mb_amount;

		        $ssb['withdrawal']=0;

		        $ssb['description']= 'MB Amount received from '.$request->accountNumber;

		        $ssb['currency_code']='INR';

		        $ssb['payment_type']='CR';

		        $ssb['payment_mode']=4;

		        $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request->mb_date)));

		        $ssbAccountTran = SavingAccountTranscation::create($ssb);

		        $ssb_transaction_id = $ssbAccountTran->id;     

		        // update saving account current balance 

		        updateSavingAccountTransaction($ssbAccountDetails->id,$ssbAccountDetails->account_no);

		        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);

		        $ssbBalance->balance=$request->balance+$request->mb_amount;

		        $ssbBalance->save();

		        $data['saving_account_transaction_id']=$ssb_transaction_id;

		        $data['investment_id']=$request->investmentId;

		        $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->mb_date)));

		        $satRef = TransactionReferences::create($data);

		        $satRefId = $satRef->id;

		        $ssb_account_id_to = $ssbAccountDetails->id;
		        $ssb_account_tran_id_to = $satRefId;

		        $amountArraySsb = array('1'=>($request->mb_amount));

		        $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$investmentDetails->branch_id,getBranchCode($investmentDetails->branch_id)->branch_code,$amountArraySsb,4,NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,NULL,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->mb_date))),NULL,NULL,$ssbAccountDetails->id,'CR');

		        $totalbalance = $request->balance+$request->mb_amount;

		        $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$totalbalance,$request->mb_amount,$withdrawal=0,$description,NULL,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,4,NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$request->mb_amount,NULL,NULL,date("Y-m-d", strtotime(convertDate($request->mb_date))),NULL,NULL,$ssbAccountDetails->account_no,'CR');

		        $dayBookRef = CommanController::createBranchDayBookReference($amount);

		        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,'MB Amount received from '.$request->accountNumber,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$request->mb_date,$created_by,$created_by_id);

		        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,'MB Amount received from '.$request->accountNumber,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$request->mb_date);*/ 

		        $branchDayBook = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,'MB Amount received from '.$request->accountNumber,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,NULL,NULL,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);



		        $head1 = 1;

		        $head2 = 8;

		        $head3 = 20;

		        $head4 = 59;

		        $head5 = 85;

		        //$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$request->mb_date);

		        $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,'MB Amount received from '.$request->accountNumber,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$request->mb_date,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

		        $vno = "";

			    for ($k = 0; $k < 10; $k++) {

			        $vno .= $chars[mt_rand(0, strlen($chars)-1)];

			    }

			    $elidayBookRef = CommanController::createBranchDayBookReference($request->mbfd_amount);

		        $elidescription = 'MB Amount Transfer to SSB A/C '.$ssbAccountDetails->account_no;

			    $elidescription_dr = ($request->accountNumber).' Dr '.$request->mb_amount;

			    $elidescription_cr = 'ELI Opening Account A/C Cr '.$request->mb_amount;

			    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->mb_amount,$request->mb_amount,$request->mb_amount,$elidescription,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$request->mb_date,$created_by,$created_by_id);

		 		/*$eliAmountTransaction = CommanController::createAllTransaction($elidayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->mb_amount,$request->mb_amount,$request->mb_amount,$elidescription,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$vno,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$request->mb_date);*/

		 		$eliBranchDayBook = CommanController::branchDayBookNew($elidayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->mb_amount,$request->mb_amount,$request->mb_amount,$elidescription,$description_dr,$description_cr,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,NULL,NULL,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

		 		$eliAmountMemberTransaction = CommanController::memberTransactionNew($elidayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$request->mb_amount,$elidescription,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$vno,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$request->mb_date,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

		 		$trdata['saving_account_transaction_id']=NULL;

			    $trdata['investment_id']=$request->investmentId;

			    $trdata['created_at']=date("Y-m-d", strtotime(convertDate($request->mb_date)));

		 		$satRef = TransactionReferences::create($trdata);

		        $satRefId = $satRef->id;

			    $amountArraySsb=array('1'=>$request->mb_amount);

			    $elicreateTransaction = CommanController::createTransaction($satRefId,17,$request->investmentId,$investmentDetails->branch_id,$investmentDetails->branch_id,getBranchCode($investmentDetails->branch_id)->branch_code,$amountArraySsb,4,getBranchName($investmentDetails->branch_id),$investmentDetails->member_id,$investmentDetails->account_number,NULL,NULL,getBranchName($investmentDetails->branch_id),$request->mb_date,NULL,NULL,NULL,'DR');

		    	$cBalance = ($investmentDetails->current_balance-$request->mb_amount);

		    	$elicreateDayBook = CommanController::createDayBook($elicreateTransaction,$satRefId,18,$request->investmentId,NULL,$investmentDetails->member_id,$cBalance,NULL,$request->mb_amount,$elidescription,NULL,$investmentDetails->branch_id,getBranchCode($investmentDetails->branch_id)->branch_code,$amountArraySsb,4,getBranchName($investmentDetails->branch_id),$investmentDetails->branch_id,$investmentDetails->account_number,NULL,NULL,getBranchName($investmentDetails->branch_id),date("Y-m-d", strtotime(convertDate($request->mb_date))),NULL,NULL,$ssbAccountDetails->id,'DR');

		    }else{
		    	$ssb_account_id_to = NULL;
		        $ssb_account_tran_id_to = NULL;
		    }

		    $eliInsdayBookRef = CommanController::createBranchDayBookReference($request->mb_inst);

	 		$eliInsdescription = 'MB Amount Interest Transfer';

		    $eliInsdescription_dr = ($request->accountNumber).' Dr '.$request->mb_inst;

		    $eliInsdescription_cr = 'ELI Opening Account A/C Cr '.$request->mb_inst;

		    if($request->mb_inst > 0){
		    	$dayBookRef = CommanController::createBranchDayBookReference($request->mb_inst);

		    	$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->mb_inst,$request->mb_inst,$request->mb_inst,$eliInsdescription,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$request->mb_date,$created_by,$created_by_id);
		    }

		    /*$dayBookRef = CommanController::createBranchDayBookReference($request->mb_amount);

		    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->mb_amount,$request->mb_amount,$request->mb_amount,$eliInsdescription,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$request->mb_date,$created_by,$created_by_id);*/

	 		/*$eliInsAmountTransaction = CommanController::createAllTransaction($eliInsdayBookRef,$branch_id,$bank_id,$bank_ac_id,4,14,36,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$request->mb_inst,$request->mb_inst,$request->mb_inst,$eliInsdescription,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$vno,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$request->mb_date);*/

		 	
	 	DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }

        return back()->with('success', 'Successfully settled amount!');

	}



	public function getEliDepositeAmount(Request $request){

		$investmentId = $request->investmentId;
		$startDate = $request->openingDate;
		$endStart = $request->dateString;
		$sDate = date("Y-m-d", strtotime(convertDate($startDate)));
		$eDate = date("Y-m-d", strtotime(convertDate($endStart)));
		$depositAmount = Daybook::where('investment_id',$investmentId)->where('is_eli',0)->whereBetween('created_at', [$sDate, $eDate])->sum('deposit');
		$eliAmount = Daybook::select('deposit')->where('investment_id',$investmentId)->where('is_eli',1)->first();
		$mInvestment = getInvestmentDetails($investmentId);

		$total = 0;
        $regularInterest = 0; 
        $cInterest = 0;
        $totalInterestDeposit = 0;
        $investmentMonths = 1*12;
        $yearlyAmount = $depositAmount+$eliAmount->deposit;

		if($yearlyAmount > ($mInvestment->deposite_amount*12)){
        	$carryForwardAmount = $yearlyAmount-($mInvestment->deposite_amount*12);
        	$totalDeposit = $mInvestment->deposite_amount*12;
        }elseif(($mInvestment->deposite_amount*12) >= $yearlyAmount){
        	$carryForwardAmount = 0;
        	$totalDeposit = $yearlyAmount;
        }

        for ($i=1; $i <= $investmentMonths ; $i++){
            $nDate =  date('Y-m-d', strtotime($sDate. ' + '.$i.' months')); 
            $cMonth = date('m');
            $cYear = date('Y');
            $cuurentInterest = $mInvestment->interest_rate;
            $ts1 = strtotime($sDate);
            $ts2 = strtotime($nDate);
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
            $defaulterInterest = 0;
            if($mInvestment->deposite_amount*$monthDiff <= $totalDeposit){
                $aviAmount = $mInvestment->deposite_amount;
                $total = $total+$mInvestment->deposite_amount;
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
            }elseif($mInvestment->deposite_amount*$monthDiff > $totalDeposit){
                $checkAmount = ($totalDeposit-$mInvestment->deposite_amount*($monthDiff-1));
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

        $fdAmount = round($totalDeposit+$totalInterestDeposit);

		$return_array = compact('depositAmount','carryForwardAmount','totalDeposit','fdAmount');

        return json_encode($return_array);

	}

}