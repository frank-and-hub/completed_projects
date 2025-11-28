<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SavingAccountTranscation; 
use App\Models\Branch; 
class TranscationController extends Controller
{
	public function transcationdetails(Request $request){	
		if(check_my_permission( Auth::user()->id,"253") != "1"){
		  return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Transaction search";	   
		return view('templates.admin.transcation_search.transcationsearch-listing', $data);
	}
	public function transcationdetailssearch(Request $request){	
		//$tdetail = $request->all();
		if ($request->ajax()) {
		  	if($request->plan_type==1)
			{               
				$data = SavingAccountTranscation::has('company')->where('id',$request->transcation_id)->first(); 
				if(!is_null($data))
				{ 
				$ssbAcDetail = \App\Models\SavingAccount::has('company')->with('ssbMember')->where('id',$data->saving_account_id)->first();
			    $databranch = Branch::where('id',$ssbAcDetail->branch_id)->first();
				//dd($ssbAcDetail);
				return \Response::json(['view' => view('templates.admin.transcation_search.partials.ssbTransctionDetail' ,['tDetails' => $ssbAcDetail,'data'=>$data,'databranch'=>$databranch])->render(),'msg_type'=>'success']);
				}
				else
				{
					$databranch= '';
					$ssbAcDetail= '';
					return \Response::json(['message'=>'No Record Found']);
				}
					}
			else if($request->plan_type==2)
			{     
             $datasecond =  \App\Models\Daybook::has('company')->where('id',$request->transcation_id)->first(); 
			 //dd( $datasecond);
         if(!is_null($datasecond))
				{
				 $data = \App\Models\Daybook::has('company')->with(['investment'])->where('id',$request->transcation_id)->first(); 
				//dd($data);
				  $databranch = Branch::where('id',$data->branch_id)->first();
				  	//dd($databranch);
				return \Response::json(['view' => view('templates.admin.transcation_search.partials.investmentTransctionDetail' ,['tDetails' => $data,'databranch'=>$databranch,'datasecond'=>$datasecond])->render(),'msg_type'=>'success']);
				}
				else
				{
					$data= '';
					$databranch= '';
					return \Response::json(['message'=>'No Record Found']);
				}
			}
			else if($request->plan_type==3)
			{
                  $data = \App\Models\LoanDayBooks::has('company')->where('id',$request->transcation_id)->first(); 
				  //dd($data);
                if(!is_null($data))
				{
					$memDetail ='';
					if($data->loan_type==3)
					{
						$memDetail=\App\Models\Grouploans::has('company')->with(['loanMember','loanMemberAssociate'])->where('id',$data->loan_id)->first(); 
						//dd($memDetail);
					}
					else{
						$memDetail=\App\Models\Memberloans::has('company')->with(['loanMember','loanMemberAssociate'])->where('id',$data->loan_id)->first(); 
						//dd($memDetail);
					}
						$databranch = Branch::where('id',$data->branch_id)->first();
				//dd($databranch);
				return \Response::json(['view' => view('templates.admin.transcation_search.partials.loanTransctionDetail' ,['tDetails' => $data,'databranch'=>$databranch,'memDetail'=>$memDetail])->render(),'msg_type'=>'success']);
				  	}
				else
				{
					$data= '';
					$databranch= '';
					return \Response::json(['message'=>'No Record Found']);
				}
			}
		}
	}
}