<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Exports\RenewalListExport;
use Illuminate\Support\Facades\Hash;
use App\Models\SavingAccount;
use App\Models\CompanyBound;
use App\Http\Controllers\Controller;
use URL;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Files;
use DateTime;
use App\Models\TransactionType;
use App\Models\VendorCategory;
use App\Models\Employee;
use App\Models\EmployeeApplication;
use App\Models\JvJournals;
use App\Models\JvJournalHeads;
use App\Exports\BalanceSheetExport;
use App\Exports\ProfitLossExport;
use App\Models\Form15G;
use App\Models\VendorBill;
use App\Models\AccountHeads;
use App\Models\SamraddhBankAccount;
use App\Exports\TdsDepositeExport;
use App\Exports\JVJournalExportReport;
use App\Exports\FuelChargeExportReport;
use App\Exports\RentTypeExportLatePaneltyReport;
use App\Exports\RentTypeExportInterestOnLoanReport;
use App\Exports\ReportAssociateBusCompareAdminExport;
use App\Models\CorrectionRequests;
use App\Exports\ProfitLossExportPanelReport;
use App\Exports\BalanceSheetReportExportDetailsDataExport;
use App\Models\ReceivedVoucher;
use App\Models\RentLiability;
use App\Models\LoanDayBooks;
use App\Models\LoanFromBank;
use App\Models\TdsDeductionSetting;
use App\Models\SavingAccountTranscation;
use App\Models\AllHeadTransaction;
use App\Models\Grouploans;
use App\Models\VendorTransaction;
use App\Models\Plans;
use App\Models\EmployeeSalary;
use App\Models\Expense;
use App\Models\TdsDeposit;
use App\Models\RentPayment;
use App\Exports\MemberExport;
use App\Exports\Update15GExport;
use App\Exports\ProfitLossExportDetailReport;
use App\Exports\ProfitLossExportHeadReport;
use App\Exports\ProfitLossExportFileChargeReport;
use App\Exports\BalanceSheetReportExportLoanAssetBranchWise;
use App\Exports\ProfitLossExportBranchWiseReport;
use App\Exports\BalanceSheetReportExportFixedAssets;
use App\Exports\ExportEInvestmentTransaction;
use App\Exports\profitLossDepreciationExport;
use App\Models\BranchDaybook;
use App\Exports\ExportExpenseReport;
use App\Models\SamraddhBankDaybook;
use App\Models\Grouploanmembers;
use App\Exports\balanceSheetReportBranchWiseSaving;
use App\Models\MemberTransaction;
use App\Exports\ProfitLossExportInterestDepositeReport;
use App\Exports\ProfitLossRentExport;
use App\Exports\InvestmentMembersExport;
use App\Exports\ProfitLossExportStationaryChargeReport;
use App\Exports\MemberInvestmentReportExport;
use App\Exports\MemberTransactionReportExport;
use App\Exports\TdsPayableExport;
use App\Exports\BillPaymentExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProfitLossCommissionExport;
use App\Models\Memberinvestments;
use App\Models\Companies;
use App\Models\MemberInvestmentInterestTds;
use App\Exports\balanceSheetReportBranchWiseDeposite;
use App\Models\AllTransaction;
use App\Exports\MemberLoanReportExport;
use App\Exports\MemberGroupLoanReportExport;
use App\Models\SamraddhBank;
use App\Models\Branch;
use App\Models\Daybook;
use App\Models\BranchCash;
use App\Models\DemandAdvice;
use App\Models\AssociateCommission;
use App\Models\Vendor;
use App\Exports\AssociateExport;
use App\Exports\ProfitLossExportReport;
use App\Exports\AssociateCommissionExport;
use App\Exports\BankLedgerReportExport;
use App\Exports\InvestmentCommissionExport;
use App\Exports\ProfitLossExportLoanTakenReport;
use App\Exports\ProfitLossExportSalaryReport;
use App\Exports\AdminBusinessReportExport;
use App\Exports\InvestmentChequeStatusExport;
use App\Exports\BalanceSheetExportReportDetails;
use App\Exports\balanceSheetReportBranchWiseAdvancePayment;
use App\Exports\KotaReportsExport;
use App\Exports\balanceSheetReportBranchWiseCashInHand;
use App\Exports\balanceSheetReportBranchWiseMembership;
use App\Exports\AssociateCommissionDetailExport;
use App\Exports\AssociateTreeExport;
use App\Exports\CorrectionRequestExport;
use App\Exports\LoanReportListExport;
use App\Exports\VoucherExport;
use App\Exports\DaybookReportExport;
use App\Exports\BalanceSheetReportExport;
use App\Exports\BalanceSheetReportExportDetails;
use App\Exports\LeaserExport;
use App\Exports\LeaserDetailExport;
use App\Exports\RentLiabilitiesExport;
use App\Exports\SmaddhChequeExport;
use App\Exports\ReceivedChequeExport;
use App\Exports\BalanceSheetReportExportDetailsBranchWiseExport;
use App\Exports\BalanceSheetReportExportDetailsBankWiseExport;
use App\Exports\LoanRecoveryExport;
use App\Exports\LoanDetailExport;
use App\Exports\GroupLoanReportListExport;
use App\Exports\GroupLoanDetailExport;
use App\Exports\GroupLoanRecoveryExport;
use App\Exports\BranchBusinessReportExport;
use Illuminate\Support\Facades\Cache;
use App\Exports\EmployeeTransferExport;
use App\Exports\EmployeeExport;
use App\Exports\EmployeeApplicationExport;
use App\Exports\EmploySalaryListExport;
use App\Exports\InactiveAssociateExport;
use App\Exports\ReportInvestmentExport;
use App\Exports\ReportSsbExport;
use App\Exports\ReportOtherExport;
use App\Exports\ReportAssociateBusinessExport;
use App\Exports\ReportAdminBusinessExport;
use App\Exports\ReportAssociateBusinessSummaryExport;
use App\Exports\ReportAssociateBusinessCompareExport;
use App\Exports\AssociateCommissionDetailLoanExport;
use App\Exports\LoanCommissionExport;
use App\Exports\LoanGroupCommissionExport;
use App\Exports\DesignationExport;
use App\Exports\RentTypeExportSalaryReport;
use App\Exports\BranchFundTransferExport;
use App\Exports\DemandAdviceExport;
use App\Exports\RentLedgerExport;
use App\Exports\RentLedgerReportExport;
use App\Exports\RentPaymentLedgerExport;
use App\Exports\RentTransferReportExport;
use App\Exports\SalaryLedgerExport;
use App\Exports\SalaryListExport;
use App\Exports\ExportbalanceSheetReportBranchWiseTds;
use App\Exports\SalaryTransferExport;
use App\Exports\MaturityReportExport;
use App\Exports\AccountHeadLedgerExport;
use App\Exports\AssetExport;
use App\Exports\DepreciationExport;
use App\Exports\DublicateDaybookReportExport;
use App\Exports\VendorCategoryExport;
use App\Exports\BillReportExport;
use App\Exports\MemberInvestmentTdsExport;
use App\Exports\LoanFromBankExport;
use App\Exports\AssociateExportApp;
use App\Models\MemberCompany;
use App\Models\AssociateException;
use DB;
use Validator;
use Carbon\Carbon;
use PDF;
use App\Models\Memberloans;
use Log;
use App\Exports\InactiveAppAssociateExport;
use App\Http\Traits\{IsLoanTrait, BalanceSheetTrait};
use Session;

class ExportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    use IsLoanTrait, BalanceSheetTrait;
    public function __construct()
    {
        $this->middleware('auth');
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
            $company_id = Companies::withoutGlobalScopes()->whereId($request['company_id'])->first(['id', 'name', 'short_name']);
            $returnURL = URL::to('/') . "/asset/" . ($request['company_id'] != 0 ? $company_id->short_name : 'all') . "_membr_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/" . ($request['company_id'] != 0 ? $company_id->short_name : 'all') . "_membr_list.csv";
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
        $data = MemberCompany::has('company')
            ->select('id', 're_date', 'member_id', 'company_id', 'associate_code', 'associate_id', 'is_block', 'customer_id', 'branch_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'first_name', 'last_name', 'dob', 'email', 'mobile_no', 'status', 'signature', 'photo', 'village', 'pin_code', 'state_id', 'district_id', 'city_id', 'associate_id', 'branch_id', 'address', 'gender', 'company_id', 'member_id')
                        ->with([
                            'branch' => function ($q) {
                                $q->select('id', 'name', 'branch_code', 'sector', 'zone');
                            }
                        ])
                        ->with([
                            'states' => function ($q) {
                                $q->select('id', 'name');
                            }
                        ])
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
                                    ->with([
                                        'idTypeFirst' => function ($q) {
                                            $q->select(['id', 'name']);
                                        }
                                    ])
                                    ->with([
                                        'idTypeSecond' => function ($q) {
                                            $q->select(['id', 'name']);
                                        }
                                    ]);
                            }
                        ])
                        ->with([
                            'children' => function ($q) {
                                $q->select(['id', 'first_name', 'last_name']);
                            }
                        ])
                        ->with([
                            'memberNomineeDetails' => function ($q) {
                                $q->select('id', 'name', 'relation', 'age', 'member_id', 'gender')->with([
                                    'nomineeRelationDetails' => function ($q) {
                                        $q->select('id', 'name');
                                    }
                                ]);
                            }
                        ])
                        ->with([
                            'savingAccount_Custom' => function ($q) {
                                $q->select('id', 'account_no', 'member_id');
                            }
                        ]);
                }
            ])
            ->where('member_id', '!=', '9999999');
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', '=', Auth::user()->branch_id);
        }
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            if ($company_id != '0') {
                $data = $data->where('company_id', '=', $company_id);
            }
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
        if (isset($request['branch_id']) && $request['branch_id'] != '') {
            $id = $request['branch_id'];
            if ($id != '0') {
                $data = $data->where('branch_id', '=', $id);
            }
        }
        if ($request['status'] != '') {
            $statusId = $request['status'];
            $data = $data->where('is_block', '=', $statusId);
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
            $sno = $_POST['start'];
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
                $NomineeDetail = $row['member']['memberNomineeDetails'];
                $val['COMPANY'] = $row['company'] ? $row['company']['name'] : 'N/A';
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->re_date));
                $val['BR BNAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $val['MEMBER ID'] = $row->member_id;
                $val['CUSTOMER ID'] = $row['member']->member_id;
                $val['NAME'] = $row->member->first_name . ' ' . $row->member->last_name;
                $val['MEMBER DOB'] = date('d/m/Y', strtotime($row->member->dob));
                if ($row->member->gender == 1) {
                    $val['Gender'] = "Male";
                } else {
                    $val['Gender'] = "Female";
                }
                $accountNo = '';
                if (isset($row->savingAccountNew->account_no)) {
                    $accountNo = $row->savingAccountNew->account_no;
                }
                $val['ACCOUNT NO'] = $accountNo;
                $val['MOBILE NUMBER'] = $row->member->mobile_no;
                $val['ASSOCIATE CODE'] = $row->associate_code;
                $val['ASSOCIATE NAME'] = $row['memberAssociate']['first_name'] . ' ' . $row['memberAssociate']['last_name'];
                if (isset($NomineeDetail->name)) {
                    $val['NOMINEE NAME'] = $NomineeDetail->name;
                } else {
                    $val['NOMINEE NAME'] = '';
                }
                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;
                    if ($relation_id) {
                        $val['RELATION'] = $NomineeDetail['nomineeRelationDetails']->name;
                    } else {
                        $val['RELATION'] = '';
                    }
                } else {
                    $val['RELATION'] = '';
                }
                $status = '';
                if ($row->member->is_block == 1) {
                    $status = 'Blocked';
                } else {
                    if ($row->member->status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'Inactive';
                    }
                }
                $val['STATUS'] = $status;
                $is_upload = 'Yes';
                if ($row->member->signature == '') {
                    $is_upload = 'No';
                }
                if ($row->member->photo == '') {
                    $is_upload = 'No';
                }
                $val['is_upload'] = $is_upload;
                $val['NOMINEE AGE'] = $NomineeDetail->age;
                if ($NomineeDetail->gender == 1) {
                    $val['NOMINEE Gender'] = 'Male';
                } else {
                    $val['NOMINEE Gender'] = 'Female';
                }
                //$val['EMAIL']=$row->email;
                $val['ADDRESS'] = preg_replace("/\r|\n/", " ", $row->member->address);
                if ($row->member['states']) {
                    $val['STATE'] = $row->member['states']->name;
                } else {
                    $val['STATE'] = ' ';
                }
                if ($row->member['district']) {
                    $val['DISTRICT'] = $row->member['district']->name;
                } else {
                    $val['DISTRICT'] = ' ';
                }
                if ($row->member['city']) {
                    $val['CITY'] = $row->member['city']->name;
                } else {
                    $val['CITY'] = ' ';
                }
                $val['VILLAGE'] = $row->member->village;
                $val['PIN CODE'] = $row->member->pin_code;
                $val['FIRST ID PROOF'] = $row['member']['memberIdProof']['idTypeFirst']->name . ' - ' . $row['member']['memberIdProof']->first_id_no;
                $val['SECOND ID PROOF'] = $row['member']['memberIdProof']['idTypeSecond']->name . ' - ' . $row['member']['memberIdProof']->second_id_no;
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
        if ($request['member_export'] == 1) {
            $memberList = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.member.memberexport', compact('memberList'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('members.pdf');
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
        $data = Member::with([
            'branch' => function ($q) {
                $q->select(['id', 'name', 'zone', 'sector', 'branch_code', 'regan']);
            }
        ])
            ->with([
                'children' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name', 'associate_no', 'member_id']);
                }
            ])
            ->with([
                'states' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->with([
                'city' => function ($q) {
                    $q->select(['id', 'name']);
                }
            ])
            ->with([
                'district' => function ($q) {
                    $q->select(['id', 'name']);
                }
            ])
            ->with([
                'memberIdProof' => function ($q) {
                    $q->with([
                        'idTypeFirst' => function ($q) {
                            $q->select(['id', 'name']);
                        }
                    ])->with([
                                'idTypeSecond' => function ($q) {
                                    $q->select(['id', 'name']);
                                }
                            ]);
                }
            ])
            ->with([
                'memberNomineeDetails' => function ($q) {
                    $q->with([
                        'nomineeRelationDetails' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ]);
                }
            ])
            ->where('member_id', '!=', '9999999')
            ->where('branch_id', $getBranchId->id)
            ->where('role_id', 5)
            ->where('is_deleted', 0);
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
                'id',
                'dob',
                're_date',
                'member_id',
                'first_name',
                'last_name',
                'mobile_no',
                'email',
                'associate_code',
                'associate_id',
                'status',
                'signature',
                'photo',
                'address',
                'state_id',
                'district_id',
                'city_id',
                'village',
                'pin_code',
                'is_block',
                'branch_id',
                'gender'
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
                $val['ADDRESS'] = preg_replace("/\r|\n/", "", $row->address);
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
    public function exportInvestmentPlan(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('investmentsListing_listAdmin' . $token);
        $count = Cache::get('investmentsListing_countAdmin' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/investmentListing.csv";
        $fileName = env('APP_EXPORTURL') . "report/investmentListing.csv";
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
        $records = array_slice($data, $start, $limit);
        foreach ($records as $row) {
            // echo '<pre>'; print_r($row['member_company']['member']['member_id_proof']); echo '</pre>'; exit();
            $sno++;
            $val['S.NO'] = $sno;
            $val['created_at'] = date("d/m/Y", strtotime($row['created_at'])) ?? 'N/A';
            $val['Form Number'] = $row['form_number'];
            $val['Plan'] = $row['plan']['name'];
            $val['company'] = $row['company']['name'] ?? 'N/A';
            $val['branch'] = $row['branch']['name'];
            $val['member'] = isset($row['member_company']['member']) ? $row['member_company']['member']['first_name'] . ' ' . $row['member_company']['member']['last_name'] : 'N/A';
            $val['customer_id'] = isset($row['member_company']['member']) ? $row['member_company']['member']['member_id'] : 'N/A';
            $val['member_id'] = isset($row['member_company']) ? $row['member_company']['member_id'] : 'N/A';
            $val['mobile_number'] = isset($row['member_company']['member']['mobile_no']) ? $row['member_company']['member']['mobile_no'] : 'N/A';
            $val['associate_code'] = $row['associate_member'] ? $row['associate_member']['associate_no'] : 'N/A';
            $val['associate_name'] = $row['associate_member'] ? $row['associate_member']['first_name'] . ' ' . $row['associate_member']['last_name'] : 'N/A';
            $val['collectorcode'] = $row['collector_account'] ? ($row['collector_account']['member_collector']['associate_no'] ?? $row['associateMember']['associate_no']) : 'N/A';
            $val['collectorname'] = $row['collector_account'] ? isset($row['collector_account']['member_collector']['first_name']) ? $row['collector_account']['member_collector']['first_name'] . ' ' . $row['collector_account']['member_collector']['last_name'] : $row['associate_member']['first_name'] . ' ' . $row['associate_member']['last_name'] : 'N/A';
            $val['account_number'] = $row['account_number'] ?? 'N/A';
            if ($row['plan_id'] == 1) {
                $tenure = 'N/A';
            } else {
                $tenure = number_format((float) $row['tenure'], 2, '.', '') . ' Year';
            }
            $val['Tenure'] = $tenure;
            if ($row['plan_id'] == 1) {
                $current_balance = isset($row['ssb_balance_view']['totalBalance']) ? number_format((float) $row['ssb_balance_view']['totalBalance'], 2, '.', '') : 'N/A';
            } else {
                $current_balance = $row['investment_balance'] ? $row['investment_balance']['totalBalance'] : 0.00;
            }
            $val['Current Balnace'] = $current_balance;
            $val['Eli Amount'] = investmentEliAmountNew($row['id']);
            $val['deposite_amount'] = $row['deposite_amount'];
            $val['secondId'] = isset($row['member_company']['member']['member_id_proof']['id_type_second']) ? $row['member_company']['member']['member_id_proof']['id_type_second']['name'] . ' - ' . $row['member_company']['member']['member_id_proof']['second_id_no'] : 'N/A';
            $val['address'] = isset($row['member_company']['member']['address']) ? preg_replace("/\r|\n/", "", $row['member_company']['member']['address']) : 'N/A';
            $val['state'] = isset($row['member_company']['member']['state_id']) ? getStateName($row['member_company']['member']['state_id']) : 'N/A'; //getStateName($row['member_company']['member']
            $val['district'] = isset($row['member_company']['member']['district_id']) ? getDistrictName($row['member_company']['member']['district_id']) : 'N/A'; //getDistrictName($row['member_company']['member']->district_id);
            $val['city'] = isset($row['member_company']['member']['city_id']) ? getCityName($row['member_company']['member']['city_id']) : 'N/A'; //getCityName($row['member_company']['member']
            $val['village'] = isset($row['member_company']['member']['village']) ? $row['member_company']['member']['village'] : 'N/A';
            $val['pin_code'] = isset($row['member_company']['member']['pin_code']) ? $row['member_company']['member']['pin_code'] : 'N/A';
            // $val['associate_code'] = $row['associateMember']['associate_no'] ?? 'N/A';
            // $val['associate_name'] = isset($row['associateMember']) ?  $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'] : 'N/A';
            // $val['branch_code'] = $row['branch']['branch_code'];
            // $val['sector_name'] = $row['branch']['sector'];
            // $val['region_name'] = $row['branch']['regan'];
            // $val['zone_name'] = $row['branch']['zone'];
            $val['First ID'] = isset($row['member_company']['member']['member_id_proof']['id_type_first']) ? $row['member_company']['member']['member_id_proof']['id_type_first']['name'] . ' - ' . $row['member_company']['member']['member_id_proof']['first_id_no'] : 'N/A';
            $val['Second ID'] = isset($row['member_company']['member']['member_id_proof']) ? $row['member_company']['member']['member_id_proof']['id_type_second']['name'] . ' - ' . $row['member_company']['member']['member_id_proof']['second_id_no'] : 'N/A';
            if (!$headerDisplayed) {
                // Use the keys from $data as the titles
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            // Put the data into the stream
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
            $fileName = env('APP_EXPORTURL') . "asset/associate_list.csv";
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
        $request['name'] = $request->name;
        $request['associate_code'] = $request->associate_code;
        $request['sassociate_code'] = $request->sassociate_code;
        $request['achieved'] = $request->achieved;
        $request['is_search'] = $request->is_search;
        $request['member_export'] = $request->member_export;
        $data = Member::select(['id', 'associate_branch_id', 'member_id', 'associate_no', 'first_name', 'last_name', 'dob', 'associate_join_date', 'mobile_no', 'email', 'associate_senior_id', 'associate_senior_code', 'associate_status', 'photo', 'signature', 'address', 'state_id', 'district_id', 'city_id', 'village', 'pin_code'])
            ->with('associate_branch')
            ->where('member_id', '!=', '0CI09999999')
            ->with([
                'seniorData' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name']);
                }
            ])
            ->with([
                'states' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->with([
                'city' => function ($q) {
                    $q->select(['id', 'name']);
                }
            ])
            ->with([
                'district' => function ($q) {
                    $q->select(['id', 'name']);
                }
            ])
            ->with([
                'memberIdProof' => function ($q) {
                    $q->with([
                        'idTypeFirst' => function ($q) {
                            $q->select(['id', 'name']);
                        }
                    ])
                        ->with([
                            'idTypeSecond' => function ($q) {
                                $q->select(['id', 'name']);
                            }
                        ]);
                }
            ])
            ->with([
                'memberNomineeDetails' => function ($q) {
                    $q->with([
                        'nomineeRelationDetails' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ]);
                }
            ])->where('is_associate', 1);
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['sassociate_code'] != '') {
                $associate_code = $request['sassociate_code'];
                $data = $data->where('associate_senior_code', '=', $associate_code);
            }
            if (isset($request['branch_id']) && $request['branch_id'] != '') {
                $id = $request['branch_id'];
                if ($id != '0') {
                    $data = $data->where('associate_branch_id', '=', $id);
                }
            }
            if ($request['associate_code'] != '') {
                $meid = $request['associate_code'];
                $data = $data->where('associate_no', '=', $meid);
            }
            if (isset($request['customer_id']) && $request['customer_id'] != '') {
                $customer_id = $request['customer_id'];
                $data = $data->where('member_id', 'like', '%' . $customer_id . '%');
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
                $sno = $_POST['start'];
                $totalResults = $data->orderby('associate_join_date', 'DESC')->count();
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
                //$rowReturn = array();
                foreach ($results as $row) {
                    $relationId = '';
                    $NomineeDetail = $row['memberNomineeDetails'];
                    $val['JOIN DATE'] = date("d/m/Y", strtotime($row->associate_join_date));
                    $val['BR NAME'] = $row['associate_branch'] ? $row['associate_branch']->name : 'N/A';
                    $val['BR CODE'] = $row['associate_branch'] ? $row['associate_branch']->branch_code : 'N/A';
                    $val['SO NAME'] = $row['associate_branch'] ? $row['associate_branch']->sector : 'N/A';
                    $val['RO NAME'] = $row['associate_branch'] ? $row['associate_branch']->regan : 'N/A';
                    $val['ZO NAME'] = $row['associate_branch'] ? $row['associate_branch']->zone : 'N/A';
                    $val['CUSTOMER ID'] = $row->member_id;
                    $val['ASSOCIATE_id'] = $row->associate_no;
                    $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                    $val['ASSOCIATE DOB'] = date('d/m/Y', strtotime($row->dob));
                    $val['NOMINEE NAME'] = $NomineeDetail ? $NomineeDetail->name : 'N/A'; //getMemberNomineeDetail($row->id)->name;
                    if ($row->id) {
                        $relation_id = $NomineeDetail ? $NomineeDetail->relation : 'N/A';
                        if ($relation_id) {
                            $val['relation'] = $NomineeDetail ? $NomineeDetail['nomineeRelationDetails']->name : 'N/A';
                        } else {
                            $val['relation'] = 'N/A';
                        }
                    }
                    $val['NOMINEE AGE'] = $NomineeDetail ? $NomineeDetail->age : 'N/A';
                    $val['EMAIL ID'] = $row->email;
                    $val['MOBILE NO'] = $row->mobile_no;
                    $val['SENIOR CODE'] = $row->associate_senior_code;
                    $val['SENIOR NAME'] = $row['seniorData'] ? $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name ?? '' : 'N/A';
                    //getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
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
                    //$val['name']=$row->first_name.' '.$row->last_name;
                    //$val['associate_code']=$row->associate_senior_code;
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
                    $val['ADDRESS'] = preg_replace("/\r|\n/", "", $row->address);
                    $val['STATE'] = $row['states']->name;
                    $val['DISTRICT'] = $row['district']->name;
                    $val['CITY'] = $row['city']->name;
                    $val['VILLAGE'] = $row->village;
                    $val['PIN CODE'] = $row->pin_code;
                    $val['FIRST ID PROOF'] = $row['memberIdProof'] ? $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no : 'N/A';
                    $val['SECOND ID PRROOF'] = $row['memberIdProof'] ? $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no : 'N/A';
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
            } elseif ($request['member_export'] == 1) {
                //$rowReturn[] = $val;
                $associateList = $data->orderby('associate_join_date', 'DESC')->get();
                $rowReturn = array();
                foreach ($associateList as $row) {
                    $NomineeDetail = $row['memberNomineeDetails'];
                    $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                    $val['branch'] = $row['associate_branch']->name;
                    $val['branch_code'] = $row['associate_branch']->branch_code;
                    $val['sector_name'] = $row['associate_branch']->sector;
                    $val['region_name'] = $row['associate_branch']->regan;
                    $val['zone_name'] = $row['associate_branch']->zone;
                    $val['dob'] = date('d/m/Y', strtotime($row->dob));
                    $val['m_id'] = $row->member_id;
                    $val['member_id'] = $row->associate_no;
                    $val['name'] = $row->first_name . ' ' . $row->last_name;
                    $val['email'] = $row->email;
                    $val['mobile_no'] = $row->mobile_no;
                    $val['associate_code'] = $row->associate_senior_code;
                    $val['associate_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                    $val['nominee_name'] = $NomineeDetail->name;
                    if ($row->id) {
                        $relation_id = $NomineeDetail->relation;
                        if ($relation_id) {
                            $val['relation'] = $NomineeDetail['nomineeRelationDetails']->name;
                        } else {
                            $val['relation'] = 'N/A';
                        }
                    }
                    $val['nominee_age'] = $NomineeDetail->age;
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
                    // $finacialYear=getFinacialYear();
                    //  $achivedTargetStatus=getFinacialYearBusinessTarget($row->id,$finacialYear['dateStart'],$finacialYear['dateEnd'],$row->current_carder_id);
                    //  if($achivedTargetStatus==0)
                    //  {
                    //     $achieved_target = 'Not Achieved';
                    //  }
                    //  else
                    //  {
                    //     $achieved_target = 'Achieved';
                    //  }
                    // $val['achieved_target']=$achieved_target;
                    $is_upload = 'Yes';
                    if ($row->signature == '') {
                        $is_upload = 'No';
                    }
                    if ($row->photo == '') {
                        $is_upload = 'No';
                    }
                    $val['is_upload'] = $is_upload;
                    $val['firstId'] = $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no;
                    $val['secondId'] = $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no;
                    $val['address'] = preg_replace("/\r|\n/", "", $row->address);
                    $val['state'] = $row['states']->name;
                    $val['district'] = $row['district']->name;
                    $val['city'] = $row['city']->name;
                    $val['village'] = $row->village;
                    $val['pin_code'] = $row->pin_code;
                    $rowReturn[] = $val;
                }
                $pdf = PDF::loadView('templates.admin.associate.export', compact('rowReturn'))->setPaper('a4', 'landscape')->setWarnings(false);
                $pdf->save(storage_path() . '_filename.pdf');
                return $pdf->download('associate.pdf');
            }
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
            $returnURL = URL::to('/') . "/asset/associate_commision_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate_commision_list.csv";
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
        $company_id = $request['company_id'];
        $startDate = Carbon::create($year, $month, $sday)->format('Y-m-d');
        $endDate = Carbon::create($year, $month)->endOfMonth()->toDateString();
        $data = Member::with('associate_branch')
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
            ->where('member_id', '!=', '0CI09999999')->where('is_associate', 1);
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
        }
        if ($request['branch_id'] != '' && $request['branch_id'] != 0) {
            $id = $request['branch_id'];
            $data = $data->where('associate_branch_id', '=', $id);
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
                $endDate = date("Y-m-d", strtotime(convertDate($request['end_date'])));
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                // $val['COMPANY NAME'] =$row['company']->name;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCIATE CARDER'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')';
                //getCarderName($row->current_carder_id);
                //$val['TOTAL COMMISION AMOUNT']= number_format($row->associate_total_commission_count,2,'.','');
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
                $val['SENIOIR NAME'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                //getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
                //$val['SENIOR CARDER']= $row['seniorData']['getCarderNameCustom']->name;
                if ($row['seniorData']['getCarderNameCustom']) {
                    $val['SENIOR CARDER'] = $row['seniorData']['getCarderNameCustom']->name;
                } else {
                    $val['SENIOR CARDER'] = "N/A";
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
            $pdf = PDF::loadView('templates.admin.associate.exportcommission', compact('data', 'startDate', 'endDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associatecommission.pdf');
        }
    }
    /**
     * Export associate commission listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */

    public function exportKotaBusinessReport(Request $request)
    {
        $Auth = Auth::user();
        if ($request['kotareport_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate_business_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate_business_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = Member::with('associate_branch')
            ->with([
                'seniorData' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name', 'associate_no', 'associate_senior_id', 'current_carder_id'])
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
            ->with(['getBusinessTargetAmt'])
            ->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if ($Auth->branch_id > 0) {
            $data = $data->where('branch_id', $Auth->branch_id);
        }
        $startDate = '';
        $endDate = '';
        $business_mode = 2;
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
        }
        /*
            if($request['start_date'] !=''){
                $startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if($request['end_date'] !=''){
                    $endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                }
                else
                {
                    $endDate='';
                }
                $data=$data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
            }
            */
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
        if ($request['business_mode'] != '') {
            $business_mode = $request['business_mode'];
        }
        if ($request['kotareport_export'] == 0) {
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
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['ASSOCIATE CARDER'] = $row['getCarderNameCustom']->name; //getCarderNameFull($row->current_carder_id);
                $val['QUOTA BUSINESS TARGET(SELF BUSINESS AMT'] = $row['getBusinessTargetAmt']->self;
                //getBusinessTargetAmt($row->current_carder_id)->self;
                $val['ACHIEVED TARGET(Self Business)'] = round(\App\Models\AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount'), 2);
                $targetSelf = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                $achievedSelf = \App\Models\AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                if ($achievedSelf > 0) {
                    $targetSelfPer = 100 - ($achievedSelf / $targetSelf) * 100;
                } else {
                    $targetSelfPer = 100;
                }
                $val['Quota Business Target (Self Business) %'] = round($targetSelfPer, 3);
                $targetSelf = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                $achievedSelf = \App\Models\AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                if ($achievedSelf > 0) {
                    $achievedSelfPer = ($achievedSelf / $targetSelf) * 100;
                } else {
                    $achievedSelfPer = 0;
                }
                $val['Achieved Target (Self Business) %'] = round($achievedSelfPer, 3);
                $val['SENIOR CODE'] = $row['seniorData']->associate_no; //getSeniorData($row->associate_senior_id,'associate_no');
                $val['SENIOR NAME'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                //getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
                $val['Senior Carder'] = $row['seniorData']['getCarderNameCustom']->name;
                //getCarderNameFull(getSeniorData($row->associate_senior_id,'current_carder_id'));
                if ($row->current_carder_id > 1) {
                    $targetTeam = round($row['getBusinessTargetAmt']->credit, 2);
                } else {
                    $targetTeam = 'N/A';
                }
                $val['Quota Business Target (Team Business) Amt'] = $targetTeam;
                if ($row->current_carder_id > 1) {
                    $achievedTarget = round(getKotaBusinessTeam($row->id, $startDate, $endDate), 2);
                } else {
                    $achievedTarget = 'N/A';
                }
                $val['Achieved Target (Team Business) Amt'] = $achievedTarget;
                /*
            $targetSelf=getBusinessTargetAmt($row->current_carder_id)->self;
            $achievedSelf = \App\Models\AssociateKotaBusiness::where('member_id',$row->id)->where('type',1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
            if($achievedSelf>0 )
            {
              $targetSelfPer=100-($achievedSelf/$targetSelf)*100;
            }
            else
            {
              $targetSelfPer=100;
            }
            $val['Quota Business Target (Team Business) %']= round($targetSelfPer,3);
            $targetSelf=getBusinessTargetAmt($row->current_carder_id)->self;
            $achievedSelf = \App\Models\AssociateKotaBusiness::where('member_id',$row->id)->where('type',1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
            if($achievedSelf>0)
            {
              $achievedSelfPer=($achievedSelf/$targetSelf)*100;
            }
            else
            {
              $achievedSelfPer=0;
            }
            $val['Achieved Target (Team Business) %']= round($achievedSelfPer,3);
*/
                if ($row->current_carder_id > 1) {
                    $targetTeam = $row['getBusinessTargetAmt']->credit; //getBusinessTargetAmt( $row->current_carder_id )->credit;
                    $targetteamAchivede = getKotaBusinessTeam($row->id, $startDate, $endDate);
                    $achievedTeamfPer = round(100.000 - ($targetteamAchivede / $targetTeam) * 100, 2);
                } else {
                    $achievedTeamfPer = 'N/A';
                }
                $val['Quota Business Target (Team Business) %'] = $achievedTeamfPer;
                if ($row->current_carder_id > 1) {
                    $targetTeam = $row['getBusinessTargetAmt']->credit; //getBusinessTargetAmt ( $row->current_carder_id )->credit;
                    $achievedTarget = getKotaBusinessTeam($row->id, $startDate, $endDate);
                    $achievedTeamfPer = round(($achievedTarget / $targetTeam) * 100, 2);
                } else {
                    $achievedTeamfPer = 'N/A';
                }
                $val['Achieved Target (Team Business) %'] = $achievedTeamfPer;
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['MOBILE NO'] = $row->mobile_no;
                if ($row->is_block == 0) {
                    if ($row->associate_status == 1) {
                        $status = 'Active';
                    } else {
                        $status = 'Inactive';
                    }
                } else {
                    $status = 'Blocked';
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
        } elseif ($request['kotareport_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.exportkotareports', compact('data', 'startDate', 'endDate', 'business_mode'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('quatareports.pdf');
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
        $data = AssociateCommission::where('member_id', '!=', $mid->id)->where('type', '>', 2)->where('type_id', $request['investment_id']);
        $investment = Memberinvestments::where('id', $request['investment_id'])->first();
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
        if ($request['investmentcommission_export'] == 0) {
            return Excel::download(new InvestmentCommissionExport($data, $investment), 'investmentcommissionexports.xlsx');
        } elseif ($request['investmentcommission_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.investment_management.exportinvestmentcommission', compact('data', 'investment'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('investmentcommissionexports.pdf');
        }
    }
    /**
     * Export investment cheque status listing in pdf,csv.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportChequeStatusListing(Request $request)
    {
        $data = Memberinvestments::with('plan', 'investmentPayment', 'branch')->where('payment_mode', 1);
        /******* fillter query start ****/
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['branch_id'] != '') {
                $id = $request['branch_id'];
                $data = $data->where('branch_id', '=', $id);
            }
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                $data = $data->whereHas('investmentPayment', function ($query) use ($startDate) {
                    $query->where('member_investments_payments.created_at', $startDate);
                });
            }
            if ($request['status'] != '') {
                $status = $request['status'];
                $data = $data->whereHas('investmentPayment', function ($query) use ($status) {
                    $query->where('member_investments_payments.status', $status);
                });
            }
        }
        $data = $data->orderby('id', 'DESC')->get();
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
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate-commission-detail.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate-commission-detail.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $request['year'] = $request->year;
        $request['month'] = $request->month;
        $request['plan_id'] = $request->plan_id;
        $request['is_search'] = $request->is_search;
        $request['commission_export'] = $request->commission_export;
        $request['id'] = $request->id;
        // if ($request['year'] <= 2022 && $request['month'] < 12) {
        //     $data = AssociateCommission::select(['id', 'type_id', 'total_amount', 'commission_amount', 'percentage', 'type', 'carder_id', 'month', 'commission_type', 'associate_exist', 'pay_type', 'is_distribute', 'created_at'])
        //         ->with([
        //             'investment' => function ($q) {
        //                 $q->select('id', 'plan_id', 'account_number');
        //             }
        //         ])
        //         ->where('member_id', $request['id'])->whereIN('type', [3, 5])->where('is_deleted', '0') /*->where('is_distribute',0)*/;
        //     if ($request['year'] != '') {
        //         $year = $request['year'];
        //         $data = $data->where(\DB::raw('YEAR(created_at)'), $year);
        //     }
        //     if ($request['month'] != '') {
        //         $month = $request['month'];
        //         $data = $data->where(\DB::raw('MONTH(created_at)'), $month);
        //     }
        //     if (isset($request['plan_id']) && $request['plan_id'] != '') {
        //         $meid = $request['plan_id'];
        //         $data = $data->whereHas('investment', function ($query) use ($meid) {
        //             $query->where('member_investments.plan_id', $meid);
        //         });
        //     }
        // } else {
        $data = \App\Models\AssociateMonthlyCommission::with([
            'investment' => function ($q) {
                $q->select('id', 'plan_id', 'account_number');
            }
        ])->with('investment.plan:id,name')
            ->where('assocaite_id', $request['id'])->where('type', 1)->where('is_deleted', '0') /*->where('is_distribute',0)*/ ;
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
        //}
        // $member=Member::where('id',$request['id'])->first();
        if ($request['commission_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count('id');
            $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get(['id', 'type_id', 'total_amount', 'commission_amount', 'percentage', 'type', 'cadre_from', 'cadre_to', 'qualifying_amount', 'month', 'commission_for_year', 'commission_for_month']);
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
                $row['ACCOUNT NUMBER'] = $val['investment']->account_number;
                $row['PLAN NAME'] = $val['investment']->plan->name;
                ;
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
            $pdf = PDF::loadView('templates.admin.associate.exportcommission_detail', compact('data', 'member'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associatecommission_detail.pdf');
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
    /**
     * Export Correction Request listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */

    public function exportCorrectionRequest(Request $request)
    {
        if ($request['correction_export'] == 0) {
            $Status = [
                0 => 'Pending',
                1 => 'Corrected',
                2 => 'Rejected',
            ];
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $companyId = $request['company_id'];
            $returnURL = URL::to('/') . "/asset/member_correction.csv";
            $company_id = Companies::withoutGlobalScopes()->when($companyId != '0', function ($q) use ($companyId) {
                $q->whereId($companyId);
            })->first(['id', 'name', 'short_name']);
            $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_member_correction.csv";
            $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_correctionRequest.pdf';
            if ($request['type'] == 0) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_memberCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_memberCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_memberCorrectionRequest.csv";
            }
            if ($request['type'] == 1) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_assocaiteCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_assocaiteCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_assocaiteCorrectionRequest.csv";
            }
            if ($request['type'] == 2) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_innvestmentCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_innvestmentCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_innvestmentCorrectionRequest.csv";
            }
            if ($request['type'] == 3) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_renewalCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_renewalCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_renewalCorrectionRequest.csv";
            }
            if ($request['type'] == 4) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_renewalCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_renewalCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_renewalCorrectionRequest.csv";
            }
            if ($request['type'] == 5) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_printPassbookCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_printPassbookCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_printPassbookCorrectionRequest.csv";
            }
            if ($request['type'] == 6) {
                $fileName = env('APP_EXPORTURL') . "asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_printCertificatekCorrectionRequest.csv";
                $pdf = (isset($company_id) ? $company_id->short_name : 0) . '_printCertificatekCorrectionRequest.pdf';
                $returnURL = URL::to('/') . "/asset/" . (isset($company_id) ? $company_id->short_name : 0) . "_printCertificatekCorrectionRequest.csv";
            }
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CorrectionRequests::has('correctionCompay')->with([
            'correctionMemberInvestmentCustom' => function ($q) {
                $q->select('id', 'account_number', 'plan_id');
            }
        ])
            ->with([
                'correctionSeniorCustom',
                'correctionDaybookCustom',
                'correctionCompay:id,name',
                'branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }
            ])
            ->where('correction_type', $request['type']);
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
            if ($branch_id != '0') {
                $data = $data->where('branch_id', '=', $branch_id);
            }
        }
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            if ($company_id != '0') {
                $data = $data->where('company_id', '=', $company_id);
            }
        }
        if ($request['correction_date'] != '') {
            $correction_date = date("Y-m-d", strtotime(convertDate($request['correction_date'])));
            $data = $data->whereDate('created_at', $correction_date);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['correction_export'] == 0) {
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
            $printType = [
                1 => 'Free',
                2 => 'Paid',
            ];
            $in_context = [
                0 => 'Member Registration',
                1 => 'Associate Registration',
                2 => 'Investment Registration',
                3 => 'Renewals Transaction',
                4 => 'Withdrawals',
                5 => 'Passbook print',
                6 => 'Certificate print',
            ];
            foreach ($results as $row) {
                $sno++;
                $val['Sr_no'] = $sno;
                $val['REQUESTED DATE'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                $val['COMPANY NAME'] = $row['correctionCompay']->name;
                $val['BR NAME'] = $row['branch']->name;
                $account_no = '';
                if ($row->correction_type == 0) {
                    $account_no = $row['correctionSeniorCustom']->member_id;
                } elseif ($row->correction_type == 1) {
                    $account_no = $row['correctionSeniorCustom']->associate_no;
                } elseif ($row->correction_type == 2) {
                    $account_no = getMemberInvestment($row->correction_type_id);
                    if (isset($account_no->account_number)) {
                        $account_no = $account_no->account_number;
                    }
                } elseif ($row->correction_type == 3) {
                    $inId = $row['correctionDaybookCustom'];
                    if ($inId) {
                        $invId = \App\Models\Memberinvestments::select('plan_id')->where('id', $inId->investment_id)->first();
                        $account_no = $inId->account_no;
                    } else {
                        $account_no = 'N/A';
                    }
                } elseif ($row->correction_type == 4) {
                    $account_no = 'N/A';
                } elseif ($row->correction_type == 5) {
                    $invId = $row['correctionMemberInvestmentCustom'];
                    $account_no = $invId->account_number;
                } elseif ($row->correction_type == 6) {
                    $invId = $row['correctionMemberInvestmentCustom'];
                    $account_no = $invId->account_number;
                }
                if ($row->correction_type == 0) {
                    $val['MEMBER ID'] = $account_no;
                } else if ($row->correction_type == 1) {
                    $val['Associate Id'] = $account_no;
                } else {
                    $val['Account Number'] = $account_no;
                }
                // if($row->correction_type == 3){
                //     $val['APPROVED DATE'] = '';
                //     $val['APPROVED BY'] = '';
                // }else{
                    $val['IN CONTEXT TO'] = $in_context[$row->correction_type];
                    $val['PRINT TYPE'] = $printType[$row->print_type] ?? 'N/A';
                // }
                $val['CORRECTION'] = $row->correction_description;
                $val['STATUS'] = $Status[$row->status];
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
    public function exportassociateCorrectionRequest(Request $request)
    {
        if ($request['correction_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate_correction.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate_correction.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CorrectionRequests::where('correction_type', $request['type']);
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $id);
        }
        if ($request['correction_date'] != '') {
            $correction_date = date("Y-m-d", strtotime(convertDate($request['correction_date'])));
            $data = $data->whereDate('created_at', $correction_date);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['correction_export'] == 0) {
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
                $val['Sr_no'] = $sno;
                $val['TRANSACTION DATE'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                //$val['ZO NAME']=$row['branch']->zone;
                $account_no = '';
                if ($row->correction_type == 0) {
                    $account_no = getSeniorData($row->correction_type_id, 'member_id');
                } elseif ($row->correction_type == 1) {
                    $account_no = getSeniorData($row->correction_type_id, 'associate_no');
                    // print_r($account_no);die;
                } elseif ($row->correction_type == 2) {
                    $account_no = getMemberInvestment($row->correction_type_id);
                    if (isset($account_no->account_number)) {
                        $account_no = $account_no->account_number;
                    }
                } elseif ($row->correction_type == 3) {
                    $inId = \App\Models\Daybook::where('id', $row->correction_type_id)->first();
                    if ($inId) {
                        $invId = \App\Models\Memberinvestments::select('plan_id')->where('id', $inId->investment_id)->first();
                        $account_no = '<a href="admin/investment/passbook/transaction/' . $inId->investment_id . '/' . $invId->plan_id . '">' . $inId->account_no . '<a>';
                    } else {
                        $account_no = 'N/A';
                    }
                } elseif ($row->correction_type == 4) {
                    $account_no = 'N/A';
                } elseif ($row->correction_type == 5) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '<a>';
                } elseif ($row->correction_type == 6) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '<a>';
                }
                $val['ASSOCIATE ID'] = $account_no;
                $in_context = '';
                if ($row->correction_type == 0) {
                    $in_context = 'Member Registration';
                } elseif ($row->correction_type == 1) {
                    $in_context = 'Associate Registration';
                } elseif ($row->correction_type == 2) {
                    $in_context = 'Investment Registration';
                } elseif ($row->correction_type == 3) {
                    $in_context = 'Renewals Transaction';
                } elseif ($row->correction_type == 4) {
                    $in_context = 'Withdrawals';
                } elseif ($row->correction_type == 5) {
                    $in_context = 'Passbook print';
                } elseif ($row->correction_type == 6) {
                    $in_context = 'Certificate print';
                }
                $val['IN CONTEXT TO'] = $in_context;
                $val['CORRECTION'] = $row->correction_description;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } elseif ($row->status == 1) {
                    $status = 'Corrected';
                } elseif ($row->status == 2) {
                    $status = 'Rejected';
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
        } elseif ($request['correction_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.common.exportassociatecorrectionreports', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associatecorrectionreports.pdf');
        }
    }
    public function exportinvestmentCorrectionRequest(Request $request)
    {
        if ($request['correction_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/investment_correction.csv";
            $fileName = env('APP_EXPORTURL') . "asset/investment_correction.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CorrectionRequests::where('correction_type', $request['type']);
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $id);
        }
        if ($request['correction_date'] != '') {
            $correction_date = date("Y-m-d", strtotime(convertDate($request['correction_date'])));
            $data = $data->whereDate('created_at', $correction_date);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['correction_export'] == 0) {
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
                $val['Sr_no'] = $sno;
                $val['TRANSACTION DATE'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $account_no = '';
                if ($row->correction_type == 0) {
                    $account_no = getSeniorData($row->correction_type_id, 'member_id');
                } elseif ($row->correction_type == 1) {
                    $account_no = getSeniorData($row->correction_type_id, 'associate_no');
                    // print_r($account_no);die;
                } elseif ($row->correction_type == 2) {
                    $account_no = getMemberInvestment($row->correction_type_id);
                    if (isset($account_no->account_number)) {
                        $account_no = $account_no->account_number;
                    }
                } elseif ($row->correction_type == 3) {
                    $inId = \App\Models\Daybook::where('id', $row->correction_type_id)->first();
                    if ($inId) {
                        $invId = \App\Models\Memberinvestments::select('plan_id')->where('id', $inId->investment_id)->first();
                        $account_no = '<a href="admin/investment/passbook/transaction/' . $inId->investment_id . '/' . $invId->plan_id . '">' . $inId->account_no . '<a>';
                    } else {
                        $account_no = 'N/A';
                    }
                } elseif ($row->correction_type == 4) {
                    $account_no = 'N/A';
                } elseif ($row->correction_type == 5) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '<a>';
                } elseif ($row->correction_type == 6) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '<a>';
                }
                $val['ACCOUNT NO'] = $account_no;
                $in_context = '';
                if ($row->correction_type == 0) {
                    $in_context = 'Member Registration';
                } elseif ($row->correction_type == 1) {
                    $in_context = 'Associate Registration';
                } elseif ($row->correction_type == 2) {
                    $in_context = 'Investment Registration';
                } elseif ($row->correction_type == 3) {
                    $in_context = 'Renewals Transaction';
                } elseif ($row->correction_type == 4) {
                    $in_context = 'Withdrawals';
                } elseif ($row->correction_type == 5) {
                    $in_context = 'Passbook print';
                } elseif ($row->correction_type == 6) {
                    $in_context = 'Certificate print';
                }
                $val['IN CONTEXT TO'] = $in_context;
                $val['CORRECTION'] = $row->correction_description;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } elseif ($row->status == 1) {
                    $status = 'Corrected';
                } elseif ($row->status == 2) {
                    $status = 'Rejected';
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
        } elseif ($request['correction_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.common.exportcorrectionreports', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('correctionreports.pdf');
        }
    }
    public function exportrenewCorrectionRequest(Request $request)
    {
        if ($request['correction_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/renew_correction.csv";
            $fileName = env('APP_EXPORTURL') . "asset/renew_correction.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CorrectionRequests::where('correction_type', $request['type']);
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $id);
        }
        if ($request['correction_date'] != '') {
            $correction_date = date("Y-m-d", strtotime(convertDate($request['correction_date'])));
            $data = $data->whereDate('created_at', $correction_date);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['correction_export'] == 0) {
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
                $val['TRANSACTION DATE'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $account_no = '';
                if ($row->correction_type == 0) {
                    $account_no = getSeniorData($row->correction_type_id, 'member_id');
                } elseif ($row->correction_type == 1) {
                    $account_no = getSeniorData($row->correction_type_id, 'associate_no');
                    // print_r($account_no);die;
                } elseif ($row->correction_type == 2) {
                    $account_no = getMemberInvestment($row->correction_type_id);
                    if (isset($account_no->account_number)) {
                        $account_no = $account_no->account_number;
                    }
                } elseif ($row->correction_type == 3) {
                    $inId = \App\Models\Daybook::where('id', $row->correction_type_id)->first();
                    if ($inId) {
                        $invId = \App\Models\Memberinvestments::select('plan_id')->where('id', $inId->investment_id)->first();
                        $account_no = '<a href="admin/investment/passbook/transaction/' . $inId->investment_id . '/' . $invId->plan_id . '">' . $inId->account_no . '<a>';
                    } else {
                        $account_no = 'N/A';
                    }
                } elseif ($row->correction_type == 4) {
                    $account_no = 'N/A';
                } elseif ($row->correction_type == 5) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '<a>';
                } elseif ($row->correction_type == 6) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '<a>';
                }
                $val['ACCOUNT NO'] = $account_no;
                $in_context = '';
                if ($row->correction_type == 0) {
                    $in_context = 'Member Registration';
                } elseif ($row->correction_type == 1) {
                    $in_context = 'Associate Registration';
                } elseif ($row->correction_type == 2) {
                    $in_context = 'Investment Registration';
                } elseif ($row->correction_type == 3) {
                    $in_context = 'Renewals Transaction';
                } elseif ($row->correction_type == 4) {
                    $in_context = 'Withdrawals';
                } elseif ($row->correction_type == 5) {
                    $in_context = 'Passbook print';
                } elseif ($row->correction_type == 6) {
                    $in_context = 'Certificate print';
                }
                $val['IN CONTEXT TO'] = $in_context;
                $val['CORRECTION'] = $row->correction_description;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } elseif ($row->status == 1) {
                    $status = 'Corrected';
                } elseif ($row->status == 2) {
                    $status = 'Rejected';
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
        } elseif ($request['correction_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.common.exportcorrectionreports', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('correctionreports.pdf');
        }
    }
    public function exportprintpassbookCorrectionRequest(Request $request)
    {
        if ($request['correction_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/print_correction.csv";
            $fileName = env('APP_EXPORTURL') . "asset/print_correction.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CorrectionRequests::where('correction_type', $request['type']);
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $id);
        }
        if ($request['correction_date'] != '') {
            $correction_date = date("Y-m-d", strtotime(convertDate($request['correction_date'])));
            $data = $data->whereDate('created_at', $correction_date);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['correction_export'] == 0) {
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
                $val['TRANSACTION DATE'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $account_no = '';
                if ($row->correction_type == 0) {
                    $account_no = getSeniorData($row->correction_type_id, 'member_id');
                } elseif ($row->correction_type == 1) {
                    $account_no = getSeniorData($row->correction_type_id, 'associate_no');
                    // print_r($account_no);die;
                } elseif ($row->correction_type == 2) {
                    $account_no = getMemberInvestment($row->correction_type_id);
                    if (isset($account_no->account_number)) {
                        $account_no = $account_no->account_number;
                    }
                } elseif ($row->correction_type == 3) {
                    $inId = \App\Models\Daybook::where('id', $row->correction_type_id)->first();
                    if ($inId) {
                        $invId = \App\Models\Memberinvestments::select('plan_id')->where('id', $inId->investment_id)->first();
                        $account_no = '<a href="admin/investment/passbook/transaction/' . $inId->investment_id . '/' . $invId->plan_id . '">' . $inId->account_no . '<a>';
                    } else {
                        $account_no = 'N/A';
                    }
                } elseif ($row->correction_type == 4) {
                    $account_no = 'N/A';
                } elseif ($row->correction_type == 5) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = $invId->account_number;
                } elseif ($row->correction_type == 6) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = $invId->account_number;
                }
                $val['ACCOUNT NO'] = $account_no;
                $in_context = '';
                if ($row->correction_type == 0) {
                    $in_context = 'Member Registration';
                } elseif ($row->correction_type == 1) {
                    $in_context = 'Associate Registration';
                } elseif ($row->correction_type == 2) {
                    $in_context = 'Investment Registration';
                } elseif ($row->correction_type == 3) {
                    $in_context = 'Renewals Transaction';
                } elseif ($row->correction_type == 4) {
                    $in_context = 'Withdrawals';
                } elseif ($row->correction_type == 5) {
                    $in_context = 'Passbook print';
                } elseif ($row->correction_type == 6) {
                    $in_context = 'Certificate print';
                }
                $val['IN CONTEXT TO'] = $in_context;
                $val['CORRECTION'] = $row->correction_description;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } elseif ($row->status == 1) {
                    $status = 'Corrected';
                } elseif ($row->status == 2) {
                    $status = 'Rejected';
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
        } elseif ($request['correction_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.common.exportcorrectionreports', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('correctionreports.pdf');
        }
    }
    public function exportprintcertificateCorrectionRequest(Request $request)
    {
        if ($request['correction_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/certifiacte_correction.csv";
            $fileName = env('APP_EXPORTURL') . "asset/certifiacte_correction.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CorrectionRequests::where('correction_type', $request['type']);
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $id);
        }
        if ($request['correction_date'] != '') {
            $correction_date = date("Y-m-d", strtotime(convertDate($request['correction_date'])));
            $data = $data->whereDate('created_at', $correction_date);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['correction_export'] == 0) {
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
                $val['TRANSACTION DATE'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->regan;
                $val['ZO NAME'] = $row['branch']->zone;
                $account_no = '';
                if ($row->correction_type == 0) {
                    $account_no = getSeniorData($row->correction_type_id, 'member_id');
                } elseif ($row->correction_type == 1) {
                    $account_no = getSeniorData($row->correction_type_id, 'associate_no');
                    // print_r($account_no);die;
                } elseif ($row->correction_type == 2) {
                    $account_no = getMemberInvestment($row->correction_type_id);
                    if (isset($account_no->account_number)) {
                        $account_no = $account_no->account_number;
                    }
                } elseif ($row->correction_type == 3) {
                    $inId = \App\Models\Daybook::where('id', $row->correction_type_id)->first();
                    if ($inId) {
                        $invId = \App\Models\Memberinvestments::select('plan_id')->where('id', $inId->investment_id)->first();
                        $account_no = '<a href="admin/investment/passbook/transaction/' . $inId->investment_id . '/' . $invId->plan_id . '">' . $inId->account_no . '<a>';
                    } else {
                        $account_no = 'N/A';
                    }
                } elseif ($row->correction_type == 4) {
                    $account_no = 'N/A';
                } elseif ($row->correction_type == 5) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = $invId->account_number;
                } elseif ($row->correction_type == 6) {
                    $invId = \App\Models\Memberinvestments::select('id', 'account_number', 'plan_id')->where('id', $row->correction_type_id)->first();
                    $account_no = $invId->account_number;
                }
                $val['ACCOUNT NO'] = $account_no;
                $in_context = '';
                if ($row->correction_type == 0) {
                    $in_context = 'Member Registration';
                } elseif ($row->correction_type == 1) {
                    $in_context = 'Associate Registration';
                } elseif ($row->correction_type == 2) {
                    $in_context = 'Investment Registration';
                } elseif ($row->correction_type == 3) {
                    $in_context = 'Renewals Transaction';
                } elseif ($row->correction_type == 4) {
                    $in_context = 'Withdrawals';
                } elseif ($row->correction_type == 5) {
                    $in_context = 'Passbook print';
                } elseif ($row->correction_type == 6) {
                    $in_context = 'Certificate print';
                }
                $val['IN CONTEXT TO'] = $in_context;
                $val['CORRECTION'] = $row->correction_description;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } elseif ($row->status == 1) {
                    $status = 'Corrected';
                } elseif ($row->status == 2) {
                    $status = 'Rejected';
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
        } elseif ($request['correction_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.common.exportcorrectionreports', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('correctionreports.pdf');
        }
    }
    /**
     * Export Leaser List.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function leaserExport(Request $request)
    {
        $data=\App\Models\CommissionLeaser:: orderby('id','DESC')->get();
        if($request['leaser_export'] == 0){
            return Excel::download(new LeaserExport($data), 'leaser.xlsx');
        }elseif ($request['leaser_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.associate.export_leaser',compact('data'));
            $pdf->save(storage_path().'_filename.pdf');
            return $pdf->download('leaser.pdf');
        }
    }
    */
    public function leaserExport(Request $request)
    {
        if ($request['leaser_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate-commission-transfer-list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate-commission-transfer-list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\CommissionLeaser::orderby('id', 'DESC');
        if ($request['leaser_export'] == 0) {
            $totalResults = $data->count();
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
            foreach ($results as $row) {
                $sno++;
                $val['Sr_no'] = $sno;
                $val['STATR DATE TIME'] = date("d/m/Y H:i:s a", strtotime($row->start_date));
                $val['END DATE TIME'] = date("d/m/Y H:i:s a", strtotime($row->end_date));
                $val['TOTAL AMT'] = $row->total_amount;
                $val['TOTAL TRANSFER AMT'] = $row->ledger_amount;
                $credit = '';
                $credit = $row->credit_amount;
                $credit = number_format((float) $credit, 2, '.', '');
                ;
                ;
                $val['Total Refund Amt.'] = $credit;
                $val['TOTAL FUL TRANSFER AMT'] = $row->total_fuel;
                $credit_fuel = '';
                $credit_fuel = $row->credit_fuel;
                $credit_fuel = number_format((float) $credit_fuel, 2, '.', '');
                ;
                ;
                $val['Total FUEL Refund .'] = $credit_fuel;
                $status = '';
                if ($row->status == 1) {
                    $status = 'Transferred';
                } else {
                    $status = 'Deleted';
                }
                $val['STATUS'] = $status;
                $val['CREATE DATE'] = date("d/m/Y H:i:s a", strtotime($row->created_at));
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
        } elseif ($request['leaser_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.export_leaser', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('leaser.pdf');
        }
    }
    /**
     * Export Leaser List.
     *
     * @return \Illuminate\Http\Response
     */

    public function AssociateCollectionReportExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/associatecollectionreport.csv";
        $fileName = env('APP_EXPORTURL') . "/report/associatecollectionreport.csv";
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
            $branch_id = 0;
            if (isset($request['branch_id']) && $request['branch_id'] > 0) {
                $branch_id = $request['branch_id'];
            }
            $associate_code = '';
            if (isset($request['associate_code']) && $request['associate_code'] != '') {
                $associate_code = $request['associate_code'];
            }
            $companyId = 0;
            if (isset($request['company_id']) && $request['company_id'] > 0) {
                $companyId = $request['company_id'];
            }
        }
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
        $count = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, $fillter, 0, 0]);
        $totalResults = count($count);
        $data = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, 0, $companyId]);
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
        foreach (array_slice($data, $start, $limit) as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Company Name'] = $row->com_name;
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
    public function leaserDetailExport(Request $request)
    {
        if ($request['leaserDetail_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/leaser_details.csv";
            $fileName = env('APP_EXPORTURL') . "asset/leaser_details.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $request['id']);
        if ($request['leaserDetail_export'] == 0) {
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
                $val['ASSOCIATE CODE'] = getSeniorData($row->member_id, 'associate_no');
                $val['ASSOCIATE NAME'] = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                $val['ASSOCIATE CARDER'] = getCarderName(getSeniorData($row->member_id, 'current_carder_id'));
                $val['PAN NO'] = get_member_id_proof($row->member_id, 5);
                $val['TOTAL AMOUNT'] = number_format((float) $row->amount_tds, 2, '.', '');
                $val['TDS AMOUNT'] = number_format((float) $row->total_tds, 2, '.', '');
                $val['FINAL PAYABLE AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $val['TOTAL COLLECTION'] = number_format((float) $row->collection, 2, '.', '');
                $val['FUEL AMOUNT'] = number_format((float) $row->fuel, 2, '.', '');
                $ssbAccountDetail = getMemberSsbAccountDetail($row->member_id);
                $val['SSB ACCOUNT NO'] = $ssbAccountDetail->account_no;
                $status = '';
                if ($row->status == 1) {
                    $status = 'Transferred';
                } else {
                    $status = 'Deleted';
                }
                $val['STATUS'] = $status;
                $val['CREATED'] = date("d/m/Y H:i:s a", strtotime($row->created_at));
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
        } elseif ($request['leaserDetail_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.export_leaser_detail', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('leaserDetail.pdf');
        }
    }
    public function exportRentLiabilities(Request $request)
    {
        $data = RentLiability::with('liabilityBranch', 'liabilityFile')->has('company')->with('company');
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if (isset($request['branch_id']) && $request['branch_id'] > 0) {
                $branchId = $request['branch_id'];
                $data = $data->where('branch_id', '=', $branchId);
            }
            if (isset($request['company_id']) && $request['company_id'] > 0) {
                $company_id = $request['company_id'];
                $data = $data->where('company_id', $company_id);
            }
            if ($request['rent_type'] != '') {
                $rent_type = $request['rent_type'];
                $data = $data->where('rent_type', '=', $rent_type);
            }
        }
        $data = $data->orderby('id', 'DESC')->get();
        return Excel::download(new RentLiabilitiesExport($data), 'rentliabilities.xlsx');
    }
    /**
     * Export loan recovery list.
     *
     * @return \Illuminate\Http\Response
     */
    public function loanRecoveryExport(Request $request)
    {
        if ($request['loan_recovery_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/loan_recovery_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/loan_recovery_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = Memberloans::has('company')->whereHas('loans', function ($q) {
            $q->where('loan_type', 'L')->select('id', 'name', 'loan_type');
        })->with([
                    'loanMemberCompany' => function ($q) {
                        $q->select('id', 'member_id')
                            ->with([
                                'ssb_detail' => function ($q1) {
                                    $q1->select('id', 'account_no', 'member_id', 'customer_id')
                                        ->with(['getSSBAccountBalance']);
                                }
                            ]);
                    }
                ])
            ->with([
                'member:id,member_id,first_name,last_name',
                'MemberCompany:id,member_id',
                'CollectorAccount.member_collector',
                'loanMemberAssociate:id,first_name,last_name,associate_no',
                'loanBranch:id,name,branch_code,regan,zone,sector',
                'loanTransactionNew:id,deposit,group_loan_id,account_number',
                'company:id,name'
            ])->with([
                    'getOutstanding' => function ($q) {
                        $q->with([
                            'loans' => function ($q) {
                                $q->where('loan_type', '!=', 'G');
                            }
                        ]);
                    }
                ])->where('company_id', $request['company_id']);
        if ($request['date_from'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['date_from'])));
            if ($request['date_to'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['date_to'])));
            } else {
                $endDate = '';
            }
            if ($endDate) {
                $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
            } else {
                $data = $data->whereDate('approve_date', '>=', $startDate);
            }
        }
        if ($request['loan_recovery_plan'] != '') {
            $planId = $request['loan_recovery_plan'];
            $data = $data->where('loan_type', '=', $planId);
        }
        if ($request['loan_account_number'] != '') {
            $loan_account_number = $request['loan_account_number'];
            $data = $data->where('account_number', '=', $loan_account_number);
        }
        if ($request['member_name'] != '') {
            $name = $request['member_name'];
            $data = $data->whereHas('loanMember', function ($query) use ($name) {
                $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
            $data = $data->where('branch_id', $branch_id);
        }
        if ($request['customer_id'] != '') {
            $customer_id = $request['customer_id'];
            $data = $data->whereHas('loanMember', function ($query) use ($customer_id) {
                $query->where('members.member_id', 'LIKE', '%' . $customer_id . '%');
            });
        }
        if ($request['member_id'] != '') {
            $meid = $request['member_id'];
            $data = $data->whereHas('MemberCompany', function ($query) use ($meid) {
                $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
            });
        }
        if ($request['associate_code'] != '') {
            $associateCode = $request['associate_code'];
            $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
            });
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', '=', $status);
        }
        if ($request['loan_recovery_export'] == 0) {
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
                $val['COMPANY'] = $row['company']->name;
                $val['BR NAME'] = $row['loanBranch']->name;
                $val['BR CODE'] = $row['loanBranch']->branch_code;
                $val['SO NAME'] = $row['loanBranch']->sector;
                $val['RO NAME'] = $row['loanBranch']->regan;
                $val['ZO NAME'] = $row['loanBranch']->zone;
                $val['ACCCOUNT NO'] = $row->account_number;
                $val['MEMBER NAME'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                $val['MEMBER ID'] = $row['MemberCompany']->member_id;
                $val['CUSTOMER ID'] = $row['member']->member_id;
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
                $val['INSURANCE CHARGE'] = $row->insurance_charge;
                if (isset($row->insurance_cgst) || isset($row->filecharge_cgst)) {
                    $insurance_cgst = $row->insurance_cgst;
                    $insurance_sgst = $row->insurance_sgst;
                    $insurance_igst = 'N/A';
                    $filecharge_cgst = $row->filecharge_cgst;
                    $filecharge_sgst = $row->filecharge_sgst;
                    $filecharge_igst = 'N/A';
                } elseif (isset($row->insurance_charge_igst) || isset($row->filecharge_igst)) {
                    $insurance_igst = $row->insurance_charge_igst;
                    $insurance_cgst = 'N/A';
                    $insurance_sgst = 'N/A';
                    $filecharge_igst = $row->filecharge_igst;
                    $filecharge_cgst = 'N/A';
                    $filecharge_sgst = 'N/A';
                } else {
                    $insurance_cgst = 'N/A';
                    $insurance_sgst = 'N/A';
                    $insurance_igst = 'N/A';
                    $filecharge_igst = 'N/A';
                    $filecharge_sgst = 'N/A';
                    $filecharge_cgst = 'N/A';
                }
                $val['INSURANCE CGST'] = $insurance_cgst;
                $val['INSURANCE SGST'] = $insurance_sgst;
                $val['INSURANCE IGST'] = $insurance_igst;
                $tenure = '';
                if ($row->emi_option == 1) {
                    $tenure = $row->emi_period . ' Months';
                } elseif ($row->emi_option == 2) {
                    $tenure = $row->emi_period . ' Weeks';
                } elseif ($row->emi_option == 3) {
                    $tenure = $row->emi_period . ' Days';
                }
                $val['TENURE'] = $tenure;
                $val['TRANSER AMOUNT'] = $row->transfer_amount;
                $val['LOAN AMOUNT'] = $row->amount;
                $file_charge = '';
                if ($row->file_charges) {
                    $file_charge = $row->file_charges;
                } else {
                    $file_charge = 'N/A';
                }
                $val['FILE CHARGE'] = $file_charge;
                $file_charges_payment_mode = 'N/A';
                if ($row->file_charge_type) {
                    $file_charges_payment_mode = 'Loan';
                } else {
                    $file_charges_payment_mode = 'Cash';
                }
                $val['FILECHARGE CGST'] = $filecharge_cgst;
                $val['FILECHARGE SGST'] = $filecharge_sgst;
                $val['FILECHARGE IGST'] = $filecharge_igst;
                $val['FILE CHARGE PAYMENT MODE'] = $file_charges_payment_mode;
                $totalbalance = $row->emi_period * $row->emi_amount;
                $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;
                // $outstanding_amount =  $Finaloutstanding_amount
                $outAmount = $row->getOutstanding()->latest('created_at')->where('is_deleted', '0')->orderby('id', 'DESC')->first() ? $row->getOutstanding()->latest('created_at')->where('is_deleted', '0')->orderby('id', 'DESC')->first()->out_standing_amount : 0;
                $outstandingAmount = isset($outAmount)
                    ? ($outAmount > 0 ? $outAmount : 0)
                    : $row->amount;
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
                $associate_codes = '';
                if (isset($row['loanMemberCustom']->id)) {
                    // getMemberData($row->associate_member_id)
                    $associate_code = $row['loanMemberCustom'];
                    //getMemberData($row->associate_member_id);
                    $associate_codes = $associate_code->associate_no;
                } else {
                    $associate_codes = 'N/A';
                }
                $val['ASSOCIATE code'] = $associate_codes;
                //$member =Member::where('id',$row->associate_member_id)->first(['id','first_name','last_name']);
                if (isset($row['loanMemberCustom']->id)) {
                    $member_name = $row['loanMemberCustom']->first_name . ' ' . $row['loanMemberCustom']->last_name;
                }
                $val['ASSOCIATE NAME'] = $member_name;
                $val['TOTAL PAYMENT'] = $row['loanTransactionNew']->sum('deposit');
                $approve_date = '';
                $approve_dated = '';
                if ($row['approve_date']) {
                    $val['SANCTION DATE'] = date("d/m/Y", strtotime($row['approve_date']));
                } else {
                    $val['SANCTION DATE'] = 'N/A';
                }
                if ($row['approved_date']) {
                    $approve_dated = date("d/m/Y", strtotime($row['approved_date']));
                } else {
                    $approve_dated = 'N/A';
                }
                $val['APPROVED DdATE'] = $approve_dated;
                $val['APPLICATION DATE'] = date("d/m/Y", strtotime($row['created_at']));
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
                    $status = 'Pending';
                } else if ($row->status == 1) {
                    $status = 'Approved';
                } else if ($row->status == 2) {
                    $status = 'Rejected';
                } else if ($row->status == 3) {
                    $status = 'Clear';
                } else if ($row->status == 4) {
                    $status = 'Due';
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
        } elseif ($request['loan_recovery_export'] == 1) {
            $type = 1;
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.loan.export_loan_recovery_list', compact('data', 'type'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('LoanRecoveryList.pdf');
        }
    }
    /**
     * Export loan recovery list.
     *
     * @return \Illuminate\Http\Response
     */

    public function loanDetailsExport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('loan_report_list_exportAdmin' . $token);
        $count = Cache::get('loan_report_list_export_countAdmin' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $applicationDate = date("Y-m-d ", strtotime(convertDate($request['create_application_date'])));
        $returnURL = URL::to('/') . "/asset/loan-requests.csv";
        $fileName = env('APP_EXPORTURL') . "asset/loan-requests.csv";
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
        foreach (array_slice($data, $start, $limit) as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $applicant_id = '';
            if (isset($row['group_activity'])) {
                if ($row['group_activity'] == 'Group loan application') {
                    $applicant_id = isset($row['groupleader_member_id']) ? $row['groupleaderMemberIDCustom']->member_id : 'N/A'; //Member::find($row['groupleader_member_id'])->member_id;
                } else {
                    $applicant_id = '';
                }
            }
            $val['APPLICANT/GROUP LEADER ID'] = $row['group_loan_common_id'] ?? '';
            $val['APPLICATION NUMBER'] = $row['application_no'] ?? 'N/A';
            $val['ACCOUNT NO'] = $row['account_number'] ?? 'N/A';
            $val['COMPANY'] = $row['company'] ? $row['company']['name'] : 'N/A';
            $val['BR NAME'] = isset($row['loan_branch']) ? $row['loan_branch']['name'] : $row['gloan_branch']['name'];
            $val['BR CODE'] = isset($row['loan_branch']) ? $row['loan_branch']['branch_code'] : $row['gloan_branch']['branch_code'];
            $val['SO NAME'] = isset($row['loan_branch']) ? $row['loan_branch']['sector'] : $row['gloan_branch']['sector'];
            $val['RO NAME'] = isset($row['loan_branch']) ? $row['loan_branch']['regan'] : $row['gloan_branch']['regan'];
            $val['ZO NAME'] = isset($row['loan_branch']) ? $row['loan_branch']['zone'] : $row['gloan_branch']['zone'];
            $val['MEMBER ID'] = isset($row['member_company']['member_id']) ? $row['member_company']['member_id'] : 'N/A';
            $val['CUSTOMER ID'] = $row['loan_member'] ? $row['loan_member']['member_id'] : 'N/A';
            $val['MEMBER NAME'] = $row['loan_member'] ? $row['loan_member']['first_name'] . ' ' . $row['loan_member']['last_name'] : 'N/A';
            $val['TOTAL DEPOSIT'] = getAllDeposit($row['loan_member']['id'], $applicationDate);
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
            $val['ASSOCIATE CODE'] = (isset($row['loan_member_associate']['associate_no']) ? $row['loan_member_associate']['associate_no'] : '');
            $val['ASSOCIATE NAME'] = isset($row['loan_member_associate']) ? $row['loan_member_associate']['first_name'] . ' ' . $row['loan_member_associate']['last_name'] : 'N/A';
            $val['LOAN PLAN'] = strtoupper($row['loan']['name']);
            $val['Loan Tenure'] = $row['emi_period'];
            $val['Emi option'] = ($row['emi_option'] == 1) ? 'Month' : (($row['emi_option'] == 2) ? 'Week' : 'Daily');
            $val['TRANSFER AMOUNT'] = $row['transfer_amount'];
            $val['Transfer Date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
            $val['LOAN AMOUNT'] = $row['amount'];
            $val['FILE CHARGE'] = $row['file_charges'];
            $val['IGST FILE CHARGE'] = $row['filecharge_igst'] ?? 'N/A';
            $val['CGST FILE CHARGE'] = $row['filecharge_cgst'] ?? 'N/A';
            $val['SGST FILE CHARGE'] = $row['filecharge_sgst'] ?? 'N/A';
            $val['INSURANCE CHARGE'] = $row['insurance_charge'];
            $val['IGST INSURANCE CHARGE'] = $row['insurance_charge_igst'] ?? 'N/A';
            $val['CGST INSURANCE CHARGE'] = $row['insurance_cgst'] ?? 'N/A';
            $val['SGSST INSURANCE CHARGE'] = $row['insurance_sgst'] ?? 'N/A';

            $val['ECS REFERENCE NO'] = $row['ecs_ref_no'] ?? '';
            $val['ECS CHARGE'] = $row['ecs_charges'] ?? 0.00;

            $val['IGST ECS CHARGE'] = $row['ecs_charge_igst'] ?? 'N/A';
            $val['CGST ECS CHARGE'] = $row['ecs_charge_cgst'] ?? 'N/A';
            $val['SGST ECS CHARGE'] = $row['ecs_charge_sgst'] ?? 'N/A';
            $status = '';
            if ($row['status'] == 0) {
                $status = 'Pending';
            } else if ($row['status'] == 1) {
                $status = 'Approved';
            } else if ($row['status'] == 2) {
                $status = 'Rejected';
            } else if ($row['status'] == 3) {
                $status = 'Clear';
            } else if ($row['status'] == 4) {
                $status = 'Due';
            } else if ($row['status'] == 7) {
                $status = 'Approved but hold';
            } else if ($row['status'] == 8) {
                $status = 'Cancel';
            }
            $applicationBankDetail = loanApplicatBankDetail($row['id']);
            $getMemberCompanyDataNew = getMemberCompanyDataNew($row['customer_id']);
            // as per new update changes are made by sourab on sachinn sir permmission
            // $val['BANK NAME'] = isset($row['loan_member_bank_details2']) ? $row['loan_member_bank_details2']['bank_name'] : ($applicationBankDetail ? $applicationBankDetail->bank_name : 'N/A');
            // $val['BANK ACCOUNT NUMBER'] = isset($row['loan_member_bank_details2']) ? '"'.$row['loan_member_bank_details2']['account_no'].'"' : ($applicationBankDetail ? '"'.$applicationBankDetail->bank_account_number.'"' : 'N/A');
            // $val['IFSC CODE'] = isset($row['loan_member_bank_details2']) ? $row['loan_member_bank_details2']['ifsc_code'] : ($applicationBankDetail ? $applicationBankDetail->ifsc_code : 'N/A');
            // $val['BANK NAME'] = ($row['loan']['loan_type'] == 'G') ? $getMemberCompanyDataNew->member['memberBankDetails'][0]->bank_name : ($applicationBankDetail ? $applicationBankDetail->bank_name : 'N/A');
            $val['BANK NAME'] = ($row['loan']['loan_type'] == 'G') ? ($getMemberCompanyDataNew->member['memberBankDetails'][0]->bank_name ?? 'N/A') : ($applicationBankDetail ? $applicationBankDetail->bank_name ?? 'N/A' : 'N/A');

            // $val['BANK ACCOUNT NUMBER'] = ($row['loan']['loan_type'] == 'G') ? "'".$getMemberCompanyDataNew->member['memberBankDetails'][0]->account_no."'" : ($applicationBankDetail ? '"'.$applicationBankDetail->bank_account_number.'"' : 'N/A');
            $val['BANK ACCOUNT NUMBER'] = ($row['loan']['loan_type'] == 'G') ?
                "'" . ($getMemberCompanyDataNew->member['memberBankDetails'][0]->account_no ?? 'N/A') . "'" :
                ($applicationBankDetail ? '"' . ($applicationBankDetail->bank_account_number ?? 'N/A') . '"' : 'N/A');


            // $val['IFSC CODE'] = ($row['loan']['loan_type'] == 'G') ? $getMemberCompanyDataNew->member['memberBankDetails'][0]->ifsc_code : ($applicationBankDetail ? $applicationBankDetail->ifsc_code : 'N/A');
            $val['IFSC CODE'] = ($row['loan']['loan_type'] == 'G') ?
                ($getMemberCompanyDataNew->member['memberBankDetails'][0]->ifsc_code ?? 'N/A') :
                ($applicationBankDetail ? ($applicationBankDetail->ifsc_code ?? 'N/A') : 'N/A');

            $val['STATUS'] = $status;
            $val['APPROVED DATE'] = ($row['approved_date']) ? date("d/m/Y", strtotime($row['approved_date'])) : 'N/A';
            $val['CREATE DATE'] = date("d/m/Y", strtotime($row['created_at']));
            $val['RUNNING LOAN ACCOUNT NUMBER'] = getMemberCurrentRunningLoan($row['customer_id'], $row['loan']['loan_type'] == "L" ? true : false, $row['account_number']);
            $val['RUNNING LOAN CLOSING AMOUNT'] = getMemberCurrentRunningClosingAmount($row['customer_id'], $row['loan']['loan_type'] == "L" ? true : false, $row['account_number']);
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
        //if($percentage > 100){
        //return Excel::download(new LoanReportListExport($DataArray), $fileName);
        //}else{
        //Excel::store(new LoanReportListExport($DataArray), $fileName);
        echo json_encode($response);
        //}
    }
    /**
     * Export group loan recovery list.
     *
     * @return \Illuminate\Http\Response
     */
    public function groupLoanRecoveryExport(Request $request)
    {
        if ($request['group_loan_recovery_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/group-loans-recovery.csv";
            $fileName = env('APP_EXPORTURL') . "asset/group-loans-recovery.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $companyId = $request['company_id'];
        $data = \App\Models\Grouploans::has('company')->with([
            'loanMemberCompanyid' => function ($q) use ($companyId) {
                $q->select('id', 'member_id')
                    ->with([
                        'ssb_detail' => function ($q1) use ($companyId) {
                            $q1->select('id', 'account_no', 'member_id', 'customer_id')->where('company_id', $companyId)
                                ->with(['getSSBAccountBalance']);
                        }
                    ]);
            }
        ])
            ->whereHas('loans', function ($q) {
                $q->where('loan_type', 'G')->select('id', 'name', 'loan_type');
            })
            ->with('loanMember:id,member_id,first_name,last_name', 'getOutstanding:loan_id,id,out_standing_amount,is_deleted,loan_type', 'CollectorAccount.member_collector', 'loanMemberAssociate:id,first_name,last_name,associate_no', 'gloanBranch:id,name,branch_code,state_id,sector,regan,zone', 'company:id,name')
            ->with([
                'loanTransaction' => function ($q1) {
                    $q1->select('id', 'deposit', 'group_loan_id', 'account_number')->whereIn('loan_sub_type', [0, 1]);
                }
            ]);
        if ($request['date_from'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['date_from'])));
            if ($request['date_to'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['date_to'])));
            } else {
                $endDate = '';
            }
            if ($endDate) {
                $data = $data->whereBetween(\DB::raw('DATE(approve_date)'), [$startDate, $endDate]);
            } else {
                $data = $data->whereDate('approve_date', '>=', $startDate);
            }
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
            if ($branch_id != '0') {
                $data = $data->where('branch_id', '=', $branch_id);
            }
        }
        if ($request['company_id'] > 0) {
            $company_id = $request['company_id'];
            if ($company_id != '0') {
                $data = $data->where('company_id', '=', $company_id);
            }
        }
        if ($request['loan_recovery_plan'] != '') {
            $planId = $request['loan_recovery_plan'];
            $data = $data->where('loan_type', '=', $planId);
        }
        if ($request['loan_account_number'] != '') {
            $loan_account_number = $request['loan_account_number'];
            $data = $data->where('account_number', '=', $loan_account_number);
        }
        if ($request['group_loan_common_id'] != '') {
            $group_loan_common_id = $request['group_loan_common_id'];
            $data = $data->where('group_loan_common_id', '=', $group_loan_common_id);
        }
        if ($request['member_name'] != '') {
            $name = $request['member_name'];
            $data = $data->whereHas('loanMember', function ($query) use ($name) {
                $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['member_id'] != '') {
            $meid = $request['member_id'];
            $data = $data->whereHas('loanMemberCompany', function ($query) use ($meid) {
                $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
            });
        }
        if ($request['customer_id'] != '') {
            $meid = $request['customer_id'];
            $data = $data->whereHas('loanMember', function ($query) use ($meid) {
                $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
            });
        }
        if ($request['associate_code'] != '') {
            $associateCode = $request['associate_code'];
            $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
            });
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', '=', $status);
        }
        if ($request['group_loan_recovery_export'] == 0) {
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
                $val['COMPANY NAME'] = $row['company']->name;
                $val['BR NAME'] = $row['gloanBranch']->name;
                $val['BR CODE'] = $row['gloanBranch']->branch_code;
                $val['SO NAME'] = $row['gloanBranch']->sector;
                $val['RO NAME'] = $row['gloanBranch']->regan;
                $val['ZO NAME'] = $row['gloanBranch']->zone;
                $val['GROUP LOAN COMMON ID'] = $row->group_loan_common_id;
                $val['ACCOUNT NO'] = $row->account_number;
                $val['MEMBER NAME'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                $val['MEMBER ID'] = $row['loanMemberCompanyid']->member_id;
                $val['CUSTOMER ID'] = $row['loanMember']->member_id;
                $val['LOAN TYPE'] = 'Group Loan';
                // pd($row->toArray());
                $tenure = '';
                if ($row->emi_option == 1) {
                    $tenure = $row->emi_period . ' Months';
                } elseif ($row->emi_option == 2) {
                    $tenure = $row->emi_period . ' Weeks';
                } elseif ($row->emi_option == 3) {
                    $tenure = $row->emi_period . ' Days';
                }
                $val['INSURANCE CHARGE'] = $row->insurance_charge;
                if (isset($row->insurance_cgst) || isset($row->filecharge_cgst)) {
                    $insurance_cgst = $row->insurance_cgst;
                    $insurance_sgst = $row->insurance_sgst;
                    $insurance_igst = 'N/A';
                    $filecharge_cgst = $row->filecharge_cgst;
                    $filecharge_sgst = $row->filecharge_sgst;
                    $filecharge_igst = 'N/A';
                } elseif (isset($row->insurance_charge_igst) || isset($row->filecharge_igst)) {
                    $insurance_igst = $row->insurance_charge_igst;
                    $insurance_cgst = 'N/A';
                    $insurance_sgst = 'N/A';
                    $filecharge_igst = $row->filecharge_igst;
                    $filecharge_cgst = 'N/A';
                    $filecharge_sgst = 'N/A';
                } else {
                    $insurance_cgst = 'N/A';
                    $insurance_sgst = 'N/A';
                    $insurance_igst = 'N/A';
                    $filecharge_igst = 'N/A';
                    $filecharge_sgst = 'N/A';
                    $filecharge_cgst = 'N/A';
                }
                $val['INSURANCE CGST'] = $insurance_cgst;
                $val['INSURANCE SGST'] = $insurance_sgst;
                $val['INSURANCE IGST'] = $insurance_igst;
                $val['TENURE'] = $tenure;
                $val['TRANSFER AMOUNT'] = $row->transfer_amount;
                $val['LOAN AMOUNT'] = $row->amount;
                $file_charge = '';
                if ($row->file_charges) {
                    $file_charge = $row->file_charges;
                } else {
                    $file_charge = 'N/A';
                }
                $val['FILE CHARGE'] = $file_charge;
                $file_charges_payment_mode = 'N/A';
                if ($row->file_charge_type) {
                    $file_charges_payment_mode = 'Loan';
                } else {
                    $file_charges_payment_mode = 'Cash';
                }
                $val['FILE CHARGE CGST'] = $filecharge_cgst;
                $val['FILE CHARGE SGST'] = $filecharge_sgst;
                $val['FILE CHARGE IGST'] = $filecharge_igst;
                $val['FILE CHARGE PAYMENT MODE'] = $file_charges_payment_mode;
                $totalbalance = $row->emi_period * $row->emi_amount;
                // $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;
                $Finaloutstanding_amount = (isset($row['getOutstanding']->out_standing_amount)) ? $row['getOutstanding']->out_standing_amount : 0;
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
                ;
                // $outstandingAmount = isset($row['get_outstanding']['out_standing_amount'])
                // ? ($row['get_outstanding']['out_standing_amount'] > 0 ? $row['get_outstanding']['out_standing_amount'] : 0)
                // :  $row['amount'];
                /** this code is commentewd by Sourab on 16-10-2023 for updateing outstanding amount */
                // $outstandingAmount = isset($row['getOutstanding']->out_standing_amount)  ? ($row['getOutstanding']->out_standing_amount > 0 ? $row['getOutstanding']->out_standing_amount : 0) : $row->amount;
                /** this code is commentewd by Sourab on 16-10-2023 for updateing outstanding amount */
                $outAmount = $row->getOutstanding()->latest('created_at')->first() ? $row->getOutstanding()->latest('created_at')->first()->out_standing_amount : 0;
                $outstandingAmount = isset($outAmount)
                    ? ($outAmount > 0 ? $outAmount : 0)
                    : $row->amount;
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
                // $associate_code =  getMemberData($row->associate_member_id);
                $val['ASSOCIATE CODE'] = $row['loanMemberAssociate']->associate_no;
                $member = Member::where('id', $row->associate_member_id)->first(['id', 'first_name', 'last_name']);
                $val['ASSOCIATE NAME'] = $member->first_name . ' ' . $member->last_name;
                $val['TOTAL PAYMENT'] = $row['loanTransactionNew']->sum('deposit');
                $approve_date = '';
                $approve_dated = '';
                if ($row['approve_date']) {
                    $approve_date = date("d/m/Y", strtotime($row['approve_date']));
                } else {
                    $approve_date = 'N/A';
                }
                $val['SANCTION DATE'] = $approve_date;
                if ($row['approved_date']) {
                    $approve_dated = date("d/m/Y", strtotime($row['approved_date']));
                } else {
                    $approve_dated = 'N/A';
                }
                $val['APPROVED DATE'] = $approve_dated;
                $val['APPLICATION DATE'] = date("d/m/Y", strtotime($row['created_at']));
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
                $status = 'N/A';
                if ($row->status == 0) {
                    $status = 'Pending';
                } else if ($row->status == 1) {
                    $status = 'Approved';
                } else if ($row->status == 2) {
                    $status = 'Rejected';
                } else if ($row->status == 3) {
                    $status = 'Clear';
                } else if ($row->status == 4) {
                    $status = 'Due';
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
        } elseif ($request['group_loan_recovery_export'] == 1) {
            $type = 3;
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.loan.export_loan_recovery_list', compact('data', 'type'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('GroupLoanRecoveryList.pdf');
        }
    }
    /**
     * Export GRoup loan deatils list.
     *
     * @return \Illuminate\Http\Response
     */

    public function groupLoanDetailsExport(Request $request)
    {
        if ($request['group_loan_details_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/group-loan-requests.csv";
            $fileName = env('APP_EXPORTURL') . "asset/group-loan-requests.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $applicationDate = date("Y-m-d ", strtotime(convertDate($request['create_application_date'])));
        $data = \App\Models\Grouploans::with('loanMember', 'loanMemberAssociate', 'groupleaderMemberIDCustom', 'MemberApplicantCustom', 'loanMemberBankDetails2')->with([
            'gloanBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->whereIn('status', [0, 1, 3, 5, 6, 7, 4]);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        if ($request['date_from'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['date_from'])));
            if ($request['date_to'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['date_to'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $branch_id);
        }
        if ($request['application_number'] != '') {
            $application_number = $request['application_number'];
            $data = $data->where('account_number', '=', $application_number);
        }
        if ($request['group_loan_common_id'] != '') {
            $group_loan_common_id = $request['group_loan_common_id'];
            $data = $data->where('group_loan_common_id', '=', $group_loan_common_id);
        }
        if ($request['member_name'] != '') {
            $name = $request['member_name'];
            $data = $data->whereHas('loanMember', function ($query) use ($name) {
                $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
            });
        }
        if ($request['member_id'] != '') {
            $meid = $request['member_id'];
            $data = $data->whereHas('loanMember', function ($query) use ($meid) {
                $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
            });
        }
        if ($request['associate_code'] != '') {
            $associateCode = $request['associate_code'];
            $data = $data->whereHas('loanMember', function ($query) use ($associateCode) {
                $query->where('members.associate_code', 'LIKE', '%' . $associateCode . '%');
            });
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', '=', $status);
        }
        if ($request['group_loan_details_export'] == 0) {
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
                $applicant_id = '';
                if ($row->group_activity == 'Group loan application') {
                    if ($row->groupleader_member_id) {
                        $applicant_id = $row['groupleaderMemberIDCustom']->member_id; //Member::find($row->groupleader_member_id)->member_id;
                    } else {
                        $applicant_id = '';
                    }
                } else {
                    if ($row->applicant_id) {
                        $applicant_id = $row['MemberApplicantCustom']->member_id; //Member::find($row->applicant_id)->member_id;
                    } else {
                        $applicant_id = '';
                    }
                }
                $val['APPLICANT/GROUP LEADER ID'] = $applicant_id;
                $val['GROUP LOAN COMMON ID'] = $row->group_loan_common_id;
                $val['ACCOUNT NO'] = $row->account_number;
                $val['BR NAME'] = $row['gloanBranch']->name;
                ;
                $val['BR CODE'] = $row['gloanBranch']->branch_code;
                $val['SO NAME'] = $row['gloanBranch']->sector;
                $val['RO NAME'] = $row['gloanBranch']->regan;
                $val['ZO NAME'] = $row['gloanBranch']->zone;
                $val['MEMBER ID'] = $row['loanMember']->member_id;
                $val['MEMBER NAME'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                $val['TOTAL DEPOSIT'] = getAllDeposit($row['loanMember']->id, $applicationDate);
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
                $associate_code = $row['loanMemberAssociate']; //getMemberData($row->associate_member_id);
                $val['ASSOCIATE CODE'] = $associate_code->associate_no;
                $member = $row['loanMemberAssociate']; //Member::where('id',$row->associate_member_id)->first(['id','first_name','last_name']);
                $val['ASSOCIATE NAME'] = $member->first_name . ' ' . $member->last_name;
                if (isset($row['loanMemberBankDetails2']->bank_name)) {
                    $bankName = $row['loanMemberBankDetails2']->bank_name;
                } else {
                    $bankName = 'N/A';
                }
                $val['BANK NAME'] = $bankName;
                if (isset($row['loanMemberBankDetails2']->account_no)) {
                    $bankAccount = $row['loanMemberBankDetails2']->account_no;
                } else {
                    $bankAccount = 'N/A';
                }
                $val['BANK ACCOUNT NUMBER'] = $bankAccount;
                if (isset($row['loanMemberBankDetails2']->ifsc_code)) {
                    $ifscCode = $row['loanMemberBankDetails2']->ifsc_code;
                } else {
                    $ifscCode = 'N/A';
                }
                $val['IFSC CODE'] = $ifscCode;
                $val['LOAN TYPE'] = 'Group Loan';
                $val['TRANSFER AMOUNT'] = $row->deposite_amount;
                $val['LOAN AMOUNT'] = $row->amount;
                $val['FILE CHARGE AMOUNT'] = $row->file_charges;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Pending';
                } else if ($row->status == 1) {
                    $status = 'Approved';
                } else if ($row->status == 2) {
                    $status = 'Rejected';
                } else if ($row->status == 3) {
                    $status = 'Clear';
                } else if ($row->status == 4) {
                    $status = 'Due';
                } else if ($row->status == 7) {
                    $status = 'Approved but hold';
                }
                $val['STATUS'] = $status;
                $approve_date = '';
                if ($row['approved_date']) {
                    $approve_date = date("d/m/Y", strtotime($row['approved_date']));
                } else {
                    $approve_date = 'N/A';
                }
                $val['APPROVE DATE'] = $approve_date;
                $val['APPLICATION DATE'] = date("d/m/Y", strtotime($row['created_at']));
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
        } elseif ($request['group_loan_details_export'] == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.loan.export_group_loan_details_list', compact('data', 'applicationDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('GroupLoanDetailsList.pdf');
        }
    }
    /**
     * cheque  List.
     *
     * @return \Illuminate\Http\Response
     */

    public function chequeExport(Request $request)
    {
        if ($request['cheque_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/cheque_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/cheque_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\SamraddhCheque::with([
            'samrddhBank' => function ($query) {
                $query->has('company')->select('id', 'bank_name', 'company_id')->with('company');
            }
        ])->with([
                    'samrddhAccount' => function ($query) {
                        $query->select('id', 'account_no');
                    }
                ]);
        if ($request['bank_id'] != '') {
            $bank_id = $request['bank_id'];
            $data = $data->where('bank_id', $bank_id);
        }
        if (isset($request['company_id']) && $request['company_id'] != '') {
            $companyId = $request['company_id'];
            if ($companyId != '0') {
                $data = $data->whereHas('samrddhBank.company', function ($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                });
            }
        }
        if ($request['account_id'] != '') {
            $id = $request['account_id'];
            $data = $data->where('account_id', $id);
        }
        if ($request['cheque_no'] != '') {
            $cheque_no = $request['cheque_no'];
            $data = $data->where('cheque_no', $cheque_no);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['CHEQUE DATE'] = date("d/m/Y", strtotime($row->cheque_create_date));
                $val['BANK NAME'] = $row['samrddhBank']->bank_name;
                $val['ACCOUNT NO'] = $row['samrddhAccount']->account_no;
                $val['CHEQUE NO'] = $row->cheque_no;
                $use = '';
                if ($row->is_use == 1) {
                    $use = 'Yes';
                } else {
                    $use = 'No';
                }
                $val['IS USED'] = $use;
                $status = '';
                $status = 'New';
                if ($row->status == 1) {
                    $status = 'New';
                }
                if ($row->status == 2) {
                    $status = 'Pending';
                }
                if ($row->status == 3) {
                    $status = 'cleared';
                }
                if ($row->status == 4) {
                    $status = 'Canceled & Re-issued';
                }
                if ($row->status == 0) {
                    $status = 'Deleted';
                }
                $val['STATUS'] = $status;
                $cheque_delete_date = '';
                if ($row->cheque_delete_date != NULL) {
                    $cheque_delete_date = date("d/m/Y", strtotime($row->cheque_delete_date));
                }
                $val['DELETE DATE'] = $cheque_delete_date;
                $cheque_cancel_date = '';
                if ($row->cheque_cancel_date != NULL) {
                    $cheque_cancel_date = date("d/m/Y", strtotime($row->cheque_cancel_date));
                }
                $val['CANCEL DATE'] = $cheque_cancel_date;
                $val['CANCEL REMARK'] = $row->remark_cancel;
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
        } elseif ($request['cheque_export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.cheque_management.export_cheque', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('SmarddhChequeList.pdf');
        }
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
            $returnURL = URL::to('/') . "/asset/received_cheque.csv";
            $fileName = env('APP_EXPORTURL') . "asset/received_cheque.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\ReceivedCheque::has('receivedCompany')->with([
            'receivedBank' => function ($query) {
                $query->select('id', 'bank_name');
            }
        ])->with([
                    'receivedAccount' => function ($query) {
                        $query->select('id', 'account_no');
                    }
                ])->with([
                    'receivedCompany' => function ($query) {
                        $query->select('id', 'name');
                    }
                ])->with([
                    'receivedBranch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ]);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
            if ($branch_id != '0') {
                $data = $data->where('branch_id', $branch_id);
            }
        }
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            if ($company_id != '0') {
                $data = $data->where('company_id', $company_id);
            }
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = $row['receivedCompany']->name;
                // $val['BR NAME'] = $row['receivedBranch']->name;
                // $val['BR CODE'] = $row['receivedBranch']->branch_code;
                // $val['SO NAME'] = $row['receivedBranch']->sector;
                // $val['RO NAME'] = $row['receivedBranch']->regan;
                // $val['ZO NAME'] = $row['receivedBranch']->zone;
                $val['CHEQUE UTR DATE'] = date("d/m/Y", strtotime($row->cheque_create_date));
                $val['CHEQUE /UTR NO'] = $row->cheque_no;
                $val['CHEQUE/UTR BANK NAME'] = $row->bank_name;
                $val['bank_branch_name'] = $row->branch_name;
                $val['account_holder_name'] = $row->account_holder_name;
                $val['cheque_account_no'] = $row->cheque_account_no;
                $val['RAMARK'] = $row->remark;
                $val['AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $val['deposit_bank_id'] = $row['receivedBank']->bank_name;
                $val['deposit_account_id'] = $row['receivedAccount']->account_no;
                $val['cheque_deposit_date'] = date("d/m/Y", strtotime($row->cheque_deposit_date));
                if ($row->status == 3) {
                    if ($row['receivedChequePayment']) {
                        $val['used_date'] = date("d/m/Y", strtotime($row['receivedChequePayment']->created_at));
                    } else {
                        $val['used_date'] = "N/A";
                    }
                } else {
                    $val['used_date'] = 'N/A';
                }
                if ($row->clearing_date) {
                    $val['clearing_date'] = date("d/m/Y", strtotime($row->clearing_date));
                } else {
                    $val['clearing_date'] = 'N/A';
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
                $val['status'] = $status;
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
        } elseif ($request['cheque_export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.cheque_management.export_received_cheque', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ReceivedChequeList.pdf');
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
        $fileName = env('APP_EXPORTURL') . "asset/employee_transfer_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = \App\Models\EmployeeTransfer::with('transferEmployee.company:id,name')->with([
            'oldDesignation',
            'designation',
            'transferBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->with([
                    'transferBranchOld' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ])->with([
                    'transferEmployee' => function ($query) {
                        $query->select('*');
                    }
                ]);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('old_branch_id', Auth::user()->branch_id);
        }
        if ($request['branch'] != '') {
            $branch = $request['branch'];
            $data = $data->where('old_branch_id', $branch);
        }
        if ($request['employee_code'] != '') {
            $employee_code = $request['employee_code'];
            $data = $data->whereHas('transferEmployee', function ($query) use ($employee_code) {
                $query->where('employee_code', $employee_code);
            });
        }
        if ($request['reco_employee_name'] != '') {
            $reco_employee_name = $request['reco_employee_name'];
            $data = $data->whereHas('transferEmployee', function ($query) use ($reco_employee_name) {
                $query->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
            });
        }
        if ($request['category'] != '') {
            $categoryid = $request['category'];
            $data = $data->whereHas('transferEmployee', function ($query) use ($categoryid) {
                $query->where('category', $categoryid);
            });
        }
        if ($request['designation'] != '') {
            $designation = $request['designation'];
            $data = $data->whereHas('transferEmployee', function ($query) use ($designation) {
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
        if ($request['report_export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['Apply DaTE'] = date("d/m/Y", strtotime($row->apply_date));
                $val['Company Name'] = $row->transferEmployee->company ? $row->transferEmployee->company->name : 'N/A';
                $val['Employee Code'] = $row['transferEmployee']->employee_code;
                $val['Employee Name'] = $row['transferEmployee']->employee_name;
                $val['Old Designation'] = $row['oldDesignation']->designation_name; //getDesignationData('designation_name',$row->old_designation_id)->designation_name;
                $old_category = '';
                if ($row->old_category == 1) {
                    $old_category = 'On-rolled';
                }
                if ($row->old_category == 2) {
                    $old_category = 'Contract';
                }
                $val['Old Category'] = $old_category;
                $val['Old Branch'] = $row['transferBranchOld']->name;
                $val['Old Branch code'] = $row['transferBranchOld']->branch_code;
                $val['Old Regan'] = $row['transferBranchOld']->regan;
                $val['Old Zone'] = $row['transferBranchOld']->zone;
                $val['Rec emp name old'] = $row->old_recommendation_name;
                $val['Transfer Date'] = date("d/m/Y", strtotime($row->transfer_date));
                $val['Br Name'] = $row['transferBranch']->name;
                $val['Br Code'] = $row['transferBranch']->branch_code;
                $val['So Name'] = $row['transferBranch']->sector;
                $val['Ro Name'] = $row['transferBranch']->regan;
                $val['Zo Name'] = $row['transferBranch']->zone;
                $val['Designation'] = $row['designation']->designation_name; //getDesignationData('designation_name',$row->designation_id)->designation_name;
                $category = '';
                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                $val['Category'] = $category;
                $val['Rec Emp Name'] = $row->recommendation_name;
                $val['file'] = $row->file;
                $val['Created'] = date("d/m/Y", strtotime($row->created_at));
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
    /**
     * Employee  list.
     *
     * @return \Illuminate\Http\Response
     */

    public function employeeExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/hr_employee.csv";
        $fileName = env('APP_EXPORTURL') . "asset/hr_employee.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = Employee::with(['branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->with([
                    'company' => function ($q) {
                        $q->select(['id', 'name']);
                    }
                ])
            ->with(['designation' => function ($query) {
                $query->select('id', 'designation_name'); }])
            ->with(['empApp:id,employee_id,application_type,status'])
            ->where('is_employee', 1);
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $data = $data->whereIn('branch_id', explode(",", $branch_ids));
        }
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
            $data = $data->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
        }
        if (isset($request['branch_id']) && $request['branch_id'] != '') {
            $branch = $request['branch_id'];
            if ($branch != '0') {
                $data = $data->where('branch_id', $branch);
            }
        }
        if (isset($request['company_id']) && $request['company_id'] != '') {
            $companyId = $request['company_id'];
            if ($companyId != '0') {
                $data = $data->where('company_id', $companyId);
            }
        }
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
        if ($request['emp_export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = $row['company'] ? $row['company']->name : '';
                $val['DESIGNATION'] = $row['designation']->designation_name; //getDesignationData('designation_name',$row->designation_id)->designation_name;
                $category = '';
                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                $val['CATEGORY'] = $category;
                $val['BR NAME'] = $row['branch']->name ?? 'N/A';
                $val['BR CODE'] = $row['branch']->branch_code ?? 'N/A';
                $val['SO NAME'] = $row['branch']->sector ?? 'N/A';
                $val['RO NAME'] = $row['branch']->regan ?? 'N/A';
                $val['ZO NAME'] = $row['branch']->zone ?? 'N/A';
                $val['Recommendation Employee Name'] = $row->recommendation_employee_name;
                $val['Employee Name'] = $row->employee_name;
                $val['Employee Code'] = $row->employee_code;
                $val['DOB'] = date("d/m/Y", strtotime($row->dob));
                $gender = 'Other';
                if ($row->gender == 1) {
                    $gender = 'Male';
                }
                if ($row->gender == 2) {
                    $gender = 'Female';
                }
                $val['GENDAR'] = $gender;
                $val['NUMBER'] = $row->mobile_no;
                $val['EMAIL ID'] = $row->email;
                $val['GUARDIAN NAME'] = $row->father_guardian_name;
                $val['GUARDIAN NUMBER'] = $row->father_guardian_number;
                $val['MOTHER NAME'] = $row->mother_name;
                $val['PAN CARD'] = $row->pen_card;
                $val['AADHAR CARD'] = ($row->aadhar_card != "")?'"' . $row->aadhar_card . '"': "";
                $val['VOTER ID'] = $row->voter_id;
                $val['ESI ACCOUNT NO.'] = ($row->esi_account_no != "")?'"' . $row->esi_account_no . '"': "";
                $val['UAN/PF  ACCOUNT NO.'] = ($row->pf_account_no != "")?'"' . $row->pf_account_no . '"': "";
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
                if ($row->empApp && $row->empApp->application_type == 2) {
                    if ($row->empApp->status == 0) {
                        $resign = 'Pending';
                    }
                    if ($row->empApp->status == 1) {
                        $resign = 'Approved';
                    }
                    if ($row->empApp->status == 3) {
                        $resign = 'Rejected';
                    }
                    if ($row->empApp->status == 9) {
                        $resign = 'Deleted';
                    }
                }
                $val['IS RESION'] = $resign;
                $terminate = 'No';
                if ($row->is_terminate == 1) {
                    $terminate = 'Yes';
                }
                $val['IS TERMINATED'] = $terminate;
                $transfer = 'No';
                if ($row->is_transfer == 1) {
                    $transfer = 'Yes';
                }
                $val['IS TRANSFERRED'] = $transfer;
                $val['Created'] = date("d/m/Y", strtotime($row->created_at));
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
        $returnURL = URL::to('/') . "/asset/hr_employee_application.csv";
        $fileName = env('APP_EXPORTURL') . "asset/hr_employee_application.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        // $data = EmployeeApplication::with(['branch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->with(['employeeget' => function($query){ $query->select('*')->with('designation');}]);
        $data = EmployeeApplication::with('branch:id,name,branch_code,sector,regan,zone')
            ->with('employeeget:id,category,recommendation_employee_name,employee_name,dob,gender,mobile_no,email,father_guardian_name,father_guardian_number,mother_name,pen_card,aadhar_card,voter_id,designation_id,esi_account_no,pf_account_no,is_employee')
            ->with('company:id,name')
            ->with('employeeget.designation:id,designation_name')/*->whereHas('employeeget', function ($query) {
$query->where('is_employee', 0);
})*/ ->whereNotIn('status', [9]);
            // ->where(function ($query) {
            //     $query->where('application_type', 1)
            //         ->WhereHas('employeeget', function ($query) {
            //             $query->where('is_employee', 0);
            //         });
            // });
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $data = $data->whereIn('branch_id', explode(",", $branch_ids));
        }
        if (isset($request['app_type'])) {
            $app_type = $request['app_type'];
            $data = $data->where('application_type', $app_type);
        }
        if ($request['employee_name'] != '') {
            $employee_name = $request['employee_name'];
            $data = $data->whereHas('employeeget', function ($query) use ($employee_name) {
                $query->where('employee_name', 'LIKE', '%' . $employee_name . '%');
            });
        }
        if ($request['reco_employee_name'] != '') {
            $reco_employee_name = $request['reco_employee_name'];
            $data = $data->whereHas('employeeget', function ($query) use ($reco_employee_name) {
                $query->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
            });
        }
        if (isset($request['branch_id']) && $request['branch_id'] != '') {
            $branch = $request['branch_id'];
            if ($branch != '0') {
                $data = $data->where('branch_id', $branch);
            }
        }
        if (isset($request['company_id']) && $request['company_id'] != '') {
            $companyId = $request['company_id'];
            if ($companyId != '0') {
                $data = $data->where('company_id', $companyId);
            }
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
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
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
        if ($request['emp_export'] == 0) {
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
                $val['Application Type'] = $application_type;
                $val['designation'] = $row['employeeget']['designation']->designation_name; //getDesignationData('designation_name',$row['employeeget']->designation_id)->designation_name;
                $category = '';
                if ($row['employeeget']->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row['employeeget']->category == 2) {
                    $category = 'Contract';
                }
                $val['CATEGORY'] = $category;
                $val['BR NAME'] = $row['branch']->name ?? "NA";
                $val['BR CODE'] = $row['branch']->branch_code ?? "NA";
                $val['SO NAME'] = $row['branch']->sector ?? "NA";
                $val['RO NAME'] = $row['branch']->regan ?? "NA";
                $val['ZO NAME'] = $row['branch']->zone ?? "NA";
                $val['Recommendation Employee Name'] = $row['employeeget']->recommendation_employee_name;
                $val['Employee Name'] = $row['employeeget']->employee_name;
                $val['Dob'] = date("d/m/Y", strtotime($row['employeeget']->dob));
                $gender = 'Other';
                if ($row['employeeget']->gender == 1) {
                    $gender = 'Male';
                }
                if ($row['employeeget']->gender == 2) {
                    $gender = 'Female';
                }
                $val['Gender'] = $gender;
                $val['Number'] = $row['employeeget']->mobile_no;
                $val['Email id'] = $row['employeeget']->email;
                $val['Guardian Name'] = $row['employeeget']->father_guardian_name;
                $val['Guardian Number'] = $row['employeeget']->father_guardian_number;
                $val['Mother Name'] = $row['employeeget']->mother_name;
                $val['Pan Card'] = $row['employeeget']->pen_card;
                $val['Aadhar Card'] = $row['employeeget']->aadhar_card;
                $val['Voter Id'] = $row['employeeget']->voter_id;
                $val['ESI Account No.'] = $row['employeeget']->esi_account_no;
                $val['UAN/PF  Account No.'] = $row['employeeget']->pf_account_no;
                $status = 'Pending';
                if ($row->status == 1) {
                    $status = 'Approved';
                }
                if ($row->status == 3) {
                    $status = 'Rejected';
                }
                $val['Status'] = $status;
                $val['Created'] = date("d/m/Y", strtotime($row->created_at));
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
    public function employeeApplicationExportpdf(Request $request)
    {
        $empID = $request->id;
        $employee = \App\Models\Employee::where('id', $empID)->first();
        $qualification = \App\Models\EmployeeQualification::where('employee_id', $empID)->get();
        $diploma = \App\Models\EmployeeDiploma::where('employee_id', $empID)->get();
        $work = \App\Models\EmployeeExperience::where('employee_id', $empID)->get();
        $pdf = PDF::loadView('templates.admin.hr_management.employee.pdf', compact('employee', 'qualification', 'diploma', 'work'))->setPaper('a4', 'landscape')->setWarnings(false);
        $pdf->save(storage_path() . '_filename.pdf');
        return $pdf->download('EmployeeApplication.pdf');
    }
    /*********************hr*************************************/
    /**
     * Export Inactive associate  listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */

    public function exportInactiveAssociate(Request $request)
    {
        if ($request['member_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate-status_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate-status_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = Member::with('associate_branch')
            ->with([
                'seniorData' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name']);
                }
            ])
            ->where('member_id', '!=', '9999999')->where('is_associate', 1)->where('associate_status', 0);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('associate_branch_id', '=', Auth::user()->branch_id);
        }
        if ($request['member_export'] == 0) {
            $totalResults = $data->orderby('associate_join_date', 'DESC')->count();
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['CUSTOMER ID'] = $row->member_id;
                $val['ASSOCIATE ID'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['EMAIL ID'] = $row->email;
                $val['MOBILE NO'] = $row->mobile_no;
                $val['SENIOR CODE'] = $row->associate_senior_code;
                $val['SENIOR NAME'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                $status = '';
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
        } elseif ($request['member_export'] == 1) {
            $associateList = $data->orderby('associate_join_date', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.inactive_associate_export', compact('associateList'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('inactive_associate.pdf');
        }
    }
    /******* Report  Management Start  ****************
use App\Exports\ReportInvestmentExport;
use App\Exports\ReportSsbExport;
use App\Exports\ReportOtherExport;
use App\Exports\ReportAssociateBusinessExport
use App\Exports\ReportAssociateBusinessSummaryExport
use App\Exports\ReportAssociateBusinessCompareExport
     */
    /**
     * Employee application list.
     *
     * @return \Illuminate\Http\Response
     */

    public function transactionDetailExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/transaction.csv";
        $fileName = env('APP_EXPORTURL') . "asset/transaction.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['payment_type'] = $request->payment_type;
        $request['payment_mode'] = $request->payment_mode;
        $request['is_search'] = $request->is_search;
        $planSSB = getPlanID('703')->id;
        $data = \App\Models\Daybook::with([
            'dbranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->with([
                    'investment' => function ($query) {
                        $query->select('id', 'plan_id', 'account_number', 'member_id');
                    }
                ])->whereHas('investment', function ($query) use ($planSSB) {
                    $query->where('member_investments.plan_id', '!=', $planSSB);
                })->where(function ($q) {
                    $q->where('transaction_type', 4);
                });
        if ($request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', $id);
        }
        if ($request['payment_mode'] != '') {
            $payment_mode = $request['payment_mode'];
            if ($payment_mode == 0) {
                $data = $data->where('payment_mode', 0);
            }
            if ($payment_mode == 1) {
                $data = $data->where('payment_mode', 1);
            }
            if ($payment_mode == 'other') {
                $data = $data->where('payment_mode', '>', 1);
            }
        }
        if ($request['payment_type'] != '') {
            $payment_type = $request['payment_type'];
            if ($payment_type == 'DR') {
                $data = $data->where('payment_type', 'DR');
            }
            if ($payment_type == 'CR') {
                $data = $data->where('payment_type', '!=', 'DR');
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
        if ($request['export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['Sr_no'] = $sno;
                $bid = $row->id;
                //$val['DT_RowIndex']=$sno;
                $val['BR NAME'] = $row['dbranch']->name;
                $val['BR CODE'] = $row['dbranch']->branch_code;
                $val['SO NAME'] = $row['dbranch']->sector;
                $val['RO NAME'] = $row['dbranch']->regan;
                $val['ZO NAME'] = $row['dbranch']->zone;
                $val['MEMBER ID'] = getSeniorData($row['investment']->member_id, 'member_id');
                $val['MEMBER NAME'] = getSeniorData($row['investment']->member_id, 'first_name') . ' ' . getSeniorData($row['investment']->member_id, 'last_name');
                $val['ACCOUNT NO'] = $row['investment']->account_number;
                $val['PLAN NAME'] = getPlanDetail($row['investment']->plan_id)->name;
                $account_number = $row['investment']->account_number;
                if (str_starts_with($account_number, 'R-')) {
                    $val['tag'] = 'R';
                } else {
                    $val['TAG'] = 'N';
                }
                $val['AMOUNT'] = number_format((float) $row->deposit, 2, '.', '');
                $p_mode = 'Other';
                if ($row->payment_mode == 0) {
                    $p_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $p_mode = 'Cheque';
                }
                $val['PAYMENT MODE'] = $p_mode;
                $p_type = 'CR';
                if ($row->payment_type == 'DR') {
                    $p_type = 'DR';
                    $val['amount'] = number_format((float) $row->withdrawal, 2, '.', '');
                }
                $val['PAYMENT TYPE'] = $p_type;
                $is_eli = 'No';
                if ($row->is_eli == 1) {
                    $p_mode = 'Yes';
                }
                $val['IS ELI'] = $is_eli;
                $val['created'] = date("d/m/Y", strtotime($row->created_at));
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
    /**
     * Employee application list.
     *
     * @return \Illuminate\Http\Response
     */

    public function transactionDetailSsbExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/transaction_ssb.csv";
        $fileName = env('APP_EXPORTURL') . "asset/transaction_ssb.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['payment_type'] = $request->payment_type;
        $request['payment_mode'] = $request->payment_mode;
        $request['is_search'] = $request->is_search;
        $data = \App\Models\SavingAccountTranscation::with([
            'savingAc' => function ($query) {
                $query->select('id', 'account_no', 'branch_id', 'member_id');
            }
        ]);
        if ($request['branch_id'] != '') {
            $bid = $request['branch_id'];
            $data = $data->whereHas('savingAc', function ($query) use ($bid) {
                $query->where('saving_accounts.branch_id', $bid);
            });
        }
        if ($request['payment_mode'] != '') {
            $payment_mode = $request['payment_mode'];
            if ($payment_mode == 0) {
                $data = $data->where('payment_mode', 0);
            }
            if ($payment_mode == 1) {
                $data = $data->where('payment_mode', 1);
            }
            if ($payment_mode == 'other') {
                $data = $data->where('payment_mode', '>', 1);
            }
        }
        if ($request['payment_type'] != '') {
            $payment_type = $request['payment_type'];
            if ($payment_type == 'DR') {
                $data = $data->where('payment_type', 'DR');
            }
            if ($payment_type == 'CR') {
                $data = $data->where('payment_type', '!=', 'DR');
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
        if ($request['export'] == 0) {
            $totalResults = $data->orderby('created_at', 'ASC')->count();
            $results = $data->orderby('created_at', 'ASC')->offset($start)->limit($limit)->get();
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
                $val['BR NAME'] = getBranchDetail($row['savingAc']->branch_id)->name;
                $val['BR CODE'] = getBranchDetail($row['savingAc']->branch_id)->branch_code;
                $val['SO NAME'] = getBranchDetail($row['savingAc']->branch_id)->sector;
                $val['RO NAME'] = getBranchDetail($row['savingAc']->branch_id)->regan;
                $val['ZO NAME'] = getBranchDetail($row['savingAc']->branch_id)->zone;
                $val['MEMBER ID'] = getSeniorData($row['savingAc']->member_id, 'member_id');
                $val['MEMBER NAME'] = getSeniorData($row['savingAc']->member_id, 'first_name') . ' ' . getSeniorData($row['savingAc']->member_id, 'last_name');
                $val['ACCOUNT NUMBER'] = $row['savingAc']->account_no;
                $val['AMOUNT'] = number_format((float) $row->deposit, 2, '.', '');
                $p_mode = 'Other';
                if ($row->payment_mode == 0) {
                    $p_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $p_mode = 'Cheque';
                }
                $val['PAYMENT MODE'] = $p_mode;
                $p_type = 'CR';
                if ($row->payment_type == 'DR') {
                    $p_type = 'DR';
                    $val['amount'] = number_format((float) $row->withdrawal, 2, '.', '');
                }
                $val['PAYMENT TYPE'] = $p_type;
                $val['created'] = date("d/m/Y", strtotime($row->created_at));
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
    /**
     * Employee application list.
     *
     * @return \Illuminate\Http\Response
     */

    public function transactionDetailOtherbExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/transaction_other.csv";
        $fileName = env('APP_EXPORTURL') . "asset/transaction_other.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['payment_type'] = $request->payment_type;
        $request['payment_mode'] = $request->payment_mode;
        $request['is_search'] = $request->is_search;
        $data = \App\Models\Transcation::whereIn('transaction_type', array(0, 7));
        if ($request['branch_id'] != '') {
            $bid = $request['branch_id'];
            $data = $data->where('branch_id', $bid);
        }
        if ($request['payment_mode'] != '') {
            $payment_mode = $request['payment_mode'];
            if ($payment_mode == 0) {
                $data = $data->where('payment_mode', 0);
            }
            if ($payment_mode == 1) {
                $data = $data->where('payment_mode', 1);
            }
            if ($payment_mode == 'other') {
                $data = $data->where('payment_mode', '>', 1);
            }
        }
        if ($request['payment_type'] != '') {
            $payment_type = $request['payment_type'];
            if ($payment_type == 'DR') {
                $data = $data->where('payment_type', 'DR');
            }
            if ($payment_type == 'CR') {
                $data = $data->where('payment_type', '!=', 'DR');
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
        if ($request['export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['BR NAME'] = getBranchDetail($row->branch_id)->name;
                $val['BR CODE'] = getBranchDetail($row->branch_id)->branch_code;
                $val['SO NAME'] = getBranchDetail($row->branch_id)->sector;
                $val['RO NAME'] = getBranchDetail($row->branch_id)->regan;
                $val['ZO NAME'] = getBranchDetail($row->branch_id)->zone;
                $val['MEMBER ID'] = getSeniorData($row->member_id, 'member_id');
                $val['MEMBER NAME'] = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                $val['ACCOUNT NO'] = 'Passbook Print';
                if ($row->transaction_type == 0) {
                    $val['account_number'] = 'Member Register';
                    if ($row->amount == 90 || $row->amount == 90.00) {
                        $val['account_number'] = 'Stn Charge';
                    }
                }
                $val['AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $p_mode = 'Other';
                if ($row->payment_mode == 0) {
                    $p_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $p_mode = 'Cheque';
                }
                $val['PAYMENT MODE'] = $p_mode;
                $p_type = 'CR';
                if ($row->payment_type == 'DR') {
                    $p_type = 'DR';
                    $val['amount'] = number_format((float) $row->withdrawal, 2, '.', '');
                }
                $val['PAYMENT TYPE'] = $p_type;
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
    /**
     * Associate business Report .
     *
     * @return \Illuminate\Http\Response
     */

    public function associateBusinessListExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/associate_Business_List.csv";
        $fileName = env('APP_EXPORTURL') . "asset/associate_Business_List.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['start_date'] = $request->start_date;
        $request['end_date'] = $request->end_date;
        $request['branch_id'] = $request->branch_id;
        $request['is_search'] = $request->is_search;
        $request['zone'] = $request->zone;
        $request['region'] = $request->region;
        $request['sector'] = $request->sector;
        $request['associate_code'] = $request->associate_code;
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
        // if($request['branch_id']!='') {
        //     $bID=$request['branch_id'];
        // }
        // else {
        //     $bID='';
        // }
        $bID = '';
        $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data = $data->where('associate_branch_id', '=', $id);
        }
        if (isset($request['branch_id']) && $request['branch_id'] != '') {
            $bID = $request['branch_id'];
            $data = $data->where('associate_branch_id', '=', $bID);
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
        if ($request['report_export'] == 0) {
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
            foreach ($results as $i => $row) {
                if (!empty($bID)) {
                    $branch_id = $bID;
                } else {
                    $branch_id = ''; //$row->branch_id;
                }
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
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE ID'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['CARDER'] = getCarderName($row->current_carder_id);
                $val['Daily N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['Daily N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['Daily Renew - No. A/C'] = investRenewAc($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                $val['Daily Renew - Total Amt'] = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                $val['Monthly N.I. - No. A/C'] = investNewAcCountType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['Monthly N.I. - Total Deno'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['Monthly Renew - No. A/C'] = investRenewAc($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['Monthly Renew - Total Amt'] = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                $val['FD N.I. - No. A/C'] = investNewAcCountType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                $val['FD N.I. - Total Deno'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                /*  $val['fd_renew_ac']=investRenewAc($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $val['fd_renew']=investRenewAmountSum($associate_id,$startDate,$endDate,$fdId,$branch_id);*/
                $val['SSB N.I. - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB N.I. - Total Deno'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB Deposit - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SSB Deposit - Total Amt'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $sum_ni_ac = $val['Daily N.I. - No. A/C'] + $val['Monthly N.I. - No. A/C'] + $val['FD N.I. - No. A/C'] + $val['SSB N.I. - No. A/C'];
                $sum_ni_amount = $val['Daily N.I. - Total Deno'] + $val['Monthly N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['SSB N.I. - Total Deno'];
                $val['OTHER MI'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                $val['OTHER STN'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                $ni_m = $val['Daily N.I. - Total Deno'] + $val['Monthly N.I. - Total Deno'] + $val['FD N.I. - Total Deno'];
                // dd($val['Daily N.I. - Total Deno'],$val['Monthly N.I. - Total Deno'],$val['FD N.I. - Total Deno'],$val['Daily Renew - Total Amt'],$val['Monthly Renew - Total Amt']);
                $tcc_m = $val['Daily N.I. - Total Deno'] + $val['Monthly N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['Daily Renew - Total Amt'] + $val['Monthly Renew - Total Amt'];
                $tcc = $val['Daily N.I. - Total Deno'] + $val['Monthly N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['SSB N.I. - Total Deno'] + $val['Daily Renew - Total Amt'] + $val['Monthly Renew - Total Amt'] + $val['SSB Deposit - Total Amt'];
                $val['NCC_M'] = number_format((float) $ni_m, 2, '.', '');
                $val['NCC'] = number_format((float) $sum_ni_amount, 2, '.', '');
                $val['TCC_M'] = number_format((float) $tcc_m, 2, '.', '');
                $val['TCC'] = number_format((float) $tcc, 2, '.', '');
                $val['Loan - No. A/C'] = totalLoanAc($associate_id, $startDate, $endDate, $branch_id);
                $val['Loan - Total Amt'] = totalLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                $val['Loan Recovery - No. A/C'] = totalRenewLoanAc($associate_id, $startDate, $endDate, $branch_id);
                $val['Loan Recovery - Total Amt.'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                // $val['total_ni_ac']=$sum_ni_ac;
                //$val['total_ni_amount']=number_format((float)$sum_ni_amount, 2, '.', '');
                $val['New Associate Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                $val['Total Associate Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                $sum_renew_ac = $val['Daily Renew - No. A/C'] + $val['Monthly Renew - No. A/C'] + $val['SSB Deposit - No. A/C'];
                $sum_renew_amount = $val['Daily Renew - Total Amt'] + $val['Monthly Renew - Total Amt'] + $val['SSB Deposit - Total Amt'];
                // $val['total_ac']=$sum_renew_ac;
                //$val['total_amount']=number_format((float)$sum_renew_amount, 2, '.', '');
                $val['New Member Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                $val['Total Member Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
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
        $returnURL = URL::to('/') . "/asset/associate_business_summary.csv";
        $fileName = env('APP_EXPORTURL') . "asset/associate_business_summary.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
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
        $branch_id = '';
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
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            //$data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_no', '=', $associate_code);
        }
        if ($request['export'] == 0) {
            $totalResults = $data->orderby('created_at', 'ASC')->count();
            $results = $data->orderby('created_at', 'ASC')->offset($start)->limit($limit)->get();
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
                $planids = array($planDaily, $planSSB, $planKanyadhan, $planMB, $planFFD, $planFRD, $planJeevan, $planMI, $planFD, $planRD, $planBhavhishya, );
                $val['DT_RowIndex'] = $sno;
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['ASSOCIATE CODE'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['CARDER'] = getCarderName($row->current_carder_id);
                $val['DAILY NI NO A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['Daily N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['Daily Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['Daily Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['RD N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['RD Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['FRD N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FRD Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['FD N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['FD N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['FFD N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['FFD N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['Smaraddh Kanyadhan N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['Smaraddh Kanyadhan N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['Smaraddh Kanyadhan Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['Smaraddh Kanyadhan Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['Smaraddh Bhavishya N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['Smaraddh Bhavishya N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['Smaraddh Bhavishya Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['Smaraddh Bhavishya Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['Smaraddh Jeevan N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['Smaraddh Jeevan N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['Smaraddh Jeevan Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                ;
                $val['Smaraddh Jeevan Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['SSB N.I. - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB N.I. - Total Deno'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['SSB Deposit - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['SSB Deposit - Total Amt'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['MIS N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MIS N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MIS Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                ;
                $val['MIS Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['MB N.I. - No. A/C'] = investNewAcCount($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB N.I. - Total Deno'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB Renew - No. A/C'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['MB Renew - Total Amt'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['Other MI'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                $val['Other STN'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                $ni_m = $val['Daily N.I. - Total Deno'] + $val['Smaraddh Kanyadhan N.I. - Total Deno'] + $val['MB N.I. - Total Deno'] + $val['FFD N.I. - Total Deno'] + $val['FRD N.I. - Total Deno'] + $val['Smaraddh Jeevan N.I. - Total Deno'] + $val['MIS N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['RD N.I. - Total Deno'] + $val['Smaraddh Bhavishya N.I. - Total Deno'];
                $tcc_m = $val['Daily N.I. - Total Deno'] + $val['Smaraddh Kanyadhan N.I. - Total Deno'] + $val['MB N.I. - Total Deno'] + $val['FFD N.I. - Total Deno'] + $val['FRD N.I. - Total Deno'] + $val['Smaraddh Jeevan N.I. - Total Deno'] + $val['MIS N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['RD N.I. - Total Deno'] + $val['Smaraddh Bhavishya N.I. - Total Deno'] + $val['Smaraddh Bhavishya Renew - Total Amt'] + $val['RD Renew - Total Amt'] + $val['MIS Renew - Total Amt'] + $val['Smaraddh Jeevan Renew - Total Amt'] + $val['FRD Renew - Total Amt'] + $val['MB Renew - Total Amt'] + $val['Smaraddh Kanyadhan Renew - Total Amt'] + $val['Daily Renew - Total Amt'];
                $tcc = $val['Daily N.I. - Total Deno'] + $val['Smaraddh Kanyadhan N.I. - Total Deno'] + $val['MB N.I. - Total Deno'] + $val['FFD N.I. - Total Deno'] + $val['FRD N.I. - Total Deno'] + $val['Smaraddh Jeevan N.I. - Total Deno'] + $val['MIS N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['RD N.I. - Total Deno'] + $val['Smaraddh Bhavishya N.I. - Total Deno'] + $val['SSB N.I. - Total Deno'] + $val['Smaraddh Bhavishya Renew - Total Amt'] + $val['RD Renew - Total Amt'] + $val['MIS Renew - Total Amt'] + $val['Smaraddh Jeevan Renew - Total Amt'] + $val['FRD Renew - Total Amt'] + $val['MB Renew - Total Amt'] + $val['Smaraddh Kanyadhan Renew - Total Amt'] + $val['SSB Deposit - Total Amt'] + $val['Daily Renew - Total Amt'];
                $sum_ni_ac = $val['DAILY NI NO A/C'] + $val['SSB N.I. - No. A/C'] + $val['Smaraddh Kanyadhan N.I. - No. A/C'] + $val['MB N.I. - No. A/C'] + $val['FFD N.I. - No. A/C'] + $val['FRD N.I. - No. A/C'] + $val['Smaraddh Jeevan N.I. - No. A/C'] + $val['MIS N.I. - No. A/C'] + $val['FD N.I. - No. A/C'] + $val['RD N.I. - No. A/C'] + $val['Smaraddh Bhavishya N.I. - No. A/C'];
                $sum_ni_amount = $val['Daily N.I. - Total Deno'] + $val['SSB N.I. - Total Deno'] + $val['Smaraddh Kanyadhan N.I. - Total Deno'] + $val['MB N.I. - Total Deno'] + $val['FFD N.I. - Total Deno'] + $val['FRD N.I. - Total Deno'] + $val['Smaraddh Jeevan N.I. - Total Deno'] + $val['MIS N.I. - Total Deno'] + $val['FD N.I. - Total Deno'] + $val['RD N.I. - Total Deno'] + $val['Smaraddh Bhavishya N.I. - Total Deno'];
                $sum_renew_ac = investRenewAc($associate_id, $startDate, $endDate, $planids, $branch_id);
                $sum_renew_amount = investRenewAmountSum($associate_id, $startDate, $planids, $planids, $branch_id);
                $val['NCC_M'] = number_format((float) $ni_m, 2, '.', '');
                $val['NCC'] = number_format((float) $sum_ni_amount, 2, '.', '');
                $val['TCC_M'] = number_format((float) $tcc_m, 2, '.', '');
                $val['TCC'] = number_format((float) $tcc, 2, '.', '');
                $val['Staff Loan - No. A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['Staff Loan - Total Amt'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['Pl Loan - No. A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['Pl Loan - Total Amt'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['Loan against Investment - No. A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['Loan against Investment - Total Amt'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['Group Loan - No. A/C'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['Group Loan - Total Amt'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['Total Loan - No. A/C'] = $val['Staff Loan - No. A/C'] + $val['Pl Loan - No. A/C'] + $val['Loan against Investment - No. A/C'] + $val['Group Loan - No. A/C'];
                $val['Total Loan - Total Amt.'] = $val['Staff Loan - Total Amt'] + $val['Pl Loan - Total Amt'] + $val['Loan against Investment - Total Amt'] + $val['Group Loan - Total Amt'];
                $val['Staff Loan EMI - No. A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['Staff Loan EMI- Total Amt'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['Pl Loan EMI - No. A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['Pl Loan EMI - Total Amt'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['Loan against Investment EMI - No. A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['Loan against Investment EMI - Total Amt'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['Group Loan EMI - No. A/C'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['Group Loan EMI - Total Amt'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['Total Loan EMI- No. A/C'] = $val['Staff Loan EMI - No. A/C'] + $val['Pl Loan EMI - No. A/C'] + $val['Loan against Investment EMI - No. A/C'] + $val['Group Loan EMI - No. A/C'];
                $val['Total Loan EMI - Total Amt.'] = $val['Staff Loan EMI- Total Amt'] + $val['Pl Loan EMI - Total Amt'] + $val['Loan against Investment EMI - Total Amt'] + $val['Group Loan EMI - Total Amt'];
                /*   $val['total_ni_ac']=$sum_ni_ac;
                $val['total_ni_amount']=number_format((float)$sum_ni_amount, 2, '.', '');
                $val['total_ac']=$sum_renew_ac;
                $val['total_amount']=number_format((float)$sum_renew_amount, 2, '.', '');
*/
                $val['New Associate Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                $val['Total Associate Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                $val['New Member Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                $val['Total Member Joining No.'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
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
        $returnURL = URL::to('/') . "/asset/associate_business_compare.csv";
        $fileName = env('APP_EXPORTURL') . "asset/associate_business_compare.csv";
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
        if ($request['report_export'] == 0) {
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
            $customplan = getPlanIDCustom();
            foreach ($results as $row) {
                $sno++;
                $associate_id = $row->id;
                // $planDaily=getPlanID('710')->id;
                // $dailyId=array($planDaily);
                // $planSSB=getPlanID('703')->id;
                // $planKanyadhan=getPlanID('709')->id;
                // $planMB=getPlanID('708')->id;
                // $planFRD=getPlanID('707')->id;
                // $planJeevan=getPlanID('713')->id;
                // $planRD=getPlanID('704')->id;
                // $planBhavhishya=getPlanID('718')->id;
                // $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
                // $planMI=getPlanID('712')->id;
                // $planFFD=getPlanID('705')->id;
                // $planFD=getPlanID('706')->id;
                // $fdId=array($planMI,$planFFD,$planFD);
                $planDaily = $customplan['710']; //getPlanID('710')->id;
                $dailyId = array($planDaily);
                $planSSB = $customplan['703']; //getPlanID('703')->id;
                $planKanyadhan = $customplan['709']; //getPlanID('709')->id;
                $planMB = $customplan['708']; //getPlanID('708')->id;
                $planFRD = $customplan['707']; //getPlanID('707')->id;
                $planJeevan = $customplan['713']; //getPlanID('713')->id;
                $planRD = $customplan['704']; //getPlanID('704')->id;
                $planBhavhishya = $customplan['718']; //getPlanID('718')->id;
                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                $planMI = $customplan['712']; //getPlanID('712')->id;
                $planFFD = $customplan['705']; //getPlanID('705')->id;
                $planFD = $customplan['706']; //getPlanID('706')->id;
                $fdId = array($planMI, $planFFD, $planFD);
                $val['S n'] = $sno;
                $val['Br Name'] = $row['associate_branch']->name;
                $val['Br code'] = $row['associate_branch']->branch_code;
                $val['So name'] = $row['associate_branch']->sector;
                $val['Ro name'] = $row['associate_branch']->regan;
                $val['Zo name'] = $row['associate_branch']->zone;
                $val['Associate id'] = $row->associate_no;
                $val['Associate Name'] = $row->first_name . ' ' . $row->last_name;
                $val['Carder'] = getCarderName($row->current_carder_id);
                $val['Current Daily N.I. - no a/c'] = investNewAcCount($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                $val['Current Daily N.I. - Total Deno'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                $val['Current Daily Renew - No. A/C'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                $val['Current Daily Renew - Total Amt'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
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
                $val['Current SSB Deposit - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                $val['Current SSB Deposit - Total Amt'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                $current_sum_ni_ac = $val['Current Daily N.I. - no a/c'] + $val['Current Monthly N.I. - No. A/C'] + $val['Current FD N.I. - No. A/C'] + $val['Current SSB N.I. - No. A/C'];
                $current_sum_ni_amount = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'] + $val['Current SSB N.I. - Total Deno'];
                //$val['current_total_ni_ac']=$current_sum_ni_ac;
                // $val['current_total_ni_amount']=number_format((float)$current_sum_ni_amount, 2, '.', '');
                $current_sum_renew_ac = $val['Current Daily Renew - No. A/C'] + $val['Current Monthly Renew - No. A/C'];
                $current_sum_renew_amount = $val['Current Daily Renew - Total Amt'] + $val['Current Monthly Renew - Total Amt'];
                // $val['current_total_ac']=$current_sum_renew_ac;
                // $val['current_total_amount']=number_format((float)$current_sum_renew_amount, 2, '.', '');
                $val['Current Other MI'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 11);
                $val['Current Other STN'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 12);
                $current_ni_m = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'];
                $current_tcc_m = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'] + $val['Current Daily Renew - Total Amt'] + $val['Current Monthly Renew - Total Amt'];
                $current_tcc = $val['Current Daily N.I. - Total Deno'] + $val['Current Monthly N.I. - Total Deno'] + $val['Current FD N.I. - Total Deno'] + $val['Current SSB N.I. - Total Deno'] + $val['Current Daily Renew - Total Amt'] + $val['Current Monthly Renew - Total Amt'] + $val['Current SSB Deposit - Total Amt'];
                $val['Current NCC_M'] = number_format((float) $current_ni_m, 2, '.', '');
                $val['Current NCC'] = number_format((float) $current_sum_ni_amount, 2, '.', '');
                $val['Current TCC_M'] = number_format((float) $current_tcc_m, 2, '.', '');
                $val['Current TCC'] = number_format((float) $current_tcc, 2, '.', '');
                $val['Current Loan - No. A/C'] = totalLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['Current Loan - Total Amt'] = totalLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['Current Loan Recovery - No. A/C'] = totalRenewLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['Current Loan Recovery - Total Amt.'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['Current New Associate Joining No.'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 0);
                $val['Current Total Associate Joining No.'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 1);
                $val['Current New Member Joining No.'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 0);
                $val['Current Total Member Joining No.'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 1);
                $val['Compare Daily N.I. - No. A/C'] = investNewAcCount($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                $val['Compare Daily N.I. - Total Deno'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                $val['Compare Daily Renew - No. A/C'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                $val['Compare Daily Renew - Total Amt'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                $val['Compare Monthly N.I. - No. A/C'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['Compare Monthly N.I. - Total Deno'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['Compare Monthly Renew - No. A/C'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['Current Monthly Renew - Total Amt'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['Compare FD N.I. - No. A/C'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                $val['Compare FD N.I. - Total Deno'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                /* $val['compare_fd_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
                $val['compare_fd_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);*/
                $val['Compare rent SSB N.I. - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1);
                $val['Compare SSB N.I. - Total Deno'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                $val['Compare SSB Deposit - No. A/C'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                $val['Compare SSB Deposit - Total Amt'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                $compare_sum_ni_ac = $val['Compare Daily N.I. - No. A/C'] + $val['Compare Monthly N.I. - No. A/C'] + $val['Compare FD N.I. - No. A/C'] + $val['Compare rent SSB N.I. - No. A/C'];
                $compare_sum_ni_amount = $val['Compare Daily N.I. - Total Deno'] + $val['Compare Monthly N.I. - Total Deno'] + $val['Compare FD N.I. - Total Deno'] + $val['Compare SSB N.I. - Total Deno'];
                $val['compare_total_ni_ac'] = $compare_sum_ni_ac;
                $val['compare_total_ni_amount'] = number_format((float) $compare_sum_ni_amount, 2, '.', '');
                $compare_sum_renew_ac = $val['Compare Daily Renew - No. A/C'] + $val['Compare Monthly Renew - No. A/C'];
                $compare_sum_renew_amount = $val['Compare Daily Renew - Total Amt'] + $val['Current Monthly Renew - Total Amt'];
                //$val['compare_total_ac']=$compare_sum_renew_ac;
                //$val['compare_total_amount']=number_format((float)$compare_sum_renew_amount, 2, '.', '');
                $val['Compare Other MI'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 11);
                $val['Compare Other STN'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 12);
                $compare_ni_m = $val['Compare Daily N.I. - Total Deno'] + $val['Compare Monthly N.I. - Total Deno'] + $val['Compare FD N.I. - Total Deno'];
                $compare_tcc_m = $val['Compare Daily N.I. - Total Deno'] + $val['Compare Monthly N.I. - Total Deno'] + $val['Compare FD N.I. - Total Deno'] + $val['Compare Daily Renew - Total Amt'] + $val['Current Monthly Renew - Total Amt'];
                $compare_tcc = $val['Compare Daily N.I. - Total Deno'] + $val['Compare Monthly N.I. - Total Deno'] + $val['Compare FD N.I. - Total Deno'] + $val['Compare SSB N.I. - Total Deno'] + $val['Compare Daily Renew - Total Amt'] + $val['Current Monthly Renew - Total Amt'] + $val['Compare SSB Deposit - Total Amt'];
                $val['Compare NCC_M'] = number_format((float) $compare_ni_m, 2, '.', '');
                $val['Compare NCC'] = number_format((float) $compare_sum_ni_amount, 2, '.', '');
                $val['Compare TCC_M'] = number_format((float) $compare_tcc_m, 2, '.', '');
                $val['Compare TCC'] = number_format((float) $compare_tcc, 2, '.', '');
                $val['Compare Loan - No. A/C'] = totalLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['Compare Loan - Total Amt'] = totalLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['Compare Loan Recovery - No. A/C'] = totalRenewLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['Compare Loan Recovery - Total Amt.'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['Compare New Associate Joining No.'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 0);
                $val['Compare Total Associate Joining No.'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 1);
                $val['Compare New Member Joining No.'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 0);
                $val['Compare Total Member Joining No.'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 1);
                $val['Result Daily N.I. - No. A/C'] = $val['Current Daily N.I. - no a/c'] - $val['Compare Daily N.I. - No. A/C'];
                $val['Result Daily N.I. - Total Deno'] = $val['Current Daily N.I. - Total Deno'] - $val['Compare Daily N.I. - Total Deno'];
                $val['Result Daily Renew - No. A/C'] = $val['Current Daily Renew - No. A/C'] - $val['Compare Daily Renew - No. A/C'];
                $val['Result Daily Renew - Total Amt'] = $val['Current Daily Renew - Total Amt'] - $val['Compare Daily Renew - Total Amt'];
                $val['Result Monthly N.I. - No. A/C'] = $val['Current Monthly N.I. - No. A/C'] - $val['Compare Monthly N.I. - No. A/C'];
                $val['Result Monthly N.I. - Total Deno'] = $val['Current Monthly N.I. - Total Deno'] - $val['Compare Monthly N.I. - Total Deno'];
                $val['Result Monthly Renew - No. A/C'] = $val['Current Monthly Renew - No. A/C'] - $val['Compare Monthly Renew - No. A/C'];
                $val['Result Monthly Renew - Total Amt'] = $val['Current Monthly Renew - Total Amt'] - $val['Current Monthly Renew - Total Amt'];
                $val['Result FD N.I. - No. A/C'] = $val['Current FD N.I. - No. A/C'] - $val['Compare FD N.I. - No. A/C'];
                $val['Result FD N.I. - Total Deno'] = $val['Current FD N.I. - Total Deno'] - $val['Compare FD N.I. - Total Deno'];
                /*$val['result_fd_renew_ac']=$val['current_fd_renew_ac']-$val['compare_fd_renew_ac'];
                $val['result_fd_renew']=$val['current_fd_renew']-$val['compare_fd_renew'];*/
                $val['Result rent SSB N.I. - No. A/C'] = $val['Current SSB N.I. - No. A/C'] - $val['Compare rent SSB N.I. - No. A/C'];
                $val['Result SSB N.I. - Total Deno'] = $val['Current SSB N.I. - Total Deno'] - $val['Compare SSB N.I. - Total Deno'];
                $val['Result SSB Deposit - No. A/C'] = $val['Current SSB Deposit - No. A/C'] - $val['Compare SSB Deposit - Total Amt'];
                $val['Result SSB Deposit - Total Amt'] = $val['Current SSB Deposit - Total Amt'] - $val['Compare SSB N.I. - Total Deno'];
                $result_sum_ni_ac = $current_sum_ni_ac - $compare_sum_ni_ac;
                $result_sum_ni_amount = $current_sum_ni_amount - $compare_sum_ni_amount;
                //$val['result_total_ni_ac']=$result_sum_ni_ac;
                //$val['result_total_ni_amount']=number_format((float)$result_sum_ni_amount, 2, '.', '');
                $result_sum_renew_ac = $current_sum_renew_ac - $compare_sum_renew_ac;
                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                //$val['result_total_ac']=$result_sum_renew_ac;
                //$val['result_total_amount']=number_format((float)$result_sum_renew_amount, 2, '.', '');
                $val['Result Other MI'] = $val['Current Other MI'] - $val['Compare Other MI'];
                $val['Result Other STN'] = $val['Current Other STN'] - $val['Compare Other STN'];
                $val['Result NCC_M'] = $val['Current NCC_M'] - $val['Compare NCC_M'];
                $val['Result NCC'] = $val['Current NCC'] - $val['Compare NCC'];
                $val['Result TCC_M'] = $val['Current TCC_M'] - $val['Compare TCC_M'];
                $val['Result TCC'] = $val['Current TCC'] - $val['Compare TCC'];
                $val['Result Loan - No. A/C'] = $val['Current Loan - No. A/C'] - $val['Compare Loan - No. A/C'];
                $val['Result Loan - Total Amt'] = $val['Current Loan - Total Amt'] - $val['Compare Loan - Total Amt'];
                $val['Result Loan Recovery - No. A/C'] = $val['Current Loan Recovery - No. A/C'] - $val['Compare Loan Recovery - No. A/C'];
                $val['Result Loan Recovery - Total Amt.'] = $val['Current Loan Recovery - Total Amt.'] - $val['Compare Loan Recovery - Total Amt.'];
                $val['Result New Associate Joining No.'] = $val['Current New Associate Joining No.'] - $val['Compare New Associate Joining No.'];
                $val['Result Total Associate Joining No.'] = $val['Current Total Associate Joining No.'] - $val['Compare Total Associate Joining No.'];
                $val['Result New Member Joining No.'] = $val['Current New Member Joining No.'] - $val['Compare New Member Joining No.'];
                $val['Result Total Member Joining No.'] = $val['Current Total Member Joining No.'] - $val['Compare Total Member Joining No.'];
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
    /******* Report  Management End   *****************/
    /**
     * Export associate commission Detail listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */
    public function loanCommissionExport(Request $request)
    {
        $data = \App\Models\AssociateCommission::where('type_id', $request['id'])->whereIn('type', array(4, 6))->where('status', 1);
        $loan = \App\Models\Memberloans::where('id', $request['id'])->first();
        // print_r($loan);die;
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
            return Excel::download(new LoanCommissionExport($data, $loan), 'loan_commission.xlsx');
        } elseif ($request['commission_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.loan.exportcommission_detail_loan', compact('data', 'loan'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('loan_commission.pdf');
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
        //  print_r($loan);die;
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
            return Excel::download(new LoanGroupCommissionExport($data, $loan), 'loan_group_commission.xlsx');
        } elseif ($request['commission_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.loan.exportcommission_detail_loan_group', compact('data', 'loan'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('loan_group_commission.pdf');
        }
    }
    /**
     * Export associate commission Detail listing in pdf.
     *
     * @return \Illuminate\Http\Response
     */

    public function exportAssociateCommissionDetailLoan(Request $request)
    {
        if ($request['commission_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/loan-commission-detail.csv";
            $fileName = env('APP_EXPORTURL') . "asset/loan-commission-detail.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        // if ($request->year <= 2022 && $request->month < 12) {
        //     $data = AssociateCommission::Where('member_id', $request->id)->whereIn('type', array(4, 6, 7, 8))->where('status', 1);
        // } else {
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
                if ($val['type'] == 2) {
                    $row['ACCOUNT NUMBER'] = $val['loan']->account_number;
                    $row['PLAN NAME'] = $val['loan']['loan']->name;
                } else {
                    $row['ACCOUNT NUMBER'] = $val['group_loan']->account_number;
                    $row['PLAN NAME'] = $val['group_loan']['loan']->name;
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
        } elseif ($request->commission_export == 1) {
            $data = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.associate.exportcommission_detail_loan', compact('data', 'member'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('associatecommission_detail_loan.pdf');
        }
    }
    /**
     * Download Loan Form PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadLoanForm($id, $type)
    {
        /* $data =\App\Models\Memberloans::with('loan','loanMember','loanMemberBankDetails','loanMemberIdProofs','LoanApplicants','LoanCoApplicants','LoanGuarantor','Loanotherdocs','GroupLoanMembers','loanInvestmentPlans')->where('id',$id)->orderby('id','DESC')->first();
        $title = '';
        $pdf = PDF::loadView('templates.admin.loan.personalAndEmployDetail',compact('data','title'))->setPaper('a4', 'landscape')->setWarnings(false);
        $pdf->save(storage_path().'_filename.pdf');
        return $pdf->download('loanForm.pdf');*/
        $data['loanDetails'] = Memberloans::with('loan', 'loanMember', 'loanMemberBankDetails', 'loanMemberIdProofs', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        $data['loanDetails'] = array('');
        return view('templates.admin.loan.personalAndEmployDetail', $data);
    }
    /**
     * Download Loan Recovery No Dues PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function DownloadRecoveryNoDueLoan($id, $type)
    {
        $data['title'] = 'Print No Dues';
        if ($type != 3) {
            $result = Memberloans::with('loanMember', 'loanMemberAssociate')->where('status', '!=', 0)->where('id', '=', $id)->where('loan_type', '!=', 3)->get();
        } else {
            $result = Grouploans::with('loanMember', 'loanMemberAssociate')->where('status', '!=', 0)->where('id', '=', $id)->where('loan_type', '=', 3)->get();
        }
        $data['account_number'] = $data['name'] = $data['father_husband'] = $data['clear_date'] = '';
        if (!empty($result)) {
            $result = $result[0]->toArray();
            /* if($result['status']!=3){
          return redirect(route('admin.loan.recovery'));
         }*/
            $data['account_number'] = $result['account_number'];
            $data['name'] = strtoupper($result['loan_member']['first_name'] . ' ' . $result['loan_member']['last_name']);
            $data['father_husband'] = strtoupper($result['loan_member']['father_husband']);
            if (!empty($result['clear_date'])) {
                $data['clear_date'] = date("d/m/Y", strtotime($result['clear_date']));
            }
        }
        $data['recovery_clear_logo'] = url('core/storage/images/recovery_clear/recovery_clear_logo.png');
        $pdf = PDF::loadView('templates.admin.loan.recovery_clear_pdf', compact('data'));
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
        $data['title'] = 'Print No Dues';
        if ($type != 3) {
            $result = Memberloans::with('loanMember', 'loanMemberAssociate')->where('status', '!=', 0)->where('id', '=', $id)->where('loan_type', '!=', 3)->get();
        } else {
            $result = Grouploans::with('loanMember', 'loanMemberAssociate')->where('status', '!=', 0)->where('id', '=', $id)->get();
        }
        $data['account_number'] = $data['name'] = $data['father_husband'] = $data['clear_date'] = '';
        if (!empty($result)) {
            $result = $result[0]->toArray();
            /*if($result['status']!=3){
          return redirect(route('admin.loan.recovery'));
         }*/
            $data['account_number'] = $result['account_number'];
            $data['name'] = strtoupper($result['loan_member']['first_name'] . ' ' . $result['loan_member']['last_name']);
            $data['father_husband'] = strtoupper($result['loan_member']['father_husband']);
            if (!empty($result['clear_date'])) {
                $data['clear_date'] = date("d/m/Y", strtotime($result['clear_date']));
            }
        }
        $data['recovery_clear_logo'] = url('core/storage/images/recovery_clear/recovery_clear_logo.png');
        $data['data'] = $data;
        return view('templates.admin.loan.recovery_clear_print', $data);
    }
    /**
     * Export designation.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    public function designationExport(Request $request)
    {
        $data=\App\Models\Designation::where('status','!=',9)->orderby('id','DESC')->get();
        if($request['export'] == 0){
            return Excel::download(new DesignationExport($data), 'designation.xlsx');
        }
    }
    */
    public function designationExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/hr_desiganation_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/hr_desiganation_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = \App\Models\Designation::where('status', '!=', 9);
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = Designation::where('status', $status)->orderby('id', 'DESC')->get();
        }
        if ($request['export'] == 0) {
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
                $val['Sr_no'] = $sno;
                $val['DESIGNATION NAME'] = $row->designation_name;
                $category = '';
                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                $val['CATEGORY'] = $category;
                $gross_salary = '';
                $sum = $row->basic_salary + $row->daily_allowances + $row->hra + $row->hra_metro_city + $row->uma + $row->convenience_charges + $row->maintenance_allowance + $row->communication_allowance + $row->prd + $row->ia + $row->ca + $row->fa;
                $deduction = $row->pf + $row->tds;
                $total = $sum - $deduction;
                $val['GROSS SALARY'] = number_format((float) $total, 2, '.', '');
                $val['BASIC SALARY'] = number_format((float) $row->basic_salary, 2, '.', '');
                $val['DAILY ALLOWANES'] = number_format((float) $row->daily_allowances, 2, '.', '');
                $val['HRA'] = number_format((float) $row->hra, 2, '.', '');
                $val['HRA METRO CITY'] = number_format((float) $row->hra_metro_city, 2, '.', '');
                $val['UMA'] = number_format((float) $row->uma, 2, '.', '');
                $val['CONVENIENCE CHARGES'] = number_format((float) $row->convenience_charges, 2, '.', '');
                $val['MAINTENANCE ALLOWANCE'] = number_format((float) $row->maintenance_allowance, 2, '.', '');
                $val['COMMUNICATION ALLOWANCE'] = number_format((float) $row->communication_allowance, 2, '.', '');
                $val['PRD'] = number_format((float) $row->prd, 2, '.', '');
                $val['IA'] = number_format((float) $row->ia, 2, '.', '');
                $val['CA'] = number_format((float) $row->ca, 2, '.', '');
                $val['FA'] = number_format((float) $row->fa, 2, '.', '');
                $val['PF'] = number_format((float) $row->pf, 2, '.', '');
                $val['TDS'] = number_format((float) $row->tds, 2, '.', '');
                $status = '';
                if ($row->status == 1) {
                    $status = 'Active';
                }
                if ($row->status == 0) {
                    $status = 'Inactive';
                }
                if ($row->status == 9) {
                    $status = 'Deleted';
                }
                $val['STATUS'] = $status;
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

    public function exportDemandAdviceReport(Request $request)
    {
        if ($request['demand_advice_report_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/demand_advice_report.csv";
            $fileName = env('APP_EXPORTURL') . "asset/demand_advice_report.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\DemandAdvice::with([
            'investment' => function ($q) {
                $q->with('associateMember', 'member', 'ssb');
            }
        ])->with([
                    'branch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'regan', 'sector', 'zone');
                    },
                    'sumdeposite2',
                    'sumdeposite'
                ])->where('is_deleted', 0)->where('is_reject', '0');
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', '=', Auth::user()->branch_id);
        }
        if ($request['date_from'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['date_from'])));
            if ($request['date_to'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['date_to'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(date)'), [$startDate, $endDate]);
        }
        if (isset($request['filter_branch']) && $request['filter_branch'] != '') {
            $branchId = $request['filter_branch'];
            $data = $data->where('branch_id', '=', $branchId);
        }
        if (isset($request['account_number']) && $request['account_number'] != '') {
            $account_number = $request['account_number'];
            $data = $data->where('account_number', '=', $account_number);
        }
        if ($request['advice_type'] != '') {
            $advice_id = $request['advice_type'];
            $advice_type_id = $request['expense_type'];
            if ($advice_id == 0 || $advice_id == 1 || $advice_id == 2) {
                if ($advice_type_id != '') {
                    $data = $data->where('payment_type', '=', $advice_id)->where('sub_payment_type', $advice_type_id);
                } else {
                    $data = $data->where('payment_type', '=', $advice_id);
                }
            } elseif ($advice_id == 3) {
                $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 0);
            } elseif ($advice_id == 4) {
                $data = $data->where('payment_type', '=', 3)->where('death_help_catgeory', '=', 1);
            } elseif ($advice_id == 5) {
                $data = $data->where('payment_type', '=', 4);
            }
        }
        if ($request['voucher_number'] != '') {
            $voucher_number = $request['voucher_number'];
            $data = $data->where('voucher_number', '=', $voucher_number);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', '=', $status);
        }
        if ($request['demand_advice_report_export'] == 0) {
            $totalResults = $data->orderby('created_at', 'DESC')->count();
            $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get(['id', 'branch_id', 'date', 'investment_id', 'opening_date', 'final_amount', 'tds_amount', 'payment_type', 'account_number', 'maturity_amount_payable', 'maturity_prematurity_amount', 'voucher_number', 'payment_mode', 'bank_account_number', 'bank_ifsc', 'is_print', 'status', 'maturity_amount_till_date', 'interestAmount', 'sub_payment_type', 'ssb_account']);
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
                $member_id = '';
                $sno++;
                $val['S/N'] = $sno;
                if (isset($row['branch']->name)) {
                    $val['BR NAME'] = $row['branch']->name;
                } else {
                    $val['BR NAME'] = 'N/A';
                }
                if (isset($row['branch']->branch_code)) {
                    $val['BR CODE'] = $row['branch']->branch_code;
                } else {
                    $val['BR CODE'] = 'N/A';
                }
                if (isset($row['branch']->sector)) {
                    $val['SO NAME'] = $row['branch']->sector;
                } else {
                    $val['SO NAME'] = 'N/A';
                }
                if (isset($row['branch']->regan)) {
                    $val['RO NAME'] = $row['branch']->regan;
                } else {
                    $val['RO NAME'] = 'N/A';
                }
                if (isset($row['branch']->zone)) {
                    $val['ZO NAME'] = $row['branch']->zone;
                } else {
                    $val['ZO NAME'] = 'N/A';
                }
                $val['DATE'] = date("d/m/Y", strtotime($row->date));
                $opening_date = 'N/A';
                if ($row->investment_id) {
                    $dateInvestDetail = $row['investment']; //$this->investmentDeatil(new Memberinvestments(),$row->investment_id,'id');
                    //  print_r($dateInvestDetail);die;
                }
                if (isset($row->payment_type)) {
                    if ($row->investment_id) {
                        if ($dateInvestDetail) {
                            $opening_date = date("d/m/Y", strtotime($dateInvestDetail->created_at));
                        } else {
                            $opening_date = 'None';
                        }
                    }
                    $val['AC OPENING DATE'] = $opening_date;
                } else {
                    if ($row->opening_date) {
                        $opening_date = date("d/m/Y", strtotime($row->opening_date));
                    } else {
                        $opening_date = "N/A";
                    }
                    $val['AC OPENING DATE'] = $opening_date;
                }
                if ($row->payment_type == 2) {
                    $val['PAYMENT TRF AMOUNT'] = round($row->final_amount);
                } else {
                    $val['PAYMENT TRF AMOUNT'] = round($row->final_amount);
                }
                if ($row->payment_type == 4) {
                    if ($row->investment_id) {
                        $data = $dateInvestDetail;
                        $account = $data->account_number;
                    }
                } else {
                    if ($row->account_number) {
                        $account = $row->account_number;
                    } else {
                        $account = 'N/A';
                    }
                }
                $val['ACCOUNT NO'] = $account;
                if (isset($row->investment_id)) {
                    $member_id = $dateInvestDetail->member_id;
                    $ac = $row['investment']['ssb']; //SavingAccount::where('member_id',$member_id)->first();
                    if ($ac) {
                        $val['SSB ACCOUNT NO'] = $ac->account_no;
                    } else {
                        $val['SSB ACCOUNT NO'] = $row->ssb_account;
                    }
                } else {
                    $val['SSB ACCOUNT NO'] = 'N/A';
                }
                if (isset($row->investment_id)) {
                    $member_id = $dateInvestDetail->member_id;
                    $member_name = $row['investment']['member']->first_name . ' ' . $row['investment']['member']->last_name; //getMemberData($member_id)->first_name.' '.getMemberData($member_id)->last_name;
                    $val['MEMBER NAME'] = $member_name;
                } else {
                    $val['MEMBER NAME'] = 'N/A';
                }
                $nominee_name = 'N/A';
                if ($member_id > 0) {
                    $nominee_name = getMemberNomineeDetail($member_id);
                    if (isset($nominee_name->name)) {
                        $nominee_name = $nominee_name->name;
                    }
                }
                $val['NOMINEE NAME'] = $nominee_name;
                if (isset($row->investment_id)) {
                    $associate_id = $dateInvestDetail->member_id;
                    $associate_code = getMemberData($associate_id)->associate_code;
                    $val['ASSOCIATE CODE'] = $associate_code;
                } else {
                    $val['ASSOCIATE CODE'] = 'N/A';
                }
                if (isset($row->investment_id)) {
                    $associate_id = $dateInvestDetail->member_id;
                    $associate_code = getMemberData($associate_id)->associate_code;
                    $associate_name = Member::where('associate_no', $associate_code)->first(['id', 'first_name', 'last_name']);
                    if (isset($associate_name->first_name) && isset($associate_name->last_name)) {
                        $associate_name = $associate_name->first_name . ' ' . $associate_name->last_name;
                    } else if (isset($associate_name->first_name)) {
                        //dd($associate_name->first_name);
                        $associate_name = $associate_name->first_name;
                    }
                } else {
                    $associate_name = 'N/A';
                }
                $val['ASSOCIATE NAME'] = $associate_name;
                $loanDetail = $this->getData(new Memberloans(), $member_id);
                $val['IS LOAN'] = $loanDetail;
                if (isset($row->investment_id)) {
                    $total_amount = Daybook::where('investment_id', $row->investment_id)->whereIn('transaction_type', [2, 4])->sum('deposit');
                } else {
                    $total_amount = 'N/A';
                }
                $val['TOTAL DEPOSIT AMOUNT'] = $total_amount;
                if (isset($row->tds_amount)) {
                    $val['TDS AMOUNT'] = round($row->tds_amount);
                } else {
                    $val['TDS AMOUNT'] = 'N/A';
                }
                $amount = '0';
                if (isset($row['sumdeposite'])) {
                    $total_amount = $row['sumdeposite']->sum('deposit');
                }
                if (isset($row['sumdeposite2'])) {
                    $total_amount = $row->sumdeposite2->sum('deposit');
                } else {
                    $total_amount = 0;
                }
                if (isset($row->interestAmount)) {
                    $amount = round($row->interestAmount);
                } else {
                    $amount = 0;
                }
                $val['INTEREST AMOUNT'] = $amount;
                $val['Total Amount With Interest'] = round($row->maturity_amount_till_date);
                $type = '';
                if ($row->payment_type == 0) {
                    $type = 'Expenses';
                } elseif ($row->payment_type == 1) {
                    $type = 'Maturity';
                } elseif ($row->payment_type == 2) {
                    $type = 'Prematurity';
                } elseif ($row->payment_type == 3) {
                    if ($row->sub_payment_type == 4) {
                        $type = 'Death Help';
                    } elseif ($row->sub_payment_type == 5) {
                        $type = 'Death Claim';
                    }
                } elseif ($row->payment_type == 4) {
                    $type = "Emergency Maturity";
                }
                $val['ADVICE TYPE'] = $type;
                $sub_type = '';
                if ($row->sub_payment_type == '0') {
                    $sub_type = 'Fresh Expense';
                } elseif ($row->sub_payment_type == '1') {
                    $sub_type = 'TA Advanced';
                } elseif ($row->sub_payment_type == '2') {
                    $sub_type = 'Advanced salary';
                } elseif ($row->sub_payment_type == '3') {
                    $sub_type = 'Advanced Rent';
                } elseif ($row->sub_payment_type == '4') {
                    $sub_type = 'N/A';
                } elseif ($row->sub_payment_type == '5') {
                    $sub_type = 'N/A';
                } else {
                    $sub_type = 'N/A';
                }
                $val['EXPENSE TYPE'] = $sub_type;
                $val['VOUCHER NO'] = $row->voucher_number;
                $mode = 'N/A';
                if (isset($row->payment_mode)) {
                    if ($row->payment_mode == 0) {
                        $mode = "Cash";
                    }
                    if ($row->payment_mode == 1) {
                        $mode = "Cheque";
                    }
                    if ($row->payment_mode == 2) {
                        $mode = "Online Transfer";
                    }
                    if ($row->payment_mode == 3) {
                        $mode = "SSB Transfer";
                    }
                }
                $val['PAYMENT MODE'] = $mode;
                if ($row->id) {
                    if ($row->payment_mode == 2) {
                        $transaction = AllHeadTransaction::where('head_id', 92)->where('type_id', $row->id)->first(['id', 'amount']);
                        ;
                        if ($transaction) {
                            $val['RTGS CHARGE'] = round($transaction->amount);
                        } else {
                            $val['RTGS CHARGE'] = 'N/A';
                        }
                    } else {
                        $val['RTGS CHARGE'] = 'N/A';
                    }
                } else {
                    $val['RTGS CHARGE'] = 'N/A';
                }
                if (isset($row->bank_account_number)) {
                    $a = $row->bank_account_number;
                    $val['BANK ACCOUNT NO'] = "'" . $a . "'";
                } else {
                    $val['BANK ACCOUNT NO'] = "N/A";
                }
                if (isset($row->bank_ifsc)) {
                    $val['IFSC CODE'] = $row->bank_ifsc;
                } else {
                    $val['IFSC CODE'] = "N/A";
                }
                if ($row->is_print == 0) {
                    $print = 'Yes';
                } else {
                    $print = 'No';
                }
                $val['PRINT'] = $print;
                if ($row->status == 0) {
                    $status = 'Pending';
                } else {
                    $status = 'Approved';
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
            // dd($handle);
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
        } elseif ($request['demand_advice_report_export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.demand-advice.demand_advice_export', compact('data'));
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('demand_advice_report.pdf');
        }
    }
    public function exportLedger(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/rent-ledger_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/rent-ledger_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['rent_month'] = $request->rent_month;
        $request['rent_year'] = $request->rent_year;
        $request['is_search'] = $request->is_search;
        $request['status'] = $request->status;
        $request['company_id'] = $request->company_id;
        $data = \App\Models\RentLedger::where('id', '>', 0)->with('company:id,name')->where('is_deleted', 0);
        if ($request['rent_month'] != '') {
            $rent_month = $request['rent_month'];
            $data = $data->where('month', $rent_month);
        }
        if ($request['rent_year'] != '') {
            $rent_year = $request['rent_year'];
            $data = $data->where('year', $rent_year);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['company_id'] != 0) {
            $company_id = $request['company_id'];
            $data = $data->where('company_id', $company_id);
        }
        if ($request['export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY'] = $row['company']->name;
                $val['MONTH'] = $row->month_name;
                $val['YEAR'] = $row->year;
                $val['TOTAL AMOUNT'] = number_format((float) $row->total_amount, 2, '.', '');
                $val['TDS AMOUNT'] = number_format((float) $row->tds_amount, 2, '.', '');
                $val['PAYABLE AMOUNT'] = number_format((float) $row->payable_amount, 2, '.', '');
                $val['TRANSFER AMOUNT'] = number_format((float) $row->transfer_amount, 2, '.', '');
                $pending = $row->total_amount - $row->transfer_amount - $row->tds_amount;
                $val['PENDING AMOUNT'] = number_format((float) $pending, 2, '.', '');
                $neft = \App\Models\RentPayment::where('ledger_id', $row->id)->sum('neft_charge');
                $val['NEFT CHARGE'] = number_format((float) $neft, 2, '.', '');
                $status = 'Pending ';
                if ($row->status == 1) {
                    $status = 'Transferred';
                }
                if ($row->status == 2) {
                    $status = 'Partial Transfer';
                }
                $val['STATUS'] = $status;
                $val['CREATED'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
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
    public function exportRentPaymentLedger(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/payment_ledger_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/payment_ledger_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $is_search = $request['is_search'];
        $branch_id = $request['branch_id'];
        $company_id = $request['company_id'];
        $status = $request['status'];
        $month = $request->month;
        $year = $request->year;
        $rent_type = $request->rent_type;
        $data = \App\Models\RentPayment::select('id', 'rent_liability_id', 'branch_id', 'company_bank_id', 'company_bank_ac_id', 'owner_ssb_id', 'employee_id', 'rent_liability_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent_amount', 'yearly_increment', 'status', 'current_advance_payment', 'actual_transfer_amount', 'transfer_amount', 'advance_payment', 'settle_amount', 'transferred_date', 'v_date', 'v_no', 'transfer_mode', 'payment_mode', 'company_cheque_id', 'company_cheque_no', 'online_transaction_no', 'neft_charge', 'tds_amount', 'company_id', 'year', 'month', 'month_name')
            ->with([
                'rentLib' => function ($q) {
                    $q->select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'agreement_to')
                        ->with([
                            'AcountHeadCustom' => function ($q) {
                                $q->select('id', 'head_id', 'sub_head');
                            }
                        ]);
                }
            ])->with([
                    'rentSSB' => function ($q) {
                        $q->select('id', 'account_no');
                    }
                ])->with([
                    'rentBank' => function ($q) {
                        $q->select('id', 'bank_name');
                    }
                ])->with([
                    'rentBankAccount' => function ($q) {
                        $q->select('id', 'account_no');
                    }
                ])->with([
                    'rentBranch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ])->with([
                    'rentEmp' => function ($query) {
                        $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with([
                            'designation' => function ($q) {
                                $q->select('id', 'designation_name');
                            }
                        ]);
                    }
                ])->has('rentCompany')->with('rentCompany:id,short_name')->where('is_deleted', 0);
        // $data=\App\Models\RentPayment::with(['rentLib' => function($q){ $q->with('AcountHeadCustom'); }])->with('rentSSB')->with('rentBank')->with('rentBankAccount')->with(['rentBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->with(['rentEmp' => function($query){ $query->select('id', 'designation_id','employee_code','employee_name','mobile_no')->with('designation');}]);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        if ($is_search == 'yes') {
            if ($branch_id > 0) {
                $data = $data->where('branch_id', $branch_id);
            }
            if ($company_id > 0) {
                $data = $data->where('company_id', $company_id);
            }
            if ($status != '') {
                $status = $status;
                $data = $data->where('status', $status);
            }
            if ($month != '') {
                $month = $month;
                $data = $data->where('month', $month);
            }
            if ($year != '') {
                $year = $year;
                $data = $data->where('year', $year);
            }
            if ($rent_type != '') {
                $rent_type = $rent_type;
                $data = $data->whereHas('rentLib', function ($query) use ($rent_type) {
                    $query->where('rent_liabilities.rent_type', $rent_type);
                });
            }
        }
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = isset($row['rentCompany']->short_name) ? $row['rentCompany']->short_name : "N/A";
                $val['BR NAME'] = $row['rentBranch']->name;
                $val['BR CODE'] = $row['rentBranch']->branch_code;
                $val['SO NAME'] = $row['rentBranch']->sector;
                $val['RO NAME'] = $row['rentBranch']->regan;
                $val['ZO NAME'] = $row['rentBranch']->zone;
                $val['MONTH'] = $row->month_name;
                $val['YEAR'] = $row->year;
                $val['RENT TYPE'] = $row['rentLib']['AcountHeadCustom']->sub_head; //getAcountHead($row['rentLib']->rent_type);
                $val['PERIOD FORM'] = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)));
                $val['PERIOD TO'] = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)));
                $val['ADDRESS'] = preg_replace("/\r|\n/", "", $row['rentLib']->place);
                $val['OWNER NAME'] = $row['rentLib']->owner_name;
                $val['OWNER MOBILE NO'] = $row['rentLib']->owner_mobile_number;
                $val['OWNER PAN CARD'] = $row['rentLib']->owner_pen_number;
                $val['OWNER AADHAR CARD'] = $row['rentLib']->owner_aadhar_number;
                $owner_ssb_account = '';
                if ($row['rentSSB']) {
                    $owner_ssb_account = $row['rentSSB']->account_no;
                }
                $val['OWNER SSB A/C'] = $owner_ssb_account;
                $val['OWNER BANK NAME'] = $row->owner_bank_name;
                $val['OWNER BANK A/C'] = $row->owner_bank_account_number;
                $val['OWNER IFSC CODE'] = $row->owner_bank_ifsc_code;
                $val['SECURITY AMOUNT'] = number_format((float) $row->security_amount, 2, '.', '');
                $val['YEARLY INCREMENT'] = number_format((float) $row->yearly_increment, 2, '.', '') . '%';
                $val['OFFICE SQURE FEET AREA'] = $row['rentLib']->office_area;
                $val['RENT'] = number_format((float) $row->rent_amount, 2, '.', '');
                $actual = 'N/A';
                $actual = 'N/A';
                if ($row->actual_transfer_amount) {
                    $actual = number_format((float) $row->actual_transfer_amount + $row->tds_amount, 2, '.', '');
                }
                $val['ACTUAL RENT AMOUNT'] = $actual;
                $val['TDS AMOUNT'] = number_format((float) $row->tds_amount, 2, '.', '');
                $val['TRASFER AMOUNT'] = number_format((float) $row->transfer_amount, 2, '.', '');
                $advance = 'N/A';
                if ($row->advance_payment) {
                    $advance = number_format((float) $row->advance_payment, 2, '.', '');
                }
                $val['ADVANCE PAYMENT'] = $advance;
                $settle = 'N/A';
                if ($row->settle_amount) {
                    $settle = number_format((float) $row->settle_amount, 2, '.', '');
                }
                $val['SETTLE AMOUNT'] = $settle;
                $status = 'Pending';
                if ($row->status == 1) {
                    $status = 'Transferred ';
                }
                $val['TRANSFER STATUS'] = $status;
                $transfer_date = 'N/A';
                if ($row->transferred_date) {
                    $transfer_date = date("d/m/Y", strtotime(convertDate($row->transferred_date)));
                }
                $val['TRANSFER DATE'] = $transfer_date;
                $v_no = 'N/A';
                if ($row->v_no) {
                    $v_no = $row->v_no;
                }
                $val['V NO'] = $v_no;
                $v_date = 'N/A';
                if ($row->v_date) {
                    $v_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                }
                $val['V DATE'] = $v_date;
                $bank = 'N/A';
                if ($row->company_bank_id) {
                    $bank = $row['rentBank']->bank_name;
                }
                $val['BANK NAME'] = $bank;
                $bank_ac = 'N/A';
                if ($row->company_bank_ac_id) {
                    $bank_ac = $row['rentBankAccount']->account_no;
                }
                $val['BANK A/C'] = $bank_ac;
                $payment_mode = 'NA';
                if ($row->payment_mode == '1') {
                    $payment_mode = 'Cheque';
                }
                if ($row->payment_mode == '0') {
                    $payment_mode = 'Online';
                }
                $val['PAYMENT MODE'] = $payment_mode;
                $cheque = 'N/A';
                if ($row->company_cheque_id) {
                    $cheque = $row->company_cheque_no;
                }
                $val['CHEQUE NO'] = $cheque;
                $online_no = 'N/A';
                if ($row->online_transaction_no) {
                    $online_no = $row->online_transaction_no;
                }
                $val['ONLINE TRANSCATION NO'] = $online_no;
                $neft = 'N/A';
                if ($row->neft_charge) {
                    $neft = $row->neft_charge;
                }
                $val['NEFT CHARGE'] = $neft;
                $val['EMPLOYEE CODE'] = $row['rentEmp']->employee_code;
                $val['EMPLOYEE NAME'] = $row['rentEmp']->employee_name;
                $val['EMPLYEE DESIGNATION'] = $row['rentEmp']['designation']->designation_name; //getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name;
                $val['EMPLOYEE MOBILE NO'] = $row['rentEmp']->mobile_no;
                // p($val['EMPLOYEE MOBILE NO'], $row);
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

    public function exportLedgerReport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/ledger_report.csv";
        $fileName = env('APP_EXPORTURL') . "asset/ledger_report.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $is_search = $request['is_search'];
        $branch_id = $request['branch_id'];
        $company_id = $request['company_id'];
        $status = $request['status'];
        $ledger_id = $request['ledger_id'];
        $data = \App\Models\RentPayment::with([
            'rentLib' => function ($q) {
                $q->with('AcountHeadCustom');
            }
        ])->with('rentSSB')->with('rentBank')->with('rentBankAccount')->with([
                    'rentBranch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ])->with([
                    'rentEmp' => function ($query) {
                        $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with('designation');
                    }
                ])->with('rentCompany:id,short_name')->where('ledger_id', $ledger_id);
        if ($branch_id > 0) {
            $data = $data->where('branch_id', $branch_id);
        }
        if ($company_id > 0) {
            $data = $data->where('company_id', $company_id);
        }
        if ($status != '') {
            $status = $status;
            $data = $data->where('status', $status);
        }
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['COMPANY NAME'] = $row['rentCompany']->short_name;
                $val['BR NAME'] = $row['rentBranch']->name;
                $val['BR CODE'] = $row['rentBranch']->branch_code;
                $val['SO NAME'] = $row['rentBranch']->sector;
                $val['RO NAME'] = $row['rentBranch']->regan;
                $val['ZO NAME'] = $row['rentBranch']->zone;
                if ($row['rentLib']['AcountHeadCustom']) {
                    $val['RENT TYPE'] = $row['rentLib']['AcountHeadCustom']->sub_head; //getAcountHead($row['rentLib']->rent_type);
                } else {
                    $val['RENT TYPE'] = 'N/A';
                }
                $val['RENT TYPE'] = getAcountHead($row['rentLib']->rent_type);
                $val['PERIOD FORM'] = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)));
                $val['PERIOD TO'] = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)));
                $val['ADDRESS'] = preg_replace("/\r|\n/", "", $row['rentLib']->place);
                $val['OWNER NAME'] = $row['rentLib']->owner_name;
                $val['OWNER MOBILE NO'] = $row['rentLib']->owner_mobile_number;
                $val['OWNER PAN CARD'] = $row['rentLib']->owner_pen_number;
                $val['OWNER AADHAR CARD'] = $row['rentLib']->owner_aadhar_number;
                $owner_ssb_account = '';
                if ($row['rentSSB']) {
                    $owner_ssb_account = $row['rentSSB']->account_no;
                }
                $val['OWNER SSB ACCOUNT'] = $owner_ssb_account;
                $val['OWNER BANK NAME'] = $row->owner_bank_name;
                $val['OWNER BANK A/C'] = $row->owner_bank_account_number;
                $val['OWNER IFSC A/C'] = $row->owner_bank_ifsc_code;
                $val['SECURITY AMOUNT'] = number_format((float) $row->security_amount, 2, '.', '');
                $val['YEARLY INCREMENT'] = number_format((float) $row->yearly_increment, 2, '.', '') . '%';
                $val['OFFICE SQURE FEET AREA'] = $row['rentLib']->office_area;
                $val['RENT AMOUNT'] = number_format((float) $row->rent_amount, 2, '.', '');
                $actual = 'N/A';
                if ($row->actual_transfer_amount) {
                    $actual = number_format((float) $row->actual_transfer_amount + $row->tds_amount, 2, '.', '');
                }
                $val['ACTUAL RENT AMOUNT'] = $actual;
                $val['TDS  AMOUNT'] = number_format((float) $row->tds_amount, 2, '.', '');
                $val['PAYABLE AMOUNT AFTER TDS'] = number_format((float) $row->transfer_amount, 2, '.', '');
                $advance = 'N/A';
                if ($row->advance_payment) {
                    $advance = number_format((float) $row->advance_payment, 2, '.', '');
                }
                $val['ADVANCE PAYMENT'] = $advance;
                $settle = 'N/A';
                if ($row->settle_amount) {
                    $settle = number_format((float) $row->settle_amount, 2, '.', '');
                }
                $val['SETTLE AMOUNT'] = $settle;
                $status = 'Pending';
                if ($row->status == 1) {
                    $status = 'Transferred ';
                }
                $val['TRANSFER STATUS'] = $status;
                $transfer_date = 'N/A';
                if ($row->transferred_date) {
                    $transfer_date = date("d/m/Y", strtotime(convertDate($row->transferred_date)));
                }
                $val['TRASFER DATE'] = $transfer_date;
                $mode = 'N/A';
                if ($row->transfer_mode == 1) {
                    $mode = 'SSB';
                }
                if ($row->transfer_mode == 2) {
                    $mode = 'Bank';
                }
                $val['TRANSFER MODE'] = $mode;
                $v_no = 'N/A';
                if ($row->v_no) {
                    $v_no = $row->v_no;
                }
                $val['V NO'] = $v_no;
                $v_date = 'N/A';
                if ($row->v_date) {
                    $v_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                }
                $val['V DATE'] = $v_date;
                $bank = 'N/A';
                if ($row->company_bank_id) {
                    $bank = $row['rentBank']->bank_name;
                }
                $val['BANK NAME'] = $bank;
                $bank_ac = 'N/A';
                if ($row->company_bank_ac_id) {
                    $bank_ac = $row['rentBankAccount']->account_no;
                }
                $val['BANK A/C'] = $bank_ac;
                $payment_mode = 'NA';
                if ($row->payment_mode == '1') {
                    $payment_mode = 'Cheque';
                }
                if ($row->payment_mode == '0') {
                    $payment_mode = 'Online';
                }
                $val['PAYMENT MODE'] = $payment_mode;
                $cheque = 'N/A';
                if ($row->company_cheque_id) {
                    $cheque = $row->company_cheque_no;
                }
                $val['CHEQUE NO'] = $cheque;
                $online_no = 'N/A';
                if ($row->online_transaction_no) {
                    $online_no = $row->online_transaction_no;
                }
                $val['ONLINE TRANSCATION NO'] = $online_no;
                $neft = 'N/A';
                if ($row->neft_charge) {
                    $neft = number_format((float) $row->neft_charge, 2, '.', '');
                }
                $val['NEFT CHARGE'] = $neft;
                $val['EMPLOYEE CODE'] = $row['rentEmp']->employee_code;
                $val['EMPLOYEE NAME'] = $row['rentEmp']->employee_name;
                if ($row['rentEmp']['designation']) {
                    $val['EMPLOYEE DESIGNATION'] = $row['rentEmp']['designation']->designation_name; //getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name;
                } else {
                    $val['EMPLOYEE DESIGNATION'] = 'N/A';
                }
                $val['EMPLOYEE MOBILE NO'] = $row['rentEmp']->mobile_no;
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
    public function exportRentTransfer(Request $request)
    {
        $selectedRent_exp = $request['selectedRent_exp'];
        $select_id_get = rtrim($request->selectedRent_exp, ',');
        $select_id = explode(",", $select_id_get);
        //print_r($_POST);die;
        $data = \App\Models\RentPayment::with('rentLib')->with('rentCompany:id,name')->with('rentSSB')->with([
            'rentBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->with([
                    'rentEmp' => function ($query) {
                        $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no');
                    }
                ])->whereIn('id', $select_id)->get();
        if ($request['export'] == 0) {
            return Excel::download(new RentTransferReportExport($data), 'rentTransferDetail.xlsx');
        }
    }
    public function exportSalaryLedger(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/hr_salary_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/hr_salary_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = \App\Models\EmployeeSalaryLeaser::with(['company:id,name'])->where('id', '>', 0)->where('is_deleted', 0);
        if ($request['month'] != '') {
            $month = $request['month'];
            $data = $data->where('month', $month);
        }
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            $data = $data->where('company_id', $company_id);
        }
        if ($request['year'] != '') {
            $year = $request['year'];
            $data = $data->where('year', $year);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['report_export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['Company Name'] = $row->company->name;
                $val['Month'] = $row->month_name;
                $val['Year'] = $row->year;
                $val['Total Amount'] = number_format((float) $row->total_amount, 2, '.', '');
                $val['Transfer Amount'] = number_format((float) $row->transfer_amount, 2, '.', '');
                $pending = $row->total_amount - $row->transfer_amount;
                $val['Pending Amount'] = number_format((float) $pending, 2, '.', '');
                $pending = EmployeeSalary::where('leaser_id', $row->id)->sum('neft_charge');
                $val['Neft Charge'] = number_format((float) $row->total_neft, 2, '.', '');
                $status = 'Pending ';
                if ($row->status == 1) {
                    $status = 'Transferred ';
                }
                if ($row->status == 2) {
                    $status = 'Partial Transfer ';
                }
                $val['Status'] = $status;
                $val['Created'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
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
    public function exportSalaryList(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/salary_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/salary_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = \App\Models\EmployeeSalary::with('salary_employee')->with([
            'salary_branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->where('leaser_id', $request->leaser_id)->where('is_deleted', 0);
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('is_transferred', $status);
        }
        if ($request['category'] != '') {
            $category = $request['category'];
            if ($category > 0) {
                $data = $data->where('category', $category);
            }
        }
        if ($request['designation'] != '') {
            $designation = $request['designation'];
            if ($designation > 0) {
                $data = $data->where('designation_id', $designation);
            }
        }
        if ($request['employee_name'] != '') {
            $employee_name = $request['employee_name'];
            $data = $data->whereHas('salary_employee', function ($query) use ($employee_name) {
                $query->where('employees.employee_name', 'LIKE', '%' . $employee_name . '%');
            });
        }
        if ($request['employee_code'] != '') {
            $employee_code = $request['employee_code'];
            $data = $data->whereHas('salary_employee', function ($query) use ($employee_code) {
                $query->where('employees.employee_code', 'LIKE', '%' . $employee_code . '%');
            });
        }
        if ($request['export'] == 0) {
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $category = 'All';
                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                if ($row->designation_id) {
                    $val['designation'] = getDesignationData('designation_name', $row->designation_id)->designation_name;
                } else {
                    $val['designation'] = 'All';
                }
                $val['Category name'] = $category;
                $val['Br name'] = $row['salary_branch']->name;
                $val['Br code'] = $row['salary_branch']->branch_code;
                $val['So name'] = $row['salary_branch']->sector;
                $val['So name'] = $row['salary_branch']->regan;
                $val['So name'] = $row['salary_branch']->zone;
                $val['Employee name'] = $row['salary_employee']->employee_name;
                $val['Employee code'] = $row['salary_employee']->employee_code;
                $val['Gross salary'] = number_format((float) $row->fix_salary, 2, '.', '');
                $val['Leave'] = number_format((float) $row->leave, 1, '.', '');
                $val['Total salary'] = number_format((float) $row->total_salary, 2, '.', '');
                $val['Deduction'] = number_format((float) $row->deduction, 2, '.', '');
                $val['Incentive bonus'] = number_format((float) $row->incentive_bonus, 2, '.', '');
                if ($row->settle_amount > 0) {
                    $val['Advance salary'] = number_format((float) $row->advance_payment, 2, '.', '');
                    $val['Settle salary'] = number_format((float) $row->settle_amount, 2, '.', '');
                }
                $val['Payable amount'] = number_format((float) $row->paybale_amount, 2, '.', '');
                $val['Esi amount'] = number_format((float) $row->esi_amount, 2, '.', '');
                $val['Pf amount'] = number_format((float) $row->pf_amount, 2, '.', '');
                $val['Tds amount'] = number_format((float) $row->tds_amount, 2, '.', '');
                $val['Final Payable Salary'] = number_format((float) $row->actual_transfer_amount, 2, '.', '');
                $val['Advance salary'] = "N/A";
                $val['Settle salary'] = "N/A";
                $val['Transferred Salary'] = number_format((float) $row->transferred_salary, 2, '.', '');
                $val['Transferred in'] = 'N/A';
                if ($row->transferred_in == 1) {
                    $val['Transferred in'] = 'SSB';
                }
                if ($row->transferred_in == 2) {
                    $val['Transferred in'] = 'Bank';
                }
                if ($row->transferred_in == 0 && $row->transferred_in != NULL) {
                    $val['Transferred in'] = 'Cash';
                }
                $val['Employee ssb'] = $row->employee_ssb;
                $val['Employee bank'] = $row->employee_bank;
                $val['Employee bank_ac'] = $row->employee_bank_ac;
                $val['Employee bank ifsc'] = $row->employee_bank_ifsc;
                /** $val['company_ssb']=$row->company_ssb;*/
                if ($row->transferred_date) {
                    $val['Transferred_date'] = date("d/m/Y", strtotime($row->transferred_date));
                } else {
                    $val['Transferred_date'] = 'N/A';
                }
                $val['Bank name'] = 'N/A';
                $val['Bank ac'] = 'N/A';
                if ($row->transferred_in == 2) {
                    $bankfrmDetail = \App\Models\SamraddhBank::where('id', $row->company_bank)->first();
                    $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $row->company_bank_ac)->first();
                    $val['Bank name'] = $bankfrmDetail->bank_name;
                    $val['Bank ac'] = $bankacfrmDetail->account_no;
                }
                $val['Payment mode'] = 'N/A';
                if ($row->transferred_in == 2) {
                    if ($row->payment_mode == 1) {
                        $val['Payment mode'] = 'Cheque';
                    }
                    if ($row->payment_mode == 2) {
                        $val['Payment mode'] = 'Online';
                    }
                }
                $val['Cheque no'] = 'N/A';
                if ($row->payment_mode == 1 && $row->transferred_in == 2) {
                    $c = \App\Models\SamraddhCheque::where('id', $row->company_cheque_id)->first();
                    $val['Cheque no'] = $c->cheque_no;
                }
                $val['Online UTR/tractions No.'] = 'N/A';
                // $val['online']='N/A';
                $val['Neft charge'] = 'N/A';
                if ($row->payment_mode == 2 && $row->transferred_in == 2) {
                    $val['Online UTR/tractions No.'] = $row->online_transaction_no;
                    //$val['online']=$row->online_transaction_no;
                    $val['Neft charge'] = number_format((float) $row->neft_charge, 2, '.', '');
                }
                if ($row->is_transferred == 0) {
                    $val['Is transfer '] = 'No';
                } else {
                    $val['Is transfer'] = 'Yes';
                }
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
    public function exportSalaryTransfer(Request $request)
    {
        $select_id_get = rtrim($request->selectedRent_exp, ',');
        $select_id = explode(",", $select_id_get);
        $data = \App\Models\EmployeeSalary::with('salary_employee')->with([
            'salary_branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->whereIn('id', $select_id)->where('is_deleted', 0);
        /******* fillter query start ****/
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['category'] != '') {
                $category = $request['category'];
                if ($category > 0) {
                    $data = $data->where('category', $category);
                }
            }
            if ($request['designation'] != '') {
                $designation = $request['designation'];
                if ($designation > 0) {
                    $data = $data->where('designation_id', $designation);
                }
            }
            if ($request['employee_name'] != '') {
                $employee_name = $request['employee_name'];
                $data = $data->whereHas('salary_employee', function ($query) use ($employee_name) {
                    $query->where('employees.employee_name', 'LIKE', '%' . $employee_name . '%');
                });
            }
            if ($request['employee_code'] != '') {
                $employee_code = $request['employee_code'];
                $data = $data->whereHas('salary_employee', function ($query) use ($employee_code) {
                    $query->where('employees.employee_code', 'LIKE', '%' . $employee_code . '%');
                });
            }
        }
        $data = $data->orderby('created_at', 'DESC')->get();
        if ($request['export'] == 0) {
            return Excel::download(new SalaryTransferExport($data), 'salaryTransferList.xlsx');
        }
    }
    public function exportEmploySalaryList(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/hr_salary_employ_laser.csv";
        $fileName = env('APP_EXPORTURL') . "asset/hr_salary_employ_laser.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $request['category'] = $request->category;
        $request['company_id'] = $request->company_id;
        $request['designation'] = $request->designation;
        $request['employee_name'] = $request->employee_name;
        $request['employee_code'] = $request->employee_code;
        $request['is_search'] = $request->is_search;
        $request['status'] = $status = $request->status;
        $request['month'] = $month = $request->month;
        $request['year'] = $year = $request->year;
        // $data = \App\Models\EmployeeSalary::with('employee_salary_company:id,name')->with('salary_employee', 'salaryCheque', 'salaryDesignationCustom', 'salaryBank', 'salaryBankAccount')->with([
        //     'salary_branch' => function ($query) {
        //         $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        //     }
        // ]);
        // if (Auth::user()->branch_id > 0) {
        //     $data = $data->where('branch_id', '=', Auth::user()->branch_id);
        // }
        // if ($request['status'] != '') {
        //     $status = $request['status'];
        //     $data = $data->where('is_transferred', $status);
        // }
        // if ($request['company_id'] > 0) {
        //     $company_id = $request['company_id'];
        //     $data = $data->where('company_id', $company_id);
        // }
        // if ($request['month'] != '') {
        //     $month = $request['month'];
        //     $data = $data->where('month', $month);
        // }
        // if ($request['year'] != '') {
        //     $year = $request['year'];
        //     $data = $data->where('year', $year);
        // }
        // if ($request['category'] != '') {
        //     $category = $request['category'];
        //     if ($category > 0) {
        //         $data = $data->where('category', $category);
        //     }
        // }
        // if ($request['designation'] != '') {
        //     $designation = $request['designation'];
        //     if ($designation > 0) {
        //         $data = $data->where('designation_id', $designation);
        //     }
        // }
        // if ($request['employee_name'] != '') {
        //     $employee_name = $request['employee_name'];
        //     $data = $data->whereHas('salary_employee', function ($query) use ($employee_name) {
        //         $query->where('employees.employee_name', 'LIKE', '%' . $employee_name . '%');
        //     });
        // }
        // if ($request['employee_code'] != '') {
        //     $employee_code = $request['employee_code'];
        //     $data = $data->whereHas('salary_employee', function ($query) use ($employee_code) {
        //         $query->where('employees.employee_code', 'LIKE', '%' . $employee_code . '%');
        //     });
        // }
        // if ($request['report_export'] == 0) {
        //     $totalResults = $data->orderby('created_at', 'DESC')->count();
        //     $results = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
        if ($request['report_export'] == 0) {
            $token = session()->get('_token');
            $results = Cache::get('employeesalarypayable_list' . $token);
            $totalResults = Cache::get('employeesalarypayable_count' . $token);
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
                $val['Company Name'] = $row['company']['name'];
                $category = '';
                if ($row['category'] == 1) {
                    $category = 'On-rolled';
                }
                if ($row['category'] == 2) {
                    $category = 'Contract';
                }
                $val['Category'] = $category;
                $designation_name = '';
                if ($row['designation_id']) {
                    $designation_name = $row['salary_designation_custom']['designation_name']; //getDesignationData('designation_name',$row['designation_id'])->designation_name;
                } else {
                    $designation_name = 'All';
                }
                $val['Designation'] = $designation_name;
                $val['Month'] = $row['month_name'];
                $val['Year'] = $row['year'];
                $val['Br Name'] = $row['salary_branch']['name'];
                $val['Br Code'] = $row['salary_branch']['branch_code'];
                $val['SO Name'] = $row['salary_branch']['sector'];
                $val['Ro Name'] = $row['salary_branch']['regan'];
                $val['ZO Name'] = $row['salary_branch']['zone'];
                $val['Employee Name'] = $row['salary_employee']['employee_name'];
                $val['Employee Code'] = $row['salary_employee']['employee_code'];
                $val['Gross Salary'] = number_format((float) $row['fix_salary'], 2, '.', '');
                $val['Leave'] = number_format((float) $row['leave'], 1, '.', '');
                $val['Total Salary'] = number_format((float) $row['total_salary'], 2, '.', '');
                $val['Deduction'] = number_format((float) $row['deduction'], 2, '.', '');
                $val['Incentive / Bonus'] = number_format((float) $row['incentive_bonus'], 2, '.', '');
                $val['Payable Amount'] = number_format((float) $row['paybale_amount'], 2, '.', '');
                $val['Esi Amount'] = number_format((float) $row['esi_amount'], 2, '.', '');
                $val['Pf Amount'] = number_format((float) $row['pf_amount'], 2, '.', '');
                $val['Tds Amount'] = number_format((float) $row['tds_amount'], 2, '.', '');
                $val['Transferred Salary'] = number_format((float) $row['transferred_salary'], 2, '.', '');
                $val['Advance Salary'] = "N/A";
                $val['Settle Salary'] = "N/A";
                $transferred_in = 'N/A';
                if ($row['transferred_in'] == 1) {
                    $transferred_in = 'SSB';
                }
                if ($row['transferred_in'] == 2) {
                    $transferred_in = 'Bank';
                }
                if ($row['transferred_in'] == 0 && $row['transferred_in'] != NULL) {
                    $transferred_in = 'Cash';
                }
                $val['Transferred In'] = $transferred_in;
                $val['Employee SSB'] = $row['employee_ssb'];
                $val['Employee Bank Name'] = $row['employee_bank'];
                $val['Employee Bank A/c'] = $row['employee_bank_ac'];
                $val['Employee Bank IFSC'] = $row['employee_bank_ifsc'];
                $transferred_date = '';
                if ($row['transferred_date']) {
                    $transferred_date = date("d/m/Y", strtotime($row['transferred_date']));
                } else {
                    $transferred_date = 'N/A';
                }
                $val['Transferred Date'] = $transferred_date;
                // $val['company_bank'] = 'N/A';
                // $val['company_bank_ac'] = 'N/A';
                // if ($row['transferred_in'] == 2) {
                //     $bankfrmDetail = $row['salaryBank']; //\App\Models\SamraddhBank::where('id',$row['company_bank'])->first();
                //     $bankacfrmDetail = $row['salaryBankAccount']; //\App\Models\SamraddhBankAccount::where('id',$row['company_bank_ac'])->first();
                //     $val['company_bank'] = $bankfrmDetail->bank_name;
                //     $val['company_bank_ac'] = $bankacfrmDetail->account_no;
                // }
                $payment_mode = 'N/A';
                if ($row['transferred_in'] == 2) {
                    if ($row['payment_mode'] == 1) {
                        $payment_mode = 'Cheque';
                    }
                    if ($row['payment_mode'] == 2) {
                        $payment_mode = 'Online';
                    }
                }
                $val['Payment Mode'] = $payment_mode;
                $company_cheque_id = 'N/A';
                if ($row['payment_mode'] == 1 && $row['transferred_in'] == 2) {
                    $c = isset($row['salaryCheque']) ? $row['salaryCheque'] : null; //\App\Models\SamraddhCheque::where('id',$row['company_cheque_id'])->first();
                    $company_cheque_id = isset($c->cheque_no) ? $c->cheque_no : null;
                }
                $val['Cheque No'] = $company_cheque_id;
                $online_transaction_no = 'N/A';
                //$val['online']='N/A';
                $neft_charge = 'N/A';
                if ($row['payment_mode'] == 2 && $row['transferred_in'] == 2) {
                    $val['Online UTR/tractions No.'] = $row['online_transaction_no'];
                    $neft_charge = number_format((float) $row['neft_charge'], 2, '.', '');
                }
                $val['RTGS/NEFT Charge'] = $neft_charge;
                if ($row['is_transferred'] == 0) {
                    $val['Is transfered'] = 'No';
                } else {
                    $val['Is transfered'] = 'Yes';
                }
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
    /******************* Slary report export end***********/
    public function loanListExport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('loanReportListAdmin' . $token);
        $count = Cache::get('loanReportListCountAdmin' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $_fileName = Session::get('_fileName');
        $returnURL = URL::to('/') . "/asset/loanreport" . $_fileName . ".csv";
        $fileName = env('APP_EXPORTURL') . "asset/loanreport" . $_fileName . ".csv";
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
        $status = [
            0 => 'Inactive',
            1 => 'Approved',
            2 => 'Rejected',
            3 => 'Completed',
            4 => 'Ongoing',
        ];
        $sno = $_POST['start'];
        foreach (array_slice($data, $start, $limit) as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Staus'] = ($status[$row['status']]) ? ucwords($status[$row['status']]) : '';
            if ($row['loan_type'] == 3) {
                if (isset($row['customer_id'])) {
                    if (customGetMemberData($row['customer_id'])) {
                        $applicantName = customGetMemberData($row['customer_id'])->first_name . ' ' . customGetMemberData($row['customer_id'])->last_name;
                    } else {
                        $applicantName = 'N/A';
                    }
                } else {
                    $applicantName = 'N/A';
                }
            } else {
                if (isset($row['customer_id'])) {
                    if (isset($row['customer_id'])) {
                        $applicantName = customGetMemberData($row['customer_id'])->first_name . ' ' . customGetMemberData($row['customer_id'])->last_name;
                    } else {
                        $applicantName = 'N/A';
                    }
                } else {
                    $applicantName = 'N/A';
                }
            }
            $val['Applicant Name'] = $applicantName;
            if ($row['loan_type'] == 3) {
                if (isset($row['customer_id'])) {
                    if (customGetMemberData($row['customer_id'])) {
                        $val['Customer Id'] = customGetMemberData($row['customer_id'])->member_id ?? 'N/A';
                    } else {
                        $val['Customer Id'] = 'N/A';
                    }
                } else {
                    $val['Customer Id'] = 'N/A';
                }
            } else {
                if (isset($row['customer_id'])) {
                    if (customGetMemberData($row['customer_id'])) {
                        $val['Customer Id'] = customGetMemberData($row['customer_id'])->member_id ?? 'N/A';
                    } else {
                        $val['Customer Id'] = 'N/A';
                    }
                } else {
                    $val['Customer Id'] = 'N/A';
                }
            }
            $val['Company'] = isset($row['company']['name']) ? $row['company']['name'] : 'N/A';
            if ($row['loan_type'] == 3) {
                if (isset($row['customer_id'])) {
                    $val['Applicant Phone Number'] = customGetMemberData($row['customer_id'])->mobile_no ?? 'N/A';
                } else {
                    $val['Applicant Phone Number'] = 'N/A';
                }
            } else {
                if (isset($row['customer_id'])) {
                    $val['Applicant Phone Number'] = customGetMemberData($row['customer_id'])->mobile_no ?? 'N/A';
                } else {
                    $val['Applicant Phone Number'] = 'N/A';
                }
            }
            $val['Account No.'] = $row['account_number'];
            $val['Branch'] = getBranchDetail($row['branch_id'])->name;
            $val['Sector Branch'] = getBranchDetail($row['branch_id'])->sector;
            // pd(getMemberCustomData($row['member_id'])->member_id);
            if ($row['loan_type'] == 3) {
                if (isset($row['member_id'])) {
                    if (getMemberCustomData($row['member_id'])) {
                        $val['Member Id'] = getMemberCustomData($row['member_id'])->member_id ?? 'N/A';
                    } else {
                        $val['Member Id'] = 'N/A';
                    }
                } else {
                    $val['Member Id'] = 'N/A';
                }
            } else {
                if (isset($row['applicant_id'])) {
                    if (getMemberCustomData($row['applicant_id'])) {
                        $val['Member Id'] = getMemberCustomData($row['applicant_id'])->member_id ?? 'N/A';
                    } else {
                        $val['Member Id'] = 'N/A';
                    }
                } else {
                    $val['Member Id'] = 'N/A';
                }
            }
            // $val['Member Id'] = isset($row['loan_member_company']) ? getMemberCustomData($row['loan_member_company']['customer_id'])->member_id : 'N/A';
            // pd($val);
            $val['Sanctioned Amount'] = $row['amount'];
            $val['Transfer Amount'] = $row['deposite_amount'];
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
            $val['Loan Mode'] = $eType;
            $val['Loan Type'] = $row['loan']['name'];
            $val['Loan Issued Date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
            $mode = \App\Models\Daybook::whereIn('transaction_type', [3, 8])->where('loan_id', $row['id'])->orderby('id', 'ASC')->first(['payment_mode', 'cheque_dd_no']);
            if ($mode) {
                switch ($mode->payment_mode) {
                    case 0:
                        $val['Loan Issued Mode'] = 'Cash';
                        break;
                    case 1:
                        $val['Loan Issued Mode'] = 'Cheque';
                        break;
                    case 2:
                        $val['Loan Issued Mode'] = 'DD';
                        break;
                    case 3:
                        $val['Loan Issued Mode'] = 'Online Transaction';
                        break;
                    case 4:
                        $val['Loan Issued Mode'] = 'SSB';
                        break;
                    case 5:
                        $val['Loan Issued Mode'] = 'From Loan Amount';
                        break;
                }
            } else {
                $val['Loan Issued Mode'] = '';
            }
            $val['Cheque No.'] = $mode ? $mode->cheque_dd_no : 'N/A';
            $amount = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->whereIn('loan_sub_type', [0, 1])->where('is_deleted', 0)->sum('deposit');
            $val['Total Recovery Amt'] = $amount;
            $lastEmi = LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('loan_sub_type', '!=', '2')->where('is_deleted', 0)->orderby('created_at', 'desc')->first('created_at');
            $val['Total Recovery EMI Till Date'] = (isset($lastEmi->created_at)) ? date('d/m/Y', strtotime($lastEmi->created_at)) : '';
            if ($row['emi_option'] == 1) {
                $closingAmountROI = $row['due_amount'] * $row['ROI'] / 1200;
            } elseif ($row['emi_option'] == 2) {
                $closingAmountROI = $row['due_amount'] * $row['ROI'] / 5200;
            } elseif ($row['emi_option'] == 3) {
                $closingAmountROI = $row['due_amount'] * $row['ROI'] / 36500;
            } else {
                $closingAmountROI = 0;
            }

            // $outstandingAmount = isset($row['get_outstanding']['out_standing_amount'])
            //  ? ($row['get_outstanding']['out_standing_amount'] > 0 ? $row['get_outstanding']['out_standing_amount'] : 0)
            //  : $row['amount'];

            $outstandingAmount = getClosingAmountByLoan($row['id'], ($row['loan']['loan_type'] == 'G' ? false : true))
                ? ((getClosingAmountByLoan($row['id'], ($row['loan']['loan_type'] == 'G' ? false : true)) > 0)
                    ? getClosingAmountByLoan($row['id'], ($row['loan']['loan_type'] == 'G' ? false : true))
                    : 0)
                : $row['amount'];

            $closingAmount = round($row['due_amount'] + $closingAmountROI);

            $val['Closing Amount'] = $outstandingAmount;

            $loanComplateDate = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            $dueStartDate = $row['approve_date'] ? Carbon::createFromFormat('Y-m-d', $row['approve_date']) : Carbon::createFromFormat('Y-m-d', date('Y-m-d'));
            if ($row['emi_option'] == 1) {
                $dueTime = $dueStartDate->floatDiffInMonths($loanComplateDate);
                $firstEmiDate = $dueStartDate->addMonth(1);
            }
            if ($row['emi_option'] == 2) {
                $dueTime = $dueStartDate->diffInWeeks($loanComplateDate);
                $firstEmiDate = $dueStartDate->addWeek(1);
            }
            if ($row['emi_option'] == 3) {
                $dueTime = $dueStartDate->diffInDays($loanComplateDate);
                $firstEmiDate = $dueStartDate->addDays(1);
            }
            $cAmount = round($dueTime * $row['emi_amount']);
            $ramount = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_sub_type', '!=', 2)->where('account_number', $row['account_number'])->sum('deposit');
            if ($ramount < $cAmount) {
                $isPending = 'Yes';
            } else {
                $isPending = 'No';
            }
            $val['Balance EMI'] = $isPending;
            $val['EMI Should be received till date'] = $cAmount;
            $val['Due Emi'] = ($cAmount - $ramount < 0) ? 0 : $cAmount - $ramount;
            $val['Date'] = date('d/m/Y'); //date("d/m/Y", strtotime($row['created_at']));
            $val['Co-Applicant Name'] = $row['associate_member_id'] ? customGetMemberData($row['associate_member_id'])->first_name . ' ' . customGetMemberData($row['associate_member_id'])->last_name : 'N/A';
            $val['Co-Applicant Number'] = $row['associate_member_id'] ? customGetMemberData($row['associate_member_id'])->mobile_no : 'N/A';
            $val['Guarantor Name'] = $row['loan_guarantor'] ? $row['loan_guarantor'][0]['name'] : 'N/A';
            $val['Guarantor Number'] = $row['loan_guarantor'] ? $row['loan_guarantor'][0]['mobile_number'] : 'N/A';
            $val['Applicant Address'] = $row['loan_applicants'] ? customGetMemberExportData($row['loan_applicants'][0]['member_loan_id']) ? preg_replace("/\r|\n/", "", customGetMemberExportData($row['loan_applicants'][0]['member_loan_id'])->address) : 'N/A' : 'N/A';
            // $val['Applicant Address'] = $row['loan_applicants'] ? customGetMemberData($row['loan_applicants'][0]['member_id']) ? preg_replace( "/\r|\n/", "",customGetMemberData($row['loan_applicants'][0]['member_id'])->address) : 'N/A' : 'N/A';
            $record = \App\Models\LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->orderby('created_at', 'asc')->first('created_at');
            if ($record && isset($record)) {
                $feDate = date("d/m/Y", strtotime(convertDate($record->created_at)));
            } else {
                $feDate = 'N/A';
            }
            $val['First EMI Date'] = $feDate;
            // if (isset($row['closing_date'])) {
            //     $val['Loan End Date'] = date("d/m/Y", strtotime(convertDate($row['closing_date'])));
            // } else {
            //     $val['Loan End Date'] = 'N/A';
            // }
            if (isset($row['approve_date'])) {
                if (isset($row['emi_option'])) {
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
            } else {
                $last_recovery_date = 'N/A';
            }
            $val['Loan End Date'] = $last_recovery_date;
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
        //if($percentage > 100){
        //return Excel::download(new LoanReportListExport($DataArray), $fileName);
        //}else{
        //Excel::store(new LoanReportListExport($DataArray), $fileName);
        echo json_encode($response);
        //}
    }
    public function groupLoanListExport(Request $request)
    {
        $data = Grouploans::with('LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'loanMemberBankDetails2');
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
        $data = $data->where('is_deleted', 0)->orderby('id', 'DESC')->get();
        if ($request['export'] == 0) {
            return Excel::download(new GroupLoanReportListExport($data), 'loanReport.xlsx');
        }
    }
    public function maturityListExport(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/maturityReport.csv";
            $fileName = env('APP_EXPORTURL') . "asset/maturityReport.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = Memberinvestments::has('company')->select('id', 'member_id', 'plan_id', 'branch_id', 'account_number', 'created_at', 'maturity_date', 'is_mature', 'deposite_amount', 'tenure', 'associate_id', 'due_amount', 'company_id', 'customer_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                },
                'company' => function ($q) {
                    $q->select('id', 'name');
                },
                'memberCompany' => function ($q) {
                    $q->select('id', 'member_id');
                },
                'associateMember' => function ($q) {
                    $q->select('id', 'associate_no', 'associate_code', 'first_name', 'last_name');
                },
                'demandadvice' => function ($q) {
                    $q->select('id', 'date', 'tds_amount', 'maturity_prematurity_amount', 'final_amount', 'payment_type', 'payment_mode', 'bank_name', 'investment_id', 'maturity_amount_payable', 'bank_account_number', 'bank_name')->with([
                        'demandAmountHead' => function ($q) {
                            $q->select('id', 'amount', 'head_id', 'type_id');
                        },
                        'demandAmount' => function ($q) {
                            $q->select('id', 'amount', 'head_id', 'type_id', 'type', 'sub_type', 'amount', 'cheque_no');
                        },
                        'demandTransactionAmount' => function ($q) {
                            $q->select('id', 'amount', 'head_id', 'type_id', 'type', 'sub_type', 'amount', 'transction_no');
                        }
                    ]);
                },
                'branch' => function ($q) {
                    $q->select('id', 'name', 'branch_code', 'zone');
                },
                'plan' => function ($q) {
                    $q->select('id', 'name');
                },
                'sumdeposite',
                'TransactionTypeDate' => function ($q) {
                    $q->select('id', 'investment_id', 'created_at');
                }
            ])->where('plan_id', '!=', 1);
        /******* fillter query start ****/
        if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            if ($request['branch_id'] > 0 && $request['branch_id']) {
                $bid = $request['branch_id'];
                $data = $data->where('branch_id', $bid);
            }
            if ($request['plan_id'] != '') {
                $planId = $request['plan_id'];
                $data = $data->where('plan_id', '=', $planId);
            }
            if ($request['company_id'] > 0 && $request['company_id']) {
                $company_id = $request['company_id'];
                $data = $data->where('company_id', '=', $company_id);
            }
            if ($request['plan_id'] != '') {
                $planId = $request['plan_id'];
                $data = $data->where('plan_id', '=', $planId);
            }
            if ($request['member_id'] != '') {
                $meid = $request['member_id'];
                $data = $data->whereHas('member', function ($query) use ($meid) {
                    $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                });
            }
            if ($request['associate_code']) {
                $associate_code = $request['associate_code'];
                $data = $data->whereHas('associateMember', function ($query) use ($associate_code) {
                    $query->where('members.associate_no', 'Like', '%' . $associate_code . '%');
                });
            }
            if ($request['scheme_account_number']) {
                $scheme_account_number = $request['scheme_account_number'];
                $data = $data->where('account_number', 'Like', '%' . $scheme_account_number . '%');
            }
            if ($request['name'] != '') {
                $name = $request['name'];
                $data = $data->whereHas('member', function ($query) use ($name) {
                    $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                });
            }
            if ($request['start_date'] != '' && $request['status'] == '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            if ($request['status'] != '' && (($request['start_date'] != '' || $request['from_date'] != '') || $request['end_date'] != '') || ($request['start_date'] == Null || $request['end_date'] == Null)) {
                $status = $request['status'];
                $Date = date('Y-m-d');
                if ($request['status'] == 0) {
                    if ($request['start_date'] != '' || $request['from_date'] != '') {
                        if ($request['start_date'] != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                            $startDateMonth = date("m", strtotime(convertDate($request['start_date'])));
                            $startDateYear = date("Y", strtotime(convertDate($request['start_date'])));
                        }
                        if ($request['from_date'] != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request['from_date'])));
                            $startDateMonth = date("m", strtotime(convertDate($request['from_date'])));
                            $startDateYear = date("Y", strtotime(convertDate($request['from_date'])));
                        }
                        if ($request['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $currentDateMonth = date("m", strtotime(convertDate($Date)));
                        $currentDateYear = date("Y", strtotime(convertDate($Date)));
                        if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                            $data = $data->whereDate('maturity_date', '>', $currentDate)->whereDate('maturity_date', '<=', $endDate);
                        } elseif ($startDateMonth > $currentDateMonth) {
                            $data = $data->whereBetween('maturity_date', [$startDate, $endDate]);
                        } else {
                            $data = $data->where('maturity_date', '');
                        }
                    }
                } elseif ($request['status'] == 1) {
                    if ($request['start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                        $startDateMonth = date("m", strtotime(convertDate($request['start_date'])));
                        $startDateYear = date("Y", strtotime(convertDate($request['start_date'])));
                        if ($request['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $currentDateMonth = date("m", strtotime(convertDate($Date)));
                        $currentDateYear = date("Y", strtotime(convertDate($Date)));
                        if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                            $data->whereHas('demandadvice', function ($query) use ($startDate, $endDate, $currentDate) {
                                $query->where('status', 1);
                            })->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $currentDate])->where('is_mature', 0);
                        } elseif ($startDateMonth < $currentDateMonth) {
                            $data->whereHas('demandadvice', function ($query) use ($startDate, $endDate, $currentDate) {
                                $query->whereBetween(\DB::raw('DATE(demand_advices.date)'), [$startDate, $endDate])->where('status', 1);
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
                } elseif ($request['status'] == 2) {
                    if ($request['start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                        $startDateMonth = date("m", strtotime(convertDate($request['start_date'])));
                        $startDateYear = date("Y", strtotime(convertDate($request['start_date'])));
                        if ($request['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                        $currentDateMonth = date("m", strtotime(convertDate($Date)));
                        $currentDateYear = date("Y", strtotime(convertDate($Date)));
                        if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                            /*$data->whereHas('demandadvice',function($query) use($currentDate,$startDate){
                                $query->orwhere('maturity_date','<',$currentDate)->where('is_mature',1)->where('maturity_date', '>=',$startDate)->orwhere('demand_advices.status','=',0)->where('demand_advices.is_mature',0);
                            })->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $endDate]);
                            $data->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $endDate]);
                            $whereCond = '((maturity_date > "'.$currentDate.'" && is_mature = 1 and maturity_date > "'.$startDate.'") )';
                            $data = $data->whereRaw($whereCond)->orwhere('demand_advices.is_mature',0);*/
                            $data->where('is_mature', 0)->whereBetween('maturity_date', [$startDate, $currentDate])->orWhere(function ($q) use ($startDate, $currentDate) {
                                $q->whereHas('demandadvice', function ($query) use ($startDate, $currentDate) {
                                    $query->where('status', 0)->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $currentDate]);
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
        if ($request['export'] == 0) {
            $totalResults = $data->orderby('created_at', 'DESC')->count();
            $data = $data->orderby('created_at', 'DESC')->offset($start)->limit($limit)->get();
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
                // echo '<pre>'; print_r($row->toArray()); die();
                $val['S/N'] = $sno;
                if ($row['sumdeposite']->sum('deposit')) {
                    $current_balance = $row['sumdeposite']->sum('deposit');
                } else {
                    $current_balance = 0;
                }
                $val['COMPANY'] = $row['company'] ? $row['company']->name : 'N/A';
                $val['BR NAME'] = $row['branch']->name;
                ;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['ZO NAMEW'] = $row['branch']->zone;
                $val['ACCOUNT NO'] = $row->account_number;
                if ($row['member']) {
                    $val['customer_id'] = $row['member']->member_id;
                } else {
                    $val['customer_id'] = 'N/A';
                }
                if ($row['memberCompany']) {
                    $val['member_id'] = $row['memberCompany']->member_id;
                } else {
                    $val['member_id'] = 'N/A';
                }
                if ($row['member']) {
                    $val['MEMBER NAME'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                } else {
                    $val['MEMBER NAME'] = 'N/A';
                }
                $val['PLAN'] = $row['plan']->name;
                if ($row->tenure) {
                    $val['TENURE'] = $row->tenure;
                } else {
                    $val['TENURE'] = "N/A";
                }
                if (isset($current_balance)) {
                    $val['DEPOSITE AMOUNT'] = $current_balance;
                } else {
                    $val['DEPOSITE AMOUNT'] = 'N/A';
                }
                $val['DENO'] = number_format((float) $row->deposite_amount, 2, '.', '');
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_type == 0) {
                        $val['MATURITY TYPE'] = 'Expense';
                    } elseif ($row['demandadvice']->payment_type == 1) {
                        $val['MATURITY TYPE'] = 'Maturity';
                    } elseif ($row['demandadvice']->payment_type == 2) {
                        $val['MATURITY TYPE'] = 'PreMaturity';
                    } elseif ($row['demandadvice']->payment_type == 3) {
                        $val['MATURITY TYPE'] = 'Death Help';
                    } elseif ($row['demandadvice']->payment_type == 4) {
                        $val['MATURITY TYPE'] = 'Emergancy Maturity';
                    } else {
                        $val['MATURITY TYPE'] = 'N/A';
                    }
                } else {
                    $val['MATURITY TYPE'] = 'N/A';
                }
                $val['MATURITY AMOUNT'] = number_format((float) $row->maturity_amount, 2, '.', '');
                if ($row['demandadvice']) {
                    $val['MATURITY PAYABLE AMOUNT'] = $row['demandadvice']->maturity_amount_payable;
                } else {
                    $val['MATURITY PAYABLE AMOUNT'] = 'N/A';
                }
                if ($row->maturity_date) {
                    $val['MATURITY DATE'] = date('d/m/Y', strtotime($row->created_at . ' + ' . ($row->tenure) . ' year'));
                } else {
                    $val['MATURITY DATE'] = "N/A";
                }
                if ($row['associateMember']) {
                    if (isset($row['associateMember']->associate_no)) {
                        $val['ASSOCIATE CODE'] = $row['associateMember']->associate_no;
                    } else {
                        $val['ASSOCIATE CODE'] = 'N/A';
                    }
                } else {
                    $val['ASSOCIATE CODE'] = 'N/A';
                }
                if ($row) {
                    if (isset($row['associateMember'])) {
                        if (isset($row['associateMember']->first_name) && isset($row['associateMember']->last_name)) {
                            $val['ASSOCIATE NAME'] = $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name;
                        } else {
                            if (isset($row['associateMember']->first_name)) {
                                $val['ASSOCIATE NAME'] = $row['associateMember']->first_name;
                            } else {
                                $val['ASSOCIATE NAME'] = '';
                            }
                        }
                    } else {
                        $val['ASSOCIATE NAME'] = '';
                    }
                } else {
                    $val['ASSOCIATE NAME'] = '';
                }
                $val['OPENING DATE'] = date("d/m/Y", strtotime($row->created_at));
                $val['DUE AMOUNT'] = number_format((float) $row->due_amount, 2, '.', '');
                $amount = 0;
                if ($row['demandadvice']) {
                    // $investmentAmount = Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');
                    // Sachin sir ne change karaya 14-03-2022
                    $tds = 0;
                    if (isset($row['demandadvice']->tds_amount)) {
                        $tds = $row['demandadvice']->tds_amount;
                    }
                    $amount = round($row['demandadvice']->final_amount - $row['demandadvice']->maturity_prematurity_amount + $tds);
                }
                $val['INTEREST'] = $amount;
                if (isset($row->tds_deduct_amount)) {
                    $val['TDS AMOUNT'] = number_format((float) $row->tds_deduct_amount, 2, '.', '');
                } else {
                    $val['TDS AMOUNT'] = 'N/A';
                }
                if (isset($row['demandadvice']->final_amount)) {
                    $val['FINAL PAYABLE AMOUNT'] = number_format((float) $row['demandadvice']->final_amount, 2, '.', '');
                    ;
                } else {
                    $val['FINAL PAYABLE AMOUNT'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 0) {
                        $val['PAYMENT MODE'] = "Cash";
                    }
                    if ($row['demandadvice']->payment_mode == 1) {
                        $val['PAYMENT MODE'] = "Cheque";
                    }
                    if ($row['demandadvice']->payment_mode == 2) {
                        $val['PAYMENT MODE'] = "Online Transfer";
                    }
                    if ($row['demandadvice']->payment_mode == 3) {
                        $val['PAYMENT MODE'] = "SSB Transfer";
                    }
                } else {
                    $val['PAYMENT MODE'] = "N/A";
                }
                if ($row->is_mature == 0) {
                    $date = $row['TransactionTypeDate'];
                    // Sachin sir ne change karaya 14-03-2022
                    if (isset($date[0]['created_at'])) {
                        $val['payment_date'] = date('d/m/Y', strtotime($date[0]['created_at']));
                    }
                } else {
                    $val['payment_date'] = "N/A";
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 1) {
                        if (isset($row['demandadvice']['demandAmount'][0])) {
                            $transaction = $row['demandadvice']['demandAmount'][0];
                        }
                        if (isset($transaction->cheque_no)) {
                            $val['CHEQUE NO.'] = $transaction->cheque_no;
                        } else {
                            $val['CHEQUE NO.'] = 'N/A';
                        }
                    } elseif ($row['demandadvice']->payment_mode == 2) {
                        $transaction = $row['demandadvice']['demandTransactionAmount'][0];
                        if ($transaction) {
                            $val['CHEQUE NO.'] = $transaction->transction_no;
                        } else {
                            $val['CHEQUE NO.'] = 'N/A';
                        }
                    } else {
                        $val['CHEQUE NO.'] = 'N/A';
                    }
                } else {
                    $val['CHEQUE NO.'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 2) {
                        $transaction = $row['demandadvice']['demandAmountHead'][0];
                        ;
                        if ($transaction) {
                            $val['RTGS CHARGE'] = number_format((float) $transaction->amount, 2, '.', '');
                        } else {
                            $val['RTGS CHARGE'] = 'N/A';
                        }
                    } else {
                        $val['RTGS CHARGE'] = 'N/A';
                    }
                } else {
                    $val['RTGS CHARGE'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    $ac = SavingAccount::where('member_id', $row['member']->id)->first();
                    if ($row['demandadvice']->payment_mode == 3) {
                        $ac = SavingAccount::where('member_id', $row['member']->id)->first();
                        if ($ac) {
                            $val['SSB ACCOUNT NO.'] = $ac->account_no;
                        } else {
                            $val['SSB ACCOUNT NO.'] = $row['demandadvice']->ssb_account;
                        }
                    } elseif (isset($ac->account_no)) {
                        $val['SSB ACCOUNT NO.'] = $ac->account_no;
                    } else {
                        $val['SSB ACCOUNT NO.'] = 'N/A';
                    }
                } else {
                    $val['SSB ACCOUNT NO.'] = 'N/A';
                }
                //ssb payment
                //Bank Payment
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2) {
                        // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                        if (isset($row['demandadvice']->bank_name)) {
                            $val['BANK NAME'] = $row['demandadvice']->bank_name;
                        } else {
                            $val['BANK NAME'] = 'N/A';
                        }
                    } else {
                        $val['BANK NAME'] = 'N/A';
                    }
                } else {
                    $val['BANK NAME'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2) {
                        if (isset($row['demandadvice']->bank_account_number)) {
                            $val['BANK ACCOUNT NUMBER'] = $row['demandadvice']->bank_account_number;
                        } else {
                            $val['BANK ACCOUNT NUMBER'] = 'N/A';
                        }
                    } else {
                        $val['BANK ACCOUNT NUMBER'] = 'N/A';
                    }
                } else {
                    $val['BANK ACCOUNT NUMBER'] = 'N/A';
                }
                if (!$headerDisplayed) {
                    // Use the keys from $data as the titles
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                // Put the data into the stream
                fputcsv($handle, $val);
            }
            fclose($handle);
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
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
        } elseif ($request['export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.report.export_maturity_report', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('maturityReport.pdf');
        }
    }

    public function voucherExport(Request $request)
    {
        $input = $request->all();
        $getBranchId = getUserBranchId(Auth::user()->id);
        $BranchId = $getBranchId->id;
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/voucher_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/voucher_list.csv";
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
        $data = \App\Models\ReceivedVoucher::has('company')->with([
            'rv_branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->with([
                    'rv_employee' => function ($query) {
                        $query->select('id', 'employee_name', 'employee_code');
                    }
                ])->with([
                    'rvCheque' => function ($query) {
                        $query->select('id', 'cheque_no', 'deposit_bank_id', 'deposit_account_id', 'cheque_deposit_date', 'account_holder_name');
                    }
                ])
            ->with('rv_member:id,first_name,last_name')
            ->with('company:id,name,short_name')
            ->where('is_deleted', 0);
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['payment_type'] != '') {
            $payment_type = $request['payment_type'];
            $data = $data->where('received_mode', '=', $payment_type);
        }
        if ($request['account_head'] != '') {
            $account_head = $request['account_head'];
            $data = $data->where('account_head_id', '=', $account_head);
        }
        if ($request['branch_id'] > 0) {
            $branch = $request['branch_id'];
            $data = $data->where('branch_id', $branch);
        }
        if ($request['company_id'] > 0) {
            $company_id = $request['company_id'];
            $data = $data->where('company_id', $company_id);
        }
        if ($request['export'] == 0) {
            $totalResults = $data->orderby('created_at', 'DESC')->count();
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
                $val['COMPANY'] = isset($row['company']->short_name) ? $row['company']->short_name : 'N/A';
                $val['BR NAME'] = $row['rv_branch']->name;
                $val['BR CODE'] = $row['rv_branch']->branch_code;
                $val['SO NAME'] = $row['rv_branch']->sector;
                $val['RO NAME'] = $row['rv_branch']->regan;
                $val['ZO NAME'] = $row['rv_branch']->zone;
                $val['DATE'] = date("d/m/Y", strtotime($row->date));
                $received_mode = '';
                if ($row->received_mode == 0) {
                    $received_mode = "Cash";
                }
                if ($row->received_mode == 1) {
                    $received_mode = "Cheque";
                }
                if ($row->received_mode == 2) {
                    $received_mode = "Online";
                }
                $val['RECEIVED MODE'] = $received_mode;
                $val['RECEIVED AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $val['ACCOUNT HEAD'] = getAcountHeadNameHeadId($row->account_head_id);
                $val['ACCOUNT SUB HEAD'] = getAcountHeadNameHeadId($row->expense_head) ?? 'N/A';
                $director = '';
                if ($row->type == 1) {
                    $director = getAcountHeadNameHeadId($row->director_id);
                } else {
                    $director = 'N/A';
                }
                $val['DIRECTOR'] = $director;
                $shareholder = '';
                if ($row->type == 2) {
                    $shareholder = getAcountHeadNameHeadId($row->shareholder_id);
                } else {
                    $shareholder = "N/A";
                }
                $val['SHARE HOLDER'] = $shareholder;
                $employee_code = '';
                if ($row['rv_employee'] && $row->employee_id != null) {
                    $employee_code = $row['rv_employee']->employee_code;
                } elseif ($row['rv_member'] && $row->member_id != null) {
                    $employee_code = \App\Models\MemberCompany::select('member_id')->where('customer_id', $row['rv_member']->id)->first()->member_id;
                } elseif ($row->associate_id != null) {
                    $employee_code = getMemberCustom($row->associate_id)->associate_no;
                } else {
                    $employee_code = "N/A";
                }
                $val['EMP CODE / MEMBER ID / ASSOCIATE ID'] = $employee_code;
                $employee_name = '';
                if ($row['rv_employee'] && $row->employee_id != null) {
                    $employee_name = $row['rv_employee']->employee_name;
                } elseif ($row['rv_member'] && $row->member_id != null) {
                    $employee_name = $row['rv_member']->first_name . " " . $row['rv_member']->last_name;
                } elseif ($row->associate_id != null) {
                    $employee_name = getMemberCustom($row->associate_id)->first_name . " " . getMemberCustom($row->associate_id)->last_name;
                } else {
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
                $val['BANK ACCOUNT NUMBER'] = $bank_account_number;
                $eli_loan = '';
                if ($row->eli_loan_id) {
                    $eli_loan = getAcountHeadNameHeadId($row->eli_loan_id);
                } else {
                    $eli_loan = "N/A";
                }
                $val['ELI LOAN'] = $eli_loan;
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
                $val['CHEQUE NUMBER'] = $cheque_no;
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
                $val['UTR TRANSACTION NO'] = $utr_transaction_number;
                $transaction_date = '';
                if ($row->received_mode == 0) {
                    $transaction_date = "N/A";
                } else {
                    $transaction_date = date("d/m/Y", strtotime($row->online_tran_date));
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
                $val['PARTY BANK ACCOUNT'] = $party_bank_account;
                $received_bank = '';
                if ($row->received_mode == 0) {
                    $received_bank = "N/A";
                } else {
                    $received_bank = getSamraddhBank($row->receive_bank_id)->bank_name;
                }
                $val['RECEIVED BANK'] = $received_bank;
                $received_bank_account = '';
                if ($row->received_mode == 0) {
                    $received_bank_account = "N/A";
                } else {
                    $received_bank_account = getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no;
                }
                $val['RECEIVED BANK ACCOUNT'] = $received_bank_account;
                $bank_slip = '';
                if ($row->slip) {
                    $a = URL::to("/asset/voucher/" . $row->slip . "");
                    $bank_slip = $row->slip;
                } else {
                    $bank_slip = 'N/A';
                }
                $val['transaction slip'] = $bank_slip;
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
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        }
    }
    public function exportAccoutHeadReport(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/account_head_ledger.csv";
            $fileName = env('APP_EXPORTURL') . "asset/account_head_ledger.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $id = $request->head;
        $label = $request->label;
        $info = 'head' . $label;
        $data = \App\Models\AllHeadTransaction::with([
            'branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->where('head_id', $id);
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                if (isset($row['branch']->name)) {
                    $val['BR NAME'] = $row['branch']->name;
                } else {
                    $val['BR NAME'] = 'N/A';
                }
                if (isset($row['branch']->branch_code)) {
                    $val['BR CODE'] = $row['branch']->branch_code;
                } else {
                    $val['BR CODE'] = 'N/A';
                }
                // if (isset($row['sector']->sector)) {
                //     $val['SO NAME'] = $row['branch']->sector;
                // } else {
                //     $val['SO NAME'] = 'N/A';
                // }
                // if (isset($row['regan']->sector)) {
                //     $val['RO NAME'] = $row['branch']->regan;
                // } else {
                //     $val['RO NAME'] = 'N/A';
                // }
                // if (isset($row['zone']->sector)) {
                //     $val['ZO NAME'] = $row['branch']->zone;
                // } else {
                //     $val['ZO NAME'] = 'N/A';
                // }
                $type = '';
                $getTransType = TransactionType::where('type', $row->type)->where('sub_type', $row->sub_type)->first();
                $type = '';
                if (isset($getTransType->type)) {
                    if ($row->type == $getTransType->type) {
                        if ($row->sub_type == $getTransType->sub_type) {
                            $type = $getTransType->title;
                        }
                    }
                }
                if ($row->type == 21) {
                    $record = \App\Models\ReceivedVoucher::where('id', $row->type_id)->first();
                    if ($record) {
                        $type = $record->particular;
                    } else {
                        $type = "N/A";
                    }
                }
                $val['TYPE'] = $type;
                $val['DESCRIPTION'] = $row->description;
                $val['AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $account_number = 'N/A';
                if ($row->type == 2 || $row->type == 3) {
                    $account_number = getInvestmentDetails($row->type_id);
                    if (isset($account_number->account_number)) {
                        $account_number = $account_number->account_number;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 4) {
                    $account_number = getSavingAccountMemberId($row->type_id);
                    if (isset($account_number->account_no)) {
                        $account_number = $account_number->account_no;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 5) {
                    $account_number = getLoanDetail($row->type_id);
                    if (isset($account_number->account_number)) {
                        $account_number = $account_number->account_number;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 13) {
                    $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                    if (isset($v_no->account_number)) {
                        $account_number = $v_no->account_number;
                    } else {
                        $account_number = "N/A";
                    }
                } else {
                    $account_number = "N/A";
                }
                $val['ACCOUNT NO'] = $account_number;
                if ($row->member_id) {
                    $member_name = getMemberData($row->member_id)->first_name . ' ' . getMemberData($row->member_id)->last_name;
                } else {
                    $member_name = 'N/A';
                }
                $val['MEMBER NAME'] = $member_name;
                $payment_type = 'N/A';
                if ($row->payment_type == 'DR') {
                    $payment_type = 'Debit';
                }
                if ($row->payment_type == 'CR') {
                    $payment_type = 'Credit';
                }
                $val['PAYMENT TYPE'] = $payment_type;
                $payment_mode = 'N/A';
                if ($row->payment_mode == 0) {
                    $payment_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $payment_mode = 'Cheque';
                }
                if ($row->payment_mode == 2) {
                    $payment_mode = 'Online Transfer';
                }
                if ($row->payment_mode == 3) {
                    $payment_mode = 'SSB Transfer Through JV';
                }
                if ($row->payment_mode == 4) {
                    if ($row->payment_type == 'CR') {
                        $payment_mode = "Auto Credit";
                    } else {
                        $payment_mode = "Auto Debit";
                    }
                }
                if ($row->payment_mode == 6) {
                    $payment_mode = "JV";
                }
                $val['PAYMENT MODE'] = $payment_mode;
                if ($row->v_no) {
                    $v_no = $row->v_no;
                    $voucher_no = $v_no;
                }
                if ($row->type == 13) {
                    $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                    $voucher_no = $v_no->voucher_number;
                } else {
                    $voucher_no = "N/A";
                }
                $val['VOUCHER NO'] = $voucher_no;
                if ($row->v_date) {
                    $voucher_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                    ;
                } else {
                    $voucher_date = "N/A";
                }
                $val['VOUCHER DATE'] = $voucher_date;
                if ($row->cheque_no) {
                    $cheque_no = $row->cheque_no;
                } else {
                    $cheque_no = "N/A";
                }
                $val['CHEQUE NO'] = $cheque_no;
                if ($row->cheque_date) {
                    $cheque_date = date("d/m/Y", strtotime(convertDate($row->cheque_date)));
                } else {
                    $cheque_date = "N/A";
                }
                $val['CHEQUE DATE'] = $cheque_date;
                if ($row->transction_no) {
                    $transction_no = $row->transction_no;
                } else {
                    $transction_no = "N/A";
                }
                $val['TRANSCATION NO'] = $transction_no;
                $val['TRANSCATION DATE'] = date("d/m/Y", strtotime(convertDate($row->transction_date)));
                if ($row->transction_bank_to) {
                    $transction_bank_to_name = getSamraddhBank($row->transction_bank_to);
                    $transction_bank_to_name = $transction_bank_to_name->bank_name;
                } else {
                    $transction_bank_to_name = "N/A";
                }
                $val['RECEIVED BANK'] = $transction_bank_to_name;
                if ($row->transction_bank_to) {
                    $transction_bank_to_ac_no = getSamraddhBankAccountId($row->transction_bank_to);
                    $transction_bank_to_ac_no = $transction_bank_to_ac_no->account_no;
                } else {
                    $transction_bank_to_ac_no = "N/A";
                }
                $val['RECEIVED BANK ACCOUNT'] = $transction_bank_to_ac_no;
                if ($row->entry_date) {
                    $date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
                } else {
                    $date = "N/A";
                }
                $val['CREATED DATE'] = $date;
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
        } elseif ($request['export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.account_head_report.export_account_head_ledger', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('accountHeadLedger.pdf');
        }
    }
    public function exportAccoutHeadReporttranscation(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/account_head_ledger_transcation.csv";
            $fileName = env('APP_EXPORTURL') . "asset/account_head_ledger_transcation.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $id = $request->head;
        $label = $request->label;
        $info = 'head' . $label;
        $data = \App\Models\AllHeadTransaction::with([
            'branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }
        ])->where('head_id', $id);
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                if (isset($row['branch']->name)) {
                    $val['BR NAME'] = $row['branch']->name;
                } else {
                    $val['BR NAME'] = 'N/A';
                }
                if (isset($row['branch']->branch_code)) {
                    $val['BR CODE'] = $row['branch']->branch_code;
                } else {
                    $val['BR CODE'] = 'N/A';
                }
                if ($row['branch']) {
                    $sector = $row['branch']->sector;
                }
                $val['SO NAME'] = $sector;
                if ($row['branch']) {
                    $regan = $row['branch']->regan;
                }
                $val['RO NAME'] = $regan;
                if ($row['branch']) {
                    $val['ZO NAME'] = $row['branch']->zone;
                } else {
                    $val['ZO NAME'] = 'N/A';
                }
                $type = '';
                $getTransType = TransactionType::where('type', $row->type)->where('sub_type', $row->sub_type)->first();
                $type = '';
                if (isset($getTransType->type)) {
                    if ($row->type == $getTransType->type) {
                        if ($row->sub_type == $getTransType->sub_type) {
                            $type = $getTransType->title;
                        }
                    }
                }
                if ($row->type == 21) {
                    $record = \App\Models\ReceivedVoucher::where('id', $row->type_id)->first();
                    if ($record) {
                        $type = $record->particular;
                    } else {
                        $type = "N/A";
                    }
                }
                $val['TYPE'] = $type;
                $val['DESCRIPTION'] = $row->description;
                $val['AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $account_number = 'N/A';
                if ($row->type == 2 || $row->type == 3) {
                    $account_number = getInvestmentDetails($row->type_id);
                    if (isset($account_number->account_number)) {
                        $account_number = $account_number->account_number;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 4) {
                    $account_number = getSavingAccountMemberId($row->type_id);
                    if (isset($account_number->account_no)) {
                        $account_number = $account_number->account_no;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 5) {
                    $account_number = getLoanDetail($row->type_id);
                    if (isset($account_number->account_number)) {
                        $account_number = $account_number->account_number;
                    } else {
                        $account_number = 'N/A';
                    }
                }
                if ($row->type == 13) {
                    $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                    if (isset($v_no->account_number)) {
                        $account_number = $v_no->account_number;
                    } else {
                        $account_number = "N/A";
                    }
                } else {
                    $account_number = "N/A";
                }
                $val['ACCOUNT NO'] = $account_number;
                if ($row->member_id) {
                    $member_name = getMemberData($row->member_id)->first_name . ' ' . getMemberData($row->member_id)->last_name;
                } else {
                    $member_name = 'N/A';
                }
                $val['MEMBER NAME'] = $member_name;
                $payment_type = 'N/A';
                if ($row->payment_type == 'DR') {
                    $payment_type = 'Debit';
                }
                if ($row->payment_type == 'CR') {
                    $payment_type = 'Credit';
                }
                $val['PAYMENT TYPE'] = $payment_type;
                $payment_mode = 'N/A';
                if ($row->payment_mode == 0) {
                    $payment_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $payment_mode = 'Cheque';
                }
                if ($row->payment_mode == 2) {
                    $payment_mode = 'Online Transfer';
                }
                if ($row->payment_mode == 3) {
                    $payment_mode = 'SSB Transfer Through JV';
                }
                if ($row->payment_mode == 4) {
                    if ($row->payment_type == 'CR') {
                        $payment_mode = "Auto Credit";
                    } else {
                        $payment_mode = "Auto Debit";
                    }
                }
                if ($row->payment_mode == 6) {
                    $payment_mode = "JV";
                }
                $val['PAYMENT MODE'] = $payment_mode;
                if ($row->v_no) {
                    $v_no = $row->v_no;
                    $voucher_no = $v_no;
                }
                if ($row->type == 13) {
                    $v_no = \App\Models\DemandAdvice::where('id', $row->type_id)->first();
                    $voucher_no = $v_no->voucher_number;
                } else {
                    $voucher_no = "N/A";
                }
                $val['VOUCHER NO'] = $voucher_no;
                if ($row->cheque_no) {
                    $cheque_no = $row->cheque_no;
                } else {
                    $cheque_no = "N/A";
                }
                $val['CHEQUE NO'] = $cheque_no;
                if ($row->cheque_date) {
                    $cheque_date = date("d/m/Y", strtotime(convertDate($row->cheque_date)));
                } else {
                    $cheque_date = "N/A";
                }
                $val['CHEQUE DATE'] = $cheque_date;
                if ($row->transction_no) {
                    $transction_no = $row->transction_no;
                } else {
                    $transction_no = "N/A";
                }
                $val['TRANSCATION NO'] = $transction_no;
                if ($row->transction_bank_to) {
                    $transction_bank_to_name = getSamraddhBank($row->transction_bank_to);
                    $transction_bank_to_name = $transction_bank_to_name->bank_name;
                } else {
                    $transction_bank_to_name = "N/A";
                }
                $val['RECEIVED BANK'] = $transction_bank_to_name;
                if ($row->transction_bank_to) {
                    $transction_bank_to_ac_no = getSamraddhBankAccountId($row->transction_bank_to);
                    $transction_bank_to_ac_no = $transction_bank_to_ac_no->account_no;
                } else {
                    $transction_bank_to_ac_no = "N/A";
                }
                $val['RECEIVED BANK ACCOUNT'] = $transction_bank_to_ac_no;
                if ($row->entry_date) {
                    $date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
                } else {
                    $date = "N/A";
                }
                $val['CREATED DATE'] = $date;
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
        } elseif ($request['export'] == 1) {
            $data = $data->orderby('created_at', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.account_head_report.export_account_head_ledger', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('accountHeadLedger.pdf');
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
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
        } else {
            $branch_id = '';
        }
        // $cash_in_hand['CR'] = BranchDaybook::where('description_dr','not like','%Eli Amount%')->where('payment_mode',0)->where('branch_id',$branch_id)->where('payment_type','CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted',0)->sum('amount');
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
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.id', 'branch_daybook.id as btid')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.branch_id', $branch_id)->whereBetween('branch_daybook.entry_date', [$startDate, $endDate])->orderBy('branch_daybook.entry_date', 'ASC')->where('branch_daybook.is_deleted', 0)->get();
        $rowReturn = array();
        foreach ($data as $key => $value) {
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
            $data = $this->getCompleteDetail($value, 1);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            if ($value->payment_type == 'CR') {
                $cr_amount = number_format((float) $value->amount, 2, '.', '');
            } else {
                $cr_amount = 0;
            }
            if ($value->payment_type == 'DR') {
                $dr_amnt = number_format((float) $value->amount, 2, '.', '');
            } else {
                $dr_amnt = 0;
            }
            // Balance
            if ($value->branch_payment_mode == 0 && $value->sub_type != 30) {
                $balance = number_format((float) $balance, 2, '.', '');
            }
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
            if (strstr($value->description_dr, 'Eli Amount')) {
                $pay_mode = 'ELI';
            } else {
                if ($value->branch_payment_mode == 0) {
                    $pay_mode = 'CASH';
                } else if ($value->branch_payment_mode == 5) {
                    $pay_mode = 'LOAN';
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
                } elseif ($value->branch_payment_mode == 8) {
                    $pay_mode = 'SSB Debit Cron';
                } else {
                    $pay_mode = '';
                }
            }
            $val['tr_date'] = date('d/m/Y', strtotime(convertDate($value->entry_date)));
            $val['bt_id'] = $value->btid;
            $val['tran_by'] = (($value->is_app) ? ($value->is_app == 1 ? 'Associate' : 'E-passbook') : 'Software');
            $val['member_account'] = $memberAccount;
            $val['plan_name'] = $plan_name;
            $val['memberName'] = $memberName;
            $val['a_name'] = $data['associate_name'];
            $val['type'] = $type;
            $val['description_cr'] = $value->description_cr;
            $val['description_dr'] = $value->description_dr;
            $val['cr_amnt'] = $cr_amount;
            $val['dr_amnt'] = $dr_amnt;
            $val['balance'] = $balance;
            $val['ref_no'] = $ref_no;
            $val['pay_mode'] = $pay_mode;
            $val['tag'] = $data['tag'];
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new DaybookReportExport($rowReturn, $cash_in_hand, $cheque, $bank, $branch_id, $startDate, $endDate), 'DaybookReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.report.export_daybook_report', compact('rowReturn', 'cash_in_hand', 'cheque', 'bank', 'branch_id', 'startDate', 'endDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('DaybookReport.pdf');
        }
    }
    // Daily Business Report Export Code Start
    public function branchBusinessReportExport(Request $request)
    {
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
        } else {
            $startDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
        }
        if ($request['end_date'] != '') {
            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
        } else {
            $endDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
        }
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
        } else {
            $branch_id = '';
        }
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
        $totalAmounts = (float) $totalLoan + (float) $totalMicro;
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
        $total_received_cheque_amount = (float) $receivedChequeLoanTotal + (float) $receivedChequeMicoTotal;
        // ...........................End RECEIVED CHEQUES Detail...................//
        if ($request['export'] == 0) {
            return Excel::download(new BranchBusinessReportExport($account_head, $branch_id, $startDate, $endDate, $loans, $micros, $totalAmounts, $receivedChequeMicro, $receivedChequeLoan, $total_received_cheque_amount), 'DayBusinessReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.report.export_branch_business_report', compact('account_head', 'startDate', 'endDate', 'branch_id', 'loans', 'micros', 'totalAmounts', 'receivedChequeMicro', 'receivedChequeLoan', 'total_received_cheque_amount'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('DayBusinessReport.pdf');
        }
    }
    public function assetExportList(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/asset_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/asset_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = \App\Models\DemandAdviceExpense::has('company')->with([
            'AssestFilesCustom',
            'AcountHeadNameHeadIdCustom',
            'company',
            'advices' => function ($q) {
                $q->with(['branch']);
            }
        ])->where('is_assets', 0);
        // ->where('company_id', $request->company_id);
        //$data=\App\Models\DemandAdviceExpense::with(['advices'])->where('is_assets',0);
        $data = $data->whereHas('advices', function ($query) {
            $query->where('demand_advices.status', 1);
        });
        $data = $data->whereHas('advices', function ($query) {
            $query->where('demand_advices.payment_type', 0);
        });
        $data = $data->whereHas('advices', function ($query) {
            $query->where('demand_advices.sub_payment_type', 0);
        });
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            if ($company_id != '0') {
                $data = $data->where('company_id', $company_id);
            }
        }
        if ($request['branch_id'] != '') {
            $branchId = $request['branch_id'];
            if ($branchId != '0') {
                $data = $data->whereHas('advices', function ($query) use ($branchId) {
                    $query->where('demand_advices.branch_id', $branchId);
                });
            }
        }
        if ($request['category'] != '') {
            $category = $request['category'];
            $data = $data->where('assets_category', $category);
        }
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = $data->where('status', $status);
        }
        if ($request['export'] == 0) {
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
                $val['COMPANY NAME'] = $row['company']->name;
                $val['BRANCH NAME'] = $row['advices']['branch']->name . '-' . $row['advices']['branch']->branch_code;
                $val['SECTOR'] = $row['advices']['branch']->sector;
                $val['REGION'] = $row['advices']['branch']->regan;
                $val['ZONE'] = $row['advices']['branch']->zone;
                //getBranchDetail($row['advices']->branch_id)->name .'-'. getBranchDetail($row['advices']->branch_id)->branch_code;
                if ($row['AcountHeadNameHeadIdCustom']) {
                    $val['ACCOUNT HEAD'] = $row['AcountHeadNameHeadIdCustom']->sub_head; //getAcountHeadNameHeadId($row->assets_category);
                } else {
                    $val['ACCOUNT HEAD'] = 'N/A';
                }
                $val['ACCOUNT HEAD'] = getAcountHeadNameHeadId($row->assets_category);
                $val['SUB ACCOUNT HEAD NAME'] = getAcountHeadNameHeadId($row->assets_subcategory);
                $val['DEMAND DATE'] = date("d/m/Y", strtotime($row['advices']->date));
                $val['ADVICE DATE'] = date("d/m/Y", strtotime($row->purchase_date));
                $val['AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $val['PARTY NAME'] = $row->party_name;
                $val['MOBILE NO'] = $row->mobile_number;
                $val['BILL NO'] = $row->bill_number;
                $bill_file_id = '';
                if ($row->billId) {
                    // $res = $row['AssestFilesCustom']; //Files::where('id',$row->bill_file_id)->first();
                    $res = VendorBill::where('id', $row->billId)->first();
                    $url = URL('core/storage/images/demand-advice/expense/' . $res->file_name . '');
                    $bill_file_id = $res->bill_upload;
                } else {
                    $bill_file_id = 'N/A';
                }
                $val['BILL COPY'] = $bill_file_id;
                $status = '';
                if ($row->status == 0) {
                    $status = 'Working';
                } else {
                    $status = 'Damaged';
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

    /**
     * updated on 09-oct 2023
     * by shahid
     */
    public function depreciationExportList(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/depreciation_list.csv";
        $fileName = env('APP_EXPORTURL') . "asset/depreciation_list.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $data = \App\Models\DemandAdviceExpense::select(
            'id',
            'current_balance',
            'assets_subcategory',
            'assets_category',
            'purchase_date',
            'amount',
            'party_name',
            'mobile_number',
            'bill_number',
            'bill_file_id',
            'created_at',
            'status',
            'demand_advice_id',
            'company_id',
            'billId',
            'depreciation_per'
        )->has('company')
            ->with([
                'AssestFilesCustom' => function ($q) {
                    $q->select('id', 'file_name');
                },
                'AcountHeadNameHeadIdCustom' => function ($q) {
                    $q->select('id', 'head_id', 'sub_head');
                },
                'AcountHeadNameHeadIdCustom2' => function ($q) {
                    $q->select('id', 'head_id', 'sub_head');
                },
                'advices' => function ($q) {
                    $q->select('id', 'status', 'payment_type', 'sub_payment_type', 'date', 'branch_id', 'company_id')
                        ->with([
                            'branch' => function ($q) {
                                $q->select('id', 'name', 'branch_code');
                            }
                        ]);
                }
            ])->where('is_assets', 0);
        if (Auth::user()->branch_id > 0) {
            $branchId = Auth::user()->branch_id;
            $data = $data->whereHas('advices', function ($query) use ($branchId) {
                $query->where('demand_advices.branch_id', $branchId);
            });
        }
        $data = $data->whereHas('advices', function ($query) {
            $query->where('demand_advices.status', 1);
        });
        $data = $data->whereHas('advices', function ($query) {
            $query->where('demand_advices.payment_type', 0);
        });
        $data = $data->whereHas('advices', function ($query) {
            $query->where('demand_advices.sub_payment_type', 0);
        });
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            if ($company_id != '0') {
                $data = $data->where('company_id', $company_id);
            }
        }
        if ($request['branch_id'] != '') {
            $branchId = $request['branch_id'];
            if ($branchId != '0') {
                $data = $data->whereHas('advices', function ($query) use ($branchId) {
                    $query->where('demand_advices.branch_id', $branchId);
                });
            }
        }
        if ($request['category'] != '') {
            $category = $request['category'];
            $data = $data->where('assets_category', $category);
        }
        if ($request['export_de'] == 0) {
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
                $val['Sr_no'] = $sno;
                $val['BRANCH NAME'] = $row['advices']['branch']->name . '-' . $row['advices']['branch']->branch_code; //getBranchDetail($row['advices']->branch_id)->name .'-'. getBranchDetail($row['advices']->branch_id)->branch_code;
                $assets_category = '';
                $assets_category = getAcountHeadNameHeadId($row->assets_category);
                if (isset($assets_category)) {
                    $assets_category = $assets_category;
                } else {
                    $assets_category = 'N/A';
                }
                $val['ACCOUNT HEAD'] = $assets_category;
                $assets_subcategory = '';
                $assets_subcategory = getAcountHeadNameHeadId($row->assets_subcategory);
                if (isset($assets_subcategory)) {
                    $assets_subcategory = $assets_subcategory;
                } else {
                    $assets_subcategory = 'N/A';
                }
                $val['SUB ACCOUNT HEAD NAME'] = $assets_subcategory;
                $val['ASSET PURCHASE DATE'] = date("d/m/Y", strtotime($row->purchase_date));
                $val['PARTY NAME'] = $row->party_name;
                $val['MOBILE NO'] = $row->mobile_number;
                $val['TOTAL VALUE OF ASSET'] = number_format((float) $row->amount, 2, '.', '');
                $val['CURRENT ASSET VALUE'] = number_format((float) $row->current_balance, 2, '.', '');
                $depreciation_per = 'N/A';
                if ($row->depreciation_per) {
                    $depreciation_per = number_format((float) $row->depreciation_per, 2, '.', '');
                }
                $val['DEPRECIATION%'] = $depreciation_per;
                $bill_file_id = '';
                if ($row->billId) {
                    // $res = Files::where('id', $row->bill_file_id)->first();
                    $res = VendorBill::where('id', $row->billId)->first();
                    $url = URL('core/storage/images/demand-advice/expense/' . $res->file_name . '');
                    $bill_file_id = $res->bill_upload;
                } else {
                    $bill_file_id = 'N/A';
                }
                $val['BILL COPY'] = $bill_file_id;
                number_format((float) $row->amount, 2, '.', '');
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
    public function adminBusinessListExport(Request $request)
    {
        $token = session()->get('_token');
        $_fileName = Session::get('_fileName');
        // $data  = Cache::get('mother_report_a' . $token);
        // $count = Cache::get('mother_report_count_a' . $token);
        $input = $request->all();
        $companyId = $request['company_id'];
        $company = Companies::withoutGlobalScopes()->pluck('name', 'id');
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/branch_business" . $_fileName . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/branch_business" . $_fileName . ".csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $branch_id = $request->branch_id;
        $startDate = DateTime::createFromFormat("d/m/Y", $request->start_date);
        $startDate = $startDate->format("Y-m-d");
        $endDate = DateTime::createFromFormat("d/m/Y", $request->end_date);
        $endDate = $endDate->format("Y-m-d");
        $data = DB::select('call BranchBusinessReports(?,?,?,?,?,?)', [$startDate, $endDate, $companyId, $branch_id, $page_number = 1, 200]);
        $totalResults = count($data);
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
        $counter = 0;
        $offset = 0;
        foreach ($record as $row) {
            $sno++;
            // $branch_id =  $_POST['branchid'];
            $val['S/N'] = $sno;
            $val['Company Name'] = $company[$companyId] ?? "All Company";
            $val['BR Name'] = $row->name;
            $val['BR Code'] = $row->branch_code;
            $val['Region'] = $row->regan;
            $val['Sector'] = $row->sector;
            $val['Zone'] = $row->zone;
            $val['Daily NCC - No. A/C'] = $row->dnccac;
            $val['Daily NCC - Amt'] = $row->dnccamt;
            $val['Daily Renew - No. A/C'] = $row->drenac;
            $val['Daily Renew - Amt'] = $row->drenamt;
            $val['Monthly NCC - No. A/C'] = $row->mnccac;
            $val['Monthly NCC - Amt'] = $row->mnccamt;
            $val['Monthly Renew- No. A/C'] = $row->mrenac;
            $val['Monthly Renew- Amt'] = $row->mrenamt;
            $val['FD NCC - No. A/C'] = $row->fnccac;
            $val['FD NCC - Amt'] = $row->fnccamt;
            $val['SSB NCC - No. A/C'] = $row->snccac;
            $val['SSB NCC - Amt'] = $row->sncc;
            $val['SSB Renew- No. A/C'] = $row->ssbren_ac;
            $val['SSB Renew- Amt'] = number_format((float) $row->ssbren, 2, '.', '');
            $val['Other MI'] = $row->MI;
            $val['Other STN'] = $row->STN;
            $val['New MI Joining - No. A/C'] = $row->member_acn;
            $val['New Associate Joining - No. A/C'] = $row->asso_ac;
            $val['Banking - No. A/C'] = $row->sumbanking_ac;
            $val['Banking - Amt'] = number_format((float) $row->sumbankingamt, 2, '.', '');
            $val['Total Payment - Withdrawal'] = number_format((float) $row->ssbw, 2, '.', '');
            $val['Total Payment - Payment'] = number_format((float) $row->MaturityPayment, 2, '.', '');
            $val['NCC'] = $row->ncc;
            $val['NCC SSB'] = $row->ncc_ssb;
            $val['TCC'] = $row->tcc;
            $val['TCC SSB'] = $row->tcc_ssb;
            $val['Loan - No. A/C'] = $row->loan_ac_no;
            $val['Loan - Amt'] = number_format((float) $row->loan_amt, 2, '.', '');
            $val['Loan Recovery - No. A/C'] = $row->loan_recv_ac_no;
            $val['Loan Recovery - Amt'] = number_format((float) $row->loan_recv_amt, 2, '.', '');
            $val['Loan Against Investment - No. A/C'] = $row->loan_aginst_ac_no;
            $val['Loan Against Investment - Amt'] = $row->loan_aginst_amt;
            $val['Loan Against Investment Recovery - No. A/C'] = $row->loan_aginst_recv_ac_no;
            $val['Loan Against Investment Recovery - Amt'] = number_format((float) $row->loan_aginst_recv_amt, 2, '.', '');
            $val['Cash in hand'] = $row->cash_in_hand;
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
    public function getBalance($startDate, $request, $endDate)
    {
        $opningbankBalance = \App\Models\BankBalance::whereDate('entry_date', '<', $startDate)
            ->whereCompanyId($request['company_id'])
            ->whereBankId($request['bank_name'])
            ->whereAccountId($request['bank_account'])
            ->orderByDESC('entry_date')
            ->sum('totalAmount');
        return $opningbankBalance;
    }
    public function bank_ledger_export(Request $request)
    {
        $bank_id = $request['bank_name'];
        $data = SamraddhBankDaybook::with(['memberInvestment.memberCompany.member', 'companyName:id,name', 'Branch:id,name,branch_code,sector,regan,zone', 'memberCompany:id,customer_id,member_id', 'memberCompany.member:id,first_name,last_name'])
            ->where('is_deleted', 0);
        if ($request['bank_name'] != '' && $request['bank_account'] != '') {
            $bank_id = $request['bank_name'];
            $bankAccount_id = $request['bank_account'];
            $data = $data->where('bank_id', $bank_id)->whereAccountId($bankAccount_id);
        }
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
            $data = $data->where('company_id', $company_id);
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            $created_at = date("Y-m-d", strtotime(convertDate($request['created_at'])));
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('entry_date'), ["" . $startDate . "", "" . $endDate . ""]);
        }
        $demobalance = $this->getbalance($startDate, $request, $endDate);
        $balance = number_format((float) $demobalance, 2, '.', '');
        $closing = "0.00";
        // $existRecord = DB::table('samraddh_bank_closing')->where('bank_id', $bank_id)->exists();
        // if ($existRecord) {
        //     if ($request['start_date'] != '') {
        //         $startDate = date('Y-m-d', strtotime($startDate . "-1 days"));
        //         $SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('entry_date', $startDate)->where('bank_id', $bank_id)->orderBy('id', 'DESC')
        //             ->select('*')->get();
        //         if (count($SamraddhBankOpeningData) > 0) {
        //             $balance = number_format((float)$SamraddhBankOpeningData[0]->closing_balance, 2, '.', '');;
        //         } else {
        //             $SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('entry_date', '<', $startDate)->where('bank_id', $bank_id)->orderBy('entry_date', 'DESC')->get();
        //             if (count($SamraddhBankOpeningData) > 0) {
        //                 $balance = number_format((float)$SamraddhBankOpeningData[0]->closing_balance, 2, '.', '');;;
        //             } else {
        //                 $balance = "0";
        //             }
        //         }
        //     } else {
        //         $SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('bank_id', $bank_id)->orderBy('entry_date', 'ASC')
        //             ->select('*')->get();
        //         $balance = number_format((float)$SamraddhBankOpeningData[0]->opening_balance, 2, '.', '');
        //     }
        // } else {
        //     $balance = 0;
        // }
        // if ($request['end_date'] != '') {
        //     $SamraddhBankClosingData = DB::table('samraddh_bank_closing')->where('entry_date', '<=', $endDate)->where('bank_id', $bank_id)->orderBy('entry_date', 'DESC')->select('*')->first();
        //     if ($SamraddhBankClosingData) {
        //         $closing = number_format((float)$SamraddhBankClosingData->balance, 2, '.', '');
        //     } else {
        //         $closing = "0.00";
        //     }
        // } else {
        //     $SamraddhBankClosingData = DB::table('samraddh_bank_closing')->where('bank_id', $bank_id)->orderBy('entry_date', 'DESC')
        //         ->select('*')->first();
        //     if ($SamraddhBankClosingData) {
        //         $closing = number_format((float)$SamraddhBankClosingData->balance, 2, '.', '');
        //     } else {
        //         $closing = "0.00";
        //     }
        // }
        // $whereCond = '((type = "3" && bank_id = "'.$bank_id.'") ||(type = "4" && amount_from_id = "'.$bank_id.'") ||(type = "10" && bank_id = "'.$bank_id.'") ||(type = "22" && amount_from_id = "'.$bank_id.'") || (type = "7" && amount_to_id = "'.$bank_id.'") || (type="5" && amount_to_id = "'.$bank_id.'") || (type = "8" && (bank_id = "'.$bank_id.'" ||(type = "12" && bank_id = "'.$bank_id.'")||(type = "14" && amount_to_id = "'.$bank_id.'") ||(type = "15" && amount_to_id = "'.$bank_id.'") || (type = "16" && amount_to_id = "'.$bank_id.'") || (type = "17" && amount_to_id = "'.$bank_id.'")|| (type = "18" && amount_from_id = "'.$bank_id.'")||amount_from_id = "'.$bank_id.'")) || (account_id = "'.$bank_id.'")  || (type = "13" && sub_type="132" && bank_id = "'.$bank_id.'"))';
        $data = $data->orderBy('entry_date', 'asc')->get();
        if ($request['fund_transfer_export'] == 0) {
            return Excel::download(new BankLedgerReportExport($data, $balance, $closing, $bank_id, $bankAccount_id, $request['start_date'], $request['end_date']), 'Bank Ledger.xlsx');
        } elseif ($request['fund_transfer_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.bank_ledger.export_bank_ledger_report', compact('data', 'balance', 'closing', 'bank_id'))
                ->setPaper('a4', 'landscape')
                ->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('Bank Ledger.pdf');
        }
    }
    public function export_einvest_transaction(Request $request)
    {
        $data = Daybook::where('account_no', $request->export_account_no)->get();
        if ($request['export_value'] == 0) {
            return Excel::download(new ExportEInvestmentTransaction($data), 'EliTransaction.xlsx');
        } elseif ($request['export_value'] == 1) {
            $pdf = PDF::loadView('templates.admin.e_investment.export_eInvest_transaction_list', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('EliTransaction.pdf');
        }
    }

    // Member Investment Renewal Detail Listing Export
    public function exportRenewalList(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('renewalexport_list' . $token);
        $count = Cache::get('renewalexport_count' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        // $limit = $count;
        // dd($limit);
        $returnURL = URL::to('/') . "/report/renewalexport_countreport.csv";
        $fileName = env('APP_EXPORTURL') . "report/renewalexport_countreport.csv";
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
        $records = array_slice($data, $start, $limit);
        $payment_mode = [
            0 => "Cash",
            1 => "Cheque",
            2 => "DD",
            3 => "Online",
            4 => "By Saving Account",
            5 => "From Loan Amount"
        ];
        foreach ($records as $row) {
            $planId = $row['investment']['plan_id'];
            $planName = '';
            if ($planId > 0) {
                if (!empty($row['investment']['plan'])) {
                    $planName = $row['investment']['plan']['name'] ?? 'N/A';
                }
            }
            $tenure = '';
            if ($planId == 1) {
                $tenure = 'N/A';
            } else {
                $tenure = $row['investment']['tenure'] . ' Year';
            }
            $sno++;
            $val['S/N'] = $sno;
            $val['CREATED DATE'] = date('d/m/Y', strtotime($row['created_at'])) ?? 'N/A';
            $val['TRANSACTION BY'] = ($row['is_app'] == 1 ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software'));
            $val['Company'] = $row['company']['name'] ?? 'N/A';
            $val['BR NAME'] = $row['dbranch']['name'] ?? 'N/A';
            $val['BR CODE'] = $row['dbranch']['branch_code'] ?? 'N/A';
            $val['SO NAME'] = $row['dbranch']['sector'] ?? 'N/A';
            $val['RO NAME'] = $row['dbranch']['sector'] ?? 'N/A';
            $val['ZO NAME'] = $row['dbranch']['zone'] ?? 'N/A';
            $val['CUSTOMER ID'] = isset($row['member_company']['member']) ? ($row['member_company']['member']['member_id'] ?? 'N/A') : 'N/A';
            $val['MEMBER ID'] = $row['member_company']['member_id'] ?? 'N/A';
            $val['ACCOUNT NO'] = $row['account_no'] ?? 'N/A';
            $val['Member'] = isset($row['member_company']['member']) ? ($row['member_company']['member']['first_name'] . ' ' . ($row['member_company']['member']['last_name'] ?? '')) : 'N/A';
            $val['PLAN'] = $planName;
            $val['TENURE'] = $tenure;
            $val['AMOUNT'] = number_format((float) $row['amount'], 2, '.', '');
            $val['ASSOCIATE CODE'] = (isset($row['associate_member'])) ? $row['associate_member']['associate_no'] : 'N/A';
            $val['ASSOCIATE NAME'] = (isset($row['associate_member'])) ? ($row['associate_member']['first_name'] . ' ' . $row['associate_member']['last_name']) : 'N/A';
            $val['PAYMENT MODE'] = $payment_mode[$row['payment_mode']];
            $val['ACCOUNT OPENING DATE'] = date('d/m/Y', strtotime($row['investment']['created_at']));
            $val['DEMO AMOUNT'] = $row['investment']['deposite_amount'];
            $val['MOTHER BRANCH'] = $row['investment']['branch']['name'] ?? 'N/A';
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
    public function daybookReportExportDublicate(Request $request)
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
        if ($request['branch_id'] != '') {
            $branch_id = $request['branch_id'];
        } else {
            $branch_id = '';
        }
        if ($request['company_id'] != '') {
            $company_id = $request['company_id'];
        } else {
            $company_id = '';
        }
        $cash_in_hand['CR'] = BranchDaybook::where(function ($q) {
            $q->where('sub_type', '!=', 30)->orwhere('sub_type', '=', NULL);
        })->where('payment_mode', 0)->where('branch_id', $branch_id)->where('payment_type', 'CR')->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $cash_in_hand['DR'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $cheque['CR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $cheque['DR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $bank = SamraddhBank::with('bankAccount')->where('company_id', $company_id)->get();
        $rowReturn = array();
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
        //Data Get All Records
        $alldata = BranchDaybook::/*select('id','type','sub_type','type_id','type_transaction_id','entry_date','amount','opening_balance','closing_balance','transction_bank_to','description_cr','description_dr;)*/ with([
            'memberCompanybyMemberId.member',
            'associateMember',
            'member_investment' => function ($q) {
                $q->select('id', 'account_number', 'plan_id', 'member_id', 'associate_id', 'customer_id')
                    ->with([
                        'member' => function ($q) {
                            $q->select('id', 'member_id', 'first_name', 'last_name');
                        }
                    ])
                    ->with('ssb', 'plan', 'associateMember', 'memberCompany');
            }
        ])
            ->when('type' == 5, function ($q) {
                return $q->with([
                    'member_loan' => function ($q) {
                        $q->select('id', 'applicant_id', 'loan_type')->with('loanMember');
                    }
                ]);
            })->when('type' == 5, function ($q) {
                $q->with([
                    'group_member_loan' => function ($q) {
                        $q->select('id', 'applicant_id', 'loan_type', 'member_loan_id', 'member_id')->with('loanMember');
                    }
                ]);
            })
            ->with([
                'demand_advice' => function ($q) {
                    $q->select('id', 'investment_id', 'employee_name')->with([
                        'investment' => function ($q) {
                            $q->select('id', 'account_number', 'plan_id', 'member_id', 'account_number')->with('plan')
                                ->with('member');
                        }
                    ])->with([
                                'expenses' => function ($qa) {
                                    $qa->select('id')->with('advices');
                                }
                            ]);
                }
            ])
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                }
            ])
            ->with('receivedvoucherbytype_id')
            ->with('receivedvoucherbytype_transaction_id')
            ->with([
                'SavingAccountTranscation' => function ($q) {
                    $q->with([
                        'savingAc' => function ($q) {
                            $q->select('id', 'account_no')->with('ssbMember')->with('associate');
                        }
                    ]);
                }
            ])
            ->when('type' == 7, function ($q) {
                return $q->with([
                    'SamraddhBank' => function ($q) {
                        $q->with('bankAccount');
                    }
                ]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with([
                    'VoucherSamraddhBank' => function ($q) {
                        $q->with('bankAccount');
                    }
                ]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with([
                    'VoucherSamraddhBankbank_ac_id' => function ($q) {
                        $q->with('bankAccount');
                    }
                ]);
            })
            ->when('type' == 1, function ($q) {
                return $q->with('memberMemberId');
            })
            ->when('type' == 2, function ($q) {
                return $q->with('memberMemberId');
            })
            ->with('accountHead')->with([
                    'loan_from_bank' => function ($q) {
                        $q->with('loan_emi');
                    }
                ])
            ->with('company_bound')->with([
                    'bill_expense' => function ($q) {
                        $q->with('head')
                            ->with('subb_head')
                            ->with('subb_head2');
                    }
                ])
            ->with('BillExpense')
            ->with([
                'EmployeeSalaryBytype_id' => function ($q) {
                    $q->with('salary_employee');
                }
            ])
            ->with([
                'RentPayment' => function ($q) {
                    $q->with('rentLib');
                }
            ])
            ->with([
                'RentLiabilityLedger' => function ($q) {
                    $q->with('rentLib');
                }
            ])
            ->with([
                'EmployeeSalary' => function ($q) {
                    $q->with('salary_employee');
                }
            ])
            ->with('associateMember')
            ->with('SavingAccountTranscationtype_trans_id')
            ->where('branch_id', $branch_id)
            ->where('company_id', $company_id)
            ->where('amount', '>', 0)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_deleted', 0)
            ->orderBy('entry_date', 'ASC')
            ->get();
        $data = $alldata;
        // dd($data);
        //Get Data By Cache
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
        foreach ($data as $index => $value) {
            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name = 'N/A';
            $a_name = 'N/A';
            $data = $this->getCompleteDetail($value);
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
                $companyname = \App\Models\Companies::withoutGlobalScopes()->where('id', $value->company_id)->value('name');
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
                $cr_amount = number_format((float) $value->amount, 2, '.', '');
            }
            if ($value->payment_type == 'DR') {
                $dr_amnt = number_format((float) $value->amount, 2, '.', '');
            }
            // Balance
            if ($value->payment_mode == 0 && $is_eli == 0) {
                $balance = number_format((float) $balance, 2, '.', '');
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
            $val['company_name'] = $companyname;
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
            return Excel::download(new DublicateDaybookReportExport($rowReturn, $cash_in_hand, $cheque, $bank, $branch_id, $startDate, $endDate, $company_id), 'DublicateDaybookReport.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.report.dublicate.export_daybook_report', compact('rowReturn', 'cash_in_hand', 'cheque', 'bank', 'branch_id', 'startDate', 'endDate'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('DublicateDaybookReport.pdf');
        }
    }
    public function export_update_15g(Request $request)
    {
        $companyId = $request->customerId;
        $memberId = $request->member_id;
        $data = Form15G::where('year', '!=', 'NULL')
            ->with([
                'member:id,first_name,last_name',
                'memberCompany:id,customer_id',
                'company:id,name'
            ])
            ->where('member_id', $memberId)->get();
        if ($request['export'] == 0) {
            return Excel::download(new Update15GExport($data), 'update_15g.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.form_g.export_update_15g', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('update_15g.pdf');
        }
    }
    public function tds_deposite_export(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/tds_deposit.csv";
            $fileName = env('APP_EXPORTURL') . "asset/tds_deposit.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = TdsDeductionSetting::select('id', 'tds_per', 'start_date', 'end_date', 'created_at', 'tds_amount', 'type')->orderBy('created_at', 'desc');
        if ($request['export'] == 0) {
            $totalResults = $data->orderBy('created_at', 'desc')->count();
            $results = $data->orderBy('created_at', 'desc')->offset($start)->limit($limit)->get();
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
                $val['DATE'] = date("d/m/Y", strtotime($row->created_at));
                $val['START DATE'] = $row->start_date ? date("d/m/Y", strtotime($row->start_date)) : 'N/A';
                $end_date = $row->end_date ? date("d/m/Y", strtotime($row->end_date)) : 'N/A';
                $val['END DATE'] = $end_date;
                $val['TDS PERCENTAGE'] = $row->tds_per;
                $val['TDS AMOUNT'] = $row->tds_amount;
                $type = 'N/A';
                if ($row->type == 1) {
                    $type = 'Interest On Deposite With Pencard';
                } elseif ($row->type == 2) {
                    $type = 'Interest On Deposite Senior Citizen';
                } elseif ($row->type == 3) {
                    $type = 'Interest On Commission With Pencard';
                } elseif ($row->type == 4) {
                    $type = 'Interest On Commission WithOut Pencard';
                } elseif ($row->type == 5) {
                    $type = 'Interest On Deposite Without Pencard';
                }
                $val['TYPE'] = $type;
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
        } elseif ($request['export'] == 1) {
            $data = TdsDeposit::get();
            $pdf = PDF::loadView('templates.admin.tds_deposit.export_tds_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('tds_deposite.pdf');
        }
    }
    /*
    public function exportMemberBlacklistOnLoan(Request $request)
    {
        $data = Member::where('member_id','!=','9999999')->where('is_blacklist_on_loan','1');
        if(!is_null(Auth::user()->branch_ids)){
            $branch_ids=Auth::user()->branch_ids;
            $data=$data->whereIn('branch_id',explode(",",$branch_ids));
        }
        if(isset($request['is_search']) && $request['is_search'] == 'yes')
        {
            if($request['associate_code'] !=''){
                $associate_code=$request['associate_code'];
                $data=$data->where('associate_code','=',$associate_code);
            }
            if($request['branch_id'] !=''){
                $id=$request['branch_id'];
                $data=$data->where('branch_id','=',$id);
            }
            if($request['member_id'] !=''){
                $meid=$request['member_id'];
                $data=$data->where('member_id','=',$meid);
            }
            if($request['name'] !=''){
                $name =$request['name'];
                $data=$data->where(function ($query) use ($name) { $query->where('first_name','LIKE','%'.$name.'%')->orWhere('last_name','LIKE','%'.$name.'%')->orWhere(DB::raw('concat(first_name," ",last_name)') , 'LIKE' , "%$name%"); });
            }
            if($request['start_date'] !=''){
                $startDate=date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if($request['end_date'] !=''){
                $endDate=date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate='';
                }
                $data=$data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        }
        $memberList=$data->orderby('id','DESC')->get();
        if($request['member_export'] == 0){
            return Excel::download(new MemberExport($memberList), 'member.xlsx');
        }elseif ($request['member_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.member.memberexport',compact('memberList'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path().'_filename.pdf');
            return $pdf->download('members.pdf');
        }
    }
*/
    public function exportMemberBlacklistOnLoan(Request $request)
    {
        if ($request['member_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/black_list_member_loan.csv";
            $fileName = env('APP_EXPORTURL') . "asset/black_list_member_loan.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = Member::with('branch')
            ->with([
                'states' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
            ->with([
                'city' => function ($q) {
                    $q->select(['id', 'name']);
                }
            ])
            ->with([
                'district' => function ($q) {
                    $q->select(['id', 'name']);
                }
            ])
            ->with([
                'memberIdProof' => function ($q) {
                    $q->with([
                        'idTypeFirst' => function ($q) {
                            $q->select(['id', 'name']);
                        }
                    ])
                        ->with([
                            'idTypeSecond' => function ($q) {
                                $q->select(['id', 'name']);
                            }
                        ]);
                }
            ])
            ->with([
                'children' => function ($q) {
                    $q->select(['id', 'first_name', 'last_name']);
                }
            ])
            ->with([
                'memberNomineeDetails' => function ($q) {
                    $q->with([
                        'nomineeRelationDetails' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ]);
                }
            ])
            ->where('member_id', '!=', '9999999')->where('is_blacklist_on_loan', '1');
        if ($request['associate_code'] != '') {
            $associate_code = $request['associate_code'];
            $data = $data->where('associate_code', '=', $associate_code);
        }
        if (isset($request['branch_id']) && $request['branch_id'] != '') {
            $id = $request['branch_id'];
            $data = $data->where('branch_id', '=', $id);
        }
        if ($request['member_id'] != '') {
            $meid = $request['member_id'];
            $data = $data->where('member_id', '=', $meid);
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
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['member_export'] == 0) {
            $sno = $_POST['start'];
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
                $NomineeDetail = $row['memberNomineeDetails'];
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->re_date));
                $val['BR NAME'] = $row['branch']->name;
                $val['BR CODE'] = $row['branch']->branch_code;
                $val['SO NAME'] = $row['branch']->sector;
                $val['RO NAME'] = $row['branch']->sector;
                $val['ZO NAME'] = $row['branch']->zone;
                $btnS = '';
                //$url8 = URL::to("admin/member-edit/".$row->id."");
                //$btnS .= '<a class=" " href="'.$url8.'" title="Edit Member">' .$row->member_id.'</a>';
                //$val['member_id']=$btnS;
                $val['MEMBER ID'] = $row->member_id;
                $val['NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['DOB'] = date('d/m/Y', strtotime($row->dob));
                $val['NOMINEE NAME'] = $NomineeDetail->name; //getMemberNomineeDetail($row->id)->name;
                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;
                    if ($relation_id) {
                        $val['RELATION'] = $NomineeDetail['nomineeRelationDetails']->name;
                    } else {
                        $val['RELATION'] = '';
                    }
                } else {
                    $val['RELATION'] = '';
                }
                $val['NOMINEE AGE'] = $NomineeDetail->age; //getMemberNomineeDetail($row->id)->age;
                $accountNo = '';
                if (getMemberSsbAccountDetail($row->id)) {
                    $accountNo = getMemberSsbAccountDetail($row->id)->account_no;
                }
                $val['ACCOUNT NO'] = $accountNo;
                //$val['EMAIL']=$row->email;
                $val['MOBILE NO'] = $row->mobile_no;
                $val['ASSOCIATE CODE'] = $row->associate_code;
                $val['ASSOCITE NAME'] = $row['children']->first_name . ' ' . $row['children']->last_name; //getSeniorData($row->associate_id,'first_name').' '.getSeniorData($row->associate_id,'last_name');
                if ($row->is_block == 1) {
                    $status = 'Blocked';
                } else {
                    if ($row->status == 1) {
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
                if ($row->is_blacklist_on_loan == "1") {
                    $is_blacklist_on_loan = 'Blacklisted';
                } else {
                    $is_blacklist_on_loan = 'Active';
                }
                $val['is_blacklist_on_loan'] = $is_blacklist_on_loan;
                $val['ADDRESSS'] = preg_replace("/\r|\n/", "", $row->address);
                $val['STATE'] = $row['states']->name; //getStateName($row->state_id);
                $val['DISTRICT'] = $row['district']->name; //getDistrictName($row->district_id);
                $val['CITY'] = $row['city']->name; //getCityName($row->city_id);
                $val['VILLAGE'] = $row->village;
                $val['PIN CODE'] = $row->pin_code;
                //$idProofDetail= \App\Models\MemberIdProof::where('member_id',$row->id)->first();
                $val['FIRST ID PROOF'] = $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no; //getIdProofName($idProofDetail->first_id_type_id).' - '.$idProofDetail->first_id_no;
                $val['SECOND ID PROOF'] = $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no; //getIdProofName($idProofDetail->second_id_type_id).' - '.$idProofDetail->second_id_no;
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
        } elseif ($request['member_export'] == 1) {
            $memberList = $data->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.member.memberexport', compact('memberList'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('members.pdf');
        }
    }
    public function exportMemberInvestmentTds(Request $request)
    {
        if ($request['m_investment_tds_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/interest_tds.csv";
            $fileName = env('APP_EXPORTURL') . "asset/interest_tds.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $plans = Plans::all();
        $responseArray = array();
        foreach ($plans as $val) {
            $mId = $request['m_id'];
            $data = Memberinvestments::where('member_id', $mId)->where('plan_id', '!=', 1)->where('plan_id', $val->id);
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                if ($request['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
            if ($request['plan_id'] != '') {
                $planId = $request['plan_id'];
            } else {
                $planId = '';
            }
            if ($request['branch_id'] != '') {
                $branch_id = $request['branch_id'];
            } else {
                $branch_id = '';
            }
        }
        if ($request['m_investment_tds_export'] == 0) {
            $sno = $_POST['start'];
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
                $val['date'] = date("d/m/Y", strtotime(convertDate($row->date_to)));
                $val['plan_name'] = getPlanDetail($row->plan_type)->name;
                $val['account_number'] = getInvestmentDetails($row->investment_id)->account_number;
                $val['interest_amount'] = $row->interest_amount;
                $val['tds_deduction'] = $row->tdsamount_on_interest;
                $val['cr_amount'] = 1;
                $val['dr_amount'] = $row->interest_amount - $row->tdsamount_on_interest;
                $val['balance'] = 1;
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
        } elseif ($request['m_investment_tds_export'] == 1) {
            $pdf = PDF::loadView('templates.admin.member.member_investment_tds_export', compact('responseArray', 'startDate', 'endDate', 'planId', 'branch_id'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('memberinvestmenttdsexport.pdf');
        }
    }
    public function export_jv_detail()
    {
        $pdf = PDF::loadView('templates.admin.jv_management.exportjvdetail')->setPaper('a4', 'landscape')->setWarnings(false);
        $pdf->save(storage_path() . '_filename.pdf');
        return $pdf->download('jvDetail.pdf');
    }
    public function balanceSheetReportExport(Request $request)
    {
        $balanceSheetData = Session::get('balanceSheet');
        $filter = Session::get('balanceSheet_filter');
        $branchName = getBranchNameByBrachAuto($filter['branch']) ?? '';
        $libalityHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 2 && $data->first()->parent_id == 1);
        });
        $assetHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return ($data->first()->labels == 2 && $data->first()->parent_id == 2);
        });
        $labelThreeHead = $balanceSheetData->groupBy('parent_id')->filter(function ($data) {
            return $data->first()->labels == 3;
        });
        $actualData = $balanceSheetData->filter(function ($data) {
            return $data->labels != NULL;
        })->toArray();
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
        $date = explode(' - ', $request->financial_year);
        $start_y = $date[0];
        $end_y = $date[1];
        $start_m = 04;
        $start_d = 01;
        $previousYearStartDate = $start_y - 1;
        $previousYearEndDate = $end_y - 1;
        $previousData = array();
        $branch_id = $request->branch_id;
        $companyId = $request->company_id;
        $previousendDate = $previousYearEndDate . '-03-31';
        $previousYearBalance = \App\Models\BalanceSheetClosing::whereHas('accountHeads', function ($q) {
            $q->where('is_trial', 0);
        })
            ->whereNotNull('levels')
            ->when($branch_id, function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            })
            ->whereCompanyId($request->company_id)
            // ->where('start_year',$previousYearStartDate)
            ->where('end_date', '<=', $previousendDate)
            ->where('is_opening_balance', 1)
            ->where('is_deleted', 0)
            ->get()
        ;
        $oldheadclosing = \App\Models\HeadClosing::with('accountHeads')->where('start_year', $previousYearStartDate)
            ->where('company_id', $companyId)
            ->when($branch_id, function ($q) use ($branch_id) {
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
        $data = [
            'libalityHead' => $libalityHead,
            'assetHead' => $assetHead,
            'labelThreeHead' => $labelThreeHead,
            'actualData' => $actualData,
            'totalAmount' => $totalAmount,
            'expenseAmount' => $expenseAmount,
            'oldheadclosing' => $oldheadclosing,
            'amountNew' => $amounts,
            'branchName' => $branchName ? $branchName->name : 'All Branch',
        ];
        return Excel::download(new BalanceSheetExport($data), 'BalancesheetReportDetail.xlsx');
    }
    public function balanceSheetReportExportDetail(Request $request)
    {
        $data['head_id'] = $head_id = $request->head_id;
        $data['branch_id'] = $branch_id = $request->branch_id;
        $data['label'] = $label = $request->label;
        $data['company_id'] = $company_id = $request->company;
        $data["start_date"] = $start_date = $start_date = date("Y-m-d", strtotime(convertDate($request->start_date)));
        $data["end_date"] = $end_date = date("Y-m-d", strtotime(convertDate($request->end_date)));
        $data['headDetail'] = $headDetail = AccountHeads::where('head_id', $head_id)->First();
        $data['childHead'] = $childHead = getHead($head_id, $data['headDetail']->labels + 1);
        $data['title'] = $title = $request->export_title . $headDetail->sub_head;
        if ($head_id == 6) {
            // $incomeDr=\App\Models\AllHeadTransaction::where('head_id',3)->where('payment_type','DR');
            // $incomeCr=\App\Models\AllHeadTransaction::where('head_id',3)->where('payment_type','CR');
            // $expenceDr=\App\Models\AllHeadTransaction::where('head_id',4)->where('payment_type','DR');
            // $expenceCr=\App\Models\AllHeadTransaction::where('head_id',4)->where('payment_type','CR');
            // if($date_filter!= "" && $end_date!= "")
            // {
            //     $date_filter=date("Y-m-d", strtotime($date_filter));
            //     $end_date=date("Y-m-d", strtotime($end_date));
            //     $expenceDr=$expenceDr->whereBetween('entry_date', [$date_filter, $end_date]);
            //     $expenceCr=$expenceCr->whereBetween('entry_date', [$date_filter, $end_date]);
            //     $incomeDr=$incomeDr->whereBetween('entry_date', [$date_filter, $end_date]);
            //     $incomeCr=$incomeCr->whereBetween('entry_date', [$date_filter, $end_date]);
            // }
            // if($branch_filter !='')
            // {
            //     $expenceDr=$expenceDr->where('branch_id',$branch_filter);
            //     $expenceCr=$expenceCr->where('branch_id',$branch_filter);
            //     $incomeDr=$incomeDr->where('branch_id',$branch_filter);
            //     $incomeCr=$incomeCr->where('branch_id',$branch_filter);
            // }
            // if(!is_null(Auth::user()->branch_ids)){
            //      $branch_ids=Auth::user()->branch_ids;
            //     $expenceDr=$expenceDr->whereIn('branch_id',explode(",",$branch_ids));
            //     $expenceCr=$expenceCr->whereIn('branch_id',explode(",",$branch_ids));
            //     $incomeDr=$incomeDr->whereIn('branch_id',explode(",",$branch_ids));
            //     $incomeCr=$incomeCr->whereIn('branch_id',explode(",",$branch_ids));
            // }
            $data['profit_loss'] = headTotalNew(3, $start_date, $end_date, $branch_id, $company_id) - headTotalNew(4, $start_date, $end_date, $branch_id, $company_id);
        } else {
            $data['profit_loss'] = '';
        }
        $profit_loss = $data["profit_loss"];
        if ($request['export'] == 0) {
            return Excel::download(new BalanceSheetReportExportDetails($data, $branch_id, $start_date, $end_date, $company_id), $request->export_title . 'ReportDetail.xlsx');
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.balance_sheet2.export_details', compact('data', 'start_date', 'end_date', 'branch_id', 'title', 'head_id', 'profit_loss', 'headDetail', 'childHead', 'company_id', 'label'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($request->export_title . 'ReportDetails.pdf');
        }
    }
    public function balanceSheetReportBranchWise(Request $request)
    {
        $data['head_id'] = $head_id = $request->head_id;
        $data['label'] = $label = $request->label;
        $data['created_at'] = $created_at = date("Y-m-d", strtotime(convertDate($request->created_at)));
        $data['branch_id'] = $branch_id = $request->branch_id;
        $data['company_id'] = $company_id = $request->company;
        $data["start_date"] = $start_date = date("Y-m-d", strtotime(convertDate($request->start_date)));
        $data["end_date"] = $end_date = date("Y-m-d", strtotime(convertDate($request->end_date)));
        $data['headDetail'] = $headDetail = AccountHeads::where('head_id', $head_id)->First();
        $data['childHead'] = $childHead = getHead($head_id, $data['headDetail']->labels + 1);
        $data['title'] = $title = $request->export_title . $headDetail->sub_head;
        $head_info = AccountHeads::where('head_id', $head_id)->first();
        $parent_id1 = AccountHeads::where('head_id', $head_id)->first();
        $parent_id2 = AccountHeads::where('head_id', $parent_id1->parent_id)->first();
        $info = 'head' . $label;
        $parent_id1 = $parent_id2;
        if ($parent_id2) {
            $parent_id3 = AccountHeads::where('head_id', $parent_id2->parent_id)->first();
            $parent_id1 = $parent_id3;
        }
        $data = Branch::orderBy('id', 'ASC')
            ->when($company_id, function ($q) use ($company_id) {
                $q->with([
                    'companybranchs' => function ($q) use ($company_id) {
                        $q->whereCompanyId($company_id)
                            ->with('company:id,name')
                            ->get();
                    }
                ]);
            });
        if ($branch_id != '') {
            $data = $data->where('id', $branch_id);
        }
        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $data = $data->whereIn('id', explode(",", $branch_ids));
        }
        $data1 = $data->get();
        $count = count($data1);
        $data = $data->get();
        $totalCount = $data->count();
        $rowReturn = array();
        foreach ($data as $row) {
            $branch_id = $row->id;
            $startdate = '';
            $enddate = '';
            $val['branch'] = $row->name;
            $val['branch_code'] = $row->branch_code;
            $val['sector_name'] = $row->sector;
            $val['region_name'] = $row->regan;
            $val['zone_name'] = $row->zone;
            $val['opening_balance'] = 0;
            // if ($head_id == 60 || $head_id == 61) {
            //     $val['total_member'] = getBranchWiseBalanceSheetCRData($head_id, $branch_id, $start_date, $end_date);
            // } else {
            //     $val['total_member'] = getBranchWiseBalanceSheetDateData($head_id, $branch_id, $start_date, $end_date);
            // }
            // $val['total_member'] = headTotalMember($head_id, $start_date, $end_date, $branch_id);
            $val['amount'] = headTotalNew($head_id, $start_date, $end_date, $branch_id, $company_id);
            $val['company'] = $row->companybranchs ? $row->companybranchs->company[0] ? $row->companybranchs->company[0]->name : 'N/A' : 'N/A';
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new BalanceSheetReportExportDetailsBranchWiseExport($rowReturn), $request->export_title . 'ReportBranchWise.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet2.export_branch', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($request->export_title . 'ReportDetailsBranchwise.pdf');
        }
        /*
         */
    }
    public function profitLossExport(Request $request)
    {
        $balanceSheetData = Session::get('profitLossData');
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
        $totalAmount = [];
        $amount = [];
        $HeadTotalAmount = 0;
        foreach ($actualData as $item) {
            $amount[$item->head_id] = 0;
            $childHeadId = [];
            $totalAmount[$item->head_id] = ($item->nature == 1) ? ($item->amount_sum - $item->amount_sum_dr) : ($item->amount_sum_dr - $item->amount_sum);
        }
        $data = [
            'libalityHead' => $incomeHead,
            'assetHead' => $expenseHead,
            'labelThreeHead' => $labelThreeHead,
            'actualData' => $actualData,
            'totalAmount' => $totalAmount,
        ];
        return Excel::download(new ProfitLossExport($data), 'ProfitLoassDetail.xlsx');
    }
    public function profitLossDetailExport(Request $request)
    {
        $data['headDetail'] = $libalityHead = AccountHeads::where('head_id', $request->head)->First();
        $data['childHead'] = getHead($request->head, $libalityHead->labels + 1);
        $data['subchildHead'] = getHead($data['headDetail']->id, 4);
        $data['date'] = date("Y-m-d", strtotime(convertDate($request->date)));
        $data['to_date'] = date("Y-m-d", strtotime(convertDate($request->to_date)));
        $data['branch_id'] = $request->branch_id;
        $fileName = strtoupper($libalityHead->sub_head) . '.xlsx';
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportDetailReport($data), $fileName);
        } else {
            //$data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_detail', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossDetailReports.pdf');
        }
    }
    public function profitLossBranchWiseExport(Request $request)
    {
        $data['date'] = date("Y-m-d", strtotime(convertDate($request->date)));
        $data['to_date'] = date("Y-m-d", strtotime(convertDate($request->to_date)));
        $data['headDetail'] = $libalityHead = AccountHeads::where('head_id', $request->head)->First();
        $data['head'] = $request->head;
        $data['label'] = $request->label;
        $branch = $request->branch;
        $data['branches'] = Branch::orderBy('id', 'ASC');
        if ($branch != '') {
            $data['branches'] = $data['branches']->where('id', $branch);
        }
        $data['branches'] = $data['branches']->get();
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportBranchWiseReport($data), 'ProfitLossBranchWiseReport.xlsx');
        } else {
            //$data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_branchWise', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossDetailReport.pdf');
        }
    }
    public function balanceSheetReportDetailsExport(Request $request)
    {
        $data['title'] = 'Balance Sheet - Details Report';
        $data['start_date'] = date("Y-m-d", strtotime(convertDate($request->start_date)));
        $data['ends_date'] = date("Y-m-d", strtotime(convertDate($request->ends_date)));
        $data['head_id'] = $request->head_id;
        $data['headDetail'] = $libalityHead = AccountHeads::where('head_id', $request->head_id)->First();
        $data['childHead'] = getHead($request->head_id, 4);
        $data['subchildHead'] = getHead($request->head_id, 5);
        $data['head'] = $request->head_id;
        $data['branch_filter'] = $request->branch_name;
        $data['libalityHead'] = AccountHeads::where('status', '!=', 9)->where('parent_id', 1)->where('labels', 2)->orderBy('head_id', 'ASC')->get();
        $data['assestHead'] = $assestHead = AccountHeads::where('status', '!=', 9)->where('parent_id', 2)->where('labels', 2)->orderBy('head_id', 'ASC')->get();
        // dd($data);
        if ($request['export'] == 0) {
            return Excel::download(new BalanceSheetExportReportDetails($data), 'BalanceSheetExportReportDetails.xlsx');
        } else {
            $title = $data["title"];
            $date_filter = date("Y-m-d", strtotime(convertDate($data["start_date"])));
            $end_date_filter = date("Y-m-d", strtotime(convertDate($data["ends_date"])));
            $head_id = $data["head_id"];
            $headDetail = $data["headDetail"];
            $childHead = $data["childHead"];
            $subchildHead = $data["subchildHead"];
            $head = $data["head"];
            $branch_filter = $data["branch_filter"];
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_balancesheet_report_details_export', compact('data', 'title', 'date_filter', 'end_date_filter', 'head_id', 'headDetail', 'childHead', 'subchildHead', 'head', 'branch_filter'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('BalanceSheetExportReportDetails.pdf');
        }
    }
    public function profitLossStationaryChargeExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request, $_POST);
        $data1 = $record[0]->get();
        $data = $record[0]->get();
        // count total recordsFiltered
        $totalCount = count($data1);
        $sno = 0;
        $rowReturn = array();
        $accountHead = $record[1];
        $totalAmount = 0;
        if ($accountHead->head_id == $request->head) {
            $fileName = strtoupper($accountHead->sub_head) . '.xlsx';
        }
        foreach ($data as $value) {
            $data = $this->getCompleteDetail($value);
            $sno++;
            $branch_id = $value->id;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($value->entry_date));
            $val['member_name'] = $data['memberName'];
            $val['member_id'] = $data['memberId'];
            $val['plan_name'] = $data['plan_name'];
            $val['account_number'] = $data['memberAccount'];
            $credit = '0.00';
            $debit = '0.00';
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
                $val['cr'] = number_format((float) ($credit), 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
                $val['dr'] = number_format((float) ($debit), 2, '.', '');
            } else {
                $val['dr'] = $debit;
            }
            if ($accountHead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) ($totalAmount), 2, '.', '');
            $rowReturn[] = $val;
        }
        /*
        if($request['export'] == 0){
            return Excel::download(new ProfitLossExportStationaryChargeReport($data,$branch,$date,$to_date,$head), 'ProfitLossReport.xlsx');
        } else {
            //$data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_statioanry_charge',compact('data','branch','date','to_date','head'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path().'_filename.pdf');
            return $pdf->download('ProfitLossReport.pdf');
        }
        */
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportStationaryChargeReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_statioanry_charge', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossExportStationaryChargeReport.pdf');
        }
    }
    public function profitLossFileChargeExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request, $_POST);
        $accountHead = $record[1];
        $data = $record[0]->get();
        // count total recordsFiltered
        $fileName = strtoupper($accountHead->sub_head) . '.xlsx';
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        foreach ($data as $value) {
            $data = $this->getCompleteDetail($value);
            $credit = '0.00';
            $debit = '0.00';
            $sno++;
            $branch_id = $value->id;
            $enddate = '';
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($value->created_at));
            $val['account_number'] = $data['memberAccount'];
            $val['member'] = $data['memberName'];
            $val['plan_name'] = $data['plan_name'];
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = 0.00;
            }
            if ($accountHead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportFileChargeReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_file_charge', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossReport.pdf');
        }
    }
    public function profitLossInterestonDepositeExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        foreach ($data as $row) {
            $branch = $row->branch_id;
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            $val['account_number'] = $memberAccount;
            $val['member_id'] = $memberId;
            $val['member_name'] = $memberName;
            $val['plan_name'] = $plan_name;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $debit = 0;
                $val['dr'] = 0.00;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportInterestDepositeReport($rowReturn, $branch), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_interest_deposite', compact('data', 'branch'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossInterestDepositeReport.pdf');
        }
    }
    public function balanceSheetReportBranchWiseTds(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        foreach ($data as $row) {
            $credit = '0.00';
            $debit = '0.00';
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            if ($row['branch']) {
                $val['branch'] = $row['branch']->name;
            } else {
                $val['branch'] = 'N/A';
            }
            $val['voucher_number'] = $memberAccount;
            if ($memberName) {
                $val['owner_name'] = $memberName;
            } else {
                $val['owner_name'] = 'N/A';
            }
            //Credit Amount
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = $debit;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
            } else {
                $total = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $total;
            if ($row->payment_mode == 0) {
                $mode = 'CASH';
            } else if ($row->payment_mode == 1 || $row->payment_mode == 2) {
                $mode = 'BANK';
            } else if ($row->payment_mode == 3) {
                $mode = 'SSB';
            } else if ($row->payment_mode == 4) {
                $mode = 'AUTO TRANSFER';
            } else if ($row->payment_mode == 5) {
                $mode = 'BY LOAN AMOUNT';
            } else if ($row->payment_mode == 6) {
                $mode = 'JV';
            } else if ($row->payment_mode == 7) {
                $mode = 'CREDIT CARD';
            }
            $val['payment_type'] = $mode;
            $val['type'] = $type;
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ExportbalanceSheetReportBranchWiseTds($rowReturn), 'BalanceSheetReportBranchWiseReport.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.report.export_balance_sheet_branchwise_tds', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('BalanceSheetReportBranchWiseReport.pdf');
        }
    }
    public function profitLossLoanTakenExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request, $_POST);
        $data1 = $record[0]->get();
        $accounthead = $record[1];
        $data = $record[0]->get();
        // count total recordsFiltered
        $totalCount = count($data1);
        $sno = 0;
        $rowReturn = array();
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        $totalAmount = 0;
        foreach ($data as $value) {
            $data = $this->getCompleteDetail($value);
            $memberName = 0;
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($value->entry_date));
            $val['account_number'] = $memberAccount;
            $val['loan_account_name'] = $memberName;
            $val['description'] = $value->description;
            //Credit Amount
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $credit = 0;
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $debit = 0;
                $val['dr'] = $debit;
            }
            //Credit Amount
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportLoanTakenReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_loan_taken', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossInterestLoanTakenReport.pdf');
        }
    }
    public function profitLossSalaryExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        foreach ($data as $value) {
            $data = $this->getCompleteDetail($value);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($value->entry_date));
            $val['salary'] = "&#x20B9;" . number_format((float) $value->amount, 2, '.', '');
            $val['owner_name'] = $memberName;
            $val['employee_code'] = $memberAccount;
            //Credit Amount
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $credit = 0;
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $debit = 0;
                $val['dr'] = $debit;
            }
            //Credit Amount
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $credit = 0;
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $debit = 0;
                $val['dr'] = $debit;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportSalaryReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.salary_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossSalaryReport.pdf');
        }
    }
    public function profitLossPanelExport(Request $request)
    {
        $date = $request->date;
        $to_date = $request->to_date;
        $branch_id = $request->branch_id;
        $head_id = $request->head_id;
        $head_ids = array($head_id);
        $records = AccountHeads::where('head_id', $head_id)->first();
        $subHeadsIDS = AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_ids = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        if (count($ids) > 0) {
            $data = AllHeadTransaction::with(['branch', 'demand_advices_fresh_expenses'])->where('is_deleted', 0)->whereIn('head_id', $ids)->orderBy('entry_date', 'asc');
        } else {
            $data = AllHeadTransaction::with(['branch', 'demand_advices_fresh_expenses'])->where('is_deleted', 0)->whereIn('head_id', [$head_id])->orderBy('entry_date', 'asc');
        }
        if ($date != '') {
            $date = date("Y-m-d", strtotime(convertDate($date)));
            $to_date = date("Y-m-d", strtotime(convertDate($to_date)));
            $data = $data->whereBetween(\DB::raw('(entry_date)'), [$date, $to_date]);
        }
        $data1 = $data->get();
        $count = count($data1);
        $data = $data->get();
        $totalCount = $data->count();
        $sno = 0;
        $credit = 0;
        $debit = 0;
        $rowReturn = array();
        $totalAmount = 0;
        foreach ($data as $row) {
            $employeeDetail = ReceivedVoucher::with('rv_employee')->where('id', $row->type_transaction_id)->first();
            $sno = $sno + 1;
            $val['status'] = '';
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->entry_date));
            $val['employee_name'] = $employeeDetail['rv_employee']->employee_name;
            $val['employee_code'] = $employeeDetail['rv_employee']->employee_code;
            //Credit Amount
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = $debit;
            }
            if ($records->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportPanelReport($rowReturn), 'ProfitLossExportPanelReport.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.panel_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossSalaryReport.pdf');
        }
    }
    public function balanceSheetReportBranchWiseCashInHand(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $memberId = $data['memberId'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->entry_date));
            if ($memberId) {
                $val['member_id'] = $memberId;
            } else {
                $val['member_id'] = 'N/A';
            }
            if ($row->member_id) {
                $val['member_name'] = getMemberData($row->member_id)->first_name . ' ' . getMemberData($row->member_id)->last_name;
            } else {
                $val['member_name'] = 'N/A';
            }
            if ($type) {
                $val['transaction_type'] = $type;
            }
            if ($type) {
                $val['transaction_type'] = $type;
            }
            if ($memberAccount) {
                $val['account_number'] = $memberAccount;
            } else {
                $val['account_number'] = 'N/A';
            }
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $debit = 0;
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new balanceSheetReportBranchWiseCashInHand($rowReturn), 'balanceSheetReportBranchWiseCashInHand.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_balance_sheet_branchwise_case_in_hand', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('balanceSheetReportBranchWiseCashInHand.pdf');
        }
    }
    public function balanceSheetReportBranchWiseAdvancePayment(Request $request)
    {
        if ($request->head_id == "72") {
            $sub_payment_type = "1";
        } else if ($request->head_id == "73") {
            $sub_payment_type = "2";
        } else if ($request->head_id == "74") {
            $sub_payment_type = "3";
        } else {
            $sub_payment_type = "0";
        }
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['name'] = $memberName;
            $val['code'] = $memberAccount;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = "&#x20B9;" . number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = "&#x20B9;" . number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $debit = 0;
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $amount = (float) $credit - (float) $debit;
            } else {
                $amount = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $amount;
            $val['amount'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new balanceSheetReportBranchWiseAdvancePayment($rowReturn), 'balanceSheetReportBranchWiseAdvancePayment.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_balance_sheet_branchwise_advance_payment', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('balanceSheetReportBranchWiseAdvancePayment.pdf');
        }
    }
    public function profitLossReportExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $credit = 0;
        $debit = 0;
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            if ($row['branch']) {
                $val['branch'] = $row['branch']->name;
            } else {
                $val['branch'] = 'N/A';
            }
            $val['transaction_type'] = $type;
            $val['voucher_number'] = $memberAccount;
            $val['owner_name'] = $memberName;
            //Credit Amount
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $credit = 0;
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $debit = 0;
                $val['dr'] = $debit;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
            } else {
                $total = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $total;
            if ($row->payment_mode == 0) {
                $mode = 'CASH';
            } else if ($row->payment_mode == 1 || $row->payment_mode == 2) {
                $mode = 'BANK';
            } else if ($row->payment_mode == 3) {
                $mode = 'SSB';
            } else if ($row->payment_mode == 5) {
                $mode = 'LOAN';
            } else if ($row->payment_mode == 6) {
                $mode = 'JV';
            } else if ($row->payment_mode == 8) {
                $mode = 'CRON';
            } else {
                $mode = '';
            }
            $val['payment_type'] = $mode;
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossExportHeadReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.head_detail_report_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossHeadDetailReport.pdf');
        }
    }
    public function balanceSheetReportBranchWiseMembership(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $credit = 0;
        $debit = 0;
        $fileName = strtoupper(str_replace("/-", '', $accounthead->sub_head, ));
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberId = $data['memberId'];
            $sno++;
            $val['DT_RowIndex'] = $sno;
            $val['member_id'] = $memberId;
            $val['member_name'] = $memberName;
            //Credit Amount
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $credit = 0;
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $debit = 0;
                $val['dr'] = $debit;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
            } else {
                $total = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $total;
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        $data = $rowReturn;
        if ($request['export'] == 0) {
            return Excel::download(new balanceSheetReportBranchWiseMembership($data), $fileName . '.xlsx');
        } else {
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_balance_sheet_branchwise_membership', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($fileName . '.pdf');
        }
    }
    public function profitLossdepreciationExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request, $_POST);
        $data1 = $record[0]->get();
        $data = $record[0]->get();
        $accounthead = $record[1];
        // count total recordsFiltered
        $totalCount = count($data1);
        $sno = 0;
        $rowReturn = array();
        $fileName = $accounthead->sub_head . '.xlsx';
        $totalAmount = 0;
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $sno++;
            $credit = '0.00';
            $debit = '0.00';
            $branch_id = $row->id;
            $startdate = '';
            $enddate = '';
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->entry_date));
            $val['party_name'] = $data['memberName'];
            //Credit Amount
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = $debit;
            }
            //Total Balance
            if ($row->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
            } else {
                $total = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $total;
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new profitLossDepreciationExport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_depreciation', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('profitLossDepreciationExport.pdf');
        }
    }
    public function balanceSheetReportBranchWiseSaving(Request $request)
    {
        $date = $request->start_date;
        $end_date = $request->ends_date;
        $branch_id = $request->branch_filter;
        $data = AllHeadTransaction::where('head_id', 56)->where('is_deleted', 0);
        if ($branch_id != '') {
            $data = $data->where('branch_id', $branch_id);
        }
        if ($date != '' && $end_date == "") {
            $date = date("Y-m-d", strtotime(convertDate($date)));
            $data = $data->whereDate('created_at', '<=', $date);
        }
        if ($date != '' && $end_date != "") {
            $date = date("Y-m-d", strtotime(convertDate($date)));
            $end_date = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween('created_at', [$date, $end_date]);
        }
        $data = $data->get();
        if ($request['export'] == 0) {
            return Excel::download(new balanceSheetReportBranchWiseSaving($data), 'balanceSheetReportBranchWiseSaving.xlsx');
        } else {
            //$data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_balance_sheet_branchwise_saving', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('balanceSheetReportBranchWiseSaving.pdf');
        }
    }
    public function balanceSheetReportBranchWiseDeposite(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request, $_POST);
        $data1 = $record[0]->get();
        $data = $record[0]->get();
        $accounthead = $record[1];
        // count total recordsFiltered
        $totalCount = count($data1);
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $fileName = strtoupper($accounthead->sub_head);
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $sno++;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            ;
            $val['account_number'] = $data['memberAccount'];
            $val['member_id'] = $data['memberId'];
            $val['member_name'] = $data['memberName'];
            $val['type'] = $data['type'];
            $mode = '';
            if ($row->payment_mode == 0) {
                $mode = 'CASH';
            } else if ($row->payment_mode == 1 || $row->payment_mode == 2) {
                $mode = 'BANK';
            } else if ($row->payment_mode == 3) {
                $mode = 'SSB';
            } else if ($row->payment_mode == 4) {
                $mode = 'AUTO TRANSFER';
            } else if ($row->payment_mode == 5) {
                $mode = 'BY LOAN AMOUNT';
            } else if ($row->payment_mode == 6) {
                $mode = 'JV';
            } else if ($row->payment_mode == 7) {
                $mode = 'CREDIT CARD';
            }
            $val['payment_mode'] = $mode;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $debit = 0;
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
            } else {
                $total = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $total;
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        //print_r($rowReturn); die;
        if ($request['export'] == 0) {
            return Excel::download(new balanceSheetReportBranchWiseDeposite($rowReturn), $fileName . '.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_balance_sheet_branchwise_deposite', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($fileName . '.pdf');
        }
    }
    public function ProfitLossrentExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        $totalAmount = 0;
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->entry_date));
            $val['owner_name'] = $memberName;
            $val['rent_type'] = $rentType;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $debit = 0;
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossRentExport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_rent', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('profitLossRentExport.pdf');
        }
    }
    public function ProfitLosscommissionExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        foreach ($data as $row) {
            $sno++;
            $credit = '0.00';
            $debit = '0.00';
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $associate_name = $data['associate_name'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->entry_date));
            $val['associate_code'] = $memberAccount;
            $val['associate_name'] = $associate_name;
            $val['member_name'] = $memberName;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = 0.00;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new ProfitLossCommissionExport($rowReturn), 'ProfitLossCommissionExport.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.export_profit_loss_commission', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('profitLossCommissionExport.pdf');
        }
    }
    public function balanceSheetReportBankWise(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request, $_POST);
        $data1 = $record[0]->get();
        $data = $record[0]->get();
        $accounthead = $record[1];
        // count total recordsFiltered
        $totalCount = count($data1);
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        $fileNamepdf = strtoupper($accounthead->sub_head);
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $sno++;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->entry_date));
            $val['type'] = $data['type'];
            if (isset($row->description)) {
                $val['description'] = $row->description;
            } else {
                $val['description'] = '';
            }
            if ($row->transction_bank_to) {
                $transction_bank_to_name = getSamraddhBank($row->transction_bank_to);
                $val['received_bank'] = $transction_bank_to_name->bank_name;
            } else {
                $val['received_bank'] = $data['memberName'];
            }
            if ($row->transction_bank_to) {
                $transction_bank_to_ac_no = getSamraddhBankAccountId($row->transction_bank_to);
                $val['account_number'] = $transction_bank_to_ac_no->account_no;
            } else {
                $val['account_number'] = $data['memberAccount'];
            }
            if ($row->bank_id) {
                $bank_name = getSamraddhBank($row->bank_id);
                if (isset($bank_name->bank_name)) {
                    $val['payment_bank'] = $bank_name->bank_name;
                } else {
                    $val['payment_bank'] = 'N/A';
                }
            } else {
                $val['payment_bank'] = "N/A";
            }
            if ($row->bank_ac_id) {
                $bank_ac_no = getSamraddhBankAccountId($row->bank_ac_id);
                $val['payment_account_number'] = $bank_ac_no->account_no;
            } else {
                $val['payment_account_number'] = 'N/A';
            }
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $debit = 0;
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new BalanceSheetReportExportDetailsDataExport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_detail_data_report', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($fileNamepdf . '.pdf');
        }
    }
    // Export BranchWise Loan Asset
    public function balanceSheetReportBranchWiseLoanAsset(Request $request)
    {
        $date = $request->start_date;
        $end_date = $request->ends_date;
        $head_id = $request->head_id;
        $branch_id = $request->branch_filter;
        $head_info = AccountHeads::where('head_id', $head_id)->first();
        $head_ids = array($head_id);
        // Now get child of that head
        $records = AccountHeads::where('head_id', $head_id)->first();
        $subHeadsIDS = AccountHeads::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
        if (count($subHeadsIDS) > 0) {
            $head_id = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        foreach ($return_array as $key => $value) {
            $ids[] = $value;
        }
        if (count($ids) > 0) {
            $data = AllHeadTransaction::with('branch')->where('is_deleted', 0)->whereIn('head_id', $ids);
        } else {
            $data = AllHeadTransaction::with('branch')->where('is_deleted', 0)->whereIn('head_id', [$head_id]);
        }
        if ($branch_id != '') {
            $data = $data->where('branch_id', $branch_id);
        }
        if ($date != '' && $end_date != "") {
            $date = date("Y-m-d", strtotime(convertDate($date)));
            $end_date = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween(\DB::raw('DATE(entry_date)'), [$date, $end_date]);
        }
        /*$data1 = $data->get();
        $count = count($data1);*/
        $totalCount = $data->count();
        $rowReturn = array();
        $data = $data->get();
        $totalAmount = 0;
        $sno = 0;
        foreach ($data as $value) {
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
            $memberData = getMemberInvestment($value->type_id);
            $loanData = getLoanDetail($value->type_id);
            $groupLoanData = \App\Models\Grouploans::where('id', $value->type_id)->first();
            $DemandAdviceData = \App\Models\DemandAdvice::where('id', $value->type_id)->first();
            $freshExpenseData = \App\Models\DemandAdviceExpense::where('id', $value->type_id)->first();
            $memberName = '';
            $memberAccount = '';
            $plan_name = '';
            $memberId = '';
            if ($value->payment_mode == 6) {
                $rentPaymentDetail = \App\Models\RentLiabilityLedger::with('rentLib')->where('id', $value->type_transaction_id)->first();
                $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
            } else {
                $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $value->type_id)->first();
                $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $value->type_id)->first();
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
                    $memberName = getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name;
                    $memberId = getMemberData($value->type_id)->member_id;
                    $memberAccount = 'N/A';
                }
            } elseif ($value->type == 2) {
                if ($value->type_id) {
                    $memberName = getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name;
                    $memberId = getMemberData($value->type_id)->member_id;
                    $memberAccount = 'N/A';
                }
            } elseif ($value->type == 3) {
                if ($value->member_id) {
                    $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                    $plan_name = getPlanDetail($memberData->plan_id)->name;
                    $memberId = getMemberData($value->member_id)->member_id;
                }
                if ($memberData) {
                    $memberAccount = $memberData->account_number;
                }
            } elseif ($value->type == 4) {
                if ($value->associate_id) {
                    $associate_name = getMemberData($value->associate_id)->first_name . ' ' . getMemberData($value->associate_id)->last_name;
                }
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
                if ($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57 || $value->sub_type == 511 || $value->sub_type == 513 || $value->sub_type == 515) {
                    if ($loanData) {
                        $memberName = getMemberData($loanData->applicant_id)->first_name . ' ' . getMemberData($loanData->applicant_id)->last_name;
                        if ($loanData->loan_type == 1) {
                            $plan_name = 'Personal Loan(PL)';
                        }
                        if ($loanData->loan_type == 2) {
                            $plan_name = 'Staff Loan(SL)';
                        }
                        if ($loanData->loan_type == 4) {
                            $plan_name = 'Loan against Investment plan(DL)';
                        }
                        $memberAccount = $loanData->account_number;
                        $memberId = getMemberData($loanData->applicant_id)->member_id;
                    }
                } elseif ($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58 || $value->sub_type == 512 || $value->sub_type == 514 || $value->sub_type == 516 || $value->sub_type == 518) {
                    if ($groupLoanData) {
                        $memberName = getMemberData($groupLoanData->member_id)->first_name . ' ' . getMemberData($groupLoanData->member_id)->last_name;
                        $memberAccount = $groupLoanData->account_number;
                        $memberId = getMemberData($groupLoanData->member_id)->member_id;
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
                $memberName = $memberName->bank_name;
                $memberAccount = getSamraddhBankAccountId($value->transction_bank_to);
                $memberAccount = $memberAccount->account_no;
            } elseif ($value->type == 9) {
                $associate_id = getMemberData($value->member_id)->associate_no;
                $associate_name = 'N/A';
                if (isset($associate_id)) {
                    $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                }
                if (isset($value->member_id)) {
                    $memberName == getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                    $memberAccount = getMemberData($value->member_id)->associate_no;
                }
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
                    $memberName = $salaryDetail['salary_employee']->employee_name;
                    $memberAccount = $salaryDetail['salary_employee']->employee_code;
                }
            } elseif ($value->type == 13) {
                if ($value->sub_type == 131) {
                    if ($freshExpenseData) {
                        $memberName = $freshExpenseData->party_name;
                        $memberAccount = $freshExpenseData['advices']->voucher_number;
                        $memberId = $freshExpenseData->bill_number;
                    }
                }
                if ($value->sub_type == 132) {
                    if ($DemandAdviceData) {
                        $memberName = $DemandAdviceData->employee_name;
                        $memberAccount = $DemandAdviceData->employee_code;
                        $memberId = $DemandAdviceData->employee_code;
                    }
                }
                if ($value->sub_type == 133) {
                    $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                    $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 134) {
                    $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                    $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 135) {
                    $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                    $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 136) {
                    $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                    $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                    $plan_name = getPlanDetail($plan_id->plan_id)->name;
                }
                if ($value->sub_type == 137) {
                    $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                    $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
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
                        $memberId = getEmployeeData($voucherDetail->employee_id)->employee_code;
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
                $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                $memberId = getMemberData($value->member_id)->member_id;
            } elseif ($value->type == 20) {
                $record = \App\Models\Expense::where('id', $value->type_id)->first();
                if ($record->account_head_id && $record->sub_head1 && $record->sub_head2) {
                    $mainHead = getAcountHeadData($record->account_head_id);
                    $subHead = getAcountHeadData($record->sub_head1);
                    $subHead2 = getAcountHeadData($record->sub_head2);
                    $memberName = 'INDIRECT EXPENSE / ' . $mainHead . '/' . $subHead . '/' . $subHead2;
                } elseif ($record->account_head_id && $record->sub_head1) {
                    $mainHead = getAcountHeadData($record->account_head_id);
                    $subHead = getAcountHeadData($record->sub_head1);
                    $memberName = 'INDIRECT EXPENSE / ' . $mainHead . '/' . $subHead;
                } elseif ($record->account_head_id) {
                    $mainHead = getAcountHeadData($record->account_head_id);
                    $memberName = 'INDIRECT EXPENSE / ' . $mainHead;
                }
            } elseif ($value->type == 26) {
                $record = \App\Models\BankingLedger::where('id', $value->type_id)->first();
                if (isset($record->vendor_type)) {
                    if ($record->vendor_type == 0) {
                        $memberDetail = \App\Models\RentLiability::where('id', $record->vendor_type_id)->first();
                        $memberName = $memberDetail->owner_name;
                        $memberId = $memberDetail->employee_code;
                        // $memberId = $memberName->emp
                        // if($record->expense_account3){
                        //     $memberName = 'Expense/'.getAcountHead($record->expense_account3);
                        // }elseif($record->expense_account2){
                        //     $memberName = 'Expense/'.getAcountHead($record->expense_account2);
                        // }elseif($record->expense_account1){
                        //     $memberName = 'Expense/'.getAcountHead($record->expense_account1);
                        // }elseif($record->expense_account){
                        //     $memberName = 'Expense/'.getAcountHead($record->expense_account);
                        // }
                    } elseif ($record->vendor_type == 1) {
                        $EmployeeDetail = \App\Models\Employee::where('id', $record->vendor_type_id)->first();
                        $memberName = $EmployeeDetail->employee_name;
                        $memberId = $EmployeeDetail->employee_code;
                    } elseif ($record->vendor_type == 2) {
                        $MemberDetail = \App\Models\Member::where('id', $record->vendor_type_id)->first();
                        if (isset($MemberDetail->member_id)) {
                            $memberName = $MemberDetail->first_name . '' . $MemberDetail->last_name;
                            $memberId = $MemberDetail->associate_no;
                        }
                    } elseif ($record->vendor_type == 3 || $record->vendor_type == 4 || $record->vendor_type == 5) {
                        $memberName = \App\Models\Vendor::where('id', $record->vendor_type_id)->first();
                        $memberName = $memberName->name;
                    }
                } else {
                    $memberName = 'N/A';
                }
            }
            // elseif($value->type ==26)
            // {
            //   $memberAccount = getMemberData($value->member_id)->member_id;
            //   $memberName =
            // }
            $sno++;
            $startdate = '';
            $enddate = '';
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime(convertDate($value->created_at)));
            ;
            if ($memberId) {
                $val['member_id'] = $memberId;
            }
            // 			elseif(isset($value->member_id))
            // 			{
            // 				$val['member_id'] = getMemberData($value->member_id)->member_id;;
            // 			}
            else {
                $val['member_id'] = "N/A";
            }
            // 			if(isset($value->member_id))
            // 			{
            // 	           $val['member_name'] = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name;
            // 			}
            if ($memberName) {
                $val['member_name'] = $memberName;
            } else {
                $val['member_name'] = 'N/A';
            }
            if (isset($memberAccount)) {
                $val['account_number'] = $memberAccount;
            } else {
                $val['account_number'] = 'N/A';
            }
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
            } else {
                $credit = 0;
            }
            $val['cr'] = number_format((float) $credit, 2, '.', '');
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
            } else {
                $debit = 0;
            }
            $val['dr'] = number_format((float) $debit, 2, '.', '');
            $val['type'] = $type;
            if ($head_info->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            if ($value->payment_mode == 0) {
                $mode = 'CASH';
            } else if ($value->payment_mode == 1 || $value->payment_mode == 2) {
                $mode = 'BANK';
            } else if ($value->payment_mode == 3) {
                $mode = 'SSB';
            } else if ($value->payment_mode == 4) {
                $mode = 'AUTO TRANSFER';
            } else if ($value->payment_mode == 5) {
                $mode = 'BY LOAN AMOUNT';
            } else if ($value->payment_mode == 6) {
                $mode = 'JV';
            } else if ($value->payment_mode == 7) {
                $mode = 'CREDIT CARD';
            }
            $val['payment_mode'] = $mode;
            $rowReturn[] = $val;
        }
        //print_r($rowReturn);die();
        if ($request['export'] == 0) {
            return Excel::download(new BalanceSheetReportExportLoanAssetBranchWise($rowReturn), 'balanceSheetReportBranchWiseLoanAsset.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_loan_asset_branch_wise', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('balanceSheetReportBranchWiseLoanAsset.pdf');
        }
    }
    /*---------------------------------------------------------------*/
    /*
    public function vendorCategoryExport(Request $request)
    {
      $data=\App\Models\VendorCategory::where('status','!=',9)->orderby('id','DESC')->get();
      if($request['vendor_category'] == 0){
        return Excel::download(new VendorCategoryExport($data), 'VendorCategory.xlsx');
      }
      elseif ($request['vendor_category'] == 1) {
            $pdf = PDF::loadView('templates.admin.vendor_management.category.export',compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path().'_filename.pdf');
            return $pdf->download('VendorCategory.pdf');
        }
    }
    */
    public function vendorCategoryExport(Request $request)
    {
        if ($request['vendor_category'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/vendor_category_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/vendor_category_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\VendorCategory::where('status', '!=', 9);
        if ($request['status'] != '') {
            $status = $request['status'];
            $data = VendorCategory::where('status', $status)->orderby('id', 'DESC')->get();
        }
        if ($request['vendor_category'] == 0) {
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
                $val['Name'] = $row->name;
                $status = '';
                if ($row->status == 1) {
                    $status = 'Active';
                }
                if ($row->status == 0) {
                    $status = 'Inactive';
                }
                $val['STATUS'] = $status;
                $val['CREATED AT'] = date("d/m/Y", strtotime($row->created_at));
                $val['UPDATED AT'] = date("d/m/Y", strtotime($row->updated_at));
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
        } elseif ($request['vendor_category'] == 1) {
            $data = \App\Models\VendorCategory::where('status', '!=', 9)->orderby('id', 'DESC')->get();
            $pdf = PDF::loadView('templates.admin.vendor_management.category.export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('VendorCategory.pdf');
        }
    }
    public function get_fixed_assets_report(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $rowReturn = array();
        $debit = 0;
        $credit = 0;
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['party_name'] = $memberName;
            $val['voucher_number'] = $memberAccount;
            $val['transaction_type'] = $type;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $val['cr'] = 0;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $row->amount, 2, '.', '');
                ;
            } else {
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $amount = (float) $credit - (float) $debit;
            } else {
                $amount = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $amount;
            $val['amount'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new BalanceSheetReportExportFixedAssets($rowReturn), 'balanceSheetReportFixedAsset.xlsx');
        } else {
            $pdf = PDF::loadView('templates.admin.balance_sheet.export_fixed_assets', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('balanceSheetReportFixedAsset.pdf');
        }
    }
    public function balanceSheetReportBranchWiseRent(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $fileName = $accounthead->sub_head;
        foreach ($data as $value) {
            $data = $this->getCompleteDetail($value);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($value->entry_date));
            $val['owner_name'] = $memberName;
            $val['rent_type'] = $rentType;
            if ($value->payment_type == 'CR') {
                $credit = $value->amount;
                $val['cr'] = number_format((float) $value->amount, 2, '.', '');
                ;
            } else {
                $credit = 0;
                $val['cr'] = 0;
            }
            if ($value->payment_type == 'DR') {
                $debit = $value->amount;
                $val['dr'] = number_format((float) $value->amount, 2, '.', '');
                ;
            } else {
                $debit = 0;
                $val['dr'] = 0;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['amount'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new RentTypeExportSalaryReport($rowReturn), $fileName . '.xlsx');
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.balance_sheet.rent_type_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download($fileName . '.pdf');
        }
    }
    public function profitLossLatePaneltyExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $debit = 0;
        $credit = 0;
        $fileName = $accounthead->sub_head . '.xlsx';
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            $val['account_number'] = $memberAccount;
            $val['member_name'] = $memberName;
            $val['plan_name'] = $plan_name;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = 0.00;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new RentTypeExportLatePaneltyReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.late_panelty_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('ProfitLossSalaryReport.pdf');
        }
    }
    public function interestOnLoanExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $FileName = strtoupper($accounthead->sub_head) . '.xlsx';
        foreach ($data as $row) {
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $sno = $sno + 1;
            $credit = '0.00';
            $debit = '0.00';
            $branch_id = $row->id;
            $startdate = '';
            $enddate = '';
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            $val['account_number'] = $memberAccount;
            $val['member'] = $memberName;
            $val['plan_name'] = $plan_name;
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = 0.00;
            }
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
                $totalAmount = $totalAmount + $total;
            } else {
                $total = (float) $debit - (float) $credit;
                $totalAmount = $totalAmount + $total;
            }
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            ;
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new RentTypeExportInterestOnLoanReport($rowReturn), $FileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.interest_on_loan_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('RentTypeExportInterestOnLoanReport.pdf');
        }
    }
    public function ProfitLossFuelChargeExport(Request $request)
    {
        $record = $this->getHeadData(new AllHeadTransaction(), new AccountHeads(), $request);
        $data = $record[0]->get();
        $accounthead = $record[1];
        $sno = 0;
        $rowReturn = array();
        $totalAmount = 0;
        $fileName = strtoupper($accounthead->sub_head) . '.xlsx';
        foreach ($data as $row) {
            $credit = '0.00';
            $debit = '0.00';
            $data = $this->getCompleteDetail($row);
            $memberName = $data['memberName'];
            $memberAccount = $data['memberAccount'];
            $associate_name = $data['associate_name'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $rentType = $data['rent_type'];
            $sno = $sno + 1;
            $val['DT_RowIndex'] = $sno;
            $val['date'] = date("d/m/Y", strtotime($row->created_at));
            $val['associate_code'] = $data['associateno'];
            $val['member_id'] = $data['memberId'];
            $val['member_name'] = strtoupper($data['memberName']);
            if ($row->payment_type == 'CR') {
                $credit = $row->amount;
                $val['cr'] = number_format((float) $credit, 2, '.', '');
            } else {
                $val['cr'] = $credit;
            }
            //Debit Amount
            if ($row->payment_type == 'DR') {
                $debit = $row->amount;
                $val['dr'] = number_format((float) $debit, 2, '.', '');
            } else {
                $val['dr'] = $debit;
            }
            //Total Balance
            if ($accounthead->cr_nature == 1) {
                $total = (float) $credit - (float) $debit;
            } else {
                $total = (float) $debit - (float) $credit;
            }
            $totalAmount = $totalAmount + $total;
            $val['balance'] = number_format((float) $totalAmount, 2, '.', '');
            $rowReturn[] = $val;
        }
        if ($request['export'] == 0) {
            return Excel::download(new FuelChargeExportReport($rowReturn), $fileName);
        } else {
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.profit_loss.fuel_charge_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('FuelChargeExportReport.pdf');
        }
    }
    public function export_jv_list(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/JV_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/JV_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = JvJournals::has('company')
            ->with([
                'Branch:id,name',
                'company:id,name',
                'jvJournalHeads:id,credits_amount,debits_amount,jv_journal_id'
            ])
            ->where('status', 1);
        if ($request['export'] == 0) {
            if (isset($input["company_id"]) && $input["company_id"] > 0) {
                $data->where('company_id', $input["company_id"]);
            }
            if (isset($input["branch_id"]) && $input["branch_id"] > 0) {
                $data->where('branch_id', $input["branch_id"]);
            }
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
                $sno = $sno + 1;
                $val['S/N'] = $sno;
                $val['JOURNAL#'] = $row->jv_auto_id;
                $val['COMPANY'] = isset($row['company']->name) ? $row['company']->name : "";
                $val['BRANCH'] = $row['Branch']->name;
                $val['REFERENCE NUMBER'] = $row->reference;
                $totaldata = JvJournalHeads::where('jv_journal_id', $row->id)->sum('debits_amount');
                $amount = $totaldata;
                $val['DEBIT'] = number_format($amount, 2);
                $totaldata1 = JvJournalHeads::where('jv_journal_id', $row->id)->sum('credits_amount');
                $amount1 = $totaldata1;
                $val['CREDIT'] = number_format($amount1, 2);
                $val['CREATED'] = $createdAt = date("d/m/Y", strtotime(convertDate($row->created_at)));
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
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
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
            //return Excel::download(new AssociateCommissionBranchExport($data,$startDate,$endDate), 'branch_associatecommission.xlsx');
        } elseif ($request['export'] == 1) {
            //$data = $rowReturn;
            $data = JvJournals::with('Branch')->where('status', 1)->orderby('id', 'DESC')->get();
            $sno = 0;
            $rowReturn = array();
            $totalAmount = 0;
            foreach ($data as $row) {
                $sno = $sno + 1;
                $val['DT_RowIndex'] = $sno;
                $val['journal'] = $row->jv_auto_id;
                $val['branch'] = $row['Branch']->name;
                $val['reference_number'] = $row->reference;
                $val['notes'] = $row->notes;
                if ($row->status == 1) {
                    $status = 'Active';
                } else {
                    $status = 'Disable';
                }
                $val['status'] = $status;
                $totaldata = JvJournalHeads::where('jv_journal_id', $row->id)->sum('debits_amount');
                $amount = $totaldata;
                $val['debit'] = number_format($amount, 2);
                $totaldata1 = JvJournalHeads::where('jv_journal_id', $row->id)->sum('credits_amount');
                $amount1 = $totaldata1;
                $val['credit'] = number_format($amount1, 2);
                $val['created'] = $createdAt = date("d/m/Y", strtotime(convertDate($row->created_at)));
                $rowReturn[] = $val;
            }
            $data = $rowReturn;
            $pdf = PDF::loadView('templates.admin.jv_management.export_listing', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('JVJournalExportReport.pdf');
        }
    }

    public function export_bill_listing(Request $request)
    {
        $arrFormData = $request->all();
        $data = VendorBill::has('company')->select('id', 'bill_date', 'bill_number', 'status', 'transferd_amount', 'balance', 'payble_amount', 'branch_id', 'vendor_id', 'company_id')->with(['company:id,name,short_name', 'vendorBranchDetail:id,name,branch_code', 'vendor:id,name']);
        if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
            if ($request->vendor != '') {
                $data = $data->where('vendor_id', $request->vendor);
            }
            if (isset($request->branch_id) && $request->branch_id > 0) {
                $data = $data->where('branch_id', $request->branch_id);
            }
            if ($request->status != '') {
                $data = $data->where('status', '=', $request->status);
            }
            if (isset($request->company_id) && $request->company_id > 0) {
                $data = $data->where('company_id', $request->company_id);
            }
            if ($arrFormData['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                if ($arrFormData['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        }
        $data = $data->where('is_deleted', '!=', 1)->orderby('id', 'DESC')->get();
        $sno = 0;
        $rowReturn = [];
        foreach ($data as $key => $row) {
            $sno = $sno + 1;
            $val['date'] = date("d/m/Y", strtotime(($row->bill_date)));
            $val['ref_number'] = $row->bill_number;
            $val['company_name'] = $row['company']->name ?? "N/A";
            $val['branch_name'] = $row['vendorBranchDetail']->name ?? "N/A";
            $val['branch_code'] = $row['vendorBranchDetail']->branch_code ?? "N/A";
            $val['vendor_name'] = $row['vendor']->name;
            $status = 'N/A';
            if ($row->status == 0) {
                $status = 'UnPaid';
            } elseif ($row->status == 1) {
                $status = 'Partial';
            } elseif ($row->status == 2) {
                $status = 'Paid';
            }
            $val['status'] = $status;
            if (isset($row->due_date)) {
                $val['due_date'] = date("d/m/Y", strtotime(($row->due_date)));
            } else {
                $val['due_date'] = 'N/A';
            }
            $val['amount'] = number_format((float) $row->transferd_amount, 2, '.', '');
            $val['due_balance'] = number_format((float) $row->balance, 2, '.', '');
            $val['bill_amount'] = number_format((float) $row->payble_amount, 2, '.', '');
            $rowReturn[] = $val;
        }
        return Excel::download(new BillReportExport($rowReturn), 'BillReportExport.xlsx');
    }
    public function vendorExport(Request $request)
    {
        $data = Vendor::has('company')->with('company');
        $typee = $request->type;
        if ($typee == 3) {
            $typee = 1;
        }
        if ($request['vendorExport'] == '0') {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/vendor_list.csv";
            $fileName = env('APP_EXPORTURL') . "asset/vendor_list.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        // $data = Vendor::whereIn('type', [0, 1])->with('company');
        $data = $data->where('type', $typee);
        if ($request->status != '') {
            $status = $request->status;
            $data = $data->where('status', $status);
        }
        if ($request->company_id != '') {
            $company_id = $request->company_id;
            if ($company_id > 0) {
                $data = $data->where('company_id', $company_id);
            }
        }
        if ($request->category != '') {
            $category = $request->category;
            $data = $data->where('vendor_category', 'LIKE', '%' . $category);
        }
        if ($request->start_date != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request->start_date)));
            if ($request->end_date != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request->end_date)));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if ($request['vendorExport'] == 0) {
            $totalResults = $data->count('id');
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
            foreach ($results as $row) {
                $sno++;
                $val['Sr_no'] = $sno;
                $val['COMPANY NAME'] = $row->company->name;
                $val['NAME'] = $row->name;
                $val['VENDOR COMPANY NAME'] = $row->company_name;
                $val['NAME'] = $row->name;
                $val['EMAIL'] = $row->email_id;
                $val['MOBILE NO'] = $row->mobile_no;
                $category = explode(',', $row->vendor_category);
                $getName = VendorCategory::whereIn('id', $category)->get();
                $gt = '';
                foreach ($getName as $ind => $value) {
                    if (count($getName) != ($ind + 1)) {
                        $gt .= $value->name . ', ';
                    } else {
                        $gt .= $value->name;
                    }
                }
                $val['CATEGORY'] = $gt;
                $val['PEN CARD'] = $row->pan_number;
                $gst_type = '';
                if ($row->gst_type == 1) {
                    $gst_type = 'Registered regular Business';
                }
                if ($row->gst_type == 2) {
                    $gst_type = 'Registered Compositor';
                }
                if ($row->gst_type == 3) {
                    $gst_type = 'Unregistered Business';
                }
                if ($row->gst_type == 4) {
                    $gst_type = 'Overseas';
                }
                $val['GST TREATMENT'] = $gst_type;
                $val['GST NO'] = $row->gst_no;
                $status = 'Active';
                if ($row->status == 1) {
                    $status = 'Active';
                }
                if ($row->status == 0) {
                    $status = 'Inactive';
                }
                $val['STATUS'] = $status;
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
        } else {
            $data = $data->where('type', '1')->get();
            $rowReturn = array();
            foreach ($data as $key => $value) {
                $gst_type = '';
                if ($value->gst_type == 1) {
                    $gst_type = 'Registered regular Business';
                }
                if ($value->gst_type == 2) {
                    $gst_type = 'Registered Compositor';
                }
                if ($value->gst_type == 3) {
                    $gst_type = 'Unregistered Business';
                }
                if ($value->gst_type == 4) {
                    $gst_type = 'Overseas';
                }
                $status = 'Active';
                if ($value->status == 1) {
                    $status = 'Active';
                }
                if ($value->status == 0) {
                    $status = 'Inactive';
                }
                $category = explode(',', $value->vendor_category);
                $getName = VendorCategory::whereIn('id', $category)->get();
                $gt = '';
                foreach ($getName as $val) {
                    $gt .= $val->name . ',';
                }
                $val['gt'] = $gt;
                $val['name'] = $value->name;
                $val['cname'] = $value->company_name;
                $val['email'] = $value->email_id;
                $val['mobile'] = $value->mobile_no;
                $val['pan'] = $value->pan_number;
                $val['gst_type'] = $gst_type;
                $val['gst_no'] = $value->gst_no;
                $val['category'] = $gt;
                $val['status'] = $status;
                $rowReturn[] = $val;
            }
            // $pdf = PDF::loadView('templates.admin.bill_management.export_bill_listing', compact('rowReturn'))->setPaper('a4', 'landscape')->setWarnings(false);
            // $pdf->save(storage_path() . '_filename.pdf');
            // return $pdf->download('BillReportExport.pdf');
        }
    }
    public function export_expense_report(Request $request)
    {
        $data = Expense::with('branch')->orderBy('id', 'DESC')->get();
        if ($request['expense_report'] == 0) {
            return Excel::download(new ExportExpenseReport($data), 'expense_report.xlsx');
        } elseif ($request['expense_report'] == 1) {
            $pdf = PDF::loadView('templates.admin.expense.export_expense', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('expense.pdf');
        }
    }
    /*
    public function exportAppInactiveAssociate(Request $request){
         $data = Member::with('associate_branch')->where('member_id','!=','9999999')->where('is_associate',1)->where('associate_app_status',0);
        if(Auth::user()->branch_id>0){
             $id=Auth::user()->branch_id;
             $data=$data->where('associate_branch_id','=',$id);
        }
        $data=$data->orderby('associate_join_date','DESC')->get();
        if($request['export'] == 0){
            return Excel::download(new AssociateExportApp($data), 'appAssociateStatus.xlsx');
        }elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.associate_app.inactive_associate_export',compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path().'_filename.pdf');
            return $pdf->download('appAssociateStatus.pdf');
        }
    }
    */
    public function exportAppInactiveAssociate(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate-app-status.csv";
            $fileName = env('APP_EXPORTURL') . "asset/associate-app-status.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = Member::with('associate_branch')->where('id', '!=', '1')->where('is_associate', 1)->where('associate_app_status', 0);
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data = $data->where('associate_branch_id', '=', $id);
        }
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
            $totalResults = $data->orderby('associate_join_date', 'DESC')->count();
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
            foreach ($results as $row) {
                $sno++;
                $val['S/N'] = $sno;
                $val['JOIN DATE'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['BR NAME'] = $row['associate_branch']->name;
                $val['BR CODE'] = $row['associate_branch']->branch_code;
                $val['SO NAME'] = $row['associate_branch']->sector;
                $val['RO NAME'] = $row['associate_branch']->regan;
                $val['ZO NAME'] = $row['associate_branch']->zone;
                $val['CUSTOMER ID'] = $row->member_id;
                $val['ASSOCIATE ID'] = $row->associate_no;
                $val['ASSOCIATE NAME'] = $row->first_name . ' ' . $row->last_name;
                $val['EMAIL ID'] = $row->email;
                $val['MOBILE NO'] = $row->mobile_no;
                $val['SENIOR CODE'] = $row->associate_senior_code;
                $val['SENIOR NAME'] = getSeniorData($row->associate_senior_id, 'first_name') . ' ' . getSeniorData($row->associate_senior_id, 'last_name');
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
                if ($row->associate_app_status == 1) {
                    $app_status = 'Active';
                } else {
                    $app_status = 'Inactive';
                }
                $val['APP STATUS'] = $app_status;
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
        } elseif ($request['export'] == 1) {
            $pdf = PDF::loadView('templates.admin.associate_app.inactive_associate_export', compact('data'))->setPaper('a4', 'landscape')->setWarnings(false);
            $pdf->save(storage_path() . '_filename.pdf');
            return $pdf->download('appAssociateStatus.pdf');
        }
    }
    public function ExportCompanyBond(Request $request)
    {
        if ($request['company_bond'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/companyBond.csv";
            $fileName = env('APP_EXPORTURL') . "asset/companyBond.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = CompanyBound::has('companies')->with('fdSamraddhBankAccountId', 'fdSamraddhBank')->where('is_deleted', 0);
        if ($request->fd_no != '') {
            $FdNo = $request['fd_no'];
            $data = $data->where('fd_no', 'like', '%' . $FdNo . '%');
        }
        if (isset($request['company_id']) && $request['company_id'] > 0) {
            $data = $data->where('company_id', $request['company_id']);
        }
        if ($request['start_date'] != '') {
            $startDate = date('Y-m-d', strtotime(convertDate($request['start_date'])));
            if ($request['end_date'] != '') {
                $endDate = date('Y-m-d', strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        if (isset($request['fd_status']) && $request['fd_status'] != '') {
            $data = $data->where('status', $request['fd_status']);
        }
        if ($request['company_bond'] == 0) {
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
            $sno = 0;
            foreach ($results as $row) {
                $sno++;
                $val['S No.'] = $sno;
                $val['DATE'] = date("d/m/Y", strtotime($row->date));
                $val['CREATED AT'] = date("d/m/Y", strtotime($row->created_at));
                $val['BANK NAME'] = $row->bank_name;
                $val['FD NO.'] = $row->fd_no;
                $val['AMOUNT'] = $row->amount;
                if (isset($row->file)) {
                    $val['FILE'] = $row->file;
                } else {
                    $val['FILE'] = 'N/A';
                }
                $val['MATURITY DATE'] = date('d/m/Y', strtotime($row->maturity_date));
                $val['RECEIVE BANK'] = $row['fdSamraddhBank']->bank_name; //getSamraddhBank($row->rec_bank)->bank_name;
                $val['REMARK'] = $row->remark;
                $a = DB::select("CALL BankAccountNumber(" . $row->rec_bank_account . ")");
                $val['RECEIVE BANK ACCOUNT'] = $a[0]->account_no;
                if ($row->status == 1) {
                    $status = 'Close';
                } elseif ($row->status == 0) {
                    $status = 'Active';
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
    public function ExportCompanyBondInterestTransaction(Request $request)
    {
        if ($request['company_bond_transaction'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/company_bond_transaction.csv";
            $fileName = env('APP_EXPORTURL') . "asset/company_bond_transaction.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\CompanyBoundTransaction::with('company_bounds')->where('bound_id', $request->bound_id);
        if ($request['company_bond_transaction'] == 0) {
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
            $sno = 0;
            foreach ($results as $row) {
                $type = 'N/A';
                if ($row->interest_type == 1) {
                    $type = 'Interest On FDR';
                } elseif ($row->interest_type == 0) {
                    $type = 'Bank Account';
                }
                $sno++;
                $val['S No.'] = $sno;
                $val['TRANSACTION DATE'] = date("d/m/Y", strtotime($row->date));
                $val['BANK NAME'] = $row['company_bounds']->bank_name;
                $val['TRANSACTION TYPE'] = $type;
                $val['FD NO'] = $row['company_bounds']->fd_no;
                $val['TDS AMOUNT'] = $row->tds_amount;
                $val['RECEIVED AMOUNT'] = $row->interest_amount;
                if (isset($row->tds_amount)) {
                    $tdsAmount = $row->tds_amount;
                } else {
                    $tdsAmount = 0;
                }
                $val['TOTAL AMOUNT'] = $row->interest_amount + $tdsAmount;
                $val['WITHDRAWAL AMOUNT'] = $row->withdrawal_amount;
                if (isset($row->rec_bank)) {
                    $val['RECEIVE BANK'] = getSamraddhBank($row->rec_bank)->bank_name;
                } else {
                    $val['RECEIVE BANK'] = 'N/A';
                }
                if (isset($row->rec_bank_account)) {
                    $a = DB::select("CALL BankAccountNumber(" . $row->rec_bank_account . ")");
                    $val['RECEIVE BANK ACCOUNT NO.'] = $a[0]->account_no;
                } else {
                    $val['RECEIVE BANK ACCOUNT NO.'] = 'N/A';
                }
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
    public function loanTransactionExportList(Request $request)
    {
        if ($request['loan_transaction_export'] == 0) {
            $data = Cache::get('loan_transaction_data');
            $count = Cache::get('loan_transaction_data_count');
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
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
            //dd($totalResults);
            $results = $data;
            //dd($results);
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
                //
                $sno++;
                $val['S/N'] = $sno;
                $val['Created Date'] = date("d/m/Y", strtotime($row->created_at));
                $val['BR Name'] = $row['loanBranch']->name;
                $val['BR Code'] = $row['loanBranch']->branch_code;
                $val['SO Name'] = $row['loanBranch']->sector;
                $val['RO Name'] = $row['loanBranch']->regan;
                $val['ZO Name'] = $row['loanBranch']->zone;
                //	$val['Member Id']=$row['loan_member']->member_id;
                $val['Account No'] = $row->account_number;
                $member = Member::where('id', $row->associate_id)->first(['id', 'first_name', 'last_name']);
                if (!empty($member)) {
                    $associate_name = $member->first_name . ' ' . $member->last_name;
                } else {
                    $associate_name = 'N/A';
                }
                //dd($row['payment_mode']);
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
                $val['Loan Type'] = $plan_name;
                $tenure = '';
                if (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 1) {
                    $tenure = $row['member_loan']['emi_period'] . ' Months';
                } elseif (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 2) {
                    $tenure = $row['member_loan']['emi_period'] . ' Weeks';
                } elseif (isset($row['member_loan']['emi_option']) && $row['member_loan']['emi_option'] == 3) {
                    $tenure = $row['member_loan']['emi_period'] . ' Days';
                }
                $val['Tenure'] = $tenure;
                switch ($row['payment_mode']) {
                    case '1':
                        $payment_mode = 'Cheque';
                        break;
                    case '2':
                        $payment_mode = 'DD';
                        break;
                    case '3':
                        $payment_mode = 'Online Ttransaction';
                        break;
                    case '4':
                        $payment_mode = 'By saving account';
                        break;
                    case '5':
                        $payment_mode = 'From loan amount';
                        break;
                    default:
                        $payment_mode = 'Cash';
                }
                if ($row->loan_type == 3) {
                    if (isset($row['loan_member'])) {
                        $val['Member Id'] = $row['group_member_loan']['loanMember']->member_id;
                        $val['Member(Account Holder Name)'] = $row['group_member_loan']['loanMember']->first_name . ' ' . $row['group_member_loan']['loanMember']->last_name;
                    } else {
                        $val['Member Id'] = '';
                        $val['Member(Account Holder Name)'] = '';
                    }
                } else {
                    if (isset($row['loan_member'])) {
                        $val['Member Id'] = $row['loan_member']->member_id;
                        $val['Member(Account Holder Name)'] = $row['loan_member']->first_name . ' ' . $row['loan_member']->last_name;
                    } else {
                        $val['Member Id'] = '';
                        $val['Member(Account Holder Name)'] = '';
                    }
                }
                $val['Emi Amount'] = $row->deposit;
                $loan_sub_type = $row->loan_sub_type;
                if ($loan_sub_type == 0) {
                    $loan_sub_type = 'EMI';
                } else {
                    $loan_sub_type = 'Late Penalty';
                }
                $val['Transaction Type'] = $loan_sub_type;
                if (isset($row['loanMemberAssociate'])) {
                    $val['Associate Code'] = $row['loanMemberAssociate']->associate_no;
                } else {
                    $val['Associate Code'] = 'N/A';
                }
                if (isset($row['loanMemberAssociate'])) {
                    $val['Associate Name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                } else {
                    $val['Associate Name'] = 'N/A';
                }
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
    public function VendorTransactionExport(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/vendoremployeetransaction.csv";
        $fileName = '/home/mysamraddh/public_html/report/vendoremployeetransaction.csv';
        header("Content-type: text/csv");
        // $data =\App\Models\EmployeeLedger::with('employee_salary')->where('employee_id',$request->vendor_id)->where('is_deleted',0)->whereIn('type',['1','3','6']);
        // $accountheadId = 61;
        $totalAmountssssss = 0;
        if ($request->vendor_type == 0) {
            $data = VendorTransaction::with('branch_detail', 'bill_detail')->where('vendor_id', $request->vendor_id)->where('is_deleted', '!=', 1);
        } elseif ($request->vendor_type == 1) {
            $data = \App\Models\CustomerTransaction::with('branch_detail', 'bill_detail')->where('customer_id', $request->vendor_id)->where('is_deleted', 0);
        } elseif ($request->vendor_type == 3) {
            $data = \App\Models\EmployeeLedger::with('branch_detail')->with('employee_salary')->where('employee_id', $request->vendor_id)->where('is_deleted', 0);
        } elseif ($request->vendor_type == 2) {
            $data = \App\Models\RentLiabilityLedger::with('rentPayment')->where('rent_liability_id', $request->vendor_id)->where('is_deleted', 0);
        } elseif ($request->vendor_type == 4) {
            $data = AssociateTransaction::where('associate_id', $request->vendor_id)->where('is_deleted', 0);
        }
        $data1 = $data->orderby('created_at', 'ASC')->get();
        $totalResults = $data1->count();
        $results = $data->orderby('created_at', 'ASC')->offset($start)->limit($limit)->get();
        if ($request->vendor_type == 0) {
            $dataCR = VendorTransaction::with('branch_detail', 'bill_detail')->where('vendor_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 140;
        } elseif ($request->vendor_type == 1) {
            $dataCR = \App\Models\CustomerTransaction::where('customer_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 142;
        } elseif ($request->vendor_type == 3) {
            $dataCR = \App\Models\EmployeeLedger::with('employee_salary')->where('employee_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 61;
        } elseif ($request->vendor_type == 2) {
            $dataCR = \App\Models\RentLiabilityLedger::with('rentPayment')->where('rent_liability_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 60;
        } elseif ($request->vendor_type == 4) {
            $dataCR = AssociateTransaction::where('type_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 141;
        }
        $accounthead = AccountHeads::where('head_id', $accountheadId)->first();
        $dataCR = $dataCR->offset($start)->limit($limit)->get();
        if ($request->vendor_type == 0 && $request->vendor_type == 1 && $request->vendor_type == 4) {
            if ($request->vendor_type == 2 || $request->vendor_type == 3) {
                $totalCR = $dataCR->where('payment_type', 'CR')->sum('deposit');
                $totalDR = $dataCR->where('payment_type', 'DR')->sum('withdrawal');
                $totalAmountssssss = $totalCR - $totalDR;
            } else {
                $totalDR = $dataCR->where('payment_type', 'DR')->sum('amount');
                $totalCR = $dataCR->where('payment_type', 'CR')->sum('amount');
                $totalAmountssssss = $totalCR - $totalDR;
            }
        }
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
            $val['DATE'] = date("d/m/Y", strtotime($row->created_at));
            if (isset($row->description)) {
                $description = $row->description;
            } else {
                $description = 'N/A';
            }
            $val['Particulars'] = $description;
            if ($request->vendor_type == 3) {
                $val['Type'] = '';
                if ($row->type == 1 || $row->type == 3) {
                    $val['Type'] = 'Salary Payment';
                }
                if ($row->type == 2) {
                    $val['Type'] = 'Advance Salary Payment';
                }
                if ($row->type == 4) {
                    $val['Type'] = 'Panel Interest';
                }
                if ($row->type == 5) {
                    $val['Type'] = 'TA Advance Payment';
                }
                if ($row->type == 6) {
                    $val['Type'] = 'Salary Ledger Create';
                }
                if ($row->type == 7) {
                    $val['Type'] = 'Advance Salary Adjusted ';
                }
                if (isset($row['employee_salary']->actual_transfer_amount)) {
                    $actual_transfer_amount = $row['employee_salary']->actual_transfer_amount;
                } else {
                    $actual_transfer_amount = '0.00';
                }
                $val['Total Payable Amount'] = '0.00';
                if ($row->type == 1 || $row->type == 3 || $row->type == 6 || $row->type == 7) {
                    $val['Total Payable Amount'] = $actual_transfer_amount;
                }
                $val['Transferred Amount'] = '0.00';
                if ($row->type == 1 || $row->type == 7) {
                    $val['Transferred Amount'] = $row->withdrawal;
                }
                if ($row->type == 2 || $row->type == 5) {
                    $val['Transferred Amount'] = $row->deposit;
                }
                $val['Received Amount'] = '0.00';
                if ($row->type == 4) {
                    $val['Received Amount'] = $row->deposit;
                }
                $val['Payment Type'] = $row->payment_type;
            }
            if ($request->vendor_type == 2) {
                if (isset($row['employee_salary']->total_salary)) {
                    $total_salary = $row['employee_salary']->total_salary;
                } else {
                    $total_salary = '0.00';
                }
                $val['total_salary'] = $total_salary;
                if (isset($row['employee_salary']->incentive_bonus)) {
                    $incentive_bonus = $row['employee_salary']->incentive_bonus;
                } else {
                    $incentive_bonus = '0.00';
                }
                $val['incentive_bonus'] = $incentive_bonus;
                if (isset($row['employee_salary']->deduction)) {
                    $deduction = $row['employee_salary']->deduction;
                } else {
                    $deduction = '0.00';
                }
                $val['deduction'] = $deduction;
                if (isset($row['rentPayment']->actual_transfer_amount)) {
                    $actual_transfer_amount = $row['rentPayment']->actual_transfer_amount;
                } else {
                    $actual_transfer_amount = '0.00';
                }
                $val['total_paybal'] = '0.00';
                if ($row->type == 2 || $row->type == 4) {
                    $val['total_paybal'] = $actual_transfer_amount;
                }
                if ($row->type == 1 || $row->type == 3 || $row->type == 6 || $row->type == 7) {
                    $val['total_paybal'] = $actual_transfer_amount;
                }
                $val['transffered_salary'] = '0.00';
                if ($row->type == 1 || $row->type == 7) {
                    $val['transffered_salary'] = $row->withdrawal;
                }
                if ($row->type == 2 || $row->type == 5) {
                    $val['transffered_salary'] = $row['rentPayment']->transferred_amount;
                }
                $val['recived'] = '0.00';
                if ($row->type == 2) {
                    $val['recived'] = $row['rentPayment']->transferred_amount;
                }
                if ($row->type == 4) {
                    $val['recived'] = $row->deposit;
                }
                $type = '';
                if ($row->type == 1 || $row->type == 3) {
                    $type = 'Salary Payment';
                }
                if ($row->type == 2) {
                    $type = 'Advance Rent Payment';
                }
                if ($row->type == 4) {
                    $type = 'Rent Ledger';
                }
                if ($row->type == 5) {
                    $type = 'TA Advance Payment';
                }
                if ($row->type == 6) {
                    $type = 'Salary Ledger Create';
                }
                if ($row->type == 7) {
                    $type = 'Advance Salary Adjusted ';
                }
                $val['type'] = $type;
                $val['Payment Type'] = $row->payment_type;
            }
            $payment_mode = '';
            if ($row->payment_mode == 0) {
                $payment_mode = 'Cash';
            }
            if ($row->payment_mode == 1) {
                $payment_mode = 'Cheque';
            }
            if ($row->payment_mode == 2) {
                $payment_mode = 'Online';
            }
            if ($row->payment_mode == 3) {
                if ($row->type == 6) {
                    $payment_mode = 'JV';
                } else {
                    $payment_mode = 'SSB';
                }
            }
            $val['Payment Mode'] = $payment_mode;
            if ($request->vendor_type == 0 || $request->vendor_type == 1 || $request->vendor_type == 4) {
                // if(isset($row['bill_detail']->bill_number))
                // {
                //     $bill =  $row['bill_detail']->bill_number;
                // }
                // else{
                //     $bill = 'N/A';
                // }
                // $val['Bill Number'] = $bill;
                if (isset($row['branch_detail']->name)) {
                    $branch = $row['branch_detail']->name;
                } else {
                    $branch = 'N/A';
                }
                $val['Branch Name'] = $branch;
                if (isset($row['branch_detail']->branch_code)) {
                    $branch_code = $row['branch_detail']->branch_code;
                } else {
                    $branch_code = 'N/A';
                }
                $val['Branch Code'] = $branch_code;
                if ($row->payment_type == 'DR') {
                    if ($request->vendor_type == 2 || $request->vendor_type == 3) {
                        $dr = number_format((float) $row->withdrawal, 2, '.', '');
                    } else {
                        $dr = number_format((float) $row->amount, 2, '.', '');
                    }
                } else {
                    $dr = '0';
                }
                $val['DR'] = $dr;
                if ($row->payment_type == 'CR') {
                    if ($request->vendor_type == 2 || $request->vendor_type == 3) {
                        $cr = number_format((float) $row->deposit, 2, '.', '');
                    } else {
                        $cr = number_format((float) $row->amount, 2, '.', '');
                    }
                } else {
                    $cr = '0';
                }
                $val['CR'] = $cr;
                if ($request->vendor_type == 0 && $row->type != 5) {
                    if ($accounthead->cr_nature == 1) {
                        $total = (float) $cr - (float) $dr;
                    } else {
                        $total = (float) $dr - (float) $cr;
                    }
                    $totalAmountssssss = $totalAmountssssss + $total;
                }
                if ($request->vendor_type == 0 && $row->type != 5) {
                    $val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
                } else {
                    $val['Balance'] = '';
                }
                if ($row->payment_type == 'CR') {
                    $payment = 'Credit';
                } else {
                    $payment = 'Debit';
                }
                $val['Payment Type'] = $payment;
                $bank_detail = getSamraddhBank($row->bank_id);
                $bank_detail1 = '';
                if ($bank_detail) {
                    $bank_detail1 = $bank_detail->bank_name;
                }
                $val['Bank Detail'] = $bank_detail1;
            }
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


    public function export(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('headLogData' . $token);
        $count = Cache::get('headLogDataLogData_count' . $token);

        $input = $request->all();

        $shortNames = [];


        foreach ($data as $log) {

            $companyIds = json_decode($log->company_id, true);


            $companies = Companies::whereIn('id', $companyIds)->pluck('short_name')->toArray();
            $shortNames[$log->id] = implode(', ', $companies);
        }

        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/head_logs.csv";
        $fileName = env('APP_EXPORTURL') . "/asset/head_logs.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");

        // if ($request['cron_export'] == 0) {
        $totalResults = $count;
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
        $status = [
            1 => 'Start',
            2 => 'InProgress',
            3 => 'Success',
            4 => 'Failed',
        ];

        foreach ($data as $row) {
            $oldValueData = json_decode($row->old_value);
            $subHead = isset($oldValueData->sub_head) ? $oldValueData->sub_head : '';
            $sno++;
            $val['S. No.'] = $sno;
            $val['Head Name'] = isset($subHead) ? $subHead : "N/A";
            $val['Type'] = $row->type == 1 ? 'Create' : ($row->type == 2 ? 'Assign' : ($row->type == 3 ? 'Grouping' : ($row->type == 4 ? 'Edit' : 'Unknown')));
            $val['Description'] = isset($row->description) ? $row->description : "N/A";
            $val['Parent'] = isset($row->parent->sub_head) ? $row->parent->sub_head : "N/A";
            $val['Companies'] = $shortNames[$row->id] ?? '';
            $val['Created By'] = $row->created_by;

            $val['Created At'] = $row->created_at;


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
    }
    /**
     *
     * Assocaite exception by durgesh
     */
    public function exportCommision(Request $request)
    {
        $token = session()->get('_token');
        $count = Cache::get('Associate_Exception_List_count' . $token);
        $results = Cache::get('Associate_Exception_List' . $token);
        $branchdetails = \App\Models\Branch::pluck('name', 'id');
        $admindetails = \App\Models\Admin::pluck('username', 'id');
        if ($request['member_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/associate_exception_list_" . $token . ".csv";
            $fileName = env('APP_EXPORTURL') . "/asset/associate_exception_list_" . $token . ".csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        /*
        $data = AssociateException::has('seniorData')->with(['seniorData' => function ($q) {
            $q->select(['id', 'first_name', 'last_name', 'associate_no', 'current_carder_id']);
        }])->where('status',1);
        if (($request['associate_code'] != '') && ($request['associate_code'] > 0)) {
            $associate_no = $input['associate_code'];
            $data = $data->whereHas('seniorData', function ($query) use ($associate_no) {
                $query->where('associate_no', $associate_no);
            });
        }
        if (!empty($input['name'])) {
            $associateName = $input['name'];
            $data = $data->whereHas('seniorData', function ($query) use ($associateName) {
                $query->where(function ($subQuery) use ($associateName) {
                    $subQuery->where(function ($nameQuery) use ($associateName) {
                        $nameQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $associateName . '%']);
                    })->orWhere('first_name', 'LIKE', '%' . $associateName . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $associateName . '%');
                });
            });
        }
        if ($request['member_export'] == 0) {
            $totalResults = $data->orderby('created_at', 'DESC')->count();
            $results = $data->orderby('created_at', 'DESC')
                ->offset($start)
                ->limit($limit)
                ->get([
                    'id',
                    'created_at',
                    'updated_at',
                    'status',
                    'associate_id',
                    'created_by',
                    'created_by_id',
                    'fuel_status',
                    'commission_status',
                    'reason',
                    'is_cron'
                ]);
            */
        $totalResults = $count;
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
        foreach (array_slice($results, $start, $limit) as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['CREATED AT'] = date("d/m/Y", strtotime($row['created_at']));
            $val['ASSOCIATE NAME'] = $row['associate_name'];
            $val['ASSOCIATe CODE'] = $row['associate_code'];
            $val['CARDER NAME'] = $row['cardername'];
            $val['CREATED BY '] = $row['createdby'];
            $val['USER NAME'] = $row['createdbyname'];
            $val['REASON'] = $row['reason'];
            $val['FUEL STATUS'] = $row['fuel_status'];
            $val['COMMISSION STATUS'] = $row['commission_status'];
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
        // } elseif ($request['cheque_export'] == 1) {
        //     dd('next process hold');
        // }
    }
}


