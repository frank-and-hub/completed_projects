<?php 
namespace App\Http\Controllers\Admin; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Transcation; 
use App\Models\Member;  
use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Account Management AccountImplementController
    |--------------------------------------------------------------------------
    |
    | This controller handles Account all functionlity.
*/
class AccountHeadImplementController extends Controller
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

    

    

    public function member_register_head()
    {
     // $get=member::orderby('id','ASC')->get();
      $get=Member::where('id','>',4500)->where('id','<=',5000)->where('member_id','!=',9999999)->orderby('created_at','ASC')->get(); 
   echo count($get);
   die('done');
  
     

      foreach($get as $val) 
      {

        DB::beginTransaction();
        try { 

        $get_created=Transcation::where('transaction_type',0)->where('amount',10)->where('member_id',$val->id)->first();
        $created_by=NULL;
        $created_by_id=NULL;
        if($get_created)
        {
            $created_by=$get_created->created_by;
            $created_by_id=$get_created->created_by_id;
        }

        $totalAmount=100;
        $entryDate = date("Y-m-d", strtotime(convertDate($val->created_at)));
        $entryTime = date("H:i:s", strtotime(convertDate($val->created_at)));
        $createdAt = $val->created_at;


        $branchRef['amount'] = $totalAmount;
        $branchRef['entry_date'] = $entryDate;
        $branchRef['entry_time'] = $entryTime;
        $branchRef['created_at'] = $createdAt;
        $branchRef['updated_at'] = $createdAt;
        $ref = \App\Models\BranchDaybookReference::create($branchRef);
        $refId = $ref->id;

/******** MI  entry ***************/
        $mi=10;

        $des_mi='Cash received from member '.$val->first_name.' '.$val->last_name.'('.$val->member_id.') through MI charge';
    /******** all_transaction entry ***************/
        $miAllTran['daybook_ref_id'] = $refId;
        $miAllTran['branch_id'] = $val->branch_id;
        $miAllTran['head1'] = 1;
        $miAllTran['head2'] = 8;
        $miAllTran['head3'] = 20;
        $miAllTran['head4'] = 55;
        $miAllTran['type'] =1;
        $miAllTran['sub_type'] = 11;
        $miAllTran['type_id'] = $val->id;
        $miAllTran['associate_id'] = $val->associate_id;
        $miAllTran['member_id'] = $val->id;
        $miAllTran['amount'] = $mi;
        $miAllTran['description'] = $des_mi;
        $miAllTran['payment_type'] = 'CR';
        $miAllTran['payment_mode'] = 0;
        $miAllTran['currency_code'] = 'INR';
        $miAllTran['created_by'] = $created_by;
        $miAllTran['created_by_id'] = $created_by_id;
        $miAllTran['entry_date'] = $entryDate;
        $miAllTran['entry_time'] = $entryTime;
        $miAllTran['created_at'] = $createdAt;  
        $miAllTran['updated_at'] = $createdAt;        
        $miAll = \App\Models\AllTransaction::create($miAllTran);
        $miAllId = $miAll->id;


        $miAllTran2['daybook_ref_id'] = $refId;
        $miAllTran2['branch_id'] = $val->branch_id;
        $miAllTran2['head1'] = 2;
        $miAllTran2['head2'] = 10;
        $miAllTran2['head3'] = 28;
        $miAllTran2['head4'] = 71;
        $miAllTran2['type'] =1;
        $miAllTran2['sub_type'] = 11;
        $miAllTran2['type_id'] = $val->id;
        $miAllTran2['associate_id'] = $val->associate_id;
        $miAllTran2['member_id'] = $val->id;
        $miAllTran2['amount'] = $mi;
        $miAllTran2['description'] = $des_mi;
        $miAllTran2['payment_type'] = 'CR';
        $miAllTran2['payment_mode'] = 0;
        $miAllTran2['currency_code'] = 'INR';
        $miAllTran2['created_by'] = $created_by;
        $miAllTran2['created_by_id'] = $created_by_id;
        $miAllTran2['entry_date'] = $entryDate;
        $miAllTran2['entry_time'] = $entryTime;
        $miAllTran2['created_at'] = $createdAt;  
        $miAllTran2['updated_at'] = $createdAt; 
        $miAll2 = \App\Models\AllTransaction::create($miAllTran2);
        $miAllId2 = $miAll2->id;

    /******** branch_daybook  entry ***************/    


        $miBranchDaybook2['daybook_ref_id'] = $refId;
        $miBranchDaybook2['branch_id'] = $val->branch_id; 
        $miBranchDaybook2['type'] =1;
        $miBranchDaybook2['sub_type'] = 11;
        $miBranchDaybook2['type_id'] = $val->id; 
        $miBranchDaybook2['associate_id'] = $val->associate_id; 
        $miBranchDaybook2['member_id'] = $val->id; 
        $miBranchDaybook2['amount'] = $mi;
        $miBranchDaybook2['description'] = $des_mi;
        $miBranchDaybook2['description_dr'] = 'Cash A/c Dr 10/-';
        $miBranchDaybook2['description_cr'] = 'To '.$val->first_name.' '.$val->last_name.'('.$val->member_id.') A/c Cr 10/-';
        $miBranchDaybook2['payment_type'] = 'CR';
        $miBranchDaybook2['payment_mode'] = 0;
        $miBranchDaybook2['currency_code'] = 'INR';
        $miBranchDaybook2['created_by'] = $created_by;
        $miBranchDaybook2['created_by_id'] = $created_by_id;
        $miBranchDaybook2['entry_date'] = $entryDate;
        $miBranchDaybook2['entry_time'] = $entryTime;
        $miBranchDaybook2['created_at'] = $createdAt; 
        $miBranchDaybook2['updated_at'] = $createdAt; 
        $miBranchDaybook2['is_contra'] = 0; 
        $miBranchDaybook2['contra_id'] =NULL;  
        $miBranchBook2 = \App\Models\BranchDaybook::create($miBranchDaybook2);
        $miBranchBookId2 = $miBranchBook2->id;

        


    /******** member_transaction  entry ***************/ 

        $miMemTran['daybook_ref_id'] = $refId;
        $miMemTran['branch_id'] = $val->branch_id; 
        $miMemTran['type'] =1;
        $miMemTran['sub_type'] = 11;
        $miMemTran['type_id'] = $val->id;
        $miMemTran['member_id'] = $val->id;
        $miMemTran['associate_id'] = $val->associate_id; 
        $miMemTran['amount'] = $mi;
        $miMemTran['description'] = 'MI Charge';
        $miMemTran['payment_type'] = 'CR';
        $miMemTran['payment_mode'] = 0;
        $miMemTran['currency_code'] = 'INR';
        $miMemTran['created_by'] = $created_by;
        $miMemTran['created_by_id'] = $created_by_id;
        $miMemTran['entry_date'] = $entryDate;
        $miMemTran['entry_time'] = $entryTime;
        $miMemTran['created_at'] = $createdAt;
        $miMemTran['updated_at'] = $createdAt;
        $miMember = \App\Models\MemberTransaction::create($miMemTran);
        $miMemberId = $miMember->id;

/******** MI  entry Start ***************/
        
/******** STN  entry Start ***************/
        $stn=90; 
        $des_stn='Cash received from member '.$val->first_name.' '.$val->last_name.'('.$val->member_id.') through STN charge';       
        /******** all_transaction entry ***************/
        $stnAllTran['daybook_ref_id'] = $refId;
        $stnAllTran['branch_id'] = $val->branch_id;
        $stnAllTran['head1'] = 3;
        $stnAllTran['head2'] = 13;
        $stnAllTran['head3'] = 34; 
        $stnAllTran['type'] =1;
        $stnAllTran['sub_type'] = 12;
        $stnAllTran['type_id'] = $val->id;
        $stnAllTran['associate_id'] = $val->associate_id;
        $stnAllTran['member_id'] = $val->id;
        $stnAllTran['amount'] = $stn;
        $stnAllTran['description'] = $des_stn;
        $stnAllTran['payment_type'] = 'CR';
        $stnAllTran['payment_mode'] = 0;
        $stnAllTran['currency_code'] = 'INR';
        $stnAllTran['created_by'] = $created_by;
        $stnAllTran['created_by_id'] = $created_by_id;
        $stnAllTran['entry_date'] = $entryDate;
        $stnAllTran['entry_time'] = $entryTime;
        $stnAllTran['created_at'] = $createdAt;   
        $stnAllTran['updated_at'] = $createdAt;       
        $stnAll = \App\Models\AllTransaction::create($stnAllTran);
        $stnAllId = $stnAll->id;


        $stnAllTran2['daybook_ref_id'] = $refId;
        $stnAllTran2['branch_id'] = $val->branch_id;
        $stnAllTran2['head1'] = 2;
        $stnAllTran2['head2'] = 10;
        $stnAllTran2['head3'] = 28;
        $stnAllTran2['head4'] = 71;
        $stnAllTran2['type'] =1;
        $stnAllTran2['sub_type'] = 12;
        $stnAllTran2['type_id'] = $val->id;
        $stnAllTran2['associate_id'] = $val->associate_id;
        $stnAllTran2['member_id'] = $val->id;
        $stnAllTran2['amount'] = $stn;
        $stnAllTran2['description'] = $des_stn;
        $stnAllTran2['payment_type'] = 'CR';
        $stnAllTran2['payment_mode'] = 0;
        $stnAllTran2['currency_code'] = 'INR';
        $stnAllTran2['created_by'] = $created_by;
        $stnAllTran2['created_by_id'] = $created_by_id;
        $stnAllTran2['entry_date'] = $entryDate;
        $stnAllTran2['entry_time'] = $entryTime;
        $stnAllTran2['created_at'] = $createdAt;  
        $stnAllTran2['updated_at'] = $createdAt;
        $stnAll2 = \App\Models\AllTransaction::create($stnAllTran2);
        $stnAllId2 = $stnAll2->id;

    /******** branch_daybook  entry ***************/       


        $stnBranchDaybook2['daybook_ref_id'] = $refId;
        $stnBranchDaybook2['branch_id'] = $val->branch_id; 
         $stnBranchDaybook2['type'] =1;
        $stnBranchDaybook2['sub_type'] = 12;
        $stnBranchDaybook2['type_id'] = $val->id;
        $stnBranchDaybook2['associate_id'] = $val->associate_id; 
        $stnBranchDaybook2['member_id'] = $val->id; 
        $stnBranchDaybook2['amount'] = $stn;
        $stnBranchDaybook2['description'] = $des_stn;
        $stnBranchDaybook2['description_dr'] = 'Cash A/c Dr 90/-';
        $stnBranchDaybook2['description_cr'] = 'To '.$val->first_name.' '.$val->last_name.'('.$val->member_id.') A/c Cr 90/-';
        $stnBranchDaybook2['payment_type'] = 'CR';
        $stnBranchDaybook2['payment_mode'] = 0;
        $stnBranchDaybook2['currency_code'] = 'INR';
        $stnBranchDaybook2['created_by'] = $created_by;
        $stnBranchDaybook2['created_by_id'] = $created_by_id;
        $stnBranchDaybook2['entry_date'] = $entryDate;
        $stnBranchDaybook2['entry_time'] = $entryTime;
        $stnBranchDaybook2['created_at'] = $createdAt; 
        $stnBranchDaybook2['is_contra'] = 0; 
        $stnBranchDaybook2['contra_id'] = NULL; 
        $stnBranchDaybook2['updated_at'] = $createdAt; 
        $stnBranchBook2 = \App\Models\BranchDaybook::create($stnBranchDaybook2);
        $stnBranchBookId2 = $stnBranchBook2->id;



    /******** member_transaction  entry ***************/ 

        $stnMemTran['daybook_ref_id'] = $refId;
        $stnMemTran['branch_id'] = $val->branch_id; 
        $stnMemTran['type'] =1;
        $stnMemTran['sub_type'] = 12;
        $stnMemTran['type_id'] = $val->id;
        $stnMemTran['member_id'] = $val->id;
        $stnMemTran['associate_id'] = $val->associate_id; 
        $stnMemTran['amount'] = $stn;
        $stnMemTran['description'] = 'STN Charge';
        $stnMemTran['payment_type'] = 'CR';
        $stnMemTran['payment_mode'] = 0;
        $stnMemTran['currency_code'] = 'INR';
        $stnMemTran['created_by'] = $created_by;
        $stnMemTran['created_by_id'] = $created_by_id;
        $stnMemTran['entry_date'] = $entryDate;
        $stnMemTran['entry_time'] = $entryTime;
        $stnMemTran['created_at'] = $createdAt;
        $stnMemTran['updated_at'] = $createdAt;
        $stnMember = \App\Models\MemberTransaction::create($stnMemTran);
        $stnMemberId = $stnMember->id;

/******** STN  entry End ***************/

/******** Balance   entry ***************/
        $branchClosing=CommanController:: checkCreateBranchClosing($val->branch_id,$val->created_at,$totalAmount,0);
        $branchCash=CommanController:: checkCreateBranchCash($val->branch_id,$val->created_at,$totalAmount,0);  


        DB::commit();
  } catch (\Exception $ex) {
    DB::rollback();
    echo $ex->getMessage();
  }     

      }
    echo 'done';

  
}



