<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Member;
use App\Models\Branch;
use App\Models\Memberinvestments;
use App\Models\AssociateMonthlyCommission;
use App\Models\CommissionFuleCollection;
use App\Models\CommissionLeaserMonthly;
use App\Models\CommissionLeaserDetailMonthly;
use App\Models\AssociateCommissionTotalMonthly;
use App\Http\Controllers\Admin\CommanCommissionController;
use App\Models\CommisionMonthEnd;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
use App\Models\SamraddhBank;
use PDF;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Associate Management AssociateController
    |--------------------------------------------------------------------------
    |
    | This controller handles associate all functionlity.
*/
class AssociateCommissionMonthlyController extends Controller
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
     * Show Laser-- commision transfer to ssb account .
     * Route: /associatebusinessreport
     * Method: get
     * @return  view
     */
    public function commissionTransfer(Request $request)
    {
       

        if (check_my_permission(Auth::user()->id, "15") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }

        $data['title']='Commission Transfer | Ledger Create - Monthly';
        $data['start_date']='';
        $data['end_date']='';
        $data['companies'] = \App\Models\Companies::where('status', 1)->where('delete', '0')->get(['id', 'name']);
        if(Request1::isMethod('post'))
        {
            $start_date=$request['start_date'];
            $end_date=$request['end_date'];
            $startDateDb=date("Y-m-d", strtotime(convertDate($start_date)));
            $endDateDb=date("Y-m-d", strtotime(convertDate($end_date)));
            $data['start_date']=$request['start_date'];
            $data['end_date']=$request['end_date'];
            $data['start_date_time']=$startDateDb;
            $data['end_date_time']=$endDateDb;
            $data['code']=1;
            $data['company_id']=$request['company_id'];
            $filter['company_id']=$request['company_id'];
            $filter['date']=$startDateDb;

            $data['assocaiteCompany'] = \App\Models\CompanyAssociate::where('status','1')->first();

            $getCurentMont =date("m", strtotime(convertDate($startDateDb)));
                $getCurentYear =date("Y", strtotime(convertDate($startDateDb)));
            $data['total_commission']= AssociateCommissionTotalMonthly::with(['comm_member' => function($q){ $q->select('id','associate_no','first_name','last_name','current_carder_id','associate_join_date');
            } ]) 
            ->with(['tds_member' => function($q) use($filter) { $q->select('id','member_id','start_date','end_date')->whereRaw("'".$filter['date']."' BETWEEN start_date AND end_date")->where('company_id', $filter['company_id']);
            } ])  
            ->where('month',$getCurentMont)
            ->where('year',$getCurentYear)
            ->where('member_id','!=',1) 
            ->Where('company_id',$data['company_id'])
            ->where('status',2) 
            ->orderby('member_id','ASC')
           // ->offset(0)->limit(100) 
            ->get(); 


           // pd($data['total_commission'][0]);
            
        }
        return view('templates.admin.associate.commission_monthly.commission_create', $data);
    }
   public function commissionLedgerCreate(Request $request)
    {
         Session::put('created_at', $request['created_at']);
         $globaldate=$request['created_at'];
        DB::beginTransaction();
        try {
            $data=\App\Models\CommissionLeaserMonthly::where('company_id',$request->companyId)->where('status',1)->where('is_deleted',0)->where([['start_date','>',$request->start_date_time],['end_date','<=',$request->end_date_time]])->whereBetween('start_date',array($request->start_date_time,$request->end_date_time))
             ->WhereBetween('end_date',array($request->start_date_time,$request->end_date_time))->get();
             $count=count($data);
             if($count>0)
             {
                return back()->with('error', 'Selected date range already exits');
             }
             $company_id=$request->companyId;
            $leaser['start_date'] = $request->start_date_time;
            $leaser['end_date'] = $request->end_date_time;
            $d=date("Y-m-d", strtotime(convertDate($request->end_date_time))).' '.date("H:i:s");

            $globaldate=date("Y-m-d H:i:s", strtotime(convertDate($d)));
            
            $startDateDb=date("Y-m-d", strtotime(convertDate($request->start_date_time)));
            $endDateDb=date("Y-m-d", strtotime(convertDate($request->end_date_time)));
            $start_date=date("My", strtotime($request->start_date_time));
            $end_date=date("My", strtotime($request->end_date_time));
            $sms_date=date("MY", strtotime($request->start_date_time));
            $leaser['total_amount'] = $request->total;
            $leaser['ledger_amount'] = $request->totalFinalAmount;
            $leaser['total_fuel'] = $request->totalFuleAmount;
            $leaser['total_collection'] = $request->totalCollection;
            $leaser['status'] = 3;
            $leaser['created_at'] = $globaldate;
            $leaser['month'] = date("m", strtotime(convertDate($request->start_date_time)));
            $leaser['year'] = date("Y", strtotime(convertDate($request->start_date_time)));
            $leaser['company_id'] = $company_id;
            $leaserCreate = \App\Models\CommissionLeaserMonthly::create($leaser);
            
            $leaserId = $leaserCreate->id;
            
                if(count($_POST['id'])>0)
                {
                    foreach ($_POST['id'] as $k => $val)
                    {
                        $dataComData= \App\Models\AssociateCommissionTotalMonthly::join('members','members.id','=','associate_commissions_total_monthly.member_id')->where('associate_commissions_total_monthly.id',$_POST['id'][$k])->first(['associate_commissions_total_monthly.*','members.id as mid','members.first_name','members.last_name','members.associate_no','members.associate_senior_id','members.associate_branch_id']);
                        // print_r($dataComData)     ;die;
                        $comDataUpdate = \App\Models\AssociateCommissionTotalMonthly::where('id',$_POST['id'][$k])->update([ 'status' => 1 ]);
                        $company_id=$dataComData->company_id;
                        //dd($_POST);
                        $leaser1['commission_leaser_id'] = $leaserId;
                        $leaser1['member_id'] = $dataComData->mid;
                        $leaser1['amount_tds'] = ($_POST['amount'][$k]);
                        $leaser1['amount'] = ($_POST['amount'][$k]-$_POST['tds'][$k]);
                        $leaser1['total_row'] =$dataComData->total_row;
                        $leaser1['commission_id'] = $dataComData->commission_id;
                        $leaser1['total_tds'] = $_POST['tds'][$k];
                        $leaser1['fuel'] = $_POST['fule'][$k];
                        $leaser1['collection'] = $_POST['collection'][$k];
                        $leaser1['status'] = 3;
                        $leaser1['created_at'] = $globaldate;
                        $leaser1['company_id'] = $company_id;

                        
                        $detail='Commission Ledger of  '.date("F Y", strtotime($request->start_date_time));
                        /*--------------------------sms end -------------------------*/
                        /*****************************Head impliment start ******************************/
                          $payment_mode=3;
                          $payment_type= 'CR';
                          $currency_code='INR';
                          $tdsAmount=$_POST['tds'][$k];
                          $fuleAmount = $_POST['fule'][$k];
                          $commAmount=($_POST['amount'][$k]-$_POST['tds'][$k]);
                          $commAmount_141=$commAmount;
                          if( $commAmount>0)
                          {
                            $commAmount= $commAmount;
                          }
                          else
                          {
                            $commAmount= 0;
                          }
                          $member_id=$dataComData->mid;
                          $amount=$commAmount+$fuleAmount+$tdsAmount;
                          $ssbAmountComm=$commAmount+$fuleAmount;
                          $daybookRef=CommanCommissionController::createBranchDayBookReferenceNew($amount,$globaldate);
                          $refId=$daybookRef;
                          $type_id=$leaserId;
                          $leaser1['create_ref_id'] = $refId;
                          $leaserCreate = \App\Models\CommissionLeaserDetailMonthly::create($leaser1);
                          $CommTrnId=$leaserCreate->id;
                          $associate_id=$dataComData->associate_senior_id;
                          $branch_id=$dataComData->associate_branch_id;
                          $entry_date=date("Y-m-d", strtotime(convertDate($globaldate)));
                          $entry_time=date("H:i:s", strtotime(convertDate($globaldate)));
                          $created_by=1;
                          $created_by_id=Auth::user()->id;
                          $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                          $updated_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                          $randNumber = mt_rand(0,999999999999999);
                          $v_no = $randNumber;
                          $v_date = $entry_date;
                          
                        // commission entry----------------------------
                          $des=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-commission create for '.date("F Y", strtotime($request->start_date_time));
                          $type=2 ;$sub_type=21;
                          //ASSOCIATE CREDITORS
                          $head4ComSsb=141;
                          $allTranCommSSB=CommanCommissionController::headTransactionCreate($refId, $branch_id,$head4ComSsb, $type, $sub_type, $type_id, $associate_id, $member_id,  $commAmount_141,  $des,'CR', $payment_mode, $currency_code, $v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId,$company_id);


                            $head3Com=87;  ;
                          $allTranComm=CommanCommissionController::headTransactionCreate($refId, $branch_id,$head3Com, $type, $sub_type, $type_id, $associate_id, $member_id,$_POST['amount'][$k], $des,'DR', $payment_mode, $currency_code, $v_no,$entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId,$company_id);
                          
                        //fule entry--------------------------------
                        if($fuleAmount>0)
                        {
                            $ssb_account_id_to =NULL;
                            $ssb_account_tran_id_to= NULL;
                            $desFule=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-Fuel charge create for '.date("F Y", strtotime($request->start_date_time));
                            $type1=2 ;$sub_type1=25;
                            //ASSOCIATE CREDITORS  fule Libility
                            $head4ComSsb1=141;
                            $allTranCommSSB1=CommanCommissionController::headTransactionCreate($refId, $branch_id,$head4ComSsb1, $type1, $sub_type1, $type_id, $associate_id, $member_id,$fuleAmount, $desFule,'CR', $payment_mode, $currency_code, $v_no,$entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId,$company_id);                  
                            
                            

                            //fule head
                            $head3Fule=88; 
                            $allTranFule=CommanCommissionController:: headTransactionCreate($refId, $branch_id,$head3Fule, $type1, $sub_type1, $type_id, $associate_id, $member_id,$fuleAmount, $desFule,'DR', $payment_mode, $currency_code, $v_no,$entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId,$company_id);
                            
                            
                          
                        }
                        if($tdsAmount>0)
                        {
                            $ssb_account_id_to =NULL;
                            $ssb_account_tran_id_to= NULL;
                            $destds=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-TDS deduction for '.date("F Y", strtotime($request->start_date_time));
                            $type2=9 ;$sub_type2=90;
                            //tds head 
                            $head4Tds=63; 
                            $allTranTds=CommanCommissionController:: headTransactionCreate($refId, $branch_id,$head4Tds, $type2, $sub_type2, $type_id, $associate_id, $member_id,$tdsAmount, $destds,'CR', $payment_mode, $currency_code, $v_no,$entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId,$company_id);
                              
                              
                        }
                      /*****************************Head impliment End ******************************/
                    }
                }


                $dataCompany = \App\Models\Companies::where('status', 1)->where('delete', '0')->count();
                $dataLeadger=\App\Models\CommissionLeaserMonthly::where('month', date("m", strtotime(convertDate($request->start_date_time))))->where('year', date("Y", strtotime(convertDate($request->start_date_time))))->count();
                if($dataCompany==$dataLeadger)
                {
                  $end = CommisionMonthEnd::where('month', date("m", strtotime(convertDate($request->start_date_time))))->where('year', date("Y", strtotime(convertDate($request->start_date_time))))->update([ 'leadger_created' => 1]);   
                }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Commission Leadger Create  Successfully');
    }
    public function commissionPayment($id,$company_id){

      if (check_my_permission(Auth::user()->id, "321") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }

        $data['title']='Commission |Payment  -- Monthly';

        // $data['comm']=\App\Models\CommissionLeaserDetailMonthly::join('members','members.id','=','commission_leaser_detail_monthly.member_id')
        // ->join('saving_accounts','saving_accounts.member_id','=','commission_leaser_detail_monthly.member_id')
        // ->join('carders','carders.id','=','members.current_carder_id')
        // ->where('commission_leaser_id',$id)
        // ->where('commission_leaser_detail_monthly.status',3)
        // ->orderby('id','DESC')->get(['commission_leaser_detail_monthly.*','members.id as mid','members.first_name','members.last_name','members.associate_no','members.associate_senior_id','members.associate_branch_id','members.current_carder_id','saving_accounts.account_no as saccount_no','carders.name as cname']);

        $expID=\App\Models\AssociateException::where('fuel_status',1)->where('commission_status',1)->pluck('associate_id');


        //echo count($expID);die;

        $data['comm']=\App\Models\CommissionLeaserDetailMonthly::with(['member'=>function($q){
              $q->select('id','member_id','first_name','last_name','current_carder_id','associate_no','associate_senior_id','associate_branch_id')
              ->with(['getCarderNameCustom'=>function($q){
                $q->select('id','name','short_name');
              },'memberIdProof'=>function($q){
                $q->select('id','first_id_no','member_id','second_id_no','first_id_type_id','second_id_type_id');
              }]);
            },'SavingAcount'=>function($q) use($company_id){
              $q->select('id','member_id','account_no','customer_id')->where('company_id',$company_id);
            }]) 
            ->where('commission_leaser_id',$id)
            ->where('commission_leaser_detail_monthly.status',3)
            ->whereNotIn('commission_leaser_detail_monthly.member_id',$expID)
            ->get();
           // pd($data['comm'][0]['company']);
           $data['company']= \App\Models\Companies::where('id',$company_id)->first(['id','name']);
        return view('templates.admin.associate.commission_monthly.commission_payment', $data);
    }
    public function commissionPaymentSave(Request $request)
    {
      $select_id_get=rtrim($request->select_id,',');
      $select_id=explode(",",$select_id_get);// done
      Session::put('created_at', $request['created_at']);
         $globaldate=$request['created_at'];
         $company=$company_id=$request['company'];
        DB::beginTransaction();
        try {
            if(count($select_id)>0)
            {
              foreach($select_id as $val)
              {
              // $comDetail=$dataComData =$memDetail= \App\Models\CommissionLeaserDetailMonthly::join('members','members.id','=','commission_leaser_detail_monthly.member_id')->where('commission_leaser_detail_monthly.id',$val)->first(['commission_leaser_detail_monthly.*','members.id as mid','members.first_name','members.last_name','members.associate_no','members.associate_senior_id','members.associate_branch_id','members.current_carder_id','members.mobile_no']);
               $comDetail=\App\Models\CommissionLeaserDetailMonthly::with(['member'=>function($q){
                $q->select('id','member_id','first_name','last_name','current_carder_id','associate_no','associate_senior_id','associate_branch_id','mobile_no')
                ->with(['getCarderNameCustom'=>function($q){
                  $q->select('id','name','short_name');
                },'memberIdProof'=>function($q){
                  $q->select('id','first_id_no','member_id','second_id_no','first_id_type_id','second_id_type_id');
                }]);
              },'SavingAcount'=>function($q) use($company){
                $q->select('id','member_id','account_no','customer_id','member_investments_id')->where('company_id',$company);
              }]) 
              ->where('id',$val)
              ->first();
            /********* Start  stope commision dobule entry validation - 29th feb 24 -- Zaid  ********/
            if($comDetail->status != 3 && !empty($comDetail->pay_ref_id))
			{
              $alreadyPaidCommissions[] = $comDetail->id;
              continue;
            }
            /********* End   ********/


                  $ledgerID=$comDetail->commission_leaser_id;
                  $ledgerDetailID = $comDetail->id; 
                  $associate_id=$comDetail->member->associate_senior_id;
                  $branch_id=$comDetail->member->associate_branch_id;
                  $member_id =$comDetail->member->id;
                  $payment_mode=3;
                  $payment_type= 'CR';
                  $currency_code='INR';
                  $tdsAmount=$comDetail->total_tds;
                  $fuleAmount = $comDetail->fuel;
                  $commAmounttds=$comDetail->amount_tds;
                  $commAmount=$comDetail->amount;
                  $entry_date=date("Y-m-d", strtotime(convertDate($globaldate)));
                  $entry_time=date("H:i:s", strtotime(convertDate($globaldate)));
                  $created_by=1;
                  $created_by_id=Auth::user()->id;
                  $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                  $updated_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                  $randNumber = mt_rand(0,999999999999999);
                  $v_no = $randNumber;
                  $v_date = $entry_date;
                  $ledgerDetail= \App\Models\CommissionLeaserMonthly::where('id',$ledgerID)->first(['start_date','end_date']);
                  $start_date=date("My", strtotime($ledgerDetail->start_date));
                  $end_date=date("My", strtotime($ledgerDetail->end_date));
                  $sms_date=date("MY", strtotime($ledgerDetail->start_date));
                  $ssbAccountDetail=$comDetail->SavingAcount[0];
                 // pd($ssbAccountDetail->id);
                  $detail='Comm '.$start_date;
                    $amounTra=$comDetail->amount_tds;
                    $transactionBydate = \App\Models\SavingAccountTransactionView::select('opening_balance')->where('saving_account_id',$ssbAccountDetail->id)->where('opening_date','<=',$globaldate)->orderBy(\DB::raw('date(opening_date)'), 'DESC')->orderBy('id', 'DESC')->first();
                    $balanceTra=0;
                    if($transactionBydate)
                    {
                      $balanceTra = $transactionBydate->opening_balance;
                    }
                    $daybookRef=CommanCommissionController::createBranchDayBookReferenceNew($amounTra,$globaldate);
                    $dataSsb['deposit'] = $amounTra;
                    $ssbBalance = $balanceTra+$amounTra;
                    $dataSsb['saving_account_id'] = $ssbAccountDetail->id;
                    $dataSsb['account_no'] = $ssbAccountDetail->account_no;
                    $dataSsb['opening_balance'] = $ssbBalance;
                    $dataSsb['type']=3;
                    $dataSsb['amount'] = $balanceTra;
                    $dataSsb['description'] = $detail;
                    $dataSsb['currency_code'] = 'INR';
                    $dataSsb['payment_type'] = 'CR';
                    $dataSsb['payment_mode'] = 3;
                    $dataSsb['created_at'] = $globaldate;
                    $dataSsb['daybook_ref_id'] = $daybookRef;
                    $dataSsb['company_id'] = $company_id;
                    $dataSsb['branch_id'] = $branch_id;
                    
                    $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
                    $SSBTRANID=$resSsb->id;
                    
                    $refId=$daybookRef;
                    $type_id=$ledgerID;
                    $CommissionLeaserDetail = \App\Models\CommissionLeaserDetailMonthly::where('id',$val)->update([ 'status' => 1 ,'pay_ref_id' =>$refId]);

                    $ssb_account_id_to =$ssbAccountDetail->id;
                    $ssb_account_tran_id_to= $SSBTRANID;
                    $type_transaction_id=$ssb_account_tran_id_to;
                    
                    // commission entry----------------------------
                    $des=$comDetail->member->first_name.' '.$comDetail->member->last_name.'('.$comDetail->member->associate_no.')-commission paid for '.date("F Y", strtotime($start_date));
                    $type=2 ;$sub_type=21;
                    //ASSOCIATE CREDITORS  -(Minees )
                    $head4ComSsb=141;
                    $allTranCommSSB=CommanCommissionController::headTransactionCreateSSB($refId, $branch_id, $head4ComSsb, $type, $sub_type, $type_id, $associate_id, $member_id,$commAmount, $des, 'DR', $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to,$company_id);
                    
                    // SSB Commission Add

                    $typeS=4 ;$sub_typeS=45;
                    //commission ssb head

                    $hedGet = \App\Models\Memberinvestments::with('getPlanCustom:id,name,deposit_head_id')->where('id',$ssbAccountDetail->member_investments_id)->first(['id','plan_id']);
                   
                    
                    $head4ComSsb=$hedGet->getPlanCustom->deposit_head_id;
                   

                    $allTranCommSSB=CommanCommissionController::headTransactionCreateSSB($refId, $branch_id, $head4ComSsb, $typeS, $sub_typeS, $ssb_account_id_to, $associate_id, $member_id,$amounTra, $des, 'CR', $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to,$company_id);                    
                    

                    //commission branch daybook
                    $comDR=$comDetail->member->first_name.' '.$comDetail->member->last_name.'('.$comDetail->member->associate_no.') A/c Dr'.$amounTra.'/-';
                    $comCR='To SSB('.$ssbAccountDetail->account_no.') A/c Cr '.$amounTra.'/-';
                    $daybookComm=CommanCommissionController:: NewFieldBranchDaybookCreate($refId, $branch_id, $typeS, $sub_typeS, $ssb_account_id_to, $associate_id, $member_id,$amounTra, $des, $comDR, $comCR, $payment_type, $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id,$created_at, $updated_at, $type_transaction_id, $ssb_account_id_to,  $ssb_account_tran_id_to,$company_id);
              
                        ///   tds entry
                        if($tdsAmount>0)
                        {
                            $type2=9 ;$sub_type2=90;
                            $detailTds='TDS deduction '.$start_date;
                            $dataSsbT['withdrawal'] = $tdsAmount;
                            $ssbBalanceTds = $ssbBalance-$tdsAmount;
                            $dataSsbT['saving_account_id'] = $ssbAccountDetail->id;
                            $dataSsbT['account_no'] = $ssbAccountDetail->account_no;
                            $dataSsbT['opening_balance'] = $ssbBalanceTds;
                            $dataSsbT['type']=14;
                            $dataSsbT['amount'] = $ssbBalanceTds;
                            $dataSsbT['description'] = $detailTds;
                            $dataSsbT['currency_code'] = 'INR';
                            $dataSsbT['payment_type'] = 'DR';
                            $dataSsbT['payment_mode'] = 3;
                            $globaldateTds=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)) +5 );
                            $dataSsbT['created_at'] = $globaldateTds;
                            $dataSsbT['daybook_ref_id'] = $refId;
                            $dataSsbT['company_id'] = $company_id;
                            $dataSsbT['branch_id'] = $branch_id;

                            $resSsbTds = \App\Models\SavingAccountTranscation::create($dataSsbT);
                            $SSBTRANIDtds=$resSsbTds->id;
                            $ssb_account_id_to =$ssbAccountDetail->id;
                            $ssb_account_tran_id_to= $SSBTRANIDtds;
                            $destds=$comDetail->member->first_name.' '.$comDetail->member->last_name.'('.$comDetail->member->associate_no.')-TDS deduction for '.date("F Y", strtotime($start_date));
                            if(associateTdsDeductGet($member_id,$start_date,$company_id)>0)
                            {
                              $tdsdeductSave =1;
                            }
                            else
                            {
                              $dateTdsSaver =getFinacialYear();
                              $tdsSaveD['member_id'] = $member_id;
                              $tdsSaveD['start_date'] =$dateTdsSaver['dateStart'];
                              $tdsSaveD['end_date'] = $dateTdsSaver['dateEnd'];
                              $tdsSaveD['company_id'] = $company_id;
                              $tdsSaveDSave = \App\Models\AssociateTdsDeduct::create($tdsSaveD);
                            }
                            
                            //tds ssb head
                            $head4ComSsbtds=$head4ComSsb;
                            
                            $allTranCommSSB=CommanCommissionController:: headTransactionCreateSSB($refId, $branch_id, $head4ComSsbtds, 4, 415, $ssb_account_id_to, $associate_id, $member_id,$tdsAmount, $destds, 'DR', $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_tran_id_to, $ssb_account_id_to, $ssb_account_tran_id_to,$company_id);  
                            
                            $TDSCR= 'To'.$comDetail->member->first_name.' '.$comDetail->member->last_name.'('.$comDetail->member->associate_no.') A/c CR'.$tdsAmount.'/-';
                            $TDSDR=' SSB('.$ssbAccountDetail->account_no.') A/c DR '.$tdsAmount.'/-';

                            $daybookTds=CommanCommissionController:: NewFieldBranchDaybookCreate($refId, $branch_id, 4,415,$ssb_account_id_to, $associate_id, $member_id,$tdsAmount, $des, $TDSDR, $TDSCR,'DR', $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id,$created_at, $updated_at, $ssb_account_tran_id_to, $ssb_account_id_to,  $ssb_account_tran_id_to,$company_id); 
                        }
                        else
                        {
                            $ssbBalanceTds = $ssbBalance;
                        }
                        if($fuleAmount>0)
                        {
                            $detailTds='Fuel '.$start_date;
                            $dataSsbF['deposit'] = $fuleAmount;
                            $ssbBalanceFuel = $ssbBalanceTds+$fuleAmount;
                            $dataSsbF['saving_account_id'] = $ssbAccountDetail->id;
                            $dataSsbF['account_no'] = $ssbAccountDetail->account_no;
                            $dataSsbF['opening_balance'] = $ssbBalanceFuel;
                            $dataSsbF['type']=4;
                            $dataSsbF['amount'] = $fuleAmount;
                            $dataSsbF['description'] = $detailTds;
                            $dataSsbF['currency_code'] = 'INR';
                            $dataSsbF['payment_type'] = 'CR';
                            $dataSsbF['payment_mode'] = 3;
                            $globaldateFuel=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)) +10 );
                            $dataSsbF['created_at'] = $globaldateFuel;                            
                            $dataSsbF['daybook_ref_id'] = $refId;
                            $dataSsbF['company_id'] = $company_id;
                            $dataSsbF['branch_id'] = $branch_id;

                            $resSsbFuel = \App\Models\SavingAccountTranscation::create($dataSsbF);

                            $SSBTRANIDFule=$resSsbFuel->id;
                            $ssb_account_id_to =$ssbAccountDetail->id;
                            $ssb_account_tran_id_to= $SSBTRANIDFule;

                            $desFule=$comDetail->member->first_name.' '.$comDetail->member->last_name.'('.$comDetail->member->associate_no.')-Fuel charge paid for '.date("F Y", strtotime($start_date));
                            $type11=2 ;$sub_type11=25;
                            //ASSOCIATE CREDITORS  fule Libility
                            $head4ComSsb1=141;
                            $allTranCommSSB1=CommanCommissionController:: headTransactionCreateSSB($refId, $branch_id, $head4ComSsb1, 2, 25, $ledgerID, $associate_id, $member_id,$fuleAmount, $desFule, 'DR', $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_tran_id_to, $ssb_account_id_to, $ssb_account_tran_id_to,$company_id);   
                            
                            $type1=4 ;$sub_type1=46;
                            //fule ssb head
              
                            $head4FuleSsb=$head4ComSsb;
              
                            $allTranFuleSSb=CommanCommissionController:: headTransactionCreateSSB($refId, $branch_id, $head4FuleSsb, $type1,$sub_type1,$ssb_account_id_to, $associate_id, $member_id,$fuleAmount, $desFule, 'CR', $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_tran_id_to, $ssb_account_id_to, $ssb_account_tran_id_to,$company_id);  

                            //fule branch daybook
             
                            $fuleDR=$comDetail->member->first_name.' '.$comDetail->member->last_name.'('.$comDetail->member->associate_no.') A/c Dr'.$fuleAmount.'/-';
                            $fuleCR='To SSB('.$ssbAccountDetail->account_no.') A/c Cr '.$fuleAmount.'/-';
                            
                            $daybookFule=CommanCommissionController:: NewFieldBranchDaybookCreate($refId, $branch_id,$type1,$sub_type1,$ssb_account_id_to,$associate_id, $member_id,$fuleAmount, $desFule, $fuleDR, $fuleCR,$payment_type, $payment_mode, $currency_code,$v_no, $entry_date, $entry_time, $created_by, $created_by_id,$created_at, $updated_at, $ssb_account_tran_id_to, $ssb_account_id_to,$ssb_account_tran_id_to,$company_id);  
              
                            $sms_text_fule='and Monthly Fuel amount '.$sms_date.' has been credited with Rs.'.$fuleAmount;
                            $sms_text='Your Monthly Commission '.$sms_date.' has been credited with Rs.'.$commAmount.' and Monthly Fuel amount '.$sms_date.' has been credited with Rs.'.$fuleAmount.' in Saving A/c '.$ssbAccountDetail->account_no.' on '.date("d-M-Y").' Thanks. http://www.samraddhbestwin.com';
                            $templateId = 1207161648340908349;
                        }
                        else
                        {
                            $sms_text='Your Monthly Commission '.$sms_date.' has been credited with Rs.'.$commAmount.' in Saving A/c '.$ssbAccountDetail->account_no.' on '.date("d-M-Y").' Thanks. http://www.samraddhbestwin.com';
                            $templateId = 1207161648370549369;
                        }
                        $contactNumber = array();
                        $contactNumber[] = $comDetail->member->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $sms_text,$templateId );
              }
              $getCount=\App\Models\CommissionLeaserDetailMonthly::where('commission_leaser_id',$ledgerID)->where('status','!=',1)->count();
              if($getCount)
              {
                $CommissionLeaserDetail = \App\Models\CommissionLeaserMonthly::where('id',$ledgerID)->update([ 'status' => 2 ]);
              }
              else
              {
                $CommissionLeaserDetail = \App\Models\CommissionLeaserMonthly::where('id',$ledgerID)->update([ 'status' => 1 ]);
              }
            }
          DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
      return redirect('admin/commission/commissionUpdate/'.$select_id_get.'/'.$ledgerID.'/'.$company_id)->with('success', 'Commission Payment sucessfully');
      //return Redirect::route('admin.associate.CommissionUpdate')->with(['data'=>$select_id]);
    }
