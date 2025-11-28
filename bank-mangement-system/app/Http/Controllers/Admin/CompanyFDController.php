<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyFDRequest;
use App\Models\CompanyBound;
use App\Http\Controllers\Admin\CommanController;
use Auth;
use DB;
use Session;
use URL;
use App\Models\CompanyBoundTransaction;
use Yajra\DataTables\DataTables;
use App\Services\ImageUpload;


class CompanyFDController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "258") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Samraddh Bank FDR || Company Bond list';
        return view('templates.admin.companyBank.list', $data);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (check_my_permission(Auth::user()->id, "260") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Samraddh Bank FDR || Company FDR';
        return view('templates.admin.companyBank.create', $data);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
        public function store(CompanyFDRequest $request)
        {
        // dd($request->all());
            $convertedDate = date('Y-m-d', strtotime(convertDate($request->company_create_date)));
            
            $validated = $request->validated();
            $head_id =  \App\Models\AccountHeads::orderBy('head_id', 'desc')->first('head_id');
            DB::beginTransaction();
            try {
                // $bankBla = \App\Models\BankBalance::where('bank_id', $request->received_bank_name)->where('account_id', $request->received_bank_account)->whereDate('entry_date', '<=', $convertedDate)->orderby('entry_date', 'desc')->get();
                // $sumTotalAmount = $bankBla->sum('totalAmount');
                
                // if ($sumTotalAmount) {
                //     if ($request->amount > $sumTotalAmount) {
                //         return redirect('admin/create/samraddh/bank')->with('alert', 'Sufficient amount not available in bank account!');
                //     }
                // } else {
                //     return redirect('admin/create/samraddh/bank')->with('alert', 'Sufficient amount not available in bank account!');
                // }
                $parentHeadDetail = \App\Models\AccountHeads::where('head_id', 233)->first(); //this head id static...dynamic when live
                $head_data['sub_head'] = $request->bank_name;
                $head_data['head_id'] = $head_id->head_id + 1;
                $head_data['labels'] = 4;
                $head_data['parent_id'] = 233;
                $head_data['parentId_auto_id'] = $parentHeadDetail->id;
                $head_data['cr_nature'] = $parentHeadDetail->cr_nature;
                $head_data['dr_nature'] = $parentHeadDetail->dr_nature;
                $head_data['is_move'] = 1;
                $head_data['status'] = 0;
                $head_data['child_head'] = [$head_id->head_id + 1];
                $idget = \App\Models\AccountHeads::create($head_data);
                $account_head = \App\Models\AccountHeads::where('id', $idget->id)->first();
                $DayBookRef = CommanController::createBranchDayBookReferenceNew($request->amount, $request->date);
    
                $data = [
                    'daybook_ref_id' => $DayBookRef,
                    'bank_name' => $request->bank_name,
                    'fd_no' => $request->fd_no,
                    'date' => date('Y-m-d', strtotime(convertDate($request->date))),
                    'maturity_date' => date('Y-m-d', strtotime(convertDate($request->maturity_date))),
                    'amount' => $request->amount,
                    'current_balance' => $request->amount,
                    'roi' => $request->roi,
                    'remark' => $request->remark,
                    'rec_bank' => $request->received_bank_name,
                    'rec_bank_account' => $request->received_bank_account,
                    'head_id' => $account_head->head_id,
                    'company_id' => $request->company_id
                ];
                $arrayold = $parentHeadDetail->child_head;
                $newArray = array($head_id->head_id + 1);
                $latestData = array_merge($arrayold, $newArray);
                $parentHeadDetail->update(['child_head' => $latestData]);
            
                $companyBound = CompanyBound::create($data);
                if ($request->hasFile('file_upload')) {
                    $mainFolder =  '/companyBound';
                    // dd($request->all());
                    $file = $request->file_upload;
                    $uploadFile =  $file->getClientOriginalName();
                    $fileName = pathinfo($uploadFile, PATHINFO_FILENAME);
                    $fname = $fileName . '_' . time() . '.' . $file->getClientOriginalExtension();
                    ImageUpload::upload($file,$mainFolder,$fname);

                    // $file->move($mainFolder, $fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $records = CompanyBound::findOrFail($companyBound->id)->update(['file'=>$fname]);
                }
                $encodeDate = json_encode($companyBound);
            
                $v_no = NULL;
                $v_date = NULL;
                $globaldate = $request->created_at;
                $currency_code = 'INR';
                $select_date = $request['date'];
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
                $amount = $request->amount;
                $opening_balance = $request->amount;
                $closing_balance = $request->amount;
                $type = 30;
                $sub_type = 301;
                $bank_ac_id = $request->received_bank_account;
                $type_id = $companyBound->id;
                $type_transaction_id = $type_id;
                $refId = $daybook_ref_id = $DayBookRef;
                $member_id = NULL;
                $bank_id = $request->received_bank_name;
                $bankDtail = getSamraddhBank($request->received_bank_name);
                $bankAcDetail = getSamraddhBankAccountId($request->received_bank_name);
                $amount_from_id = null;
                $amount_from_name = null;
                $amount_to_id = $bank_id;
                $amount_to_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';
                $payment_mode = 2;
                $des =  ' Bank Account Dr  ' . $amount_to_name . ' to FD Cr';
                $description_cr = 'Bank A/c Dr ' . $amount . '/-';
                $description_dr = 'To  FD A/c Cr ' . $amount . ' /-';
                $transction_bank_to_name = $bankDtail->bank_name;
                $transction_bank_to_ac_no = $bankAcDetail->account_no;
                $transction_bank_to_branch = $bankAcDetail->branch_name;
                $transction_bank_to_ifsc = $bankAcDetail->ifsc_code;
                $transction_bank_from = $request->bank_name;
                $transction_bank_ac_from = $request->fd_no;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = $bank_id;
                $transction_bank_ac_to = $bank_ac_id;
                $lastheadId = $bankDtail->account_head_id;
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
                $bid=$request->branch_id;
                $transction_no = NULL;
                $transction_date = $entry_date;
                $branch_id = Auth::user()->id;
                $company_id = $request->company_id;
                $associate_id = NULL;
                $branch_id_to = NULL;
                $branch_id_from = NULL;
                $opening_balance = NULL;
                $closing_balance = NULL;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;
                /// ------------- bank head -------------
                $allTran2 = CommanController::headTransactionCreate($daybook_ref_id,$request->branch_id, $bank_id, $bank_ac_id, $lastheadId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);


                $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($daybook_ref_id, $bank_id,$bank_ac_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id,$request->branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $des, $description_dr, $description_cr, 'dr', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id,$company_id);
            
            
            
                
                //-----------   bank balence  ---------------------------
                /// $bankClosing=CommanController:: checkCreateBankClosing($bank_id,$bank_ac_id,$created_at,$amount,0);
                // if ($current_date == $entry_date) {
                //     $bankClosing = CommanController::checkCreateBankClosing($bank_id, $bank_ac_id, $created_at, $amount, 0);
                // } else {
                //     $bankClosing = CommanController::checkCreateBankClosingCRBackDate($bank_id, $bank_ac_id, $created_at, $amount, 0);
                // }
                $branchDayBook = CommanController::branchDayBookNew($daybook_ref_id, $request->branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date,  $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);


            
                /// --------------------FDR bank -----------------
                $allTranlb = CommanController::headTransactionCreate ($daybook_ref_id,$request->branch_id, $bank_id, $bank_ac_id, $account_head->head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);
                DB::commit();
                return redirect()->route('admin.company.fd.list')->with('success', 'Company Bond Created Successfully!');
            } catch (\Exception $ex) {
                DB::rollback();
                // return back()->with('alert', $ex->getMessage());
                return back()->with('alert', $ex->getMessage());
                // return back()->with('alert', $ex->getMessage());
            }
        }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
        public function destroy(Request $request)
        {
            $getRecord = CompanyBound::findorfail($request->id);
            $deleteSamraddhBank = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->update(['is_deleted' => 1]);
            $deleteAllHeadTransaction = \App\Models\AllHeadTransaction::where('daybook_ref_id', $getRecord->daybook_ref_id)->update(['is_deleted' => 1]);
            $getRecord->update(['is_deleted' => 1]);
            $message = 'Company Bond FD Deleted Successfully!';
            $status = 1;
            return response()->json(['message' => $message, 'status' => $status]);
        }

   

        public function listing_company_bound(Request $request)
        {
          if ($request->ajax()) {
            $searchFormData = [];
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $searchFormData[$frm_data['name']] = $frm_data['value'];
                }
            }

            $dataQuery = CompanyBound::has('companies')->with([
                'fdSamraddhBankAccountId:id,account_no,company_id',
                'fdSamraddhBank:id,bank_name'])
            ->withCount('getCompanyBoundTransaction')
            ->where('is_deleted', 0);

            if (isset($searchFormData['is_search']) && $searchFormData['is_search'] == 'yes') {
                if (isset($searchFormData['fd_no']) && $searchFormData['fd_no'] != '') {
                    $dataQuery = $dataQuery->where('fd_no', 'like', '%' . $searchFormData['fd_no'] . '%');
                }
                if (isset($searchFormData['company_id']) && $searchFormData['company_id'] > 0) {
                    $dataQuery = $dataQuery->where('company_id', $searchFormData['company_id']);
                }

                if ($searchFormData['start_date'] != '') {
                    $startDate = date('Y-m-d', strtotime(convertDate($searchFormData['start_date'])));
                    $dataQuery = $dataQuery->where(\DB::raw('DATE(date)'), '>=', $startDate);
                }

                if ($searchFormData['end_date'] != '') {
                    $endDate = date('Y-m-d', strtotime(convertDate($searchFormData['end_date'])));
                    $dataQuery = $dataQuery->where(\DB::raw('DATE(date)'), '<=', $endDate);
                }
                if (isset($searchFormData['fd_status']) && $searchFormData['fd_status'] != '') {
                    $dataQuery = $dataQuery->where('status', $searchFormData['fd_status']);
                }
            }

            $count = $dataQuery->count('id');

            $data = $dataQuery->orderBy('created_at', 'DESC')
                ->offset($_POST['start'])
                ->limit($_POST['length'])
                ->get();

            // Rest of the code remains the same...


        
                $sno = $_POST['start'];
                $rowReturn = [];
                $totalCount = $count;
        
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['date'] = date("d/m/Y", strtotime($row->date));
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $val['bank_name'] = $row->bank_name;
                    $val['fd_no'] = $row->fd_no;
                    $val['amount'] = $row->amount;
        
                    if (isset($row->file)) {
                        $folderName = 'companyBound/'.$row->file;
                        $url = ImageUpload::generatePreSignedUrl($folderName);

                        //$url = URL::to("/core/storage/images/companyBound/" . $row->file . "");

                        $val['file'] = '<a href="' . $url . '" target="blank">' . $row->file . '</a>';
                    } else {
                        $val['file'] = 'N/A';
                    }
        
                    $val['maturity_date'] = date('d/m/Y', strtotime($row->maturity_date));
                    $val['receive_bank'] = $row['fdSamraddhBank']->bank_name;
                    $val['remark'] = $row->remark;
        
                    $status = 'N/A';
                    if ($row->status == 1) {
                        $status = 'Closed';
                    } elseif ($row->status == 0) {
                        $status = 'Active';
                    }
                    $val['status'] = $status;
        
                    $a = $row['fdSamraddhBankAccountId'];
                    $val['receive_bank_account'] = $a->account_no;
        
                    $countInterest = $row->get_company_bound_transaction_count;
                    $genInterest = URL::to("admin/company_bound/interest/" . $row->id);
                    $closeFd = URL::to("admin/samraddh/fd/close/" . $row->id);
                    $transaction = URL::to("admin/samraddh/interest/transaction/" . $row->id);
        
                    $btn = '';
                    $permissions = [
                        check_my_permission(Auth::user()->id, "261"),
                        check_my_permission(Auth::user()->id, "259"),
                        check_my_permission(Auth::user()->id, "262"),
                        check_my_permission(Auth::user()->id, "263"),
                    ];
        
                    if (in_array("1", $permissions)) {
                        $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        if ($countInterest == 0 && $row->status == 0 && check_my_permission(Auth::user()->id, "261") == "1") {
                            $btn .= '<button class="dropdown-item delete_expense" data-row-id="' . $row->id . '" title="Delete Expense"><i class="icon-box mr-2"></i> Delete</button>';
                        }
                        if ($row->status == 0) {
                            if (check_my_permission(Auth::user()->id, "262") == "1") {
                                $btn .= '<a class="dropdown-item" href="' . $closeFd . '" target="blank"><i class="icon-list mr-2"></i> Close Investment</a>';
                            }
                            if (check_my_permission(Auth::user()->id, "263") == "1") {
                                $btn .= '<a class="dropdown-item" href="' . $genInterest . '" target="blank"><i class="icon-list mr-2"></i> Generate Interest</a>';
                            }
                        }
                        if (check_my_permission(Auth::user()->id, "259") == "1") {
                            $btn .= '<a class="dropdown-item" href="' . $transaction . '" target="blank"><i class="icon-list mr-2"></i> View Transactions</a>';
                        }
                        $btn .= '</div></div></div>';
                    }
        
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
        
                $output = [
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $totalCount,
                    "recordsFiltered" => $count,
                    "data" => $rowReturn,
                ];
        
                return json_encode($output);
            }
        }
        
        
        

        public function generate_interest(CompanyBound $id)
        {
            
            if (check_my_permission(Auth::user()->id, "263") != "1") {
                return redirect()->route('admin.dashboard');
            }
           
            $data['title'] = 'Samraddh Bank FDR || Generate Interest';
            $data['detail'] = $id->with('companies:id,name')->where('id',$id->id)->first();
            $data['banks'] = \App\Models\SamraddhBank::where('company_id',$id->company_id)->get();
            $data['tds_heads'] = \App\Models\AccountHeads::select('head_id', 'sub_head')->where('parent_id', 236)->get();

            $data['branch'] = \App\Models\Branch::where('id', 29)->first();

            return view('templates.admin.companyBank.generate_interest', $data);
        }

        public function saveInterest(Request $request)
        {
            try {
                
                $DayBookRef = CommanController::createBranchDayBookReference($request->interest_amount, $request->created_at);
                $companyBound = CompanyBound::where('id', $request->bond_id)->where('is_deleted', 0)->first();
                $data = [
                    'daybook_ref_id' => $DayBookRef,
                    'bound_id' => $companyBound->id,
                    'date' => date('Y-m-d', strtotime(convertDate($request->date))),
                    'tds_amount' => $request->tds_amount,
                    'interest_amount' => $request->interest_amount,
                    'remark' => $request->remark,
                    'rec_bank' => $request->received_bank_name,
                    'rec_bank_account' => $request->received_bank_account,
                    'interest_type' => $request->interest_type,
                    'tds_receivable' => $request->tds_receive_year,
                ];
                // dd($data);
                $createTransaction = CompanyBoundTransaction::create($data);
                $encodeDate = json_encode($createTransaction);
                $v_no = NULL;
                $v_date = NULL;
                $globaldate = $request->created_at;
                $currency_code = 'INR';
                $select_date = $request['date'];
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
                $amount = $request->interest_amount;
                $opening_balance = $request->interest_amount;
                $closing_balance = $request->interest_amount;
                $type = 30;
                $sub_type = 302;
                $bank_ac_id = $request->received_bank_account;
                $type_id = $createTransaction->id;
                $type_transaction_id = $type_id;
                $refId = $daybook_ref_id = $DayBookRef;
                $member_id = NULL;
                if ($request->interest_type == '0') {
                     
                    $bank_id = $request->received_bank_name;
                    $bankDtail = getSamraddhBank($request->received_bank_name);
                    $bankAcDetail = getSamraddhBankAccountId($request->received_bank_name);
                    $amount_from_id = $type;
                    $amount_from_name =$request->fd_no;
                    $amount_to_id = $bank_id;
                    $amount_to_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';
                    $transction_bank_to_name = $bankDtail->bank_name;
                    $transction_bank_from = $request->bank_name;
                    $transction_bank_to = $bank_id;
                    $transction_bank_ac_to = $bank_ac_id;
                    $lastheadId = $bankDtail->account_head_id;
                } else {
                    $lastheadId = $companyBound->head_id;
                    $amount_to_name = NULL;
                    $amount_to_id = null;

                } 
                $bank_id = NULL;
                $bankDtail = NULL;
                $bankAcDetail = NULL;
                $amount_from_id = $type;
                $amount_from_name = $request->fd_no;
               
                $payment_mode = 2;
                $des = ' Bank Account Dr  ' . $amount_to_name . ' to FD Interest Cr';
                $description_cr = 'Bank A/c Dr ' . $amount . '/-';
                $desAmount =  $request->interest_amount - $request->tds_amount;
                $description_dr = 'To  FD Interest A/c Cr ' . $desAmount . ' /-';
                $ndes = ' Bank Account CR  ' . $amount_to_name . ' to FD Interest Cr';
                $ndescription_cr = 'Bank A/c CR ' . $desAmount . '/-';
                $ndescription_dr = 'To  FD Interest A/c Cr ' . $desAmount . ' /-';
                $transction_bank_to_name = NULL;
                $transction_bank_from = $request->bank_name;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $transction_bank_ac_from = $request->fd_no;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
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
                $company_id= $request->company_id;
                $associate_id = NULL;
                $branch_id_to = NULL;
                $branch_id_from = NULL;
                $opening_balance = NULL;
                $closing_balance = NULL;
                $lastheadId1 = $companyBound->head_id;
                $total_amount = $request->interest_amount - $request->tds_amount;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;
                /// ------------- bank head -------------
                if ($request->interest_type == 0) {
                    $allTran2 = CommanController::headTransactionCreate($daybook_ref_id, $request->branch_id, $bank_ac_id, $bank_ac_id, 234, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des,'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);

                    

                    $smbdc = CommanController::samraddhBankDaybookCreate($daybook_ref_id, $bank_ac_id, $bank_ac_id, $type, $sub_type, $type_id, $associate_id= NULL, $member_id, $request->branch_id, $amount1=$request->interest_amount - $request->tds_amount , $amount1=$request->interest_amount - $request->tds_amount , $amount1=$request->interest_amount - $request->tds_amount , $ndes, $ndescription_dr, $ndescription_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
 
                }
                /// -------------------- loan from bank -----------------
                if ($request->interest_type == 1) {
                    $allTranlb = CommanController::headTransactionCreate($daybook_ref_id, $request->branch_id, $bank_ac_id, $bank_ac_id, 234, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des,'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);
                    $companyBound->update(['current_balance' =>  $companyBound->current_balance + $amount]);
                }
                $allTranlb = CommanController::headTransactionCreate($daybook_ref_id, $request->branch_id, $bank_ac_id, $bank_ac_id, $lastheadId2 =$request->tds_receive_year, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount=$request->tds_amount, $des,'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);

                $allTranlb = CommanController::headTransactionCreate($daybook_ref_id, $request->branch_id, $bank_ac_id, $bank_ac_id, $lastheadId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  
                $amount1=$request->interest_amount - $request->tds_amount , $des,'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);


                $branchDayBook = CommanController::branchDayBookNew($daybook_ref_id, $request->branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, $description_dr, $description_cr,'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date,  $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);

                
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                return back()->with('alert', $ex->getMessage());
            }
            return redirect()->route('admin.company.fd.list')->with('success', 'Company Bond Interest Generate Successfully!');
        }

        public function transactions($id)
        {
            if (check_my_permission(Auth::user()->id, "259") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Samraddh Bank FDR || Interest List';
            $data['id'] = $id;
            return view('templates.admin.companyBank.interestTransaction', $data);
        }
        public function transactionList(Request $request)
        {
            if ($request->ajax()) {
                $data = CompanyBoundTransaction::with('company_bounds')->where('bound_id', $request->bound_id)->where('is_deleted', '0');
                $getAmount = CompanyBound::findorfail($request->bound_id);
                $data1 = $data->orderby('created_at', 'ASC')->get();
                $count = count($data1);
                $data = $data->orderby('created_at', 'ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                $totalCount = $count;
                $totalAmount = $getAmount->amount;
                
                foreach ($data as $row) {

                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['transaction_date'] = date("d/m/Y", strtotime($row->date));
                    $val['bank_name'] = $row['company_bounds']->bank_name;
                    $type = 'N/A';
                    if ($row->interest_type == 1) {
                        $type = 'Interest On FDR';
                    } elseif ($row->interest_type == 0) {
                        $type = 'Bank Account';
                    }
                    $val['transaction_type'] = $type;
                    $val['fd_no'] = $row['company_bounds']->fd_no;
                    $val['tds_amount'] = $row->tds_amount;
                    $val['interest_amount'] = $row->interest_amount;
                    if (isset($row->tds_amount)) {
                        $tdsAmount = $row->tds_amount;
                    } else {
                        $tdsAmount = 0;
                    }
                    $totalAmount = $totalAmount - $tdsAmount + $row->interest_amount;
                    $val['total_amount'] = $totalAmount;
                    if (isset($row->withdrawal_amount)) {
                        $val['withdrawal_amount'] = $row->withdrawal_amount;
                    } else {
                        $val['withdrawal_amount'] = 'N/A';
                    }
                    if (isset($row->rec_bank)) {
                        $val['receive_bank'] = getSamraddhBank($row->rec_bank)->bank_name;
                    } else {
                        $val['receive_bank'] = 'N/A';
                        $val['receive_bank_account'] = 'N/A';
                    }
                    if (isset($row->rec_bank_account)) {
                        $a = getSamraddhBankAccountId($row->rec_bank_account);
                        $val['receive_bank_account'] = $a->account_no;
                    } else {
                        $val['receive_bank_account'] = 'N/A';
                    }
                    $val['remark'] = $row->remark;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            }
        }
        public function FDClose(CompanyBound $id)
        {
            if (check_my_permission(Auth::user()->id, "262") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Samraddh Bank FDR || FD Close';
            $data['detail'] = $id->with('companies:id,name')->where('id',$id->id)->first();
            $data['banks'] = \App\Models\SamraddhBank::where('company_id',$id->company_id)->get();
            $data['branch'] = \App\Models\Branch::where('id', 29)->first();
            $data['totalAmount1'] = $data['detail']->amount +  $data['detail']->getCompanyBoundTransactionV2->sum('interest_amount') - $data['detail']->getCompanyBoundTransactionV2->sum('tds_amount');
            $data['interest_amount'] = \App\Models\CompanyBoundTransaction::where('bound_id',$id->id)->where('interest_type',1)->sum('interest_amount');
            $data['tds_amount'] = \App\Models\CompanyBoundTransaction::where('bound_id',$id->id)->where('interest_type',1)->sum('tds_amount');
            $data['totalAmount2'] = $data['interest_amount'] - $data['tds_amount'];
            $data['totalAmount'] =$data['detail']->amount +  $data['totalAmount2'];
            // $data['totalAmount'] = $data['interest_amount'] - $data['tds_amount'];
            

            return view('templates.admin.companyBank.closeFD', $data);
        }
        public function FDClosePermanent(Request $request)
        {


            try {
                
                $id = $request->id;
                $updateStatus = CompanyBound::where('id', $id)->first();;
                $curAmount =  $updateStatus->amount +  $updateStatus->getCompanyBoundTransactionV2->sum('interest_amount') - $updateStatus->getCompanyBoundTransactionV2->sum('tds_amount');
                // dd($curAmount);
                $DayBookRef = CommanController::createBranchDayBookReferenceNew($request->current_balance, date("Y-m-d", strtotime(convertDate($request->created_at))));
                $encodeDate = json_encode($updateStatus);
                $v_no = NULL;
                $v_date = NULL;
                $globaldate = $updateStatus->created_at;
                $currency_code = 'INR';
                $select_date = $request->maturity_date1;
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
                $amount = $request->current_balance;
                $opening_balance = $request->current_balance;
                $closing_balance = $request->current_balance;
                $type = 30;
                $sub_type = 303;
                $bank_ac_id = $updateStatus->rec_bank_account;
                $type_id = $updateStatus->id;
                $type_transaction_id = $type_id;
                $refId = $daybook_ref_id = $DayBookRef;
                $member_id = NULL;
                $bank_id = $request->received_bank_name;
                $bankDtail = getSamraddhBank($request->received_bank_name);
                $bankAcDetail = getSamraddhBankAccountId($request->received_bank_account);
                $amount_from_id = $type;
                $amount_from_name = $updateStatus->fd_no;
                $amount_to_id = $bank_id;
                $amount_to_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';
                $payment_mode = 2;
                $des = ' Bank Account Dr  ' . $amount_to_name . ' to FD  Cr';
                $description_cr = 'Bank A/c Cr ' . $amount . '/-';
                $description_dr = 'To  FD  A/c Dr ' . $amount . ' /-';
                $ndes = ' Bank Account CR  ' . $amount_to_name . ' to FD  Dr';
                $ndescription_cr = 'Bank A/c CR ' . $amount . '/-';
                $ndescription_dr = 'To  FD  A/c Dr ' . $amount . ' /-';
                $transction_bank_to_name = $bankDtail->bank_name;
                $transction_bank_to_ac_no = $bankAcDetail->account_no;
                $transction_bank_to_branch = $bankAcDetail->branch_name;
                $transction_bank_to_ifsc = $bankAcDetail->ifsc_code;
                $transction_bank_from = $updateStatus->bank_name;
                $transction_bank_ac_from = $updateStatus->fd_no;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = $bank_id;
                $transction_bank_ac_to = $bank_ac_id;
                $lastheadId = $bankDtail->account_head_id;
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
                $branch_id = Auth::user()->id;
                $associate_id = NULL;
                $branch_id_to = NULL;
                $branch_id_from = NULL;
                $opening_balance = NULL;
                $closing_balance = NULL;
                $company_id = $request->company_id;
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;
                /// ------------- bank head -------------
                $allTran2 = CommanController::headTransactionCreate($daybook_ref_id,$request->branch_id, $bank_id, $bank_ac_id, $lastheadId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);
                $allTran3 = CommanController::headTransactionCreate($daybook_ref_id,$request->branch_id, $bank_id, $bank_ac_id, 
                $lastheadId =$updateStatus->head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $amount, $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id,$company_id);
                $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify(
                    $daybook_ref_id, $bank_id,$bank_ac_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $request->branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id,$company_id
                );
                $branchDayBook = CommanController::branchDayBookNew($daybook_ref_id, $request->branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date,  $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
             
                $data = [
                    'daybook_ref_id' => $daybook_ref_id,
                    'bound_id' => $updateStatus->id,
                    'date' => $entry_date,
                    'tds_amount' => 0,
                    'interest_amount' => 0,
                    'remark' => $request->remark,
                    'rec_bank' => $request->received_bank_name,
                    'rec_bank_account' => $request->received_bank_account,
                    'interest_type' => 0,
                    'tds_receivable' => NULL,
                    'withdrawal_amount' => $amount,
                ];
                $createTransaction = CompanyBoundTransaction::create($data);
                $updateStatus->update(['status' => 1, 'current_balance' => $curAmount - $amount]);
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                return back()->with('alert', $ex->getMessage());
            }
            return redirect()->route('admin.company.fd.list')->with('success', 'Company Bond Closed Successfully!');
        }

        
    }