public function investment_register_daybook_cash()
{
     // $get=member::orderby('id','ASC')->get();
    //  $get=\App\Models\Memberinvestments::where('id','>',0)->where('id','<=',50)->where('plan_id','!=',1)->orderby('created_at','ASC')->get(); 
$pid=1;
      $getDaybook=\App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($pid) { $query->where('member_investments.plan_id','!=',$pid); })->where('id','>',0)->where('id','<=',6000)->where('payment_mode','=',0)->where('description','Like','%opening%')->whereNotIn('transaction_type',array(1))->orderby('created_at','ASC')->get();

      
     // echo 'register cash <br>';
      echo count($getDaybook);
     die('register cash <br>');

     
      if($getDaybook)
      {

        foreach($getDaybook as $daybook) 
        {

        DB::beginTransaction();
        try 
        { 

            $randNumber = mt_rand(0,999999999999999);
                  $member_id = $daybook->member_id;
                    $associate_id = $daybook->associate_id;
                    $branch_id  = $daybook->branch_id;
                    $depositAmount=$daybook->deposit; 
                    $created_by=$daybook->created_by;
                    $created_by_id=$daybook->created_by_id;
                    
            $entryDate = date("Y-m-d", strtotime(convertDate($daybook->created_at)));
          $entryTime = date("H:i:s", strtotime(convertDate($daybook->created_at)));
          $createdAt = $daybook->created_at;

                    $planDetail=getPlanDetail($daybook['investment']->plan_id);                  
                    $type=3; $sub_type=31;    

                $des='Amount received for Account opening '.$planDetail->name.'('.$daybook['investment']->account_number.') through cash('.getBranchCode($branch_id)->branch_code.')'; 

                $rdDesMem='Account opening '.$planDetail->name.'('.$daybook['investment']->account_number.') through cash('.getBranchCode($branch_id)->branch_code.')';  

                  
                  $branchRef['amount'] = $depositAmount;
                        $branchRef['entry_date'] = $entryDate;
                        $branchRef['entry_time'] = $entryTime;
                        $branchRef['created_at'] = $createdAt;
                        $ref = \App\Models\BranchDaybookReference::create($branchRef);
                        $refId = $ref->id;

                     
                        
                  if($daybook->is_eli==0)
                  {
                      $paymentMode =0;
                      
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 2;
                        $allTran2['head2'] = 10;
                        $allTran2['head3'] = 28; 
                        $allTran2['head4'] = 71; 
                        $allTran2['head5'] = NULL; 
                        $allTran2['type'] =$type;
                        $allTran2['sub_type'] = $sub_type;
                        $allTran2['type_id'] = $daybook['investment']->id;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $depositAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'CR';
                        $allTran2['payment_mode'] = $paymentMode; 
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt;  
                        $allTran2['updated_at'] = $createdAt; 
                        $allTran2['type_transaction_id'] = $daybook->id; 
                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;

                        $invBranchDaybook2['daybook_ref_id'] = $refId;
                        $invBranchDaybook2['branch_id'] = $branch_id; 
                        $invBranchDaybook2['type'] =$type;
                        $invBranchDaybook2['sub_type'] = $sub_type;
                        $invBranchDaybook2['type_id'] =$daybook['investment']->id;
                        $invBranchDaybook2['associate_id'] = $associate_id; 
                        $invBranchDaybook2['member_id'] = $member_id; 
                        $invBranchDaybook2['amount'] = $depositAmount;
                        $invBranchDaybook2['description'] = $des;
                        $invBranchDaybook2['description_dr'] = 'Cash A/c Dr '.$depositAmount.'/-';
                        $invBranchDaybook2['description_cr'] = 'To '.$planDetail->name.'('.$daybook['investment']->account_number.')  A/c Cr '.$depositAmount.'/-';
                        $invBranchDaybook2['payment_type'] = 'CR';
                        $invBranchDaybook2['payment_mode'] = $paymentMode;
                        $invBranchDaybook2['currency_code'] = 'INR';
                        $invBranchDaybook2['created_by'] = $created_by;
                        $invBranchDaybook2['created_by_id'] = $created_by_id;
                        $invBranchDaybook2['entry_date'] = $entryDate;
                        $invBranchDaybook2['entry_time'] = $entryTime;
                        $invBranchDaybook2['created_at'] = $createdAt; 
                        $invBranchDaybook2['is_contra'] = 0; 
                        $invBranchDaybook2['contra_id'] = NULL; 
                        $invBranchDaybook2['updated_at'] = $createdAt; 
                        $invBranchDaybook2['type_transaction_id'] = $daybook->id; 
                        $invBranchBook2 = \App\Models\BranchDaybook::create($invBranchDaybook2);
                        $invBranchBookId2 = $invBranchBook2->id;

                        

                        
                        /******** Balance   entry ***************/
                          $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$depositAmount,0);
                          $branchCash=CommanController:: checkCreateBranchCash($branch_id,$createdAt,$depositAmount,0); 
                  }
                  else
                  {
                      $paymentMode =3; 
                      $type=3; $sub_type=30;                       
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 2;
                        $allTran2['head2'] = 10;
                        $allTran2['head3'] = 89; 
                        $allTran2['head4'] = NULL;
                        $allTran2['head5'] = NULL;
                        $allTran2['type'] =$type;
                        $allTran2['sub_type'] = $sub_type;
                        $allTran2['type_id'] = $daybook['investment']->id;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $depositAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'CR';
                        $allTran2['payment_mode'] = $paymentMode;
                        if($paymentMode==3)
                        {
                          $allTran2['v_no'] = $randNumber;
                          $allTran2['v_date'] = $entryDate;
                        }
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt; 
                        $allTran2['updated_at'] = $createdAt; 
                        $allTran2['type_transaction_id'] = $daybook->id; 
                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;

                       $invBranchDaybook2['daybook_ref_id'] = $refId;
                        $invBranchDaybook2['branch_id'] = $branch_id; 
                        $invBranchDaybook2['type'] =$type;
                        $invBranchDaybook2['sub_type'] = $sub_type;
                        $invBranchDaybook2['type_id'] = $daybook['investment']->id;
                        $invBranchDaybook2['associate_id'] = $associate_id; 
                        $invBranchDaybook2['member_id'] = $member_id; 
                        $invBranchDaybook2['amount'] = $depositAmount; 
                        $invBranchDaybook2['description'] = $des;
                        $invBranchDaybook2['description_dr'] = 'Eli Amount Dr '.$depositAmount.'/-';
                        $invBranchDaybook2['description_cr'] = 'To '.$planDetail->name.'('.$daybook['investment']->account_number.')  A/c Cr '.$depositAmount.'/-';

                        $invBranchDaybook2['payment_type'] = 'CR';
                        $invBranchDaybook2['payment_mode'] = $paymentMode;
                        if($paymentMode==3)
                        {
                          $invBranchDaybook2['v_no'] = $randNumber;
                          $invBranchDaybook2['v_date'] = $entryDate;
                        }
                        $invBranchDaybook2['currency_code'] = 'INR';
                        $invBranchDaybook2['created_by'] = $created_by;
                        $invBranchDaybook2['created_by_id'] = $created_by_id;
                        $invBranchDaybook2['entry_date'] = $entryDate;
                        $invBranchDaybook2['entry_time'] = $entryTime;
                        $invBranchDaybook2['created_at'] = $createdAt; 
                        $invBranchDaybook2['is_contra'] = 0; 
                        $invBranchDaybook2['contra_id'] = NULL; 

                        $invBranchDaybook2['updated_at'] = $createdAt; 
                        $invBranchDaybook2['type_transaction_id'] = $daybook->id; 
                        $invBranchBook2 = \App\Models\BranchDaybook::create($invBranchDaybook2);
                        $invBranchBookId2 = $invBranchBook2->id;                       

                        
                        /******** Balance   entry ***************/
                          $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$depositAmount,0);

                  }
                        $planCode=getPlanCode($daybook['investment']->plan_id);
                        if($planCode==709)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=80;
                        }
                        if($planCode==708)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=85;
                        }
                        if($planCode==705)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=57;$head5=79;
                        }
                        if($planCode==707)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=81;
                        }
                        if($planCode==713)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=84;
                        }
                        if($planCode==710)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=58;$head5=NULL;
                        }
                        if($planCode==712)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=57;$head5=78;
                        }
                        if($planCode==706)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=57;$head5=77;
                        }
                        if($planCode==704)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=83;
                        }
                        if($planCode==718)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=82;
                        }
                        $allTran['daybook_ref_id'] = $refId;
                        $allTran['branch_id'] = $branch_id;
                        $allTran['head1'] = $head1;
                        $allTran['head2'] = $head2;
                        $allTran['head3'] = $head3;
                        $allTran['head4'] = $head4;
                        $allTran['head5'] = $head5;
                        $allTran['type'] =$type;
                        $allTran['sub_type'] = $sub_type;
                        $allTran['type_id'] = $daybook['investment']->id;
                        $allTran['associate_id'] = $associate_id;
                        $allTran['member_id'] = $member_id;
                        $allTran['amount'] = $depositAmount;
                        $allTran['description'] = $des;
                        $allTran['payment_type'] = 'CR';
                        $allTran['payment_mode'] = $paymentMode;
                        if($paymentMode==3)
                        {
                          $allTran['v_no'] = $randNumber;
                          $allTran['v_date'] = $entryDate;
                        }
                        $allTran['currency_code'] = 'INR';
                        $allTran['created_by'] = $created_by;
                        $allTran['created_by_id'] = $created_by_id;
                        $allTran['entry_date'] = $entryDate;
                        $allTran['entry_time'] = $entryTime;
                        $allTran['created_at'] = $createdAt;                   
                        
                        $allTran['updated_at'] = $createdAt; 
                        $allTran['type_transaction_id'] = $daybook->id;         
                        $all = \App\Models\AllTransaction::create($allTran);
                        $allId = $all->id;
        /******** member_transaction  entry ***************/ 
                        $invMemTran['daybook_ref_id'] = $refId;
                        $invMemTran['branch_id'] = $branch_id; 
                        $invMemTran['type'] =$type;
                        $invMemTran['sub_type'] = $sub_type;
                        $invMemTran['type_id'] = $daybook['investment']->id;
                        $invMemTran['associate_id'] = $associate_id;
                        $invMemTran['member_id'] = $member_id;
                        $invMemTran['amount'] = $depositAmount;
                        $invMemTran['description'] = $rdDesMem;
                        $invMemTran['payment_type'] = 'CR';
                        $invMemTran['payment_mode'] = $paymentMode;
                        if($paymentMode==3)
                        {
                          $invMemTran['v_no'] = $randNumber;
                          $invMemTran['v_date'] = $entryDate;
                        }
                        $invMemTran['currency_code'] = 'INR';
                        $invMemTran['created_by'] = $created_by;
                        $invMemTran['created_by_id'] = $created_by_id;
                        $invMemTran['entry_date'] = $entryDate;
                        $invMemTran['entry_time'] = $entryTime;
                        $invMemTran['created_at'] = $createdAt; 


                        $invMemTran['updated_at'] = $createdAt; 
                        $invMemTran['type_transaction_id'] = $daybook->id; 

                        $invMember = \App\Models\MemberTransaction::create($invMemTran);
                        $invMemberId = $invMember->id;        
                
             
            DB::commit();
          } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
          }    

      }
    }
    echo 'done';
  
}


