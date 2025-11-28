<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\{Branch, AccountHeads,CompanyBranch};
use Session;
use URL;
use DB;
use App\Http\Traits\BalanceSheetTrait;
class BalanceSheetController extends Controller
{
    use BalanceSheetTrait;
    public function __construct()
    {
        $this->middleware('auth');
    }
    // Balance SheetTable
    public function index()
    {
        $branch_id = '';
        $company_id = '';
        $endDateDb = $endDate = '';
        // StartDate
        $finacialYear = getFinacialYear();
        $startDatee = date("Y-m-d", strtotime(convertDate($finacialYear['dateStart'])));
        $branchIddd = 33;
        $globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
        $endDatee = date("Y-m-d", strtotime(convertDate($globalDate1)));
        if (check_my_permission(Auth::user()->id, "37") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Balance Sheet";
        // $data['branches'] = Branch::select('id', 'name')
        //     ->where('status', 1)
        //     ->get()
        //     ->toArray()
        //     ;
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $branch_ids = explode(",", $branch_ids);
            $data['libalityHead'] = $libalityHead = AccountHeads::select('id', 'sub_head', 'head_id', 'company_id')
                ->where('status', '!=', 9)
                ->where('parent_id', 1)
                ->where('labels', 2)
                ->orderBy('head_id', 'ASC')
                ->get();
            $data['assestHead'] = $assestHead = AccountHeads::select('id', 'sub_head', 'head_id', 'company_id')
                ->where('status', '!=', 9)
                ->where('parent_id', 2)
                ->where('labels', 2)
                ->orderBy('head_id', 'ASC')
                ->get();
            $data['end_date'] = $end_date = $endDateDb;
            $data['branch_id'] = $branch_id;
            $data['company_id'] = $company_id;
            $data['profit_loss'] = $profit_loss = headTotalNew(3, $startDatee, $endDatee, $branch_id, $company_id) - headTotalNew(4, $startDatee, $endDatee, $branch_id, $company_id);
            $data['script'] = 'templates.admin.balance_sheet.partials.script';
            return view('templates.admin.balance_sheet.branch_index', $data);
            /*
            return \Response::json(['view' => view('templates.admin.balance_sheet.partials.sheet_filter' ,['data' => $data,'libalityHead' => $libalityHead,'assestHead' => $assestHead,'end_date' => $end_date,'branch_id' => $branch_id,'totalAssest' => $totalAssest,'totalLibality' => $totalLibality,'profit_loss' => $profit_loss])
            ->render(),'msg_type'=>'success']);
            */
        } else {
            $data['globalDate'] = $globalDate1;
            $data['startFinenceDate'] = $startDatee;
            $data['libalityHead'] = $libalityHead = AccountHeads::select('id', 'sub_head', 'head_id', 'company_id')
                ->where('status', '!=', 9)
                ->where('parent_id', 1)
                ->where('labels', 2)
                ->orderBy('head_id', 'ASC')
                ->get();
            $data['assestHead'] = $assestHead = AccountHeads::select('id', 'sub_head', 'head_id', 'company_id')
                ->where('status', '!=', 9)
                ->where('parent_id', 2)
                ->where('labels', 2)
                ->orderBy('head_id', 'ASC')
                ->get();
            $data['script'] = 'templates.admin.balance_sheet.partials.script';
            return view('templates.admin.balance_sheet.index', $data);
        }
    }
    //  Detailed Balance Sheet
    public function balanceSheetAjax(Request $request)
    {
        $branch_id = $request->branch ?? 0;
        $startDate = date('Y-m-d', strtotime(convertDate($request->start_date)));
        $endDate = date('Y-m-d', strtotime(convertDate($request->create_application_date)));
        $finacialYear = $request->financial_year;
        $companyId = $request->company_id;
        $balanceSheetData = collect(DB::select('call HeadAmount(?,?,?,?,?)', [$branch_id, '1', $startDate, $endDate, $companyId]));
        $date = explode(' - ', $request->financial_year);
        $start_y = $date[0];
        $end_y = $date[1];
        $start_m = 04;
        $start_d = 01;
        $previousYearStartDate = $start_y - 1;
        $previousYearEndDate = $end_y - 1;
        $previousData = array();
        // $previousYearBalance = \App\Models\BalanceSheetClosing::with(['accountHeads'])
        //     ->whereNotNull('levels')
        //     ->when($branch_id, function ($q) use ($branch_id) {
        //         $q->where('branch_id', $branch_id);
        //     })
        //     ->where('is_opening_balance',0)
        //     ->whereCompanyId($companyId)
        //     ->where('start_year', $previousYearStartDate)
        //     ->where('end_year', $previousYearEndDate)
        //     ->where('is_deleted', 0)
        //     ->get();

        $previousendDate = $previousYearEndDate.'-03-31';
        $previousYearBalance = \App\Models\BalanceSheetClosing::whereHas('accountHeads',function($q){
            $q->where('is_trial',0);
        })
        ->whereNotNull('levels')
        ->when($branch_id,function($q) use($branch_id){
            $q->where('branch_id',$branch_id);
        })
        ->whereCompanyId($request->company_id)
        // ->where('start_year',$previousYearStartDate)
        ->where('end_date','<=',$previousendDate)
        ->where('is_opening_balance',1)
        ->where('is_deleted',0)
        ->get()
        ;
        $oldheadclosing = \App\Models\HeadClosing::with('accountHeads')->where('start_year', $previousYearStartDate)
            ->where('company_id', $companyId)->when($branch_id, function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            })
            ->where('end_year', $previousYearEndDate)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get()
            ->keyBy('head_id');
        $totalOpeningBalance = $previousYearBalance->filter(function ($value) {
            return $value->levels == 1;
        });
        $previousOpeningAmount = $oldheadclosing->filter(function ($value) {
            return $value->accountHeads->labels == 1;
        });
        $amounts = [];
        $amountNew = 0;
        foreach ($previousYearBalance as $previousData) {
            $headId = $previousData->head_id;
            $amountNew = $previousData->total;
            if (isset($amounts[$headId])) {
                $amounts[$headId] += $amountNew;
            } else {
                $amounts[$headId] = $amountNew;
            }
        }
        // $balanceSheetData = $balanceSheetData->filter(function ($data) use ($companyId) {
        //     return in_array($companyId, json_decode($data->companyId));
        // });
        $libalityHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 2 && $data->first()->parent_id == 1);
        });
        $assetHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 2 && $data->first()->parent_id == 2);
        });
        $labelThreeHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 3);
        });
        $actualData = $balanceSheetData->filter(function ($data) {
            return ($data->labels != NULL);
        })->toArray();
        $actualData = array_column($actualData, null, 'head_id');

        $totalAmount = [];
        $amount = [];
        $headThreeTotalAmount = 0;
        $headFourTotalAmount = 0;
        $headThreeChild = array_unique(explode(',', $actualData[3]->child_id));
        $headFourChild = array_unique(explode(',', $actualData[4]->child_id));
        $HeadTotalAmount = 0;
        foreach ($actualData as $item) {
            $amount[$item->head_id] = 0;
            $childHeadId = [];
            $totalAmount[$item->head_id] = ($item->nature == 1) ? ($item->amount_sum - $item->amount_sum_dr) : ($item->amount_sum_dr - $item->amount_sum);
        }
        $headThreeTotal = array_intersect_key($totalAmount, array_flip($headThreeChild));

        $headThreeTotalAmount += array_sum($headThreeTotal);
        $headFourTotal = array_intersect_key($totalAmount, array_flip($headFourChild));
        $headFourTotalAmount += array_sum($headFourTotal);
        $expenseAmount = $headThreeTotalAmount - $headFourTotalAmount;
        Session::put('balanceSheet', $balanceSheetData);
        Session::put('balanceSheet_filter', $request->all());
        $view = [
            'libalityHead' => $libalityHead, 
            'start_date' => $startDate, 
            'end_date' => $endDate, 
            'branch_id' => $branch_id, 
            'create_application_date' => $request->create_application_date, 
            'financial_year' => $finacialYear, 
            'labelThreeHead' => $labelThreeHead, 
            'assetHead' => $assetHead, 
            'totalAmount' => $totalAmount, 
            'HeadTotalAmount' => $HeadTotalAmount, 
            'expenseAmount' => $expenseAmount ?? 0, 
            'companyId' => $companyId, 
            'previousData' => $previousData, 
            'previousOpeningAmount' => $previousOpeningAmount, 
            'totalOpeningBalance' => $totalOpeningBalance, 
            'oldheadclosing' => $oldheadclosing, 
            'amountNew' => $amounts
        ];
        return \Response::json(['view' => view('templates.admin.balance_sheet.partials.sheet_filter', $view)->render(), 'msg_type' => 'success']);
    }
    public function current_liabilityDetailBranchWiseListing(Request $request)
    {
        if ($request->ajax()) {
            $head_id = $request->head_id;
            $label = $request->label;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $branch = $request->branch_id ?? 0;
            $company_id = $request->company_id;
            $start = $request->start;
            $length = $request->length;
            // $info = 'head' . $label;
            $head_info = AccountHeads::where('head_id', $head_id)->first();
            $parent_id1 = AccountHeads::where('head_id', $head_id)->first();
            $parent_id2 = AccountHeads::where('head_id', $parent_id1->parent_id)->first();
            $parent_id1 = $parent_id2;
            if ($parent_id2) {
                $parent_id3 = AccountHeads::where('head_id', $parent_id2->parent_id)->first();
                $parent_id1 = $parent_id3;
            }
            $previousYearEndDate = date('Y', strtotime(convertDate($end_date))) -1;
          
           
            $previousData = array();
            // $previousYearBalance = \App\Models\BalanceSheetClosing::with(['accountHeads'])
            //     ->whereNotNull('levels')
            //     ->when($branch_id, function ($q) use ($branch_id) {
            //         $q->where('branch_id', $branch_id);
            //     })
            //     ->where('is_opening_balance',0)
            //     ->whereCompanyId($company_id)
            //     ->where('start_year', $previousYearStartDate)
            //     ->where('end_year', $previousYearEndDate)
            //     ->where('is_deleted', 0)
            //     ->get()
            //     ;
            $previousendDate = $previousYearEndDate.'-03-31';
            $previousYearBalance = \App\Models\BalanceSheetClosing::whereHas('accountHeads',function($q){
                $q->where('is_trial',0);
            })
            ->whereNotNull('levels')
            ->when($branch,function($q) use($branch){
                $q->where('branch_id',$branch);
            })->where('head_id',$head_id)
            
           
            ->whereCompanyId($company_id)
            // ->where('start_year',$previousYearStartDate)
            ->where('end_date','<=',$previousendDate)
            ->where('is_opening_balance',1)
            ->where('is_deleted',0)
            ->get();
            
            $amountFiltered = collect($previousYearBalance)->groupBy('branch_id')->filter(function($data){
                return $data->sum('total');
            }) ;
       
          

           


            $data = CompanyBranch::orderBy('id', 'ASC')
                ->with('branch','company')
                ->when((!is_null(Auth::user()->branch_ids)), function ($q) use ($branch) {
                    $q->whereIn('branch_id', explode(",", Auth::user()->branch_ids));
                })
                ->when($branch,function($q)use($branch){
                    $q->whereBranchId($branch);
                })
                ->when($company_id,function($q)use($company_id){
                    $q->whereCompanyId($company_id);
                })
            ;
            $data1 = $data->get();
            $count = count($data1);
            $data = $data->offset($start)->limit($length)->get();
            $totalCount = $data->count();
            $sno = $start;
            $rowReturn = [];
           
            foreach ($data as $row) {
                $totalColumn = (isset($amountFiltered[$row->branch_id])) ?  ($amountFiltered[$row->branch_id]) : 0;
                $openingAmount =(isset($amountFiltered[$row->branch_id]))  ? $totalColumn->sum('total') : 0;
                $sno++;
                $val['sno'] = $sno;
                // $head_ids = array($head_id);
                // $subHeadsIDS = AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('parent_id')->toArray();
                // if (count($subHeadsIDS) > 0) {
                //     $head_ids = array_merge($head_ids, $subHeadsIDS);
                //     $record = $this->get_account_head_ids($head_ids, $subHeadsIDS, true);
                // }
                // foreach ($record as $key => $value) {
                //     $ids[] = $value;
                // }
                $branch_id = $row->branch->id;
                $val['branch'] = $row->branch->name;
                $val['branch_code'] = $row->branch->branch_code;
                $val['opening_balance'] = isset( $openingAmount ) ? $openingAmount : 0 ;
                $val['company'] = $row->company ? $row->company[0]->name : 'N/A';
                $val['company'] = $row->company ? $row->company[0]->name : 'N/A';
                // $val['total_member'] = headTotalMember($head_id, $start_date, $end_date, $branch_id, $company_id);
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
                    'branch_id' => $row->branch->id,
                    'company_id' => $company_id,
                    'label' => $label,
                    'page' => str_replace('/', '^', $head_info->sub_head),
                ];
                $encryptedData = Crypt::encrypt($dataArray, $key);
                $name = str_replace('_', ' ', ucwords($head_info->sub_head));
                // $urladd = 'admin/balance-sheet/'.str_replace('/','^',$head_info->sub_head).'/'.$encryptedData;
                $urladd = 'admin/balance-sheet/' . $encryptedData;
                $url = URL::to($urladd);
                $btn .= $url . '" title="' . $name . '"><i class="fas fa-print mr-2"></i>' . $name . '</a></div></div></div>';
                $val['action'] = in_array($head_id,[458,409]) ? 'N/A' : $btn;
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    public function currentDetail(Request $request)
    {
        $head_id = $request->head_id ? $request->head_id : null;
        $data['head_id'] = $head_id;
        $label = $request->label ? $request->label : null;
        $data['label'] = $label;
        $company_id = $request->company_id ? $request->company_id : null;
        $data['company_id'] = $company_id;
        $branch_id = $request->branch_id ? $request->branch_id : null;
        $data['branch_id'] = $branch_id ?? 0;
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
        $data['title'] = 'Balance Sheet - ' . ucwords($head_info->sub_head);
        $data['filter'] = 'templates.admin.balance_sheet.filter';
        $data['script'] = 'templates.admin.balance_sheet.partials.list_script';
        $data['route'] = 'admin.balance-sheet.curr_liability_detailBranchWise_listing';
        $previousYearStartDate = date('Y', strtotime($start_date)) -1;
        $previousYearEndDate = date('Y', strtotime(convertDate($end_date))) -1;
       
        $previousData = array();
        // $previousYearBalance = \App\Models\BalanceSheetClosing::with(['accountHeads'])
        //     ->whereNotNull('levels')
        //     ->when($branch_id, function ($q) use ($branch_id) {
        //         $q->where('branch_id', $branch_id);
        //     })
        //     ->where('is_opening_balance',0)
        //     ->whereCompanyId($company_id)
        //     ->where('start_year', $previousYearStartDate)
        //     ->where('end_year', $previousYearEndDate)
        //     ->where('is_deleted', 0)
        //     ->get()
        //     ;
        $previousendDate = $previousYearEndDate.'-03-31';
        $previousYearBalance = \App\Models\BalanceSheetClosing::whereHas('accountHeads',function($q){
            $q->where('is_trial',0);
        })
        ->whereNotNull('levels')
        ->when($branch_id,function($q) use($branch_id){
            $q->where('branch_id',$branch_id);
        })
        
       
        ->whereCompanyId($request->company_id)
        // ->where('start_year',$previousYearStartDate)
        ->where('end_date','<=',$previousendDate)
        ->where('is_opening_balance',1)
        ->where('is_deleted',0)
        ->get();
           
        $oldheadclosing = \App\Models\HeadClosing::with('accountHeads')
            ->where('company_id', $company_id)
            ->when($branch_id, function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            })
            ->where('start_year', $previousYearStartDate)
            ->where('end_year', $previousYearEndDate)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->get()
            ->keyBy('head_id')
            ;
        $amounts = [];
        $amountNew = 0;
        foreach ($previousYearBalance as $previousData) {
            $headId = $previousData->head_id;
            $amountNew = $previousData->total;
            if (isset($amounts[$headId])) {
                $amounts[$headId] += $amountNew;
            } else {
                $amounts[$headId] = $amountNew;
            }
        }
       
        $data['amountNew'] = $amounts;
        $data['oldheadclosing'] = $oldheadclosing;
        $child_head = [];
        $data['array'] = [
            'S/N' => 'sno',
            'BR Name' => 'branch',
            'BR Code' => 'branch_code',
            // 'Total Member' => 'total_member',
            'Opening Balance' => 'opening_balance',
            'Amount' => 'amount',
            'Company' => 'company',
            'Action' => 'action',
        ];
        
        if (isset($head_info) && (gettype($head_info->child_head) == 'string')) {
            $head_info->child_head = explode(',', str_replace('[', '', str_replace(']', '', $head_info->child_head)));
            foreach ($head_info->child_head as $val) {
                $child_head[] = (int) $val;
            }
        } else {
            $child_head = $head_info->child_head;
        }
        
        // $child_head = \App\Models\AccountHeads::where('parent_id', (int)$head_id)->pluck('head_id');
        // dd($child_head);
        if (!empty($child_head) && (count($child_head) > 1)) {
            if ($request->direct) {
                $view = 'templates.admin.balance_sheet.listing';
            } else {
                $data['child'] = $child_head;
                $data['childHead'] = getHead($head_id, $label + 1, $company_id);
                $data['script'] = 'templates.admin.balance_sheet.partials.details_script';
                $view = 'templates.admin.balance_sheet.details';
            }
        } else {
            
            $view = 'templates.admin.balance_sheet.listing';
        }
        if (request()->method() == 'POST') {
            return \Response::json(['view' => view('templates.admin.balance_sheet.detail', $data)->render(), 'msg_type' => 'success']);
        } else {
            return view($view, $data);
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
        $page = $decryptData['page'];
        $data['branches'] = \App\Models\Branch::pluck('name', 'id');
        /*
        if (check_my_permission(Auth::user()->id, "36") != "1") {
        return redirect()
        ->route('admin.dashboard');
        }
        */
        $data['title'] = "Balance Sheet Account - " . $page;
        return view('templates.admin.profit_loss.detail_new', $data);
    }

}