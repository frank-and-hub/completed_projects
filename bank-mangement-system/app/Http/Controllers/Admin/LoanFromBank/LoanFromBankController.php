<?php

namespace App\Http\Controllers\Admin\LoanFromBank;

use Illuminate\Http\Request;

use Auth;

use App\Models\Settings;

use App\Http\Controllers\Controller;

use App\Models\Branch;

use App\Models\AccountHeads;
use App\Models\Companies;

use App\Models\SamraddhBank;

use App\Models\SamraddhBankAccount;

use App\Models\LoanFromBank;

use App\Models\LoanEmi;
use Illuminate\Support\Facades\Cache;



use Validator;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;



class LoanFromBankController extends Controller

{

    public function index()

    {
        if (check_my_permission(Auth::user()->id, "55") != "1") {
            return redirect()->route('admin.dashboard');
        }
        // $data['company'] = Companies::select('id','name')->get();

        $data['title'] = "Account Head Management | Loan From Bank";

        return view('templates.admin.loan_from_bank.index', $data);
    }



    public function createLoanFromBank(Request $request)

    {

        if (check_my_permission(Auth::user()->id, "54") != "1") {
            return redirect()->route('admin.dashboard');
        }


        $data['title'] = "Account Head Management | Create Loan From Bank";

        $data['banks'] =  SamraddhBank::select('id', 'bank_name')->with(['bankAccount' => function ($q) {
            $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
        }])->where('status', 1)
            ->get();

        $data['heads'] = AccountHeads::select('id', 'head_id', 'sub_head')->where('parent_id', 9)->get();
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->where('id', 1)->where('status', 1)->get();

        $data['vendor'] = \App\Models\Vendor::where('type', 0)->where('status', 1)->get(['id', 'name']);



        return view('templates.admin.loan_from_bank.createLoanFromBank', $data);
    }

    public function getloanaccount()
    {
        $data = LoanFromBank::select('loan_account_number')->get();
        return response()->json($data);
    }

    public function getVendorByCompany(Request $request)
    {
        $vendorList =   \App\Models\Vendor::where('company_id', $request->company_id)->where('type', 0)->where('status', 1)->get(['id', 'name']);
        $return_array = compact('vendorList');
        return json_encode($return_array);
    }
    public function storeLoanFromBank(Request $request)

