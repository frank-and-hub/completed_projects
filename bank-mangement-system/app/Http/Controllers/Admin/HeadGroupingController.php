<?php 
namespace App\Http\Controllers\Admin; 

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AccountHeads;
use App\Models\Member; 
use App\Models\HeadLog;

use App\Models\Companies;
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

class HeadGroupingController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index()
    {
		if(check_my_permission( Auth::user()->id,"144") != "1"){
		  return redirect()->route('admin.dashboard');
		}			
		$data['title']='Head Grouping'; 
		$data['account_heads'] = AccountHeads::select('id','head_id','sub_head','parent_id','labels')->where('labels','>',1)->where('status',0)->get();

        return view('templates.admin.head_grouping.index', $data);
    }
	
	
	
	// public function get_sub_head_using_head(Request $request)
    // { 
	// 	if($request->head_type == "1"){
			
	// 		// Balance sheet head
	// 		$head_id_main = array('1','2');
			
	// 	} elseif($request->head_type == "2") {
			
	// 		// Profilt and loss head
	// 		$head_id_main = array('3','4');
			
	// 	}
		
	// 	if( count($head_id_main) > 0 ){
	// 		// Now get child of that head
	// 		$subHeadsIDS = AccountHeads::whereIn('parent_id',$head_id_main)->where('status',0)->where('labels','>',1)->pluck('head_id')->toArray();
			
	// 		$head_ids = array();
	// 		if( count($subHeadsIDS) > 0 ){
				
	// 			$return_array = $this->get_change_sub_account_head_main_head($head_ids,$subHeadsIDS,true);
	// 			return json_encode($return_array); die;
				
	// 		} else {
				
	// 			$account_heads = AccountHeads::whereIn('head_id',$head_ids)->where('status',0)->get();
	// 			$return_array = compact('account_heads');
	// 			return json_encode($return_array); die;
	// 		}
	// 	}
	// }

	public function get_sub_head_using_head(Request $request)
    { 
		// if($request->head_type == "1"){
			
		// 	// Balance sheet head
		// 	$head_id_main = array('1','2');
			
		// } elseif($request->head_type == "2") {
			
		// 	// Profilt and loss head
		// 	$head_id_main = array('3','4');
			
		// }
		
		// if( count($head_id_main) > 0 ){
			// Now get child of that head
			// $subHeadsIDS = AccountHeads::whereIn('parent_id',$head_id_main)->where('status',0)->where('labels','>',1)->pluck('head_id')->toArray();
			$subHeadsIDS = AccountHeads::where('parent_id',$request->head_type)->where('status',0)->where('labels','>',1)->pluck('head_id')->toArray();
			
			$head_ids = array();
			if( count($subHeadsIDS) > 0 ){
			
				$return_array = $this->get_change_sub_account_head_main_head($head_ids,$subHeadsIDS,true);
				return json_encode($return_array); die;
				
			} else {
				
				
				$account_heads = AccountHeads::whereIn('head_id',$head_ids)->where('status',0)->get();
				$return_array = compact('account_heads');
				return json_encode($return_array); die;
			}
		// }
	}
	
	public function get_comapny_details(Request $request){
	

		$mainHeads = AccountHeads::where('head_id',$request->head_id)->first();
		
		$mainHeadId = AccountHeads::where('head_id',$mainHeads->parent_id)->first();
		$mainHead = $mainHeadId->sub_head;
		$comapies = json_decode($mainHeads->company_id);
	
		$mainComapny = Companies::whereIn('id',$comapies)->get();
		return response()->json(['mainHeadId' => $mainHead,'mainComapny'=>$mainComapny]);


	}
	
	
	public function get_change_sub_account_head_main_head($head_ids,$subHeadsIDS,$is_level)
    {
		if($is_level == false){
			
			$account_heads = AccountHeads::whereIn('head_id',$head_ids)->where('status',0)->get();
				
			$return_array = compact('account_heads');
			
			echo json_encode($return_array); die;
			
		} else {
				
			$subHeadsIDS2 = AccountHeads::whereIn('parent_id',$subHeadsIDS)->where('status',0)->pluck('head_id')->toArray();
			
			if( count($subHeadsIDS2) > 0 ){
				
				$head_ids =  array_merge($head_ids,$subHeadsIDS2);
				
				$this->get_change_sub_account_head_main_head($head_ids,$subHeadsIDS2,true);
				
			} else {
				
				$this->get_change_sub_account_head_main_head($head_ids,$subHeadsIDS,false);
			}
			
		}
	}
	
	
	public function get_change_sub_head(Request $request)
    {
		if( isset($request->head_id) && $request->head_id!= ""){
			$head_id = $request->head_id;
			
			$head_ids = array($head_id);
			
			// Now get child of that head
			$subHeadsIDS = AccountHeads::where('parent_id',$head_id)->where('status',0)->where('labels','>',1)->pluck('head_id')->toArray();
			
			if( count($subHeadsIDS) > 0 ){
				$head_ids =  array_merge($head_ids,$subHeadsIDS);
				
				$return_array = $this->get_change_sub_account_head($head_ids,$subHeadsIDS,true);
				return json_encode($return_array); die;
				
			} else {
				
				$account_heads = AccountHeads::select('id','head_id','sub_head','parent_id','labels')->whereNotIn('head_id',$head_ids)->where('status',0)->get();
				$return_array = compact('account_heads');
				return json_encode($return_array); die;
			}
			
		}
		
    }
	
	
	
	public function get_change_sub_account_head($head_ids,$subHeadsIDS,$is_level)
    {
		if($is_level == false){
			
			$account_heads = AccountHeads::whereNotIn('head_id',$head_ids)->where('status',0)->get();
				
			$return_array = compact('account_heads');
			
			echo json_encode($return_array); die;
			
		} else {
				
			$subHeadsIDS2 = AccountHeads::whereIn('parent_id',$subHeadsIDS)->where('status',0)->pluck('head_id')->toArray();
			
			if( count($subHeadsIDS2) > 0 ){
				
				$head_ids =  array_merge($head_ids,$subHeadsIDS2);
				
				$this->get_change_sub_account_head($head_ids,$subHeadsIDS2,true);
				
			} else {
				
				$this->get_change_sub_account_head($head_ids,$subHeadsIDS,false);
			}
			
		}
	}
	
	
	
	public function change_account_head_position(Request $request)
    {
		$requests = $request->all();

		$companiesidParent = AccountHeads::where('head_id',$request->account_head)->first();
		$companiesidHead = AccountHeads::where('head_id',$request->change_account_head)->first();
	
		$parentHeadCompaniesIds = $companiesidParent->company_id;
		$headCompaniesIds = $companiesidHead->company_id;
		$companyIdsArray = json_decode($headCompaniesIds);
		$parentCompanyIdsArray = json_decode($parentHeadCompaniesIds);
	
		$mainCompanies = Companies::whereIn('id', $companyIdsArray)->get();
		$parentHeadCoapnies = Companies::whereIn('id', $parentCompanyIdsArray)->get();
	

		$parentHeadCompaniesIds = json_decode($parentHeadCompaniesIds, true);
		$headCompaniesIds = json_decode($headCompaniesIds, true);

		// dd($mainCompanies,$parentHeadCoapnies );

		$intersection = array_intersect($headCompaniesIds, $parentHeadCompaniesIds);

		if (empty($intersection)) {
			$return_array = array("status" => "0", "position Not updated",'mainCompanies'=>$mainCompanies,'parentHeadCoapnies'=>$parentHeadCoapnies);
			echo json_encode($return_array); die;
		}
		
		if( isset($request->account_head) && $request->account_head!= "" && isset($request->change_account_head) && $request->change_account_head!= "" ){
			$account_head = $request->account_head;
			$change_account_head = $request->change_account_head;
			
			
			// Now check that change head has an parvious head id
				$isPreviousParentId = 0;
				$currentLevel = 0;
				$changeHeadIDHeadID = 0;
				$changeHeadIDHeadParentID = 0;
					
				$checkPreviousParentHeadID = AccountHeads::where('head_id',$change_account_head)->get()->toArray();
				if( (count($checkPreviousParentHeadID) > 0) && ($checkPreviousParentHeadID[0]["previous_parent_id"] > 0) ){
					$isPreviousParentId = 1;
				}
				$currentLevel = $checkPreviousParentHeadID[0]["labels"];
				$changeHeadIDHeadID = $checkPreviousParentHeadID[0]["head_id"];
				$changeHeadIDHeadParentID = $checkPreviousParentHeadID[0]["parent_id"];
				
				// IF Change has not previous parent ID then get level 1 head_id
				$finalPreviousParentID = $changeHeadIDHeadParentID;
				for($i=$currentLevel; $i>0; $i--){
					$checkPreviousParentHeadID1 = AccountHeads::where('head_id',$changeHeadIDHeadParentID)->get()->toArray();
					if( (count($checkPreviousParentHeadID1) > 0) && ($checkPreviousParentHeadID1[0]["parent_id"] > 0)  ){
						$changeHeadIDHeadParentID = trim($checkPreviousParentHeadID1[0]["parent_id"]);
						if($checkPreviousParentHeadID1[0]["parent_id"] > 0){
							$finalPreviousParentID = trim($checkPreviousParentHeadID1[0]["parent_id"]);
						}
					}
				}
			// End check that change head has an parvious head id
			
			
			// Now For Get Current Head ID Level1 id
				$getCurrentParentHeadID = AccountHeads::where('head_id',$account_head)->get()->toArray();
				$currentHeadIDHeadParentID = $getCurrentParentHeadID[0]["parent_id"];
				$currentLevelForCurrent = $getCurrentParentHeadID[0]["labels"];
				
				$finalCurrentParentID = $currentHeadIDHeadParentID;
				for($j=$currentLevelForCurrent; $j>0; $j--){
					$checkPreviousParentHeadID2 = AccountHeads::where('head_id',$currentHeadIDHeadParentID)->get()->toArray();
					if( (count($checkPreviousParentHeadID2) > 0) && ($checkPreviousParentHeadID2[0]["parent_id"] > 0)  ){
						$currentHeadIDHeadParentID = trim($checkPreviousParentHeadID2[0]["parent_id"]);
						if($checkPreviousParentHeadID2[0]["parent_id"] > 0){
							$finalCurrentParentID = trim($checkPreviousParentHeadID2[0]["parent_id"]);
						}
					}
				}
			// End Level 1 Head iD
			
			$getAccountLevel = AccountHeads::where('head_id',$account_head)->get()->toArray();
			
			if( count($getAccountLevel) > 0 ){
				
				$blankArr = array();
				
				$labels = $getAccountLevel[0]["labels"];
				$main_head_id = $getAccountLevel[0]["head_id"];
				$parentId_auto_id = $getAccountLevel[0]["id"];
				
				// Now Firstly Set Parent ID and lebel and then update
				$upgradLevel = (int)$labels + 1;
				$changeArr = array("parent_id" => $main_head_id, "labels" => $upgradLevel, "parentId_auto_id" => $parentId_auto_id, "current_parent_id" => $finalCurrentParentID);
				
				if($isPreviousParentId == 0){
					$changeArr["previous_parent_id"] = $finalPreviousParentID;
				}
				head_create_logs($checkPreviousParentHeadID[0]['head_id'] , $checkPreviousParentHeadID[0]['company_id'], $checkPreviousParentHeadID[0]['parent_id'], $type = 3,$checkPreviousParentHeadID[0]['sub_head'], $getCurrentParentHeadID[0]['sub_head'],null, $checkPreviousParentHeadID =null,$getCurrentParentHeadID[0]['head_id'],null,$request->systemdate);

				$updateLevel = AccountHeads::where('head_id',$change_account_head)->update($changeArr);
				
				// Now Add Their childs in new added childs
				$subHeadsIDS = AccountHeads::where('parent_id',$change_account_head)->where('status',0)->pluck('head_id')->toArray();
				
				if( count($subHeadsIDS) > 0 ){
					$upgradLevel = (int)$upgradLevel + 1;
					$return_array = $this->change_head_account_position($upgradLevel,$subHeadsIDS,true,$isPreviousParentId,$finalPreviousParentID,$finalCurrentParentID);
					$this->updateNewValues($request->change_account_head, $request->systemdate);
					return json_encode($return_array); die;
				} else {
					$checkPreviousParentHeadID = AccountHeads::where('head_id',$change_account_head)->first()->toArray();
				$this->updateNewValues($request->change_account_head,$request->systemdate);
					$return_array = array("status" => "1", "position updated successfully");
					echo json_encode($return_array); die;
				}
				
				
			}
			
		}
		
    }
	function updateNewValues($headId, $systemDate){

		// Fetch the AccountHead once
		$value = AccountHeads::where('head_id', $headId)->first();
	
		// Check if a log entry already exists
		$logDetail = HeadLog::where('head_id', $headId)->latest()->first();
		// $date = DateTime::createFromFormat("d/m/Y", $systemDate);
		// $formattedDate = $date->format("Y-m-d H:i:s");
		// $globaldate = Session::get('created_at');

		// Update or create the log entry
		if ($logDetail) {
			// $systemdates = Carbon::parse($systemDate)->setTime(now()->hour, now()->minute, now()->second);
            $systemdates = Carbon::createFromFormat('d/m/Y', $systemDate)->format('Y-m-d H:i:s');

			
			$logDetail->update([
				'new_value' => json_encode($value->toArray()),
				'created_at' =>  $systemdates,
				'updated_at' =>  $systemdates,
			]);
		} else {
			// If the log entry does not exist, create a new one
			
		}
	
	
			
		
	
	}
	
	public function change_head_account_position($level,$subHeadsIDS,$is_level,$isPreviousParentId,$finalPreviousParentID,$finalCurrentParentID)
    {
		if($is_level == false){

			$return_array = array("status" => "1", "position updated successfully");
			
			echo json_encode($return_array); die;
			
		} else {
			
			// Now Update Level on child
			$updateLevel = (int)$level;
			$updateLevelArr = array("labels" => $updateLevel,"current_parent_id" => $finalCurrentParentID);
			if($isPreviousParentId == 0){
				$updateLevelArr["previous_parent_id"] = $finalPreviousParentID;
			}
			$updateHeads = AccountHeads::whereIn('head_id',$subHeadsIDS)->update($updateLevelArr);
				
			$subHeadsIDS2 = AccountHeads::whereIn('parent_id',$subHeadsIDS)->where('status',0)->pluck('head_id')->toArray();
			
			if( count($subHeadsIDS2) > 0 ){
				$levels = (int)$updateLevel + 1;
				$this->change_head_account_position($levels,$subHeadsIDS2,true,$isPreviousParentId,$finalPreviousParentID,$finalCurrentParentID);
				
			} else {
				$this->change_head_account_position($level,$subHeadsIDS,false,$isPreviousParentId,$finalPreviousParentID,$finalCurrentParentID);
			} 
			
		}
	}
	
	
	
}
