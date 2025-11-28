<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Member;
use App\Models\AssociateException;
use App\Models\AssociateExceptionLog;
use App\Models\Branch;
use App\Models\Carder;
use App\Models\Memberinvestments;
use App\Models\AssociateCommission;
use App\Models\CommissionDailySetting;
use App\Models\CommisionMonthEnd;
use App\Models\CorrectionRequests;
use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
use App\Models\SamraddhBank;
use App\Interfaces\RepositoryInterface;
use Illuminate\Support\Facades\Cache;


class CommissionController extends Controller
{

    /**
     * Create a new controller instance.
     * @return void
     */

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        // check user login or not
        $this->middleware('auth');
    }

    public function index($id)
    {
        $loan_type_id = $id;
        $commission = $this->repository->getAllCommissionLoanDetails()->with('carder:id,name')->whereLoan_type_id($loan_type_id)->whereNull('effective_to')->get();
        $title = "Loan Commission Percentage";
        return view('templates.admin.loan-commission.commissionLoanDetails', compact('title', 'loan_type_id', 'commission'));
    }
    public function listing(Request $request)
    {
        $id = $request->id;
        if ($request->ajax()) {
            $data = \App\Models\LoanTenure::whereLoan_id($id)->get(['id', 'name', 'tenure', 'effective_from', 'emi_option', 'effective_to', 'status', 'loan_id']);
            if ($data) {
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('name', function ($row) {
                        return  $row->name;
                    })
                    ->rawColumns(['name'])
                    ->addColumn('tenure_type', function ($row) {
                        $option = [1 => "Month", 2 => "Weeks", 3 => "Days"];
                        return  $option[$row->emi_option];
                    })
                    ->rawColumns(['tenure_type'])
                    ->addColumn('effective_from', function ($row) {
                        $effective_from = date("d/m/Y", strtotime($row->effective_from));
                        return $effective_from;
                    })
                    ->rawColumns(['effective_from'])
                    ->addColumn('effective_to', function ($row) {
                        $effective_to = ($row->effective_to) ? date("d/m/Y", strtotime($row->effective_to)) : "N/A";
                        return $effective_to;
                    })
                    ->rawColumns(['effective_to'])
                    ->addColumn('status', function ($row) {
                        return $row->status;
                    })
                    ->rawColumns(['status'])
                    ->addColumn('commission_added', function ($row) {
                        $data = \App\Models\CommissionLoanDetail::where(['tenure_type' => $row->emi_option, 'tenure' => $row->tenure])->where('effective_from', '=', $row->effective_from)->where('loan_type_id', $row->loan_id);
                        $commissionGenerted = $data->exists();
                        return ($commissionGenerted) ? 1 : 0;
                    })
                    ->rawColumns(['commission_added'])
                    ->addColumn('action', function ($row) {
                        $data = \App\Models\CommissionLoanDetail::where(['tenure_type' => $row->emi_option, 'tenure' => $row->tenure])->where('effective_from', '=', $row->effective_from)->where('loan_type_id', $row->loan_id);
                        $commissionGenerted = $data->exists();
                        $url = route("admin.loan.commission.create", ["id" =>  $row->id]);
                        $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        if (!$commissionGenerted) {
                            $btn .= '<button class=" btn btn-white w-100 legitRipple" ><a href="' . $url . '" class="text-dark"><i class="fa fa-plus"></i> &nbsp; Create</a></button>';
                        } else {
                            $btn .= '<button class="view_data btn btn-white w-100 legitRipple" data-loan="' . $row->loan_id . '"  data-tenure="' . $row->id . '"  
                            title="view data" ><i class="fa fa-eye  mr-2"></i>View</button>';
                        }
                        $btn .= ' </div></div></div>';
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } else {
                $data = []; // Empty array to return blank row
                return Datatables::of($data)->make(true);
            }
        }
    }
    public function create($id)
    {
        $title = "Create Loan Commission Percentage";
        $data = \App\Models\LoanTenure::find($id);
        $loan_type_id = $data->loan_id;
        $Carder = $this->repository->getAllCarder()->get(['id', 'name']);
        return view('templates.admin.loan-commission.createCommission', compact('title', 'Carder', 'loan_type_id', 'data'));
    }
    public function mdoel(Request $request)
    {
        $tenureId = $request->tenureId;
        $loanId = $request->loanId;
        $data['loanTenure'] = \App\Models\LoanTenure::find($tenureId);
        $data['commisssion'] =  $this->repository->getAllCommissionLoanDetails()->with('carder:id,name')->whereLoan_type_id($loanId)->where('tenure', $data['loanTenure']->tenure)->where('tenure_type', $data['loanTenure']->emi_option)->where('effective_from', '>=',  $data['loanTenure']->effective_from)->get();
        if (isset($request->model2)) {
            return \Response::json(['view' => view('templates.admin.loan-commission.model2', ['data' => $data])->render(), 'msg_type' => 'success']);
        } else {
            return \Response::json(['view' => view('templates.admin.loan-commission.model', ['data' => $data])->render(), 'msg_type' => 'success']);
        }
    }
    public function update(Request $request)
    {
        $updateEffectiveToDate = date('Y-m-d', strtotime(convertDate($request->effective_from) . "-1 Days"));
        $update = $this->repository->getAllCommissionLoanDetails()->find($request->id)->update(['effective_to' => $updateEffectiveToDate]);
        if ($update) {
            $insert = $this->repository->getAllCommissionLoanDetails()->create([
                'loan_type_id' => $request->loan_type_id,
                'carder_id' => $request->carder_id,
                'tenure_type' => $request->tenure_type,
                'tenure' => $request->tenure,
                'collector_per' => $request->collector_per,
                'effective_from' => date('Y-m-d', strtotime(convertDate($request->effective_from))),
            ]);
            $response =  ($insert) ?  1 :  0;
        } else {
            $response = 0;
        }
        $data = ['data' => $response];
        return response()->json($data);
    }

    public function oldTenureUpdate(Request $request)
    {
        $updateEffectDate = date('Y-m-d', strtotime(convertDate($request->effect_from) . "-1 Days"));
        $update = $this->repository->getAllCommissionLoanDetails()->where(['tenure_type' => $request->tenure_type, 'tenure' => $request->tenure])->whereDate('effective_from', '=', $request->effect_from);
        if ($update->exists()) {
            $update->update(['effective_to' => $updateEffectDate]);
            $response = 1;
        } else {
            $response = 0;
        }
        $data = ['data' => $response];
        return response()->json($data);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $effective_from = date('Y-m-d', strtotime(convertDate($request->effect_from)));
            $carderDetails = $this->repository->getAllCarder()->get(['id']);
            foreach ($carderDetails as $carderValue) {
                $create = [
                    'loan_type_id' => $request->loan_type_id,
                    'carder_id' => $carderValue['id'],
                    'tenure_type' => $request->tenure_type,
                    'tenure' => $request->tenure,
                    'collector_per' => ($request->collector[$carderValue['id']]) ? $request->collector[$carderValue['id']] : "0.000",
                    'effective_from' => $effective_from,
                ];
                $insertData = $this->repository->createCommissionLoanDetails($create);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->with('alert', $ex->Message());
        }
        return redirect()->route('admin.loan.commission.percentage', ['id' => $request->loan_type_id])->with('success', 'Loan Commission Percentage Generated Successfully');
    }

    

    public function monthList(Request $request)

    {
        if (check_my_permission(Auth::user()->id, "315") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }
        $data['title'] = 'Commission | Commission Month End Listing';
        //$data['commsionnmonth']  =  CommisionMonthEnd::orderBy('id','ASC')->get();

        return view('templates.admin.associate.commision_month_list', $data);
    }
    public function list(Request $request)
    {

        if ($request->ajax()) {



            $data = CommisionMonthEnd::select('id', 'created_at', 'month', 'year', 'created_by', 'created_by_id')->orderby('id', 'DESC')->get();



            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    $created_at = date("d/m/Y", strtotime($row->created_at));
                    return $created_at;
                })
                ->rawColumns(['created_at'])
                ->addColumn('month', function ($row) {
                    $monthNum = $row->month;
                    $name = date("F", mktime(0, 0, 0, $monthNum, 10));
                    return $name;
                })
                ->rawColumns(['month'])

                ->addColumn('year', function ($row) {
                    $name = $row->year;
                    return $name;
                })
                ->rawColumns(['year'])


                ->addColumn('created_by', function ($row) {
                    $createdbyname = "";
                    if ($row->created_by == 1) {
                        $createdbyname = "Admin";
                    } elseif ($row->created_by == 2) {
                        $createdbyname = "Branch";
                    } else {

                        $createdbyname = "Sub admin";
                    }

                    return $created_by = $createdbyname;
                })
                ->rawColumns(['created_by'])

                ->addColumn('created_by_id', function ($row) {
                    if ($row->created_by == 1) {
                        $createdbyname = \App\Models\Admin::where('id', $row->created_by_id)->first('username');
                        $createdbyname = $createdbyname->username;
                    } else {
                        $createdbyname = \App\Models\Branch::where('id', $row->created_by_id)->first('name');
                        $createdbyname = $createdbyname->name;
                    }


                    return $createdbyname;
                })


                ->make(true);
        }
    }
    public function assmonth()
    {

        if (check_my_permission(Auth::user()->id, "316") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }

        $stateid = 33;
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        
        
        //echo date('m',  strtotime($globaldate));die;
       // if(date('t',  strtotime($globaldate)) == 30 || date('t',  strtotime($globaldate)) == 31  || date('t',  strtotime($globaldate)) == 29)
        if( date('d', strtotime($globaldate)) == date('t', strtotime($globaldate)) )
        {
            $data['month'] = date('m',  strtotime($globaldate));
        }
        else
        {
            $data['month'] = date('m', strtotime("-1 month", strtotime($globaldate)));
        }
        
        $data['year'] = date('Y', strtotime($globaldate));
        if($data['month'] == 12){
            $data['year'] = $data['year'] - 1;
        }
        $data['exist'] = \App\Models\CommissionLeaserMonthly::where('month', $data['month'])->where('year', $data['year'])->where('is_deleted', 0)->exists();

        if (!$data['exist']) {
            
           // if(date('d',  strtotime($globaldate)) == 30 || date('d',  strtotime($globaldate)) == 31 )
            if(date('d', strtotime($globaldate)) == date('t', strtotime($globaldate))  )
            {
                $data['newmonth'] =  date('M',strtotime($globaldate));
            }     
            else{
                $data['newmonth'] =  date('M', strtotime("-1 month", strtotime($globaldate)));
            }
            $data['month'] =  $data['month'];
        } else {
            $data['newmonth'] = '';
        }


        $data['title'] = 'Commision | Create Month End Commission';
        return view('templates.admin.associate.associatemonthtransfer', $data);
    }

    public function monthtransfersave(Request $request)
    {

        $exist = CommisionMonthEnd::where('month', $request['month_id'])->where('year', $request['year_id'])->exists();

        $created_by_id = Auth::user()->id;
        $AssociateCommisionMonthEnd  = new  CommisionMonthEnd;
        $AssociateCommisionMonthEnd->month = $request['month_id'];
        $AssociateCommisionMonthEnd->year = $request['year_id'];
        $AssociateCommisionMonthEnd->created_by_id = $created_by_id;
        if (!$exist) {
            $AssociateCommisionMonthEnd->save();
            return redirect('admin/associate/commision/month-end-comission-list')->with('success', 'Commission generated successfully');
        } else {
            return back()->with('alert', 'Commission for this month already exist');
        }
    }

    public function dailyacc()
    {

        if (check_my_permission(Auth::user()->id, "318") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }
        $data['dailyaccountsettings']  =  CommissionDailySetting::orderBy('created_at', 'DESC')->get();
        $data['title'] = 'Commission | Daily Account Setting';
        return view('templates.admin.associate.daily_account_setting', $data);
    }

    // public function checkCommissionData(Request $req)
    // {
    //     $getAssociate = \App\Models\Member::where('associate_no',$req->associate)->first();
    //     $checkData = AssociateException::where('associate_id',$getAssociate->id)->where('month',$req->month)->where('year',$req->year)->where('type',$req->value)->first('type');

    //     $chk=0;

    //     if($chk)
    //     {
    //         $chk=1;
    //     }
    //     return response()->json($chk);

    // }

    public function accountsettingsave(Request $request)
    {
        $created_by_id = Auth::user()->id;

        $exist = CommissionDailySetting::where('status', 1)->orderBy('created_at', 'desc')->first();


        if ($exist) {

            CommissionDailySetting::where('id', $exist->id)->update(['status' => 0]);
        }

        $AssociateDailyAccountSetting  = new  CommissionDailySetting;
        $AssociateDailyAccountSetting->min_days = $request['min_days'];
        $AssociateDailyAccountSetting->max_days = $request['max_days'];

        $AssociateDailyAccountSetting->created_by_id = $created_by_id;




        $AssociateDailyAccountSetting->save();

        return back()->with('success', 'Daily Account Setting updated successfully');
    }

    /***********Associate Exception Start
     * 
     *  Moddify by Durgesh -- 22 sep 2023 to 26 sep 2023
     * 
     * **********/
    public function exceptionList(Request $request)

    {

        if (check_my_permission(Auth::user()->id, "329") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }

        $data['title'] = 'Associate Mangement | Exception List';

        return view('templates.admin.associate.commision_exception_list', $data);
    }

    /*********Exception Listing ******** */
    public function exceptionListing(Request $request)
    {

        //commission status change
        $created_by_id = Auth::user()->id;
        $branchdetails = \App\Models\Branch::pluck('name','id');
        $admindetails = \App\Models\Admin::pluck('username','id');
        if (!empty($request->comissionid)) {
            $commissionStatus = AssociateException::find($request->comissionid);
            if ($commissionStatus) {
                $newCommissionlStatus = $commissionStatus->commission_status == 1 ? 0 : 1;

                $msgc = "Associate's Commission  stop by " . Auth::user()->username;
                if ($commissionStatus->commission_status == 1) {
                    $msgc = "Associate's Commission released by " . Auth::user()->username;
                }
                $data1['created_by_id'] = $created_by_id;
                $data1['created_by'] = 1;
                $data1['description'] =  $msgc;
                $data1['associate_exception_id'] =  $request->comissionid;
                $transcation1 = AssociateExceptionLog::create($data1);
                $commissionStatus->update(['commission_status' => $newCommissionlStatus]);
                return response()->json(['commissionStatus' => true]);
            }
        }
        //Fuel status change
        if (!empty($request->fuelid)) {
            $fuelStatus = AssociateException::find($request->fuelid);

            if ($fuelStatus) {
                $newFuelStatus = $fuelStatus->fuel_status == 1 ? 0 : 1;

                $msg = "Associate's fuel  stop by " . Auth::user()->username;
                if ($fuelStatus->fuel_status == 1) {
                    $msg = "Associate's fuel released  by " . Auth::user()->username;
                }

                $data1['created_by_id'] = $created_by_id;
                $data1['created_by'] = 1;
                $data1['description'] =  $msg;
                $data1['associate_exception_id'] = $request->fuelid;
                $transcation1 = AssociateExceptionLog::create($data1);
                $fuelStatus->update(['fuel_status' => $newFuelStatus]);

                return response()->json(['fuelStatus' => true]);
            }
        }
        if ($request->ajax()) {
            // fillter array            
            $arrFormData['name'] = $request->name;
            $arrFormData['associate_code'] = $request->associate_code;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['member_export'] = $request->member_export;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = AssociateException::has('seniorData')->with(['seniorData:id,first_name,last_name,associate_no,current_carder_id'])->where('status',1);
                
                /******* fillter query start ****/
                if ($arrFormData['associate_code'] != '') {
                    $associate_no = $arrFormData['associate_code'];
                    $data = $data->whereHas('seniorData', function ($query) use ($associate_no) {
                        $query->where('associate_no', $associate_no);
                    });
                }                
                if (!empty($arrFormData['name'])) {
                    $associateName = $arrFormData['name'];
                    $data = $data->whereHas('seniorData', function ($query) use ($associateName) {
                        $query->where(function ($subQuery) use ($associateName) {
                            $subQuery->where(function ($nameQuery) use ($associateName) {                                
                                $nameQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $associateName . '%']);
                            })->orWhere('first_name', 'LIKE', '%' . $associateName . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $associateName . '%');
                        });
                    });
                }
                $data1 = $data->count('id');
                $count = $data1;
                $totalCount = $data1;
                $token = session()->get('_token');
                Cache::put('Associate_Exception_List_count' . $token, $totalCount);
                $export = $data->orderby('created_at', 'DESC')->get();                
                $data = $data->orderby('created_at', 'DESC')
                    ->offset($_POST['start'])
                    ->limit($_POST['length'])
                    ->get([
                        'id', 
                        'created_at', 
                        'updated_at', 
                        'status', 
                        'associate_id', 
                        'created_by', 
                        'created_by_id', 
                        'fuel_status', 
                        'commission_status', 
                        'reason',
                        'is_cron'
                    ]);
                $sno = $_POST['start'];
                $rowReturn = array();
                $rowReturn2 = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $val['associate_name'] = $row['seniorData'] ? (($row['seniorData']->first_name) . ' ' . ($row['seniorData']->last_name??'')) : 'N/A';
                    $val['associate_code'] = $row['seniorData'] ? $row['seniorData']->associate_no : 'N/A';
                    $carder_id = $row['seniorData'] ? $row['seniorData']->current_carder_id : '0';
                    $val['cardername'] = getCarderName($carder_id);
                    $val['reason'] = $row->reason ?? ' ';
                    $fuelStatus = is_null($row->fuel_status)
                        ? '<span class=" btn list-icons-item" style="color:blue" onclick="fuelStatus(' . $row->id . ')">Add Exception</span>'
                        : ($row->fuel_status == 1
                            ? '<span class=" btn list-icons-item" style="color:red" onclick="fuelStatus(' . $row->id . ')">Stop</span>'
                            : '<span class=" btn list-icons-item" style="color:blue" onclick="fuelStatus(' . $row->id . ')">Release</span>');
                    $val['fuel_status'] = $fuelStatus;
                    $commissionStatus = is_null($row->commission_status)
                        ? '<span class=" btn list-icons-item" style="color:blue" onclick="commissionStatus(' . $row->id . ')">Add Exception</span>'
                        : ($row->commission_status == 1
                            ? '<span class=" btn list-icons-item" style="color:red" onclick="commissionStatus(' . $row->id . ')">Stop</span>'
                            : '<span class="btn list-icons-item" style="color:blue" onclick="commissionStatus(' . $row->id . ')">Release</span>');
                    $val['commission_status'] = $commissionStatus;
                    if($row->is_cron == 0) {
                        if ($row->created_by == 1) {
                            // $createdbyname = \App\Models\Admin::where('id', $row->created_by_id)->first('username');
                            // $createdbyname = $createdbyname->username;
                            $createdbyname = $admindetails[$row->created_by_id];
                            $createdby = "Admin";
                        } 
                        if ($row->created_by == 2) {
                            // $createdbyname = \App\Models\Branch::where('id', $row->created_by_id)->first('name');
                            // $createdbyname = $createdbyname->name;
                            $createdbyname = $branchdetails[$row->created_by_id];
                            $createdbyname = $branchdetails[$row->created_by_id];
                            $createdby  = "Branch";
                        }
                    } else {
                        $createdbyname = 'Cron Job';
                        $createdby = "Cron";
                    }
                    $val['user_name'] = $createdbyname;
                    $val['created_by'] = $createdby;

                    $url = URL::to("admin/commision/exception_logs/" . $row->id . "");
                    $btn = '<a class="dropdown-item" href="' . $url . '" title="Log Detail"><i class="icon-eye  mr-2"></i></a> ';
                    $val['action'] = $btn;

                    $rowReturn[] = $val;
                }
                foreach ($export as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $val['associate_name'] = $row['seniorData'] ? (($row['seniorData']->first_name) . ' ' . ($row['seniorData']->last_name??'')) : 'N/A';
                    $val['associate_code'] = $row['seniorData'] ? $row['seniorData']->associate_no : 'N/A';
                    $carder_id = $row['seniorData'] ? $row['seniorData']->current_carder_id : '0';
                    $val['cardername'] = getCarderName($carder_id);
                    $val['reason'] = $row->reason ?? ' ';
                    if($row->is_cron == 0)
                    {
                        if ($row->created_by == 1) {
                            $createdbyname = $admindetails[$row->created_by_id];
                            $createdby = "Admin";
                        } 
                         if ($row->created_by == 2) {
                            $createdbyname = $branchdetails[$row->created_by_id];
                            $createdby  = "Branch";
                        }
                    }
                     else {
                        $createdbyname = 'Cron Job';
                        $createdby = "Cron";
                    }
                    
                    $fuelStatus = is_null($row->fuel_status)
                        ? 'Add Exception'
                        : ($row->fuel_status == 1
                            ? 'Stop'
                            : 'Release');
                    $val['fuel_status'] = $fuelStatus;
                    $commissionStatus = is_null($row->commission_status)
                        ? 'Add Exception'
                        : ($row->commission_status == 1
                            ? 'Stop'
                            : 'Release');
                    $val['commission_status'] = $commissionStatus;
                    if($row->is_cron == 0) {
                        if ($row->created_by == 1) {
                            // $createdbyname = \App\Models\Admin::where('id', $row->created_by_id)->first('username');
                            // $createdbyname = $createdbyname->username;
                            $createdbyname = $admindetails[$row->created_by_id];
                            $createdby = "Admin";
                        } 
                        if ($row->created_by == 2) {
                            // $createdbyname = \App\Models\Branch::where('id', $row->created_by_id)->first('name');
                            // $createdbyname = $createdbyname->name;
                            $createdbyname = $branchdetails[$row->created_by_id];
                            $createdbyname = $branchdetails[$row->created_by_id];
                            $createdby  = "Branch";
                        }
                    } else {
                        $createdbyname = 'Cron Job';
                        $createdby = "Cron";
                    }
                    $val['user_name'] = $createdbyname;
                    $val['created_by'] = $createdby;

                    $url = URL::to("admin/commision/exception_logs/" . $row->id . "");
                    $btn = '<a class="dropdown-item" href="' . $url . '" title="Log Detail"><i class="icon-eye  mr-2"></i></a> ';
                    $val['action'] = $btn;
                    $val['createdby'] = $createdby;
                    $val['createdbyname'] = $createdbyname;
                    $rowReturn2[] = $val;
                }
                Cache::put('Associate_Exception_List' . $token, $rowReturn2);
                $output = array("draw" => $_POST['draw'], "recordsFiltered" => $count, "recordsTotal" => $totalCount, "data" => $rowReturn,);
                return json_encode($output);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }

    /*********Exception Add ******** */
    public function assocaiteException()
    {
        if (check_my_permission(Auth::user()->id, "330") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }

        $data['title'] = 'Associate | Commision Exception';

        return view('templates.admin.associate.associatexceptiontransfer', $data);
    }

    /*********Associate data get  ******** */
    public function getAssociateextansferData(Request $request)
    {

        $branch =   Branch::where('status', 1);
        $data = Member::where('associate_no', $request->code)
            ->where('is_deleted', 0);
        if (Auth::user()->branch_id > 0) {
            $branch = $branch->where('id', Auth::user()->branch_id);
        }

        $branch = $branch->get();

        $data = $data->first();
        $type = $request->type;
        if ($data) {
            if ($data->is_block == 1) {
                return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            } else {
                if ($data->associate_status == 1) {
                    $id = $data->id;
                    $carder = $data->current_carder_id;
                    return \Response::json(['view' => view('templates.admin.associate.partials.associateexceptiontransfer_detail', ['memberData' => $data, 'branch' => $branch])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
                } else {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }

    /*********Associate exception save  ******** */
    public function exceptionSave(Request $request)
    {

        $created_by_id = Auth::user()->id;

        if ($request['type_id'] == 1) {
            $dataexp['fuel_status'] =  1;
            $msg = "Associate's fuel stopped by " . Auth::user()->username;
        } else if ($request['type_id'] == 2) {
            $dataexp['commission_status'] =  1;
            $msg = "Associate's commission stopped by " . Auth::user()->username;
        } else {
            $dataexp['fuel_status'] =  1;
            $dataexp['commission_status'] =  1;
            $msg = "Associate's commission & fuel stopped by " . Auth::user()->username;
        }
        $dataexp['created_by_id'] = $created_by_id;
        $dataexp['created_by'] = 1;
        $dataexp['reason'] =  $request['reason'];
        $dataexp['associate_id'] = $request['associate_id'];
        //  pd($dataexp);
        $exist = AssociateException::where('associate_id', $request['associate_id'])->exists();
        if (!$exist) {
            $transcation = AssociateException::create($dataexp);
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by'] = 1;
            $data1['description'] =  $msg;
            $data1['associate_exception_id'] =  $transcation->id;
            $transcation1 = AssociateExceptionLog::create($data1);
            return redirect('admin/commision/exception-list')->with('success', 'Associate exception created successfully');
        } else {
            return back()->with('alert', 'Associate exception already exist');
        }
    }

    /*********Associate exception log detail   ******** */
    public function exceptionLogDetail($id)
    {
        if (check_my_permission(Auth::user()->id, "331") != "1")
        {
          return redirect()
          ->route('admin.dashboard')
          ->with('alert', "you do not  have permission");
        }


        $data['title'] = 'Associate  Management | Associate Exception Logs';
        $data['log'] = AssociateExceptionLog::where('associate_exception_id', $id)->orderby('created_at', 'DESC')->get();
        $member1 = AssociateException::where('id', $id)->first()->associate_id;
        $data['detail'] = Member::where('id', $member1)->first();
        return view('templates.admin.associate.commision_exception_log', $data);
    }

    /***********Associate Exception End ************* */
}
