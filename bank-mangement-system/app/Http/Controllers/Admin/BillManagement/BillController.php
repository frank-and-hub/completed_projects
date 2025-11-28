<?php

namespace App\Http\Controllers\Admin\BillManagement;

use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBank;
use App\Models\AccountHeads;
use App\Models\Vendor;
use App\Models\VendorBill;
use App\Models\VendorBillItem;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use DB;
use URL;
use App\Models\SamraddhBankAccount;
use App\Services\ImageUpload;
class BillController extends Controller

{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Bill  Report 
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "154") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Bill Management | Listing";
        $data['vendors'] = Vendor::select('id', 'name')->where('status', 1)->get();
        return view('templates.admin.bill_management.bill_listing', $data);
    }

    // Report Listing
    public function bill_listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['is_search'] = $request->is_search;

            $data = VendorBill::has('company')->select('id', 'bill_date', 'bill_number', 'status', 'transferd_amount', 'balance', 'payble_amount', 'branch_id', 'vendor_id', 'company_id','bill_upload')
                ->with(['company:id,name,short_name', 'vendorBranchDetail:id,name,branch_code', 'vendor:id,name']);

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

                $data = $data->where('is_deleted', '!=', 1)->orderby('id', 'DESC')->get();

                $sno = $_POST['start'];
                $rowReturn = array();

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('date', function ($row) {
                        $date = 'N/A';
                        if (isset($row->bill_date)) {
                            $date = date("d/m/Y", strtotime(($row->bill_date)));
                        }
                        return $date;
                    })
                    ->rawColumns(['date'])
                    ->addColumn('ref_number', function ($row) {
                        $bill_number = $row->bill_number;
                        
                        return $bill_number;
                    })
                    ->addColumn('vendor_name', function ($row) {
                        return $row['vendor']->name;
                    })
                    ->rawColumns(['vendor_name'])
                    ->addColumn('branch_name', function ($row) {
                        return $row['vendorBranchDetail']->name . '-' . $row['vendorBranchDetail']->branch_code;
                    })
                    ->rawColumns(['branch_name'])
                    ->addColumn('company_name', function ($row) {
                        return $row['company']->short_name;
                    })
                    ->rawColumns(['company_name'])
                    ->addColumn('status', function ($row) {
                        $status = 'N/A';
                        if ($row->status == 0) {
                            $status = 'UnPaid';
                        } elseif ($row->status == 1) {
                            $status = 'Partial';
                        } elseif ($row->status == 2) {
                            $status = 'Paid';
                        }
                        return $status;
                    })
                    ->rawColumns(['status'])
                    ->addColumn('due_date', function ($row) {
                        $due_date = 'N/A';
                        if (isset($row->due_date)) {
                            $due_date = date("d/m/Y", strtotime(($row->due_date)));
                        }
                        return $due_date;
                    })
                    ->rawColumns(['due_date'])
                    ->addColumn('amount', function ($row) {
                        return number_format((float) $row->transferd_amount, 2, '.', '');
                    })
                    ->rawColumns(['amount'])
                    ->addColumn('due_balance', function ($row) {
                        return number_format((float) $row->balance, 2, '.', '');
                    })
                    ->rawColumns(['due_balance'])
                    ->addColumn('bill_amount', function ($row) {
                        return number_format((float) $row->payble_amount, 2, '.', '');
                    })
                    ->rawColumns(['bill_amount'])
                    ->addColumn('action', function ($row) {
                        $btn = '';
                        $url = URL::to("admin/bill/edit/" . $row->id . "");
                        $url2 = URL::to("admin/bill/delete/" . $row->id . "");
                        $url3 = URL::to("admin/vendor-credit/create/" . $row->id . "");
                        $view_url = URL::to("admin/bill/view-listing/" . $row->id . "");
                        $payment_url = URL::to("admin/bill/payment?id=" . $row->vendor_id . "");

                        if (check_my_permission(Auth::user()->id, "208") == "1" || check_my_permission(Auth::user()->id, "209") == "1" || check_my_permission(Auth::user()->id, "210") == "1" || check_my_permission(Auth::user()->id, "211") == "1") {
                            $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                            if ($row->status == 0 && check_my_permission(Auth::user()->id, "208") == "1") {
                                $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                            }
                            if (check_my_permission(Auth::user()->id, "209") == "1") {
                                // $btn .= '<button class="dropdown-item printBill" data-row-id="' . $row->id . '"  data-toggle="modal" data-target="#exampleModal"  ><i class="icon-eye mr-2"></i>Detail</button>';
                            }

                            if (check_my_permission(Auth::user()->id, "198") == "1") {
                                $btn .= '<a style="margin-right: 5px;" class="dropdown-item" href="' . $payment_url . '"><i class="fas fa-money"></i>Payment</a>';

                                

                                

                            }
                            $folderName = 'bill_expense/' . $row->bill_upload;
                            if ($row->bill_upload != '' && ImageUpload::fileExists($folderName)) {
                                $photo_url = ImageUpload::generatePreSignedUrl($folderName);
                                $btn .= '<a style="margin-right: 5px;" class="dropdown-item" href="' . $photo_url . ' target="_blank" "><i class="fas fa-file-alt"></i>Bill No</a>';
                            } else {
                                $photo_url = "#";
                            }
                           
                            
                            $btn .= '</div></div>';
                        }
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            } else {
                $data = [];
                return Datatables::of($data)->make(true);
            }
        }
    }


    public function print_bill()
    {
        $data['title'] = "Bill Management | Print";

        return view('templates.admin.bill_management.print_bill', $data);
    }

    public function vendor_bill(Request $request)
    {
        $companyID = $request->company_id;

        $data['vendors'] = Vendor::where('company_id', $companyID)->where('type', '0')->pluck('name', 'id');
        return $data;
    }

    public function bill_edit()
    {

        if (check_my_permission(Auth::user()->id, "208") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = "Bill Management | Listing";
        $data['vendors'] = Vendor::where('status', 1)->get();
        $data['branches'] = Branch::where('status', 1)->get();

        return view('templates.admin.bill_management.bill_listing', $data);
    }


    public function credit_note()
    {

        if (check_my_permission(Auth::user()->id, "211") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = "Bill Management | Listing";
        $data['vendors'] = Vendor::where('status', 1)->get();
        $data['branches'] = Branch::where('status', 1)->get();

        return view('templates.admin.bill_management.bill_listing', $data);
    }



    public function getBillDetails(Request $request)
    {

        if (isset($request->bill_id)) {
            $bill_id = trim($request->bill_id);

            // Now Get bill records
            $bill_records = VendorBill::with(['company:id,name,short_name', 'vendorBranchDetail:id,name,branch_code'])->where("id", $bill_id)->first();

            $date = date("d/m/Y", strtotime($bill_records->bill_date));
            $bill_records["bill_date"] = $date;

            // Branch data
            if ($bill_records->branch_id > 0) {
                $bill_records["branch_name"] = $bill_records['vendorBranchDetail']->name;
            } else {
                $bill_records["branch_name"] = "N/A";
            }

            $bill_item_details = VendorBillItem::where("vendor_bill_id", $bill_id)->get()->toArray();

            $bill_item_html = "";
            for ($x = 0; $x < count($bill_item_details); $x++) {
                $sr = $x + 1;
                $bill_item_html .= '<tr style="border-bottom:1px solid #ededed" class="breakrow-inside breakrow-after"><td  class="">
                                        ' . $sr . '<p style="color: grey;"> </p><p style="margin-bottom: 0px;color: grey;"></p></td><td  class="">' . $bill_item_details[$x]["item_name"] . '</td>
                                      <td  class=" ">' . $bill_item_details[$x]["quantity"] . '</td><td  class=" text-right">' . number_format((float)$bill_item_details[$x]["rate"], 2, '.', '') . '</td><td  class=" text-right">' . number_format((float)$bill_item_details[$x]["amount"], 2, '.', '') . '</td></tr>';
            }


            $array = array("bill_records" => $bill_records, "bill_item_details" => $bill_item_details, "bill_item_html" => $bill_item_html);

            echo json_encode($array);
        }
    }
}