public function CommissionUpdate($data,$lid,$company_id)
{
       $select_id_get=rtrim($data,',');
      $select_id=explode(",",$select_id_get);
        DB::beginTransaction();
        try {
            if(count($select_id)>0)
            {
              foreach($select_id as $val)
              {
               $comDetail= \App\Models\CommissionLeaserDetailMonthly::where('id',$val)->first();
                if (isset($comDetail->commission_id))
                {
                  
                    $commGet=rtrim($comDetail->commission_id,',');
                    $com=explode(",",$commGet); 
                  
                  $comDataUpdate = AssociateMonthlyCommission::whereIN('id',$com)->update([ 'is_distribute' => 1 ]);
                }
               //$comDataUpdate = AssociateMonthlyCommission::whereIN('id',$com)->update([ 'is_distribute' => 1 ]);
              }
            }
        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
      return redirect('admin/commission/ledger_payment/'.$lid.'/'.$company_id)->with('success', 'Commission Payment sucessfully');
}
/**
     * Show Laser-- commision transfer to ssb account .
     * Route: /associatebusinessreport
     * Method: get
     * @return  view
     */
    public function commissionTransferList(Request $request)
    {
      if (check_my_permission(Auth::user()->id, "317") != "1")
      {
        return redirect()->route('admin.dashboard')->with('alert', "you do not  have permission");
      }
        $data['title']='Commission | Ledger List -- Monthly';
        return view('templates.admin.associate.commission_monthly.commission_transfer_list', $data);
    }
    /**
     * Show Laser-- commision transfer to ssb account .
     * Route: ajax call from - /admin/associate
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function leaserList(Request $request)
    {
        if ($request->ajax())
        {

          $arrFormData = array();   
          if(!empty($_POST['searchform']))
          {
              foreach($_POST['searchform'] as $frm_data)
              {
                  $arrFormData[$frm_data['name']] = $frm_data['value'];
              }
          }

            $data=\App\Models\CommissionLeaserMonthly::with('ledgerCompany:id,name')->where('is_deleted',0);

            if(isset($arrFormData['company_id']) && $arrFormData['company_id']>0){
              $company_id = $arrFormData['company_id'];
              $data=$data->where('company_id',$company_id);
            }
            $data=$data->orderby('id','DESC')->get();
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('start', function($row){
                $start = date("d/m/Y H:i:s a", strtotime($row->start_date));
                return $start;
            })
            ->rawColumns(['start'])

            ->addColumn('company_name', function($row){
              $name = $row['ledgerCompany']->name;
              return $name;
          })
          ->rawColumns(['company_name'])

            ->addColumn('end', function($row){
                $end = date("d/m/Y H:i:s a", strtotime($row->end_date));
                return $end;
            })
            ->rawColumns(['end'])
            ->addColumn('total', function($row){
                $total = $row->total_amount;
                return number_format((float)$total, 2, '.', ''); 
            })
            ->rawColumns(['total'])
            ->addColumn('credit', function($row){
                $credit = $row->credit_amount;
                 return number_format((float)$credit, 2, '.', ''); 
            })
            ->rawColumns(['total'])
            ->addColumn('status', function($row){
                if($row->status==1)
                {
                  $status = 'Transferred';
                }
                else if($row->status==2)
                {
                  $status = 'Partial Transfer';
                }
                else if($row->status==3)
                {
                  $status = 'Pending';
                }
                else{
                    $status = 'Deleted';
                }
                return $status;
            })
            ->rawColumns(['status'])
            ->addColumn('created', function($row){
                $created = date("d/m/Y H:i:s a", strtotime($row->created_at));
                return $created;
            })
            ->rawColumns(['created'])
             ->addColumn('ledgerAmount', function($row){
                $ledgerAmount = $row->ledger_amount;
                 return number_format((float)$ledgerAmount, 2, '.', '');;;
            })
            ->rawColumns(['ledgerAmount'])
            ->addColumn('total_fuel', function($row){
                $total_fuel = $row->total_fuel;
                return number_format((float)  $total_fuel, 2, '.', '');;;
            })
            ->rawColumns(['total_fuel'])
            ->addColumn('credit_fuel', function($row){
                $credit_fuel = $row->credit_fuel;
                return number_format((float) $credit_fuel, 2, '.', '');;;
            })
            ->rawColumns(['credit_fuel'])
            ->addColumn('action', function($row){
                 $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $url = URL::to("admin/commission/transfer-detail/".$row->id."");
                $url1 = URL::to("admin/commission/ledger-delete/".$row->id."");
                $url2 = URL::to("admin/commission/ledger_payment/".$row->id."/".$row['ledgerCompany']->id);
                $btn .= '<a  class="dropdown-item" href="'.$url.'" title="Ledger  Detail"><i class="icon-eye-blocked2  mr-2"></i>Ledger  Detail</a>  ';
                $btn .= '<a  class="dropdown-item" href="'.$url2.'" title="Ledger  Payment"><i class="icon-share4  mr-2"></i>Ledger  Payment</a>  ';
                if($row->status==1)
                {
                if(Auth::user()->id!= "13"){
               // $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="leaserDelete('.$row->id.')"><i class="fa fa-trash  mr-2"></i>Delete</button>  ';
                }
                }
                $btn .= '</div></div></div>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }
    /**
     * Show Laser-- commision transfer to ssb account Detail.
     * Route: /associatebusinessreport
     * Method: get
     * @return  view
     */
    public function commissionTransferDetail($id)
    {
      if (check_my_permission(Auth::user()->id, "322") != "1")
      {
        return redirect()
        ->route('admin.dashboard')
        ->with('alert', "you do not  have permission");
      }
        $data['title']='Commission | Ledger Detail  -- Monthly';
        $data['detail']=\App\Models\CommissionLeaserMonthly::with('ledgerCompany:id,name')->where('id',$id)->first();
        return view('templates.admin.associate.commission_monthly.transfer_list_detail', $data);
    }
    /**
     * Show Laser-- commision transfer to ssb account detail .
     * Route: ajax call from - /admin/associate
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function leaserDetailList(Request $request)
    {
        if ($request->ajax())
        {
          $arrFormData = array();   
            if(!empty($_POST['searchform']))
            {
                foreach($_POST['searchform'] as $frm_data)
                {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data=\App\Models\CommissionLeaserDetailMonthly::select('id','member_id','amount','status','created_at','total_tds','fuel','collection','amount_tds','commission_leaser_id')
            ->with(['member'=>function($q){
              $q->select('id','member_id','first_name','last_name','current_carder_id','associate_no')->with(['getCarderNameCustom'=>function($q){
                $q->select('id','name','short_name');
              },'memberIdProof'=>function($q){
                $q->select('id','first_id_no','member_id');
              }]);
            },'SavingAcount'=>function($q) use($arrFormData){
              $q->select('id','member_id','account_no','customer_id')->where('company_id',$arrFormData['company_id']);
            }])
            ->with(['commissionLeaser' => function($q){ $q->select('id','month','year'); } ])
            ->where('commission_leaser_id',$request->id);
            if(isset($arrFormData['associate_code']) && $arrFormData['associate_code']!=''){
              $meid = $arrFormData['associate_code'];
              $data=$data->whereHas('member', function ($query) use ($meid) {
                            $query->where('members.associate_no',$meid);
                          });
            } 
            $count =  $data->count('id');
            $data = $data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount  = $count;
            $sno=$_POST['start'];
            $rowReturn = array();
          //  pd($data[0]);die;
            foreach ($data as $row)
            {
                $sno++;
                $val['DT_RowIndex']=$sno;
                if(isset($row['member']->associate_no))
                {
                  $val['code']=$row['member']->associate_no;
                }
                else{
                    $val['code']='N/A';
                }
                 if(isset($row['member']['getCarderNameCustom']))
                {
                 // $val['carder']=$row['member']['getCarderNameCustom'.'short_name']->name;
                  $val['carder']=$row['member']['getCarderNameCustom']->name.'('.$row['member']['getCarderNameCustom']->short_name.')';
                }
                else{
                    $val['carder']='N/A';
                }
                if(isset($row['member']))
                {
                  $val['name']=$row['member']->first_name.' '.$row['member']->last_name;
                }
                else{
                    $val['name']='N/A';
                }
                if(isset($row->amount))
                {
                  $val['total']=number_format((float) $row->amount, 2, '.', '');
                }
                else{
                    $val['total']='N/A';
                }
                if(count($row['SavingAcount'])>0)
                {
                  $val['account']=$row['SavingAcount'][0]->account_no;
                }
                else{
                    $val['account']='N/A';
                }
                 if(isset($row->status))
                {
                  if($row->status==1)
                  {
                    $status = 'Transferred';
                  }
                  else if($row->status==2)
                  {
                    $status = 'Partial Transfer';
                  }
                  else if($row->status==3)
                  {
                    $status = 'Pending';
                  }
                  else{
                      $status = 'Deleted';
                  }
                }
                else{
                  $status = 'Deleted';
              }
                $val['status'] = $status;
                if(isset($row->created_at))
                {
                  $val['created']=date("d/m/Y H:i:s a", strtotime($row->created_at));
                }
                else{
                    $val['created']='N/A';
                }
                if(isset($row->member_id))
                {
                  $val['pan']=  $row['member']['memberIdProof']->first_id_no;
                }
                else{
                    $val['pan']='N/A';
                }
                $val['tds'] =number_format((float) $row->total_tds, 2, '.', '');
                $val['fuel'] =number_format((float) $row->fuel, 2, '.', '');
                $val['collection'] =number_format((float) $row->collection, 2, '.', '');
                $val['amount_tds'] =number_format((float) $row->amount_tds, 2, '.', '');
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $url = URL::to("admin/associate-commission-detail/".$row->member_id."?year=".$row['commissionLeaser']->year."&month=".$row['commissionLeaser']->month."");
                 $url1 = URL::to("admin/associate/loan-commission-detail/".$row->member_id."?year=".$row['commissionLeaser']->year."&month=".$row['commissionLeaser']->month."");
                //  $btn .= '<a class="dropdown-item" href="'.$url.'" title="Investment Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Investment Commission Detail </a>  ';
                //   $btn .= '<a class="dropdown-item" href="'.$url1.'" title="Loan Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Loan Commission Detail</a>  ';
                 $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
        }
        $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
        return json_encode($output);
    }
    public function leaserDetailExport(Request $request)
    {
      if($request['leaserDetail_export'] == 0){
      $input = $request->all();
      $start = $input["start"];
      $limit = $input["limit"];
      $returnURL = URL::to('/')."/asset/ledger_details_monthly.csv";
      $fileName = env('APP_EXPORTURL')."asset/ledger_details_monthly.csv";
      global $wpdb;
      $postCols = array(
        'post_title',
        'post_content',
        'post_excerpt',
        'post_name',
      );
      header("Content-type: text/csv");
      }
      $data=\App\Models\CommissionLeaserDetailMonthly::select('id','member_id','amount','status','created_at','total_tds','fuel','collection','amount_tds','commission_leaser_id')->with(['member'=>function($q){
        $q->select('id','member_id','first_name','last_name','current_carder_id','associate_no')->with(['getCarderNameCustom'=>function($q){
          $q->select('id','name');
        },'memberIdProof'=>function($q){
          $q->select('id','first_id_no','member_id');
        }]);
      },'SavingAcount'=>function($q) use($request){
        $q->select('id','member_id','account_no','customer_id')->where('company_id',$request['company_id']);
      }]) 
      ->where('commission_leaser_id',$request->id);
      if(isset($request['associate_code']) && $request['associate_code']!=''){
        $meid = $request['associate_code'];
        $data=$data->whereHas('member', function ($query) use ($meid) {
                      $query->where('members.associate_no',$meid);
                    });
      }
        if($request['leaserDetail_export'] == 0){
        $totalResults=$data->orderby('id','DESC')->count();
        $results=$data->orderby('id','DESC')->offset($start)->limit($limit)->get();
        $result = 'next';
        if( ($start + $limit ) >= $totalResults){
          $result = 'finished';
        }
        // if its a fist run truncate the file. else append the file
        if($start==0) {
          $handle = fopen($fileName, 'w');
        }else{
          $handle = fopen($fileName, 'a');
        }
        if($start==0) {
          $headerDisplayed = false;
        }else{
          $headerDisplayed = true;
        }
        $sno=$_POST['start'];
        foreach ($results as $row)
        {
          $sno++;
          $val['S/N']=$sno;
          $val['ASSOCIATE CODE']=getSeniorData($row->member_id,'associate_no');
          $val['ASSOCIATE NAME']=getSeniorData($row->member_id,'first_name').' '.getSeniorData($row->member_id,'last_name');
          $val['ASSOCIATE CARDER']=getCarderName(getSeniorData($row->member_id,'current_carder_id'));

          $val['PAN NO']=get_member_id_proof($row->member_id,5);
          $val['TOTAL AMOUNT']=number_format((float) $row->amount_tds, 2, '.', '');
          $val['TDS AMOUNT']=number_format((float) $row->total_tds, 2, '.', '');
          $val['FINAL PAYABLE AMOUNT']=number_format((float) $row->amount, 2, '.', '');
          $val['TOTAL COLLECTION']=number_format((float)$row->collection, 2, '.', '');
          $val['FUEL AMOUNT']=number_format((float) $row->fuel, 2, '.', '');
          $val['SSB ACCOUNT NO']= 'N/A'; 
          if(count($row['SavingAcount'])>0)
          {
            $val['SSB ACCOUNT NO']= $row['SavingAcount'][0]->account_no;
          }
          
          $status='';
            if($row->status==1)
            {
              $status = 'Transferred';
            }
            else if($row->status==0)
            {
              $status = 'Deleted';
            }
            else{
              $status = 'Pending';
            }
          $val['STATUS']=$status;
          $val['CREATED']=date("d/m/Y H:i:s a", strtotime($row->created_at));
          if (!$headerDisplayed) {
            // Use the keys from $data as the titles
            fputcsv($handle, array_keys($val));
            $headerDisplayed = true;
          }
          // Put the data into the stream
          fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if($totalResults == 0)
        {
          $percentage=100;
        }
        else{
          $percentage = ($start+$limit)*100/$totalResults;
          $percentage = number_format((float)$percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
          'result'        => $result,
          'start'         => $start,
          'limit'         => $limit,
          'totalResults'  => $totalResults,
          'fileName' => $returnURL,
          'percentage' => $percentage
        );
        echo json_encode($response);
      }elseif ($request['leaserDetail_export'] == 1) {
      $data=$data->orderby('id','DESC')->get();
              $pdf = PDF::loadView('templates.admin.associate.commission_monthly.export_leaser_detail',compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
              $pdf->save(storage_path().'_filename.pdf');
              return $pdf->download('ledger_details_monthly.pdf');
        }
	}
  public function leaserExport(Request $request)
  {
		if($request['leaser_export'] == 0){
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/')."/asset/Monthly_commission_ledger.csv";
		$fileName = env('APP_EXPORTURL')."asset/Monthly_commission_ledger.csv";
		global $wpdb;
		$postCols = array(
			'post_title',
			'post_content',
			'post_excerpt',
			'post_name',
		);
		header("Content-type: text/csv");
		}
		$data=\App\Models\CommissionLeaserMonthly::with('ledgerCompany:id,name')->where('is_deleted',0);


    if(isset($request['company_id']) && $request['company_id']>0){
      $company_id = $request['company_id'];
      $data=$data->where('company_id',$company_id);
    }
    $data=$data->orderby('id','DESC');

		if($request['leaser_export'] == 0){
			$totalResults=$data->count();
			$results=$data->offset($start)->limit($limit)->get();
			$result = 'next';
			if( ($start + $limit ) >= $totalResults){
				$result = 'finished';
			}
			// if its a fist run truncate the file. else append the file
			if($start==0) {
				$handle = fopen($fileName, 'w');
			}else{
				$handle = fopen($fileName, 'a');
			}
			if($start==0) {
				$headerDisplayed = false;
			}else{
				$headerDisplayed = true;
			}
			$sno=$_POST['start'];
			foreach ($results as $row)
			{
				$sno++;
				$val['SR NO']=$sno;
        $val['COMPANY NAME']=$row->ledgerCompany->name;
				$val['START DATE TIME']=date("d/m/Y H:i:s a", strtotime($row->start_date));
			    $val['END DATE TIME']=date("d/m/Y H:i:s a", strtotime($row->end_date));
				 $val['TOTAL AMT']=$row->total_amount;
				 $val['TOTAL TRANSFER AMT']=$row->ledger_amount;
				   $credit='';
				 $credit = $row->credit_amount;
                 $credit= number_format((float)$credit, 2, '.', '');;;
				  $val['TOTAL REFUND AMT.']=$credit;
				  $val['TOTAL FUEL TRANSFER AMT']= $row->total_fuel;
				  $credit_fuel='';
				   $credit_fuel = $row->credit_fuel;
                $credit_fuel= number_format((float) $credit_fuel, 2, '.', '');;;
				$val['TOTAL FUEL REFUND .']= $credit_fuel;
				  $status='';
				  if($row->status==1)
          {
            $status = 'Transferred';
          }
          else if($row->status==3)
          {
            $status = 'Pending';
          }
          else if($row->status==2)
          {
            $status = 'Partial Transfer';
          }
          else{
              $status = 'Deleted';
          }
				 $val['STATUS']= $status;
				 $val['CREATE DATE']=date("d/m/Y H:i:s a", strtotime($row->created_at));
				if (!$headerDisplayed) {
					// Use the keys from $data as the titles
					fputcsv($handle, array_keys($val));
					$headerDisplayed = true;
				}
				// Put the data into the stream
				fputcsv($handle, $val);
			}
			 // Close the file
			fclose($handle);
			if($totalResults == 0)
			{
				$percentage=100;
			}
			else{
				$percentage = ($start+$limit)*100/$totalResults;
				$percentage = number_format((float)$percentage, 1, '.', '');
			}
			// Output some stuff for jquery to use
			$response = array(
				'result'        => $result,
				'start'         => $start,
				'limit'         => $limit,
				'totalResults'  => $totalResults,
				'fileName' => $returnURL,
				'percentage' => $percentage
			);
			echo json_encode($response);
		}elseif ($request['leaser_export'] == 1) {
			$data =$data->orderby('id','DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.commission_monthly.export_leaser',compact('data'));
            $pdf->save(storage_path().'_filename.pdf');
            return $pdf->download('Monthly_commission_ledger.pdf');
        }
	}
}
