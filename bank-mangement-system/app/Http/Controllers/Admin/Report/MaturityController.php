<?php
namespace App\Http\Controllers\Admin\Report;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DemandAdvice;
use App\Models\Branch;
use App\Models\Plans;
use App\Models\Memberinvestments;
use Carbon\Carbon;
use DB;

/*
|---------------------------------------------------------------------------
| Admin Panel -- Member Management MemberController
|--------------------------------------------------------------------------
|
| This controller handles members all functionlity.
*/
class MaturityController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */
    // use BranchPermissionRoleWiseTrait;
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    /** Maturity Report Demanad Data */
    public function maturityReportdemand()
    {
        if (check_my_permission(Auth::user()->id, "291") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Maturity Demand Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['plans'] = Plans::has('company')->where('status', 1)->where('id', '!=', 1)->pluck('name', 'id');
        return view('templates.admin.report.maturity.maturity_demand', $data);
    }
    /**
     * Maturity Demanad Listing 
     */
    public function maturityDemandlist(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $data = DemandAdvice::select('company_id', 'id', 'branch_id', 'investment_id', 'account_number', 'amount', 'opening_date', 'tenure', 'total_deposit', 'payment_date', 'status', 'plan_name', 'created_at', 'final_amount', 'payment_type', 'date', 'is_mature', 'payment_mode')->has('company')
                        ->with([
                            'investment' => function ($q) {
                                $q->select('customer_id', 'id', 'member_id', 'created_at', 'is_mature', 'plan_id', 'payment_mode', 'deposite_amount', 'tenure', 'account_number', 'maturity_date', 'created_at', 'associate_id', 'branch_id')
                                    ->with([
                                        'memberCompany' => function ($qmc) {
                                            $qmc->select('id', 'member_id');
                                        }
                                    ])
                                    ->with([
                                        'member' => function ($qm) {
                                            $qm->select('id', 'member_id', 'first_name', 'last_name');
                                        }
                                    ]);
                                $q->with([
                                    'associateMember' => function ($qs) {
                                        $qs->with('associateCode')->select('id', 'member_id', 'first_name', 'last_name', 'associate_code', 'associate_no');
                                    }
                                ]);
                                $q->with([
                                    'plan' => function ($qp) {
                                        $qp->select('id', 'name', 'amount', 'status');
                                    }
                                ]);
                                $q->with([
                                    'branch' => function ($qb) {
                                        $qb->select('id', 'name', 'sector', 'regan', 'zone', 'branch_code');
                                    }
                                ]);
                            }
                        ])
                        ->with([
                            'sumdeposite' => function ($sq) {
                                $sq->select('id', 'deposit', 'investment_id', 'transaction_type')->Where('is_deleted', 0);
                            }
                        ])
                        ->with([
                            'sumdeposite2' => function ($ssq) {
                                $ssq->select('id', 'deposit', 'investment_id', 'transaction_type')->Where('is_deleted', 0);
                            }
                        ])
                        ->where('is_deleted', '=', '0')
                        ->whereIn('payment_type', [1, 2, 4])
                        ->whereIn('status', [0, 1])
                        ->where('is_reject', '=', '0')
                        ->whereIn('is_mature', [0, 1]);
                    if ($arrFormData['maturity_start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['maturity_start_date'])));
                        if ($arrFormData['maturity_end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['maturity_end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
                    }
                    if ($arrFormData['status'] != '') {
                        $status = $arrFormData['status'];
                        if ($status == 'pending') {
                            $data = $data->where('status', 0)->where('is_mature', 1);
                        } elseif ($status == 'proceed') {
                            $data = $data->where('status', 0)->where('is_mature', 0);
                        } elseif ($status == 'paid') {
                            $data = $data->where('status', 1)->where('is_mature', 0);
                        } else {
                            $data = $data->whereIn('status', [0, 1])->whereIn('is_mature', [0, 1]);
                        }
                    }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                        $branch_id = $arrFormData['branch_id'];
                        $data = $data->whereHas('investment', function ($query) use ($branch_id) {
                            $query->whereHas(
                                'branch',
                                function ($br) use ($branch_id) {
                                    $br->where('branch_id', $branch_id);
                                }
                            );
                        });
                    }
                    if ($arrFormData['plan'] != '') {
                        $planid = $arrFormData['plan'];
                        $data = $data->whereHas('investment', function ($query) use ($planid) {
                            $query->whereHas(
                                'plan',
                                function ($pl) use ($planid) {
                                    $pl->where('id', $planid);
                                }
                            );
                        });
                        $data->where('account_number', '!=', '');
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $data = $data->whereHas('investment', function ($query) use ($member_id) {
                            $query->whereHas(
                                'memberCompany',
                                function ($qm) use ($member_id) {
                                    $qm->where('member_id', 'LIKE', '%' . $member_id . '%');
                                }
                            );
                        });
                    }
                    if ($arrFormData['company_id'] && $arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        $data = $data->whereHas('investment', function ($query) use ($company_id) {
                            $query->where('company_id', $company_id);
                        });
                    }
                    if ($arrFormData['member_name'] != '') {
                        $member_name = $arrFormData['member_name'];
                        $data = $data->whereHas('investment', function ($query) use ($member_name) {
                            $query->whereHas(
                                'member',
                                function ($qm) use ($member_name) {
                                    $qm->where('first_name', 'LIKE', '%' . $member_name . '%')
                                        ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                                        ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                                }
                            );
                        });
                    }
                    if ($arrFormData['account_no'] != '') {
                        $accno = $arrFormData['account_no'];
                        $data->where('account_number', $accno);
                    }
                    if ($arrFormData['associate_code'] != '') {
                        $acode = $arrFormData['associate_code'];
                        $data = $data->whereHas('investment', function ($query) use ($acode) {
                            $query->whereHas(
                                'associateMember',
                                function ($qm) use ($acode) {
                                    $qm->where('associate_no', $acode);
                                }
                            );
                        });
                    }
                    $dataRecord = $data->orderby('id', 'DESC')->get();
                    $count = $data->orderby('id', 'DESC')->count();
                    $DataArray = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                    $sno = $_POST['start'];
                    $rowReturn = array();
                    $totalCount = $count;
                    foreach ($DataArray as $row) {
                        $sno++;
                        $val['DT_RowIndex'] = $sno;
                        if (isset($row['investment']['branch'])) {
                            $val['branch'] = $row['investment']['branch']['name'];
                        } else {
                            $val['branch'] = 'N/A';
                        }
                        $val['customer_id'] = 'N/A';
                        $val['customer_id'] = $row['investment']['member']['member_id'];
                        $val['member_id'] = 'N/A';
                        $val['member_name'] = 'N/A';
                        if (isset($row['investment'])) {
                            $val['member_id'] = $row['investment']['memberCompany']['member_id'];
                            $val['member_name'] = $row['investment']['member']['first_name'] . ' ' . $row['investment']['member']['last_name'];
                        } else {
                            $val['member_id'] = 'N/A';
                            $val['member_name'] = 'N/A';
                        }
                        if (isset($row['account_number'])) {
                            $val['account_number'] = $row['account_number'];
                        } elseif (isset($row['investment_id'])) {
                            $val['account_number'] = $row['investment']['account_number'];
                        } else {
                            $val['account_number'] = 'N/A';
                        }
                        if (isset($row['plan_name'])) {
                            $val['plan'] = $row->plan_name;
                        } elseif (isset($row['investment_id'])) {
                            $val['plan'] = $row['investment']['plan']['name'];
                        } else {
                            $val['plan'] = 'N/A';
                        }
                        if (isset($row['investment']['tenure'])) {
                            $val['tenure'] = $row['investment']['tenure'] . ' Year';
                        } else {
                            $val['tenure'] = 'N/A';
                        }
                        if (isset($row['investment_id'])) {
                            $val['open_date'] = date("d/m/Y", strtotime(convertDate($row['investment']->created_at)));
                        } else {
                            $val['open_date'] = 'N/A';
                        }
                        if (isset($row['investment']['maturity_date'])) {
                            $val['maturity_date'] = date("d/m/Y", strtotime(convertDate($row['investment']['maturity_date'])));
                        } else {
                            $val['maturity_date'] = 'N/A';
                        }
                        if (isset($row->date)) {
                            $val['demand_date'] = date("d/m/Y", strtotime(convertDate($row->date)));
                        } else {
                            $val['demand_date'] = 'N/A';
                        }
                        if (isset($row['sumdeposite'])) {
                            $val['total_deposit'] = number_format($row['sumdeposite']->sum('deposit'), 2, '.', '');
                        }
                        if (isset($row['sumdeposite2'])) {
                            $val['total_deposit'] = number_format($row->sumdeposite2->sum('deposit'), 2, '.', '');
                        } else {
                            $val['total_deposit'] = 'N/A';
                        }
                        if ($row['status'] == 1 && $row['is_mature'] == 0) {
                            if (isset($row->payment_date)) {
                                $val['payment_date'] = date('d/m/Y', strtotime(convertDate($row->payment_date)));
                            } else {
                                $val['payment_date'] = 'N/A';
                            }
                        } else {
                            $val['payment_date'] = 'N/A';
                        }
                        if ($row['status'] == 1 && $row['is_mature'] == 0) {
                            if (isset($row->payment_mode)) {
                                if ($row->payment_mode == 0) {
                                    $val['payment_mode'] = "Cash";
                                } elseif ($row->payment_mode == 1) {
                                    $val['payment_mode'] = "Cheque";
                                } elseif ($row->payment_mode == 2) {
                                    $val['payment_mode'] = "Online Transfer";
                                } elseif ($row->payment_mode == 3) {
                                    $val['payment_mode'] = "SSB GV Transfer";
                                } elseif ($row->payment_mode == 4) {
                                    $val['payment_mode'] = "Auto Transfer (ECS)";
                                }
                            } else {
                                $val['payment_mode'] = 'N/A';
                            }
                        } else {
                            $val['payment_mode'] = 'N/A';
                        }
                        if ($row['status'] == 0 && $row['is_mature'] == 1) {
                            $val['status'] = 'Pending';
                        } elseif ($row['status'] == 0 && $row['is_mature'] == 0) {
                            $val['status'] = 'Processed';
                        } elseif ($row['status'] == 1 && $row['is_mature'] == 0) {
                            $val['status'] = 'Paid';
                        }
                        if (!empty($row['investment']['associateMember'])) {
                            $val['associate_code'] = $row['investment']['associateMember']->associate_no;
                        } else {
                            $val['associate_code'] = 'N/A';
                        }
                        if (!empty($row['investment']['associateMember'])) {
                            $val['associate_name'] = $row['investment']['associateMember']->first_name . ' ' . $row['investment']['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
                        } else {
                            $val['associate_name'] = 'N/A';
                        }
                        $rowReturn[] = $val;
                    }
                    Cache::put('maturity_demandlist', $dataRecord);
                    Cache::put('maturity_demandlist_count', $totalCount);
                    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn);
                    return json_encode($output);
                } else {
                    $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                    return json_encode($output);
                }
            }
        }
    }
    /** Maturity Payment Demanad Data */
    public function maturityReportpayment()
    {
        if (check_my_permission(Auth::user()->id, "292") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Maturity Payment Reports';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['plans'] = Plans::has('company')->where('status', 1)->where('id', '!=', 1)->pluck('name', 'id');
        return view('templates.admin.report.maturity.maturity_payment', $data);
    }
    /** Maturity Payment List Data */
    public function maturityPaymentlist(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $data = DemandAdvice::whereHas('company', function ($query) {
                        $query->where('status', 1);
                    })
                        ->with([
                            'investment' => function ($q) {
                                $q->select('company_id', 'customer_id', 'id', 'member_id', 'created_at', 'is_mature', 'plan_id', 'payment_mode', 'deposite_amount', 'tenure', 'account_number', 'maturity_date', 'created_at', 'associate_id', 'branch_id');
                                $q->Where('is_mature', 0);
                                $q->with([
                                    'member' => function ($qm) {
                                        $qm->select('id', 'member_id', 'first_name', 'last_name');
                                    }
                                ]);
                                $q->with([
                                    'memberCompany' => function ($qmc) {
                                        $qmc->select('id', 'member_id');
                                    }
                                ]);
                                $q->with([
                                    'associateMember' => function ($qmc) {
                                        $qmc->select('id', 'member_id', 'first_name', 'last_name', 'associate_no');
                                    }
                                ]);
                                $q->with([
                                    'plan' => function ($qp) {
                                        $qp->select('id', 'name', 'amount', 'status');
                                    }
                                ]);
                                $q->with([
                                    'branch' => function ($qb) {
                                        $qb->select('id', 'name', 'sector', 'regan', 'zone', 'branch_code');
                                    }
                                ]);
                            }
                        ])
                        // ->with([
                        //     'sumdeposite' => function ($sq) {
                        //         $sq->select('id', 'deposit', 'investment_id', 'transaction_type')->Where('is_deleted', 0);
                        //     }
                        // ])
                        ->with([
                            'sumdeposite2' => function ($ssq) {
                                $ssq->select('id', 'deposit', 'investment_id', 'transaction_type')->Where('is_deleted', 0);
                            }
                        ])
                        ->where('status', 1)->where('is_mature', 0)->whereIn('payment_type', [1, 2, 3, 4])->where('is_deleted', 0);
                    if (Auth::user()->branch_id > 0) {
                        $ids = $this->getDataRolewise(new Branch());
                        $data = $data->whereIn('branch_id', $ids);
                    }
                    if ($arrFormData['maturity_start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['maturity_start_date'])));
                        if ($arrFormData['maturity_end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['maturity_end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $data = $data->whereBetween('payment_date', [$startDate, $endDate]);
                    }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                        $branch_id = $arrFormData['branch_id'];
                        $data = $data->whereHas('investment', function ($query) use ($branch_id) {
                            $query->whereHas(
                                'branch',
                                function ($br) use ($branch_id) {
                                    $br->where('branch_id', $branch_id);
                                }
                            );
                        });
                    }
                    if ($arrFormData['plan'] != '') {
                        $planid = $arrFormData['plan'];
                        $data = $data->whereHas('investment', function ($query) use ($planid) {
                            $query->whereHas(
                                'plan',
                                function ($pl) use ($planid) {
                                    $pl->where('id', $planid);
                                }
                            );
                        });
                        // $data->where('account_number', '!=', '');
                    }
                    if ($arrFormData['company_id'] && $arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        $data = $data->whereHas('investment', function ($query) use ($company_id) {
                            $query->where('company_id', $company_id);
                        });
                        // $data->where('account_number', '!=', '');
                    }
                    if ($arrFormData['member_name'] != '') {
                        $member_name = $arrFormData['member_name'];
                        $data = $data->whereHas('investment', function ($query) use ($member_name) {
                            $query->whereHas(
                                'member',
                                function ($qm) use ($member_name) {
                                    $qm->where('first_name', 'LIKE', '%' . $member_name . '%')
                                        ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                                        ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                                }
                            );
                        });
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $data = $data->whereHas('investment', function ($query) use ($member_id) {
                            $query->whereHas(
                                'memberCompany',
                                function ($qm) use ($member_id) {
                                    $qm->where('member_id', 'LIKE', '%' . $member_id . '%');
                                }
                            );
                        });
                    }
                    // dd($arrFormData);
                    if ($arrFormData['account_no'] != '') {
                        $accno = $arrFormData['account_no'];
                        $data->where('account_number', '=', $accno);
                    }
                    if ($arrFormData['associate_code'] != '') {
                        $acode = $arrFormData['associate_code'];
                        $data = $data->whereHas('investment', function ($query) use ($acode) {
                            $query->whereHas(
                                'associateMember',
                                function ($qm) use ($acode) {
                                    $qm->where('associate_no', $acode);
                                }
                            );
                        });
                    }
                    $dataRecord = $data->orderby('id', 'DESC')->get();
                    $count = $data->orderby('id', 'DESC')->count();
                    $DataArray = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                    $sno = $_POST['start'];
                    $rowReturn = array();
                    $totalCount = $count;
                    foreach ($DataArray as $row) {
                        $sno++;
                        $val['DT_RowIndex'] = $sno;
                        if (isset($row['investment']['branch'])) {
                            $val['branch'] = $row['investment']['branch']['name'];
                        } else {
                            $val['branch'] = 'N/A';
                        }
                        $val['customer_id'] = 'N/A';
                        $val['customer_id'] = $row['investment']['member']['member_id'];
                        $val['member_id'] = 'N/A';
                        $val['member_name'] = 'N/A';
                        //$val['branch'] = $sno;
                        if (isset($row['investment_id']) && !empty($row['investment_id'])) {
                            $val['member_id'] = $row['investment']['memberCompany']['member_id'];
                            $val['member_name'] = $row['investment']['member']['first_name'] . ' ' . $row['investment']['member']['last_name'];
                        } elseif (isset($row['investment']) && !empty($row['investment'])) {
                            $val['member_id'] = $row['investment']['member']['member_id'];
                            $val['member_name'] = $row['investment']['member']['first_name'] . ' ' . $row['investment']['member']['last_name'];
                        }
                        $val['account_number'] = 'N/A';
                        if (isset($row['account_number'])) {
                            $val['account_number'] = $row['account_number'];
                        } elseif (isset($row['investment_id'])) {
                            $val['account_number'] = $row['investment']['account_number'];
                        }
                        if (isset($row['plan_name'])) {
                            $val['plan'] = $row['investment']['plan']['name'];
                        } elseif (isset($row['investment_id'])) {
                            $val['plan'] = $row['investment']['plan']['name'];
                        } else {
                            $val['plan'] = 'N/A';
                        }
                        if (isset($row['investment']['tenure'])) {
                            $val['tenure'] = $row['investment']['tenure'] . ' Year';
                        } else {
                            $val['tenure'] = 'N/A';
                        }
                        if (isset($row['investment_id'])) {
                            $val['open_date'] = date("d/m/Y", strtotime(convertDate($row['investment']->created_at)));
                        } else {
                            $val['open_date'] = 'N/A';
                        }
                        if (isset($row['investment']['maturity_date'])) {
                            $val['maturity_date'] = date("d/m/Y", strtotime(convertDate($row['investment']['maturity_date'])));
                        } else {
                            $val['maturity_date'] = 'N/A';
                        }
                        if (isset($row['payment_date'])) {
                            $val['payment_date'] = date("d/m/Y", strtotime(convertDate($row['payment_date'])));
                        } else {
                            $val['payment_date'] = 'N/A';
                        }
                        // if (isset($row['sumdeposite'])) {
                        //     $val['total_deposit'] = number_format($row['sumdeposite']->sum('deposit'), 2, '.', '');
                        // }
                        $val['total_deposit'] = 'N/A';
                        if (isset($row['sumdeposite2'])) {
                            $val['total_deposit'] = number_format($row['sumdeposite2']->sum('deposit'), 2, '.', '');
                        }
                        /*
                         if (isset($row['final_amount'])) {
                         $val['payment_amount'] = $row['final_amount'];
                         } else {
                         $val['payment_amount'] = 'N/A';
                         }*/
                        $val['payment_amount'] = 'N/A';
                        if (isset($row['maturity_amount_payable'])) {
                            $val['payment_amount'] = $row['maturity_amount_payable'];
                        }
                        $val['payment_mode'] = 'N/A';
                        if (isset($row->payment_mode)) {
                            if ($row->payment_mode == 0) {
                                $val['payment_mode'] = 'Cash';
                            } elseif ($row->payment_mode == 1) {
                                $val['payment_mode'] = 'Cheque';
                            } elseif ($row->payment_mode == 2) {
                                $val['payment_mode'] = 'Online Transfer';
                            } elseif ($row->payment_mode == 3) {
                                $val['payment_mode'] = 'SSB/GV Transfer';
                            } elseif ($row->payment_mode == 4) {
                                $val['payment_mode'] = 'Auto Transfer(ECS)';
                            }
                        }
                        $val['associate_code'] = 'N/A';
                        $val['associate_code'] = $row['investment']['associateMember']->associate_no;
                        $val['associate_name'] = 'N/A';
                        $val['associate_name'] = $row['investment']['associateMember']->first_name . ' ' . $row['investment']['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
                        $rowReturn[] = $val;
                    }
                    Cache::put('maturity_paymentlist', $dataRecord);
                    Cache::put('maturity_paymentlist_count', $totalCount);
                    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                    return json_encode($output);
                } else {
                    $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                    return json_encode($output);
                }
            }
        }
    }
    /** Maturity Over Due Demanad Data */
    public function maturityReportoverdue()
    {
        if (check_my_permission(Auth::user()->id, "293") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Maturity Over Due  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['plans'] = Plans::has('company')->where('status', 1)->where('id', '!=', 1)->pluck('name', 'id');
        return view('templates.admin.report.maturity.maturity_overdue', $data);
    }
    /** Maturity Over Due  Data */
    public function maturityOverdduelist(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $data = Memberinvestments::select('company_id', 'customer_id', 'id', 'branch_id', 'account_number', 'plan_id', 'tenure', 'is_mature', 'maturity_date', 'deposite_amount', 'created_at', 'member_id', 'current_balance', 'associate_id')->has('company')
                        ->with([
                            'branch' => function ($q) {
                                $q->select('id', 'name', 'sector', 'regan', 'zone', 'branch_code', 'state_id');
                            },
                            'plan' => function ($qp) {
                                $qp->select('id', 'name', 'amount', 'status', 'plan_category_code', 'plan_sub_category_code');
                            }
                        ])
                        ->with([
                            'memberCompany' => function ($q) {
                                $q->select('id', 'member_id');
                            }
                        ])
                        ->with([
                            'associateMember' => function ($q) {
                                $q->select('id', 'member_id', 'associate_no', 'first_name', 'last_name', 'created_at');
                            }
                        ])
                        // ->with([
                        //     'member' => function ($q) {
                        //         $q->select('id', 'member_id', 'first_name', 'last_name', 'created_at'); 
                        //     }
                        // ])
                        ->with([
                            'member' => function ($q) {
                                $q->select('id', 'member_id', 'first_name', 'last_name', 'created_at');
                                $q->with('associateCode')->select('id', 'member_id', 'first_name', 'last_name', 'associate_code');
                            }
                        ])
                        ->where('is_mature', 1)->where('is_deleted', 0)
                        ->whereHas('plan', function ($q) {
                            $q->where('plans.plan_category_code', '!=', 'S');
                        })
                        ->whereDate('maturity_date', '<', Carbon::today());
                    //->whereNotIn('plan_id', [1, 12])
                    // dd($data);
                    if (Auth::user()->branch_id > 0) {
                        $ids = $this->getDataRolewise(new Branch());
                        $data = $data->whereIn('branch_id', $ids);
                    }
                    // if ($arrFormData['maturity_start_date'] != '') {
                    //     $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['maturity_start_date'])));
                    //     if ($arrFormData['maturity_end_date'] != '') {
                    //         $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['maturity_end_date'])));
                    //     } else {
                    //         $endDate = '';
                    //     }
                    //     $data = $data->whereBetween('maturity_date', [$startDate, $endDate]);
                    // }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                        $branch_id = $arrFormData['branch_id'];
                        // $data = $data->where('branch_id', '=', $branch_id);
                        $data = $data->whereHas('branch', function ($query) use ($branch_id) {
                            $query->where('branch_id', $branch_id);
                        });
                    }
                    if ($arrFormData['plan'] != '') {
                        $planid = $arrFormData['plan'];
                        $data = $data->where('plan_id', $planid);
                    }
                    if ($arrFormData['member_name'] != '') {
                        $member_name = $arrFormData['member_name'];
                        $data = $data->whereHas('member', function ($query) use ($member_name) {
                            $query->where('first_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                        });
                    }
                    // dd($arrFormData);
                    if ($arrFormData['account_no'] != '') {
                        $accno = $arrFormData['account_no'];
                        $data->where('account_number', '=', $accno);
                    }
                    if ($arrFormData['company_id'] && $arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        $data = $data->where('company_id', $company_id);
                    }
                    if ($arrFormData['associate_code'] != '') {
                        $acode = $arrFormData['associate_code'];
                        $data = $data->whereHas('associateMember', function ($query) use ($acode) {
                            $query->where('associate_no', $acode);
                        });
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $data = $data->whereHas('memberCompany', function ($query) use ($member_id) {
                            $query->where('member_id', 'LIKE', '%' . $member_id . '%');
                        });
                    }
                    //else {
                    //$startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                    //$endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                    //}
                    $datas = $data->orderby('id', 'DESC')->get();
                    $count = count($datas);
                    $DataArray = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                    $sno = $_POST['start'];
                    $rowReturn = array();
                    $totalCount = $count;
                    foreach ($DataArray as $row) {
                        $sno++;
                        $val['DT_RowIndex'] = $sno;
                        if (isset($row['branch_id'])) {
                            $val['branch'] = $row['branch']['name']; //customGetBranchDetail($row->branch_id)->name;
                        } else {
                            $val['branch'] = 'N/A';
                        }
                        $val['customer_id'] = 'N/A';
                        if (isset($row['member']['member_id'])) {
                            $val['customer_id'] = $row['member']['member_id'];
                        }
                        //$val['branch'] = $sno;
                        if (isset($row['member']['member_id'])) {
                            $val['member_id'] = $row['memberCompany']['member_id']; //customGetBranchDetail($row->branch_id)->sector;
                        } else {
                            $val['member_id'] = 'N/A';
                        }
                        if (isset($row['member']['first_name'])) {
                            $val['member_name'] = $row['member']['first_name'] . ' ' . $row['member']['last_name']; //customGetBranchDetail($row->branch_id)->sector;
                        } else {
                            $val['member_name'] = 'N/A';
                        }
                        if (isset($row['account_number'])) {
                            $val['account_number'] = $row['account_number'];
                        } else {
                            $val['account_number'] = 'N/A';
                        }
                        if (isset($row['plan'])) {
                            $val['plan'] = $row['plan']->name;
                        } else {
                            $val['plan'] = 'N/A';
                        }
                        if ($row['plan']['plan_sub_category_code'] == 'X') {
                            $val['tenure'] = 1 . ' Year';
                        } else {
                            $val['tenure'] = $row->tenure . ' Year';
                        }
                        if (isset($row->created_at)) {
                            $val['open_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                        } else {
                            $val['open_date'] = 'N/A';
                        }
                        if (isset($row->maturity_date)) {
                            $val['maturity_date'] = date("d/m/Y", strtotime(convertDate($row->maturity_date)));
                        } else {
                            $val['maturity_date'] = 'N/A';
                        }
                        if (isset($row['current_balance'])) {
                            $val['total_deposit'] = $row['current_balance'];
                        } else {
                            $val['total_deposit'] = 'N/A';
                        }
                        if (isset($row['maturity_date'])) {
                            $state_id = getBranchDetail($row['branch_id'])->state_id;
                            $startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id));
                            $currentDatee = Carbon::parse($startDatee);
                            $maturitydate = Carbon::parse($row['maturity_date']);
                            // $planDetailMonthly = array('2', '3', '4', '5', '6', '9', '10', '11');
                            // $planDetailDaily = array('7');
                            $planDetailMonthly = array('M', 'F');
                            $planDetailDaily = array('D');
                            $periodMonthly = (in_array($row['plan']['plan_category_code'], $planDetailMonthly) ? $currentDatee->diffInMonths($maturitydate) : '');
                            $periodDaily = (in_array($row['plan']['plan_category_code'], $planDetailDaily) ? $currentDatee->diffInDays($maturitydate) : '');
                            //dd($periodMonthly,$periodDaily,$row['plan']['plan_category_code']);
                            if ($row['plan']['plan_category_code'] == 'D') {
                                $val['overdue_period'] = $periodDaily . 'Days';
                            } elseif ($row['plan']['plan_category_code'] == 'M' || $row['plan']['plan_category_code'] == 'F') {
                                $val['overdue_period'] = $periodMonthly . 'Month';
                            } else {
                                $val['overdue_period'] = 'N/A';
                            }
                        } else {
                            $val['overdue_period'] = 'N/A';
                        }
                        if (!empty($row['associateMember'])) {
                            $val['associate_code'] = $row['associateMember']->associate_no;
                        } else {
                            $val['associate_code'] = 'N/A';
                        }
                        if (!empty($row['associateMember'])) {
                            $val['associate_name'] = $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
                        } else {
                            $val['associate_name'] = 'N/A';
                        }
                        $rowReturn[] = $val;
                    }
                    $value = Cache::put('maturity_overduelist', $datas);
                    Cache::put('maturity_overduelist_count', $totalCount);
                    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                    return json_encode($output);
                } else {
                    $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                    return json_encode($output);
                }
            }
        }
    }
    /** Maturity Over Due Demanad Data */
    public function maturityReportupcomings()
    {
        if (check_my_permission(Auth::user()->id, "294") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Maturity Upcoming  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['plans'] = Plans::has('company')->where('status', 1)->where('id', '!=', 1)->pluck('name', 'id');
        return view('templates.admin.report.maturity.maturity_upcoming', $data);
    }
    /** Maturity Upcoming  Data */
    public function maturityUpcominglist(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $data = Memberinvestments::select('company_id', 'customer_id', 'id', 'branch_id', 'account_number', 'plan_id', 'tenure', 'is_mature', 'maturity_date', 'deposite_amount', 'created_at', 'member_id', 'current_balance', 'associate_id')->has('company')
                        ->with([
                            'associateMember',
                            'branch' => function ($q) {
                                $q->select('id', 'name', 'sector', 'regan', 'zone', 'branch_code', 'state_id');
                            },
                            'plan' => function ($qp) {
                                $qp->select('id', 'name', 'amount', 'status', 'plan_category_code', 'plan_sub_category_code');
                            }
                        ])
                        ->with([
                            'memberCompany' => function ($q) {
                                $q->select('id', 'member_id');
                            }
                        ])
                        ->with([
                            'associateMember' => function ($q) {
                                $q->select('id', 'member_id', 'associate_no', 'first_name', 'last_name', 'created_at');
                            }
                        ])
                        ->with([
                            'member' => function ($q) {
                                $q->select('id', 'member_id', 'first_name', 'last_name', 'created_at');
                            }
                        ])->where('is_mature', 1)->where('is_deleted', 0)->whereHas('plan', function ($q) {
                            $q->where('plan_category_code', '!=', 'S');
                        });
                    //->whereBetween('maturity_date', [$startdate,$enddate])
                    if (Auth::user()->branch_id > 0) {
                        $ids = $this->getDataRolewise(new Branch());
                        $data = $data->whereIn('branch_id', $ids);
                    }
                    if ($arrFormData['maturity_start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['maturity_start_date'])));
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['maturity_end_date'])));
                        $searchstartDate = date("d", strtotime(convertDate($arrFormData['maturity_start_date'])));
                        $searchstartMonth = date("m", strtotime(convertDate($arrFormData['maturity_start_date'])));
                        $yearbackstartdate = Carbon::parse($startDate)->subYear();
                        $oneyearbackstartdate = date("Y-m-d", strtotime(convertDate($yearbackstartdate)));
                        $yearbackenddate = Carbon::parse($endDate)->subYear();
                        $oneyearbackenddate = date("Y-m-d", strtotime(convertDate($yearbackenddate)));
                        $flexiArray = array('X');
                        $data = $data->when($arrFormData['plan'] == '', function ($q) use ($startDate, $endDate, $oneyearbackstartdate, $oneyearbackenddate, $arrFormData) {
                            $q->when(
                                $arrFormData['branch_id'] != '',
                                function ($q) use ($arrFormData) {
                                    $branch_id = $arrFormData['branch_id'];
                                    $q->where('branch_id', $branch_id);
                                }
                            )->when(
                                    $arrFormData['member_id'] != '',
                                    function ($q) use ($arrFormData) {
                                        $member_id = $arrFormData['member_id'];
                                        $q->whereHas(
                                            'memberCompany',
                                            function ($query) use ($member_id) {
                                                $query->where('member_id', 'LIKE', '%' . $member_id . '%');
                                            }
                                        );
                                    }
                                )->when(
                                    $arrFormData['member_name'] != '',
                                    function ($q) use ($arrFormData) {
                                        $member_name = $arrFormData['member_name'];
                                        $q->whereHas(
                                            'member',
                                            function ($query) use ($member_name) {
                                                $query->where('first_name', 'LIKE', '%' . $member_name . '%')
                                                    ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                                                    ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                                            }
                                        );
                                    }
                                )->when(
                                    $arrFormData['account_no'] != '',
                                    function ($q) use ($arrFormData) {
                                        $accno = $arrFormData['account_no'];
                                        $q->where('account_number', '=', $accno);
                                    }
                                )->when(
                                    $arrFormData['associate_code'] != '',
                                    function ($q) use ($arrFormData) {
                                        $acode = $arrFormData['associate_code'];
                                        $q->whereHas(
                                            'associateMember',
                                            function ($query) use ($acode) {
                                                $query->where('associate_no', $acode);
                                            }
                                        );
                                    }
                                )->whereBetween(DB::raw('date(maturity_date)'), [$startDate, $endDate])->orWhere(
                                    function ($q) use ($oneyearbackstartdate, $oneyearbackenddate) {
                                        $q->whereBetween(DB::raw('date(created_at)'), [$oneyearbackstartdate, $oneyearbackenddate])->whereHas('plan', function ($q) {
                                            $q->where('plan_sub_category_code', '=', 'X');
                                        })->where('is_mature', 1);
                                    }
                                );
                        })->when(in_array($arrFormData['plansubcategory'], $flexiArray), function ($q) use ($oneyearbackstartdate, $oneyearbackenddate, $arrFormData) {
                            $q->whereBetween(DB::raw('date(created_at)'), [$oneyearbackstartdate, $oneyearbackenddate]);
                        })->when(($arrFormData['plansubcategory'] != '' && !in_array($arrFormData['plansubcategory'], $flexiArray)), function ($q) use ($startDate, $endDate) {
                            $q->whereBetween(DB::raw('date(maturity_date)'), [$startDate, $endDate]);
                        });
                    }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                        $branch_id = $arrFormData['branch_id'];
                        $data = $data->whereHas('branch', function ($query) use ($branch_id) {
                            $query->where('branch_id', $branch_id);
                        });
                    }
                    if ($arrFormData['plan'] != '') {
                        $planid = $arrFormData['plan'];
                        $data = $data->whereHas('plan', function ($query) use ($planid) {
                            $query->where('id', $planid);
                        });
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $data = $data->whereHas('memberCompany', function ($query) use ($member_id) {
                            $query->where('member_id', 'LIKE', '%' . $member_id . '%');
                        });
                    }
                    if ($arrFormData['member_name'] != '') {
                        $member_name = $arrFormData['member_name'];
                        $data = $data->whereHas('member', function ($query) use ($member_name) {
                            $query->where('first_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                        });
                    }
                    // dd($arrFormData);
                    if ($arrFormData['account_no'] != '') {
                        $accno = $arrFormData['account_no'];
                        $data->where('account_number', '=', $accno);
                    }
                    if ($arrFormData['company_id'] && $arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        $data = $data->where('company_id', $company_id);
                    }
                    if ($arrFormData['associate_code'] != '') {
                        $acode = $arrFormData['associate_code'];
                        $data = $data->whereHas('associateMember', function ($query) use ($acode) {
                            $query->where('associate_no', $acode);
                        });
                    }
                    $datas = $data->orderby('id', 'DESC')->get();
                    $cacheData = $datas;
                    $count = count($datas);
                    $DataArray = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                    $sno = $_POST['start'];
                    $rowReturn = array();
                    $totalCount = $count;
                    foreach ($DataArray as $row) {
                        $sno++;
                        $val['DT_RowIndex'] = $sno;
                        $val['branch'] = 'N/A';
                        if (isset($row['branch_id'])) {
                            $val['branch'] = $row['branch']['name'];
                        }
                        $val['member_id'] = 'N/A';
                        if (isset($row['memberCompany']['member_id'])) {
                            $val['member_id'] = $row['memberCompany']['member_id'];
                        }
                        $val['customer_id'] = 'N/A';
                        if (isset($row['member']['member_id'])) {
                            $val['customer_id'] = $row['member']['member_id'];
                        }
                        $val['member_name'] = 'N/A';
                        if (isset($row['member']['first_name'])) {
                            $val['member_name'] = $row['member']['first_name'] . ' ' . $row['member']['last_name'];
                        }
                        $val['account_number'] = 'N/A';
                        if (isset($row['account_number'])) {
                            $val['account_number'] = $row['account_number'];
                        }
                        $val['plan'] = 'N/A';
                        if (isset($row['plan'])) {
                            $val['plan'] = $row['plan']->name;
                        }
                        if ($row['plan']['plan_sub_category_code'] == "X") {
                            $val['tenure'] = 1 . ' Year';
                        } else {
                            $val['tenure'] = $row->tenure . ' Year';
                        }
                        $val['open_date'] = 'N/A';
                        if (isset($row->created_at)) {
                            $val['open_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                        }
                        $val['maturity_date'] = 'N/A';
                        if (isset($row->maturity_date)) {
                            $val['maturity_date'] = date("d/m/Y", strtotime(convertDate($row->maturity_date)));
                        }
                        $val['deno_amount'] = "N/A";
                        if (isset($row->deposite_amount)) {
                            $val['deno_amount'] = $row->deposite_amount;
                        }
                        $val['total_deposit'] = 'N/A';
                        if (isset($row['current_balance'])) {
                            $val['total_deposit'] = $row['current_balance'];
                        }
                        if ($row['plan']['plan_sub_category_code'] == "X") {
                            $formatedcreated_at = Carbon::parse($row->created_at)->addYear();
                            $newcreateddate = date("Y-m-d", strtotime(convertDate($formatedcreated_at)));
                            $val['remaining_period'] = 'N/A';
                            $val['expected_deposit'] = 'N/A';
                            if (isset($newcreateddate)) {
                                $startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), ($row['branch'] ? $row['branch']['state_id'] : 33)));
                                $currentDatee = Carbon::parse($startDatee);
                                $currentformatedDate = strtotime(convertDate($currentDatee));
                                $maturitydate = Carbon::parse($newcreateddate);
                                $maturityformatedDate = strtotime(convertDate($maturitydate));
                                $remainingDate = $maturityformatedDate - $currentformatedDate;
                                $remainingdays = round($remainingDate / (60 * 60 * 24));
                                $remainingmonths = round($remainingdays / 30);
                                $planDetailMonthly = array("X");
                                $periodMonthly = (in_array($row['plan']['plan_sub_category_code'], $planDetailMonthly) ? $remainingmonths : 0);
                                $val['remaining_period'] = $periodMonthly . 'Month';
                                $expected_deposit = $periodMonthly * $row->deposite_amount;
                                if ($row['plan']['plan_sub_category_code'] == "X" && $row['plan']['plan_category_code'] == "F") {
                                    $val['expected_deposit'] = 0;
                                } else {
                                    $val['expected_deposit'] = $expected_deposit;
                                }
                            }
                        } elseif ($row['plan']['plan_category_code'] == "F" || $row['plan']['plan_category_code'] == "M" || $row['plan']['plan_category_code'] == "D") {
                            $val['remaining_period'] = 'N/A';
                            $val['expected_deposit'] = 'N/A';
                            if (isset($row['maturity_date'])) {
                                $startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), ($row['branch'] ? $row['branch']['state_id'] : 33)));
                                $currentDatee = Carbon::parse($startDatee)->floorMonth();
                                $maturitydate = Carbon::parse($row['maturity_date'])->floorMonth();
                                $currentformatedDate = strtotime(convertDate($currentDatee));
                                $maturityformatedDate = strtotime(convertDate($maturitydate));
                                $remainingDate = $maturityformatedDate - $currentformatedDate;
                                $remainingdays = round($remainingDate / (60 * 60 * 24));
                                // $remainingmonths = round($remainingdays/30);
                                $remainingmonths = $currentDatee->diffInMonths($maturitydate);
                                $planDetailMonthly = array("F", "M");
                                $planDetailDaily = array('D');
                                $periodMonthly = (in_array($row['plan']['plan_category_code'], $planDetailMonthly) ? $remainingmonths : 0);
                                $periodMonthlyDiffdaily = (in_array($row['plan']['plan_category_code'], $planDetailMonthly) ? $remainingdays : 0);
                                $periodDaily = (in_array($row['plan']['plan_category_code'], $planDetailDaily) ? $remainingdays : 0);
                                // dd($periodMonthly,$periodDaily);
                                if ($row['plan']['plan_category_code'] == "D") {
                                    $val['remaining_period'] = $periodDaily . ' Days';
                                    $expected_deposit = $periodDaily * $row->deposite_amount;
                                    $val['expected_deposit'] = $expected_deposit;
                                } elseif (in_array($row['plan']['plan_category_code'], $planDetailMonthly)) {
                                    $tag = ($periodMonthly > 1) ? ' Months' : ' Month';
                                    $val['remaining_period'] = $periodMonthly . $tag;
                                    if ($row['plan']['plan_category_code'] == "F") {
                                        $val['expected_deposit'] = 0;
                                    } else {
                                        $expected_deposit = $periodMonthly * $row->deposite_amount;
                                        $val['expected_deposit'] = $expected_deposit;
                                    }
                                }
                            }
                        }
                        $val['associate_code'] = 'N/A';
                        if (!empty($row['associateMember'])) {
                            $val['associate_code'] = $row['associateMember']->associate_no;
                        }
                        $val['associate_name'] = 'N/A';
                        if (!empty($row['associateMember'])) {
                            $val['associate_name'] = $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name;
                        }
                        $rowReturn[] = $val;
                    }
                    $value = Cache::put('maturity_upcominglist', $cacheData);
                    Cache::put('maturity_upcominglist_count', $totalCount);
                    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                    return json_encode($output);
                } else {
                    $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                    return json_encode($output);
                }
            }
        }
    }
}