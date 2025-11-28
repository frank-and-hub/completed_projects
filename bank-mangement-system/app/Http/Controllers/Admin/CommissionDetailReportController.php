<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;
use App\Models\CommissionLeaserMonthly;
use App\Models\AssociateMonthlyCommission;
use App\Models\AssociateCommission;
use App\Models\Memberloans;
use DB;
use Session;
use Redirect;
use URL;
use Illuminate\Support\Facades\Cache;

class CommissionDetailReportController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Investment account commission detail get 
     * replace old controller to new 
     * Date -- 31-07-2023
     * Created by --- Durgesh
     *
     * @param  $id
     * @return  array()  Response
     */
    public function investmentCommission($id)
    {

        $data['title'] = 'Investment Plan | Commission';
        $data['investment'] = getInvestmentDetails($id);

        $data['year'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get('year');

        $data['month'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get(['month', 'year']);

        // pd($data['year_month']);

        return view('templates.admin.investment_management.commission_report.commission', $data);
    }
    /**
     * Fetch invest listing data.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function investmentCommissionListing(Request $request)

    {
      
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();

            if (!empty($_POST['searchform'])) {

                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

                $data = AssociateMonthlyCommission::with('member:id,associate_id,first_name,last_name,associate_no')
                    ->with('carderName:name,id,short_name')
                    ->where('type', 1)
                    ->where('type_id', $arrFormData['account_no']);

                $totalCount = $data->count('id');
                // if(!is_null(Auth::user()->branch_ids)){
                // $branch_ids=Auth::user()->branch_ids;
                // $data=$data->whereIn('branch_id',explode(",",$branch_ids));
                // }

                /******* fillter query start ****/

                if ($arrFormData['year'] > 0) {
                    $year = $arrFormData['year'];
                    $data = $data->where('commission_for_year', $year);
                }

                if ($arrFormData['month'] != '') {
                    $month = $arrFormData['month'];

                    $data = $data->where('commission_for_month', $month);
                }


                if ($arrFormData['associate_code'] != '') {
                    $name = $arrFormData['associate_code'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.associate_no', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.associate_no)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['associate_name'] != '') {
                    $name = $arrFormData['associate_name'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }

                /******* fillter query End ****/
                $count = $data->count('id');

                $data = $data->offset($_POST['start'])->limit($_POST['length']);
                $data = $data->orderby('created_at', 'DESC')->get(['id', 'total_amount', 'qualifying_amount', 'commission_amount', 'percentage', 'cadre_from', 'cadre_to', 'commission_for_month', 'company_id', 'assocaite_id', 'commission_for_year']);
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $monthAbbreviations = [
                        1 => 'Jan',
                        2 => 'Feb',
                        3 => 'Mar',
                        4 => 'Apr',
                        5 => 'May',
                        6 => 'Jun',
                        7 => 'Jul',
                        8 => 'Aug',
                        9 => 'Sep',
                        10 => 'Oct',
                        11 => 'Nov',
                        12 => 'Dec'
                    ];
                    $val['month'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                    $val['associate_id'] = $row->member->associate_no;
                    $val['associate_name'] = $row->member ? ($row->member->first_name . ' ' . ($row->member->last_name??'')) : 'N/A';
                    $val['carder_name'] = $row->carderName->name . '(' . $row->carderName->short_name . ')';
                    $val['total_amount'] = number_format((float) $row->total_amount, 2, '.', '');
                    $val['qualifying_amount'] = number_format((float) $row->qualifying_amount, 2, '.', '');
                    $val['commission_amount'] = number_format((float) $row->commission_amount, 2, '.', '');
                    $val['percentage'] = number_format((float) $row->percentage, 2, '.', '');
                    $val['carder_from'] = $row->cadre_from;
                    $val['carder_to'] = $row->cadre_to;
                    if ($row->cadre_from === 1) {
                        $val['commission_type'] = 'Self';
                    } else {
                        $val['commission_type'] = 'Team';
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

    /**
     * Export investment listing commission in pdf,csv.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportInvestmentCommission(Request $request)

    {
        if ($request['commission_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/investment_commision_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/investment_commision_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
        }
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['associate_code'] = $request->associate_code;
        $request['associate_name'] = $request->associate_name;
        $request['is_search'] = $request->is_search;
        $year = $request->year;
        $month = $request->month;
        $sday = 1;       
        $data = AssociateMonthlyCommission::with('member:id,associate_id,first_name,last_name,associate_no')
            ->with('carderName:name,id,short_name')->where('type', 1)
            ->where('type_id', $request['account_no']);


        if ($request['year'] != '') {
            $year = $request['year'];
            $data = $data->where('commission_for_year', $year);
        }

        if ($request['month'] != '') {
            $month = $request['month'];
            $data = $data->where('commission_for_month', $month);
        }
       
        if ($request['associate_code'] != '') {
            $name = $request['associate_code'];
            $data = $data->whereHas('member', function ($query) use ($name) {
                $query->where('members.associate_no', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.associate_no)'), 'LIKE', "%$name%");
            });
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->whereHas('member', function ($query) use ($name) {
                $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['commission_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count();
            $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get(['id', 'total_amount', 'qualifying_amount', 'commission_amount', 'percentage', 'cadre_from', 'cadre_to', 'commission_for_month', 'company_id', 'assocaite_id', 'commission_for_year']);
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
                $val['S/N'] = $sno;
                $monthAbbreviations = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec'
                ];
                $val['MONTH'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                $val['ASSOCIAT ID'] = $row->member->associate_no;
                $val['ASSOCIAT NAME'] = !empty($row->member->first_name) && !empty($row->member->last_name) ? $row->member->first_name . ' ' . $row->member->last_name : '';
                $val['CARDER NAME'] = $row->carderName->name . '(' . $row->carderName->short_name . ')';
                $val['TOTAL NAME'] = number_format((float) $row->total_amount, 2, '.', '');
                $val[' QUALIFYING AMOUNT'] = number_format((float) $row->qualifying_amount, 2, '.', '');
                $val['COMMISSION AMOUNT'] = number_format((float) $row->commission_amount, 2, '.', '');
                $val[' PERCENTAGE'] = number_format((float) $row->percentage, 2, '.', '');
                $val['CARDER FORM'] = $row->cadre_from;
                $val['CARDER TO'] = $row->cadre_to;
                if ($row->cadre_from === 1) {
                    $val['COMMISSION TYPE'] = 'Self';
                } else {
                    $val['COMMISSION TYPE'] = 'Team';
                }
                //getCarderName(getSeniorData($row->associate_senior_id,'current_carder_id'));
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
            echo json_encode($response);
        } elseif ($request['commission_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.exportcommission', compact('data', 'startDate', 'endDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associatecommission.pdf');
        }
    }
    public function loanCommission($id)
    {
   
        //die('hi');
        $data['title'] = 'Loan Commission Detail | Listing';
        $data['loan'] = Memberloans::where('id', $id)->first();
        $data['year'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get('year');

        $data['month'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get(['month', 'year']);
        return view('templates.admin.loan.commissionDetailLoan', $data);
    }
    /**
     * Get loan  commission list
     * Route: ajax call from - /admin/loan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function loanCommissionList(Request $request)
    {
        //dd ($arrFormData['loan_id']);

        if ($request->ajax()) {

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
           
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = AssociateMonthlyCommission::with('member:id,associate_id,first_name,last_name,associate_no')
                    ->with('carderName:name,id,short_name')->whereIn('type',[2,3])->where('type_id', $arrFormData['loan_id']);

                $totalCount = $data->count('id');
                /******* fillter query start ****/

                if ($arrFormData['year'] != '') {
                    $year = $arrFormData['year'];
                    $data = $data->where('commission_for_year', $year);
                }
                if ($arrFormData['month'] != '') {
                    $month = $arrFormData['month'];

                    $data = $data->where('commission_for_month', $month);
                }


                if ($arrFormData['associate_code'] != '') {
                    $name = $arrFormData['associate_code'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.associate_no', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.associate_no)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['associate_name'] != '') {
                    $name = $arrFormData['associate_name'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                /******* fillter query End ****/
                $count = $data->count('id');
                $data = $data->offset($_POST['start'])->limit($_POST['length']);
                $data = $data->orderby('created_at', 'DESC')->get(['id', 'total_amount', 'qualifying_amount', 'commission_amount', 'percentage', 'cadre_from', 'cadre_to', 'commission_for_month', 'company_id', 'assocaite_id', 'commission_for_year']);
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $monthAbbreviations = [
                        1 => 'Jan',
                        2 => 'Feb',
                        3 => 'Mar',
                        4 => 'Apr',
                        5 => 'May',
                        6 => 'Jun',
                        7 => 'Jul',
                        8 => 'Aug',
                        9 => 'Sep',
                        10 => 'Oct',
                        11 => 'Nov',
                        12 => 'Dec'
                    ];
                    $val['month'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                    $val['associate_id'] = $row->member->associate_no;
                    $val['associate_name'] = !empty($row->member->first_name) && !empty($row->member->last_name) ? $row->member->first_name . ' ' . $row->member->last_name : '';
                    $val['carder_name'] = $row->carderName->name . '(' . $row->carderName->short_name . ')';
                    $val['total_amount'] = number_format((float) $row->total_amount, 2, '.', '');
                    $val['qualifying_amount'] = number_format((float) $row->qualifying_amount, 2, '.', '');
                    $val['commission_amount'] = number_format((float) $row->commission_amount, 2, '.', '');
                    $val['percentage'] = number_format((float) $row->percentage);
                    $val['carder_from'] = $row->cadre_from;
                    $val['carder_to'] = $row->cadre_to;
                    if ($row->cadre_from === 1) {
                        $val['commission_type'] = 'Self';
                    } else {
                        $val['commission_type'] = 'Team';
                    }
                    // dd($row->toArray());             
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
    public function loanCommissionExport(Request $request)
    {
        if ($request['commission_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/loan_commision_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/loan_commision_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
        }
        $request['associate_code'] = $request->associate_code;
        $request['associate_name'] = $request->associate_name;
        $request['is_search'] = $request->is_search;
        $year = $request->year;
        $month = $request->month;
        $sday = 1;
        $data = AssociateMonthlyCommission::with('member:id,associate_id,first_name,last_name,associate_no')
            ->with('carderName:name,id,short_name')->whereIn('type', [2,3])
            ->where('type_id', $request['loan_id']);

        if ($request['year'] != '') {
            $year = $request['year'];

            $data = $data->where('commission_for_year', $year);
        }

        if ($request['month'] != '') {
            $month = $request['month'];

            $data = $data->where('commission_for_month', $month);
        }


        if ($request['associate_code'] != '') {
            $name = $request['associate_code'];
            $data = $data->whereHas('member', function ($query) use ($name) {
                $query->where('members.associate_no', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.associate_no)'), 'LIKE', "%$name%");
            });
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->whereHas('member', function ($query) use ($name) {
                $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['commission_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count();
            $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get(['id', 'total_amount', 'qualifying_amount', 'commission_amount', 'percentage', 'cadre_from', 'cadre_to', 'commission_for_month', 'company_id', 'assocaite_id', 'commission_for_year']);
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
                $val['S/N'] = $sno;
                $monthAbbreviations = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec'
                ];
                $val['MONTH'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                $val['ASSOCIAT ID'] = $row->member->associate_no;
                $val['ASSOCIAT NAME'] = !empty($row->member->first_name) && !empty($row->member->last_name) ? $row->member->first_name . ' ' . $row->member->last_name : '';
                $val['CARDER NAME'] = $row->carderName->name . '(' . $row->carderName->short_name . ')';
                $val['TOTAL NAME'] = number_format((float) $row->total_amount, 2, '.', '');
                $val[' QUALIFYING AMOUNT'] = number_format((float) $row->qualifying_amount, 2, '.', '');
                $val['COMMISSION AMOUNT'] = number_format((float) $row->commission_amount, 2, '.', '');
                $val[' PERCENTAGE'] = number_format((float) $row->percentage, 2, '.', '');
                $val['CARDER FORM'] = $row->cadre_from;
                $val['CARDER TO'] = $row->cadre_to;
                if ($row->cadre_from === 1) {
                    $val['COMMISSION TYPE'] = 'Self';
                } else {
                    $val['COMMISSION TYPE'] = 'Team';
                }
                //getCarderName(getSeniorData($row->associate_senior_id,'current_carder_id'));
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
            echo json_encode($response);
        } elseif ($request['commission_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.exportcommission', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associatecommission.pdf');
        }
    }
    public function loanGroupCommission($id)
    {
        $data['title'] = 'Loan Commission Detail | Listing';
       $data['plans'] = Plans::where('status',1)->get();
        $data['loan'] = Grouploans::where('id', $id)->first();
        $data['year'] = CommissionLeaserMonthly::where('is_deleted', 0)
        ->where('year', '>', 2022)
        ->distinct()
        ->get('year');

    $data['month'] = CommissionLeaserMonthly::where('is_deleted', 0)
        ->where('year', '>', 2022)
        ->distinct()
        ->get(['month', 'year']);
        return view('templates.admin.loan.commissionDetailLoan', $data);
    }
   
}