public function investment_renew_daybook_cash()
    {
    
$pid=1;
      $getDaybook=\App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($pid) { $query->where('member_investments.plan_id','!=',$pid); })->where('id','>',25133)->where('id','<=',25134)->where('payment_mode','=',0)->where('description','Not Like','%opening%')->whereNotIn('transaction_type',array(1))->orderby('created_at','ASC')->get();

    //  echo 'renew cash<br>';
      echo count($getDaybook);
    
die('start---done');
     
      if($getDaybook)
      {

        foreach($getDaybook as $daybook) 
        {

        DB::beginTransaction();
        try 
        { 

            $randNumber = mt_rand(0,999999999999999);
                  $member_id = $daybook->member_id;
                    $associate_id = $daybook->associate_id;
                    $branch_id  = $daybook->branch_id;
                    $depositAmount=$daybook->deposit; 
                    $created_by=$daybook->created_by;
                    $created_by_id=$daybook->created_by_id;
                    
            $entryDate = date("Y-m-d", strtotime(convertDate($daybook->created_at)));
          $entryTime = date("H:i:s", strtotime(convertDate($daybook->created_at)));
          $createdAt = $daybook->created_at;

                                      
                    $type=3; $sub_type=32;   
                                     
                  
                  $branchRef['amount'] = $depositAmount;
                        $branchRef['entry_date'] = $entryDate;
                        $branchRef['entry_time'] = $entryTime;
                        $branchRef['created_at'] = $createdAt;
                        $ref = \App\Models\BranchDaybookReference::create($branchRef);
                        $refId = $ref->id;

                     $planDetail=getPlanDetail($daybook['investment']->plan_id);
                     $investmentAccountNoRd=$daybook['investment']->account_number;
                     // $des='Cash received from Account renew '.$planDetail->name.'('.$daybook['investment']->account_number.') '; 
        $des='Amount received for '.$planDetail->name.' A/C Renewal ('.$daybook['investment']->account_number.') through cash('.getBranchCode($branch_id)->branch_code.')';   
        $rdDesMem=$planDetail->name.' A/C Renewal ('.$daybook['investment']->account_number.') through cash('.getBranchCode($branch_id)->branch_code.')';   
                  
                      $paymentMode =0;
                      
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 2;
                        $allTran2['head2'] = 10;
                        $allTran2['head3'] = 28; 
                        $allTran2['head4'] = 71; 
                        $allTran2['head5'] = NULL; 
                        $allTran2['type'] =$type;
                        $allTran2['sub_type'] = $sub_type;
                        $allTran2['type_id'] = $daybook['investment']->id;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $depositAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'CR';
                        $allTran2['payment_mode'] = $paymentMode; 
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt; 

                        $allTran2['updated_at'] = $createdAt; 
                        $allTran2['type_transaction_id'] = $daybook->id; 

                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;

                        $invBranchDaybook2['daybook_ref_id'] = $refId;
                        $invBranchDaybook2['branch_id'] = $branch_id; 
                        $invBranchDaybook2['type'] =$type;
                        $invBranchDaybook2['sub_type'] = $sub_type;
                        $invBranchDaybook2['type_id'] = $daybook['investment']->id;
                        $invBranchDaybook2['associate_id'] = $associate_id; 
                        $invBranchDaybook2['member_id'] = $member_id; 
                        $invBranchDaybook2['amount'] = $depositAmount;
                        $invBranchDaybook2['description'] = $des;
                        $invBranchDaybook2['description_dr'] = 'Cash A/c Dr '.$depositAmount.'/-';
                        $invBranchDaybook2['description_cr'] = 'To '.$planDetail->name.'('.$daybook['investment']->account_number.') A/c Cr '.$depositAmount.'/-';
                        $invBranchDaybook2['payment_type'] = 'CR';
                        $invBranchDaybook2['payment_mode'] = $paymentMode;
                        $invBranchDaybook2['currency_code'] = 'INR';
                        $invBranchDaybook2['created_by'] = $created_by;
                        $invBranchDaybook2['created_by_id'] = $created_by_id;
                        $invBranchDaybook2['entry_date'] = $entryDate;
                        $invBranchDaybook2['entry_time'] = $entryTime;
                        $invBranchDaybook2['created_at'] = $createdAt; 
                        $invBranchDaybook2['is_contra'] = 0; 
                        $invBranchDaybook2['contra_id'] = NULL; 

                        $invBranchDaybook2['updated_at'] = $createdAt; 
                        $invBranchDaybook2['type_transaction_id'] = $daybook->id; 
                        $invBranchBook2 = \App\Models\BranchDaybook::create($invBranchDaybook2);
                        $invBranchBookId2 = $invBranchBook2->id;

                        

                        
                        /******** Balance   entry ***************/
                          $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$depositAmount,0);
                          $branchCash=CommanController:: checkCreateBranchCash($branch_id,$createdAt,$depositAmount,0); 
                  

                        $planCode=getPlanCode($daybook['investment']->plan_id);
                        if($planCode==709)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=80;
                        }
                        if($planCode==708)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=85;
                        }
                        if($planCode==705)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=57;$head5=79;
                        }
                        if($planCode==707)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=81;
                        }
                        if($planCode==713)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=84;
                        }
                        if($planCode==710)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=58;$head5=NULL;
                        }
                        if($planCode==712)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=57;$head5=78;
                        }
                        if($planCode==706)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=57;$head5=77;
                        }
                        if($planCode==704)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=83;
                        }
                        if($planCode==718)
                        {
                          $head1=1;$head2=8;$head3=20;$head4=59;$head5=82;
                        }
                        $allTran['daybook_ref_id'] = $refId;
                        $allTran['branch_id'] = $branch_id;
                        $allTran['head1'] = $head1;
                        $allTran['head2'] = $head2;
                        $allTran['head3'] = $head3;
                        $allTran['head4'] = $head4;
                        $allTran['head5'] = $head5;
                        $allTran['type'] =$type;
                        $allTran['sub_type'] = $sub_type;
                        $allTran['type_id'] = $daybook['investment']->id;
                        $allTran['associate_id'] = $associate_id;
                        $allTran['member_id'] = $member_id;
                        $allTran['amount'] = $depositAmount;
                        $allTran['description'] = $des;
                        $allTran['payment_type'] = 'CR';
                        $allTran['payment_mode'] = $paymentMode;
                        if($paymentMode==3)
                        {
                          $allTran['v_no'] = $randNumber;
                          $allTran['v_date'] = $entryDate;
                        }
                        $allTran['currency_code'] = 'INR';
                        $allTran['created_by'] = $created_by;
                        $allTran['created_by_id'] = $created_by_id;
                        $allTran['entry_date'] = $entryDate;
                        $allTran['entry_time'] = $entryTime;
                        $allTran['created_at'] = $createdAt;    


                        $allTran['updated_at'] = $createdAt; 
                        $allTran['type_transaction_id'] = $daybook->id;      
                        $all = \App\Models\AllTransaction::create($allTran);
                        $allId = $all->id;
        /******** member_transaction  entry ***************/ 
                        $invMemTran['daybook_ref_id'] = $refId;
                        $invMemTran['branch_id'] = $branch_id; 
                        $invMemTran['type'] =$type;
                        $invMemTran['sub_type'] = $sub_type;
                        $invMemTran['type_id'] = $daybook['investment']->id;
                        $invMemTran['associate_id'] = $associate_id;
                        $invMemTran['member_id'] = $member_id;
                        $invMemTran['amount'] = $depositAmount;
                        $invMemTran['description'] = $rdDesMem;
                        $invMemTran['payment_type'] = 'CR';
                        $invMemTran['payment_mode'] = $paymentMode;
                        if($paymentMode==3)
                        {
                          $invMemTran['v_no'] = $randNumber;
                          $invMemTran['v_date'] = $entryDate;
                        }
                        $invMemTran['currency_code'] = 'INR';
                        $invMemTran['created_by'] = $created_by;
                        $invMemTran['created_by_id'] = $created_by_id;
                        $invMemTran['entry_date'] = $entryDate;
                        $invMemTran['entry_time'] = $entryTime;
                        $invMemTran['created_at'] = $createdAt;    
                        

                        $invMemTran['updated_at'] = $createdAt; 
                        $invMemTran['type_transaction_id'] = $daybook->id;   
                        $invMember = \App\Models\MemberTransaction::create($invMemTran);
                        $invMemberId = $invMember->id;        
                
             
            DB::commit();
          } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
          }    

      }
    }
    echo 'done';
  
}


