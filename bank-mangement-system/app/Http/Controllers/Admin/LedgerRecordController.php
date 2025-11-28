<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validator;

use Illuminate\Support\Facades\Cache;

use App\Models\AccountHeads;

use App\Models\Employee;

use App\Models\Companies;

use App\Models\EmployeeLedger;

use App\Models\Vendor;

use App\Models\Member;

use App\Models\RentLiability;

use App\Models\RentLiabilityLedger;

use App\Models\RentPayment;

use App\Models\Memberinvestments;

use App\Models\AssociateTransaction;

use App\Models\ShareHolder;

use App\Models\VendorTransaction;

use App\Models\CustomerTransaction;

use App\Models\AllHeadTransaction;

use App\Models\TransactionType;

use App\Models\MemberTransaction;

use App\Models\BranchDaybookReference;

use App\Models\BranchDaybook;

use App\Models\Branch;

use App\Models\EmployeeSalary;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use Session;

use Image;

use Redirect;

use URL;

use DB;

use App\Services\Email;

use App\Services\Sms;

use Illuminate\Support\Facades\Schema;

class LedgerRecordController extends Controller
{
    public function __construct()
    {
        // check user login or not
        $this->middleware("auth");
    }

    public function index()
    {
        if (check_my_permission(Auth::user()->id, "202") != "1") {
            return redirect()->route("admin.dashboard");
        }

        $x = Employee::where("status", 1)
            ->where("is_employee", "1")
            ->get();
        $data["title"] = "Ledger Report";
        $data["branch"] = Branch::where("status", 1)->get();
        $data["heads"] = AccountHeads::where("labels", 1)
            ->where("status", 0)
            ->get();
        $data["subHeads"] = AccountHeads::where("labels", ">", 1)
            ->where("labels", "<", 5)
            ->where("status", 0)
            ->get();
        $data["employee"] = Employee::where("status", 1)
            ->where("is_employee", "1")
            ->get();
        $data["member"] = Member::where("is_block", "0")
            ->where("status", 1)
            ->limit(20)
            ->get();
        $data["associate"] = Member::where("is_block", "0")
            ->where("status", 1)
            ->where("is_associate", "1")
            ->limit(20)
            ->get();
        $data["rent_owner"] = RentLiability::where("status", 0)->get();
        $data["director"] = ShareHolder::where("type", 19)->get();
        $data["share_holder"] = ShareHolder::where("type", 15)->get();
        $data["vendors"] = Vendor::where("type", "0")->get();
        $data["customers"] = Vendor::where("type", "1")->get();

        return view("templates.admin.ledger_listing.ledger_record", $data);
    }