    {

        $head_id =  AccountHeads::orderBy('head_id', 'desc')->first('head_id');

        DB::beginTransaction();

        try {
            
            $globaldate = $request->created_at;
            $amount = $request->loan_amount;
            $parentHeadDetail = AccountHeads::where('head_id', $request->head_type)->first();
            $head_data['sub_head'] = $request->bank_name;
            $head_data['head_id'] = $head_id->head_id + 1;
            $head_data['labels'] = $parentHeadDetail->labels + 1;
            $head_data['parent_id'] = $request->head_type;
            $head_data['parentId_auto_id'] = $parentHeadDetail->id;
            $head_data['cr_nature'] = $parentHeadDetail->cr_nature;
            $head_data['dr_nature'] = $parentHeadDetail->dr_nature;
            $head_data['is_move'] = 1;
            $head_data['status'] = 0;
            $head_data['company_id'] = json_encode([(int)$request->company_id]);

            $idget = AccountHeads::create($head_data);

            $account_head = AccountHeads::where('id', $idget->id)->first();


            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
            $branch_id = $request->branch_id;
            //echo $branch_id;die;

            $data['bank_name'] = $request->bank_name;
            $data['current_balance'] = $request->loan_amount;
            $data['daybook_ref_id'] = $daybookRef;
            $data['branch_name'] = getBranchDetail($request->branch_id)->name;

            if ($request->head_type == 230) {
                $loanType = 0;
            } elseif ($request->head_type == 231) {
                $loanType = 1;
            }

            $data['loan_type'] = $loanType;



            $data['address'] = $request->address;

            $data['emi_amount'] = $request->emi_amount;

            $data['emi_start_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['start_date'])));

            $data['emi_end_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['end_date'])));

            $data['account_head_id'] = $account_head->head_id;

            $data['loan_amount'] = $request->loan_amount;

            $data['remark'] = $request->remark;

            $data['loan_account_number'] = $request->loan_account_number;

            $data['loan_interest_rate'] = $request->loan_interest_rate;

            $data['number_of_emi'] = $request->no_of_emi;
            $data['received_type'] = $request->received_type;
            if ($request->received_type == 1) {
                $payment_mode = 2;
                $data['received_bank'] = $bank_id = $request->received_bank_name;
                $data['received_bank_account'] = $bank_ac_id = $request->received_bank_account;
            } else {
                $payment_mode = 3;
                $data['vendor_id'] = $vendor_id = $vendorId = $request->vendor_id;
                $data['vendor_bill_id'] = $vendor_bill_id = $bill_id = $request->vendor_bill_id;
                $bank_id = $bank_ac_id = NULL;
            }
            $data['payment_mode'] = $payment_mode;
            $data['company_id'] = $request->company_id;


            $loanCreate = LoanFromBank::create($data);

            $encodeDate = json_encode($data);
            $arrs = array("loan_from_bank_id" => $loanCreate->id, "type" => "16", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Loan From bank Create", "data" => $encodeDate);
            // DB::table('user_log')->insert($arrs);




            $currency_code = 'INR';

            $randNumber = mt_rand(0, 999999999999999);
            $v_no = NULL;
            $v_date = NULL;


            $select_date = $request['start_date'];
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));

            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;

            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);



            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;



            $type = 17;
            $sub_type = 171;
            $type_id = $account_head->head_id;
            $type_transaction_id = $loanCreate->id;
            $refId = $daybook_ref_id = $daybookRef;
            $member_id = NULL;
            $amount_from_id = $type;
            $amount_from_name = getAcountHeadNameHeadId($type_id);


            if ($request->received_type == 1) {
                $bankDtail = getSamraddhBank($bank_id);
                $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);

                $amount_to_id = $bank_id;
                $amount_to_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';


                $des = getAcountHeadNameHeadId($type_id) . ' Loan amount transfer to bank ' . $amount_to_name . '  through online';
                $description_cr = 'Bank A/c Dr ' . $amount . '/-';
                $description_dr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . ' /-';
                $transction_bank_to_name = $bankDtail->bank_name;
                $transction_bank_to_ac_no = $bankAcDetail->account_no;
                $transction_bank_to_branch = $bankAcDetail->branch_name;
                $transction_bank_to_ifsc = $bankAcDetail->ifsc_code;
                $transction_bank_from = $request->bank_name;
                $transction_bank_ac_from = $request->loan_account_number;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = $request->branch_id;
                $transction_bank_to = $bank_id;
                $transction_bank_ac_to = $bank_ac_id;
                $lastheadId = $bankDtail->account_head_id;
            } else {

                $vendorDetail = \App\Models\Vendor::where('id', $vendor_id)->first();
                $billDetaill = $billDetail = \App\Models\VendorBill::where('id', $bill_id)->first();

                $des = getAcountHeadNameHeadId($type_id) . ' Loan amount transfer to vendor ' . $vendorDetail->name . 'for  bill(' . $billDetaill->bill_number . ')  ';
                $description_cr = $vendorDetail->name . ' A/c Dr ' . $amount . '/-';
                $description_dr = 'To ' . getAcountHeadNameHeadId($type_id) . ' A/c Cr ' . $amount . ' /-';
                $amount_to_id = $vendor_id;
                $amount_to_name = $vendorDetail->name;

                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $transction_bank_from = $request->bank_name;
                $transction_bank_ac_from = $request->loan_account_number;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = $request->branch_id;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                //$lastheadId=$bankDtail->account_head_id;/// vendor head id 

                $v_no = $randNumber;
                $v_date = $entry_date;
            }




            $ssb_account_id_to = NULL;
            $cheque_bank_from_id = NULL;
            $cheque_bank_ac_from_id = NULL;
            $cheque_bank_to_name = NULL;
            $cheque_bank_to_branch = NULL;
            $cheque_bank_to_ac_no = NULL;
            $cheque_bank_to_ifsc = NULL;
            $transction_bank_from_id = $type_id;
            $transction_bank_from_ac_id = NULL;

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

            $transction_date = $entry_date;

            $branch_id = $request->branch_id;
            $associate_id = NULL;
            $branch_id_to = NULL;
            $branch_id_from = NULL;
            $opening_balance = NULL;
            $closing_balance = NULL;
            $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;
            /// ------------- bank head -------------        



            if ($request->received_type == 1) {
                /// bank head entry 
                $allTran2 = CommanController::newHeadTransactionCreate($daybook_ref_id, $request->branch_id, $bank_id, $bank_ac_id, $lastheadId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $request->company_id);


                $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify(
                    $daybook_ref_id,
                    $bank_id,
                    $bank_ac_id,
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
                    $type_transaction_id,
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
                    $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id,
                    $request->company_id
                );



                //-----------   bank balence  ---------------------------
                /// $bankClosing=CommanController:: checkCreateBankClosing($bank_id,$bank_ac_id,$created_at,$amount,0);

                // if ($current_date == $entry_date)
                // {
                //   $bankClosing = CommanController::checkCreateBankClosing($bank_id, $bank_ac_id, $created_at, $amount, 0);
                // }
                // else
                // {
                //   $bankClosing = CommanController::checkCreateBankClosingCRBackDate($bank_id, $bank_ac_id, $created_at, $amount, 0);
                // }

            } else {

                // vendor entry 

                $bill_id = $vendor_bill_id;

                $branch_id = $branch_id_bill = $billDetaill->branch_id;
                $vendorBillID = $billDetaill->id;
                $status = 1;
                $trAmount = $billDetaill->transferd_amount + $amount;
                $due = $billDetaill->payble_amount - $trAmount;
                if ($due == 0) {
                    $status = 2;
                }

                $vendorBill['transferd_amount'] = $trAmount;
                $vendorBill['due_amount'] = $due;
                $vendorBill['balance'] = $due;
                $vendorBill['status'] = $status;
                $vendorBill['payment_date'] = $entry_date;
                $vendorBillUpdate = \App\Models\VendorBill::find($bill_id);
                $vendorBillUpdate->update($vendorBill);

                $desLibe1 = 'Bill(' . $billDetaill->bill_number . ') payment  for Rs.' . number_format((float)$amount, 2, '.', '') . ' through loan from bank';

                $vendorBillPayment['branch_id'] = $branch_id_bill;
                $vendorBillPayment['vendor_id'] = $vendorId;
                $vendorBillPayment['vendor_bill_id'] = $billDetail->id;
                $vendorBillPayment['bill_type'] = 0;
                $vendorBillPayment['withdrawal'] = $amount;
                $vendorBillPayment['description'] = $desLibe1;
                $vendorBillPayment['currency_code'] = $currency_code;
                $vendorBillPayment['payment_type'] = 'DR';
                $vendorBillPayment['payment_mode'] = $payment_mode;
                $vendorBillPayment['payment_date'] = $entry_date;
                $vendorBillPayment['created_by'] = $created_by;
                $vendorBillPayment['created_by_id'] = $created_by_id;
                $vendorBillPayment['created_at'] = $created_at;
                $vendorBillPayment['updated_at'] = $updated_at;
                $vendorBillPayment['daybook_ref_id'] = $refId;
                $vendorBillPayment['company_id'] = $request->company_id;
                $vendorBillPaymentCreate = \App\Models\VendorBillPayment::create($vendorBillPayment);

                $billPaymentId = $vendorBillPaymentCreate->id;

                //---------   vendor transaction -----------
                
                $desExp = $desLibe1;
                
                $desTran = $desExp;
                $vendorTran1['type'] = 2;
                $vendorTran1['sub_type'] = 21;
                $vendorTran1['type_id'] = $vendorBillID;
                $vendorTran1['vendor_id'] = $vendorId;
                $vendorTran1['type_transaction_id'] = $billPaymentId;
                $vendorTran1['branch_id'] = $branch_id_bill;
                $vendorTran1['amount'] = $amount;
                $vendorTran1['description'] = $desLibe1;
                $vendorTran1['payment_type'] = 'DR';
                $vendorTran1['payment_mode'] = $payment_mode;
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
                $vendorTran1['company_id'] = $request->company_id;
                
                $vendorTranCreate1 = \App\Models\VendorTransaction::create($vendorTran1);
                $vendorTranID1 = $vendorTranCreate1->id;
                // dd($billPaymentId, $vendorTranID1);
                /// -----  vendor libility head ------
                $LibHead = 140;
                $desLibe = $desLibe1;

                $allTran1 = CommanController::newHeadTransactionCreate($daybook_ref_id, $branch_id_bill, $bank_id, $bank_ac_id, $LibHead, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $desLibe, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $request->company_id);



                $vendorLog['vendor_id'] = $vendorId;
                $vendorLog['vendor_bill_id'] = $vendorBillID;
                $vendorLog['title'] = 'Bill Payment Through Loan From Bank';
                $vendorLog['bill_no'] = $billDetaill->bill_number;
                $vendorLog['description'] = 'Bill Payment for Rs.' . number_format((float)$amount, 2, '.', '') . ' by ' . $created_by_name;
                $vendorLog['amount'] = $amount;
                $vendorLog['item_detail'] = 'Bill(' . $billDetaill->bill_number . ')';
                $vendorLog['item_id'] = NULL;
                $vendorLog['created_by'] = 1;
                $vendorLog['created_by_id'] = $created_by_id;
                $vendorLog['created_by_name'] = $created_by_name;
                $vendorLog['created_at'] = $created_at;
                $vendorLog['updated_at'] = $updated_at;
                $vendorLogCreate = \App\Models\VendorLog::create($vendorLog);
                $vendorLogID = $vendorLogCreate->id;
            }


            $branchDayBook = CommanController::branchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $request->company_id);