//-----

public function investment_renew_daybook_other()
    {
    
$pid=1;
      $getDaybook=\App\Models\Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number');}])->whereHas('investment', function ($query) use ($pid) { $query->where('member_investments.plan_id','!=',$pid); })->where('id','>',41546)->where('id','<=',9000000000)->where('payment_mode','=',4)->where('payment_type','CR')->whereNotIn('transaction_type',array(1))->orderby('created_at','ASC')->get();

//->where('id',3854)
      //->where('description','Not Like','%opening%')
    //  echo 'renew cash<br>';
      echo count($getDaybook);
    
die('start---- done');
     
      if($getDaybook)
      {

        foreach($getDaybook as $daybook) 
        {
            

        DB::beginTransaction();
        try 
        { 

            $randNumber = mt_rand(0,999999999999999);
                  $member_id =$memberId= $daybook->member_id;
                    $associate_id = $daybook->associate_id;
                    $branch_id  = $daybook->branch_id;
                    $depositAmount=$daybook->deposit; 
                    $created_by=$daybook->created_by;
                    $created_by_id=$daybook->created_by_id;
                    $planId=$daybook['investment']->plan_id;
                    $createDayBook=$daybook->id;
                    $cheque_id=$daybook->received_cheque_id;
                    $cheque_date=date("Y-m-d", strtotime(convertDate($daybook->payment_date)));
                    $ssbId=$daybook->saving_account_id;

                    $investmentAccountNoRd=$daybook['investment']->account_number;
                    
            $amount =$daybook->deposit;
            $globaldate = $daybook->created_at;
        $daybookRefRD=CommanController::createBranchDayBookReferenceNew($amount,$globaldate);
        $refIdRD=$daybookRefRD;
        $currency_code='INR'; 
        $payment_type_rd='CR';
        $payment_mode=$daybook->payment_mode;
        
        $type_id=$daybook['investment']->id;   

        $entry_date=date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time=date("H:i:s", strtotime(convertDate($globaldate))); 
        $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));    
        
        $planDetail=getPlanDetail($planId);                  
        $type=3; $sub_type=32;  
    
        $planCode=$planDetail->plan_code; ;
        if($planCode==703)
            {
                $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=56;$head5Invest=NULL;
            }

            if($planCode==709)
            {
                $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=80;
            }
                        if($planCode==708)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=85;
                        }
                        if($planCode==705)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=57;$head5Invest=79;
                        }
                        if($planCode==707)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=81;
                        }
                        if($planCode==713)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=84;
                        }
                        if($planCode==710)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=58;$head5Invest=NULL;
                        }
                        if($planCode==712)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=57;$head5Invest=78;
                        }
                        if($planCode==706)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=57;$head5Invest=77;
                        }
                        if($planCode==704)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=83;
                        }
                        if($planCode==718)
                        {
                          $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=82;
                        }


    

            $v_no = NULL;  $v_date = NULL;   $ssb_account_id_from = NULL;  
            
            $cheque_no = NULL;  $cheque_date = NULL;  $cheque_bank_from = NULL; $cheque_bank_ac_from = NULL;
            $cheque_bank_ifsc_from = NULL;  $cheque_bank_branch_from = NULL;  $cheque_bank_to = NULL;  
            $cheque_bank_ac_to = NULL;  
            
            $transction_no = NULL;  $transction_bank_from = NULL;  $transction_bank_ac_from = NULL;  
            $transction_bank_ifsc_from = NULL;  $transction_bank_branch_from = NULL;  
            $transction_bank_to = NULL;  $transction_bank_ac_to = NULL;  $transction_date = NULL; 


//echo $daybook->received_cheque_id;
    if($payment_mode==1)
    {  // cheque moade 
        $headPaymentModeRD=1;
        $chequeDetail = \App\Models\ReceivedCheque::where('id',$cheque_id)->first();
      //  print_r($chequeDetail);die;

        $cheque_no = $chequeDetail->cheque_no;  
        $cheque_date = $cheque_date;  
        $cheque_bank_from = $chequeDetail->bank_name; 
        $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
        $cheque_bank_ifsc_from = NULL; 
        $cheque_bank_branch_from = $chequeDetail->branch_name;  
        $cheque_bank_to = $chequeDetail->deposit_bank_id;  
        $cheque_bank_ac_to = $chequeDetail->deposit_account_id ;

        $receivedPayment['type']=2;
                  $receivedPayment['branch_id']=$branch_id;
                  $receivedPayment['investment_id']=$type_id;
                  $receivedPayment['day_book_id']=$createDayBook;
                  $receivedPayment['cheque_id']=$cheque_id; 
                  $receivedPayment['created_at'] = $created_at;
                  $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);

                  $dataRC['status']=3;
                  $receivedcheque = \App\Models\ReceivedCheque::find($cheque_id);
                  $receivedcheque->update($dataRC);



        $getBankHead=\App\Models\SamraddhBank::where('id',$cheque_bank_to)->first(); 
         
        $head11 = 2; $head21 =10 ; $head31 =27 ; $head41 =$getBankHead->account_head_id; $head51 =NULL;

        $rdDesDR = 'Bank A/c Dr '.$amount.'/-';
        $rdDesCR  = 'To '.$planDetail->name.'('.$investmentAccountNoRd.')  A/c Cr '.$amount.'/-';
        $rdDes='Amount received for '.$planDetail->name.' A/C Renewal ('.$investmentAccountNoRd.') through cheque('.$cheque_no.')'; 
        $rdDesMem=$planDetail->name.' A/C Renewal ('.$investmentAccountNoRd.') through cheque('.$cheque_no.')'; 


         //bank head entry
        $allTranRDcheque=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);

        //bank entry
        $bankCheque=CommanController::createSamraddhBankDaybookNew($refIdRD,$cheque_bank_to,$cheque_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);
        //bank balence
        $bankClosing=CommanController:: checkCreateBankClosing($cheque_bank_to,$cheque_bank_ac_to,$created_at,$amount,0); 

    }elseif($payment_mode==4)
    {// ssb
        $headPaymentModeRD=3; 

        $v_no = mt_rand(0,999999999999999);  $v_date = $entry_date;   
        $ssb_account_id_from = $ssbId; 
        $SSBDescTran='Amount transfer to '.$planDetail->name.'('.$investmentAccountNoRd.') ';
        $head1rdSSB =1 ; $head2rdSSB =8 ; $head3rdSSB =20 ; $head4rdSSB =56 ; $head5rdSSB =NULL ; 

        $ssbDetals=\App\Models\SavingAccount::where('id',$ssb_account_id_from)->first();

        $rdDesDR = $planDetail->name.'('.$investmentAccountNoRd.') A/c Dr '.$amount.'/-';
        $rdDesCR  = 'To SSB('.$ssbDetals->account_no.') A/c Cr '.$amount.'/-';
        $rdDes='Amount received for '.$planDetail->name.' A/C Renewal ('.$investmentAccountNoRd.') through SSB('.$ssbDetals->account_no.')'; 
        $rdDesMem=$planDetail->name.' A/C Renewal ('.$investmentAccountNoRd.') online through SSB('.$ssbDetals->account_no.')'; 

        // ssb  head entry -
        $allTranRDSSB=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);

        $branchClosingSSB=CommanController:: checkCreateBranchClosingDr($branch_id,$created_at,$amount,0);

        $memberTranInvest77 = CommanController::createMemberTransactionNew($refIdRD,'4','47',$ssb_account_id_from,$associate_id,$ssbDetals->member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$SSBDescTran,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=$type_id,$amount_to_name=$planDetail->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);


        
    }

    
        //branch day book entry +
    $daybookInvest = CommanController::createBranchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$createDayBook);
        // Investment head entry +
    $allTranInvest = CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);
        // Member transaction  +
    $memberTranInvest = CommanController::createMemberTransactionNew($refIdRD,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);
        /******** Balance   entry ***************/
        $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$created_at,$amount,0);

                                      
                    
                      
                               
                
             
            DB::commit();
          } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
          }    

      }
    }
    echo 'done';
  
}


