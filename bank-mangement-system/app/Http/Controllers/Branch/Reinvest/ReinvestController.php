<?php

namespace App\Http\Controllers\Branch\Reinvest;

use App\Models\Reinvest;
use App\Models\ReinvestMemberNominee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\CommanController;
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
use App\Http\Controllers\Branch\CommanTransactionsController;
use Carbon\Carbon;
use DB;
use Image;
use App\Models\Plans;
use App\Models\ReinvestData;
use App\Models\Daybook;
use App\Models\Transcation;
use App\Models\TranscationLog;
use App\Services\ImageUpload;

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

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 * Show form for reinvest
	 */
    public function index()
    {
        if(!in_array('Reinvestment Create', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		
		$data['title'] = "Reinvest";
        return view('templates.branch.reinvest.add', $data);
    }

	/**
	 * @param Request $request
	 * @return get member detail for reinvest and auto fill in form
	 */
    public function getInvestment( Request $request )
    {
        $reinvestData = Reinvest::where('ac_code',$request->investAccountNumber)->first();

        $mCount = Member::whereRaw("find_in_set('".$request->investAccountNumber."',reinvest_old_account_number)")->count();
        if ($mCount > 0) {
			$return['count'] = 0;
			$return['message'] = 'Investment Plan Already Created!';
			return $return;
		}
		if ( $reinvestData == null ) {
			$return['count'] = 0;
			$return['message'] = 'Reinvest not found!';
			return $return;
		}
        $data = array();

	    $memberData = Member::where('old_c_id', $reinvestData->c_id)->with('memberNominee', 'memberBankDetails')->first();

	    if ( $memberData ) {
		    $data['count'] = 2;
		    $data['message'] = 'member already exist';
		    $data['plan_id'] = $reinvestData->plan_id;
		    $data['member_detail'] = $memberData;
		    //$data['account_application_date'] = date('d/m/Y', strtotime(str_replace('-', '/', $reinvestData->op_date) ) );
		    $data['account_application_date'] =date('d/m/Y', strtotime(str_replace('-', '/', $reinvestData->op_date) ) ) ;
		    $data['planSlug'] = Plans::find($reinvestData->plan_id)->slug;
		    $data['planName'] = Plans::find($reinvestData->plan_id)->name;
		    return $data;
	    }

	    $data['count'] = 0;
	    $data['message'] = 'Reinvest not found!';
        if ( $reinvestData ) {
            $data['count'] = 1;
            $data['message'] = 'success';
            $data['member_id'] = $reinvestData->c_id;
            $data['plan_id'] = $reinvestData->plan_id;
	        $exDate = explode('/', $reinvestData->op_date);
	        $updateDate = array();
	        if ( $exDate[0] > 12 ) {
		        $updateDate[] = $exDate[1];
		        $updateDate[] = $exDate[0];
		        $updateDate[] = $exDate[2];
		        $newDate = implode('/', $updateDate );

		        $data['account_application_date'] = date('d/m/Y', strtotime(str_replace('-', '/', $newDate) ) );
	        } else {
		        $data['account_application_date'] = date('d/m/Y', strtotime(str_replace('-', '/', $reinvestData->op_date) ) );
	        }
           // $data['account_application_date'] = date('d/m/Y', strtotime(str_replace('-', '/', $reinvestData->op_date) ) );

	        $data['first_name'] = null;
	        $data['last_name'] = null;
	        $data['name'] = null;
	        $data['f_h_name'] = null;
            if ( stripos( $reinvestData->ledeger_name, 'S/O' ) >= 1 ) {
                $dataName = explode('S/O', $reinvestData->ledeger_name );
                $firstLastName = explode(' ', trim($dataName[0], ' ' ) );
                $data['first_name'] = implode(' ', array_slice($firstLastName, 0, count($firstLastName)-1));
                $data['last_name'] = end($firstLastName);
                $data['f_h_name'] = $dataName[1];
            } elseif( stripos( $reinvestData->ledeger_name, 'W/O' ) >= 1 ) {
                $dataName = explode('W/O', $reinvestData->ledeger_name );
                $firstLastName = explode(' ', trim($dataName[0], ' ' ) );
                $data['first_name'] = implode(' ', array_slice($firstLastName, 0, count($firstLastName)-1));
                $data['last_name'] = end($firstLastName);
                $data['name'] = $dataName[0];
                $data['f_h_name'] = $dataName[1];
            } elseif( stripos( $reinvestData->ledeger_name, 'D/O' ) >= 1 ) {
	            $dataName = explode('D/O', $reinvestData->ledeger_name );
	            $firstLastName = explode(' ', trim($dataName[0], ' ' ) );
	            $data['first_name'] = implode(' ', array_slice($firstLastName, 0, count($firstLastName)-1));
	            $data['last_name'] = end($firstLastName);
	            $data['name'] = $dataName[0];
	            $data['f_h_name'] = $dataName[1];
            } elseif( stripos( $reinvestData->ledeger_name, 'U/G' ) >= 1 ) {
	            $dataName = explode('U/U', $reinvestData->ledeger_name );
	            $firstLastName = explode(' ', trim($dataName[0], ' ' ) );
	            $data['first_name'] = implode(' ', array_slice($firstLastName, 0, count($firstLastName)-1));
	            $data['last_name'] = end($firstLastName);
	            $data['name'] = $dataName[0];
	            $data['f_h_name'] = $dataName[1];
            }
            else {
	            $dataName = explode(' ', $reinvestData->ledeger_name );
	            $data['first_name'] = implode(' ', array_slice($dataName, 0, count($dataName)-1));;
	            $data['last_name'] = end($dataName);;
	            $data['name'] = $dataName[0];
                $data['f_h_name'] = null;
            }

            $data['mobile_no'] = $reinvestData->phone_no;
            $data['address'] = preg_replace( "/\r|\n/", "",$reinvestData->address);
            $data['ac_code'] = $reinvestData->ac_code;
            if ( $reinvestData->pan_no ) {
                $data['first_it_type'] = 5;
                $data['first_id_proof_no'] = strtoupper ($reinvestData->pan_no);
            } else {
                $data['first_it_type'] = null;
                $data['first_id_proof_no'] = null;
            }
	        $data['nominee_name'] = $reinvestData->nom_name;
            $data['dob'] = date('d/m/Y', strtotime(str_replace('-', '/', $reinvestData->dob) ) );
            $date = date('Y-m-d', strtotime( str_replace('-', '/', $reinvestData->dob) ) );
            $dateOfBirth = Carbon::createFromFormat('Y-m-d', $date );
            $data['age'] = $dateOfBirth->diffInYears( Carbon::now()->format('Y-m-d') );
        }


        return $data;
    }

	/**
	 * @param Request $request
	 * @return Create member for reinvest and waiting for approve from admin
	 */
	public function save(Request $request)
	{
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
			$investAccount = $request->oldAccountNumber;
			if ( empty( $investAccount ) ) {
				$return['count'] = 0;
				$return['message'] = 'Please enter correct Invest Account Number!';
				return $return;
			} else {
				$memberCount = Member::where('reinvest_old_account_number',$investAccount)->count();
				if ( $memberCount > 0 ) {
					$return['count'] = 0;
					$return['message'] = 'Reinvest Already created!';
					return $return;
				}
			}

			//get login user branch id(branch manager)pass auth id
			$getBranchId=getUserBranchId(Auth::user()->id);
			// pass fa id 1 for member
			$getfaCode=getFaCode(1);
			$faCode=$getfaCode->code;
			$branch_id=$getBranchId->id;
			// pass role_id(5 for member),branch_id,fa_code
			$getMiCode=getLastMiCode(5,$branch_id,$faCode);
			if ( !empty( $getMiCode ) ) {
				if($getMiCode->mi_code==9999998) {
					$miCodeAdd=$getMiCode->mi_code+2;
				} else {
					$miCodeAdd=$getMiCode->mi_code+1;
				}
			} else {
				$miCodeAdd=1;
			}

			$miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
			$getBranchCode=getBranchCode($branch_id);
			$branchCode=$getBranchCode->branch_code;
			// genarate Member id
			$getmemberID=$branchCode.$faCode.$miCode;
			// save member details

			$branchMi=$branchCode.$miCode;
			$data = $this->getData($request->all(),'create');

			$data['role_id']=5;
			$data['branch_id']=$branch_id;
			$data['branch_code']=$branchCode;
			$data['branch_mi']=$branchMi;
			$data['member_id']=$getmemberID;
			$data['mi_code']=$miCode;
			$data['fa_code']=$faCode;
			$data['reinvest_approve_status']=0;
			//  print_r($data);
			$member = Member::create($data);
			$memberId = $member->id;

			/****associate target entry****/
			/*$com_type=1;
			$member_carder=getSeniorData($request['associate_id'],'current_carder_id'));
			$commistionMember = CommanTransactionsController::commissionDistributeMember($request['associate_id'],$memberId,$com_type,$branch_id,$member_carder);*/
			/****associate target entry****/


			// upload signature or profile picture
			$signature_filename='';
			$photo_filename='';
			if ($request->hasFile('signature')) {
				$signature_image = $request->file('signature');
				$signature_filename = $memberId.'_'.time().'.'.$signature_image->getClientOriginalExtension();
				$signature_location = 'asset/profile/member_signature/' . $signature_filename;
				$mainFolderSignature= '/profile/member_signature/';
				ImageUpload::upload($signature_image, $mainFolderSignature,$signature_filename);
				// Image::make($signature_image)->resize(300,300)->save($signature_location);

			}
			if ($request->hasFile('photo')) {
				$photo_image = $request->file('photo');
				$photo_filename = $memberId.'_'.time().'.'.$photo_image->getClientOriginalExtension();
				$photo_location = 'asset/profile/member_avatar/' . $photo_filename;
				$mainFolderPhoto = '/profile/member_avatar/';
				ImageUpload::upload($photo_image, $mainFolderPhoto,$photo_filename);
				// Image::make($photo_image)->resize(300,300)->save($photo_location);

			}
			$memberUpdate = Member::find($memberId);
			$memberUpdate->signature=$signature_filename;
			$memberUpdate->photo=$photo_filename;
			$memberUpdate->save();
			// Save bank Information
			if($request['bank_name']!='' || $request['bank_branch_name']!='' || $request['bank_account_no']!='' || $request['bank_ifsc']!='' || $request['bank_branch_address']!='')
			{
				$dataBank = $this->getBankData($request->all(),'create',$memberId);
				$bankInfoSave=\App\Models\MemberBankDetail::create($dataBank);
			}
			// Save nominee information
			$dataNominee = $this->getNomineeData($request->all(),'create',$memberId);
			$nomineeInfoSave= \App\Models\MemberNominee::create($dataNominee);
			// Save Id proofs
			$dataIdProof = $this->getIdProofData($request->all(),'create',$memberId);
			$idProofInfoSave= \App\Models\MemberIdProof::create($dataIdProof);
			/*
			 //create savings account
				 $amount=90;
				 $payment_mode=0;//0 for cash
				 $createAccount = CommanTransactionsController::createSavingAccount($memberId,$branch_id,$branchCode,$amount,$payment_mode);
				 $ssbAccountId=$createAccount;//sb account id
				 */
			// create transaction
			$transaction_type=0;
			$payment_mode=0;//0 for cash
			$deposit_by_name=$request['first_name'].' '.$request['last_name'];
			$deposit_by_id=$memberId;
			$amountArray=array('1'=>10,'2'=>90);

			$createTransaction = CommanTransactionsController::createTransaction($satRefId=NULL,$transaction_type,$table_id='0',$memberId,$branch_id,$branchCode,$amountArray,$payment_mode,$deposit_by_name,$deposit_by_id,$account_no='0',$cheque_dd_no='0',$bank_name='null',$branch_name='null',$payment_date='null',$online_payment_id='null',$online_payment_by='null',$saving_account_id=0,'CRC');

			$tranID=$createTransaction;//transaction id
			// create recipt

			$typeArray=array('1'=>1,'2'=>2);
			$receipts_for=1;
			$createRecipt = CommanTransactionsController::createPaymentRecipt(0,0,$memberId,$branch_id,$branchCode,$amountArray,$typeArray,$receipts_for,$account_no='0');
			$recipt_id=$createRecipt;
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage());
		}
		$contactNumber = array();
		$contactNumber[] = $data['mobile_no'];
		$text = "Dear " . $data['first_name'].' '.$data['last_name'] .', ';
		$text .= "Welcome to S.B. Micro Finance association, Thank you so much for allowing us to help you with your recent account opening. Have a good day";
		$numberWithMessage = array();
		$numberWithMessage['contactNumber'] = $contactNumber;
		$numberWithMessage['message'] = $text;

		/*$adminEmail = new Email();
		$adminEmail->sendEmail( env('ADMIN_EMAIL','micro@mailinator.com'), $member);*/
		//$sendToMember = new Sms();
		//$sendToMember->sendSms( $contactNumber, $text );
		$dataForReinvestData = array();
		$dataForReinvestData['count'] = 1;
		$dataForReinvestData['message'] = "reinvest data";
		$dataForReinvestData['title'] = 'Reinvest';
		$dataForReinvestData['memberDetail'] = Member::find($memberId);
		$dataForReinvestData['memberId'] = Member::find($memberId)->member_id;
		$dataForReinvestData['mId'] = Member::find($memberId)->id;
		$dataForReinvestData['id'] = $memberId;
		$dataForReinvestData['reInvestAccountNumber'] = 'R-'.$request->oldAccountNumber;
		$dataForReinvestData['openingDate'] = date('Y-m-d', strtotime( str_replace('/', '-', $request->account_application_date)) );
		$dataForReinvestData['openDate'] = $request->account_application_date;
		$dataForReinvestData['planId'] = $request->plan_id;
		$dataForReinvestData['planName'] = Plans::find($request->plan_id)->name;
		$dataForReinvestData['planSlug'] = Plans::find($request->plan_id)->slug;
		return $dataForReinvestData;
	}

    public function saveFormData( Request $request )
    {
	    $rules = [
		    'form_no' => ['required','numeric'],
		    'first_name' => ['required'],
	    ];

	    $customMessages = [
		    'required' => 'Please enter :attribute.',
		    'unique' => ' :Attribute already exists.'

	    ];
	    $this->validate($request, $rules, $customMessages);

    	$data['form_type'] = $request->from_type;
	    $data['account_number'] = $request->oldAccountNumber;
	    $data['account_application_date'] = $request->account_application_date;
	    $data = array();
	    foreach ( $request->amount as $key => $amount ) {
	    	$innerData = array();
		    $innerData['amount_date'] = $request->renewal_date[$key];
		    $innerData['amount'] = $amount;
		    $data[] = $innerData;
	    }


	    $data['form_data'] = json_encode($request->all());

	    ReinvestData::create($data);

	    $dataForReinvestData['reInvestAccountNumber'] = 'R-'.$request->oldAccountNumber;
	    $dataForReinvestData['openingDate'] = date('Y-m-d', strtotime( str_replace('/', '-', $request->account_application_date)) );
	    $dataForReinvestData['openDate'] = $request->account_application_date;
	    $dataForReinvestData['planId'] = $request->plan_id;
	    $dataForReinvestData['planName'] = Plans::find($request->plan_id)->name;
	    $dataForReinvestData['planSlug'] = Plans::find($request->plan_id)->slug;
	    return $dataForReinvestData;
    }

    public function saveForm( Request $request )
    {
    	$getBranchId=getUserBranchId(Auth::user()->id);        
	    $branch_id=$getBranchId->id;
	    $allData['form_type'] = $request->from_type;
    	if ( $request->plan_id_transaction == 1 || $request->plan_id_transaction == 4 || $request->plan_id_transaction == 8 || $request->plan_id_transaction == 9 ) {
		    $allData['form_type'] = 'free_transaction';
	    }

	    $allData['account_number'] = $request->oldAccountNumber;
	    $request['branch_id'] = $branch_id;
	    $allData['form_data'] = json_encode($request->all());

	    if( $request->from_type == 'reinvest_transaction') {
	    	if ( isset( $request->amount ) ) {
			    $data = array();
			    foreach ( $request->amount as $key => $amount ) {
				    $innerData = array();
				    if ( $amount ) {
					    $innerData['amount_date'] =( $request->renewal_date[$key] ) ? $request->renewal_date[$key] : date('Y-m-d') ;
					    $innerData['amount'] = $amount;
					    $data[] = $innerData;
				    }
			    }
			    $data['closing_Balance_reinvest'] = $request->closing_Balance_reinvest;
			    $data['opening_Balance_reinvest'] = $request->opening_Balance_reinvest;
			    $data['total_reinvest_amount'] = $request->total_reinvest_amount;
			    $data['collection_reinvest_amount'] = $request->collection_reinvest_amount;
			    $data['payment_mode'] = $request->payment_mode;
			    $allData['form_data'] = json_encode($data);
		    }

		    $mCount =Member::whereRaw("find_in_set('".$request->oldAccountNumber."',reinvest_old_account_number)")->count();
	        if ($mCount == 0) {
	        	$memberDetail = Member::find($request->member_auto_id);
	        	if ( $memberDetail->reinvest_old_account_number ) {
			        $oldAccountNumber = explode( ',', $memberDetail->reinvest_old_account_number );
			        $oldAccountNumber[] = $request->oldAccountNumber;
			        $allOld = implode(",", $oldAccountNumber);
		        } else {
			        $allOld = $request->oldAccountNumber;
		        }
			    Member::where('id', $request->member_auto_id)->update(['reinvest_old_account_number' => $allOld,'is_block' => 1]);
			}


	    }

	    $status = ReinvestData::create($allData);

	    if ($status) {
		    $updateData = array('status'=>'success','id'=>$status->id, 'data' => $request->all() );
		    return $updateData;
	    } else {
		    $updateData = array('status'=>'failed','id'=>null);
		    return true;
	    }
    }




    public function saveForReinvestUpdate(Request $request)
	    {

///echo date("Y-m-d", strtotime($request['dob']));die;
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

      /*  try {*/

            // pass fa id 1 for member
            if($request['id']==0)
            {
	            $getBranchId=getUserBranchId(Auth::user()->id);

                $getfaCode=getFaCode(1);
                $faCode=$getfaCode->code;
                $branch_id=$getBranchId->id;

                // pass role_id(5 for member),branch_id,fa_code
                $getMiCode=getLastMiCode(5,$branch_id,$faCode);
                if(!empty($getMiCode))
                {
                    if($getMiCode->mi_code==9999998)
                    {
                        $miCodeAdd=$getMiCode->mi_code+2;
                    }
                    else
                    {
                        $miCodeAdd = substr($request->oldMemberId, -4);
                    }
                }
                else
                {
                    $miCodeAdd=1;
                }

                $miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);

                $getBranchCode=getBranchCode($branch_id);
                $branchCode=$getBranchCode->branch_code;
                // genarate Member id
                $getmemberID=$branchCode.$faCode.$miCode;
                // save member details
                $branchMi=$branchCode.$miCode;
                $data = $this->getData($request->all(),'create');

            }

            if($request['id']==0)
            {
                $data['role_id']=5;

                $data['member_id']=$getmemberID;
                $data['mi_code']=$miCode;
                $data['fa_code']=$faCode;
                $data['branch_id']=$branch_id;
                $data['branch_code']=$branchCode;
                $data['branch_mi']=$branchMi;
                //  print_r($data);

                $member = ReinvestMember::create($data);
                $memberId = $member->id;

                /****associate target entry****/
                $com_type=1;
                $member_carder=0;
                /*$commistionMember = CommanController::commissionDistributeMember($request['associate_id'],$memberId,$com_type,$branch_id,$member_carder);*/
                /****associate target entry****/
            }
           /* else
            {
                $memberId=$request['id'];
                $member = ReinvestMember::find($memberId);
                $member->update($data);
            }*/
            // upload signature or profile picture
            /*$imgChk=0;
            $signature_filename='';
            $photo_filename='';
            if ($request->hasFile('signature')) {
                $signature_image = $request->file('signature');
                $signature_filename = $memberId.'_'.time().'.'.$signature_image->getClientOriginalExtension();
                $signature_location = 'asset/profile/member_signature/' . $signature_filename;

                Image::make($signature_image)->resize(300,300)->save($signature_location);
                $imgChk++;

            }
            if ($request->hasFile('photo')) {
                $photo_image = $request->file('photo');
                $photo_filename = $memberId.'_'.time().'.'.$photo_image->getClientOriginalExtension();
                $photo_location = 'asset/profile/member_avatar/' . $photo_filename;

                Image::make($photo_image)->resize(300,300)->save($photo_location);
                $imgChk++;
            }
            if($imgChk>0)
            {
                $memberUpdate = Member::find($memberId);
                if($signature_filename!='')
                {
                    $memberUpdate->signature=$signature_filename;
                }
                if($photo_filename!='')
                {
                    $memberUpdate->photo=$photo_filename;
                }
                $memberUpdate->save();
            }*/


            // Save bank Information
            if($request['bank_name']!='' || $request['bank_branch_name']!='' || $request['bank_account_no']!='' || $request['bank_ifsc']!='' || $request['bank_branch_address']!='')
            {
                $dataBank = $this->getBankData($request->all(),'create',$memberId);
                if($request['bank_id']==0)
                {
                    $bankInfoSave=\App\Models\ReinvestMemberBankDetail::create($dataBank);
                }
                else
                {
                    $bankInfoSave = \App\Models\ReinvestMemberBankDetail::find($request['bank_id']);
                    $bankInfoSave->update($dataBank);
                }

            }
            // Save nominee information
            $dataNominee = $this->getNomineeData($request->all(),'create',$memberId);
            if($request['nominee_id']==0)
            {
                $nomineeInfoSave= \App\Models\ReinvestMemberNominee::create($dataNominee);
            }
            else
            {
                $nomineeInfoSave = \App\Models\ReinvestMemberNominee::find($request['nominee_id']);
                $nomineeInfoSave->update($dataNominee);
            }
            // Save Id proofs
            $dataIdProof = $this->getIdProofData($request->all(),'create',$memberId);
            if($request['IdProof_id']==0)
            {
                $idProofInfoSave= \App\Models\ReinvestMemberIdProof::create($dataIdProof);
            }
            else
            {
                $idProofInfoSave = \App\Models\ReinvestMemberIdProof::find($request['IdProof_id']);
                $idProofInfoSave->update($dataIdProof);
            }
            if($request['id']==0)
            {
                // create transaction
                $transaction_type=0;
                $payment_mode=0;//0 for cash
                $deposit_by_name=$request['first_name'].' '.$request['last_name'];
                $deposit_by_id=$memberId;
                $amountArray=array('1'=>10,'2'=>90);

              /*  $createTransaction = CommanController::createTransaction($transaction_type,$table_id='0',$memberId,$branch_id,$branchCode,$amountArray,$payment_mode,$deposit_by_name,$deposit_by_id,$account_no='0',$cheque_dd_no='0',$bank_name='null',$branch_name='null',$payment_date='null',$online_payment_id='null',$online_payment_by='null',$saving_account_id=0,'CRC');

                $tranID=$createTransaction;//transaction id
                // create recipt

                $typeArray=array('1'=>1,'2'=>2);
                $receipts_for=1;
                $createRecipt = CommanController::createPaymentRecipt(0,0,$memberId,$branch_id,$branchCode,$amountArray,$typeArray,$receipts_for,$account_no='0');
                $recipt_id=$createRecipt;*/
            }
            DB::commit();
       /* } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }*/
        $dataForReinvestData = array();
        $dataForReinvestData['title'] = 'Reinvest';
        $dataForReinvestData['memberDetail'] = ReinvestMember::find($memberId);
        $dataForReinvestData['memberId'] = ReinvestMember::find($memberId)->member_id;
        $dataForReinvestData['mId'] = ReinvestMember::find($memberId)->id;
        $dataForReinvestData['id'] = $memberId;
        $dataForReinvestData['reInvestAccountNumber'] = 'R-'.$request->oldAccountNumber;
        $dataForReinvestData['openingDate'] = date('Y-m-d', strtotime( str_replace('/', '-', $request->account_application_date)) );
        $dataForReinvestData['openDate'] = $request->account_application_date;
        $dataForReinvestData['planId'] = $request->plan_id;
        $dataForReinvestData['planName'] = Plans::find($request->plan_id)->name;
        $dataForReinvestData['planSlug'] = Plans::find($request->plan_id)->slug;
        return $dataForReinvestData;
    }

	public function planForm(Request $request)
	{
		$plan = $request->plan;
		$mId = $request->memberAutoId;
		$member = \App\Models\MemberNominee::where('member_id',$mId)->get();
		$relations = Relations::all();
		if ($plan) {
			return view('templates.branch.investment_management.'.$plan.'.'.$plan.'', compact('member','relations'));
		}
	}

    /**
     * Get member data to save.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getData($request,$type)
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
            $data['anniversary_date'] = date("Y-m-d", strtotime( convertDate($request['anniversary_date'] )));
        } else {
            $data['anniversary_date'] = null;
        }
        $data['father_husband'] = $request['f_h_name'];
        $data['address'] = preg_replace( "/\r|\n/", "",$request['address']);
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
        $data['reinvest_opening_date'] = date("Y-m-d", strtotime( str_replace('/','-', $request['account_application_date'] ) ) );
        $data['reinvest_old_account_number'] = $request['oldAccountNumber'];
        $data['old_c_id'] = $request['oldCId'];
        return $data;
    }

    /**
     * Get member bank information for save.
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
        $data['address'] = preg_replace( "/\r|\n/", "",$request['bank_branch_address']);
        return $data;
    }

    /**
     * Get member nominee detail for save.
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
     * Get member Id proof for save.
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
        $data['first_address'] = preg_replace( "/\r|\n/", "",$request['first_address_proof']);
        $data['second_id_type_id'] = $request['second_id_type'];
        $data['second_id_no'] = $request['second_id_proof_no'];
        $data['second_address'] = preg_replace( "/\r|\n/", "",$request['second_address_proof']);
        return $data;
    }


    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */    
    public function renew()
    {
        $data['title'] = "Reinvest";
        dd($data);
        return view('templates.branch.reinvest.index', $data);
    }

    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */    
    public function getCollectorAssociate(Request $request)
    {
        $code = $request->code;
        $collectorDetails = Member::with('savingAccount')->leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
        ->where('members.associate_no',$code)
        ->where('members.status',1)
        ->where('members.is_deleted',0)
        ->where('members.is_associate',1)
        ->where('members.is_block',0)
        ->select('carders.name as carders_name','members.first_name','members.last_name','members.id')
        ->first();
        if($collectorDetails)
        {   
            return Response::json(['msg_type'=>'success','collectorDetails'=>$collectorDetails]);
        }else{
            return Response::json(['view' => 'No data found','msg_type'=>'error']);
        }
    }

    /**
     * Get investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getInvestmentDetails(Request $request)
    {
        $accountNumber = $request->account_number;
        $planId = $request->renewPlanId;
        $investment = Memberinvestments::with('member','associateMember','ssb')->where('plan_id',$planId)->where('account_number',$accountNumber)->get();
        
        if(count($investment) > 0){
            $investmentLastDate = Investmentplantransactions::select('deposite_date','deposite_month')->where('investment_id',$investment[0]->id)->orderBy('id', 'desc')->first();

            if($investmentLastDate && $investment[0]->plan_id==7){
                $start_date = strtotime($investmentLastDate->deposite_date); 
                $end_date = strtotime(date('Y-m-d')); 
                $daysDiff = ($end_date - $start_date)/60/60/24;
                if($daysDiff > 0){
                    $amount = ($investment[0]->deposite_amount*$daysDiff)+$investment[0]->due_amount;
                }else{
                    $amount = $investment[0]->due_amount+$investment[0]->deposite_amount;
                }
            }else if($investmentLastDate && $investment[0]->plan_id==1){
                $amount = $investment[0]->deposite_amount;
            }else if($investmentLastDate){
                $lMonth = $investmentLastDate->deposite_month;
                $cMonth = date('m');
                $daysDiff = ($cMonth - $lMonth);
                if($daysDiff > 0){
                    $amount = ($investment[0]->deposite_amount*$daysDiff)+$investment[0]->due_amount;
                }else{
                    $amount = $investment[0]->due_amount+$investment[0]->deposite_amount;
                }
            }else{
                $amount = $investment[0]->deposite_amount;    
            }
        }

        $resCount = count($investment);
        $return_array = compact('investment','resCount','amount');
        return json_encode($return_array);
    }

    /**
     * Get investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function store(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);        
        $branch_id = $getBranchId->id;
        $getBranchCode = getBranchCode($branch_id);
        $getBranchName = getBranchName($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $branchName = $getBranchName->name;
        $accountNumbers = $request['account_number'];
        $renewPlanId = $request['renewplan_id'];
        $sAccount = $this->getSavingAccountDetails($request['member_id']);

        foreach ($accountNumbers as $key => $accountNumber) {
            if($accountNumber && $request['amount'][$key]){
                $amountArraySsb=array('1'=>$request['amount'][$key]);

                if($renewPlanId==2){
                    $sAccountId = $this->getSavingAccountId($request['investment_id'][$key]);
                    if($sAccountId){
                      $ssb_id = $sAccountId->id;  
                      $ssbAccountNumber = $sAccountId->account_no;
                    }else{
                        $ssb_id = NULL;
                        $ssbAccountNumber = NULL;
                    }

                    $savingAccountDetail = $this->getSavingAccountDetails($request['investment_member_id'][$key]);

                    if($savingAccountDetail){
                        $renewSavingOpeningBlanace = $savingAccountDetail->balance;
                    }else{
                        $renewSavingOpeningBlanace = NULL;        
                    }

                    $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                    if($investmentDetail){
                        $sResult = Memberinvestments::find($request['investment_id'][$key]);
                        $totalbalance = $investmentDetail->current_balance+$request['amount'][$key];
                        $investData['current_balance'] = $totalbalance;
                        $sResult->update($investData);
                    }else{
                        $totalbalance = '';
                    }

                    if($request['payment_mode'] == 4){
                        $ssbAccountAmount = $sAccount->balance-$request['amount'][$key];
                        $ssb_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        

                        $ssb['saving_account_id']=$ssb_id;
                        $ssb['account_no']=$accountNumber;
                        $ssb['opening_balance']=$ssbAccountAmount;
                        $ssb['withdrawal']=$request['amount'][$key];
                        //$ssb['description'] = ''.$accountNumber.'/Auto debit - collection';     
                        $ssb['description'] = 'Cash withdrawal';    
                        $ssb['currency_code']='INR';
                        $ssb['payment_type']='CR';
                        $ssb['payment_mode']=$request['payment_mode'];
                        $ssb['deposit']=NULL;

                        $ssbAccountTran = SavingAccountTranscation::create($ssb);

                        $createTransaction = CommanTransactionsController::createTransaction(5,0,$request['investment_member_id'][$key],$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssb_id,'DR');
                    }

                    $ssbAccountAmount = $renewSavingOpeningBlanace+$request['amount'][$key];
                    $ssb_id = $savingAccountDetail->id;
                    $sResult = SavingAccount::find($ssb_id);
                    $sData['balance'] = $ssbAccountAmount;
                    $sResult->update($sData);

                    $ssb['saving_account_id']=$ssb_id;
                    $ssb['account_no']=$accountNumber;
                    $ssb['opening_balance']=$renewSavingOpeningBlanace+$request['amount'][$key];
                    $ssb['withdrawal']=0;
                    /*if($request['payment_mode']==0){
                        $ssb['description'] = 'Cash deposit - collection';
                    }elseif($request['payment_mode']==1){
                        $ssb['description'] = 'Cheque deposit - collection';
                    }elseif($request['payment_mode']==2){
                        $ssb['description'] = 'DD deposit - collection';
                    }elseif($request['payment_mode']==3){
                        $ssb['description'] = 'Online transaction deposit - collection';
                    }elseif($request['payment_mode']==4){
                        $ssb['description'] = ''.$accountNumber.'/Auto debit - collection';     
                    }*/
                    $ssb['description'] = 'Cash deposit';
                    $ssb['currency_code']='INR';
                    $ssb['payment_type']='CR';
                    $ssb['payment_mode']=$request['payment_mode'];
                    $ssb['deposit']=$request['amount'][$key];
                    $ssbAccountTran = SavingAccountTranscation::create($ssb);

                    $createTransaction = CommanTransactionsController::createTransaction(5,0,$request['investment_member_id'][$key],$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssb_id,'CR');
                            
                    $createDayBook = CommanTransactionsController::createDayBook($createTransaction,2,$request['investment_id'][$key],$request['member_id'],$request['investment_member_id'][$key],$totalbalance,$request['amount'][$key],$withdrawal=0,$ssb['description'],$ssbAccountNumber,$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssb_id,'CR');    

                    $transaction = $this->transactionData($request->all(),$request['investment_id'][$key],$request['amount'][$key]);
                    $res = Investmentplantransactions::create($transaction);

                }else{
                    //$investmentAmount = Memberinvestments::select('due_amount')->where('id',$request['investment_id'][$key])->first();

                    $data['due_amount'] = $request['deo_amount'][$key];
                    $investment = Memberinvestments::find($request['investment_id'][$key]);
                    $investment->update($data);
               
                    if($request['payment_mode'] == 4){
                        $ssbAccountAmount = $sAccount->balance-$request['amount'][$key];
                        $ssb_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id']=$ssb_id;
                        $ssb['account_no']=$accountNumber;
                        $ssb['opening_balance']=$ssbAccountAmount;
                        $ssb['deposit']=NULL;
                        $ssb['withdrawal']=$request['amount'][$key];
                        //$ssb['description']=''.$accountNumber.'/Auto debit';  
                        $ssb['description'] = 'Cash withdrawal';                
                        $ssb['currency_code']='INR';
                        $ssb['payment_type']='CR';
                        $ssb['payment_mode']=$request['payment_mode'];
                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                        
                    }
                    $sAccount = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
                    if($sAccount){
                        $ssbAccountId = $sAccount->id;
                    }else{
                        $ssbAccountId = 0;        
                    }

                    $rplanId = $request['renew_investment_plan_id'];
                    if($rplanId==7){
                      $description = 'SDD Collection';  
                    }elseif($rplanId==10){
                      $description = 'ERD collection';  
                    }elseif($rplanId==5){
                      $description = 'FRD collection';  
                    }elseif($rplanId==3){
                      $description = 'SMB collection';  
                    }elseif($rplanId==2){
                      $description = 'SK collection';  
                    }elseif($rplanId==6){
                      $description = 'SJ collection';  
                    }

                    /*if($request['payment_mode']==0){
                        $description = 'Cash deposit - collection';
                    }elseif($request['payment_mode']==1){
                        $description = 'Cheque deposit - collection';
                    }elseif($request['payment_mode']==2){
                        $description = 'DD deposit - collection';
                    }elseif($request['payment_mode']==3){
                        $description = 'Online transaction deposit - collection';
                    }elseif($request['payment_mode']==4){
                        $description = ''.$accountNumber.'/Auto debit';     
                    }*/

                    $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                    if($investmentDetail){
                        $sResult = Memberinvestments::find($request['investment_id'][$key]);
                        $totalbalance = $investmentDetail->current_balance+$request['amount'][$key];
                        $investData['current_balance'] = $totalbalance;
                        $sResult->update($investData);
                    }else{
                        $totalbalance = '';
                    }

                    $createTransaction = CommanTransactionsController::createTransaction(4,$request['investment_id'][$key],$request['investment_member_id'][$key],$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbAccountId,'CR'); 
                    
                    $createDayBook = CommanTransactionsController::createDayBook($createTransaction,2,$request['investment_id'][$key],$request['member_id'],$request['investment_member_id'][$key],$totalbalance,$request['amount'][$key],$withdrawal=0,$description,$ref=NULL,$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,0,NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbAccountId,'CR'); 

                    $transaction = $this->transactionData($request->all(),$request['investment_id'][$key],$request['amount'][$key]);

                    $res = Investmentplantransactions::create($transaction); 
                }   
            }   
        }

        if ($createTransaction) {
            //redirect()->route('investment/recipt/'.$insertedid);
            //return redirect('branch/renew/recipt/'.$insertedid);
            //return back()->with('success', 'Saved Successfully!');
            $data['title'] = "Renewal Recipt";
            $data['renewFields'] = $request->all();
            $data['branchCode'] = $branchCode;
            $data['branchName'] = $branchName;
            return view('templates.branch.investment_management.renewal.recipt', $data);
        } else {
            return back()->with('alert', 'Problem With Register New Plan');
        }
    }

    /**
     * Get saving account id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSavingAccountDetails($mId)
    {
        $getDetails = SavingAccount::where('member_id',$mId)->select('id','balance','account_no')->first();
        return $getDetails;
    }

    /**
     * Get saving account id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSavingAccountId($investmentId)
    {
        $getDetails = SavingAccount::where('member_investments_id',$investmentId)->select('id','balance','account_no')->first();
        return $getDetails;
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

    /**
     * Get investment plans transaction data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transactionData($request,$investmentId,$amount)
    {

        $getBranchId=getUserBranchId(Auth::user()->id);
        $branch_id=$getBranchId->id;
        $getBranchCode=getBranchCode($branch_id);
        $branchCode=$getBranchCode->branch_code;
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = $request['renew_investment_plan_id'];
        $data['member_id'] = $request['member_id'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $amount;
        $data['deposite_date'] = date('Y-m-d');
        $data['deposite_month'] = date('m');
        $data['payment_mode'] = $request['payment_mode'];
        if($sAccount->id)
        {
            $data['saving_account_id'] = $sAccount->id;
        }
        else{
            $data['saving_account_id'] = NULL;
        }

        return $data;
    }

	public function createPlane(Request $request)
	{

		// print_r($_POST);die;
		$plantype = $request->input('plan_type');

		switch ($plantype) {
			case "samraddh-kanyadhan-yojana":
				$rules = [
					'form_number' => 'required',
					//'guardian-ralationship' => 'required',
					//'daughter-name' => 'required',
					//'phone-number' => 'required',
					//'dob' => 'required',
					'amount' => 'required',
					//'tenure' => 'required',
					//'age' => 'required',
					'payment-mode' => 'required',
				];
				break;
			case "special-samraddh-money-back":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'ssbacount' =>  'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "flexi-fixed-deposit":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "fixed-recurring-deposit":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "samraddh-jeevan":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'ssbacount' =>  'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "daily-deposit":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "monthly-income-scheme":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'ssbacount' =>  'required',
					//'ssbacount' =>  'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "fixed-deposit":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'payment-mode' => 'required',
					'tenure' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "recurring-deposit":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'payment-mode' => 'required',
					'tenure' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
			case "samraddh-bhavhishya":
				$rules = [
					'form_number' => 'required',
					'amount' => 'required',
					'payment-mode' => 'required',
					'investmentplan' => 'required',
					'memberid' => 'required',
					'fn_first_name' => 'required',
					//'fn_second_name' => 'required',
					'fn_relationship' => 'required',
					'fn_gender' => 'required',
					'fn_dob' => 'required',
					//'fn_age' => 'required',
					'fn_percentage' => 'required',
					//'fn_mobile_number' => 'required',
					//'sn_first_name' => 'required',
					//'sn_second_name' => 'required',
					//'sn_relationship' => 'required',
					//'sn_gender' => 'required',
					//'sn_dob' => 'required',
					//'sn_age' => 'required',
					//'sn_percentage' => 'required',
					//'sn_mobile_number' => 'required',
				];
				break;
		}

		$customMessages = [
			'fn_first_name.required' => 'The first name field is required.',
			'required' => 'The :attribute field is required.'
		];
		$this->validate($request, $rules, $customMessages);

		$type = 'create';

		DB::beginTransaction();
		try {
			//get login user branch id(branch manager)pass auth id
			$getBranchId=getUserBranchId(Auth::user()->id);
			$branch_id=$getBranchId->id;
			$getBranchCode=getBranchCode($branch_id);
			$branchCode=$getBranchCode->branch_code;
			//get login user branch id(branch manager)pass auth id
			$faCode=getPlanCode($request['investmentplan']);
			$planId=$request['investmentplan'];
			$investmentMiCode=getInvesmentMiCode($planId,$branch_id);
			if(!empty($investmentMiCode))
			{
				$miCodeAdd=$investmentMiCode->mi_code+1;
			}
			else
			{
				$miCodeAdd=1;
			}
			$miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);

			$miCodeBig   =str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);

			$passbook =  '720'.$branchCode.$faCode.$miCodeBig;
			$certificate ='719'.$branchCode.$faCode.$miCodeBig;

			// Invesment Account no
			$investmentAccount=$branchCode.$faCode.$miCode;
			$data = $this->getData($request->all(),$type,$miCode,$investmentAccount,$branch_id);
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

			$sAccount = $this->getSavingAccountDetails($request->input('memberAutoId'));

			if(count($sAccount) > 0){
				$ssbAccountNumber = $sAccount[0]->account_no;
				$ssbId = $sAccount[0]->id;
				$ssbBalance = $sAccount[0]->balance;
			}else{
				$ssbAccountNumber = '';
				$ssbId = '';
				$ssbBalance = '';
			}

			switch ($plantype) {
				case "saving-account":
					$is_primary = 0;
					$description = 'SSB Account opening';
					if ( SavingAccount::where('member_id', $request['memberAutoId'])->count() > 0) {
						return back()->with('alert', 'Your saving account already created!');
					}

					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$savingAccountId = $res->account_number;

					$createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($request['memberAutoId'],$branch_id,$branchCode,$request['amount'],0,$insertedid,$miCode,$investmentAccount,$is_primary,$faCode,$description,$request['associatemid']);

					$satRefId = CommanTransactionsController::createTransactionReferences($createAccount['ssb_transaction_id'],$insertedid);

					$ssbAccountId=$createAccount['ssb_id'];
					$amountArraySsb=array('1'=>$request['amount']);

					$amount_deposit_by_name = substr($request['member_name'], 0, strpos($request['member_name'], "-"));
					$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$ssbAccountId,$request['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,0,$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=NULL,$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'CR');

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,1,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArraySsb,0,$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);

					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();
					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/C'. $savingAccountDetail->account_no.' is Created on '
						.$savingAccountDetail->created_at->format('d M Y').' With Rs. '. round($request['amount'],2).' Cur Bal: '. round($savingAccountDetail->balance, 2).'. Thanks Have a good day';
					break;
				case "samraddh-kanyadhan-yojana":
					$description = 'SK Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){

						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');

						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);

						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);

					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/

					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SK A/c No.'. $investmentDetail->account_number.' is Credited on '.$investmentDetail->created_at->format('d M Y').' With Rs. '. round($investmentDetail->deposite_amount,2).'. Thanks Have a good day';
					break;
				case "special-samraddh-money-back":
					$description = 'EMB Account opening';
					$res = Memberinvestments::create($data);
					$monyBackAccount = $res->id;

					$insertedid = $res->id;
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C '. $memberInvestData->ssb_account_number.' is Created on '.$memberInvestData->created_at->format('d M Y') . ' with Rs. 100.00 CR, Money Back A/c No. '.$memberInvestData->account_number.' is Created on '.$memberInvestData->created_at->format('d M Y').' with Rs. '.round($memberInvestData->deposite_amount,2).' CR. Have a good day';
					break;
				case "flexi-fixed-deposit":
					$description = 'FFD Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FFD A/c No.'. $investmentDetail->account_number.' is Credited on '
						.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'. Thanks Have a good day';
					break;
				case "fixed-recurring-deposit":
					$description = 'FRD Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}


					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure']);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FRD A/c No.'. $investmentDetail->account_number.' is Credited on '
						.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
					break;
				case "samraddh-jeevan":
					$description = 'SJ Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$monyBackAccount = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C '. $memberInvestData->ssb_account_number.' is Created on '.$memberInvestData->created_at->format('d M Y') . ' with Rs. 100.00 CR, S. Jeevan A/c No. '.$memberInvestData->account_number.' is Created on '.$memberInvestData->created_at->format('d M Y').' with Rs. '.round($memberInvestData->deposite_amount,2).' CR. Have a good day';
					break;
				case "daily-deposit":
					$description = 'SDD Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');

					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SDD A/c No.'. $investmentDetail->account_number.' is Credited on '
						.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
					break;
				case "monthly-income-scheme":
					$description = 'MIS Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$monyBackAccount = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C '. $memberInvestData->ssb_account_number.' is Created on '.$memberInvestData->created_at->format('d M Y') . ' with Rs. 100.00 CR, MIS A/c No. '.$memberInvestData->account_number.' is Created on '.$memberInvestData->created_at->format('d M Y').' with Rs. '.round($memberInvestData->deposite_amount,2).' CR. Have a good day';
					break;
				case "fixed-deposit":
					$description = 'SFD Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');

						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SFD A/c No.'. $investmentDetail->account_number.' is Credited on '
						.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
					break;
				case "recurring-deposit":
					$description = 'ERD Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');

						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/
					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SRD A/c No.'. $investmentDetail->account_number.' is Credited on '
						.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
					break;
				case "samraddh-bhavhishya":
					$description = 'SB Account opening';
					$res = Memberinvestments::create($data);
					$insertedid = $res->id;
					$investmentDetail = Memberinvestments::find($res->id);
					$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
					$res = Memberinvestmentsnominees::create($fNominee);
					if($request['second_nominee_add']==1){
						$sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
						$res = Memberinvestmentsnominees::create($sNominee);
					}
					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);
					$amountArray=array('1'=>$request->input('amount'));
					$amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
					if($request->input('payment-mode')==3){
						$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
						$res = SavingAccountTranscation::create($savingTransaction);
						$satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
						$sAccountId = $ssbId;
						$sAccountAmount = $ssbBalance-$request->input('amount');
						$sResult = SavingAccount::find($sAccountId);
						$sData['balance'] = $sAccountAmount;
						$sResult->update($sData);
						$ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
						$sAccountNumber = $ssbAccountNumber;
					}else{
						$sAccountNumber = NULL;
						$satRefId = NULL;
						$ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
					}

					$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');

					/* ------------------ commission genarate-----------------*/
					$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
					$commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);

					/*----- ------  credit business start ---- ---------------*/
					$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);

					/*----- ------  credit business end ---- ---------------*/

					/* ------------------ commission genarate-----------------*/

					$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
					$res = Investmentplantransactions::create($transaction);

					$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
					$res = Memberinvestmentspayments::create($paymentData);

					$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Bhavhishya A/c No.'. $investmentDetail->account_number.' is Credited on '
						.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
					break;
			}
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			return back()->with('alert', $ex->getMessage());
		}
		$contactNumber = array();
		$memberDetail = Member::find($request['memberAutoId']);
		$contactNumber[] = $memberDetail->mobile_no;

		$sendToMember = new Sms();
		$sendToMember->sendSms( $contactNumber, $text );

		if ($res) {
			//redirect()->route('investment/recipt/'.$insertedid);
			return redirect('branch/investment/recipt/'.$insertedid);
			return back()->with('success', 'Saved Successfully!');
		} else {
			return back()->with('alert', 'Problem With Register New Plan');
		}
	}

	public function addPlaneCode (Request $request)
	{
		$result = ReinvestData::update(['plan' => substr (DB::raw("ac_code"), 3, 3 )]);
		echo $result;
	}
	public function updateBranchId()
	{
		$reinvestData = ReinvestData::where('form_type','reinvest_plane')->get();
		foreach ( $reinvestData as $rData ) {
			$member = Member::whereRaw("find_in_set('".$rData->account_number."',reinvest_old_account_number)")->first();
			if($member){
				$updateFormData = json_decode( $rData->form_data, true );
				$updateFormData['branch_id'] = $member->branch_id;
				json_encode($updateFormData);
				$result = ReinvestData::where('id', $rData->id)->update(['form_data' => json_encode($updateFormData)]);
			}
		}

		dd($result );
	}

	public function updateCId()
	{
		$members = Member::where("reinvest_old_account_number", '!=','')->get();

		foreach ( $members as $member ) {

			$cid = Reinvest::where('ac_code', $member->reinvest_old_account_number )->pluck('c_id')->first();
			$update = Member::where('id', $member->id )->update(['old_c_id'=> $cid ]);

		}
		dd($update);
	}
	public function updateBranchIdInTransaction()
	{
		$memberIds = Memberinvestments::where('account_number', 'like', 'R-%')->pluck('member_id');
		$memberBranchId = Member::whereIn('id', $memberIds)->pluck('branch_id', 'id');
		foreach ( $memberBranchId as $key => $value  ) {
			Memberinvestments::where('account_number', 'like', 'R-%')->where('member_id', $key )->update(['branch_id' => $value]);
		}

		$memberInvestmentData = Memberinvestments::where('account_number', 'like', 'R-%')->select('id','branch_id')->get();

		foreach ( $memberInvestmentData as $rData ) {
			$result = Daybook::where('investment_id', $rData->id)->update(['branch_id' => $rData->branch_id]);
			$result = Transcation::where('transaction_type', 2)->where('transaction_type_id', $rData->id)->update(['branch_id' => $rData->branch_id]);
			$result = TranscationLog::where('transaction_type', 2)->where('transaction_type_id', $rData->id)->update(['branch_id' => $rData->branch_id]);
			$result = Investmentplantransactions::where('investment_id', $rData->id)->update(['branch_id' => $rData->branch_id]);
		}

		dd($result );
	}
	public function getMemberId()
	{
		$members = Member::where("reinvest_old_account_number", '!=','')->get();
		$memberWithOutPlane = array();
		foreach ($members as $member )
		{
			//dd("DD", explode(',', $member->reinvest_old_account_number ) );
			$count = ReinvestData::where('account_number', $member->reinvest_old_account_number)->whereIn('form_type', ['reinvest_plane','reinvest_transaction'])->count();

			if( $count < 2 ) {
				$memberWithOutPlane[] = $member;
			}
		}
		dd ( $memberWithOutPlane);


	}

}
