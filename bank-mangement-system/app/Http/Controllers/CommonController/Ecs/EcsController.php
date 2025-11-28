<?php

namespace App\Http\Controllers\CommonController\Ecs;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Carbon;
use URL;
use App\Models\ECSTransaction;
use Auth;
use DB;
use Illuminate\Http\Request;
class EcsController extends Controller
{
    public function index()
    {
        $data['title'] = 'ECS | Transaction Listing';
        return view('templates/CommonViews/Ecs.index', $data);
    }
    public function getData(Request $request)
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
                $data = ECSTransaction::with([
                    'memberLoanDetails:id,customer_id,loan_type',
                    'memberLoanDetails.loanMember:id,first_name,last_name,member_id',
                    'memberLoanDetails.loanPlans:id,name',
                    'loanBranch:id,name,branch_code',
                    'memberGroupLoanDetails:id,customer_id,loan_type',
                    'memberGroupLoanDetails.loanPlans:id,name',
                    'memberGroupLoanDetails.loanMember:id,first_name,last_name,member_id',
                    'memberAssocite:id,associate_id,first_name,last_name,associate_no'
                ]);
                /******* fillter query start ****/
                if ($arrFormData['ecs_status'] != '') {
                    $data = $data->where('transaction_status', $arrFormData['ecs_status']);
                }
                if ($arrFormData['ecs_type'] != '') {
                    $data = $data->where('transaction_type', $arrFormData['ecs_type']);
                }
                if ($arrFormData['company_id'] > 0) {
                    $data = $data->where('company_id', $arrFormData['company_id']);
                }
                $startDate = isset($request->from_date) ? date("Y-m-d", strtotime(convertDate($request->from_date))) : null;
                $endDate = isset($request->to_date) ? date("Y-m-d", strtotime(convertDate($request->to_date))) : null;
                if ($startDate !== null && $endDate !== null) {
                    $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
                }
                if (Auth::user()->role_id == 3) {
                    $getBranchId = getUserBranchId(Auth::user()->id);
                    $branch_id = $getBranchId->id;
                    $data = $data->where('branch_id', $branch_id);
                } else if ($arrFormData['branch_id'] != 0) {
                    $data = $data->where('branch_id', $arrFormData['branch_id']);
                }
                if (!empty($arrFormData['customer_id'])) {
                    $customerID = $arrFormData['customer_id'];
                    $data->where(function ($query) use ($customerID) {
                        $query->orWhereHas('memberGroupLoanDetails.loanMember', function ($subQuery) use ($customerID) {
                            $subQuery->where('member_id', 'LIKE', '%' . $customerID . '%');
                        })->orWhereHas('memberLoanDetails.loanMember', function ($subQuery) use ($customerID) {
                            $subQuery->where('member_id', 'LIKE', '%' . $customerID . '%');
                        });
                    });
                }
                if ($arrFormData['account_no'] != '') {
                    $data = $data->where('account_number', $arrFormData['account_no']);
                }
                /******* fillter query End ****/
                $count = $data->count('id');
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno = '';
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['date'] = date("d/m/Y", strtotime($row->date));
                    $val['branch_name'] = $row['loanBranch']->name . '(' . $row['loanBranch']->branch_code . ')';
                    $val['account_no'] = $row->account_number;
                    $val['plan'] = $row['memberLoanDetails']['loanPlans']['name'] ?? $row['memberGroupLoanDetails']['loanPlans']['name'] ?? '';
                    $memberId = $row['memberGroupLoanDetails']['loanMember']['member_id'] ?? $row['memberLoanDetails']['loanMember']['member_id'] ?? '';
                    $firstName = $row['memberGroupLoanDetails']['loanMember']['first_name'] ?? $row['memberLoanDetails']['loanMember']['first_name'] ?? '';
                    $lastName = $row['memberGroupLoanDetails']['loanMember']['last_name'] ?? $row['memberLoanDetails']['loanMember']['last_name'] ?? '';
                    $val['customer_name'] = $memberId !== '' ? $firstName . ' ' . $lastName : '';
                    $val['customer_id'] = $memberId;
                    $val['collector_code'] = $row['memberAssocite']->associate_no ?? 'N/A';
                    $val['collector_name'] = isset($row['memberAssocite']) ? $row['memberAssocite']->first_name . ' ' . $row['memberAssocite']->last_name : 'N/A';
                    $val['amount'] = $row->amount;
                    if ($row->transaction_type == 1) {
                        $type = 'Bank';
                    } else {
                        $type = 'SSB';
                    }
                    $val['ecs_mode'] = $type;
                    if ($row->transaction_status == 1) {
                        $transaction = "Success";
                    } else {
                        $transaction = "Fail";
                    }
                    $val['ecs_status'] = $transaction;
                    $val['bounc_charge'] = $row->bounce_charge ?? '0.00';
                    $val['sgst'] = $row->sgst_charge ?? '0.00';
                    $val['cgst'] = $row->cgst_charge ?? '0.00';
                    $val['igst'] = $row->igst_charge ?? '0.00';
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $count, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            } else {
                $output = array(
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    public function ecsExport(Request $request)
    {
        if ($request['ecs_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/ecs_transaction_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/ecs_transaction_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = ECSTransaction::with([
            'memberLoanDetails:id,customer_id,loan_type',
            'memberLoanDetails.loanPlans:id,name',
            'memberLoanDetails.loanMember:id,first_name,last_name,member_id',
            'loanBranch:id,name,branch_code',
            'memberGroupLoanDetails:id,customer_id,loan_type',
            'memberGroupLoanDetails.loanPlans:id,name',
            'memberGroupLoanDetails.loanMember:id,first_name,last_name,member_id',
            'memberAssocite:id,associate_id,first_name,last_name,associate_no'
        ]);
        if ($request['ecs_status'] != '') {
            $data = $data->where('transaction_status', $request['ecs_status']);
        }
        if ($request['ecs_type'] != '') {
            $data = $data->where('transaction_type', $request['ecs_type']);
        }
        if ($request['company_id'] > 0) {
            $data = $data->where('company_id', $request['company_id']);
        }
        $startDate = isset($request->from_date) ? date("Y-m-d", strtotime(convertDate($request->from_date))) : null;
        $endDate = isset($request->to_date) ? date("Y-m-d", strtotime(convertDate($request->to_date))) : null;
        if ($startDate !== null && $endDate !== null) {
            $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
        }
        if (Auth::user()->role_id == 3) {
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = $data->where('branch_id', $branch_id);
        } else if ($request['branch_id'] > 0) {
            $data = $data->where('branch_id', $request['branch_id']);
        }
        if (!empty($request['customer_id'])) {
            $customerID = $request['customer_id'];
            $data->where(function ($query) use ($customerID) {
                $query->orWhereHas('memberGroupLoanDetails.loanMember', function ($subQuery) use ($customerID) {
                    $subQuery->where('member_id', 'LIKE', '%' . $customerID . '%');
                })->orWhereHas('memberLoanDetails.loanMember', function ($subQuery) use ($customerID) {
                    $subQuery->where('member_id', 'LIKE', '%' . $customerID . '%');
                });
            });
        }
        if ($request['account_no'] != '') {
            $data = $data->where('account_number', $request['account_no']);
        }
        if ($request['ecs_export'] == 0) {
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
                $val['S/N'] = $sno;
                $val['DATE'] = date("d/m/Y", strtotime($row->date));
                $val['BRANCH NAME'] = $row['loanBranch']->name . '(' . $row['loanBranch']->branch_code . ')';
                $val['ACCOUNT NO'] = $row->account_number;
                $val['PLAN'] = $row['memberLoanDetails']['loanPlans']['name'] ?? $row['memberGroupLoanDetails']['loanPlans']['name'] ?? '';
                $memberId = $row['memberGroupLoanDetails']['loanMember']['member_id'] ?? $row['memberLoanDetails']['loanMember']['member_id'] ?? '';
                $firstName = $row['memberGroupLoanDetails']['loanMember']['first_name'] ?? $row['memberLoanDetails']['loanMember']['first_name'] ?? '';
                $lastName = $row['memberGroupLoanDetails']['loanMember']['last_name'] ?? $row['memberLoanDetails']['loanMember']['last_name'] ?? '';
                $val['CUSTOMER NAME'] = $memberId !== '' ? $firstName . ' ' . $lastName : '';
                $val['CUSTOMER ID'] = $memberId;
                $val['COLLECTOR CODE'] = $row['memberAssocite']->associate_no ?? 'N/A';
                $val['COLLECTOR NAME'] = isset($row['memberAssocite']) ? $row['memberAssocite']->first_name . ' ' . $row['memberAssocite']->last_name : 'N/A';
                $val['AMOUNT'] = $row->amount;
                if ($row->transaction_type == 1) {
                    $type = 'Bank';
                } else {
                    $type = 'SSB';
                }
                $val['ECS MODE'] = $type;
                if ($row->transaction_status == 1) {
                    $transaction = "Success";
                } else {
                    $transaction = "Fail";
                }
                $val['ECS STATUS'] = $transaction;
                $val['BOUNC CHARGE'] = $row->bounce_charge ?? '0.00';
                $val['SGST'] = $row->sgst_charge ?? '0.00';
                $val['CGST'] = $row->cgst_charge ?? '0.00';
                $val['IGST'] = $row->igst_charge ?? '0.00';
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
    }
}