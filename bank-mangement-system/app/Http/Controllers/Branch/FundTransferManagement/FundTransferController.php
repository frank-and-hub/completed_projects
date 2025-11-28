<?php

namespace App\Http\Controllers\Branch\FundTransferManagement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Image;
use App\Models\Branch;
use App\Models\AccountHeads;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBank;
use App\Models\CompanyBranch;
use App\Models\Companies;
use App\Models\FundTransfer;
use App\Models\Files;
use App\Models\BranchCash;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Services\ImageUpload;
use App\Http\Traits\DeleteFundTransferTrait;

class FundTransferController extends Controller
{
    use DeleteFundTransferTrait;
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
     * Branch To Ho amount transfer view.
     *
     * @return \Illuminate\Http\Response
     */
    public function branchToHo()
    {
        if (
            !in_array('Branch To Ho', auth()
                ->user()
                ->getPermissionNames()
                ->toArray())
        ) {
            return redirect()->route('branch.dashboard');
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Branch to Bank Deposit Fund Transfer Requests';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        $data['banks'] = SamraddhBank::with('bankAccount')->whereStatus('1')
            ->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')->whereStatus(1)
            ->get();
        return view('templates.branch.fund-transfer.branch_to_ho', $data);
    }
    /**
     * Amount withdrawal view.
     *
     * @return \Illuminate\Http\Response
     */
    public function bankToBank()
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Bank To Bank Transfer';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        $data['banks'] = SamraddhBank::with('bankAccount')->whereStatus('1')
            ->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')->whereStatus(1)
            ->get();
        return view('templates.branch.fund-transfer.bank_to_bank', $data);
    }
    /**
     * Get branch to ho amount transfer listing.
     * Route: ajax call from - /branch/member
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function branchToHoListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            if (!empty($_POST['searchBranchToHo'])) {
                foreach ($_POST['searchBranchToHo'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = FundTransfer::has('company')
                    ->with([
                        'getFirstFileDataCustom:id,file_name',
                        'getSamraddhBankCustom:id,bank_name'
                    ])
                    ->whereBranchId($branch_id)
                    ->where('transfer_type', 0)
                    ->whereStatus(0)
                    ->whereIsDeleted(0)
                    ->orderBy('id', 'DESC');

                if ($arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    $data = $data->whereCompanyId($companyId);
                }

                $count = $data->count('id');
                $data = $data->orderby('id', 'DESC')
                    ->offset($_POST['start'])
                    ->limit($_POST['length'])
                    ->get();
                $totalCount = FundTransfer::whereBranchId($branch_id)
                    ->where('transfer_type', 0)
                    ->whereStatus(0)
                    ->whereIsDeleted(0)
                    ->count('id');
                $sno = $_POST['start'];
                $rowReturn = array();
                $bName = getBranchName(Auth::user()->id);

                if ($bName) {
                    $branch_name = $bName->name;
                }
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['date'] = date('d/m/Y', strtotime($row->transfer_date_time));
                    $val['branch'] = "$branch_name ( $row->branch_code )";
                    // $companyName = Companies::where('id', $row->company_id)->get('name');
                    // $val['company'] = $companyName[0]->name;
                    // $val['branch_code'] = $row->branch_code;
                    $val['created_at'] = date("d/m/Y", strtotime(convertDate($row->transfer_date_time)));
                    if ($row->transfer_mode == 0) {
                        $val['transfer_mode'] = 'Cash';
                    } else {
                        $val['transfer_mode'] = 'Cash';
                    }
                    $val['transfer_amount'] = $row->amount;
                    if ($row['getSamraddhBankCustom']->id && $row['getSamraddhBankCustom']->id != '' || $row['getSamraddhBankCustom']->id != null) {
                        //$bankDetails = getSamraddhBank($row->head_office_bank_id);
                        $val['bank'] = $row['getSamraddhBankCustom']->bank_name;
                    } else {
                        $val['bank'] = 'N/A';
                    }
                    $val['bank_account_number'] = $row->head_office_bank_account_number;
                    if ($row->status == 0) {
                        $val['status'] = 'Pending';
                    } elseif ($row->status == 1) {
                        $val['status'] = 'Approved';
                    } else {
                        $val['status'] = '';
                    }
                    if (isset($row['getFirstFileDataCustom']->id)) {
                        $val['bank_slip'] = fileI($row->bank_slip_id);
                    } else {
                        $val['bank_slip'] = 'N/A';
                    }
                    $rowReturn[] = $val;
                }
                $output = array("recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    /**
     * Get bank to bank amount transfer listing.
     * Route: ajax call from - /branch/member
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function bankTobankListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = FundTransfer::whereBranchId($branch_id)
                ->where('transfer_type', 1)
                ->whereIsDeleted(0)
                ->orderby('id', 'DESC');
            return Datatables::of($data)
                ->addColumn('from_bank', function ($row) {
                    if (getBranchName($row->from_bank_id)) {
                        return getBranchName($row->from_bank_id)->name;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['from_bank'])
                ->addColumn('bank_account_number', function ($row) {
                    return $row->from_bank_account_number;
                })
                ->rawColumns(['bank_account_number'])
                ->addColumn('transfer_mode', function ($row) {
                    if ($row->transfer_mode == 0) {
                        return 'Loan';
                    } elseif ($row->transfer_mode == 1) {
                        return 'Miro';
                    } else {
                        return 'None';
                    }
                })
                ->rawColumns(['transfer_mode'])
                ->addColumn('to_bank', function ($row) {
                    if (getBranchName($row->to_bank_id)) {
                        return getBranchName($row->to_bank_id)->name;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['to_bank'])
                ->addColumn('bank_account_number', function ($row) {
                    return $row->to_bank_account_number;
                })
                ->rawColumns(['bank_account_number'])
                ->addColumn('transfer_amount', function ($row) {
                    return $row->amount;
                })
                ->rawColumns(['transfer_amount'])
                ->addColumn('remark', function ($row) {
                    return $row->remark;
                })
                ->rawColumns(['remark'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } elseif ($row->status == 1) {
                        $status = 'Approved';
                        // } elseif ($row->status == 2) {
                        //     $status = 'Admin Updated';
                        // } elseif ($row->status == 3) {
                        //     $status = 'Rejected';
                    } else {
                        $status = '';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->make(true);
        }
    }
    /**
     * Create Branch To Ho Form.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBranchToHo()
    {
        if (
            !in_array('Branch to Bank Fund Transfer', auth()
                ->user()
                ->getPermissionNames()
                ->toArray())
        ) {
            return redirect()->route('branch.dashboard');
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Branch to Bank Deposit Fund Transfer';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')
            ->where('manager_id', Auth::user()->id)
            ->get();
        $companyData = CompanyBranch::whereBranchId($data['branch_id'])
            ->get(['company_id', 'branch_id'])->toArray();
        $data['company'] = [];
        foreach ($companyData as $row) {
            $company = Companies::where('id', $row['company_id'])->whereStatus('1')->where('delete', '0')->get(['id', 'name']) ?? '';
            if ($company != "") {
                $data['company'][] = $company;
            }

        }

        $data['banks'] = SamraddhBank::with('bankAccount')->whereStatus('1')
            ->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')->whereStatus(1)
            ->get();
        $microPendingAmountRes = FundTransfer::where('transfer_type', 0)->where('transfer_mode', 1)
            ->whereIsDeleted(0)
            ->whereStatus(0)
            ->get();
        $loanPendingAmountRes = FundTransfer::where('transfer_type', 0)->where('transfer_mode', 0)
            ->whereIsDeleted(0)
            ->whereStatus(0)
            ->get();
        $microPendingAmount = 0;
        foreach ($microPendingAmountRes as $key => $value) {
            $microPendingAmount = $microPendingAmount + $value->amount;
        }
        $loanPendingAmount = 0;
        foreach ($loanPendingAmountRes as $key => $value) {
            $loanPendingAmount = $loanPendingAmount + $value->amount;
        }
        return view('templates.branch.fund-transfer.createbranchtoho', $data);
    }
    /**
     * Get Loan Micro Day Book Amount from Date.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLoanMicroAmount(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $microPendingAmountRes = FundTransfer::whereBranchId($getBranchId)
            ->where('transfer_type', 0)
            ->where('transfer_mode', 1)
            ->whereStatus(0)
            ->whereIsDeleted(0)
            ->get();
        $loanPendingAmountRes = FundTransfer::whereBranchId($getBranchId)
            ->where('transfer_type', 0)
            ->where('transfer_mode', 0)
            ->whereStatus(0)
            ->whereIsDeleted(0)
            ->get();
        $microPendingAmount = 0;
        foreach ($microPendingAmountRes as $key => $value) {
            $microPendingAmount = $microPendingAmount + $value->amount;
        }
        $loanPendingAmount = 0;
        foreach ($loanPendingAmountRes as $key => $value) {
            $loanPendingAmount = $loanPendingAmount + $value->amount;
        }
        $microLoanRes = BranchCash::select('balance', 'loan_balance')->whereBranchId($getBranchId->id)
            ->whereDate('entry_date', '<=', $date)->orderBy('entry_date', 'desc')
            ->first();
        if ($microLoanRes) {
            $microDayBookCurrentAmount = $microLoanRes->balance - $microPendingAmount;
            $loanDayBookCurrentAmount = $microLoanRes->loan_balance - $loanPendingAmount;
        } else {
            $microDayBookCurrentAmount = 0;
            $loanDayBookCurrentAmount = 0;
        }
        $return_array = compact('microDayBookCurrentAmount', 'loanDayBookCurrentAmount');
        return json_encode($return_array);
    }
    /**
     * Create Bank To Bank Form.
     *
     * @return \Illuminate\Http\Response
     */
    public function createBankToBank()
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branch_id'] = $getBranchId->id;
        $data['title'] = 'Bank To Bank Transfer';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        $data['banks'] = SamraddhBank::with('bankAccount')->whereStatus('1')
            ->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')->whereStatus(1)
            ->get();
        return view('templates.branch.fund-transfer.createbanktobank', $data);
    }
    public function fundTransferHeadOffice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'branch_code' => 'required',
            'transfer_mode' => 'required',
            'transfer_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return back()
                ->with('alert', 'Please fill the correct values!');
        }
        $bankSlipId = null;
        if ($request->hasFile('bank_slip')) {
            $validator = Validator::make($request->all(), [
                'bank_slip' => 'mimes:jpeg,jpg,png,gif|required',
            ]);
            if ($validator->fails()) {
                return back()
                    ->with('alert', 'Please upload only Jpeg,jpg,png file!');
            }
            $bankSlipImage = $request->file('bank_slip');
            $bankSlipName = time() . '.' . $bankSlipImage->getClientOriginalExtension();
            // $bankSlipLocation = 'asset/fund_transfer/' . $bankSlipName;
            $bankSlipLocation = 'fund_transfer';
            $return = ImageUpload::upload($bankSlipImage, $bankSlipLocation, $bankSlipName);
            // $return = Image::make($bankSlipImage)->save($bankSlipLocation);
            if ($return) {
                $bankSlip = Files::create(
                    [
                        'file_name' => $bankSlipName,
                        'file_path' => $bankSlipLocation,
                        'file_extension' => $bankSlipImage->getClientOriginalExtension()
                    ]
                );
                $bankSlipId = $bankSlip->id;
            }
        }
        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $branch = getBranchDetail($request->branch_id);
        $data = array();
        $t = date("H:i:s");
        $data['transfer_type'] = 0;
        $data['branch_id'] = $request->branch_id;
        $data['branch_code'] = $request->branch_code;
        $data['transfer_date_time'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $data['transfer_mode'] = $request->transfer_mode;
        $data['amount'] = $request->transfer_amount;
        $data['loan_day_book_amount'] = $request->loan_daybook_amount;
        $data['micro_day_book_amount'] = $request->micro_daybook_amount;
        $data['head_office_bank_id'] = $request->bank;
        $data['head_office_bank_account_number'] = $request->from_Bank_account_no;
        $data['bank_slip_id'] = $bankSlipId;
        $data['company_id'] = $request->company_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $response = FundTransfer::create($data);
        $encodeDate = json_encode($data);
        \App\Models\FundTransferLog::create([
            'funds_transfer_id' => $response->id,
            'type' => $response->transfer_type, // 0 : branch to ho , 1 : bank to bank
            'old_value' => $encodeDate,
            'new_value' => $encodeDate,
            'amount' => $response->amount,
            'title' => 'Create Branch to Head Office Payment Request',
            'remark' => $response->remark ?? '',
            'created_by' => auth()->user()->role_id == 3 ? '2' : '1',
            'created_by_id' => auth()->user()->id,
            'created_at' => date('Y-m-d ' . date('H:i:s') . "", strtotime(convertdate($response->created_at))),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        if ($branch->first_login == '0') {
            $branch->update(['first_login' => '1']);
        }
        branchbalancecrone($branch->manager_id, Permission::all());
        if ($response) {
            return redirect()->route('branch.fundtransfer.branchtoho')
                ->with('success', 'Fund transfer request created successfully!');
        } else {
            return back()
                ->with('alert', 'Fund transfer request not created!');
        }
    }
    public function fundTransferBankToBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_bank' => 'required',
            'from_Bank_account_no' => 'required',
            'from_cheque_number' => 'required',
            'rtgs_neft_charge' => 'required',
            'bank_transfer_amount' => 'required',
            'to_bank' => 'required',
            'to_Bank_account_no' => 'required',
            'to_cheque_number' => 'required',
            'bank_receive_amount' => 'required',
            'remark' => 'required',
        ]);
        if ($validator->fails()) {
            return back()
                ->with('alert', 'Please fill the correct values!');
        }
        $data = array();
        $t = date("H:i:s");
        $data['transfer_type'] = 1;
        $data['transfer_date_time'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $data['branch_id'] = $request->branch_id;
        $data['branch_code'] = $request->branch_code;
        $data['from_bank_id'] = $request->from_bank;
        $data['from_Bank_account_number'] = $request->from_Bank_account_no;
        $data['from_cheque_utr_no'] = $request->from_cheque_number;
        $data['to_bank_id'] = $request->to_bank;
        $data['to_Bank_account_number'] = $request->to_Bank_account_no;
        $data['to_cheque_utr_no'] = $request->to_cheque_number;
        $data['rtgs_neft_charge'] = $request->rtgs_neft_charge;
        $data['transfer_amount'] = $request->bank_transfer_amount;
        $data['receive_amount'] = $request->bank_receive_amount;
        $data['remark'] = $request->remark;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($request->date)));
        $response = FundTransfer::create($data);
        if ($response) {
            //return redirect()->route('branch.fund.transfer')->with('success', 'Fund transfer request created successfully!');
        } else {
            return back()->with('alert', 'Fund transfer request not created!');
        }
    }
    /**
     * fund transfer report view.
     *
     * @return \Illuminate\Http\Response
     */
    public function fundtransfer_report()
    {
        if (!in_array('Report', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $getBranchId = (Auth::user()->id);
        $branch_id = $getBranchId;
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data['branchName'] = getBranchName($branch_id)->name;
        $data['branchCode'] = getBranchCode($getBranchId->id)->branch_code;
        $data['title'] = 'Fund Transfer Report';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        return view('templates.branch.fund-transfer.fundtransferreport', $data);
    }
    public function fundTransferReportListing(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = FundTransfer::has('company')
                    ->with([
                        'BranchNameByBrachAutoCustom:id,name',
                        'getFirstFileDataCustom:id,file_name',
                        'getSamraddhBankCustom:id,bank_name',
                        'getSamraddhBankCustomZeroMode:id,bank_name'
                    ])
                    ->whereBranchId($branch_id)
                    ->whereIsDeleted(0)
                    ->orderBy('id', 'DESC');
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->whereStatus($status);
                }
                if ($arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    $data = $data->whereCompanyId($companyId);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }

                $count = $data->orderby('id', 'DESC')->count();
                $data = $data->orderby('id', 'DESC')
                    ->offset($_POST['start'])
                    ->limit($_POST['length'])
                    ->get();
                $totalCount = FundTransfer::whereBranchId($branch_id)
                    ->where('transfer_type', 0)
                    ->whereIsDeleted(0)
                    ->count('id');
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    //echo $row['getSamraddhBankCustom']->bank_name;$row['getSamraddhBankCustomZeroMode']->bank_name
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    if ($row->transfer_type == 0) {
                        $val['request_type'] = 'Branch to Bank Deposit';
                    } else {
                        $val['request_type'] = 'Bank To Bank';
                    }
                    if ($row['BranchNameByBrachAutoCustom']->id) {
                        $val['branch_name'] = $row['BranchNameByBrachAutoCustom']->name;
                    } else {
                        $val['branch_name'] = 'N/A';
                    }
                    $val['branch_code'] = $row->branch_code;
                    $val['loan_day_book_amount'] = $row->loan_day_book_amount;
                    $val['micro_day_book_amount'] = $row->micro_day_book_amount;
                    if ($row->transfer_type == 0) {
                        $val['transfer_amount'] = $row->amount;
                    } else {
                        $val['transfer_amount'] = $row->transfer_amount;
                    }
                    if ($row->company_id != '') {
                        $companyName = Companies::where('id', $row->company_id)->get('name');
                        $val['company'] = $companyName[0]->name;
                    } else {
                        $val['company'] = 'N/A';
                    }
                    $val['transfer_date_time'] = date("d/m/Y", strtotime(convertDate($row->transfer_date_time)));
                    if ($row->transfer_type == 0) {
                        if ($row->transfer_mode == 0) {
                            $val['transfer_mode'] = 'Cash';
                        } else {
                            $val['transfer_mode'] = 'Cash';
                        }
                    } else {
                        if ($row->btb_tranfer_mode == 0) {
                            $val['transfer_mode'] = 'Cheque';
                        } else {
                            $val['transfer_mode'] = 'Online Transfer';
                        }
                    }
                    if ($row->transfer_type == 0) {
                        $val['receive_amount'] = $row->amount;
                    } else {
                        $val['receive_amount'] = $row->receive_amount;
                    }
                    if ($row->transfer_mode == 0) {
                        // $bank = SamraddhBank::where('id', $row->to_bank_id)
                        //    ->first('bank_name');;
                        if ($row['getSamraddhBankCustomZeroMode']->id) {
                            $val['receive_bank_name'] = $row['getSamraddhBankCustomZeroMode']->bank_name;
                        }
                    } else {
                        // $bank = SamraddhBank::where('id', $row->head_office_bank_id)
                        //  ->first('bank_name');;
                        if ($row['getSamraddhBankCustom']->bank_name) {
                            $val['receive_bank_name'] = $row['getSamraddhBankCustom']->bank_name;
                        }
                    }
                    if ($row->transfer_mode == 0) {
                        $val['receive_bank_acc'] = $row->to_bank_account_number;
                    } else {
                        $val['receive_bank_acc'] = $row->head_office_bank_account_number;
                    }
                    $val['request_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                    if (isset($row['getFirstFileDataCustom']->id)) {
                        $val['bank_slip'] = fileI($row->bank_slip_id);
                    } else {
                        $val['bank_slip'] = 'N/A';
                    }
                    //$val['approve_reject_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } elseif ($row->status == 1) {
                        $status = 'Approved';
                    } elseif ($row->status == 3) {
                        $status = 'Deleted';
                    } else {
                        $status = '';
                    }
                    $val['status'] = $status;
                    $rowReturn[] = $val;
                }
                $output = array("recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    public function getbranchbankbalanceamount(Request $request)
    {
        /*
            $globaldate = $request->entrydate;
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $branch_id = $request->branch_id;
            $company_id = $request->company_id;
            $getBranchAmount = \App\Models\Branch::whereId($branch_id)->first();

            $Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;
            $balance = \App\Models\BranchCurrentBalance::whereBranchId( $branch_id)->whereCompanyId( $company_id)->whereDate('entry_date', '<=', $entry_date)->sum('totalAmount');
            $return_array = ['balance' => $balance];
            return json_encode($return_array); 
        */

        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $branch_id = $request->branch_id;
        $company_id = $request->company_id;
        $getBranchAmount = \App\Models\Branch::whereId($branch_id)->first();
        $Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;

        $startDate = ($company_id == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
        $balance = \App\Models\BranchCurrentBalance::whereBranchId($branch_id)
            ->whereCompanyId($company_id)
            ->when($startDate != '', function ($q) use ($startDate) {
                $q->whereDate('entry_date', '>=', $startDate);
            })
            ->where('entry_date', '<=', $entry_date);

        if ($company_id != '') {
            $balance = $balance->whereCompanyId($company_id);
        }
        $balance = $balance->sum('totalAmount');
        // $getBranchAmount = \App\Models\Branch::findorfail($branch_id);
        $return_array = ['balance' => number_format(($balance + $Amount), 2, '.', '')];
        return json_encode($return_array);
    }
    public function getBankAccountNo(Request $request)
    {
        $bank_id = $request->bank_id;

        $accountNo = \App\Models\SamraddhBankAccount::select('account_no', 'id')->where('bank_id', $bank_id)->whereStatus(1)->get();
        return json_encode($accountNo);
    }
    public function getBankListByCompanyId(Request $request)
    {
        $data = SamraddhBank::where('company_id', $request->company_id)->whereStatus(1)->get(['id', 'bank_name']);
        return json_encode($data);
    }
    public function fundtransferlogs($id)
    {
        $i = base64_decode($id);
        $data['log'] = \App\Models\FundTransferLog::whereFundsTransferId($i)->get();
        if (count($data['log']) == 0) {
            return redirect()->back()->with('alert', 'Record Not Found !');
        }
        $data['title'] = "Fund Transfer Request for " . ($data['log'][0]['type'] == 1 ? "Bank To Bank" : "Branch To Head Office");
        return view('templates.branch.fund-transfer.logs', $data);
    }
    public function deleteFundTransfer(Request $request)
    {
        $response = json_decode($this->delete($request));
        return back()->with($response->type, $response->msg);
    }
}
// branch 