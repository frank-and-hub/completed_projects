<?php 
namespace App\Http\Controllers\Admin; 


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountHeads;
use Illuminate\Support\Facades\Crypt;
use App\Models\Branch;
use App\Models\HeadClosing;
use App\Exports\TrailBalanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use carbon\Carbon;
use DB;
class TrailBalanceController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index(Request $request)
    {		
		/*if(check_my_permission( Auth::user()->id,"144") != "1"){
		  return redirect()->route('admin.dashboard');
		}*/		
        
        $data['title']='Trial Balance';   
        $data['branches'] = Branch::select('id','name')->where('status',1)
            ->get();
       
        return view('templates.admin.trailbalance.index', $data);
        
    }
    public function indexsub($data)
    {		
		/*if(check_my_permission( Auth::user()->id,"144") != "1"){
		  return redirect()->route('admin.dashboard');
		}*/		
        $Data = [];
        $request = decrypt($data);
        $Data['branch_id'] = $branch_id = $request['branch_id']??'';
        $Data['head_id'] = $request['head_id'];  
        $Data['financial_year'] = $request['financial_year'];  
        $Data['company_id'] = $request['company_id'];  
        $Data['name'] = $request['name'];  
        $Data['title']='Trial Balance';
        $Data['branches'] = Branch::select('id','name')->where('status',1)
            ->when($branch_id,function($q)use($branch_id){
                $q->whereId($branch_id);
            })
            ->get();
       
        return view('templates.admin.trailbalance.index', $Data);
        
    }
    

    public function getHeadClosingList(Request $request)
    { 
        
        $head_id = $request->head_id;
        $child_id = $request->child_id;
        $lebel = $request->lebel;
        $name = $request->name;
        $childIds = explode(',',$child_id);
        (array_push($childIds,$head_id));
        $childIds = array_unique($childIds);
        $financial_year = $request->financial_year;            
        $date=explode(' - ',$request->financial_year);
        $start_y=$date[0];
        $end_y=$date[1]; 
        $start_m=04;
        $start_d=01;
        $companyList = $company_id = $request->company_id;
        $arrayCompanyList = explode(' ', $companyList);
        $companyList = array_map(function ($value) {
            return intval($value);
        }, $arrayCompanyList);
        $end_m=03;
        $end_d=31;
        $branch_id=$request->branch;
        
        $totalCR=0;
        $totalDR=0;
        
        $newstartDate = $start_y-1;
        $newEndDate = $end_y-1;
        if($head_id)
        {
            $childIds = AccountHeads::where('parent_id',$head_id)->pluck('head_id');
            $childIds[] = (Int)$head_id;
        }
        
        $oldheadclosing = HeadClosing::with('accountHeads')
            ->where('start_year',$newstartDate)
            ->where('company_id',$company_id)
            ->where('end_year',$newEndDate)
            ->when($branch_id != '',function($q) use($branch_id){
                $q->where('branch_id',$branch_id);
            })
            ->when($head_id,function($q)use($childIds){
                $q->whereIn('head_id',$childIds);
            })
            ->where('status',1)
            ->where('is_deleted',0)
            ->get()
            ;

        $currentyearheadclosing = HeadClosing::with('accountHeads')
        ->where('start_year',$start_y)
        ->where('company_id',$company_id)
        ->where('end_year',$end_y)
        ->when($branch_id != '',function($q) use($branch_id){
            $q->where('branch_id',$branch_id);
        })
        ->when($head_id,function($q)use($head_id){
            $q->where('head_id',$head_id);
        })
        ->where('status',1)
        ->where('is_deleted',0)
        ->get()
        ;
        
        $data = AccountHeads::when($head_id,function($q)use($head_id){
            $q->where('head_id',$head_id);
        })
        ->when(!$head_id,function($q){
            $q->where('labels',1)->whereNotIn('head_id',[6,17,54]);
        })
        ->with(['headcloses','subcategory'=>function($q){
            $q->where('status',0);
        }])
        ->whereIn('status',[0,1])
        ->getCompanyRecords("company_id", $companyList)->whereNotIn('head_id',[6,17,54])
        ->orderBy('head_id', 'ASC')
        ->get()
        ;
       

        $previousYearStartDate = $start_y-1;
        $previousYearEndDate = $end_y-1;
        $endDate = $previousYearEndDate.'-03-31';
        $previousYearBalance = \App\Models\BalanceSheetClosing::whereHas('accountHeads',function($q){
            $q->where('is_trial',0);
        })
        ->whereNotNull('levels')
        ->when($branch_id,function($q) use($branch_id){
            $q->where('branch_id',$branch_id);
        })
        ->when($head_id,function($q) use($head_id){
            $q->where(function($q) use($head_id){
                $q->where('parent_id',$head_id)->orwhere('head_id',$head_id);
            });
        })
       
        ->whereCompanyId($request->company_id)
        // ->where('start_year',$previousYearStartDate)
        ->where('end_date','<=',$endDate)
        ->where('is_opening_balance',1)
        ->where('is_deleted',0)
        ->get()
        ;
       
        if(!$head_id){
            
            $totalOpeningBalance = $previousYearBalance->filter(function($value){        
                return $value->levels == 1;
            });
            $previousOpeningAmount = $oldheadclosing->filter(function($value){
                return $value->accountHeads->labels == 1;
            });
            $previousClosingAmount = $currentyearheadclosing->filter(function($value){
                return $value->accountHeads->labels == 1;
            });
        }else{
            $totalOpeningBalance = $previousYearBalance->filter(function($value) use($head_id){        
                return $value->head_id == $head_id;
                
            });          
            
            $previousOpeningAmount = ($oldheadclosing);;
            $previousClosingAmount = $totalOpeningBalance;
        }
        $totalOpeningBalance = $totalOpeningBalance->sum('total') ;
        $headOpeningAmount = $previousOpeningAmount->sum('amount');
        
        $headopeningsum = ($headOpeningAmount == 0 && empty($oldheadclosing)) ? $totalOpeningBalance : $headOpeningAmount;
        $headClosingAmount = $previousClosingAmount->sum('amount');

        $headclosingtotalsum = 0;
        $previousDataNew =  array();
        $childArray =  array();

        $currentHeadId = array();
        $parentId = array();
        $amounts = [];
        $amountNew = 0;

        foreach ($previousYearBalance as $previousData) {
            $headId = $previousData->head_id;
            $amountNew = $previousData->total;
            if (isset($amounts[$headId])) {
                $amounts[$headId] += $amountNew;
            } else {
                $amounts[$headId] = $amountNew;
            }
        }
        $amountNew2 = 0;
        $amounts2 = [];

        foreach ($oldheadclosing as $oldpreviousData) {
            $headId = $oldpreviousData->head_id;
            $amountNew2 = $oldpreviousData->amount;
            
            if (isset($amounts2[$headId])) {
                $amounts2[$headId] += $amountNew2;
            } else {
                $amounts2[$headId] = $amountNew2;
            }
        }

        $startDate = $start_y.'-04-01';
        $endDate = $end_y.'-03-31';
        $startDate = date('Y-m-d',strtotime($startDate));
        $endDate = date('Y-m-d',strtotime($endDate));

        

        $HeadBalance = \App\Models\BalanceSheetClosing::with(['accountHeads'])->whereNotNull('levels')->when($branch_id,function($q) use($branch_id){
            $q->where('branch_id',$branch_id);
        })->whereCompanyId($request->company_id)->where('start_year',$start_y)->where('end_year',$end_y)->where('is_deleted',0)->where('is_opening_balance',1)->get();

        $amounts3 = [];
   
        foreach ($HeadBalance as $HeadBalancedatat) {
            $headId = $HeadBalancedatat->head_id;
            $crAmount = $HeadBalancedatat->cr_amount;
            $drAmount = $HeadBalancedatat->dr_amount;
            
            if (isset($amounts3[$headId])) {
                $amounts3[$headId]['cr_amount'] += $crAmount;
                $amounts3[$headId]['dr_amount'] += $drAmount;
            } 
            else{
                $amounts3[$headId]['cr_amount'] = $crAmount;
                $amounts3[$headId]['dr_amount'] = $drAmount;            
            }

        }
        
        $companyDetail = \App\Models\Companies::findorFail($request->company_id);

        $view = [
            'data' => $data,
            'start_y' => $start_y, 
            'end_y' => $end_y,
            'start_m'=>$start_m,
            'start_d'=>$start_d,
            'end_m'=>$end_m,
            'end_d'=>$end_d,
            'branch_id'=>$branch_id,
            'totalCR'=>$totalCR,
            'totalDR'=>$totalDR,
            'newstartDate'=>$newstartDate,
            'newEndDate'=>$newEndDate,
            'headopeningsum'=>$headopeningsum, 
            'oldheadclosing'=> $amounts2,
            'currentyearheadclosing'=>$currentyearheadclosing, 
            'headclosingtotalsum'=> $headclosingtotalsum,
            'HeadBalance'=>$amounts3,
            'companyDetail'=>$companyDetail,
            'company_id'=>$company_id,
            'previousData'=>$amounts,
            'totalOpeningBalance'=>$totalOpeningBalance,
            'head_id' => $head_id,
            'name' => $name,
            'financial_year' => $financial_year,
            'lebel' => $lebel ?? '',
        ];
        // dd($view['data']);
        if($HeadBalance || $previousYearBalance )
        {
            if($head_id){
                Session::put('trailBalance-'.$head_id,$view);
            }else{
                Session::put('trailBalance',$view);
            }
            return \Response::json(['view' => view('templates.admin.trailbalance.partials.trial_balance_view', $view)->render() , 'msg_type' => 'success']);
        }
        else{
            return \Response::json(['view' => '', 'msg_type' => 'error']);
        }
      
    }

    
    public function export(Request $request){
        $head_id = ($request->head_id)??'';
        $name = ($request->name)??'';
        if($head_id){
            $view = Session::get('trailBalance-'.$head_id);
        }else{
            $view = Session::get('trailBalance');
        }
        return Excel::download(new TrailBalanceReportExport($view), 'TrailBalanceReport'.strtolower($name).'.xlsx');
    }

    public function runCronTrailBalance(Request $request)
    {
    try{
            $branchId = ($request->branch_id == NULL ) ? 0 : $request->branch_id;
            $date=explode(' - ',$request->financial_year);
            $start_y=$date[0];
            $end_y=$date[1];
            $endDate = '31-03-'.$end_y;
            $endDate = date('Y-m-d H:i:s',strtotime(convertDate($endDate)));
            $startDate = '01-04-'.$start_y;
            $startDate = date('Y-m-d H:i:s',strtotime(convertDate($startDate)));
            $companyId = $request->company_id;
            $get_yeardata=\App\Models\BalanceSheetClosing::when($branchId != 0 ,function($q) use($branchId){
                $q->where('branch_id',$branchId);
            })->where('start_year', $start_y)->where('end_year', $end_y)->where('company_id',$companyId)->delete();
            $dataTotalCount = DB::select('call headclosing_procedure_update(?,?,?,?,?)',[$branchId,$startDate,$endDate,$companyId,1] );
            DB::commit();
    }
        catch(\Exception $error)
    {

            DB::rollback();
            return \Response::json(['view' => $error->getMessage(), 'msg_type' => 'error']);
        }
        return \Response::json(['view' => '', 'msg_type' => 'success']);
    /*$mLoan = Memberloans::find($loanId);
    $mLoanData['edit_reject_request'] = 1;
    $mLoanData['rejection_description'] = $request->rejection;
    $mLoanData['status'] = 2;
    $mLoan->update($mLoanData);*/

        //     $start_y=$request->sdate;
    //     $end_y=$request->edate;
    //     DB::beginTransaction();

    //     try
    //     {


    //     if(isset($_POST['head_id']))
    //     {
    //         foreach(($_POST['head_id']) as $key=>$option)
    //         {
    //           /// print_r(['head_id']);die;

    //             $saveHead['head_id'] = $_POST['head_id'][$key];
    //             $saveHead['amount'] = $_POST['head_amount'][$key];
    //             $saveHead['start_year'] = $start_y;
    //             $saveHead['end_year'] = $end_y;
    //             $saveHead['created_by'] = 1;
    //             $saveHead['created_by_id'] = Auth::user()->id;

    //             $createSaveHeade = HeadClosing::create($saveHead);
    //         }
    //     }
    //    DB::commit();

    //     }
    //     catch(\Exception $ex)
    //     {

    //         DB::rollback();
    //         return \Response::json(['view' => $ex->getMessage(), 'msg_type' => 'error']);

    //     }


    //      return \Response::json(['view' => '', 'msg_type' => 'success']);

    }


	
}
