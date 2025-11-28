<?php 
namespace App\Http\Controllers\Admin; 

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Member; 
use App\Models\Branch; 
use App\Models\Receipt;
use App\Models\ReceiptAmount;
use App\Models\Grouploanmembers;
use App\Models\Memberloans;
use App\Models\Loans;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentspayments;
use App\Models\CorrectionRequests;
use App\Models\MemberTransaction;
use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class MemberBlacklistController extends Controller
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
     * Show    members list.
     * Route: /admin/member 
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
		if( check_my_permission( Auth::user()->id,"233") != "1"){
		  return redirect()->route('admin.dashboard');
		}					
		$data['title']='Manage Blacklist Members For Loan'; 
        $data['branch'] = Branch::where('status',1)->get(['id']);
         
        
        return view('templates.admin.member.blacklist_members_listing', $data);
    }
    /**
     * Get members list
     * Route: ajax call from - /admin/member
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function member_blacklist_on_loan_listing(Request $request)
    { 
         if ($request->ajax() && check_my_permission( Auth::user()->id,"233") == "1") {
		
        
        // fillter array 
        $arrFormData = array();   
        if(!empty($_POST['searchform']))
        {
            foreach($_POST['searchform'] as $frm_data)
            {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        }

            $data = Member::select('id','re_date','member_id','first_name','last_name','dob','email','mobile_no','associate_code','is_block','status','signature','photo','is_blacklist_on_loan','address','village','pin_code','state_id','district_id','city_id','member_id','associate_id','branch_id','address','branch_id')->with(['branch' => function($q){ $q->select('id','name','branch_code','sector','zone'); } ])
            				->with(['states' =>function($query) { $query->select('id','name'); }])
                            ->with(['city' => function($q){ $q->select(['id','name']); }])
                            ->with(['district' => function($q){$q->select(['id','name']); }])
                            ->with(['memberIdProof'=>function($q){ 
                                    $q->select('id','first_id_no','second_id_no','member_id','first_id_type_id','second_id_type_id')->with(['idTypeFirst'=>function($q){ $q->select(['id','name']); }])
                                    ->with(['idTypeSecond'=>function($q){ $q->select(['id','name']); }]); }])
                            ->with(['children'=>function($q){ $q->select(['id','first_name','last_name']); }])
                            ->with(['memberNomineeDetails' => function($q){ 
                                        $q->select('id','name','relation','age','member_id')->with(['nomineeRelationDetails'=>function($q){
                                        $q->select('id','name'); }]);

                            }])
                            ->with(['savingAccount_Custom' => function($q){ $q->select('id','account_no','member_id'); }])
            				->where('member_id','!=','9999999')->where('is_blacklist_on_loan','1');
			
			if(!is_null(Auth::user()->branch_ids)){
			 $id=Auth::user()->branch_ids;
			 $data=$data->whereIn('branch_id',explode(",",$id));
			}

            /******* fillter query start ****/        
           if(isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes')
            {
                if($arrFormData['associate_code'] !=''){
                    $associate_code=$arrFormData['associate_code'];
                    $data=$data->where('associate_code','=',$associate_code);
                }
                if(isset($arrFormData['branch_id']) && $arrFormData['branch_id'] !=''){
                    $id=$arrFormData['branch_id'];
                    $data=$data->where('branch_id','=',$id);
                }
                if($arrFormData['member_id'] !=''){
                    $meid=$arrFormData['member_id'];
                    $data=$data->where('member_id','=',$meid);
                }
                if($arrFormData['name'] !=''){
                    $name =$arrFormData['name'];
                 $data=$data->where(function ($query) use ($name) { $query->where('first_name','LIKE','%'.$name.'%')->orWhere('last_name','LIKE','%'.$name.'%')->orWhere(DB::raw('concat(first_name," ",last_name)') , 'LIKE' , "%$name%"); });  
                }
                if($arrFormData['start_date'] !=''){

                    $startDate=date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));

                    if($arrFormData['end_date'] !=''){
                    $endDate=date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    }
                    else
                    {
                        $endDate='';
                    }
                    $data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
                }
            }
            
            /******* fillter query End ****/


	        $count=$data->orderby('id','DESC')->count('id');

            $data=$data->orderby('id','DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

            $dataCount = Member::where('member_id','!=','9999999')->where('is_blacklist_on_loan','1');

			if(!is_null(Auth::user()->branch_ids)){
			 $id=Auth::user()->branch_ids;
			 $dataCount=$dataCount->whereIn('branch_id',explode(",",$id));
			}
			$totalCount =$dataCount->count('id');
			
                    
            $sno=$_POST['start'];
            $rowReturn = array(); 
            foreach ($data as $row)
            {  
                $sno++;
                $val['DT_RowIndex']=$sno;
                $NomineeDetail = $row['memberNomineeDetails'];
                $val['join_date']=date("d/m/Y", strtotime($row->re_date));

                $val['branch']=$row['branch']->name;

                $val['branch_code']=$row['branch']->branch_code;
                $val['sector_name']=$row['branch']->sector;
                $val['region_name']=$row['branch']->sector;
                $val['zone_name']=$row['branch']->zone;
				$btnS = ''; 
				 $url8 = URL::to("admin/member-edit/".$row->id."");
                $btnS .= '<a class=" " href="'.$url8.'" title="Edit Member">' .$row->member_id.'</a>';
                //$val['member_id']=$btnS;
				$val['member_id']=$row->member_id;
                $val['name']=$row->first_name.' '.$row->last_name;
				$val['dob']= date('d/m/Y', strtotime($row->dob));
				$val['nominee_name']= $NomineeDetail->name;
				if($row->id)
				{
					$relation_id = $NomineeDetail->relation; 
					if($relation_id)
					{
						$val['relation']= $NomineeDetail['nomineeRelationDetails']->name;
					}else{
						$val['relation']= 'N/A';
					}
				}
				
				$val['nominee_age']= $NomineeDetail->age;
                $accountNo='';
                if($row['savingAccount_Custom'])
                {
                    $accountNo= $row['savingAccount_Custom']->account_no; //getMemberSsbAccountDetail($row->id)->account_no;
                }
                $val['ssb_account']=$accountNo;
                $val['email']=$row->email;
                $val['mobile_no']=$row->mobile_no;
                $val['associate_code']=$row->associate_code;
                $val['associate_name']= $row['children']->first_name.' '.$row['children']->last_name; //getSeniorData($row->associate_id,'first_name').' '.getSeniorData($row->associate_id,'last_name');
                if($row->is_block==1)
                {
                    $status = 'Blocked';
                }
                else
                {
                        if($row->status==1)
                        {
                          $status = 'Active';
                        }
                        else
                        {
                            $status = 'Inactive';
                        }
                }
                $val['status']=$status;
                $is_upload='Yes';
                if($row->signature=='')
                 {
                    $is_upload = 'No'; 
                 }
                 if($row->photo=='')
                 {
                    $is_upload = 'No'; 
                 } 
                $val['is_upload']=$is_upload;
				
				if($row->is_blacklist_on_loan == "1")
                {
                    $is_blacklist_on_loan = 'Blacklisted';
                } else {
					 $is_blacklist_on_loan = 'Active';
				}
				$val['is_blacklist_on_loan']=$is_blacklist_on_loan;



                //$idProofDetail= \App\Models\MemberIdProof::where('member_id',$row->id)->first();

                 $val['firstId']= $row['memberIdProof']['idTypeFirst']->name .' - '.$row['memberIdProof']->first_id_no;//getIdProofName($idProofDetail->first_id_type_id).' - '.$idProofDetail->first_id_no;
                 $val['secondId']= $row['memberIdProof']['idTypeSecond']->name .' - '.$row['memberIdProof']->second_id_no; //getIdProofName($idProofDetail->second_id_type_id).' - '.$idProofDetail->second_id_no;

                $val['address']=preg_replace( "/\r|\n/", "",$row->address);
                $val['state']= $row['states']->name; //getStateName($row->state_id);
                $val['district']= $row['district']->name; //getDistrictName($row->district_id);
                $val['city']= $row['city']->name; //getCityName($row->city_id);
                $val['village']= $row->village;
                $val['pin_code']= $row->pin_code;
                

                if(check_my_permission( Auth::user()->id,"244") == "1" )
                {
                	$btn = '<div class="list-icons"><div class="dropdown"><button class="list-icons-item unblockUser" title="Unblock" data-row-id='.$row->id.'><i class="icon-blocked"></i></button>'; 
                } 

                //$btn .= '<a class="dropdown-item" href="'.$url5.'" ><i class="fas fa-print mr-2"></i>Unblock</a>  '; 
 
                $btn .= '</div></div>';  

                $val['action']=$btn;
                $rowReturn[] = $val; 
          } 
          $output = array("branch_id"=>Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );

          return json_encode($output); 
 
            
        }
    }
 /**
     * Show    create member.
     * Route: /admin/member-register 
     * Method: get 
     * @return  array()  Response
     */
    public function add_blacklist()
    {
       	   
	    if(check_my_permission( Auth::user()->id,"3") != "1" || check_my_permission( Auth::user()->id,"235") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		   if(Auth::user()->branch_id>0){
			 $id=Auth::user()->branch_id;
			  $data['branch'] = Branch::where('status',1)->where('id','=',$id)->get();
		   }
		   else{
		      $data['branch'] = Branch::where('status',1)->get();
		   }
	   
	    $data['title']='Add Blacklist Member For Loan'; 
       
        $data['state']=stateList();  
        $data['occupation']=occupationList();
        $data['religion']=religionList();
        $data['specialCategory']=specialCategoryList();
        $data['idTypes']=idTypeList();
        $data['relations']=relationsList();
         
        
        return view('templates.admin.member.add_blacklist_member_on_loan', $data);
    }

	/*
    public function detail($id)
    {
        if(check_my_permission( Auth::user()->id,"3") != "1"){
		  return redirect()->route('admin.dashboard');
		}	
		$data['title']='Member | Detail'; 
        $data['memberDetail'] = Member::where('id',$id)->first();
        $data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id',$id)->first();
        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id',$id)->first();
        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id',$id)->first();
        $recipt=Receipt::where('member_id',$id)->where('receipts_for',1)->first('id');
        $data['recipt']= $recipt ? $recipt->id : '';         
        
        return view('templates.admin.member.detail', $data);
    }
	*/
	
	public function getBlacklistMemberData(Request $request)
	{
		$data=Member::where('member_id',$request->code)->where('status',1)->where('is_deleted',0);

		if(!is_null(Auth::user()->branch_ids)){
		 $id=Auth::user()->branch_ids;
		 $data=$data->whereIn('branch_id',explode(",",$id));
		}
		$data=$data->first();


		if($data)
		{
			if($data->is_block==1)
			{
				 return \Response::json(['view' => 'No data found','msg_type'=>'error2']);

			} else {
				
				if($data->is_blacklist_on_loan == "0")
				{

					$id=$data->id;
					$member_name = $data->first_name." ".$data->last_name;
					
					// Now Get Associate Name
					$associate_code=$data->associate_code;
					$associate_name = getSeniorData($data->associate_id,'first_name').' '.getSeniorData($data->associate_id,'last_name');
					
					$idProofDetail = \App\Models\MemberIdProof::where('member_id',$id)->first();

					$firstId = getIdProofName($idProofDetail->first_id_type_id).' - '.$idProofDetail->first_id_no;
					$secondId = getIdProofName($idProofDetail->second_id_type_id).' - '.$idProofDetail->second_id_no;

					$viewData = '<h4 class="card-title mb-3">Member Personal Detail</h4>';
					$viewData = '<input type = "hidden" name="memberID" id="memberID" value='.$id.'>';
					$viewData .= '<div class="row">
									<div class="col-lg-6 ">
									<div class=" row">
									  <label class=" col-lg-3">Member Name</label><label class=" col-lg-1">:</label>
									  <div class="col-lg-8 ">
										'.$member_name.'
									  </div>
									</div>
								  </div>
								  <div class="col-lg-6 ">
									<div class=" row">
									  <label class="col-lg-3">Associate Name</label><label class=" col-lg-1">:</label>
									  <div class="col-lg-8 ">
										'.$associate_name.' 
									  </div>
									</div>
								  </div>
								  </div>';
					$viewData .= '<div class="row">
									<div class="col-lg-6 ">
									<div class=" row">
									  <label class=" col-lg-3">First ID Proof</label><label class=" col-lg-1">:</label>
									  <div class="col-lg-8 ">
										'.$firstId.'
									  </div>
									</div>
								  </div>
								  <div class="col-lg-6 ">
									<div class=" row">
									  <label class="col-lg-3">Second ID Proof</label><label class=" col-lg-1">:</label>
									  <div class="col-lg-8 ">
										'.$secondId.' 
									  </div>
									</div>
								  </div>
								  </div>';	

					$viewData .= '<div class="row" style="text-align:center">
									<div class="col-lg-12"> 
										<button type="button" class="btn btn-primary legitRipple blockMemberOnLoan" id="btnAdd">Blacklist</button>  
									</div></div>';								  

					//return \Response::json(['view' => view('templates.admin.associate.partials.member_detail' ,['memberData' => $data,'idProofDetail' => $idProofDetail])->render(),'msg_type'=>'success','id'=>$id]);
					
					return \Response::json(['view' => $viewData,'msg_type'=>'success']);
				} else {
					return \Response::json(['view' => 'Customer is already black listed','msg_type'=>'error1']);
				}
			}
		} else {
			return \Response::json(['view' => 'No data found','msg_type'=>'error']);
		}
	}
	
    public function action_blacklist_member_for_loan(Request $request)
    {
		
		$member_id = $request->memberID;
		$is_blacklist_on_loan = $request->is_block;
		
		DB::table('members')->where('id', $member_id)->update(array('is_blacklist_on_loan' => $is_blacklist_on_loan));
		
		if($is_blacklist_on_loan == "1"){
			return \Response::json(['view' => 'Member blacklisted successfully','msg_type'=>'success']);
		} else {
			return \Response::json(['view' => 'Member activated successfully.','msg_type'=>'success']);
		}
    }
	
	
	
	public function print_blacklist_member_on_loan(Request $request)
    {
		$data = Member::with('branch')
    				->with(['states' =>function($query) { $query->select('id','name'); }])
                        ->with(['city' => function($q){ $q->select(['id','name']); }])
                        ->with(['district' => function($q){$q->select(['id','name']); }])
                        ->with(['memberIdProof'=>function($q){ 
                                $q->with(['idTypeFirst'=>function($q){ $q->select(['id','name']); }])
                                ->with(['idTypeSecond'=>function($q){ $q->select(['id','name']); }]); }])
                        ->with(['children'=>function($q){ $q->select(['id','first_name','last_name']); }])
                        ->with(['memberNomineeDetails' => function($q){ 
                                    $q->with(['nomineeRelationDetails'=>function($q){
                                    $q->select('id','name'); }]);

                    }])->where('member_id','!=','9999999')->where('is_blacklist_on_loan','1');
   
		if(!is_null(Auth::user()->branch_ids)){
			$branch_ids=Auth::user()->branch_ids;
			$data=$data->whereIn('branch_id',explode(",",$branch_ids));
		}

		if(isset($request['is_search']) && $request['is_search'] == 'yes')
		{
			if($request['associate_code'] !=''){
				$associate_code=$request['associate_code'];
				$data=$data->where('associate_code','=',$associate_code);
			}

			if($request['branch_id'] !=''){
				$id=$request['branch_id'];
				$data=$data->where('branch_id','=',$id);
			}

			if($request['member_id'] !=''){
				$meid=$request['member_id'];
				$data=$data->where('member_id','=',$meid);
			}

			if($request['name'] !=''){
				$name =$request['name'];
				$data=$data->where(function ($query) use ($name) { $query->where('first_name','LIKE','%'.$name.'%')->orWhere('last_name','LIKE','%'.$name.'%')->orWhere(DB::raw('concat(first_name," ",last_name)') , 'LIKE' , "%$name%"); }); 
			}

			if($request['start_date'] !=''){
				$startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
				
				if($request['end_date'] !=''){
				$endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));
				} else {
					$endDate='';
				}

				$data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]); 
			}
		}
		
		$memberList=$data->orderby('id','DESC')->get();

		return view('templates.admin.member.memberBlacklistExport',compact('memberList'));
	}
	
	
}
