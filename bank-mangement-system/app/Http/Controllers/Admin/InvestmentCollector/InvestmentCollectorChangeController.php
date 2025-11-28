<?php
namespace App\Http\Controllers\Admin\InvestmentCollector;
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
use App\Http\Traits\BranchPermissionRoleWiseTrait;

class InvestmentCollectorChangeController extends Controller
{
    
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    use BranchPermissionRoleWiseTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the loans.
     *
     * @return \Illuminate\Http\Response
     */
    public function investmentCollectorChange()
    {
		if(check_my_permission( Auth::user()->id,"301") != "1"){
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Investment Collector | Collector Change';
        return view('templates.admin.investment_management.investmentcollector.collectorchangeindex', $data);
    }
    
	
	public function investmentCollectorDataGet(Request $request)
    {

        
        $data = Memberinvestments::with(['plan' => function($query){ $query->select('id','name');}])->with(['member'=>function($q){
            $q->select('id','member_id','first_name','last_name');
        }])->with(['memberCompany'=>function($q){
            $q->select(['id','member_id']); }])->with(['CollectorAccount'=> function ($q){
            $q->with(['member_collector']);
            }])->with(['Branch'=>function($q){
            $q->select('id','name','branch_code');
        }])->with(['associateMember' => function($query){ $query->select('id','first_name','last_name','mobile_no','current_carder_id','associate_status','associate_no');}])->where('account_number',$request->code)->whereNotNull('associate_id')->first();
        //dd($data);
        //$is_mature=$data->is_mature;
        
        if ($data)
        {
            if( $data->is_mature==0){
                return \Response::json(['view' => 'You can not change collector of matured plans!', 'msg_type' => 'collector_error']);

            }
            else
            {
                $id = $data->id;
                return \Response::json(['view' => view('templates.admin.investment_management.investmentcollector.collector_detail', ['investData' => $data])->render() , 'msg_type' => 'success', 'id' => $id]);
            }

        }
        else
        {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    
    }

	// Get New Associate Details

	public function getnewCollectorData(Request $request)
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
                        return \Response::json(['view' => view('templates.admin.investment_management.investmentcollector.newcollector_detail',['memberData' => $data])->render(),'msg_type'=>'success','id'=>$id,'carder'=>$carder]);
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


	public function investmentCollectorChangeSave(Request $request)
    {
		
		
		
		$created_by_id = Auth::user()->id;
        $globaldate=$request['created_at'];
        $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));

        $exist = CollectorAccount::where('type_id',$request->invest_id)->where('type',1)->first();

        //dd($exist);
       
        if(isset($exist->status)){

            CollectorAccount::where('type_id',$request->invest_id)->where('type',1)->update(['status'=>0]);

        }
        
		$record['type'] = 1;
		$record['type_id'] = $request->invest_id;
		$record['associate_id'] = $request->new_collector_id;
		$record['status'] = 1;
		$record['created_id'] = $created_by_id;
		$record['created_by'] = 1;
		$record['created_at'] = $created_at;
		$record['updated_at'] = $created_at;
		$recordInsert= CollectorAccount::create($record);

        if ($recordInsert)
        {
            return redirect('admin/investment_management/collector-change')->with('success', 'Collector Changed Successfully!');
        }
        else
        {
            return back()
                ->with('alert', 'Problem With Chaning Collector');
        }
	}


	
}