    public function ledgerRecordListing(Request $request)
    {
        $arrFormData = [];
        if (!empty($_POST["searchform"])) {
            foreach ($_POST["searchform"] as $frm_data) {
                $arrFormData[$frm_data["name"]] = $frm_data["value"];
            }
        }
        $token = session()->get("_token");
        if (isset($arrFormData["ledger_type"])) {
            if ($arrFormData["ledger_type"] == "1") {
                $data1 = Member::select("id", "member_id")
                    ->where("id", $arrFormData["member_id"])
                    ->with([
                        "ledgerListing" => function ($query) {
                            $query->with("branches:id,name");
                        },
                    ]);

                $data = $data1->first();

                $data = $data["ledgerListing"];
            } elseif ($arrFormData["ledger_type"] == "2") {
                $data = EmployeeLedger::select("employee_ledgers.*")
                    ->where("employee_id", $arrFormData["employee_id"])
                    ->when(
                        $arrFormData["company_id"] !== "" &&
                        $arrFormData["company_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("company_id", $arrFormData["company_id"]);
                        }
                    )
                    ->when(
                        $arrFormData["branch_id"] !== "" &&
                        $arrFormData["branch_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("branch_id", $arrFormData["branch_id"]);
                        }
                    );
            } elseif ($arrFormData["ledger_type"] == "3") {
                $data = AssociateTransaction::select("associate_transaction.*");
            } elseif ($arrFormData["ledger_type"] == "4") {
                $data = RentLiabilityLedger::with("rentLib")
                    ->where("id", $arrFormData["rent_owner_id"])
                    ->when(
                        $arrFormData["company_id"] !== "" &&
                        $arrFormData["company_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("company_id", $arrFormData["company_id"]);
                        }
                    )
                    ->when(
                        $arrFormData["branch_id"] !== "" &&
                        $arrFormData["branch_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->whereHas("rentLib", function ($sq) use ($arrFormData) {
                                $sq->where(
                                    "branch_id",
                                    $arrFormData["branch_id"]
                                );
                            });
                        }
                    );
            } elseif ($arrFormData["ledger_type"] == "5") {
                $data = VendorTransaction::select("vendor_transaction.*")
                    ->where("vendor_id", $arrFormData["vendor_id"])
                    ->when(
                        $arrFormData["company_id"] !== "" &&
                        $arrFormData["company_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("company_id", $arrFormData["company_id"]);
                        }
                    )
                    ->when(
                        $arrFormData["branch_id"] !== "" &&
                        $arrFormData["branch_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("branch_id", $arrFormData["branch_id"]);
                        }
                    );
            } elseif ($arrFormData["ledger_type"] == "6") {
                // dd('asd');
                $data = BranchDaybook::where("type", 15)
                    ->whereHas("shareHolder", function ($q) use ($arrFormData) {
                        $q->where("id", $arrFormData["director_id"]);
                    })
                    ->when(
                        $arrFormData["company_id"] !== "" &&
                        $arrFormData["company_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("company_id", $arrFormData["company_id"]);
                        }
                    )
                    ->when(
                        $arrFormData["branch_id"] !== "" &&
                        $arrFormData["branch_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("branch_id", $arrFormData["branch_id"]);
                        }
                    );
            } elseif ($arrFormData["ledger_type"] == "7") {
                $data = BranchDaybook::where("type", 16)
                    ->whereHas("shareHolder", function ($q) use ($arrFormData) {
                        $q->where("id", $arrFormData["share_holder_id"]);
                    })
                    ->when(
                        $arrFormData["company_id"] !== "" &&
                        $arrFormData["company_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("company_id", $arrFormData["company_id"]);
                        }
                    )
                    ->when(
                        $arrFormData["branch_id"] !== "" &&
                        $arrFormData["branch_id"] !== "0",
                        function ($q) use ($arrFormData) {
                            $q->where("branch_id", $arrFormData["branch_id"]);
                        }
                    );
            } elseif ($arrFormData["ledger_type"] == "8") {
                $data = CustomerTransaction::select("customer_transaction.*");
            } else {
                $data = AllHeadTransaction::with("branch", "member");
            }
        } else {
            $data = AllHeadTransaction::with("branch", "member");
        }
        // pd($data->get()->toArray());
        // pd($ledgerListing['ledgerListing']->toArray());

        if (
            isset($arrFormData["is_search"]) &&
            $arrFormData["is_search"] == "yes"
        ) {
            if ($arrFormData["start_date"] != "") {
                $startDate = $arrFormData["start_date"];
                $endDate = $arrFormData["end_date"];
                $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                $data = $data->whereBetween(\DB::raw("DATE(created_at)"), [
                    $startDate,
                    $endDate,
                ]);
                // dd($data->count());
            }

            // dd($data->count());
            $dataCR = $data;
            $count = $data->count();
            $export = $data;
            $export = $export->orderby("id", "desc")->get();
            if ($_POST["start"] == 0) {
                Cache::put("data_ledger_listing" . $token, $export);
            }
            if (isset($arrFormData["ledger_type"])) {
                if ($arrFormData["ledger_type"] == "10") {
                    $data = $data
                        ->orderby("all_head_transaction.created_at", "asc")
                        ->offset($_POST["start"])
                        ->limit($_POST["length"])
                        ->select("all_head_transaction.*")
                        ->get();
                } else {
                    $data = $data
                        ->orderby("id", "asc")
                        ->offset($_POST["start"])
                        ->limit($_POST["length"])
                        ->get();
                }
            } else {
                $data = $data
                    ->orderby("id", "asc")
                    ->offset($_POST["start"])
                    ->limit($_POST["length"])
                    ->get();
            }

            if ($arrFormData["ledger_type"] == "1") {
                $totalCount = Member::count("id");
            } elseif ($arrFormData["ledger_type"] == "2") {
                $totalCount = EmployeeLedger::count("id");
            } elseif ($arrFormData["ledger_type"] == "4") {
                $totalCount = RentLiabilityLedger::count("id");
            } elseif ($arrFormData["ledger_type"] == "5") {
                $totalCount = VendorTransaction::count("id");
            } elseif ($arrFormData["ledger_type"] == "8") {
                $totalCount = CustomerTransaction::count("id");
            } elseif ($arrFormData["ledger_type"] == "3") {
                $totalCount = AssociateTransaction::count("id");
            } else {
                $totalCount = AllHeadTransaction::count("id");
            }

            // $count = $totalCount;
            if ($_POST["start"] == 0) {
                Cache::put("data_ledger_listing_count_" . $token, $count);
            }
            // Now For totals
            $totalAmountssssss = 0;
            if (
                isset($arrFormData["ledger_type"]) &&
                $arrFormData["ledger_type"] != ""
            ) {
                if ($_POST["pages"] == 1) {
                    $totalAmount = 0;
                } else {
                    $totalAmount = $_POST["total"];
                }
                if ($_POST["pages"] == "1") {
                    $length = $_POST["pages"] * $_POST["length"];
                } else {
                    $length = ($_POST["pages"] - 1) * $_POST["length"];
                }
                $dataCR = $dataCR
                    ->offset(0)
                    ->limit($length)
                    ->get();

                if (
                    $arrFormData["ledger_type"] == "6" ||
                    $arrFormData["ledger_type"] == "7" ||
                    $arrFormData["ledger_type"] == "5" ||
                    $arrFormData["ledger_type"] == "8" ||
                    $arrFormData["ledger_type"] == "3"
                ) {
                    $totalDR = $dataCR
                        ->where("payment_type", "DR")
                        ->sum("amount");
                    $totalCR = $dataCR
                        ->where("payment_type", "CR")
                        ->sum("amount");
                } else {
                    $totalCR = $dataCR
                        ->where("payment_type", "CR")
                        ->sum("deposit");

                    $totalDR = $dataCR
                        ->where("payment_type", "DR")
                        ->sum("withdrawal");

                }
                $totalAmountssssss = $totalCR - $totalDR;
                if ($_POST["pages"] == "1") {
                    $totalAmountssssss = 0;
                }
            }
            if ($_POST["start"] == 0) {
                Cache::put("data_ledger_listing_totalAmountssssss" . $token, $totalAmountssssss);
            }
            $sno = $_POST["start"];
            $rowReturn = [];
            $tranTypes = [
                11 => "Member Register(MI Charge)",
                12 => "Member Register(STN Charge)",
                13 => "Member JV Entry",
                14 => "Member TDS on Interest",
            ];
            $typeMappings = [
                21 => "Associate Commission",
                22 => "Associate JV Commission",
                23 => "Associate JV Fuel Charge",
            ];
            $transactionMappings = [
                2 => [
                    21 => "Associate Commission",
                    22 => "Associate JV Commission",
                    23 => "Associate JV Fuel Charge",
                ],
                3 => [
                    30 => "R-Investment Register",
                    31 => "Account opening",
                    32 => "Renew",
                    33 => "Passbook Print",
                    38 => "JV Entry",
                    39 => "JV Stationary Charge",
                    311 => "JV Passbook Print",
                    312 => "JV Certificate Print",
                    "default" => "Investment",
                ],
                4 => [
                    41 => "Account opening",
                    42 => "Deposit",
                    43 => "Withdraw",
                    44 => "Passbook Print",
                    45 => "Commission",
                    46 => "Fuel Charge",
                    412 => "SSB JV Entry",
                    413 => "SSB JV Passbook Print",
                    414 => "SSB JV Certificate Print",
                    "default" => "Saving Account",
                ],
                5 => [
                    51 => "Loan",
                    52 => "Loan",
                    53 => "Loan",
                    57 => "Loan",
                    54 => "Group Loan",
                    55 => "Group Loan",
                    56 => "Group Loan",
                    58 => "Group Loan",
                    511 => "Loan JV Loan",
                    512 => "Loan JV Group Loan",
                    513 => "Loan JV Loan Panelty",
                    514 => "Loan JV Group Loan Panelty",
                    515 => "Loan JV Loan Emi",
                    516 => "Loan JV Group Loan Emi",
                ],
            ];
            $subTypeMappings = [
                61 => "Employee Salary",
                62 => "Employee JV Salary",
                70 => "Branch Cash",
                71 => "Branch Cheque",
                72 => "Branch Online",
                73 => "Branch SSB",
                80 => "Bank Cash",
                81 => "Bank Cheque",
                82 => "Bank Online",
                83 => "Bank SSB",
                101 => "Rent - Ledger",
                102 => "Rent - Payment",
                103 => "Rent - Security",
                104 => "Rent - Advance",
                105 => "Rent - JV Ledger",
                106 => "Rent - JV Security",
                121 => "Salary - Ledger",
                122 => "Salary - Transfer",
                123 => "Salary - Advance",
                131 => "Demand Advice - Fresh Expense",
                132 => "Demand Advice - Ta Advance",
                133 => "Demand Advice - Maturity",
                134 => "Demand Advice - Prematurity",
                135 => "Demand Advice - Death Help",
                136 => "Demand Advice - Death Claim",
                137 => "Demand Advice - EM",
                138 => "Demand Advice - JV Ta Advance",
                141 => "Voucher - Director",
                142 => "Voucher - ShareHolder",
                143 => "Voucher - Penal Interest",
                144 => "Voucher - Bank",
                145 => "Voucher - Eli Loan",
                151 => "Director - Deposit",
                152 => "Director - Withdraw",
                153 => "Director - JV Deposit",
                161 => "ShareHolder - Deposit",
                162 => "ShareHolder - Transfer",
                163 => "ShareHolder - JV Deposit",
                171 => "Loan From Bank - Create Loan",
                172 => "Loan From Bank - Emi Payment",
                173 => "Loan From Bank - JV Entry",
            ];
            $paymentModeMappings = [
                0 => "Cash",
                1 => "Cheque",
                2 => "Online Transfer",
                3 => "SSB/GV Transfer",
                4 => "Auto Transfer(ECS)",
                5 => "By loan amount",
                6 => "JV Module",
                7 => "Credit Card",
            ];
            foreach ($data as $row) {
                $sno++;
                $val["DT_RowIndex"] = $sno;
                $val["id"] = $row->id;
                $val["description"] = $row->description;
                $val["created_date"] = date(
                    "d/m/Y",
                    strtotime(convertDate($row->created_at))
                );
                $val["company_id"] = companies::where(
                    "id",
                    $row->company_id
                )->first("name")->name;
                // dd($val['company_id']);
                if ($arrFormData["ledger_type"] == "4") {
                    $data2 = Branch::where(
                        "id",
                        $row["rentLib"]->branch_id
                    )->first("name");
                } else {
                    $data2 = Branch::where("id", $row->branch_id)->first(
                        "name"
                    );
                }

                $val["branch_name"] = $data2->name ?? "";
                // dd($row->type,$row->sub_type);
                $val["head_name"] = $row->type
                    ? TransactionType::where("type", $row->type)
                        // ->where("sub_type", $row->sub_type)
                        ->value("title") ?? "N/A"
                    : "N/A";

                $tran_type =
                    $row->type == 1 &&
                    array_key_exists($row->sub_type, $tranTypes)
                    ? $tranTypes[$row->sub_type]
                    : "N/A";

                if ($row->type == 2) {
                    $tran_type = $typeMappings[$row->sub_type] ?? "N/A";
                } else {
                    $tran_type = "N/A";
                }

                if (isset($transactionMappings[$row->type])) {
                    $subType = $row->sub_type ?? "default";
                    $tran_type =
                        $transactionMappings[$row->type][$subType] ??
                        "Unknown Type";
                } else {
                    $tran_type = "Unknown Type";
                }

                $type = $row->type;
                $subType = $row->sub_type ?? "default";

                $tran_type = $transactionMappings[$type][$subType] ?? "N/A";

                if ($row->type == 6) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 7) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 8) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 9) {
                    if ($row->sub_type == 90) {
                        $tran_type = "Commission TDS";
                    }
                }

                if ($row->type == 10) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 11) {
                    $tran_type = "Demand";
                }

                if ($row->type == 12) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 13) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 14) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 15) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 16) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 17) {
                    $tran_type = $subTypeMappings[$row->sub_type] ?? null;
                }

                if ($row->type == 18) {
                    if ($row->sub_type == 181) {
                        $tran_type = "Bank Charge  - Create";
                    }
                }
                if ($row->type == 19) {
                    if ($row->sub_type == 191) {
                        $tran_type = "Assets  - Assets";
                    } elseif ($row->sub_type == 192) {
                        $tran_type = "Assets  - Depreciation";
                    }
                }
                if ($row->type == 20) {
                    if ($row->sub_type == 201) {
                        $tran_type = "Expense Booking  - Create Expense";
                    }
                }

                if ($row->type == 21) {
                    $tran_type = "Stationery Charge";
                }
                if ($row->type == 22) {
                    if ($row->sub_type == 222) {
                        $tran_type = "JV To Bank";
                    }
                }

                if ($row->type == 23) {
                    if ($row->sub_type == 232) {
                        $tran_type = "JV To Branch";
                    }
                }

                if ($arrFormData["ledger_type"] == "1") {
                    $val["head_name"] = $tran_type;
                }
                if ($arrFormData["ledger_type"] == "4") {
                    if ($row->type == 1) {
                        $tran_type = "Rent Payment";
                    } elseif ($row->type == 2) {
                        $tran_type = "Advance Rent Payment";
                    } elseif ($row->type == 3) {
                        $tran_type = "Rent Payment with Settlement";
                    }
                    $val["head_name"] = $tran_type;
                }

                //$val['head_name']=getAcountHead($row->head_id);

                $paymentModeValue = $row->payment_mode ?? -1;

                $val["payment_mode"] =
                    $paymentModeMappings[$paymentModeValue] ?? "N/A";

                if ($arrFormData["ledger_type"] == "1") {
                    if ($row->payment_type == "DR") {
                        $val["debit"] = number_format(
                            (float) $row->amount,
                            2,
                            ".",
                            ""
                        );
                        $val["credit"] = 0;
                    } elseif ($row->payment_type == "CR") {
                        $val["debit"] = 0;
                        $val["credit"] = number_format(
                            (float) $row->amount,
                            2,
                            ".",
                            ""
                        );
                    }
                    $val["balance"] = number_format(
                        (float) $row->amount,
                        2,
                        ".",
                        ""
                    );

                    $payment_type = "N\A";
                    if ($row->payment_type == "CR") {
                        $payment_type = "Credit";
                    }
                    if ($row->payment_type == "DR") {
                        $payment_type = "Debit";
                    }
                    $val["payment_type"] = $payment_type;
                } else {
                    $debit = 0;
                    $val["debit"] = 0;
                    $credit = 0;
                    $val["credit"] = 0;

                    $payment_type = "N\A";
                    if ($row->payment_type == "CR") {
                        $payment_type = "Credit";
                    }
                    if ($row->payment_type == "DR") {
                        $payment_type = "Debit";
                    }
                    $val["payment_type"] = $payment_type;

                    if (
                        $arrFormData["ledger_type"] == "6" ||
                        $arrFormData["ledger_type"] == "7" ||
                        $arrFormData["ledger_type"] == "5" ||
                        $arrFormData["ledger_type"] == "8" ||
                        $arrFormData["ledger_type"] == "3"
                    ) {
                        if ($row->payment_type == "CR") {
                            $credit = $row->amount;
                            $val["credit"] = number_format(
                                (float) $row->amount,
                                2,
                                ".",
                                ""
                            );
                        }
                        if ($row->payment_type == "DR") {
                            $debit = $row->amount;
                            $val["debit"] = number_format(
                                (float) $row->amount,
                                2,
                                ".",
                                ""
                            );
                        }
                    } else {
                        if ($row->payment_type == "CR") {
                            $credit = $row->deposit;
                            $val["credit"] = number_format(
                                (float) $row->deposit,
                                2,
                                ".",
                                ""
                            );
                        }
                        if ($row->payment_type == "DR") {
                            $debit = $row->withdrawal;
                            $val["debit"] = number_format(
                                (float) $row->withdrawal,
                                2,
                                ".",
                                ""
                            );
                        }
                    }

                    if (
                        $arrFormData["ledger_type"] == "2" ||
                        $arrFormData["ledger_type"] == "3" ||
                        $arrFormData["ledger_type"] == "4" ||
                        $arrFormData["ledger_type"] == "5" ||
                        $arrFormData["ledger_type"] == "6" ||
                        $arrFormData["ledger_type"] == "7"
                    ) {
                        $total = (float) $credit - (float) $debit;
                        $totalAmountssssss = $totalAmountssssss + $total;
                    } else {
                        $total = (float) $debit - (float) $credit;
                        $totalAmountssssss = $totalAmountssssss + $total;
                    }

                    $val["balance"] = number_format((float) $totalAmountssssss, 2, ".", "");
                }

                $rowReturn[] = $val;
            }

            $output = [
                "draw" => $_POST["draw"],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $count,
                "data" => $rowReturn,
                "total" => $totalAmountssssss,
            ];

            return json_encode($output);
        } else {
            $output = [
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => 0,
                "total" => 0,
            ];
            return json_encode($output);
        }
    }

    public function exportledgerRecordListing(Request $request)
    {
        // dd('dgdf');
        $token = session()->get("_token");
        $data = Cache::get("data_ledger_listing" . $token);
        $totalCount = Cache::get("data_ledger_listing_count_" . $token);
        // dd($totalCount);
        $totalAmountssssss = Cache::get("data_ledger_listing_totalAmountssssss" . $token);
        $input = $request->all();
        // dd($data);
        $start = $input["start"];
        $limit = $input["limit"];
        $company_id = $input["company_id"];
        $companydetails = Companies::pluck("name", "id");
        $returnURL = URL::to("/") . "/asset/" . ($input["company_id"] != 0 ? $companydetails[$company_id] : "all") . "_ledger_list.csv";
        $fileName = env("APP_EXPORTURL") . "asset/" . ($input["company_id"] != 0 ? $companydetails[$company_id] : "all") . "_ledger_list.csv";
        $getBranchDetail = Branch::pluck("name", "id");
        $sno = $_POST["start"];
        $tranTypes = [
            11 => "Member Register(MI Charge)",
            12 => "Member Register(STN Charge)",
            13 => "Member JV Entry",
            14 => "Member TDS on Interest",
        ];
        $typeMappings = [
            21 => "Associate Commission",
            22 => "Associate JV Commission",
            23 => "Associate JV Fuel Charge",
        ];
        $transactionMappings = [
            2 => [
                21 => "Associate Commission",
                22 => "Associate JV Commission",
                23 => "Associate JV Fuel Charge",
            ],
            3 => [
                30 => "R-Investment Register",
                31 => "Account opening",
                32 => "Renew",
                33 => "Passbook Print",
                38 => "JV Entry",
                39 => "JV Stationary Charge",
                311 => "JV Passbook Print",
                312 => "JV Certificate Print",
                "default" => "Investment",
            ],
            4 => [
                41 => "Account opening",
                42 => "Deposit",
                43 => "Withdraw",
                44 => "Passbook Print",
                45 => "Commission",
                46 => "Fuel Charge",
                412 => "SSB JV Entry",
                413 => "SSB JV Passbook Print",
                414 => "SSB JV Certificate Print",
                "default" => "Saving Account",
            ],
            5 => [
                51 => "Loan",
                52 => "Loan",
                53 => "Loan",
                57 => "Loan",
                54 => "Group Loan",
                55 => "Group Loan",
                56 => "Group Loan",
                58 => "Group Loan",
                511 => "Loan JV Loan",
                512 => "Loan JV Group Loan",
                513 => "Loan JV Loan Panelty",
                514 => "Loan JV Group Loan Panelty",
                515 => "Loan JV Loan Emi",
                516 => "Loan JV Group Loan Emi",
            ],
        ];
        $subTypeMappings = [
            61 => "Employee Salary",
            62 => "Employee JV Salary",
            70 => "Branch Cash",
            71 => "Branch Cheque",
            72 => "Branch Online",
            73 => "Branch SSB",
            80 => "Bank Cash",
            81 => "Bank Cheque",
            82 => "Bank Online",
            83 => "Bank SSB",
            101 => "Rent - Ledger",
            102 => "Rent - Payment",
            103 => "Rent - Security",
            104 => "Rent - Advance",
            105 => "Rent - JV Ledger",
            106 => "Rent - JV Security",
            121 => "Salary - Ledger",
            122 => "Salary - Transfer",
            123 => "Salary - Advance",
            131 => "Demand Advice - Fresh Expense",
            132 => "Demand Advice - Ta Advance",
            133 => "Demand Advice - Maturity",
            134 => "Demand Advice - Prematurity",
            135 => "Demand Advice - Death Help",
            136 => "Demand Advice - Death Claim",
            137 => "Demand Advice - EM",
            138 => "Demand Advice - JV Ta Advance",
            141 => "Voucher - Director",
            142 => "Voucher - ShareHolder",
            143 => "Voucher - Penal Interest",
            144 => "Voucher - Bank",
            145 => "Voucher - Eli Loan",
            151 => "Director - Deposit",
            152 => "Director - Withdraw",
            153 => "Director - JV Deposit",
            161 => "ShareHolder - Deposit",
            162 => "ShareHolder - Transfer",
            163 => "ShareHolder - JV Deposit",
            171 => "Loan From Bank - Create Loan",
            172 => "Loan From Bank - Emi Payment",
            173 => "Loan From Bank - JV Entry",
        ];
        $paymentModeMappings = [
            0 => "Cash",
            1 => "Cheque",
            2 => "Online Transfer",
            3 => "SSB/GV Transfer",
            4 => "Auto Transfer(ECS)",
            5 => "By loan amount",
            6 => "JV Module",
            7 => "Credit Card",
        ];
        if ($start == 0) {
            $handle = fopen($fileName, "w");
        } else {
            $handle = fopen($fileName, "a");
        }
        if ($start == 0) {
            $headerDisplayed = false;
        } else {
            $headerDisplayed = true;
        }
        $totalResults = $totalCount;
        $result = "next";
        if ($start + $limit >= $totalResults) {
            $result = "finished";
        }
        $resultdata = $data->slice($start, $limit);
        foreach ($resultdata as $row) {
            // dd($row);
            $sno++;
            $val["S.No"] = $sno;
            // $val["id"] = $row->id;

            $val["created_date"] = date(
                "d/m/Y",
                strtotime(convertDate($row->created_at))
            );
            $val["company"] = companies::where(
                "id",
                $row->company_id
            )->first("name")->name;
            // dd($val['company_id']);
            if ($input["ledger_type"] == "4") {
                $data2 = Branch::where("id", $row["rentLib"]->branch_id)->first(
                    "name"
                );
            } else {
                $data2 = Branch::where("id", $row->branch_id)->first("name");
            }

            $val["branch_name"] = $data2->name ?? "";

            $val["Type"] = $row->type
                ? TransactionType::where("type", $row->type)
                    // ->where("sub_type", $row->sub_type)
                    ->value("title") ?? "N/A"
                : "N/A";

            $tran_type =
                $row->type == 1 && array_key_exists($row->sub_type, $tranTypes)
                ? $tranTypes[$row->sub_type]
                : "N/A";

            if ($row->type == 2) {
                $tran_type = $typeMappings[$row->sub_type] ?? "N/A";
            } else {
                $tran_type = "N/A";
            }

            if (isset($transactionMappings[$row->type])) {
                $subType = $row->sub_type ?? "default";
                $tran_type =
                    $transactionMappings[$row->type][$subType] ??
                    "Unknown Type";
            } else {
                $tran_type = "Unknown Type";
            }

            $type = $row->type;
            $subType = $row->sub_type ?? "default";

            $tran_type = $transactionMappings[$type][$subType] ?? "N/A";

            if ($row->type == 6) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 7) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 8) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 9) {
                if ($row->sub_type == 90) {
                    $tran_type = "Commission TDS";
                }
            }

            if ($row->type == 10) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 11) {
                $tran_type = "Demand";
            }

            if ($row->type == 12) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 13) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 14) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 15) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 16) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 17) {
                $tran_type = $subTypeMappings[$row->sub_type] ?? null;
            }

            if ($row->type == 18) {
                if ($row->sub_type == 181) {
                    $tran_type = "Bank Charge  - Create";
                }
            }
            if ($row->type == 19) {
                if ($row->sub_type == 191) {
                    $tran_type = "Assets  - Assets";
                } elseif ($row->sub_type == 192) {
                    $tran_type = "Assets  - Depreciation";
                }
            }
            if ($row->type == 20) {
                if ($row->sub_type == 201) {
                    $tran_type = "Expense Booking  - Create Expense";
                }
            }

            if ($row->type == 21) {
                $tran_type = "Stationery Charge";
            }
            if ($row->type == 22) {
                if ($row->sub_type == 222) {
                    $tran_type = "JV To Bank";
                }
            }

            if ($row->type == 23) {
                if ($row->sub_type == 232) {
                    $tran_type = "JV To Branch";
                }
            }

            if ($input["ledger_type"] == "1") {
                $val["head_name"] = $tran_type;
            }
            if ($input["ledger_type"] == "4") {
                if ($row->type == 1) {
                    $tran_type = "Rent Payment";
                } elseif ($row->type == 2) {
                    $tran_type = "Advance Rent Payment";
                } elseif ($row->type == 3) {
                    $tran_type = "Rent Payment with Settlement";
                }
                $val["head_name"] = $tran_type;
            }

            //$val['head_name']=getAcountHead($row->head_id);

            $paymentModeValue = $row->payment_mode ?? -1;

            $val["payment_mode"] =
                $paymentModeMappings[$paymentModeValue] ?? "N/A";

            $val["description"] = $row->description;

            if ($input["ledger_type"] == "1") {
                if ($row->payment_type == "DR") {
                    $val["debit"] = number_format(
                        (float) $row->amount,
                        2,
                        ".",
                        ""
                    );
                    $val["credit"] = 0;
                } elseif ($row->payment_type == "CR") {
                    $val["debit"] = 0;
                    $val["credit"] = number_format(
                        (float) $row->amount,
                        2,
                        ".",
                        ""
                    );
                }
                $val["balance"] = number_format(
                    (float) $row->amount,
                    2,
                    ".",
                    ""
                );

                $payment_type = "N\A";
                if ($row->payment_type == "CR") {
                    $payment_type = "Credit";
                }
                if ($row->payment_type == "DR") {
                    $payment_type = "Debit";
                }
                $val["payment_type"] = $payment_type;
            } else {
                $debit = 0;
                $val["debit"] = 0;
                $credit = 0;
                $val["credit"] = 0;
                // $payment_type = "N\A";
                // if ($row->payment_type == "CR") {
                //     $payment_type = "Credit";
                // }
                // if ($row->payment_type == "DR") {
                //     $payment_type = "Debit";
                // }
                // $val["payment_type"] = $payment_type;

                if (
                    $input["ledger_type"] == "6" ||
                    $input["ledger_type"] == "7" ||
                    $input["ledger_type"] == "5" ||
                    $input["ledger_type"] == "8" ||
                    $input["ledger_type"] == "3"
                ) {
                    if ($row->payment_type == "CR") {
                        $credit = $row->amount;
                        $val["credit"] = number_format(
                            (float) $row->amount,
                            2,
                            ".",
                            ""
                        );
                    }
                    if ($row->payment_type == "DR") {
                        $debit = $row->amount;
                        $val["debit"] = number_format(
                            (float) $row->amount,
                            2,
                            ".",
                            ""
                        );
                    }
                } else {
                    if ($row->payment_type == "CR") {
                        $credit = $row->deposit;
                        $val["credit"] = number_format(
                            (float) $row->deposit,
                            2,
                            ".",
                            ""
                        );
                    }
                    if ($row->payment_type == "DR") {
                        $debit = $row->withdrawal;
                        $val["debit"] = number_format(
                            (float) $row->withdrawal,
                            2,
                            ".",
                            ""
                        );
                    }
                }

                if (
                    $input["ledger_type"] == "2" ||
                    $input["ledger_type"] == "3" ||
                    $input["ledger_type"] == "4" ||
                    $input["ledger_type"] == "5" ||
                    $input["ledger_type"] == "6" ||
                    $input["ledger_type"] == "7"
                ) {
                    $total = (float) $credit - (float) $debit;
                    $totalAmountssssss = $totalAmountssssss + $total;
                } else {
                    $total = (float) $debit - (float) $credit;
                    $totalAmountssssss = $totalAmountssssss + $total;
                }

                $val["balance"] = number_format(
                    (float) $totalAmountssssss,
                    2,
                    ".",
                    ""
                );
                // dd($val);
                if (!$headerDisplayed) {
                    fputcsv($handle, array_keys($val));
                    $headerDisplayed = true;
                }
                fputcsv($handle, $val);
            }
        }
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = (($start + $limit) * 100) / $totalResults;
            $percentage = number_format((float) $percentage, 1, ".", "");
        }
        $response = [
            "result" => $result,
            "start" => $start,
            "limit" => $limit,
            "totalResults" => $totalResults,
            "fileName" => $returnURL,
            "percentage" => $percentage,
        ];
        // dd($response);
        echo json_encode($response);
        // $accounthead = Cache::get('account_Heads_' . $token);
    }
}