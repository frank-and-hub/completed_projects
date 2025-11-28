<?php



namespace App\Http\Controllers\Admin;


use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validator;

use App\Models\User;

use App\Models\Settings;

use App\Models\Logo;

use App\Models\Save;

use App\Models\Branch;

use App\Models\Loan;

use App\Models\Bank;

use App\Models\Currency;

use App\Models\Alerts;

use App\Models\Transfer;

use App\Models\Int_transfer;

use App\Models\Plans;

use App\Models\Adminbank;

use App\Models\Gateway;

use App\Models\Deposits;

use App\Models\Banktransfer;

use App\Models\Withdraw;

use App\Models\Withdrawm;

use App\Models\Merchant;

use App\Models\Profits;

use App\Models\Social;

use App\Models\About;

use App\Models\Faq;

use App\Models\Page;

use App\Models\Contact;

use App\Models\Ticket;

use App\Models\Reply;

use App\Models\Review;

use App\Models\Chart;

use App\Models\Asset;

use App\Models\Exchange;

use App\Models\Buyer;

use App\Models\Seller;

use App\Models\Exttransfer;

use App\Models\Member; 

use App\Models\SamraddhBank; 

use App\Models\SsbAccountSetting;

use Carbon\Carbon;

use Image;
use Yajra\DataTables\DataTables;


use DB;

use Illuminate\Support\Facades\Hash;

use App\Models\Admin; 

use App\Models\Userpermission; 

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\RoleHasPermission;







class SsbController extends Controller

{


	 public function register(){	
		if(check_my_permission( Auth::user()->id,"250") != "1"){
			return redirect()->route('admin.dashboard');
		}  
		$data['title']='SSB Account Setting | Create Setting';
		return view('templates.admin.ssb_account.add',$data);
	}
	
	
	
	

	public function save(Request $request){
		
		$validator = Validator::make($request->all(), [
			'amount' => 'required|numeric|gt:0',
			'plan_type' => 'required',
			'user_type' => 'required',
		]);
		
		if ($validator->fails()) {
			return redirect('admin/ssbaccount-register')
				->withErrors($validator)
				->withInput();
		}
		$cnt =SsbAccountSetting::where('plan_type',$request->plan_type)
		->where('user_type',$request->user_type)
		->where('amount',$request->amount)
		->count();
		if($cnt == 0){
			$data['amount'] = $request->amount;
			$data['plan_type'] = $request->plan_type;
			$data['user_type'] = $request->user_type;
			//dd($data);
			$res = SsbAccountSetting::create($data);
			
			
			if($res){
				return redirect()->route('admin.ssbaccount.register')->with('success', 'SSB Account Setting Created Successfully!');
			} 
			else{
				return back()->with('alert', 'Problem With Creating SSB Account');
			}
		}
		else{
			return back()->with('alert', 'SSB Account Setting Already with this Amount!');
		}
	}
	
	public function ssbaccountdetailsmain(Request $request)
	{
		//dd($request->all());
		
		return redirect('admin.ssbaccount.ssbaccountdetails');
	}
	
	
	public function ssbaccountdetails(Request $request){	
		if(check_my_permission( Auth::user()->id,"251") != "1"){
			return redirect()->route('admin.dashboard');
		} 
		if(check_my_permission( Auth::user()->id,"76") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title'] = "SSB Account Setting | Activate Setting";	   
		return view('templates.admin.ssb_account.ssbaccount-listing', $data);

	}
	public function activatesetting(Request $request){	
		
		
		if(isset($_POST['update'])){
			$status_id = $request->status;
			$user_type = $request->user_type;
			$plan_type = $request->plan_type;
			$data = SsbAccountSetting::where('id',$status_id)->first();
			$data->status = 1;
			if($data->save()){
				
				$data = SsbAccountSetting::where('id','!=',$status_id)->where('user_type','=',$user_type)->where('plan_type','=',$plan_type);
				$data->update(['status'=>0]);
				
				return back()->with('success', 'Update was Successful!');
			}
			else{
				return back()->with('alert', 'An error occured');
			}

		}

	}
	
	public function ssbaccountdetailssearch(Request $request){	
		if ($request->ajax()) {
			
			$data = SsbAccountSetting::where('user_type',$request->user_type)->where('plan_type',$request->plan_type)->orderBy('id','desc')->get()->toArray();
			if(!empty($data)){
				$table = '';
				$i = 1;
				foreach($data as $value){
					$table .= "<tr>";
					$table .= "<td>".$i."</td>";
					$table .= "<td>".date("d/m/Y H:i:s",strtotime($value['created_at']))."</td>";
					$ptype = "Ssb Child";
					if($value['plan_type'] == 1){
						$ptype = "Ssb";
					}
					$table .= "<td>".$ptype."</td>";

					$user_type = "Associate";
					if($value['user_type'] == 1){
						$user_type = "Member";
					}
					$table .= "<td>".$user_type."</td>";
					$table .= "<td>".$value['amount']."</td>";
					$checked = $value['status'] == 1 ? 'checked' : "";
					$table .= "<td><input type='radio' name='status' $checked value='".$value['id']."'></td>";
					$table .= "</tr>";
					$i++;
				}
				$table .= "<input type='hidden' name='user_type' value='".$request->user_type."'>";
				$table .= "<input type='hidden' name='plan_type' value='".$request->plan_type."'>";
				echo $table;
			}
			else{				
				echo "error";
			}
			//return json_encode($data);		
		}
	}

}

