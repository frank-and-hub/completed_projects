<?php
namespace App\Http\Controllers\Admin;

use App\Exports\GstOutwordReportExport;
use App\Http\Controllers\Controller;
use App\Models\GstTransaction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use URL;
use DB;
use Session;
use Auth;

class GstReportController extends Controller
{
    /**
     * create a new Controller instance
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->transaction = new GstTransaction();
    }
    public function gst_summary_report()
    {
        if (check_my_permission(Auth::user()->id, "272") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'GST Summary Report';
        return view('templates.admin.gstReport.gstSummaryReport', $data);
    }
    public function crdr_report()
    {
        if (check_my_permission(Auth::user()->id, "271") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'GST Report - CR DR  Supply';
        $data['data'] = GstTransaction::select('id', 'type_id', 'invoice_number', 'created_at', 'total_amount', 'customer_gst_no', 'amount_of_tax_igst', 'amount_of_tax_cgst', 'amount_of_tax_sgst', 'tax_value', 'head_id')->with([
            'memberDetails' => function ($q) {
                $q->select('id', 'branch_id', 'first_name')->with([
                    'branch' => function ($q) {
                        $q->select('id', 'state_id')->with([
                            'branchStatesCustom' => function ($q) {
                                $q->select('id', 'name');
                            }
                        ]);
                    }
                ]);
            },
            'gstHeadrate' => function ($q) {
                $q->select('id', 'head_id', 'gst_percentage');
            }
        ])->paginate(20);
        return view('templates.admin.gstReport.crdr_report', $data);
    }
    public function gst_report()
    {
        if (check_my_permission(Auth::user()->id, "270") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'GST Report - Outward Supply';
        $data['data'] = GstTransaction::select('id', 'type_id', 'invoice_number', 'created_at', 'total_amount', 'amount_of_tax_igst', 'customer_gst_no', 'amount_of_tax_cgst', 'amount_of_tax_sgst', 'tax_value', 'head_id')->with([
            'memberDetails' => function ($q) {
                $q->select('id', 'branch_id', 'first_name')->with([
                    'branch' => function ($q) {
                        $q->select('id', 'state_id')->with([
                            'branchStatesCustom' => function ($q) {
                                $q->select('id', 'name');
                            }
                        ]);
                    }
                ]);
            },
            'gstHeadrate' => function ($q) {
                $q->select('id', 'head_id', 'gst_percentage');
            }
        ])->paginate(20);
        return view('templates.admin.gstReport.report', $data);
    }
    /**
     * Lisitng of Gst Summary
     */
    public function gst_summary_report_listing()
    {
        $data = \App\Models\GstTransaction::get()->unique('head_id');
        //dd($data);
        $sno = $_POST['start'];
        $rowReturn = array();
        $totalCount = count($data);
        $count = $totalCount;
        $s = 0;
        foreach ($data as $i => $row) {
            $invoice = $this->transaction->firstTransaction($row['head_id']);
            //$dayBook = Daybook::where('investment_id',$row->id)->where('account_no',$row->account_number)->orderby('created_at','desc')->first();
            $sno++;
            $val['DT_RowIndex'] = $sno;
            $val['nature_of_doc'] = $row->Heads->sub_head;
            $val['sr_from'] = $invoice['start']->invoice_number;
            $val['sr_to'] = $invoice['last']->invoice_number;
            $val['total_number'] = $invoice['count'];
            $val['cancelled'] = 0;
            $val['net_issued'] = $invoice['count'];
            $rowReturn[] = $val;
            $s++;
        }
        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
        return json_encode($output);
    }
    /**
     * Gst Summary Report Export
     * @param export Excel
     */
    public function exportgstSummaryReport(Request $request)
    {
        if ($request['export_summary_extension'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/GstSummaryReport.csv";
            $fileName = env('APP_EXPORTURL') . "asset/GstSummaryReport.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = GstTransaction::get()->groupBy('head_id');
        if ($request['export_summary_extension'] == 0) {
            $sno = $_POST['start'];
            $totalResults = count($data);
            $results = $data;
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
            $i = 0;
            foreach ($results as $k => $row) {
                // pd($row);
                $invoice = $this->transaction->firstTransaction($row[$i]->head_id);
                $sno++;
                $val['NATURE OF DOCUMENT'] = $row[$i]['Heads']->sub_head;
                $val['SR FROM'] = $invoice['start']->invoice_number;
                $val['SR TO'] = $invoice['last']->invoice_number;
                $val['TOTAL NUMBER'] = $invoice['count'];
                $val['CANCELLED'] = 0;
                $val['NET ISSUED'] = $invoice['count'];
                // $i++;
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
     * CR DR Note Listing
     * @param Request $request
     */
    public function gst_cr_dr_note_report(Request $request)
    {
        if ($request['export_outward_report_extension'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/GstSummaryReport.csv";
            $fileName = env('APP_EXPORTURL') . "asset/GstSummaryReport.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = GstTransaction::with('memberDetails')->get();
        if ($request['export_outward_report_extension'] == 0) {
            $sno = $_POST['start'];
            $totalResults = count($data);
            $results = $data;
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
            $i = 0;
            foreach ($results as $value) {
                $val['NAME OF RECEIPIENT'] = $value['memberDetails']->first_name;
                $val['GSTIN'] = '-';
                $val['State NAME'] = $value['memberDetails']['branch']['branchStatesCustom']->name;
                $val['POS'] = '-';
                $val['TYPE OF NOTE NO'] = '-';
                $val['TYPE OF NOTE DATE'] = '-';
                $val['REASON OF ISSUEING DEBIT CARD NOTE'] = '-';
                $val['HSN CODE OF GOODS/SERVICE'] = '-';
                $val['GOODS/SERVICE DESCRIPTION'] = '-';
                $val['QUANTITY'] = '-';
                $val['QUANTITY UNIT'] = '-';
                $val['ORIGINAL INVOICE NO.'] = $value->invoice_number;
                $val['ORIGINAL INVOICE DATE'] = date('d-m-Y', strtotime($value->created_at));
                $val['DIFFERENTIAL VALUE'] = '-';
                if ($value->gstHeadrate && $value->amount_of_tax_igst > 0) {
                    $per = $value->gstHeadrate->gst_percentage;
                    $val['IGST RATE'] = $per;
                    $val['IGST AMOUNT'] = $value->amount_of_tax_igst;
                } else {
                    $per = $value->gstHeadrate->gst_percentage / 2;
                    $val['CGST RATE'] = $per;
                    $val['CGST AMOUNT'] = $value->amount_of_tax_cgst;
                    $val['SGST RATE'] = $per;
                    $val['SGST AMOUNT'] = $value->amount_of_tax_sgst;
                }
                $val['CESS RATE'] = '-';
                $val['CESS AMOUNT'] = '-';
                $val['INDICATE IF SUPPLY ATTRACTS REVERSE CHARGE'] = '-';
                $val['WHETHER PRE GST'] = '-';
                $val['SELECT RECIPIENT CATEGORY DIFFERENT FROM REGULAR'] = '-';
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
    public function exportgstOutwardReport(Request $request)
    {
        if ($request['export_outward_report_extension'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/GstOutwardReport.csv";
            $fileName = env('APP_EXPORTURL') . "asset/GstOutwardReport.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = GstTransaction::with('memberDetails')->get();
        if ($request['export_outward_report_extension'] == 0) {
            $sno = $_POST['start'];
            $totalResults = count($data);
            $results = $data;
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
            $i = 0;
            foreach ($results as $value) {
                $gstNum = '';
                if (isset($value->customer_gst_no)) {
                    $gstNum = $value->customer_gst_no;
                }
                $val['NAME OF RECEIPIENT'] = $value['memberDetails']->first_name;
                $val['GSTIN'] = $gstNum;
                $val['State Name'] = $value['memberDetails']['branch']['branchStatesCustom']->name;
                $val['POS'] = '-';
                $val['INVOICE NUMBER'] = $value->invoice_number;
                $val['INVOICE DATE'] = date('d-m-Y', strtotime($value->created_at));
                $val['INVOICE VALUE'] = $value->total_amount;
                $val['INVOICE HSN/SAC'] = '-';
                $val['INVOICE GOODS/SERVICE DESCRIPTION'] = '-';
                $val['INVOICE TAXABLE VALUE'] = $value->tax_value;
                $val['QUANTITY'] = '-';
                $val['QUANTITY UNIT'] = '-';
                if ($value->gstHeadrate && $value->amount_of_tax_igst > 0) {
                    $per = $value->gstHeadrate->gst_percentage;
                    $val['IGST RATE'] = $per;
                    $val['IGST AMOUNT'] = $value->amount_of_tax_igst;
                } else {
                    $per = $value->gstHeadrate->gst_percentage / 2;
                    $val['CGST RATE'] = $per;
                    $val['CGST AMOUNT'] = $value->amount_of_tax_cgst;
                    $val['SGST RATE'] = $per;
                    $val['SGST AMOUNT'] = $value->amount_of_tax_sgst;
                }
                $val['CESS RATE'] = '-';
                $val['CESS AMOUNT'] = '-';
                $val['REVERSE CHARGE'] = '-';
                $val['NAME OF ECOMMERCE OPERATOR'] = '-';
                $val['GSTIN OF ECOMMERCE OPERATOR'] = '-';
                $val['SHIPPING EXPORT TYPE'] = '-';
                $val['SHIPPING NO.'] = '-';
                $val['SHIPPING DATE'] = '-';
                $val['SHIPPING PORT CODE'] = '-';
                $val['RECIPIENT CATEGORY'] = '-';
                $val['ITEM TYPE'] = '-';
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
    public function exportgstcrdrReport(Request $request)
    {
        if ($request['export_report_extension'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/GstCRDRReport.csv";
            $fileName = env('APP_EXPORTURL') . "asset/GstCRDRReport.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = GstTransaction::with('memberDetails')->get();
        if ($request['export_report_extension'] == 0) {
            $sno = $_POST['start'];
            $totalResults = count($data);
            $results = $data;
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
            $i = 0;
            foreach ($results as $value) {
                $gstNum = '';
                if (isset($value->customer_gst_no)) {
                    $gstNum = $value->customer_gst_no;
                }
                $val['NAME OF RECEIPIENT'] = $value['memberDetails']->first_name;
                $val['GSTIN'] = $gstNum;
                $val['State NAME'] = $value['memberDetails']['branch']['branchStatesCustom']->name;
                $val['POS'] = '-';
                $val['TYPE OF NOTE NO'] = '-';
                $val['TYPE OF NOTE DATE'] = '-';
                $val['REASON OF ISSUEING DEBIT CARD NOTE'] = '-';
                $val['HSN CODE OF GOODS/SERVICE'] = '-';
                $val['GOODS/SERVICE DESCRIPTION'] = '-';
                $val['QUANTITY'] = '-';
                $val['QUANTITY UNIT'] = '-';
                $val['ORIGINAL INVOICE NO.'] = $value->invoice_number;
                $val['ORIGINAL INVOICE DATE'] = date('d-m-Y', strtotime($value->created_at));
                $val['DIFFERENTIAL VALUE'] = '-';
                if ($value->gstHeadrate && $value->amount_of_tax_igst > 0) {
                    $per = $value->gstHeadrate->gst_percentage;
                    $val['IGST RATE'] = $per;
                    $val['IGST AMOUNT'] = $value->amount_of_tax_igst;
                } else {
                    $per = $value->gstHeadrate->gst_percentage / 2;
                    $val['CGST RATE'] = $per;
                    $val['CGST AMOUNT'] = $value->amount_of_tax_cgst;
                    $val['SGST RATE'] = $per;
                    $val['SGST AMOUNT'] = $value->amount_of_tax_sgst;
                }
                $val['CESS RATE'] = '-';
                $val['CESS AMOUNT'] = '-';
                $val['INDICATE IF SUPPLY ATTRACTS REVERSE CHARGE'] = '-';
                $val['WHETHER PRE GST'] = '-';
                $val['SELECT RECIPIENT CATEGORY DIFFERENT FROM REGULAR'] = '-';
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