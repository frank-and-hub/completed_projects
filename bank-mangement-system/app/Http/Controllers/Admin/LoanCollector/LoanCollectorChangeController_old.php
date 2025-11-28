<?php
namespace App\Http\Controllers\Admin\LoanCollector;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Request as Request1;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use URL;
use DB;
use Validator;
use Session;
use DateTime;
use App\Services\Sms;


use App\Models\User;
use App\Models\Member;
use App\Models\Plans;
use App\Models\AccountBranchTransfer;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentsnominees;
use App\Models\Memberinvestmentspayments;
use App\Models\SavingAccount;
use App\Models\Investmentplantransactions;
use App\Models\Transcation;
use App\Models\SavingAccountTranscation;
use App\Models\MemberNominee;
use App\Models\Relations;
use App\Models\Investmentplanamounts;
use App\Models\MemberIdProof;
use App\Models\Branch;
use App\Models\AssociateCommission;
use App\Models\Daybook;
use App\Models\CorrectionRequests;
use App\Models\TransactionReferences;
use App\Http\Controllers\Admin\CommanController;
use App\Models\SamraddhBank;
use App\Models\CollectorAccount;
use App\Models\Memberloans;
use App\Models\Grouploans;

class LoanCollectorChangeController extends Controller
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
     * Display a listing of the loans.
     *
     * @return \Illuminate\Http\Response
     */
    public function loanCollectorChange()
    {
		if(check_my_permission( Auth::user()->id,"302") != "1"){
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Loan Collector | Collector Change';
        return view('templates.admin.loan.loancollector.collectorchangeindex', $data);
    }
    
	
	public function loanCollectorDataGet(Request $request)
    {
		
			$groupList='';
			$data = Memberloans::with(['member'=>function($q){
				$q->select('id','member_id','first_name','last_name');
			}, 'loanMemberAssociate'=>function($q){
				$q->select('id','associate_no','first_name','last_name');
			}, 'loanBranch'=>function($q){
				$q->select('id','branch_code','name');
			}])->with(['CollectorAccount'=> function ($q){
                $q->with(['member_collector']);
             }])->with(['loans'])->with(['loanMemberCompany'=>function($q){
             	$q->select('id','member_id');
             }])->where('account_number', $request->code)->whereIn('status',[1,3,4])
			->first();

			if(empty($data))
			{
				$data = Grouploans::
				with(['member'=>function($q){
						$q->select('id','member_id','first_name','last_name');
					}, 'loanMemberAssociate'=>function($q){
						$q->select('id','associate_no','first_name','last_name');
					}, 'loanBranch'=>function($q){
						$q->select('id','branch_code','name');
					}
				])->with(['CollectorAccount'=> function ($q){
					$q->with(['member_collector']);
				 }])
				->with(['loans'])
				->with(['loanMemberCompany'=>function($q){
             	$q->select('id','member_id');
             }])
				->where('account_number', $request->code)
				->whereIn('status',[1,3,4])
				->first();
				
				if ($data)
				{	
					// dd($data->toArray());
					$groupList=Grouploans::with(['member'=>function($q){
						$q->select('id','member_id','first_name','last_name');
					}])->with(['loans'])->with(['loanMemberCompany'=>function($q){
             	$q->select('id','member_id');
             }])->where('account_number', $data->account_number)->whereIn('status',[1,3,4])->get();
					
					
					
				}
			}


			$type = $request->type;


			//dd($data);

			//$status=$data->status;
			if ($data)
			{
				if($data->loan_type==3)
				{     
						if($data->status==3)
						{
							return \Response::json(['view' => 'You can not change collector of clear loan plans!', 'msg_type' => 'error_clear']);
						}
						else
						{
						$id = $data->id;
						
						return \Response::json(['view' => view('templates.admin.loan.loancollector.collector_detail', ['loanData' => $data, 'groupList'=>$groupList])->render() , 'msg_type' => 'success', 'id' => $id]);
						}

				}
				else
				{
					if($data->status==3)
					{
						return \Response::json(['view' => 'You can not change collector of clear loan plans!', 'msg_type' => 'error_clear']);
					}
					else
					{
						$id = $data->id;
						return \Response::json(['view' => view('templates.admin.loan.loancollector.collector_detail', ['loanData' => $data, 'groupList'=>$groupList])->render() , 'msg_type' => 'success', 'id' => $id]);
					}

				}

			}
			else
			{
			return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
			}


    
    }

	// Get New Associate Details

	public function getnewAssociteData(Request $request)
    {
        $data=Member::where('associate_no',$request->code)->where('is_deleted',0)->first();
        $type=$request->type;
        if($data)
        {
            if($data->is_block==1)
            {
                return \Response::json(['view' => 'No data found','msg_type'=>'error2']);
            }
            else
            {
                if($data->associate_status==1)
                {
                    $id=$data->id;
                    $carder=$data->current_carder_id;
                    if($carder > $request->carder)
                    {
                        return \Response::json(['view' => view('templates.admin.loan.loancollector.newassociate_detail' ,['memberData' => $data])->render(),'msg_type'=>'success','id'=>$id,'carder'=>$carder]);
                    }
                    else
                    {
                        return \Response::json(['view' => $request->carder.'=='.$carder,'msg_type'=>'error3']);
                    }
                }        
                else
                {
                    return \Response::json(['view' => 'No data found','msg_type'=>'error1']);
                }
            }
        }
        else
        {
            return \Response::json(['view' => 'No data found','msg_type'=>'error']);
        }
    }


	public function loanCollectorChangeSave(Request $request)
    {
		
		
		
		$created_by_id = Auth::user()->id;
        $globaldate=$request['created_at'];
        $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));

		if($request->loan_type!=3) {
			$exist = CollectorAccount::where('type_id',$request->loan_id)->where('type',2)->first();
			if(isset($exist->status)){

				CollectorAccount::where('type_id',$request->loan_id)->where('type',2)->update(['status'=>0]);

			}
		}elseif($request->loan_type==3){ 

			$exist = CollectorAccount::where('type_id',$request->loan_id)->where('type',3)->first();
			if(isset($exist->status)){

				CollectorAccount::where('type_id',$request->loan_id)->where('type',3)->update(['status'=>0]);

			}
		}

		if($request->loan_type!=3)
		{
			
			$record['type'] = 2;
            $record['type_id'] = $request->loan_id;
            $record['associate_id'] = $request->new_collector_id;
			$record['status'] = 1;
            $record['created_id'] = $created_by_id;
            $record['created_by'] = 1;
            $record['created_at'] = $created_at;
            $record['updated_at'] = $created_at;
            
		}elseif($request->loan_type==3){
			$record['type'] = 3;
            $record['type_id'] = $request->loan_id;
            $record['associate_id'] = $request->new_collector_id;
			$record['status'] = 1;
            $record['created_id'] = $created_by_id;
            $record['created_by'] = 1;
            $record['created_at'] = $created_at;
            $record['updated_at'] = $created_at;
		}
		
        
		$recordInsert= CollectorAccount::create($record);

		if ($recordInsert)
		{
			return redirect('admin/loan/loancollector/collector-change')->with('success', 'Collector Changed Successfully!');
		}
		else
		{
			return back()
				->with('alert', 'Problem With Chaning Collector');
		}
	
	}


	
}