public function ssb_register_cash()
{
      $getDaybook=\App\Models\SavingAccountTranscation::where('id','>',0)->where('id','<=',100000)->where('payment_mode','=',0)->where('type',1)->orderby('created_at','ASC')->get();

    //  echo 'ssb register cash<br>';
      echo count($getDaybook);

   die('ssb register cash -- done');

     
      if($getDaybook)
      {

        foreach($getDaybook as $daybook) 
        {

        DB::beginTransaction();
        try 
        { 
          $ssbdetail = \App\Models\SavingAccount::where('id',$daybook->saving_account_id)->first();
          $investmentData = \App\Models\Memberinvestments::where('id',$ssbdetail->member_investments_id)->first();

            
                    $member_id = $ssbdetail->member_id;
                    $associate_id = $daybook->associate_id;
                    $branch_id  = $daybook->branch_id;
                    $ssbId = $ssbdetail->id;
                    $paymentMode=0;

                    $depositAmount=$daybook->deposit; 
                    $created_by=$ssbdetail->created_by;
                    $created_by_id=$ssbdetail->created_by_id;
                    
          $entryDate = date("Y-m-d", strtotime(convertDate($daybook->created_at)));
          $entryTime = date("H:i:s", strtotime(convertDate($daybook->created_at)));
          $createdAt = $daybook->created_at;

                                      
        $type=4; $sub_type=41;   
        $des='Amount received for SSB A/C Deposit ('.$ssbdetail->account_no.') through cash('.getBranchCode($branch_id)->branch_code.')';   
        
        $rdDesMem='SSB A/C Deposit ('.$ssbdetail->account_no.') through cash('.getBranchCode($branch_id)->branch_code.')';                    
                  
                        $branchRef['amount'] = $depositAmount;
                        $branchRef['entry_date'] = $entryDate;
                        $branchRef['entry_time'] = $entryTime;
                        $branchRef['created_at'] = $createdAt;
                        $ref = \App\Models\BranchDaybookReference::create($branchRef);
                        $refId = $ref->id;                                      
                  
                      
                    /************ head entry*************/
                        $allTran['daybook_ref_id'] = $refId;
                        $allTran['branch_id'] = $branch_id;
                        $allTran['head1'] = 1;
                        $allTran['head2'] = 8;
                        $allTran['head3'] = 20;
                        $allTran['head4'] = 56;
                        $allTran['head5'] = Null;
                        $allTran['type'] =$type;
                        $allTran['sub_type'] = $sub_type;
                        $allTran['type_id'] = $ssbId;
                        $allTran['associate_id'] = $associate_id;
                        $allTran['member_id'] = $member_id;
                        $allTran['amount'] = $depositAmount;
                        $allTran['description'] = $des;
                        $allTran['payment_type'] = 'CR';
                        $allTran['payment_mode'] = $paymentMode; 
                        $allTran['currency_code'] = 'INR';
                        $allTran['created_by'] = $created_by;
                        $allTran['created_by_id'] = $created_by_id;
                        $allTran['entry_date'] = $entryDate;
                        $allTran['entry_time'] = $entryTime;
                        $allTran['created_at'] = $createdAt;   
                        
                        $allTran['updated_at'] = $createdAt; 
                        $allTran['type_transaction_id'] = $daybook->id;        
                        $all = \App\Models\AllTransaction::create($allTran);
                        $allId = $all->id;
                      
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 2;
                        $allTran2['head2'] = 10;
                        $allTran2['head3'] = 28; 
                        $allTran2['head4'] = 71;
                        $allTran2['head5'] = NULL;  
                        $allTran2['type'] =$type;
                        $allTran2['sub_type'] = $sub_type;
                        $allTran2['type_id'] = $ssbId;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $depositAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'CR';
                        $allTran2['payment_mode'] = $paymentMode; 
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt; 

                        $allTran2['updated_at'] = $createdAt; 
                        $allTran2['type_transaction_id'] = $daybook->id;  
                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;
/************ double  entry*************/
                        

                        $invBranchDaybook['daybook_ref_id'] = $refId;
                        $invBranchDaybook['branch_id'] = $branch_id; 
                        $invBranchDaybook['type'] =$type;
                        $invBranchDaybook['sub_type'] = $sub_type;
                        $invBranchDaybook['type_id'] = $ssbId;
                        $invBranchDaybook['associate_id'] = $associate_id; 
                        $invBranchDaybook['member_id'] = $member_id; 
                        $invBranchDaybook['amount'] = $depositAmount;
                        $invBranchDaybook['description'] = $des;
                        $invBranchDaybook['description_dr'] = 'Cash A/c Dr '.$depositAmount.'/-';
                        $invBranchDaybook['description_cr'] = 'To SSB ('.$ssbdetail->account_no.') A/c Cr '.$depositAmount.'/-';;
                        $invBranchDaybook['payment_type'] = 'CR';
                        $invBranchDaybook['payment_mode'] =$paymentMode;
                        $invBranchDaybook['currency_code'] = 'INR';
                        $invBranchDaybook['created_by'] = $created_by;
                        $invBranchDaybook['created_by_id'] = $created_by_id;
                        $invBranchDaybook['entry_date'] = $entryDate;
                        $invBranchDaybook['entry_time'] = $entryTime;
                        $invBranchDaybook['created_at'] = $createdAt; 
                          
                        $invBranchDaybook['updated_at'] = $createdAt; 
                        $invBranchDaybook['type_transaction_id'] = $daybook->id; 
                        $invBranchBook = \App\Models\BranchDaybook::create($invBranchDaybook);
                        $invBranchBookId = $invBranchBook->id; 

        /******** member_transaction  entry ***************/ 
                        $invMemTran['daybook_ref_id'] = $refId;
                        $invMemTran['branch_id'] = $branch_id; 
                        $invMemTran['type'] =$type;
                        $invMemTran['sub_type'] = $sub_type;
                        $invMemTran['type_id'] = $ssbId;
                        $invMemTran['associate_id'] = $associate_id;
                        $invMemTran['member_id'] = $member_id;
                        $invMemTran['amount'] = $depositAmount;
                        $invMemTran['description'] = $rdDesMem;
                        $invMemTran['payment_type'] = 'CR';
                        $invMemTran['payment_mode'] = $paymentMode; 
                        $invMemTran['currency_code'] = 'INR';
                        $invMemTran['created_by'] = $created_by;
                        $invMemTran['created_by_id'] = $created_by_id;
                        $invMemTran['entry_date'] = $entryDate;
                        $invMemTran['entry_time'] = $entryTime;
                        $invMemTran['created_at'] = $createdAt; 
                          
                        $invMemTran['updated_at'] = $createdAt; 
                        $invMemTran['type_transaction_id'] = $daybook->id; 
                        $invMember = \App\Models\MemberTransaction::create($invMemTran);
                        $invMemberId = $invMember->id;  
          /******** Balance   entry ***************/
                  $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$depositAmount,0);
                  $branchCash=CommanController:: checkCreateBranchCash($branch_id,$createdAt,$depositAmount,0);      
                
             echo $daybook->saving_account_id.'<br>';
            DB::commit();
          } catch (\Exception $ex) {
            DB::rollback();
          
            echo $ex->getMessage();
          }
      }
    }
    echo 'done';
  
}

public function ssb_deposit_cash()
    { 

      $getDaybook=\App\Models\SavingAccountTranscation::where('id','>',0)->where('id','<=',100000)->where('payment_mode','=',0)->where('type',2)->orderby('created_at','ASC')->get();

    //  echo 'ssb register cash<br>';
      echo count($getDaybook);
     die('ssb deposit cash<br>');

     
      if($getDaybook)
      {

        foreach($getDaybook as $daybook) 
        {

        DB::beginTransaction();
        try 
        { 
          $ssbdetail = \App\Models\SavingAccount::where('id',$daybook->saving_account_id)->first();
          $investmentData =\App\Models\Daybook::where('account_no',$ssbdetail->account_no)->first();

            
                    $member_id = $ssbdetail->member_id;
                    $associate_id = $daybook->associate_id;
                    $branch_id  = $daybook->branch_id;
                    $ssbId = $ssbdetail->id;
                    $paymentMode=0;

                    $depositAmount=$daybook->deposit; 
                    $created_by=$investmentData->created_by;
                    $created_by_id=$investmentData->created_by_id;
                    
          $entryDate = date("Y-m-d", strtotime(convertDate($daybook->created_at)));
          $entryTime = date("H:i:s", strtotime(convertDate($daybook->created_at)));
          $createdAt = $daybook->created_at;

                                      
                    $type=4; $sub_type=42;   
                   $des='Amount received for SSB A/C Deposit ('.$ssbdetail->account_no.') through cash('.getBranchCode($branch_id)->branch_code.')';   
        $rdDesMem='SSB A/C Deposit ('.$ssbdetail->account_no.') through cash('.getBranchCode($branch_id)->branch_code.')';                   
                  
                        $branchRef['amount'] = $depositAmount;
                        $branchRef['entry_date'] = $entryDate;
                        $branchRef['entry_time'] = $entryTime;
                        $branchRef['created_at'] = $createdAt;
                        $ref = \App\Models\BranchDaybookReference::create($branchRef);
                        $refId = $ref->id;                                      
                  
                      
                    /************ head entry*************/
                        $allTran['daybook_ref_id'] = $refId;
                        $allTran['branch_id'] = $branch_id;
                        $allTran['head1'] = 1;
                        $allTran['head2'] = 8;
                        $allTran['head3'] = 20;
                        $allTran['head4'] = 56;
                        $allTran['head5'] = Null;
                        $allTran['type'] =$type;
                        $allTran['sub_type'] = $sub_type;
                        $allTran['type_id'] = $ssbId;
                        $allTran['associate_id'] = $associate_id;
                        $allTran['member_id'] = $member_id;
                        $allTran['amount'] = $depositAmount;
                        $allTran['description'] = $des;
                        $allTran['payment_type'] = 'CR';
                        $allTran['payment_mode'] = $paymentMode; 
                        $allTran['currency_code'] = 'INR';
                        $allTran['created_by'] = $created_by;
                        $allTran['created_by_id'] = $created_by_id;
                        $allTran['entry_date'] = $entryDate;
                        $allTran['entry_time'] = $entryTime;
                        $allTran['created_at'] = $createdAt;         
                        $all = \App\Models\AllTransaction::create($allTran);
                        $allId = $all->id;
                      
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 2;
                        $allTran2['head2'] = 10;
                        $allTran2['head3'] = 28; 
                        $allTran2['head4'] = 71;
                        $allTran2['head5'] = NULL;  
                        $allTran2['type'] =$type;
                        $allTran2['sub_type'] = $sub_type;
                        $allTran2['type_id'] = $ssbId;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $depositAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'CR';
                        $allTran2['payment_mode'] = $paymentMode; 
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt;  
                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;
/************ double  entry*************/
                        

                        $invBranchDaybook['daybook_ref_id'] = $refId;
                        $invBranchDaybook['branch_id'] = $branch_id; 
                        $invBranchDaybook['type'] =$type;
                        $invBranchDaybook['sub_type'] = $sub_type;
                        $invBranchDaybook['type_id'] = $ssbId;
                        $invBranchDaybook['associate_id'] = $associate_id; 
                        $invBranchDaybook['member_id'] = $member_id; 
                        $invBranchDaybook['amount'] = $depositAmount;
                        $invBranchDaybook['description'] = $des;
                        $invBranchDaybook['description_dr'] = 'Cash A/c Dr '.$depositAmount.'/-';
                        $invBranchDaybook['description_cr'] = 'To SSB ('.$ssbdetail->account_no.') A/c Cr '.$depositAmount.'/-';;
                        $invBranchDaybook['payment_type'] = 'CR';
                        $invBranchDaybook['payment_mode'] =$paymentMode;
                        $invBranchDaybook['currency_code'] = 'INR';
                        $invBranchDaybook['created_by'] = $created_by;
                        $invBranchDaybook['created_by_id'] = $created_by_id;
                        $invBranchDaybook['entry_date'] = $entryDate;
                        $invBranchDaybook['entry_time'] = $entryTime;
                        $invBranchDaybook['created_at'] = $createdAt; 
                        $invBranchBook = \App\Models\BranchDaybook::create($invBranchDaybook);
                        $invBranchBookId = $invBranchBook->id; 

        /******** member_transaction  entry ***************/ 
                        $invMemTran['daybook_ref_id'] = $refId;
                        $invMemTran['branch_id'] = $branch_id; 
                        $invMemTran['type'] =$type;
                        $invMemTran['sub_type'] = $sub_type;
                        $invMemTran['type_id'] = $ssbId;
                        $invMemTran['associate_id'] = $associate_id;
                        $invMemTran['member_id'] = $member_id;
                        $invMemTran['amount'] = $depositAmount;
                        $invMemTran['description'] = $rdDesMem;
                        $invMemTran['payment_type'] = 'CR';
                        $invMemTran['payment_mode'] = $paymentMode; 
                        $invMemTran['currency_code'] = 'INR';
                        $invMemTran['created_by'] = $created_by;
                        $invMemTran['created_by_id'] = $created_by_id;
                        $invMemTran['entry_date'] = $entryDate;
                        $invMemTran['entry_time'] = $entryTime;
                        $invMemTran['created_at'] = $createdAt; 
                        $invMember = \App\Models\MemberTransaction::create($invMemTran);
                        $invMemberId = $invMember->id;  
          /******** Balance   entry ***************/
                  $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$depositAmount,0);
                  $branchCash=CommanController:: checkCreateBranchCash($branch_id,$createdAt,$depositAmount,0);      
                
             
            DB::commit();
          } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
          }    

      }
    }
    echo 'done';
  
}