            /// -------------------- loan from bank -----------------            

            $allTranlb = CommanController::newHeadTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $type_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $request->company_id);






            DB::commit();
        } catch (\Exception $ex) {

            DB::rollback();

            return back()->with('alert', $ex->getMessage());
        }

        return redirect()->route('admin.loan_from_bank')->with('success', 'Head Created Successfully!');
    }



    public function reportListing(Request $request)

    {

        if ($request->ajax() && check_my_permission(Auth::user()->id, "55") == "1") {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if ($arrFormData['is_search'] == 'no' || $arrFormData['company_id'] == null) {
                $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                return json_encode($output);
            }
            $data = LoanFromBank::has('company')->select('id', 'account_head_id', 'bank_name', 'branch_name', 'loan_amount', 'current_balance', 'loan_type', 'loan_account_number', 'loan_interest_rate', 'number_of_emi', 'received_bank', 'remark', 'emi_start_date', 'emi_end_date', 'received_type', 'vendor_id', 'company_id')->with('company:id,name')->with(['bankDetails' => function ($q) {
                $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
            }])->with(['headDetails' => function ($q) {
                $q->select('id', 'head_id', 'sub_head', 'labels');
            }])->where('status', 1)->where('is_deleted', 0);
            if ($arrFormData['company_id'] != 0 && $arrFormData['company_id' ]> 0) {
                $data = $data->where('company_id', $arrFormData['company_id']);
            }
            if ($arrFormData['branch_id'] != 0 && $arrFormData['branch_id'] != "" && $arrFormData['branch_id'] > 0) {
                $branch = Branch::whereId($arrFormData['branch_id'])->first('name');
                // dd($branch->name);
                $data = $data->where('branch_name', 'LIKE', '%' . $branch->name . '%');
            }
            $totalCount = $data->count('id');

            $forcache = $data->orderBy('id', 'DESC')->get()->toArray();
            if (isset($forcache)) {
                $token = session()->get('_token');
                //Set value on caches
                Cache::put('Loansfrombankexport' . $token, $forcache);
                Cache::put('LoansfrombankexportCount' . $token, count($forcache));
                //End Set value on caches
            }


            $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('id', 'DESC')->get();

            $sno = $_POST['start'];
            $rowReturn = array();

            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['bank_name'] = $row->bank_name;
                $val['branch_name'] = $row->branch_name;
                $val['loan_amount'] = number_format((float) $row->loan_amount, 2, '.', '');
                $val['current_balance'] = number_format((float) $row->current_balance, 2, '.', '');
                if ($row->loan_type == 0) {
                    $val['loan_type'] = 'Secure';
                }
                if ($row->loan_type == 1) {
                    $val['loan_type'] = 'In-Secure';
                }

                $val['loan_account_number'] = $row->loan_account_number;
                $val['loan_interest_rate'] = number_format((float) $row->loan_interest_rate, 2, '.', '');
                $val['number_of_emi'] = $row->number_of_emi;

                if ($row->received_bank) {
                    $bank_name =  getSamraddhBank($row->received_bank);
                    $val['received_bank'] = $bank_name->bank_name;
                } else {
                    $val['received_bank'] = 'N/A';
                }

                $account = $row['bankDetails'];

                if (isset($account->account_no)) {
                    $val['received_bank_account'] = $account->account_no;
                } else {
                    $val['received_bank_account'] = 'N/A';
                }

                $val['remark'] = $row->remark;
                $val['start_date'] = date("d/m/Y", strtotime(convertDate($row->emi_start_date)));
                $val['end_date'] = date("d/m/Y", strtotime(convertDate($row->emi_end_date)));
                if ($row->received_type == 1) {
                    $val['received_type'] = 'Bank';
                }
                if ($row->received_type == 2) {
                    $val['received_type'] = 'Vendor';
                }


                if ($row->vendor_id > 0) {
                    $val['vendor_name'] = getVendorDetail($row->vendor_id)->name;
                } else {
                    $val['vendor_name'] = 'N/A';
                }

                $btn = '';
                $head_id = $row['headDetails'];

                $url = URL::to("admin/loanFromBank/edit/" . $row->id . "");
                // if (check_my_permission(Auth::user()->id, 222) == 1 || check_my_permission(Auth::user()->id, 223) == 1 || check_my_permission(Auth::user()->id, 224) == 1) {

                $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                // if (check_my_permission(Auth::user()->id, 222) == 1) {
                $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                // }
                $headLedger = URL::to("admin/loanFromBank/ledger/" . $row->id . '/' . $row->account_head_id . '/' . $head_id->labels . "");
                $btn .= '<a class="dropdown-item" href="' . $headLedger . '" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';

                if ($row->status == 0) {
                    // if (check_my_permission(Auth::user()->id, 224) == 1) {
                    $btn .= '<button class="dropdown-item" href="#" title="" onclick="statusUpdate(' . $row->account_head_id . ')"><i class="icon-checkmark4 mr-2"></i>Active</button>';
                    // }
                    // } else {
                    //     if (check_my_permission(Auth::user()->id, 223) == 1) {
                    // $btn .= '<button class="dropdown-item" href="#" title="" onclick="statusUpdate(' . $row->account_head_id . ')"><i class="icon-checkmark4 mr-2"></i>Deactive</button>';
                    // }
                }

                $btn .= '<button class="dropdown-item delete_expense" data-row-id="' . $row->id . '" title="Delete Expense"><i class="icon-box mr-2"></i> Delete</button>';
                $btn .= '</div></div></div>';
                // }
                $val['company'] = $row['company']->name;
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }

            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn);
            return json_encode($output);
        }
    }



    public function edit_loan_from_bank($id)

    {
        // if (check_my_permission(Auth::user()->id, 222) != 1 || check_my_permission(Auth::user()->id, 55) != 1) {
        //     return redirect()->route('admin.dashboard');
        // }

        $data['title'] = "Account Head Management | Edit Loan From Bank";


        $data['data'] = LoanFromBank::where('id', $id)->with('company:id,name')->first();
        $data['banks'] = SamraddhBank::with('bankAccount')->where('status', 1)->where('company_id', $data['data']['company']['id'])->get();
        return view('templates.admin.loan_from_bank.edit_loan_from_bank', $data);
    }



    public function update_loan_from_bank(Request $request)

    {

        $rules = [

            'loan_account_number' => 'unique:loan_from_banks,loan_account_number,' . $request->id,


        ];

        $customMessages = [
            'required' => 'The :attribute field is unique.'
        ];

        $this->validate($request, $rules, $customMessages);

        try {
            $head_data['sub_head'] = $request->bank_name;

            AccountHeads::where('head_id', $request->head_id)->update($head_data);
            $data['bank_name'] = $request->bank_name;
            $data['branch_name'] = $request->branch_name;
            $data['address'] = $request->address;
            $data['emi_amount'] = $request->emi_amount;

            $data['emi_start_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['start_date'])));

            $data['emi_end_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['end_date'])));

            //$data['loan_amount'] = $request->loan_amount;

            $data['remark'] = $request->remark;

            $data['loan_account_number'] = $request->loan_account_number;

            $data['loan_interest_rate'] = $request->loan_interest_rate;

            $data['number_of_emi'] = $request->no_of_emi;

            $data['received_bank'] = $request->received_bank_name;

            $data['received_bank_account'] = $request->received_bank_account;

            $data['loan_type'] = $request->head_type;
            $date = date("Y-m-d", strtotime(str_replace('/', '-', $request['start_date'])));
            //$data['current_balance'] = $request->loan_amount;
            $dataAllHead =    \App\Models\AllHeadTransaction::select('daybook_ref_id')->first();

            $dataAllHead =    \App\Models\AllHeadTransaction::select('daybook_ref_id')->where('type', 17)->where('head_id', $request->head_id)->where('type_id', $request->head_id)->where('type_transaction_id', $request->id)->first();
            $updatedataAllHead =    \App\Models\AllHeadTransaction::where('daybook_ref_id', $dataAllHead->daybook_ref_id)->update(['entry_date' => $date]);
            $updatedataAllSam =    \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $dataAllHead->daybook_ref_id)->update(['entry_date' => $date]);
            $updatedataAllSam =    \App\Models\BranchDaybook::where('daybook_ref_id', $dataAllHead->daybook_ref_id)->update(['entry_date' => $date]);

            LoanFromBank::where('id', $request->id)->update($data);



            DB::commit();
        } catch (\Exception $ex) {

            DB::rollback();

            return back()->with('alert', $ex->getMessage());
        }

        return redirect()->route('admin.loan_from_bank')->with('success', 'Head Updated Successfully!');
    }



    public function updateStatus(Request $request)

    {

        // print_r($_POST);die;

        $headStatus = AccountHeads::select('status', 'id')->where('head_id', $request->head_id)->first();

        $statusLoan = LoanFromBank::select('status', 'id')->where('account_head_id', $request->head_id)->first();

        $statusLoanUpdate = LoanFromBank::findOrFail($statusLoan->id);

        $updateStatus = AccountHeads::findOrFail($headStatus->id);

        if ($headStatus->status == 0) {

            $updateStatus->status = 1;
        } else {
            $updateStatus->status = 0;
        }
        if ($statusLoan->status == 0) {

            $statusLoanUpdate->status = 1;
        } else {
            $statusLoanUpdate->status = 0;
        }

        $updateStatus = $updateStatus->save();

        $statusLoanUpdate = $statusLoanUpdate->save();

        $message = [$updateStatus];

        return response()->json($updateStatus);
    }





    public function loan_emi()
    {
        if (check_my_permission(Auth::user()->id, "56") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = " Loan From Bank | Loan Emi";

        $data['account'] = LoanFromBank::has('company')->select('id', 'account_head_id', 'bank_name', 'branch_name', 'loan_amount', 'current_balance', 'loan_type', 'loan_account_number', 'loan_interest_rate', 'number_of_emi', 'received_bank', 'remark', 'emi_start_date', 'emi_end_date', 'received_type', 'vendor_id')->where('status', 1)->where('is_deleted', 0)->get();

        // $data['banks'] = SamraddhBank::select('id', 'bank_name')->with(['bankAccount' => function ($q) {
        //     $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
        // }])->where('status', 1)->get();
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->where('id', 1)->where('status', 1)->get();


        return view('templates.admin.loan_from_bank.loan_emi', $data);
    }
    public function loan_emi_bank(Request $request)
    {

        if (check_my_permission(Auth::user()->id, "56") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $account =  SamraddhBank::select('id', 'bank_name')->with(['bankAccount' => function ($q) {
            $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name');
        }])->where('status', 1)->where('company_id', $request->company_id)->get();
        $return_array = compact('account');
        return json_encode($return_array);
    }



    public function get_loan_account_detail(Request $request)

    {

        $id = $request->id;
        $dateGet = '';

        $data['loanData'] = LoanFromBank::has('company')->select('id', 'account_head_id', 'bank_name', 'branch_name', 'loan_amount', 'current_balance', 'loan_type', 'loan_account_number', 'loan_interest_rate', 'number_of_emi', 'received_bank', 'remark', 'emi_start_date', 'emi_end_date', 'received_type', 'vendor_id', 'company_id')->with('company:id,name')->where('id', $id)->first();
        if ($data['loanData']) {
            $dateGet = date("d/m/Y", strtotime(convertDate($data['loanData']->emi_start_date)));
            $data['company_name'] =  $data['loanData']['company']->name;
            $data['company_id'] =  $data['loanData']['company']->id;
        }
        $data['dateGet'] = $dateGet;
        return response()->json($data);
    }



    public function save_loan_emi(Request  $request)
    {
        DB::beginTransaction();
        try {
            $rules = [
                'bank_name' => 'required',
                'loan_amount' => 'required',
                'loan_account_number' => 'required',
                'date' => 'required',
                'emi_number' => 'required',
                'emi_principal_amount' => 'required',
                'emi_interest_rate' => 'required',
                'current_loan_amount' => 'required',
                'received_bank_name' => 'required',
                'received_bank_account' => 'required',

            ];

            $customMessages = [
                'required' => 'The :attribute field is required.'
            ];



            $this->validate($request, $rules, $customMessages);
            // DB::beginTransaction();
            // try {

            $globaldate = $request->created_at;


            $currency_code = 'INR';

            $randNumber = mt_rand(0, 999999999999999);
            $v_no = NULL;
            $v_date = NULL;


            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $amount = $request->emi_principal_amount;


            $select_date = $request->date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));

            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;

            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);

            $accountDetail = LoanFromBank::where('id', $request->loan_account_number)->first();
            $data['loan_bank_name'] = $request->bank_name;
            $data['account_head_id'] = $request->account_head_id;
            $data['loan_amount'] = $request->loan_amount;
            $data['loan_emi_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
            $data['loan_bank_account'] = $request->loan_account_number;
            $data['loan_from_bank_id'] = $request->loan_from_bank_id;
            $data['emi_principal_amount'] = $request->emi_principal_amount;
            $data['emi_interest_rate'] = $request->emi_interest_rate;
            $data['emi_number'] = $request->emi_number;
            $data['payment_mode'] = 4;
            $data['received_bank'] = $bank_id = $request->received_bank_name;
            $data['received_bank_account'] = $bank_ac_id = $request->received_bank_account;
            $data['company_id'] = $company_id = $request->company_id;
            $loanEmiCreate = LoanEmi::create($data);

            $remaningBalance = $accountDetail->current_balance - $request->emi_principal_amount;
            $accountDetail->update(['current_balance' =>  $remaningBalance]);
            $amount1 = $request->emi_principal_amount + $request->emi_interest_rate;
            $member_id = NULL;
            $type = 17;
            $sub_type = 172;
            $type_id = $request->account_head_id;
            $type_transaction_id = $loanEmiCreate->id;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($amount1, $globaldate);
            $refId = $daybook_ref_id = $daybookRef;
            $bankDtail = getSamraddhBank($bank_id);
            $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);

            $amount_from_id = $bank_id;
            $amount_from_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';

            $amount_to_id = $type_id;
            $amount_to_name = getAcountHeadNameHeadId($type_id);

            $payment_mode = 4;

            $des = getAcountHeadNameHeadId($type_id) . ' Loan emi payment through auto debit(ECS) ' . $amount_from_name;
            $bkAmount = $request->emi_interest_rate + $amount;
            $description_dr = 'To Bank A/c Cr ' . $bkAmount . '/-';
            $description_cr = getAcountHeadNameHeadId($type_id) . ' A/c Dr ' . $bkAmount . ' /-';

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

            $branch_id = $request->branch_id;
            $associate_id = NULL;
            $branch_id_to = NULL;
            $branch_id_from = NULL;
            $opening_balance = NULL;
            $closing_balance = NULL;
            $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;


            /// -------------- interest entry ----------------
            $branch_id = Branch::where('name', $request->branch_name)->first('id');
            $branch_id = $branch_id['id'];
            $branchh_id = $branch_id;
            $allTranINterest = CommanController::newHeadTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, 97, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $request->emi_interest_rate, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $request->company_id);

            $branchDayBook = CommanController::branchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $request->emi_interest_rate + $amount, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $request->company_id);


            /// ------------- bank head -------------
            $bkAmount = $request->emi_interest_rate + $amount;

            $allTran2 = CommanController::newHeadTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $bankDtail->account_head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $bkAmount, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $request->company_id);

            $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify(
                $daybook_ref_id,
                $bank_id,
                $bank_ac_id,
                $type,
                $sub_type,
                $type_id,
                $associate_id = NULL,
                $member_id,
                $branch_id = NULL,
                $opening_balance = NULL,
                $bkAmount,
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
                $type_transaction_id,
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
                $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id,
                $request->company_id
            );
            //-----------   bank balence  ---------------------------
            // $bankClosing=CommanController:: checkCreateBankClosingDR($bank_id,$bank_ac_id,$created_at,$amount1,0);
            // if ($current_date == $entry_date) {
            //     $bankClosing = CommanController::checkCreateBankClosingDR($bank_id, $bank_ac_id, $created_at, $amount1, 0);
            // } else {
            //     $bankClosing = CommanController::checkCreateBankClosingDRBackDate($bank_id, $bank_ac_id, $created_at, $amount1, 0);
            // }
            /// -------------------- loan from bank -----------------
            $allTranlb = CommanController::newHeadTransactionCreate($daybook_ref_id, $branchh_id, $bank_id, $bank_ac_id, $type_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $request->company_id);



            //  DB::commit();

            // } 

            // catch (\Exception $ex) 

            // {

            //     DB::rollback(); 

            //     return back()->with('alert', $ex->getMessage());

            // }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.loan_emi')->with('success', 'Loan Emi Payment Created Successfully!');
    }



    public function loan_emi_report()

    {
        if (check_my_permission(Auth::user()->id, "57") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = "Account Head Management | Loan From Bank | Loan Emi List";
        $data['company'] = Companies::select('id', 'name')->withoutGlobalScopes()->get();
        $data['loan'] = LoanFromBank::has('company')->select('id', 'account_head_id', 'bank_name', 'branch_name', 'loan_amount', 'current_balance', 'loan_type', 'loan_account_number', 'loan_interest_rate', 'number_of_emi', 'received_bank', 'remark', 'emi_start_date', 'emi_end_date', 'received_type', 'vendor_id')->where('status', 1)->where('is_deleted', 0)->get();

        return view('templates.admin.loan_from_bank.loan_emi_list', $data);
    }
    public function loan_emi_report_bank(Request $request)

    {

        if (check_my_permission(Auth::user()->id, "57") != "1") {
            return redirect()->route('admin.dashboard');
        }
        if ($request->company_id == 0) {
            $account = LoanFromBank::has('company')->select('id', 'account_head_id', 'bank_name', 'branch_name', 'loan_amount', 'current_balance', 'loan_type', 'loan_account_number', 'loan_interest_rate', 'number_of_emi', 'received_bank', 'remark', 'emi_start_date', 'emi_end_date', 'received_type', 'vendor_id')->where('status', 1)->where('is_deleted', 0)->get();
        } else {
            $account = LoanFromBank::has('company')->select('id', 'account_head_id', 'bank_name', 'branch_name', 'loan_amount', 'current_balance', 'loan_type', 'loan_account_number', 'loan_interest_rate', 'number_of_emi', 'received_bank', 'remark', 'emi_start_date', 'emi_end_date', 'received_type', 'vendor_id')->where('status', 1)->where('is_deleted', 0)->where('company_id', $request->company_id)->get();
        }
        $return_array = compact('account');
        return json_encode($return_array);
    }



    public function loanemiReportListing(Request $request)

    {
        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = LoanEmi::select('loan_emi_date', 'emi_principal_amount', 'emi_interest_rate', 'emi_number', 'loan_from_bank_id', 'received_bank_account', 'received_bank', 'company_id')->with(['loanBank' => function ($q) {
                    $q->select('id', 'loan_amount', 'bank_name', 'loan_account_number');
                }])->with(['loanSamraddhBankAccount' => function ($q) {
                    $q->select('id', 'account_no');
                }])->with(['loanSamraddhBank' => function ($q) {
                    $q->select('id', 'bank_name');
                }])->with('company:id,name');
                //fillter array 


                if ($arrFormData['loanAccount'] != '') {
                    $loanAccount = $arrFormData['loanAccount'];
                    $data = $data->where('loan_from_bank_id', $loanAccount);
                }

                $forcache = $data->orderBy('id', 'DESC')->get()->toArray();
                if (isset($forcache)) {
                    $token = session()->get('_token');

                    //Set value on caches
                    Cache::put('LoanEMIexport' . $token, $forcache);
                    Cache::put('LoanEMIexportCount' . $token, count($forcache));
                    //End Set value on caches
                }
                /******* fillter query End ****/
                $data1 = $data->orderBy('id', 'DESC')->count('id');
                $count = $data1;
                $data = $data->orderBy('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $totalCount = ($count);
                $sno = $_POST['start'];
                $rowReturn = array();

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row['company']->name;
                    if ($row['loanBank']) {
                        $val['bank_name'] = $row['loanBank']->bank_name;
                    } else {
                        $val['bank_name'] = 'N/A';
                    }

                    if ($row['loanBank']) {
                        $val['bank_account'] = $row['loanBank']->loan_account_number;
                    } else {
                        $val['bank_account'] = 'N/A';
                    }

                    $val['emi_date'] = date("d/m/Y", strtotime($row->loan_emi_date));

                    if ($row['loanBank']) {
                        $val['loan_amount'] = number_format((float)$row['loanBank']->loan_amount, 2, '.', '');
                    } else {
                        $val['loan_amount'] = 'N/A';
                    }

                    $val['emi_number'] = $row->emi_number;
                    // Changes by Gaurav (10/01/2024) start here------------->
                    $emi_principal = number_format((float) $row->emi_principal_amount, 2, '.', '');
                    $emi_interest_rate = number_format((float) $row->emi_interest_rate, 2, '.', '');

                    $emi_amountget = $emi_principal + $emi_interest_rate;
                    $val['new_emi_amount'] = number_format((float) $emi_amountget, 2, '.', '');
                    $val['emi_principal'] = $emi_principal;
                    $val['emi_interest_rate'] = $emi_interest_rate;

                    // $val['emi_amount'] = number_format((float) $row->emi_principal_amount, 2, '.', '');
                    // $val['emi_interest_rate'] = number_format((float) $row->emi_interest_rate, 2, '.', '');

                    // Changes by Gaurav (10/01/2024) end here------------->

                    $bank_name =  $row['loanSamraddhBank']; //getSamraddhBank($row->received_bank);

                    if ($bank_name) {
                        $val['received_bank'] = $bank_name->bank_name;
                    } else {
                        $val['received_bank'] = 'N/A';
                    }

                    $account = $row['loanSamraddhBankAccount']; //SamraddhBankAccount::where('bank_id',$row->received_bank)->first();
                    if (isset($account->account_no)) {
                        $val['received_bank_account'] = $account->account_no;
                    } else {
                        $val['received_bank_account'] = 'N/A';
                    }

                    $rowReturn[] = $val;
                }

                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
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


    public function ledger($id, $head_id, $label)
    {
        $data['title'] = "Account Head Management | Loan From Bank Ledger Report";

        $data['head'] = $head_id;
        $data['label'] = $label;
        $data['detail'] = LoanFromBank::where('id', $id)->first();
        return view('templates.admin.loan_from_bank.ledger', $data);
    }

    public function ledgerListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $id = $arrFormData['head'];
            $label = $arrFormData['label'];
            $info = 'head' . $label;
            $date = $arrFormData['start_date1'];
            $end_date = $arrFormData['end_date1'];

            $data = \App\Models\AllHeadTransaction::where('head_id', $id)->where('type', 17)->where('is_deleted', 0)->with(['account_number:id,account_no', 'bankname:id,bank_name', 'company:id,name']);

            if ($date != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($date)));
                if ($end_date != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($end_date)));
                    $data = $data->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate]);
                } else {
                    $endDate = '';
                    $data = $data->where(\DB::raw('DATE(entry_date)'), '>=', $startDate);
                }
            }

            $data = $data->orderBy('entry_date', 'ASC')->get();
            $forcache = $data;
            $balance = 0;
            if (isset($forcache)) {
                $token = session()->get('_token');

                //Set value on caches
                Cache::put('Loanfrombankexport' . $token, $forcache);
                Cache::put('LoanfrombankexportCount' . $token, count($forcache));
                //End Set value on caches
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company', function ($row) {
                    return $row->company->name;
                })
                ->addColumn('type', function ($row) {
                    $getTransType = \App\Models\TransactionType::where('type', $row->type)->where('sub_type', $row->sub_type)->first();
                    $type = '';
                    if ($row->type == $getTransType->type) {
                        if ($row->sub_type == $getTransType->sub_type) {
                            $type = $getTransType->title;
                        }
                    }
                    if ($row->type == 21) {
                        $record = \App\Models\ReceivedVoucher::where('id', $row->type_id)->first();
                        if ($record) {
                            $type = $record->particular;
                        } else {
                            $type = "N/A";
                        }
                    }
                    return $type;
                })

                ->addColumn('amount', function ($row) {
                    if ($row->payment_type == 'CR') {
                        return number_format((float)$row->amount, 2, '.', '');
                    } else {
                        return 0;
                    }
                })
                ->rawColumns(['amount'])
                ->addColumn('amount1', function ($row) {
                    if ($row->payment_type == 'DR') {
                        return number_format((float)$row->amount, 2, '.', '');
                    } else {
                        return 0;
                    }
                })
                ->rawColumns(['amount1'])
                ->addColumn('amount2', function ($row) use (&$balance) {


                    if ($row->payment_type == 'DR') {
                        $balance = $balance - $row->amount;
                    } else {
                        $balance = $balance + $row->amount;
                    }

                    return number_format((float)$balance, 2, '.', '');
                })
                ->rawColumns(['amount2'])

                ->addColumn('interest', function ($row) use ($id) {
                    $emi_interest_rate = \App\Models\AllHeadTransaction::where('head_id', 97)->where('daybook_ref_id', $row->daybook_ref_id)->first();

                    if (isset($emi_interest_rate->amount)) {
                        return $emi_interest_rate->amount;
                    } else {
                        return 'N/A';
                    }
                })

                ->rawColumns(['interest'])
                ->addColumn('description', function ($row) {
                    if ($row) {
                        return $row->description;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['description'])

                ->addColumn('payment_type', function ($row) {
                    $payment_type = 'N/A';
                    if ($row->payment_type == 'DR') {
                        $payment_type = 'Debit';
                    }
                    if ($row->payment_type == 'CR') {
                        $payment_type = 'Credit';
                    }
                    return $payment_type;
                })
                ->rawColumns(['payment_type'])
                ->addColumn('payment_mode', function ($row) {
                    $payment_type = 'N/A';
                    if ($row->payment_mode == 0) {
                        $payment_mode = 'Cash';
                    }
                    if ($row->payment_mode == 1) {
                        $payment_mode = 'Cheque';
                    }
                    if ($row->payment_mode == 2) {
                        $payment_mode = 'Online Transfer';
                    }
                    if ($row->payment_mode == 3) {
                        $payment_mode = 'SSB Transfer Through JV';
                    }
                    if ($row->payment_mode == 4) {
                        if ($row->payment_type == 'CR') {
                            $payment_mode =  "Auto Credit";
                        } else {
                            $payment_mode = "Auto Debit";
                        }
                    }
                    if ($row->payment_mode == 6) {

                        $payment_mode =  "JV";
                    }

                    return $payment_mode;
                })
                ->rawColumns(['payment_mode'])
                ->addColumn('received_bank', function ($row) {
                    return ($row->bankname->bank_name) ?? "N/a";
                })
                ->rawColumns(['received_bank'])
                ->addColumn('received_bank_account', function ($row) {
                    // if ($row->payment_mode == 2) {
                    //     $transction_bank_to_ac_no = $row->transction_bank_to_ac_no;
                    //     return $transction_bank_to_ac_no;
                    // }
                    // if ($row->payment_mode == 4) {

                    //     $bank_id = $row->amount_from_id;

                    //     $bankDtail = getSamraddhBank($bank_id);
                    //     $bankAcDetail = SamraddhBankAccount::where('bank_id', $bank_id)->first();

                    //     return $bankAcDetail->account_no;
                    // }
                    return ($row->account_number->account_no) ?? "N/a";
                })
                ->rawColumns(['received_bank_account'])
                ->addColumn('date', function ($row) {
                    if ($row->entry_date) {

                        $date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
                        return $date;
                    } else {
                        return "N/A";
                    }
                })
                ->rawColumns(['date'])

                ->make(true);
        }
    }
    public function Loanfrombankexport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('Loanfrombankexport' . $token);
        $count = Cache::get('LoanfrombankexportCount' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/Account_Head_Management.csv";
        $fileName = env('APP_EXPORTURL') . "asset/Account_Head_Management.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        if ($start == 0) {
            $handle = fopen($fileName, 'w');
        } else {
            $handle = fopen($fileName, 'a');
        }
        if ($start == 0) {
            $headerDisplayed = false;
        } else {
            $headerDisplayed = true;
        }
        $sno = $_POST['start'];
        foreach ($data as $row) {
            $sno++;
            $val["S no"] = $sno;
            $val["Company Name"] = $row->company->name;
            $val["Created At"] = date("d/m/Y", strtotime(convertDate($row->entry_date)));
            $getTransType = \App\Models\TransactionType::where('type', $row->type)->where('sub_type', $row->sub_type)->first();
            $type = '';
            if ($row->type == $getTransType->type) {
                if ($row->sub_type == $getTransType->sub_type) {
                    $type = $getTransType->title;
                }
            }
            if ($row->type == 21) {
                $record = \App\Models\ReceivedVoucher::where('id', $row->type_id)->first();
                if ($record) {
                    $type = $record->particular;
                } else {
                    $type = "N/A";
                }
            }
            $val["Type"] = $type;
            $val["Description"] = ($row->description) ?? "N/A";
            if ($row->payment_type == 'CR') {
                $cr = number_format((float)$row->amount, 2, '.', '');
            } else {
                $cr = 0;
            }
            $val["Credit(CR)"] = $cr;
            if ($row->payment_type == 'DR') {
                $dr =  number_format((float)$row->amount, 2, '.', '');
            } else {
                $dr =  0;
            }
            $val["Debit(DR)"] = $dr;
            $emi_interest_rate = \App\Models\AllHeadTransaction::where('head_id', 97)->where('daybook_ref_id', $row->daybook_ref_id)->first();

            if (isset($emi_interest_rate->amount)) {
                $Intrest =  $emi_interest_rate->amount;
            } else {
                $Intrest =  'N/A';
            }
            $val["Intrest Amount"] = $Intrest;
            $balance = 0;
            if ($row->payment_type == 'DR') {
                $balance = $balance - $row->amount;
            } else {
                $balance = $balance + $row->amount;
            }

            $balance = number_format((float)$balance, 2, '.', '');
            $val["Balance"] = $balance;
            $payment_type = 'N/A';
            if ($row->payment_type == 'DR') {
                $payment_type = 'Debit';
            }
            if ($row->payment_type == 'CR') {
                $payment_type = 'Credit';
            }
            $val["Payment Type"] = $payment_type;
            $payment_mode = 'N/A';
            if ($row->payment_mode == 0) {
                $payment_mode = 'Cash';
            }
            if ($row->payment_mode == 1) {
                $payment_mode = 'Cheque';
            }
            if ($row->payment_mode == 2) {
                $payment_mode = 'Online Transfer';
            }
            if ($row->payment_mode == 3) {
                $payment_mode = 'SSB Transfer Through JV';
            }
            if ($row->payment_mode == 4) {
                if ($row->payment_type == 'CR') {
                    $payment_mode =  "Auto Credit";
                } else {
                    $payment_mode = "Auto Debit";
                }
            }
            if ($row->payment_mode == 6) {

                $payment_mode =  "JV";
            }

            $val["Payment Mode"] = $payment_mode;

            // if ($row->payment_mode == 4) {

            //     $bank_id = $row->amount_from_id;

            //     $bankDtail = getSamraddhBank($bank_id);


            //     $bank = $bankDtail->bank_name;
            // }
            // if ($row->payment_mode == 2) {

            //     $transction_bank_to_name = $row->transction_bank_to_name;
            //     $bank = $transction_bank_to_name;
            // }
            $val["Bank"] = ($row->bankname->bank_name) ?? "N/a";

            // if ($row->payment_mode == 2) {
            //     $transction_bank_to_ac_no = $row->transction_bank_to_ac_no;
            //     $acc_no = $transction_bank_to_ac_no;
            // }
            // if ($row->payment_mode == 4) {

            //     $bank_id = $row->amount_from_id;

            //     $bankDtail = getSamraddhBank($bank_id);
            //     $bankAcDetail = SamraddhBankAccount::where('bank_id', $bank_id)->first();

            //     $acc_no = $bankAcDetail->account_no;

            // }
            $val["Bank Account"] = ($row->account_number->account_no) ?? "N/A";

            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float)$percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result'        => $result,
            'start'         => $start,
            'limit'         => $limit,
            'totalResults'  => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        //if($percentage > 100){
        //return Excel::download(new LoanReportListExport($DataArray), $fileName);
        //}else{
        //Excel::store(new LoanReportListExport($DataArray), $fileName);
        echo json_encode($response);
        //}
    }
    public function Loansfrombankexport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('Loansfrombankexport' . $token);
        $count = Cache::get('LoansfrombankexportCount' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/Loan_from_bank.csv";
        $fileName = env('APP_EXPORTURL') . "asset/Loan_from_bank.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        if ($start == 0) {
            $handle = fopen($fileName, 'w');
        } else {
            $handle = fopen($fileName, 'a');
        }
        if ($start == 0) {
            $headerDisplayed = false;
        } else {
            $headerDisplayed = true;
        }
        $sno = $_POST['start'];
        foreach (array_slice($data, $start, $limit) as $row) {
            $sno++;

            $val['S No.'] = $sno;
            $val['company'] = $row['company']['name'];
            $val['Bank Name'] = $row['bank_name'];
            $val['Branch Name'] = $row['branch_name'];
            $val['Loan Amount'] = number_format((float) $row['loan_amount'], 2, '.', '');
            $val['Current Balance'] = number_format((float) $row['current_balance'], 2, '.', '');
            if ($row['loan_type'] == 0) {
                $val['Loan Type'] = 'Secure';
            }
            if ($row['loan_type'] == 1) {
                $val['Loan type'] = 'In-Secure';
            }

            $val['Loan Account Number'] = $row['loan_account_number'];
            $val['Loan Interest Rate'] = number_format((float) $row['loan_interest_rate'], 2, '.', '');
            $val['Number of EMI'] = $row['number_of_emi'];

            if ($row['received_bank']) {
                $bank_name =  getSamraddhBank($row['received_bank']);
                $val['Received Bank'] = $bank_name->bank_name;
            } else {
                $val['Received Bank'] = 'N/A';
            }

            $account = $row['bank_details'];
            if ($row['bank_details']) {
                $aa = '' . $row['bank_details']['account_no'] . '';
                $val['Account Number'] = strval($aa);
                // p($val['Account Number']);
            } else {
                $val['Account Number'] = 'N/A';
            }

            $val['Remark'] = $row['remark'];
            $val['Start Date'] = date("d/m/Y", strtotime(convertDate($row['emi_start_date'])));
            $val['End Date'] = date("d/m/Y", strtotime(convertDate($row['emi_end_date'])));
            if ($row['received_type'] == 1) {
                $val['Received Type'] = 'Bank';
            }
            if ($row['received_type'] == 2) {
                $val['Received Type'] = 'Vendor';
            }


            if ($row['vendor_id'] > 0) {
                $val['Vendor Name'] = getVendorDetail($row['vendor_id'])->name;
            } else {
                $val['Vendor Name'] = 'N/A';
            }

            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float)$percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result'        => $result,
            'start'         => $start,
            'limit'         => $limit,
            'totalResults'  => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        //if($percentage > 100){
        //return Excel::download(new LoanReportListExport($DataArray), $fileName);
        //}else{
        //Excel::store(new LoanReportListExport($DataArray), $fileName);
        echo json_encode($response);
        //}
    }
    public function Loanemiexport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('LoanEMIexport' . $token);
        $count = Cache::get('LoanEMIexportCount' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/Loan_EMI.csv";
        $fileName = env('APP_EXPORTURL') . "asset/Loan_EMI.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        if ($start == 0) {
            $handle = fopen($fileName, 'w');
        } else {
            $handle = fopen($fileName, 'a');
        }
        if ($start == 0) {
            $headerDisplayed = false;
        } else {
            $headerDisplayed = true;
        }
        $sno = $_POST['start'];
        foreach (array_slice($data, $start, $limit) as $row) {
            $sno++;

            $val['S No.'] = $sno;
            $val['S No.'] = $sno;
            $val['Company Name'] = $row['company']['name'];
            if ($row['loan_bank']) {
                $val['Bank Name'] = $row['loan_bank']['bank_name'];
            } else {
                $val['Bank Name'] = 'N/A';
            }

            if ($row['loan_bank']) {
                $val['Loan Account'] = $row['loan_bank']['loan_account_number'];
            } else {
                $val['Loan Account'] = 'N/A';
            }

            
            if ($row['loan_bank']) {
                $val['Loan Amount'] = number_format((float)$row['loan_bank']['loan_amount'], 2, '.', '');
            } else {
                $val['Loan Amount'] = 'N/A';
            }

            $val['EMI Date'] = date("d/m/Y", strtotime($row['loan_emi_date']));
            
            $val['EMI Number'] = $row['emi_number'];
            $emi_principal = number_format((float) $row['emi_principal_amount'], 2, '.', '');
            $emi_interest_rate = number_format((float) $row['emi_interest_rate'], 2, '.', '');
            $val['EMI Amount'] = $emi_principal + $emi_interest_rate;
            $val['EMI Principal'] = number_format((float) $row['emi_principal_amount'], 2, '.', '');
            $val['EMI Interest Rate'] = number_format((float) $row['emi_interest_rate'], 2, '.', '');

            $bank_name =  $row['loan_samraddh_bank']; //getSamraddhBank($row->received_bank);

            if ($bank_name) {
                $val['Received Bank'] = $row['loan_samraddh_bank']['bank_name'];
            } else {
                $val['Received Bank'] = 'N/A';
            }

            $account = $row['loan_samraddh_bank_account']; //SamraddhBankAccount::where('bank_id',$row->received_bank)->first();
            if (isset($account)) {
                $val['Bank A/c No.'] = $row['loan_samraddh_bank_account']['account_no'];
            } else {
                $val['Bank A/c No.'] = 'N/A';
            }

            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float)$percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result'        => $result,
            'start'         => $start,
            'limit'         => $limit,
            'totalResults'  => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        //if($percentage > 100){
        //return Excel::download(new LoanReportListExport($DataArray), $fileName);
        //}else{
        //Excel::store(new LoanReportListExport($DataArray), $fileName);
        echo json_encode($response);
        //}
    }
    public function delete(Request $request)
    {
        $getData = LoanFromBank::findorfail($request->id);
        $deleteAllHeadTransaction = \App\Models\AllHeadTransaction::where('daybook_ref_id', $getData->daybook_ref_id)->update(['is_deleted' => 1]);
        $deleteSamraddhBank = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $getData->daybook_ref_id)->update(['is_deleted' => 1]);
        $deleteBranchDaybook = \App\Models\BranchDaybook::where('daybook_ref_id', $getData->daybook_ref_id)->update(['is_deleted' => 1]);
        $deleteHead = \App\Models\AccountHeads::where('head_id', $getData->account_head_id)->update(['status' => 9]);

        $deleteLoanFromBank = $getData->update(['is_deleted' => 1]);
        if ($deleteLoanFromBank) {
            $message = 'Loan From Bank Deleted Successfully!';
            $status = 1;
        } else {
            $message = 'SomeThing Wrong!';
            $status = 0;
        }
        return response()->json(['message' => $message, 'status' => $status]);
    }
}
