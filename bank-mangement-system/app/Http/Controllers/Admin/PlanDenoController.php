<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PlanDenosRequest;
use Yajra\DataTables\DataTables;
use App\Models\PlanCategory;
use App\Models\Plans;
use Illuminate\Support\Facades\DB;
use App\Models\PlanTenures;
use App\Models\PlanDenos;
use Illuminate\Support\Facades\Auth;

class PlanDenoController extends Controller
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
    * Display the add plan deno page
    *
    * @return \Illuminate\Http\Response
    */


    public function index($slug)
    {
        //title of the page
        $data['title'] = "Plan Deno";

        /**get the data from plans table
        * columns get (name, plan_code)
        **/

        $fetch = Plans::with('PlanTenures')->where('slug',$slug)->get(['name','plan_code','id']);

        /**get the data from plan_tenures table
        * columns get (tenure,denomination,effective_from)
        **/

        $fetch_tenures = PlanTenures::with('plans')->select(['tenure'])->get();

        return view('templates.admin.py-scheme.planDeno',$data,compact('fetch','fetch_tenures'));
    }

    /**
    * Fetch the data from PLAN_DENOS
    * 
    * table plan_denos
    *
    * @return \Illuminate\Http\Response
    */

    public function listing(Request $request)
    {
        $plan_code = $request->plan_code;
        $statusClass = "";
        
        if($request->ajax())
        {           
            // $data = PlanDenos::whereHas('plans',function($q) use ($plan_code)
            // {
            //     $q->where('plan_code',$plan_code);
            // })->get(['id','tenure','denomination','effective_from','plan_code']);

            $data = PlanDenos::with('plans')->where('plan_code',$plan_code)->whereDeleted_at(null)->orderBy('id','desc')->get(['id','tenure','denomination','effective_from','effective_to','status']);
            return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('effective_from', function($row){
                $effective_from = date("d/m/Y",strtotime($row->effective_from));
                return $effective_from;
            })
            ->rawColumns(['effective_from']) 
            ->addColumn('effective_to', function($row){
                $effective_to = ($row->effective_to)?date("d/m/Y",strtotime($row->effective_to)):"N/A";
                return $effective_to;
            })
            ->rawColumns(['effective_to']) 
            ->addColumn('action', function($row){
                if($row->status == 1)
                {
                    $btnClass = "btn btn-success";
                    $statusClass = "fa fa-check-circle";
                }
                else
                {
                    $btnClass = "btn btn-secondary";
                    $statusClass = "fa fa-ban";
                }

                $btn = "";
                $btn .= '<button class="delete_data btn btn-danger" del_id="'.$row->id.'" title="Delete"><i class="fa fa-trash"></i></button> <button class="change_status '.$btnClass.'" statusId="'.$row->id.'" data-status="'.$row->status.'" title="Status"><i class="'.$statusClass.'"></i></button>'; 
                return $btn;
            })
            ->rawColumns(['action'])            

            ->make(true);
        }
    }

    /**
    * Insert the data into PLAN_DENOS
    * 
    * table plan_denos
    *
    * @return \Illuminate\Http\Response
    */

    public function insert(PlanDenosRequest $request)
    {
        $checkTenure = PlanDenos::where('tenure',$request->tenure)->where('effective_from',$request->effective_from)->where('plan_code',$request->plan_code)->where('status',1)->exists();
        $data = $checkTenure ? 1 : 0;
        $save = 0;  
        if($data==0)
        {
            $plan_denos = new PlanDenos;
            $plan_denos->plan_id = $request->plan_id;
            $plan_denos->plan_code = $request->plan_code;
            $plan_denos->tenure = $request->tenure;
            $plan_denos->denomination = $request->denomination;
            $plan_denos->effective_from = date("Y-m-d",strtotime(convertDate($request->effective_from)));
            $plan_denos->effective_to = date("Y-m-d",strtotime(convertDate($request->effective_to)));
            $plan_denos->created_by_id = Auth::user()->id;
            $plan_denos->created_at = $request->created_at;
            $plan_denos->updated_at = $request->created_at;
            $plan_denos->save();      
            $save = 1;      
        }
        return response()->json(['data'=>$data,'save'=>$save]);
    }

    /**
    * Delete the data into PLAN_DENOS
    * 
    * table plan_denos
    *
    * @return \Illuminate\Http\Response
    */

    public function destroy(Request $request)
    {
        $planDetail = PlanDenos::find($request->id);
        $data = $planDetail->delete();      
        $success = $data?:0;
        return response()->json(['success'=>$success]);
    }

    /**
    * Status Change 
    * 
    * table plan_denos
    *
    * @return \Illuminate\Http\Response
    */    

    public function status(Request $request)
    {
        if($request->status == 1)
        {
            $fetchData = PlanDenos::where('id',$request->id)->update(['status'=>0]);    
            $save = 1;
        }
        else
        {
            $fetchData = PlanDenos::where('id',$request->id)->update(['status'=>1]);    
            $save = 1;   
        }

        return response()->json(["save"=>$save]);
    }
}