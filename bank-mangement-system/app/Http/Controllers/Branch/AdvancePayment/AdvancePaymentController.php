<?php

namespace App\Http\Controllers\Branch\AdvancePayment; 
use Illuminate\Support\Facades\Cache;
use App\Models\SamraddhCheque;
use App\Models\SamraddhBankClosing;
use App\Models\RentLiability;
use App\Models\RentLiabilityLedger;
use App\Models\RentLedger;
use App\Models\TaSettlment;
use App\Services\ImageUpload;
use App\Models\SamraddhChequeIssue;
use App\Models\SavingAccount;
use App\Models\EmployeeLedger;
use App\Http\Controllers\Branch\CommanController;
use App\Models\Branch;
use App\Models\AccountHeads;
use Carbon\Carbon;
use Session;
use URL;
use App\Models\Employee;
use App\Models\SamraddhBank;
use App\Models\CompanyBranch;
use App\Models\BranchCash;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\AdvancedTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request2;


class AdvancePaymentController extends Controller
{
    public function add($id, $paymenttype)
    {

        if (!in_array('Advance Payment Request', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        // Check this Advance is already approved or not
        if ($id) {
            $adTransection = AdvancedTransaction::where('id', $id)->first(['id', 'status']);
            if ($adTransection['status'] == 1) {
                return Redirect::to('branch/requestList')->with('error', 'Already Approved!!');
            }
        }

        $data = [];
        $data['u_id'] = $id;
        $data['u_paymenttype'] = $paymenttype;
        $advancePayment = AdvancedTransaction::with([
            'Employee' => function ($e) {
                $e->with([
                    'getSsb' => function ($g) {
                        $g->select('id', 'account_no');
                    }
                ])->select('id', 'ssb_id', 'employee_code', 'employee_name', 'mobile_no', 'bank_name', 'bank_account_no', 'bank_ifsc_code');
            },
            'rentLiability' => function ($r) {
                $r->with(['employee_rent' => function ($q) {
                    $q->with(['getssbaccountnumber' => function ($s) {
                        $s->select('id', 'account_no');
                    }])->select('id', 'ssb_id');
                }])->select('id', 'owner_name', 'owner_mobile_number','owner_ssb_number', 'employee_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code');
            }
            ])->with('company:id,name')->where('id', $id)->where('status', 0)->first(['id', 'type', 'sub_type', 'type_id', 'branch_id', 'amount', 'description', 'narration', 'status', 'status_date', 'status_remark', 'demand_amount', 'created_by', 'created_by_id', 'created_at', 'updated_at','company_id']);


        $data['payment_type'] = 'N/A';

        if (!empty($advancePayment['sub_type'])) {
            if ($advancePayment['sub_type'] == 317) {
                $data['payment_type'] = 2;
            } else if ($advancePayment['sub_type'] == 316) {
                $data['payment_type'] = 1;
            } else if ($advancePayment['sub_type'] == 315) {
                $data['payment_type'] = 0;
            } else {
                $data['payment_type'] = '';
            }
        }
        $data['branch_id']          = $advancePayment['branch_id'] ?? NULL;
        $data['date']               = $advancePayment['created_at'] ?? NULL;

        if ($paymenttype == 316 || $paymenttype == 317) {

            $data['employeecode']       = $advancePayment['Employee']['employee_code'] ?? NULL;
            $data['employename']        = $advancePayment['Employee']['employee_name'] ?? NULL;
            $data['employeid']          = $advancePayment['Employee']['id'] ?? NULL;
            $data['mobilenumber']       = $advancePayment['Employee']['mobile_no'] ?? NULL;
            $data['bankname']           = $advancePayment['Employee']['bank_name'] ?? NULL;
            $data['bankacountnumber']   = $advancePayment['Employee']['bank_account_no'] ?? NULL;
            $data['bankifsc']           = $advancePayment['Employee']['bank_ifsc_code'] ?? NULL;
            $data['ssbaccountnumber']   = $advancePayment['Employee']['getSsb']['account_no'] ?? NULL;
        } else {
            $data['employeecode']       = $advancePayment['rentLiability']['id'] ?? NULL;
            $data['employename']        = $advancePayment['rentLiability']['owner_name'] ?? NULL;
            $data['mobilenumber']       = $advancePayment['rentLiability']['owner_mobile_number'] ?? NULL;
            $data['employeid']          = $advancePayment['rentLiability']['employee_id'] ?? NULL;
            $data['bankname']           = $advancePayment['rentLiability']['owner_bank_name'] ?? NULL;
            $data['bankacountnumber']   = $advancePayment['rentLiability']['owner_bank_account_number'] ?? NULL;
            $data['bankifsc']           = $advancePayment['rentLiability']['owner_bank_ifsc_code'] ?? NULL;
            $data['ssbaccountnumber']   = $advancePayment['rentLiability']['owner_ssb_number'] ?? NULL;
        }

        $data['narration']         = $advancePayment['narration'] ?? NULL;
        $data['company']         = $advancePayment['company']['name'] ?? NULL;
        $data['company_id']         = $advancePayment['company']['id'] ?? NULL;
        $data['advancamount']      = $advancePayment['demand_amount'] ?? NULL;

        $branchcurrentbalance = \App\Models\BranchCurrentBalance::where('company_id',$data['company_id'])->where('branch_id', $data['branch_id'])->whereDate('entry_date', '<=', $data['date'])->orderby('entry_date', 'desc')->sum('totalAmount');
        $data['BranchCurrentBalance'] = $branchcurrentbalance?? 0;
        $data['branch'] =  Branch::where('status', 1)->get(['id', 'name'])->toArray();
        $data['advanceTranserctionId'] = $advancePayment['id'];

        // if (Auth::user()->branch_id > 0) {
        //     $ids = $this->getDataRolewise(new Branch());
        //     $datas = $datas->whereIn('id', $ids);
        // }
        $data['rentOwners'] = RentLiability::where('status', 0)->get(['id', 'owner_name']);
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id',$data['company_id'])->get(['id', 'bank_name']);

        if ($data['payment_type'] == 0) {
            $payt = 'Advance Rent Payment';
        } else if ($data['payment_type'] == 1) {
            $payt = 'Advance Salary';
        } else {
            $payt = 'TA Imprest Adance';
        }
        $title = $payt;


        return view('templates/branch/AdvancePayment/approve', compact('data', 'title'));
    }


    public function add_request()
    {
        if (!in_array('Advance Payment Add Request', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $datas =  Branch::where('status', 1);
        $data['branch'] = $datas->get(['id', 'name']);
        // $data['rentOwners'] = RentLiability::where('status', 0)->get(['id', 'owner_name']);
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->get(['id', 'bank_name']);
        $getBranchId=getUserBranchId(Auth::user()->id);
        $data['branch_id']=$getBranchId->id;
        // $data['company'] = CompanyBranch::where('branch_id', $data['branch_id'])->with([
        //     'get_company' => function ($e) {
        //                 $e->select('id', 'name')->where('status',1);
        //             }
        //         ])->get(['id', 'company_id']);
        //         dd($data['company']);
        $data['title'] = 'Create Request';
        return view('templates/branch/AdvancePayment/add_request', $data);
    }

    public function requestList()
    {
        if (!in_array('Advance Request List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        // $datas =  Branch::where('status', 1);
        // $data['branch'] = $datas->get(['id', 'name']);
        // if (Auth::user()->branch_id > 0) {
        //     $ids = $this->getDataRolewise(new Branch());
        //     $datas = $datas->whereIn('id', $ids);
        // }
        $data['rentOwners'] = RentLiability::where('status', 0)->get(['id', 'owner_name']);
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->get(['id', 'bank_name']);

        $getBranchId=getUserBranchId(Auth::user()->id);
        $data['branch_id']=$getBranchId->id;
        $data['company'] = CompanyBranch::where('branch_id', $data['branch_id'])->has('company')->with('get_company:id,name')->get(['id', 'company_id']);
        $data['title'] = 'Request listing';
        return view('templates/branch/AdvancePayment/requestList', $data);
    }

    // Payment List Page
    public function paymentList()
    {
        if (!in_array('Advance Payment Request', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $datas =  Branch::where('status', 1);
        $data['branch'] = $datas->get(['id', 'name']);
        $getBranchId=getUserBranchId(Auth::user()->id);
        $data['branch_id']=$getBranchId->id;
        $data['company'] = CompanyBranch::where('branch_id', $data['branch_id'])->with('get_company:id,name')->get(['id', 'company_id']);
        $data['rentOwners'] = RentLiability::where('status', 0)->get(['id', 'owner_name']);
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->get(['id', 'bank_name']);

        $data['title'] = 'Payment listing';
        return view('templates/branch/AdvancePayment/paymentList', $data);
    }


    public function advanceTrasectionReject(Request $request, $id)
    {

        $entryTime = date("H:i:s");
        $date = date('Y-m-d H:i:s', strtotime($request['created_at']));

        $AdvancedTransaction = AdvancedTransaction::find($id);
        DB::beginTransaction();
        try {
            $AdvancedTransaction['status'] = 2;
            $AdvancedTransaction['status_date'] = $date;
            $AdvancedTransaction['status_remark'] = $request->remark ?? null;

            $rejected = $AdvancedTransaction->update();
            DB::commit();
            if ($rejected) {
                return Redirect::to('branch/requestList')->with('success', 'Request Rejected successfully!');
            } else {
                return Redirect::to('branch/requestList')->with('error', 'Something Went Wrong!');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return $e->getMessage();
        }
    }

    // datatable request listing page
    public function AdvancedRequestListing(Request $request)
    {
        $AdvancedTransaction = AdvancedTransaction::with([
            'Employee' => function ($e) {
                $e->with([
                    'getSsb' => function ($g) {
                        $g->select('id', 'account_no');
                    }
                ])->select('id', 'employee_code', 'employee_name', 'mobile_no', 'bank_name', 'bank_account_no', 'bank_ifsc_code');
            },
            'rentLiability' => function ($r) {
                $r->with([
                    'getssbaccountnumber' => function ($s) {
                        $s->select('id', 'account_no');
                    }
                ])->select('id', 'owner_name', 'owner_mobile_number', 'employee_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code');
            }, 'branch' => function ($b) {
                $b->select('id', 'name');
            }, 'user' => function ($a) {
                $a->select('id', 'username');
            }
            // ])->whereIn('status', [0, 2]);
            ])->has('company')->with('company:id,name')->with('branchUser:id,username')->where('is_deleted', 0);

        // Fillter Query Start
        $startDate   =  $request['start_date'];
        $endDate     =  $request['end_date'];
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branchId    =  $getBranchId->id;
        $company_id    =  $request['company_id'];
        $paymentType =  $request['paymentType'];
        $status      =  $request['status'];
        // $status_ = $status == 0 ? '1' : '2';
        $settlement =  $request['settlement'];

        $AdvancedTransaction = ($settlement != null) ? $AdvancedTransaction->where('settlement', $settlement) : $AdvancedTransaction;
        if ($status != null) {
            $AdvancedTransaction = $AdvancedTransaction->where('status', $status);
        } else {
            $AdvancedTransaction = $AdvancedTransaction->whereIn('status', [0,1,2]);
        }
        // dd($AdvancedTransaction->get()->toArray());
        // if ($startDate  != '') {
        //     $startDate = date("Y-m-d", strtotime(convertDate($startDate)));

        //     if ($endDate != '') {
        //         $endDate = date("Y-m-d ", strtotime(convertDate($endDate))) ?? '';
        //     }

        //     $AdvancedTransaction->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        // }
        if ($startDate != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
            $AdvancedTransaction->whereDate('created_at', '>=', $startDate);
        }
        
        // End Date Filter
        if ($endDate != '') {
            $endDate =  date("Y-m-d ", strtotime(convertDate($endDate))) ?? '';
            $AdvancedTransaction->whereDate('created_at', '<=', $endDate);
        }
        if ($branchId != '') {
            $AdvancedTransaction->where('branch_id', $branchId);
        }
        if ($company_id != '' && $company_id != 0) {
            $AdvancedTransaction->where('company_id', $company_id);
        }
        if ($paymentType != '') {

            // Here we can check request payment type then we have 315 for rent 316 for salary 317 for TA so we put fillter condition accordingly.
            if ($paymentType == 0) {
                $subType = 31;
            } else if ($paymentType == 1) {
                $subType = 42;
            } else {
                $subType = 41;
            }

            $AdvancedTransaction->where('sub_type', $subType);
        }
        // Fillter Query End

        // $count      = $AdvancedTransaction->count('id');
        // $totalCount =  $AdvancedTransaction->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
        // $data =  $AdvancedTransaction->get(['id', 'type', 'sub_type', 'type_id', 'branch_id', 'amount', 'status', 'status_date', 'status_remark', 'demand_amount', 'narration', 'created_by', 'created_by_id', 'created_at', 'updated_at','company_id']);
        // $rowReturn = array();



        $count      =  $AdvancedTransaction->count('id');
        $datac      =  $AdvancedTransaction->orderby('id', 'DESC')->get(['id', 'type', 'sub_type', 'type_id', 'branch_id', 'amount', 'status', 'status_date', 'status_remark', 'demand_amount', 'narration', 'created_by', 'created_by_id', 'created_at', 'updated_at', 'company_id','settlement','used_amount','repay','withdraw']);
        $totalCount =  $AdvancedTransaction->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
        $data =  $AdvancedTransaction->get(['id', 'type', 'sub_type', 'type_id', 'branch_id', 'amount', 'status', 'status_date', 'status_remark', 'demand_amount', 'narration','file', 'created_by', 'created_by_id', 'created_at', 'updated_at', 'company_id','settlement','used_amount','repay','withdraw']);
        $rowReturn = array();
        $token = session()->get('_token');

        $Cache = Cache::put('advance_reqb' . $token, $datac);
        Cache::put('advance_reqb_COUNT' . $token, $count);
        foreach ($data as $row) {
            $btn = '';

            $subtype = 'N/A';
            $typeid = 'N/A';
            if ($row['sub_type'] == 31) {
                $subtype = 'Rent Advance Request';
                $typeName = "Advance Rent";
                $typeid = $row['rentLiability']['owner_name'];
            } else if ($row['sub_type'] == 42) {
                $subtype = 'Salary Advance Request';
                $typeName = "Advance Salary";
                $typeid = $row['employee']['employee_name'];
            } else if ($row['sub_type'] == 41) {
                $subtype = 'Ta Advance Request';
                $typeName = "TA Advance";
                $typeid = $row['employee']['employee_name'];
            }
            $userw = $row['created_by'];
            if ($userw == 1) {
                $userw =  $row['user']['username'];
            } else if ($userw == 2) {
                $userw =  $row['branchUser']['username'];
            } else if ($userw == 3) {
                $userw = 'Associate App';
            }

            $val['DT_RowIndex']     = $row['id'] ?? 'N/A';
            $val['type']            = $typeName ?? 'N/A';
            $val['sub_type']        = $subtype ?? 'N/A';
            $val['type_id']         = $typeid;
            $val['branch_id']       = $row['branch']['name'] ?? 'N/A';
            $val['amount']          = $row['amount'] ?? 'N/A';
            $val['demand_amount']   = $row['demand_amount'] ?? 'N/A';
            $val['settled_amount']  = $row['used_amount'] ?? 'N/A';
            $val['description']     = $row['narration'] ?? 'N/A';
            // $val['payment_settlement']   = ($row['settlement'] == '0') ? 'No' : ($row->settlement == '1' ? 'Yes' : '');
            $image = 'N/A';
            if ($row['file']) {
                // $image = URL::to("/asset/taAdjustment/$value->image");
                $folderName = 'AdvanceRequest/'.$row['file'];
                $url = ImageUpload::generatePreSignedUrl($folderName);
                $image = '<a href="' . $url . '" target="_blank"><i class="fas fa-eye mr-2s"></i></a>';
            }
            $val['image'] = $image;
            $val['payment_settlement'] = 'N/A';
            if ($row['settlement'] == '0') {
                $val['payment_settlement'] = 'Pending';
            } else if ($row['settlement'] == '1'){
                $val['payment_settlement'] = 'Fully Settled';
            } else if($row['settlement'] == '2'){
                $val['payment_settlement'] = 'Partially Settled';
            }
            $val['status']          = ($row['status'] == 0) ? 'Pending' : (($row['status'] == 1) ? 'Paid' : 'Rejected');
            $val['status_date']     = $row['status_date'] ? date("d/m/Y", strtotime(convertDate($row['status_date']))) : 'N/A';
            $val['status_remark']   = $row['status_remark'] ?? 'N/A';
            $val['created_by']      = $userw;
            $val['created_by_id']   = $row['admin']['username'] ?? 'N/A';
            $val['created_at']      = date("d/m/Y", strtotime(convertDate($row['created_at']))) ?? 'N/A';
            $val['updated_at']      = date("d/m/Y", strtotime(convertDate($row['updated_at']))) ?? 'N/A';
            $val['company']      = $row['company']['name'] ?? 'N/A';
            
            $excess = 'N/A';
            if ($row['withdraw'] || $row['repay']) {
               $excess = !empty($row['withdraw']) ? $row['withdraw'] : '-'.$row['repay'];
            }
            $val['excess'] = $excess;

            // if ($row['status'] == 2 || $row['status'] == 1) {
            //     $btn .= 'N/A';
            // } else {
            //     $data2 = array();
            //     $data2 = [
            //         'payment_type' => $row['sub_type'] ?? 'n/a',
            //         'branch' => $row['branch']['id'] ?? 'n/a',
            //         'owner_name' => $row['type_id'] ?? 'n/a',
            //         'narration' => $row['narration'] ?? 'n/a',
            //         'amount' => $row['amount'] ?? 'n/a',
            //         'number' => $row['Employee']['mobile_no'] ?? 'n/a',
            //         'bankname' => $row['Employee']['bank_name'] ?? 'n/a',
            //         'bankacnumber' => $row['Employee']['bank_account_no'] ?? 'n/a',
            //         'ifsccode' => $row['Employee']['bank_ifsc_code'] ?? 'n/a',
            //         'ssbaccountnumber' => $row['Employee']['ssb_account'] ?? 'n/a',
            //         'employee_code' => $row['type_id'] ?? 'n/a',
            //     ];
            //     $id = $row['id'];
            //     $paymentTypee = $row['sub_type'];
            //     $url = url('/branch/advancePayment/' . $id . '/' . $paymentTypee);
            //     $btn .= '<div class="list-icons">
            //                 <div class="dropdown">
            //                     <a href="#" class="list-icons-item" data-toggle="dropdown">
            //                     <i class="fas fa-bars ml-3"></i>
            //                     </a>
            //                     <div class="dropdown-menu dropdown-menu-right">
            //                         <a class="dropdown-item" href="' . $url . '" data-id="' . $row['id'] . '" data-status="1" title="Approve">
            //                         <i class="fas fa-check mr-3 ml-2"></i>Approve
            //                         </a>
            //                         <a class="dropdown-item remark" href="#" data-url="' . route('branch.advancePayment.advanceTrasectionReject', $row['id']) . '" data-id="' . $row['id'] . '" data-status="2" data-toggle="modal" data-target="#remark" title="Reject">
            //                         <i class="fas fa-times mr-3 ml-2"></i>&nbsp; Reject
            //                         </a>
            //                     </div>
            //                 </div>
            //         </div>';
            // }
            if ($row['sub_type'] == 41) {
                $btn .= '<a href="' . route('branch.advancePayment.Adjestmentview', $row['id']) . '"><i class="far fa-eye mr-2"></i></a>';
            } else {
                $btn = 'N/A';
            }
            $val['action'] = $btn;
            $rowReturn[] = $val;
        }

        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);

        return json_encode($output);
    }

    public function exportAdvanceRequestList(Request $request)
    {
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data  = Cache::get('advance_reqb' . $token);
        $count = Cache::get('advance_reqb_COUNT' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/AdvancePaymentRequest" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/AdvancePaymentRequest" . $file . ".csv";
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
        $rowReturn = [];
        $data = $data->toArray();
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        foreach ($record as $row) {
            $userw = $row['created_by'];
            if ($userw == 1) {
                $userw =  $row['user']['username'];
            } else if ($userw == 2) {
                $userw =  $row['branch_user']['username'];
            } else if ($userw == 3) {
                $userw = 'Associate App';
            }
            $sno++;
            $val['S/N'] = $sno;
            $typeid = 'N/A';
            $subtype = 'N/A';
            if ($row['sub_type'] == 31) {
                $subtype = 'Rent Advance Request';
                $typeName = "Advance Rent";
                $typeid = $row['rent_liability']['owner_name'];
            } else if ($row['sub_type'] == 42) {
                $subtype = 'Salary Advance Request';
                $typeName = "Advance Salary";
                $typeid = $row['employee']['employee_name'];
            } else if ($row['sub_type'] == 41) {
                $subtype = 'Ta Advance Request';
                $typeName = "TA Advance";
                $typeid = $row['employee']['employee_name'];
            }

           
            
            $val['BRANCH NAME']     = $row['branch']['name'] ?? 'N/A';
            $val['REQUESTED DATE']      = date("d/m/Y", strtotime(convertDate($row['created_at']))) ?? 'N/A';
            $val['TYPE']            = $typeName ?? 'N/A';
            $val['NAME']            = $typeid;
            $val['REQUEST AMOUNT']  = $row['demand_amount'] ?? 'N/A';
            $val['DESCRIPTION']     = $row['narration'] ?? 'N/A';
            $val['STATUS']          = ($row['status'] == 0) ? 'Pending' : (($row['status'] == 1) ? 'Paid' : 'Rejected');
            $val['ADVANCE DATE']     = $row['status_date'] ? date("d/m/Y", strtotime(convertDate($row['status_date']))) : 'N/A';
            // $val['SUB TYPE']        = $subtype ?? 'N/A';
            $val['ADVANCE AMOUNT']  = $row['amount'] ?? 'N/A';
            $val['SETTLED AMOUNT']  = $row['used_amount'] ?? 'N/A';
            // $val['PAYMENT SETTLEMENT']   = ($row['settlement'] == '0') ? 'No' : ($row->settlement == '1' ? 'Yes' : '');
            $val['SETTLEMENT STATUS'] = 'N/A';
            if ($row['settlement'] == '0') {
                $val['SETTLEMENT STATUS'] = 'Pending';
            } else if ($row['settlement'] == '1'){
                $val['SETTLEMENT STATUS'] = 'Fully Settled';
            } else if($row['settlement'] == '2'){
                $val['SETTLEMENT STATUS'] = 'Partially Settled';
            }
            $val['COMPANY NAME']      = $row['company']['name'] ?? 'N/A';
            // $val['STATUS REMARK']   = $row['status_remark'] ?? 'N/A';
            $val['USER']      = $userw;
            // $val['CREATED BY ID']   = $row['user']['username'] ?? 'N/A';
            // $val['UPDATED AT']      = date("d/m/Y", strtotime(convertDate($row['updated_at']))) ?? 'N/A';
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
    // datatable Payment listing page
    public function PaymentListing(Request $request)
    {
        $AdvancedTransaction = AdvancedTransaction::with([
            'Employee' => function ($e) {
                $e->with([
                    'getSsb' => function ($g) {
                        $g->select('id', 'account_no');
                    }
                ])->select('id', 'employee_code', 'employee_name', 'mobile_no', 'bank_name', 'bank_account_no', 'bank_ifsc_code');
            },
            'rentLiability' => function ($r) {
                $r->with([
                    'getssbaccountnumber' => function ($s) {
                        $s->select('id', 'account_no');
                    }
                ])->select('id', 'owner_name', 'owner_mobile_number', 'employee_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code');
            }, 'branch' => function ($b) {
                $b->select('id', 'name', 'branch_code');
            }, 'admin' => function ($a) {
                $a->select('id', 'username');
            }
            ])->with('company:id,name')->where('is_deleted', 0);
        // ])->whereIn('status', [1]);

        // Fillter Query Start
        $startDate   =  $request['start_date'];
        $endDate     =  $request['end_date'];
        $getBranchId=getUserBranchId(Auth::user()->id);
        $branchId    =  $getBranchId->id;
        $company_id    =  $request['company_id'];
        $paymentType =  $request['paymentType'];
        $settlement =  $request['settlement'];

        $AdvancedTransaction = ($settlement != null) ? $AdvancedTransaction->where('settlement', $settlement) : $AdvancedTransaction;

        if ($startDate  != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));

            if ($endDate != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($endDate))) ?? '';
            }

            $AdvancedTransaction->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        if ($branchId != '' && $branchId != 0) {
            $AdvancedTransaction->where('branch_id', $branchId);
        }
        if ($company_id != '' && $company_id != 0) {
            $AdvancedTransaction->where('company_id', $company_id);
        }
        if ($paymentType != '') {

            // Here we can check request payment type then we have 315 for rent 316 for salary 317 for TA so we put fillter condition accordingly.
            if ($paymentType == 0) {
                $subType = 31;
            } else if ($paymentType == 1) {
                $subType = 42;
            } else {
                $subType = 41;
            }

            $AdvancedTransaction->where('sub_type', $subType);
        }
        // Fillter Query End
        $count      = $AdvancedTransaction->count('id');
        $totalCount =  $AdvancedTransaction->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
        $data =  $AdvancedTransaction->get(['id', 'type', 'sub_type', 'type_id', 'branch_id', 'amount', 'description', 'status', 'status_date', 'status_remark', 'demand_amount', 'created_by', 'created_by_id', 'created_at', 'updated_at', 'settlement','repay','withdraw']);

        $rowReturn = array();


        foreach ($data as $row) {
            $btn = '';
            $subtype = 'N/A';
            if ($row['sub_type'] == 315) {
                $subtype = 'Rent Advance Request';
            } else if ($row['sub_type'] == 316) {
                $subtype = 'Salary Advance Request';
            } else if ($row['sub_type'] == 317) {
                $subtype = 'Ta Advance Request';
            }
            $userw = $row['created_by'];
            if($userw == 1){
                $userw = 'Admin';
            } else if ($userw == 2){
                $userw = 'Branch';
            } else if ($userw == 3){
                $userw = 'Associate App';
            } 

            $val['DT_RowIndex']          = $row['id'] ?? 'N/A';
            $val['date']                 = date("d/m/Y", strtotime(convertDate($row['created_at']))) ?? 'N/A';
            $val['branch_id']            = $row['branch']['name'] ?? 'N/A';
            $val['branch_code']          = $row['branch']['branch_code'] ?? 'N/A';
            $val['sub_type']             = $subtype ?? 'N/A';
            $val['amount']               = $row['amount'] ?? 'N/A';
            if ($row['status'] == 2) {
                $val['amount'] =  "Rejected";
            }
            $excess = 'N/A';
            if ($row['withdraw'] || $row['repay']) {
               $excess = !empty($row['withdraw']) ? $row['withdraw'] : '-'.$row['repay'];
            }
            $val['excess'] = $excess;
            $val['name']                 = $row['rentLiability']['owner_name'] ?? $row['Employee']['employee_name'];
            $val['payment_settlement']   = ($row->settlement == '0') ? 'No' : ($row->settlement == '1' ? 'Yes' : '');
            $val['created_by']           = $userw;



            if ($row['sub_type'] == 317) {
                $btn .= '<div class="list-icons">
                <div class="dropdown">
                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                    <i class="icon-menu9"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="' . route('branch.advancePayment.addAdjestment', $row['id']) . '" data-status="1" title="Approve">
                            <i class="icon-pencil5  mr-2"></i>Adjust Payment
                        </a>
                        <a class="dropdown-item" href="' . route('branch.advancePayment.Adjestmentview', $row['id']) . '"><i class="far fa-eye mr-2"></i>View Transaction</a>
                        
                    </div>
                </div>
             </div>';
            } else {
                $btn = 'N/A';
            }



            $val['action'] = $btn;
            $rowReturn[] = $val;
        }

        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
        return json_encode($output);
    }

    public function advancerequest(Request $request)
    {

        /*Validate function to validate the BANK NAME,BANK NUMBER,BANK IFSC CODE,AMOUNT if not came it's through error*/
        $this->validate($request, [
            'paymentType' => 'required',
            'date' => 'required',
            'branch_id' => 'required',
            'narration' => 'required',
            'aamount' => 'required',
            'advanced_salary_mobile_number2' => 'required',
            'advanced_salary_bank_name2' => 'required',
            'advanced_salary_bank_account_number2' => 'required',
            'advanced_salary_ifsc_code2' => 'required',
        ]);
        $entryTime              = date("H:i:s");
        $created_at             = $request['date'];
        $date                   = $request['date'];
        $branch_id              = $request['branch_id'];
        $amount                 = $request['aamount'];
        $narration              = $request['narration'];
        $company_id              = $request['company_id'];

        // DB::beginTransection();

        // try{

            if ($request['paymentType'] == 2) {
                $type     = 4;
                $sub_type = 41;
                $type_id  = $request['employee_id'];
                $des = 'TA Advance Payment';
            } else if ($request['paymentType'] == 1) {
                $type            = 4;
                $sub_type        = 42;
                $type_id  = $request['employee_id'];
                $des = ' Advance Salary';
            } else if ($request['paymentType'] == 0) {
                $type         = 3;
                $sub_type     = 31;
                $type_id      = $request['advanced_rent_party_name'];
                $des = ' Advance Rent';
            } else {
                /*Please check paymentType we can't find and Transection Id with this payment type */
                return response()->json(['error' => 'we can not find any Transection id attached with this paymentType!'], 404);
            }
            $file_name = null;
            if (isset($request['file'])) {
                $uploaded_file = $request['file'];
                $rand = rand(0000, 9999);
    
                $file_extension = $uploaded_file->getClientOriginalExtension();
                $file_name = $rand . '_' . time() . '.' . $file_extension;
                $file_location = 'AdvanceRequest/';
                ImageUpload::upload($uploaded_file,$file_location,$file_name);
            }
        /* Save the data in Advance Transection table */
        $empAdvance['type']                =  $type;
        $empAdvance['narration']           =  $narration;
        $empAdvance['sub_type']            =  $sub_type;
        $empAdvance['type_id']             =  $type_id;
        $empAdvance['branch_id']           =  $branch_id;
        $empAdvance['demand_amount']       =  $amount;
        $empAdvance['status']              =  0;
        $empAdvance['status_date']         =  NULL;
        $empAdvance['description']         =  $des;
        $empAdvance['file']                 =  $file_name;
        $empAdvance['created_by_id']          =  Auth::user()->id;
        $empAdvance['created_by']       =  (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1; /*branch id*/
        $empAdvance['created_at']          =  date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_at)));
        $empAdvance['updated_at']          =  date("Y-m-d " . $entryTime . "", strtotime(convertDate($created_at)));
        $empAdvance['company_id']          =  $company_id;



        $AdvanceTransectionCreate  = AdvancedTransaction::create($empAdvance);
        return response()->json(['Advance Request added successfully'], 200);
        //     DB::commit();
        // } catch (\Exception $e) {
        //     return $e->getMessage();
        // } 

    }


    public function getemployee(Request $request)
    {
        if($request['employee_code']){
            $edata = Employee::with(['getSsb' => function ($query) {
                $query->select('id', 'account_no', 'member_id');
            }])->where('employee_code', $request['employee_code'])->first(['id', 'employee_name', 'branch_id', 'mobile_no', 'bank_name', 'bank_account_no', 'bank_ifsc_code', 'ssb_id', 'status', 'company_id', 'employee_date']);
            return response()->json([$edata]);
        }
        else{
            return response()->json(401);
        }
    }

    public function saveTAadvancepayment(Request $request)
    {


        /*Validate function to validate the BANK NAME,BANK NUMBER,BANK IFSC CODE,AMOUNT if not came it's through error*/
        $this->validate($request, [
            'advanced_salary_bank_name2' => 'required',
            'advanced_salary_bank_account_number2' => 'required',
            'advanced_salary_ifsc_code2' => 'required',
            'aamount' => 'required'
        ]);
        $branch_id              = $request['branch']; /*Now it's static Head Office Id if we need in future then we can dynamic it*/
        $bank_ac_id             = null;
        $head_id                = 76; /*account_heads table where you can find this is "Imprest, T.A. Advance" head Id*/
        $amount                 = $request['aamount'];
        $globaldate             = $request['created_at'];
        $select_date            = $request['date'];
        $current_date           = date("Y-m-d", strtotime(convertDate($globaldate)));
        $created_at             = date("Y-m-d", strtotime(convertDate($request['date'])));
        $entry_date             = $created_at;
        $employee_id            = $request['employee_id'];
        $transfer_mode            = $request['transfer_mode'];
        $associate_id           = NULL;
        $member_id              = $request['member_id'];
        $employeename           = $request['ename']??$request['employee_name'];
        $payment_mode           = $request['payment_mode'];
        $ssbAccountNo           = $request['ssbno'];
        $bankTransferMode       = $request['transfer_mode'];
        $bank_id                = $request['bank_id'] ?? NULL;
        $bank_name              = $request['advanced_salary_bank_name2'] ?? NULL;
        $bank_account_number    = $request['advanced_salary_bank_account_number2'] ?? NULL;
        $bank_account_ifsc      = $request['advanced_salary_ifsc_code2'] ?? NULL;
        $account_id             = $request['account_id'] ?? NULL;
        $bankAccountNumber      = $request['accountNumber'] ?? NULL;
        $chequeid               = $request['cheque_id'] ?? NULL;
        $neftcharges            = $request['neft_charge'] ?? NULL;
        $entryTime              = date("H:i:s");
        $ownerId                = $request['advanced_rent_party_name'] ?? NULL;
        $company_id             = $request['company_id'];
        if ($neftcharges == 0) {
            $neftcharges = '';
        }

        // Get Transection Type 
        /* There is transection_type this is a table where you can find type id and his sub_type like here we use 311 for Ta Advance and his   Type is 31 */
        if ($request['paymentType'] == 2) {
            $type = 31;
            if (!empty($neftcharges)) {
                $sub_type = 318;
                $sub_type2 = 311;
            } else {
                $sub_type = 311;
            }

            $descriptionTitle = 'TA Advance Payment';
            $type_id = $employee_id;
            $PheadId = 72;
            $EledgerType = 5;
            $NeftsubType = 318;
        } else if ($request['paymentType'] == 1) {
            $type = 31;
            if (!empty($neftcharges)) {
                $sub_type = 319;
                $sub_type2 = 313;
            } else {
                $sub_type = 313;
            }

            $descriptionTitle = 'Advance Salary';
            $type_id = $employee_id;
            $PheadId = 73;
            $EledgerType = 2;
            $NeftsubType = 319;
        } else if ($request['paymentType'] == 0) {
            $type = 31;
            if (!empty($neftcharges)) {
                $sub_type = 320;
                $sub_type2 = 314;
            } else {
                $sub_type = 314;
            }

            $descriptionTitle = 'Advance Rent Payment';
            $type_id = $request['advanced_rent_party_name'];
            $PheadId = 74;
            $NeftsubType = 320;
        } else {
            /*Please check paymentType we can't find and Transection Id with this payment type */
            return response()->json(['error' => 'we can not find any Transection id attached with this paymentType!'], 404);
        }

        // Get branch Current Balance
        $branchCurrentBalance = \App\Models\BranchCurrentBalance::where('branch_id', 29)->orderby('entry_date', 'desc')->sum('totalAmount');
        $barnch_opening_balance = $branchCurrentBalance;
        $barnch_closing_balance = $branchCurrentBalance;


        // Get Employee ssb account details
        $emplyeeSsbAccountDetails     = SavingAccount::where('account_no', $ssbAccountNo)->first();
        $employee_ssb_account_id      = $emplyeeSsbAccountDetails->id ?? NULL;
        $employee_ssb_account_balance = $emplyeeSsbAccountDetails->balance ?? NULL;


        




        try {

            DB::beginTransaction();


            // If payment type TA advance and payment mode cash 
            if ($request['paymentType'] == 2 || $request['paymentType'] == 1 || $request['paymentType'] == 0) {

                if ($payment_mode == "CASH") {
                    if (getbranchbankbalanceamounthelper($request->branch, $request->company_id, $request->date) <= $request['aamount']) {
                        return response()->json([
                            'message' => 'berror',
                            'redirect' => back()
                        ], 200);
                    }
                    $paymentMode = 0;
                    $transectionMethod = "CASH";
                    $headId = 28;
                    $ptd = 'DR';
                    $ptc = 'CR';
                    $description_dr                = "" . $employeename . " A/C Dr" . $amount . "/-";
                    $branch_daybook_payment_type   = "DR";
                    $branch_daybook_description_cr = "To " . $branch_name . " A/C Cr " . $amount . "/-";
                }

                if ($payment_mode == "SSB") {
                    $paymentMode = 3;
                    $transectionMethod = "SSB";
                    $headId = headbyssb($ssbAccountNo)->getPlanCustom->deposit_head_id;
                    $ptd = 'DR';
                    $ptc = 'CR';
                    $description_dr                = "" . $employeename . " A/C Dr" . $amount . "/-";
                        $branch_daybook_payment_type   = "DR";
                        $branch_daybook_description_cr = "To SSB(".$ssbAccountNo.") A/C Cr " . $amount . "/-";
                }

                if ($payment_mode == "BANK") {

                    
                    // Get Bank head Id
                    $samradhBankdata = SamraddhBank::where('id', $bank_id)->first();
                    $acc_number = SamraddhBankAccount::where('id',$account_id)->first();
                    // Get Cheque Details $chequeid
                    $checkData = SamraddhChequeIssue::where('cheque_id', $chequeid)->first(['id', 'type']);
                    $checkdetails = SamraddhCheque::where('id', $chequeid)->where('bank_id', $bank_id)->first(['id', 'cheque_no', 'account_id', 'cheque_create_date']);
                    // dd($checkdetails);
                    // Get Cheque last entry type id
                    $checkLastTypeid = SamraddhCheque::latest()->first();
                    
                    $paymentMode = ($bankTransferMode == 0) ? 1 : 2;
                    $transectionMethod = "BANK";
                    $headId = $samradhBankdata->account_head_id;       
                    $ptd = 'DR';
                    $ptc = 'CR';
                    $description_dr                = "" . $employeename . " A/C Dr " . $amount . " /-";
                    $branch_daybook_payment_type   = "DR";
                    $branch_daybook_description_cr = "To " . $samradhBankdata->bank_name."(".$acc_number->account_no.") A/C Cr " . $amount . "/-";
                    $entryDateb = date('Y-m-d',strtotime(convertDate($select_date)));
                    $getData =\App\Models\BankBalance::whereBankId($bank_id)->whereAccountId($account_id)->Where('entry_date','<=',$entryDateb)->sum('totalAmount');
                    if (($getData) <= $request['aamount']) {
                        return response()->json([
                            'message' => 'berror',
                            'redirect' => back()
                        ], 200);
                    }
                }

                if ($payment_mode) {

                    /*This function take 2 parameter amount and timestemp and 
                    Create a entry in branch_daybook_reference Table and then You recived "daybook_ref_id" */
                    $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $created_at);

                    $daybookRefId = $daybookRef;


                    // Getting Advance transection id and insert a entry to Advance Payment Table.


                    $empAdvance = AdvancedTransaction::where('id', $request['advanceTranserctionId'])->first();


                    // dd($empAdvance);

                    $type_transaction_id = NULL;
                    // $input['type']                 =  $type;
                    // $input['sub_type']             =  $sub_type;
                    // $input['type_id']              =  $type_id;
                    $input['type_transaction_id']  =  $type_transaction_id;
                    $input['branch_id']            =  $branch_id;
                    $input['amount']               =  $amount;
                    $input['status']               =  1;
                    $input['description']          =  $descriptionTitle;
                    $input['payment_type']         =  'DR';
                    $input['payment_mode']         =  $paymentMode;
                    $input['currency_code']        =  'INR';
                    $input['v_no']                 =  null;
                    $input['entry_date']           =  $created_at;
                    $input['entry_time']           = date("H:i:s");
                    $input['created_by']           = Auth::user()->id;
                    $input['created_by_id']        = Auth::user()->id; /*Admin id*/
                    $input['created_at']           = $created_at;
                    $input['updated_at']           = $created_at;
                    $input['daybook_ref_id']       = $daybookRefId;


                    $empAdvance->update($input);

                    // dd($empAdvance);
                    // Geting advance transection id


                    $type_transaction_id = $empAdvance->id;



                    // Getting Branch Name
                    $branch_data = Branch::where('id', $branch_id)->first();
                    $branch_name = $branch_data->name;

                    // Create description for branch daybook
                    $branch_daybook_description = $AhtDesc   = $employeename ." Advance payment " . $amount ." /- by ".$transectionMethod;
                    $branch_daybook_payment_mode   = $paymentMode;


                    // Assign value to variable so it could be used in createAllHeadTransaction entry. 
                    $daybook_ref_id               = $daybookRefId;
                    $branch_id                    = $branch_id;
                    $bank_ac_id                   = getSamraddhBankAccount($bankAccountNumber)->id ?? NULL;
                    $associate_id                 = null;
                    $member_id                    = $member_id;
                    $branch_id_to                 = null;
                    $branch_id_from               = null;
                    $opening_balance              = $barnch_opening_balance;
                    $closing_balance              = $barnch_closing_balance;
                    // $description                  = $transectionMethod . " received from " . $branch_name . " for" . $descriptionTitle;
                    $description                  = $AhtDesc;
                    $payment_type                 = 'DR';
                    $payment_mode                 = $paymentMode;
                    $currency_code                = "INR";
                    $amount_to_id                 = null;
                    $amount_from_id               = $branch_id;
                    $amount_to_name               = null;
                    $amount_from_name             = getBranchDetail($branch_id)->name ?? NULL;
                    $jv_unique_id                 = null;
                    $v_no                         = null;
                    $v_date                       = null;
                    $ssb_account_id_from          = null;
                    $ssb_account_id_to            = null;
                    $ssb_account_tran_id_to       = null;
                    $ssb_account_tran_id_from     = null;
                    $cheque_type                  = $checkData->type ?? NULL; ////cheque
                    $cheque_id                    = $chequeid ?? NULL; ////cheque
                    $cheque_no                    = $checkdetails->cheque_no ?? NULL; ////cheque
                    $cheque_date                  = $checkdetails->cheque_create_date ?? NULL; ////cheque //ASK TO MAM IT'S RIGHT OR NOT
                    $cheque_bank_from             = null;
                    $cheque_bank_ac_from          = null;
                    $cheque_bank_ifsc_from        = null;
                    $cheque_bank_branch_from      = null;
                    $cheque_bank_from_id          = null;
                    $amount_from_name             = null;
                    $cheque_bank_ac_from_id       = null;
                    $cheque_bank_to               = null;
                    $cheque_bank_ac_to            = null;
                    $cheque_bank_to_name          = null;
                    $cheque_bank_to_branch        = null;
                    $cheque_bank_to_ac_no         = null;
                    $cheque_bank_to_ifsc          = null;
                    $transction_no                = null; ///
                    $transction_bank_from         = getSamraddhBank($bank_id)->bank_name ?? NULL;
                    $transction_bank_ac_from      = $bankAccountNumber;
                    $transction_bank_ifsc_from    = getSamraddhBankAccount($bankAccountNumber)->ifsc_code ?? NULL;
                    $transction_bank_branch_from  = null;
                    $transction_bank_from_id      = getSamraddhBank($bank_id)->id ?? NULL;
                    $transction_bank_from_ac_id   = getSamraddhBankAccount($bankAccountNumber)->id ?? NULL;
                    $transction_bank_to           = null;
                    $transction_bank_ac_to        = null;
                    $transction_bank_to_name      = ($bankTransferMode == 1) ? $employeename : NULL; ///online
                    $transction_bank_to_ac_no     = ($bankTransferMode == 1) ? $bank_account_number : NULL; ///online
                    $transction_bank_to_branch    = NULL; ///online
                    $transction_bank_to_ifsc      = ($bankTransferMode == 1) ? $bank_account_ifsc : NULL; ///online
                    $transction_date              = $created_at;
                    $entry_date                   = $created_at;
                    $entry_time                   = date("H:i:s");
                    $is_contra                    = null;
                    $contra_id                    = null;
                    $updated_at                   = $created_at;
                    $created_by                   = 1;
                    $created_by_id                = Auth::user()->id;
                    $amount_type                  = 0;    //0:micro;1:loan


                    // Disable check
                    $checkN = isset($checkdetails->cheque_no);
                    if ($checkN) {
                        SamraddhCheque::where('cheque_no', $checkdetails->cheque_no)->update(['is_use' => 1]);
                    }
                }
                // save entry for ta advance CR 
                $allHeadTransaction = CommanController::newHeadTransactionCreate(
                    $daybook_ref_id,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $headId,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id,
                    $member_id,
                    $branch_id_to,
                    $branch_id_from,
                    $amount,
                    $AhtDesc,
                    $ptc,
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
                    $company_id
                );
                $newHead = \App\Models\AdvancedTransaction::getHeadId($request['payment_type']);


                // save entry for ta advance TA advance head id 72
                $allHeadTransaction2 = CommanController::newHeadTransactionCreate(
                    $daybook_ref_id,
                    $branch_id,
                    $bank_id,
                    $bank_ac_id,
                    $PheadId,
                    $type,
                    $sub_type,
                    $type_id,
                    $associate_id,
                    $member_id,
                    $branch_id_to,
                    $branch_id_from,
                    $amount,
                    $AhtDesc,
                    $ptd,
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
                    $company_id
                );
                if (!empty($neftcharges)) {

                    $amountNeft =  $neftcharges;
                    $branch_daybook_description_charges    = "" . $employeename . " A/C CR " . $amountNeft . " - To " . $branch_name . " A/C Dr" . $descriptionTitle;
                    $description_charges_dr                = "" . $branch_name . " A/C Dr " . $amountNeft . "";

                    $branch_daybook_description_mainAmount    = "" . $employeename . " A/C CR " . $amount . " - To " . $branch_name . " A/C Dr" . $descriptionTitle;
                    $description_mainAmount_dr                = "" . $branch_name . " A/C Dr " . $amount . "";




                    // save entry to Branch daybook in neft case
                    $saveinBranchDaybook = CommanController::branchDaybookCreateModified($daybook_ref_id, 29, $type, $NeftsubType, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amountNeft, $branch_daybook_description_charges,  $description_charges_dr, '', 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id);



                    // save entry to Branch daybook in neft case main amount
                    $saveinBranchDaybook = CommanController::branchDaybookCreateModified($daybook_ref_id, $branch_id, $type, $sub_type2, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $branch_daybook_description_mainAmount, $description_mainAmount_dr, '', 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id);

                    CommanController::newHeadTransactionCreate(
                        $daybook_ref_id,
                        29,
                        $bank_id,
                        $bank_ac_id,
                        $samradhBankdata->account_head_id,
                        $type,
                        $sub_type,
                        $type_id,
                        $associate_id,
                        $member_id,
                        $branch_id_to,
                        $branch_id_from,
                        $amountNeft,
                        'NEFT CHARGE',
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
                        $company_id
                    );


                    $samradhdaybook = CommanController::NewFieldAddSamraddhBankDaybookCreate(
                        $daybook_ref_id,
                        $bank_id,
                        $account_id,
                        $type,
                        $sub_type,
                        $type_id,
                        $associate_id,
                        $member_id,
                        29,
                        $amountNeft,
                        $amountNeft,
                        $amountNeft,
                        'NEFT CHARGE',
                        'NEFT CHARGE',
                        'NEFT CHARGE',
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
                        $ssb_account_tran_id_to,
                        $ssb_account_tran_id_from,
                        $jv_unique_id,
                        $cheque_type,
                        $cheque_id,
                        $company_id
                    );
                } else {
                    // $descriptionn = 
                    //new daybook entry to Branch daybook
                    $saveinBranchDaybook = CommanController::branchDaybookCreateModified($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $branch_daybook_description_cr, $branch_daybook_payment_type, $branch_daybook_payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id);
                }


                // Update employee Ta advance balance
                if ($request['paymentType'] == 1) {
                    $employee_data = Employee::find($employee_id);
                    $employee_data->advance_payment = $amount + $employee_data->advance_payment;
                    $employee_data->update();
                } else if ($request['paymentType'] == 2) {
                    $employee_data = Employee::find($employee_id);
                    $employee_data->ta_advance_payment = $amount + $employee_data->ta_advance_payment;
                    $employee_data->update();
                } else {
                }


                if ($request['paymentType'] == 1 || $request['paymentType'] == 2) {

                    // Update employee Ledgers table
                    $employeeLedger = new EmployeeLedger;
                    $employeeLedger->employee_id = $employee_id;
                    $employeeLedger->branch_id = $branch_id;
                    $employeeLedger->type =  $EledgerType;
                    $employeeLedger->type_id = $type_transaction_id;
                    $employeeLedger->opening_balance = null;
                    $employeeLedger->deposit = $amount;
                    $employeeLedger->withdrawal = null;
                    $employeeLedger->description = $descriptionTitle;
                    $employeeLedger->currency_code = $currency_code;
                    $employeeLedger->payment_type = "CR";
                    $employeeLedger->payment_mode = $paymentMode;
                    $employeeLedger->status = 1;
                    $employeeLedger->created_at = $created_at;
                    $employeeLedger->updated_at = $created_at;
                    $employeeLedger->jv_unique_id = NULL;
                    $employeeLedger->v_no = NULL;
                    $employeeLedger->v_date = NULL;
                    $employeeLedger->ssb_account_id_to = NULL;
                    $employeeLedger->ssb_account_id_from = NULL;
                    $employeeLedger->to_bank_name = NULL;
                    $employeeLedger->to_bank_branch = NULL;
                    $employeeLedger->to_bank_ac_no = NULL;
                    $employeeLedger->to_bank_ifsc = NULL;
                    $employeeLedger->to_bank_id = NULL;
                    $employeeLedger->to_bank_account_id = NULL;
                    $employeeLedger->from_bank_name = NULL;
                    $employeeLedger->from_bank_branch = NULL;
                    $employeeLedger->from_bank_ac_no = NULL;
                    $employeeLedger->from_bank_ifsc = NULL;
                    $employeeLedger->from_bank_id = NULL;
                    $employeeLedger->from_bank_ac_id = NULL;
                    $employeeLedger->cheque_id = NULL;
                    $employeeLedger->cheque_no = NULL;
                    $employeeLedger->cheque_date = NULL;
                    $employeeLedger->transaction_no = NULL;
                    $employeeLedger->transaction_date = NULL;
                    $employeeLedger->transaction_charge = NULL;
                    $employeeLedger->jv_journal_id = NULL;
                    $employeeLedger->banking_id = NULL;
                    $employeeLedger->is_deleted = 0;
                    $employeeLedger->daybook_ref_id = $daybookRefId;
                    $employeeLedger->reference_no = NULL;
                    $employeeLedger->company_id = $company_id;

                    $employeeLedger->save();
                }


                // if ($request['payment_mode'] == "CASH") {
                //     // Branch cash table entry check if current date entry exists then update otherwise create
                //     $created_at = $request['created_at'];
                //     if ($current_date == $entry_date) {
                //         $branchCash = CommanController::checkCreateBranchCashDR($branch_id, $created_at, $amount, 0);
                //     } else {
                //         $branchCash = CommanController::createBaranchCashBackDateDR($branch_id, $created_at, $amount, 0);
                //     }
                // }

                if ($request['payment_mode'] == "SSB") {


                    $savig = SavingAccount::where('account_no', $request['ssbno'])->first();
                    $memberId = $savig->member_id;

                  
                    // create a entry to saving account transection table
                    $descriptionForTa   = $descriptionTitle;
                    $payment_type       = "CR";
                    $type               = 31; //transection type need to ask to mam and then change.
                    $date               = $select_date;


                    //SSB transaction entry
                    $ssbTransectionnew = CommanController::SSBDateCRNew($employee_ssb_account_id, $ssbAccountNo, $employee_ssb_account_balance, $amount, $descriptionForTa, $currency_code, $payment_type, $paymentMode, $branch_id, $associate_id, $type, $date, $company_id);

                    //ssb balance update

                    $ssbTransectionnew2 = CommanController::SSBBackDateCRNew($employee_ssb_account_id, $date, $amount);
                }

                if ($request['payment_mode'] == "BANK") {

                    $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<=', $entry_date)->where('company_id', $company_id)->orderby('entry_date', 'desc')->sum('totalAmount');
                    if ($bankBla) {
                        if ($request->total_transfer_amount > $bankBla) {
                            return redirect('admin/advancePayment/' . $request->u_id . '/' . $request->u_paymenttype)->with('alert', 'Sufficient amount not available in bank account!');
                        }
                    } else {
                        return redirect('admin/advancePayment/' . $request->u_id . '/' . $request->u_paymenttype)->with('alert', 'Sufficient amount not available in bank account!');
                    }


                    // Create a entry in samradh daybook
                    $description_cr = $branch_daybook_description_cr;
                    $samradhdaybook = CommanController::NewFieldAddSamraddhBankDaybookCreate($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);

                    // Update samradh bank closing
                    $bank_id_from = $bank_id;
                    $bank_ac_id_from = $account_id;

                    // if ($current_date == $entry_date) {
                    //     $bankClosing = CommanController::checkCreateBankClosingDR($bank_id_from, $bank_ac_id_from, $created_at, $amount, 0);
                    // } else {
                    //     $bankClosing = CommanController::checkCreateBankClosingDRBackDate($bank_id_from, $bank_ac_id_from, $created_at, $amount, 0);
                    // }

                    // dd($chequeid);
                    // Transfer Type Cheque
                    if ($bankTransferMode == 0) {
                        // create a Entry to Samraddh Cheque Table
                        $samradhCheckNew  =  new SamraddhCheque;
                        $samradhCheckNew->bank_id            = $bank_id;
                        $samradhCheckNew->cheque_book_id     = $chequeid;
                        $samradhCheckNew->account_id         = $account_id;
                        $samradhCheckNew->cheque_no          = $checkdetails->cheque_no;
                        $samradhCheckNew->cheque_create_date = $entry_date;
                        $samradhCheckNew->is_use             = 1;
                        $samradhCheckNew->status             = 3;
                        $samradhCheckNew->cheque_delete_date = NULL;
                        $samradhCheckNew->cheque_cancel_date = NULL;
                        $samradhCheckNew->remark_cancel      = NULL;
                        $samradhCheckNew->cheque_reissue_id  = NULL;
                        $samradhCheckNew->save();

                        // Create a Entry to Samraddh Cheque issue table
                        $samradhCheckissue = new SamraddhChequeIssue;
                        $samradhCheckissue->cheque_id         = $chequeid;
                        $samradhCheckissue->type              = $type;
                        $samradhCheckissue->sub_type          = $sub_type;
                        $samradhCheckissue->type_id           = $type_id;
                        $samradhCheckissue->cheque_issue_date = $entry_date;
                        $samradhCheckissue->cheque_cancel_date = null;
                        $samradhCheckissue->status = 1;
                        $samradhCheckissue->save();
                    }

                    // Transfer Type Online Transfer
                    if ($bankTransferMode == 1) {
                        $amountwithcharges = $neftcharges;
                        if (!empty($neftcharges)) {
                            $allHeadTransaction3 = CommanController::newHeadTransactionCreate($daybook_ref_id, 29, $bank_id, $bank_ac_id, 92, $type, $NeftsubType, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amountwithcharges, 'NEFT CHARGE', 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        }
                    }
                }
                if ($request['paymentType'] == 0) {


                    $rentownerId = $request['advanced_rent_party_name'];
                    // Update Rent Liabilities table

                    $rentLiability = RentLiability::find($ownerId);

                    $rentLiability->advance_payment = $rentLiability->advance_payment + $request['aamount'];
                    $rentLiability->update();

                    $rentLiabilityId = $rentLiability->id;
                    $rentLedgeramount = $request['aamount'];
                    $rentLedgerdate = $request['date'];
                    $date_obj = Carbon::createFromFormat('d/m/Y', $rentLedgerdate);
                    // Update Rent Ledger table

                    $rentLedger = CommanController::rentLedgerBackDateCR($rentLiabilityId, $date_obj, $rentLedgeramount);

                    // Entry To Rent Libility Ledger 
                    $IfResultNull = \App\Models\RentLiabilityLedger::where('rent_liability_id', $rentLiabilityId)->first();
                    $date = Carbon::parse($date_obj);
                    $formattedDate = $date->format('F Y');

                    if (isset($request['payment_mode'])) {
                        if ($request['payment_mode'] == "CASH") {
                            $paymentMode = 0;
                        } else if ($request['payment_mode'] == "BANK") {
                            if ($transfer_mode == 0) {
                                $paymentMode = 1;
                            } else {
                                $paymentMode = 2;
                            }
                        } else {
                            $paymentMode = 3;
                        }
                    }


                    $rentLiability                    = new RentLiabilityLedger;
                    $rentLiability->rent_liability_id = $rentLiabilityId;
                    $rentLiability->type              = 2;
                    $rentLiability->type_id           = $employee_id;
                    $rentLiability->deposit           = $amount;
                    $rentLiability->description       = "Advance Rent Payment";
                    $rentLiability->currency_code     = $currency_code;
                    $rentLiability->payment_type      = "CR";
                    $rentLiability->payment_mode      = $paymentMode;
                    $rentLiability->status            = 1;
                    $rentLiability->created_at        = date("Y-m-d", strtotime(convertDate($request['date'])));
                    $rentLiability->daybook_ref_id    = $daybook_ref_id;
                    $rentLiability->company_id    = $company_id;
                    $rentLiability->save();
                    DB::commit();
                    return response()->json(['message' => 'Advance ' . $transectionMethod . ' entry saved successfully', 'success' => 'true', 'redirect' => route('admin.advancePayment.requestList')], 200);
                }



                DB::commit();
                return response()->json(['message' => 'Ta advance ' . $transectionMethod . ' entry saved successfully', 'success' => 'true', 'redirect' => route('admin.advancePayment.requestList')], 200);
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function addAdjestment($id)
    {
        $data['advancePayment'] = AdvancedTransaction::where('id', $id)->first(['id', 'amount', 'used_amount', 'updated_at', 'type_id', 'branch_id']);
        $data['branchName']     = getBranchDetail($data['advancePayment']['branch_id'])->name ?? NULL;
        $data['employee']       = Employee::with(['getSsb' => function ($query) {
            $query->select('id', 'account_no', 'member_id');
        }])->where('id', $data['advancePayment']['type_id'])->first(['id', 'employee_code', 'employee_name']);
        $title                  = 'Advance |  Adjustment';
        $data['branches']       = Branch::select('id', 'name')->where('status', 1)->get();
        $data['account_heads']  = AccountHeads::select('id', 'head_id', 'sub_head')->whereIn('parent_id', [4, 9])->get();
        $data['bank']           = \App\Models\SamraddhBank::select('id', 'bank_name')->where('status', 1)->get();
        return view('templates/branch/AdvancePayment/add_adjestment', compact('data', 'title'));
    }

    public function getOwnerNames(Request $request)
    {
        $getBranchId=getUserBranchId(Auth::user()->id);
        $branchId    =  $getBranchId->id;
        $company_id = $request->company_id;
        $ownerDetails = RentLiability::select('id', 'owner_name')->where('company_id',$company_id)->where('branch_id', $branchId)->where('status', 0)->get();
        $return_array = compact('ownerDetails');
        return json_encode($return_array);
    }
    public function getemployeee(Request $request)
    {
        $branchId = $request->branch;
        $company_id = $request->company_id;
        $employedata = Employee::select('employee_code', 'employee_name')->where('company_id', $company_id)->where('branch_id', $branchId)->where('is_employee',1)->where('is_resigned',0)->where('is_terminate',0)->where('status',1)->get();
        $return_array = compact('employedata');
        return json_encode($return_array);
    }
    public function get_expense(Request $request)
    {
        $account_heads = AccountHeads::whereIn('parent_id', [4, 9])->get();
        return response()->json($account_heads);
    }

    public function get_indirect_expense_sub_head(Request $request)
    {

        $id = $request->id ?? 'N/A';
        $name = $request->name ?? 'N/A';
        $head_id = $request->head_id ?? 'N/A';

        if ($id == '1' && $name == "expence") {
            $account_heads = AccountHeads::whereParent_id(86)->whereStatus(0)->get();
        } else if ($id == '2' && $name == "fixedasset") {
            $account_heads = AccountHeads::whereParent_id(9)->whereStatus(0)->get();
        } else {
            if (!empty($head_id == '2' && $name == "fixedasset")) {
                $account_heads = AccountHeads::whereParent_id(9)->whereStatus(0)->get();
            } else if (!empty($head_id  == '1' && $name == "expence")) {
                $account_heads = AccountHeads::whereParent_id(86)->whereStatus(0)->get();
            } else {
                $account_heads = AccountHeads::whereParent_id($head_id)->whereStatus(0)->get();
            }
        }

        $return_array = compact('account_heads');
        return json_encode($return_array);
    }

    public function addAdjestmentSave(Request $request)
    {
        $rules = [
            'account_head' => ['required'],
            'branch_id' => ['required'],
            'amount' => ['required'],


        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        $created_at              = $request->created_at;
        $create_application_date = $request->create_application_date;
        $employeename            = $request->employeename;
        $employeecode            = $request->employeecode;
        $employeeid              = $request->id;
        $AdvanceTransectionId    = $request->id;
        $approveAmount           = $request->approveAmount;
        $adjestmentdate          = $request->adjestmentdate;
        $branch_id               = $request->branch_id;
        $account_head            = $request->account_head;
        $sub_head1               = $request->sub_head1;
        $sub_head2               = $request->sub_head2;
        $amount                  = $request->amount;
        $description             = $request->description;
        $adjdate                 = $request->adjdate;
        $adjId                   = $request->id;
        $total_amount            = $request->total_amount;


        //------------------------------------------------
        DB::beginTransaction();
        try {

            /*This function take 2 parameter amount and timestemp and 
                Create a entry in branch_daybook_reference Table and then You recived "daybook_ref_id" */
            // $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $created_at);

            // $$daybook_ref_id = $daybookRef;

            $data = [];
            $rowdata = $request->account_head_more;
            if ($rowdata) {
                $count = count($rowdata);
            }
            $branchName = getBranchDetail($branch_id)->name ?? NULL;


            $type                         = 31;
            $sub_type                     = 312;
            $type_id                      = $employeeid; //according if member then member id if rent employee id
            $bank_ac_id                   = null;
            $associate_id                 = null;
            $member_id                    = NULL;
            $type_transaction_id          = NULL;
            $branch_id_to                 = NULL;
            $bank_id                      = NULL;
            $branch_id_from               = null;
            $opening_balance              = null;
            $closing_balance              = null;
            $description_dr               = null;
            $branch_daybook_description_cr = null;
            $payment_type                 = 'DR';
            $payment_mode                 =   0;
            $currency_code                = "INR";
            $amount_to_id                 = null;
            $amount_from_id               = $branch_id;
            $description                  = "received from " . $branchName . " Branch for advance TA Imprest";
            $amount_to_name               = null;
            $amount_from_name             = NULL;
            $jv_unique_id                 = null;
            $v_no                         = null;
            $v_date                       = null;
            $ssb_account_id_from          = null;
            $ssb_account_id_to            = null;
            $ssb_account_tran_id_to       = null;
            $ssb_account_tran_id_from     = null;
            $cheque_type                  = NULL;
            $cheque_id                    = NULL;
            $cheque_no                    = NULL;
            $cheque_date                  = NULL;
            $cheque_bank_from             = null;
            $cheque_bank_ac_from          = null;
            $cheque_bank_ifsc_from        = null;
            $cheque_bank_branch_from      = null;
            $cheque_bank_from_id          = null;
            $amount_from_name             = null;
            $cheque_bank_ac_from_id       = null;
            $cheque_bank_to               = null;
            $cheque_bank_ac_to            = null;
            $cheque_bank_to_name          = null;
            $cheque_bank_to_branch        = null;
            $cheque_bank_to_ac_no         = null;
            $cheque_bank_to_ifsc          = null;
            $transction_no                = null;
            $transction_bank_from         = null;
            $transction_bank_ac_from      = null;
            $transction_bank_ifsc_from    = NULL;
            $transction_bank_branch_from  = null;
            $transction_bank_from_id      = NULL;
            $transction_bank_from_ac_id   = NULL;
            $transction_bank_to           = null;
            $transction_bank_ac_to        = null;
            $transction_bank_to_name      = NULL;
            $transction_bank_to_ac_no     = NULL;
            $transction_bank_to_branch    = NULL;
            $transction_bank_to_ifsc      = NULL;
            $transction_date              = $created_at;
            $entry_date                   = $created_at;
            $entry_time                   = date("H:i:s");
            $is_contra                    = null;
            $contra_id                    = null;
            $updated_at                   = $created_at;
            $created_by                   = 1;
            $created_by_id                = Auth::user()->id;
            $amount_type                  = 0;    //0:micro;1:loan



            $data1 = [
                'account_head_more' => $request->account_head ?? NULL,
                'sub_head1_more'    => $request->sub_head1 ?? NULL,
                'sub_head2_more'    => $request->sub_head2 ?? NULL,
                'sub_head3_more'    => $request->sub_head3 ?? NULL,
                'sub_head4_more'    => $request->sub_head4 ?? NULL,
                'sub_head5_more'    => $request->sub_head5 ?? NULL,
                'amount_more'       => $request->amount ?? NULL,
                'description_more'  => $request->description ?? NULL,
                'date_more'         => $request->date ?? $request->adjdate,
                'total_amount'      => $request->total_amount ?? NULL,
            ];

            array_push($data, $data1);

            if (isset($request->account_head_more)) {
                for ($i = 0; $i < $count; $i++) {

                    $data2 = [
                        'account_head_more' => $request->account_head_more[$i] ?? NULL,
                        'sub_head1_more'    => $request->sub_head1_more[$i] ?? NULL,
                        'sub_head2_more'    => $request->sub_head2_more[$i] ?? NULL,
                        'sub_head3_more'    => $request->sub_head3_more[$i] ?? NULL,
                        'sub_head4_more'    => $request->sub_head4_more[$i] ?? NULL,
                        'sub_head5_more'    => $request->sub_head5_more[$i] ?? NULL,
                        'amount_more'       => $request->amount_more[$i] ?? NULL,
                        'description_more'  => $request->description_more[$i] ?? NULL,
                        'date_more'         => $request->date_more[$i] ?? NULL,
                        'total_amount'      => $request->total_amount[$i] ?? NULL,
                    ];

                    array_push($data, $data2);
                }
            }

            foreach ($data as $value) {

                /*This function take 2 parameter amount and timestemp and 
                Create a entry in branch_daybook_reference Table and then You recived "daybook_ref_id" */
                $daybookRef = CommanController::createBranchDayBookReferenceNew($value['amount_more'], $created_at);

                $daybook_ref_id = $daybookRef;



                if ($value['sub_head4_more']) {
                    $head = $value['sub_head4_more'];
                } elseif ($value['sub_head3_more']) {
                    $head = $value['sub_head3_more'];
                } elseif ($value['sub_head2_more']) {
                    $head = $value['sub_head2_more'];
                } elseif ($value['sub_head1_more']) {
                    $head = $value['sub_head1_more'];
                } elseif ($value['account_head_more']) {
                    $head = $value['account_head_more'];
                }


                // save entry to ta settlement table

                TaSettlment::create([
                    'daybook_ref_id' => $daybook_ref_id,
                    'advanced_transection_id' => $AdvanceTransectionId,
                    'account_head' => $value['account_head_more'],
                    'head_1' => $value['sub_head1_more'] ?? 0,
                    'head_2' => $value['sub_head2_more'] ?? 0,
                    'head_3' => $value['sub_head3_more'] ?? 0,
                    'head_4' => $value['sub_head4_more'] ?? 0,
                    'head_5' => $value['sub_head5_more'] ?? 0,
                    'amount' => $value['amount_more'],
                    'discription' => $value['description_more'],
                    'date' => $value['date_more'],
                ]);


                $parentId = AccountHeads::where('head_id', $head)->first('parent_id');

                $amount  = $value['amount_more'];


                if ($daybook_ref_id) {
                    //new daybook entry to Branch daybook
                    $saveinBranchDaybook = CommanController::NewFieldBranchDaybookCreate($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $branch_daybook_description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $amount_type, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                    $descriptionAllHeadcr = "Recived From" . $branchName . " Branch for advance TA Imprest";

                    // save entry for ta advance TA advance head
                    $allHeadTransaction2 =  CommanController::headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $descriptionAllHeadcr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $descriptionTitleCr = "TA Adjestment Amount CR";

                    // dd($AdvanceTransectionId);
                    $advanT = AdvancedTransaction::find($AdvanceTransectionId);
                    $advanT->used_amount = $advanT->used_amount + $total_amount;
                    $advanT->settlement = 2; //for partial payment
                    $advanT->save();

                    //Employee Ledger Update
                    $employeeLedger = new EmployeeLedger;
                    $employeeLedger->employee_id = $advanT->type_id;
                    $employeeLedger->branch_id = $branch_id;
                    $employeeLedger->type = 7;
                    $employeeLedger->type_id = 318;
                    $employeeLedger->opening_balance = null;
                    $employeeLedger->deposit = $total_amount;
                    $employeeLedger->withdrawal = null;
                    $employeeLedger->description = $descriptionTitleCr;
                    $employeeLedger->currency_code = $currency_code;
                    $employeeLedger->payment_type = "CR";
                    $employeeLedger->payment_mode = 3;
                    $employeeLedger->status = 1;
                    $employeeLedger->created_at = $created_at;
                    $employeeLedger->updated_at = $created_at;
                    $employeeLedger->jv_unique_id = NULL;
                    $employeeLedger->v_no = NULL;
                    $employeeLedger->v_date = NULL;
                    $employeeLedger->ssb_account_id_to = NULL;
                    $employeeLedger->ssb_account_id_from = NULL;
                    $employeeLedger->to_bank_name = NULL;
                    $employeeLedger->to_bank_branch = NULL;
                    $employeeLedger->to_bank_ac_no = NULL;
                    $employeeLedger->to_bank_ifsc = NULL;
                    $employeeLedger->to_bank_id = NULL;
                    $employeeLedger->to_bank_account_id = NULL;
                    $employeeLedger->from_bank_name = NULL;
                    $employeeLedger->from_bank_branch = NULL;
                    $employeeLedger->from_bank_ac_no = NULL;
                    $employeeLedger->from_bank_ifsc = NULL;
                    $employeeLedger->from_bank_id = NULL;
                    $employeeLedger->from_bank_ac_id = NULL;
                    $employeeLedger->cheque_id = NULL;
                    $employeeLedger->cheque_no = NULL;
                    $employeeLedger->cheque_date = NULL;
                    $employeeLedger->transaction_no = NULL;
                    $employeeLedger->transaction_date = NULL;
                    $employeeLedger->transaction_charge = NULL;
                    $employeeLedger->jv_journal_id = NULL;
                    $employeeLedger->banking_id = NULL;
                    $employeeLedger->is_deleted = 0;
                    $employeeLedger->daybook_ref_id = $daybook_ref_id;
                    $employeeLedger->reference_no = NULL;
                    $employeeLedger->save();

                    $descriptionAllHead = "Amount Debit From" . $branchName . " Branch for advance TA Imprest";

                    // save entry for ta advance TA advance head id 72
                    //which branch amount trnasfered 
                    $transferBranchId = $branch_id;
                    $allHeadTransaction2 =  CommanController::headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $transferBranchId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $total_amount, $closing_balance, $descriptionAllHead, 'DR', 3, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);



                    // Employee Table update
                    $employee_data = Employee::find($advanT->type_id);
                    $employee_data->ta_advance_payment = $employee_data->ta_advance_payment - $amount;
                    $employee_data->update();
                }
            }

            DB::commit();
        } catch (\Execption $e) {
            DB::rollback();
            return $e->getMessage();
        }
    }

    public function bankChequeList(Request $request)

    {
        // dd($request->account_id);

        $checkIssued = SamraddhChequeIssue::where('type', '!=', 31)->get(['cheque_id'])->toArray();
        $result = array_column($checkIssued, 'cheque_id');

        // dd($result);

        $chequeListAcc = SamraddhCheque::where('is_use', 0)->where('status', '!=', 0)->where('account_id', $request->account_id)->whereNotIn('cheque_no', $result)->get(['id', 'cheque_no']);

        $return_array = compact('chequeListAcc');

        return json_encode($return_array);
    }

    public function Adjestmentview($id)
    {
        $data['advancePayment'] = AdvancedTransaction::where('id', $id)->first(['id', 'amount', 'used_amount', 'updated_at', 'type_id', 'branch_id']);
        $data['branchName']     = getBranchDetail($data['advancePayment']['branch_id'])->name ?? NULL;
        $data['employee']       = Employee::with(['getSsb' => function ($query) {
            $query->select('id', 'account_no', 'member_id');
        }])->where('id', $data['advancePayment']['type_id'])->first(['id', 'employee_code', 'employee_name']);
        $title                  = 'Advance |  Adjustment';
        $data['branches']       = Branch::select('id', 'name')->where('status', 1)->get();
        $data['account_heads']  = AccountHeads::select('id', 'head_id', 'sub_head')->whereIn('parent_id', [4, 9])->get();
        $data['bank']           = \App\Models\SamraddhBank::select('id', 'bank_name')->where('status', 1)->get();
        $data['TaSettlment']    = \App\Models\TaSettlment::where('advanced_transection_id', $id)->get();

        $data3 = array();

        foreach ($data['TaSettlment'] as $value) {

            $account_head = $value->account_head;
            $sub_head1    = $value->head_1;
            $sub_head2    = $value->head_2;
            $sub_head3    = $value->head_3;
            $sub_head4    = $value->head_4;
            $sub_head5    = $value->head_5;
            $amount       = $value->amount;
            $description  = $value->discription;
            $date         = $value->date;


            if ($value->account_head == 1 || $value->account_head == 4) {
                $name = "expence";
            } else {
                $name = "fixedasset";
            }


            if ($sub_head1 > 0) {
                $googl = Request2::instance();
                $googl->id = $id;
                $googl->name = $name;
                $googl->value = $value->head_1;

                if ($googl->value) {
                    $result = json_decode($this->get_indirect_expense_sub_head($googl));
                    $head1 = $result->account_heads[0]->sub_head;
                } else {
                    $head1 = "";
                }
            }

            if ($sub_head2 > 0) {
                $googl = Request2::instance();
                $googl->id = $id;
                $googl->name = $name;
                $googl->value = $value->head_2;

                if ($googl->value) {
                    $result = json_decode($this->get_indirect_expense_sub_head($googl));
                    $head2 = $result->account_heads[0]->sub_head ?? '';
                } else {
                    $head2 = "";
                }
            }

            if ($sub_head3 > 0) {
                $googl = Request2::instance();
                $googl->id = $id;
                $googl->name = $name;
                $googl->value = $value->head_3;

                if ($googl->value) {
                    $result = json_decode($this->get_indirect_expense_sub_head($googl));
                    $head3 = $result->account_heads[0]->sub_head;
                } else {
                    $head3 = "";
                }
            }

            if ($sub_head4 > 0) {
                $googl = Request2::instance();
                $googl->id = $id;
                $googl->name = $name;
                $googl->value = $value->head_4;

                if ($googl->value) {
                    $result = json_decode($this->get_indirect_expense_sub_head($googl));
                    $head4 = $result->account_heads[0]->sub_head;
                } else {
                    $head4 = "";
                }
            }

            if ($sub_head5 > 0) {
                $googl = Request2::instance();
                $googl->id = $id;
                $googl->name = $name;
                $googl->value = $value->head_5;

                if ($googl->value) {
                    $result = json_decode($this->get_indirect_expense_sub_head($googl));
                    $head5 = $result->account_heads[0]->sub_head;
                } else {
                    $head5 = "";
                }
            }






            $data33 = [
                'account_head' => $name,
                'sub_head1name' => $head1,
                'sub_head2name' => $head2 ?? '',
                'sub_head3name' => $head3 ?? '',
                'sub_head4name' => $head4 ?? '',
                'sub_head5name' => $head5 ?? '',
                'amount'      => $value->amount,
                'description' => $value->discription,
                'date'        => $value->date,
            ];

            array_push($data3, $data33);
        }

        return view('templates/branch/AdvancePayment/viewAdjustment', compact('data', 'title', 'data3'));
    }

    // datatable Payment listing page
    public function AdjListingtable(Request $request)
    {
        $id = $request->id;
        $data['TaSettlment']    = \App\Models\TaSettlment::where('advanced_transection_id', $id);

        $data3 = array();
        $rowReturn = array();
        $data1 = $data['TaSettlment']->count('id');
        $count = $data1;
        $data = $data['TaSettlment']->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
        $totalCount =  $count;
        $sno = $_POST['start'];
        foreach ($data as $value) {
            $account_head = $value->account_head;
            $sub_head1    = $value->head_1;
            $sub_head2    = $value->head_2;
            $sub_head3    = $value->head_3;
            $sub_head4    = $value->head_4;
            $sub_head5    = $value->head_5;
            $amount       = $value->amount;
            $description  = $value->discription;
            $date         = $value->date;
            $head1 = "";
            $head2 = "";
            $head3 = "";
            $head4 = "";
            $head5 = "";

            if ($value->account_head == 1 || $value->account_head == 4) {
                $name = "expence";
            } else {
                $name = "fixed asset";
            }


            if ($sub_head1 > 0) {
                // $googl = Request2::instance();
                // $googl->id = $id;
                // $googl->name = $name;
                // $googl->value = $value->head_1;

                // if ($googl->value) {
                //     $result = json_decode($this->get_indirect_expense_sub_head($googl));
                //     $head1 = $result->account_heads[0]->sub_head;
                // } else {
                //     $head1 = "";
                // }
                $head1 = AccountHeads::select('head_id', 'sub_head')->where('head_id',$sub_head1)->first();
                $head1 = $head1->sub_head ?? "";
            }

            if ($sub_head2 > 0) {
                // $googl = Request2::instance();
                // $googl->id = $id;
                // $googl->name = $name;
                // $googl->value = $value->head_2;

                // if ($googl->value) {
                //     $result = json_decode($this->get_indirect_expense_sub_head($googl));
                //     $head2 = $result->account_heads[0]->sub_head ?? '';
                // } else {
                //     $head2 = "";
                // }
                $head2 = AccountHeads::select('head_id', 'sub_head')->where('head_id',$sub_head2)->first();
                $head2 = $head2->sub_head ?? "";
            }

            if ($sub_head3 > 0) {
                // $googl = Request2::instance();
                // $googl->id = $id;
                // $googl->name = $name;
                // $googl->value = $value->head_3;

                // if ($googl->value) {
                //     $result = json_decode($this->get_indirect_expense_sub_head($googl));
                //     $head3 = $result->account_heads[0]->sub_head;
                // } else {
                //     $head3 = "";
                // }
                $head3 = AccountHeads::select('head_id', 'sub_head')->where('head_id',$sub_head3)->first();
                $head3 = $head3->sub_head ?? "";
            }

            if ($sub_head4 > 0) {
                // $googl = Request2::instance();
                // $googl->id = $id;
                // $googl->name = $name;
                // $googl->value = $value->head_4;

                // if ($googl->value) {
                //     $result = json_decode($this->get_indirect_expense_sub_head($googl));
                //     $head4 = $result->account_heads[0]->sub_head;
                // } else {
                //     $head4 = "";
                // }
                $head4 = AccountHeads::select('head_id', 'sub_head')->where('head_id',$sub_head4)->first();
                $head4 = $head4->sub_head ?? "";
            }

            if ($sub_head5 > 0) {
                // $googl = Request2::instance();
                // $googl->id = $id;
                // $googl->name = $name;
                // $googl->value = $value->head_5;

                // if ($googl->value) {
                //     $result = json_decode($this->get_indirect_expense_sub_head($googl));
                //     $head5 = $result->account_heads[0]->sub_head;
                // } else {
                //     $head5 = "";
                // }
                $head5 = AccountHeads::select('head_id', 'sub_head')->where('head_id',$sub_head5)->first();
                $head5 = $head5->sub_head ?? "";
            }
            $i = 0;
            $i++;
            $image = "N/A";
            if ($value->image) {
                // $image = URL::to("/asset/taAdjustment/$value->image");
                // $image = '<a href="' . $image . '" target="_blank"><i class="fas fa-eye mr-2s"></i></a>';
                $folderName = 'taAdjustment/'.$value->image;
                $url = ImageUpload::generatePreSignedUrl($folderName);
                $image = '<a href="' . $url . '" target="_blank"><i class="fas fa-eye mr-2s"></i></a>';

            }
            $data33 = [
                'Sno' => $i,
                'account_head' => $name,
                'sub_head1name' => $head1,
                'sub_head2name' => $head2 ?? '',
                'sub_head3name' => $head3 ?? '',
                'sub_head4name' => $head4 ?? '',
                'sub_head5name' => $head5 ?? '',
                'amount'      => $value->amount ?? 'N/A',
                'description' => $value->discription ?? 'N/A',
                'image' => $image ?? 'N/A',
                'date'        => $value->date ?? 'N/A',
            ];
            array_push($data3, $data33);
        }

        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $data3);
        return json_encode($output);
    }
    public function bankChkbalance(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $bank_id_from_c = $request->bank_id;
        $companyId = $request->companyId;
        $bank_ac_id_from_c = $request->account_id;
        // $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereBetween('entry_date', ['2021-08-05', $entry_date])->sum('totalAmount');
        $bankBla = \App\Models\BankBalance::select('id', 'balance', 'bank_id')->where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->sum('totalAmount');
        $balance = '0.00';
        $create_date = '';
        if ($bankBla) {
            $balance = number_format((float) $bankBla, 2, '.', '');
        }
        $getRecord = \App\Models\SamraddhBank::select('id', 'bank_name', 'created_at', 'company_id')->where('id', $bank_id_from_c)->whereCompanyId($companyId)->first();
        if ($getRecord) {
            $create_date = date("d/m/Y", strtotime($getRecord->created_at));
        }
        $return_array = compact('balance', 'create_date');
        return json_encode($return_array);
    }
    public function companyDate(Request $request)
    {
        $return = \App\Models\Companies::where('id',$request->company_id)->first('created_at')->created_at;
        $return = date('d/m/Y', strtotime(convertDate($return)));
        return json_encode($return);
    }
    
}
