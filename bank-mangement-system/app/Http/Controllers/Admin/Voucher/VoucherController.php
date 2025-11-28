<?php

namespace App\Http\Controllers\Admin\Voucher;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Illuminate\Support\Facades\Response;
use Validator;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Models\Branch;
use App\Models\SamraddhBank;
use App\Models\Member;
use App\Http\Controllers\Admin\CommanController;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "132") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Receive Voucher Management | Voucher List";
        $data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        return view('templates.admin.voucher.index', $data);
    }
    public function create()
    {
        if (check_my_permission(Auth::user()->id, "131") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Receive Voucher Management | Voucher Request";
        $data['samraddh_bank'] = SamraddhBank::select('id', 'bank_name')->where('status', 1)->get();
        $data['sub_head'] = \App\Models\AccountHeads::select('head_id', 'sub_head', 'company_id')->where([['status', 0], ['parent_id', 86]])->get();
        return view('templates.admin.voucher.create_voucher', $data);
    }
    public function save(Request $request)
    {
        $globaldate = $request->created_at;
        $currentSoftwareDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        $rules = [
            'branch_id' => ['required'],
            'head' => ['required'],
            'date' => ['required'],
            'company_id' => ['required'],
            'payment_mode' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        $companyId = $request->company_id;
        // dd($request);
        DB::beginTransaction();
        try {
            $select_date = $request->date;
            $sub_head = $request->sub_head;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $neft_charge = 0;
            $leaser_id = $ledger_id = $type_id = $request->leaser_id;
            $total_transfer_amount = 0;
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $amount_to_id = NULL;
            $amount_to_name = NULL;
            $amount_from_id = NULL;
            $amount_from_name = NULL;
            $associate_id = NULL;
            $type_id = $bank_id = $bank_id_ac = $bank_ac_id = $empID = NULL;
            $des = '';
            $data['received_mode'] = $payment_mode = $request->payment_mode;
            $data['branch_id'] = $branch_id = $request->branch_id;
            $data['branch_code'] = $request->branch_code;
            $branchDetail = getBranchDetail($branch_id);
            $data['account_head_id'] = $request->head;
            $data['date'] = date("Y-m-d", strtotime(convertDate($request->date)));
            $data['particular'] = $des = $request->particular;
            $data['amount'] = $amount = $request->amount;
            $member_id = NULL;
            if ($request->head == 19) { //Director
                $types = 1;
                $data['director_id'] = $type_id = $request->director_id;
                $type = 14;
                $sub_type = 141;
                $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = getAcountHeadNameHeadId($type_id);
                $directorDetail = \App\Models\ShareHolder::where('head_id', $type_id)->first();
                $member_id = $directorDetail->member_id;
            }
            if ($request->head == 15) { //Shareholder
                $types = 2;
                $data['shareholder_id'] = $type_id = $request->shareholder_id;
                $type = 14;
                $sub_type = 142;
                $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = getAcountHeadNameHeadId($type_id);
                $directorDetail = \App\Models\ShareHolder::where('head_id', $type_id)->first();
                $member_id = $directorDetail->member_id;
            }
            if ($request->head == 32) { //Penal Interest
                $types = 3;
                $data['employee_id'] = $type_id = $empID = $request->emp_id;
                $type = 14;
                $sub_type = 143;
                $description_cr = 'To ' . $request->emp_name . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = $request->emp_name;
            }
            if ($request->head == 27) {  //Bank
                $types = 4;
                $data['bank_id'] = $type_id = $bank_id = $request->bank_id;
                $data['bank_ac_id'] = $bank_id_ac = $bank_ac_id = $request->bank_account;
                $type = 14;
                $sub_type = 144;
                $description_cr = 'To ' . getSamraddhBank($bank_id)->bank_name . ' A/c ' . getSamraddhBankAccountId($bank_id_ac)->account_no . ' Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = getSamraddhBank($bank_id)->bank_name . ' - ' . getSamraddhBankAccountId($bank_id_ac)->account_no;
            }
            if ($request->head == 96) { //Eli Loan
                $types = 5;
                $data['eli_loan_id'] = $type_id = $request->eli_loan_id;
                $type = 14;
                $sub_type = 145;
                $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = getAcountHeadNameHeadId($type_id);
            }
            if ($request->head == 122) { //stationary charge
                $types = 6;
                $type = 21;
                $sub_type = 35;
                $data['member_id'] =  $member_id  = $request->member_auto_id;
                $cgstAmount = $request->cgst_stationary_charge;
                $sgstAmount = $request->sgst_stationary_charge;
                $igstAmount = $request->igst_stationary_charge;
            }
            if ($request->head == 86) {  //indirect expense
                $types = 7;
                $type = 25;
                $sub_type = $request->sub_head;
                $data['expense_head'] = $request->sub_head;
                $description_cr = 'To ' . getAcountHead($request->sub_head) . ' A/c Cr ' . $amount . '/-';
            }
            if ($request->head == 87) { //commision
                $types = 8;
                $type = 26;
                $sub_type = 45;
                $data['associate_id'] = $associate_id =  $request->asso_id;
                $description_cr = 'To ' . $request->asso_name . ' A/c Cr ' . $amount . '/-';
            }
            $data['created_at'] = $created_at;
            // ---------------------head ---------------------------------
            $v_no = NULL;
            $v_date = NULL;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $cheque_date = NULL;
            $cheque_bank_from = NULL;
            $cheque_bank_ac_from = NULL;
            $cheque_bank_ifsc_from = NULL;
            $cheque_bank_branch_from = NULL;
            $cheque_bank_to = NULL;
            $cheque_bank_ac_to = NULL;
            $transction_no = NULL;
            $transction_bank_from = NULL;
            $transction_bank_ac_from = NULL;
            $transction_bank_ifsc_from = NULL;
            $transction_bank_branch_from = NULL;
            $transction_bank_to = NULL;
            $transction_bank_ac_to = NULL;
            $transction_date = NULL;
            $ssb_account_id_to = NULL;
            $cheque_bank_from_id = NULL;
            $cheque_bank_ac_from_id = NULL;
            $cheque_bank_to_name = NULL;
            $cheque_bank_to_branch = NULL;
            $cheque_bank_to_ac_no = NULL;
            $cheque_bank_to_ifsc = NULL;
            $transction_bank_from_id = NULL;
            $transction_bank_from_ac_id = NULL;
            $transction_bank_to_name = NULL;
            $transction_bank_to_ac_no = NULL;
            $transction_bank_to_branch = NULL;
            $transction_bank_to_ifsc = NULL;
            $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $created_at);
            $refId = $daybookRef;

            if ($request->payment_mode == 0) { //cash
                $data['daybook_type'] = $request->daybook;
                $data['daybook_balance'] = $request->branch_total_balance;
                $amount_to_id = $branch_id;
                $amount_to_name = $branchDetail->name . '' . $branchDetail->branch_code;
            }
            if ($request->payment_mode == 1) { //cheque
                $data['cheque_id'] = $request->cheque_no;
                $chequeDetail = \App\Models\ReceivedCheque::where('id', $request->cheque_no)->first();
                $cheque_no = $chequeDetail->cheque_no;
                $cheque_date = date("Y-m-d", strtotime(convertDate($chequeDetail->cheque_deposit_date)));
                $cheque_bank_from = $chequeDetail->bank_name;
                $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = $chequeDetail->branch_name;
                $cheque_bank_to = $chequeDetail->deposit_bank_id;
                $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = getSamraddhBank($cheque_bank_to)->bank_name;
                $cheque_bank_to_branch = getSamraddhBankAccountId($cheque_bank_ac_to)->branch_name;
                $cheque_bank_to_ac_no = getSamraddhBankAccountId($cheque_bank_ac_to)->account_no;
                $cheque_bank_to_ifsc = getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code;
                $cheque_type = 0;
                $cheque_id = $request->cheque_no;
                $data['cheque_bank_name'] = $cheque_bank_from;
                $data['cheque_bank_ac_no'] = $cheque_bank_ac_from;
                $data['cheque_date'] = $cheque_date;
                $data['receive_bank_id'] = $bank_id = $cheque_bank_to;
                $data['receive_bank_ac_id'] = $bank_ac_id =$cheque_bank_ac_to;
                $amount_to_id = $cheque_bank_to;
                $amount_to_name = $cheque_bank_to_name . ' - ' . $cheque_bank_to_ac_no;
            }

            if ($request->payment_mode == 2) { //online
                $data['online_tran_date'] = date("Y-m-d", strtotime(convertDate($request->utr_date)));
                $data['online_tran_no'] = $request->utr_no;
                $data['online_tran_bank_name'] = $request->transaction_bank;
                $data['online_tran_bank_ac_no'] = $request->transaction_bank_ac;
                $data['receive_bank_id'] = $bank_id =$request->online_bank;
                $data['receive_bank_ac_id'] = $bank_ac_id = $request->online_bank_ac;
                $data['slip'] = $request->daybook_type;
                $transction_no = $request->utr_no;
                $transction_bank_from = $request->transaction_bank;
                $transction_bank_ac_from = $request->transaction_bank_ac;
                $transction_bank_to =  $request->online_bank;
                $transction_bank_ac_to =  $request->online_bank_ac;
                $transction_date = date("Y-m-d", strtotime(convertDate($request->utr_date)));
                $transction_bank_to_name = getSamraddhBank($transction_bank_to)->bank_name;
                $transction_bank_to_branch = getSamraddhBankAccountId($transction_bank_ac_to)->branch_name;
                $transction_bank_to_ac_no = getSamraddhBankAccountId($transction_bank_ac_to)->account_no;
                $transction_bank_to_ifsc = getSamraddhBankAccountId($transction_bank_ac_to)->ifsc_code;
                $amount_to_id = $transction_bank_to;
                $amount_to_name = $transction_bank_to_name . ' - ' . $transction_bank_to_ac_no;
            }
            $data['company_id'] = $companyId;
       
            $data['type'] = $types;

            $dataCreate = \App\Models\ReceivedVoucher::create($data);
            $id = $tranId = $dataCreate->id;
            $encodeDate = json_encode($data);

            if ($request->hasFile('bank_slip')) {
                $slip_image = $request->file('bank_slip');
                $slip_filename = $id . '_' . time() . '.' . $slip_image->getClientOriginalExtension();
                // $slip_location = 'asset/voucher/' . $slip_filename;
                $slip_location = 'voucher/';
                $file = $request->file('bank_slip');
                $uploadFile = $file->getClientOriginalName();
                // $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                ImageUpload::upload($slip_image, $slip_location,$slip_filename);
                // $file->move('asset/voucher/', $slip_filename);
                $voucherUpdate = \App\Models\ReceivedVoucher::find($id);
                $voucherUpdate->slip = $slip_filename;
                $voucherUpdate->save();
            }

            if ($request->head != 122) {

                if ($request->payment_mode == 0) {
                    //cash
                    $description_dr = 'Cash A/c Dr ' . $amount . '/-';
                    // branch daybook

                    $brDaybook = CommanController::branchDaybookCreateModified(
                        $refId,
                        $branch_id,
                        $type,
                        $sub_type,
                        $type_id,
                        $associate_id ,
                        $member_id,
                        $branch_id_to = NULL,
                        $branch_id_from = NULL,
                        $amount,
                        $des,
                        $description_dr,
                        $description_cr,
                        'CR',
                        $payment_mode,
                        $currency_code,
                        $v_no,
                        $ssb_account_id_from,
                        $cheque_no,
                        $transction_no,
                        $entry_date,
                        $entry_time,
                        $created_by,
                        $created_by_id,
                        $created_at,
                        $updated_at,
                        $tranId,
                        $ssb_account_id_to,
                        $companyId
                    );

                    // branch cash head + ----------
                    $head1rdC = 2;
                    $head2rdC = 10;
                    $head3rdC = 28;
                    $head4rdC = 71;
                    $head5rdC = NULL;
                    $allTran1 = CommanController::newHeadTransactionCreate(
                        $refId,
                        $branch_id,
                        $bank_id,
                        $bank_ac_id,
                        $head3rdC,
                        $type,
                        $sub_type,
                        $type_id,
                        $associate_id,
                        $member_id,
                        $branch_id_to = NULL,
                        $branch_id_from = NULL,
                        $amount,
                        $des,
                        'DR',
                        $payment_mode,
                        $currency_code,
                        $v_no,
                        $ssb_account_id_from,
                        $cheque_no,
                        $transction_no,
                        $entry_date,
                        $entry_time,
                        $created_by,
                        $created_by_id,
                        $created_at,
                        $updated_at,
                        $tranId,
                        $jv_unique_id,
                        $ssb_account_id_to,
                        $ssb_account_tran_id_to,
                        $ssb_account_tran_id_from,
                        $cheque_type,
                        $cheque_id,
                        $companyId
                    );

                    if ($request->head == 27) { //Bank

                        $getBHead = \App\Models\SamraddhBank::where('id', $bank_id)->first();
                        $headB1 = 2;
                        $headB2 = 10;
                        $headB3 = $request->head;
                        $headB4 = $getBHead->account_head_id;
                        $headB5 = NULL;
                        $bank_id = $bank_id;
                        $bank_ac_id = $bank_id_ac;
                        // bank head entry ++
                        $allTran1 = CommanController::newHeadTransactionCreate(
                            $refId,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            $headB4,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $member_id,
                            $branch_id_to = NULL,
                            $branch_id_from = NULL,
                            $amount,
                            $des,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $jv_unique_id,
                            $ssb_account_id_to,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );
                        // bank daybook entry ++
                        $bankcashDaybook = CommanController::NewFieldAddSamraddhBankDaybookCreateModify(
                            $refId,
                            $bank_id,
                            $bank_id_ac,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $memberId = NULL,
                            $branch_id,
                            $opening_balance = NULL,
                            $amount,
                            $closing_balance = NULL,
                            $des,
                            $description_dr,
                            $description_cr,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $amount_to_id,
                            $amount_to_name,
                            $amount_from_id,
                            $amount_from_name,
                            $v_no,
                            $v_date,
                            $ssb_account_id_from,
                            $cheque_no,
                            $cheque_date,
                            $cheque_bank_from,
                            $cheque_bank_ac_from,
                            $cheque_bank_ifsc_from,
                            $cheque_bank_branch_from,
                            $cheque_bank_to,
                            $cheque_bank_ac_to,
                            $transction_no,
                            $transction_bank_from,
                            $transction_bank_ac_from,
                            $transction_bank_ifsc_from,
                            $transction_bank_branch_from,
                            $transction_bank_to,
                            $transction_bank_ac_to,
                            $transction_date,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $ssb_account_id_to,
                            $cheque_bank_from_id,
                            $cheque_bank_ac_from_id,
                            $cheque_bank_to_name,
                            $cheque_bank_to_branch,
                            $cheque_bank_to_ac_no,
                            $cheque_bank_to_ifsc,
                            $transction_bank_from_id,
                            $transction_bank_from_ac_id,
                            $transction_bank_to_name,
                            $transction_bank_to_ac_no,
                            $transction_bank_to_branch,
                            $transction_bank_to_ifsc,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $jv_unique_id,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );
                    }
                } else {
                    $description_dr = 'Bank A/c Dr ' . $amount . '/-';
                    if ($request->payment_mode == 1) {
                        // $type = 5;

                        $brDaybook = CommanController::branchDaybookCreateModified(
                            $refId,
                            $branch_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $member_id,
                            $branch_id_to = NULL,
                            $branch_id_from = NULL,
                            $amount,
                            $des,
                            $description_dr,
                            $description_cr,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $ssb_account_id_to,
                            $companyId
                        );
                        // cheque
                        $receivedPayment['type'] = 5;
                        $receivedPayment['branch_id'] = $branch_id;
                        $receivedPayment['type_id'] = $tranId;
                        $receivedPayment['cheque_id'] = $request->cheque_no;
                        $receivedPayment['created_at'] = $created_at;
                        $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                        $dataRC['status'] = 3;
                        $receivedcheque = \App\Models\ReceivedCheque::find($request->cheque_no);
                        $receivedcheque->update($dataRC);
                        $gbh = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
                        $empLedger['cheque_id'] = $request->cheque_no;
                        $empLedger['cheque_no'] = $cheque_no;
                        $empLedger['cheque_date'] = $cheque_date;
                        $empLedger['to_bank_name'] = $cheque_bank_to_name;
                        $empLedger['to_bank_ac_no'] = $cheque_bank_to_ac_no;
                        $empLedger['to_bank_ifsc'] = $cheque_bank_to_ifsc;
                        $empLedger['to_bank_id'] = $cheque_bank_to;
                        $empLedger['to_bank_account_id'] = $cheque_bank_ac_to;
                        $empLedger['from_bank_name'] = $cheque_bank_from;
                        $empLedger['from_bank_ac_no'] = $cheque_bank_ac_from;
                        $empLedger['from_bank_ifsc'] = $cheque_bank_ifsc_from;
                        $empLedger['from_bank_id'] = $cheque_bank_from_id;
                        $empLedger['from_bank_ac_id'] = $cheque_bank_ac_from_id;
                        $empLedger['company_id'] = $companyId;
                        // bank Head entry +
                        $allTran2 = CommanController::newHeadTransactionCreate(
                            $refId,
                            $branch_id,
                            $cheque_bank_to,
                            $cheque_bank_ac_to,
                            $gbh->account_head_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $member_id,
                            $branch_id_to = NULL,
                            $branch_id_from = NULL,
                            $amount,
                            $des,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $jv_unique_id,
                            $ssb_account_id_to,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );
                        // bank daybook entry ++
                        $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreate($refId, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $associate_id , $member_id, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                    }

                    if ($request->payment_mode == 2) {
                        $brDaybook = CommanController::branchDaybookCreateModified(
                            $refId,
                            $branch_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $member_id,
                            $branch_id_to = NULL,
                            $branch_id_from = NULL,
                            $amount,
                            $des,
                            $description_dr,
                            $description_cr,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $ssb_account_id_to,
                            $companyId
                        );
                        // online
                        $empLedger['transaction_no'] = $transction_no;
                        $empLedger['transaction_date'] = $transction_date;
                        $empLedger['to_bank_name'] = $transction_bank_to_name;
                        $empLedger['to_bank_ac_no'] = $transction_bank_to_ac_no;
                        $empLedger['to_bank_ifsc'] = $transction_bank_to_ifsc;
                        $empLedger['to_bank_id'] = $transction_bank_to;
                        $empLedger['to_bank_account_id'] = $transction_bank_ac_to;
                        $empLedger['from_bank_name'] = $transction_bank_from;
                        $empLedger['from_bank_ac_no'] = $transction_bank_ac_from;
                        $empLedger['from_bank_ifsc'] = $transction_bank_ifsc_from;
                        $empLedger['from_bank_id'] = $transction_bank_from_id;
                        $empLedger['from_bank_ac_id'] = $transction_bank_from_ac_id;
                        $empLedger['company_id'] = $companyId;

                        $gbh = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();
                        // bank head entry ++
                        $allTran2 = CommanController::newHeadTransactionCreate(
                            $refId,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            $gbh->account_head_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $member_id,
                            $branch_id_to = NULL,
                            $branch_id_from = NULL,
                            $amount,
                            $des,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $jv_unique_id,
                            $ssb_account_id_to,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );
                        // bank daybook entry ++
                        $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify(
                            $refId,
                            $transction_bank_to,
                            $transction_bank_ac_to,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id ,
                            $member_id,
                            $branch_id,
                            $opening_balance = NULL,
                            $amount,
                            $closing_balance = NULL,
                            $des,
                            $description_dr,
                            $description_cr,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $amount_to_id,
                            $amount_to_name,
                            $amount_from_id,
                            $amount_from_name,
                            $v_no,
                            $v_date,
                            $ssb_account_id_from,
                            $cheque_no,
                            $cheque_date,
                            $cheque_bank_from,
                            $cheque_bank_ac_from,
                            $cheque_bank_ifsc_from,
                            $cheque_bank_branch_from,
                            $cheque_bank_to,
                            $cheque_bank_ac_to,
                            $transction_no,
                            $transction_bank_from,
                            $transction_bank_ac_from,
                            $transction_bank_ifsc_from,
                            $transction_bank_branch_from,
                            $transction_bank_to,
                            $transction_bank_ac_to,
                            $transction_date,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $updated_at,
                            $tranId,
                            $ssb_account_id_to,
                            $cheque_bank_from_id,
                            $cheque_bank_ac_from_id,
                            $cheque_bank_to_name,
                            $cheque_bank_to_branch,
                            $cheque_bank_to_ac_no,
                            $cheque_bank_to_ifsc,
                            $transction_bank_from_id,
                            $transction_bank_from_ac_id,
                            $transction_bank_to_name,
                            $transction_bank_to_ac_no,
                            $transction_bank_to_branch,
                            $transction_bank_to_ifsc,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $jv_unique_id,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );
                    }
                }
            }

            if ($request->head == 19) {

                //Director
                $current_balance = $directorDetail->current_balance;
                $ddata['current_balance'] = $current_balance + $amount;
                $ddata['updated_at'] = $updated_at;
                $ddataUpdate = \App\Models\ShareHolder::find($directorDetail->id);
                $ddataUpdate->update($ddata);
                $headD1 = 1;
                $headD2 = 7;
                $headD3 = $request->head;
                $headD4 = $type_id;
                $headD5 = NULL;
                $allTran1 = CommanController::newHeadTransactionCreate(
                    $refId,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headD4,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $des,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $tranId,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
            }

            if ($request->head == 15) {
                //Shareholder
                $current_balance = $directorDetail->current_balance;
                $ddata['current_balance'] = $current_balance + $amount;
                $ddata['updated_at'] = $updated_at;
                $ddataUpdate = \App\Models\ShareHolder::find($directorDetail->id);
                $ddataUpdate->update($ddata);
                $headS1 = 1;
                $headS2 = 5;
                $headS3 = $request->head;
                $headS4 = $type_id;
                $headS5 = NULL;
                $allTran1S = CommanController::newHeadTransactionCreate(
                    $refId,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headS4,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $des,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $tranId,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
            }

            if ($request->head == 32) {
                //Penal Interest
                $headP1 = 3;
                $headP2 = 12;
                $headP3 = $request->head;
                $headP4 = NULL;
                $headP5 = NULL;

                $allTran1P = CommanController::newHeadTransactionCreate(
                    $refId,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headP3,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $des,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $tranId,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
                $empDetail = \App\Models\Employee::where('id', $empID)->first();
                $empCurrentBalance = $empDetail->current_balance;
                $empLeGet = \App\Models\EmployeeLedger::where('employee_id', $empID)->whereDate('created_at', '<=', $entry_date)->orderby('created_at', 'DESC')->first();
                $empLedger['employee_id'] = $empID;
                $empLedger['branch_id'] = $branch_id;
                $empLedger['type'] = 4;
                $empLedger['type_id'] = $tranId;
                if ($empLeGet) {
                    $empLedger['opening_balance'] = $empLeGet->opening_balance + $amount;
                } else {
                    $empLedger['opening_balance'] = $amount;
                }
                $empLedger['deposit'] = $amount;
                $empLedger['description'] = $des;
                $empLedger['currency_code'] = $currency_code;
                $empLedger['payment_type'] = 'CR';
                $empLedger['payment_mode'] = $request->payment_mode;

                $empLedger['created_at'] = $created_at;
                $empLedger['updated_at'] = $updated_at;
                $empLedger['company_id'] = $companyId;
                $empLedger['daybook_ref_id'] = $refId;
                $empL = \App\Models\EmployeeLedger::create($empLedger);
                $empdata['current_balance'] = $empCurrentBalance + $amount;
                $empdata['updated_at'] = $updated_at;
                $empdataUpdate = \App\Models\Employee::find($empID);
                $empdataUpdate->update($empdata);
                $empLedgerBackDate = CommanController::employeeLedgerBackDateCR($empID, $created_at, $amount);
            }

            if ($request->head == 96) {
                //Eli Loan
                $headE1 = 3;
                $headE2 = 13;
                $headE3 = $request->head;
                $headE4 = $type_id;
                $headE5 = NULL;
                $allTran1E = CommanController::newHeadTransactionCreate(
                    $refId,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headE4,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $des,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $tranId,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
            }
            if ($request->head == 87) { //commision
                $headE4 = 87;
                $allTran1E = CommanController::newHeadTransactionCreate(
                    $refId,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headE4,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $des,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $tranId,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
            }
            if ($request->head == 86) { //commision
                $headE4 = $request->sub_head;
                $allTran1E = CommanController::newHeadTransactionCreate(
                    $refId,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headE4,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $des,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $tranId,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
            }

            if ($request->head == 122) {

                //Stationary Charge
                $vno = NULL;
                $branch_id = $request->branch_id;
                $type = 21;
                $sub_type = NULL;
                $type_id = $tranId;
                $type_transaction_id = NULL;
                $associate_id = NULL;
                $member_id = $request->member_auto_id;
                $branch_id_to = NULL;
                $branch_id_from = NULL;

                $amount = 50;

                $description = 'Stationery charges on member';
                $description_dr = 'Stationery charges on member';
                $description_cr = 'Stationery charges on member';
                $payment_type = 'CR';
                $payment_mode = 0;
                $currency_code = 'INR';
                $v_no = $vno;
                $ssb_account_id_from = NULL;
                $cheque_no = NULL;

                $created_by = 1;
                $created_by_id = 1;
                $is_contra = NULL;
                $contra_id = NULL;
                $sub_type_cgst = 321;
                $sub_type_sgst = 322;
                $sub_type_igst = 323;
                $bank_id = NULL;
                $bank_ac_id = NULL;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_id_to = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                $select_date = $request->date;
                $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $date_create = $entry_date . ' ' . $entry_time;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
                Session::put('created_at', $created_at);
                $member = Member::with('branch')->where('id', $request->member_auto_id)->first();
                $dayBookRef = CommanController::createBranchDayBookReference($amount);
                $allTransaction = CommanController::newHeadTransactionCreate(
                    $dayBookRef,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    122,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $description,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $type_transaction_id,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );

                $allTransaction1 = CommanController::newHeadTransactionCreate(
                    $dayBookRef,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    28,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $description,
                    'DR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $updated_at,
                    $type_transaction_id,
                    $jv_unique_id,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );

                $branchDayBook = CommanController::createBranchDayBookModify(
                    $dayBookRef,
                    $branch_id,
                    $type,
                    $sub_type,
                    $type_id,
                    $type_transaction_id,
                    $associate_id,
                    $member_id,
                    $branch_id_to,
                    $branch_id_from,
                    $amount,
                    $description,
                    $description_dr,
                    $description_cr,
                    'CR',
                    $payment_mode,
                    $currency_code,
                    $v_no,
                    $ssb_account_id_from,
                    $cheque_no,
                    $transction_no,
                    $entry_date,
                    $entry_time,
                    $created_by,
                    $created_by_id,
                    $created_at,
                    $companyId
                );
                $amount = 50;
                $detail = getBranchDetail($request->branch_id)->state_id;
                $getHeadSetting = \App\Models\HeadSetting::where('head_id', 122)->first();
                $getGstSetting = \App\Models\GstSetting::where('state_id', $member['branch']->state_id)->where('applicable_date', '<=', $entry_date)->exists();
                $gstAmount = 0;
                $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no')->where('state_id', $member['branch']->state_id)->where('applicable_date', '<=', $entry_date)->first();
                if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
                    if ($member['branch']->state_id == $detail) {
                        $gstAmount = (($amount * $getHeadSetting->gst_percentage) / 100) / 2;
                        $cgstHead = 171;
                        $sgstHead = 172;
                        $IntraState = true;
                    } else {
                        $gstAmount = ($amount * $getHeadSetting->gst_percentage) / 100;
                        $cgstHead = 170;
                        $IntraState = false;
                    }
                    $msg = true;
                } else {
                    $IntraState = false;
                    $msg = false;
                }

                if ($gstAmount > 0) {
                    $createdGstTransaction = CommanController::gstTransactionNew(
                        $dayBookId = $dayBookRef,
                        $getGstSettingno->gst_no,
                        (!isset($member->gst_no)) ? NULL : $member->gst_no,
                        $amount,
                        $getHeadSetting->gst_percentage,
                        ($IntraState == false ? $igstAmount : 0),
                        ($IntraState == true ? $cgstAmount : 0),
                        ($IntraState == true ? $sgstAmount : 0),
                        ($IntraState == true) ? $amount + $cgstAmount + $sgstAmount : $amount + $igstAmount,
                        122,
                        $entry_date,
                        'IPC122',
                        $member->id,
                        $branch_id,
                        $companyId,
                        $dataCreate->id
                    );
                    if ($IntraState) {
                        $description = 'Stationary  Cgst Charge';
                        $descriptionB = 'Stationary Sgst Charge';
                        $amountArraySsb = array('1' => ($cgstAmount));
                        $allTransaction = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            $cgstHead,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $description,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id = NULL,
                            $v_no,
                            $ssb_account_id_from,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no,
                            $transction_no,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                        $allTransaction = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            28,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $description,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id = NULL,
                            $v_no,
                            $ssb_account_id_from,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no,
                            $transction_no,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                        $allTransaction = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            $cgstHead,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $descriptionB,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id = NULL,
                            $v_no,
                            $ssb_account_id_from,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no,
                            $transction_no,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                        $allTransaction = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            28,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $descriptionB,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id = NULL,
                            $v_no,
                            $ssb_account_id_from,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no,
                            $transction_no,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );

                        $branchDayBook = CommanController::branchDayBookNew(
                            $dayBookRef,
                            $branch_id,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $description,
                            $description,
                            $description,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $jv_unique_id = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $companyId
                        );
                        $branchDayBook = CommanController::branchDayBookNew(
                            $dayBookRef,
                            $branch_id,
                            $type,
                            $sub_type_sgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $descriptionB,
                            $descriptionB,
                            $descriptionB,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $jv_unique_id = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $companyId
                        );
                        $rec = [
                            'cgst_stationary_chrg' => $cgstAmount,
                            'sgst_stationary_chrg' => $sgstAmount,
                            'invoice_id' => $createdGstTransaction,
                        ];
                    } else {
                        $description = 'Stationary  Igst Charge';
                        $amountArraySsb = array('1' => ($igstAmount));
                        $allTransaction = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            28,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $description,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id = NULL,
                            $v_no,
                            $ssb_account_id_from,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no,
                            $transction_no,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                        $allTransaction = CommanController::createAllHeadTransactionModify(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            28,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $description,
                            'DR',
                            $payment_mode,
                            $currency_code,
                            $jv_unique_id = NULL,
                            $v_no,
                            $ssb_account_id_from,
                            $ssb_account_id_to = NULL,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $cheque_no,
                            $transction_no,
                            $created_by,
                            $created_by_id,
                            $companyId
                        );
                        $branchDayBook = CommanController::branchDayBookNew(
                            $dayBookRef,
                            $branch_id,
                            $type,
                            $sub_type_igst,
                            $type_id,
                            $createdGstTransaction,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
                            $description,
                            $description,
                            $description,
                            'CR',
                            $payment_mode,
                            $currency_code,
                            $v_no,
                            $ssb_account_id_from,
                            $cheque_no,
                            $transction_no,
                            $entry_date,
                            $entry_time,
                            $created_by,
                            $created_by_id,
                            $created_at,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $jv_unique_id = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $companyId
                        );
                        $rec = [
                            'igst_stationary_chrg' => $gstAmount,
                            'invoice_id' => $createdGstTransaction,
                        ];
                    }
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
             return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/voucher/print/' . $id)->with('success', 'Voucher created successfully');
    }
    public function print($id)
    {
        $data['title'] = "Receive Voucher Management | Receive Voucher Print";
        $data['row'] = \App\Models\ReceivedVoucher::with(['rv_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with([
            'company' => function ($q) {
                $q->select(['id', 'name','short_name']);
            }
        ])->with(['rv_employee' => function ($query) {
            $query->select('id', 'employee_name', 'employee_code');
        }])
            ->with(['rvCheque' => function ($query) {
                $query->select('id', 'cheque_no', 'deposit_bank_id', 'deposit_account_id', 'cheque_deposit_date', 'account_holder_name', 'bank_name', 'cheque_account_no');
            }])->where('id', $id)->where('is_deleted', 0)->first();
        //print_r($data['data']);die;
        return view('templates.admin.voucher.print', $data);
    }
    public function voucherList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = \App\Models\ReceivedVoucher::has('company')->select('id', 'date', 'received_mode', 'amount', 'type', 'account_head_id', 'director_id', 'shareholder_id', 'bank_ac_id', 'eli_loan_id', 'daybook_type', 'cheque_date', 'online_tran_no', 'online_tran_date', 'cheque_bank_name', 'online_tran_bank_name', 'cheque_bank_ac_no', 'online_tran_bank_ac_no', 'receive_bank_id', 'receive_bank_ac_id', 'slip', 'created_at', 'branch_id', 'cheque_id', 'company_id','employee_id','member_id','associate_id','expense_head')->with(['rv_branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }])->with([
                    'company' => function ($q) {
                        $q->select(['id', 'name','short_name']);
                    }
                ])
                    ->with([
                        'rv_employee' => function ($query) {
                            $query->select('id', 'employee_name', 'employee_code');
                        }
                    ])
                    ->with(['rvCheque' => function ($query) {
                        $query->select('id', 'cheque_no', 'deposit_bank_id', 'deposit_account_id', 'cheque_deposit_date', 'account_holder_name');
                    }])
                    ->with('rv_member:id,first_name,last_name')
                    ->where('is_deleted', 0);

                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if ($arrFormData['payment_type'] != '') {
                    $payment_type = $arrFormData['payment_type'];
                    $data = $data->where('received_mode', '=', $payment_type);
                }
                if ($arrFormData['account_head'] != '') {
                    $account_head = $arrFormData['account_head'];
                    $data = $data->where('account_head_id', '=', $account_head);
                }
                if ($arrFormData['branch_id'] > 0) {
                    $branch = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $branch);
                }
                if ($arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                $count = $data->count('id');
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
                $data = $data->orderby('created_at', 'DESC')->get();
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = isset($row['company']->name) ?$row['company']->short_name : "N/A";
                    $val['branch'] = $row['rv_branch']->name;
                    // $val['branch_code'] = $row['rv_branch']->branch_code;
                    // $val['sector'] = $row['rv_branch']->sector;
                    // $val['regan'] = $row['rv_branch']->regan;
                    // $val['zone'] = $row['rv_branch']->zone;
                    $val['date'] = date("d/m/Y", strtotime($row->date));
                    if ($row->received_mode == 0) {
                        $mode = "Cash";
                    }
                    if ($row->received_mode == 1) {
                        $mode = "Cheque";
                    }
                    if ($row->received_mode == 2) {
                        $mode = "Online";
                    }
                    $val['rv_mode'] = $mode;
                    $val['rv_amount'] = number_format((float) $row->amount, 2, '.', '');
                    $val['account_head'] = getAcountHeadNameHeadId($row->account_head_id);
                    $val['account_sub_head'] = getAcountHeadNameHeadId($row->expense_head) ?? "N/A";
                    if ($row->type == 1) {
                        $val['director'] = getAcountHeadNameHeadId($row->director_id);
                    } else {
                        $val['director'] = 'N/A';
                    }
                    if ($row->type == 2) {
                        $val['shareholder'] = getAcountHeadNameHeadId($row->shareholder_id);
                    } else {
                        $val['shareholder'] = 'N/A';
                    }
                    if ($row['rv_employee'] && $row->employee_id != null) {
                        $val['employee_code'] = $row['rv_employee']->employee_code;
                    } 
                    elseif($row['rv_member'] && $row->member_id != null){
                        $val['employee_code'] =  \App\Models\MemberCompany::select('member_id')->where('customer_id',$row['rv_member']->id)->first()->member_id;
                    }
                    elseif($row->associate_id != null){
                        $val['employee_code'] =  getMemberCustom($row->associate_id)->associate_no;
                    }
                    else {
                        $val['employee_code'] = 'N/A';
                    }
                    if ($row['rv_employee'] && $row->employee_id != null) {
                        $val['employee_name'] = $row['rv_employee']->employee_name ?? '';
                    } 
                    elseif ($row['rv_member'] && $row->member_id != null) {
                        $val['employee_name'] = $row['rv_member']->first_name . " " .$row['rv_member']->last_name ?? '';
                    }
                    elseif($row->associate_id != null){
                        $val['employee_name'] =  getMemberCustom($row->associate_id)->first_name . " " .getMemberCustom($row->associate_id)->last_name ?? '';
                    }
                    else {
                        $val['employee_name'] = 'N/A';
                    }
                    if ($row->type == 4) {
                        $val['bank_name'] = getSamraddhBank($row->bank_id)->bank_name;
                    } else {
                        $val['bank_name'] = 'N/A';
                    }
                    if ($row->type == 4) {
                        $val['bank_account_number'] = getSamraddhBankAccountId($row->bank_ac_id)->account_no;
                    } else {
                        $val['bank_account_number'] = 'N/A';
                    }
                    if ($row->eli_loan_id) {
                        $val['eli_loan'] = getAcountHeadNameHeadId($row->eli_loan_id);
                    } else {
                        $val['eli_loan'] = 'N/A';
                    }
                    if ($row->received_mode == 0) {
                        if ($row->daybook_type == 0) {
                            $val['day_book'] = "Cash";
                        } else {
                            $val['day_book'] = "Cash";
                        }
                    } else {
                        $val['day_book'] = "N/A";
                    }
                    if ($row->received_mode == 0) {
                        $val['cheque_no'] = "N/A";
                    }
                    if ($row->received_mode == 2) {
                        $val['cheque_no'] = "N/A";
                    }
                    if ($row->received_mode == 1) {
                        $val['cheque_no'] = $row['rvCheque']->cheque_no;
                    }
                    if ($row->received_mode == 1) {
                        $val['cheque_date'] = date("d/m/Y", strtotime($row->cheque_date));
                    } else {
                        $val['cheque_date'] = "N/A";
                    }
                    if ($row->received_mode == 0) {
                        $val['utr_transaction_number'] = "N/A";
                    } else {
                        $val['utr_transaction_number'] = $row->online_tran_no;
                    }
                    if ($row->received_mode == 0) {
                        $val['transaction_date'] = "N/A";
                    } else {
                        $val['transaction_date'] = date("d/m/Y", strtotime($row->online_tran_date));
                    }
                    if ($row->received_mode == 0) {
                        $val['party_bank_name'] = "N/A";
                    }
                    if ($row->received_mode == 1) {
                        $val['party_bank_name'] = $row->cheque_bank_name;
                    }
                    if ($row->received_mode == 2) {
                        $val['party_bank_name'] = $row->online_tran_bank_name;
                    }
                    if ($row->received_mode == 0) {
                        $val['party_bank_account'] = "N/A";
                    }
                    if ($row->received_mode == 1) {
                        $val['party_bank_account'] = $row->cheque_bank_ac_no;
                    }
                    if ($row->received_mode == 2) {
                        $val['party_bank_account'] = $row->online_tran_bank_ac_no;
                    }
                    if ($row->received_mode == 0) {
                        $val['received_bank'] = "N/A";
                    } else {
                        $val['received_bank'] = getSamraddhBank($row->receive_bank_id)->bank_name;
                    }
                    if ($row->received_mode == 0) {
                        $val['received_bank_account'] = "N/A";
                    } else {
                        $val['received_bank_account'] = getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no;
                    }
                    if ($row->slip) {
                        // $a = URL::to("/asset/voucher/" . $row->slip . "");
                        $a = ImageUpload::generatePreSignedUrl('voucher/' . $row->slip);
                        $val['bank_slip'] = '<a href="' . $a . '" target="_blanck">' . $row->slip . '</a>';
                    } else {
                        $val['bank_slip'] = 'N/A';
                    }
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $url = URL::to("admin/voucher/print/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="fas fa-print mr-2"></i>Print</a>';
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
                return json_encode($output);
            } else {
                $output = array(
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    public function getMemberDetails(Request $request)
    {
        $memberId = $request->member_id;
        $data = \App\Models\MemberCompany::select('customer_id', 'member_id','company_id','branch_id')->where('member_id', $memberId)->first();
        $id = $data->customer_id;
        $collectorDetails =  Member::where('id', $id)->first();

        if ($collectorDetails &&   $id) {
            $createdDate = date("d/m/Y", strtotime(convertDate($collectorDetails->created_at)));
            return Response::json(['msg_type' => 'success', 'collectorDetails' => $collectorDetails, 'data'=>$data ,'createdDate' => $createdDate]);
        } else {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public static function updateBranchCashCr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            if ($type == 0) {
                $data1['balance'] = $currentDateRecord->balance + $amount;
            } elseif ($type == 1) {
                $data1['loan_balance'] = $currentDateRecord->loan_balance + $amount;
            }
            $Result->update($data1);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                if ($type == 0) {
                    $data2['balance'] = $oldDateRecord->balance + $amount;
                } else {
                    $data2['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data2['loan_balance'] = $oldDateRecord->loan_balance + $amount;
                } else {
                    $data2['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data2['opening_balance'] = $oldDateRecord->balance;
                $data2['closing_balance'] = 0;
                $data2['loan_opening_balance'] = $oldDateRecord->loan_balance;
                $data2['loan_closing_balance'] = 0;
                $data2['branch_id'] = $branch_id;
                $data2['entry_date'] = $entryDate;
                $data2['entry_time'] = $entryTime;
                $data2['type'] = $type;
                $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data2);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data3['balance'] = 0 - $amount;
                } else {
                    $data3['balance'] = 0;
                }
                if ($type == 1) {
                    $data3['loan_balance'] = 0 - $amount;
                } else {
                    $data3['loan_balance'] = 0;
                }
                $data3['opening_balance'] = 0;
                $data3['closing_balance'] = 0;
                $data3['loan_opening_balance'] = 0;
                $data3['loan_closing_balance'] = 0;
                $data3['branch_id'] = $branch_id;
                $data3['entry_date'] = $entryDate;
                $data3['entry_time'] = $entryTime;
                $data3['type'] = $type;
                $data3['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data3);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function updateBranchClosingCashCr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("h:i:s");
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            if ($type == 0) {
                $data1['balance'] = $currentDateRecord->balance + $amount;
            } elseif ($type == 1) {
                $data1['loan_balance'] = $currentDateRecord->loan_balance + $amount;
            }
            $Result->update($data1);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                if ($type == 0) {
                    $data2['balance'] = $oldDateRecord->balance + $amount;
                } else {
                    $data2['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data2['loan_balance'] = $oldDateRecord->loan_balance + $amount;
                } else {
                    $data2['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data2['opening_balance'] = $oldDateRecord->balance;
                $data2['closing_balance'] = 0;
                $data2['loan_opening_balance'] = $oldDateRecord->loan_balance;
                $data2['loan_closing_balance'] = 0;
                $data2['branch_id'] = $branch_id;
                $data2['entry_date'] = $entryDate;
                $data2['entry_time'] = $entryTime;
                $data2['type'] = $type;
                $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data2);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data3['balance'] = $amount;
                } else {
                    $data3['balance'] = 0;
                }
                if ($type == 1) {
                    $data3['loan_balance'] = $amount;
                } else {
                    $data3['loan_balance'] = 0;
                }
                $data3['opening_balance'] = 0;
                $data3['closing_balance'] = 0;
                $data3['loan_opening_balance'] = 0;
                $data3['loan_closing_balance'] = 0;
                $data3['branch_id'] = $branch_id;
                $data3['entry_date'] = $entryDate;
                $data3['entry_time'] = $entryTime;
                $data3['type'] = $type;
                $data3['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data3);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }
    public function associateDetail(Request $request)
    {
        $data = \App\Models\Member::select('id', 'first_name', 'last_name')->where([['associate_no', 'like', '%' . $request->associate_code . '%'], ['is_block', 0],/*['branch_id',$request->branch_id],['company_id',$request->company_id]*/])->first();
        return json_encode($data);
    }
    public function checkGstData(Request $request)
    {
        $companyId =  $request->company_id;
        $branchId = $request->branch_id;
        $stateid = getBranchDetail($branchId)->state_id;
        $parts = explode("/", $request->create_application_date);
        $applicationDate = $parts[2] . "/" . $parts[1] . "/" . $parts[0];
        // $stateid = Auth::user()->branch->state_id;
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 122)->first();

        $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $applicationDate)->whereCompanyId($companyId)->exists();
        $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $stateid)->where('applicable_date', '<=', $applicationDate)->whereCompanyId($companyId)->first();
        $gstData = array();
        //Gst Insuramce
        if($getGstSetting)
        {
            if (isset($getHeadSetting->gst_percentage)) {
                $gstData['gst_percentage'] =  $getHeadSetting->gst_percentage;
                $gstData['IntraState'] = ($stateid == $getGstSettingno->state_id ? true : false);
            } else {
                $gstData['gst_percentage'] =  '0';
                $gstData['IntraState'] = false;
            }
        }   
       
        return json_encode($gstData);
    }
}
// ADMIN