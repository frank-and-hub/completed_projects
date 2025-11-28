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
use App\Models\AssociateCommission; 
use App\Models\CorrectionRequests;
use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
use App\Models\SamraddhBank;

/*

    |---------------------------------------------------------------------------

    | Admin Panel -- Associate Management AssociateController

    |--------------------------------------------------------------------------

    |

    | This controller handles associate all functionlity.

*/

class AssociateCommissionController extends Controller
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

    public function index(Request $request)
    {      

        if(check_my_permission( Auth::user()->id,"14") != "1"){
          return redirect()->route('admin.dashboard');
        }

        $data['title']='Commission Calculate | Calculate Total Commission';
       // $a= get_member_id_proofNew(6,5);
     die('hi');
        $data['start_date']='';
        $data['end_date']='';
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

            $mid=Member::where('associate_no', '9999999')->first('id'); 
            //echo $mid->id;die;
            //$midId=Member::where('associate_no','!=', '9999999')->where('id','>',3)->where('id','<=',20)->get(['id']); 
           
           // $data['total_commission']=AssociateCommission::select(DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('member_id','!=',$mid->id)->where('is_distribute',0)->whereBetween(\DB::raw('DATE(created_at)'), [$startDateDb, $endDateDb])->groupBy(DB::raw('member_id'))->get(); 

            $getCurentMont =date("m", strtotime(convertDate($startDateDb)));
                $getCurentYear =date("Y", strtotime(convertDate($endDateDb)));


            $midId=Member::where('associate_no','!=', '9999999')->where('id','>',0)->where('id','<=',200000)->get(['id']);

            $data['total_commission']=AssociateCommission::select(DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('is_add','!=',1)->whereIn('member_id',$midId)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->groupBy(DB::raw('member_id'))->get(); 

//print_r($data['total_commission']);die;

            foreach ($data['total_commission'] as $v) {
               //print_r($v->member_id);die;
              $getCurentMont =date("m", strtotime(convertDate($startDateDb)));
                $getCurentYear =date("Y", strtotime(convertDate($endDateDb)));

                  $count= \App\Models\AssociateCommissionTotal::where('month',$getCurentMont)->where('year',$getCurentYear)->where('member_id',$v->member_id)->count();
                  if($count==0)
                  {

                   
                    $commission=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->pluck('id')->toArray(); 
                    
                    $a=implode( ',',$commission); 
                    

                    $commission1=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('commission_amount');

                     $commission11=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('total_amount'); 


                       
                      $globaldate=$request['created_at']; 
                      $leaser['member_id'] = $v->member_id;
                      $leaser['total_amount'] = $commission11; 
                      $leaser['commission_amount'] = $commission1;              
                      $leaser['month'] = $getCurentMont; 
                      $leaser['year'] = $getCurentYear; 
                      $leaser['total_row'] = count($commission); 
                      $leaser['commission_id'] = $a;
                      $leaser['status'] = 2;
                      $leaser['created_at'] = $globaldate;
                      $leaser['updated_at'] = $globaldate;

                      $leaserCreate = \App\Models\AssociateCommissionTotal::create($leaser);
                      //$comDataUpdate = AssociateCommission::whereIN('id',$commission)->update([ 'status' => 2 ]);
                      $comDataUpdate = AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->update([ 'is_add' => 1 ]);

                }
                else
                {
                	$globaldate=$request['created_at']; 
                    //$ccc=AssociateCommission::where('type','>',2)->where('status',1)->where('is_add',0)->where('member_id',$v->member_id)->where('is_distribute',0)->whereBetween(DB::raw('DATE(created_at)'), [$startDateDb,  $endDateDb])->count();
                    $ccc=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->count();
                    if($ccc>0)
                    {

                      $commission=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->pluck('id')->toArray(); 
                    
                    $a=implode( ',',$commission); 

                    $commission1=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('commission_amount');

                     $commission11=AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->sum('total_amount'); 


                      
                      $leaser['total_amount'] = $commission11; 
                      $leaser['commission_amount'] = $commission1;  
                      $leaser['total_row'] = count($commission); 
                      $leaser['commission_id'] = $a;
                      $leaser['updated_at'] = $globaldate;

                      $leaserCreate = \App\Models\AssociateCommissionTotal::where('month',$getCurentMont)->where('year',$getCurentYear)->where('member_id',$v->member_id)->update($leaser);
                      $comDataUpdate = AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$v->member_id)->where('is_distribute',0)->where(\DB::raw('MONTH(created_at)'),$getCurentMont)->where(\DB::raw('YEAR(created_at)'),$getCurentYear)->update([ 'is_add' => 1 ]);
                    }
                }
            }
                    return redirect('admin/associate-commission-calculate')->with('success', 'Commission calculate sucessfully');
        }
        else
        {
           return view('templates.admin.associate.commission.commission_transfer', $data);
        }
        
       


    }



        public function commissionTransfer(Request $request)

    {

        

        if(check_my_permission( Auth::user()->id,"14") != "1"){

          return redirect()->route('admin.dashboard');

        }

        $data['title']='Commission Transfer | Ledger Create-- old';

        $data['start_date']='';

        $data['end_date']='';





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

           // $mid=Member::where('associate_no', '9999999')->first('id'); 

        //$midId=Member::where('associate_no','!=', '9999999')->where('id','>',1)->where('id','<=',10)->get(['id']); 



           // $data['total_commission']=AssociateCommissionTotal::select(DB::raw('sum(commission_amount) as total'),DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->whereIn('member_id',$midId)->where('is_distribute',0)->whereBetween(\DB::raw('DATE(created_at)'), [$startDateDb, $endDateDb])->groupBy(DB::raw('member_id'))->get(); 

            $getCurentMont =date("m", strtotime(convertDate($startDateDb)));
                $getCurentYear =date("Y", strtotime(convertDate($endDateDb)));

            $data['total_commission']= \App\Models\AssociateCommissionTotal::join('members','members.id','=','associate_commissions_total.member_id')->join('saving_accounts','saving_accounts.member_id','=','associate_commissions_total.member_id')->join('carders','carders.id','=','members.current_carder_id')
            ->where('month',$getCurentMont)
            ->where('year',$getCurentYear)
            ->where('associate_commissions_total.status',2)
            ->where('members.associate_no','!=','321970200026')
            ->get(['associate_commissions_total.id','associate_commissions_total.member_id','associate_commissions_total.commission_amount', 'associate_commissions_total.month','associate_commissions_total.status','associate_commissions_total.year','associate_commissions_total.commission_amount', 'members.id as mid','members.first_name','members.last_name','members.associate_no','members.current_carder_id','saving_accounts.account_no as saccount_no','carders.name as cname']); 

      
      $data['tds_with']=\App\Models\TdsDeposit::where('type',3)->where(\DB::raw('DATE(start_date)'),'<=',$startDateDb)->where(function ($q) use ($startDateDb){
         $q->where(\DB::raw('DATE(end_date)'),'>=',$startDateDb)->orWhereNull('end_date');
     })->first(['tds_amount','tds_per']);
      $data['tds_without']=\App\Models\TdsDeposit::where('type',4)->where(\DB::raw('DATE(start_date)'),'<=',$startDateDb)->where(function ($q) use ($startDateDb){
         $q->where(\DB::raw('DATE(end_date)'),'>=',$startDateDb)->orWhereNull('end_date');
     })->first(['tds_amount','tds_per']);

//print_r($data['tds_without']);die;

            //$data['total_commission']=AssociateCommission::select(DB::raw('sum(commission_amount) as total'),DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('member_id','!=',$mid->id)->where('is_distribute',0)->whereDate('created_at','<=', $endDateDb)->groupBy(DB::raw('member_id'))->get();







        }
     
        return view('templates.admin.associate.commission.commission_create', $data);

    }


   public function commissionLedgerCreate(Request $request)
    {

         Session::put('created_at', $request['created_at']);
         $globaldate=$request['created_at']; 
         $globaldate='2022-12-05 15:00:00';
        DB::beginTransaction();
        try {
            $data=\App\Models\CommissionLeaser::where('status',1)->where('is_deleted',0)->where([['start_date','>',$request->start_date_time],['end_date','<=',$request->end_date_time]])->whereBetween('start_date',array($request->start_date_time,$request->end_date_time))
             ->WhereBetween('end_date',array($request->start_date_time,$request->end_date_time))->get();

             $count=count($data);
             if($count>0)
             {
                return back()->with('error', 'Selected date range already exits');
             }             

            $leaser['start_date'] = $request->start_date_time;
            $leaser['end_date'] = $request->end_date_time;

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

            $leaserCreate = \App\Models\CommissionLeaser::create($leaser);
            $leaserId = $leaserCreate->id;          

            $encodeDate = json_encode($leaser);
            $arrs = array("leaser_id" => $leaserCreate->id, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Associate ledger Create", "data" => $encodeDate);           
             DB::table('user_log')->insert($arrs);            

                if(count($_POST['id'])>0)
                {

                    foreach ($_POST['id'] as $k => $val) 
                    {  

                        $dataComData= \App\Models\AssociateCommissionTotal::join('members','members.id','=','associate_commissions_total.member_id')->where('associate_commissions_total.id',$_POST['id'][$k])->first(['associate_commissions_total.*','members.id as mid','members.first_name','members.last_name','members.associate_no','members.associate_senior_id','members.associate_branch_id']);  
                       // print_r($dataComData)     ;die;   

                       $comDataUpdate = \App\Models\AssociateCommissionTotal::where('id',$_POST['id'][$k])->update([ 'status' => 1 ]); 

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

                        $leaserCreate = \App\Models\CommissionLeaserDetail::create($leaser1);               
                        
                        $CommTrnId=$leaserCreate->id;          

                        $detail='Commission Ledger of  '.date("F Y", strtotime($request->start_date_time));
                        /*--------------------------sms end -------------------------*/

/*****************************Head impliment start ******************************/

                        $payment_mode=3;
                        $payment_type= 'CR';
                        $currency_code='INR';
                        $tdsAmount=$_POST['tds'][$k];
                        $fuleAmount = $_POST['fule'][$k];
                        $commAmount=($_POST['amount'][$k]-$_POST['tds'][$k]);
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
                        $daybookRef=CommanController::createBranchDayBookReferenceNew($amount,$globaldate);             
                        $refId=$daybookRef; 
                        $type_id=$leaserId;    

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
                        $ssb_account_id_to =NULL;
                        $ssb_account_tran_id_to= NULL;
                        $jv_unique_id  = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL ;
                        $amount_to_name=NULL;$amount_from_id=NULL;$amount_from_name=NULL;$amount_to_id=NULL;
                        $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date=NULL;
                        $bank_id=NULL;$bank_ac_id=NULL;
              // commission entry----------------------------
                        $des=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-commission create for '.date("F Y", strtotime($request->start_date_time));
                        $type=2 ;$sub_type=21;
                        //ASSOCIATE CREDITORS
                        
                        $head4ComSsb=141; 

                        $allTranCommSSB=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4ComSsb,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$commAmount,$closing_balance= NULL,$des,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$CommTrnId,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                        //commission head
                        $head1Com=4; $head2Com=86; $head3Com=87; $head4Com=NULL; $head5Com=NULL;

                        $allTranComm=CommanController::headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head3Com,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$_POST['amount'][$k],$closing_balance= NULL,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$CommTrnId,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id); 

                        $aTran['type'] = 1; 
                        $aTran['sub_type'] = 11;
                        $aTran['type_id'] = $type_id;
                        $aTran['type_transaction_id'] = $CommTrnId;

                        $aTran['associate_id'] = $member_id; 
                        $aTran['branch_id'] = $branch_id;
                        $aTran['amount'] = $commAmount;
                        $aTran['pending_amount'] = $commAmount;
                        $aTran['description'] = $des; 
                        $aTran['payment_type'] = 'CR';
                        $aTran['payment_mode'] = $payment_mode;
                        $aTran['currency_code'] = $currency_code;
                        $aTran['v_no'] = $v_no; 
                        $aTran['v_date'] = $v_date;
                        $aTran['entry_date'] = $entry_date;
                        $aTran['entry_time'] = $entry_time;

                        $aTran['created_by'] = $created_by_id; 
                        $aTran['created_by_id'] = $created_by_id;
                        $aTran['payment_status'] = 0; 
                        $aTran['created_at'] = $globaldate;
                        $assoTran = \App\Models\AssociateTransaction::create($aTran); 


    //fule entry--------------------------------
                      if($fuleAmount>0)
                      {
                          $ssb_account_id_to =NULL;
                          $ssb_account_tran_id_to= NULL;


                          $desFule=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-Fuel charge create for '.date("F Y", strtotime($request->start_date_time));
                          $type1=2 ;$sub_type1=25; 
                          //ASSOCIATE CREDITORS  fule Libility 
                          
                          $head4ComSsb1=141; 

                          $allTranCommSSB1=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4ComSsb1,$type1,$sub_type1,$type_id,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$fuleAmount,$closing_balance= NULL,$desFule,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$CommTrnId,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                          //fule head
                          $head1Fule=4;$head2Fule=86;$head3Fule=88;$head4Fule=NULL;$head5Fule=NULL;             

                          $allTranFule=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head3Fule,$type1,$sub_type1,$type_id,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$fuleAmount,$closing_balance= NULL,$desFule,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$CommTrnId,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                        $aTran['type'] = 2; 
                        $aTran['sub_type'] = 21;
                        $aTran['type_id'] = $type_id;
                        $aTran['type_transaction_id'] = $CommTrnId;
                        $aTran['associate_id'] = $member_id; 
                        $aTran['branch_id'] = $branch_id;
                        $aTran['amount'] = $fuleAmount;
                        $aTran['pending_amount'] = $fuleAmount;
                        $aTran['description'] = $desFule; 
                        $aTran['payment_type'] = 'CR';
                        $aTran['payment_mode'] = $payment_mode;
                        $aTran['currency_code'] = $currency_code;
                        $aTran['v_no'] = $v_no; 
                        $aTran['v_date'] = $v_date;
                        $aTran['entry_date'] = $entry_date;
                        $aTran['entry_time'] = $entry_time;
                        $aTran['created_by'] = $created_by_id; 
                        $aTran['created_by_id'] = $created_by_id;
                        $aTran['payment_status'] = 0; 
                        $aTran['created_at'] = $globaldate;
                        $assoTran = \App\Models\AssociateTransaction::create($aTran);

                      }

                      if($tdsAmount>0)
                      {                        
                          
                          $ssb_account_id_to =NULL;
                          $ssb_account_tran_id_to= NULL;

                          $destds=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-TDS deduction for '.date("F Y", strtotime($request->start_date_time));                         

                          $type2=9 ;$sub_type2=90;
                          //tds head 

                          $head1Tds=1;$head2Tds=8;$head3Tds=22;$head4Tds=63;$head5Tds=NULL;
                           $allTranTds=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4Tds,$type2,$sub_type2,$type_id,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$tdsAmount,$closing_balance= NULL,$destds,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$CommTrnId,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);

                           $aTran['type'] = 3; 
                          $aTran['sub_type'] = 31;
                          $aTran['type_id'] = $type_id;
                          $aTran['type_transaction_id'] = $CommTrnId;
                          $aTran['associate_id'] = $member_id; 
                          $aTran['branch_id'] = $branch_id;
                          $aTran['amount'] = $tdsAmount;
                          $aTran['pending_amount'] = $tdsAmount;
                          $aTran['description'] = $destds; 
                          $aTran['payment_type'] = 'CR';
                          $aTran['payment_mode'] = $payment_mode;
                          $aTran['currency_code'] = $currency_code;
                          $aTran['v_no'] = $v_no; 
                          $aTran['v_date'] = $v_date;
                          $aTran['entry_date'] = $entry_date;
                          $aTran['entry_time'] = $entry_time;
                          $aTran['created_by'] = $created_by_id; 
                          $aTran['created_by_id'] = $created_by_id;
                          $aTran['payment_status'] = 0; 
                          $aTran['created_at'] = $globaldate;
                          $assoTran = \App\Models\AssociateTransaction::create($aTran);

                          }
/*****************************Head impliment End ******************************/

                    }
                }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Commission Leadger Create  Successfully');

    }


