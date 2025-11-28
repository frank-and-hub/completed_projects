<?php 
namespace App\Http\Controllers\Branch; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Member; 
use App\Models\SavingAccount; 
use App\Models\SavingAccountTranscation; 
use App\Models\SavingAccountTransactionView;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

use Session;
use Image;
use Redirect;
use URL;
/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class SavingController extends Controller
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
     * Show  particular member account detail.
     * Route: /account/detail 
     * Method: get 
     * @return  array()  Response
     */
    public function index($memberId)
    {
        
		if(!in_array('View Saving Account', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		
		$data['title']='Saving | Account'; 
        
        $data['accountDetail'] = SavingAccount::with(['savingBranch' => function($query){ $query->select('id', 'name','branch_code','city_id','pin_code','address');}])->where('id',$memberId)->first();

        $data['memberDetail'] = Member::where('id',$data['accountDetail']->customer_id)->first();
        if(!empty($data['accountDetail']))
        {
         $data['accountTranscation'] = SavingAccountTransactionView::where('saving_account_id',$data['accountDetail']->id)->orderby('opening_date','DESC')->limit(10)->get(); 
        }
        $data['memberId']=$memberId;
       $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$memberId)->first();
        
        return view('templates.branch.saving.index', $data);
    }
    /**
     * Show  passbook.
     * Route: /account/printpassbook 
     * Method: get 
     * @return  array()  Response
     */
    public function passbook($id,$memberId)
    {
        $data['title']='Saving | Passbook';  
        $data['accountDetail'] = SavingAccount::with(['savingBranch' => function($query){ $query->select('id', 'name','branch_code','city_id','pin_code','address');}])->where('id',$id)->first();
         $data['memberDetail'] = Member::where('id',$memberId)->first();
        
        return view('templates.branch.saving.passbook', $data);
    }
    /**
     * filer account data .
     * Route: /account/printpassbook 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function passbook_filter(Request $request)
    {
        $data['title']='Saving | Passbook';
        $data['accountDetail'] = SavingAccount::with(['savingBranch' => function($query){ $query->select('id', 'name','branch_code','city_id','pin_code','address');}])->where('id',$request['id'])->first();
        $memberId=$data['accountDetail']->member_id;
        $data['memberDetail'] = Member::where('id',$memberId)->first();
        $startDate=date("Y-m-d", strtotime($request['start_date']));
        $endDate=date("Y-m-d", strtotime($request['end_date']));
         
        $data['accountTranscation'] = SavingAccountTranscation::where('saving_account_id',$request['id'])->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->orderby('id','DESC')->get(); 
        
       $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$memberId)->first();
       $data['startDate']=$request['start_date'];
       $data['endDate']=$request['end_date'];
       $data['is_fillter']=1;
        
        return view('templates.branch.saving.passbook', $data);
    }
    /**
     * Show  statement.
     * Route: /account/printpassbook 
     * Method: get 
     * @return  array()  Response
     */
    public function statement($id,$memberId)
    {
        $data['title']='Saving | Statement';  
        $data['accountDetail'] = SavingAccount::with(['savingBranch' => function($query){ $query->select('id', 'name','branch_code','city_id','pin_code','address');}])->where('id',$id)->first();
         $data['memberDetail'] = Member::where('id',$memberId)->first();
        
        return view('templates.branch.saving.statement', $data);
    }
    /**
     * filer account statement .
     * Route: /account/statement 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function statement_filter(Request $request)
    {
      //  print_r($_POST);die;
         $data['title']='Saving | Statement';
        $data['accountDetail'] = SavingAccount::with(['savingBranch' => function($query){ $query->select('id', 'name','branch_code','city_id','pin_code','address');}])->where('id',$request['id'])->first();
        $memberId=$data['accountDetail']->member_id;
        $data['memberDetail'] = Member::where('id',$memberId)->first();
        $startDate=date("Y-m-d", strtotime($request['start_date']));
        $endDate=date("Y-m-d", strtotime($request['end_date']));
         
        $data['accountTranscation'] = SavingAccountTranscation::where('saving_account_id',$request['id'])->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->orderby('id','DESC')->get(); 
        
       $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$memberId)->first();
       $data['startDate']=$request['start_date'];
       $data['endDate']=$request['end_date'];
       $data['is_fillter']=1;
        
        return view('templates.branch.saving.statement', $data);
    }

    public function savingIndex($memberId)
    {

		if(!in_array('View Saving Account', auth()->user()->getPermissionNames()->toArray())){
		 return redirect()->route('branch.dashboard');
		 }
		$data['title']='Saving | Account'; 
        $data['memberDetail'] = Member::where('id',$memberId)->first();
      
       
        
        return view('templates.branch.saving.savingIndex', $data);
    }


    public function savingListing(Request $request)
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
          $memberId=$request['member_id'];
           
            $data = \App\Models\SavingAccount::with( 'company','savingBranchDetailCustom','customerSSB','ssbMemberCustomer','getSSBAccountBalance')->where('customer_id',$memberId)->orderBy('id', 'DESC')->get();
            
   
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('company_name', function($row){
                $company_name =$row['company']->name;
                return $company_name;
            })
           
            ->rawColumns(['branch_name'])
            ->addColumn('branch_name', function($row){
                $branch_name =$row['savingBranchDetailCustom']->name.'('.$row['savingBranchDetailCustom']->branch_code.')';
                return $branch_name;
            })
           
            ->rawColumns(['branch_name'])
            ->addColumn('customer_id', function($row){
                $customer_id =$row['customerSSB']->member_id;
                return $customer_id;
            })
            ->rawColumns(['customer_id'])
         
            ->addColumn('member_id', function($row){
                $member_id =$row['ssbMemberCustomer']->member_id;
                return $member_id;
            })
            ->rawColumns(['member_id'])
         
            ->addColumn('account_no', function($row){
                return $row->account_no;
            })
            ->rawColumns(['account_no'])
            ->addColumn('member_name', function($row){
                $member_name =$row['customerSSB']['first_name'].' '.$row['customerSSB']['last_name'];
                return $member_name;
            })
            ->rawColumns(['member_name'])
            ->addColumn('balance', function($row){
                $balance =0;
                if( $row->getSSBAccountBalance)                
                {
                    $balance = $row->getSSBAccountBalance->totalBalance;
                }
                
                return $balance;
            })
            ->rawColumns(['balance'])
            ->addColumn('action', function($row){
                $authDetail = auth()->user()->getPermissionNames()->toArray();
                $url = '';
                $btn = '';
                $url1 = URL::to("branch/member/account/".$row->id."");

                if(in_array('View Saving Account', $authDetail )){
                    $btn .= '<a class=" " href="'.$url1.'" title="Detail"><i class="fa fa-eye" aria-hidden="true  text-default mr-2"></i></a>  ';
				} 
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

}
