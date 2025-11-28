<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\{Branch, States, BranchLog, City, User, IpAddresses, BranchCurrentBalance, BranchLogs,Companies,CompanyBranch};
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\DataTables;
use URL;
use Artisan;
use DB;

class BranchController extends Controller
{
	public function __construct()
	{

	}

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function create()
	{
		if(check_my_permission( Auth::user()->id,"66") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		
		$data['title']='Bank branches';
		$allcompany = DB::table('companies')->where('status','1')->get();
		$data['allcompany'] = $allcompany;
		return view('templates.admin.branch.create', $data);
	}

	/**
	 * @param Request $request
	 * create branch with manager and assign to branch
	 * @return \Illuminate\Http\RedirectResponse
	 */
    public function CreateBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:branch|max:255',
            'password' => 'nullable|required_with:password_confirmation|string|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect('admin/branch')
                ->withErrors($validator)
                ->withInput();
        }
	    $stateData = States::find($request->state);

	    $maxBranchCode = Branch::where('state_id', $request->state)->max('branch_code');
	    if ( $maxBranchCode ) {
		    $branchCode = $maxBranchCode + 1;
	    } else {
		    $branchCode = $stateData->code;
	    }

        $user = new User();
        $user->password = Hash::make($request->password);
        //$user->email = strtolower(str_replace(' ', '',$request->name)).env('MAIL_HOSTNAME','@microfinance.com');
        $user->email = $request->email;
        $user->name = $request->name;
        $user->username = $request->name;
        $user->ip_address = user_ip();
        $user->role_id = 3;
        $user->phone = $request->phone;
        $user->balance = 0;
        $user->email_verify = 1;
        $user->phone_verify = 1;
        $user->sms_code = 1;
	    $user->status = 0;
        $userCheck = $user->save();
	    $user->assignRole('Branch Manager');

		$permission = Permission::all();
		foreach ($permission as $permissions) {
			$user->givePermissionTo($permissions);
		}

        // $user->givePermissionTo('Member Create');
	    // $user->givePermissionTo('Member View Detail');
	    // $user->givePermissionTo('Member View');
	    // $user->givePermissionTo('Associate Create');
	    // $user->givePermissionTo('Associate View');
	    // $user->givePermissionTo('Register Loan');
	    // $user->givePermissionTo('Loan View');
	    // $user->givePermissionTo('Investment Plan Registration');
	    // $user->givePermissionTo('Investment Plan Detail View');
	    // $user->givePermissionTo('Investment Receipt');
	    // $user->givePermissionTo('Investment Plan View');
        // $user->givePermissionTo('Passbook view');
        // $user->givePermissionTo('Passbook Cover Print');
        // $user->givePermissionTo('Certificate Print');
        // $user->givePermissionTo('Passbook Transaction Print');
        // $user->givePermissionTo('Associate Profile View');

        if($request->checkotp){
	    	$otpPermission = 0;
	    }else{
	    	$otpPermission = 1;
	    }