public function commissionPayment($id){       

        $data['title']='Commission |Payment'; 

        $data['comm']=\App\Models\CommissionLeaserDetail::join('members','members.id','=','commission_leaser_details.member_id')->join('saving_accounts','saving_accounts.member_id','=','commission_leaser_details.member_id')->join('carders','carders.id','=','members.current_carder_id')->where('commission_leaser_id',$id)->where('commission_leaser_details.status',3)->orderby('id','DESC')->get(['commission_leaser_details.*','members.id as mid','members.first_name','members.last_name','members.associate_no','members.associate_senior_id','members.associate_branch_id','members.current_carder_id','saving_accounts.account_no as saccount_no','carders.name as cname']);

        return view('templates.admin.associate.commission.commission_payment', $data);

    }


public function commissionPaymentSave(Request $request)
{

 /// print_r($_POST);

      $select_id_get=rtrim($request->select_id,',');
      $select_id=explode(",",$select_id_get);
      Session::put('created_at', $request['created_at']);
         $globaldate=$request['created_at']; 
         $globaldate='2022-12-05 15:00:00';
        DB::beginTransaction();
        try {
            if(count($select_id)>0)
            {
              foreach($select_id as $val)
              {

               $comDetail=$dataComData =$memDetail= \App\Models\CommissionLeaserDetail::join('members','members.id','=','commission_leaser_details.member_id')->where('commission_leaser_details.id',$val)->first(['commission_leaser_details.*','members.id as mid','members.first_name','members.last_name','members.associate_no','members.associate_senior_id','members.associate_branch_id','members.current_carder_id','members.mobile_no']);

                $ledgerID=$comDetail->commission_leaser_id;
                $ledgerDetailID = $comDetail->id;

                $CommissionLeaserDetail = \App\Models\CommissionLeaserDetail::where('id',$val)->update([ 'status' => 1 ]); 
//print_r($ledgerID);die;

                $associate_id=$comDetail->associate_senior_id;
                        $branch_id=$comDetail->associate_branch_id; 
                $member_id =$comDetail->mid;

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


                        $ledgerDetail= \App\Models\CommissionLeaser::where('id',$ledgerID)->first(['start_date','end_date']);



                 $start_date=date("My", strtotime($ledgerDetail->start_date));
                  $end_date=date("My", strtotime($ledgerDetail->end_date));
                  $sms_date=date("MY", strtotime($ledgerDetail->start_date));

                  $ssbAccountDetail=getMemberSsbAccountDetail($member_id);
                  $detail='Comm '.$start_date;


                    $amounTra=$comDetail->amount_tds;
                    
                    $transactionBydate = \App\Models\SavingAccountTranscation::select('opening_balance')->where('saving_account_id',$ssbAccountDetail->id)->whereDate('created_at','<=',$globaldate)->where('is_deleted',0)->orderby('id','desc')->first();
                    $balanceTra = $transactionBydate->opening_balance;                    

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
                    $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
                    $SSBTRANID=$resSsb->id;

                    $daybookRef=CommanController::createBranchDayBookReferenceNew($amounTra,$globaldate);  
                    
                    $refId=$daybookRef; 
                    $type_id=$ledgerID;

                         
                        $ssb_account_id_to =$ssbAccountDetail->id;
                        $ssb_account_tran_id_to= $SSBTRANID;
                        $jv_unique_id  = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL ;
                        $amount_to_name=NULL;$amount_from_id=NULL;$amount_from_name=NULL;$amount_to_id=NULL;
                        $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date=NULL;
                        $bank_id=NULL;$bank_ac_id=NULL;
              // commission entry----------------------------
                        $des=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-commission paid for '.date("F Y", strtotime($start_date));
                        $type=2 ;$sub_type=21;
                        //ASSOCIATE CREDITORS  -(Minees )
                        
                        $head4ComSsb=141; 

                        $allTranCommSSB=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4ComSsb,$type,$sub_type,$type_id,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$commAmount,$closing_balance= NULL,$des,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$ledgerDetailID,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);


                         $aTran['type'] = 1; 
                        $aTran['sub_type'] = 11;
                        $aTran['type_id'] = $type_id;
                        $aTran['type_transaction_id'] = $ledgerDetailID;

                        $aTran['associate_id'] = $member_id; 
                        $aTran['branch_id'] = $branch_id;
                        $aTran['amount'] = $commAmount;
                        $aTran['pending_amount'] = $commAmount;
                        $aTran['description'] = $des; 
                        $aTran['payment_type'] = 'DR';
                        $aTran['payment_mode'] = $payment_mode;
                        $aTran['currency_code'] = $currency_code;
                        $aTran['v_no'] = $v_no; 
                        $aTran['v_date'] = $v_date;
                        $aTran['entry_date'] = $entry_date;
                        $aTran['entry_time'] = $entry_time;

                        $aTran['created_by'] = $created_by_id; 
                        $aTran['created_by_id'] = $created_by_id;
                        $aTran['payment_status'] = 2; 
                        $aTran['created_at'] = $globaldate;
                        $assoTran = \App\Models\AssociateTransaction::create($aTran); 


                      // SSB Commission Add 

                        $typeS=4 ;$sub_typeS=45;
              //commission ssb head
                  $head1ComSsb=1;$head2ComSsb=8;$head3ComSsb=20;$head4ComSsb=56;$head5ComSsb=NULL;

                  $allTranCommSSB=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4ComSsb,$typeS,$sub_typeS,$ssb_account_id_to,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$amounTra,$closing_balance= NULL,$des,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$ssb_account_tran_id_to,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);
                //commission branch daybook 

              $comDR=$memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.') A/c Dr'.$amounTra.'/-'; 

              $comCR='To SSB('.$ssbAccountDetail->account_no.') A/c Cr '.$amounTra.'/-';

              $daybookComm=CommanController:: NewFieldBranchDaybookCreate($refId,$branch_id,$typeS,$sub_typeS,$ssb_account_id_to,$associate_id,$member_id,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amounTra,$closing_balance=NULL,$des,$comDR,$comCR,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$ssb_account_tran_id_to,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,0,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);  

              //commission member transaction

              $memComDes='Commission transfer for '.date("F Y", strtotime($start_date));
              $memberTranComm=CommanController:: NewFieldAddMemberTransactionCreate($refId,$typeS,$sub_typeS,$ssb_account_id_to,$associate_id,$member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$commAmount,$memComDes,$payment_type,$payment_mode,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$ssb_account_tran_id_to,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


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
                            $dataSsbT['amount'] = $balanceTra; 
                            $dataSsbT['description'] = $detailTds;
                            $dataSsbT['currency_code'] = 'INR';
                            $dataSsbT['payment_type'] = 'DR';
                            $dataSsbT['payment_mode'] = 3; 

                            $globaldateTds=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)) +5 );

                            $dataSsbT['created_at'] = $globaldateTds;
                            $resSsbTds = \App\Models\SavingAccountTranscation::create($dataSsbT);
                            $SSBTRANIDtds=$resSsbTds->id;

                            $ssb_account_id_to =$ssbAccountDetail->id;
                            $ssb_account_tran_id_to= $SSBTRANIDtds;


                            $destds=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-TDS deduction for '.date("F Y", strtotime($start_date)); 

                            if(associateTdsDeductGet($member_id,$start_date)>0)
                            {
                              $tdsdeductSave =1;
                            }
                            else
                            {
                              $dateTdsSaver =getFinacialYear();
                              $tdsSaveD['member_id'] = $member_id; 
                              $tdsSaveD['start_date'] =$dateTdsSaver['dateStart'];
                              $tdsSaveD['end_date'] = $dateTdsSaver['dateEnd']; 
                              $tdsSaveDSave = \App\Models\AssociateTdsDeduct::create($tdsSaveD);

                            }


                            
                              //tds ssb head
                                  $head4ComSsbtds=56;

                                  $allTranCommSSB=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4ComSsbtds,4,415,$ssb_account_id_to,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$tdsAmount,$closing_balance= NULL,$destds,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$ssb_account_tran_id_to,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);



                                                  $aTran['type'] = 3; 
                                                  $aTran['sub_type'] = 31;
                                                  $aTran['type_id'] = $ledgerID;
                                                  $aTran['type_transaction_id'] = $ledgerDetailID;
                                                  $aTran['associate_id'] = $member_id; 
                                                  $aTran['branch_id'] = $branch_id;
                                                  $aTran['amount'] = $tdsAmount;
                                                  $aTran['pending_amount'] = $tdsAmount;
                                                  $aTran['description'] = $destds; 
                                                  $aTran['payment_type'] = 'DR';
                                                  $aTran['payment_mode'] = $payment_mode;
                                                  $aTran['currency_code'] = $currency_code;
                                                  $aTran['v_no'] = $v_no; 
                                                  $aTran['v_date'] = $v_date;
                                                  $aTran['entry_date'] = $entry_date;
                                                  $aTran['entry_time'] = $entry_time;
                                                  $aTran['created_by'] = $created_by_id; 
                                                  $aTran['created_by_id'] = $created_by_id;
                                                  $aTran['payment_status'] = 2; 
                                                  $aTran['created_at'] = $globaldate;
                                                  $assoTran = \App\Models\AssociateTransaction::create($aTran);

                                          $memTdsDes='TDS deduction for '.date("F Y", strtotime($start_date));
                                          $memberTranComm=CommanController:: NewFieldAddMemberTransactionCreate($refId,$type2,$sub_type2,$ledgerID,$associate_id,$member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$tdsAmount,$memTdsDes,$payment_type,$payment_mode,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$ledgerDetailID,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                                          $TDSCR= 'To'.$memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.') A/c CR'.$tdsAmount.'/-'; 
                                          $TDSDR=' SSB('.$ssbAccountDetail->account_no.') A/c DR '.$tdsAmount.'/-';

                                      $daybookTds=CommanController:: NewFieldBranchDaybookCreate($refId,$branch_id,4,415,$ssb_account_id_to,$associate_id,$member_id,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$tdsAmount,$closing_balance=NULL,$des,$TDSDR,$TDSCR,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$ssb_account_tran_id_to,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,0,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);
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
                            $resSsbFuel = \App\Models\SavingAccountTranscation::create($dataSsbF);
                            $SSBTRANIDFule=$resSsbFuel->id;

                            $ssb_account_id_to =$ssbAccountDetail->id;
                            $ssb_account_tran_id_to= $SSBTRANIDFule;



                          $desFule=$dataComData->first_name.' '.$dataComData->last_name.'('.$dataComData->associate_no.')-Fuel charge paid for '.date("F Y", strtotime($start_date));
                          $type11=2 ;$sub_type11=25; 
                          //ASSOCIATE CREDITORS  fule Libility 
                          
                          $head4ComSsb1=141; 

                          $allTranCommSSB1=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4ComSsb1,$type11,$sub_type11,$ledgerID,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$fuleAmount,$closing_balance= NULL,$desFule,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$ledgerDetailID,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);



              $type1=4 ;$sub_type1=46;
              //fule ssb head 
              $head1FuleSsb=1;$head2FuleSsb=8;$head3FuleSsb=20;$head4FuleSsb=56;$head5FuleSsb=NULL;            

              $allTranFuleSSb=CommanController:: headTransactionCreate($refId,$branch_id,$bank_id,$bank_ac_id,$head4FuleSsb,$type1,$sub_type1,$ssb_account_id_to,$associate_id,$member_id,$branch_id_to= NULL,$branch_id_from= NULL,$opening_balance= NULL,$fuleAmount,$closing_balance= NULL,$desFule,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$SSBTRANIDFule,$jv_unique_id,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$cheque_bank_from_id,$cheque_bank_ac_from_id);
              

              //fule branch daybook  

              $fuleDR=$memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.') A/c Dr'.$fuleAmount.'/-'; 
              $fuleCR='To SSB('.$ssbAccountDetail->account_no.') A/c Cr '.$fuleAmount.'/-';
              $daybookFule=CommanController:: NewFieldBranchDaybookCreate($refId,$branch_id,$type1,$sub_type1,$ssb_account_id_to,$associate_id,$member_id,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$fuleAmount,$closing_balance=NULL,$desFule,$fuleDR,$fuleCR,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$SSBTRANIDFule,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,0,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);          

              //fule member transaction
              $memFuleDes='Fule transfer for '.date("F Y", strtotime($request->start_date_time));
              $memberTranFule=CommanController:: NewFieldAddMemberTransactionCreate($refId,$type1,$sub_type1,$ssb_account_id_to,$associate_id,$member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$fuleAmount,$memFuleDes,$payment_type,$payment_mode,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$SSBTRANIDFule,$ssb_account_id_to,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

              $aTran['type'] = 2; 
                        $aTran['sub_type'] = 21;
                        $aTran['type_id'] = $ledgerID;
                        $aTran['type_transaction_id'] = $ledgerDetailID;
                        $aTran['associate_id'] = $member_id; 
                        $aTran['branch_id'] = $branch_id;
                        $aTran['amount'] = $fuleAmount;
                        $aTran['pending_amount'] = $fuleAmount;
                        $aTran['description'] = $desFule; 
                        $aTran['payment_type'] = 'DR';
                        $aTran['payment_mode'] = $payment_mode;
                        $aTran['currency_code'] = $currency_code;
                        $aTran['v_no'] = $v_no; 
                        $aTran['v_date'] = $v_date;
                        $aTran['entry_date'] = $entry_date;
                        $aTran['entry_time'] = $entry_time;
                        $aTran['created_by'] = $created_by_id; 
                        $aTran['created_by_id'] = $created_by_id;
                        $aTran['payment_status'] = 2; 
                        $aTran['created_at'] = $globaldate;
                        $assoTran = \App\Models\AssociateTransaction::create($aTran);







                          // $sms_text_fule='and Monthly Fuel amount '.$sms_date.' has been credited with Rs.'.$fuleAmount;
                          // $sms_text='Your Monthly Commission '.$sms_date.' has been credited with Rs.'.$commAmount.' and Monthly Fuel amount '.$sms_date.' has been credited with Rs.'.$fuleAmount.' in Saving A/c '.$ssbAccountDetail->account_no.' on '.date("d-M-Y").' Thanks. http://www.samraddhbestwin.com';
                          // $templateId = 1207161648340908349;
                        

                        }
                        else
                        {
                          // $sms_text='Your Monthly Commission '.$sms_date.' has been credited with Rs.'.$commAmount.' in Saving A/c '.$ssbAccountDetail->account_no.' on '.date("d-M-Y").' Thanks. http://www.samraddhbestwin.com';
                          // $templateId = 1207161648370549369;
                        }

                        
                        // $contactNumber = array();
                        // $contactNumber[] = $comDetail->mobile_no;                        
                        // $sendToMember = new Sms();
                        // $sendToMember->sendSms( $contactNumber, $sms_text,$templateId );



              }

              $getCount=\App\Models\CommissionLeaserDetail::where('commission_leaser_id',$ledgerID)->where('status','!=',1)->count();
              if($getCount)
              {
                $CommissionLeaserDetail = \App\Models\CommissionLeaser::where('id',$ledgerID)->update([ 'status' => 2 ]); 
              }
              else
              {
                $CommissionLeaserDetail = \App\Models\CommissionLeaser::where('id',$ledgerID)->update([ 'status' => 1 ]); 
              }
              

            }


          DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
