<?php 
namespace App\Http\Controllers\Admin; 


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AccountHeads;
use App\Models\Branch;

use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use Illuminate\Support\Facades\Schema;

class TrailBalanceController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index()
    {		
		/*if(check_my_permission( Auth::user()->id,"144") != "1"){
		  return redirect()->route('admin.dashboard');
		}*/		
		
		$data['title']='Trial Balance';   
        $data['branches'] = Branch::select('id','name')->where('status',1)->get();

        return view('templates.admin.trailbalance.index', $data);

    }
    public function getHeadClosingList(Request $request)
    { 
    	$date=explode(' - ',$request->financial_year);
    	$start_y=$date[0];
    	$end_y=$date[1]; 
        $start_m=04;
        $start_d=01;
        $end_m=03;
        $end_d=31;
        $branch_id=$request->branch;

        $totalCR=0;
        $totalDR=0;

    	/*if($branch_id=='all')
        {
            $branch='';
        }
        else {
            $branch=$branch_id;
        }
        $head1Child= DB::select('call getAllHead(?)',['1']);
        $childID1=$head1Child[0]->headVal;

        $head2Child= DB::select('call getAllHead(?)',['2']);
        $childID2=$head2Child[0]->headVal;


        $head3Child= DB::select('call getAllHead(?)',['3']);
        $childID3=$head3Child[0]->headVal;

        $head4Child= DB::select('call getAllHead(?)',['4']);
        $childID4=$head4Child[0]->headVal;

        $amountDR1=headSumType(1,$childID1,$branch,$start_y,$end_y,'DR');
        $amountCR1=headSumType(1,$childID1,$branch,$start_y,$end_y,'CR');
        
        $amountDR2=headSumType(2,$childID2,$branch,$start_y,$end_y,'DR');
        $amountCR2=headSumType(2,$childID2,$branch,$start_y,$end_y,'CR');
        
        $amountDR3=headSumType(3,$childID3,$branch,$start_y,$end_y,'DR');
        $amountCR3=headSumType(3,$childID3,$branch,$start_y,$end_y,'CR');

        $amountDR4=headSumType(4,$childID4,$branch,$start_y,$end_y,'DR');
        $amountCR4=headSumType(4,$childID4,$branch,$start_y,$end_y,'CR');

        $totalCR=$amountCR1+$amountCR2+$amountCR3+$amountCR4;
        $totalDR=$amountDR1+$amountDR2+$amountDR3+$amountDR4;*/
        
        
            $data = AccountHeads::where('labels',1)->where('status',0)->orderBy('head_id', 'ASC')->get(); 
            return \Response::json(['view' => view('templates.admin.trailbalance.partials.head', ['data' => $data,'start_y' => $start_y, 'end_y' => $end_y,'start_m'=>$start_m,'start_d'=>$start_d,'end_m'=>$end_m,'end_d'=>$end_d,'branch_id'=>$branch_id,'totalCR'=>$totalCR,'totalDR'=>$totalDR])->render() , 'msg_type' => 'success']);
       
    	
    }

    

	
	
	
}
