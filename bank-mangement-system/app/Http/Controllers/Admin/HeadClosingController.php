<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AccountHeads;
use App\Models\HeadClosing;
use App\Exports\HeadClosingBalanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use DB;
use Illuminate\Support\Facades\Schema;
class HeadClosingController extends Controller
{
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    public function index()
    {
        if (Auth::user()->id == 16 || Auth::user()->id == 14 || Auth::user()->id == 1) {
            $data['title'] = 'View Head Closing Amount';
            $data['type'] = 1;
            return view('templates.admin.head_closing.index', $data);
        } else {
            return redirect()->route('admin.dashboard')->with('alert', "Sorry you don't have permission to access");
        }
    }
    public function getHeadClosingList(Request $request)
    {
        $date = explode(' - ', $request->financial_year);
        $start_y = $date[0];
        $end_y = $date[1];
        $type = $request->type;
        $companyList = $company_id = $request->company_id;
        $branchId = $branchId = $request->branch_id;

        $arrayCompanyList = explode(' ', $companyList);
        $companyList = array_map(function ($value) {
            return intval($value);
        }, $arrayCompanyList);
        $get_yeardata = HeadClosing::where('start_year', $start_y)
            ->where('status', 1)->when($branchId,function($query) use($branchId){
                $query->where('branch_id',$branchId);
            })
            ->where('company_id', $company_id)
            ->where('end_year', $end_y)
            ->count('id');
        $data = AccountHeads::where('status', 0)
            ->where('labels', 1)->whereIn('head_id',[1,2])
            ->when($company_id, function ($q) use ($companyList) {
                $q->getCompanyRecords("CompanyId", $companyList);
            })
            ->get()
        ;
        $companyDetail = \App\Models\Companies::findorFail($company_id);
        $view = [
            'data' => $data,
            'start_y' => $start_y,
            'end_y' => $end_y,
            'type' => $type,
            'company_id' => $company_id,
            'companyDetail' => $companyDetail,
            'branchId' => $branchId ?? '',
        ];
        if ($get_yeardata > 0 && $request->type != 1) {
            return \Response::json(['view' => '', 'msg_type' => 'closing_add']);
        } elseif ($get_yeardata == 0 && $request->type == 1) {
            return \Response::json(['view' => '', 'msg_type' => 'error']);
        } else {
            Session::put('getHeadClosingList', $view);
            return \Response::json(['view' => view('templates.admin.head_closing.partials.head', $view)->render(), 'msg_type' => 'success']);
        }
    }
    public function add()
    {
        if (Auth::user()->id == 16 || Auth::user()->id == 14 || Auth::user()->id == 1) {
            $data['title'] = 'Add Head Closing Amount';
            $data['type'] = 0;
            return view('templates.admin.head_closing.index', $data);
        } else {
            return redirect()->route('admin.dashboard')->with('alert', "Sorry you don't have permission to access");
        }
    }
    /**
     * This function saves head closing data to the database.
     *
     * @param Request $request The request object containing the data to be saved.
     * @return JsonResponse A JSON response indicating success or failure.
     */
    public function headClosingSave(Request $request)
    {
        // Get start and end year from request.
        $start_y = $request->sdate;
        $end_y = $request->edate;
        // Start transaction.
        //DB::beginTransaction();
        // try {
        // Check if request contains head_id.
        if ($request->has('head_id')) {
            // Loop through head_id array.
            foreach ($request->input('head_id') as $key => $option) {
                // Get cr_amount and dr_amount for current head_id.
                // $cr_amount = $request->input('head_amount_cr')[$key];
                // $dr_amount = $request->input('head_amount_dr')[$key];
                $amount = $request->input('head_amount')[$key];
                // If both amounts are 0 or negative, skip this iteration.
                // Create array of data to be saved.
                $saveHead = [
                    'head_id' => $option,
                    // 'cr_amount' => $cr_amount,
                    // 'dr_amount' => $dr_amount,
                    'branch_id' => $request->branchId,
                    'amount' => $amount,
                    'company_id' => $request->company_id,
                    'start_year' => $start_y,
                    'end_year' => $end_y,
                    'created_by' => 1,
                    'created_by_id' => Auth::user()->id,
                ];
                // Save data to database.
                HeadClosing::create($saveHead);
            }
        }
        // Commit transaction.
        //     DB::commit();
        // } catch (\Exception $ex) {
        //     // Rollback transaction on exception.
        //     DB::rollback();
        //     // Return error response.
        //     return \Response::json([
        //         'view' => $ex->getMessage(),
        //         'msg_type' => 'error'
        //     ]);
        // }
        // Return success response.
        return \Response::json([
            'view' => '',
            'msg_type' => 'success'
        ]);
    }
    public function resetClosingHead(Request $request)
    {
        $financial = explode(' - ', $request->financial_year);
        $deleteFinancial = HeadClosing::where(['start_year' => $financial[0], 'end_year' => $financial[1]])->delete();
        if ($deleteFinancial) {
            return \Response::json(['view' => '', 'msg_type' => 'success']);
        }
        return \Response::json(['view' => '', 'msg_type' => 'error']);
    }
    public function export(Request $request)
    {
        $view = Session::get('getHeadClosingList');
        return Excel::download(new HeadClosingBalanceReportExport($view), 'HeadClosingBalanceReportExportReport.xlsx');
    }
}