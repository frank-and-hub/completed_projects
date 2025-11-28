<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\AccountHeads;
use App\Http\Traits\BalanceSheetTrait;
use Illuminate\Support\Facades\Crypt;
use Session;
use URL;

class ProfitLossController extends Controller
{
    use BalanceSheetTrait;

    public function __construct()
    {
        $this->middleware('auth');
    }

    // Balance SheetTable
    public function index(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "36") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        // StartDate
        $finacialYear = getFinacialYear();
        $date = date("Y-m-d", strtotime($finacialYear['dateStart']));
        $branchIddd = 33;
        $globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
        $to_date = date("Y-m-d", strtotime(convertDate($globalDate1)));
        // $data['branches'] = Branch::where('status', 1)->get(['id', 'name']);
        $data['title'] = "Profit & Loss Account";
        $data['end_date'] = "";

        $data['incomeHead'] = AccountHeads::select('id', 'head_id', 'labels', 'sub_head', 'child_head')->whereIn('status', [0, 1])->where('parent_id', 3)
            ->where('labels', 2)
            ->orderBy('head_id', 'ASC')
            ->get();

        $data['expenseHead'] = AccountHeads::select('id', 'head_id', 'sub_head', 'child_head', 'labels')->whereIn('status', [0, 1])->where('parent_id', 4)
            ->where('labels', 2)
            ->orderBy('head_id', 'ASC')
            ->get();
        return view('templates.admin.profit_loss.index', $data);
    }
    public function profitLossAjax(Request $request)
    {
        $branch_id = $request->branch_id ?? 0;
        $startDate = date('Y-m-d', strtotime(convertDate($request->start_date)));
        $endDate = date('Y-m-d', strtotime(convertDate($request->to_date)));
        $finacialYear = $request->financial_year;
        $companyId = $request->company_id;
        $balanceSheetData = collect(DB::select('call HeadAmount(?,?,?,?,?)', [$branch_id, '1', $startDate, $endDate, $companyId]));
        $incomeHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 2 && $data->first()->parent_id == 3);
        });
        $expenseHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 2 && $data->first()->parent_id == 4);
        });

        $labelThreeHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return $data->first()->labels == 3;
        });

        $actualData = $balanceSheetData->filter(function ($data) {
            return $data->labels != NULL;
        })->toArray();

        Session::put('profitLoss', $actualData);
        Session::put('profitLossData', $balanceSheetData);

        foreach ($actualData as $item) {
            $amount[$item->head_id] = 0;
            $childHeadId = [];
            $totalAmount[$item->head_id] = ($item->nature == 1) ? ($item->amount_sum - $item->amount_sum_dr) : ($item->amount_sum_dr - $item->amount_sum);
        }
        return \Response::json(['view' => view('templates.admin.profit_loss2.partials.sheet_filter', ['incomeHead' => $incomeHead, 'start_date' => $startDate, 'end_date' => $endDate, 'branch_id' => $branch_id, 'to_date' => $endDate, 'financial_year' => $finacialYear, 'expenseHead' => $expenseHead, 'totalAmount' => $totalAmount, 'labelThreeHead' => $labelThreeHead, 'companyId' => $companyId])->render(), 'msg_type' => 'success']);
    }

    //  Detailed Balance Sheet
    public function detailedProfitLoass(Request $request)
    {
        $data['title'] = 'Detailed Report';
        return view('templates.admin.profit_loss.detail', $data);
    }

    public function labelTwo(Request $request)
    {
        $startDate = $request->date;
        $label = $request->label;
        $endDate = $request->to_date;
        $companyId = $request->company_id;
        $finacialYear = $request->financial_year;
        $branchId = $request->branch_id;
        $headId = $request->head_id;
        $getProfitData = collect(Session::get('profitLoss'))->keyBy('head_id')->toArray();
        $data['title'] = 'Profit and Loss Sheet - Detail Report';
        $data['headDetail'] = $libalityHead = $getProfitData[$headId];
        $data['childHead'] = explode(',', $getProfitData[$headId]->child_id);

        $date = '';
        $to_date = '';

        if (isset($_GET['date'])) {

            $date = $_GET['date'];

            $date = date("Y-m-d", strtotime(convertDate($date)));
        }
        if (isset($_GET['to_date'])) {

            $to_date = $_GET['to_date'];

            $to_date = date("Y-m-d", strtotime(convertDate($to_date)));
        }

        $data['date'] = $date;

        $data['to_date'] = $to_date;

        $data['title'] = 'Profit and Loss Sheet - Detail Report';

        // $data['headDetail'] = $libalityHead = AccountHeads::select('id', 'head_id', 'labels', 'sub_head', 'labels')->where('head_id', $headId)->First();

        // $data['childHead'] = getHead($headId, $label + 1);

        // $data['subchildHead'] = getHead($data['headDetail']->id, 4);
        // $data['subchildHead2'] = getHead($data['headDetail']->id, 5);

        // $data['penal_interest'] = AccountHeads::where('head_id', 32)->first();

        // $data['sales'] = AccountHeads::where('head_id', 112)->first();
        // $data['statioanryHead'] = AccountHeads::whereIn('head_id', ['34', '35', '90'])->get();

        $data['headId'] = $headId;
        // $data['sub_head_id'] = $sub_id =  AccountHeads::where('id', $libalityHead->parent_id)->First();
        // $data['child_sub_head_id'] = AccountHeads::where('id', $sub_id->parent_id)->First();

        // if ($data['child_sub_head_id']) {
        //     $data['child_sub_head_id'] = $data['child_sub_head_id']->headId;
        // }
        // if ($data['sub_head_id']) {
        //     $data['sub_head_id'] = $data['sub_head_id']->headId;
        // }
        // $head_ids = array($headId);
        // $subHeadsIDS = AccountHeads::where('head_id', $headId)->where('status', 0)->first();



        // if (count($subHeadsIDS) > 0) {
        //     $head_ids =  array_merge($head_ids, $subHeadsIDS);
        //     $record = $this->get_account_head_ids($head_ids, $subHeadsIDS, true);
        // }

        // foreach ($record as $key => $value) {
        //     $ids[] = $value;
        // }
        // $data['ids'] = $subHeadsIDS->child_head;

        return view('templates.admin.profit_loss.child_head_detail', $data);
    }

    public function detail($head_id)
    {
        $data['title'] = "Profit Loss | Head Detailssss";
        return view('templates.admin.profit_loss.head_detail', $data);
    }

    public function branch_wise_detail($head_id, $label)
    {
        $data['title'] = 'Profit Loss | Head Detail';
        $date = '';
        $to_date = '';
        if (isset($_GET['date'])) {

            $date = $_GET['date'];

            $date = date("Y-m-d", strtotime(convertDate($date)));
        }
        if (isset($_GET['to_date'])) {

            $to_date = $_GET['to_date'];

            $to_date = date("Y-m-d", strtotime(convertDate($to_date)));
        }
        if (isset($_GET['branch_id'])) {

            $branchId = $_GET['branch_id'];
        } else {
            $branchId = '';
        }

        $data['date'] = $date;
        $data['to_date'] = $to_date;

        $data['headDetail'] = $libalityHead = AccountHeads::where('head_id', $head_id)->First();

        $data['head'] = $head_id;

        $data['label'] = $label;

        $data['branch'] = $branchId;

        $data['branches'] = Branch::where('id', $branchId)->orderBy('id', 'ASC')->get();

        if (isset($_GET['financial_year'])) {
            $data['financial_year'] = $_GET['financial_year'];
        } else {
            $data['financial_year'] = '';
        }

        return view('templates.admin.profit_loss.branch_wise_detail', $data);
    }

    public function branch_wise_detailed(Request $request)
    {


        $head_id = $request->head;
        $parent_id1 = '';
        $financialYear = $request->financial_year;
        $headData = AccountHeads::where('head_id', $head_id)->first();

        if ($head_id == 53) {
            $parent_id1 = $head_id;
        } else {

            if ($headData->parent_id != 53) {
                $headData1 = AccountHeads::where('head_id', $headData->head_id)->first();
                if ($headData1->parent_id == 53) {
                    $parent_id1 = $headData1->parent_id;
                }
            } else {
                $parent_id1 = $headData->parent_id;
            }
        }

        if ($head_id == 37) {
            $parent_id1 = $head_id;
        } else {

            if ($headData->parent_id != 37) {
                $headData1 = AccountHeads::where('head_id', $headData->head_id)->first();
                if ($headData1->parent_id == 37) {
                    $parent_id1 = $headData1->parent_id;
                }
            } else {
                $parent_id1 = $headData->parent_id;
            }
        }



        if ($request->ajax()) {

            $head_id = $request->head;

            $label = $request->label;

            $date = $request->date;

            $to_date = $request->to_date;


            $branch = $request->branch;

            $head_info = AccountHeads::where('head_id', $head_id)->first();


            $info = 'head' . $label;

            $data = Branch::orderBy('id', 'ASC');

            if ($branch != '') {

                $data = $data->where('id', $branch);
            }

            if (!is_null(Auth::user()->branch_ids)) {
                $branch_ids = Auth::user()->branch_ids;
                $data = $data->whereIn('id', explode(",", $branch_ids));
            }



            $data1 = $data->get();

            $count = count($data1);

            $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();

            $totalCount = $data->count();

            $sno = $_POST['start'];

            $rowReturn = array();

            foreach ($data as $row) {
                $head_ids = array($head_id);


                $subHeadsIDS = AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('parent_id')->toArray();

                if (count($subHeadsIDS) > 0) {
                    $head_ids = array_merge($head_ids, $subHeadsIDS);
                    $record = $this->get_account_head_ids($head_ids, $subHeadsIDS, true);
                }

                foreach ($record as $key => $value) {
                    $ids[] = $value;
                }
                // dd($ids);

                $sno++;

                $branch_id = $row->id;

                $startdate = '';

                $enddate = '';

                $val['DT_RowIndex'] = $sno;

                $val['branch_name'] = $row->name;

                $val['branch_code'] = $row->branch_code;

                $val['total_member'] = getBranchWiseBusinessDate($head_id, $label, $branch_id, $date, $to_date);

                $val['amount'] = "&#8377;" . number_format((float) headTotalNew($head_id, $date, $to_date, $branch_id), 2, '.', '');

                $btn = 'N/A';

                $url = URL::to("admin/profit-loss/detailed/interest_on_loan/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url1 = URL::to("admin/profit-loss/detailed/penal/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url2 = URL::to("admin/profit-loss/detailed/panelty?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url3 = URL::to("admin/profit-loss/detailed/interest_on_deposit/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url5 = URL::to('admin/profit-loss/detailed/interest_on_loan_taken/' . $head_info->head_id . '/' . $head_info->labels . '?branch_id=' . $row->id . '&date= ' . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url6 = URL::to("admin/profit-loss/detailed/salary/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url7 = URL::to("admin/profit-loss/detailed/rent/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                $url8 = URL::to("admin/profit-loss/detailed/file_charge/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);
                $url9 = URL::to("admin/profit-loss/detailed/stationary_charge/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);
                $url10 = URL::to("admin/profit-loss/duplicate_passbook/?head=" . $head_id . "&branch_id=" . $row->id . "&date= " . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);
                $url11 = URL::to('admin/profit-loss/head_detail_report/' . $head_info->head_id . '/' . $head_info->labels . '?branch_id=' . $row->id . '&date= ' . $date . "&to_date=" . $to_date . "&financial_year=" . $financialYear);

                if (in_array(31, $ids)) {
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url . '"title="' . $head_info->sub_head . '"><i class="icon-pencil5  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(32, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url1 . '"title="' . $head_info->sub_head . '"><i class="icon-box  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(33, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url2 . '"title="' . $head_info->sub_head . '"><i class="icon-box  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(36, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url3 . '"title="' . $head_info->sub_head . '"><i class="icon-box  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                }

                /*if ($head_id == 97)
                {
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $btn .= '<a class="dropdown-item" href="' . $url5 . '" title="INTEREST ON LOAN TAKEN "><i class="fas fa-print mr-2"></i>INTEREST ON LOAN TAKEN </a>  ';
                }*/elseif (in_array(37, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url6 . '"title="' . $head_info->sub_head . '"><i class="icon-box  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(53, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url7 . '"title="' . $head_info->sub_head . '"><i class="icon-box  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(90, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url8 . '"title="' . $head_info->sub_head . '"><i class="icon-pencil5  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(34, $ids) || in_array(121, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url9 . '" title="' . $head_info->sub_head . '"><i class="icon-pencil5  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                } elseif (in_array(35, $ids) || in_array(139, $ids)) {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url10 . '" title="' . $head_info->sub_head . '"><i class="icon-pencil5  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                }
                // if ($head_info->parent_id == 42)

                // {

                //     $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                //     $btn .= '<a class="dropdown-item" href="' . $url11 . '" title="'.$head_info->sub_head.'"><i class="icon-pencil5  mr-2"></i>'.$head_info->sub_head.'</a>  ';

                // }
                else {

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url11 . '" title="' . $head_info->sub_head . '"><i class="icon-pencil5  mr-2"></i>' . $head_info->sub_head . '</a>  ';
                }
                $btn .= '</div></div></div>';

                $val['action'] = $btn;

                $rowReturn[] = $val;
            }

            //  print_r($rowReturn);die;
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $count,
                "data" => $rowReturn,
            );

            return json_encode($output);
        }
    }

    public function get_account_head_ids($head_ids, $subHeadsIDS, $is_level)
    {
        if ($is_level == false) {
            $record = AccountHeads::whereIn('head_id', $head_ids)->where('status', 0)->pluck('head_id')->toArray();
        } else {
            $subHeadsIDS2 = AccountHeads::whereIn('head_id', $subHeadsIDS)->pluck('parent_id')->toArray();
            if (count($subHeadsIDS2) > 0) {
                $head_ids = array_merge($head_ids, $subHeadsIDS2);
                $record = $this->get_account_head_ids($head_ids, $subHeadsIDS2, true);
            } else {
                $record = $this->get_account_head_ids($head_ids, $subHeadsIDS, false);
            }
        }
        return $record;
    }
    public function detailNewAjax(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $totalAmount = 0;
            $totalCount = 0;
            if ($arrFormData['is_search'] == 'yes') {
                $company_id = $arrFormData['company_id'];
                $branch_id = $arrFormData['branch_id'];
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date'])));
                $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['to_date'])));
                $head_id = $arrFormData['head_id'];
                if ($head_id) {
                    $data['headDetail'] = $head_info = getAccountHeadsDetails($head_id);
                }

                $child_head = [];
                if (gettype($data['headDetail']->child_head) == 'string') {
                    $data['headDetail']->child_head = explode(',', str_replace('[', '', str_replace(']', '', $data['headDetail']->child_head)));
                    foreach ($data['headDetail']->child_head as $val) {
                        $child_head[] = (int) $val;
                    }
                } else {
                    $child_head = $data['headDetail']->child_head;
                }

                // $child_head = \App\Models\AccountHeads::where('parent_id', (int)$head_id)->pluck('head_id');
                $data = \App\Models\AllHeadTransaction::with([
                    'AccountHeads:id,cr_nature,dr_nature,head_id,sub_head',
                    'memberCompany:id,customer_id,member_id',
                    'memberCompany.member:id,member_id,first_name,last_name,associate_no',
                    'member_investment',
                    'member_investment.demandadvice',
                    'member_investment.demandadvice.memberCompany:id,member_id,customer_id',
                    'member_investment.demandadvice.memberCompany.member:id,first_name,last_name,member_id,associate_no',
                    'member_investment.memberCompany:id,member_id,customer_id',
                    'member_investment.memberCompany.member:id,member_id',
                    'associateMember:id,first_name,last_name,associate_no,associate_id',
                    'savingAccount',
                    'fundTransferBranchToHo:id,bank_name',
                    'transactionType',
                    'loanFromBank',
                    'loanEmi',
                    'bankingLedger',
                    'expense',
                    'billExpense',
                    'rentPayment',
                    'demand_advices_fresh_expenses',
                    'salaryPayment',
                    'companybound',
                    'companyboundtransactions',
                    'salaryPayment.salary_employee',
                    'rentPayment.rentBankAccount',
                    'rentPayment.rentBank',
                    'receivedVoucher',
                    'DemandAdvice',
                    'rentPayment.rentLib',
                    // 'gst_payable',
                    'tds_payable',
                    // 'gst_transfer',
                    'tds_transfer',
                ])
                    ->where('is_deleted', 0)
                    ->whereIn('head_id', $child_head)
                    ->whereBetween('entry_date', [$startDate, $endDate]);
                if ($company_id != '') {
                    $data->where('company_id', $company_id);
                }
                if ($branch_id > 0) {
                    $data->where('branch_id', $branch_id);
                }
                $totalCount = $data->count('id');
                $data1 = $data->get();
                $sno = $_POST['start'];
                $rowReturn = [];
                $totalAmountData = $data->limit($_POST['start'])->orderBy('entry_date', 'asc')->get();

                foreach ($totalAmountData as $itemm) {
                    $c = 0;
                    $d = 0;
                    if ($itemm->payment_type == 'CR') {
                        $c = $itemm->amount;
                    }
                    if ($itemm->payment_type == 'DR') {
                        $d = $itemm->amount;
                    }
                    if (isset($itemm['AccountHeads']) && $itemm['AccountHeads']->cr_nature == 1) {
                        $total = (float) $c - (float) $d;
                        $totalAmount = $totalAmount + $total;
                    } else {
                        $total = (float) $d - (float) $c;
                        $totalAmount = $totalAmount + $total;
                    }
                }

                $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('entry_date', 'asc')->get();
                foreach ($data as $key => $value) {
                    $data = $this->balancesheettraitfunction($value);
                    $credit = '0.00';
                    $debit = '0.00';
                    $sno++;
                    $val['DT_RowIndex'] = $sno;

                    $val['date'] = date("d/m/Y", strtotime($value->created_at));

                    $val['main_id'] = ($data['memberId'] != "") ? $data['memberId'] : "N/A";

                    $val['associate_no'] = ($data['associateno'] != "") ? $data['associateno'] : "N/A";

                    $val['party_name'] = ($data['memberName'] != '') ? $data['memberName'] : (($data['associate_name']) ? $data['associate_name'] : 'N/A');

                    $val['account_no'] = $data['memberAccount'] ?? 'N/A';


                    if ($value->payment_type == 'CR') {
                        $credit = $value->amount;
                        $val['cr'] = number_format((float) $credit, 2, '.', '') . " &#8377;";
                    } else {
                        $val['cr'] = $credit;
                    }
                    if ($value->payment_type == 'DR') {
                        $debit = $value->amount;
                        $val['dr'] = number_format((float) $debit, 2, '.', '') . " &#8377;";
                    } else {
                        $val['dr'] = 0.00;
                    }
                    if ($value['AccountHeads']->cr_nature == 1) {

                        $total = (float) $credit - (float) $debit;
                        $totalAmount = $totalAmount + $total;
                    } else {
                        $total = (float) $debit - (float) $credit;
                        $totalAmount = $totalAmount + $total;
                    }
                    $val['balance'] = number_format((float) $totalAmount, 2, '.', '') . " &#8377;";
                    $rowReturn[] = $val;
                }
            } else {
                $rowReturn = [];
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $totalCount,
                "data" => $rowReturn,
                "total" => $totalAmount,
            );

            return json_encode($output);
        }
    }
    public function exportDetailNew(Request $request)
    {
        $title = strtolower(str_replace(' ', '_', $request->title));
        $input = $request->all();
        $company_id = $input['company_id'];
        $branch_id = $input['branch_id'];
        $start = $input["start"];
        $limit = $input["limit"];
        $startDate = date("Y-m-d", strtotime(convertDate($input['date'])));
        $endDate = date("Y-m-d", strtotime(convertDate($input['to_date'])));
        $head_id = $input['head_id'];
        if ($head_id) {
            $data['headDetail'] = getAccountHeadsDetails($head_id);
        }
        $child_head = [];
        if (gettype($data['headDetail']->child_head) == 'string') {
            $data['headDetail']->child_head = explode(',', str_replace('[', '', str_replace(']', '', $data['headDetail']->child_head)));
            foreach ($data['headDetail']->child_head as $val) {
                $child_head[] = (int) $val;
            }
        } else {
            $child_head = $data['headDetail']->child_head;
        }
        $data = \App\Models\AllHeadTransaction::with([
            'AccountHeads:id,cr_nature,dr_nature,head_id,sub_head',
            'memberCompany:id,customer_id,member_id',
            'memberCompany.member:id,member_id,first_name,last_name,associate_no',
            'member_investment',
            'member_investment.demandadvice',
            'member_investment.demandadvice.memberCompany:id,member_id,customer_id',
            'member_investment.demandadvice.memberCompany.member:id,first_name,last_name,member_id,associate_no',
            'member_investment.memberCompany:id,member_id,customer_id',
            'member_investment.memberCompany.member:id,member_id',
            'associateMember:id,first_name,last_name,associate_no,associate_id',
            'savingAccount',
            'fundTransferBranchToHo:id,bank_name',
            'transactionType',
            'loanFromBank',
            'loanEmi',
            'bankingLedger',
            'expense',
            'billExpense',
            'rentPayment',
            'demand_advices_fresh_expenses',
            'salaryPayment',
            'companybound',
            'companyboundtransactions',
            'salaryPayment.salary_employee',
            'rentPayment.rentBankAccount',
            'rentPayment.rentBank',
            'receivedVoucher',
            'DemandAdvice',
            'rentPayment.rentLib',
            'gst_payable',
            'tds_payable',
            'gst_transfer',
            'tds_transfer'
        ])
            ->where('is_deleted', 0)
            ->whereIn('head_id', $child_head)
            ->whereBetween('entry_date', [$startDate, $endDate]);
        if ($company_id != '') {
            $data->where('company_id', $company_id);
        }
        if ($branch_id > 0) {
            $data->where('branch_id', $branch_id);
        }
        if ($input['export'] == 0 && $input['is_search'] == 'yes') {
            $returnURL = URL::to('/') . "/asset/" . $title . ".csv";
            $fileName = env('APP_EXPORTURL') . "/asset/" . $title . ".csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }

        if ($input['export'] == 0) {
            $totalResults = $data->count('id');
            $results = $data->orderBy('entry_date', 'asc')->get();
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
            $totalAmount = 0.00;
            $totalAmountData = $data->limit($_POST['start'])->orderBy('entry_date', 'asc')->get();

            foreach ($totalAmountData as $itemm) {
                $c = 0;
                $d = 0;
                if ($itemm->payment_type == 'CR') {
                    $c = $itemm->amount;
                }
                if ($itemm->payment_type == 'DR') {
                    $d = $itemm->amount;
                }
                if (isset($itemm['AccountHeads']) && $itemm['AccountHeads']->cr_nature == 1) {
                    $total = (float) $c - (float) $d;
                    $totalAmount = $totalAmount + $total;
                } else {
                    $total = (float) $d - (float) $c;
                    $totalAmount = $totalAmount + $total;
                }
            }

            $results = $data->offset($_POST['start'])->limit($_POST['limit'])->orderBy('entry_date', 'asc')->get();
            foreach ($results as $value) {
                $data = $this->balancesheettraitfunction($value);
                $credit = '0.00';
                $debit = '0.00';
                $sno++;
                $val['S/No'] = $sno;

                $val['Date'] = date("d/m/Y", strtotime($value->created_at));

                $val['V.NO. / AC.NO. / M.ID / EMP.Code'] = ($data['memberId'] != "") ? $data['memberId'] : "N/A";

                $val['Name'] = ($data['memberName'] != '') ? $data['memberName'] : (($data['associate_name']) ? $data['associate_name'] : 'N/A');

                $val['Associate No.'] = ($data['associateno'] != "") ? $data['associateno'] : "N/A";

                $val['Account No'] = $data['memberAccount'] ?? 'N/A';


                if ($value->payment_type == 'CR') {
                    $credit = $value->amount;
                    $val['CR'] = number_format((float) $credit, 2, '.', '');
                } else {
                    $val['CR'] = $credit;
                }
                if ($value->payment_type == 'DR') {
                    $debit = $value->amount;
                    $val['DR'] = number_format((float) $debit, 2, '.', '');
                } else {
                    $val['DR'] = 0.00;
                }
                if ($value['AccountHeads']->cr_nature == 1) {

                    $total = (float) $credit - (float) $debit;
                    $totalAmount = $totalAmount + $total;
                } else {
                    $total = (float) $debit - (float) $credit;
                    $totalAmount = $totalAmount + $total;
                }
                $val['Balance'] = number_format((float) $totalAmount, 2, '.', '');
                if (!$headerDisplayed) {
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                fputcsv($handle, $val);
            }
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
    }
    public function page(Request $request)
    {
        $head_id = $request->head_id ? $request->head_id : null;
        $data['head_id'] = $head_id;
        $label = $request->label ? $request->label : null;
        $data['label'] = $label;
        $company_id = $request->company_id ? $request->company_id : null;
        $data['company_id'] = $company_id;
        $branch_id = $request->branch_id ? $request->branch_id : null;
        $data['branch_id'] = $branch_id;
        $data['branch'] = getBranchDetail($branch_id);
        $start_date = $request->start_date ? $request->start_date : null;
        $data['start_date'] = date('d/m/Y', strtotime(convertDate($start_date)));
        $end_date = $request->end_date ? $request->end_date : null;
        $data['end_date'] = date('d/m/Y', strtotime(convertDate($end_date)));
        if ($head_id) {
            $data['headDetail'] = $head_info = getAccountHeadsDetails($head_id);
        }
        $data['Allbranch'] = getCompanyBranch($company_id);
        $finacialYear = getFinacialYear();
        $data['dateStart'] = $finacialYear['dateStart'];
        $data['dateEnd'] = $finacialYear['dateEnd'];
        $data['title'] = 'Profit & Loss - ' . ucwords($head_info->sub_head);
        $data['filter'] = 'templates.admin.profit_loss2.profitLoss.filter';
        $data['script'] = 'templates.admin.profit_loss2.profitLoss.partials.details_script';
        $child_head = [];

        if (gettype($data['headDetail']->child_head) == 'string') {
            $data['headDetail']->child_head = explode(',', str_replace('[', '', str_replace(']', '', $data['headDetail']->child_head)));
            foreach ($data['headDetail']->child_head as $val) {
                $child_head[] = (int) $val;
            }
        } else {
            $child_head = $data['headDetail']->child_head;
        }
        if (!empty($child_head) && count($child_head) > 1) {
            $data['child'] = $child_head;
            $data['childHead'] = getHead($head_id, $label + 1);
            return view('templates.admin.profit_loss2.profitLoss.details', $data);
        } else {
            $data['script'] = 'templates.admin.profit_loss2.profitLoss.partials.list_script';
            $data['route'] = 'admin.profit-loss.curr_liability_detailBranchWise_listing';
            $data['array'] = [
                'S/N' => 'DT_RowIndex',
                'BR Name' => 'branch',
                'BR Code' => 'branch_code',
                // 'Total Member' => 'total_member',
                'Amount' => 'amount',
                'Comapny' => 'company',
                'Action' => 'action',
            ];
            return view('templates.admin.profit_loss2.profitLoss.listing', $data);
        }
    }
    public function currentDetail(Request $request)
    {
        $head_id = $request->head_id ? $request->head_id : null;
        $data['head_id'] = $head_id;
        $label = $request->label ? $request->label : null;
        $data['label'] = $label;
        $company_id = $request->company_id ? $request->company_id : null;
        $data['company_id'] = $company_id;
        $branch_id = $request->branch_id ? $request->branch_id : null;
        $data['branch_id'] = $branch_id;
        $data['branch'] = getBranchDetail($branch_id);
        $data['Allbranch'] = getCompanyBranch($company_id);
        $start_date = $request->start_date ? $request->start_date : null;
        $data['start_date'] = date('d/m/Y', strtotime(convertDate($start_date)));
        $end_date = $request->end_date ? $request->end_date : null;
        $data['end_date'] = date('d/m/Y', strtotime(convertDate($end_date)));
        if ($head_id) {
            $data['headDetail'] = $head_info = getAccountHeadsDetails($head_id);
        }
        $finacialYear = getFinacialYear();
        $data['dateStart'] = $finacialYear['dateStart'];
        $data['dateEnd'] = $finacialYear['dateEnd'];
        $data['title'] = 'Profit & Loss - ' . ucwords($head_info->sub_head);
        $data['filter'] = 'templates.admin.profit_loss2.profitLoss.filter';
        $data['script'] = 'templates.admin.profit_loss2.profitLoss.partials.details_script';
        $child_head = [];
        if (gettype($data['headDetail']->child_head) == 'string') {
            $data['headDetail']->child_head = explode(',', str_replace('[', '', str_replace(']', '', $data['headDetail']->child_head)));
            foreach ($data['headDetail']->child_head as $val) {
                $child_head[] = (int) $val;
            }
        } else {
            $child_head = $data['headDetail']->child_head;
        }
        if ($request->direct) {
            $data['script'] = 'templates.admin.profit_loss2.profitLoss.partials.list_script';
            $data['route'] = 'admin.profit-loss.curr_liability_detailBranchWise_listing';
            $data['array'] = [
                'S/N' => 'DT_RowIndex',
                'BR Name' => 'branch',
                'BR Code' => 'branch_code',
                // 'Total Member' => 'total_member',
                'Amount' => 'amount',
                'Comapny' => 'company',
                'Action' => 'action',
            ];
            return view('templates.admin.profit_loss2.profitLoss.listing', $data);
        }
        if (!empty($child_head) && (count($child_head) > 1)) {
            $data['child'] = $child_head;
            $data['childHead'] = getHead($head_id, $label + 1);
            return view('templates.admin.profit_loss2.profitLoss.details', $data);
        } else {
            $data['script'] = 'templates.admin.profit_loss2.profitLoss.partials.list_script';
            $data['route'] = 'admin.profit-loss.curr_liability_detailBranchWise_listing';
            $data['array'] = [
                'S/N' => 'DT_RowIndex',
                'BR Name' => 'branch',
                'BR Code' => 'branch_code',
                'Total Member' => 'total_member',
                'Amount' => 'amount',
                'Comapny' => 'company',
                'Action' => 'action',
            ];
            return view('templates.admin.profit_loss2.profitLoss.listing', $data);
        }
    }
    public function current_liabilityDetailBranchWiseListing(Request $request)
    {
        if ($request->ajax()) {
            $head_id = $request->head_id;
            $label = $request->label;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $branch = $request->branch_id;
            $company_id = $request->company_id;
            $head_info = AccountHeads::where('head_id', $head_id)->first();
            $parent_id1 = AccountHeads::where('head_id', $head_id)->first();
            $parent_id2 = AccountHeads::where('head_id', $parent_id1->parent_id)->first();
            $parent_id1 = $parent_id2;
            if ($parent_id2) {
                $parent_id3 = AccountHeads::where('head_id', $parent_id2->parent_id)->first();
                $parent_id1 = $parent_id3;
            }
            $data = Branch::orderBy('id', 'ASC')->when($branch, function ($q) use ($branch) {
                $q->whereId($branch);
            })
                ->when($company_id, function ($q) use ($company_id) {
                    $q->with([
                        'companybranchs' => function ($q) use ($company_id) {
                            $q->whereCompanyId($company_id)
                                ->with('company:id,name')
                                ->get();
                        }
                    ]);
                })
                ->when((!is_null(Auth::user()->branch_ids)), function ($q) use ($branch) {
                    $q->whereIn('id', explode(",", Auth::user()->branch_ids));
                });
            $data1 = $data->get();
            $count = count($data1);
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $data->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $head_ids = array($head_id);
                $subHeadsIDS = AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('parent_id')->toArray();
                if (count($subHeadsIDS) > 0) {
                    $head_ids = array_merge($head_ids, $subHeadsIDS);
                    $record = $this->get_account_head_ids($head_ids, $subHeadsIDS, true);
                }
                foreach ($record as $key => $value) {
                    $ids[] = $value;
                }
                $branch_id = $row->id;
                $startdate = '';
                $enddate = '';
                $val['DT_RowIndex'] = $sno;
                $val['branch'] = $row->name;
                $val['branch_code'] = $row->branch_code;
                $val['company'] = ($row->companybranchs) ? ($row->companybranchs->company[0] ? $row->companybranchs->company[0]->name : 'N/A') : 'N/A';
                $val['total_member'] = headTotalMember($head_id, $start_date, $end_date, $branch_id);
                $val['amount'] = "&#x20B9;" . number_format((float) headTotalNew($head_id, $start_date, $end_date, $branch_id, $company_id), 2, '.', '');
                $btn = '';
                $btn .= '<div class="list-icons">
                            <div class="dropdown">
                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="
                                ';

                $key = '123456789987654321';
                $dataArray = [
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'head_id' => $head_id,
                    'branch_id' => $row->id,
                    'company_id' => $company_id,
                    'label' => $label,
                ];
                $encryptedData = Crypt::encrypt($dataArray, $key);
                $name = str_replace('_', ' ', ucwords($head_info->sub_head));
                $urladd = 'admin/profit-loss/' . $encryptedData;
                $url = URL::to($urladd);
                $btn .= $url . '" title="' . $name . '"><i class="fas fa-print mr-2"></i>' . $name . '</a></div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    public function datatable($key)
    {
        $p = '123456789987654321';
        $decryptData = Crypt::decrypt($key, $p);
        $data['head_id'] = $decryptData['head_id'];
        $data['start_date'] = $decryptData['start_date'];
        $data['end_date'] = $decryptData['end_date'];
        $data['company_id'] = $decryptData['company_id'];
        $data['branch_id'] = $decryptData['branch_id'];
        $data['label'] = $decryptData['label'];
        $data['branches'] = Branch::where('status', 1)->pluck('name', 'id');
        $data['title'] = "Profit & Loss Account - " . getAcountHead($data['head_id']);
        return view('templates.admin.profit_loss.detail_new', $data);
    }
}