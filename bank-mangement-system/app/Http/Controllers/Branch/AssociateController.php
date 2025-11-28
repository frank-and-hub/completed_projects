<?php 
namespace App\Http\Controllers\Branch; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member; 
use App\Models\Receipt;
use App\Models\Memberinvestments;
use App\Models\ReceiptAmount;
use App\Http\Controllers\Branch\CommanController;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Associate Management AssociateController
    |--------------------------------------------------------------------------
    |
    | This controller handles associate all functionlity.
*/
class AssociateController extends Controller
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
     * Show  particular branch members list.
     * Route: /branch/associate 
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        $data['title']='Associate Management | Listing'; 
        
        return view('templates.branch.associate_management.index', $data);
    }
    /**
     * Get Accociate list according to branch.
     * Route: ajax call from - /branch/associate
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateListing(Request $request)
    { 
      
        if ($request->ajax()) {
          
            //get login user branch id(branch manager)pass auth id
        $getBranchId=getUserBranchId(Auth::user()->id);
        $branch_id=$getBranchId->id;
            $data = Member::where('member_id','!=','9999999')->where('branch_id',$branch_id)->where('is_associate',1)->where('is_deleted',0)->where('company_id',$request->company_id)->orderBy('associate_join_date', 'DESC')->get(); 

            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('join_date', function($row){
                 $re_date = date("m/d/Y", strtotime($row->associate_join_date));
                return $re_date;
            })
            ->rawColumns(['join_date'])
            ->addColumn('associate_no', function($row){
                $associate_no = $row->associate_no;
                return $associate_no;
            })
            ->rawColumns(['associate_no'])
            ->addColumn('name', function($row){
                $name = $row->first_name.' '.$row->last_name;
                return $name;
            })
            ->rawColumns(['name'])
            ->addColumn('email', function($row){
                $email = $row->email;
                return $email;
            })
            ->rawColumns(['email'])
            ->addColumn('mobile_no', function($row){
                $mobile_no = $row->mobile_no;
                return $mobile_no;
            })
            ->rawColumns(['mobile_no'])
            ->addColumn('senior_code', function($row){
                if($row->associate_senior_id==0)
                {
                    $senior_code = $row->associate_senior_id.' (Super Admin)';
                }
                else
                {
                   $senior_code = $row->associate_senior_code; 
                }
                
                return $senior_code;
            })
            ->rawColumns(['senior_code'])   
            ->addColumn('status', function($row){
                if($row->status==1)
                {
                  $status = 'Active';
                }
                else
                {
                    $status = 'Inactive';
                }
                
                return $status;
            })
            ->rawColumns(['status']) 
            ->addColumn('action', function($row){ 
                $url = URL::to("branch/associate/detail/".$row->id."");
	            $btn ="";
	            if( in_array('Associate Profile View', auth()->user()->getPermissionNames()->toArray() ) ) {
		            $btn = '<a class=" " href="'.$url.'" title="Associate Detail"><i class="fas fa-eye text-default"></i></a>  ';
	            }

                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }
    /**
     * Show  associate registration.
     * Route: /branch/associate/registration 
     * Method: get 
     * @return  array()  Response
     */
    public function register()
    {

    	$data['title']='Associate | Registration';
        $data['carder']=\App\Models\Carder::where('status',1)->where('is_deleted',0)->limit(3)->get(['id','name','short_name'] ); 
        $data['relations']=relationsList(); 
        
	    return view('templates.branch.associate_management.add', $data);
		
    }

    /**
     * associate form number exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateFormNoCheck(Request $request)
    { 
        $resCount=checkMemberFormNo($request->form_no,'associate_form_no');   
        $return_array = compact('resCount'); 
        return json_encode($return_array); 
    }
    
	/**
     * Save associate data.
     * Route: /branch/associate/registration 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function save(Request $request)
    {
        Session::put('created_at', $request['created_at']);
        $errorCount=0;  $form1=0;    $form2=0;
        $dataMsg['errormsg1']='';  $dataMsg['errormsg2']='';
        $investmentAccountNoRd='';  $investmentAccountNoSsb='';
        $isReceipt='no';
        $recipt_id=0; 
        $is_primary=0;

        //get login user branch id(branch manager)pass auth id
        $getBranchId=getUserBranchId(Auth::user()->id);        
        $branch_id=$getBranchId->id;
        $memberId=$request['id'];  
        $getBranchCode=getBranchCode($branch_id);
        $branchCode=$getBranchCode->branch_code;        
        $dataMemberDetail = Member::where('id',$memberId)->first(); 
        if(!empty($dataMemberDetail)) {
        	$deposit_by_name=$dataMemberDetail->first_name.' '.$dataMemberDetail->last_name;
        }
        $deposit_by_id=$memberId;

        if($request['id']=='' || $request['member_id']=='')
        {
            $dataMsg['errormsg1'].='Please select member.<br>';
            $form1++; $errorCount++;
        }
        if($request['form_no']=='')
        {
            $dataMsg['errormsg1'].='Please enter form no.<br>';
            $form1++; $errorCount++;
        }
        if($request['application_date']=='')
        {
            $dataMsg['errormsg1'].='Please enter application date.<br>';
            $form1++; $errorCount++;
        }
        if($request['current_carder']=='')
        {
            $dataMsg['errormsg1'].='Please assign carder.<br>';
            $form1++; $errorCount++;
        }
        if($request['senior_id']=='')
        {
            $dataMsg['errormsg1'].='Please enter senior code.<br>';
            $form1++; $errorCount++;
        }
        if($request['first_g_first_name']=='')
        {
            $dataMsg['errormsg1'].='Please enter guarantor  name.<br>';
            $form1++; $errorCount++;
        }
        if($request['first_g_Mobile_no']=='')
        {
            $dataMsg['errormsg1'].='Please enter guarantor mobile number.<br>';
            $form1++;  $errorCount++;
        }
        if($request['first_g_address']=='')
        {
            $dataMsg['errormsg1'].='Please enter guarantor address.<br>';
            $form1++; $errorCount++;
        }
        if($request['ssb_account']=='')
        {
            $dataMsg['errormsg2'].='Please select SSB account option.<br>';
            $form2++; $errorCount++;
        }
        if($request['ssb_account']==1)
        {
            if($request['ssb_account_number']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB account no.<br>';
                $form2++; $errorCount++;
            }
        }
        if($request['ssb_account']==0)
        {
            if($request['ssb_amount']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB amount.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_first_first_name']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB first nominee  name.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_first_relation']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB first nominee relation.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_first_dob']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB first nominee date of birth.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_first_percentage']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB first nominee percentage.<br>';
                $form2++; $errorCount++;
            }
            if(!isset($request['ssb_first_gender']))
            {
                $dataMsg['errormsg2'].='Please select SSB first nominee gender.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_first_age']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB first nominee age.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_first_mobile_no']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB first nominee mobile No.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_second_validate']==1){
            if($request['ssb_second_first_name']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB second nominee  name.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_second_relation']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB second nominee relation.<br>';
                $form2++;  $errorCount++;
            }
            if($request['ssb_second_dob']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB second nominee date of birth.<br>';
                $form2++;  $errorCount++;
            }
            if($request['ssb_second_percentage']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB second nominee percentage.<br>';
                $form2++; $errorCount++;
            } 
            if(!isset($request['ssb_second_gender']))
            {
                $dataMsg['errormsg2'].='Please select SSB second nominee gender.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_second_age']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB second nominee age.<br>';
                $form2++; $errorCount++;
            }
            if($request['ssb_second_mobile_no']=='')
            {
                $dataMsg['errormsg2'].='Please enter SSB second nominee mobile No.<br>';
                $form2++; $errorCount++;
            }
        }
        }
        if($request['rd_account']=='')
        {
            $dataMsg['errormsg2'].='Please select RD account option.<br>';
            $form2++; $errorCount++;
        }
        if($request['rd_account']==1)
        {
            if($request['rd_account_number']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD account no.<br>';
                $form2++; $errorCount++;
            }
        }
        if($request['rd_account']==0)
        {
            if($request['rd_amount']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD amount.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_first_first_name']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD first nominee  name.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_first_relation']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD first nominee relation.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_first_dob']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD first nominee date of birth.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_first_percentage']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD first nominee percentage.<br>';
                $form2++; $errorCount++;
            } 
            if(!isset($request['rd_first_gender']))
            {
                $dataMsg['errormsg2'].='Please select RD first nominee gender.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_first_age']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD first nominee age.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_first_mobile_no']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD first nominee mobile No.<br>';
                $form2++; $errorCount++;
            }
        if($request['rd_second_validate']==1)
        {
            if($request['rd_second_first_name']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD second nominee  name.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_second_relation']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD second nominee relation.<br>';
                $form2++;  $errorCount++;
            }
            if($request['rd_second_dob']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD second nominee date of birth.<br>';
                $form2++;  $errorCount++;
            }
            if($request['rd_second_percentage']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD second nominee percentage.<br>';
                $form2++; $errorCount++;
            } 
            if(!isset($request['rd_second_gender']))
            {
                $dataMsg['errormsg2'].='Please select RD second nominee gender.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_second_age']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD second nominee age.<br>';
                $form2++; $errorCount++;
            }
            if($request['rd_second_mobile_no']=='')
            {
                $dataMsg['errormsg2'].='Please enter RD second nominee mobile No.<br>';
                $form2++; $errorCount++;
            }
        }
    }
        $dataMsg['msg']='Associate not created.Check your fields';
        
        
        if($request['rd_account']==1)
        {
            // ssb account no exits or not(pass member id and ssb account no)
            $rdAccountDetail=getInvestmentAccount($memberId,$request['rd_account_number']);
            
            if(!empty($rdAccountDetail))
            {
                $investmentAccountNoRd=$request['rd_account_number'];
            }
            else
            {
                $dataMsg['msg_type']='error';
                $dataMsg['msg']='RD account not created!';
                $dataMsg['reciept_generate ']='no';
                $dataMsg['reciept_id']=0;
                $dataMsg['errormsg2'].='RD account number wrong.<br>';
                $form2++;
                $errorCount++;
            }
             
        }
        if($request['rd_account']==0)
        {
            if($request['payment_mode']==3)
            {
                // pass member id 
                $ssbPrimary=getMemberSsbAccountDetail($memberId); 
                if(!empty($ssbPrimary))
                {               
                    if($ssbPrimary->balance<$request['rd_amount'])
                    {
                        $dataMsg['msg_type']='error';
                        $dataMsg['msg']='SSB account does not have a sufficient balance.Change your payment mode';
                        $dataMsg['reciept_generate ']='no';
                        $dataMsg['reciept_id']=0;
                        $dataMsg['errormsg2'].='Your SSB account does not have a sufficient balance.<br>';
                        $form2++;
                        $errorCount++;
                    }
                }
                else
                {
                    $dataMsg['msg_type']='error';
                    $dataMsg['msg']='You does not have SSB account';
                    $dataMsg['reciept_generate ']='no';
                    $dataMsg['reciept_id']=0;
                    $dataMsg['errormsg2'].='You does not have SSB account.<br>';
                    $form2++;
                    $errorCount++;
                }
            }

        }

        if($request['ssb_account']==1)
        {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail=getInvestmentAccount($memberId,$request['ssb_account_number']);
            
            if(!empty($ssbAccountDetail))
            {
                $investmentAccountNoSsb=$request['ssb_account_number'];
            }
            else
            {
                $dataMsg['msg_type']='error';
                $dataMsg['msg']='SSB account number wrong.';
                $dataMsg['reciept_generate ']='no';
                $dataMsg['reciept_id']=0;
                $dataMsg['errormsg2'].='SSB account number wrong.<br>';
                $form2++;
                 $errorCount++;
            }
             
        }
        if($request['ssb_account']==0)
        {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail=getMemberSsbAccountDetail($memberId);
            
            if(!empty($ssbAccountDetail))
            {
                $dataMsg['msg_type']='error';
                $dataMsg['msg']='SSB account already exists!.';
                $dataMsg['reciept_generate ']='no';
                $dataMsg['reciept_id']=0;
                $dataMsg['errormsg2'].='SSB account already exists!.<br>';
                $form2++;
                 $errorCount++;
            } 
             
        }
        if( $errorCount>0) {
        	$dataMsg['form1']=$form1;
            $dataMsg['form2']=$form2;
           // print_r($dataMsg);die;
            return json_encode($dataMsg);
        }
DB::beginTransaction();
try {
        
        if($request['rd_account']==0)
        {        
            $faCode=704;
            $dataInvestrd['deposite_amount'] = $request['rd_amount'];
            $dataInvestrd['payment_mode'] = $request['payment_mode'];
            $dataInvestrd['tenure'] = $request['tenure']/12;
            $tenure=$request['tenure']/12;
            if($tenure == 36){
              $tenurefacode = $faCode.'002';
            }elseif($tenure == 60){
              $tenurefacode = $faCode.'003';
            }elseif($tenure == 84){
              $tenurefacode = $faCode.'004';
            }else{
              $tenurefacode = $faCode.'001';
            }
             $dataInvestrd['tenure_fa_code'] = $tenurefacode;
            //$formNumber = rand(10,1000);
            $formNumber = $request['rd_form_no'];
            // getInvesment Plan id by plan code

            $planIdGet=getPlanID($faCode);
            $planId=$planIdGet->id;

            $investmentMiCode=getInvesmentMiCode($planId,$branch_id);
            if(!empty($investmentMiCode))
            {
                $miCodeAdd=$investmentMiCode->mi_code+1;
                if($investmentMiCode->mi_code==9999998)
                {
                    $miCodeAdd=$investmentMiCode->mi_code+2;
                }

            }
            else
            {
               $miCodeAdd=1;
            }

            $miCode=str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
            // Invesment Account no
            $investmentAccountNoRd=$branchCode.$faCode.$miCode;
            $miCodeBig   =str_pad($miCodeAdd, 7, '0', STR_PAD_LEFT); 
        
            $passbook =  '720'.$branchCode.$miCodeBig; 
          if($faCode==704)
          {
            $dataInvestrd['passbook_no']=$passbook;
            $dataInvestrd['maturity_amount'] = $request['rd_amount_maturity'];
            $dataInvestrd['interest_rate'] = $request['rd_rate']; 
          }

            $payment_mode=0;
            $rdDebitaccountId=0;
            $rdPayDate=null;
            if($request['payment_mode']==1)
            {
                $invPaymentMode['cheque_number'] = $request['rd_cheque_no'];
                $invPaymentMode['bank_name'] = $request['rd_branch_name'];
                $invPaymentMode['branch_name'] = $request['rd_bank_name'];
                $invPaymentMode['cheque_date'] = date("Y-m-d", strtotime($request['rd_cheque_date']));
                $payment_mode=1;
                $rdPayDate=date("Y-m-d", strtotime($request['rd_cheque_date']));
            }
            if($request['payment_mode']==2)
            {
                $invPaymentMode['transaction_id'] = $request['rd_online_id'];
                $invPaymentMode['transaction_date'] = date("Y-m-d", strtotime($request['rd_online_date']));
                $payment_mode=3;
                $rdPayDate=date("Y-m-d", strtotime($request['rd_online_date']));

            }
            if($request['payment_mode']==3)
            {
                // pass member id
                $rdPayDate=date("Y-m-d");
                $ssbAccountDetail=getMemberSsbAccountDetail($memberId);
                $invPaymentMode['ssb_account_id'] = $ssbAccountDetail->id;
                $invPaymentMode['ssb_account_no'] = $ssbAccountDetail->account_no;
                $payment_mode=4;
                $rdDebitaccountId=$ssbAccountDetail->id;
                if(!empty($ssbAccountDetail)){
                    if($ssbAccountDetail->balance>$request['rd_amount'])
                    {
                        $detail='RD/'.$investmentAccountNoRd.'/Auto Debit';
                        $ssbTranCalculation = CommanController::ssbTransaction($ssbAccountDetail->id,$ssbAccountDetail->account_no,$ssbAccountDetail->balance,$request['rd_amount'],$detail,'INR','DR',3);

                        $amountArrayRD=array('1'=>$request['rd_amount']);

                        $rdCreateTran = CommanController::createTransaction(1,$ssbAccountDetail->id,$memberId,$branch_id,$branchCode,$amountArrayRD,5,$deposit_by_name,$deposit_by_id,$ssbAccountDetail->account_no,$cheque_dd_no='0',$bank_name='null',$branch_name='null',$payment_date='null',$online_payment_id='null',$online_payment_by='null',$saving_account_id=0,'DR');
                    }
                    else
                    {
                        $dataMsg['msg_type']='error';
                        $dataMsg['msg']='Your SSB account does not have a sufficient balance.';
                        $dataMsg['reciept_generate ']='no';
                        $dataMsg['reciept_id']=0;
                        $dataMsg['errormsg2'].='Your SSB account does not have a sufficient balance.<br>';
                        $form2++;
                        $errorCount++;
                        return json_encode($dataMsg);
                    }
                }
                else
                {
                    $dataMsg['msg_type']='error';
                    $dataMsg['msg']='You does not have SSB account';
                    $dataMsg['reciept_generate ']='no';
                    $dataMsg['reciept_id']=0;
                    $dataMsg['errormsg2'].='You does not have SSB account.<br>';
                    $form2++;
                    $errorCount++;
                    return json_encode($dataMsg);
                }

            }


            $dataInvestrd['plan_id'] = $planId;
            $dataInvestrd['form_number'] = $formNumber;
            $dataInvestrd['member_id'] = $memberId;
            $dataInvestrd['branch_id'] = $branch_id;
            $dataInvestrd['account_number'] = $investmentAccountNoRd;
            $dataInvestrd['mi_code'] = $miCode;
            $dataInvestrd['associate_id']=$request['senior_id'];
            $res = \App\Models\Memberinvestments::create($dataInvestrd);
            $investmentId = $res->id;
            $invDatard1= array(
                                    'investment_id' => $investmentId,
                                    'nominee_type' => 0,
                                    'name' => $request['rd_first_first_name'],
                                  //  'second_name' => $request['rd_first_last_name'],
                                    'relation' => $request['rd_first_relation'],
                                    'gender' => $request['rd_first_gender'],
                                    'dob' => date("Y-m-d", strtotime($request['rd_first_dob'])),
                                    'age' => $request['rd_first_age'],
                                    'percentage' => $request['rd_first_percentage'],
                                    'phone_number' => $request['rd_first_mobile_no'],
                                    'created_at' => $request['created_at'],
                                 );
            $resinvDatard1 = \App\Models\Memberinvestmentsnominees::create($invDatard1);
            if($request['rd_second_validate']==1) {
            	$invDatard2= array(
            		'investment_id' => $investmentId,
                    'nominee_type' => 1,
                    'name' => $request['rd_second_first_name'],
                    // 'second_name' => $request['rd_second_last_name'],
		            'relation' => $request['rd_second_relation'],
                    'gender' => $request['rd_second_gender'],
                    'dob' => date("Y-m-d", strtotime($request['rd_second_dob'])),
                    'age' => $request['rd_second_age'],
                    'percentage' => $request['rd_second_percentage'],
                    'phone_number' => $request['rd_second_mobile_no'],
                    'created_at' => $request['created_at'],
	            );
                $resinvDatard2 = \App\Models\Memberinvestmentsnominees::create($invDatard2);
                }

            $invPaymentMode['investment_id'] = $investmentId;
            $res = \App\Models\Memberinvestmentspayments::create($invPaymentMode);

            $amountArray=array('1'=>$request['rd_amount']);

                   $createTransaction = CommanController::createTransaction(2,$investmentId,$memberId,$branch_id,$branchCode,$amountArray,$payment_mode,$deposit_by_name,$deposit_by_id,$investmentAccountNoRd,$request['rd_cheque_no'],$request['rd_bank_name'],$request['rd_branch_name'],$rdPayDate,$request['rd_online_id'],$online_payment_by='null',$rdDebitaccountId,'CR');
        }

        if($request['ssb_account']==0)
        {
            $formNumber = rand(10,1000);
            // getInvesment Plan id by plan code
            $faCode=703;
            $planIdGet=getPlanID($faCode);
            $planId=$planIdGet->id;
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
            // Invesment Account no
            $investmentAccountNoSsb=$branchCode.$faCode.$miCode;

            $dataInvest['deposite_amount'] = $request['ssb_amount'];
            $dataInvest['plan_id'] = $planId;
            $dataInvest['form_number'] = $request['ssb_form_no'];
            $dataInvest['member_id'] = $memberId;
            $dataInvest['branch_id'] = $branch_id;
            $dataInvest['account_number'] = $investmentAccountNoSsb;
            $dataInvest['mi_code'] = $miCode;
            $dataInvest['associate_id']=$request['senior_id'];
            $dataInvest['created_at'] = $request['created_at'];
            $res = \App\Models\Memberinvestments::create($dataInvest);
            $investmentId = $res->id;

        //create savings account
            $amount=$request['ssb_amount'];
            $createAccount = CommanController::createSavingAccount($memberId,$branch_id,$branchCode,$amount,$payment_mode,$investmentId,$miCode,$investmentAccountNoSsb,$is_primary,$faCode);

            $ssbAccountId=$createAccount;//sb account id

            $amountArraySsb=array('1'=>$amount);

            $ssbCreateTran = CommanController::createTransaction(1,$ssbAccountId,$memberId,$branch_id,$branchCode,$amountArraySsb,0,$deposit_by_name,$deposit_by_id,$investmentAccountNoSsb,$cheque_dd_no='0',$bank_name='null',$branch_name='null',$payment_date='null',$online_payment_id='null',$online_payment_by='null',$saving_account_id=0,'CR');

            $invData1ssb=  array(
                                    'investment_id' => $investmentId,
                                    'nominee_type' => 0,
                                    'name' => $request['ssb_first_first_name'],
                                  //  'second_name' => $request['ssb_first_last_name'],
                                    'relation' => $request['ssb_first_relation'],
                                    'gender' => $request['ssb_first_gender'],
                                    'dob' => date("Y-m-d", strtotime($request['ssb_first_dob'])),
                                    'age' => $request['ssb_first_age'],
                                    'percentage' => $request['ssb_first_percentage'],
                                    'phone_number' => $request['ssb_first_mobile_no'],
                                    'created_at' => $request['created_at'],
                                 );
            $resinvData1 = \App\Models\Memberinvestmentsnominees::create($invData1ssb);
            if($request['ssb_second_validate']==1)
            {
                $invData2ssb=  array(
                                    'investment_id' => $investmentId,
                                    'nominee_type' => 1,
                                    'name' => $request['ssb_second_first_name'],
                                   // 'second_name' => $request['ssb_second_last_name'],
                                    'relation' => $request['ssb_second_relation'],
                                    'gender' => $request['ssb_second_gender'],
                                    'dob' => date("Y-m-d", strtotime($request['ssb_second_dob'])),
                                    'age' => $request['ssb_second_age'],
                                    'percentage' => $request['ssb_second_percentage'],
                                    'phone_number' => $request['ssb_second_mobile_no'],
                                    'created_at' => $request['created_at'],
                                 );
                 $resinvData2 = \App\Models\Memberinvestmentsnominees::create($invData2ssb);
            }
        }

        if($investmentAccountNoSsb=='' || $investmentAccountNoRd=='')
        {
                    $dataMsg['msg_type']='error';
                    $dataMsg['msg']='Associate not created.Try again';
                    $dataMsg['reciept_generate ']='no';
                    $dataMsg['reciept_id']=0;
        }
        else
        {
            // pass fa id 2 for Associate
            $getfaCode=getFaCode(2);
            $faCodeAssociate=$getfaCode->code;
            $getMiCodeAssociate=getAssociateMiCode($memberId,$branch_id); 
             if(!empty($getMiCodeAssociate))
                {
                    if($getMiCodeAssociate->associate_micode==9999998)
                    {
                        $miCodeAddAssociate=$getMiCodeAssociate->associate_micode+2;
                    }
                    else
                    {
                       $miCodeAddAssociate=$getMiCodeAssociate->associate_micode+1; 
                    }
                }
                else
                {
                   $miCodeAddAssociate=1; 
                }
            $miCodeAssociate=str_pad($miCodeAddAssociate, 5, '0', STR_PAD_LEFT);
        // genarate Member id
            $getmemberID=$branchCode.$faCodeAssociate.$miCodeAssociate;

            $dataAssociate['associate_form_no'] = $request['form_no'];
            $dataAssociate['associate_join_date'] = date("Y-m-d", strtotime($request['application_date']));
            $dataAssociate['associate_no']=$getmemberID;
            $dataAssociate['is_associate']=1;
            $dataAssociate['associate_status']=1;
            $dataAssociate['associate_micode']=$miCodeAssociate;
            $dataAssociate['associate_facode']=$faCodeAssociate;

            $dataAssociate['associate_senior_code']=$request['senior_code'];
            $dataAssociate['associate_senior_id']=$request['senior_id'];
            $dataAssociate['current_carder_id']=$request['current_carder'];
            if($request['ssb_account']==0){
            $dataAssociate['ssb_account']=$investmentAccountNoSsb;
            }
            if($request['rd_account']==0){
            $dataAssociate['rd_account']=$investmentAccountNoRd;
            }
            $memberDataUpdate = Member::find($memberId);
            $memberDataUpdate->update($dataAssociate);


            if(isset($_POST['dep_first_name']) && $_POST['dep_first_name']!='')
           {
                $associateDependent1['member_id'] = $memberId;
                $associateDependent1['name'] = $_POST['dep_first_name'];

                if($_POST['dep_age']!='')
                    {
                         $associateDependent1['age'] = $_POST['dep_age'];
                    } 
                    if($_POST['dep_relation']!='')
                    {
                        $associateDependent1['relation'] = $_POST['dep_relation'];
                    } 
                    if($_POST['dep_income']!='')
                    {
                        $associateDependent1['monthly_income'] = $_POST['dep_income'];
                    } 

                $associateDependent1['gender'] = $_POST['dep_gender'];
                $associateDependent1['marital_status'] = $_POST['dep_marital_status'];
                $associateDependent1['living_with_associate'] = $_POST['dep_living']; 
                $associateDependent1['dependent_type'] = $_POST['dep_type'];
                $associateDependent1['created_at'] = $request['created_at'];
                $associateInsert1 = \App\Models\AssociateDependent::create($associateDependent1);
            }

            //print_r($_POST);die;

	        if(isset($_POST['dep_first_name1']))
{
    if(!empty($_POST['dep_first_name1']))
    {
        foreach(($_POST['dep_first_name1']) as $key=>$option)
        {
               if(isset($_POST['dep_first_name1'][$key]) && $_POST['dep_first_name1'][$key]!='')
               {
                    $associateDependent['member_id'] = $memberId;
                    $associateDependent['name'] = $_POST['dep_first_name1'][$key];
                    if($_POST['dep_age1'][$key]!='')
                            {
                                $associateDependent['age'] = $_POST['dep_age1'][$key];
                            } 
                            if($_POST['dep_relation1'][$key]!='')
                            {
                                $associateDependent['relation'] = $_POST['dep_relation1'][$key];
                            } 
                             if($_POST['dep_income1'][$key]!='')
                            {
                                 $associateDependent['monthly_income'] = $_POST['dep_income1'][$key];
                            }  
                    $associateDependent['gender'] = $_POST['dep_gender1'][$key];
                    $associateDependent['marital_status'] = $_POST['dep_marital_status1'][$key];
                    $associateDependent['living_with_associate'] = $_POST['dep_living1'][$key]; 
                    $associateDependent['dependent_type'] = $_POST['dep_type1'][$key];  
                    $associateDependent['created_at'] = $request['created_at'];              
                    $associateInsert = \App\Models\AssociateDependent::create($associateDependent);
                }
        }
    }
}

            $associateGuarantor['member_id'] = $memberId;
            $associateGuarantor['first_name'] = $request['first_g_first_name'];
            $associateGuarantor['first_mobile_no'] = $request['first_g_Mobile_no'];
            $associateGuarantor['first_address'] = $request['first_g_address'];
            $associateGuarantor['second_name'] = $request['second_g_first_name'];
            $associateGuarantor['second_mobile_no'] = $request['second_g_Mobile_no'];
            $associateGuarantor['second_address'] = $request['second_g_address'];
            $associateGuarantor['created_at'] = $request['created_at'];       
            $associateInsert = \App\Models\AssociateGuarantor::create($associateGuarantor);


/* ***************   associate tree start ****************** */
            $getParentID=\App\Models\AssociateTree::Where('member_id',$request['senior_id'])->first();
           
            $associateTree['member_id'] = $memberId;
            $associateTree['parent_id'] = $getParentID->id;
            $associateTree['senior_id'] = $request['senior_id'];
            $associateTree['carder'] = $request['current_carder']; 
            $associateTree['created_at'] = $request['created_at'];
            $associateTreeInsert = \App\Models\AssociateTree::create($associateTree);