public function associate_commission_gv_ssb()
  { 

    $com=\App\Models\CommissionLeaserDetail::where('id','>',0)->where('id','<=',10000)->orderby('created_at','ASC')->get();

    //  echo 'ssb register cash<br>';
      echo count($com);
   // die('ssb commission cash<br>');

     
    if($com)
    {

      foreach($com as $val) 
      {

        DB::beginTransaction();
        try 
        { 
          $member_id=$val->member_id;
          $comAmount=$val->amount;
          $tdsAmount=$val->total_tds;
          $fuleAmount=$val->fuel;
          $ssbdetail = \App\Models\SavingAccount::where('member_id',$val->member_id)->first();
          $memDetail = \App\Models\Member::where('id',$val->member_id)->first();
          $associate_id=$ssbdetail->associate_id;
          $branch_id=$ssbdetail->branch_id;
          $legerDetail = \App\Models\CommissionLeaser::where('id',$val->commission_leaser_id)->first();
          $ssbId=$ssbdetail->id;
          
          $paymentMode=3;

          $des=$memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.')-commission paid for '.date("F Y", strtotime($legerDetail->start_date));
          $desFule=$memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.')-Fuel charge paid for '.date("F Y", strtotime($legerDetail->start_date));

          $destds=$memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.')-TDS deduction for '.date("F Y", strtotime($legerDetail->start_date));                  
                  $amount=$comAmount+$fuleAmount+$tdsAmount;

          $entryDate = date("Y-m-d", strtotime(convertDate($val->created_at)));
          $entryTime = date("H:i:s", strtotime(convertDate($val->created_at)));
          $createdAt = $val->created_at;
          $created_by=1;
          $created_by_id=1;

                        $branchRef['amount'] = $amount;
                        $branchRef['entry_date'] = $entryDate;
                        $branchRef['entry_time'] = $entryTime;
                        $branchRef['created_at'] = $createdAt;
                        $ref = \App\Models\BranchDaybookReference::create($branchRef);
                        $refId = $ref->id;                                      
                  
                      
                    /************ head entry*************/
                    //commission ssb
                        $allTran['daybook_ref_id'] = $refId;
                        $allTran['branch_id'] = $branch_id;
                        $allTran['head1'] = 1;
                        $allTran['head2'] = 8;
                        $allTran['head3'] = 20;
                        $allTran['head4'] = 56;
                        $allTran['head5'] = Null;
                        $allTran['type'] =4;
                        $allTran['sub_type'] = 45;
                        $allTran['type_id'] = $ssbId;
                        $allTran['associate_id'] = $associate_id;
                        $allTran['member_id'] = $member_id;
                        $allTran['amount'] = $comAmount;
                        $allTran['description'] = $des;
                        $allTran['payment_type'] = 'CR';
                        $allTran['payment_mode'] = $paymentMode; 
                        $allTran['currency_code'] = 'INR';
                        $allTran['created_by'] = $created_by;
                        $allTran['created_by_id'] = $created_by_id;
                        $allTran['entry_date'] = $entryDate;
                        $allTran['entry_time'] = $entryTime;
                        $allTran['created_at'] = $createdAt;    


                        $all = \App\Models\AllTransaction::create($allTran);
                        $allId = $all->id;
                    //commission  
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 4;
                        $allTran2['head2'] = 86;
                        $allTran2['head3'] = 87; 
                        $allTran2['head4'] = NULL;
                        $allTran2['head5'] = NULL;  
                        $allTran2['type'] =4;
                        $allTran2['sub_type'] = 45;
                        $allTran2['type_id'] = $ssbId;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $comAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'CR';
                        $allTran2['payment_mode'] = $paymentMode; 
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt;  
                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;

                        //fule ssb
                    if($fuleAmount>0)
                    {
                        $allTran3['daybook_ref_id'] = $refId;
                        $allTran3['branch_id'] = $branch_id;
                        $allTran3['head1'] = 1;
                        $allTran3['head2'] = 8;
                        $allTran3['head3'] = 20; 
                        $allTran3['head4'] = 56;
                        $allTran3['head5'] = NULL;  
                        $allTran3['type'] =4;
                        $allTran3['sub_type'] = 46;
                        $allTran3['type_id'] = $ssbId;
                        $allTran3['associate_id'] = $associate_id;
                        $allTran3['member_id'] = $member_id;
                        $allTran3['amount'] = $fuleAmount;
                        $allTran3['description'] = $desFule;
                        $allTran3['payment_type'] = 'CR';
                        $allTran3['payment_mode'] = $paymentMode; 
                        $allTran3['currency_code'] = 'INR';
                        $allTran3['created_by'] = $created_by;
                        $allTran3['created_by_id'] = $created_by_id;
                        $allTran3['entry_date'] = $entryDate;
                        $allTran3['entry_time'] = $entryTime;
                        $allTran3['created_at'] = $createdAt;  
                        $all3 = \App\Models\AllTransaction::create($allTran3);
                        $allId3 = $all3->id;
                      //fule head
                        $allTran5['daybook_ref_id'] = $refId;
                        $allTran5['branch_id'] = $branch_id;
                        $allTran5['head1'] = 4;
                        $allTran5['head2'] = 86;
                        $allTran5['head3'] = 88; 
                        $allTran5['head4'] = NULL;
                        $allTran5['head5'] = NULL;  
                        $allTran5['type'] =4;
                        $allTran5['sub_type'] = 46;
                        $allTran5['type_id'] = $ssbId;
                        $allTran5['associate_id'] = $associate_id;
                        $allTran5['member_id'] = $member_id;
                        $allTran5['amount'] = $fuleAmount;
                        $allTran5['description'] = $desFule;
                        $allTran5['payment_type'] = 'CR';
                        $allTran5['payment_mode'] = $paymentMode; 
                        $allTran5['currency_code'] = 'INR';
                        $allTran5['created_by'] = $created_by;
                        $allTran5['created_by_id'] = $created_by_id;
                        $allTran5['entry_date'] = $entryDate;
                        $allTran5['entry_time'] = $entryTime;
                        $allTran5['created_at'] = $createdAt;  
                        $all5 = \App\Models\AllTransaction::create($allTran5);
                        $allId5 = $all5->id;
                    }


                        //tds head 
                    if($tdsAmount>0)
                    {
                        $allTran4['daybook_ref_id'] = $refId;
                        $allTran4['branch_id'] = $branch_id;
                        $allTran4['head1'] = 1;
                        $allTran4['head2'] = 8;
                        $allTran4['head3'] = 22; 
                        $allTran4['head4'] = 63;
                        $allTran4['head5'] = NULL;  
                        $allTran4['type'] =9;
                        $allTran4['sub_type'] = 90;
                        $allTran4['type_id'] = $ssbId;
                        $allTran4['associate_id'] = $associate_id;
                        $allTran4['member_id'] = $member_id;
                        $allTran4['amount'] = $tdsAmount;
                        $allTran4['description'] = $destds;
                        $allTran4['payment_type'] = 'CR';
                        $allTran4['payment_mode'] = $paymentMode; 
                        $allTran4['currency_code'] = 'INR';
                        $allTran4['created_by'] = $created_by;
                        $allTran4['created_by_id'] = $created_by_id;
                        $allTran4['entry_date'] = $entryDate;
                        $allTran4['entry_time'] = $entryTime;
                        $allTran4['created_at'] = $createdAt;  
                        $all4 = \App\Models\AllTransaction::create($allTran4);
                        $allId4 = $all4->id;

                    }
/************ double  entry*************/
                        

                        $invBranchDaybook['daybook_ref_id'] = $refId;
                        $invBranchDaybook['branch_id'] = $branch_id; 
                        $invBranchDaybook['type'] =4;
                        $invBranchDaybook['sub_type'] = 45;
                        $invBranchDaybook['type_id'] = $ssbId;
                        $invBranchDaybook['associate_id'] = $associate_id; 
                        $invBranchDaybook['member_id'] = $member_id; 
                        $invBranchDaybook['amount'] = $comAmount;
                        $invBranchDaybook['description'] = $des;
                        $invBranchDaybook['description_dr'] = $memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.') A/c Dr'.$comAmount.'/-';
                        $invBranchDaybook['description_cr'] = 'To SSB('.$ssbdetail->account_no.') A/c Cr '.$comAmount.'/-';;
                        $invBranchDaybook['payment_type'] = 'CR';
                        $invBranchDaybook['payment_mode'] =$paymentMode;
                        $invBranchDaybook['currency_code'] = 'INR';
                        $invBranchDaybook['created_by'] = $created_by;
                        $invBranchDaybook['created_by_id'] = $created_by_id;
                        $invBranchDaybook['entry_date'] = $entryDate;
                        $invBranchDaybook['entry_time'] = $entryTime;
                        $invBranchDaybook['created_at'] = $createdAt; 
                        $invBranchBook = \App\Models\BranchDaybook::create($invBranchDaybook);
                        $invBranchBookId = $invBranchBook->id;

                      if($fuleAmount>0)
                      {
                        $invBranchDaybook2['daybook_ref_id'] = $refId;
                        $invBranchDaybook2['branch_id'] = $branch_id; 
                        $invBranchDaybook2['type'] =4;
                        $invBranchDaybook2['sub_type'] = 46;
                        $invBranchDaybook2['type_id'] = $ssbId;
                        $invBranchDaybook2['associate_id'] = $associate_id; 
                        $invBranchDaybook2['member_id'] = $member_id; 
                        $invBranchDaybook2['amount'] = $fuleAmount;
                        $invBranchDaybook2['description'] = $desFule;
                        $invBranchDaybook2['description_dr'] = $memDetail->first_name.' '.$memDetail->last_name.'('.$memDetail->associate_no.') A/c Dr'.$fuleAmount.'/-';
                        $invBranchDaybook2['description_cr'] = 'To SSB('.$ssbdetail->account_no.') A/c Cr '.$fuleAmount.'/-';;
                        $invBranchDaybook2['payment_type'] = 'CR';
                        $invBranchDaybook2['payment_mode'] =$paymentMode;
                        $invBranchDaybook2['currency_code'] = 'INR';
                        $invBranchDaybook2['created_by'] = $created_by;
                        $invBranchDaybook2['created_by_id'] = $created_by_id;
                        $invBranchDaybook2['entry_date'] = $entryDate;
                        $invBranchDaybook2['entry_time'] = $entryTime;
                        $invBranchDaybook2['created_at'] = $createdAt; 
                        $invBranchBook2 = \App\Models\BranchDaybook::create($invBranchDaybook2);
                        $invBranchBookId2 = $invBranchBook2->id; 
                    }
        /******** member_transaction  entry ***************/ 
                        $invMemTran['daybook_ref_id'] = $refId;
                        $invMemTran['branch_id'] = $branch_id; 
                        $invMemTran['type'] =4;
                        $invMemTran['sub_type'] = 45;
                        $invMemTran['type_id'] = $ssbId;
                        $invMemTran['associate_id'] = $associate_id;
                        $invMemTran['member_id'] = $member_id;
                        $invMemTran['amount'] = $comAmount;
                        $invMemTran['description'] = 'Commission transfer for '.date("F Y", strtotime($legerDetail->start_date));
                        $invMemTran['payment_type'] = 'CR';
                        $invMemTran['payment_mode'] = $paymentMode; 
                        $invMemTran['currency_code'] = 'INR';
                        $invMemTran['created_by'] = $created_by;
                        $invMemTran['created_by_id'] = $created_by_id;
                        $invMemTran['entry_date'] = $entryDate;
                        $invMemTran['entry_time'] = $entryTime;
                        $invMemTran['created_at'] = $createdAt; 
                        $invMember = \App\Models\MemberTransaction::create($invMemTran);
                        $invMemberId = $invMember->id; 

                    if($fuleAmount>0)
                    {
                        $invMemTran1['daybook_ref_id'] = $refId;
                        $invMemTran1['branch_id'] = $branch_id; 
                        $invMemTran1['type'] =4;
                        $invMemTran1['sub_type'] = 46;
                        $invMemTran1['type_id'] = $ssbId;
                        $invMemTran1['associate_id'] = $associate_id;
                        $invMemTran1['member_id'] = $member_id;
                        $invMemTran1['amount'] = $fuleAmount;
                        $invMemTran1['description'] = 'Fule transfer for '.date("F Y", strtotime($legerDetail->start_date));
                        $invMemTran1['payment_type'] = 'CR';
                        $invMemTran1['payment_mode'] = $paymentMode; 
                        $invMemTran1['currency_code'] = 'INR';
                        $invMemTran1['created_by'] = $created_by;
                        $invMemTran1['created_by_id'] = $created_by_id;
                        $invMemTran1['entry_date'] = $entryDate;
                        $invMemTran1['entry_time'] = $entryTime;
                        $invMemTran1['created_at'] = $createdAt; 
                        $invMember1 = \App\Models\MemberTransaction::create($invMemTran1);
                        $invMemberId1 = $invMember1->id; 
                    }
                    if($tdsAmount>0)
                    {
                        $invMemTran2['daybook_ref_id'] = $refId;
                        $invMemTran2['branch_id'] = $branch_id; 
                        $invMemTran2['type'] =9;
                        $invMemTran2['sub_type'] = 90;
                        $invMemTran2['type_id'] = $ssbId;
                        $invMemTran2['associate_id'] = $associate_id;
                        $invMemTran2['member_id'] = $member_id;
                        $invMemTran2['amount'] = $tdsAmount;
                        $invMemTran2['description'] = 'TDS deduction for '.date("F Y", strtotime($legerDetail->start_date));
                        $invMemTran2['payment_type'] = Null;
                        $invMemTran2['payment_mode'] = NULL; 
                        $invMemTran2['currency_code'] = 'INR';
                        $invMemTran2['created_by'] = $created_by;
                        $invMemTran2['created_by_id'] = $created_by_id;
                        $invMemTran2['entry_date'] = $entryDate;
                        $invMemTran2['entry_time'] = $entryTime;
                        $invMemTran2['created_at'] = $createdAt; 
                        $invMember2 = \App\Models\MemberTransaction::create($invMemTran2);
                        $invMemberId2 = $invMember2->id;
                    }
          /******** Balance   entry ***************/
          

                  $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$amount,0);



          DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
        } 
      }
    }
     echo 'done';
  }




  public function ssb_withdraw_cash()/***prnding*?*/
    { 

      $getDaybook=\App\Models\SavingAccountTranscation::where('id','>',0)->where('id','<=',100000)->where('payment_mode','=',0)->where('type',5)->orderby('created_at','ASC')->get();

    //  echo 'ssb register cash<br>';
    echo count($getDaybook);
    //  die('start');

     
      if($getDaybook)
      {

        foreach($getDaybook as $daybook) 
        {

        DB::beginTransaction();
        try 
        { 
          $ssbdetail = \App\Models\SavingAccount::where('id',$daybook->saving_account_id)->first();
          $investmentData = \App\Models\Daybook::where('account_no',$ssbdetail->account_no)->first();

            
                    $member_id = $ssbdetail->member_id;
                    $associate_id = $daybook->associate_id;
                    $branch_id  = $daybook->branch_id;
                    $ssbId = $ssbdetail->id;
                    $paymentMode=0;

                    $depositAmount=$daybook->withdrawal; 
                    //echo  $depositAmount ;die('start');
                    $created_by=$investmentData->created_by;
                    $created_by_id=$investmentData->created_by_id;
                    
          $entryDate = date("Y-m-d", strtotime(convertDate($daybook->created_at)));
          $entryTime = date("H:i:s", strtotime(convertDate($daybook->created_at)));
          $createdAt = $daybook->created_at;

                                      
                    $type=4; $sub_type=42;   
                   $des='Cash withdraw from SSB ('.$ssbdetail->account_no.') ';                  
                  
                        $branchRef['amount'] = $depositAmount;
                        $branchRef['entry_date'] = $entryDate;
                        $branchRef['entry_time'] = $entryTime;
                        $branchRef['created_at'] = $createdAt;
                        $ref = \App\Models\BranchDaybookReference::create($branchRef);
                        $refId = $ref->id;                                      
                  
                      
                    /************ head entry*************/
                        $allTran['daybook_ref_id'] = $refId;
                        $allTran['branch_id'] = $branch_id;
                        $allTran['head1'] = 1;
                        $allTran['head2'] = 8;
                        $allTran['head3'] = 20;
                        $allTran['head4'] = 56;
                        $allTran['head5'] = Null;
                        $allTran['type'] =$type;
                        $allTran['sub_type'] = $sub_type;
                        $allTran['type_id'] = $ssbId;
                        $allTran['associate_id'] = $associate_id;
                        $allTran['member_id'] = $member_id;
                        $allTran['amount'] = $depositAmount;
                        $allTran['description'] = $des;
                        $allTran['payment_type'] = 'DR';
                        $allTran['payment_mode'] = $paymentMode; 
                        $allTran['currency_code'] = 'INR';
                        $allTran['created_by'] = $created_by;
                        $allTran['created_by_id'] = $created_by_id;
                        $allTran['entry_date'] = $entryDate;
                        $allTran['entry_time'] = $entryTime;
                        $allTran['created_at'] = $createdAt; 

                        $allTran['updated_at'] = $createdAt; 
                        $allTran['type_transaction_id'] = $daybook->id;          
                        $all = \App\Models\AllTransaction::create($allTran);
                        $allId = $all->id;
                      
                        $allTran2['daybook_ref_id'] = $refId;
                        $allTran2['branch_id'] = $branch_id;
                        $allTran2['head1'] = 2;
                        $allTran2['head2'] = 10;
                        $allTran2['head3'] = 28; 
                        $allTran2['head4'] = 71;
                        $allTran2['head5'] = NULL;  
                        $allTran2['type'] =$type;
                        $allTran2['sub_type'] = $sub_type;
                        $allTran2['type_id'] = $ssbId;
                        $allTran2['associate_id'] = $associate_id;
                        $allTran2['member_id'] = $member_id;
                        $allTran2['amount'] = $depositAmount;
                        $allTran2['description'] = $des;
                        $allTran2['payment_type'] = 'DR';
                        $allTran2['payment_mode'] = $paymentMode; 
                        $allTran2['currency_code'] = 'INR';
                        $allTran2['created_by'] = $created_by;
                        $allTran2['created_by_id'] = $created_by_id;
                        $allTran2['entry_date'] = $entryDate;
                        $allTran2['entry_time'] = $entryTime;
                        $allTran2['created_at'] = $createdAt;  
                        $allTran2['updated_at'] = $createdAt; 
                        $allTran2['type_transaction_id'] = $daybook->id;
                        $all2 = \App\Models\AllTransaction::create($allTran2);
                        $allId2 = $all2->id;
/************ double  entry*************/
                        

                        $invBranchDaybook['daybook_ref_id'] = $refId;
                        $invBranchDaybook['branch_id'] = $branch_id; 
                        $invBranchDaybook['type'] =$type;
                        $invBranchDaybook['sub_type'] = $sub_type;
                        $invBranchDaybook['type_id'] = $ssbId;
                        $invBranchDaybook['associate_id'] = $associate_id; 
                        $invBranchDaybook['member_id'] = $member_id; 
                        $invBranchDaybook['amount'] = $depositAmount;
                        $invBranchDaybook['description'] = $des;
                        $invBranchDaybook['description_dr'] = 'SSB ('.$ssbdetail->account_no.') A/c DR '.$depositAmount.'/-';
                        $invBranchDaybook['description_cr'] = 'To Cash A/c Cr '.$depositAmount.'/-';
                        $invBranchDaybook['payment_type'] = 'DR';
                        $invBranchDaybook['payment_mode'] =$paymentMode;
                        $invBranchDaybook['currency_code'] = 'INR';
                        $invBranchDaybook['created_by'] = $created_by;
                        $invBranchDaybook['created_by_id'] = $created_by_id;
                        $invBranchDaybook['entry_date'] = $entryDate;
                        $invBranchDaybook['entry_time'] = $entryTime;
                        $invBranchDaybook['created_at'] = $createdAt; 
                        $invBranchDaybook['updated_at'] = $createdAt; 
                        $invBranchDaybook['type_transaction_id'] = $daybook->id;
                        $invBranchBook = \App\Models\BranchDaybook::create($invBranchDaybook);
                        $invBranchBookId = $invBranchBook->id; 

        /******** member_transaction  entry ***************/ 
                        $invMemTran['daybook_ref_id'] = $refId;
                        $invMemTran['branch_id'] = $branch_id; 
                        $invMemTran['type'] =$type;
                        $invMemTran['sub_type'] = $sub_type;
                        $invMemTran['type_id'] = $ssbId;
                        $invMemTran['associate_id'] = $associate_id;
                        $invMemTran['member_id'] = $member_id;
                        $invMemTran['amount'] = $depositAmount;
                        $invMemTran['description'] = 'Cash withdraw';
                        $invMemTran['payment_type'] = 'DR';
                        $invMemTran['payment_mode'] = $paymentMode; 
                        $invMemTran['currency_code'] = 'INR';
                        $invMemTran['created_by'] = $created_by;
                        $invMemTran['created_by_id'] = $created_by_id;
                        $invMemTran['entry_date'] = $entryDate;
                        $invMemTran['entry_time'] = $entryTime;
                        $invMemTran['created_at'] = $createdAt; 
                        $invMemTran['updated_at'] = $createdAt; 
                        $invMemTran['type_transaction_id'] = $daybook->id;
                        $invMember = \App\Models\MemberTransaction::create($invMemTran);
                        $invMemberId = $invMember->id;  
          /******** Balance   entry ***************/
                  $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$createdAt,$depositAmount,0);
                  $branchCash=CommanController:: checkCreateBranchCash($branch_id,$createdAt,$depositAmount,0);      
                
             
            DB::commit();
          } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
          }    

      }
    }
    echo 'done';
  
}


   public function branch_balance_update_cash()
  { 

    $com=\App\Models\BranchCash::orderby('entry_date','ASC')->get();

    die('start');
    if($com)
    {

      foreach($com as $val) 
      {

        DB::beginTransaction();
        try 
        {
          $cr=\App\Models\BranchDaybook::where('branch_id',$val->branch_id)->where('payment_type','CR')->where('payment_mode',0)->wheredate('entry_date',$val->entry_date)->sum('amount');

          $dr=\App\Models\BranchDaybook::where('branch_id',$val->branch_id)->where('payment_type','DR')->where('payment_mode',0)->whereDate('entry_date',$val->entry_date)->sum('amount');

          $amount=$cr-$dr;
        //  echo $cr.'==='.$dr.'======='.$amount;die;

          $oldDateRecord = \App\Models\BranchCash::where('branch_id',$val->branch_id)->where('type',$val->type)->whereDate('entry_date','<',$val->entry_date)->orderby('entry_date','DESC')->first();
            if($oldDateRecord)
            {
              //die('4');
              $Result1 = \App\Models\BranchCash::find($oldDateRecord->id); 
                $data1['closing_balance']=$oldDateRecord->balance;    
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;


                $Result = \App\Models\BranchCash::find($val->id);
                $data['opening_balance']=$oldDateRecord->balance;
                $data['closing_balance']=0;  
                $data['balance']=$oldDateRecord->balance+$amount;   
                $Result->update($data);
                $insertid = $val->id;
            }
            else
            {

                  $Result = \App\Models\BranchCash::find($val->id);
                  $data['opening_balance']=0;
                  $data['closing_balance']=0;  
                  $data['balance']=$amount;  
                  $Result->update($data);
                  $insertid = $val->id;
            }



          DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
        }
      }
      echo 'done';
    }
    

  }
  public function branch_balance_update_closing()
  { 
    $com=\App\Models\BranchClosing::orderby('entry_date','ASC')->get();

    die('start');

    if($com)
    {

      foreach($com as $val) 
      {

        DB::beginTransaction();
        try 
        {
          $cr=$com=\App\Models\BranchDaybook::where('branch_id',$val->branch_id)->where('payment_type','CR')->whereDate('entry_date',$val->entry_date)->sum('amount');

          $dr=$com=\App\Models\BranchDaybook::where('branch_id',$val->branch_id)->where('payment_type','DR')->whereDate('entry_date',$val->entry_date)->sum('amount');

          $amount=$cr-$dr;

          $oldDateRecord = \App\Models\BranchClosing::where('branch_id',$val->branch_id)->where('type',$val->type)->whereDate('entry_date','<',$val->entry_date)->orderby('entry_date','DESC')->first();
            if($oldDateRecord)
            {
                

                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id); 
                $data1['closing_balance']=$oldDateRecord->balance;    
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;


                $Result = \App\Models\BranchClosing::find($val->id);
                $data['opening_balance']=$oldDateRecord->balance;
                $data['closing_balance']=0;  
                $data['balance']=$oldDateRecord->balance+$amount;   
                $Result->update($data);
                $insertid = $val->id;


            }
            else
            {
                  $Result = \App\Models\BranchClosing::find($val->id);
                  $data['opening_balance']=0;
                  $data['closing_balance']=0 ;
                  $data['balance']=$amount;  
                  $Result->update($data);
                  $insertid = $val->id;
            }



          DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            echo $ex->getMessage();
        }
      }
      echo 'done';
    }

  }



    public function bank_balance_update()
    { 
        $a=\App\Models\SamraddhBankDaybook::where('id','>',0)->where('id','<=',1)->orderby('entry_date','ASC')->get();  
       count($a);die;    
        if($a)
        {
            foreach($a as $val) 
            {        //print_r($val);
                DB::beginTransaction();
                try 
                {
                    $cr=\App\Models\SamraddhBankDaybook::where('bank_id',$val->bank_id)->where('payment_type','CR')->where('payment_mode',0)->wheredate('entry_date',$val->entry_date)->sum('amount');

                    $dr=\App\Models\SamraddhBankDaybook::where('bank_id',$val->bank_id)->where('payment_type','DR')->where('payment_mode',0)->whereDate('entry_date',$val->entry_date)->sum('amount');

                    $amount=$cr-$dr;
                    echo $cr.'==='.$dr.'======='.$amount.'<br>';
                    echo  $val->bank_id.'==='.$val->account_id.'==='.$val->entry_date.'==='.$amount.'<br>';


                    if($val->payment_type=='CR')
                    {
                        $bankClosing=CommanController:: checkCreateBankClosing($val->bank_id,$val->account_id,$val->entry_date,$val->amount,0);
                    }
                    else
                    {
                        $bankClosing=CommanController:: checkCreateBankClosingDR($val->bank_id,$val->account_id,$val->entry_date,$val->amount,0);
                    }             
                    //die('222');
                    DB::commit();
                } catch (\Exception $ex) {
                    DB::rollback();
                    echo $ex->getMessage();
                }
            }
          
        }
        
        echo 'done';
    }






    public function branch_balance_update()
    { 
        $a=\App\Models\BranchDaybook::where('id','>',0)->where('id','<=',1)->orderby('entry_date','ASC')->get(); 
        //count($a);die;  
        if($a)
        {
            foreach($a as $val) 
            {        //print_r($val);
                DB::beginTransaction();
                try 
                {

                    if($val->sub_type==11 || $val->sub_type==12 || $val->sub_type==1 || $val->sub_type==30 || $val->sub_type==31 || $val->sub_type==32 || $val->sub_type==33 || $val->sub_type==34 || $val->sub_type==41 || $val->sub_type==42 || $val->sub_type==43 || $val->sub_type==44 || $val->sub_type==45 || $val->sub_type==46 || $val->sub_type==47 || $val->sub_type==49 || $val->sub_type==410 || $val->sub_type==61 || $val->sub_type==90 || $val->sub_type==101 || $val->sub_type==102 || $val->sub_type==103 || $val->sub_type==104 || $val->sub_type==121 || $val->sub_type==122 || $val->sub_type==121 || $val->sub_type==121
                 )
                    {
                        $amount_type=0;
                    }

                    if($val->payment_type=='CR')
                    {
                        $bankClosing=CommanController:: checkCreateBranchClosing($val->bank_id,$val->account_id,$val->entry_date,$val->amount,0);
                    }
                    else
                    {
                        $bankClosing=CommanController:: checkCreateBranchClosingDr($val->bank_id,$val->account_id,$val->entry_date,$val->amount,0);
                    } 
                    DB::commit();
                } catch (\Exception $ex) {
                    DB::rollback();
                    echo $ex->getMessage();
                }
            }          
        }        
        echo 'done';
    }

}
