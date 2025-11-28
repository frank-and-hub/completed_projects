<?php

namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use URL;
use App\Models\Member;
use App\Models\Daybook;
use App\Models\AllHeadTransaction;
use App\Models\SamraddhBank;
use App\Models\BranchDaybook;
use App\Exports\{Update15GExport,BranchFundTransferExport};
use App\Models\AccountHeads;
use App\Models\SamraddhBankDaybook;
use App\Models\AllTransaction;
use App\Exports\GroupLoanListExport;
use App\Exports\LoanListExport;
use App\Exports\BranchDayBookReportExport;
use App\Exports\BranchMemberExport;
use App\Exports\InvestmentMembersExport;
use App\Exports\BranchRenewalListExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Memberinvestments;
use App\Models\AssociateCommission;
use App\Exports\AssociateExport;
use App\Exports\AssociateCommissionExport;
use App\Exports\InvestmentCommissionExport;
use App\Exports\InvestmentChequeStatusExport;
use App\Exports\KotaReportsExport;
use App\Exports\AssociateCommissionDetailExport;
use App\Exports\AssociateTreeExport;
use App\Exports\ReportBranchBusinessExport;
use App\Exports\AssociateCommissionDetailBranchExport;
use App\Exports\AssociateCommissionBranchExport;
use App\Exports\InvestmentCommissionBranchExport;
use App\Exports\InvestmentMembersBranchExport;
use App\Exports\BranchVoucherExport;
use App\Exports\BranchMaturityReportExport;
use App\Exports\DemandAdviceExport;
use App\Exports\ReceivedChequeBranchExport;
use App\Exports\AssociateBranchExport;
use App\Models\SavingAccount;
use App\Models\EmployeeSalary;
use App\Models\SavingAccountTranscation;
use App\Models\ReceivedVoucher;
use App\Exports\EmployeeTransferBranchExport;
use App\Exports\EmployeeBranchExport;
use App\Exports\EmployeeApplicationBranchExport;
use App\Exports\ReportAssociateBusinessBranchExport;
use App\Exports\ReportAssociateBusinessSummaryBranchExport;
use App\Exports\ReportAssociateBusinessCompareBranchExport;
use App\Exports\BranchAssociateCommissionDetailLoanExport;
use App\Exports\DublicateDaybookReportBranchExport;
use App\Exports\BranchLoanGroupCommissionExport;
use App\Exports\LoanReportListBranchExport;
use App\Exports\GroupLoanReportListBranchExport;
use App\Models\Grouploans;
use App\Models\Companies;
use DB;
use App\Exports\MemberInvestmentExport;
use App\Models\Memberloans;
use App\Models\{MemberCompany,Form15G};
use Validator;
use Carbon\Carbon;
use LDAP\Result;
use PDF;
use Illuminate\Support\Facades\Cache;
use Session;
class ExportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }
    /**
     * Export member listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportMember(Request $request)
    {
        if ($request['member_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $company_id = Companies::whereId($request['company_id'])->first(['id', 'name', 'short_name']);
            $returnURL = URL::to('/') . "/asset/" . $company_id->short_name . "_membr_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/" . $company_id->short_name . "_membr_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $endDate = '';
        $relation = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data =  MemberCompany::with(['branch' => function ($q) {
            $q->select('id', 'name', 'branch_code', 'sector', 'zone');
        }])
            ->with(['memberAssociate' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'associate_no', 'member_id');
            }])
            ->with(['member' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'dob', 'email', 'mobile_no', 'status', 'signature', 'photo', 'village', 'pin_code', 'state_id', 'district_id', 'city_id', 'branch_id', 'address', 'gender', 'member_id', 'reinvest_old_account_number')

                    ->with(['states' => function ($q) {
                        $q->select('id', 'name');
                    }])
                    ->with([
                        'city' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ])
                    ->with([
                        'district' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ])
                    ->with([
                        'memberIdProof' => function ($q) {
                            $q->select('id', 'first_id_no', 'second_id_no', 'member_id', 'first_id_type_id', 'second_id_type_id')
                                ->with(['idTypeFirst' => function ($q) {
                                    $q->select(['id', 'name']);
                                }])
                                ->with(['idTypeSecond' => function ($q) {
                                    $q->select(['id', 'name']);
                                }]);
                        }
                    ])
                    ->with(['memberNomineeDetails' => function ($q) {
                        $q->select('id', 'name', 'relation', 'age', 'member_id', 'gender')->with(['nomineeRelationDetails' => function ($q) {
                            $q->select('id', 'name');
                        }]);
                    }])
                    ->with(['savingAccount_Custom' => function ($q) {
                        $q->select('id', 'account_no', 'member_id');
                    }]);
            }])
            ->where('member_id', '!=', '9999999')->where('branch_id', $getBranchId->id)->where('role_id', 5)->where('is_deleted', 0);



        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            $data = $data = $data->where('company_id', '=', $company_id);
        }
        if ($request['customer_id'] != '') {
            $customer_id = $request['customer_id'];
            $data = $data->whereHas('member', function ($query) use ($customer_id) {
                $query->where('members.member_id', $customer_id);
            });
        }

        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->whereHas('memberAssociate', function ($query) use ($associate_code) {
                $query->where('members.associate_no', $associate_code);
            });
        }
        if ($request['member_id'] != '') {
            $meid = $request['member_id'];
            $data = $data->where('member_id', '=', $meid);
        }

        if ($request['name'] != '') {
            $name = $request['name'];
            $data = $data->whereHas(
                'member',
                function ($qm) use ($name) {
                    $qm->where('first_name', 'LIKE', '%' . $name . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $name . '%')
                        ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                }
            );
        }
        if ($request['start_date'] != '') {

            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));

            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }


        if ($request['member_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count();
            //dd($totalResults);
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
                $NomineeDetail =  $row['member']['memberNomineeDetails'];
                $val['S/N'] = $sno;
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->re_date));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->sector;
                $val['ZO NAME'] = $row['branch']->zone;
                $val['CUSTOMER ID'] = $row['member']->member_id;
                $val['MEMBER ID'] = $row->member_id;
                $val['NAME'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                $val['MEMBER DOB'] = date('d/m/Y', strtotime($row['member']->dob));
                if ($row['member']->gender == 1) {
                    $val['Gender'] = 'Male';
                } else {
                    $val['Gender'] = 'Female';
                }
                $val['MOBILE NO'] = $row['member']->mobile_no;
                // $val['ASSOCIATE CI NO'] = $row['memberAssociate']->member_id;
                $val['ASSOCIATE CODE'] = $row['memberAssociate']->associate_no;
                $val['ASSOCIATE NAME'] = $row['memberAssociate']->first_name . ' ' . $row['memberAssociate']->last_name;
                $val['NOMINEE NAME'] = $NomineeDetail->name;
                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;
                    if ($relation_id) {
                        $relation = $NomineeDetail['nomineeRelationDetails']->name;
                    }
                } else {
                    $relation = '';
                }
                $val['RELATION'] = $relation;
                $val['NOMINEE AGE'] = $NomineeDetail->age;
                if ($NomineeDetail->gender == 1) {
                    $val['NOMINEE Gender'] = 'Male';
                } else {
                    $val['NOMINEE Gender'] = 'Female';
                }
                $status = '';
                if ($row['member']->is_block == 1) {
                    $status = 'Blocked';
                } else {
                    if ($row['member']->status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'Inactive';
                    }
                }
                $val['STATUS'] = $status;
                $is_upload = 'Yes';
                if ($row['member']->signature == '') {
                    $is_upload = 'No';
                }
                if ($row->photo == '') {
                    $is_upload = 'No';
                }
                $val['IS UPLOADED'] = $is_upload;
                $val['ADDRESS'] = preg_replace( "/\r|\n/", " ",$row['member']->address);
                $val['STATE'] = $row['member']['states']->name;
                $val['DISTRICT'] = $row['member']['district']->name;
                $val['CITY'] = $row['member']['city']->name;
                $val['VILLAGE'] = $row['member']->village;
                $val['PIN CODE'] = $row['member']->pin_code;
                $val['FIRST ID PROOF'] = $row['member']['memberIdProof']['idTypeFirst']['name'] . ' - ' . $row['member']['memberIdProof']['first_id_no'];
                $val['SECOND ID PROOF'] = $row['member']['memberIdProof']['idTypeFirst']['name'] . ' - ' . $row['member']['memberIdProof']['second_id_no'];
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
        }
        if ($request['member_export'] == 1) {
            $memberList = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.member_management.memberexport', compact('memberList'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($company_id->short_name . '_members.pdf');
        }
    }



    public function exportCustomer(Request $request)
    {
        if ($request['customer_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/customer_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/customer_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $endDate = '';
        $relation = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $data = Member::with(['branch' => function ($q) {
            $q->select(['id', 'name', 'zone', 'sector', 'branch_code', 'regan']);
        }])
            ->with(['children' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'associate_no', 'member_id']);
            }])
            ->with(['states' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['city' => function ($q) {
                $q->select(['id', 'name']);
            }])
            ->with(['district' => function ($q) {
                $q->select(['id', 'name']);
            }])
            ->with(['memberIdProof' => function ($q) {
                $q->with(['idTypeFirst' => function ($q) {
                    $q->select(['id', 'name']);
                }])->with(['idTypeSecond' => function ($q) {
                    $q->select(['id', 'name']);
                }]);
            }])
            ->with(['memberNomineeDetails' => function ($q) {
                $q->with(['nomineeRelationDetails' => function ($q) {
                    $q->select('id', 'name');
                }]);
            }])
            ->where('member_id', '!=', '9999999')->where('branch_id', $getBranchId->id)->where('role_id', 5)->where('is_deleted', 0);



        if ($request['customer_id'] != '') {
            $customer_id = $request['customer_id'];
            $data = $data->where('member_id', 'LIKE', '%' . $customer_id . '%');
        }

        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['member_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count('id');
            //dd($totalResults);
            $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get([
                'id', 'dob', 're_date', 'member_id', 'first_name', 'last_name', 'mobile_no', 'email', 'associate_code',
                'associate_id', 'status', 'signature', 'photo', 'address', 'state_id', 'district_id', 'city_id', 'village', 'pin_code', 'is_block', 'branch_id', 'gender'
            ]);
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
                $NomineeDetail = $row['memberNomineeDetails'];
                $val['S/N'] = $sno;
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->re_date));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->sector;
                $val['ZO NAME'] = $row['branch']->zone;
                $val['CUSTOMER ID'] = $row->member_id;
                $val['NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['MEMBER DOB'] = date('d/m/Y', strtotime($row->dob));
                if ($row->gender == 1) {
                    $val['Gender'] = 'Male';
                } else {
                    $val['Gender'] = 'Female';
                }
                $val['MOBILE NO'] = $row->mobile_no;
                $val['ASSOCIATE CI NO'] = $row['children']->member_id;
                $val['ASSOCIATE CODE'] = $row['children']->associate_no;
                $val['ASSOCIATE NAME'] = $row['children']->first_name . ' ' . $row['children']->last_name;
                $val['NOMINEE NAME'] = $NomineeDetail->name;
                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;
                    if ($relation_id) {
                        $relation = $NomineeDetail['nomineeRelationDetails']->name;
                    }
                } else {
                    $relation = '';
                }
                $val['RELATION'] = $relation;
                $val['NOMINEE AGE'] = $NomineeDetail->age;
                if ($NomineeDetail->gender == 1) {
                    $val['NOMINEE Gender'] = 'Male';
                } else {
                    $val['NOMINEE Gender'] = 'Female';
                }
                $status = '';
                if ($row->is_block == 1) {
                    $status = 'Blocked';
                } else {
                    if ($row->status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'Inactive';
                    }
                }
                $val['STATUS'] = $status;
                $is_upload = 'Yes';
                if ($row->signature == '') {
                    $is_upload = 'No';
                }
                if ($row->photo == '') {
                    $is_upload = 'No';
                }
                $val['IS UPLOADED'] = $is_upload;
                $val['ADDRESS'] = preg_replace( "/\r|\n/", "",$row->address);
                $val['STATE'] = $row['states']->name;
                $val['DISTRICT'] = $row['district']->name;
                $val['CITY'] = $row['city']->name;
                $val['VILLAGE'] = $row->village;
                $val['PIN CODE'] = $row->pin_code;
                $idProofDetail = \App\Models\MemberIdProof::where('member_id', $row->id)->first();
                $val['FIRST ID PROOF'] = $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no;
                $val['SECOND ID PROOF'] = $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no;
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
        }
    }


    public function exportInvestmentPlan(Request $request)
    {
        $data = Memberinvestments::with('plan', 'member', 'associateMember')->where('is_deleted', 0)/*->where('branch_id',$branch_id)*/;
        /******* fillter query start ****/
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['start_date'] != '') {
                $startDate = $request['start_date'];
                $endDate = $request['end_date'];
                $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            if ($request['branch_id'] != '') {
                $id = $request['branch_id'];
                $data = $data->where('branch_id', '=', $id);
            }
            if ($request['plan_id'] != '') {
                $planId = $request['plan_id'];
                $data = $data->where('plan_id', '=', $planId);
            }
            if ($request['scheme_account_number'] != '') {
                $sAccountNumber = $request['scheme_account_number'];
                $data = $data->where('account_number', $sAccountNumber);
            }
            if ($request['member_id'] != '') {
                $meid = $request['member_id'];
                $data = $data->whereHas('member', function ($query) use ($meid) {
                    $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                });
            }
            if ($request['associate_code'] != '') {
                $associateCode = $request['associate_code'];
                $data = $data->whereHas('associateMember', function ($query) use ($associateCode) {
                    $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                });
            }
            if ($request['name'] != '') {
                $name = $request['name'];
                $data = $data->whereHas('member', function ($query) use ($name) {
                    $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                });
            }
        }
        /******* fillter query End ****/
        $investmentMemberLists = $data->orderby('id', 'DESC')->get();
        if ($request['investments_export'] == 0) {
            return Excel::download(new InvestmentMembersExport($investmentMemberLists), 'investmentMembersLists.xlsx');
        } elseif ($request['investments_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.member.investmentexport', compact('investmentMemberLists'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('investmentMembersLists.pdf');
        }
    }
    public function exportInvestmentPlanBranch(Request $request)
    {
        $data = Cache::get('investment_list');
        $count = Cache::get('investment_list_count');

        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/Investmentreport.csv";
        $fileName = env('APP_EXPORTURL')."report/Investmentreport.csv";
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
        // $record = array_slice($data, $start, $limit);
        $record = $data->slice($start, $limit);
        $totalCount = count($record);

        foreach ($record as $row) {

            $sno++;
            $val['DT_RowIndex'] = $sno;
            $val['plan'] = $row['plan']->name;
            $val['company'] = $row['company']['name'] ?? 'N/A';
            $val['form_number'] = $row->form_number;
            $val['tenure'] = ($row->plan_id == 1) ? 'N/A' : number_format((float)$row->tenure, 2, '.', '') . ' Year';

            if ($row->plan_id == 1) {
                $ssb = getSsbAccountDetail($row->account_number);
                $current_balance = isset($ssb->balance) ? $ssb->balance : 'N/A';
            } else {
                $dayBook = Daybook::where('investment_id', $row->id)->where('account_no', $row->account_number)->orderby('created_at', 'desc')->first(['opening_balance']);
                if ($dayBook) {
                    $current_balance = $dayBook->opening_balance;
                } else {
                    $current_balance = $row->deposite_amount;
                }
            }


            $val['current_balance'] = $current_balance;
            $val['eli_amount'] = investmentEliAmount($row->id);
            $val['deposite_amount'] = $row->deposite_amount;
            $val['member'] = $row['memberCompany']['member']->first_name . ' ' . $row['memberCompany']['member']->last_name;
            $val['customer_id'] = ($row['memberCompany']['member']) ? $row['memberCompany']['member']['member_id'] : 'N/A';
            $val['member_id'] = isset($row['memberCompany']->member_id) ?  $row['memberCompany']->member_id : 'N/A';
            $val['mobile_number'] = $row['memberCompany']['member']->mobile_no;
            if ($row['associateMember']) {
                $val['associate_code'] = $row['associateMember']['associate_no'];
            } else {
                $val['associate_code'] = "N/A";
            }
            $val['account_number'] = $row['account_number'];
            $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
            $val['account_number'] = $row->account_number;
            if ($row['associateMember']) {
                $val['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
            } else {
                $val['associate_name'] = "N/A";
            }
            $val['branch'] = $row['branch']->name;
            $val['branch_code'] = $row['branch']->branch_code;
            $val['sector_name'] = $row['branch']->sector;
            $val['region_name'] = $row['branch']->regan;
            $val['zone_name'] = $row['branch']->zone;
            $idProofDetail = \App\Models\MemberIdProof::select('first_id_type_id', 'second_id_type_id', 'first_id_no', 'second_id_no')->where('member_id', $row['memberCompany']['member']->id)->first();
            $val['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
            $val['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
            if (isset($row['CollectorAccount']['member_collector']['associate_no'])) {
                $val['collectorcode'] = $row['CollectorAccount']['member_collector']['associate_no'];
            } else {
                $val['collectorcode'] = "N/A";
            }
            if (isset($row['CollectorAccount']['member_collector']['first_name'])) {
                $val['collectorname'] = $row['CollectorAccount']['member_collector']['first_name'] . ' ' . $row['CollectorAccount']['member_collector']['last_name'];
            } else {
                $val['collectorname'] = "N/A";
            }
            $val['address'] = preg_replace( "/\r|\n/", "",$row['memberCompany']['member']->address);
            $val['state'] = getStateName($row['memberCompany']['member']->state_id);
            $val['district'] = getDistrictName($row['memberCompany']['member']->district_id);
            $val['city'] = getCityName($row['memberCompany']['member']->city_id);
            $val['village'] = $row['memberCompany']['member']->village;
            $val['pin_code'] = $row['memberCompany']['member']->pin_code;

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

    /**
     * Export associate  listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportAssociate(Request $request)
    {
        if ($request['member_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate_list.csv";
            $fileName = env('APP_EXPORTURL')."asset/associate_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $startDate = '';
        $endDate = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = Member::select(['id', 'associate_branch_id', 'member_id', 'associate_no', 'first_name', 'last_name', 'dob', 'associate_join_date', 'mobile_no', 'email', 'associate_senior_id', 'associate_senior_code', 'associate_status', 'photo', 'signature', 'address', 'state_id', 'district_id', 'city_id', 'village', 'pin_code'])->with(['associate_branch'])
            ->with(['seniorData' => function ($q) {
                $q->select(['id', 'first_name', 'last_name']);
            }])
            ->with(['states' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['city' => function ($q) {
                $q->select(['id', 'name']);
            }])
            ->with(['district' => function ($q) {
                $q->select(['id', 'name']);
            }])
            ->with(['memberIdProof' => function ($q) {
                $q->with(['idTypeFirst' => function ($q) {
                    $q->select(['id', 'name']);
                }])
                    ->with(['idTypeSecond' => function ($q) {
                        $q->select(['id', 'name']);
                    }]);
            }])
            ->with(['memberNomineeDetails' => function ($q) {
                $q->with(['nomineeRelationDetails' => function ($q) {
                    $q->select('id', 'name');
                }]);
            }])
            // ->where('member_id', '!=', '9999999')
            ->where('member_id', '!=', '0CI09999999')
            ->where('associate_branch_id', $branch_id)
            ->where('is_associate', 1)
            ->where('is_deleted', 0);
        if ($request->branch_id != '') {
            $branch_id = $request->branch_id;
            $data = $data->where('associate_branch_id', $branch_id);
        }
        if ($request['sassociate_code'] != '') {
            $associate_code = $request['sassociate_code'];
            $data = $data->where('associate_senior_code', '=', $associate_code);
        }
        if ($request['associate_code'] != '') {
            $meid = $request['associate_code'];
            $data = $data->where('associate_no', '=', $meid);
        }
        if ($request['customer_id'] != '') {
            $customer_id = $request['customer_id'];
            $data = $data->where('member_id', 'like', '%'.$customer_id.'%');
        }
        if ($request['name'] != '') {
            $name = $request['name'];
            $data = $data->where(function ($query) use ($name) {
                $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
        }
        if ($request['member_export'] == 0) {
            $totalResults = $data->orderby('associate_join_date', 'DESC')->count();
            //dd($totalResults);
            $results = $data->orderby('associate_join_date', 'DESC')->offset($start)->limit($limit)->get();
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
            $rowReturn = array();
            foreach ($results as $row) {
                $relationId = '';
                $sno++;
                $val['S/N'] = $sno;
                $NomineeDetail = $row['memberNomineeDetails'];
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['MEMBER ID'] = $row->member_id;
                $val['ASSOCIATE ID'] = $row->associate_no;
                $val['NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['ASSOCIATE DOB'] = date('d/m/Y', strtotime($row->dob));
                $val['NOMINEE NAME'] = $NomineeDetail->name;
                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;
                    if ($relation_id) {
                        $val['RELATION'] = $NomineeDetail['nomineeRelationDetails']->name;
                    } else {
                        $val['RELATION'] = 'N/A';
                    }
                }
                $val['NOMINEE AGE'] = $NomineeDetail->age;
                $val['EMAIL ID'] = $row->email;
                $val['MOBILE NO'] = $row->mobile_no;
                if ($row->associate_senior_id == 0) {
                    $senior_code = $row->associate_senior_id . ' (Super Admin)';
                } else {
                    $senior_code = $row->associate_senior_code;
                }
                // $val['SENIOR CODE']=$senior_code;
                // $val['SENIOR NAME']=$row['seniorData']->first_name.' '.$row['seniorData']->last_name;
                if ($row->is_block == 1) {
                    $status = 'Blocked';
                } else {
                    if ($row->associate_status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'Inactive';
                    }
                }
                $val['STATUS'] = $status;
                $is_upload = 'Yes';
                if ($row->signature == '') {
                    $is_upload = 'No';
                }
                if ($row->photo == '') {
                    $is_upload = 'No';
                }
                $val['IS UPLOADED'] = $is_upload;
                $val['ADDRESS'] = preg_replace( "/\r|\n/", "",$row->address);
                $val['STATE'] = $row['states']->name;
                $val['DISTRICT'] = $row['district']->name;
                $val['CITY'] = $row['city']->name;
                $val['VILLAGE'] = $row->village;
                $val['PIN CODE'] = $row->pin_code;
                $val['FIRST ID PROOF'] = $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no;
                $val['SECOND ID PROOF'] = $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no;
                $rowReturn[] = $val;
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['member_export'] == 1) {
            $associateList = $data->orderby('associate_join_date', 'DESC')->get();
            $rowReturn = array();
            foreach ($associateList as $row) {
                $NomineeDetail = $row['memberNomineeDetails'];
                //$val['S/N']=$sno;
                $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['branch'] = $row['associate_branch']->name;
                $val['branch_code'] = $row['associate_branch']->branch_code;
                $val['sector_name'] = $row['associate_branch']->sector;
                $val['region_name'] = $row['associate_branch']->regan;
                $val['zone_name'] = $row['associate_branch']->zone;
                $val['member_id'] = $row->member_id;
                $val['m_id'] = $row->associate_no;
                $val['name'] = $row->first_name . ' ' . $row->last_name;
                $val['dob'] = date('d/m/Y', strtotime($row->dob));
                $val['nominee_name'] = $NomineeDetail->name;
                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;
                    $relation_id = $NomineeDetail->relation;
                    if ($relation_id) {
                        $val['relation'] = $NomineeDetail['nomineeRelationDetails']->name;
                    } else {
                        $val['relation'] = 'N/A';
                    }
                }
                $val['nominee_age'] = $NomineeDetail->age;
                $val['email'] = $row->email;
                $val['mobile_no'] = $row->mobile_no;
                if ($row->associate_senior_id == 0) {
                    $senior_code = $row->associate_senior_id . ' (Super Admin)';
                } else {
                    $senior_code = $row->associate_senior_code;
                }
                $val['associate_code'] = $senior_code;
                $val['associate_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                if ($row->is_block == 1) {
                    $status = 'Blocked';
                } else {
                    if ($row->associate_status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'Inactive';
                    }
                }
                $val['status'] = $status;
                $is_upload = 'Yes';
                if ($row->signature == '') {
                    $is_upload = 'No';
                }
                if ($row->photo == '') {
                    $is_upload = 'No';
                }
                $val['is_upload'] = $is_upload;
                $val['address'] = preg_replace( "/\r|\n/", "",$row->address);
                $val['state'] = $row['states']->name;
                $val['district'] = $row['district']->name;
                $val['city'] = $row['city']->name;
                $val['village'] = $row->village;
                $val['pin_code'] = $row->pin_code;
                $val['firstId'] = $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no;
                $val['secondId'] = $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no;
                $rowReturn[] = $val;
            }
            $pdf = PDF::loadView('templates.branch.associate_management.export', compact('rowReturn', 'associateList'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('BranchAssociate.pdf');
        }
    }
    /**
     * Export associate commission listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportAssociateCommission(Request $request)
    {
        if ($request['commission_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate_commission.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate_commission.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $year = $input['year'];
        $month = $input['month'];
        $sday = 1;
        $company_id = $input['company_id'];
        $startDate = Carbon::create($year, $month, $sday)->format('Y-m-d');
        $endDate = Carbon::create($year, $month)->endOfMonth()->toDateString();
        $b_id = getUserBranchId(Auth::user()->id)->id;
        $data = Member::with([
            'associate_branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])
            ->with([
                'seniorData' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name', 'current_carder_id'])
                        ->with([
                            'getCarderNameCustom' => function ($q) {
                                        $q->select('id', 'name', 'short_name');
                                    }
                        ]);
                }
            ])
            ->with([
                'getCarderNameCustom' => function ($q) {
                    $q->select('id', 'name', 'short_name');
                }
            ])
            ->withCount([
                'AssociateTotalCommission' => function ($q) {
                    $q->select(DB::raw('sum(commission_amount)'));
                }
            ])
            ->where('member_id', '!=', '9999999')->where('is_associate', 1);
        $data = $data->where('associate_branch_id', '=', $b_id);
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->where(function ($query) use ($name) {
                $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['commission_export'] == 0) {
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
            $rowReturn = array();
            foreach ($results as $row) {
                //  dd($row->count());
                $sno++;
                $val['S N'] = $sno;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCIATE CARDER'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')';
                // $val['TOTAL COMMISION AMOUNT'] = number_format($row->associate_total_commission_count, 2, '.', ''); //getAssociateTotalCommissionDistribute($row->id,$startDate,$endDate,'commission_amount');
                if (($year == 2022 && $month <= 11) || ($year < 2022)) {
                    $val['TOTAL COMMISION AMOUNT'] = getAssociateTotalCommissionAdmin($row->id, $startDate, $endDate, 'commission_amount');
                    $val['TOTAL COLLECTION AMOUNT'] = getTotalCollection($row->id, $startDate, $endDate); //plan no 4,9 not include in this and loan recovery also not added
                    $val['TOTAL COLLECTION AMOUNT ALL'] = getTotalCollection_all($row->id, $startDate, $endDate); //Investment total renewal all type add and loan recovery also not added
                } else {
                    $val['TOTAL COMMISION AMOUNT'] = getAssociateTotalCommissionAdminNew($row->id, $year, $month, $company_id);
                    $val['TOTAL COLLECTION AMOUNT'] = getTotalCollectionNew2($row->id, $year, $month, $company_id); //plan no 4,9 not include in this and loan recovery also not added
                    $val['TOTAL COLLECTION AMOUNT ALL'] = getTotalCollection_allNew($row->id, $year, $month, $company_id); //Investment total renewal all type add and loan recovery also not added
                }
                $val['SENIOR CODE'] = $row->associate_senior_code;
                $val['SENIOR NAME'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name; //getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
                $val['SENIOR CARDER'] = $row['seniorData']['getCarderNameCustom']->name . '(' . $row['seniorData']['getCarderNameCustom']->short_name . ')'; //getCarderName(getSeniorData($row->associate_senior_id,'current_carder_id'));
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['commission_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.associate_management.exportcommission', compact('data', 'startDate', 'endDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('branch_associatecommission.pdf');
        }
    }
    /**
     * Export associate commission listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportKotaBusinessReport(Request $request)
    {
        $data = Member::with(['associate_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->where('is_associate', 1)->where('is_deleted', 0);
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['associate_code'] != '') {
                $associate_code = $request['associate_code'];
                $data = $data->whereHas('member', function ($query) use ($associate_code) {
                    $query->where('members.associate_no', 'LIKE', '%' . $associate_code . '%');
                });
            }
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            if ($request['branch_id'] != '') {
                $id = $request['branch_id'];
                $data = $data->where('associate_branch_id', '=', $id);
            }
            if ($request['associate_code'] != '') {
                $associate_code = $request['associate_code'];
                $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
            }
            if ($request['associate_name'] != '') {
                $name = $request['associate_name'];
                $data = $data->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
            }
            if ($request['cader_id'] != '') {
                $cader_id = $request['cader_id'];
                $data = $data->where('current_carder_id', '=', $cader_id);
            }
        }
        $data = $data->orderby('id', 'DESC')->get();
        if ($request['kotareport_export'] == 0) {
            return Excel::download(new KotaReportsExport($data), 'kotareports.xlsx');
        } elseif ($request['kotareport_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.associate.exportkotareports', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('kotareports.pdf');
        }
    }
    /**
     * Export investment listing commission in pdf,csv.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportInvestmentCommission(Request $request)
    {
        $mid = Member::where('associate_no', '9999999')->first('id');
        $data =  AssociateCommission::where('member_id', '!=', $mid->id)->where('type', 3)->where('type_id', $request['investment_id'])->orderby('id', 'DESC')->get();
        $investment = Memberinvestments::where('id', $request['investment_id'])->first();
        if ($request['investmentcommission_export'] == 0) {
            return Excel::download(new InvestmentCommissionBranchExport($data, $investment), 'branch_investmentcommissionexports.xlsx');
        } elseif ($request['investmentcommission_export'] == 1) {
            $pdf = PDF::loadView('templates.branch.investment_management.exportinvestmentcommission', compact('data', 'investment'))->setPaper('a4', 'landscape')->setWarnings(false);;
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('branch_investmentcommissionexports.pdf');
        }
    }
    /**
     * Export investment cheque status listing in pdf,csv.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportChequeStatusListing(Request $request)
    {
        $data = Memberinvestments::with('plan', 'investmentPayment')->where('payment_mode', 1)->orderBy('id', 'DESC')->get();
        if ($request['cheque_export'] == 0) {
            return Excel::download(new InvestmentChequeStatusExport($data), 'chequestatusexports.xlsx');
        } elseif ($request['cheque_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.member.exportchequestatus', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('chequestatusexports.pdf');
        }
    }
    /**
     * Export associate commission Detail listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportAssociateCommissionDetail(Request $request)
    {
        if ($request['commission_export'] == 0) {
            $input = $request->all();
            //dd($input);
            $getBranchId = getUserBranchId(Auth::user()->id);
            $BranchId = $getBranchId->id;
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate_commission_detail.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate_commission_detail.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\AssociateMonthlyCommission::with('investment')->with('investment.plan:id,name')->where('assocaite_id', $request['id'])->where('type', 1)->where('is_deleted', '0');
        if ($request['year'] != '') {
            $year = $request['year'];
            $data = $data->where('commission_for_year', $year);
        }
        if ($request['month'] != '') {
            $month = $request['month'];
            $data = $data->where('commission_for_month', $month);
        }
        if (isset($request['plan_id']) && $request['plan_id'] != '') {
            $meid = $request['plan_id'];
            $data = $data->whereHas('investment', function ($query) use ($meid) {
                $query->where('member_investments.plan_id', $meid);
            });
        }
        if ($request['commission_export'] == 0) {
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
            $rowReturn = array();
            foreach ($results as $row) {
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
                $val['MONTH'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                $val['ACCOUNT NUMBER'] = $row['investment']->account_number;
                $val['PLAN NAME'] = $row['investment']->plan->name;
                $val['TOTAL NAME'] = number_format((float) $row->total_amount, 2, '.', '');
                $val['QUALIFYING AMOUNT '] = number_format((float) $row->qualifying_amount, 2, '.', '');
                $val['COMMISSION AMOUNT'] = number_format((float) $row->commission_amount, 2, '.', '');
                $val['PERSENTAGE'] = number_format((float) $row->percentage, 2, '.', '');
                $val['CARDER FROM'] = $row->cadre_from;
                $val['CARDER TO'] = $row->cadre_to;
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['commission_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.associate_management.exportcommission_detail', compact('data', 'member'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('branch_associatecommission_detail.pdf');
        }
    }
    /**
     * Associate tree export
     *
     * @return \Illuminate\Http\Response
     */
    public function exportAssociateTree(Request $request)
    {
        $associate = Member::where('id', $request['member_id'])->first();
        if ($request['member_export'] == 0) {
            return Excel::download(new AssociateTreeExport($associate), 'associate_tree.xlsx');
        } elseif ($request['member_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.associate.export_associate_tree', compact('associate'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associate_tree.pdf');
        }
    }
    public function memberInvestmentTransaction(Request $request)
    {
        $memberData = Member::leftJoin('member_investments', 'members.id', '=', 'member_investments.member_id')->leftJoin('plans', 'member_investments.plan_id', '=', 'plans.id')->leftJoin('transactions', 'member_investments.id', '=', 'transactions.transaction_type_id')->whereIn('transaction_type', [2, 4])->where('members.member_id', '!=', '9999999')->select('members.id', 'members.first_name', 'members.last_name', 'members.member_id', 'plans.name', 'member_investments.account_number', 'member_investments.current_balance', 'member_investments.deposite_amount', 'transactions.transaction_type', 'transactions.amount', 'transactions.created_at')->get();
        return Excel::download(new MemberInvestmentExport($memberData), 'member_investment_plane_transactions.xlsx');
    }
    /**
     * cheque  List.
     *
     * @return \Illuminate\Http\Response
     */
    public function receivedChequeExport(Request $request)
    {
        if ($request['cheque_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/check_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/check_list.csv";
            //die($fileName.' ='.$returnURL);
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $startDate = '';
        $endDate = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        // code updated by sourab
        $data = \App\Models\ReceivedCheque::with([
            'receivedBank:id,bank_name',
            'receivedCompany:id,name',
            'receivedAccount:id,account_no',
            'receivedBranch:id,name,branch_code,sector,regan,zone',
            'receivedChequePayment:id,cheque_id,created_at'
        ]);
        $getBranchId = $getBranchId->id;
        $data = $data->where('branch_id', $getBranchId);
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
            $data = $data->where('branch_id', $branch_id);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }

        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            $data = $data->where('company_id', $company_id);
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(cheque_create_date)'), [$startDate, $endDate]);
        }
        if ($request['cheque_export'] == 0) {
            $totalResults = $data->orderby('created_at', 'DESC')->count();
            $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
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
            //$rowReturn = array();
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = $row['receivedCompany']->name;
                // $val['BR NAME']=$row['receivedBranch']->name;
                // $val['BR CODE']=$row['receivedBranch']->branch_code;
                // $val['SO NAME']=$row['receivedBranch']->sector;
                // $val['RO NAME']=$row['receivedBranch']->regan;
                // $val['ZO NAME']=$row['receivedBranch']->zone;
                $val['CHEQUE/UTR DATE'] = date("d/m/Y", strtotime($row->cheque_create_date));
                $val['CHEQUE/UTR NUMBER'] = $row->cheque_no;
                $val['CHEQUE/UTR BANK NAME'] = $row->bank_name;
                $val['CHEQUE/UTR BRANCH NAME'] = $row['receivedBranch']->name;
                $val['CHEQUE/UTR ACCOUNT HOLDER NAME'] = $row->account_holder_name;
                $val['CHEQUE/UTR ACCOUNT NO'] = $row->cheque_account_no;
                $val['AMOUNT'] = number_format((float)$row->amount, 2, '.', '');
                $val['DEPOSIT DATE'] = date("d/m/Y", strtotime($row->cheque_deposit_date));
                $val['DEPOSIT BANK NAME'] = $row['receivedBank']->bank_name;
                $val['DEPOSIT ACCOUNT NO'] = $row['receivedAccount']->account_no;
                if ($row->status == 3) {
                    if ($row['receivedChequePayment']) {
                        $val['USED DATE'] = date("d/m/Y", strtotime($row['receivedChequePayment']->created_at));
                    } else {
                        $val['USED DATE'] = "N/A";
                    }
                } else {
                    $val['CLEAR CHECK DATE'] = 'N/A';
                }
                if ($row->clearing_date) {
                    $val['CLEARING DATE'] = date("d/m/Y", strtotime($row->clearing_date));
                } else {
                    $val['CLEARING DATE'] = 'N/A';
                }
                $status = 'New';
                if ($row->status == 1) {
                    $status = 'Pending';
                }
                if ($row->status == 2) {
                    $status = 'Apporved';
                }
                if ($row->status == 3) {
                    $status = 'cleared';
                }
                if ($row->status == 0) {
                    $status = 'Deleted';
                }
                $val['STATUS'] = $status;
                $val['REMARK'] = $row->remark;
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
            //$data = $data->orderby('created_at','DESC')->get();
            // print_r($data);die();
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['cheque_export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.cheque_management.export_received_cheque', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ReceivedChequeListBranch.pdf');
        }
    }
    /*********************hr*************************************/
    /**
     * Employee transfer list.
     *
     * @return \Illuminate\Http\Response
     */

    public function employeeTransferExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/employee_transfer_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/employee_transfer_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $data = \App\Models\EmployeeTransfer::has('company')
        ->with(['transferBranch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['transferBranchOld' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['transferEmployee' => function ($query) {
            $query->select('*');
        }]);
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['associate_code'] != '') {
                $associate_code = $request['associate_code'];
                $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
            }
            if ($request['associate_name'] != '') {
                $name = $request['associate_name'];
                $data = $data->where(function ($query) use ($name) {
                    $query->where('member.first_name', 'LIKE', '%' . $name . '%')->orWhere('member.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(member.first_name," ",member.last_name)'), 'LIKE', "%$name%");
                });
            }
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        }
        $totalResults = $data->orderby('id', 'DESC')->count();
        //dd($totalResults);
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
        $rowReturn = array();
        if ($request['emp_export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['apply_date'] = date("d/m/Y", strtotime($row->apply_date));
                $val['employee_code'] = $row['transferEmployee']->employee_code;
                $val['employee_name'] = $row['transferEmployee']->employee_name;
                $val['old_designation'] = getDesignationData('designation_name', $row->old_designation_id)->designation_name;
                $old_category = '';
                if ($row->old_category == 1) {
                    $old_category = 'On-rolled';
                }
                if ($row->old_category == 2) {
                    $old_category = 'Contract';
                }
                $val['old_category'] = $old_category;
                $val['old_branch'] = $row['transferBranchOld']->name;
                $val['old_branch_code'] = $row['transferBranchOld']->branch_code;
                $val['old_sector'] = $row['transferBranchOld']->sector;
                $val['old_regan'] = $row['transferBranchOld']->regan;
                $val['old_zone'] = $row['transferBranchOld']->old_zone;
                $val['rec_employee_name_old'] = $row->old_recommendation_name;
                $val['transfer_date'] = date("d/m/Y", strtotime(convertDate($row->approve_date))) ?? '';
                $val['branch'] = $row['transferBranch']->name;
                $val['branch_code'] = $row['transferBranch']->branch_code;
                $val['sector'] = $row['transferBranch']->regan;
                $val['zone'] = $row['transferBranch']->zone;
                $val['designation'] = getDesignationData('designation_name', $row->designation_id)->designation_name;
                $category = '';
                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                $val['category'] = $category;
                $val['rec_employee_name'] = $row->recommendation_name;
                $val['file'] = $row->file;
                $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        }
        if ($request['emp_export'] == 1) {
            // echo 'hi';die;
            return Excel::download(new EmployeeTransferBranchExport($data), 'branchEmployeeTransferList.xlsx');
        }
    }

    /**
     * Employee transfer list.
     *
     * @return \Illuminate\Http\Response
     */

    public function employeeExport(Request $request)
    {


        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/hr_employee_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/hr_employee_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
      // pd( $request['branch']);
        $branch_id = $getBranchId->id;
        $data =  \App\Models\Employee::has('company')
            ->with([
                'branch:id,name,branch_code,sector,regan,zone',
                'empApp:id,employee_id,application_type,status',
                'company:id,name'
            ])
            ->where('is_employee', 1)
            ->where('branch_id', $branch_id);
        $data = $data->where('branch_id', $branch_id);
        if ($request['employee_name'] != '') {
            $employee_name = $request['employee_name'];
            $data = $data->where('employee_name', 'LIKE', '%' . $employee_name . '%');
        }
        if ($request['employee_code'] != '') {
            $employee_code = $request['employee_code'];
            $data = $data->where('employee_code', 'LIKE', '%' . $employee_code . '%');
        }
        if ($request['reco_employee_name'] != '') {
            $reco_employee_name = $request['reco_employee_name'];
            //$data=$data->where('recommendation_employee_name',$reco_employee_name);
            $data = $data->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
        }
        // if ($request['branch'] != '') {
        //     $branch = $request['branch'];
        //     $data = $data->where('branch_id', $branch);
        // }
        if ($request['category'] != '') {
            $categoryid = $request['category'];
            $data = $data->where('category', $categoryid);
        }
        if ($request['designation'] != '') {
            $designation = $request['designation'];
            $data = $data->where('designation_id', $designation);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            if ($status == 'resigned') {
                $data = $data->where('is_resigned', '>', 0);
            }
            if ($status == 'terminated') {
                $data = $data->where('is_terminate', 1);
            }
            if ($status == 'tranfered') {
                $data = $data->where('is_transfer', 1);
            }
            if ($status == 'active') {
                $data = $data->where('status', 1);
            }
            if ($status == 'inactive') {
                $data = $data->where('status', 0);
            }
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $totalResults = $data->orderby('created_at', 'DESC')->count();
        $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        if ($request['export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = $row['company']->name;
                $val['DESIGANATION'] = getDesignationData('designation_name', $row->designation_id)->designation_name;
                $category = '';
                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                $val['CATEGORY'] = $category;
                $val['BR ANME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $val['Recommendation Employee Name'] = $row->recommendation_employee_name;
                $val['EMPLOYEE NAME'] = $row->employee_name;
                $val['EMPLOYEE CODE'] = $row->employee_code;
                $val['DOB'] = date("d/m/Y", strtotime($row->dob));
                $gender = '';
                $gender = 'Other';
                if ($row->gender == 1) {
                    $gender = 'Male';
                }
                if ($row->gender == 2) {
                    $gender = 'Female';
                }
                $val['GENDER'] = $gender;
                $val['MOBILE NO'] = $row->mobile_no;
                $val['EMAIL'] = $row->email;
                $val['GUARDIAN NAME'] = $row->father_guardian_name;
                $val['GUARDIAN NUMBER'] = $row->father_guardian_number;
                $val['MOTHER NAME'] = $row->mother_name;
                $val['PAN CARD'] = $row->pen_card;
                $val['AADHAR CARD'] = ($row->aadhar_card != "")?'"' . $row->aadhar_card . '"': "";
                $val['VOTER ID'] = $row->voter_id;
                $val['ESI Account No.'] = ($row->esi_account_no != "")?'"' . $row->esi_account_no . '"': "";
                $val['UAN/PF  Account No.'] = ($row->pf_account_no != "")?'"' . $row->pf_account_no . '"': "";
                $status = '';
                if ($row->is_employee == 0) {
                    $status = 'Pending';
                } else {
                    $status = 'Inactive';
                    if ($row->status == 1) {
                        $status = 'Active';
                    }
                }
                $val['STATUS'] = $status;
                $resign = 'No';
                if ($row->is_resigned == 1 || $row->is_resigned == 2) {
                    $resign = 'Yes';
                }

                if($row->empApp && $row->empApp->application_type==2)
                {
                    if($row->empApp->status==0){
                        $resign = 'Pending';
                    }
                    if($row->empApp->status==1){
                        $resign = 'Approved';
                    }
                    if($row->empApp->status==3){
                        $resign = 'Rejected';
                    }
                    if($row->empApp->status==9){
                        $resign = 'Deleted';
                    }
                }

                $val['IS RESIGNED'] = $resign;
                $terminate = 'No';
                if ($row->is_terminate == 1) {
                    $terminate = 'Yes';
                }
                $val['IS TERMINATED'] = $terminate;
                $transfer = 'No';
                if ($row->is_transfer == 1) {
                    $transfer = 'Yes';
                }
                $val['IS TRANSFERD'] = $transfer;
                $val['CREATED'] = date("d/m/Y", strtotime($row->created_at));
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        }
        if ($request['emp_export'] == 0) {
            // echo 'hi';die;
            return Excel::download(new EmployeeBranchExport($data), 'branchEmployeeList.xlsx');
        }
    }

    /**
     * Employee application list.
     *
     * @return \Illuminate\Http\Response
     */



    public function employeeApplicationExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/employee_application_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/employee_application_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $data = \App\Models\EmployeeApplication::has('company')->with(['branch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])
        ->with(['company' => function ($q) {
            $q->select(['id','name']); }])
        ->with(['employeeget' => function($query){ $query->select('id','category','recommendation_employee_name','employee_name','dob','gender','mobile_no','email','father_guardian_name','father_guardian_number','mother_name','pen_card','aadhar_card','voter_id','designation_id','esi_account_no','pf_account_no');}])

        ->where(function ($query) {
            $query->where('application_type', 1)
                    ->WhereHas('employeeget', function ($query) {
                        $query->where('is_employee', 0);
                    });
        });
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = $data->where('branch_id', $branch_id);
        if ($request->branch_id != '') {
            $branch_id = $request->branch_id;
            $data = $data->where('branch_id', $branch_id);
        }
        if (isset($request['app_type'])) {
            $app_type = $request['app_type'];
            $data = $data->where('application_type', $app_type);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['employee_name'] != '') {
            $employee_name = $request['employee_name'];
            $data = $data->whereHas('employeeget', function ($query) use ($employee_name) {
                $query->where('employee_name', 'LIKE', '%' . $employee_name . '%');
            });
        }
        if ($request['reco_employee_name'] != '') {
            $reco_employee_name = $request['reco_employee_name'];
            //$data=$data->where('recommendation_employee_name',$reco_employee_name);
            $data = $data->whereHas('employeeget', function ($query) use ($reco_employee_name) {
                $query->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
            });
        }
        if ($request['category'] != '') {
            $categoryid = $request['category'];
            $data = $data->whereHas('employeeget', function ($query) use ($categoryid) {
                $query->where('category', $categoryid);
            });
        }
        if ($request['designation'] != '') {
            $designation = $request['designation'];
            $data = $data->whereHas('employeeget', function ($query) use ($designation) {
                $query->where('designation_id', $designation);
            });
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $totalResults = $data->orderby('created_at', 'DESC')->count();
        //dd($totalResults);
        $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        if ($request['emp_export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;

                $val['COMPANY NAME'] = $row['company']->name;
                $application_type = '';
                if ($row->application_type == 1) {
                    $application_type = 'Register';
                } else {
                    $application_type = 'Resign';
                }
                $val['APPLICATION TYPE'] = $application_type;
                $val['DESIGANATION'] = getDesignationData('designation_name', $row['employeeget']->designation_id)->designation_name;
                $category = '';
                if ($row['employeeget']->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row['employeeget']->category == 2) {
                    $category = 'Contract';
                }
                $val['CATEGORY'] = $category;
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $val['RECOMMENDATION EMPLOYEE NAME'] = $row['employeeget']->recommendation_employee_name;
                $val['EMPLOYEE NAME'] = $row['employeeget']->employee_name;
                $val['DOB'] = date("d/m/Y", strtotime($row['employeeget']->dob));
                $gender = 'Other';
                if ($row['employeeget']->gender == 1) {
                    $gender = 'Male';
                }
                if ($row['employeeget']->gender == 2) {
                    $gender = 'Female';
                }
                $val['GENDER'] = $gender;
                $val['NUMBER'] = $row['employeeget']->mobile_no;
                $val['EMAIL ID'] = $row['employeeget']->email;
                $val['GUARDIAN NAME'] = $row['employeeget']->father_guardian_name;
                $val['GUARDIAN NUMBER'] = $row['employeeget']->father_guardian_number;
                $val['MOTHER NAME'] = $row['employeeget']->mother_name;
                $val['PEN CARD'] = $row['employeeget']->pen_card;
                $val['AADHAR CARD'] = $row['employeeget']->aadhar_card;
                $val['VOTER ID'] = $row['employeeget']->voter_id;
                $val['ESI Account No.'] = $row['employeeget']->esi_account_no;
                $val['UAN/PF  Account No.'] = $row['employeeget']->pf_account_no;
                $status = 'Pending';
                if ($row->status == 1) {
                    $status = 'Approved';
                }
                if ($row->status == 2) {
                    $status = 'Rejected';
                }
                $val['APPLICATION STATUS'] = $status;
                $val['CREATED'] = date("d/m/Y", strtotime($row->created_at));
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        }
        if ($request['emp_export'] == 0) {
            // echo 'hi';die;
            return Excel::download(new EmployeeApplicationBranchExport($data), 'branchEmployeeApplicationList.xlsx');
        }
    }
    public function employeeApplicationExportpdf(Request $request)
    {
        $empID = $request->id;
        $employee = \App\Models\Employee::where('id', $empID)->first();
        $qualification = \App\Models\EmployeeQualification::where('employee_id', $empID)->get();
        $diploma = \App\Models\EmployeeDiploma::where('employee_id', $empID)->get();
        $work = \App\Models\EmployeeExperience::where('employee_id', $empID)->get();
        $customerID = $employee->customer_id;
        $memberD = Member::where('id',$customerID)->select('id','member_id')->first();
        $pdf = PDF::loadView('templates.branch.hr_management.employee.pdf', compact('employee', 'qualification', 'diploma', 'work','memberD'))->setPaper('a4', 'landscape')->setWarnings(false);
        $pdf->save(storage_path() . '_filename.pdf');
        return $pdf->download('EmployeeApplication.pdf');
    }
    /******* Report  Management Start  ****************
use App\Exports\ReportAssociateBusinessBranchExport
use App\Exports\ReportAssociateBusinessSummaryBranchExport
use App\Exports\ReportAssociateBusinessCompareBranchExport
     */
    /**
     * Associate business Report .
     *
     * @return \Illuminate\Http\Response
     */    public function associateBusinessListExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['is_search'] = $request->is_search;
        $request['zone'] = $request->zone;
        $request['region'] = $request->region;
        $request['sector'] = $request->sector;
        $request['associate_code'] = $request->associate_code;
        $returnURL = URL::to('/') . "/asset/associate_business_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/associate_business_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if ($request['zone'] != '') {
            $zone = $request['zone'];
            $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                $query->where('branch.zone', $zone);
            });
        }
        if ($request['region'] != '') {
            $region = $request['region'];
            $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                $query->where('branch.regan', $region);
            });
        }
        if ($request['sector'] != '') {
            $sector = $request['sector'];
            $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                $query->where('branch.sector', $sector);
            });
        }
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', '=', $associate_code);
        }
        if ($request->branch_id != '') {
            $branch_id = $request->branch_id;
            $data = $data->where('associate_branch_id', $branch_id);
        } else {
            $branch_id = '';
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
        }
        $totalResults = $data->orderby('associate_join_date', 'ASC')->count();
        $results = $data->orderby('associate_join_date', 'ASC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        $PlanData = getPlanIDCustom();
        $planDaily = getPlanID('710')->id; //$PlanData['710'];
        $dailyId = array($planDaily);
        $planSSB = getPlanID('703')->id; //$PlanData['703'];
        $planKanyadhan = getPlanID('709')->id; //$PlanData['709'];
        $planMB = getPlanID('708')->id; //$PlanData['708'];
        $planFRD = getPlanID('707')->id; //$PlanData['707'];
        $planJeevan = getPlanID('713')->id; //$PlanData['713'];
        $planRD = getPlanID('704')->id; //$PlanData['704'];
        $planBhavhishya = getPlanID('718')->id; //$PlanData['718'];
        $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
        $planMI = getPlanID('712')->id; //$PlanData['712'];
        $planFFD = getPlanID('705')->id; //$PlanData['705'];
        $planFD = getPlanID('706')->id; //$PlanData['706'];
        $fdId = array($planMI, $planFFD, $planFD);
        $investNewAcCount = investNewAcCountCustom($startDate, $endDate, $planDaily, $branch_id);
        $investNewDenoSum = investNewDenoSumCustom($startDate, $endDate, $planDaily, $branch_id);
        if ($request['export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $associate_id = $row->id;
                $val['S/N'] = $sno;
                //$val['join_date']=date("d/m/Y", strtotime($row->associate_join_date));
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['CARDER'] = getCarderName($row->current_carder_id);
                $val['DAILY NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                // if(array_key_exists($associate_id.'_'.$planDaily, $investNewAcCount)){
                //              $val['DAILY NI NO-A/C'] = $investNewAcCount[$associate_id.'_'.$planDaily];
                //          }else{
                //              $val['DAILY NI NO-A/C'] = '0';
                //          }
                $val['DAILY NI TOTAL DEMO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                // if(array_key_exists($associate_id.'_'.$planDaily, $investNewDenoSum)){
                //     $val['DAILY NI TOTAL DEMO'] = $investNewDenoSum[$associate_id.'_'.$planDaily];
                // }else{
                //     $val['DAILY NI TOTAL DEMO'] = '0';
                // }
                $val['DAILT RENEW NO A/C'] = investRenewAc($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                $val['DAILT RENEW TOTAL AMT'] = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                $val['MONTHLY NI NO A/C'] = investNewAcCountType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['MONTHLY NI TOTAL DENO'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['MONTHLY RENEW NO-AC'] = investRenewAc($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['MONTHLY RENEW TOTAL AMT'] = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['FD NI-NO-A/C'] = investNewAcCountType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                $val['FD NI TOTAL DENO'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                /*  $val['fd_renew_ac']=investRenewAc($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $val['fd_renew']=investRenewAmountSum($associate_id,$startDate,$endDate,$fdId,$branch_id);*/
                $val['SSB NI-NO-A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB NI TOTAL DENO'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB DEPOSIT NO A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SSB DEPOSIT TOTAL AMT'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $sum_ni_ac = $val['DAILY NI NO-A/C'] + $val['MONTHLY NI TOTAL DENO'] + $val['FD NI-NO-A/C'] + $val['SSB NI-NO-A/C'];
                $sum_ni_amount = $val['DAILY NI TOTAL DEMO'] + $val['MONTHLY NI TOTAL DENO'] + $val['FD NI TOTAL DENO'] + $val['SSB NI TOTAL DENO'];
                //$val['SSB NI-NO-A/C']=$sum_ni_ac;
                //$val['SSB NI TOTAL DENO']=number_format((float)$sum_ni_amount, 2, '.', '');
                $sum_renew_ac = $val['DAILT RENEW NO A/C'] + $val['MONTHLY RENEW NO-AC'] + $val['SSB DEPOSIT NO A/C'];
                $sum_renew_amount = $val['DAILT RENEW TOTAL AMT'] + $val['MONTHLY RENEW TOTAL AMT'] + $val['SSB DEPOSIT TOTAL AMT'];
                //$val['SSB DEPOSIT NO A/C']=$sum_renew_ac;
                //$val['SSB DEPOSIT TOTAL AMT']=number_format((float)$sum_renew_amount, 2, '.', '');
                $val['OTHER MI'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                $val['OTHER STN'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                $ni_m = $val['DAILY NI TOTAL DEMO'] + $val['MONTHLY NI TOTAL DENO'] + $val['FD NI TOTAL DENO'];
                $tcc_m = $val['DAILY NI TOTAL DEMO'] + $val['MONTHLY NI TOTAL DENO'] + $val['FD NI TOTAL DENO'] + $val['DAILT RENEW TOTAL AMT'] + $val['MONTHLY RENEW TOTAL AMT'];
                $tcc = $val['DAILY NI TOTAL DEMO'] + $val['MONTHLY NI TOTAL DENO'] + $val['FD NI TOTAL DENO'] + $val['SSB NI TOTAL DENO'] + $val['DAILT RENEW TOTAL AMT'] + $val['MONTHLY RENEW TOTAL AMT'] + $val['SSB DEPOSIT TOTAL AMT'];
                $val['NCC_M'] = number_format((float)$ni_m, 2, '.', '');
                $val['NCC'] = number_format((float)$sum_ni_amount, 2, '.', '');
                $val['TCC_M'] = number_format((float)$tcc_m, 2, '.', '');
                $val['TCC'] = number_format((float)$tcc, 2, '.', '');
                $val['LOAN NO-A/C'] = totalLoanAc($associate_id, $startDate, $endDate, $branch_id);
                $val['LOAN TOTAL AMT'] = totalLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                $val['LOAN RECOVERY-NO A/C'] = totalRenewLoanAc($associate_id, $startDate, $endDate, $branch_id);
                $val['LOAN RECOVERY TOTAL AMT'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                $val['NEW ASSOCIATING JOINING NO'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                $val['TOTAL ASSOCIATING JOINING NO'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                $val['NEW MEMBER JOINING NO'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                $val['TOTAL MEMEBER JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['export'] == 1) {
            // echo 'hi';die;
            return Excel::download(new ReportAssociateBusinessBranchExport($data, $branch_id, $startDate, $endDate), 'BranchAssociateBusinessReport.xlsx');
        }
    }
    /**
     * Associate business summary Report .
     *
     * @return \Illuminate\Http\Response
     */
    public function associateBusinessSummaryExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/associative_business_summary_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/associative_business_summary_list.csv";
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['is_search'] = $request->is_search;
        $request['zone'] = $request->zone;
        $request['region'] = $request->region;
        $request['sector'] = $request->sector;
        $request['associate_code'] = $request->associate_code;
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if ($request['zone'] != '') {
            $zone = $request['zone'];
            $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                $query->where('branch.zone', $zone);
            });
        }
        if ($request['region'] != '') {
            $region = $request['region'];
            $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                $query->where('branch.regan', $region);
            });
        }
        if ($request['sector'] != '') {
            $sector = $request['sector'];
            $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                $query->where('branch.sector', $sector);
            });
        }
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', '=', $associate_code);
        }
        if ($request->branch_id != '') {
            $branch_id = $request->branch_id;
            $data = $data->where('associate_branch_id', $branch_id);
        } else {
            $branch_id = '';
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->where(function ($query) use ($name) {
                $query->where('member.first_name', 'LIKE', '%' . $name . '%')->orWhere('member.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(member.first_name," ",member.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
        }
        $totalResults = $data->orderby('associate_join_date', 'ASC')->count();
        $results = $data->orderby('associate_join_date', 'ASC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        if ($request['export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $associate_id = $row->id;
                $planDaily = getPlanID('710')->id;
                $planSSB = getPlanID('703')->id;
                $planKanyadhan = getPlanID('709')->id;
                $planMB = getPlanID('708')->id;
                $planFFD = getPlanID('705')->id;
                $planFRD = getPlanID('707')->id;
                $planJeevan = getPlanID('713')->id;
                $planMI = getPlanID('712')->id;
                $planFD = getPlanID('706')->id;
                $planRD = getPlanID('704')->id;
                $planBhavhishya = getPlanID('718')->id;
                $planids = array($planDaily, $planSSB, $planKanyadhan, $planMB, $planFFD, $planFRD, $planJeevan, $planMI, $planFD, $planRD, $planBhavhishya,);
                $val['S/N'] = $sno;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCITE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['CARDER'] = getCarderName($row->current_carder_id);
                $val['DAILY NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['DAILY NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['DAILY RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['DAILY RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['RD NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['FRD NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FD NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['FD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['FFD NI-NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['FFD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['SAMRADDH KANYADHAN NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH KANYADHAN RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH KANYADHAN RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH BHavhishya NI-NO.A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH BHavhishya NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH BHavhishya RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH BHavhishya RENEW TOTAL-DENO'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH JEEVAN NI-NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SAMRADDH JEEVAN NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SAMRADDH JEEVAN RENEW-NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);;
                $val['SAMRADDH JEEVAN RENEW TOTAL-DENO'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SSB NI NO-A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB NI TOTAL-DENO'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB DEPOSIT NO-A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SSB DEPOSIT TOTAL-AMT'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                // $val['ffd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);
                //$val['ffd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);
                $val['MIS NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MIS NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MIS RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);;
                $val['MIS RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MB NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB RENEW TOTAL-DENO'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                // $val['fd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);
                //$val['fd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);
                $sum_ni_ac = $val['DAILY NI NO-A/C'] + $val['SSB NI NO-A/C'] + $val['SAMRADDH KANYADHAN NI NO-A/C'] + $val['MB NI NO-A/C'] + $val['FFD NI-NO-A/C'] + $val['FRD NI NO-A/C'] + $val['SAMRADDH JEEVAN NI-NO-A/C'] + $val['MIS NI NO-A/C'] + $val['FD NI NO-A/C'] + $val['RD NI NO-A/C'] + $val['SAMRADDH BHavhishya NI-NO.A/C'];
                $sum_ni_amount = $val['DAILY NI TOTAL-DENO'] + $val['SSB NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'];
                $sum_renew_ac = investRenewAc($associate_id, $startDate, $endDate, $planids, $branch_id);
                $sum_renew_amount = investRenewAmountSum($associate_id, $startDate, $planids, $planids, $branch_id);
                //$val['MB NI NO-A/C']=$sum_ni_ac;
                //$val['MB NI TOTAL-DENO']=number_format((float)$sum_ni_amount, 2, '.', '');
                // $val['MB RENEW NO-A/C']=$sum_renew_ac;
                // $val['MB RENEW TOTAL-AMT']=number_format((float)$sum_renew_amount, 2, '.', '');
                $val['OTHER MI'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                $val['OTHER STN'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                $ni_m = $val['DAILY NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'];
                $tcc_m = $val['DAILY NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya RENEW TOTAL-DENO'] + $val['RD RENEW TOTAL-AMT'] + $val['MIS RENEW TOTAL-AMT'] + $val['SAMRADDH JEEVAN RENEW TOTAL-DENO'] + $val['FRD RENEW TOTAL-AMT'] + $val['MB RENEW TOTAL-DENO'] + $val['SAMRADDH KANYADHAN RENEW TOTAL-AMT'] + $val['DAILY RENEW TOTAL-AMT'];
                $tcc = $val['DAILY NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'] + $val['SSB NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya RENEW TOTAL-DENO'] + $val['RD RENEW TOTAL-AMT'] + $val['MIS RENEW TOTAL-AMT'] + $val['SAMRADDH JEEVAN RENEW TOTAL-DENO'] + $val['FRD RENEW TOTAL-AMT'] + $val['MB RENEW TOTAL-DENO'] + $val['SAMRADDH KANYADHAN RENEW TOTAL-AMT'] + $val['SSB DEPOSIT TOTAL-AMT'] + $val['DAILY RENEW TOTAL-AMT'];
                $val['NCC_M'] = number_format((float)$ni_m, 2, '.', '');
                $val['NCC'] = number_format((float)$sum_ni_amount, 2, '.', '');
                $val['TCC_M'] = number_format((float)$tcc_m, 2, '.', '');
                $val['TCC'] = number_format((float)$tcc, 2, '.', '');
                $val['STAFF LOAN NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['STAFF LOAN TOTAL-AMT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['PL LOAN NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['PL LOAN TOTAL-AMT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['LOAN AGAINST INVESTMENT NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['LOAN AGAINST INVESTMENT TOTAL-AMT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['GROUP LOAN NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['GROUP LOAN TOTAL-AMOUNT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['TOTAL LOAN NO-A/C'] = $val['STAFF LOAN NO-A/C'] + $val['PL LOAN NO-A/C'] + $val['LOAN AGAINST INVESTMENT NO-A/C'] + $val['GROUP LOAN NO-A/C'];
                $val['TOTAL LOAN TOTAL-AMT'] = $val['STAFF LOAN TOTAL-AMT'] + $val['PL LOAN TOTAL-AMT'] + $val['LOAN AGAINST INVESTMENT TOTAL-AMT'] + $val['GROUP LOAN TOTAL-AMOUNT'];
                $val['STAFF LOAN EMI NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SATFF LOAN EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['PL LOAN EMI NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['PL LOAN EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['LOAN AGAINST INVESTMENT NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['LOAN AGAINST INVESTMENT EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['GROUP LOAN EMI NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['GROUP LOAN EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['TOTAL LOAN EMI NO-A/C'] = $val['STAFF LOAN EMI NO-A/C'] + $val['PL LOAN EMI NO-A/C'] + $val['LOAN AGAINST INVESTMENT NO-A/C'] + $val['GROUP LOAN EMI NO-A/C'];
                $val['TOTAL LOAN EMI TOTAL-AMT'] = $val['SATFF LOAN EMI TOTAL-AMT'] + $val['PL LOAN EMI TOTAL-AMT'] + $val['LOAN AGAINST INVESTMENT EMI TOTAL-AMT'] + $val['GROUP LOAN EMI TOTAL-AMT'];
                $val['NEW ASSOCIATE JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                $val['TOTAL ASSOCIATE JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                $val['NEW MEMBER JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                $val['total member joining no'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['export'] == 1) {
            // echo 'hi';die;
            return Excel::download(new ReportAssociateBusinessSummaryBranchExport($data, $branch_id, $startDate, $endDate), 'BranchAssociateBusinessSummaryReport.xlsx');
        }
    }
    public function associateBusinessSummaryExportBranchWise(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/branch_associative_business_summary_list_brach_wise.csv";
        $fileName = env('APP_EXPORTURL')."asset/branch_associative_business_summary_list_brach_wise.csv";
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['is_search'] = $request->is_search;
        $request['zone'] = $request->zone;
        $request['region'] = $request->region;
        $request['sector'] = $request->sector;
        $request['associate_code'] = $request->associate_code;
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if ($request['zone'] != '') {
            $zone = $request['zone'];
            $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                $query->where('branch.zone', $zone);
            });
        }
        if ($request['region'] != '') {
            $region = $request['region'];
            $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                $query->where('branch.regan', $region);
            });
        }
        if ($request['sector'] != '') {
            $sector = $request['sector'];
            $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                $query->where('branch.sector', $sector);
            });
        }
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', '=', $associate_code);
        }
        if ($request->branch_id != '') {
            $branch_id = $request->branch_id;
            $data = $data->where('associate_branch_id', $branch_id);
        } else {
            $branch_id = '';
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->where(function ($query) use ($name) {
                $query->where('member.first_name', 'LIKE', '%' . $name . '%')->orWhere('member.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(member.first_name," ",member.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
        }
        $totalResults = $data->orderby('associate_join_date', 'ASC')->count();
        $results = $data->orderby('associate_join_date', 'ASC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        if ($request['export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $associate_id = $row->id;
                $planDaily = getPlanID('710')->id;
                $planSSB = getPlanID('703')->id;
                $planKanyadhan = getPlanID('709')->id;
                $planMB = getPlanID('708')->id;
                $planFFD = getPlanID('705')->id;
                $planFRD = getPlanID('707')->id;
                $planJeevan = getPlanID('713')->id;
                $planMI = getPlanID('712')->id;
                $planFD = getPlanID('706')->id;
                $planRD = getPlanID('704')->id;
                $planBhavhishya = getPlanID('718')->id;
                $planids = array($planDaily, $planSSB, $planKanyadhan, $planMB, $planFFD, $planFRD, $planJeevan, $planMI, $planFD, $planRD, $planBhavhishya,);
                $val['S/N'] = $sno;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCITE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['CARDER'] = getCarderName($row->current_carder_id);
                $val['DAILY NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['DAILY NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['DAILY RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['DAILY RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['RD NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['FRD NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FD NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['FD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['FFD NI-NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['FFD NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['SAMRADDH KANYADHAN NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH KANYADHAN RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH KANYADHAN RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['SAMRADDH BHavhishya NI-NO.A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH BHavhishya NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH BHavhishya RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH BHavhishya RENEW TOTAL-DENO'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['SAMRADDH JEEVAN NI-NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SAMRADDH JEEVAN NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SAMRADDH JEEVAN RENEW-NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);;
                $val['SAMRADDH JEEVAN RENEW TOTAL-DENO'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SSB NI NO-A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB NI TOTAL-DENO'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB DEPOSIT NO-A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SSB DEPOSIT TOTAL-AMT'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                // $val['ffd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);
                //$val['ffd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFFD,$branch_id);
                $val['MIS NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MIS NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MIS RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);;
                $val['MIS RENEW TOTAL-AMT'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MB NI NO-A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB NI TOTAL-DENO'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB RENEW NO-A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB RENEW TOTAL-DENO'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                // $val['fd_renew_ac']=investRenewAcPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);
                //$val['fd_renew']=investRenewAmountSumPlan($associate_id,$startDate,$endDate,$planFD,$branch_id);
                $sum_ni_ac = $val['DAILY NI NO-A/C'] + $val['SSB NI NO-A/C'] + $val['SAMRADDH KANYADHAN NI NO-A/C'] + $val['MB NI NO-A/C'] + $val['FFD NI-NO-A/C'] + $val['FRD NI NO-A/C'] + $val['SAMRADDH JEEVAN NI-NO-A/C'] + $val['MIS NI NO-A/C'] + $val['FD NI NO-A/C'] + $val['RD NI NO-A/C'] + $val['SAMRADDH BHavhishya NI-NO.A/C'];
                $sum_ni_amount = $val['DAILY NI TOTAL-DENO'] + $val['SSB NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'];
                $sum_renew_ac = investRenewAc($associate_id, $startDate, $endDate, $planids, $branch_id);
                $sum_renew_amount = investRenewAmountSum($associate_id, $startDate, $planids, $planids, $branch_id);
                //$val['MB NI NO-A/C']=$sum_ni_ac;
                //$val['MB NI TOTAL-DENO']=number_format((float)$sum_ni_amount, 2, '.', '');
                // $val['MB RENEW NO-A/C']=$sum_renew_ac;
                // $val['MB RENEW TOTAL-AMT']=number_format((float)$sum_renew_amount, 2, '.', '');
                $val['OTHER MI'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                $val['OTHER STN'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                $ni_m = $val['DAILY NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'];
                $tcc_m = $val['DAILY NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya RENEW TOTAL-DENO'] + $val['RD RENEW TOTAL-AMT'] + $val['MIS RENEW TOTAL-AMT'] + $val['SAMRADDH JEEVAN RENEW TOTAL-DENO'] + $val['FRD RENEW TOTAL-AMT'] + $val['MB RENEW TOTAL-DENO'] + $val['SAMRADDH KANYADHAN RENEW TOTAL-AMT'] + $val['DAILY RENEW TOTAL-AMT'];
                $tcc = $val['DAILY NI TOTAL-DENO'] + $val['SAMRADDH KANYADHAN NI TOTAL-DENO'] + $val['MB NI TOTAL-DENO'] + $val['FFD NI TOTAL-DENO'] + $val['FRD NI TOTAL-DENO'] + $val['SAMRADDH JEEVAN NI TOTAL-DENO'] + $val['MIS NI TOTAL-DENO'] + $val['FD NI TOTAL-DENO'] + $val['RD NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya NI TOTAL-DENO'] + $val['SSB NI TOTAL-DENO'] + $val['SAMRADDH BHavhishya RENEW TOTAL-DENO'] + $val['RD RENEW TOTAL-AMT'] + $val['MIS RENEW TOTAL-AMT'] + $val['SAMRADDH JEEVAN RENEW TOTAL-DENO'] + $val['FRD RENEW TOTAL-AMT'] + $val['MB RENEW TOTAL-DENO'] + $val['SAMRADDH KANYADHAN RENEW TOTAL-AMT'] + $val['SSB DEPOSIT TOTAL-AMT'] + $val['DAILY RENEW TOTAL-AMT'];
                $val['NCC_M'] = number_format((float)$ni_m, 2, '.', '');
                $val['NCC'] = number_format((float)$sum_ni_amount, 2, '.', '');
                $val['TCC_M'] = number_format((float)$tcc_m, 2, '.', '');
                $val['TCC'] = number_format((float)$tcc, 2, '.', '');
                $val['STAFF LOAN NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['STAFF LOAN TOTAL-AMT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['PL LOAN NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['PL LOAN TOTAL-AMT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['LOAN AGAINST INVESTMENT NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['LOAN AGAINST INVESTMENT TOTAL-AMT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['GROUP LOAN NO-A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['GROUP LOAN TOTAL-AMOUNT'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['TOTAL LOAN NO-A/C'] = $val['STAFF LOAN NO-A/C'] + $val['PL LOAN NO-A/C'] + $val['LOAN AGAINST INVESTMENT NO-A/C'] + $val['GROUP LOAN NO-A/C'];
                $val['TOTAL LOAN TOTAL-AMT'] = $val['STAFF LOAN TOTAL-AMT'] + $val['PL LOAN TOTAL-AMT'] + $val['LOAN AGAINST INVESTMENT TOTAL-AMT'] + $val['GROUP LOAN TOTAL-AMOUNT'];
                $val['STAFF LOAN EMI NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SATFF LOAN EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['PL LOAN EMI NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['PL LOAN EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['LOAN AGAINST INVESTMENT NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['LOAN AGAINST INVESTMENT EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['GROUP LOAN EMI NO-A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['GROUP LOAN EMI TOTAL-AMT'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['TOTAL LOAN EMI NO-A/C'] = $val['STAFF LOAN EMI NO-A/C'] + $val['PL LOAN EMI NO-A/C'] + $val['LOAN AGAINST INVESTMENT NO-A/C'] + $val['GROUP LOAN EMI NO-A/C'];
                $val['TOTAL LOAN EMI TOTAL-AMT'] = $val['SATFF LOAN EMI TOTAL-AMT'] + $val['PL LOAN EMI TOTAL-AMT'] + $val['LOAN AGAINST INVESTMENT EMI TOTAL-AMT'] + $val['GROUP LOAN EMI TOTAL-AMT'];
                $val['NEW ASSOCIATE JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                $val['TOTAL ASSOCIATE JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                $val['NEW MEMBER JOINING NUMBER'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                $val['total member joining no'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['export'] == 1) {
            // echo 'hi';die;
            return Excel::download(new ReportAssociateBusinessSummaryBranchExport($data, $branch_id, $startDate, $endDate), 'BranchAssociateBusinessSummaryReport.xlsx');
        }
    }
    /**
     * Associate business compare Report .
     *
     * @return \Illuminate\Http\Response
     */
    public function associateBusinessCompareExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/associate_business_compare_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/associate_business_compare_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['current_start_date'] = $request->current_start_date;
        $request['current_end_date'] = $request->current_end_date;
        $request['comp_start_date'] = $request->comp_start_date;
        $request['comp_end_date'] = $request->comp_end_date;
        $request['branch_id'] = $request->branch_id;
        $request['is_search'] = $request->is_search;
        $request['zone'] = $request->zone;
        $request['region'] = $request->region;
        $request['sector'] = $request->sector;
        $request['associate_code'] = $request->associate_code;
        if ($request['current_start_date'] != '') {
            $current_start_date = date("Y-m-d", strtotime(convertDate($request['current_start_date'])));
        } else {
            $current_start_date = '';
        }
        if ($request['current_end_date'] != '') {
            $current_end_date = date("Y-m-d ", strtotime(convertDate($request['current_end_date'])));
        } else {
            $current_end_date = '';
        }
        if ($request['comp_start_date'] != '') {
            $comp_start_date = date("Y-m-d", strtotime(convertDate($request['comp_start_date'])));
        } else {
            $comp_start_date = '';
        }
        if ($request['comp_end_date'] != '') {
            $comp_end_date = date("Y-m-d ", strtotime(convertDate($request['comp_end_date'])));
        } else {
            $comp_end_date = '';
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
        } else {
            $branch_id = '';
        }
        $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('associate_branch_id', '=', $id);
        }
        if ($request['zone'] != '') {
            $zone = $request['zone'];
            $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                $query->where('branch.zone', $zone);
            });
        }
        if ($request['region'] != '') {
            $region = $request['region'];
            $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                $query->where('branch.regan', $region);
            });
        }
        if ($request['sector'] != '') {
            $sector = $request['sector'];
            $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                $query->where('branch.sector', $sector);
            });
        }
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', '=', $associate_code);
        }
        if ($request['comp_start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['comp_start_date'])));
            if ($request['comp_end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['comp_end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
        }
        $totalResults = $data->orderby('associate_join_date', 'ASC')->count();
        //dd($totalResults);
        $results = $data->orderby('associate_join_date', 'ASC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        if ($request['export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $associate_id = $row->id;
                $planDaily = getPlanID('710')->id;
                $dailyId = array($planDaily);
                $planSSB = getPlanID('703')->id;
                $planKanyadhan = getPlanID('709')->id;
                $planMB = getPlanID('708')->id;
                $planFRD = getPlanID('707')->id;
                $planJeevan = getPlanID('713')->id;
                $planRD = getPlanID('704')->id;
                $planBhavhishya = getPlanID('718')->id;
                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                $planMI = getPlanID('712')->id;
                $planFFD = getPlanID('705')->id;
                $planFD = getPlanID('706')->id;
                $fdId = array($planMI, $planFFD, $planFD);
                $val['S/N'] = $sno;
                //$val['join_date']=date("d/m/Y", strtotime($row->associate_join_date));
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['CARDER'] = getCarderName($row->current_carder_id);
                $val['Current Daily N.I. - No. A/C'] = investNewAcCount($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                $val['Current Daily N.I. - Total Deno'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                $val['Current Daily Renew - No. A/C'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                $val['Current Daily Renew - total-amt'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                $val['Current Monthly N.I. - No. A/C'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['Current Monthly N.I. - Total Deno'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['Current Monthly Renew - No. A/C'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['Current Monthly Renew - Total Amt'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['Current FD N.I. - No. A/C'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                $val['Current FD N.I. - Total Deno'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                /*$val['current_fd_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
                $val['current_fd_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);*/
                $val['Current SSB N.I. - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                $val['Current SSB N.I. - Total Deno'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                //$val['current_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
                //$val['current_ssb_renew']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
                $current_sum_ni_ac = $val['Current Daily N.I. - No. A/C'] + $val['Current Monthly N.I. - No. A/C'] + $val['Current FD N.I. - No. A/C'] + $val['Current SSB N.I. - No. A/C'];
                $current_sum_ni_amount = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'] + $val['Current SSB N.I. - Total Deno'];
                $val['CURRENT SSB NI-NO A/C'] = $current_sum_ni_ac;
                $val['CURRENT SSB NI-TOTAL DENO'] = number_format((float)$current_sum_ni_amount, 2, '.', '');
                $current_sum_renew_ac = $val['Current Daily Renew - No. A/C'] + $val['Current Monthly Renew - No. A/C'];
                $current_sum_renew_amount = $val['Current Daily Renew - total-amt'] + $val['Current Monthly Renew - Total Amt'];
                $val['CURRENT SSB DEPOSIT NO-A/C'] = $current_sum_renew_ac;
                $val['CURRENT SSB DEPOSIT TOTAL-AMT'] = number_format((float)$current_sum_renew_amount, 2, '.', '');
                $val['CURRENT OTHER MI'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 11);
                $val['CURRENT OTHER STN'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 12);
                $current_ni_m = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'];
                $current_tcc_m = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'] + $val['Current Daily Renew - total-amt'] + $val['Current Monthly Renew - Total Amt'];
                $current_tcc = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'] + $val['Current SSB N.I. - Total Deno'] + $val['Current Daily Renew - total-amt'] + $val['Current Monthly Renew - Total Amt'];
                $val['CURRENT_NCC_M'] = number_format((float)$current_ni_m, 2, '.', '');
                $val['CURRENT NCC'] = number_format((float)$current_sum_ni_amount, 2, '.', '');
                $val['CURRENT_TCC_M'] = number_format((float)$current_tcc_m, 2, '.', '');
                $val['CURRENT TCC'] = number_format((float)$current_tcc, 2, '.', '');
                $val['CURRENT LOAN NO-A/C'] = totalLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['CURRENT LOAN TOTAL-AMT'] = totalLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['CURRENT LOAN RECOVERY NO-A/C'] = totalRenewLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['CURRENT LOAN RECOVERY TOTAL-AMT'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['CURRENT NEW ASSOCIATE JOINING NO'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 0);
                $val['CURRENT ASSOCIATE TOTAL JOINING NO'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 1);
                $val['CURRENT NEW MEMBER JOINING NO'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 0);
                $val['CURRENT TOTAL MEMBER JOINING NO'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 1);
                $val['COMPARE DAILY NI NO-A/C'] = investNewAcCount($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                $val['COMPARE DAILY NI TOTAL-DENO'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                $val['COMPARE DAILY RENEW NO-A/C'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                $val['COMPARE DAILY RENEW TOTAL-AMT'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                $val['COMPARE MONTHLY NI NO-A/C'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['COMPARE MONTHLY NI TOTAL-DENO'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['COMPARE MONTHLY RENEW NO-A/C'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['CURRENT MONTHLY RENEW TOTAL-AMT'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['COMPARE FD NI NO-A/C'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                $val['COMAPRE FD NI TOTAL-DENO'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                /* $val['compare_fd_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
                $val['compare_fd_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);*/
                $val['COMPARE RENT SSB NI-NO A/C'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1);
                $val['COMPARE SSB NI TOTAL-DENO'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                //$val['compare_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
                //$val['compare_ssb_renew']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
                $compare_sum_ni_ac = $val['COMPARE DAILY NI NO-A/C'] + $val['COMPARE MONTHLY NI NO-A/C'] + $val['COMPARE FD NI NO-A/C'] + $val['COMPARE RENT SSB NI-NO A/C'];
                $compare_sum_ni_amount = $val['COMPARE DAILY NI TOTAL-DENO'] + $val['COMPARE MONTHLY NI TOTAL-DENO'] + $val['COMAPRE FD NI TOTAL-DENO'] + $val['COMPARE SSB NI TOTAL-DENO'];
                $val['COMPARE RENT SSB NI NO-A/C'] = $compare_sum_ni_ac;
                $val['COMPARE SSB NI TOTAL-DENO'] = number_format((float)$compare_sum_ni_amount, 2, '.', '');
                $compare_sum_renew_ac = $val['COMPARE DAILY RENEW NO-A/C'] + $val['COMPARE MONTHLY RENEW NO-A/C'];
                $compare_sum_renew_amount = $val['COMPARE DAILY RENEW TOTAL-AMT'] + $val['CURRENT MONTHLY RENEW TOTAL-AMT'];
                $val['COMPARE SSB DEPOSIT NO-A/C'] = $compare_sum_renew_ac;
                $val['COMPARE SSB DEPOSIT TOTAL-AMT'] = number_format((float)$compare_sum_renew_amount, 2, '.', '');
                $val['COMPARE OTHER MI'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 11);
                $val['COMAPRE OTHER STN'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 12);
                $compare_ni_m = $val['COMPARE DAILY NI TOTAL-DENO'] + $val['COMPARE MONTHLY NI TOTAL-DENO'] + $val['COMAPRE FD NI TOTAL-DENO'];
                $compare_tcc_m = $val['COMPARE DAILY NI TOTAL-DENO'] + $val['COMPARE MONTHLY NI TOTAL-DENO'] + $val['COMAPRE FD NI TOTAL-DENO'] + $val['COMPARE DAILY RENEW TOTAL-AMT'] + $val['CURRENT MONTHLY RENEW TOTAL-AMT'];
                $compare_tcc = $val['COMPARE DAILY NI TOTAL-DENO'] + $val['COMPARE MONTHLY NI TOTAL-DENO'] + $val['COMAPRE FD NI TOTAL-DENO'] + $val['COMPARE SSB NI TOTAL-DENO'] + $val['COMPARE DAILY RENEW TOTAL-AMT'] + $val['CURRENT MONTHLY RENEW TOTAL-AMT'];
                $val['COMPARE NCC_M'] = number_format((float)$compare_ni_m, 2, '.', '');
                $val['COMPARE NCC'] = number_format((float)$compare_sum_ni_amount, 2, '.', '');
                $val['COMPARE TCC_M'] = number_format((float)$compare_tcc_m, 2, '.', '');
                $val['COMPARE TCC'] = number_format((float)$compare_tcc, 2, '.', '');
                $val['COMPARE LOAN NO-A/C'] = totalLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['COMPARE LOAN TOTAL-AMT'] = totalLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['COMAPRE LOAN RECOVERY NO-A/C'] = totalRenewLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['COMPARE LOAN RECOVERY TOTAL-AMT'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['COMPARE NEW ASSOCIATE JOINING NO'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 0);
                $val['COMPATE TOTAL ASSOCIATE JOINING NUMBER'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 1);
                $val['COMPARE NEW MEMBER JOINING NO'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 0);
                $val['COMPARE TOTAL MEMBER JOINING NO'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 1);
                $val['RESULT DAILY NI NO-A/C'] = $val['Current Daily N.I. - No. A/C'] - $val['COMPARE DAILY NI NO-A/C'];
                $val['RESULT DAILY NI TOTAL-DENO'] = $val['Current Daily N.I. - Total Deno'] - $val['COMPARE DAILY NI TOTAL-DENO'];
                $val['RESULT DAILY RENEW NO A/C'] = $val['Current Daily Renew - No. A/C'] - $val['COMPARE DAILY RENEW NO-A/C'];
                $val['RESULT DAILY RENEW TOTAL-AMT'] = $val['Current Daily Renew - No. A/C'] - $val['COMPARE DAILY RENEW TOTAL-AMT'];
                $val['RESULT MONHTLY NI NO-A/C'] = $val['Current Monthly N.I. - No. A/C'] - $val['COMPARE MONTHLY NI NO-A/C'];
                $val['RESULT MONHTLY NI TOTAL-DENO'] = $val['Current Monthly N.I. - Total Deno'] - $val['COMPARE MONTHLY NI TOTAL-DENO'];
                $val['RESULT MONTHLY RENEW NO A/C'] = $val['Current Monthly Renew - No. A/C'] - $val['COMPARE MONTHLY RENEW NO-A/C'];
                $val['RESULT MONTHLY RENEW TOTAL-AMT'] = $val['Current Monthly Renew - Total Amt'] - $val['CURRENT MONTHLY RENEW TOTAL-AMT'];
                $val['RESULT FD NI NO-A/C'] = $val['Current FD N.I. - No. A/C'] - $val['COMPARE FD NI NO-A/C'];
                $val['RESULT FD NI TOTAL-DENO'] = $val['Current FD N.I. - Total Deno'] - $val['COMAPRE FD NI TOTAL-DENO'];
                /*$val['result_fd_renew_ac']=$val['current_fd_renew_ac']-$val['compare_fd_renew_ac'];
                $val['result_fd_renew']=$val['current_fd_renew']-$val['compare_fd_renew'];*/
                //$val['result_ssb_new_ac']=$val['Current SSB N.I. - No. A/C']-$val['COMPARE RENT SSB NI-NO A/C'];
                // $val['result_ssb_deno_sum']=$val['Current SSB N.I. - Total Deno']-$val['COMPARE SSB NI TOTAL-DENO'];
                //$val['result_ssb_renew_ac']=$val['current_ssb_renew_ac']-$val['compare_ssb_renew'];
                //$val['result_ssb_renew']=$val['current_ssb_renew']-$val['COMPARE SSB NI TOTAL-DENO'];
                $result_sum_ni_ac = $current_sum_ni_ac - $compare_sum_ni_ac;
                $result_sum_ni_amount = $current_sum_ni_amount - $compare_sum_ni_amount;
                $val['RESULT RENT SSB NI-NO A/C'] = $result_sum_ni_ac;
                $val['RESULT SSB NI TOTAL-DENO'] = number_format((float)$result_sum_ni_amount, 2, '.', '');
                $result_sum_renew_ac = $current_sum_renew_ac - $compare_sum_renew_ac;
                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                $val['RESULT SSB DEPOSIT NO-A/C'] = $result_sum_renew_ac;
                $val['RESULT SSB DEPOSIT TOTAL-AMT'] = number_format((float)$result_sum_renew_amount, 2, '.', '');
                $val['RESULT OTHER MI'] = $val['CURRENT OTHER MI'] - $val['COMPARE OTHER MI'];
                $val['RESULT OTHER STN'] = $val['CURRENT OTHER STN'] - $val['COMAPRE OTHER STN'];
                $val['RESULT_NCC_M'] = $val['CURRENT_NCC_M'] - $val['COMPARE NCC_M'];
                $val['RESULT NCC'] = $val['CURRENT NCC'] - $val['COMPARE NCC'];
                $val['RESULT TCC_M'] = $val['CURRENT_TCC_M'] - $val['COMPARE TCC_M'];
                $val['RESULT TCC'] = $val['CURRENT TCC'] - $val['COMPARE TCC'];
                $val['RESULT LOAN NO-A/C'] = $val['CURRENT LOAN NO-A/C'] - $val['COMPARE LOAN NO-A/C'];
                $val['RESULT LOAN TOTAL AMT'] = $val['CURRENT LOAN TOTAL-AMT'] - $val['COMPARE LOAN TOTAL-AMT'];
                $val['RESULT LOAN RECOVERY-NO A/C '] = $val['CURRENT LOAN RECOVERY NO-A/C'] - $val['COMAPRE LOAN RECOVERY NO-A/C'];
                $val['RESULT LOAN RECOVERY TOTAL AMOUNT'] = $val['CURRENT LOAN RECOVERY TOTAL-AMT'] - $val['COMPARE LOAN RECOVERY TOTAL-AMT'];
                $val['RESULT NEW ASSOCIATE JOINING NO'] = $val['CURRENT NEW ASSOCIATE JOINING NO'] - $val['COMPARE NEW ASSOCIATE JOINING NO'];
                $val['RESULT TOTAL ASSOCIATE JOINING NO'] = $val['CURRENT ASSOCIATE TOTAL JOINING NO'] - $val['COMPATE TOTAL ASSOCIATE JOINING NUMBER'];
                $val['RESULT NEW MEMBER JOINING NO'] = $val['CURRENT NEW MEMBER JOINING NO'] - $val['COMPARE NEW MEMBER JOINING NO'];
                $val['RESULT TOTAL MEMBER JOINING NO'] = $val['CURRENT TOTAL MEMBER JOINING NO'] - $val['COMPARE TOTAL MEMBER JOINING NO'];
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['export'] == 1) {
            // echo 'hi';die;
            return Excel::download(new ReportAssociateBusinessCompareBranchExport($data, $branch_id, $current_start_date, $current_end_date, $comp_start_date, $comp_end_date), 'BranchAssociateBusinessCompareReport.xlsx');
        }
    }
    /******* Report  Management End   *****************/
    /**
     * Export associate commission Detail listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */    public function exportAssociateCommissionDetailLoan(Request $request)
    {
        if ($request['commission_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/branch_associate_commision_loan.csv";
            $fileName = env('APP_EXPORTURL')."asset/branch_associate_commision_loan.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $startDate = '';
        $endDate = '';
        $data = \App\Models\AssociateMonthlyCommission::
        with('loan.loan:id,name')
        ->with('loan:id,account_number,loan_type')
        ->with('group_loan.loan:id,name')
        ->with('group_loan:id,account_number,loan_type')
        ->where('assocaite_id', $request->id)->whereIn('type', array(2, 3))->where('is_deleted', '0');
        // }
        $member = Member::where('id', $request->id)->first();

        if ($request->year != '') {
            $year = $request->year;
            $data = $data->where('commission_for_year', $year);
        }
        if ($request->month != '') {
            $month = $request->month;
            $data = $data->where('commission_for_month', $month);
        }

        if (isset($request['plan_id']) && $request['plan_id'] != '') {
            $meid = $request['plan_id'];
            $data = $data->whereHas('investment', function ($query) use ($meid) {
                $query->where('member_investments.plan_id', $meid);
            });
        }
        if ($request->commission_export == 0) {
            $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get();
            $totalResults = $data->orderby('id', 'DESC')->count();
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
            foreach ($results as $val) {
                $sno++;
                $row['S/N'] = $sno;
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
                $row['MONTH'] = $monthAbbreviations[$val->commission_for_month] . '-' . $val->commission_for_year;

                if($val['type']==2){
                    $row['ACCOUNT NUMBER'] = $val['loan']->account_number;
                    $row['PLAN NAME'] =  $val['loan']['loan']->name;
                }
                else
                {
                    $row['ACCOUNT NUMBER']  = $val['group_loan']->account_number;
                    $row['PLAN NAME']=  $val['group_loan']['loan']->name;
                }


                $row['TOTAL AMOUNT'] = number_format((float) $val->total_amount, 2, '.', '');
                $row['QUALIFYING amonut '] = number_format((float) $val->qualifying_amount, 2, '.', '');
                $row['COMMISION AMOUNT'] = number_format((float) $val->commission_amount, 2, '.', '');
                $row['PERCENTAGE'] = number_format((float) $val->percentage, 2, '.', '');
                $row['CARDR FROM'] = $val->cadre_from;
                $row['CARDER TO'] = $val->cadre_to;

                if (!$headerDisplayed) {
                    // Use the keys from $data as the titles
                    fputcsv($handle, array_keys($row));
                    $headerDisplayed = true;
                }
                // Put the data into the stream
                fputcsv($handle, $row);
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
        } elseif ($request['commission_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.associate_management.exportcommission_detail_loan', compact('data', 'member'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('branch_ssociatecommission_detail_loan.pdf');
        }
    }
    /**
     * Export associate commission Detail listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function loanCommissionExport(Request $request)
    {
        $data = \App\Models\AssociateCommission::where('type_id', $request['id'])->whereIn('type', array(4, 6))->where('status', 1);
        $loan = \App\Models\Memberloans::where('id', $request['id'])->first();
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        }
        $data = $data->orderby('id', 'DESC')->get();
        if ($request['commission_export'] == 0) {
            return Excel::download(new BranchLoanCommissionExport($data, $loan), 'branch_loan_commission.xlsx');
        } elseif ($request['commission_export'] == 1) {
            $pdf = PDF::loadView('templates.branch.loan_management.exportcommission_detail_loan', compact('data', 'loan'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('branch_loan_commission.pdf');
        }
    }
    /**
     * Export associate commission Detail listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function loanGroupCommissionExport(Request $request)
    {
        $data = \App\Models\AssociateCommission::where('type_id', $request['id'])->whereIn('type', array(7, 8))->where('status', 1);
        $loan = \App\Models\Grouploans::where('id', $request['id'])->first();
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        }
        $data = $data->orderby('id', 'DESC')->get();
        if ($request['commission_export'] == 0) {
            return Excel::download(new BranchLoanGroupCommissionExport($data, $loan), 'branch_loan_group_commission.xlsx');
        } elseif ($request['commission_export'] == 1) {
            $pdf = PDF::loadView('templates.branch.loan_management.exportcommission_detail_loan_group', compact('data', 'loan'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('branch_loan_group_commission.pdf');
        }
    }
    /**
     * Download Loan Recovery No Dues PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function DownloadRecoveryNoDueLoan($id, $type)
    {
        /* $data['loanDetails'] = Memberloans::with('loan')->findOrFail($id);*/
        $data['loanDetails'] = \App\Models\Memberloans::with('loan', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        if (!empty($data['loanDetails'])) {
            $loanDetails = $data['loanDetails']->toArray();
            $loan_type = $loanDetails['loan_type'];
            if ($loan_type == 3) {
                if (!in_array('Group Loan Download No Dues PDF', auth()->user()->getPermissionNames()->toArray())) {  //group loan
                    return redirect()->route('branch.dashboard');
                }
            } else {
                if (!in_array('Loan Download No Dues PDF', auth()->user()->getPermissionNames()->toArray())) {
                    return redirect()->route('branch.dashboard');
                }
            }
        }
        $data['title'] = 'Print No Dues';
        $result = Memberloans::with('loanMember', 'loanMemberAssociate')->where('status', '!=', 0)->where('id', '=', $id)->where('loan_type', '!=', 3)->get();
        //$result=Memberloans::with('loanMember','loanMemberAssociate')->where('id','=',$id)->get();
        $data['account_number'] = $data['name'] = $data['father_husband'] = $data['clear_date'] = '';
        if (!empty($result)) {
            $result = $result[0]->toArray();
            if ($result['status'] != 3) {
                return redirect(route('loan.loans'));
            }
            $data['account_number'] = $result['account_number'];
            $data['name'] = strtoupper($result['loan_member']['first_name'] . ' ' . $result['loan_member']['last_name']);
            $data['father_husband'] = strtoupper($result['loan_member']['father_husband']);
            if (!empty($result['clear_date'])) {
                $data['clear_date'] = date("d/m/Y", strtotime($result['clear_date']));
            }
        }
        $data['recovery_clear_logo'] = url('core/storage/images/recovery_clear/recovery_clear_logo.png');
        $pdf = PDF::loadView('templates.branch.loan_management.recovery_clear_pdf', compact('data'));
        $pdf->save(storage_path() . '_filename.pdf');
        return $pdf->download('loan_noc.pdf');
    }
    /**
     * Loan Recovery Print No Dues
.
     *
     * @return \Illuminate\Http\Response
     */
    public function PrintRecoveryNoDueLoan($id, $type)
    {
        $data['loanDetails'] = \App\Models\Memberloans::with('loan', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        if (!empty($data['loanDetails'])) {
            $loanDetails = $data['loanDetails']->toArray();
            $loan_type = $loanDetails['loan_type'];
            if ($loan_type == 3) {
                if (!in_array('Group Loan Print No Dues', auth()->user()->getPermissionNames()->toArray())) {  //group loan
                    return redirect()->route('branch.dashboard');
                }
            } else {
                if (!in_array('Loan Print No Dues', auth()->user()->getPermissionNames()->toArray())) {
                    return redirect()->route('branch.dashboard');
                }
            }
        }
        $data['title'] = 'Print No Dues';
        $result = Memberloans::with('loanMember', 'loanMemberAssociate')->where('status', '!=', 0)->where('id', '=', $id)->where('loan_type', '!=', 3)->get();
        // $result= Memberloans::with('loanMember','loanMemberAssociate')->where('id','=',$id)->get();
        $data['account_number'] = $data['name'] = $data['father_husband'] = $data['clear_date'] = '';
        if (!empty($result)) {
            $result = $result[0]->toArray();
            if ($result['status'] != 3) {
                return redirect(route('loan.loans'));
            }
            $data['id'] = $result['id'];
            $data['account_number'] = $result['account_number'];
            $data['name'] = strtoupper($result['loan_member']['first_name'] . ' ' . $result['loan_member']['last_name']);
            $data['father_husband'] = strtoupper($result['loan_member']['father_husband']);
            if (!empty($result['clear_date'])) {
                $data['clear_date'] = date("d/m/Y", strtotime($result['clear_date']));
            }
        }
        $data['recovery_clear_logo'] = url('core/storage/images/recovery_clear/recovery_clear_logo.png');
        $data['data'] = $data;
        return view('templates.branch.loan_management.recovery_clear_print', $data);
    }
    public function voucherExport(Request $request)
    {
        $input = $request->all();
        $companyIdd = $input['company_id'];
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/voucher_list.csv";
        $fileName = env('APP_EXPORTURL')."asset/voucher_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $startDate = '';
        $endDate = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = \App\Models\ReceivedVoucher::with(['rv_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['rv_employee' => function ($query) {
            $query->select('id', 'employee_name', 'employee_code');
        }])->with(['rvCheque' => function ($query) {
            $query->select('id', 'cheque_no', 'deposit_bank_id', 'deposit_account_id', 'cheque_deposit_date', 'account_holder_name');
        }])
        ->with('rv_member:id,first_name,last_name')
        ->with('company:id,name')
        ->where('branch_id', $branch_id);
        if ($companyIdd > 0) {
            $data->where('company_id',$companyIdd);
        }
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->where(function ($query) use ($name) {
                $query->where('member.first_name', 'LIKE', '%' . $name . '%')->orWhere('member.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(member.first_name," ",member.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['payment_type'] != '') {
            $payment_type = $request['payment_type'];
            $data = $data->where('received_mode', '=', $payment_type);
        }
        if ($request['account_head'] != '') {
            $account_head = $request['account_head'];
            $data = $data->where('account_head_id', '=', $account_head);
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $totalResults = $data->orderby('created_at', 'DESC')->count();
        //dd($totalResults);
        $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
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
        $rowReturn = array();
        if ($request['export'] == 0) {
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = $row['company']->name;
                $val['BR NAME'] = $row['rv_branch']->name;
                $val['BR CODE'] = $row['rv_branch']->branch_code;
                $val['SO NAME'] = $row['rv_branch']->sector;
                $val['RO NAME'] = $row['rv_branch']->regan;
                $val['ZO NAME'] = $row['rv_branch']->zone;
                $val['DATE'] = date("d/m/Y", strtotime($row->date));
                $rv_mode = '';
                if ($row->received_mode == 0) {
                    $rv_mode = "Cash";
                }
                if ($row->received_mode == 1) {
                    $rv_mode = "Cheque";
                }
                if ($row->received_mode == 2) {
                    $rv_mode = "Online";
                }
                $val['RECEIVED MODE'] = $rv_mode;
                $val['RECEIVED AMOUNT'] = number_format((float)$row->amount, 2, '.', '');
                $val['ACCOUNT HEAD'] = getAcountHeadNameHeadId($row->account_head_id);
                $director = '';
                if ($row->type == 1) {
                    $director = getAcountHeadNameHeadId($row->director_id);
                }
                $val['DIRECTOR'] = $director;
                $shareholder = '';
                if ($row->type == 2) {
                    $shareholder = getAcountHeadNameHeadId($row->shareholder_id);
                }
                $val['SHARE HOLDER'] = $shareholder;
                $employee_code = '';
                if ($row['rv_employee']  && $row->employee_id != null) {
                    $employee_code = $row['rv_employee']->employee_code;
                }
                elseif ($row['rv_member'] && $row->member_id != null) {
                    $employee_code =  \App\Models\MemberCompany::select('member_id')->where('customer_id',$row['rv_member']->id)->first()->member_id;
                }
                else {
                    $employee_code = "N/A";
                }
                $val['EMP CODE / MEMBER ID'] = $employee_code;
                $employee_name = '';
                if ($row['rv_employee'] && $row->employee_id != null) {
                    $employee_name = $row['rv_employee']->employee_name ?? '';
                }
                elseif ($row['rv_member'] && $row->member_id != null) {
                    $employee_name = $row['rv_member']->first_name . " ".$row['rv_member']->last_name ?? '';
                }
                else {
                    $employee_name = "N/A";
                }
                $val['EMPLOYEE NAME'] = $employee_name;
                $bank_name = '';
                if ($row->type == 4) {
                    $bank_name = getSamraddhBank($row->bank_id)->bank_name;
                } else {
                    $bank_name = 'N/A';
                }
                $val['BANK NAME'] = $bank_name;
                $bank_account_number = '';
                if ($row->type == 4) {
                    $bank_account_number = getSamraddhBankAccountId($row->bank_ac_id)->account_no;
                } else {
                    $bank_account_number = 'N/A';
                }
                $val['BANK A/C'] = $bank_account_number;
                $eli_loan_id = '';
                if ($row->eli_loan_id) {
                    $eli_loan_id = getAcountHeadNameHeadId($row->eli_loan_id);
                } else {
                    $eli_loan_id = 'N/A';
                }
                $val['ELI LOAN'] = $eli_loan_id;
                $cheque_no = '';
                if ($row->received_mode == 0) {
                    $cheque_no = "N/A";
                }
                if ($row->received_mode == 2) {
                    $cheque_no = "N/A";
                }
                if ($row->received_mode == 1) {
                    $cheque_no = $row['rvCheque']->cheque_no;
                }
                $val['CHEQUE NO'] = $cheque_no;
                $cheque_date = '';
                if ($row->received_mode == 1) {
                    $cheque_date = date("d/m/Y", strtotime($row->cheque_date));
                } else {
                    $cheque_date = "N/A";
                }
                $val['CHEQUE DATE'] = $cheque_date;
                $utr_transaction_number = '';
                if ($row->received_mode == 0) {
                    $utr_transaction_number = "N/A";
                } else {
                    $utr_transaction_number = $row->online_tran_no;
                }
                $val['UTR/TRANSCATION NO'] = $utr_transaction_number;
                $transaction_date = '';
                if ($row->received_mode == 0) {
                    $transaction_date = "N/A";
                } else {
                    $transaction_date =  date("d/m/Y", strtotime($row->online_tran_date));
                }
                $val['TRANSACTION DATE'] = $transaction_date;
                $party_bank_name = '';
                if ($row->received_mode == 0) {
                    $party_bank_name = "N/A";
                }
                if ($row->received_mode == 1) {
                    $party_bank_name = $row->cheque_bank_name;
                }
                if ($row->received_mode == 2) {
                    $party_bank_name = $row->online_tran_bank_name;
                }
                $val['PARTY BANK NAME'] = $party_bank_name;
                $party_bank_account = '';
                if ($row->received_mode == 0) {
                    $party_bank_account = "N/A";
                }
                if ($row->received_mode == 1) {
                    $party_bank_account = $row->cheque_bank_ac_no;
                }
                if ($row->received_mode == 2) {
                    $party_bank_account = $row->online_tran_bank_ac_no;
                }
                $val['PARTY BANK A/C'] = $party_bank_account;
                $received_bank = '';
                if ($row->received_mode == 0) {
                    $received_bank = 'N/A';
                } else {
                    $received_bank = getSamraddhBank($row->receive_bank_id)->bank_name;
                }
                $val['RECEIVE BANK'] = $received_bank;
                $received_bank_account = '';
                if ($row->received_mode == 0) {
                    $received_bank_account = "N/A";
                } else {
                    $received_bank_account =  getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no;
                }
                $val['RECEIVE BANK AACOUNT'] = $received_bank_account;
                $bank_slip = '';
                if ($row->slip) {
                    $a = URL::to("/asset/voucher/" . $row->slip . "");
                    $bank_slip =  $row->slip; // '<a href="' . $a . '" target="_blanck">' . $row->slip . '</a>';
                } else {
                    $bank_slip =  'N/A';
                }
                $val['TRANSACTION SLIP'] = $bank_slip;
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        }
        $data = $data->orderby('created_at', 'DESC')->get();
        if ($request['export'] == 1) {
            return Excel::download(new BranchVoucherExport($data), 'voucherReport.xlsx');
        }
    }
    public function maturityListExport(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = Memberinvestments::select('id', 'member_id', 'plan_id', 'branch_id', 'account_number', 'created_at', 'maturity_date', 'is_mature', 'deposite_amount', 'tenure', 'associate_id', 'due_amount', 'company_id', 'customer_id')->with(['member' => function ($q) {
            $q->select('id', 'member_id', 'first_name', 'last_name');
        }, 'company' => function ($q) {
            $q->select('id', 'name');
        }, 'memberCompany' => function ($q) {
            $q->select('id', 'member_id');
        }, 'associateMember' => function ($q) {
            $q->select('id', 'associate_no', 'associate_code', 'first_name', 'last_name');
        }, 'demandadvice' => function ($q) {
            $q->select('id', 'date', 'tds_amount', 'maturity_prematurity_amount', 'final_amount', 'payment_type', 'payment_mode', 'bank_name', 'investment_id', 'maturity_amount_payable', 'bank_account_number', 'bank_name')->with(['demandAmountHead' => function ($q) {
                $q->select('id', 'amount', 'head_id', 'type_id');
            }, 'demandAmount' => function ($q) {
                $q->select('id', 'amount', 'head_id', 'type_id', 'type', 'sub_type', 'amount', 'cheque_no');
            }, 'demandTransactionAmount' => function ($q) {
                $q->select('id', 'amount', 'head_id', 'type_id', 'type', 'sub_type', 'amount', 'transction_no');
            }]);
        }, 'branch' => function ($q) {
            $q->select('id', 'name', 'branch_code', 'zone');
        }, 'plan' => function ($q) {
            $q->select('id', 'name');
        }, 'sumdeposite', 'TransactionTypeDate' => function ($q) {
            $q->select('id', 'investment_id', 'created_at');
        }])->where('plan_id', '!=', 1)->where('branch_id', $branch_id);
        /******* fillter query start ****/
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['branch'] != '') {
                $bid = $request['branch'];
                $data = $data->where('branch_id', $bid);
            }
            if ($request['plan_id'] != '') {
                $planId = $request['plan_id'];
                $data = $data->where('plan_id', '=', $planId);
            }
            if ($request['company_id'] != '') {
                $company_id = $request['company_id'];
                $data = $data->where('company_id', '=', $company_id);
            }
            if ($request['member_id'] != '') {
                $meid = $request['member_id'];
                $data = $data->whereHas('memberCompany', function ($query) use ($meid) {
                    $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
                });
            }
            if ($request['associate_code']) {
                $associate_code = $request['associate_code'];
                $data = $data->whereHas('associateMember', function ($query) use ($associate_code) {
                    $query->where('members.associate_no', 'Like', '%' . $associate_code . '%');
                });
            }
            if ($request['name'] != '') {
                $name = $request['name'];
                $data = $data->whereHas('member', function ($query) use ($name) {
                    $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                });
            }
            if ($request['start_date'] != ''  && $request['status'] == '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            if ($request['status'] != '' ) {
                $status = $request['status'];
                $Date = date('Y-m-d');
                if ($request['status'] == 0) {
                    if ($request['from_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($request['from_date'])));
                        $startDateMonth = date("m", strtotime(convertDate($request['from_date'])));
                        $startDateYear = date("Y", strtotime(convertDate($request['from_date'])));
                        if ($request['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $currentDateMonth = date("m", strtotime(convertDate($Date)));
                        $currentDateYear =  date("Y", strtotime(convertDate($Date)));
                        if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                            $data = $data->whereDate('maturity_date', '>', $currentDate)->whereDate('maturity_date', '<=', $endDate);
                        } elseif ($startDateMonth > $currentDateMonth) {
                            $data = $data->whereBetween('maturity_date', [$startDate, $endDate]);
                        } else {
                            $data = $data->where('maturity_date', '');
                        }
                    }
                } if ($request['status'] == 1) {
                    if ($request['from_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($request['from_date'])));
                        $startDateMonth = date("m", strtotime(convertDate($request['from_date'])));
                        $startDateYear = date("Y", strtotime(convertDate($request['from_date'])));
                        if ($request['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $currentDateMonth = date("m", strtotime(convertDate($Date)));
                        $currentDateYear =  date("Y", strtotime(convertDate($Date)));
                        if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                            $data->whereHas('demandadvice', function ($query) use ($startDate, $endDate, $currentDate) {
                                $query->where('status', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $currentDate]);
                            })->where('is_mature', 0);
                        } elseif ($startDateMonth < $currentDateMonth) {
                            $data->whereHas('demandadvice', function ($query) use ($startDate, $endDate, $currentDate) {
                                $query->where('status', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                            })->where('is_mature', 0);
                        } else {
                            $data->whereHas('demandadvice', function ($query) {
                                $query->where('status', 1);
                            })->where('maturity_date', '<', '');
                        }
                    }

                    if ($request['end_date'] != '' && $request['start_date'] == Null) {
                        $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        $data->whereHas('demandadvice', function ($query) use ($endDate) {
                            $query->where('status', 1);
                        })->whereDate(\DB::raw('DATE(maturity_date)'), '<=', $endDate)->where('is_mature', 0);
                    }
                    if ($request['end_date'] == NUll && $request['start_date'] == Null) {
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        $data->whereHas('demandadvice', function ($query) use ($endDate) {
                            $query->where('status', 1);
                        })->whereDate(\DB::raw('DATE(maturity_date)'), '<=', $currentDate)->where('is_mature', 0);
                    }
                } if ($request['status'] == 2) {
                    if ($request['start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($request['from_date'])));
                        $startDateMonth = date("m", strtotime(convertDate($request['from_date'])));
                        $startDateYear = date("Y", strtotime(convertDate($request['from_date'])));
                        if ($request['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $currentDateMonth = date("m", strtotime(convertDate($Date)));
                        $currentDateYear =  date("Y", strtotime(convertDate($Date)));
                        if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                            /*$data->whereHas('demandadvice',function($query) use($currentDate,$startDate){
								$query->orwhere('maturity_date','<',$currentDate)->where('is_mature',1)->where('maturity_date', '>=',$startDate)->orwhere('demand_advices.status','=',0)->where('demand_advices.is_mature',0);
							})->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $endDate]);
							$data->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $endDate]);
							$whereCond = '((maturity_date > "'.$currentDate.'" && is_mature = 1 and maturity_date > "'.$startDate.'") )';
							$data = $data->whereRaw($whereCond)->orwhere('demand_advices.is_mature',0);*/
                            $data->where('is_mature', 0)->whereBetween('maturity_date', [$startDate, $currentDate])->orWhere(function ($q) use ($startDate, $currentDate) {
                                $q->whereHas('demandadvice', function ($query) use ($startDate, $currentDate) {
                                    $query->where('status', 0)->whereBetween(\DB::raw('DATE(date)'), [$startDate, $currentDate]);
                                });
                            });
                        } elseif ($startDateMonth < $currentDateMonth) {
                            $data->where('is_mature', 0)->whereBetween('maturity_date', [$startDate, $endDate])->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->whereHas('demandadvice', function ($query) use ($startDate, $endDate) {
                                    $query->where('status', 0)->whereBetween(\DB::raw('DATE(member_investments.maturity_date)'), [$startDate, $endDate]);
                                });
                            });
                        } else {
                            $data->whereHas('demandadvice', function ($query) {
                                $query->where('status', 0);
                            })->where('maturity_date', '<', '');
                        }
                    }
                }
            }
        }
        $data = $data->orderby('created_at', 'DESC')->get();
        if ($request['export'] == 0) {
            return Excel::download(new BranchMaturityReportExport($data), 'maturityReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.branch.report.export_maturity_report', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('maturityReport.pdf');
        }
    }
    public function daybookReportExport(Request $request)
    {
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
        } else {
            $startDate = '';
        }
        if ($request['end_date'] != '') {
            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
        } else {
            $endDate = '';
        }
        if ($request['company'] != '') {
            $company = $request['company'];
        } else {
            $company = '';
        }
        //dd($company);
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        // $cash_in_hand['CR'] = BranchDaybook::where(function($q){
        //         $q->where('sub_type','!=',30)->orwhere('sub_type','=',NULL);
        //     })->where('payment_mode',0)->where('branch_id',$branch_id)->where('payment_type','CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
        // $cash_in_hand['DR'] = BranchDaybook::where('payment_mode',0)->where('branch_id',$branch_id)->where('payment_type','DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');

        $aa = BranchDaybookAmount($startDate, $endDate, $branch_id);
        $cash_in_hand['DR'] = 0;
        $cash_in_hand['CR'] = 0;
        if (array_key_exists('0_CR', $aa)) {
            $cash_in_hand['CR'] = $aa['0_CR'];
        }
        if (array_key_exists('0_DR', $aa)) {
            $cash_in_hand['DR'] = $aa['0_DR'];
        }
        $cheque['CR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $cheque['DR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $bank = SamraddhBank::with('bankAccount')->get();
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.id', 'branch_daybook.id as btid')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.branch_id', $branch_id)->whereBetween('branch_daybook.entry_date', [$startDate, $endDate])->orderBy('branch_daybook.entry_date', 'ASC')->where('branch_daybook.is_deleted', 0)->limit(100)->get();
        $rowReturn = array();
        foreach ($data as $key => $value) {
            //  dd($value);
            $getBranchOpening = getBranchOpeningDetail($branch_id);
            $balance = 0;
            $currentdate = date('Y-m-d');
            if ($getBranchOpening->date == $startDate) {
                $balance = $getBranchOpening->total_amount;
            }
            if ($getBranchOpening->date < $startDate) {
                if ($getBranchOpening->date != '') {
                    $getBranchTotalBalance = getBranchTotalBalanceAllTran($startDate, $getBranchOpening->date, $getBranchOpening->total_amount, $branch_id);
                } else {
                    $getBranchTotalBalance = getBranchTotalBalanceAllTran($startDate, $currentdate, $getBranchOpening->total_amount, $branch_id);
                }
                $balance = $getBranchTotalBalance;
                if ($endDate == '') {
                    $endDate = $currentdate;
                }
            }
            $type = '';
            $getTransType = \App\Models\TransactionType::where('type', $value->type)->where('sub_type', $value->sub_type)->first();
            $type = '';
            if (isset($getTransType->type)) {
                if ($value->type == $getTransType->type) {
                    if ($value->sub_type == $getTransType->sub_type) {
                        $type = $getTransType->title;
                    }
                }
            } else {
                $type = 'N/A';
            }
            if ($value->type == 21) {
                $record = ReceivedVoucher::where('id', $value->type_id)->first();
                if ($record) {
                    $type = $record->particular;
                } else {
                    $type = "N/A";
                }
            }
            // Member Name, Member Account and Member Id
            $memberData = $value->type_id;
            $loanData = getLoanDetail($value->type_id);
            $groupLoanData = getGroupLoanDetail($value->type_id);
            $DemandAdviceData = \App\Models\DemandAdvice::where('id', $value->type_id)->first();
            $freshExpenseData = \App\Models\DemandAdviceExpense::where('id', $value->type_id)->first();
            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name = 'N/A';
            if ($value->payment_mode == 6) {
                $rentPaymentDetail = \App\Models\RentLiabilityLedger::with('rentLib')->where('id', $value->type_transaction_id)->first();
                $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
            } else {
                $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $value->type_transaction_id)->first();
                $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
            }
            if ($value->type == 14) {
                if ($value->sub_type == 144) {
                    $voucherDetail = ReceivedVoucher::where('id', $value->type_transaction_id)->first();
                } else {
                    $voucherDetail = ReceivedVoucher::where('id', $value->type_id)->first();
                }
            }
            if ($value->type == 1) {
                if ($value->type_id) {
                    $memberName = !empty(getMemberData($value->type_id)) ? getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name : 'N/A';
                    $memberId = getMemberData($value->type_id)->member_id;
                    $memberAccount = 'N/A';
                }
            } elseif ($value->type == 2) {
                if ($value->type_id) {
                    $memberName = !empty(getMemberData($value->type_id)) ? getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name : 'N/A';
                    $memberId = getMemberData($value->type_id)->member_id;
                    $memberAccount = 'N/A';
                }
            } elseif ($value->type == 3) {
                if ($value->member_id) {
                    $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                    $memberId = getMemberData($value->member_id)->member_id;
                }
                // if($memberData)
                // {

                //     $plan_name = getPlanDetail($memberData)->name;
                //     dd($plan_name);
                //     $memberAccount = $memberData->account_number;
                // }
            } elseif ($value->type == 4) {
                if ($value->member_id) {
                    $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                    $memberId = getMemberData($value->member_id)->member_id;
                }
                if ($value->sub_type == 42) {
                    $memberAccount = SavingAccountTranscation::where('id', $value->type_transaction_id)->first();
                    if (isset($memberAccount->account_no)) {
                        $memberAccount = $memberAccount->account_no;
                    }
                } else {
                    $memberAccount = getSsbAccountNumber($value->type_id);
                    if ($memberAccount) {
                        $memberAccount = $memberAccount->account_no;
                    }
                }
            } elseif ($value->type == 5) {
                if ($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57 ||  $value->sub_type == 525  || $value->sub_type == 511  || $value->sub_type == 513  || $value->sub_type == 515 ||  $value->sub_type == 528 ||  $value->sub_type == 529 ||  $value->sub_type == 530 ||  $value->sub_type == 531 || $value->sub_type == 527 || $value->sub_type == 532) {
                    if ($loanData) {
                        $memberName = getMemberData($loanData->applicant_id)->first_name . ' ' . getMemberData($loanData->applicant_id)->last_name;
                        $memberAccount = $loanData->account_number;
                        if ($loanData->loan_type == 1) {
                            $plan_name = 'Personal Loan(PL)';
                        }
                        if ($loanData->loan_type == 2) {
                            $plan_name = 'Staff Loan(SL)';
                        }
                        if ($loanData->loan_type == 4) {
                            $plan_name = 'Loan against Investment plan(DL)';
                        }
                    }
                } elseif ($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58  || $value->sub_type == 512  || $value->sub_type == 514  || $value->sub_type == 516 || $value->sub_type == 518) {
                    if ($groupLoanData) {
                        $memberAccount = $groupLoanData->account_number;
                        $memberName = getMemberData($groupLoanData->applicant_id)->first_name . ' ' . getMemberData($groupLoanData->applicant_id)->last_name;
                    }
                }
            } elseif ($value->type == 6) {
                if (isset($salaryDetail['ledger_employee']->employee_name)) {
                    $memberName = $salaryDetail['ledger_employee']->employee_name;
                    $memberAccount = $salaryDetail['ledger_employee']->employee_code;
                } elseif (isset($salaryDetail['salary_employee']->employee_name)) {
                    $memberName = $salaryDetail['salary_employee']->employee_name;
                    $memberAccount = $salaryDetail['salary_employee']->employee_code;
                }
            } elseif ($value->type == 7) {
                $memberName = SamraddhBank::where('id', $value->transction_bank_to)->first();
                $memberName =  $memberName->bank_name;
                $memberAccount = getSamraddhBankAccountId($value->transction_bank_to);
                $memberAccount = $memberAccount->account_no;
            } elseif ($value->type == 9) {
                $memberName == !empty(getMemberData($value->type_id)) ? getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name : 'N/A';
                $memberAccount = getMemberData($value->type_id)->member_id;
            } elseif ($value->type == 10) {
                if ($rentPaymentDetail['rentLib']) {
                    if ($rentPaymentDetail) {
                        $memberName = $rentPaymentDetail['rentLib']->owner_name;
                    }
                }
                $memberAccount = 'N/A';
            } elseif ($value->type == 11) {
                if ($DemandAdviceData['employee_name']) {
                    $memberName = $DemandAdviceData->party_name;
                }
                $memberAccount = 'N/A';
            } elseif ($value->type == 12) {
                if ($salaryDetail['salary_employee']) {
                    $memberName =   $salaryDetail['salary_employee']->employee_name;
                    $memberAccount = $salaryDetail['salary_employee']->employee_name;
                }
            } elseif ($value->type == 13) {
                if ($value->sub_type == 131) {
                    if ($freshExpenseData) {
                        $memberAccount = $freshExpenseData['advices']->voucher_number;
                        $memberId = $freshExpenseData->bill_number;
                    }
                }
                if ($value->sub_type == 132) {
                    if ($freshExpenseData) {
                        $memberAccount = $freshExpenseData['advices']->voucher_number;
                        $memberId = $freshExpenseData->bill_number;
                    }
                }
                if ($value->sub_type == 133) {
                    $memberAccount = $DemandAdviceData->investment_id;
                    $plan_id = $DemandAdviceData->investment_id;
                    $plan_name = 'N/A';
                }
                if ($value->sub_type == 134) {
                    $memberAccount = $DemandAdviceData->investment_id;
                    $plan_id = $DemandAdviceData->investment_id;
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 135) {
                    $memberAccount = $DemandAdviceData->investment_id;
                    $plan_id = $DemandAdviceData->investment_id;
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 136) {
                    $memberAccount = $DemandAdviceData->investment_id;
                    $plan_id = $DemandAdviceData->investment_id;
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 137) {
                    $memberAccount = $DemandAdviceData->investment_id;
                    $plan_id = $DemandAdviceData->investment_id;
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 142) {
                    if ($freshExpenseData) {
                        $memberName = $freshExpenseData->party_name;
                        $memberAccount = $freshExpenseData['advices']->voucher_number;
                        $memberId = $freshExpenseData->bill_number;
                    }
                }
            } elseif ($value->type == 14) {
                if ($voucherDetail != '') {
                    if ($voucherDetail->type == 1) {
                        if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                            $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                            $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                            if (isset($bankAccount)) {
                                $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                            }
                        }
                    }
                    if ($voucherDetail->type == 2) {
                        if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                            $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                            $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                            if (isset($bankAccount)) {
                                $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                            }
                        }
                    }
                    if ($voucherDetail->type == 3) {
                        $memberId =  getEmployeeData($voucherDetail->employee_id)->employee_code;
                        if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                            $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                            $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                            if (isset($bankAccount)) {
                                $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                            }
                        }
                    }
                    if ($voucherDetail->type == 4) {
                        $memberAccount = getSamraddhBankAccountId($voucherDetail->bank_ac_id)->account_no;
                    }
                    if ($voucherDetail->type == 5) {
                        if (isset($voucherDetail)) {
                            $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                            $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                            if (isset($bankAccount)) {
                                $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                            }
                        }
                    }
                }
            } elseif ($value->type == 15) {
                $memberName = getAcountHeadNameHeadId($value->type_id);
                $memberAccount = "N/A";
            } elseif ($value->type == 16) {
                $memberName = getAcountHeadNameHeadId($value->type_id);
                $memberAccount = "N/A";
            } elseif ($value->type == 17) {
                if ($value->sub_type == 171) {
                    $detail = \App\Models\LoanFromBank::where('daybook_ref_id', $value->daybook_ref_id)->first();
                    if ($detail) {
                        $memberAccount = $detail->loan_account_number;
                        $memberName = $detail->bank_name;
                    }
                } else if ($value->sub_type == 172) {
                    $detail = \App\Models\LoanEmi::where('id', $value->type_transaction_id)->first();
                    if ($detail) {
                        $memberAccount = \App\Models\LoanFromBank::where('id', $detail->loan_bank_account)->first();
                        $memberAccount = $memberAccount->loan_account_number;
                        $memberName = $detail->loan_bank_name;
                    }
                }
            } elseif ($value->type == 30) {
                if ($value->sub_type == 301) {
                    $detail = \App\Models\CompanyBound::where('daybook_ref_id', $value->daybook_ref_id)->first();
                    if ($detail) {
                        $memberAccount = $detail->fd_no;
                        $memberName = $detail->bank_name;
                    }
                } else if ($value->sub_type == 302) {
                    $detail = \App\Models\CompanyBoundTransaction::where('daybook_ref_id', $value->daybook_ref_id)->first();
                    if ($detail) {
                        $record = \App\Models\CompanyBound::where('id', $detail->bound_id)->first();
                        $memberAccount = $record->fd_no;
                        $memberName = $record->bank_name;
                    }
                }
            } elseif ($value->type == 21) {
                $memberAccount = $value->memberMemberId->member_id;
                $memberName = $value->memberMemberId->first_name . ' ' . $value->memberMemberId->last_name;
            } elseif ($value->type == 29) {
                $d = SavingAccount::where('member_id', $value->member_id)->first();
                $memberAccount = $d->account_no;
                $memberName = getMemberData($d->member_id)->first_name . ' ' . getMemberData($d->member_id)->last_name;;
                $plan_name = 'Saving Account';
            }
            if ($value->type == 22) {
                if ($value->sub_type == 222) {
                    $type = $value->description;
                }
            }
            if ($value->type == 23) {
                if ($value->sub_type == 232) {
                    $type = $value->description;
                }
            }
            if ($value->sub_type == 43 || $value->sub_type == 41) {
                $associate_code = SavingAccount::where('id', $value->type_id)->first();
                $associate_name = Member::where('id', $associate_code->associate_id)->first();
            }
            if ($value->type == 13  || $value->sub_type == 35 || $value->sub_type == 37 || $value->sub_type == 33 || $value->sub_type == 34 || $value->type == 21) {
                $associate_code = getAssociateId($value->member_id);
                $associate_name = Member::where('associate_no', $associate_code)->first();
            }
            if ($value->type == 20) {
                $record = \App\Models\Expense::where('id', $value->type_id)->first();
                if ($record->account_head_id && $record->sub_head1 && $record->sub_head2) {
                    $mainHead =  getAcountHeadData($record->account_head_id);
                    $subHead =  getAcountHeadData($record->sub_head1);
                    $subHead2 =  getAcountHeadData($record->sub_head2);
                    $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead . '/' . $subHead2;
                } elseif ($record->account_head_id && $record->sub_head1) {
                    $mainHead =  getAcountHeadData($record->account_head_id);
                    $subHead =  getAcountHeadData($record->sub_head1);
                    $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead;
                } elseif ($record->account_head_id) {
                    $mainHead =  getAcountHeadData($record->account_head_id);
                    $plan_name = 'INDIRECT EXPENSE /' . $mainHead;
                }
            }
            // Associate
            $a_name = 'N/A';
            if ($value->sub_type == 43 || $value->sub_type == 41 || $value->type == 13 || $value->sub_type == 35  || $value->sub_type == 37 || $value->sub_type == 33 || $value->sub_type == 34 || $value->type == 21) {
                if ($associate_name) {
                    $a_name = $associate_name->first_name . ' ' . $associate_name->last_name . '(' . $associate_name->associate_no . ')';
                }
            } else {
                if ($value->branch_associate_id) {
                    $a_name = getMemberData($value->branch_associate_id)->first_name . ' ' . getMemberData($value->branch_associate_id)->last_name . '(' . getMemberData($value->branch_associate_id)->associate_no . ')';
                }
            }
            // Payment Type
            $cr_amount = 0;
            $dr_amnt = 0;
            if ($value->payment_type == 'CR') {
                $cr_amount = number_format((float)$value->amount, 2, '.', '');
            }
            if ($value->payment_type == 'DR') {
                $dr_amnt =  number_format((float)$value->amount, 2, '.', '');
            }
            // Balance
            if ($value->branch_payment_mode == 0 && $value->sub_type != 30) {
                $balance = number_format((float)$balance, 2, '.', '');
            }
            // Ref Number
            if ($value->branch_payment_mode == 0) {
                $ref_no = 'N/A';
            } elseif ($value->branch_payment_mode == 1) {
                $ref_no = $value->cheque_no;
            } elseif ($value->branch_payment_mode == 2) {
                $ref_no = $value->transction_no;
            } elseif ($value->branch_payment_mode == 3) {
                $ref_no = $value->v_no;
            } elseif ($value->branch_payment_mode == 6) {
                $ref_no = $value->jv_unique_id;
            } else {
                $ref_no = 'N/A';
            }
            // Payment Mode
            if ($value->sub_type == 30) {
                $pay_mode = 'ELI';
            } else
            if ($value->branch_payment_mode == 0) {
                $pay_mode = 'CASH';
            } elseif ($value->branch_payment_mode == 1) {
                $pay_mode = 'CHEQUE';
            } elseif ($value->branch_payment_mode == 2) {
                $pay_mode = 'ONLINE TRANSFER';
            } elseif ($value->branch_payment_mode == 3) {
                $pay_mode = 'SSB';
            } elseif ($value->branch_payment_mode == 4) {
                $pay_mode = 'AUTO TRANSFER';
            } elseif ($value->branch_payment_mode == 6) {
                $pay_mode = 'JV';
            } elseif ($value->branch_payment_mode == 5) {
                $pay_mode = 'From Loan';
            } elseif ($value->branch_payment_mode == 8) {
                $pay_mode = 'SSB Debit Cron';
            }
            if ($value->entry_date) {
                $date = date("d/m/Y", strtotime(convertDate($value->entry_date)));
            } else {
                $date = 'N/A';
            }
            // tag
            $tag = '';
            if ($value->type = 3) {
                if ($value->sub_type == 31) {
                    $tag = 'N';
                }
                if ($value->sub_type == 32) {
                    $tag = 'R';
                }
            }
            if ($value->type == 4) {
                if ($value->sub_type == 41) {
                    $tag = 'N';
                }
                if ($value->sub_type == 42) {
                    $tag = 'R';
                }
                if ($value->sub_type == 43) {
                    $tag = 'W';
                }
            }
            if ($value->type == 5) {
                if ($value->sub_type == 51) {
                    $tag = 'LD';
                }
                if ($value->sub_type == 52) {
                    $tag = 'L';
                }
                if ($value->sub_type == 54) {
                    $tag = 'LD';
                }
                if ($value->sub_type == 55) {
                    $tag = 'L';
                }
            }
            if ($value->type == 7) {
                $tag = 'B';
            }
            if ($value->type == 13) {
                if ($value->sub_type == 131) {
                    $tag = 'E';
                }
                if ($value->sub_type == 133) {
                    $tag = 'M';
                }
                if ($value->sub_type == 134) {
                    $tag = 'M';
                }
                if ($value->sub_type == 135) {
                    $tag = 'M';
                }
                if ($value->sub_type == 136) {
                    $tag = 'M';
                }
                if ($value->sub_type == 137) {
                    $tag = 'M';
                }
            }
            $val['tr_date'] = $date;
            $val['tran_by'] = (($value->is_app) ? ($value->is_app == 1 ? 'Associate' : 'E-passbook') : 'Software');
            $val['bt_id'] = $value->btid;
            $val['member_account'] = $memberAccount;
            $val['plan_name'] = $plan_name;
            $val['memberName'] = $memberName;
            $val['a_name'] = $a_name;
            $val['type'] = $type;
            $val['description_cr'] = $value->description_cr;
            $val['description_dr'] = $value->description_dr;
            $val['cr_amnt'] = $cr_amount;
            $val['dr_amnt'] = $dr_amnt;
            $val['balance'] = $balance;
            $val['ref_no'] = $ref_no;
            $val['pay_mode'] = $pay_mode;
            $val['tag'] = $tag;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new BranchDayBookReportExport($rowReturn, $cash_in_hand, $cheque, $bank, $branch_id, $startDate, $endDate), 'DaybookReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.branch.report.export_daybook_report', compact('balance_cash', 'C_balance_cash', 'rowReturn', 'cash_in_hand', 'cheque', 'bank', 'branch_id', 'startDate', 'endDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('DaybookReport.pdf');
        }
    }


    public function loanListExport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('loanReportListbranch'.$token);
        $count = Cache::get('loanReportListCountbranch'.$token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/loanReportbranch.csv";
        $fileName = env('APP_EXPORTURL') . "asset/loanReportbranch.csv";
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

        $sno=$_POST['start'];
        foreach (array_slice($data, $start, $limit) as $row) {
            $sno++;
            $val['S/N'] = $sno;
            switch ($row['status']) {
                case 0:
                    $val['Status'] = 'Inactive';
                    break;
                case 1:
                    $val['Status'] = 'Approved';
                    break;
                case 2:
                    $val['Status'] = 'Rejected';
                    break;
                case 3:
                    $val['Status'] = 'Completed';
                    break;
                case 4:
                    $val['Status'] = 'ONGOING';
                    break;
            }
            // pd($row['loan_member_company']);

            if ($row['loan_type'] == 3) {
                if (isset($row['loan_member_company']['member'])) {
                    $applicantName =  $row['loan_member_company']['member']['first_name'] . ' ' .$row['loan_member_company']['member']['last_name'];
                } else {
                    $applicantName = 'N/A';
                }
                // $applicantName = isset($row['loan_member_company']) ? $row['loan_member_company']['member']['first_name'] . ' ' . $row['loan_member_company']['member']['last_name'] : 'N/A';
            } else {
                if (isset($row['loan_member_company'])) {
                    if ($row['loan_member_company']['member']) {
                        $applicantName = $row['loan_member_company']['member']['first_name'] . ' ' . $row['loan_member_company']['member']['last_name'];
                    } else {
                        $applicantName = 'N/A';
                    }
                } else {
                    $applicantName = 'N/A';
                }

                // $applicantName = isset($row['loan_member_company']['member']) ? $row['loan_member_company']['member']['first_name'] . ' ' . $row['loan_member_company']['member']['last_name'] : 'N/A';
            }
            $val['Applicant Name'] = $applicantName;
            $val['customer_id'] = isset($row['loan_member_company']['member']['member_id']) ? $row['loan_member_company']['member']['member_id'] : 'N/A';
            $val['company'] = isset($row['company']['name']) ? $row['company']['name'] : 'N/A';
            // if ($row['loan_type'] == 3) {
            //     $applicantId =  $row['group_loan_common_id'];
            // } else {
            //     $applicantId =   isset($row['loan_member_company']->member_id) ? $row['loan_member_company']->member_id : 'N/A';
            // }
            // $val['Applicant Id'] = $applicantId;
            if ($row['loan_type'] == 3) {
                // pd($row['loan_member_company']);
                if (isset($row['loan_member_company']['customer_id'])) {
                    if (customGetMemberData($row['loan_member_company']['customer_id'])) {
                        $applicantMobile =  customGetMemberData($row['loan_member_company']['customer_id'])->mobile_no;
                    } else {
                        $applicantMobile =  'N/A';
                    }
                } else {
                    $applicantMobile =  'N/A';
                }
                // $applicantMobile = isset($row['member']['mobile_no']) ? $row['member']['mobile_no'] : 'N/A';
            } else {
                if (isset($row['customer_id'])) {
                    if (customGetMemberData($row['customer_id'])) {
                        $applicantMobile =  customGetMemberData($row['loan_member_company']['customer_id'])->mobile_no;
                        $applicantMobile =  'N/A';
                    }
                } else {
                    $applicantMobile =  'N/A';
                }
                // $applicantMobile = isset($row['loan_member_company']['member']['mobile_no']) ? $row['loan_member_company']['member']['mobile_no'] : 'N/A';
            }
            $val['Applicant Phone Number'] = $applicantMobile;
            $val['Account No.'] = $row['account_number'];
            $val['Branch'] = getBranchDetail($row['branch_id'])->name;
            $val['Sector Branch'] = getBranchDetail($row['branch_id'])->sector;
            if ($row['loan_type'] == 3) {
                $val['Member Id'] = isset($row['loan_member_company']['member_id']) ? $row['loan_member_company']['member_id'] : 'N/A';
            } else {
                $val['Member Id'] = isset($row['loan_member_company']['member_id']) ? $row['loan_member_company']['member_id'] : 'N/A';
            }

            $val['Sanctioned Amount'] = $row['amount'];
            $val['Transfer Amount'] = $row['transfer_amount'];
            $val['TRANSFER DATE'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));

            if (isset($row['approve_date'])) {
                $val['Sanctioned Date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
            } else {
                $val['Sanctioned Date'] = 'N/A';
            }
            $val['EMI Amount'] = $row['emi_amount'];
            $val['No. of Installments'] = $row['emi_period'];
            if ($row['emi_option'] == 1) {
                $eType = 'Months';
            } elseif ($row['emi_option'] == 2) {
                $eType = 'Weeks';
            } elseif ($row['emi_option'] == 3) {
                $eType = 'Daily';
            }
            $val['Loan Mode'] = $eType??'N/A';
            $val['Loan Type'] = $row['loan']['name']??'N/A';
            $val['Loan Issued Date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
            $mode = \App\Models\Daybook::whereIn('transaction_type', [3, 8])->where('loan_id', $row['id'])->orderby('id', 'ASC')->first(['payment_mode','cheque_dd_no']);
            if ($mode) {
                switch ($mode->payment_mode) {
                    case 0:
                        $pMode = 'Cash';
                        break;
                    case 1:
                        $pMode = 'Cheque';
                        break;
                    case 3:
                        $pMode = 'Online Transaction';
                        break;
                    case 4:
                        $pMode = 'SSB';
                        break;
                    case 5:
                        $pMode = 'From loan amount';
                        break;
                    }
            } else {
                $pMode = 'N/A';
            }
            $val['Loan Issued Mode'] = $pMode;
            $val['Cheque No.'] = $mode ? $mode->cheque_dd_no??'N/A' : 'N/A';
            $amount = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('is_deleted', 0)->whereIn('loan_sub_type',[0,1])->sum('deposit');
            $val['Total Recovery Amt'] = $amount;
            // $val['Total Recovery EMI Till Date'] = $row['credit_amount'];
            switch ($row['emi_option']) {
                case 1:
                    $closingAmountROI = $row['due_amount'] * $row['ROI'] / 1200;
                    break;
                case 2:
                    $closingAmountROI = $row['due_amount'] * $row['ROI'] / 5200;
                    break;
                case 3:
                    $closingAmountROI = $row['due_amount'] * $row['ROI'] / 36500;
                    break;
                default:
                    $closingAmountROI = 0;
                    break;
            }
            $outstandingAmount = isset($row['get_outstanding']['out_standing_amount'])
            ? ($row['get_outstanding']['out_standing_amount'] > 0 ? $row['get_outstanding']['out_standing_amount'] : 0)
            :  $row['amount'];
            $val['Closing Amount'] = $outstandingAmount;
            $d1 = explode('-', $row['created_at']);
            $d2 = explode('-', date("Y-m-d"));
            $firstMonth = $d1[1];
            $secondMonth = $d2[1];
            $monthDiff = $secondMonth - $firstMonth;
            $ramount = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('account_number', $row['account_number'])->where('is_deleted',0)->sum('deposit');
            $camount  = $monthDiff * $row['emi_amount'];
            if ($ramount < $camount) {
                $isPending = 'Yes';
            } else {
                $isPending = 'No';
            }
            $val['Balance EMI'] = $isPending;
            $d1 = explode('-', $row['approve_date']);
            $d2 = explode('-', date("Y-m-d"));
            $firstMonth = $d1[1];
            $secondMonth = $d2[1];
            $monthDiff = $secondMonth - $firstMonth;
            $camount  = $monthDiff * $row['emi_amount'];
            $val['EMI Should be received till date'] = $camount;
            $val['Future EMI Due Till Date(Total)'] = $camount;
            $val['Date'] = date("d/m/Y", strtotime($row['created_at']));

            $coappName = (isset($row['loan_co_applicants']) ? count($row['loan_co_applicants']) > 0 : false) ? (customGetMemberData($row['loan_co_applicants'][0]['member_id'])) ? customGetMemberData($row['loan_co_applicants'][0]['member_id'])->first_name . ' ' . customGetMemberData($row['loan_co_applicants'][0]['member_id'])->last_name : 'N/A'  : 'N/A';
            $val['Co-Applicant Name'] = $coappName;

            $coappmName = (isset($row['loan_co_applicants']) ? count($row['loan_co_applicants']) > 0 : false) ? (customGetMemberData($row['loan_co_applicants'][0]['member_id'])) ? customGetMemberData($row['loan_co_applicants'][0]['member_id'])->mobile_no : 'N/A' : 'N/A';
            $val['Co-Applicant Number'] = $coappmName;

            $val['Guarantor Name'] = (isset($row['loan_guarantor']) ? count($row['loan_guarantor']) > 0 : false) ? isset($row['loan_guarantor'][0]['name']) ? ($row['loan_guarantor'][0]['name']) : 'N/A' : 'N/A';

            $val['Guarantor Number'] = (isset($row['loan_guarantor']) ? count($row['loan_guarantor']) > 0 : false) ? isset($row['loan_guarantor'][0]['name']) ? ($row['loan_guarantor'][0]['mobile_number']) : 'N/A' : 'N/A';

            $val['Applicant Address'] = (isset($row['loan_applicants']) ? count($row['loan_applicants']) > 0 : false) ? (customGetMemberData($row['loan_applicants'][0]['member_id'])) ? preg_replace( "/\r|\n/", "",customGetMemberData($row['loan_applicants'][0]['member_id'])->address) : 'N/A' : 'N/A';

            $record = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->orderby('created_at', 'asc')->first('created_at');
            if ($record && isset($record)) {
                $feDate = date("d/m/Y", strtotime(convertDate($record->created_at)));
            } else {
                $feDate = '';
            }
            $val['First EMI Date'] = $feDate;
            if ($row['approve_date']) {
                if ($row['emi_option'] == 1) {
                    $last_recovery_date = date('d/m/Y', strtotime("+" . $row['emi_period'] . " months", strtotime($row['approve_date'])));
                } elseif ($row['emi_option'] == 2) {
                    $days = $row['emi_period'] * 7;
                    $start_date = $row['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $last_recovery_date = date('d/m/Y', $date);
                } elseif ($row['emi_option'] == 3) {
                    $days = $row['emi_period'];
                    $start_date = $row['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $last_recovery_date = date('d/m/Y', $date);
                }
            } else {
                $last_recovery_date = 'N/A';
            }
            $val['Loan End Date'] = isset($row['closing_date']) ? date('d/m/Y', strtotime($row['closing_date'])) : $last_recovery_date ;

            // $amount = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('is_deleted', 0)->sum('deposit');
            // $val['Total Deposit Till Date'] = $amount;
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
        //if($percentage > 100){
        //return Excel::download(new LoanReportListExport($DataArray), $fileName);
        //}else{
        //Excel::store(new LoanReportListExport($DataArray), $fileName);
        echo json_encode($response);
    }
    public function branchBusinessReportExport(Request $request)
    {
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
        } else {
            $startDate = '';
        }
        if ($request['end_date'] != '') {
            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
        } else {
            $endDate = '';
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $account_head = AccountHeads::where(function ($q) {
            $q->orwhere('parent_id', 14)
                ->orwhere('parent_id', 86);
        })->where('status', 0)->get();
        // ...........................Fund Transfer Loan and Micro Detail...................//
        $loans = DB::table('funds_transfer as w')
            ->join('samraddh_banks', 'w.head_office_bank_id', '=', 'samraddh_banks.id')
            ->select(array(DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.transfer_date_time) day'), 'w.head_office_bank_id', 'samraddh_banks.bank_name'))
            ->where('w.transfer_type', 0)->where('w.transfer_mode', '0')->where('w.branch_id', $branch_id)
            ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
            ->groupBy('day', 'head_office_bank_id')
            ->orderBy('day', 'desc')
            ->get();
        $micros = DB::table('funds_transfer as w')
            ->join('samraddh_banks', 'w.head_office_bank_id', '=', 'samraddh_banks.id')
            ->select(array(DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.transfer_date_time) day'), 'w.head_office_bank_id', 'samraddh_banks.bank_name'))
            ->where('w.transfer_type', 0)->where('w.transfer_mode', '1')->where('w.branch_id', $branch_id)
            ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
            ->groupBy('day', 'head_office_bank_id')
            ->orderBy('day', 'desc')
            ->get();
        $totalMicro = DB::table('funds_transfer as w')
            ->select(array(DB::Raw('sum(w.amount) as amount')))
            ->where('w.transfer_type', 0)->where('w.transfer_mode', '1')->where('w.branch_id', $branch_id)
            ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
            ->get();
        if (count($totalMicro) > 0) {
            $totalMicro = $totalMicro[0]->amount;
        } else {
            $totalMicro = 0;
        }
        $totalLoan = DB::table('funds_transfer as w')
            ->select(array(DB::Raw('sum(w.amount) as amount')))
            ->where('w.transfer_type', 0)->where('w.transfer_mode', '0')->where('w.branch_id', $branch_id)
            ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
            ->get();
        if (count($totalLoan) > 0) {
            $totalLoan = $totalLoan[0]->amount;
        } else {
            $totalLoan = 0;
        }
        $totalAmounts = (float)$totalLoan + (float)$totalMicro;
        // ...........................End Fund Transfer Loan and Micro Detail...................//
        // ........................... RECEIVED CHEQUES Detail...................//
        $receivedChequeMicro = DB::table('branch_daybook as w')
            ->join('samraddh_banks', 'w.cheque_bank_to', '=', 'samraddh_banks.id', 'left')
            ->select(array(DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.entry_date) day'), 'w.cheque_bank_to', 'samraddh_banks.bank_name'))
            ->where('w.payment_mode', 1)->where('w.payment_type', 'CR')->where('w.branch_id', $branch_id)
            ->where('w.type', '!=', '5')->where('w.type', '!=', '10')->where('w.type', '!=', '12')
            ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
            ->groupBy('day', 'cheque_bank_to')
            ->orderBy('day', 'desc')
            ->get();
        $receivedChequeLoan = DB::table('samraddh_bank_daybook as w')
            ->join('samraddh_banks', 'w.amount_from_id', '=', 'samraddh_banks.id', 'left')
            ->select(array(DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.entry_date) day'), 'w.amount_from_id', 'samraddh_banks.bank_name'))
            ->where('w.payment_mode', 1)->where('w.payment_type', 'CR')->where('w.branch_id', $branch_id)
            ->where('w.type', '5')
            ->whereIn('w.sub_type', array("52,53,55,56,57,58"))
            ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
            ->groupBy('day', 'amount_from_id')
            ->orderBy('day', 'desc')
            ->get();
        $receivedChequeMicoTotal = DB::table('branch_daybook as w')
            ->select(array(DB::Raw('sum(w.amount) as amount')))
            ->where('w.payment_mode', 1)->where('w.payment_type', 'CR')->where('w.branch_id', $branch_id)
            ->where('w.type', '!=', '5')->where('w.type', '!=', '10')->where('w.type', '!=', '12')
            ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
            ->get();
        if (count($receivedChequeMicoTotal) > 0) {
            $receivedChequeMicoTotal = $receivedChequeMicoTotal[0]->amount;
        } else {
            $receivedChequeMicoTotal = 0;
        }
        $receivedChequeLoanTotal = DB::table('samraddh_bank_daybook as w')
            ->select(array(DB::Raw('sum(w.amount) as amount')))
            ->where('w.payment_mode', 1)->where('w.payment_type', 'CR')->where('w.branch_id', $branch_id)
            ->where('w.type', '5')
            ->whereIn('w.sub_type', array("52,53,55,56,57,58"))
            ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
            ->get();
        if (count($receivedChequeLoanTotal) > 0) {
            $receivedChequeLoanTotal = $receivedChequeLoanTotal[0]->amount;
        } else {
            $receivedChequeLoanTotal = 0;
        }
        $total_received_cheque_amount = (float)$receivedChequeLoanTotal + (float)$receivedChequeMicoTotal;
        // ...........................End RECEIVED CHEQUES Detail...................//
        if ($request['export'] == 0) {
            return Excel::download(new ReportBranchBusinessExport($account_head, $branch_id, $startDate, $endDate, $loans, $micros, $totalAmounts, $receivedChequeMicro, $receivedChequeLoan, $total_received_cheque_amount), 'DayBusinessReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.branch.report.export_branch_business_report', compact('account_head', 'startDate', 'endDate', 'branch_id', 'loans', 'micros', 'totalAmounts', 'receivedChequeMicro', 'receivedChequeLoan', 'total_received_cheque_amount'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('DayBusinessReport.pdf');
        }
    }
    public function groupLoanListExport(Request $request)
    {
        $data = Grouploans::with('LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor');
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween('created_at', [$startDate, $endDate]);
            }
            if ($request['branch_id'] != '') {
                $branch_id = $request['branch_id'];
                $data = $data->where('branch_id', '=', $branch_id);
            }
            if ($request['status'] != '') {
                $status = $request['status'];
                $data = $data->where('status', '=', $status);
            }
            if ($request['member_id'] != '') {
                $member_id = $request['member_id'];
                $data = $data->where('associate_member_id', '=', $member_id);
            }
        }
        $data = $data->orderby('id', 'DESC')->get();
        if ($request['export'] == 0) {
            return Excel::download(new GroupLoanReportListBranchExport($data), 'branchGrouploanReport.xlsx');
        }
    }
    public function exportFundTransfer(Request $request)
    {
        if ($request['report_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/fund_transfer_report.csv";
            $fileName = env('APP_EXPORTURL')."asset/fund_transfer_report.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $fundTransfer = \App\Models\FundTransfer::where('branch_id', '=', $branch_id)->where('transfer_type', 0)->where('is_deleted', 0);
        if ($request['status'] != '') {
            $status = $request['status'];
            $fundTransfer = $fundTransfer->where('status', '=', $status);
        }
        if ($request['company_id'] != '') {
            $companyId = $request['company_id'];
            $fundTransfer = $fundTransfer->where('company_id', '=', $companyId);
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $fundTransfer = $fundTransfer->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['report_export'] == 0) {
            $totalResults = $fundTransfer->orderby('created_at', 'DESC')->count();
            $results = $fundTransfer->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
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
                $val['Sr_no'] = $sno;
                if ($row->transfer_type == 0) {
                    $transfer_type = 'Branch to Bank Deposit';
                } else {
                    $transfer_type = 'Bank To Bank';
                }
                $val['Request Type'] = $transfer_type;
                if (getBranchNameByBrachAuto($row->branch_id)) {
                    $branch_name = getBranchNameByBrachAuto($row->branch_id)->name;
                } else {
                    $branch_name = 'N/A';
                }
                $val['Branch Name'] = $branch_name;
                if ($row->company_id != '') {
                    $companyName = Companies::where('id', $row->company_id)->get('name');
                    $val['company'] = $companyName[0]->name;
                } else {
                    $val['company'] = 'N/A';
                }
                $val['Branch Code'] = $row->branch_code;
                //$val['Branch Code']=$row->loan_day_book_amount;
                $val['Branch Cash In Hand Amount'] = $row->micro_day_book_amount;
                if ($row->transfer_type == 0) {
                    $transfer_amount = $row->amount;
                } else {
                    $transfer_amount = $row->transfer_amount;
                }
                $val['Transfer Amount'] = $transfer_amount;
                $val['Transfer Date'] = date("d/m/Y", strtotime(convertDate($row->transfer_date_time)));
                $transfer_mode = "";
                if ($row->transfer_type == 0) {
                    if ($row->transfer_mode == 0) {
                        $transfer_mode = 'Cash';
                    } else {
                        $transfer_mode = 'Cash';
                    }
                } else {
                    if ($row->btb_tranfer_mode == 0) {
                        $transfer_mode = 'Cheque';
                    } else {
                        $transfer_mode = 'Online Transfer';
                    }
                }
                $val['Transfer Mode'] = $transfer_mode;
                $receive_amount = "";
                if ($row->transfer_type == 0) {
                    $receive_amount = $row->amount;
                } else {
                    $receive_amount = $row->receive_amount;
                }
                $val['Receive Amount'] = $receive_amount;
                $receive_bank_name = "";
                if ($row->transfer_mode == 0) {
                    $bank =  SamraddhBank::where('id', $row->to_bank_id)->first('bank_name');;
                    if ($bank) {
                        $receive_bank_name = $bank->bank_name;
                    }
                } else {
                    $bank =  SamraddhBank::where('id', $row->head_office_bank_id)->first('bank_name');;
                    if ($bank) {
                        $receive_bank_name = $bank->bank_name;
                    }
                }
                $val['Receive Bank Name'] = $receive_bank_name;
                $receive_bank_acc = "";
                if ($row->transfer_mode == 0) {
                    $receive_bank_acc = $row->to_bank_account_number;
                } else {
                    $receive_bank_acc = $row->head_office_bank_account_number;
                }
                $val['Receive Bank A\C'] = $receive_bank_acc;
                $val['Request Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                if (getFirstFileData($row->bank_slip_id)) {
                    $file_name = getFirstFileData($row->bank_slip_id)->file_name;
                    $bank_slip = $file_name;
                } else {
                    $bank_slip = 'N/A';
                }
                $val['Bank Slip'] = $bank_slip;
                //$val['approve_reject_date']= date("d/m/Y", strtotime(convertDate($row->created_at)));
                if ($row->status == 0) {
                    $status = 'Pending';
                } elseif ($row->status == 1) {
                    $status = 'Approved';
                } else {
                    $status = '';
                }
                $val['Status'] = $status;
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
        } elseif ($request['report_export'] == 1) {
            $fundTransfer = $fundTransfer->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.fund-transfer.fundTransferExport', compact('fundTransfer'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('fund_transfer_report.pdf');
        }
    }
    public function loan_list_export(Request $request)
    {
        if ($request['loan_recovery_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/loan_list.csv";
            $fileName = env('APP_EXPORTURL')."asset/loan_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $startDate = '';
        $endDate = '';
        $companyId = $input['company_idd'];
        $getBranchId = getUserBranchId(Auth::user()->id);
        $BranchId = $getBranchId->id;
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = Memberloans::with([
            'loan:id,name,loan_type,slug,loan_category',
            'savingAccountCustom',
            'CollectorAccount.member_collector',
            'loanMemberCustom:id,member_id,first_name,last_name,associate_code',
            'memberCompany:id,member_id,customer_id',
            'loanBranch:id,name,branch_code,sector,regan,zone',
            'loanMemberBankDetails:id,member_id,bank_name,account_no,ifsc_code',
            'getOutstanding'=>function($q) {
                $q->with(['loans'=>function($q){
                    $q->where('loan_type','!=','G');
                }]);
            },
            'member',
            'loanSavingAccount'=>function($q) use($companyId){
                $q->whereCompanyId($companyId);
            }
        ])
        ->where('branch_id', $BranchId)
        ->where('company_id', $companyId)
        ->where('loan_type', '!=', 3)
        ->where('is_deleted',0)
        ->orderBy('id', 'DESC');
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->whereHas('loanMemberCustom', function ($query) use ($associate_code) {
                $query->where('members.associate_code', $associate_code);
            });
        }
        if ($request->branch_id != '') {
            $branch_id = $request->branch_id;
            $data = $data->where('branch_id', $branch_id);
        }
        if ($request['associate_name'] != '') {
            $name = $request['associate_name'];
            $data = $data->where(function ($query) use ($name) {
                $query->where('member.first_name', 'LIKE', '%' . $name . '%')->orWhere('member.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(member.first_name," ",member.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['plan'] != '') {
            $planId = $request['plan'];
            $data = $data->where('loan_type', '=', $planId);
        }
        if ($request['loan_account_number'] != '') {
            $loan_account_number = $request['loan_account_number'];
            $data = $data->where('account_number', '=', $loan_account_number);
        }
        if ($request['member_name'] != '') {
            $name = $request['member_name'];
            $data = $data->whereHas('member', function ($query) use ($name) {
                $query->where('first_name', 'LIKE', '%' . $name . '%')
                ->orWhere('last_name', 'LIKE', '%' . $name . '%')
                ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['member_id'] != '') {
            $member_id = $request['member_id'];
            $data = $data->whereHas('memberCompany', function ($query) use ($member_id) {
                $query->where('member_id', 'LIKE', '%' . $member_id . '%');
            });
        }
        if ($request['associate_code'] != '') {
            $associateCode = $request['associate_code'];
            $data = $data->whereHas('loanMemberCustom', function ($query) use ($associateCode) {
                $query->where('members.associate_code', $associateCode );
            });
        }
        if ($request['customer_idd'] != '') {
            $customer_idd = $request['customer_idd'];
            $data = $data->whereHas('member', function ($query) use ($customer_idd) {
                $query->where('member_id', $customer_idd );
            });
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', '=', $status);
        }
        if ($request['date_from'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['date_from'])));
            if ($request['date_to'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['date_to'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
        } else {
            $data = $data->where('status', '=', 3);
        }
        if ($request['loan_recovery_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count();
            $results = $data->offset($start)->limit($limit)->get();
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
            $rowReturn = array();
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['BR NAME'] = $row['loanBranch']->name;
                $val['BR CODE'] = $row['loanBranch']->branch_code;
                $val['SO NAME'] = $row['loanBranch']->sector;
                $val['RO NAME'] = $regan = $row['loanBranch']->regan;
                $val['ZO NAME'] = $row['loanBranch']->zone;
                $val['A/C NO'] = $row->account_number;
                $val['MEMBER NAME'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                $val['MEMBER ID'] =  $row['memberCompany']->member_id;
                $val['CUSTOMER ID'] =  $row['memberCompany']->member->member_id;

                $applicationDate = date('Y-m-d');
                $val['Total Deposit'] = getAllDeposit($row['loanMember']->id, $applicationDate);
                $plan_name = '';
                if ($row->loan_type == 1) {
                    $plan_name = 'Personal Loan';
                } elseif ($row->loan_type == 2) {
                    $plan_name = 'Staff Loan(SL)';
                } elseif ($row->loan_type == 3) {
                    $plan_name = 'Group Loan';
                } elseif ($row->loan_type == 4) {
                    $plan_name = 'Loan against Investment plan(DL) ';
                }
                $val['LOAN TYPE'] = $plan_name;
                $tenure = '';
                if ($row->emi_option == 1) {
                    $tenure =  $row->emi_period . ' Months';
                } elseif ($row->emi_option == 2) {
                    $tenure =  $row->emi_period . ' Weeks';
                } elseif ($row->emi_option == 3) {
                    $tenure =  $row->emi_period . ' Days';
                }
                $val['TENURE'] = $tenure;
                $val['EMI AMOUNT'] = $row->emi_amount;
                $val['TRANSFER AMOUNT'] = $row->transfer_amount;
                $val['TRANSFER DATE'] = date("d/m/Y", strtotime(convertDate($row->approve_date)));
                $val['LOAN AMOUNT'] = $row->amount;
                $file_charge = '';
                if ($row->file_charges) {
                    $file_charge =  $row->file_charges;
                } else {
                    $file_charge =  'N/A';
                }
                $val['INSURANCE CHARGE'] = $row->insurance_charge;
                $val['FILE CHARGE'] = $file_charge;
                $val['ECS REF NO'] = $row->ecs_ref_no ?? '';
                $val['ECS CHARGE'] = $row->ecs_charges ?? 0.00;
                $file_charges_payment_mode = 'N/A';
                if ($row->file_charge_type) {
                    $file_charges_payment_mode = 'Loan Amount';
                } else {
                    $file_charges_payment_mode = 'Cash';
                }
                $val['FILE CHARGES PAYMENT MODE'] = $file_charges_payment_mode;
                $outstanding_amount = '';
                $totalbalance = $row->emi_period * $row->emi_amount;
                $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;
                $outstandingAmount = isset($row['getOutstanding']->out_standing_amount)
                ? ($row['getOutstanding']->out_standing_amount > 0 ? $row['getOutstanding']->out_standing_amount : 0)
                : $row['amount'];
                $val['OUTSTANDING AMOUNT'] = $outstandingAmount;
                $last_recovery_date = '';
                if ($row->approve_date) {
                    if ($row->emi_option == 1) {
                        $last_recovery_date = date('d/m/Y', strtotime("+" . $row->emi_period . " months", strtotime($row->approve_date)));
                    } elseif ($row->emi_option == 2) {
                        $days = $row->emi_period * 7;
                        $start_date = $row->approve_date;
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    } elseif ($row->emi_option == 3) {
                        $days = $row->emi_period;
                        $start_date = $row->approve_date;
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    }
                } else {
                    $last_recovery_date = 'N/A';
                }
                $val['LAST RECOVERY DATE'] = $last_recovery_date;

                $member = Member::where('id', $row->associate_member_id)->first(['id', 'first_name', 'last_name','associate_no']);
                $val['ASSOCIATE CODE'] = $member ? $member->associate_no : 'N/A';
                $associate_name = '';
                $associate_name = $member->first_name . ' ' . $member->last_name;
                $val['ASSOCIATE NAME'] = $associate_name;
                $applicationBankDetail = loanApplicatBankDetail($row->id);
                if (isset($applicationBankDetail->bank_name)) {
                    $bankName = $applicationBankDetail->bank_name;
                } else {
                    $bankName = 'N/A';
                }
                $val['BANK NAME'] = $bankName;
                if (isset($applicationBankDetail->bank_account_number)) {
                    $bankAccount = $applicationBankDetail->bank_account_number;
                } else {
                    $bankAccount = 'N/A';
                }
                $val['BANK ACCOUNT NUMBER'] = $bankAccount;
                if (isset($applicationBankDetail->ifsc_code)) {
                    $ifscCode = $applicationBankDetail->ifsc_code;
                } else {
                    $ifscCode = 'N/A';
                }
                $val['IFSC CODE'] = $ifscCode;
                $val['TOTAL PAYMENT'] = loanOutsandingAmount($row->id, $row->account_number);
                $approve_date = '';
                $approve_dated = '';
                if ($row['approve_date']) {
                    $approve_date =   date("d/m/Y", strtotime($row['approve_date']));
                } else {
                    $approve_date = 'N/A';
                }
                $val['SANCTION DATE'] = $approve_date;
                if ($row['approved_date']) {
                    $approve_dated =   date("d/m/Y", strtotime($row['approved_date']));
                } else {
                    $approve_dated = 'N/A';
                }
                $val['APPROVED DATE'] = $approve_dated;
                $val['APPLICATION DATE'] = date("d/m/Y", strtotime($row->created_at));
                if (isset($row['CollectorAccount']['member_collector']['associate_no'])) {
                    $val['COLLECTOR CODE'] = $row['CollectorAccount']['member_collector']['associate_no'];
                } else {
                    $val['COLLECTOR CODE'] = "N/A";
                }
                if (isset($row['CollectorAccount']['member_collector']['first_name'])) {
                    $val['COLLECTOR NAME'] = $row['CollectorAccount']['member_collector']['first_name'] . ' ' . $row['CollectorAccount']['member_collector']['last_name'];
                } else {
                    $val['COLLECTOR NAME'] = "N/A";
                }
                $status = '';
                if ($row->status == 0) {
                    $status =  'pending';
                } elseif ($row->status == 3) {
                    $status = 'clear';
                } elseif ($row->status == 4) {
                    $status =  'due';
                } else if ($row->status == 5) {
                    $status = 'Rejected';
                } else if ($row->status == 6) {
                    $status = 'Hold';
                } else if ($row->status == 7) {
                    $status = 'Approved but hold';
                } else if ($row->status == 8) {
                    $status = 'Cancel';
                }
                $val['STATUS'] = $status;
                $val['RUNNING LOAN ACCOUNT NUMBER'] = getMemberCurrentRunningLoan($row['customer_id'], $row['loan']['loan_type'] == "L" ? true : false, $row['account_number']);
            $val['RUNNING LOAN CLOSING AMOUNT'] = getMemberCurrentRunningClosingAmount($row['customer_id'], $row['loan']['loan_type'] == "L" ? true : false, $row['account_number']);
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
            // Make sure nothing else is sent, our file is done
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['loan_recovery_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.branch.loan_management.export_loan_list', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('LoanList.pdf');
        }
    }
    public function group_loan_list_export(Request $request)
    {
            $token = session()->get('_token');
            $data = Cache::get('groupLoanExportlistBranch'.$token);
            $count = Cache::get('groupLoanExportlist_countBranch'.$token);
            $input = $request->all();
            $start = $_POST["start"];
            $limit = $_POST["limit"];
            $returnURL = URL::to('/') . "/asset/GroupLoanBranchLoanExport.csv";
            $fileName = env('APP_EXPORTURL') . "asset/GroupLoanBranchLoanExport.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
            $totalResults = $count;
            $results=$data;
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

            $sno=$_POST['start'];
            $results = array_slice($results,$start,$limit);
            foreach ($results as $row) {

                $sno++;
                $val['S/N'] = $sno;
                $val['BR NAME'] = $row['gloan_branch']['name'];
                $val['BR CODE'] = $row['gloan_branch']['branch_code'];
                $val['SO NAME'] = $row['gloan_branch']['sector'];
                $val['RO NAME'] = $regan = $row['gloan_branch']['regan'];
                $val['ZO NAME'] = $row['gloan_branch']['zone'];
                $val['A/C NO'] = $row['account_number'];
                $val['GROUP LOAN COMMON ID'] = $row['group_loan_common_id'];
                $val['MEMBER NAME'] = $row['member']['first_name'] . ' ' . $row['member']['last_name'];
                $val['CUSTOMER ID'] = $row['member']['member_id'];
                // pd($row);
                $val['MEMBER ID'] = $row['loan_member_companyid']['member_id'];
                $applicationDate = date('Y-m-d');
                $val['Total Deposit'] = getAllDeposit($row['member']['id'], $applicationDate);
                $plan_name = '';
                if ($row['loan_type'] == 1) {
                    $plan_name = 'Personal Loan';
                } elseif ($row['loan_type'] == 2) {
                    $plan_name = 'Staff Loan(SL)';
                } elseif ($row['loan_type'] == 3) {
                    $plan_name = 'Group Loan';
                } elseif ($row['loan_type'] == 4) {
                    $plan_name = 'Loan against Investment plan(DL) ';
                }
                $val['LOAN TYPE'] = $plan_name;
                $tenure = '';
                if ($row['emi_option'] == 1) {
                    $tenure =  $row['emi_period'] . ' Months';
                } elseif ($row['emi_option'] == 2) {
                    $tenure =  $row['emi_period'] . ' Weeks';
                } elseif ($row['emi_option'] == 3) {
                    $tenure =  $row['emi_period'] . ' Days';
                }
                $val['TENURE'] = $tenure;
                $val['EMI AMOUNT'] = $row['emi_amount'];
                $val['TRANSFER AMOUNT'] = $row['transfer_amount'];
                $val['TRANSFER DATE'] = $row['approve_date'];

                $val['LOAN AMOUNT'] = $row['amount'];
                $file_charges = '';
                if ($row['file_charges']) {
                    $file_charge =  $row['file_charges'];
                } else {
                    $file_charge =  'N/A';
                }
                $val['FILE CHARGE'] = $file_charge;
                $val['INSURANCE CHARGE'] = $row['insurance_charge'];
                $val['ECS REF NO'] = $row['ecs_ref_no'] ?? '';
                $val['ECS CHARGE'] = $row['ecs_charges'] ?? 0.00;
                $file_charges_payment_mode = 'N/A';
                if ($row['file_charge_type']) {
                    $file_charges_payment_mode = 'Loan Amount';
                } else {
                    $file_charges_payment_mode = 'Cash';
                }
                $val['FILE CHARGES PAYMENT MODE'] = $file_charges_payment_mode;
                $outstanding_amount = '';
                $totalbalance = $row['emi_period'] * $row['emi_amount'];
                $Finaloutstanding_amount = $totalbalance - $row['received_emi_amount'];

                $outstandingAmount = isset($row['get_outstanding']['out_standing_amount'])
                ? ($row['get_outstanding']['out_standing_amount'] > 0 ? $row['get_outstanding']['out_standing_amount'] : 0)
                : $row['amount'];
                $val['OUTSTANDING AMOUNT'] = $outstandingAmount;
                $last_recovery_date = '';
                if ($row['approve_date']) {
                    if ($row['emi_option'] == 1) {
                        $last_recovery_date = date('d/m/Y', strtotime("+" . $row['emi_period'] . " months", strtotime($row['approve_date'])));
                    } elseif ($row['emi_option'] == 2) {
                        $days = $row['emi_period'] * 7;
                        $start_date = $row['approve_date'];
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    } elseif ($row['emi_option'] == 3) {
                        $days = $row['emi_period'];
                        $start_date = $row['approve_date'];
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                    }
                } else {
                    $last_recovery_date = 'N/A';
                }
                $val['LAST RECOVERY DATE'] = $last_recovery_date;
                $associate_code =  getMemberCompanyDataNew($row['associate_member_id']);
                $val['ASSOCIATE CODE'] = $associate_code->member->associate_no;
                $member = Member::where('id', $row['associate_member_id'])->first(['id', 'first_name', 'last_name']);
                $associate_name = $member->first_name . ' ' . $member->last_name;
                $val['ASSOCIATE NAME'] = $associate_name;
                $val['BANK NAME'] = (isset($row['loan_member_bank_details2'])) ? $row['loan_member_bank_details2']['bank_name'] : 'N/A';

                $val['BANK ACCOUNT NUMBER'] = (isset($row['loan_member_bank_details2'])) ? $row['loan_member_bank_details2']['account_no'] : 'N/A';

                $val['IFSC CODE'] = (isset($row['loan_member_bank_details2'])) ? $row['loan_member_bank_details2']['ifsc_code'] : 'N/A';
                $val['TOTAL PAYMENT'] = loanOutsandingAmount($row['id'],$row['account_number']);
                $approve_date = '';
                if ($row['approve_date']) {
                    $approve_date =   date("d/m/Y", strtotime($row['approve_date']));
                } else {
                    $approve_date = 'N/A';
                }
                $val['SANCTION DATE'] = $approve_date;
                if ($row['approved_date']) {
                    $approve_dated =   date("d/m/Y", strtotime($row['approved_date']));
                } else {
                    $approve_dated = 'N/A';
                }
                $val['APPROVED DATE'] = $approve_dated;
                $val['APPLICATION DATE'] = date("d/m/Y", strtotime($row['created_at']));
                $status = '';
                if ($row['status'] == 0) {
                    $status = 'Pending';
                } else if ($row['status'] == 1) {
                    $status = 'Approved';
                } else if ($row['status'] == 3) {
                    $status = 'Clear';
                } else if ($row['status'] == 4) {
                    $status = 'Due';
                } else if ($row['status'] == 5) {
                    $status = 'Rejected';
                } else if ($row['status'] == 6) {
                    $status = 'Hold';
                } else if ($row['status'] == 7) {
                    $status = 'Approved but hold';
                }
                $val['STATUS'] = $status;
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
            // Make sure nothing else is sent, our file is done
            exit;
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        // } elseif ($request['group_loan_recovery_export'] == 1) {
        //     $data = $data->orderby('id', 'DESC')->get();
        //     $pdf = PDF::loadView('templates.branch.loan_management.export_group_loan_list', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
        //     $pdf->save(storage_path() . '_filename.pdf');
        //     return $pdf->download('GroupLoanList.pdf');
        // }
    }

    public function exportRenewalList(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('renewalexport_list_branch'.$token);
        $count = Cache::get('renewalexport_count_branch'.$token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/renewalexport_countreport.csv";
        $fileName = env('APP_EXPORTURL')."report/renewalexport_countreport.csv";
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
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        $payment_mode = [
            0 => "Cash",
            1 => "Cheque",
            2 => "DD",
            3 => "Online",
            4 => "By Saving Account",
            5 => "From Loan Amount"
        ];
        foreach ($record as $row) {
            $planId = $row['investment'] ? $row['investment']['plan_id'] : 'N/A';
            $tenure = '';
            if ($planId == 1) {
                $tenure = 'N/A';
            } else {
                $tenure = $row['investment']['tenure'] . ' Year';
            }
            $planName = '';
            if ($planId > 0) {
                $PlanDetail = getPlanDetail($planId);
                if (!empty($PlanDetail)) {
                    $planName = $PlanDetail->toArray()['name'];
                }
            }
            $sno++;
            $val['S/N'] = $sno;
            $val['CREATED DATE'] = date("d/m/Y", strtotime($row['created_at']));
            $val['TRANSACTION BY'] = ($row['is_app'] == 1 ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software'));
            $val['compnay'] = $row['company']['name'];
            $val['BR NAME'] = $row['dbranch']['name'];
            $val['BR CODE'] = $row['dbranch']['branch_code'];
            $val['SO NAME'] = $row['dbranch']['sector'];
            $val['RO NAME'] = $row['dbranch']['sector'];
            $val['ZO NAME'] = $row['dbranch']['zone'];
            $val['CUSTOMER ID'] = $row['member']['member_id'] ?? 'N/A';
            $val['MEMBER ID'] = $row['member_company']['member_id'] ?? 'N/A';
            $val['ACCOUNT NO'] = $row['account_no'] ?? 'N/A';
            $val['MEMBER ACCOUNT(HOLDER NAME)'] = $row['member'] ? $row['member']['first_name']??'' . ' ' . $row['member']['last_name']??'' : 'N/A';
            $val['PLAN'] = $planName;
            $val['TENURE'] = $tenure;
            $val['AMOUNT'] = $row['amount'];
            $val['ASSOCIATE CODE'] = (isset($row['associate_member'])) ? ($row['associate_member']['associate_no']) : 'N/A';
            $val['ASSOCIATE NAME'] = (isset($row['associate_member'])) ? ($row['associate_member']['first_name'] . ' ' . $row['associate_member']['last_name']) : 'N/A';
            $val['PAYMENT CODE'] = $payment_mode[$row['payment_mode']];
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

    public function daybookReportExportDublicate(Request $request)
    {
        //dd($request->all());
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
        } else {
            $startDate = '';
        }
        if ($request['end_date'] != '') {
            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
        } else {
            $endDate = '';
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
        } else {
            $branch_id = '';
        }
        if ($request['company'] != '') {
            $company_id = $request['company'];
        } else {
            $company_id = '';
        }

        $cash_in_hand['CR'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $record = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->get();
        foreach ($record as $key => $value) {
            $rec = 0;
            if ($value->type == 3 && $value->sub_type == 30) {
                $rec  = \App\Models\Daybook::where('id', $value->type_transaction_id)->where('company_id', $company_id)->where('branch_id', $branch_id)->first();
                $rec = $rec->is_eli;
                if ($rec == 1) {
                    $cash_in_hand['CR'] = $cash_in_hand['CR'] - $value->amount;
                }
            }
        }
        $cash_in_hand['DR'] = BranchDaybook::where(function ($q) {
            $q->where('sub_type', '!=', 30)->orwhere('sub_type', '=', NULL);
        })->where('payment_mode', 0)->where('company_id', $company_id)->where('branch_id', $branch_id)->where('payment_type', 'DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $cheque['CR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $cheque['DR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $bank = SamraddhBank::with('bankAccount')->where('company_id', $company_id)->get();
        //     $data=DB::table('branch_daybook')->select('branch_daybook.*','branch_daybook.created_at as record_created_date','branch_daybook.payment_mode as branch_payment_mode','branch_daybook.payment_type as branch_payment_type','branch_daybook.member_id as branch_member_id','branch_daybook.associate_id as branch_associate_id','member_investments.id','branch_daybook.id as btid','branch_daybook.company_id')->leftjoin('member_investments','member_investments.id','branch_daybook.type_id')->where('branch_daybook.company_id',$company_id)->where('branch_daybook.branch_id',$branch_id)->whereBetween('branch_daybook.entry_date',[$startDate, $endDate])->where('branch_daybook.is_deleted',0)->orderBy('branch_daybook.entry_date','ASC')->get();

        $balance = '';
        $getBranchOpening = getBranchOpeningDetail($branch_id);
        $currentdate = date('Y-m-d');
        if ($getBranchOpening->date == $startDate) {
            $balance = $getBranchOpening->total_amount;
        }
        if ($getBranchOpening->date < $startDate) {
            if ($getBranchOpening->date != '') {
                $getBranchTotalBalance = getBranchTotalBalanceAllTran($startDate, $getBranchOpening->date, $getBranchOpening->total_amount, $branch_id, $company_id);
            } else {
                $getBranchTotalBalance = getBranchTotalBalanceAllTran($startDate, $currentdate, $getBranchOpening->total_amount, $branch_id, $company_id);
            }
            $balance = $getBranchTotalBalance;
        }
       // $data = Cache::get('daybook_transaction');

        $data =  BranchDaybook::/*select('id','type','sub_type','type_id','type_transaction_id','entry_date','amount','opening_balance','closing_balance','transction_bank_to','description_cr','description_dr;)*/with(['member_investment' => function ($q) {
            $q->select('id', 'account_number', 'plan_id', 'member_id', 'associate_id', 'customer_id')->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])->with('ssb')->with('plan')->with('associateMember');
        }])
            ->when('type' == 5, function ($q) {
                return $q->with(['member_loan' => function ($q) {
                    $q->select('id', 'applicant_id', 'loan_type')->with('loanMember');
                }]);
            })->when('type' == 5, function ($q) {
                $q->with(['group_member_loan' => function ($q) {
                    $q->select('id', 'applicant_id', 'loan_type', 'member_loan_id', 'member_id')->with('loanMember');
                }]);
            })
            ->with(['demand_advice' => function ($q) {
                $q->select('id', 'investment_id', 'employee_name')->with(['investment' => function ($q) {
                    $q->select('id', 'account_number', 'plan_id', 'member_id', 'account_number', 'customer_id')->with('plan')
                        ->with('member');
                }])->with(['expenses' => function ($qa) {
                    $qa->select('id')->with('advices');
                }]);
            }])
            ->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])->with('receivedvoucherbytype_id')->with('receivedvoucherbytype_transaction_id')
            ->with(['SavingAccountTranscation' => function ($q) {
                $q->with(['savingAc' => function ($q) {
                    $q->select('id', 'account_no')->with('ssbMember')->with('associate');
                }]);
            }])
            ->when('type' == 7, function ($q) {
                return $q->with(['SamraddhBank' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with(['VoucherSamraddhBank' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with(['VoucherSamraddhBankbank_ac_id' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })->when('type' == 1, function ($q) {
                return $q->with('memberMemberId');
            })->when('type' == 2, function ($q) {
                return $q->with('memberMemberId');
            })->with('accountHead')->with(['loan_from_bank' => function ($q) {
                $q->with('loan_emi');
            }])->with('company_bound')->with(['bill_expense' => function ($q) {
                $q->with('head')->with('subb_head')->with('subb_head2');
            }])
            ->with('BillExpense')
            ->with(['EmployeeSalaryBytype_id' => function ($q) {
                $q->with('salary_employee');
            }])
            ->with(['RentPayment' => function ($q) {
                $q->with('rentLib');
            }])
            ->with(['RentLiabilityLedger' => function ($q) {
                $q->with('rentLib');
            }])
            ->with(['EmployeeSalary' => function ($q) {
                $q->with('salary_employee');
            }])->with('associateMember')->with('SavingAccountTranscationtype_trans_id')
            ->where('company_id', $company_id)
            ->where('branch_id', $branch_id)->whereBetween('entry_date', [$startDate, $endDate])->where('is_deleted', 0)->orderBy('entry_date', 'ASC')->get();

        $payment_mode[0] = 'CASH';
        $payment_mode[1] = 'CHEQUE';
        $payment_mode[2] = 'ONLINE TRANSFER';
        $payment_mode[3] = 'SSB';
        $payment_mode[4] = 'AUTO TRANSFER';
        $payment_mode[5] = 'Loan';
        $payment_mode[6] = 'JV';
        $payment_mode[8] = 'SSb Debit Cron';
        $types = getTransactionTypeCustom();
        $transactionCreatedBy = array();
        $transactionCreatedBy[0] = 'Software';
        $transactionCreatedBy[1] = 'Associate App';
        $transactionCreatedBy[2] = 'E-Passbook App';

        $rowReturn = array();
        foreach ($data as $index => $value) {
            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name = 'N/A';
            $a_name = 'N/A';
            $data = getCompleteDetail($value);
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $memberName = $data['memberName'];
            $a_name = $data['associate_name'];
            $f1 = 0;
            $f2 = 0;
            if ($value->type != 21) {
                if (array_key_exists($value->type . '_' . $value->sub_type, $types)) {
                    $type = $types[$value->type . '_' . $value->sub_type];
                }
            }
            if (!empty($value->company_id)) {
                $companyname = \App\Models\Companies::where('id', $value->company_id)->value('name');
            } else {
                $companyname = 'N/A';
            }
            if ($value->type == 21 && $value->sub_type == '') {
                $record = ReceivedVoucher::where('id', $value->type_id)->first();
                if ($record) {
                    $type = $record->particular;
                } else {
                    $type = "N/A";
                }
            }
            if ($value->type == 22 || $value->type == 23) {
                if ($value->sub_type == 222) {
                    $type = $value->description;
                }
            }
            // Member Name, Member Account and Member Id
            $is_eli = 0;
            if ($value->sub_type == 30) {
                $is_eli = Daybook::where('id', $value->type_transaction_id)->first();
                if (isset($is_eli->is_eli)) {
                    $is_eli = $is_eli->is_eli;
                }
            }


            if ($value->payment_mode == 6) {
                $rentPaymentDetail = $value['RentLiabilityLedger'];
                $salaryDetail = $value['EmployeeSalary'];
            } else {
                $rentPaymentDetail = $value['RentPayment'];
                $salaryDetail = $value['EmployeeSalaryBytype_id'];
            }
            $cr_amount = 0;
            $dr_amnt = 0;
            if ($value->payment_type == 'CR') {
                $cr_amount = number_format((float)$value->amount, 2, '.', '');
            }
            if ($value->payment_type == 'DR') {
                $dr_amnt =  number_format((float)$value->amount, 2, '.', '');
            }
            // Balance
            if ($value->payment_mode == 0 && $is_eli == 0) {
                $balance = number_format((float)$balance, 2, '.', '');
            }
            // Ref Number
            if ($value->payment_mode == 0) {
                $ref_no = 'N/A';
            } elseif ($value->payment_mode == 1) {
                $ref_no = $value->cheque_no;
            } elseif ($value->payment_mode == 2) {
                $ref_no = $value->transction_no;
            } elseif ($value->payment_mode == 3) {
                $ref_no = $value->v_no;
            } elseif ($value->payment_mode == 6) {
                $ref_no = $value->jv_unique_id;
            } else {
                $ref_no = 'N/A';
            }
            // Payment Mode
            if ($value->sub_type == 30) {
                $pay_mode = 'ELI';
            } else
                if ($value->payment_mode == 0) {
                $pay_mode = 'CASH';
            } elseif ($value->payment_mode == 1) {
                $pay_mode = 'CHEQUE';
            } elseif ($value->payment_mode == 2) {
                $pay_mode = 'ONLINE TRANSFER';
            } elseif ($value->payment_mode == 3) {
                $pay_mode = 'SSB';
            } elseif ($value->payment_mode == 4) {
                $pay_mode = 'AUTO TRANSFER';
            } elseif ($value->payment_mode == 5) {
                $pay_mode = 'Loan';
            } elseif ($value->payment_mode == 6) {
                $pay_mode = 'JV';
            }
            if ($value->entry_date) {
                $date = date("d/m/Y", strtotime(convertDate($value->entry_date)));
            } else {
                $date = 'N/A';
            }
            $tag = '';
            if ($value->type = 3) {
                if ($value->sub_type == 31) {
                    $tag = 'N';
                }
                if ($value->sub_type == 32) {
                    $tag = 'R';
                }
            }
            if ($value->type == 4) {
                if ($value->sub_type == 41) {
                    $tag = 'N';
                }
                if ($value->sub_type == 42) {
                    $tag = 'R';
                }
                if ($value->sub_type == 43) {
                    $tag = 'W';
                }
            }
            if ($value->type == 5) {
                if ($value->sub_type == 51) {
                    $tag = 'LD';
                }
                if ($value->sub_type == 52) {
                    $tag = 'L';
                }
                if ($value->sub_type == 54) {
                    $tag = 'LD';
                }
                if ($value->sub_type == 55) {
                    $tag = 'L';
                }
            }
            if ($value->type == 7) {
                $tag = 'B';
            }
            if ($value->type == 13) {
                if ($value->sub_type == 131) {
                    $tag = 'E';
                }
                if ($value->sub_type == 133) {
                    $tag = 'M';
                }
                if ($value->sub_type == 134) {
                    $tag = 'M';
                }
                if ($value->sub_type == 135) {
                    $tag = 'M';
                }
                if ($value->sub_type == 136) {
                    $tag = 'M';
                }
                if ($value->sub_type == 137) {
                    $tag = 'M';
                }
            }
            if ($value->payment_type == 'CR') {
                if ($value->payment_mode == 0 && $is_eli == 0) {
                    $balance = $balance + $value->amount;
                }
            }
            if ($value->payment_type == 'DR') {
                if ($value->payment_mode == 0 && $is_eli == 0) {
                    $balance = $balance - $value->amount;
                }
            }

            $val['tr_date'] = $date;
            $val['bt_id'] = $value->id;
            $val['tran_by'] = (($value->is_app) ? ($value->is_app == 1 ? 'Associate' : 'E-passbook') : 'Software');
            $val['member_account'] = $memberAccount;
            $val['company_name']  = $companyname;
            $val['plan_name'] = $plan_name;
            $val['memberName'] = $memberName;
            $val['a_name'] = $a_name;
            $val['type'] = $type;
            $val['description_cr'] = $value->description_cr;
            $val['description_dr'] = $value->description_dr;
            $val['cr_amnt'] = $cr_amount;
            $val['dr_amnt'] = $dr_amnt;
            if ($value->payment_mode == 0 && $is_eli == 0) {
                $val['balance'] = $balance;
            } else {
                $val['balance'] = '';
            }
            $val['ref_no'] = $ref_no;
            $val['pay_mode'] = $pay_mode;
            $val['tag'] = $tag;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new DublicateDaybookReportBranchExport($rowReturn, $cash_in_hand, $cheque, $bank, $branch_id, $startDate, $endDate, $company_id), 'BdublicateDaybookBranchReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.branch.report.dublicate.export_daybook_report', compact('rowReturn', 'cash_in_hand', 'cheque', 'bank', 'branch_id', 'startDate', 'endDate', 'company_id'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('BdublicateDaybookBranchReport.pdf');
        }
    }
    public function loanTransactionExportList(Request $request)
    {

            $token = session()->get('_token');
            $data = Cache::get('loan_transaction_listbranch'.$token);
            $count = Cache::get('loan_transaction__countbranch'.$token);
            $input = $request->all();
            $start = $_POST["start"];
            $limit = $_POST["limit"];
            $returnURL = URL::to('/') . "/asset/LoanTransactionsList.csv";
            $fileName = env('APP_EXPORTURL') . "asset/LoanTransactionsList.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
            $totalResults = $count;
            $results=$data;
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

            $sno=$_POST['start'];
            $results = array_slice($results,$start,$limit);
            foreach ($results as $row) {
                switch($row['loan_type']){
                    case 1 :
                        $plan_name = 'Personal Loan';
                        break;
                    case 2 :
                        $plan_name = 'Staff Loan(SL)';
                        break;
                    case 3 :
                        $plan_name = 'Group Loan';
                        break;
                    case 4 :
                        $plan_name = 'Loan against Investment plan(DL)';
                        break;
                    default :
                        $plan_name = 'N/A';
                        break;
                }
                // tenure
                $emi_tenure = 'N/A';
                if (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 1) {
                    $emi_tenure = $row['member_loan']['emi_period'] . " Months";
                } elseif (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 2) {
                    $emi_tenure = $row['member_loan']['emi_period'] . " Weeks";
                } elseif (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 3) {
                    $emi_tenure = $row['member_loan']['emi_period'] . " Days";
                }

                if (isset($row['loan_member'])) {
                    $member_id = $row['loan_member']['member_id'];
                    $member_name = $row['loan_member']['first_name'] . ' ' . $row['loan_member']['last_name'];
                }

                switch ($row['payment_mode']) {
                    case 0:
                        $payment_mode = 'Cash';
                        break;
                    case 1:
                        $payment_mode = 'Cheque';
                        break;
                    case 2:
                        $payment_mode = 'DD';
                        break;
                    case 3:
                        $payment_mode = 'Online Transaction';
                        break;
                    case 4:
                        $payment_mode = 'By Saving Account';
                        break;
                    default:
                        $payment_mode = 'Cash';
                        break;
                }
                $sno++;
                $val['S/N'] = $sno;
                $val['Created Date'] = date("d/m/Y", strtotime($row['created_at']));
                $val['Company'] = $row['company'] ? $row['company']['name'] : 'N/A';

                $val['Customer Id'] = $member_id;
                $val['Member Id'] = $row['loan_member']['member_company']['member_id'] ?? 'N/A';




                $val['Account Number'] = $row['account_number']??0.00;
                $val['Member Name'] = $member_name;
                $val['Plan Name'] = $plan_name;
                $val['Tenure'] = $emi_tenure;
                $val['EMI Amount'] = $row['deposit']??0.00;
                $val['Loan Sub Type'] = $row['loan_sub_type'] == 0 ? 'EMI' : 'Late Penalty' ;
                $val['Associate Code'] = (isset($row['loan_member_associate'])) ? $row['loan_member_associate']['associate_no'] : 'N/A' ;
                $val['Associate Name'] = (isset($row['loan_member_associate'])) ? $row['loan_member_associate']['first_name'] . ' ' . $row['loan_member_associate']['last_name'] : 'N/A' ;
                $val['Payment Mode'] = $payment_mode;
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
    }
    public function AssociateCollectionReportExport(Request $request)
    {

        $input = $request->all();
        $company_id = $input['company_id'];
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/associatecollectionreport.csv";
        $fileName = env('APP_EXPORTURL') . "report/associatecollectionreport.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            $fillter = 0;
            $startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
            $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                }
            }
            $branch_id = getUserBranchId(Auth::user()->id)->id;
            if (isset($request['branch_id']) && $request['branch_id'] != '') {
                $branch_id = $request['branch_id'];
            }
            $associate_code = '';
            if (isset($request['associate_code']) && $request['associate_code'] != '') {
                $associate_code = $request['associate_code'];
            }
        }
        $branch_id = getUserBranchId(Auth::user()->id)->id;
        //$dataNew=$data[0]->id;
        $branchId = $branch_id;
        $associteCode = $associate_code;
        $pageNo = 0;
        $_POST['length'] = 20;
        if ($_POST['length']) {
            $perPageRecord = $_POST['length'];
        }
        if ($_POST['start'] == 0) {
            $pageNo = 1;
        } else {
            $pageGet = $_POST['start'] / $_POST['length'];
            $pageNo = $pageGet + 1;
        }
        $toDay = date("d", strtotime($startDate));
        $toMonth = date("m", strtotime($startDate));
        $toYear = date("Y", strtotime($startDate));
        $fromDay = date("d", strtotime($endDate));
        $fromMonth = date("m", strtotime($endDate));
        $fromYear = date("Y", strtotime($endDate));
        // $branchId=1;$associteCode='';$toDay='1';$toMonth='';$toYear='';$fromDay='';$fromMonth='';$fromYear='';$fillter='';
        $count = DB::select('call associteCollectionListCount(?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, $fillter]);
        $totalResults = count($count);
        $data = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, 0, $company_id]);
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
        foreach ($data as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Branch Name'] = $row->name;
            $val['Branch Code'] = $row->branch_code;
            $val['Branch Zone'] = $row->zone;
            $val['Branch Sector'] = $row->sector;
            $val['Branch Region'] = $row->regan;
            $val['Associate Code'] = $row->associate_no;
            $val['Associate Name'] = $row->first_name . ' ' . $row->last_name;
            $val['Collection Amount'] = $row->totalsum;
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
    public function export_update_15g(Request $request)
    {
		$companyId = $request->customerId;
		$memberId = $request->member_id;
        $data = Form15G::where('year','!=','NULL')
			->with([
				'member:id,first_name,last_name',
				'memberCompany:id,customer_id',
				'company:id,name'
			])
			->where('member_id',$memberId)->get();
        if ($request['export'] == 0) {
            return Excel::download(new Update15GExport($data), 'update_15g.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.branch.form_g.export_update_15g', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('update_15g.pdf');
        }
    }
}