/* ***************   associate tree end ****************** */ 


            if($request['rd_account']==0  && $request['ssb_account']==0)
            {

                $amountArray1=array('1'=>$request['ssb_amount'],'2'=>$request['rd_amount']);
                $typeArray=array('1'=>1,'2'=>2);
                $receipts_for=4;
                $createRecipt = CommanController::createPaymentRecipt(0,0,$memberId,$branch_id,$branchCode,$amountArray1,$typeArray,$receipts_for,$account_no='0');
                $recipt_id=$createRecipt; $isReceipt='yes';
            }
            elseif($request['rd_account']==0  && $request['ssb_account']==1)
            {
                $amountArray1=array('1'=>$request['rd_amount']);
                $typeArray=array('1'=>2);
                $receipts_for=4;
                $createRecipt = CommanController::createPaymentRecipt(0,0,$memberId,$branch_id,$branchCode,$amountArray1,$typeArray,$receipts_for,$account_no='0');
                $recipt_id=$createRecipt; $isReceipt='yes';
            }
            elseif ($request['rd_account']==1  && $request['ssb_account']==0)
            {
                $amountArray1=array('1'=>$request['ssb_amount']);
                $typeArray=array('1'=>1);
                $receipts_for=4;
                $createRecipt = CommanController::createPaymentRecipt(0,0,$memberId,$branch_id,$branchCode,$amountArray1,$typeArray,$receipts_for,$account_no='0');
                $recipt_id=$createRecipt; $isReceipt='yes';
            }
            $dataMsg['msg_type']='success';
            $dataMsg['msg']='Associate created Successfully';
            $dataMsg['reciept_generate ']=$isReceipt;
            $dataMsg['reciept_id']=$recipt_id;
        }
	$contactNumber = array();
	$contactNumber[] = $memberDataUpdate->mobile_no;

	$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Associate Id No. '. $dataAssociate['associate_no'].', Saving A/C '
        .SavingAccount::find($ssbAccountId)->account_no .' is Created on '. $res->created_at->format('d M Y') . ' with Rs. '. round($request['ssb_amount'],2).' CR, Recurring A/c No. '.$investmentAccountNoRd.' is Created on '. $res->created_at->format('d M Y').' with Rs. '.round($dataInvestrd['deposite_amount'],2).'. Have a good day';
    $templateId = 1207161519623599748;

	$sendToMember = new Sms();
	$sendToMember->sendSms( $contactNumber, $text, $templateId);

	DB::commit();
} catch (\Exception $ex) {
    DB::rollback();
    $dataMsg['msg_type']='error';
            $dataMsg['msg']=$ex->getMessage();
            $dataMsg['reciept_generate ']=0;
            $dataMsg['reciept_id']=0;
             return json_encode($dataMsg);
}
         // create transaction

                return json_encode($dataMsg);

    }


    /**
     * Show associate  detail
     * Route: /branch/associate/detail/
     * Method: get
     * @param  $id
     * @return  array()  Response
     */
    public function associateDetail($id)
    {
        $data['title']='Associate | Detail';
        $data['memberDetail'] = Member::where('id',$id)->first();


        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id',$id)->first();
        $data['guarantorDetail'] = \App\Models\AssociateGuarantor::where('member_id',$id)->first();
        $data['dependentDetail'] = \App\Models\AssociateDependent::where('member_id',$id)->get();
        $recipt=Receipt::where('member_id',$id)->where('receipts_for',4)->first();
        $data['recipt']= ($recipt) ? $recipt->id :0;


        return view('templates.branch.associate_management.detail', $data);
    }

    /**
     * Get Member detail through member code.
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getMemberData(Request $request)
    {

        $data=Member::where('member_id',$request->code)->where('status',1)->where('is_deleted',0)->first();
        if($data)
        {
            if($data->is_associate==0)
            {
                $id=$data->id;
                $idProofDetail = \App\Models\MemberIdProof::where('member_id',$id)->first();
                $nomineeDetail = \App\Models\MemberNominee::where('member_id',$id)->first();
                $nomineeDOB=date("m/d/Y", strtotime($nomineeDetail->dob));

            return Response::json(['view' => view('templates.branch.associate_management.partials.member_detail' ,['memberData' => $data,'idProofDetail' => $idProofDetail])->render(),'msg_type'=>'success','id'=>$id,'nomineeDetail' => $nomineeDetail,'nomineeDOB'=>$nomineeDOB]);
            }
            else
            {
                return Response::json(['view' => 'No data found','msg_type'=>'error1']);
            }

        }
        else
        {
            return Response::json(['view' => 'No data found','msg_type'=>'error']);
        }


    }

    /**
     * Get Senior detail through senior code.
     * Route: ajax call from - /branch/member/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getSeniorDetail(Request $request)
    {
        $a = array("id","first_name", "last_name",'mobile_no','address','current_carder_id');
        $data=memberFieldData($a,$request->code,'associate_no');
        $resCount = count($data);
        $carder="";
        $carder_id="";
        if($resCount>0)
        {
            $carder=getCarderName($data[0]->current_carder_id) ;
             $carder_id=$data[0]->current_carder_id ;
        }

        $return_array = compact('data','resCount','carder','carder_id');
        return json_encode($return_array);
    }

    /**
     * Get carder below Senior carder.
     * Route: ajax call from - /branch/member/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getCarderAssociate(Request $request)
    {
        //print_r($request->id);die;
         $carde=\App\Models\Carder::where('id','<=',$request->id)->where('status',1)->where('is_deleted',0)->limit(3)->get(['id','name','short_name'] );
        $return_array = compact('carde');
        return json_encode($return_array);
    }

/**
     * Member's ssb account  exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function checkSsbAcount(Request $request)
    {
        $resCount=0;
        $data=getInvestmentAccount($request->member_id,$request->account_no);
        if(!empty($data))
        {
            $resCount=1;
        }
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
/**
     * Member's ssb account  Etail .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateSsbAccountGet(Request $request)
    {
        $resCount=0;
        $account_no='';
        $balance='';
        $name='';
        $data=getMemberSsbAccountDetail($request->member_id);
        $member=Member::where('id',$request->member_id)->first();
        if(!empty($data))
        {
            $account_no=$data->account_no;
            $balance=$data->balance;
            $resCount= 1;
        }
        if(!empty($member))
        {
            $name=$member->first_name.' '.$member->last_name;
        }
        $return_array = compact('account_no','balance','resCount','name');
        return json_encode($return_array);
    }

    /**
     * Member's ssb account Balance  exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function checkSsbAcountBalance(Request $request)
    {
        //print_r($request->member_id);die;
        $resCount=0;
        $data=getMemberSsbAccountDetail($request->member_id);
        //print_r($data);die;
        if(!empty($data))
        {
            if($data->balance>=$request->rd_amount)
            {
                $resCount=1;
            }
        }
        else
        {
            $resCount=2;
        }
        $return_array = compact('resCount');
        return json_encode($return_array);
    }

	/**
	 * Member's rd account Balance  exists or not .
	 * Route: ajax call from - /branch/associate/registration
	 * Method: Post
	 * @param  \Illuminate\Http\Request  $request
	 * @return JSON array
	 */
	public function associateRdbAccountGet(Request $request)
	{
		$data = Memberinvestments::where('id', $request->rdAccountId)->select('account_number','deposite_amount','member_id')->first();
		$return_array = [];
		if ( $data ){
			$return_array['account_id'] = $data->account_number;
			$return_array['amount'] = $data->deposite_amount;
			$return_array['name'] = Member::find($data->member_id)->first_name;
		} else {
			$return_array['account_id'] = '';
			$return_array['amount'] = '';
			$return_array['name'] = '';
		}
		return json_encode($return_array);
	}

	public function associateRdbAccounts(Request $request)
	{
		$data = Memberinvestments::where(['plan_id' => 10, 'member_id' => $request->member_id])->where('deposite_amount', '>=', 500)->where('tenure', '>=', 5)->pluck
		('account_number','id');
		
		return json_encode($data);
	}
     /**
     * Show recipt detail after create Associate
     * Route: branch/associate/receipt/ 
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function reciept($id)
    {
        $data['title']='Associate | Recipt'; 
        $data['type']='1'; 
        $data['recipt']=Receipt::with(['memberReceipt' => function($query){ $query->select('id', 'member_id','first_name','last_name','mobile_no','address','ssb_account','rd_account','associate_no');}])->with( ['branchReceipt' => function($query){ $query->select('id', 'name','branch_code');}])->where('id',$id)->first(); 
        $data['recipt_amount']=ReceiptAmount::where('receipt_id',$id)->get(['receipt_id','amount','type_label']); 
        $data['total_amount']=ReceiptAmount::where('receipt_id',$id)->sum('amount');  
        
        return view('templates.branch.associate_management.recipt', $data);
    }
}