return redirect('admin/associate-commissionUpdate/'.$select_id_get.'/'.$ledgerID)->with('success', 'Commission Payment sucessfully');

    //return Redirect::route('admin.associate.CommissionUpdate')->with(['data'=>$select_id]); 


}


public function CommissionUpdate($data,$lid)
{

       $select_id_get=rtrim($data,',');
      $select_id=explode(",",$select_id_get);
      
        
        DB::beginTransaction();
        try {
            if(count($select_id)>0)
            {
              foreach($select_id as $val)
              {

               $comDetail= \App\Models\CommissionLeaserDetail::where('id',$val)->first();

               $commGet=rtrim($comDetail->commission_id,',');
      $com=explode(",",$commGet);

               $comDataUpdate = AssociateCommission::whereIN('id',$com)->update([ 'is_distribute' => 1 ]);

             }
           }


        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
return redirect('admin/associate-commission-ledger-payment/'.$lid)->with('success', 'Commission Payment sucessfully');

}














    /**

     * Show Laser-- commision transfer to ssb account .

     * Route: /associatebusinessreport 

     * Method: get 

     * @return  view

     */

    public function commissionTransferList(Request $request)

    {

        if(check_my_permission( Auth::user()->id,"15") != "1"){

          return redirect()->route('admin.dashboard');

        }

       

        $data['title']='Commission Transfer | Ledger List';

        return view('templates.admin.associate.commission_transfer_list', $data);

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

            

            $data=\App\Models\CommissionLeaser::select('id','start_date','end_date','total_amount','credit_amount','status','created_at','ledger_amount','total_fuel','credit_fuel')->where('is_deleted',0)->orderby('id','DESC')->get();



            return Datatables::of($data)

            ->addIndexColumn()

            ->addColumn('start', function($row){

                $start = date("d/m/Y H:i:s a", strtotime($row->start_date));

                return $start;

            })

            ->rawColumns(['start'])

            ->addColumn('end', function($row){

                $end = date("d/m/Y H:i:s a", strtotime($row->end_date));

                return $end;

            })

            ->rawColumns(['end'])

            ->addColumn('total', function($row){

                $total = $row->total_amount;

                return number_format((float)$total, 2, '.', '');;;

            })

            ->rawColumns(['total'])

            ->addColumn('credit', function($row){

                $credit = $row->credit_amount;
                 return number_format((float)$credit, 2, '.', '');;;
    
                

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



                $url = URL::to("admin/associate-commission-transfer-detail/".$row->id.""); 

                $url1 = URL::to("admin/ledger-delete/".$row->id."");  
                $url2 = URL::to("admin/associate-commission-ledger-payment/".$row->id."");  

                

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

        $data['title']='Commission Transfer | Ledger Detail';

        $data['detail']=\App\Models\CommissionLeaser:: select('id','start_date','end_date')->where('id',$id)->first();

        

        return view('templates.admin.associate.transfer_list_detail', $data);

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

            

            $data=\App\Models\CommissionLeaserDetail::select('id','member_id','amount','status','created_at','total_tds','fuel','collection','amount_tds')->with(['member'=>function($q){
              $q->select('id','member_id','first_name','last_name','current_carder_id','associate_no')->with(['getCarderNameCustom'=>function($q){
                $q->select('id','name');
              },'memberIdProof'=>function($q){
                $q->select('id','first_id_no','member_id');
              }]);
              
            },'SavingAcount'=>function($q){
              $q->select('id','member_id','account_no');
            }])->where('commission_leaser_id',$request->id);
            $count =  $data->count('id');
            $data = $data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount  = $count;
            $sno=$_POST['start'];
            $rowReturn = array(); 
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
                  $val['carder']=$row['member']['getCarderNameCustom']->name; 
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
                if(isset($row->member_id))
                {
                  $val['account']=$row['SavingAcount']->account_no; 
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



                $url = URL::to("admin/associate-commission-detail/".$row->member_id."");  
 
                 $url1 = URL::to("admin/associate/loan-commission-detail/".$row->member_id.""); 
 
                 
 
                 $btn .= '<a class="dropdown-item" href="'.$url.'" title="Investment Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Investment Commission Detail</a>  ';
 
                  $btn .= '<a class="dropdown-item" href="'.$url1.'" title="Loan Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Loan Commission Detail</a>  ';
 
                  
 
                 
 
                 $btn .= '</div></div></div>'; 
                $val['action'] = $btn;
                $rowReturn[] = $val; 

                
            }

            

        }
        $output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
        return json_encode($output);

    }


}

