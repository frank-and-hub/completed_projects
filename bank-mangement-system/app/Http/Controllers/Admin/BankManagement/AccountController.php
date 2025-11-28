<?php

namespace App\Http\Controllers\Admin\BankManagement;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\RepositoryInterface;
use Yajra\DataTables\DataTables;
use App\Models\AccountHeads;

class AccountController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->middleware('auth');
    }

    /*------------------- Display the index page of listing bank management -----------------*/
    public function index()
    {
        $data['title'] = "Bank Account Management";
        return view('templates.admin.bank_management.bank-accounts', $data);
    }

    /*----------- Listing ---------------*/
    /*---------- Table samraddh_bank_accounts -----------*/
    public function listing(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->repository->getAllSamraddhBankAccounts()->with(['getCompanyDetail:id,name', 'samraddhbank'])
                ->when(($request->companyId != '0'), function ($q) use ($request) {
                    $q->where('company_id', $request->companyId);
                })
                ->when(isset($request->bankId), function ($q) use ($request) {
                    $q->where('bank_id', $request->bankId);
                })
                ->orderBy('id', 'desc')
                ->get(['id', 'bank_id', 'account_no', 'ifsc_code', 'branch_name', 'address', 'status', 'created_at', 'company_id']);


            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('bank_id', function ($row) {
                    // $bankDetails = $this->repository->getAllSamraddhBank()->whereId($row->bank_id)->first(["bank_name",'id']); 
    
                    // return $bankDetails->bank_name??'N/A';
                    return $row->samraddhbank->bank_name ?? 'N/A';
                })
                ->rawColumns(['bank_id'])
                ->addColumn('company_id', function ($row) {
                    $companyDetails = $this->repository->getAllCompanies()->whereId($row->company_id)->first(['name', 'id']);
                    return $companyDetails->name ?? "N/A";
                })
                ->rawColumns(['company_id'])
                ->addColumn('created_at', function ($row) {
                    $created_at = date('d/m/Y', strtotime($row->created_at));

                    return $created_at;
                })
                ->rawColumns(['created_at'])
                ->addColumn('action', function ($row) {
                    $btn = "";
                    $btn = "<div class='list-icons'>
                <div class='dropdown'><a href='#' class='list-icons-item' data-toggle='dropdown'><i class='icon-menu9'></i></a>
                    <div class='dropdown-menu dropdown-menu-right'>
                 
                <button class='status_data btn btn-white w-100 legitRipple text-left' id='status_data' data-id='$row->id' data-status='$row->status' 
                title='Status Change' ><i class='far fa-thumbs-up mr-2'></i>Status Change</button>

                <button class='edit_data btn btn-white w-100 legitRipple text-left' data-toggle='modal' data-target='#editBankAccountModel' id='edit-data' data-id='$row->id' data-bank-id='$row->bank_id' data-company-id='$row->company_id'
				title='Edit' ><i class='fa fa-edit mr-2'></i>Edit</button> 
                
                </div>
                </div>
                </div>";
                    return $btn;
                })
                ->rawColumns(['action'])

                ->make(true);
        }
    }

    // // Fetch the bank data on select the company 
    // public function fetch(Request $request)
    // {
    //    $fetch = $this->repository->getAllSamraddhBank()->whereCompany_id($request->company_id)->get(['id','bank_name']);
    //    return response()->json($fetch); 
    // }

    /*
     * Insert the data 
     * Table name is Samraddh_banks
     */
    public function create(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'accountCompanyId' => 'required',
                'accountBankId' => 'required',
                'account_no' => 'required|unique:samraddh_bank_accounts,account_no',
                'ifsc_code' => 'required',
                'branch_name' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $bank_name = $this->repository->getAllSamraddhBank()->where('id', $request->accountBankId)->first(['id', 'bank_name', 'account_head_id']);
            //print_r($bank_name);die;

            $lastAccountHeadId = $bank_name->account_head_id;
            $fetchAccountHeadsData = AccountHeads::where('head_id', $lastAccountHeadId)->first(['head_id', 'labels', 'cr_nature', 'dr_nature', 'id']);
            $lastHeadId = AccountHeads::orderBy('head_id', 'desc')->first('head_id');

            $insertAccountHeads = AccountHeads::create([
                'sub_head' => $request->account_no,
                'head_id' => $lastHeadId->head_id + 1,
                'parent_id' => $lastAccountHeadId,
                'parentId_auto_id' => $fetchAccountHeadsData->id,
                'labels' => $fetchAccountHeadsData->labels + 1,
                'status' => 0,
                'cr_nature' => $fetchAccountHeadsData->cr_nature,
                'dr_nature' => $fetchAccountHeadsData->dr_nature,
                'is_move' => 0,
                'company_id' => '[' . $request->accountCompanyId . ']',
                'can_disable_status' => 0,
                'basic_heads' => 2,
                'created_at' => $request->created_at,
                'updated_at' => $request->created_at
            ]);

            $insertAccount = $this->repository->getAllSamraddhBankAccounts()->create([
                'account_head_id' => $lastHeadId->head_id + 1,
                'bank_id' => $request->accountBankId,
                'company_id' => $request->accountCompanyId,
                'account_no' => $request->account_no,
                'ifsc_code' => $request->ifsc_code,
                'branch_name' => $request->branch_name,
                'address' => $request->address,
                'created_at' => $request->created_at,
                'updated_at' => $request->created_at
            ]);

            return response()->json(['data' => ($insertAccountHeads && $insertAccount) ? 1 : 0]);
        }
    }

    //------------ Collect data for edit value ----------------------
    public function collect(Request $request)
    {
        $bank = $this->repository->getAllSamraddhBank()->whereId($request->bank_id)->first(['id', 'bank_name']);
        $company = $this->repository->getAllCompanies()->whereId($request->company_id)->first(['id', 'name']);
        $accounts = $this->repository->getAllSamraddhBankAccounts()->whereId($request->id)->first(['id', 'account_no', 'ifsc_code', 'branch_name', 'address']);

        return response()->json(['bank_id' => ($bank->id) ?? '', 'bank_name' => ($bank->bank_name) ?? '', 'company_id' => ($company->id) ?? '', 'company_name' => ($company->name) ?? '', 'account_no' => ($accounts->account_no) ?? '', 'ifsc_code' => ($accounts->ifsc_code) ?? '', 'branch_name' => ($accounts->branch_name) ?? '', 'address' => ($accounts->address) ?? '']);
    }

    //------------- Update the data ---------------
    public function update(Request $request)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'account_no' => 'required|unique:samraddh_bank_accounts,account_no,' . $request->account_id,
                'ifsc_code' => 'required',
                'branch_name' => 'required',
                'address' => 'required',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $update = $this->repository->getAllSamraddhBankAccounts()->where('id', $request->account_id)->update(['account_no' => $request->account_no, 'ifsc_code' => $request->ifsc_code, 'branch_name' => $request->branch_name, 'address' => $request->address, 'updated_at' => $request->created_at]);

            $checkGetAc = $this->repository->getAllSamraddhBankAccounts()->where('id', $request->account_id)->first();

            $insertAccountHeads = AccountHeads::where('head_id', $checkGetAc->account_head_id)->update(['sub_head' => $request->account_no, 'updated_at' => $request->created_at]);

            return response()->json(['data' => ($update) ? 1 : 0]);
        }
    }

    /*
     * Status Change 
     */

    public function status(Request $request)
    {
        $data = $this->repository->getAllSamraddhBankAccounts()->whereId($request->id)->first(['id', 'status']);
        if ($data->status == '1') {
            $data->update(['status' => '0']);
            $response = "1";
        } else {
            $data->update(['status' => '1']);
            $response = "1";
        }

        $data = ['data' => ($response) ? $response : "0"];
        return response()->json($data);
    }
}