<?php

namespace App\Http\Controllers\Branch\Voucher;

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
use App\Services\ImageUpload;
use App\Http\Controllers\Branch\CommanTransactionsController;
use App\Http\Controllers\Branch\CommanController;

class VoucherController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    // Cash In Hand Table
    public function index()
    {
        if (!in_array('Voucher List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = "Receive Voucher Management | Voucher";
        return view('templates.branch.voucher.index', $data);
    }
    public function create()
    {

        if (!in_array('Voucher Request', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = "Receive Voucher Management | Voucher Request";

        $branchData = customBranchName(Auth::user()->id);

        $data['branch_id'] = $branchData->id;

        $data['branch_name'] = $branchData->name;
        $data['branch_code'] = $branchData->branch_code;

        $data['samraddh_bank'] = SamraddhBank::where('status', 1)->get(['id', 'bank_name']);
        return view('templates.branch.voucher.create_voucher', $data);
    }
    public function save(Request $request)
    {

        $globaldate = $request->created_at;
        $currentSoftwareDate = date("d/m/Y", strtotime(convertDate($globaldate)));
        $rules = [
            'branch_id' => ['required'],
            'head' => ['required'],
            'date' => ['required', 'before_or_equal:' . $currentSoftwareDate],
            'payment_mode' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            $companyId = $request->company_id,

        ];
        $this->validate($request, $rules, $customMessages);


        DB::beginTransaction();
        try {



            Session::put('created_at', $request->created_at);
            $neft_charge = 0;
            $leaser_id = $ledger_id = $type_id = $request->leaser_id;
            $total_transfer_amount = 0;


            $currency_code = 'INR';
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));

            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $amount_to_id = NULL;
            $amount_to_name = NULL;
            $amount_from_id = NULL;
            $amount_from_name = NULL;
            $ssb_account_id_to = NULL;
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

            // if ($request->head == 19) { //Director

            //     $types = 1;
            //     $data['director_id'] = $type_id = $request->director_id;
            //     $type = 14;
            //     $sub_type = 141;
            //     $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
            //     $amount_from_id = $type_id;
            //     $amount_from_name = getAcountHeadNameHeadId($type_id);

            //     $directorDetail = \App\Models\ShareHolder::where('head_id', $type_id)->first();
            //     $member_id = $directorDetail->member_id;
            // }
            // if ($request->head == 15) { //Shareholder
            //     $types = 2;
            //     $data['shareholder_id'] = $type_id = $request->shareholder_id;
            //     $type = 14;
            //     $sub_type = 142;
            //     $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
            //     $amount_from_id = $type_id;
            //     $amount_from_name = getAcountHeadNameHeadId($type_id);

            //     $directorDetail = \App\Models\ShareHolder::where('head_id', $type_id)->first();
            //     $member_id = $directorDetail->member_id;
            // }
            if ($request->head == 32) { //Penal Interest
                $types = 3;
                $data['employee_id'] = $type_id = $empID = $request->emp_id;
                $type = 14;
                $sub_type = 143;

                $description_cr = 'To ' . $request->emp_name . ' A/c Cr ' . $amount . '/-';
                $amount_from_id = $type_id;
                $amount_from_name = $request->emp_name;
            }
            // if ($request->head == 27) { //Bank
            //     $types = 4;
            //     $data['bank_id'] = $type_id = $bank_id = $request->bank_id;
            //     $data['bank_ac_id'] = $bank_id_ac = $bank_ac_id = $request->bank_account;
            //     $type = 14;
            //     $sub_type = 144;
            //     $description_cr = 'To ' . getSamraddhBank($bank_id)->bank_name . ' A/c ' . getSamraddhBankAccountId($bank_id_ac)->account_no . ' Cr ' . $amount . '/-';
            //     $amount_from_id = $type_id;
            //     $amount_from_name = getSamraddhBank($bank_id)->bank_name . ' - ' . getSamraddhBankAccountId($bank_id_ac)->account_no;
            // }
            // if ($request->head == 96) { //Eli Loan
            //     $types = 5;
            //     $data['eli_loan_id'] = $type_id = $request->eli_loan_id;
            //     $type = 14;
            //     $sub_type = 145;
            //     $description_cr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . '/-';
            //     $amount_from_id = $type_id;
            //     $amount_from_name = getAcountHeadNameHeadId($type_id);
            // }
            if ($request->head == 122) { //stationary charge
                $types = 6;
                $type = 21;
                $sub_type = 35;
                $data['member_id'] =  $member_id  = $request->member_auto_id;
                $cgstAmount = $request->cgst_stationary_charge;
                $sgstAmount = $request->sgst_stationary_charge;
                $igstAmount = $request->igst_stationary_charge;
            }

            $data['type'] = $types;
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

            $daybookRef = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
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

                $data['cheque_bank_name'] = $cheque_bank_from;
                $data['cheque_bank_ac_no'] = $cheque_bank_ac_from;
                $data['cheque_date'] = $cheque_date;
                $data['receive_bank_id'] = $bank_id = $cheque_bank_to;
                $data['receive_bank_ac_id'] = $bank_ac_id = $cheque_bank_ac_to;
                $cheque_type = 0;
                $cheque_id = $request->cheque_no;

                $amount_to_id = $cheque_bank_to;
                $amount_to_name = $cheque_bank_to_name . ' - ' . $cheque_bank_to_ac_no;
            }

            if ($request->payment_mode == 2) { //online 
                $data['online_tran_date'] = date("Y-m-d", strtotime(convertDate($request->utr_date)));
                $data['online_tran_no'] = $request->utr_no;
                $data['online_tran_bank_name'] = $request->transaction_bank;
                $data['online_tran_bank_ac_no'] = $request->transaction_bank_ac;
                $data['receive_bank_id'] = $bank_id = $request->online_bank;
                $data['receive_bank_ac_id'] = $bank_ac_id = $request->online_bank_ac;
                $data['slip'] = $request->daybook_type;

                $transction_no = $request->utr_no;
                $transction_bank_from = $request->transaction_bank;
                $transction_bank_ac_from = $request->transaction_bank_ac;
                $transction_bank_to = $request->online_bank;
                $transction_bank_ac_to = $request->online_bank_ac;
                $transction_date = date("Y-m-d", strtotime(convertDate($request->utr_date)));
                $transction_bank_to_name = getSamraddhBank($transction_bank_to)->bank_name;
                $transction_bank_to_branch = getSamraddhBankAccountId($transction_bank_ac_to)->branch_name;
                $transction_bank_to_ac_no = getSamraddhBankAccountId($transction_bank_ac_to)->account_no;
                $transction_bank_to_ifsc = getSamraddhBankAccountId($transction_bank_ac_to)->ifsc_code;
                $amount_to_id = $transction_bank_to;
                $amount_to_name = $transction_bank_to_name . ' - ' . $transction_bank_to_ac_no;
            }
            $data['company_id'] = $companyId;
            $dataCreate = \App\Models\ReceivedVoucher::create($data);
            $id = $dataCreate->id;
            $tranId = $dataCreate->id;
            if ($request->hasFile('bank_slip')) {
                $slip_image = $request->file('bank_slip');
                $slip_filename = $id . '_' . time() . '.' . $slip_image->getClientOriginalExtension();
                // $slip_location = 'asset/voucher/' . $slip_filename;
                $slip_location = 'voucher/';
                $file = $request->file('bank_slip');
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                ImageUpload::upload($file, $slip_location,$slip_filename);
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
                    $brDaybook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                        $refId,
                        $branch_id,
                        $type,
                        $sub_type,
                        $type_id,
                        $associate_id = NULL,
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
                        $ssb_account_id_to,
                        $ssb_account_tran_id_to,
                        $ssb_account_tran_id_from,
                        $jv_unique_id,
                        $cheque_type,
                        $cheque_id,
                        $companyId
                    );

                    $head1rdC = 2;
                    $head2rdC = 10;
                    $head3rdC = 28;
                    $head4rdC = 71;
                    $head5rdC = NULL;
                    $allTran1 = CommanTransactionsController::headTransactionCreate(
                        $refId,
                        $branch_id,
                        $bank_id,
                        $bank_ac_id,
                        $head3rdC,
                        $type,
                        $sub_type,
                        $type_id,
                        $associate_id = NULL,
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

                    // if ($request->head == 27) {

                    //     //Bank 
                    //     // echo $bank_id;die;
                    //     $getBHead = \App\Models\SamraddhBank::where('id', $bank_id)->first();
                    //     $headB1 = 2;
                    //     $headB2 = 10;
                    //     $headB3 = $request->head;
                    //     $headB4 = $getBHead->account_head_id;
                    //     $headB5 = NULL;

                    //     // bank head entry ++
                    //     $allTran1 = CommanTransactionsController::headTransactionCreate(
                    //         $refId,
                    //         $branch_id,
                    //         $bank_id,
                    //         $bank_ac_id,
                    //         $headB4,
                    //         $type,
                    //         $sub_type,
                    //         $type_id,
                    //         $associate_id = NULL,
                    //         $member_id,
                    //         $branch_id_to = NULL,
                    //         $branch_id_from = NULL,
                    //         $amount,
                    //         $des,
                    //         'CR',
                    //         $payment_mode,
                    //         $currency_code,
                    //         $v_no,
                    //         $ssb_account_id_from,
                    //         $cheque_no,
                    //         $transction_no,
                    //         $entry_date,
                    //         $entry_time,
                    //         $created_by,
                    //         $created_by_id,
                    //         $created_at,
                    //         $updated_at,
                    //         $tranId,
                    //         $jv_unique_id,
                    //         $ssb_account_id_to,
                    //         $ssb_account_tran_id_to,
                    //         $ssb_account_tran_id_from,
                    //         $cheque_type,
                    //         $cheque_id,
                    //         $companyId
                    //     );
                    //     // bank daybook entry ++
                    //     $bankcashDaybook = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate(
                    //         $refId,
                    //         $bank_id,
                    //         $bank_id_ac,
                    //         $type,
                    //         $sub_type,
                    //         $type_id,
                    //         $associate_id = NULL,
                    //         $memberId = NULL,
                    //         $branch_id,
                    //         $opening_balance = NULL,
                    //         $amount,
                    //         $closing_balance = NULL,
                    //         $des,
                    //         $description_dr,
                    //         $description_cr,
                    //         'DR',
                    //         $payment_mode,
                    //         $currency_code,
                    //         $amount_to_id,
                    //         $amount_to_name,
                    //         $amount_from_id,
                    //         $amount_from_name,
                    //         $v_no,
                    //         $v_date,
                    //         $ssb_account_id_from,
                    //         $cheque_no,
                    //         $cheque_date,
                    //         $cheque_bank_from,
                    //         $cheque_bank_ac_from,
                    //         $cheque_bank_ifsc_from,
                    //         $cheque_bank_branch_from,
                    //         $cheque_bank_to,
                    //         $cheque_bank_ac_to,
                    //         $transction_no,
                    //         $transction_bank_from,
                    //         $transction_bank_ac_from,
                    //         $transction_bank_ifsc_from,
                    //         $transction_bank_branch_from,
                    //         $transction_bank_to,
                    //         $transction_bank_ac_to,
                    //         $transction_date,
                    //         $entry_date,
                    //         $entry_time,
                    //         $created_by,
                    //         $created_by_id,
                    //         $created_at,
                    //         $updated_at,
                    //         $tranId,
                    //         $ssb_account_id_to,
                    //         $cheque_bank_from_id,
                    //         $cheque_bank_ac_from_id,
                    //         $cheque_bank_to_name,
                    //         $cheque_bank_to_branch,
                    //         $cheque_bank_to_ac_no,
                    //         $cheque_bank_to_ifsc,
                    //         $transction_bank_from_id,
                    //         $transction_bank_from_ac_id,
                    //         $transction_bank_to_name,
                    //         $transction_bank_to_ac_no,
                    //         $transction_bank_to_branch,
                    //         $transction_bank_to_ifsc,
                    //         $ssb_account_tran_id_to,
                    //         $ssb_account_tran_id_from,
                    //         $jv_unique_id,
                    //         $cheque_type,
                    //         $cheque_id,
                    //         $companyId
                    //     );

                    //     // $bankClosing = CommanTransactionsController::checkCreateBankClosingDR($bank_id, $bank_id_ac, $created_at, $amount, 0);
                    // }
                } else {
                    $description_dr = 'Bank A/c Dr ' . $amount . '/-';
                    if ($request->payment_mode == 1) {
                        //branchDay book entry  anoop sir ke khne pr  23/06/2023
                        $brDaybook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                            $refId,
                            $branch_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id = NULL,
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
                            $ssb_account_id_to,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $jv_unique_id,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );

                        $receivedPayment['type'] = 5;
                        $receivedPayment['branch_id'] = $branch_id;
                        $receivedPayment['type_id'] = $tranId;
                        $receivedPayment['cheque_id'] = $request->cheque_no;
                        $receivedPayment['created_at'] = $globaldate;
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
                        // bank Head entry +
                        $allTran2 = CommanTransactionsController::headTransactionCreate(
                            $refId,
                            $branch_id,
                            $cheque_bank_to,
                            $cheque_bank_ac_to,
                            $gbh->account_head_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id = NULL,
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
                        $smbdc = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate(
                            $refId,
                            $cheque_bank_to,
                            $cheque_bank_ac_to,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id = NULL,
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

                    if ($request->payment_mode == 2) {
                        //branchDay book entry  anoop sir ke khne pr  23/06/2023
                        $brDaybook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                            $refId,
                            $branch_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id = NULL,
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
                            $ssb_account_id_to,
                            $ssb_account_tran_id_to,
                            $ssb_account_tran_id_from,
                            $jv_unique_id,
                            $cheque_type,
                            $cheque_id,
                            $companyId
                        );

                        $empLedger['transaction_no'] = $transction_no;
                        $v_date = Null;

                        $gbh = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();
                        // bank head entry ++

                        $allTran2 = CommanTransactionsController::headTransactionCreate(
                            $refId,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            $gbh->account_head_id,
                            $type,
                            $sub_type,
                            $type_id,
                            $associate_id = NULL,
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
                        $smbdc = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate($refId, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                    }
                }
            }

            // if ($request->head == 19) { //Director

            //     $current_balance = $directorDetail->current_balance;

            //     $ddata['current_balance'] = $current_balance + $amount;
            //     $ddata['updated_at'] = $updated_at;
            //     $ddataUpdate = \App\Models\ShareHolder::find($directorDetail->id);
            //     $ddataUpdate->update($ddata);

            //     $headD1 = 1;
            //     $headD2 = 7;
            //     $headD3 = $request->head;
            //     $headD4 = $type_id;
            //     $headD5 = NULL;
            //     $allTran1 = CommanTransactionsController::headTransactionCreate(
            //         $refId,
            //         $branch_id,
            //         $bank_id,
            //         $bank_ac_id,
            //         $headD4,
            //         $type,
            //         $sub_type,
            //         $type_id,
            //         $associate_id = NULL,
            //         $member_id,
            //         $branch_id_to = NULL,
            //         $branch_id_from = NULL,
            //         $amount,
            //         $des,
            //         'CR',
            //         $payment_mode,
            //         $currency_code,
            //         $v_no,
            //         $ssb_account_id_from,
            //         $cheque_no,
            //         $transction_no,
            //         $entry_date,
            //         $entry_time,
            //         $created_by,
            //         $created_by_id,
            //         $created_at,
            //         $updated_at,
            //         $tranId,
            //         $jv_unique_id,
            //         $ssb_account_id_to,
            //         $ssb_account_tran_id_to,
            //         $ssb_account_tran_id_from,
            //         $cheque_type,
            //         $cheque_id,
            //         $companyId
            //     );
            // }

            // if ($request->head == 15) { //Shareholder 

            //     $current_balance = $directorDetail->current_balance;

            //     $ddata['current_balance'] = $current_balance + $amount;
            //     $ddata['updated_at'] = $updated_at;
            //     $ddataUpdate = \App\Models\ShareHolder::find($directorDetail->id);
            //     $ddataUpdate->update($ddata);

            //     $headS1 = 1;
            //     $headS2 = 5;
            //     $headS3 = $request->head;
            //     $headS4 = $type_id;
            //     $headS5 = NULL;
            //     $allTran1S = CommanTransactionsController::headTransactionCreate(
            //         $refId,
            //         $branch_id,
            //         $bank_id,
            //         $bank_ac_id,
            //         $headS4,
            //         $type,
            //         $sub_type,
            //         $type_id,
            //         $associate_id = NULL,
            //         $member_id,
            //         $branch_id_to = NULL,
            //         $branch_id_from = NULL,
            //         $amount,
            //         $des,
            //         'CR',
            //         $payment_mode,
            //         $currency_code,
            //         $v_no,
            //         $ssb_account_id_from,
            //         $cheque_no,
            //         $transction_no,
            //         $entry_date,
            //         $entry_time,
            //         $created_by,
            //         $created_by_id,
            //         $created_at,
            //         $updated_at,
            //         $tranId,
            //         $jv_unique_id,
            //         $ssb_account_id_to,
            //         $ssb_account_tran_id_to,
            //         $ssb_account_tran_id_from,
            //         $cheque_type,
            //         $cheque_id,
            //         $companyId
            //     );
            // }


            if ($request->head == 32) { //Penal Interest 

                $headP1 = 3;
                $headP2 = 12;
                $headP3 = $request->head;
                $headP4 = NULL;
                $headP5 = NULL;
                $allTran1P = CommanTransactionsController::headTransactionCreate(
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
                $empLedger['employee_id'] = $empID;
                $empLedger['branch_id'] = $branch_id;
                $empLedger['type'] = 4;
                $empLedger['type_id'] = $tranId;
                $empLedger['opening_balance'] = $empCurrentBalance + $amount;
                $empLedger['deposit'] = $amount;
                $empLedger['description'] = $des;
                $empLedger['currency_code'] = $currency_code;
                $empLedger['payment_type'] = 'CR';
                $empLedger['payment_mode'] = $payment_mode;
                $empLedger['created_at'] = $created_at;
                $empLedger['updated_at'] = $updated_at;
                $empLedger['company_id'] = $companyId;
                $empLedger['daybook_ref_id'] = $refId;
                $empL = \App\Models\EmployeeLedger::create($empLedger);
                $empdata['current_balance'] = $empCurrentBalance + $amount;
                $empdata['updated_at'] = $updated_at;
                $empdataUpdate = \App\Models\Employee::find($empID);
                $empdataUpdate->update($empdata);
            }

            // if ($request->head == 96) {
            //     //Eli Loan
            //     $headE1 = 3;
            //     $headE2 = 13;
            //     $headE3 = $request->head;
            //     $headE4 = $type_id;
            //     $headE5 = NULL;
            //     $allTran1E = CommanTransactionsController::headTransactionCreate(
            //         $refId,
            //         $branch_id,
            //         $bank_id,
            //         $bank_ac_id,
            //         $headE4,
            //         $type,
            //         $sub_type,
            //         $type_id,
            //         $associate_id = NULL,
            //         $member_id,
            //         $branch_id_to = NULL,
            //         $branch_id_from = NULL,
            //         $amount,
            //         $des,
            //         'CR',
            //         $payment_mode,
            //         $currency_code,
            //         $v_no,
            //         $ssb_account_id_from,
            //         $cheque_no,
            //         $transction_no,
            //         $entry_date,
            //         $entry_time,
            //         $created_by,
            //         $created_by_id,
            //         $created_at,
            //         $updated_at,
            //         $tranId,
            //         $jv_unique_id,
            //         $ssb_account_id_to,
            //         $ssb_account_tran_id_to,
            //         $ssb_account_tran_id_from,
            //         $cheque_type,
            //         $cheque_id,
            //         $companyId
            //     );
            // }
            if ($request->head == 122) {


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
                // $customerIdd = \App\Models\MemberCompany::find($request->member_auto_id);
                $member = \App\Models\Member::with('branch')->where('id', $request->member_auto_id)->first();
                $dayBookRef = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $created_at);
                $allTransaction =  CommanTransactionsController::headTransactionCreate(
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

                $allTransaction1 = CommanTransactionsController::headTransactionCreate($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount,  $description, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                $branchDayBook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                    $dayBookRef,
                    $branch_id,
                    $type,
                    $sub_type,
                    $type_id,
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
                    $updated_at,
                    $ssb_account_id_to,
                    $ssb_account_tran_id_to,
                    $ssb_account_tran_id_from,
                    $jv_unique_id,
                    $cheque_type,
                    $cheque_id,
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
                    $createdGstTransaction = CommanTransactionsController::gstTransactionNew(
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
                        $allTransaction = CommanTransactionsController::headTransactionCreate($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $allTransaction = CommanTransactionsController::headTransactionCreate($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount,  $description, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $allTransaction = CommanTransactionsController::headTransactionCreate($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $allTransaction = CommanTransactionsController::headTransactionCreate($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount,  $descriptionB, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


                        $branchDayBook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                            $dayBookRef,
                            $branch_id,
                            $type,
                            $sub_type_cgst,
                            $type_id,
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
                            $updated_at,
                            0,
                            $ssb_account_tran_id_to = NULL,
                            $ssb_account_tran_id_from = NULL,
                            $jv_unique_id = NULL,
                            $cheque_type = NULL,
                            $cheque_id = NULL,
                            $companyId
                        );
                        $branchDayBook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                            $dayBookRef,
                            $branch_id,
                            $type,
                            $sub_type_sgst,
                            $type_id,
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
                            $updated_at,
                            0,
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
                        $allTransaction = CommanTransactionsController::headTransactionCreate(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            28,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
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
                        $allTransaction = CommanTransactionsController::headTransactionCreate(
                            $dayBookRef,
                            $branch_id,
                            $bank_id,
                            $bank_ac_id,
                            28,
                            $type,
                            $sub_type_cgst,
                            $type_id,
                            $associate_id,
                            $member_id,
                            $branch_id_to,
                            $branch_id_from,
                            $gstAmount,
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

                        $branchDayBook = CommanTransactionsController::NewFieldBranchDaybookCreate(
                            $dayBookRef,
                            $branch_id,
                            $type,
                            $sub_type_igst,
                            $type_id,
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
                            $updated_at,
                            $ssb_account_id_to,
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
            p($ex->getMessage());
            echo '<br>';
            pd($ex->getLine());
            // return back()->with('alert', $ex->getMessage());
        }



        return redirect('branch/voucher/print/' . $id)->with('success', 'Voucher created successfully');
    }

    public function print($id)
    {
        if (!in_array('Voucher Print', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = "Receive Voucher Management | Voucher List";
        $data['row'] = \App\Models\ReceivedVoucher::with(['rv_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['company' => function ($q) {
            $q->select(['id', 'name', 'short_name']);
        }])->with(['rv_employee' => function ($query) {
            $query->select('id', 'employee_name', 'employee_code');
        }])->with(['rvCheque' => function ($query) {
            $query->select('id', 'cheque_no', 'deposit_bank_id', 'deposit_account_id', 'cheque_deposit_date', 'account_holder_name', 'bank_name', 'cheque_account_no');
        }])->where('id', $id)->first();

        //print_r($data['data']);die;
        return view('templates.branch.voucher.print', $data);
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
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

                $data = \App\Models\ReceivedVoucher::with(['rv_branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }])->with(['company' => function ($q) {
                    $q->select(['id', 'name', 'short_name']);
                }])->with(['rv_employee' => function ($query) {
                    $query->select('id', 'employee_name', 'employee_code');
                }])->with(['rvCheque' => function ($query) {
                    $query->select('id', 'cheque_no', 'deposit_bank_id', 'deposit_account_id', 'cheque_deposit_date', 'account_holder_name');
                }])->with('rv_member:id,first_name,last_name')
                    ->where('branch_id', $branch_id);




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
                if ($arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                $data = $data->orderby('created_at', 'DESC')->get();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('branch', function ($row) {
                        $branch = $row['rv_branch']->name . " (" . $row['rv_branch']->branch_code . ")";
                        return $branch;
                    })
                    ->rawColumns(['branch'])

                    ->addColumn('company_name', function ($row) {
                        $company_name = '';
                        if (is_object($row['company'])) {
                            $company_name = $row['company']->short_name;
                        }
                        return $company_name;
                    })
                    ->rawColumns(['company_name'])
                    ->addColumn('date', function ($row) {

                        return date("d/m/Y", strtotime($row->date));
                    })
                    ->rawColumns(['date'])
                    ->addColumn('rv_mode', function ($row) {
                        if ($row->received_mode == 0) {
                            $mode = "Cash";
                        }
                        if ($row->received_mode == 1) {
                            $mode = "Cheque";
                        }
                        if ($row->received_mode == 2) {
                            $mode = "Online";
                        }
                        return $mode;
                    })
                    ->rawColumns(['rv_mode'])
                    ->addColumn('rv_amount', function ($row) {
                        return number_format((float) $row->amount, 2, '.', '');
                    })
                    ->rawColumns(['rv_amount'])
                    ->addColumn('account_head', function ($row) {

                        return getAcountHeadNameHeadId($row->account_head_id);
                    })
                    ->rawColumns(['account_head'])
                    ->addColumn('employee_code', function ($row) {
                        if ($row['rv_employee'] && $row->employee_id != null) {
                            return $row['rv_employee']->employee_code;
                        }
                        if ($row['rv_member'] && $row->member_id != null) {
                            return \App\Models\MemberCompany::select('member_id')->where('customer_id', $row['rv_member']->id)->first()->member_id;
                        }
                        return "N/A";
                    })
                    ->rawColumns(['employee_code'])

                    ->addColumn('employee_name', function ($row) {
                        if ($row['rv_employee'] && $row->employee_id != null) {
                            return $row['rv_employee']->employee_name ?? "";
                        }
                        if ($row['rv_member'] && $row->member_id != null) {
                            return $row['rv_member']->first_name . " " . $row['rv_member']->last_name ?? '';
                        }
                        return "N/A";
                    })
                    ->rawColumns(['employee_name'])
                    ->addColumn('bank_name', function ($row) {
                        if ($row->type == 4) {
                            return getSamraddhBank($row->bank_id)->bank_name;
                        } else {
                            return 'N/A';
                        }
                    })
                    ->rawColumns(['bank_name'])
                    ->addColumn('bank_account_number', function ($row) {


                        if ($row->type == 4) {
                            return getSamraddhBankAccountId($row->bank_ac_id)->account_no;
                        } else {
                            return 'N/A';
                        }
                    })
                    ->rawColumns(['bank_account_number'])
                    ->addColumn('eli_loan', function ($row) {
                        if ($row->eli_loan_id) {
                            return getAcountHeadNameHeadId($row->eli_loan_id);
                        }
                        return "N/A";
                    })
                    ->rawColumns(['eli_loan'])
                    ->addColumn('day_book', function ($row) {
                        if ($row->received_mode == 0) {
                            if ($row->daybook_type == 0) {
                                return "Cash";
                            } else {
                                return "Cash";
                            }
                        } else {
                            return "N/A";
                        }
                    })
                    ->rawColumns(['day_book'])
                    ->addColumn('cheque_no', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        if ($row->received_mode == 2) {
                            return "N/A";
                        }
                        if ($row->received_mode == 1) {
                            return $row['rvCheque']->cheque_no;
                        }
                    })
                    ->rawColumns(['cheque_no'])
                    ->addColumn('utr_transaction_number', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        return $row->online_tran_no;
                    })
                    ->rawColumns(['utr_transaction_number'])
                    ->addColumn('transaction_date', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        return date("d/m/Y", strtotime($row->online_tran_date));
                    })
                    ->rawColumns(['transaction_date'])
                    ->addColumn('party_bank_name', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        if ($row->received_mode == 1) {
                            return $row->cheque_bank_name;
                        }
                        if ($row->received_mode == 2) {
                            return $row->online_tran_bank_name;
                        }
                    })
                    ->rawColumns(['party_bank_name'])
                    ->addColumn('party_bank_account', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        if ($row->received_mode == 1) {
                            return $row->cheque_bank_ac_no;
                        }
                        if ($row->received_mode == 2) {
                            return $row->online_tran_bank_ac_no;
                        }
                    })
                    ->rawColumns(['party_bank_account'])
                    ->addColumn('received_bank', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        return getSamraddhBank($row->receive_bank_id)->bank_name;
                    })
                    ->rawColumns(['received_bank'])

                    ->addColumn('received_bank_account', function ($row) {
                        if ($row->received_mode == 0) {
                            return "N/A";
                        }
                        return getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no;
                    })
                    ->rawColumns(['received_bank_account'])
                    ->addColumn('bank_slip', function ($row) {
                        if ($row->slip) {
                            // $a = URL::to("/asset/voucher/" . $row->slip . "");
                            $a = ImageUpload::generatePreSignedUrl('voucher/' . $row->slip);
                            return '<a href="' . $a . '" target=_blank >' . $row->slip . '</a>';
                        } else {
                            return 'N/A';
                        }
                    })
                    ->escapeColumns(['received_bank_account'])
                    ->addColumn('created_at', function ($row) {
                        return date("d/m/Y", strtotime($row->created_at));
                    })
                    ->rawColumns(['created_at'])
                    ->addColumn('action', function ($row) {
                        $btn = '';
                        $url = URL::to("branch/voucher/print/" . $row->id . "");
                        if (!in_array('Voucher Print', auth()->user()->getPermissionNames()->toArray())) {
                            $btn .= '<a class="" href="' . $url . '" title="Print"><i class="fas fa-print text-default mr-2"></i></a>';
                        }


                        return $btn;
                    })
                    ->rawColumns(['action'])

                    ->make(true);
            } else {
                return DataTables::of([])
                    ->make(true);
            }
        }
    }


    public function getMemberDetails(Request $request)
    {
        $memberId = $request->member_id;
        $id = \App\Models\MemberCompany::select('customer_id', 'member_id', 'company_id', 'id', 'created_at')->where('member_id', $memberId)->where('branch_id', $request->branch_id)->first();
        $data =  \App\Models\Member::where('id', $id->customer_id)->first(['id', 'first_name', 'last_name']);
        $collectorDetails['id'] = $data->id;
        $collectorDetails['first_name'] = $data->first_name;
        $collectorDetails['last_name'] = $data->last_name;
        $collectorDetails['company_id'] = $id->company_id;

        if ($collectorDetails &&   $id) {
            $createdDate = date("d/m/Y", strtotime(convertDate($id->created_at)));
            return Response::json(['msg_type' => 'success', 'collectorDetails' => $collectorDetails, 'createdDate' => $createdDate]);
        } else {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public function checkGstData(Request $request)
    {
        $companyId =  $request->company_id;
        $stateid = $request->state_id;
        $parts = explode("/", $request->create_application_date);
        $applicationDate = $parts[2] . "/" . $parts[1] . "/" . $parts[0];
        // $stateid = Auth::user()->branch->state_id;
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 122)->first();

        $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $applicationDate)->whereCompanyId($companyId)->exists();
        $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $stateid)->where('applicable_date', '<=', $applicationDate)->whereCompanyId($companyId)->first();
        $gstData = array();
        //Gst Insuramce
        if (isset($getHeadSetting->gst_percentage) &&  $getGstSetting) {
            $gstData['gst_percentage'] =  $getHeadSetting->gst_percentage;
            $gstData['IntraState'] = ($stateid == $getGstSettingno->state_id ? true : false);
        } else {
            $gstData['gst_percentage'] =  '0';
            $gstData['IntraState'] = false;
        }
        return json_encode($gstData);
    }
}
// BRANCH
