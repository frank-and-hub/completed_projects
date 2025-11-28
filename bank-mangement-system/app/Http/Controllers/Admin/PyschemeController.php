<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Validator;
use App\Models\User;
use App\Interfaces\RepositoryInterface;
use App\Models\Plans;
use App\Models\PlanCategory;
use App\Models\PlanTenures;
use App\Models\AccountHeads;
use App\Models\Companies;
use App\Models\Profits;
use App\Models\Memberinvestments;
use App\Models\FaCode;
use Carbon\Carbon;
use URL;
use DB;

class PyschemeController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    protected $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->middleware('auth');
    }

    /**
     * Display a listing of the investment plans module.
     *
     * @return \Illuminate\Http\Response
     */
    public function Plans()
    {
        if (check_my_permission(Auth::user()->id, "334") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Investment plans';
        return view('templates.admin.py-scheme.plans', $data);
    }

    /**
     * Display the specified resource.
     * This is our ajax listing of plans 
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function planListing(Request $request)
    {
        if ($request->ajax()) {
            //If Data is not in DB thenn Show Amount 0 and string N/A
            if ($request->company_id) {
                $data = Plans::orderBy('created_at', 'DESC')->with(['InterestHeadName', 'DepositHeadName', 'CategoryName'])->where('company_id', $request->company_id)->get(['id', 'name', 'plan_code', 'status', 'slug', 'deposit_head_id', 'interest_head_id', 'plan_category_code', 'loan_against_deposit', 'plan_sub_category_code', 'is_editable', 'min_deposit', 'max_deposit', 'created_by_id', 'multiple_deposit', 'amount', 'effective_from', 'effective_to', 'company_id', 'death_help', 'created_at']);
            } else {
                $data = Plans::orderBy('created_at', 'DESC')->with(['InterestHeadName', 'DepositHeadName', 'CategoryName'])->get(['id', 'name', 'plan_code', 'status', 'slug', 'deposit_head_id', 'interest_head_id', 'plan_category_code', 'loan_against_deposit', 'plan_sub_category_code', 'is_editable', 'min_deposit', 'max_deposit', 'created_by_id', 'multiple_deposit', 'amount', 'effective_from', 'effective_to', 'company_id', 'death_help', 'created_at']);
            }
            // print_r($data);die;
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('min_deposit', function ($row) {
                    $min_deposit = number_format($row->min_deposit) ?? 0;
                    return $min_deposit;
                })
                ->rawColumns(['min_deposit'])

                ->addColumn('max_deposit', function ($row) {
                    $amount = number_format($row->max_deposit) ?? 0;
                    return $amount;
                })
                ->rawColumns(['max_deposit'])

                ->addColumn('deposit_head_id', function ($row) {
                    $depositHeadName =  $row->DepositHeadName[0]->sub_head ?? 'N/A';
                    return $depositHeadName;
                })
                ->rawColumns(['deposit_head_id'])

                ->addColumn('interest_head_id', function ($row) {
                    $interestHeadName =  ($row->InterestHeadName[0]->sub_head) ?? 'N/A';
                    return $interestHeadName;
                })
                ->rawColumns(['interest_head_id'])
                ->addColumn('plan_category_code', function ($row) {
                    $categoryName =  ($row->CategoryName[0]->name) ?? 'N/A';
                    return $categoryName;
                })
                ->rawColumns(['plan_category_code'])

                ->addColumn('created_by_id', function ($row) {
                    $created_by = $row->admin->username ?? 'N/A';
                    return $created_by;
                })
                ->rawColumns(['created_by_id'])
                ->addColumn('company_id', function ($row) {
                    $company_id = $row->company->name ?? 'N/A';
                    return $company_id;
                })
                ->rawColumns(['company_id'])
                ->addColumn('created_at', function ($row) {
                    $created_at = ($row->created_at) ? date("d/m/Y", strtotime($row->created_at)) : "N/A";
                    return $created_at;
                })
                ->rawColumns(['created_at'])
                ->addColumn('effective_from', function ($row) {
                    $effective_from = ($row->effective_from) ? date("d/m/Y", strtotime($row->effective_from)) : "N/A";
                    return $effective_from;
                })
                ->rawColumns(['effective_from'])
                ->addColumn('effective_to', function ($row) {
                    $effective_to = ($row->effective_to) ? date("d/m/Y", strtotime($row->effective_to)) : "N/A";
                    return $effective_to;
                })
                ->rawColumns(['effective_to'])
                ->addColumn('action', function ($row) {
                    $url = URL::to("admin/py-plan/" . $row->slug . "");
                    //if effective to column is blank then we show status change button otherwise no
                    $statusbtn = ($row->effective_to == "") ? '<button class="dropdown-item  sbutton" data-slug="' . $row->slug . '">
                        <i class="fas fa-ban mr-2"></i>Status</button>' : "";
                    $editbtn = ($row->is_editable == 1) ? '<a class="dropdown-item" href="' . $url . '">
                    <i class="icon-pencil7 mr-2"></i>Edit</a>' : "";
                    $planDetailBtn = '<a class="dropdown-item" href="' . route('admin.plan.show', $row->slug) . '"><i class="far fa-eye mr-2"></i>Plan Detail</a>';
                    $planDenoBtn = ($row->plan_sub_category_code == "K") ? '<a class="dropdown-item" href="' . route('planDeno', $row->slug) . '">
                    <i class="fas fa-chart-bar mr-2"></i>Plan Deno</a>' : "";
                    $moneyBackBtn = ($row->plan_sub_category_code == "B") ? '<a class="dropdown-item" href="' . route('moneyBack.list', $row->slug) . '">
                    <i class="fas fa-hand-holding-usd mr-2"></i>Money Back Setting</a>' : "";
                    $DeathHelpBtn = ($row->death_help == 1) ? '<a class="dropdown-item" href="' . route('deathHelp.list', $row->slug) . '">
                    <i class="fas fa-hands-helping mr-2"></i>Death Help Setting</a>'  : "";
                    $loanAgainstBtn = ($row->loan_against_deposit == 1) ? '<a class="dropdown-item" href="' . route('loanAgainst.list', $row->slug) . '">
                    <i class="far fa-handshake mr-2"></i>Loan Against Deposit</a>'  : "";
                    $tenure = ($row->plan_category_code != "S") ? '<a class="dropdown-item" href="' . route('admin.py-plans.tenure', $row->id) . '"><i class="fas fa-chart-line mr-2"></i>Plan Tenure</a>' : ''; //$row->slug
                    $btn = '<div class="list-icons"><div class="dropdown">
                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                            <div class="dropdown-menu dropdown-menu-right">' . $editbtn . $statusbtn . $planDenoBtn . $moneyBackBtn . $DeathHelpBtn . $planDetailBtn . $loanAgainstBtn . $tenure . '
                            </div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    //fetch the slug from plan category table

    // public function fetchSlug(Request $request)
    // {
    //     $slug = PlanCategory::where('code', $request->plan_category)->first(['code', 'slug']);

    //     $plan_code = FaCode::where('slug', 'ssb')->where('company_id', $request->company_id)->first(['slug', 'code']);
    //     $data = ['plan_code' => $plan_code->code];

    //     return response()->json($data);
    // }

    /**

     * Show the form for creating a new investment plan.

     * 

     * @return \Illuminate\Http\Response

     */
    //We show our plan add form by this Create function 
    public function Create()
    {
        //while we add plan then we show categories and company in dropdown 
        $allPlansCategory = PlanCategory::where('status', '1')->get(['code', 'name', 'is_basic'])->toArray();
        $planCompanies = Companies::get(['id', 'name'])->toArray();

        $categoryies = array_filter($allPlansCategory, function ($e) {
            return $e['is_basic'] == 1;
        });

        $subcategoryies = array_filter($allPlansCategory, function ($e) {
            return $e['is_basic'] == 0;
        });

        $data['title'] = 'Create New Plan';
        $data['categoryies'] = $categoryies;
        $data['subcategoryies'] = $subcategoryies;
        $data['hybrid_type'] = $subcategoryies;
        $data['plan_companies'] = $planCompanies;
        return view('templates.admin.py-scheme.create', $data);
    }

    /**
     * Store a newly created plan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // We storre our plan add form data with store function 
    public function Store(Request $request)
    {
        // This is our validation rules for plan add form and our name field is unqie so we can make unqie slug by name 
        $rules = [
            'name' => 'required|unique:plans',
            'short_name' => 'required',
            'company' => 'required',
            // 'plan_code' => 'required|unique:plans|numeric|min:1',
            'plan_category' => 'required',
            'min_amount' => 'required|numeric|min:1|lte:max_amount',
            'multiple_deposit' => 'required|numeric|min:1',
            'max_amount' => 'required|numeric|min:1',
            'prematurity' => 'required',
            'load_against_deposit' => 'required',
            'death_help' => 'required',
            'effective_from' => 'required',
            'ssb_required' => 'required',
        ];

        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        // this is laravel  validation function 
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {

            

            if ( $request->plan_category=='S' && $request->plan_sub_category=='')
            {
                $checkCategory = Plans::where('company_id', $request->company)->where('plan_category_code','S')->where('plan_sub_category_code',null)->exists();
                if($checkCategory)
                {
                    return back()->with('alert', 'You cannot create more than one basic savings account in the same company.');
                    exit();
                }
                
            }
           
                $plan_code = Companies::where('id',$request->company)->orderBy('last_fa_code','desc')->first(['id','last_fa_code']);

                $data['name'] = $request->name;
                // we slug making function 
                function slug($z)
                {
                    $z = strtolower($z);
                    $z = preg_replace('/[^a-z0-9 -]+/', '', $z);
                    $z = str_replace(' ', '-', $z);
                    return trim($z, '-');
                }
                $data['slug'] = slug($request->name);
                $data['company_id'] = $request->company;
                
                if($request->plan_category=='S' && $request->plan_sub_category=='')
                {
                    $getFaCode =FaCode::where('company_id',$request->company)->where('slug','saving_account')->first();
                    $planCode =$getFaCode->code;
                }
                else
                {
                    $planCode = $plan_code->last_fa_code + 1;
                }
                $data['plan_code'] = $planCode;
                $data['plan_category_code'] = $request->plan_category;
                $data['is_ssb_required'] = $request->ssb_required;

                $headid = PlanCategory::where('code', $request->plan_category)->get('head_id')->first();
                $Nature = AccountHeads::where('head_id', $headid->head_id)->get(['cr_nature', 'dr_nature', 'labels'])->toArray();
                $maxheadid = AccountHeads::max('head_id');

                //We make a newAccountHead for deposit in accountheads table
                $newAccountHead = new AccountHeads;
                $newAccountHead->head_id = ($maxheadid + 1);
                $newAccountHead->parent_id = $headid->head_id;
                $newAccountHead->sub_head = "Deposit " . ucwords($request->name);
                $newAccountHead->cr_nature = $Nature[0]['cr_nature'];
                $newAccountHead->dr_nature = $Nature[0]['dr_nature'];
                $newAccountHead->labels = $Nature[0]['labels'] + 1;
                $newAccountHead->company_id = '[' . $request->company . ']';
                $newAccountHead->entry_everywhere = 0;
                $newAccountHead->basic_heads = '0';
                
                // $newAccountHead->created_at = $request->created_at;
                // $newAccountHead->updated_at = $request->updated_at;
                $newAccountHead->save();

                //We make a newAccountHead for deposit in accountheads table
                $maxheadid = AccountHeads::max('head_id');
                $newAccountHead2 = new AccountHeads;
                $newAccountHead2->head_id = ($maxheadid + 1);
                $newAccountHead2->parent_id = $headid->head_id;
                $newAccountHead2->sub_head = "Accrued Interest " . ucwords($request->name);
                $newAccountHead2->cr_nature = $Nature[0]['cr_nature'];
                $newAccountHead2->dr_nature = $Nature[0]['dr_nature'];
                $newAccountHead2->labels = $Nature[0]['labels'] + 1;
                $newAccountHead2->company_id = '[' . $request->company . ']';
                $newAccountHead2->entry_everywhere = 0;
                $newAccountHead2->basic_heads = '0';
                // $newAccountHead2->created_at = $request->created_at;
                // $newAccountHead2->updated_at = $request->updated_at;
                $newAccountHead2->save();
                $data['deposit_head_id'] =  $newAccountHead->head_id;
                $data['interest_head_id'] =  $newAccountHead2->head_id;
                $data['plan_sub_category_code'] = $request->plan_sub_category;
                $data['hybrid_type'] = $request->hybrid_type;
                $data['hybrid_tenure'] = $request->hybrid_tenure;
                $data['min_deposit'] = $request->min_amount;
                $data['multiple_deposit'] = $request->multiple_deposit;
                $data['max_deposit'] = $request->max_amount;
                $data['short_name'] = $request->short_name;
                $data['prematurity'] = $request->prematurity;
                $data['death_help'] = $request->death_help;
                $data['created_by_id'] = Auth::user()->id;
                $data['loan_against_deposit'] = $request->load_against_deposit;
                $date = str_replace('/', '-', $request->effective_from);
                $data['effective_from'] =date("Y-m-d", strtotime( str_replace('/','-', $request->effective_from)) );

                $data['status'] =1;

                // $data['created_at'] = $request->created_at;
                // $data['updated_at'] = $request->updated_at;

                $res = Plans::create($data);
                if ($res) {
                    if($request->plan_category!='S' )
                    {
                        $count = Companies::where('id', $request->company)->first(['id', 'count']);
                        Companies::where('id', $request->company)->update(['last_fa_code' => $planCode, 'count' => $count->count + 1]);
                    }
                    
                }
                DB::commit();
            
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        //if our plan add successfully then we redirect to plan list page with message 
        $redirect = ($res) ? redirect()->route('admin.py.plans')->with('success', 'Saved Successfully!') : redirect()->route('admin.py.plans')->with('alert', 'Problem With Creating New Plan');
        return $redirect;
    }

    /**
     * Destroy created plan.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function PlanDestroy($id)
    {
        $data = Plans::findOrFail($id);
        $redirect =  ($data->delete()) ? back()->with('success', 'Request was Successfully deleted!') : back()->with('alert', 'Problem With Deleting Request');
        return $redirect;
    }

    /**
     * Display created plan by id.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function Edit($slug)
    {
        // while we edit plan then we show category and company in dropdown 
        $allPlansCategory = PlanCategory::where('status', '1')->get(['code', 'name', 'is_basic'])->toArray();
        $planCompanies = Companies::get(['id', 'name'])->toArray();
        /* the array filter method we use for filtering catogoryies if is_basic is 1 then use in dropdown for category and if is_basic is 
        0 then it is subcategory */
        $categoryies = array_filter($allPlansCategory, function ($e) {

            return $e['is_basic'] == 1;
        });

        $subcategoryies = array_filter($allPlansCategory, function ($e) {
            return $e['is_basic'] == 0;
        });

        $data['plan'] = Plans::where('slug', $slug)->first();
        $data['title'] = $data['plan']->name;
        $data['categoryies'] = $categoryies;
        $data['subcategoryies'] = $subcategoryies;
        $data['hybrid_type'] = $subcategoryies;
        $data['plan_companies'] = $planCompanies;
        return view('templates.admin.py-scheme.edit', $data);
    }

    /**
     * Update the specified plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Update(Request $request) //this is our update function that is called when edit form submitted and we save edit form data.
    {

        //before save data we use a validation 
        $rules = [
            'company' => 'required',
            'plan_category' => 'required',
            'min_amount' => 'required|numeric|min:1',
            'multiple_deposit' => 'required|numeric|min:1',
            'max_amount' => 'required|numeric|min:1',
            'prematurity' => 'required',
            'load_against_deposit' => 'required',
            'death_help' => 'required',
            'effective_from' => 'required',
            // 'short_name' => 'required',
            'ssb_required' => 'required',
        ];
        //this is custom message that is visible when error is show
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];

        $this->validate($request, $rules, $customMessages);
        try {
            DB::beginTransaction();
            $data = Plans::where('slug', $request->slug)->first();
            //after validation we save data in plans table with slug 
            $data->company_id = $request->company;
            $data->is_ssb_required = $request->ssb_required;
            // $data->short_name = $request->short_name;
            $data->plan_category_code = $request->plan_category;
            $data->plan_sub_category_code = $request->plan_sub_category;
            $data->hybrid_type = $request->hybrid_type;
            $data->hybrid_tenure = $request->hybrid_tenure;
            $data->min_deposit = $request->min_amount;
            $data->multiple_deposit = $request->multiple_deposit;
            $data->max_deposit = $request->max_amount;
            $data->prematurity = $request->prematurity;
            $data->loan_against_deposit = $request->load_against_deposit;
            $data->death_help = $request->death_help;
            $data->updated_at = $request->created_at;
            $date = str_replace('/', '-', $request->effective_from); 
            $data->effective_from = date("Y-m-d", strtotime( str_replace('/','-', $request->effective_from)) );
            $headid = PlanCategory::where('code', $request->plan_category)->get('head_id')->first();
            $Nature = AccountHeads::where('head_id', $headid->head_id)->get(['cr_nature', 'dr_nature', 'labels'])->toArray();

            $newAccountHead = AccountHeads::where('head_id', $data->deposit_head_id)->first();
            $newAccountHead->parent_id = $headid->head_id;
            $newAccountHead->sub_head = "Deposit " . ucwords($request->name);
            $newAccountHead->cr_nature = $Nature[0]['cr_nature'];
            $newAccountHead->dr_nature = $Nature[0]['dr_nature'];
            $newAccountHead->labels = $Nature[0]['labels'] + 1;
            $newAccountHead->company_id = '[' . $request->company . ']';
            $newAccountHead->update();

            //We make a newAccountHead for deposit in accountheads table
            $newAccountHead2 =  AccountHeads::where('head_id', $data->interest_head_id)->first();
            $newAccountHead2->parent_id = $headid->head_id;
            $newAccountHead2->sub_head = "Accrued Interest " . ucwords($request->name);
            $newAccountHead2->cr_nature = $Nature[0]['cr_nature'];
            $newAccountHead2->dr_nature = $Nature[0]['dr_nature'];
            $newAccountHead2->labels = $Nature[0]['labels'] + 1;
            $newAccountHead2->company_id = '[' . $request->company . ']';
            $newAccountHead2->update();
           $res =  $data->save();

            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        $redirect = ($res) ? redirect()->route('admin.py.plans')->with('success', 'Update was Successful!') : redirect()->route('admin.py.plans')->with('alert', 'An error occured');
        return $redirect;
    }
    //this is our status change function 
    public function Status(Request $request)
    {
        $data = Plans::where('slug', $request->slug)->first();
        //here we check if our effective to column is null then admin can change the status otherwise not
        if ($data->effective_to == "") {
            $date = str_replace('/', '-', $request->gdate);
            $data->effective_to = date('Y-m-d', strtotime($date));
            $data->status = ($data->status == 1) ? 0 : 1;
            $data->save();
        }
    }

    public function show($slug)
    {
        $data['title'] = 'Plan Details';
        $data['plan'] = Plans::where('slug', $slug)->with('PlanTenures')->with('CategoryName')->with('SubCategoryName')->with('MoneyBack')->with('DeathHelpSettin')->with('PlanDeno')->with('LoanAgainst')->with('commissiondetail')->get()->toArray() ?? "";
        //    echo '<pre>'; print_r($data['plan']);die;
        return view('templates.admin.py-scheme.planDetail', $data);
    }
    public function tenure($id)
    {
        $title = "Investment Plans | Create Plan Tenure";
        $plan_tenures = $this->repository->getAllPlanTenures()->where('plan_id', $id)->get();
        $plan = $this->repository->getPlansById($id)->where('status', 1)->where('plan_category_code', '!=', 'S')->first(['id', 'name', 'plan_code']);
        // if (!$plan) {
        //     return back()->with('warning', 'not authorized');
        // } else {
        //     return view('templates.admin.py-scheme.tenure', compact('title', 'plan', 'plan_tenures'));
        // }
        return view('templates.admin.py-scheme.tenure', compact('title', 'plan', 'plan_tenures'));
    }
    public function tenure_listing()
    {
        // $title = "Investment plans | Create Plan tenure";
        // $data = $this->repository->getAllPlanTenures()->where('plan_id', $id)->get();
        // $plan = $this->repository->getPlansById($id)->where('status',1)->first(['id','name','plan_code']);   
        // return view('templates.admin.py-scheme.tenure',compact('title','plan','plan_tenures'));
    }
    public function tenure_save(Request $request)
    {
        $rules = [
            'plan_code' => 'required|numeric',
            'plan_id' => 'required|numeric',
            'month_from' => 'required',
            'month_to' => 'required',
            'roi' => 'required',
            'tenure' => 'required',
            'spl_roi' => 'required',
            'compounding' => 'required',
            'effective_from' => 'required',
            'created_by_id' => 'required|numeric',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.',
            'numeric' => 'The :attribute field must number only.'
        ];
        $this->validate($request, $rules, $customMessages);
        $input = new PlanTenures;
        $input->plan_code = $request->plan_code;
        $input->tenure = $request->tenure;
        $input->plan_id = $request->plan_id;
        $input->month_from = $request->month_from;
        $input->month_to = $request->month_to;
        $input->roi = $request->roi;
        $input->spl_roi = $request->spl_roi;
        $input->compounding = $request->compounding;
        $date = str_replace('/', '-', $request->effective_from);
        $input->effective_from = date('Y-m-d', strtotime($date));
        $input->created_by_id = $request->created_by_id;

        $redirect = ($input->save()) ? redirect()->back()->with('success', 'New Plan Tenure Created Successfully!') : redirect()->back()->with('alert', 'Opps Something went Worng');
        return $redirect;

        // return redirect()->back()->with([ ($tenure)? 'sucess' : 'error', ($tenure)? 'New Plan Tenure Created Successfully ' : 'Opps Something went Worng !']);
    }
    // public function tenureStatus($id)
    // {
    //     $tenure = PlanTenures::find($id);
    //     if ($tenure) {
    //         $tenure->status = 0;
    //         $tenure->effective_to = date("Y-m-d");
    //         $tenure->save();
    //         return redirect()->back()->with('success', 'Tenure status change successfully!');
    //     } else {
    //         return redirect()->back()->with('alert', 'Sorry there was a problem!');
    //     }
    // }
    public function tenure_status(Request $request)
    {
        $data = PlanTenures::where('id',$request->id)->first();
        // dd($data);
        $plan_data = Plans::whereId($data->plan_id)->first();
        if($data->status == 1)
        {
            $date = str_replace('/', '-', $request->date);
            $data->effective_to = date('Y-m-d', strtotime($date));
            $data->status = 0;
            $data->save();

            /**Insert the log in the table
             * table name is plan_log_details
             */
            $log_insert = new PlanLogDetails();
            $log_insert->type = 2;
            $log_insert->type_id = $data->plan_id;
            $log_insert->tenure_id = $data->id;
            $log_insert->title = 'Investment Plan Tenure Status Change';

            if($plan_data->plan_category_code == 'D')
            {
                $log_insert->description = $data->tenure .' Days [' .$plan_data->name. ' - '.$plan_data->plan_code .'] was deactivated by '. Auth::user()->username ;
                
            }
            else if($plan_data->plan_category_code == 'M' || $plan_data->plan_category_code == 'F')
            {
                $log_insert->description = $data->tenure .' Months [' .$plan_data->name. ' - '.$plan_data->plan_code .'] was deactivated by '. Auth::user()->username ;
            }
            
            $log_insert->old_data = 'Active';
            $log_insert->new_data = 'Inactive';
            $log_insert->created_by = 1;
            $log_insert->created_by_id = Auth::user()->id;
            $log_insert->save();

            return response(['response'=>1]);
        }
        else
        {
            $data->effective_to = null;
            $data->status = 1;
            $data->save();
            
            /**Insert the log in the table
             * table name is plan_log_details
             */
            $log_insert = new PlanLogDetails();
            $log_insert->type = 2;
            $log_insert->type_id = $data->plan_id;
            $log_insert->tenure_id = $data->id;
            $log_insert->title = 'Investment Plan Tenure Status Change';
            
            if($plan_data->plan_category_code == 'D')
            {
                $log_insert->description = $data->tenure .' Days [' .$plan_data->name. ' - '.$plan_data->plan_code .'] was activated by '. Auth::user()->username ;
            
            }
            else if($plan_data->plan_category_code == 'M' || $plan_data->plan_category_code == 'F')
            {
                    $log_insert->description = $data->tenure .' Months [' .$plan_data->name. ' - '.$plan_data->plan_code .'] was activated by '. Auth::user()->username ;
            }
                
            $log_insert->old_data = 'Inactive';
            $log_insert->new_data = 'Active';
            $log_insert->created_by = 1;
            $log_insert->created_by_id = Auth::user()->id;
            $log_insert->save();

            return response(['response'=>1]);
        }
    }
    public function modelShow(Request $request)
    {
        $tenureId = $request->tenureId;
        $id = $request->id;
        $planId = $request->planId;
        $data['planTenures'] = \App\Models\PlanTenures::find($id);
        $data['commisssion'] = $this->repository->getAllCommissionDetail()->with('carder:id,name')
            ->where('plan_id', $planId)
            ->where('tenure', $tenureId)
            ->where('effective_to',null)
            ->orderBy('effective_from', 'DESC')
            ->get();
        return \Response::json(['view' => view('templates.admin.py-scheme.partials.model', ['data' => $data])->render(), 'msg_type' => 'success']);
    }
    public function plan_tenure_listing(Request $request)
    {
        $frm_data = $request->all();
        $arrFormData = array();
        if (!empty($_POST['searchform'])) {
            foreach ($_POST['searchform'] as $frm_data) {
                $arrFormData[$frm_data['name']] = $frm_data['value'];
            }
        }
        $data = PlanTenures::whereNull('deleted_at')
            ->where('plan_code', $arrFormData['plan_code'])
            ->where('plan_id', $arrFormData['plan_id'])
            // ->with(['plans:id,name', 'plans.Commissiondetail:id,plan_id,tenure'])
            ->orderBy('id', 'DESC')
        ;
        $count = $data->count('id');
        $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
        $totalCount = $count;
        $sno = $_POST['start'];
        $rowReturn = array();
        $compounding = [
            3 => 'Quarterly',
            6 => 'Half Yearly',
            12 => 'Annually'
        ];
        // pd($data->toArray());
        foreach ($data as $row) {
            $btn = '';
            $plans = $row->plans;
            $commisionbtn = '<a class="view_data btn btn-white text-dark w-100 legitRipple" title="Create Plan Tenure Commission" href=' . route("admin.py-plans.tenure.plan_tenure_create", [$row->id]) . ' ><i class="fas fa-plus mr-2"></i>Create</a>';
            // $statusbtn = '<a class="status_btn btn btn-white text-dark w-100 legitRipple" title="Status" href="#" >Status</a>';
            $statusbtn = '<button class="status_btn btn btn-white w-100 legitRipple" data-id="' . $row->id . '" title="Status Change">Status</button>';
            // $view = '<button class="view_data btn btn-white w-100 legitRipple" data-toggle="modal" data-target="#commissionLoanModel" id="view-data" data-id="' . $row->id . '" data-tenure="' . $row->tenure . '"  data-plan_id="' . $row->plan_id . '" title="view data" ><i class="fa fa-eye  mr-2"></i>Commission</button>';
            $view = '<button class="view_data btn btn-white w-100 legitRipple" data-toggle="modal" data-target="#commissionLoanModel" id="view-data" data-id="' . $row->id . '" data-tenure="' . $row->tenure . '"  data-plan_id="' . $row->plan_id . '" title="view data" >Commission</button>';
            $btn .= '<div class="list-icons">
            <div class="dropdown">
                <a href="#" class="list-icons-item" data-toggle="dropdown">
                <i class="icon-menu9"></i></a>
            <div class="dropdown-menu dropdown-menu-right">';
            $btn .= plantenureToCommissionExists($row->plan_id,$row->tenure) ? $view : '';
            $btn .= $commisionbtn . $statusbtn.'</div>
            </div>
            </div>';
            $active = '<div class="col-lg-12 "><span class="badge badge-success">Active</span></div>';
            $inactive = '<div class="col-lg-12 "><span class="badge badge-danger">Inactive</span></div>';
            $sno++;
            $val = [
                'DT_RowIndex' => $sno,
                // 'plan_code'=> $row->plan_code,
                'plan' => $plans->name,
                'tenure' => $row->tenure,
                'roi' => $row->roi,
                'spl_roi' => $row->spl_roi,
                'compounding' => $compounding[$row->compounding],
                'status' => $row->status == '1' ? $active : $inactive,
                'effective_from' => date('d-m-Y', strtotime($row->effective_from)),
                'action' => $btn,
            ];
            $rowReturn[] = $val;
        }
        $token = session()->get('_token');
        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
        return response()->json($output);
    }
}
