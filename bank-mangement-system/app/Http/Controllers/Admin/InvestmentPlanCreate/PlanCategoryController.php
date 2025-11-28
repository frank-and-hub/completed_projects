<?php
namespace App\Http\Controllers\Admin\InvestmentPlanCreate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\PlanCategory;
use App\Models\AccountHeads;
use App\Http\Requests\PlanCategoryRequest;
use App\Facades\ReportFacade;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PlanCategoryController extends Controller
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
     * Display a listing of the loans.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Display a Plan Categories Page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['title']="Plan Categories"; 
        return view('templates.admin.py-scheme.planCategory',$data);
        //chan plancategory to planCategory
    }

    /**
     * Display a listing of the catesgory.
     * table plan_categories
     * @return \Illuminate\Http\Response
     */

    public function categoryListing(Request $request)
    {
        
        if($request->ajax())
        {
            $data = PlanCategory::with([
                'admin'=>function($q)
                {
                    $q->get(['username','id']);
                },
                'admins'=>function($q)
                {
                    $q->get(['name','id']);
                },
                'accountheads'=>function($q){
                    $q->get([
                        'sub_head',
                        'head_id'
                    ]);
                }
            ])->orderBy('id','desc')->get();
            return Datatables::of($data)
            ->addIndexColumn()
            // ->addColumn('head_id', function($row){
            //     $head_name = $row->accountheads->sub_head??'N/A';
            //     return $head_name;
            // })
            // ->rawColumns(['head_id'])
            ->addColumn('is_basic', function($row){
                $is_basic = ($row->is_basic == '1')? 'Yes' : 'No';
                return $is_basic;
            })
            ->rawColumns(['is_basic'])
            ->addColumn('created_by', function($row){
                $created_by = $row->admin->username??'N/A';
                return $created_by;
            })
            ->rawColumns(['created_by'])
            ->addColumn('created_at', function($row){
                $created_at = date("d/m/Y", strtotime($row->created_at));
                return $created_at;
            })
            ->rawColumns(['created_at'])
            ->addColumn('action', function($row){
                $btn = "";
                // $clickText = "return confirm('Are you sure want to change the status?')";
                $btn = ($row->is_basic==0)?'<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right"><button class="change_status btn btn-white w-100" data-slug="'.$row->slug.'" data-status="'.$row->status.'"><i class="icon-pencil7  mr-2"></i>Status Change</button> </div></div></div>':'N/A';
                return $btn;
            })
            ->rawColumns(['action'])
            
            ->make(true);
        }

    }

    /**
     * Display add Category page
     * @return \Illuminate\Http\Response
     */

	public function addPage()
    {
        $data['title'] = "Create New Category";
        return view('templates.admin.py-scheme.createCategory',$data);
    }

    /**
     *  Check the name already exists or not
     *  table plan_categories
     *  @return \Illuminate\Http\Response
     */

     public function check(Request $request)
     {
        
        $name = PlanCategory::where('name',$request->name)->exists();
        $plan_code = PlanCategory::where('code',$request->plan_code)->exists();
        return response()->json(['data_name'=>$name?'1':'0','data_code'=>$plan_code?'1':'0']);
     }

    /**
     *  Save Plan Category.
     *  table plan_categories
     *  @param $request data
     *  @return \Illuminate\Http\Response
     */

    public function addCategory(PlanCategoryRequest $request)
    {
        $request->validated();
        $category_value = new PlanCategory;
        $category_value->name = $request->name;
        $category_value->code = $request->plan_code;
        $category_value->slug = Str::slug($request->name, '-');
        $category_value->created_by_id = Auth::user()->id;
        $category_value->created_at = $request->created_at;
        $category_value->updated_at = $request->created_at;

        // $accounthead =AccountHeads::orderBy('head_id','desc')->first();
        // $account_head_id = $accounthead->head_id+1;   

       //  $accountHead = array();
       //    $accountHead = [
       //      'head_id'=>$account_head_id,
       //      'sub_head'=>$request->name,
       //      'parent_id'=>20,
       //      'labels'=>4,
       //      'cr_nature'=>1,
       //      'dr_nature'=>2,
       //      ];
       // $accountHeadValue = AccountHeads::insert($accountHead);
       // $category_value->head_id = $account_head_id;
        $category_value->save();
        return redirect('admin/plan-categories')->with('success','Category Created SuccessFully!');
    }

    /**
     *  Status Change
     *  table plan_categories
     *  @param $request data with slug
     *  @return \Illuminate\Http\Response
     */

    public function status(Request $request)
    {
        $status = $request->status;
        $status_update = ($status == 1) ? ['status'=>0] : ['status'=>1];
        $query = PlanCategory::where('slug',$request->slug)->update($status_update);
        $response =($query) ? ['data'=>1]  : ['data'=>0] ;
        return response()->json($response);
    }
}