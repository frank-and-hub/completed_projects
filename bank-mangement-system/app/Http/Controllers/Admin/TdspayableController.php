<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\{Auth, Hash};
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\{Files,Branch,AccountHeads,AllHeadTransaction, SamraddhBank, SamraddhBankAccount, TdsPayable, TdsTransfer};
use App\Http\Controllers\Admin\CommanController;
use Carbon\Carbon;
use DB;
use Str;
use URL;
use Session;
use App\Services\ImageUpload;
class TdspayableController extends Controller
{
    use SoftDeletes;
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "161") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Duties and Taxes | Report';
        $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
        // $data['tdsHeads'] = AccountHeads::where('parent_id', 22)->pluck('sub_head', 'head_id');
        $data['tdsHeads'] = AccountHeads::whereIn('parent_id', [22,322,330])->pluck('sub_head', 'head_id');
        $data['view'] = 0;
        return view('templates.admin.tds_payable.index', $data);
    }
    public function tds_payable_listing(Request $request)
    {
        // dd($request->all());
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = AllHeadTransaction::has('company')->select('id', 'created_at', 'head_id', 'member_id', 'branch_id', 'daybook_ref_id', 'amount', 'payment_type', 'company_id')
                ->with([
                    'member:id,member_id,first_name,last_name',
                    'member.memberIdProof:id,first_id_no,second_id_no,member_id,first_id_type_id,second_id_type_id',
                    'AccountHeads:id,head_id,sub_head,cr_nature',
                    'branch:id,name',
                    'company:id,name,short_name'
                ]);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {                
                if (isset($arrFormData['head_id']) && $arrFormData['head_id'] != '') {
                    $headId = $arrFormData['head_id'];
                    $data = $data->where('head_id', (int)$headId);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                $data = $data
                ->whereNotIn('sub_type',[92,93])
                ->where('payment_type', 'CR');
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0 ) {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', (int)$branch_id);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', (int)$company_id);
                }
            } else {
                $data = $data->where('id', 0);
            }
            $data = $data->where('is_deleted', 0);
            $count = $data->count('id');
            // $token = Session::get('_token');
            // $export = $data->orderby('id', 'DESC')->get()->toArray();
            // Cache::put('tds_payable_listing_admin_'.$token,$export);
            // Cache::put('tds_payable_listing_count_admin_'.$token,$count);
            $totalAmount = 0;
            $totalAmountData = $data->limit($_POST['start'])->orderby('id', 'DESC')->get();
            foreach ($totalAmountData as $item) {
                $totalAmount = ($totalAmount + (float) $item->amount) - (float) getTdsDrAmount($item->daybook_ref_id, $item->head_id,$item->company->id);
            }
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderby('id', 'DESC')->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $totalAmount = ($totalAmount + (float) $row->amount) - ((float) getTdsDrAmount($row->daybook_ref_id, $row->head_id,$row->company->id));
                $sno++;
                $val = [
                    'DT_RowIndex' => $sno,
                    'created_date' => date("d/m/Y", strtotime(convertDate($row->created_at))),
                    'company' => $row->company ? $row->company->short_name : 'N/A',
                    'branch' => $row->branch ? $row->branch->name : 'N/A',
                    'tds_head' => $row['AccountHeads'] ? $row['AccountHeads']->sub_head : 'N/A',
                    'vendor_name' => ($row->head_id == 62 || $row->head_id == 63) ? ($row['member'] ? $row['member']->first_name . ' ' . $row['member']->last_name : 'N/A') : 'N/A',
                    'pan_number' => (isset($row['member']['memberIdProof']) && $row['member']['memberIdProof']->first_id_type_id == 5) ? $row['member']['memberIdProof']->first_id_no : ((isset($row['member']['memberIdProof']) && $row['member']['memberIdProof']->second_id_type_id == 5) ? $row['member']['memberIdProof']->second_id_no : 'N/A'),
                    'dr_entry' => number_format(getTdsDrAmount($row->daybook_ref_id, $row->head_id,$row->company->id), 2),
                    'cr_entry' => number_format($row->amount, 2),
                    'balance' => number_format((float) $totalAmount, 2, '.', '') . " &#8377;",
                ];
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function print_tds_payable(Request $request)
    {
        $data = AllHeadTransaction::where('payment_type', 'CR')->where('is_deleted', 0);
        if (isset($request->is_search) && $request->is_search == 'yes') {
            if (isset($request->head_id) && $request->head_id != '') {
                $headId = $request->head_id;
                $data = $data->where('head_id', $headId);
            }
            if (isset($request->branch_id) && $request->branch_id != '') {
                $id = $request->branch_id;
                $data = $data->where('branch_id', $id);
            }
            if ($request->start_date != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request->start_date)));
                if ($request->end_date != '') {
                    $endDate = date("Y-m-d", strtotime(convertDate($request->end_date)));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        } else {
            $data = $data->where('id', 0);
        }
        $data = $data->orderby('id', 'DESC')->get();
        return view('templates.admin.tds_payable.tds_payable_print', compact('data'));
    }
    public function add_tds_payable(Request $request)
    {
        $data['title'] = 'TDS Transfer Payable';
        $data['branch'] = Branch::where('status', 1)->get();
        // $data['tdsHeads'] = AccountHeads::where('parent_id', 22)->pluck('sub_head', 'head_id');;
        $data['tdsHeads'] = AccountHeads::whereIn('parent_id', [22,322,330])->pluck('sub_head', 'head_id');
        $data['SamraddhBanks'] = SamraddhBank::has('company')->where('status', 1)->get();
        $data['SamraddhBankAccounts'] = SamraddhBankAccount::has('getCompanyDetail')->where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
        $data['view'] = 0;
        return view('templates.admin.tds_payable.add_tds_payable', $data);
    }
    public function getTdsPayableAmount(Request $request)
    {
        $startDate = date("Y-m-d", strtotime(convertDate($request->startDate)));
        $start = date("Y-m-d H:i:s", strtotime(convertDate($request->startDate)));
        $endDate = date("Y-m-d", strtotime(convertDate($request->endDate)));
        $end = date("Y-m-d H:i:s", strtotime(convertDate($request->endDate)));
        $headId = $request->headId;
        $companyId = $request->companyId;
        
        $checkStartDate = TdsPayable::where('from_date', '<=', $startDate)->where('to_date', '>=', $startDate)->where('company_id', $companyId)->where('tds_head_id', $headId)->where('is_deleted',0)->count();
        
        $checkEndDate = TdsPayable::where('from_date', '<=', $endDate)->where('to_date', '>=', $endDate)->where('company_id', $companyId)->where('tds_head_id', $headId)->where('is_deleted',0)->count();
        
        $checkTDSStartDate = TdsTransfer::where('deleted_at',NULL)->where(function ($query) use ($start,$headId,$companyId) {
            $query->whereDate('start_date', '<=', $start)->whereDate('end_date', '>=', $start)->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at',NULL);
        })->orWhereBetween(\DB::raw('DATE(start_date)'), [$start, $end])->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at',NULL)->count();
        
        $checkTDSEndDate = TdsTransfer::where('deleted_at',NULL)->where(function ($query) use ($end,$headId,$companyId) {
            $query->whereDate('start_date', '>=', $end)->whereDate('end_date', '<=', $end)->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at',NULL);
        })->orWhereBetween(\DB::raw('DATE(end_date)'), [$start, $end])->where('head_id', $headId)->where('company_id', $companyId)->where('deleted_at',NULL)->count();
        
        $sumAmount = AllHeadTransaction::where('head_id', $headId)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->whereNotIn('sub_type',[92,93])->where('payment_type', 'CR')->where('company_id', $companyId)->where('is_deleted', 0)
        // ->dd()
        ->sum('amount');
        
        $tdstransferrequest = TdsTransfer::where('deleted_at',NULL)->where('head_id', $headId)->where('company_id', $companyId)->where('start_date', '<=', $endDate)->count();
        
        $data = number_format((float) $sumAmount, 2, '.', '');
        
        $return_array = compact('data', 'checkStartDate', 'checkEndDate', 'checkTDSEndDate', 'checkTDSStartDate');
        return json_encode($return_array);
    }
    public function payTdsPayableAmount(Request $request)
    {
        $rules = [
            'payable_start_date' => 'required',
            'payable_end_date' => 'required',
            'payable_head_id' => 'required',
            'payable_tds_amount' => 'required',
            'payable_payment_date' => 'required',
            'payable_paid_amount' => 'required',
            'bank_id' => 'required',
            'account_id' => 'required',
            // 'upload_challan' => 'required',
            'remark' => 'required',
            'daybook_diff' => 'required',
            'id' => 'required',
        ];
        $messages = [
            'required' => 'The :attribute field is required.'
        ];
        $validate = $this->validate($request, $rules, $messages);
        $companyId = $request->company_id;
        $late_penalty = $request->payable_late_penalty;
        $heads = AccountHeads::pluck('sub_head','head_id');
        DB::beginTransaction();
        try {
            if ($request->has('upload_challan') ) {
                try {
                    $mainFolder = 'tds-payable/challan';
                    $file = $request->file('upload_challan');
                    $uploadFile = $file->getClientOriginalName();
                    $fname = time() . '.' . $file->getClientOriginalExtension();
                    $files = ImageUpload::upload($file,$mainFolder,$fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $file_id = Files::insertGetId($fData);
                } catch (\Exception $e) {
                    $file_id = $e->getMessage();
                }
            } else {
                $file_id = NULL;
            }
            $t = date("H:i:s");
            $entry_time = date("H:i:s");
            $fromDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_start_date)));
            $toDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date)));
            // $AddtoDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date . ' +1 day')));
            $paymentDate = date("Y-m-d " . $t . "", strtotime(str_replace('/', '-', $request->payable_payment_date)));
            Session::put('created_at', $paymentDate);
            Session::put('created_atUpdate', $paymentDate);
            $referenceId = $request->daybook_diff;
            $tdsTrasfer = $request->id;
            $created_by = 1;
            $late_penalty = $request->payable_late_penalty;
            $tdsPayableData = [
                'tds_head_id' => $request->payable_head_id,
                'tds_amount' => $request->payable_tds_amount,
                'final_tds_amount'=>$request->payable_paid_amount,
                'late_penalty'=>$late_penalty,
                'neft_charge' => $request->neft_charge,
                'paid_amount' => $request->total_paid_amount,
                'from_date' => $fromDate,
                'daybook_ref_id' => $referenceId,
                'to_date' => $toDate,
                'payment_date' => $paymentDate,
                'bank_id' => $request->bank_id,
                'account_id' => $request->account_id,
                'transaction_number' => $request->transaction_number,
                'challan_id' => $file_id,
                'remark' => $request->remark,
                'created_at' => $request->created_at,
                'company_id' => $companyId,
                'is_deleted' => 0,
            ];
            $tdsPayable = TdsPayable::create($tdsPayableData);
            $nft = $request->neft_charge ?? 0;
            $created_by_id = Auth::user()->id;
            $insertpayable = [];
            // bank payable entry
            if($request->total_paid_amount > 0){
                $insertpayable = [
                    'daybook_ref_id' => $referenceId,
                    'branch_id' => 29,
                    'bank_id' => $request->bank_id,
                    'bank_ac_id' => $request->account_id,
                    'head_id' => $request->payable_head_id, // 22, // TDS PAYABLE head 
                    'type' => 9,
                    'sub_type' => 93, // bank payable on TDS payable head
                    'type_id' => $tdsPayable->id,
                    'type_transaction_id' => $tdsTrasfer,
                    'associate_id' => NULL,
                    'member_id' => NULL,
                    'branch_id_to' => NULL,
                    'branch_id_from' => NULL,
                    'amount' => ($request->total_paid_amount - $late_penalty),
                    'description' => 'TDS Payable '  . ucfirst($heads[$request->payable_head_id]) . ' Bank Payable A/C ' . ($request->total_paid_amount - $late_penalty). '',
                    'payment_type' => 'DR',
                    'payment_mode' => 2,
                    'currency_code' => 'INR',
                    'jv_unique_id' => NULL,
                    'v_no' => NULL,
                    'ssb_account_id_from' => NULL,
                    'ssb_account_id_to' => NULL,
                    'ssb_account_tran_id_to' => NULL,
                    'ssb_account_tran_id_from' => NULL,
                    'cheque_type' => NULL,
                    'cheque_id' => NULL,
                    'cheque_no' => NULL,
                    'transction_no' => $request->transaction_number,
                    'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                    'entry_time' => $t,
                    'created_by' => $created_by,
                    'created_by_id' => $created_by_id,
                    'company_id' => $companyId,
                    'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                    'is_app' => 0
                ];
            }
            $insertlate = [];
            // LATE PENALTY entry
            if($late_penalty > 0){
                $insertlate = [
                    'daybook_ref_id' => $referenceId,
                    'branch_id' => 29,
                    'bank_id' => $request->bank_id,
                    'bank_ac_id' => $request->account_id,
                    'head_id' => 33,  // LATE PENALTY head 
                    'type' => 9,
                    'sub_type' => 93, //  bank payable
                    'type_id' => $tdsPayable->id,
                    'type_transaction_id' => $tdsTrasfer,
                    'associate_id' => NULL,
                    'member_id' => NULL,
                    'branch_id_to' => NULL,
                    'branch_id_from' => NULL,
                    'amount' => $late_penalty,
                    'description' => 'TDS Payable '  . ucfirst($heads[$request->payable_head_id]) . ' Late Panalty A/c Dr ' . $late_penalty . '',
                    'payment_type' => 'DR',
                    'payment_mode' => 2,
                    'currency_code' => 'INR',
                    'jv_unique_id' => NULL,
                    'v_no' => NULL,
                    'ssb_account_id_from' => NULL,
                    'ssb_account_id_to' => NULL,
                    'ssb_account_tran_id_to' => NULL,
                    'ssb_account_tran_id_from' => NULL,
                    'cheque_type' => NULL,
                    'cheque_id' => NULL,
                    'cheque_no' => NULL,
                    'transction_no' => $request->transaction_number,
                    'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                    'entry_time' => $t,
                    'created_by' => $created_by,
                    'created_by_id' => $created_by_id,
                    'company_id' => $companyId,
                    'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                    'is_app' => 0,
                    'is_query' => 0,
                    'is_cron' => 0,
                ];
            }
            // total amount late penalty + tds_amount + nft if any
            $totalamount = $request->total_paid_amount + ($nft);
            //credit ampount on bank crarges entry 
            $insertbank = [
                'daybook_ref_id' => $referenceId,
                'branch_id' => 29,
                'bank_id' => $request->bank_id,
                'bank_ac_id' => $request->account_id,
                'head_id' => getSamraddhBank($request->bank_id)->account_head_id,// Bnak head id,
                'type' => 9,
                'sub_type' => 93,//  bank payable
                'type_id' => $tdsPayable->id,
                'type_transaction_id' => $tdsTrasfer,
                'associate_id' => NULL,
                'member_id' => NULL,
                'branch_id_to' => NULL,
                'branch_id_from' => NULL,
                'amount' => $totalamount,
                'description' => 'TDS Payable '  . ucfirst($heads[$request->payable_head_id]) . ' Bank Transaction A/c Cr ' . $totalamount . '',
                'payment_type' => 'CR',
                'payment_mode' => 2,
                'currency_code' => 'INR',
                'jv_unique_id' => NULL,
                'v_no' => NULL,
                'ssb_account_id_from' => NULL,
                'ssb_account_id_to' => NULL,
                'ssb_account_tran_id_to' => NULL,
                'ssb_account_tran_id_from' => NULL,
                'cheque_type' => NULL,
                'cheque_id' => NULL,
                'cheque_no' => NULL,
                'transction_no' => $request->transaction_number,
                'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                'entry_time' => $t,
                'created_by' => $created_by,
                'created_by_id' => $created_by_id,
                'company_id' => $companyId,
                'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                'is_app' => 0,
                'is_query' => 0,
                'is_cron' => 0,
            ];
            //nft charges if any
            $insertnft = [];
            if ($request->neft_charge > 0) {
                $insertnft = [
                    'daybook_ref_id' => $referenceId,
                    'branch_id' => 29,
                    'bank_id' => $request->bank_id,
                    'bank_ac_id' => $request->account_id,
                    'head_id' => 92, // Bank Charge head // Bnak head id,
                    'type' => 9,
                    'sub_type' => 93, //  bank payable
                    'type_id' => $tdsPayable->id,
                    'type_transaction_id' => $tdsTrasfer,
                    'associate_id' => NULL,
                    'member_id' => NULL,
                    'branch_id_to' => NULL,
                    'branch_id_from' => NULL,
                    'amount' => $nft,
                    'description' => 'TDS Payable '  . 'NEFT Charge on '.ucfirst($heads[$request->payable_head_id]) . ' A/c Dr ' . $nft,
                    'payment_type' => 'DR',
                    'payment_mode' => 2,
                    'currency_code' => 'INR',
                    'jv_unique_id' => NULL,
                    'v_no' => NULL,
                    'ssb_account_id_from' => NULL,
                    'ssb_account_id_to' => NULL,
                    'ssb_account_tran_id_to' => NULL,
                    'ssb_account_tran_id_from' => NULL,
                    'cheque_type' => NULL,
                    'cheque_id' => NULL,
                    'cheque_no' => NULL,
                    'transction_no' => $request->transaction_number,
                    'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                    'entry_time' => $t,
                    'created_by' => $created_by,
                    'created_by_id' => $created_by_id,
                    'company_id' => $companyId,
                    'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                    'is_app' => 0,
                    'is_query' => 0,
                    'is_cron' => 0,
                ];
            }
            // one entry for all head transaction table
            // $allHeadTransaction_TDS_DR = AllHeadTransaction::insert($insert);
            if(!empty($insertpayable)){    
            $allHeadTransaction_TDS_DR_insertpayable = AllHeadTransaction::insert($insertpayable);
            }
            if(!empty($insertbank)){
            $allHeadTransaction_TDS_DR_insertbank = AllHeadTransaction::insert($insertbank);
            }
            if(!empty($insertlate)){    
                $allHeadTransaction_TDS_DR_insertlate = AllHeadTransaction::insert($insertlate);
            }
            if(!empty($insertnft)){ 
                $allHeadTransaction_TDS_DR_insertnft = AllHeadTransaction::insert($insertnft);
            }   
            
            // one entry for SamraddhBankDaybook table
            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($referenceId, $request->bank_id, $request->account_id, 9, 93, $tdsPayable->id, $tdsTrasfer, NULL, NULL, 29, $totalamount, $totalamount, $totalamount, 'TDS Payable Amount ' . $totalamount . '', 'TDS Payable Dr ' . $totalamount . '', 'TDS Payable Cr ' . $totalamount . '', 'DR', 2, 'INR', NULL, NULL, $request->bank_id, getSamraddhBank($request->bank_id)->bank_name, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $request->transaction_number, $request->bank_id, $request->account_id, getSamraddhBankAccountId($request->account_id)->ifsc_code, NULL, $request->bank_id, $request->account_id, NULL, NULL, NULL, NULL, $paymentDate, $paymentDate, $entry_time, 1, Auth::user()->id, $request->created_at, NULL, NULL, NULL, NULL, NULL, $companyId);
            
            // one entry for branch_dayBook table
            $createBranchDayBookModify = CommanController::createBranchDayBookModify($referenceId, 29, 9, 93, $tdsPayable->id, $tdsTrasfer, NULL, NULL, null, null, $totalamount, 'TDS Payable Amount ' . $totalamount . '', 'TDS Payable Dr ' . $totalamount . '', 'TDS Payable Cr ' . $totalamount . '', 'DR', 2, 'INR', NULL, NULL, NULL, NULL, $request->created_at, $entry_time, 1, $created_by_id, $paymentDate, $companyId);
            
            // tds transfer update
            $transfer = TdsTransfer::where('id', $request->id)->update(['is_paid' => 1, 'payment_ref_id' => $referenceId]);
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.add-tds-payable')->with('success', 'Payment Completed Successfully!');
    }
    public function payTdsTransferAmount(Request $request)
    {
        $rules = [
            'payable_start_date' => 'required',
            'payable_end_date' => 'required',
            'payable_head_id' => 'required',
            'payable_tds_amount' => 'required|numeric|gt:0',
            // Add 'gt:0' to check if the amount is greater than zero
            'company_id' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.',
            'numeric' => 'The :attribute field must be a number.',
            'gt' => 'The :attribute field must be greater than zero.',
        ];
        $companyId = $request->company_id;
        $this->validate($request, $rules, $customMessages);
        $created_by_id = Auth::user()->id;
        $heads = AccountHeads::pluck('sub_head','head_id');
        DB::beginTransaction();
        try {
            $t = date("H:i:s");
            $fromDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_start_date)));
            $toDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date)));
            $payable_start_date = date("Y-m-d " . $t . "", strtotime(str_replace('/', '-', $request->payable_start_date)));
            $payable_end_date = date("Y-m-d " . $t . "", strtotime(str_replace('/', '-', $request->payable_end_date)));
            // $AddtoDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date . ' +1 day')));
            Session::put('created_at',$toDate);
            Session::put('created_atUpdate',$toDate);
            $referenceId = CommanController::createBranchDayBookReference($request->payable_tds_amount);
            $data = AllHeadTransaction::where('head_id', $request->payable_head_id)
                ->whereBetween(\DB::raw('DATE(created_at)'), [$fromDate, $toDate])
                ->where('is_deleted', 0)
                ->where('company_id', $companyId)
                // ->whereNotIn('type',[9])
                ->whereNotIn('sub_type',[92,93])
            ;
            $transacion = clone $data;
            // $transacion2 = clone $data;
            $transacions = $transacion->where('payment_type', 'CR')->get();
            $sumAmountBranch = $data->where('payment_type', 'CR')->get()->groupBy('branch_id')->map(function ($g) {
                return $g->sum('amount');
            })->toArray();
            
            $old_daybook_id = [];
            $sumAmount = 0;
            $insert = [];
            $insert2 = [];
            foreach ($transacions as $value) {
                if($value->amount != 0){
                    $insert[] = [
                        'daybook_ref_id' => $referenceId,
                        'branch_id' => $value->branch_id,
                        'bank_id' => NULL,
                        'bank_ac_id' => NULL,
                        'head_id' => $value->head_id,
                        'type' => 9, // $value->type,
                        'sub_type' => 92, // $value->sub_type,
                        'type_id' => $value->type_id,
                        'type_transaction_id' => $value->type_transaction_id,
                        'associate_id' => $value->associate_id,
                        'member_id' => $value->member_id,
                        'branch_id_to' => NULL,
                        'branch_id_from' => NULL,
                        'amount' => $value->amount,
                        'description' => 'TDS Transfer '  . ucfirst($heads[$value->head_id]).' Amount ' . $value->amount . '',
                        'payment_type' => 'DR',
                        'payment_mode' => 3,
                        'currency_code' => 'INR',
                        'jv_unique_id' => NULL,
                        'v_no' => NULL,
                        'ssb_account_id_from' => NULL,
                        'ssb_account_id_to' => NULL,
                        'ssb_account_tran_id_to' => NULL,
                        'ssb_account_tran_id_from' => NULL,
                        'cheque_type' => NULL,
                        'cheque_id' => NULL,
                        'cheque_no' => NULL,
                        'transction_no' => NULL,
                        'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
                        'entry_time' => $t,
                        'created_by' => 1,
                        'created_by_id' => $created_by_id,
                        'company_id' => $companyId,
                        'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
                        'is_app' => 0,
                        'is_query' => 0,
                        'is_cron' => 0,
                    ];
                }
                // this enrty was commenter by sourab as per current update   
                // $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($referenceId,$request->bank_id,$request->account_id,$value->type,$value->sub_type,$value->type_id,$value->type_transaction_id,$value->associate_id,$value->member_id,$value->branch_id,$value->amount,$value->amount,$value->amount,'TDS Payable amount '.$value->amount.'','TDS Payable Dr '.$value->amount.'','TDS Payable Cr '.$value->amount.'','DR',2,$value->currency_code,NULL,NULL,$request->bank_id,getSamraddhBank($request->bank_id)->bank_name,$v_no=NULL,$v_date=NULL,$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$request->transaction_number,$request->bank_id,$request->account_id,getSamraddhBankAccountId($request->account_id)->ifsc_code,$transction_bank_branch_from=NULL,$request->bank_id,$request->account_id,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$paymentDate,$paymentDate,$entry_time,1,Auth::user()->id,$paymentDate,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$companyId);                
                $sumAmount += $value->amount;
                array_push($old_daybook_id, $value->daybook_ref_id);
            }
            $allHeadTransaction_Branch = AllHeadTransaction::insert($insert);
            $tdsTransferData = [
                'daybook_ref_id' => $referenceId,
                'head_id' => $request->payable_head_id,
                'transfer_date' => $toDate,
                'is_paid' => 0,
                'start_date' => $payable_start_date,
                'end_date' => $payable_end_date,
                'tds_amt' => number_format((float) $sumAmount, 2, '.', ''),
                'deleted_at' => NULL,
                'old_daybook_id' => json_encode($old_daybook_id),
                'transfer_daybook_ref_id' => $referenceId,
                'payment_ref_id' => NULL,
                'company_id' => $companyId,
            ];
            $TdsTransfer = TdsTransfer::create($tdsTransferData);
            $lth_headId = 408; // head_id for LIABILITY TRANSFER
            foreach ($sumAmountBranch as $key => $value) {
                // LTH CR In loop  as per branch total amount  
                if($value > 0){       
                    $insert2[] = [
                        'daybook_ref_id' => $referenceId,
                        'branch_id' => $key, // branch ID
                        'bank_id' => NULL,
                        'bank_ac_id' => NULL,
                        'head_id' => $lth_headId,
                        'type' => 9, // TDS Type
                        'sub_type' => 92,  // tds transafer head id
                        'type_id' => $TdsTransfer->id, // tds transafer table auto ID
                        'type_transaction_id' => $TdsTransfer->id,  // tds transafer table auto ID
                        'associate_id' => NULL,
                        'member_id' => NULL,
                        'branch_id_to' => NULL,
                        'branch_id_from' => NULL,    
                        'amount' => $value,                   
                        'description' => 'TDS Transfer '  . ucfirst($heads[$lth_headId]) .' Amount ' . $value . '',
                        'payment_type' => 'CR',
                        'payment_mode' => 3,
                        'currency_code' => 'INR',
                        'jv_unique_id' => NULL,
                        'v_no' => NULL,
                        'ssb_account_id_from' => NULL,
                        'ssb_account_id_to' => NULL,
                        'ssb_account_tran_id_to' => NULL,
                        'ssb_account_tran_id_from' => NULL,
                        'cheque_type' => NULL,
                        'cheque_id' => NULL,
                        'cheque_no' => NULL,
                        'transction_no' => NULL,
                        'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
                        'entry_time' => $t,
                        'created_by' => 1,
                        'created_by_id' => $created_by_id,
                        'company_id' => $companyId,
                        'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
                        'is_app' => 0,
                        'is_query' => 0,
                        'is_cron' => 0,
                    ];
                }
            }            
            $allHeadTransaction_LTH = AllHeadTransaction::insert($insert2);

            //LTH CR
            $allHeadTransactionLTH_DR = CommanController::createAllHeadTransaction($referenceId, 29, NULL, NULL, $request->payable_head_id, 9, 92, $TdsTransfer->id, $TdsTransfer->id, NULL, NULL, NULL, NULL, $sumAmount, ucfirst($heads[$request->payable_head_id]) .' Transfer amount ' . $sumAmount . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, $created_by_id, $companyId);
            
            //TDS DR
            $allHeadTransaction_LTH_CR = CommanController::createAllHeadTransaction($referenceId, 29, NULL, NULL, $lth_headId, 9, 92, $TdsTransfer->id, $TdsTransfer->id, NULL, NULL, NULL, NULL, $sumAmount, ucfirst($heads[$lth_headId]) . ' Transfer amount ' . $sumAmount . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, $created_by_id, $companyId);
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
            // return back()->with('alert', $ex->getLine());
        }
        return back()->with('success', ucfirst($heads[$request->payable_head_id]) . ' Transfer Request Created Successfully!');
    }
    public function tds_transfer_listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = TdsTransfer::has('company')->whereNull('deleted_at')
                ->with([
                    'company:id,name,short_name',
                    'AllHeadTransaction:id,head_id,daybook_ref_id,branch_id,amount,description,payment_type,payment_mode,entry_date,entry_time,is_deleted,company_id,created_by_id',
                    'AllHeadTransaction.AccountHeads:id:head_id,sub_head',
                    'payable',
                    'payable.challan'
                ]);
            $headName = AccountHeads::pluck('sub_head', 'head_id');
            $count = $data->count('id');
            
            if(!empty($arrFormData)){               
                $data->where(
                    function ($query) use ($arrFormData) {
                        if($arrFormData['transfer_date'] != NULL){
                            $transfer_date = date('Y-m-d H:i:s',strtotime(convertDate($arrFormData['transfer_date'])));
                            $query->where('transfer_date', '=',$transfer_date);
                        }
                        if($arrFormData['tds_head'] != NULL){
                            $query->whereHas('AllHeadTransaction.AccountHeads', function ($account_head) use ($arrFormData) {
                                $account_head->where('head_id', 'LIKE', '%' . $arrFormData['tds_head'] . '%');
                            });
                        }
                    }
                );
            }
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $count; //AllTransaction::where('payment_type','CR')->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $btn = '';
                $urlImage = $row->payable ? ImageUpload::generatePreSignedUrl('tds-payable/challan/'.($row->payable->challan->file_name)) : '';
                $pay = '<a title="Pay" class="text-dark pay_data btn btn-white w-100 legitRipple text-left dropdown-item" target="_blank" href="' . route('admin.tds_transfer_pay', [$row->company->id, $row->id]) . '" ><i class="fa fa-credit-card  mr-2"></i>Pay</a>';
                $view = '<a title="View Details" class="text-dark view_details btn btn-white w-100 legitRipple text-left dropdown-item" href="' . route('admin.tds_transfer_pay.view', [$row->company_id, $row->id]) . '" target="_blank" ><i class="fa fa-eye  mr-2"></i>View File</a>';
                $viewfile = $row->payable ? '<a href="'. $urlImage . '" title="Vew File" target="_blank" class="">' . ($row->payable->challan->file_name) . '</a>' : 'N/A';
                $download = '<div class="download_data btn btn-white w-100 legitRipple text-left dropdown-item" data-name="' . (string) ($row->payable ? $row->payable->challan->file_name : '') . '" data-path="' . ($row->payable ? $row->payable->challan->file_path . '/' . $row->payable->challan->file_name : '') . '"><i class="fa fa-download  mr-2"></i>Download File</div>';
                $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $btn .= empty($row->payable) ? $pay : '';
                $btn .= !empty($row->payable) ? $view : '';
                // $btn .= !empty($row->payable) ? $view . $download : '';
                $btn .= '</div></div></div>';
                $val = [
                    'DT_RowIndex' => $sno,
                    'transfer_date' => date('d/m/Y', strtotime($row->transfer_date)),
                    'date_range' => date("d/m/Y", strtotime(convertDate($row->start_date))) . ' - ' . date("d/m/Y", strtotime(convertDate($row->end_date))),
                    'head_name' => $headName[$row->head_id],
                    'head_amount' => '&#8377 ' . number_format((float) $row->tds_amt, 2, '.', ''),
                    'penalty_amount' => '&#8377 ' . number_format((float) ($row->payable ? ($row->payable->paid_amount) : 0), 2, '.', ''),
                    'payment_date' => $row->payable ? date('d/m/Y', strtotime($row->payable->payment_date)) : 'N/A',
                    'to_paid' => $row->is_paid == 0 ? 'No' : 'Yes',
                    'company' => $row->company->short_name,
                    'file' => $row->payable ? $viewfile : 'N/A',
                    'action' => $btn,
                ];
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function tds_transfer_pay(Request $request, $companyId, $id)
    {
        $data['title'] = 'TDS Payable';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['tdsHeads'] = AccountHeads::where('parent_id', 22)->pluck('sub_head', 'head_id');
        ;
        $data['SamraddhBanks'] = SamraddhBank::where('status', 1)->whereCompanyId($companyId)->pluck('bank_name', 'id');
        $data['companyId'] = $companyId;
        $details = TdsTransfer::whereId($id)->with('payable')->whereCompanyId($companyId)->first();
        if (!($details)) {
            return back();
        }
        $data['SamraddhBankAccounts'] = SamraddhBankAccount::where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
        $data['startDate'] = $details->start_date;
        $data['endDate'] = $details->end_date;
        $data['head_id'] = $details->head_id;
        $data['daybook_diff'] = $details->daybook_ref_id;
        $data['id'] = $id;
        $data['view'] = 0;
        return view('templates.admin.tds_payable.add_tds_payable_new', $data);
    }
    public function tds_transfer_view(Request $request, $companyId, $id)
    {
        $data['title'] = 'TDS Payable Details';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['tdsHeads'] = AccountHeads::where('parent_id', 22)->pluck('sub_head', 'head_id');
        
        $data['SamraddhBanks'] = SamraddhBank::where('status', 1)->whereCompanyId($companyId)->pluck('bank_name', 'id');
        $data['companyId'] = $companyId;
        $details = TdsTransfer::whereId($id)->with(['payable', 'payable.challan'])->whereCompanyId($companyId)->first();
        if (!$details) {
            return back();
        }
        $data['tds_amount'] = empty($details)? 0 : ( $details->tds_amt ?? 0 ) ;
        $data['SamraddhBankAccounts'] = SamraddhBankAccount::where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
        $SamraddhBankAccount = SamraddhBankAccount::pluck('account_no', 'id');
        $data['startDate'] = $details->start_date;
        $data['endDate'] = $details->end_date;
        $data['head_id'] = $details->head_id;
        $data['daybook_diff'] = $details->daybook_ref_id;
        $data['id'] = $id;
        $data['view'] = 1;
        if(!$details->payable){  
            return back();
        }
        $data['payment_date'] = date('d/m/Y', strtotime($details->payable->payment_date));
        $data['late_penalty'] = $details->payable ? ($details->payable->late_penalty) : 0 ;
        $data['total_paid'] = number_format((float) $details->payable->paid_amount, 2, '.', '');
        $data['bank_id'] = $details->payable->bank_id;
        $data['remark'] = $details->payable->remark;
        $data['neft_charge'] = number_format((float) $details->payable->neft_charge, 2, '.', '');
        $data['transaction_number'] = $details->payable->transaction_number;
        $data['account_no'] = $SamraddhBankAccount[$details->payable->bank_id];
        $data['bank_available_balance'] = checkBankBalance((object) [
            'account_id' => $details->payable->account_id,
            'bank_id' => $details->payable->bank_id,
            'company_id' => $details->payable->company_id,
            'entry_date' => $details->payable->created_at,
        ]);
        $data['ChalanFile'] = $details->payable->challan->file_name;
        $data['ChalanSrc'] = ImageUpload::generatePreSignedUrl('tds-payable/challan/'.$details->payable->challan->file_name);
        return view('templates.admin.tds_payable.add_tds_payable_new', $data);
    }
    public function chalandownload(Request $request)
    {
        $fileName = $request->input('name');
        $file = $request->input('path');
        return response()->download($file, $fileName);
    }
    public function displayImage(Request $request)
    {
        $imageName = $request->input('image');
        $imagePath = $request->input('path');
        if (Str::contains($imagePath, $imageName)) {
            return response()->file($imagePath);
        } else {
            return back();
        }
    }
    public function export_tds_transafer(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $_fileName = Session::get('_fileName');
            $returnURL = URL::to('/') . "/asset/tds_transfer" . $_fileName . ".csv";
            $fileName = env('APP_EXPORTURL') . "asset/tds_transfer" . $_fileName . ".csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = TdsTransfer::has('company')->whereNull('deleted_at')
            ->with([
                'company:id,name,short_name',
                'AllHeadTransaction:id,head_id,daybook_ref_id,branch_id,amount,description,payment_type,payment_mode,entry_date,entry_time,is_deleted,company_id,created_by_id',
                'AllHeadTransaction.AccountHeads:id:head_id,sub_head',
                'payable',
                'payable.challan'
            ]);

        if(!empty($input)){               
            $data->where(
                function ($query) use ($input) {
                    if($input['transfer_date'] != NULL){
                        $transfer_date = date('Y-m-d H:i:s',strtotime(convertDate($input['transfer_date'])));
                        $query->where('transfer_date', '=',$transfer_date);
                    }
                    if($input['tds_head'] != NULL){
                        $query->whereHas('AllHeadTransaction.AccountHeads', function ($q) use ($input) {
                            $q->where('head_id', 'LIKE', '%' . $input['tds_head'] . '%');
                        });
                    }
                }
            );
        }
        // pd($data->toArray());
        $headName = AccountHeads::pluck('sub_head', 'head_id');
        $totalResults = $data->orderby('id', 'DESC')->count();
        $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get();
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        // if its a fist run truncate the file. else append the file
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
        foreach ($results as $row) {
            $sno++;
            $val = [
                'S/No' => $sno,
                'TRANSFER DATE' => date('d/m/Y', strtotime($row->transfer_date)),
                'DATE RANGE' => date("d/m/Y", strtotime(convertDate($row->start_date))) . ' - ' . date("d/m/Y", strtotime(convertDate($row->end_date))),
                'HEAD NAME' => $headName[$row->head_id],
                'HEAD AMOUNT' => number_format((float) $row->tds_amt, 2, '.', ''),
                'PANALTY AMOUNT' => number_format((float) ($row->payable ? ($row->payable->paid_amount) : 0), 2, '.', ''),
                'PAYMENT DATE' => $row->payable ? date('d/m/Y', strtotime($row->payable->payment_date)) : 'N/A',
                'IS PAID' => $row->is_paid == 0 ? 'No' : 'Yes',
                'CHALLAN SLIP' => $row->payable ? $row->payable->challan->file_name : 'N/A',
                'COMPANY' => $row->company->short_name,
            ];
            if (!$headerDisplayed) {
                // Use the keys from $data as the titles
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            // Put the data into the stream
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result' => $result,
            'start' => $start,
            'limit' => $limit,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }
    public function export_tds_payable(Request $request)
    {
        if ($request['tds_payable_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $balance = 0;
            $_fileName = Session::get('_fileName');
            $returnURL = URL::to('/') . "/asset/tds_payable_list" . $_fileName . ".csv";
            $fileName = env('APP_EXPORTURL') . "asset/tds_payable_list" . $_fileName . ".csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $token = Session::get('_token');
        $data = AllHeadTransaction::has('company')->select('id', 'created_at', 'head_id', 'member_id', 'branch_id', 'daybook_ref_id', 'amount', 'payment_type', 'company_id')
            ->with([
                'member:id,member_id,first_name,last_name',
                'member.memberIdProof:id,first_id_no,second_id_no,member_id,first_id_type_id,second_id_type_id',
                'AccountHeads:id,head_id,sub_head,cr_nature',
                'branch:id,name',
                'company:id,name,short_name'
            ])
            ->where('payment_type', 'CR')
            ->where('is_deleted', 0);
        if (isset($input['is_search']) && $input['is_search'] == 'yes') {
            if (isset($input['head_id']) && $input['head_id'] != '') {
                $headId = $input['head_id'];
                $data = $data->where('head_id', $headId);
            }
            if (isset($input['branch_id']) && $input['branch_id'] > 0 ) {
                $id = $input['branch_id'];
                $data = $data->where('branch_id', $id);
            }
            if (isset($input['company_id']) && $input['company_id'] > 0 ) {
                $company_id = $input['company_id'];
                $data = $data->where('company_id', $company_id);
            }
            if ($input['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($input['start_date'])));
                if ($input['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($input['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        } else {
            $data = $data->where('id', 0);
        }
        $count = $data->count('id');
        $totalResults = $count;
        $totalAmountData = $data->limit($start)->orderby('id', 'DESC')->get();
        foreach ($totalAmountData as $item) {
            $balance = ($balance + (float)$item->amount) - (float)getTdsDrAmount($item->daybook_ref_id, $item->head_id);
        }
        $results = $data->offset($start)->limit($limit)->orderby('id', 'DESC')->get()->toArray();
        // $results = array_slice($data, $start, $count);
        // $bal = array_slice($data, 0, $start);
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
        // foreach ($bal as $row) {
        //     $balance = ($balance + (float) $row['amount']) - (float) getTdsDrAmount($row['daybook_ref_id'], $row['head_id']);
        // }
        foreach ($results as $row) {
            $sno++;
            $getTdsDrAmount = getTdsDrAmount($row['daybook_ref_id'], $row['head_id']);
            $balance = ($balance + (float) $row['amount']) - (float) $getTdsDrAmount;
            $val = [
                'S/N' => $sno,
                'CREATED DATE' => date("d/m/Y", strtotime(convertDate($row['created_at']))),
                'COMPANY' => $row['company']['name'] ?? 'N/A',
                'BRANCH' => $row['branch']['name'] ?? 'N/A',
                'TDS HEAD' => $row['account_heads'] ? $row['account_heads']['sub_head'] : 'N/A',
                'VENDOR NAME' => ($row['head_id'] == 62 || $row['head_id'] == 63) ? $row['member'] ? $row['member']['first_name'] . ' ' . $row['member']['last_name'] ?? '' : 'N/A' : 'N/A',
                'PAN NUMBER' => isset($row['member']['member_id_proof']) ? ($row['member']['member_id_proof']['first_id_type_id'] == 5 ? $row['member']['member_id_proof']['first_id_no'] : ($row['member']['member_id_proof']['second_id_type_id'] == 5 ? $row['member']['member_id_proof']['second_id_no'] : 'N/A')) : 'N/A',
                'DR' => number_format($getTdsDrAmount, 2),
                'CR' => number_format($row['amount'], 2),
                'BALANCE' => number_format($balance, 2),
            ];
            if (!$headerDisplayed) {
                // Use the keys from $data as the titles
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            // Put the data into the stream
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result' => $result,
            'start' => $start,
            'limit' => $count,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }
}