	    $data['name'] = $request->name;
	    $data['country_id'] = $request->country;
	    $data['state_id'] = $request->state;
	    $data['city_id'] = $request->city;
	    $data['sector'] = $request->sector;
	    $data['regan'] = $request->regan;
	    $data['zone'] = $request->zone;
	    $data['pin_code'] = $request->pin_code;
	    $data['manager_id'] = $user->id;
	    $data['address'] = $request->address;
	    $data['phone'] = $request->phone;
	    $data['email'] = $request->email;
		$data['cash_in_hand'] = ($request->cash_in_hand >=0 && !is_null($request->cash_in_hand))  ? $request->cash_in_hand : 0;
	    $data['branch_code'] = $branchCode;
	    $data['otp_login'] = $otpPermission;
		//$data['company_id'] = $request->company_name;
		$data['date'] = date("Y-m-d ", strtotime(convertDate($request->created_at)));
		if($request->cash_in_hand == 0){
			$permission = Permission::all();
			foreach($permission as $permissions){
				$user->givePermissionTo($permissions);
			}
		}
		if($request->cash_in_hand >= 0){
			$res = Branch::create($data);
			
			$encodeDate = json_encode($data);
			//Company Brnach Data insert
			
			if(!empty($request->company_chekbox)){
				$oldbuss = str_replace('old_','',$request->old_buss);
				$newbuss = str_replace('new_','',$request->new_buss);
				$primary = explode(',',str_replace('primary_','',$request->primarybox));
				foreach($request->company_chekbox as $cid){
					$arrs = ["branch_id" => $res->id, 
							"company_id" => $cid,
							"is_old_business" => in_array($cid, $oldbuss) ? '1' : '0',
							"is_new_business" =>  in_array($cid, $newbuss) ? '1' : '0',
							"is_primary" => in_array($cid, $primary) ? '1' : '0',
					 		"created_by" => 1, 
					 		"created_by_id" => Auth::user()->id,
					  		"status" => 1];
					CompanyBranch::create($arrs);
				}
			}
			$logarrs = [
				"branch_id" => $res->id,
				"ip_address" => $_SERVER['REMOTE_ADDR'],
				"created_by" => 1,
				"created_by_id" => Auth::user()->id,
				"title" => "Branch Create",
				"description" => "Branch Create",
				'old_data' => NULL,
				'new_data' => $encodeDate,
			];
			BranchLog::insert($logarrs);
			if ($userCheck) {
				return redirect()->route('admin.branch')->with('success', 'Branch Created Successfully!');
			} else {
				return back()->with('alert', 'Problem With Creating New Branch');
			}
		}else{
			return back()->with('alert', 'Problem With Branch Limit');
		}
    }

	/***
	 * @param $id
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
    public function edit( $id )
    {
	    $data['title']='Bank branches';
	    $data['branch']=Branch::find($id);
	    return view('templates.admin.branch.edit', $data);
    }
    
	/**
	 * @param Request $request
	 * update branch
	 * @return \Illuminate\Http\RedirectResponse
	 */
    public function UpdateBranch( Request $request ){    
	    $branch = Branch::find($request->id);
	    $user = User::find( $branch->manager_id );
		
		$cash_in_hand = $request->cash_in_hand;
	    $otpPermission = $request->checkotp ? 0 : 1;
	    if( $branch ) {
			$data = [
				'name' => trim($request->name),
				'sector' => $request->sector,
				'regan' => $request->regan,
				'zone' => $request->zone,
				'pin_code' => $request->pin_code,
				'address' => trim($request->address),
				'phone' => $request->phone,
				'email' => trim($request->email),
				'otp_login' => $otpPermission,
				'cash_in_hand' => ($cash_in_hand > 0) ? $cash_in_hand : 0,
			];
			$old_data = [
				'name' => $branch->name,
				'sector' => $branch->sector,
				'regan' => $branch->regan,
				'zone' => $branch->zone,
				'pin_code' => $branch->pin_code,
				'address' => $branch->address,
				'phone' => $branch->phone,
				'email' => $branch->email,
				'otp_login' => $branch->otp_login,
				'cash_in_hand' => $branch->cash_in_hand,
			];
			// if($cash_in_hand >= 0){
			// 	if($branch->cash_in_hand != $cash_in_hand){
			// 		$arrs = [
			// 			'branch_id' => $request->id,
			// 			'ip_address' => $_SERVER['REMOTE_ADDR'],
			// 			'type' => '1',
			// 			'user_id' => auth()->id(),
			// 			'message' => 'Branch Edit Limit',
			// 			'data' => json_encode($data),
			// 		];
			// 		DB::table('logs_branch')->insert($arrs);
			// 	}
			// }
			// else{
			// 	return back()->with('alert', 'Problem With Branch Limit Updating');
			// }
			$Logarrs = [
				'branch_id' => $request->id,
				'title' => 'Detail Update',
				'description' => 'Branch Details has updated  by ' . ucwords(Auth::user()->username),
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'created_by_id' => Auth::user()->id,
				'created_by' =>'1',
				'old_data' => json_encode($old_data),
				'new_data' => json_encode($data),
			];
			BranchLog::insert($Logarrs);
			$branchUpdate = $branch->update( $data );
			if($branch->first_login == '0'){
				$branch->update(['first_login'=>'1']);
			}
			branchbalanceInableOrDescablecrone($branch->manager_id,Permission::all());
		    if ( $branchUpdate ) {
			    return redirect('admin/branch')->with('success', 'Branch Updated Successfully!');
		    } else {
			    return back()->with('alert', 'Problem With Updating');
		    }
	    } else {
		    return back()->with('alert', 'Problem With Updating');
	    }
    }

	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function branch()
	{
		if(check_my_permission( Auth::user()->id,"65") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title']='Bank branches';
		$data['branch'] = Branch::select('id','name','branch_code','created_at','sector','zone','regan')->latest()->get();
		return view('templates.admin.branch.index', $data);
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function branchListing(Request $request)
	{
		$data['title']='Bank branches';
		if ($request->ajax()) {

			$data = Branch::select('id','name','branch_code','created_at','sector','zone','regan','date','total_amount','city_id','state_id','phone','email','otp_login','status','address','cash_in_hand','day_closing_amount')->with(['branchCityCustom' => function($q){ $q->select('id','name'); }])
				->with([
					'branchCityCustom:id,name',
					'branchStatesCustom:id,name',
					'branchLog'
				]);
			//$data1=$data->orderby('id', 'desc')->count('id');
            
            
            if(isset($_POST['search']) && !empty($_POST['search']['value']) ){
            	$value = $_POST['search']['value'];
            	
            	if(is_numeric($_POST['search']['value'])){
            		$data = $data->where('branch_code','like','%' . $_POST['search']['value'] . '%');
            	}else{
            		$data = $data->where('name','like','%' . $_POST['search']['value'] . '%');
            	}
            	
            }            
            $count = $data->count('id');
			$data = $data->orderBy('id', 'desc')->offset($_POST['start'])->limit($_POST['length'])->get();	
			$totalCount =$count;
			$sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row)
            {
				$getCompany = \App\Models\CompanyBranch::whereBranchId($row->id)->pluck('company_id');
				$branchBalance = 0;
				foreach($getCompany as $K => $V){
					$branchBalance += getbranchbankbalanceamounthelper($row->id,$V); // get branch current balance from branch bank balance view by branch id 
				}
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['limit'] = number_format((float)$row['cash_in_hand'], 2, '.', '');
                $val['balance_amount'] = number_format((float)($branchBalance), 2, '.', '');
                $val['created_at1'] = date("d/m/Y", strtotime( $row['created_at']));
                $val['branch_code'] = $row->branch_code;
                $val['name'] = $row->name;
                $val['sector'] = $row->sector;
                $val['regan'] = $row->regan;
                $val['zone'] = $row->zone;

                if ( $row->city_id ) 
                {
                    $val['city_id'] = $row['branchCityCustom']->name; //City::find($row->city_id)->name;
                } else {
             		$val['city_id'] = '';
                }

                if ( $row->state_id ) 
                {
                    $val['state_id'] = $row['branchStatesCustom']->name; //States::find($row->state_id)->name;
                } else {
             		$val['state_id'] = '';
                }

                $val['phone'] = $row->phone;
                $val['email'] = $row->email;

                if ( $row->otp_login ==0 ) 
                {
                    $val['otp'] = "Yes";
                } else {
             		$val['otp'] = 'No';
                }

                $val['status'] = $row->status;
                $val['address'] = $row->address;

                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $url1 = URL::to("/admin/branch-edit/".$row->id."");
           		$btn .= '<a href="'. $url1 .'" class="dropdown-item"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                $url2 = URL::to("admin/get-ip/".$row->id."");
                $url3 = URL::to("admin/branch-status/".$row->id."/".$row->status);
                $btn .= '<a href="'. $url3. '" class="dropdown-item"><i class="far fa-thumbs-up mr-2"></i>';
                if($row->status == 1 ) {
	                $btn .= 'Deactive';
                } else {
	                $btn .= 'Active';
                }

				//$btn .= '</a><a class="dropdown-item assign_branch" data-toggle="modal" id="assign_branch" data-target="#branchModal" data-id="' . $row->id . '" ><i class="icon-snowflake mr-2"> </i> Change Branch Name </a>';
				
				$btn .= '</a><a class="dropdown-item assign_branch" data-toggle="modal" id="assign_branch" data-target="#branchModal" data-id="' . $row->id . '" ><i class="icon-snowflake mr-2"> </i> Change Branch Name </a>';
                $btn .= '</a><a class="dropdown-item assign_company" data-toggle="modal" data-target="#assignModal" data-id="'.$row->id.'"><i class="icon-snowflake mr-2"> </i> Assign Company </a>';
                $btn .= '</a><a href="'. Route('admin.changedPassword',['id'=>$row->id]) .'" class="dropdown-item"><i class="icon-bin2 mr-2"> </i> Change Password </a>';
				$btn .= '</a><a class="dropdown-item" href="admin/branch/company_view/'.$row->id.'" data-id="'.$row->id.'"><i class="icon-eye mr-2"> </i> View Details </a>';
                $btn .= '</div></div></div>';

                $val['action'] = $btn;                
                $rowReturn[] = $val;
            }

			$output = array( "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn );
            return json_encode($output);

		}
	}

	/**
	 * @param Request $request in Branch name
	 * @return array in status in true/false
	 */
	public function checkBranch(Request $request)
	{
		$branch = User::where('name', $request->branchName)->pluck('name')->first();
			if ( $branch ) {
				return array( 'status'=>true );
			} else {
				return array( 'status'=>false );
			}
	}

	public function branchStatusUpdate ( $id, $status )
	{
		$branch = Branch::find($id);
		if ($status == 1) {
			$branch->update(['status' => 0]);
			User::find($branch->manager_id)->update(['status' => 1]);
			$type = 'Deactivate';
			$msg = 'Branch has deactivated  by '.Auth::user()->username;
			$new_data = ['status' => 0];
			$old_data = ['status' => 1];
		} else {
			$branch->update(['status' => 1]);
			User::find($branch->manager_id)->update(['status' => 0]);
			$type = 'Active';
			$msg = 'Branch has activated  by '.Auth::user()->username;
			$new_data = ['status' => 1];
			$old_data = ['status' => 0];
		}
		$arrs = [
			"branch_id" => $branch->id,
			"ip_address" => $_SERVER['REMOTE_ADDR'],
			"title" => $type,
			"description" => $msg,
			"created_by" => 1,
			"old_data" => json_encode($old_data),
			"new_data" => json_encode($new_data),
			"created_by_id" => Auth::user()->id,			
		];
		BranchLog::insert($arrs);		
		return back()->with('success', $msg . " Successfully!");
	}

	public function changePassword ( Request $request )
	{
		$validator = Validator::make($request->all(), [
			'branch' => 'required',
			'password' => 'required|required_with:password_confirmation|string|confirmed',
		]);

		if ($validator->fails()) {
			return back()->with('alert', $validator->messages()->messages()['password'][0]);
		}
		
		if ( $request->password ) {
			$user = User::find( Branch::find($request->branch)->manager_id);
			$user->password = Hash::make($request->password);
			$user->update();			
			$arrs = [
				"branch_id" => $request->branch,
				"ip_address" => $_SERVER['REMOTE_ADDR'],
				"title" => 'Password Change',
				"description" => "Password has changed by " . Auth::user()->username,
				"created_by" => 1,
				"created_by_id" => Auth::user()->id,
			];
			BranchLog::insert($arrs);
			return redirect('admin/branch')->with('success', 'Branch Password Updated Successfully!');
		} else {
			return back()->with('alert', 'Problem With Updating');
		}
	}

	public function password ( $branchId )
	{
		$data['title']='Branch Password Change';
		$data['branchId'] = $branchId;
		return view('templates.admin.branch.password', $data);
	}

	/**
	 * @param Request $request
	 * update branch
	 * @return \Illuminate\Http\RedirectResponse
	 */
    public function updateAllBranches( Request $request )
    {
	    $action = $request->activate_deactive_all;
		$status = (int)(!$action);
		$branch = Branch::query()->update(['status' => $action]);
		$user = User::where('role_id', 3)->update(['status' => $status]);
		$des = ' All branches has deactivated by ' . Auth::user()->username;		
		$title = ' Branches  Deactivate';
		if ($action == 1) {
			$title = 'Branches  Active';
			$des = 'All branches has activated by ' . Auth::user()->username;
		}
		$new_data = ['status'=>1];
		$arrs = [
			"branch_id" => 0,
			"ip_address" => $_SERVER['REMOTE_ADDR'],
			"title" => $title,
			"description" => $des,
			"created_by" => 1,
			"old_data" => null,
			"new_data" => json_encode($new_data),
			"created_by_id" => Auth::user()->id,
		];
		BranchLog::insert($arrs);
		return back()->with('success', 'Successfully updated all branches!');
    }
    
    
    public function checkEmail(Request $request)
	{
		$branch_email = Branch::where('email', $request->email)->pluck('email')->first();
			if ( $branch_email ) {
				return array( 'status'=>true );
			} else {
				return array( 'status'=>false );
			}
	}

	public function checkPhone(Request $request)
	{
		$branch_phone = Branch::where('phone', $request->phone)->pluck('phone')->first();
			if ($branch_phone) {
				return array( 'status'=>true );
			} else {
				return array( 'status'=>false );
			}
	}
	public function branchLogs(Request $request)
	{
		
		if ($request->ajax()) {
			$data = BranchLogs::with('getBranchLogs')->orderBy('id', 'desc')->get();
			return Datatables::of($data)
			        ->addIndexColumn()

					->addColumn('created_at', function($row){				
						return date("d/m/Y H:i:s", strtotime( $row['created_at']));
					})
					->addColumn('branch_name', function($row){		
						return $row['getBranchLogs']->name;
					})
					->addColumn('branch_code', function($row){
						return $row['getBranchLogs']->branch_code;
					})
					->addColumn('ip_address', function($row){
						return $row['ip_address'];
					})
					->addColumn('message', function($row){
						return $row['message'];
					})
					->make(true);
		}
		if(check_my_permission( Auth::user()->id,"67") != "1"){
			return redirect()->route('admin.dashboard');
		}
		$data['title']='Branches Logs';
		$data['branchlogs'] = BranchLogs::with('getBranchLogs')->orderBy('id', 'desc')->get();
		//dd($data['branchlogs']);
		return view('templates.admin.branch.branchlogs', $data);
	}

	/**  Assigend Branch Model */

	public function AssigendBranchModel(Request $request){
		$branch = Branch::FindOrFail($request->branch_id);
		$allcompany = DB::table('companies')->where('status','1')->get();
		$html = view('templates.admin.branch.assigendbranchmodel')->with(['branch'=>$branch,'allcompany'=>$allcompany])->render();
		return response()->json(['status' => true, 'html' => $html], 200);
	}

	/** Assigend Branch */
	public function AssigendBranch(Request $request){
		// if(!empty($request->branch_id) && !empty($request->company)){	
		
		// 	$company = explode(',',$request->company);
		// 	$oldbuss = explode(',',str_replace('old_','',$request->oldvalues));
		// 	$newbuss = explode(',',str_replace('new_','',$request->newvalues));
		// 	$primary = explode(',',str_replace('primary_','',$request->primary));
			
		// 	$checkallradyassigend = \App\Models\CompanyBranch::where('branch_id',$request->branch_id)->where('status','1')->pluck('company_id');
		// 	//dd($checkallradyassigend);
			
		// 	//Update Last Record Uncheked
		// 	if(!empty($checkallradyassigend)){
		// 		foreach($checkallradyassigend as $key => $assigend){
		// 			if(!in_array($assigend, $company)){
		// 				\App\Models\CompanyBranch::where('branch_id',$request->branch_id)->where('company_id',$assigend)->where('status','1')->update(['status'=>'0']);
		// 			}
		// 		}
		// 	}

		// 	if(!empty($company)){
				
		// 		foreach($company as $key => $com){
		// 			//print_r($primary);
		// 			if(!in_array($com, $checkallradyassigend->toArray())){
						
		// 				$updatecompany = \App\Models\CompanyBranch::where('branch_id',$request->branch_id)->where('company_id',$com)->first();
		// 				// if(!empty($updatecompany)){
		// 				// 	$updatecompany = \App\Models\CompanyBranch::where('branch_id',$request->branch_id)->where('company_id',$com)->update(['is_primary'=>Null]);
		// 				// }
		// 				if(empty($updatecompany)){
		// 					$updatecompany = new \App\Models\CompanyBranch();
		// 				}
						
		// 				$updatecompany->branch_id = $request->branch_id;
		// 				$updatecompany->company_id = $com;
		// 				$updatecompany->is_old_business = in_array($com, array_unique($oldbuss)) ? '1' : '0';
		// 				$updatecompany->is_new_business =  in_array($com, array_unique($newbuss)) ? '1' : '0';
		// 				$updatecompany->is_primary = in_array($com, array_unique($primary)) ? 1 : 0;
		// 				$updatecompany->created_by = 1;
		// 				$updatecompany->created_by_id = Auth::user()->id;
		// 				$updatecompany->status = '1';
		// 				//dd($updatecompany);
		// 				$updatecompany->save();
		// 			}else{
		// 				$updatecheckcompany = \App\Models\CompanyBranch::where('branch_id',$request->branch_id)->where('company_id',$com)->first();
						
		// 				if(empty($updatecompany)){
		// 					$updatecompany = new \App\Models\CompanyBranch();
		// 				}
						
		// 				$updatecheckcompany->branch_id = $request->branch_id;
		// 				$updatecheckcompany->company_id = $com;
		// 				$updatecheckcompany->is_old_business = in_array($com, array_unique($oldbuss)) ? '1' : '0';
		// 				$updatecheckcompany->is_new_business =  in_array($com, array_unique($newbuss)) ? '1' : '0';
		// 				$updatecheckcompany->is_primary = in_array($com, array_unique($primary)) ? 1 : 0;
		// 				$updatecheckcompany->created_by = 1;
		// 				$updatecheckcompany->created_by_id = Auth::user()->id;
		// 				$updatecheckcompany->status = '1';
						
		// 				$updatecheckcompany->save();
		// 			}
		// 		}
		// 	}else{
		// 		dd('asd');
		// 	}
		// 	return response()->json(['success' => true, 'message' => "Companies Assigned Successfully!"], 200);
		// }
		DB::beginTransaction();
		try{
			if (!empty($request->branch_id) && !empty($request->company)) {
				$company = explode(',', $request->company);
				$oldbuss = explode(',', str_replace('old_', '', $request->oldvalues));
				$newbuss = explode(',', str_replace('new_', '', $request->newvalues));
				$primary = explode(',', str_replace('primary_', '', $request->primary));
				$new_data = [
					'primary' => array_map('intval', $primary),
					'is_new_business' => array_map('intval', $newbuss),
					'is_old_business' => array_map('intval', $oldbuss),
				];
				$old_data = [
					'primary' => CompanyBranch::where('branch_id',$request->branch_id)->whereStatus('1')->whereIsPrimary('1')->pluck('company_id')->toArray(),
					'is_new_business' => CompanyBranch::where('branch_id',$request->branch_id)->whereStatus('1')->where('is_new_business','1')->pluck('company_id')->toArray(),
					'is_old_business' => CompanyBranch::where('branch_id',$request->branch_id)->whereStatus('1')->where('is_old_business','1')->pluck('company_id')->toArray(),
				];
				$checkallradyassigend = CompanyBranch::where('branch_id', $request->branch_id)->where('status', '1')->pluck('company_id');
				//Update Last Record Uncheked
				if (!empty($checkallradyassigend)) {
					foreach ($checkallradyassigend as $key => $assigend) {
						if (!in_array($assigend, $company)) {
							CompanyBranch::where('branch_id', $request->branch_id)->where('company_id', $assigend)->where('status', '1')->update(['status' => '0']);
						}
					}
				}
				if (!empty($company)) {
					foreach ($company as $key => $com) {
						if (!in_array($com, $checkallradyassigend->toArray())) {
							$updatecompany = CompanyBranch::where('branch_id', $request->branch_id)->where('company_id', $com)->first();
							if (empty($updatecompany)) {
								$updatecompany = new CompanyBranch();
							}
							$updatecompany->branch_id = $request->branch_id;
							$updatecompany->company_id = $com;
							$updatecompany->is_old_business = in_array($com, array_unique($oldbuss)) ? '1' : '0';
							$updatecompany->is_new_business =  in_array($com, array_unique($newbuss)) ? '1' : '0';
							$updatecompany->is_primary = in_array($com, array_unique($primary)) ? 1 : 0;
							$updatecompany->created_by = 1;
							$updatecompany->created_by_id = Auth::user()->id;
							$updatecompany->status = '1';
							$updatecompany->save();
						} else {
							$updatecheckcompany = CompanyBranch::where('branch_id', $request->branch_id)->where('company_id', $com)->first();
							if (empty($updatecompany)) {
								$updatecompany = new CompanyBranch();
							}
							$updatecheckcompany->branch_id = $request->branch_id;
							$updatecheckcompany->company_id = $com;
							$updatecheckcompany->is_old_business = in_array($com, array_unique($oldbuss)) ? '1' : '0';
							$updatecheckcompany->is_new_business =  in_array($com, array_unique($newbuss)) ? '1' : '0';
							$updatecheckcompany->is_primary = in_array($com, array_unique($primary)) ? 1 : 0;
							$updatecheckcompany->created_by = 1;
							$updatecheckcompany->created_by_id = Auth::user()->id;
							$updatecheckcompany->status = '1';
							$updatecheckcompany->save();
						}
					}					
					$arrs = [
						"branch_id" => $request->branch_id,
						"ip_address" => $_SERVER['REMOTE_ADDR'],
						"title" => 'Company Assign',  
						"description" => 'Branch company assignment has modified by ' . (Auth::user()->username),  
						"created_by_id" => Auth::user()->id,
						"created_by" => 1, // Admin upddate's
						"old_data" => json_encode($old_data),
						"new_data" => json_encode($new_data),
					];
					// this will store data in logs_branch table 
					BranchLog::insert($arrs);
				}
			}
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			dd($ex->getMessage());
		}
		return response()->json(['success' => true, 'message' => "Companies Assigned Successfully!"], 200);
	}

	public static function CompanyDataGet($brnach_id,$company_id){
		$compnaybrnach = CompanyBranch::where('branch_id',$brnach_id)->where('company_id',$company_id)->where('status','1')->select('company_id','branch_id','is_primary','is_new_business','is_old_business')->first();
		return $compnaybrnach;
	}

	public function CompanyBranchView($id){
		$branch = Branch::FindOrFail($id);
		$title = "Bank Branch View";
		return view('templates.admin.branch.assignviewcompany',compact('branch','title'));
	}

	public static function CompanyLocation($id,$type){
		if($type == 'country'){
			$res = DB::table('country')->where('id',$id)->value('name');
		}
		if($type == 'city'){
			$res = DB::table('cities')->where('id',$id)->value('name');
		}
		if($type == 'state'){
			$res = DB::table('states')->where('id',$id)->value('name');
		}
		return $res;
	}

	/* branch Otp Setting 
	*By Durgesh 
	*15-Sep-2023
	*/

	public function BranchStatus(Request $request)
	{
		$updated = Branch::query()->update(['otp_login' => $request->otp_status]);
		$statusId = $request->otp_status;
		if ($updated) {			
			$otp = "OTP On";
			$title = "OTP of all branches has been turned on by " . Auth::user()->username;
			if ($statusId == 1) {
				$title = "OTP of all branches has been turned off by " . Auth::user()->username;
				$otp = "OTP Off ";
			}
			$branchStatus = [
				"branch_id" => 0,
				"ip_address" => $_SERVER['REMOTE_ADDR'],
				"description" => $title,
				"title" => $otp,
				"created_by" => '1',
				"old_data" => null,
				"new_data" => json_encode(['otp_login'=>$statusId]),
				"created_by_id" => Auth::user()->id,
			];
			BranchLog::insert($branchStatus);
			return response()->json(['data' => true]);
		} else {
			return response()->json(['data' => false]);
		}
	}

	/* branch name update 
	*By Durgesh 
	*15-Sep-2023
	*/
	public function BranchUpdate(Request $request)
	{
		$change = 0;
		if (!empty($request->branch_id) && !empty($request->branch_name)) {
			$checkName = Branch::where('name', trimData($request->branch_name))->where('id', '!=', $request->branch_id)->count('id');
			if ($checkName == 0) {
				$branch = Branch::find($request->branch_id);
				$oldBranchName = $branch->name;
				if (!empty($branch)) {
					$branch->update(['name' => trimData($request->branch_name)]);
				}
				$user = User::find($branch->manager_id);
				if (!empty($user)) {
					$user->update([
						'username' => trimData($request->branch_name),
						'name' => trimData($request->branch_name)
					]);
				}				
				$branchName = [
					"branch_id" => $request->branch_id,
					"ip_address" => $_SERVER['REMOTE_ADDR'],
					"title" => 'Change Name',
					"description" => "Branch Name Changed by " . Auth::user()->username,
					"old_data" => json_encode($oldBranchName),
					"new_data" => json_encode($request->branch_name),
					"created_by" => 1,
					"created_by_id" => Auth::user()->id,
				];
				BranchLog::insert($branchName);
				$change = 1;
			} else {
				$change = 2;
			}
		}
		return  $change;
	}
	public function BranchLimitChange(Request $request)
	{
		if (check_my_permission(Auth::user()->id, "313") != "1") {
            return redirect()->route('admin.dashboard');
        }
		$title = "Branch Management | Branch Cash Limit";
		$branch = Branch::where('status', 1)->select('id', 'name', 'cash_in_hand')->orderBy('name','ASc')->get();

		return view('templates.admin.branch.branch_cash_limit', compact('title', 'branch'));
	}
	public function branch_log(Request $request){
		if (check_my_permission(Auth::user()->id, "314") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Branch Management | Branch Log Details';
		$data['branch'] = Branch::pluck('name','id');
		return view('templates.admin.branch.update_log', $data);
	}
	public function branch_Balance_crone(){
		Artisan::call('branchdaily:balance');
		return "Branch Balance Crone run successfully !";
	}
	public function branch_log_filter(Request $request){
		$id = $request->branchId;
		$code = $request->branchCode;
		$branchCode = Branch::pluck('id','branch_code');
		$branch = Branch::whereId($id);	
		if(!isset($code) && !isset($id)){
			return \Response::json(['msg_type' => 'error','msg'=>'Please select Branch first!']);
		}	
		$log = BranchLog::where('branch_id', $id);
		if(isset($branchCode[$code])){
			$branch = Branch::whereNotNull('id')->where('branch_code',$code);
			$log = BranchLog::where('branch_id', $branchCode[$code]);
		}
		if(!isset($id) && !(isset($branchCode[$code]))){
			return \Response::json(['msg_type' => 'error','msg'=>'Please enter currect branch code !']);
		}
		$data['branch'] = $branch->get()->toArray();
		$data['log'] = $log->orderby('created_at', 'DESC')->get();
		$data['all'] = ($id) ? (($id==0) ? false : true) : ($branch ? true : false);
		// pd($data['branch']);
		return \Response::json(['view' => view('templates.admin.branch.log', $data)->render(), 'msg_type' => 'success','msg'=>'']);
	}
	public function Branchlimitupdate(Request $request)
	{
		foreach ($request->total_amount as $k => $v) {
			if ($v != null) {
				$branchId = $request->branch_id[$k];
				$branch = Branch::whereId($branchId)->select('id','cash_in_hand','manager_id','branch_code')->first();
				$ob = $branch->cash_in_hand;
				$logArray = [
					"branch_id" => $branchId,
					"ip_address" => $_SERVER['REMOTE_ADDR'],
					"title" => 'Cash Limit Change',
					"description" => "Cash limit for all branches has set to " . ' (' . $v . ') ' . ' by ' . Auth::user()->username,
					"old_data" => 'old cash limit value' . '(' . $ob . ')',
					"new_data" => 'new cash limit value ' . '(' . $v . ')',
					"created_by" => 1,
					"created_by_id" => Auth::user()->id,
				];
				BranchLog::insert($logArray);
				$branch->update(['cash_in_hand'=>$v]);
				branchbalanceInableOrDescablecrone($branch->manager_id, Permission::all());
			}
		}		
		return redirect()->back()->with('success', 'Cash limit updated successfully!');
	}
	public function branchbalanceInableOrDescablecrone($id){
		$p = Permission::all();
		branchbalanceInableOrDescablecrone($id,$p);
	}
	/**
	 * this function modyfy by Sourab on 28-09-2023
	 * this is use to change all cash in hand account
	 * in branch table and give all perrmission to all
	 * branches as per 0 case in hand amount as well
	 * store record in logs_branch table.
	 */
	public function BranchRemoveCash(Request $request)
	{
		$permission = Permission::pluck('name')->toArray();	
		$updateLog = false;
		$log = [
			"branch_id" => 0,
			"ip_address" => $_SERVER['REMOTE_ADDR'],
			"description" => 'Cash limit for all branches has set to zero (0)',
			"title" => 'Cash Limit Zero (0)',
			"created_by" => '1',
			"old_data" => null,
			"new_data" => json_encode(['cash_in_hand'=>0]),
			"created_by_id" => Auth::user()->id,
		];
		DB::beginTransaction();
		try{
			// Update cash_in_hand for all branches in one query
			Branch::query()->update(['cash_in_hand' => 0]);
			// Fetch manager IDs of all branches at once where id not null
			Branch::select('id','manager_id')->each(function ($branch) use ($permission) {
				// foreach($branches as $branch){
					// Assuming the authenticated user is an instance of the User model	and getting data from User Table where id equal to branch table menager id	
					$authUser = User::findOrFail($branch->manager_id);  
					// give all permission to auth user (branch) if cash in hand amount is zero
					$authUser->syncPermissions($permission); 
				// }
			});
			$updateLog = BranchLog::insert($log);
			// commit complete data after getting all
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			$updateLog = false;
			dd($ex->getMessage(),$ex->getLine());
		}
		return response()->json(['data' => ($updateLog) ? true : false]);
	}
	//  Branch Limit Change page
}
