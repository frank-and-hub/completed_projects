<?php

namespace App\Http\Controllers\Admin\vendorManagement;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\ExpenseItem;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\RentLiability;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use App\Models\VendorBillPayment;
use App\Models\VendorCategory;
use App\Models\Vendor;
use App\Models\SavingAccount;
use App\Models\AccountHeads;


use App\Models\VendorLog;
use App\Models\VendorCreditNode;
use App\Models\AdvancedTransaction;
use App\Models\AssociateTransaction;

use App\Models\VendorTransaction;

use App\Http\Controllers\Admin\CommanController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;

class BillPaymentController extends Controller
{

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }


    public function index()
    {
        if (check_my_permission(Auth::user()->id, "228") != "1") {
            return redirect()->route('admin.dashboard');
        }
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $data['vendor'] = Vendor::whereId($id)->first();
            $data['vendorID'] = $vendorID = $_GET['id'];
            if (isset($_GET['billId'])) {
                $data['vendorBillID'] = $billID = $_GET['billId'];
                $data['billDetail'] = VendorBill::where('id', $billID)->where('balance','>', 0)->where('is_deleted',0)->get();
                // $data['last_date'] = VendorBill::where('id', $billID)->orderBy('payment_date', 'asc')->first()->bill_date ;
                // $data['last_date'] = date('d/m/Y', strtotime(convertDate($data['last_date'])));
            } else {
                $data['vendorBillID'] = 0;
                $data['billDetail'] = VendorBill::where('vendor_id', $vendorID)->where('balance','>', 0)->where('is_deleted',0)->get();
                // $data['last_date'] = VendorBill::where('vendor_id', $vendorID)->orderBy('payment_date', 'asc')->first()->bill_date ;
                // $data['last_date'] = date('d/m/Y', strtotime(convertDate($data['last_date'])));
            }
        } else {
            $id = '';
            return back()->with('Error', 'Vendor Select ');
        }
      
        $data['title'] = 'Bill Payment';
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->get(['id', 'bank_name', 'company_id']);
        $data['branch'] = \App\Models\Branch::where('status', 1)->get(['id', 'name', 'branch_code']);
        return view('templates.admin.vendor_management.bill_payment.index', $data);
    }

    public function bill()
    {
        $data['title'] = 'Bill Payment';
        $data['branch'] = Branch::where('status', 1)->get();


        return view('templates.admin.bill_payment.index', $data);
    }

    /**
     * Save bill.
     * Route: /bill/payment
     * Method: Post 
     * @return  array()  Response

     */

    public function save(Request $request)
    {

        $rules = [
            'amount' => 'required',
            'payment_date' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        $companyId = $request->company_id;
        if (isset($request->branch_id) && $request->amount > getbranchbankbalanceamounthelper($request->branch_id, $companyId, $request->payment_date)) {
            return back()->with('alert', 'Unsufficient Amount');
        }
        DB::beginTransaction();
        try {

            $globaldate = $request->created_at;
            $select_date = $request->payment_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $vendorDetail = Vendor::where('id', $request->vid)->first();

            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $v_no = NULL;
            $v_date = NULL;

            $typeBill = 27;
            $sub_typeBill = 272;
            $typeAdv = 27;
            $sub_typeAdv = 275;


            $cheque_id = NULL;
            $cheque_number = $cheque_no = NULL;
            $cheque_date = NULL;
            $utr_no = NULL;
            $transaction_date = NULL;
            $neft_charge  = NULL;

            $bank_id = NULL;
            $bank_ac_id = NULL;
            $associate_id = NULL;
            $member_id = NULL;
            $branch_id_to = NULL;
            $branch_id_from = NULL;
            $opening_balance = NULL;
            $closing_balance = NULL;
            $amount_to_id = NULL;
            $amount_to_name = NULL;
            $amount_from_id = NULL;
            $amount_from_name = NULL;
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
            $jv_unique_id = NULL;
            $ssb_account_id_to = NULL;
            $ssb_account_tran_id_to = NULL;
            $ssb_account_tran_id_from = NULL;
            $cheque_type = NULL;
            $cheque_id = NULL;
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
            $cheque_bank_from_id = NULL;
            $cheque_bank_ac_from_id = NULL;

            $paymentMode = $request->payment_mode;
            if ($request->payment_mode == 1) {
                $cheque_id = $request->cheque_id;
                $cheque_number = $cheque_no = $request->cheque_number;
                $cheque_date = $entry_date;
            }
            if ($request->payment_mode == 2) {
                $utr_no = $request->utr_no;
                $transaction_date = $entry_date;
                $neft_charge = $request->neft_charge;
            }
            $totalPaybaleAmount = $request->amount + $neft_charge;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($totalPaybaleAmount, $globaldate);
            $refId = $daybookRef;


            $type_id = $vendorId = $request->vid;
            $bank_id_from_c = $request->bank_id;
            $bank_ac_id_from_c = $bank_id_ac = $request->bank_ac;
            $bank_id_from = $bank_id_from_c;

            $bank_ac_id_from = $bank_ac_id_from_c;
            $bank_name_to = $vendorDetail->bank_name;
            $bank_ac_to = $vendorDetail->bank_ac_no;
            $bank_ifsc_to = $vendorDetail->ifsc;

            if ($request->payment_mode == 1 || $request->payment_mode == 2) { /// bank 

                $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from_c)->first();
                $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $bank_ac_id_from_c)->first();

                $vendorBillPayment['from_bank_name'] = $bankfrmDetail->bank_name;
                $vendorBillPayment['from_bank_branch'] = $bankacfrmDetail->branch_name;
                $vendorBillPayment['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                $vendorBillPayment['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                $bank_id = $bankfrmDetail->id;
                $bank_ac_id = $bankacfrmDetail->id;
                if ($request->payment_mode == 1) {
                    $cheque_no = $cheque_no;
                    $cheque_date = $entry_date;
                    $cheque_bank_from = $bankfrmDetail->bank_name;
                    $cheque_bank_ac_from = $bankacfrmDetail->account_no;
                    $cheque_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                    $cheque_bank_branch_from = $bankacfrmDetail->branch_name;
                    $cheque_bank_from_id = $bank_id_from = $bank_id_from_c;
                    $cheque_bank_ac_from_id = $bank_ac_id_from;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_to_name = $bank_name_to = $vendorDetail->bank_name;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_to_ac_no = $vendorDetail->bank_ac_no;;
                    $cheque_bank_to_ifsc = $vendorDetail->ifsc;
                }
                if ($request->payment_mode == 2) {
                    $transction_bank_from = $bankfrmDetail->bank_name;
                    $transction_bank_ac_from = $bankacfrmDetail->account_no;
                    $transction_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                    $transction_bank_branch_from = $bankacfrmDetail->branch_name;
                    $transction_bank_from_id = $bank_id_from;
                    $transction_bank_from_ac_id = $bank_ac_id_from;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_bank_to_name = $bank_name_to;
                    $transction_bank_to_branch = NULL;
                    $transction_bank_to_ac_no = $bank_ac_to;
                    $transction_bank_to_ifsc = $bank_ifsc_to;
                }
            }
            $vendorBillID = '';
            if (isset($_POST['bill_id'])) {
                foreach (($_POST['bill_id']) as $key => $option) {
                    if ($_POST['pay_amount'][$key] > 0) {
                        $bill_id = $_POST['bill_id'][$key];
                        $billDetaill = $billDetail = VendorBill::where('id', $_POST['bill_id'][$key])->first();
                        $branch_id_bill = $billDetaill->branch_id;
                        $vendorBillID = $billDetaill->id;
                        $status = 1;
                        $trAmount = $billDetaill->transferd_amount + $_POST['pay_amount'][$key];
                        $due = $billDetaill->payble_amount - $trAmount;
                        if ($due == 0) {
                            $status = 2;
                        }
                        $vendorBill['transferd_amount'] = $trAmount;
                        $vendorBill['due_amount'] = $due;
                        $vendorBill['balance'] = $due;
                        $vendorBill['status'] = $status;
                        $vendorBill['payment_date'] = $entry_date;
                        $vendorBillUpdate = VendorBill::find($bill_id);
                        $vendorBillUpdate->update($vendorBill);
                        $desLibe1 = 'Bill(' . $billDetaill->bill_number . ') payment  for Rs.' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '');
                        $vendorBillPayment['branch_id'] = $branch_id_bill;
                        $vendorBillPayment['vendor_id'] = $vendorId;
                        $vendorBillPayment['vendor_bill_id'] = $billDetail->id;
                        $vendorBillPayment['bill_type'] = 0;
                        $vendorBillPayment['withdrawal'] = $_POST['pay_amount'][$key];
                        $vendorBillPayment['description'] = $desLibe1;
                        $vendorBillPayment['currency_code'] = $currency_code;
                        $vendorBillPayment['payment_type'] = 'DR';
                        $vendorBillPayment['payment_mode'] = $request->payment_mode;
                        $vendorBillPayment['payment_date'] = $entry_date;
                        $vendorBillPayment['created_by'] = $created_by;
                        $vendorBillPayment['created_by_id'] = $created_by_id;
                        $vendorBillPayment['created_at'] = $created_at;
                        $vendorBillPayment['updated_at'] = $updated_at;

                        if ($request->payment_mode == 1 || $request->payment_mode == 2) {
                            $vendorBillPayment['to_bank_name'] = $vendorDetail->bank_name;
                            $vendorBillPayment['to_bank_ac_no'] = $vendorDetail->bank_ac_no;
                            $vendorBillPayment['to_bank_ifsc'] = $vendorDetail->ifsc;
                            $vendorBillPayment['from_bank_name'] = $bankfrmDetail->bank_name;
                            $vendorBillPayment['from_bank_branch'] = $bankacfrmDetail->branch_name;
                            $vendorBillPayment['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                            $vendorBillPayment['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                            $vendorBillPayment['from_bank_id'] = $bankfrmDetail->id;
                            $vendorBillPayment['from_bank_ac_id'] = $bankacfrmDetail->id;
                        }
                        if ($request->payment_mode == 1) {

                            $vendorBillPayment['cheque_id_company'] = $cheque_id;
                            $vendorBillPayment['cheque_no_company'] = $cheque_number;
                            $vendorBillPayment['cheque_date'] = $cheque_date;
                        }
                        if ($request->payment_mode == 2) {
                            $vendorBillPayment['transaction_no'] = $utr_no;
                            $vendorBillPayment['transaction_date'] = $transaction_date;
                            $vendorBillPayment['transaction_charge'] = $neft_charge;
                        }
                        $vendorBillPayment['daybook_ref_id'] = $refId;
                        $vendorBillPayment['company_id'] = $companyId;
                        $vendorBillPaymentCreate = VendorBillPayment::create($vendorBillPayment);
                        $billPaymentId = $vendorBillPaymentCreate->id;
                        //-------------------  Branch Daybook ----------------                            
                        if ($request->payment_mode == 1 || $request->payment_mode == 2) {
                            // ------------ bank head --------                                                          
                            $desLibe = $desLibe1;
                            $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id_bill, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pay_amount'][$key],  $desLibe, 'CR', $request->payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                            $description_dr_b = $vendorDetail->name . 'A/c Dr ' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';
                            $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';

                            $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id_bill, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pay_amount'][$key], $desLibe, $description_dr_b, $description_cr_b, 'DR', $request->payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $ssb_account_id_to, $companyId);

                            // --------- bank daybook -----------
                            $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_bill, $opening_balance = NULL, $_POST['pay_amount'][$key], $closing_balance = NULL, $desLibe, $description_dr_b, $description_cr_b, 'DR', $request->payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                        }

                        // ----------- Cash--------
                        if ($request->payment_mode == 0) {

                            // ------------ Cash  head --------                                                          
                            $desLibe = $desLibe1;
                            $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id_bill, $bank_id, $bank_ac_id, 28, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pay_amount'][$key], $desLibe, 'CR', $request->payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                            $description_dr_b1 = $vendorDetail->name . 'A/c Dr ' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';
                            $description_cr_b1 = 'To Branch Cash A/c Cr' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';

                            $brDaybook1 = CommanController::branchDaybookCreateModified($refId, $branch_id_bill, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pay_amount'][$key], $desLibe, $description_dr_b1, $description_cr_b1, 'DR', $request->payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $ssb_account_id_to, $companyId);
                        }

                        // ----------- Eli Amount --------
                        if ($request->payment_mode == 3) {

                            // ------------ Cash  head --------                                                          
                            $desLibe = $desLibe1;
                            $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id_bill, $bank_id, $bank_ac_id, 89, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['pay_amount'][$key],  $desLibe, 'CR', $request->payment_mode, $currency_code, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                            $description_dr_b1 = $vendorDetail->name . 'A/c Dr ' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';
                            $description_cr_b1 = 'To Branch Cash A/c Cr' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';

                            $brDaybook1 = CommanController::branchDaybookCreateModified($refId, $branch_id_bill, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['pay_amount'][$key], $desLibe, $description_dr_b1, $description_cr_b1, 'DR', $request->payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $billPaymentId, $ssb_account_id_to, $companyId);
                        }


                        //---------   vendor transaction -----------

                        $desExp = $desLibe1;

                        $desTran = $desExp;
                        $vendorTran1['type'] = 1;
                        $vendorTran1['sub_type'] = 11;
                        $vendorTran1['type_id'] = $vendorBillID;
                        $vendorTran1['vendor_id'] = $vendorId;
                        $vendorTran1['type_transaction_id'] = $billPaymentId;
                        $vendorTran1['branch_id'] = $branch_id_bill;
                        $vendorTran1['amount'] = $_POST['pay_amount'][$key];
                        $vendorTran1['description'] = $desLibe1;
                        $vendorTran1['payment_type'] = 'DR';
                        $vendorTran1['payment_mode'] = $request->payment_mode;
                        $vendorTran1['currency_code'] = 'INR';
                        $vendorTran1['v_no'] = $v_no;
                        $vendorTran1['bank_id'] = $bank_id;
                        $vendorTran1['account_id'] = $bank_ac_id;

                        $vendorTran1['entry_date'] = $entry_date;
                        $vendorTran1['entry_time'] = $entry_time;
                        $vendorTran1['created_by'] = 1;
                        $vendorTran1['created_by_id'] = $created_by_id;
                        $vendorTran1['created_at'] = $created_at;
                        $vendorTran1['updated_at'] = $updated_at;
                        $vendorTran1['daybook_ref_id'] = $refId;
                        $vendorTran1['company_id'] = $companyId;

                        $vendorTranCreate1 = VendorTransaction::create($vendorTran1);
                        $vendorTranID1 = $vendorTranCreate1->id;
                        /// -----  vendor libility head ------
                        $LibHead = 140;
                        $desLibe = $desLibe1;

                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id_bill, $bank_id, $bank_ac_id, $LibHead, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pay_amount'][$key],  $desLibe, 'DR', $request->payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


                        $vendorLog['vendor_id'] = $vendorId;
                        $vendorLog['vendor_bill_id'] = $vendorBillID;
                        $vendorLog['title'] = 'Bill Payment';
                        $vendorLog['bill_no'] = $billDetaill->bill_number;
                        $vendorLog['description'] = 'Bill Payment for Rs.' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . ' by ' . $created_by_name;
                        $vendorLog['amount'] = $_POST['pay_amount'][$key];
                        $vendorLog['item_detail'] = 'Bill(' . $billDetaill->bill_number . ')';
                        $vendorLog['item_id'] = NULL;
                        $vendorLog['created_by'] = 1;
                        $vendorLog['created_by_id'] = $created_by_id;
                        $vendorLog['created_by_name'] = $created_by_name;
                        $vendorLog['created_at'] = $created_at;
                        $vendorLog['updated_at'] = $updated_at;
                        $vendorLog['daybook_ref_id'] = $refId;
                        $vendorLogCreate = VendorLog::create($vendorLog);
                        $vendorLogID = $vendorLogCreate->id;
                    }
                }
            }
            if ($request->payment_mode == 1) {
                $chequeIssue['cheque_id'] = $cheque_id;
                $chequeIssue['type'] = 10;
                $chequeIssue['sub_type'] = 101;
                $chequeIssue['type_id'] = $request->vid;
                $chequeIssue['cheque_issue_date'] = $entry_date;
                $chequeIssue['created_at'] = $created_at;
                $chequeIssue['updated_at'] = $updated_at;
                $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);

                $chequeUpdate['is_use'] = 1;
                $chequeUpdate['status'] = 3;
                $chequeUpdate['updated_at'] = $updated_at;
                $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                $chequeDataUpdate->update($chequeUpdate);
            }

            $branch_idV = 1;
            if ($request->payment_mode == 0) {
                $branch_idV = $request->branch_id;
            }

            if ($request->amount_excess > 0) {
                $typeAdv = 27;
                $sub_typeAdv = 275;
                $amount_excess = $request->amount_excess;
                $desAdv = $vendorDetail->name . ' advanced Payment of Rs. ' . number_format((float)$amount_excess, 2, '.', '') . '/-';
                $desLibe1 = $desAdv;
                $vendorBillPaymentA['branch_id'] = $branch_idV;
                $vendorBillPaymentA['vendor_id'] = $vendorId;
                $vendorBillPaymentA['bill_type'] = 7;
                $vendorBillPaymentA['deposit'] = $amount_excess;
                $vendorBillPaymentA['description'] = $desAdv;
                $vendorBillPaymentA['currency_code'] = $currency_code;
                $vendorBillPaymentA['payment_type'] = 'CR';
                $vendorBillPaymentA['payment_mode'] = $request->payment_mode;
                $vendorBillPaymentA['payment_date'] = $entry_date;
                $vendorBillPaymentA['created_by'] = $created_by;
                $vendorBillPaymentA['created_by_id'] = $created_by_id;
                $vendorBillPaymentA['created_at'] = $created_at;
                $vendorBillPaymentA['updated_at'] = $updated_at;
                if ($request->payment_mode == 1 || $request->payment_mode == 2) {
                    $vendorBillPaymentA['to_bank_name'] = $vendorDetail->bank_name;
                    $vendorBillPaymentA['to_bank_ac_no'] = $vendorDetail->bank_ac_no;
                    $vendorBillPaymentA['to_bank_ifsc'] = $vendorDetail->ifsc;
                    $vendorBillPaymentA['from_bank_name'] = $bankfrmDetail->bank_name;
                    $vendorBillPaymentA['from_bank_branch'] = $bankacfrmDetail->branch_name;
                    $vendorBillPaymentA['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                    $vendorBillPaymentA['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                    $vendorBillPaymentA['from_bank_id'] = $bankfrmDetail->id;
                    $vendorBillPaymentA['from_bank_ac_id'] = $bankacfrmDetail->id;
                }
                if ($request->payment_mode == 1) {

                    $vendorBillPaymentA['cheque_id_company'] = $cheque_id;
                    $vendorBillPaymentA['cheque_no_company'] = $cheque_number;
                    $vendorBillPaymentA['cheque_date'] = $cheque_date;
                }
                if ($request->payment_mode == 2) {
                    $vendorBillPaymentA['transaction_no'] = $utr_no;
                    $vendorBillPaymentA['transaction_date'] = $transaction_date;
                    $vendorBillPaymentA['transaction_charge'] = $neft_charge;
                }
                $vendorBillPaymentA['daybook_ref_id'] = $refId;
                $vendorBillPaymentA['company_id'] = $companyId;
                $vendorBillPaymentCreate = VendorBillPayment::create($vendorBillPaymentA);

                $billPaymentIdA = $vendorBillPaymentCreate->id;
                $vendorLog['vendor_id'] = $vendorId;
                $vendorLog['title'] = 'Advanced Payment';
                $vendorLog['description'] = 'Vendor advanced Payment for Rs.' . number_format((float)$amount_excess, 2, '.', '') . ' by ' . $created_by_name;
                $vendorLog['amount'] = $amount_excess;
                $vendorLog['item_detail'] = 'Advanced Payment';
                $vendorLog['item_id'] = NULL;
                $vendorLog['created_by'] = 1;
                $vendorLog['created_by_id'] = $created_by_id;
                $vendorLog['created_by_name'] = $created_by_name;
                $vendorLog['created_at'] = $created_at;
                $vendorLog['updated_at'] = $updated_at;
                $vendorLog['daybook_ref_id'] = $refId;
                $vendorLogCreate = VendorLog::create($vendorLog);
                $vendorLogID = $vendorLogCreate->id;

                if ($request->payment_mode == 1 || $request->payment_mode == 2) {
                    // ------------------ samraddh bank entry -(mines) ---------------
                    $description_dr_ba = $vendorDetail->name . 'A/c Dr ' . number_format((float)$amount_excess, 2, '.', '') . '/-';
                    $description_cr_ba = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . number_format((float)$amount_excess, 2, '.', '') . '/-';
                    // bank head 
                    $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_idV, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $typeAdv, $sub_typeAdv, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount_excess, $desAdv, 'CR', $paymentMode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentIdA, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                    // branch day book 
                    //  echo $branch_idV;die;
                    $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_idV, $typeAdv, $sub_typeAdv, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $amount_excess,  $desAdv, $description_dr_ba, $description_cr_ba, 'DR', $request->payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $billPaymentIdA, $ssb_account_id_to, $companyId);

                    // bank hedaybook 
                    $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $typeAdv, $sub_typeAdv, $type_id, $associate_id = NULL, $member_id = NULL, $branch_idV, $opening_balance = NULL, $amount_excess, $closing_balance = NULL, $desAdv, $description_dr_ba, $description_cr_ba, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentIdA, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                } // ----------- Cash--------
                if ($request->payment_mode == 0) {


                    $description_dr_ba = $vendorDetail->name . 'A/c Dr ' . number_format((float)$amount_excess, 2, '.', '') . '/-';
                    $description_cr_ba = 'To  Branch Cash A/c Cr ' . number_format((float)$amount_excess, 2, '.', '') . '/-';
                    // bank head 
                    $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_idV, $bank_id, $bank_ac_id, 28, $typeAdv, $sub_typeAdv, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $amount_excess, $desAdv, 'CR', $paymentMode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentIdA, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                    // branch day book 
                    //  echo $branch_idV;die;
                    $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_idV, $typeAdv, $sub_typeAdv, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $amount_excess, $desAdv, $description_dr_ba, $description_cr_ba, 'DR', $request->payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $billPaymentIdA, $ssb_account_id_to, $companyId);
                }
                $vendorTran1a['type'] = 1;
                $vendorTran1a['type_id'] = $vendorId;
                $vendorTran1a['type_transaction_id'] = $billPaymentIdA;
                $vendorTran1a['branch_id'] = $branch_idV;
                $vendorTran1a['amount'] = $amount_excess;
                $vendorTran1a['description'] = $desAdv;
                $vendorTran1a['payment_type'] = 'DR';
                $vendorTran1a['payment_mode'] = $request->payment_mode;
                $vendorTran1a['total_amount'] = $request->amount;
                $vendorTran1a['sub_type'] = 11;
                $vendorTran1a['currency_code'] = 'INR';
                $vendorTran1a['v_no'] = $v_no;
                $vendorTran1a['entry_date'] = $entry_date;
                $vendorTran1a['entry_time'] = $entry_time;
                $vendorTran1a['created_by'] = 1;
                $vendorTran1a['created_by_id'] = $created_by_id;
                $vendorTran1a['created_at'] = $created_at;
                $vendorTran1a['updated_at'] = $updated_at;
                $vendorTran1a['daybook_ref_id'] = $refId;
                $vendorTran1a['company_id'] = $companyId;
                $vendorTranCreate1 = AdvancedTransaction::create($vendorTran1a);
                $vendorTranID1 = $vendorTranCreate1->id;

                $bk['type'] = 2;
                $bk['vendor_type_id'] = $vendorId;
                $bk['banking_type'] = 2;
                $bk['account_type'] = 1;
                $bk['vendor_type'] = 3;
                $bk['amount'] = $amount_excess;
                $bk['advanced_amount'] = $amount_excess;
                $bk['description'] = $desAdv;
                if ($request->payment_mode == 1 && $request->payment_mode == 2) {
                    $bk['payment_mode'] = 1;
                } else {
                    $bk['payment_mode'] = 2;
                }
                $bk['date'] = $v_date;
                $bk['created_at'] = $created_at;
                $bk['updated_at'] = $updated_at;
                $bk['company_id'] = $companyId;

                \App\Models\BankingLedger::create($bk);
                /// ----------------------------------
                $vendorTran11['type'] = 5;
                $vendorTran11['sub_type'] = 51;
                $vendorTran11['vendor_id'] = $vendorId;
                $vendorTran11['type_transaction_id'] = $billPaymentIdA;
                $vendorTran11['branch_id'] = $branch_idV;
                $vendorTran11['amount'] = $amount_excess;
                $vendorTran11['description'] = $desAdv;
                $vendorTran11['payment_type'] = 'DR';
                $vendorTran11['payment_mode'] = $request->payment_mode;
                $vendorTran11['currency_code'] = 'INR';
                $vendorTran11['v_no'] = $v_no;

                $vendorTran11['bank_id'] = $bank_id;
                $vendorTran11['account_id'] = $bank_ac_id;

                $vendorTran11['entry_date'] = $entry_date;
                $vendorTran11['entry_time'] = $entry_time;
                $vendorTran11['created_by'] = 1;
                $vendorTran11['created_by_id'] = $created_by_id;
                $vendorTran11['created_at'] = $created_at;
                $vendorTran11['updated_at'] = $updated_at;
                $vendorTran11['daybook_ref_id'] = $refId;
                $vendorTran11['company_id'] = $companyId;

                $vendorTranCreate11 = VendorTransaction::create($vendorTran11);
                $vendorTranID11 = $vendorTranCreate11->id;

                // advance head 
                $allTran2ADV = CommanController::newHeadTransactionCreate($refId, $branch_idV, $bank_id, $bank_ac_id, 185, $typeAdv, $sub_typeAdv, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount_excess,  $desAdv, 'DR', $request->payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentIdA, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
            }

            if ($neft_charge > 0 && $request->payment_mode == 2) {
                $desneft = $vendorDetail->name . ' neft charge of Rs. ' . number_format((float)$neft_charge, 2, '.', '') . '/-';
                $description_dr_n = $vendorDetail->name . 'A/c Dr ' . number_format((float)$neft_charge, 2, '.', '') . '/-';
                $description_cr_n = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . number_format((float)$neft_charge, 2, '.', '') . '/-';
                $desneft = $vendorDetail->name . ' neft charge of Rs. ' . number_format((float)$neft_charge, 2, '.', '') . '/-';

                // neft head 
                $allTranSSB = CommanController::newHeadTransactionCreate($refId, $branch_idV, $bank_id, $bank_ac_id, 92, 27, 277, $vendorId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $neft_charge, $desneft, 'DR', $request->payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, NULL, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                // bank head 
                $allTranSSB = CommanController::newHeadTransactionCreate($refId, $branch_idV, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, 27, 277, $vendorId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $neft_charge, $desneft, 'CR', $request->payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, NULL, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                // bank day book 

                $smbdc1 = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, 27, 277, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_idV, $opening_balance = NULL, $neft_charge, $closing_balance = NULL, $desneft, $description_dr_n, $description_cr_n, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, NULL, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                // branch daybook 

                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_idV, 27, 277, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $neft_charge, $desneft, $description_dr_n, $description_cr_n, 'DR', $request->payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, NULL, $ssb_account_id_to, $companyId);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            echo '<pre>'; 
            echo $ex->getLine();
            echo '</br>';
            echo $ex->getMessage();
            die;
            return back()->with('alert', $ex->getMessage());
        }

        return back()->with('success', 'Bill Payment Successfully');
    }
}
