<?php
namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Branch;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\Memberinvestments;
use App\Models\CommissionLeaserMonthly;
use App\Models\Daybook;
use App\Models\SavingAccountTransactionView;
use App\Models\Memberinvestmentsnominees;

use App\Http\Controllers\Branch\CommanTransactionsController;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Scopes\ActiveScope;
use Session;
use Image;
use Redirect;
use URL;
use DB;

/*
|---------------------------------------------------------------------------
| Branch Panel -- Member Management MemberController
|--------------------------------------------------------------------------
|
| This controller handles members all functionlity.
*/
class PassbookController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }

    /**
     * All Investment Listing.
     * Route: /member/passbook
     * Method: get
     * @return  array()  Response
     */
    public function index()
    {
        if (!in_array('Passbook view', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()

                ->route('branch.dashboard');

        }
        $data['title'] = 'Passbook | Listing';
        $data['branch'] = Branch::where('status', 1)->get();

        return view('templates.branch.passbook.index', $data);
    }


    /**
     * Fetch accounts listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function accountListing(Request $request)
    {

        if ($request->ajax()) {
            $arrFormData = array();
            //   print_r($_POST);die;
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                //  echo $arrFormData['branch_id'];die;

                $data = Memberinvestments::leftJoin('plans', 'plans.id', '=', 'member_investments.plan_id')->leftJoin('members', 'members.id', '=', 'member_investments.customer_id')->leftJoin('member_companies', 'member_companies.id', '=', 'member_investments.member_id');
                $b_id = getUserBranchId(Auth::user()->id)->id;
                $data = $data->where('member_investments.branch_id', '=', $b_id);


                /*if($arrFormData['branch_id'] !=''){
                $id=$arrFormData['branch_id'];
                $data=$data->where('member_investments.branch_id','=',$id);
                }*/
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->where('member_investments.account_number', '=', $meid);
                }
                if ($arrFormData['member_name'] != '') {
                    $name = $arrFormData['member_name'];
                    $data = $data->where(function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('member_investments.company_id', $company_id);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(str_replace('/', '-', $arrFormData['start_date'])));

                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(str_replace('/', '-', $arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    //dd("dd", $startDate, $endDate, date("Y-m-d", strtotime( str_replace('/','-', $arrFormData['start_date'] ) ) ) );
                    $data = $data->whereBetween(\DB::raw('DATE(member_investments.created_at)'), [$startDate, $endDate]);
                }




                $data1 = $data->orderBy('id', 'DESC')->get(['member_investments.id', 'member_investments.member_id', 'member_investments.customer_id', 'members.first_name', 'members.last_name', 'members.id as mid', 'plans.name', 'member_investments.account_number', 'plans.plan_code', 'member_investments.created_at', 'members.member_id as customer_id', 'member_investments.branch_id', 'member_investments.is_passbook_print', 'member_investments.company_id', 'member_companies.member_id as memberCode', 'plans.plan_category_code']);
                $count = count($data1);

                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['member_investments.id', 'member_investments.member_id', 'member_investments.customer_id', 'members.first_name', 'members.last_name', 'members.id as mid', 'plans.name', 'member_investments.account_number', 'plans.plan_code', 'member_investments.created_at', 'members.member_id as customer_id', 'member_investments.branch_id', 'member_investments.is_passbook_print', 'member_investments.company_id', 'member_companies.member_id as memberCode', 'plans.plan_category_code']);

                $bid = getUserBranchId(Auth::user()->id)->id;
                $totalCount = Memberinvestments::where('member_investments.branch_id', '=', $bid)->count();


                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['date'] = date("d/m/Y", strtotime($row->created_at));
                    $val['plan'] = $row->name;

                    $val['member'] = $row->first_name . ' ' . $row->last_name;
                    $val['account_no'] = $row->account_number;
                    $branch = Branch::where('id', $row->branch_id)->first();
                    $branch_name = $branch->name;
                    $val['branch_name'] = $branch_name;
                    $printPassbook = 'No';
                    if ($row->is_passbook_print == 1) {
                        $printPassbook = 'Yes';
                    }

                    $val['is_passbook_print'] = $printPassbook;
                    $val['branch_code'] = $branch->branch_code;
                    $val['sector_name'] = $branch->sector;
                    $val['region_name'] = $branch->regan;
                    $val['zone_name'] = $branch->zone;

                    $val['member_id'] = $row->memberCode;
                    $val['customer_id'] = $row->customer_id;
                    $url_new = URL::to("branch/member/passbook/cover_new/" . $row->id);

                    if ($row->plan_categry_code == 's') {
                        $countSSBTran = getSavingAccountId($row->id);
                    }

                    $url = URL::to("branch/member/passbook/cover/" . $row->id);
                    $urlMaturity = URL::to("branch/member/passbook/maturity/" . $row->id);
                    $url2 = URL::to("branch/member/passbook/transaction/" . $row->id . '/' . $row->plan_category_code);

                    $url_new = URL::to("branch/member/passbook/cover_new/" . $row->id);
                    $url2_new = URL::to("branch/member/passbook/transaction_new/" . $row->id . '/' . $row->plan_category_code);

                    $url3 = URL::to("branch/member/passbook/certificate/" . $row->id . '/' . $row->plan_category_code);
                    $btn = "";
                    $btn_cover = "";
                    $btn_tan = "";
                    $btn_maturity = "";
                    if (in_array('Maturity View', auth()->user()->getPermissionNames()->toArray())) {

                        if ($row->plan_category_code != 'S') {
                            $btn_maturity .= '<a class="" href="' . $urlMaturity . '" title="Maturity Print" target="_blank"><i class="fas fa-print  text-default mr-2"> </i></a>';
                        }
                    }

                    if ($row->plan_category_code == 'F') {

                        $btn .= '<a class="" href="' . $url3 . '" title="Certificate" target="_blank"><i class="fas fa-certificate text-default mr-2"></i></a> ';


                    } else {
                        // if( in_array('Passbook Cover View', auth()->user()->getPermissionNames()->toArray() ) ) {

                        //     $btn_cover.= '<a class="" href="'.$url.'" title="Passbook Cover" target="_blank"><i class="fas fa-sticky-note"></i>

                        //     </a>  ';

                        // }
                        if (in_array('New Passbook Cover View', auth()->user()->getPermissionNames()->toArray())) {

                            if ($row->plan_category_code == 'S') {
                                $countSSBTran = SavingAccountTranscation::where('account_no', $row->account_number)->count('id');

                                if ($countSSBTran > 0) {
                                    $btn_cover .= '<a class="" href="' . $url_new . '" title="Passbook New Cover" target="_blank"><i class="far fa-sticky-note"></i></a>';
                                }
                            } else {
                                $btn_cover .= '<a class="" href="' . $url_new . '" title="Passbook New Cover" target="_blank"><i class="far fa-sticky-note"></i></a>';
                            }


                        }




                        $btn_tan .= '<a class="" href="' . $url2 . '" title="Old Passbook Transaction"><i class=" fa fa-file text-default mr-2">  </i></a>';
                        if (in_array('New Passbook Transactions View', auth()->user()->getPermissionNames()->toArray())) {
                            $btn_tan .= '<a class="" href="' . $url2_new . '" title="New Passbook Transaction"><i class="far fa-file-alt text-default mr-2"> </i></a>';
                        }


                    }
                    $val['cover'] = $btn_cover;
                    $val['maturity'] = $btn_maturity;
                    $val['transaction'] = $btn_tan;
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

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
    public function passbookCover($id)
    {
        $stateid = getBranchState(Auth::user()->username);
        $data['IntraState'] = '';
        $data['gst_percentage'] = '';
        $totalAmount = 50;

        $data['title'] = 'Passbook | Cover';
        $data['passbook'] = Memberinvestments::with([
            'ssb_detail' => function ($query) {
                $query->select('id', 'passbook_no', 'member_investments_id', 'account_no');
            }
        ])->with([
                    'branch' => function ($query) {
                        $query->select('id', 'name', 'state_id');
                    }
                ])->with([
                    'member' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'member_id', 'father_husband', 'address', 'state_id', 'city_id', 'pin_code', 'district_id', 'village');
                    }
                ])->with([
                    'plan' => function ($query) {
                        $query->select('id', 'name', 'plan_code');
                    }
                ])->where('id', $id)->first(['id', 'company_id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'passbook_no', 'daughter_name', 'dob', 're_dob', 're_name']);
        //print_r($data['passbook']);die;
        $data['correctionStatus'] = getCorrectionStatus(5, $id);
        $data['id'] = $id;

        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 35)->first();
        $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->whereCompanyId($data['passbook']->company_id)->first();
        $data['gstAmount'] = 0;
        $totalAmount = 50;
        if (isset($getHeadSetting->gst_percentage) && !empty($getGstSetting)) {
            if ($data['passbook']['branch']->state_id == $getGstSetting->state_id) {
                $data['gstAmount'] = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100) / 2;

                $data['IntraState'] = false;



            } else {
                $data['gstAmount'] = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100);
                $data['IntraState'] = true;
            }
            $data['gst_percentage'] = $getHeadSetting->gst_percentage;
        }

        return view('templates.branch.passbook.cover', $data);
    }
    public function coverPrint(Request $request)
    {
        $id = $request->id;
        $data['is_passbook_print'] = 1;
        $data['print_request'] = NULL;
        $data['print_date'] = date("Y-m-d H:i:s");
        $investment = Memberinvestments::find($id);
        $investment->update($data);
        return json_encode(array('msg_type' => 'success'));

    }

    public function passbookTransaction($id, $code)
    {
        /*
        if (!in_array('Investment Transaction', auth()->user()->getPermissionNames()->toArray()) && !in_array('Member Investment Transaction', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code;
        if ($code == 'S') {
            $data['accountDetail'] = $accountDetail =  SavingAccount::where('member_investments_id', $id)->first();
            // $data['lastId'] = SavingAccountTranscation::where('saving_account_id', $data['accountDetail']->id)->orderBy('id', 'DESC')->first();
            // $data['countRecord'] = SavingAccountTranscation::where('saving_account_id', $data['accountDetail']->id)->count();
            $data['lastId'] = SavingAccountTransactionView::where('saving_account_id', $accountDetail->id)->orderBy('opening_date', 'DESC')->first();
            $data['countRecord'] = SavingAccountTransactionView::where('saving_account_id', $accountDetail->id)->count('id');
        } else {
            // $data['accountDetail'] = Memberinvestments::with(['plan' => function ($q) {$q->withoutGlobalScope(ActiveScope::class);}])->where('id', $id)->first();
            // $data['lastId'] = Daybook::where('investment_id', $id)->orderBy('id', 'DESC')->first();
            // $data['countRecord'] = Daybook::where('investment_id', $id)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->count();
            $data['accountDetail'] = $accountDetail = Memberinvestments::with('plan')->whereHas('plan', function ($q) {$q->withoutGlobalScope(ActiveScope::class)->where('plan_category_code', '!=', 'S');})->where('id', $id)->first();
            $data['lastId'] = Daybook::where('investment_id', $id)->orderBy('id', 'DESC')->first();
            $data['countRecord'] = Daybook::where('account_no', $accountDetail->account_number)->whereIn('payment_mode',[0,4])->whereIn('transaction_type',[2,4])->where('is_deleted', 0)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->count('id');
        }
        if ($data['lastId'] && isset($data['lastId'])) {
            $data['correctionStatus'] = getCorrectionStatus(3, $data['lastId']->id);
            $data['iId'] = $data['lastId']->id;
        } else {
            $data['correctionStatus'] = '';
            $data['iId'] = '';
        }
        */
        if (!in_array('Investment Transaction', auth()->user()->getPermissionNames()->toArray()) && !in_array('Member Investment Transaction', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['button_show'] = 0;
        $data['iId'] = '';
        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = [];
        $data['accountDetail'] = [];
        $data['code'] = $code;
        $lastId = '';
        if ($code == 'S') {
            $data['accountDetail'] = $accountDetail = SavingAccount::has('company')->with('getMemberinvestments:id,renewal_correction_request')
                ->where('member_investments_id', $id)
                ->first();
            if ($accountDetail) {
                $data['lastId'] = $lastId = SavingAccountTranscation::has('company')->where('saving_account_id', $accountDetail->id)->where('is_deleted', 0)->orderBy('created_at_default', 'DESC')->first();
                // if (isset($lastId) && $lastId->type == 2 && ($lastId->payment_mode == 0 || $lastId->payment_mode == 4) ) {
                if (isset($lastId) && $lastId->type == 2) {
                    // condication : the task transaction is renwal then only will show the correction btn.
                    if ($accountDetail->getMemberinvestments->renewal_correction_request == 0) {
                        $data['button_show'] = 1;
                        $data['iId'] = $id = $lastId->id;
                    }
                }
            }
        } else {
            $data['accountDetail'] = $accountDetail = Memberinvestments::with('plan')
                ->whereHas('plan', function ($q) {
                    $q->withoutGlobalScope(ActiveScope::class)->where('plan_category_code', '!=', 'S');
                })
                ->where('id', $id)
                // ->where('is_mature','1')
                ->first();
            if (isset($accountDetail) && ($accountDetail->is_mature == 1) && ($accountDetail->renewal_correction_request == 0)) {
                $data['lastId'] = $lastId = Daybook::where('account_no', $accountDetail->account_number)
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'DESC')
                    ->first(['id', 'transaction_type', 'payment_mode', 'created_at', 'investment_id', 'payment_mode', 'payment_type', 'amount']);
                // if ($lastId->transaction_type == 4 && ($lastId->payment_mode == 0 || $lastId->payment_mode == 4)) {
                if ($lastId->transaction_type == 4) {
                    // condication : the task transaction is renwal then only will show the correction btn.
                    $data['button_show'] = 1;
                    $data['iId'] = $id = $lastId->id;
                }
            }
        }
        if (($lastId)) {
            $createdAt = $lastId->created_at;
            $comMonth = date("m", strtotime($createdAt));
            $comYear = date("Y", strtotime($createdAt));
            $countCommLe = CommissionLeaserMonthly::where('year', $comYear)->where('month', $comMonth)->count();
            if ($countCommLe > 0) {
                $data['button_show'] = 0;
            }
        }
        return view('templates.branch.passbook.transaction', $data);
    }
    public function transactionList(Request $request)
    {

        if ($request->ajax()) {
            // print_r($_POST);die;
            $code = $request->code;
            if ($request->code == 'S') {
                // $data = SavingAccountTranscation::where('saving_account_id', $request->id)->where('is_deleted', 0)->orderBy('id', 'DESC')->orderBy(\DB::raw('date(created_at)'), 'DESC')->get();
                $data = SavingAccountTransactionView::where('saving_account_id', $request->id)->orderBy('opening_date', 'DESC')->get();

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tranid', function ($row) {
                        $tranid = $row->transaction_id;
                        return $tranid;
                    })
                    ->rawColumns(['tranid'])
                    ->addColumn('date', function ($row) {
                        $date = date("d/m/Y", strtotime($row->opening_date));
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addIndexColumn()
                    ->addColumn('tran_by', function ($row) {
                        $tran_by = $row->transaction_by;
                        return $tran_by;
                    })
                    ->rawColumns(['tran_by'])
                    ->addColumn('description', function ($row) {
                        $description = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
                        return $description;
                    })
                    ->rawColumns(['description'])
                    ->addColumn('reference_no', function ($row) use ($code) {
                        $reference_no = '';
                        if ($row->payment_mode == 1) {
                            $reference_no = $row->cheque_dd_no;
                        }
                        if ($row->payment_mode == 4 || $row->payment_mode == 5) {
                            $reference_no = $row->reference_no;
                        }
                        if ($row->payment_mode == 3) {
                            $reference_no = $row->online_payment_id;
                        }

                        return $reference_no;
                    })
                    ->rawColumns(['reference_no'])
                    ->addColumn('withdrawal', function ($row) {
                        if ($row->withdrawal > 0) {
                            $withdrawal = $row->withdrawal;
                        } else {
                            $withdrawal = '';
                        }
                        return $withdrawal;
                    })
                    ->rawColumns(['withdrawal'])
                    ->addColumn('deposit', function ($row) {
                        if ($row->deposit > 0) {
                            $deposit = $row->deposit;
                        } else {
                            $deposit = '';
                        }
                        return $deposit;
                    })
                    ->rawColumns(['deposit'])
                    ->addColumn('opening_balance', function ($row) {

                        $opening_balance = $row->opening_balance;

                        return $opening_balance;
                    })
                    ->rawColumns(['opening_balance'])

                    ->addColumn('action', function ($row) {
                        $url = URL::to("branch/member/passbook/ssbtransaction/" . $row->id . "");
                        $btn = '<a class="dropdown-item" href="' . $url . '" title="Detail"><i class="fas fa-eye    mr-2"></i></a>  ';
                        $url2 = URL::to("branch/investment/renew/ssbtransaction/receipt/" . $row->id . "");
                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        // if($row->payment_type == 'CR')
                        // {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Receipt" ><i class="fa fa-file  mr-2"></i></a>  ';
                        // }

                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } else {


                $accountDetail = Memberinvestments::with('plan')
                    ->whereHas('plan', function ($query) {
                        $query->where('plan_category_code', '!=', 'S');
                    })
                    ->where('id', $request->id)
                    ->first();

                // $data = Daybook::where('account_no', $accountDetail->account_number)
                //     ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                //     ->where('is_deleted', 0)
                //     ->orderByDesc('created_at')
                //     ->orderByDesc('id')
                //     ->get();
                $data = Daybook::selectRaw('*, (
                    SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                    FROM day_books AS sub
                    WHERE sub.account_no = day_books.account_no
                    AND (sub.created_at) <= (day_books.created_at)
                    AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                    AND sub.is_deleted = 0

                ) AS opening_balance')
                    ->where('account_no', $accountDetail->account_number)
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();




                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('tranid', function ($row) {
                        $tranid = $row->id;
                        return $tranid;
                    })
                    ->rawColumns(['tranid'])
                    ->addColumn('date', function ($row) {
                        $date = date("d/m/Y", strtotime($row->created_at));
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addIndexColumn()
                    ->addColumn('tran_by', function ($row) {
                        $tran_by = (($row->is_app == 1) ? 'Associcate' : (($row->is_app == 2) ? 'E-Passbook' : 'Software'));
                        return $tran_by;
                    })
                    ->rawColumns(['tran_by'])
                    ->addColumn('description', function ($row) {
                        $description = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
                        return $description;
                    })
                    ->rawColumns(['description'])
                    ->addColumn('reference_no', function ($row) use ($code) {
                        $reference_no = '';
                        if ($row->payment_mode == 1) {
                            $reference_no = $row->cheque_dd_no;
                        }
                        if ($row->payment_mode == 4 || $row->payment_mode == 5) {
                            $reference_no = $row->reference_no;
                        }
                        if ($row->payment_mode == 3) {
                            $reference_no = $row->online_payment_id;
                        }

                        return $reference_no;
                    })
                    ->rawColumns(['reference_no'])
                    ->addColumn('withdrawal', function ($row) {
                        if ($row->withdrawal > 0) {
                            $withdrawal = $row->withdrawal;
                        } else {
                            $withdrawal = '';
                        }
                        return $withdrawal;
                    })
                    ->rawColumns(['withdrawal'])
                    ->addColumn('deposit', function ($row) {
                        if ($row->deposit > 0) {
                            $deposit = $row->deposit;
                        } else {
                            $deposit = '';
                        }
                        return $deposit;
                    })
                    ->rawColumns(['deposit'])
                    ->addColumn('opening_balance', function ($row) {

                        $opening_balance = $row->opening_balance;

                        return $opening_balance;
                    })
                    ->rawColumns(['opening_balance'])
                    ->addColumn('action', function ($row) {
                        $url = URL::to("branch/member/passbook/transaction/" . $row->id . "");
                        $btn = '<a class="dropdown-item" href="' . $url . '" title="Member Detail"><i class="fas fa-eye  mr-2"></i></a>  ';
                        $url2 = URL::to("branch/investment/renew/receipt/" . $row->id . "");
                        // if($row->payment_type == 'CR')
                        // {
                        if($row->transaction_type != 18){
                            $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Receipt" ><i class="fa fa-file  mr-2"></i></a>  ';
                        }

                        /*$btn = '<a class="dropdown-item" href="javascript:void(0);" title="Member Detail"><i class="icon-eye  mr-2"></i>View</a>  ';*/
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
        }
    }

    public function viewTransaction($id)
    {
        $data['title'] = 'Transaction Detail';
        $data['tDetails'] = Daybook::where('id', $id)->first();
        $data['accountData'] = Memberinvestments::with([
            'member' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'member_id']);
            }
        ])
            ->with([
                'memberCompany' => function ($q) {
                    $q->select(['id', 'member_id']);
                }
            ])->where('id', $data['tDetails']->investment_id)->first();
        ;
        $data['tDetails']['type'] = 1;
        return view('templates.branch.passbook.viewtransaction', $data);
    }

    public function viewssbTransaction($id)
    {
        $data['title'] = 'Transaction Detail';
        $data['tDetails'] = SavingAccountTransactionView::where('id', $id)->first();
        // print_r($data['tDetails'] );die;
        $mId = SavingAccount::with([
            'ssbcustomerDataGet' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'member_id']);
            }
        ])
            ->with([
                'ssbmembersDataGet' => function ($q) {
                    $q->select(['id', 'member_id']);
                }
            ])
            ->where('id', $data['tDetails']->saving_account_id)->first(['member_investments_id', 'member_id', 'account_no', 'customer_id', 'id']);

        $aId = Memberinvestments::select('associate_id')->where('id', $mId->member_investments_id)->first();
        $data['tDetails']['member_id'] = $mId->member_id;
        $data['tDetails']['customer_id'] = $mId->member_id;

        if ($data['tDetails']->associate_id > 0) {
            $data['tDetails']['associate_id'] = $data['tDetails']->associate_id;
        } else {
            $data['tDetails']['associate_id'] = $aId->associate_id;
        }
        $data['tDetails']['account_no'] = $mId->account_no;
        $data['accountData'] = $mId;

        $data['tDetails']['type'] = 0;
        return view('templates.branch.passbook.viewtransaction', $data);
    }

    public function transactionStart(Request $request)
    {
        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code = $request['code'];
        $start = $request['transaction_id_from'];
        $end = $request['transaction_id_to'];
        if (Request1::isMethod('get')) {
            return back()->with('alert', 'Please submit form');
        }
        $data['eliOpeningAmount'] = 0.00;
        $data['accountsNumber'] = array('R-066523000256', 'R-084511001514', 'R-066523000257', 'R-066523000620', 'R-084523000800', 'R-066523000510', 'R-066523000258', 'R-084504006762', 'R-066504006287', 'R-066523000509', 'R-066523000588', 'R-084504007930', 'R-066504007682', 'R-066504007645');
        $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
        $endDate = date("Y-m-d", strtotime(convertDate($request['end_date'])));
        try {
            if ($code == 'S') {
                $data['accountDetail'] = SavingAccount::has('company')->where('id', $request['id'])->first();
                $data['accountTranscation'] = SavingAccountTransactionView::where('saving_account_id', $request['id'])
                    ->whereBetween(\DB::raw('id'), [$start, $end])
                    /* ->whereBetween(\DB::raw('DATE(opening_date)'), [$startDate, $endDate])*/
                    ->orderby('opening_date', 'ASC')
                    ->get();
            } else {
                $data['accountDetail'] = Memberinvestments::with('plan')/*->whereHas('plan', function ($query) {
$query->where('plan_category_code', '!=', 'S');
})*/ ->where('id', $request['id'])->first();
                $data['eliOpeningAmount'] = Daybook::where('investment_id', $request['id'])
                    ->whereIn('account_no', $data['accountsNumber'])
                    ->where('is_eli', 1)
                    ->where(function ($q) {
                        $q->where('transaction_type', 2)
                            ->where('is_deleted', 0)
                            ->orWhere('transaction_type', 4);
                    })
                    ->pluck('amount')
                    ->first();
                $data['eliOpeningAmount'] = $data['eliOpeningAmount'] != null ? round($data['eliOpeningAmount'], 2) : 0.00;
                $data['accountTranscation'] = Daybook::selectRaw('*, (
                    SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                    FROM day_books AS sub
                    WHERE sub.account_no = day_books.account_no
                    AND (sub.created_at) <= (day_books.created_at)
                    AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                    AND sub.is_deleted = 0
                ) AS opening_balance')
                    ->whereBetween(\DB::raw('id'), [$start, $end])
                    ->where('account_no', $data['accountDetail']->account_number)
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } catch (\Exception $ex) {
            return back()->with('alert', $ex->getMessage());
        }
        return view('templates.branch.passbook.transaction_print', $data);
    }


    public function payPrintOld(Request $request)
    {
        $id = $request->id;
        DB::beginTransaction();

        try {
            $data = Memberinvestments::with([
                'branch' => function ($query) {
                    $query->select('id', 'branch_code');
                }
            ])->with([
                        'member' => function ($query) {
                            $query->select('id', 'first_name', 'last_name', 'member_id');
                        }
                    ])->first(['id', 'member_id', 'branch_id']);
            $transaction_type = 7;
            $payment_mode = 0; //0 for cash
            $deposit_by_name = $data['member']->first_name . ' ' . $data['member']->last_name;
            $deposit_by_id = $memberId = $data['member']->id;
            $amountArray = array('1' => 50);
            $branch_id = $data['branch']->id;
            $branchCode = $data['branch']->branch_code;

            $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, $transaction_type, $table_id = '0', $memberId, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no = '0', $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'CRC');
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return json_encode(array('msg_type' => $ex->getMessage()));
        }
        if ($createTransaction) {
            $data_inv['is_passbook_print'] = 1;
            $data_inv['print_request'] = NULL;
            $data_inv['print_date'] = date("Y-m-d H:i:s");
            $investment = Memberinvestments::find($id);
            $investment->update($data_inv);
            return json_encode(array('msg_type' => 'success'));
        } else {
            return json_encode(array('msg_type' => 'error'));
        }

    }



    public function payPrint(Request $request)
    {
        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        Session::put('created_at', $globaldate);


        $id = $request->id;
        // DB::beginTransaction();

        // try {
        $data = Memberinvestments::with([
            'ssb_detail' => function ($query) {
                $query->select('id', 'passbook_no', 'member_investments_id', 'account_no');
            }
        ])->with([
                    'branch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'state_id');
                    }
                ])->with([
                    'plan' => function ($query) {
                        $query->select('id', 'name', 'plan_code');
                    }
                ])->with([
                    'member' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'member_id');
                    }
                ])->where('id', $id)->first(['id', 'member_id', 'branch_id', 'plan_id', 'customer_id', 'company_id']);

        $transaction_type = 7;
        $payment_mode = 0; //0 for cash
        $deposit_by_name = $data['member']->first_name . ' ' . $data['member']->last_name;
        $deposit_by_id = $memberId = $data->member_id;
        $companyId = $data['company_id'];
        // dd($companyId);
        $associate_id = $data['id'];
        $amountArray = array('1' => 50);

        $getBranchId = getUserBranchId(Auth::user()->id);

        $branch_id = $getBranchId->id;

        $branchCode = getBranchCode($branch_id)->branch_code;
        // dd($data);
        if ($data['plan']->plan_code == 'S') {
            $type_id = $data['ssb']->id;
            $type = 4;
            $sub_type = 44;
            $type1 = 4;
            $sub_type_cgst = 419;
            $sub_type_sgst = 420;
            $sub_type_igst = 421;
        } else {
            $type_id = $data->id;
            $type = 3;
            $sub_type = 33;
            $type1 = 3;
            $sub_type_cgst = 318;
            $sub_type_sgst = 319;
            $sub_type_igst = 320;
        }
        $totalAmount = 50;
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 139)->first();


        $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $globaldate)->whereCompanyId($data->company_id)->exists();
        $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id')->where('applicable_date', '<=', $globaldate)->whereCompanyId($data->company_id)->where('state_id', $stateid)->first();
        //Gst Insuramce

        if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
            if ($data['branch']->state_id == $getGstSettingno->state_id) {
                $gstAmount = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100) / 2;
                $cgstHead = 171;
                $sgstHead = 172;
                $IntraState = true;

            } else {
                $gstAmount = ceil($totalAmount * $getHeadSetting->gst_percentage) / 100;
                $cgstHead = 170;
                $IntraState = false;
            }


        } else {
            $gstAmount = 0;
        }

        // $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, $transaction_type, $type_id, $memberId, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no = '0', $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'CRC');

        /************************* Account head impelment ********************/

        $amount_to_id = $amount_to_name = $amount_from_id = $amount_from_name = $v_no = $v_date = $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = $entry_date = $entry_time = $created_by = $created_by_id = $is_contra = $contra_id = $created_at = $updated_at = $type_transaction_id = $ssb_account_id_to = $cheque_bank_from_id = $cheque_bank_ac_from_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = NULL;
        $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;

        $bank_id = NULL;
        $bank_ac_id = NULL;
        $type_transaction_id = NULL;


        $totalAmount = 50;
        $daybookRef = CommanTransactionsController::createBranchDayBookReferenceNew($totalAmount, $globaldate);
        $refId = $daybookRef;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 2;
        $created_by_id = Auth::user()->id;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $payment_type = 'CR';
        $payment_mode = 0;
        $currency_code = 'INR';

        $head1 = 3;
        $head2 = 13;
        $head3 = 35;
        $head4 = NULL;
        $head5 = NULL;
        $head12 = 2;
        $head22 = 10;
        $head32 = 28;
        $head42 = 71;
        $head52 = NULL;

        $des = 'Cash received from member ' . $deposit_by_name . '(' . $data['member']->member_id . ') through  duplicate passbook printing';
        $desDR = 'Cash A/c Dr 50/-';
        $desCR = 'To ' . $deposit_by_name . '(' . $data['member']->member_id . ') A/c Cr 50/-';




        $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $totalAmount, $des, $desDR, $desCR, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);

        /// passbook head
        $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $totalAmount, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


        // cash head
        $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $totalAmount, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

        // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $totalAmount, 'Duplicate Passbook Printing Fee', $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);




        if ($gstAmount > 0) {
            if ($IntraState) {

                $descA = 'Dublicate Passbook  Cgst Charge';
                $descB = 'Dublicate Passbook Sgst Charge';
                $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, $descA, $descA, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);




                /// passbook head
                // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


                // cash head
                // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $gstAmount, $descA, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                /**
                 * SGST
                 **/
                $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descB, $descB, $descB, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);


                /// passbook head
                // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descB, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                // cash head
                // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descB, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $gstAmount, $descB, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);



            } else {
                $descA = 'Dublicate Passbook  Igst Charge';
                $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, $descA, $descA, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);




                /// passbook head
                // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                // cash head
                // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $gstAmount, $descA, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

            }

            $createdGstTransaction = CommanController::gstTransaction($dayBookId = $refId, $getGstSettingno->gst_no, (!isset($data['member']->gst_no)) ? NULL : $data['member']->gst_no, $totalAmount, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $totalAmount + $gstAmount + $gstAmount : $totalAmount + $gstAmount, 35, $entry_date, 'DP35', $data['member']->id, $branch_id);

        }

        /******** Balance   entry ***************/
        // $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $totalAmount + $gstAmount, 0);
        // // dd("hi");
        // $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $totalAmount + $gstAmount, 0);
        /************************* Account head impelment ********************/
        //     DB::commit();
        // } catch (\Exception $ex) {
        //     DB::rollback();
        //     return json_encode(array('msg_type' => $ex->getMessage()));
        // }
        // if ($createTransaction) {
        $data_inv['is_passbook_print'] = 1;
        $data_inv['print_request'] = NULL;
        $data_inv['print_date'] = date("Y-m-d H:i:s");
        $investment = Memberinvestments::find($id);
        $investment->update($data_inv);
        return json_encode(array('msg_type' => 'success'));
        // } else {
        //     return json_encode(array('msg_type' => 'error'));
        // }

    }

    public function certificate($id, $code)
    {

        $stateid = getBranchState(Auth::user()->username);
        $data['title'] = 'Passbook | Certificate';
        $data['code'] = $code;
        $tenureAll = Memberinvestments::where('id', $id)->first(['id', 'tenure', 'created_at']);
        $effectiveDate = date('Y-m-d', strtotime($tenureAll->created_at));
        $tenure = round($tenureAll->tenure * 12, 0);
        $data['certificate'] = Memberinvestments::with([
            'branch:id,name,zone,regan,sector,state_id',
            'member:id,first_name,last_name,member_id,father_husband,address,state_id,city_id,pin_code,district_id',
            'plan' => function ($query) use ($tenure, $effectiveDate) {
                $query->select('id', 'name', 'plan_code', 'plan_sub_category_code')
                    ->with([
                        'plantenure' => function ($q) use ($tenure, $effectiveDate) {
                            $q->whereTenure($tenure)
                                ->whereColumn('tenure', 'month_to')
                                ->where('effective_from', '<=', $effectiveDate)
                                ->where(function ($query) use ($effectiveDate) {
                                    $query->where('effective_to', '>=', $effectiveDate)
                                        ->orWhere('effective_to', NULL);
                                });
                        }
                    ]);
            }
        ])
            ->where('id', $id)
            ->first(['id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'interest_rate', 'maturity_amount', 'certificate_no', 'maturity_date', 'form_number', 'created_at', 'is_certificate_print', 'customer_id', 'company_id']);
        //  dd($data['certificate']);

        $data['correctionStatus'] = getCorrectionStatus(6, $id);
        $data['getCorrectionDetail'] = getCorrectionDetail(6, $id);


        $data['id'] = $id;
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $data['softdate'] = $globaldate;
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 139)->first();
        $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $globaldate)->whereCompanyId($data['certificate']->company_id)->first();

        $data['gstAmount'] = 0;
        $totalAmount = 50;
        if (isset($getHeadSetting->gst_percentage) && !empty($getGstSetting)) {
            if ($data['certificate']['branch']->state_id == $getGstSetting->state_id) {
                $data['gstAmount'] = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100) / 2;

                $data['IntraState'] = true;



            } else {
                $data['gstAmount'] = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100);
                $data['IntraState'] = false;
            }
            $data['gst_percentage'] = $getHeadSetting->gst_percentage;


        }
        return view('templates.branch.passbook.certificate', $data);
    }

    public function certificatePrint(Request $request)
    {

        $id = $request->id;

        $data['is_certificate_print'] = 1;
        $data['_certificate_print_date'] = date("Y-m-d H:i:s");
        $investment = Memberinvestments::find($id);
        $investment->update($data);
        return json_encode(array('msg_type' => 'success'));
    }

    public function CertificatepayPrint(Request $request)
    {

        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        Session::put('created_at', $globaldate);
        $id = $request->id;
        DB::beginTransaction();
        $gstAmount = 0;
        $tenureAll = Memberinvestments::where('id', $id)->first(['id', 'tenure', 'created_at']);
        $effectiveDate = date('Y-m-d', strtotime($tenureAll->created_at));
        $tenure = round($tenureAll->tenure * 12, 0);
        try {
            $data = Memberinvestments::with([
                'ssb' => function ($query) {
                    $query->select('id', 'passbook_no', 'member_investments_id', 'account_no');
                }
            ])->with([
                        'branch' => function ($query) {
                            $query->select('id', 'name', 'branch_code', 'state_id');
                        }
                    ])->with([
                        'plan' => function ($query) use ($tenure, $effectiveDate) {
                            $query->select('id', 'name', 'plan_code')
                                ->with([
                                    'plantenure' => function ($q) use ($tenure, $effectiveDate) {
                                        $q->whereTenure($tenure)
                                            ->whereColumn('tenure', 'month_to')
                                            ->where('effective_from', '<=', $effectiveDate)
                                            ->where(function ($query) use ($effectiveDate) {
                                                $query->where('effective_to', '>=', $effectiveDate)
                                                    ->orWhere('effective_to', NULL);
                                            });
                                    }
                                ]);
                        }
                    ])->with([
                        'member' => function ($query) {
                            $query->select('id', 'first_name', 'last_name', 'member_id');
                        }
                    ])->where('id', $id)->first(['id', 'member_id', 'branch_id', 'plan_id', 'customer_id', 'company_id']);
            $transaction_type = 7;
            $payment_mode = 0; //0 for cash
            $deposit_by_name = $data['member']->first_name . ' ' . $data['member']->last_name;
            $deposit_by_id = $memberId = $data['member']->id;
            $amountArray = array('1' => 50);
            $companyId = $data['company_id'];
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $branchCode = getBranchCode($branch_id)->branch_code;
            if ($data['plan']->plan_code == 'S') {
                $type_id = $data['ssb']->id;
                $type = 4;
                $sub_type = 411;
                $type1 = 4;
                $sub_type_cgst = 416;
                $sub_type_sgst = 417;
                $sub_type_igst = 418;
            } else {
                $type_id = $data->id;
                $type = 3;
                $sub_type = 37;
                $type1 = 3;
                $sub_type_cgst = 315;
                $sub_type_sgst = 316;
                $sub_type_igst = 317;
            }

            $getHeadSetting = \App\Models\HeadSetting::where('head_id', 139)->first();
            $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $globaldate)->whereCompanyId($data->company_id)->first();
            $totalAmount = 50;
            //Gst Insuramce
            if (isset($getHeadSetting->gst_percentage) && !empty($getGstSetting)) {
                if ($data['branch']->state_id == $getGstSetting->state_id) {
                    $gstAmount = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;

                } else {
                    $gstAmount = ceil($totalAmount * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }


            }
            // $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, $transaction_type, $type_id, $memberId, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no = '0', $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'CRC');

            /************************* Account head impelment ********************/

            $amount_to_id = $amount_to_name = $amount_from_id = $amount_from_name = $v_no = $v_date = $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = $entry_date = $entry_time = $created_by = $created_by_id = $is_contra = $contra_id = $created_at = $updated_at = $type_transaction_id = $ssb_account_id_to = $cheque_bank_from_id = $cheque_bank_ac_from_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = NULL;
            $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = NULL;

            $bank_id = NULL;
            $bank_ac_id = NULL;
            $type_transaction_id = NULL;



            $daybookRef = CommanTransactionsController::createBranchDayBookReferenceNew($totalAmount, $globaldate);
            $refId = $daybookRef;
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $created_by = 2;
            $created_by_id = Auth::user()->id;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $payment_type = 'CR';
            $payment_mode = 0;
            $currency_code = 'INR';

            $head1 = 3;
            $head2 = 13;
            $head3 = 139;
            $head4 = NULL;
            $head5 = NULL;
            $head12 = 2;
            $head22 = 10;
            $head32 = 28;
            $head42 = 71;
            $head52 = NULL;

            $des = 'Cash received from member ' . $deposit_by_name . '(' . $data['member']->member_id . ') through  duplicate certificate printing';
            $desDR = 'Cash A/c Dr 50/-';
            $desCR = 'To ' . $deposit_by_name . '(' . $data['member']->member_id . ') A/c Cr 50/-';

            // $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $totalAmount, $closing_balance = NULL, $des, $desDR, $desCR, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);


            $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $totalAmount, $des, $desDR, $desCR, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);






            /// passbook head
            // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $totalAmount, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);



            $allTran = CommanTransactionsController::headTransactionCreate(
                $refId,
                $branch_id,
                $bank_id,
                $bank_ac_id,
                $head3,
                $type,
                $sub_type,
                $type_id,
                $associate_id = NULL,
                $memberId,
                $branch_id_to = NULL,
                $branch_id_from = NULL,
                $totalAmount,
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
                $type_transaction_id,
                $jv_unique_id,
                $ssb_account_id_to,
                $ssb_account_tran_id_to,
                $ssb_account_tran_id_from,
                $cheque_type,
                $cheque_id,
                $companyId
            );




            // cash head
            // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $totalAmount, $closing_balance = NULL, $des, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

            $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $totalAmount, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);


            // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type, $sub_type, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $totalAmount, 'Duplicate Certificate Printing Fee', $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

            if ($gstAmount > 0) {
                if ($IntraState) {
                    $descA = 'Dublicate Certificate  Cgst Charge';
                    $descB = 'Dublicate Certificate Sgst Charge';
                    // $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, $descA, $descA, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                    $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, $descA, $descA, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);


                    /// passbook head
                    // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);




                    // cash head
                    // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);






                    // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type_cgst, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $gstAmount, $descA, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                    /**
                     * SGST
                     **/
                    // $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descB, $descB, $descB, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);


                    $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descB, $descB, $descB, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);







                    /// passbook head
                    // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descB, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);







                    // cash head
                    // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descB, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);







                    // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type_sgst, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $gstAmount, $descB, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);



                } else {
                    $descA = 'Dublicate Certificate  Igst Charge';
                    // $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, $descA, $descA, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);


                    $daybook = CommanTransactionsController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, $descA, $descA, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);




                    /// passbook head
                    // $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                    // cash head
                    // $allTran2 = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $gstAmount, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    $allTran = CommanTransactionsController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descA, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);



                    // $memberTran = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type_igst, $type_id, $associate_id = NULL, $memberId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $gstAmount, $descA, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                }
                $createdGstTransaction = CommanController::gstTransaction($dayBookId = $refId, $getGstSetting->gst_no, (!isset($data['member']->gst_no)) ? NULL : $data['member']->gst_no, $totalAmount, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $totalAmount + $gstAmount + $gstAmount : $totalAmount + $gstAmount, 139, $entry_date, 'DC139', $data['member']->id, $branch_id);

            }

            // dd("hi");
            /******** Balance   entry ***************/
            // $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $totalAmount + $gstAmount, 0);
            // $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $totalAmount + $gstAmount, 0);
            /************************* Account head impelment ********************/
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return json_encode(array('msg_type' => $ex->getMessage()));
        }
        // if (createdGstTransaction) {

        return json_encode(array('msg_type' => 'success'));
        // } else {
        //     return json_encode(array('msg_type' => 'error'));
        // }

    }




    public function passbookCoverNew($id)
    {
        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $data['IntraState'] = '';
        $data['gst_percentage'] = '';
        $totalAmount = 50;
        $data['relation'] = Memberinvestmentsnominees::with('investmentRelation:id,name')
            ->where('investment_id', $id)
            ->first();
        $data['title'] = 'Passbook | Cover';
        $data['passbook'] = Memberinvestments::has('company')->with([
            'ssb_detail:id,passbook_no,member_investments_id,account_no',
            'branch:id,name,state_id',
            'memberCompany:id,member_id',
            'company:id,name,short_name,address',
            'member:id,first_name,last_name,member_id,father_husband,address,state_id,city_id,pin_code,district_id,village',
            'plan:id,name,plan_code,plan_sub_category_code,plan_category_code',
            'investmentNomiees:investment_id,percentage,name'
        ])
            ->where('id', $id)
            ->first(['id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'passbook_no', 'daughter_name', 'dob', 're_dob', 're_name', 'customer_id', 'company_id', 'is_mature']);

        $data['correctionStatus'] = getCorrectionStatus(5, $id);
        $data['getCorrectionDetail'] = getCorrectionDetail(5, $id);

        $data['id'] = $id;
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 35)->first();
        $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $globaldate)->whereCompanyId($data['passbook']->company_id)->first();
        $data['gstAmount'] = 0;
        if (isset($getHeadSetting->gst_percentage) && !empty($getGstSetting)) {

            if ($data['passbook']['branch']->state_id == $getGstSetting->state_id) {
                $data['gstAmount'] = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100) / 2;
                $data['IntraState'] = true;
            } else {
                $data['gstAmount'] = ceil(($totalAmount * $getHeadSetting->gst_percentage) / 100);
                $data['IntraState'] = false;
            }
            $data['gst_percentage'] = $getHeadSetting->gst_percentage;
        }
        return view('templates.branch.passbook.new.cover', $data);
    }

    public function passbookTransactionNew($id, $code)
    {

        /*
        if (!in_array('Investment Transaction', auth()->user()->getPermissionNames()->toArray()) && !in_array('Member Investment Transaction', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code;
        if ($code == 'S') {
            $data['accountDetail'] = SavingAccount::where('member_investments_id', $id)->first();
            // $data['lastId'] = SavingAccountTranscation::select('id')->where('saving_account_id',$data['accountDetail']->id)->orderBy('id','DESC')->first();
            // $data['countRecord'] =  SavingAccountTranscation::select('id')->where('saving_account_id',$data['accountDetail']->id)->count();
            $data['lastId'] = '';
            $data['countRecord'] = '';
        } else {
            $data['accountDetail'] = Memberinvestments::with('plan')->where('id', $id)->first();
            $data['lastId'] = Daybook::select('id')->where('investment_id', $id)->orderBy('id', 'DESC')->first();
            $data['countRecord'] = Daybook::select('id')->where('investment_id', $id)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->count();
        }

        if ($data['lastId'] && isset($data['lastId'])) {
            $data['correctionStatus'] = getCorrectionStatus(3, $data['lastId']->id);
            $data['iId'] = $data['lastId']->id;
        } else {
            $data['correctionStatus'] = '';
            $data['iId'] = '';
        }
        */
        if (!in_array('Investment Transaction', auth()->user()->getPermissionNames()->toArray()) && !in_array('Member Investment Transaction', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code;
        $data['button_show'] = 0;
        $data['iId'] = '';
        $lastId = '';
        if ($code == 'S') {
            $data['accountDetail'] = $accountDetail = SavingAccount::has('company')->with('getMemberinvestments:id,renewal_correction_request')
                ->where('member_investments_id', $id)
                ->first();
            if ($accountDetail) {
                $data['lastId'] = $lastId = SavingAccountTranscation::where('saving_account_id', $accountDetail->id)->where('is_deleted', 0)->orderBy('created_at_default', 'DESC')->first();
                // if (isset($lastId) && $lastId->type == 2 && ($lastId->payment_mode == 0 || $lastId->payment_mode == 4)) {
                if (isset($lastId) && $lastId->type == 2) {
                    // condication : the task transaction is renwal then only will show the correction btn.
                    if ($accountDetail->getMemberinvestments->renewal_correction_request == 0) {
                        $data['button_show'] = 1;
                        $data['iId'] = $id = $lastId->id;
                    }
                }
            }
        } else {
            $data['accountDetail'] = $accountDetail = Memberinvestments::with('plan')
                ->whereHas('plan', function ($q) {
                    $q->withoutGlobalScope(ActiveScope::class)->where('plan_category_code', '!=', 'S');
                })
                ->where('id', $id)
                // ->where('is_mature','1')
                ->first();
            if (isset($accountDetail) && ($accountDetail->is_mature == 1) && ($accountDetail->renewal_correction_request == 0)) {
                $data['lastId'] = $lastId = Daybook::where('account_no', $accountDetail->account_number)
                    ->where('is_deleted', 0)
                    ->orderBy('id', 'DESC')
                    ->first(['id', 'transaction_type', 'payment_mode']);
                // if ($lastId->transaction_type == 4 && ($lastId->payment_mode == 0 || $lastId->payment_mode == 4)) {
                if ($lastId->transaction_type == 4) {
                    // condication : the task transaction is renwal then only will show the correction btn.
                    $data['button_show'] = 1;
                    $data['iId'] = $id = $lastId->id;
                }
            }
        }
        if (($lastId)) {
            $createdAt = $lastId->created_at;
            $comMonth = date("m", strtotime($createdAt));
            $comYear = date("Y", strtotime($createdAt));
            $countCommLe = CommissionLeaserMonthly::where('year', $comYear)->where('month', $comMonth)->count();
            if ($countCommLe > 0) {
                $data['button_show'] = 0;
            }
        }
        return view('templates.branch.passbook.new.transaction', $data);
    }


    public function transactionStartNew(Request $request)
    {
        $data['title'] = 'Passbook | Transaction';
        $data['accountTranscation'] = array();
        $data['accountDetail'] = array();
        $data['code'] = $code = $request['code'];
        $start = $request['transaction_id_from'];
        $end = $request['transaction_id_to'];
        if (Request1::isMethod('get')) {
            return back()->with('alert', 'Please submit form');
        }
        $data['eliOpeningAmount'] = 0.00;
        $data['accountsNumber'] = array('R-066523000256', 'R-084511001514', 'R-066523000257', 'R-066523000620', 'R-084523000800', 'R-066523000510', 'R-066523000258', 'R-084504006762', 'R-066504006287', 'R-066523000509', 'R-066523000588', 'R-084504007930', 'R-066504007682', 'R-066504007645');
        try {

            if ($code == 'S') {
                $data['accountDetail'] = SavingAccount::has('company')->where('id', $request['id'])->first();
                // $data['accountTranscation'] = SavingAccountTranscation::where('saving_account_id', $request['id'])->whereBetween(\DB::raw('id'), [$start, $end])->where('is_deleted', 0)->orderby('created_at', 'ASC')->get();
                $data['accountTranscation'] = SavingAccountTransactionView::where('saving_account_id', $request['id'])->whereBetween(\DB::raw('id'), [$start, $end])->orderby('opening_date', 'ASC')->get();
                //SavingAccountTransactionView::where('saving_account_id',$request['id'])->whereBetween(\DB::raw('DATE(opening_date)'), [$startDate, $endDate])->orderby('opening_date','ASC')->get();
            } else {
                $data['accountDetail'] = Memberinvestments::with('plan')/*->whereHas('plan', function ($query) {
$query->where('plan_category_code', '!=', 'S');
})*/ ->where('id', $request['id'])->first();
                $data['eliOpeningAmount'] = Daybook::where('investment_id', $request['id'])
                    ->whereIn('account_no', $data['accountsNumber'])
                    ->where('is_eli', 1)
                    ->where(function ($q) {
                        $q->where('transaction_type', 2)
                            ->where('is_deleted', 0)
                            ->orWhere('transaction_type', 4);
                    })
                    ->pluck('amount')
                    ->first();
                $data['eliOpeningAmount'] = $data['eliOpeningAmount'] != null ? round($data['eliOpeningAmount'], 2) : 0.00;
                $data['accountTranscation'] = Daybook::selectRaw('*, (
                    SELECT SUM(IF(deposit > 0, deposit, -withdrawal))
                    FROM day_books AS sub
                    WHERE sub.account_no = day_books.account_no
                    AND (sub.created_at) <= (day_books.created_at)
                    AND sub.transaction_type IN (2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30)
                    AND sub.is_deleted = 0
                ) AS opening_balance')
                    ->whereBetween(\DB::raw('id'), [$start, $end])
                    ->where('account_no', $data['accountDetail']->account_number)
                    ->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 29, 30])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();
            }
        } catch (\Exception $ex) {
            return back()->with('alert', $ex->getMessage());
        }
        return view('templates.branch.passbook.new.transaction_print', $data);
    }


    public function passbookMaturity($id)
    {


        $data['title'] = 'Passbook | Cover';
        $data['passbook'] = Memberinvestments::with([
            'plan' => function ($query) {
                $query->select('id', 'name', 'plan_code');
            }
        ])->where('id', $id)->first(['id', 'account_number', 'tenure', 'deposite_amount', 'member_id', 'branch_id', 'plan_id', 'is_passbook_print', 'passbook_no', 'interest_rate']);
        $data['maturity'] = \App\Models\DemandAdvice::where('investment_id', $id)->first();
        $loan_id = \App\Models\Memberloans::where('applicant_id', $data['passbook']->member_id)->where('loan_type', 4)->first();

        if ($loan_id) {
            $data['loan_detail'] = \App\Models\Loaninvestmentmembers::where('member_loan_id', $loan_id->id)->where('plan_id', $id)->first();
            $data['loan_record'] = \App\Models\AllHeadTransaction::where('type', 5)->where('sub_type', 51)->where('type_id', $loan_id->id)->where('is_deleted', 0)->first();

        } else {
            $data['loan_record'] = '';
        }
        // dd($data);




        return view('templates.branch.passbook.new.maturity', $data);
    }

    public function renewal_receipt(Request $request, $id)
    {
        $data['title'] = "Transaction Receipt";
        $data['data'] = \App\Models\Daybook::with([
            'dbranch',
            'investment' => function ($query) {
                $query->with([
                    'plan' => function ($q) {
                        $q->withoutGlobalScope(ActiveScope::class);
                    }
                ])->select('id', 'plan_id', 'account_number', 'tenure', 'member_id', 'customer_id', 'associate_id');
            }
        ])->where('is_deleted', 0)->where('id', $id)->first();

        $data['accountData'] = Memberinvestments::with([
            'member' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'member_id', 'associate_id']);
            }
        ])
            ->with([
                'memberCompany' => function ($q) {
                    $q->select(['id', 'member_id', 'customer_id']);
                }
            ])->where('id', $data['data']->investment_id)->first();
        ;

        $data['data']['type'] = 1;
        return view('templates.branch.investment_management.receipt', $data);
    }

    public function viewssbTransactionreceipt($id)
    {
        $data['title'] = 'Transaction Detail';
        $data['data'] = SavingAccountTranscation::where('id', $id)->first();
        // print_r($data['tDetails'] );die;
        $mId = SavingAccount::with([
            'ssbcustomerDataGet' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'member_id']);
            }
        ])
            ->with([
                'ssbmembersDataGet' => function ($q) {
                    $q->select(['id', 'member_id']);
                }
            ])
            ->where('id', $data['data']->saving_account_id)->first(['member_investments_id', 'member_id', 'account_no', 'customer_id', 'id']);
        $aId = Memberinvestments::select('associate_id')->where('id', $mId->member_investments_id)->first();
        $data['data']['member_id'] = $mId->member_id;
        //print_r($mId);die;
        $data['accountData'] = $mId;
        $data['aId'] = $aId;


        if ($data['data']->associate_id > 0) {
            $data['data']['associate_id'] = $data['data']->associate_id;
        } else {
            $data['data']['associate_id'] = $aId->associate_id;
        }
        $data['data']['account_no'] = $mId->account_no;

        $data['data']['type'] = 0;
        // dd($data);

        return view('templates.branch.investment_management.receipt', $data);
    }



}
