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
class ImplementHeadController extends Controller
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

    

    public function ssb_data()
    {
        die('ssb-done');
        //SELECT id, account_no,(select member_id FROM members WHERE members.id = saving_accounts.member_id) as member_id ,branch_id,branch_code,associate_id  FROM `saving_accounts` WHERE `associate_id` IS NULL   ORDER BY `id` ASC

        //1-228 229-500 500-1000 1000-2000

     /* $getDaybook=\App\Models\SavingAccount::where('id','>',1000)->where('id','<=',4000)->get();
     // print_r(count($getDaybook));die;

      foreach($getDaybook as $val) 
      {
            $in=\App\Models\Memberinvestments::where('id',$val->member_investments_id)->first();

            $Result = \App\Models\SavingAccount::find($val->id);
                  $data['associate_id']=$in->associate_id;  
                // $data['branch_id']=$val->branch_id; 
                //  $data['type']=1;
                  $Result->update($data); 
      }
      echo 'done';
      */

 /*     $getDaybook=\App\Models\SavingAccount::where('id','>',1)->where('id','<=',10000)->get();
    //  print_r(count($getDaybook));die;

      foreach($getDaybook as $val) 
      {
            $in=\App\Models\SavingAccountTranscation::where('saving_account_id',$val->id)->where('description','Like','%Opening%')->first();

            $Result = \App\Models\SavingAccountTranscation::find($in->id);
                  $data['associate_id']=$val->associate_id;  
                  $data['branch_id']=$val->branch_id; 
                  $data['type']=1;
                  $Result->update($data); 
      }
      echo 'done';
    */  
    
    /* $getDaybook=\App\Models\SavingAccountTranscation::where('description','Like','%Comm%')->get();
      foreach($getDaybook as $val) 
      {
        $in=\App\Models\SavingAccount::where('id',$val->saving_account_id)->first();

            $Result = \App\Models\SavingAccountTranscation::find($val->id);
                  
                  $data['branch_id']=$in->branch_id; 
                  $data['type']=3;
                  $Result->update($data);
      }
    */
  /*  $getDaybook=\App\Models\SavingAccountTranscation::where('description','Like','%Fuel%')->get();
      foreach($getDaybook as $val) 
      {
        $in=\App\Models\SavingAccount::where('id',$val->saving_account_id)->first();

            $Result = \App\Models\SavingAccountTranscation::find($val->id);
                  
                  $data['branch_id']=$in->branch_id; 
                  $data['type']=4;
                  $Result->update($data);
      }
    */
/*
      $getDaybook=\App\Models\SavingAccountTranscation::where('description','Like','%Cash deposit%')->get();
      foreach($getDaybook as $val) 
      {
        $in=\App\Models\Daybook::where('account_no',$val->account_no)->where('deposit',$val->deposit)->first();

            $Result = \App\Models\SavingAccountTranscation::find($val->id);
                  $data['associate_id']=$in->associate_id; 
                  $data['branch_id']=$in->branch_id; 
                  $data['type']=2;
                  $Result->update($data);
      }
    */

      $getDaybook=\App\Models\SavingAccountTranscation::where('description','Like','%Cash Withdrawal%')->where('id','>',5602)->get();
      foreach($getDaybook as $val) 
      {
        $in=\App\Models\Daybook::where('account_no',$val->account_no)->where('withdrawal',$val->withdrawal)->first();

            $Result = \App\Models\SavingAccountTranscation::find($val->id); 
                  $data['branch_id']=$in->branch_id; 
                  $data['type']=5;
                  $Result->update($data);
      }


    }

 

}
