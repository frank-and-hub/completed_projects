<?php

namespace App\Http\Controllers\Admin\Loan;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use DateTime;
use App\Http\Controllers\Admin\CommanController;
use Session;
use App\Http\Traits\Oustanding_amount_trait;
use App\Http\Traits\getRecordUsingDayBookRefId;
use App\Http\Traits\EmiDatesTraits;
use App\Models\ECSTransaction;
use App\Models\Member;
use App\Models\Companies;
use App\Models\LoanDaybooks;
use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Models\Branch;
use App\Models\CollectorAccount;
use App\Models\LoanEmisNew;
use App\Models\SamraddhBankAccount;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\Sms;
class EcsController extends Controller
{
    use Oustanding_amount_trait, getRecordUsingDayBookRefId, EmiDatesTraits;
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function importview()
    {
        if (check_my_permission(Auth::user()->id, "344") != "1" && check_my_permission(Auth::user()->id, "345") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan | Bank ECS Import';
        return view('templates.admin.loan.ecs_transaction.import', $data);
    }

    // public function import(Request $request)
    // {
    //     // DB::beginTransaction();
    //     try {
    //         $file = $request->file('excel_file');
    //         // Load the xlsx file using PhpSpreadsheet
    //         $spreadsheet = IOFactory::load($file);
    //         $sheet = $spreadsheet->getActiveSheet();
    //         $headers = [];
    //         $array = [];
    //         $array2 = [];
    //         $return = [];
    //         $important = [];
    //         foreach ($sheet->getRowIterator() as $row) {
    //             if (empty($row->getCellIterator()->current()->getValue())) {
    //                 continue;
    //             }
    //             $rowData = [];
    //             $cellIterator = $row->getCellIterator();
    //             foreach ($cellIterator as $index => $cell) {
    //                 if ($row->getRowIndex() === 1) {
    //                     $headers[] = $cell->getValue();
    //                 } else {
    //                     // Convert only the third cell (index 2) to a readable date format
    //                     $value = $cell->getValue();
    //                     $rowData[] = $value;
    //                 }
    //             }
    //             if ($row->getRowIndex() === 1) {
    //                 continue;
    //             }
    //             $columnData = array_combine($headers, $rowData);
    //             // Add custom logic to handle numeric values with 'E' notation
    //             foreach ($columnData as &$value) {
    //                 if (is_numeric($value) && strpos($value, 'E') !== false) {
    //                     $value = number_format($value, 0, '.', '');
    //                 }
    //             }
    //             if ($columnData['STATUS'] == 'REALISED') {
    //                 $requiredKeys = [
    //                     'STATUS',
    //                     'POOLING ACCOUNT NUMBER',
    //                     'CREDIT DATE',
    //                     'TRANSACTION AMOUNT',
    //                     'Customer Transaction Ref No',
    //                     'IFSC/MICR CODE',
    //                     'DEBIT ACCOUNT NUMBER',
    //                     'CREDIT CONSOLIDATION NUMBER',
    //                     'UMRN NUMBER'
    //                 ];
    //                 $numericKeys = [
    //                     'CREDIT CONSOLIDATION NUMBER',
    //                     'POOLING ACCOUNT NUMBER',
    //                     'DEBIT ACCOUNT NUMBER',
    //                     'Customer Transaction Ref No',
    //                     'TRANSACTION AMOUNT'
    //                 ];
    //             } else if ($columnData['STATUS'] == 'RETURNED') {
    //                 $requiredKeys = [
    //                     'STATUS',
    //                     'CREDIT DATE',
    //                     'TRANSACTION AMOUNT',
    //                     'Customer Transaction Ref No',
    //                     'IFSC/MICR CODE',
    //                     'DEBIT ACCOUNT NUMBER',
    //                     'UMRN NUMBER'
    //                 ];
    //                 $numericKeys = [
    //                     'DEBIT ACCOUNT NUMBER',
    //                     'Customer Transaction Ref No',
    //                     'TRANSACTION AMOUNT'
    //                 ];
    //             } else {
    //                 return redirect()->back()->with('alert', 'Error: ' . "Unknown status for account " . $columnData['Customer Transaction Ref No'] . '');
    //             }
    //             $patternKeys = [
    //                 'UMRN NUMBER' => '/^[A-Za-z]{4}\d{16}$/',
    //                 // 'IFSC/MICR CODE' => '/^[A-Za-z]{4}\d{7}$/',
    //             ];

    //             $missingKeys = [];
    //             $nonNumericKeys = [];
    //             $patternMismatchKeys = [];

    //             foreach ($requiredKeys as $key) {
    //                 if (!isset($columnData[$key])) {
    //                     $missingKeys[] = $key;
    //                 } elseif (in_array($key, $numericKeys) && !is_numeric($columnData[$key])) {
    //                     $nonNumericKeys[] = $key;
    //                 } elseif (array_key_exists($key, $patternKeys) && !preg_match($patternKeys[$key], $columnData[$key])) {
    //                     $patternMismatchKeys[] = $key;
    //                 }
    //             }
    //             if (!empty($missingKeys) || !empty($nonNumericKeys) || !empty($patternMismatchKeys)) {
    //                 $errorMessage = '';

    //                 if (!empty($missingKeys)) {
    //                     $errorMessage .= 'The following fields are missing: ' . implode(', ', $missingKeys) . '. ';
    //                 }

    //                 if (!empty($nonNumericKeys)) {
    //                     $errorMessage .= 'The following fields must be numeric: ' . implode(', ', $nonNumericKeys) . '. ';
    //                 }

    //                 if (!empty($patternMismatchKeys)) {
    //                     $errorMessage .= 'The following fields do not match the required pattern: ' . implode(', ', $patternMismatchKeys) . '.';
    //                 }

    //                 // Throw an exception or handle the error as needed
    //                 return redirect()->back()->with('alert', 'Error: ' . $errorMessage);
    //             }
    //             $format = 'd-m-Y';
    //             $chkdate = DateTime::createFromFormat($format, $columnData['CREDIT DATE']);
    //             if ($chkdate == false) {
    //                 return redirect()->back()->with('alert', 'Error: ' . 'Date format mismatch for ' . $columnData['Customer Transaction Ref No']);
    //             }
    //             $timestamp1 = strtotime($columnData['CREDIT DATE']);
    //             $timestamp2 = strtotime(date($format));
    //             if ($timestamp1 > $timestamp2) {
    //                 return redirect()->back()->with('alert', 'Error: ' . 'You cannot make a transaction in future date for ' . $columnData['Customer Transaction Ref No']);
    //             }
    //             if (!empty($columnData['Customer Transaction Ref No'])) {
    //                 $importData = [
    //                     'utr_transaction_number' => $columnData['CREDIT CONSOLIDATION NUMBER'],
    //                     'customer_account_number' => $columnData['DEBIT ACCOUNT NUMBER'],
    //                     'customer_ifsc_code' => $columnData['IFSC/MICR CODE'],
    //                     'account_number' => $columnData['Customer Transaction Ref No'],
    //                     'amount' => $columnData['TRANSACTION AMOUNT'],
    //                     'date' => $columnData['CREDIT DATE'],
    //                     'bank_acc_no' => $columnData['POOLING ACCOUNT NUMBER'],
    //                     'transaction_status' => $columnData['STATUS'],
    //                     'ecs_ref_no' => $columnData['UMRN NUMBER'],
    //                 ];
    //                 if (Memberloans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->exists()) {
    //                     $member_loan_company_id = Memberloans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->first(['company_id', 'account_number', 'branch_id', 'id'])->toArray() ?? null;
    //                     $importData['loan_type'] = 'loan';
    //                     $a_id = CollectorAccount::where('type', 2)->where('type_id', $member_loan_company_id['id'])->where('status', 1)->value('associate_id');
    //                 } else if (Grouploans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->exists()) {
    //                     $member_loan_company_id = Grouploans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->first(['company_id', 'account_number', 'branch_id', 'id'])->toArray() ?? null;
    //                     $importData['loan_type'] = 'Group loan';
    //                     $a_id = CollectorAccount::where('type', 3)->where('type_id', $member_loan_company_id['id'])->where('status', 1)->value('associate_id');
    //                 } else {
    //                     return redirect()->back()->with('alert', $columnData['Customer Transaction Ref No'] . " Account number not found");
    //                 }
    //                 $state_id =  (Branch::whereId($member_loan_company_id['branch_id'])->value('state_id'));
    //                 $systemDate = headerMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
    //                 $sysDate = date('d-m-Y', strtotime(convertdate($systemDate)));

    //                 $timestamp1 = date('d-m-Y', strtotime(convertdate($columnData['CREDIT DATE'])));
    //                 if ($timestamp1 > $sysDate) {
    //                     return redirect()->back()->with('alert', 'Error: ' . 'You cannot make a transaction in future date for ' . $columnData['Customer Transaction Ref No'].'and date in state is '.$sysDate);
    //                 }
    //                 // $loan_charge = LoanCharge::where('loan_type', $member_loan_company_id)->where('company_id',)
    //                 $sbmfa_company_id = SamraddhBankAccount::where('account_no', $columnData['POOLING ACCOUNT NUMBER'])->exists() ? SamraddhBankAccount::where('account_no', $columnData['POOLING ACCOUNT NUMBER'])->first(['company_id', 'bank_id', 'account_no'])->toArray() : "n";
    //                 if ($member_loan_company_id == null) {
    //                     array_push($array2, $columnData['Customer Transaction Ref No']);
    //                 } elseif ($columnData['STATUS'] == 'REALISED') {
    //                     if ($member_loan_company_id['company_id'] != $sbmfa_company_id['company_id']) {
    //                         array_push($array, $columnData['Customer Transaction Ref No']);
    //                     } else {
    //                         $importData['branch_name'] = getBranchDetail($member_loan_company_id['branch_id'])->name;
    //                         $importData['bank_name'] = getSamraddhBank($sbmfa_company_id['bank_id'])->bank_name;
    //                         $associate = getMemberCustom($a_id);
    //                         $importData['associate_name'] = $associate->firstname . ' ' . $associate->last_name ?? ' ';
    //                         $importData['associate_no'] = $associate->associate_no;
    //                         $importData['associate_id'] = $a_id;
    //                         $importData['loan_id'] = $member_loan_company_id['id'];
    //                         $importData['branch'] = $member_loan_company_id['branch_id'];
    //                         $importData['company_id'] = $member_loan_company_id['company_id'];
    //                         $importData['company_name'] = Companies::whereId($member_loan_company_id['company_id'])->value('name');
    //                         $arr = array_merge($importData, $member_loan_company_id, $sbmfa_company_id);
    //                         array_push($return, $arr);
    //                         array_push($important, $importData);
    //                     }
    //                 } else {
    //                     $importData['branch_name'] = getBranchDetail($member_loan_company_id['branch_id'])->name;
    //                     $importData['bank_name'] = null;
    //                     $importData['account_no'] = null;
    //                     $importData['utr_transaction_number'] = null;
    //                     $associate = getMemberCustom($a_id);
    //                     $importData['associate_name'] = $associate->firstname . ' ' . $associate->last_name ?? ' ';
    //                     $importData['associate_no'] = $associate->associate_no;
    //                     $importData['associate_id'] = $a_id;
    //                     $importData['loan_id'] = $member_loan_company_id['id'];
    //                     $importData['branch'] = $member_loan_company_id['branch_id'];
    //                     $importData['company_id'] = $member_loan_company_id['company_id'];
    //                     $importData['company_name'] = Companies::whereId($member_loan_company_id['company_id'])->value('name');
    //                     $arr = array_merge($importData, $member_loan_company_id);
    //                     array_push($return, $arr);
    //                     array_push($important, $importData);
    //                 }
    //             }
    //         }
    //         if (empty($array)) {
    //             $data = array();
    //             $data['dataarray'] = $return;
    //             $title = 'Loan | Bank ECS Preview';
    //             return view('templates.admin.loan.ecs_transaction.import_inner', [
    //                 'data' => $data,
    //                 'important' => $important,
    //                 'title' => $title
    //             ]);
    //             // $importData->save();
    //             // DB::commit();
    //         } else {
    //             $a = $b;
    //         }
    //         // Commit the transaction
    //         // DB::commit();
    //         // redirect()->back()->with('success', 'XLSX file imported successfully');
    //     } catch (\Exception $e) {
    //         // DB::rollback();
    //         if (!empty($array)) {
    //             $string = implode(', ', $array);
    //             $string = "Company mismatch in account $string so no data inserted.";
    //             $string2 = "";
    //             if (!empty($array2)) {
    //                 $string2 = implode(', ', $array2);
    //                 $string2 = "  These accounts are considered invalid $string2";
    //             }
    //             return redirect()->back()->with('alert', "$string $string2");
    //         }
    //         return redirect()->back()->with('alert', 'Error: ' . $e->getMessage() . $e->getLine());
    //     }
    // }
    public function import(Request $request)
    {
        // DB::beginTransaction();
        try {
            $file = $request->file('excel_file');
            // Load the xlsx file using PhpSpreadsheet
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [];
            $array = [];
            $array2 = [];
            $return = [];
            $important = [];
            $total_realised_amount = 0;
            $total_record = 0;
            $total_realised_record = 0;
            $total_returned_record = 0;
            foreach ($sheet->getRowIterator() as $row) {
                if (empty($row->getCellIterator()->current()->getValue())) {
                    continue;
                }
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                foreach ($cellIterator as $index => $cell) {
                    if ($row->getRowIndex() === 1) {
                        $headers[] = $cell->getValue();
                    } else {
                        // Convert only the third cell (index 2) to a readable date format
                        $value = $cell->getValue();
                        $rowData[] = $value;
                    }
                }
                if ($row->getRowIndex() === 1) {
                    continue;
                }
                $columnData = array_combine($headers, $rowData);
                // Add custom logic to handle numeric values with 'E' notation
                foreach ($columnData as &$value) {
                    if (is_numeric($value) && strpos($value, 'E') !== false) {
                        $value = number_format($value, 0, '.', '');
                    }
                }
                $total_record++;
                if ($columnData['STATUS'] == 'REALISED' || $columnData['STATUS'] == 'RETURNED') {
                    if ($columnData['STATUS'] == 'REALISED') {
                        $total_realised_record++;
                        $total_realised_amount = $total_realised_amount+$columnData['TRANSACTION AMOUNT'];
                        $requiredKeys = [
                            'STATUS',
                            'POOLING ACCOUNT NUMBER',
                            'CREDIT DATE',
                            'TRANSACTION AMOUNT',
                            // 'Customer Transaction Ref No',
                            'IFSC/MICR CODE',
                            'DEBIT ACCOUNT NUMBER',
                            'CREDIT CONSOLIDATION NUMBER',
                            'UMRN NUMBER'
                        ];
                        $numericKeys = [
                            'CREDIT CONSOLIDATION NUMBER',
                            'POOLING ACCOUNT NUMBER',
                            'DEBIT ACCOUNT NUMBER',
                            // 'Customer Transaction Ref No',
                            'TRANSACTION AMOUNT'
                        ];
                    } else if ($columnData['STATUS'] == 'RETURNED') {
                        $total_returned_record++;
                        $requiredKeys = [
                            'STATUS',
                            'CREDIT DATE',
                            'TRANSACTION AMOUNT',
                            // 'Customer Transaction Ref No',
                            'IFSC/MICR CODE',
                            'DEBIT ACCOUNT NUMBER',
                            'UMRN NUMBER'
                        ];
                        $numericKeys = [
                            'DEBIT ACCOUNT NUMBER',
                            // 'Customer Transaction Ref No',
                            'TRANSACTION AMOUNT'
                        ];
                    } else {
                        return redirect()->back()->with('alert', 'Error: ' . "Unknown status for account " . $columnData['Customer Transaction Ref No'] . '');
                    }
                    $patternKeys = [
                        'UMRN NUMBER' => '/^[A-Za-z]{4}\d{16}$/',
                        // 'IFSC/MICR CODE' => '/^[A-Za-z]{4}\d{7}$/',
                    ];

                    $missingKeys = [];
                    $nonNumericKeys = [];
                    $patternMismatchKeys = [];

                    foreach ($requiredKeys as $key) {
                        if (!isset($columnData[$key])) {
                            $missingKeys[] = $key;
                        } elseif (in_array($key, $numericKeys) && !is_numeric($columnData[$key])) {
                            $nonNumericKeys[] = $key;
                        } elseif (array_key_exists($key, $patternKeys) && !preg_match($patternKeys[$key], $columnData[$key])) {
                            $patternMismatchKeys[] = $key;
                        }
                    }
                    if (!empty($missingKeys) || !empty($nonNumericKeys) || !empty($patternMismatchKeys)) {
                        $errorMessage = '';

                        if (!empty($missingKeys)) {
                            $errorMessage .= 'The following fields are missing: ' . implode(', ', $missingKeys) . '. ';
                        }

                        if (!empty($nonNumericKeys)) {
                            $errorMessage .= 'The following fields must be numeric: ' . implode(', ', $nonNumericKeys) . '. ';
                        }

                        if (!empty($patternMismatchKeys)) {
                            $errorMessage .= 'The following fields do not match the required pattern: ' . implode(', ', $patternMismatchKeys) . '.';
                        }

                        // Throw an exception or handle the error as needed
                        return redirect()->back()->with('alert', 'Error: ' . $errorMessage);
                    }
                    $format = 'd-m-Y';
                    $chkdate = DateTime::createFromFormat($format, $columnData['CREDIT DATE']);
                    if ($chkdate == false) {
                        return redirect()->back()->with('alert', 'Error: ' . 'Date format mismatch for ' . $columnData['Customer Transaction Ref No']);
                    }
                    $timestamp1 = strtotime($columnData['CREDIT DATE']);
                    $timestamp2 = strtotime(date($format));
                    if ($timestamp1 > $timestamp2) {
                        return redirect()->back()->with('alert', 'Error: ' . 'You cannot make a transaction in future date for ' . $columnData['Customer Transaction Ref No']);
                    }
                    $columnData['Customer Transaction Ref No'] = Memberloans::where('ecs_ref_no', $columnData['UMRN NUMBER'])->value('account_number')
                    ?? Grouploans::where('ecs_ref_no', $columnData['UMRN NUMBER'])->value('account_number')
                    ?? null;
                    if (!empty($columnData['Customer Transaction Ref No'])) {
                        $importData = [
                            'utr_transaction_number' => $columnData['CREDIT CONSOLIDATION NUMBER'],
                            'customer_account_number' => $columnData['DEBIT ACCOUNT NUMBER'],
                            'customer_ifsc_code' => $columnData['IFSC/MICR CODE'],
                            'account_number' => $columnData['Customer Transaction Ref No'],
                            'amount' => $columnData['TRANSACTION AMOUNT'],
                            'date' => $columnData['CREDIT DATE'],
                            'bank_acc_no' => $columnData['POOLING ACCOUNT NUMBER'],
                            'transaction_status' => $columnData['STATUS'],
                            'ecs_ref_no' => $columnData['UMRN NUMBER'],
                        ];
                        if (Memberloans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->exists()) {
                            $member_loan_company_id = Memberloans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->first(['company_id', 'account_number', 'branch_id', 'id'])->toArray() ?? null;
                            $importData['loan_type'] = 'loan';
                            $a_id = CollectorAccount::where('type', 2)->where('type_id', $member_loan_company_id['id'])->where('status', 1)->value('associate_id');
                        } else if (Grouploans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->exists()) {
                            $member_loan_company_id = Grouploans::where('account_number', $columnData['Customer Transaction Ref No'])->where('emi_option', 1)->where('status', 4)->first(['company_id', 'account_number', 'branch_id', 'id'])->toArray() ?? null;
                            $importData['loan_type'] = 'Group loan';
                            $a_id = CollectorAccount::where('type', 3)->where('type_id', $member_loan_company_id['id'])->where('status', 1)->value('associate_id');
                        } else {
                            return redirect()->back()->with('alert', $columnData['Customer Transaction Ref No'] . " Account number not found");
                        }
                        // $loan_charge = LoanCharge::where('loan_type', $member_loan_company_id)->where('company_id',)
                        $sbmfa_company_id = SamraddhBankAccount::where('account_no', $columnData['POOLING ACCOUNT NUMBER'])->exists() ? SamraddhBankAccount::where('account_no', $columnData['POOLING ACCOUNT NUMBER'])->first(['company_id', 'bank_id', 'account_no'])->toArray() : "n";
                        if ($member_loan_company_id == null) {
                            array_push($array2, $columnData['Customer Transaction Ref No']);
                        } elseif ($columnData['STATUS'] == 'REALISED') {
                            if ($member_loan_company_id['company_id'] != $sbmfa_company_id['company_id']) {
                                array_push($array, $columnData['Customer Transaction Ref No']);
                            } else {
                                $importData['branch_name'] = getBranchDetail($member_loan_company_id['branch_id'])->name;
                                $importData['bank_name'] = getSamraddhBank($sbmfa_company_id['bank_id'])->bank_name;
                                $associate = getMemberCustom($a_id);
                                $importData['associate_name'] = $associate->firstname . ' ' . $associate->last_name ?? ' ';
                                $importData['associate_no'] = $associate->associate_no;
                                $importData['associate_id'] = $a_id;
                                $importData['loan_id'] = $member_loan_company_id['id'];
                                $importData['branch'] = $member_loan_company_id['branch_id'];
                                $importData['company_id'] = $member_loan_company_id['company_id'];
                                $importData['company_name'] = Companies::whereId($member_loan_company_id['company_id'])->value('name');
                                $arr = array_merge($importData, $member_loan_company_id, $sbmfa_company_id);
                                array_push($return, $arr);
                                array_push($important, $importData);
                            }
                        } else {
                            $importData['branch_name'] = getBranchDetail($member_loan_company_id['branch_id'])->name;
                            $importData['bank_name'] = null;
                            $importData['account_no'] = null;
                            $importData['utr_transaction_number'] = null;
                            $associate = getMemberCustom($a_id);
                            $importData['associate_name'] = $associate->firstname . ' ' . $associate->last_name ?? ' ';
                            $importData['associate_no'] = $associate->associate_no;
                            $importData['associate_id'] = $a_id;
                            $importData['loan_id'] = $member_loan_company_id['id'];
                            $importData['branch'] = $member_loan_company_id['branch_id'];
                            $importData['company_id'] = $member_loan_company_id['company_id'];
                            $importData['company_name'] = Companies::whereId($member_loan_company_id['company_id'])->value('name');
                            $arr = array_merge($importData, $member_loan_company_id);
                            array_push($return, $arr);
                            array_push($important, $importData);
                        }
                    }
                }
            }
            if (empty($array)) {
                $data = array();
                $data['dataarray'] = $return;
                $title = 'Loan | Bank ECS Preview';
                return view('templates.admin.loan.ecs_transaction.import_inner', [
                    'data' => $data,
                    'important' => $important,
                    'title' => $title,
                    'total_realised_amount' =>$total_realised_amount,
                    'total_record' => $total_record,
                    'total_realised_record' => $total_realised_record,
                    'total_returned_record' => $total_returned_record,
                ]);
                // $importData->save();
                // DB::commit();
            } else {
                $a = $b;
            }
            // Commit the transaction
            // DB::commit();
            // redirect()->back()->with('success', 'XLSX file imported successfully');
        } catch (\Exception $e) {
            // DB::rollback();
            if (!empty($array)) {
                $string = implode(', ', $array);
                $string = "Company mismatch in account $string so no data inserted.";
                $string2 = "";
                if (!empty($array2)) {
                    $string2 = implode(', ', $array2);
                    $string2 = "  These accounts are considered invalid $string2";
                }
                return redirect()->back()->with('alert', "$string $string2");
            }
            return redirect()->back()->with('alert', 'Error: ' . $e->getMessage() . $e->getLine());
        }
    }
    public function ecs_import_listing(Request $request)
    {
    }
    public function import_data(Request $request)
    {
        DB::beginTransaction();
        try {
            $assas = array();
            $in_case_of_fail = array();
            foreach ($request['data'] as $req) {
                $req['date'] =  date('Y-m-d', strtotime($req['date']));
                $old_date = ECSTransaction::where('account_number', $req['account_number'])->value('date');
                if ($old_date) {
                    if ($old_date == $req['date']) {
                        return response()->json('A transaction for the account number ' . $req['account_number'] . ' on the date ' . $req['date'] . ' has already been processed.', 302);
                    }
                }
                $associate_id = $req['associate_no'];
                $associateDetail = Member::where('associate_no', $associate_id)->first();
                if ($req['transaction_status'] == 'REALISED') {
                    $bank_bal = SamraddhBankAccount::where('account_no', $req['bank_acc_no'])->first(['bank_id', 'id']);
                    $entryTime = date("H:i:s");
                    $createDayBook = $DayBookref  = CommanController::createBranchDayBookReference($req['amount']);
                    $deposit = $req['deposite_amount'] = $req['amount'];
                    $loanId = $req['loan_id'];
                    $branchId = $req['branch'];
                    if ($req['loan_type'] == 'loan') {
                        $mLoan = Memberloans::with(['loanMember', 'loan'])->where('id', $req['loan_id'])->first();
                    } elseif ($req['loan_type'] == 'Group loan') {
                        $mLoan = Grouploans::with(['loanMember', 'loanBranch', 'loan'])->where('id', $req['loan_id'])->first();
                    }
                    $importData = [
                        'loan_type' => substr($req['loan_type'], 0, 1),
                        'account_number' => $req['account_number'],
                        'amount' => $req['amount'],
                        'loan_id' => $req['loan_id'],
                        'date' => $req['date'],
                        'daybook_ref_id' => $createDayBook,
                        'bank_acc_no' => $req['bank_acc_no'],
                        'transaction_status' => 1,
                        'utr_transaction_number' => $req['utr_transaction_number'],
                        'bank_name' => $req['bank_name'],
                        'company_id' => $req['company_id'],
                        'transaction_type' => 1,
                        'branch_id' => $req['branch'],
                        'associate_id' => $associateDetail->id,
                    ];
                    $ecs = ECSTransaction::create($importData);
                    $companyId = $mLoan->company_id;
                    $application_date = $req['created_at'] = $req['application_date'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($importData['date'])));
                    Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($importData['date']))));
                    $globaldate = date('Y-m-d', strtotime(convertDate($importData['date'])));
                    $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
                    $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
                    $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
                    $CurrentDateYear = date('Y');
                    $CurrentDateMonth = date('m');
                    $applicationDate = date('Y-m-d', strtotime(convertDate($req['application_date'])));
                    $applicationCurrentDate = date('d', strtotime(convertDate($req['application_date'])));
                    $applicationCurrentDateYear = date('Y', strtotime(convertDate($req['application_date'])));
                    $applicationCurrentDateMonth = date('m', strtotime(convertDate($req['application_date'])));
                    $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                    $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
                    $roi = 0; //$accruedInterest['accruedInterest'];
                    $principal_amount = 0; //$accruedInterest['principal_amount'];
                    $totalDayInterest = 0;
                    $totalDailyInterest = 0;
                    $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
                    $newDate = array();
                    $deposit = $req['deposite_amount'];
                    if ($lastOutstanding != NULL && isset($lastOutstanding->out_standing_amount)) {
                        $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
                        $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
                        if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
                            if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                                $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
                            } else {
                                $preDate = current($nextEmiDates);
                                $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
                                if ($mLoan->emi_option == 1) {
                                    // $previousDate = Carbon::parse($oldDate)->subMonth(1); 
                                    $previousDate = date('Y-m-d', strtotime("-1 month", strtotime($oldDate)));
                                }
                                $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
                                if ($preDate == $applicationDate) {
                                    $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                                } else {
                                    $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                                }
                                if ($aqmount > 0) {
                                    $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi   + $aqmount);
                                } else {
                                    $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
                                }
                            }
                            $dailyoutstandingAmount = $outstandingAmount + $roi;
                        }
                        $deposit =  $req['deposite_amount'];
                    } else {
                        // $gapDayes = Carbon::parse($mLoan->approve_date)->diff(Carbon::parse($applicationDate))->format('%a');
                        if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
                        {
                            if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                                $outstandingAmount = ($mLoan->amount - $deposit);
                            } else {
                                $outstandingAmount = ($mLoan->amount - $deposit + $roi);
                            }
                            $dailyoutstandingAmount = $outstandingAmount + $roi;
                        } else {
                            $outstandingAmount = ($mLoan->amount - $principal_amount);
                        }
                        $deposit =  $req['deposite_amount'];
                        $dailyoutstandingAmount = $mLoan->amount + $roi;
                    }
                    $amountArraySsb = array(
                        '1' => $req['deposite_amount']
                    );
                    if (isset($ssbAccountDetails['ssbMember'])) {
                        $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
                    } else {
                        $amount_deposit_by_name = NULL;
                    }
                    $dueAmount = $mLoan->due_amount - round($principal_amount);

                    if ($req['loan_type'] == 'loan') {
                        $mlResult = Memberloans::find($req['loan_id']);
                        $rec_msg = "Loan Recovery   - Loan EMI payment";
                    } elseif ($req['loan_type'] == 'Group loan') {
                        $mlResult = Grouploans::find($req['loan_id']);
                        $rec_msg = "Group Loan Recovery - Loan EMI payment";
                    }
                    // $mlResult = Memberloans::find($req['loan_id']);
                    $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
                    $lData['due_amount'] = $dueAmount;
                    $lData['accrued_interest'] = $mLoan->accrued_interest - $roi;
                    if ($dueAmount == 0) {
                        //$lData['status'] = 3;
                        //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($req['application_date'])));
                    }
                    $lData['received_emi_amount'] = $mLoan->received_emi_amount + $req['deposite_amount'];

                    // $num = $newDate->format('d');
                    // $numm = $newDate->format('m');
                    // $year = $newDate->format('Y');
                    // switch (true) {
                    //     case $num >= 1 && $num <= 12:
                    //         $result = 12;
                    //         break;
                    //     case $num > 12 && $num <= 22:
                    //         $result = 22;
                    //         break;
                    //     case $num > 22 && $numm == 1:
                    //         $result = (new DateTime("$year-03-01"))->modify('-1 day')->format('d');
                    //         break;
                    //     default:
                    //         $result = 30;
                    //         break;
                    // }
                    $newDate = ($mlResult->emi_due_date) ? date('Y-m-d', strtotime($mlResult->emi_due_date . ' +1 month')) : date('Y-m-d', strtotime($req['date'] . ' +1 month'));
                    $lData['emi_due_date'] = $newDate;
                    $lData['is_bounce'] = 0;
                    // $lData['ecs_ref_no'] = $req['ecs_ref_no'];
                    $mlResult->update($lData);
                    // add log
                    $postData = $_POST;
                    $enData = array(
                        "post_data" => $postData,
                        "lData" => $lData
                    );
                    $encodeDate = json_encode($enData);
                    $arrs = array(
                        "load_id" => $loanId,
                        "type" => "7",
                        "account_head_id" => 0,
                        "user_id" => Auth::user()->id,
                        "message" => $rec_msg,
                        "data" => $encodeDate
                    );
                    if ($mLoan->branch_id != (int)$req['branch']) {
                        $code = '(' . getBranchCode($req['branch']) . ')';
                    } else {
                        $code = '';
                    }
                    $desType = "Loan EMI deposit $code";
                    $req['loan_emi_payment_mode'] = 1;
                    $req['bank_transfer_mode'] = 1;
                    $req['associate_member_id'] = $associateDetail['id'];
                    if ($req['loan_emi_payment_mode'] == 1) {
                        $cheque_dd_no = NULL;
                        $paymentMode = 3;
                        $ssbpaymentMode = 5;
                        $online_payment_id = $req['utr_transaction_number'];
                        $online_payment_by = NULL;
                        $satRefId = NULL;
                        $bank_name = NULL;
                        $cheque_date = NULL;
                        $account_number = NULL;
                        $paymentDate = date("Y-m-d", strtotime(convertDate($req['application_date'])));
                        $ssb_transaction_id = '';
                        $ssb_account_id_from = '';
                        $ssbCreateTran = NULL;
                    }
                    $ssbCreateTran = NULL;
                    // $ssbCreateTran = CommanController::createTransaction
                    // No Entry in Day Book table as per current Updates Changes Done by Sourab
                    // $createDayBook = CommanController::createDayBook
                    if ($req['bank_transfer_mode'] == 0 && $req['bank_transfer_mode'] != '') {
                        $checkData['type'] = 4;
                        $checkData['branch_id'] = $req['branch'];
                        // $checkData['loan_id']=$req['loan_id'];
                        // $checkData['day_book_id'] = $createDayBook;
                        // $checkData['cheque_id'] = $cheque_dd_no;
                        $checkData['status'] = 1;
                        $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['application_date'])));
                        $dataRC['status'] = 3;
                        $type = 1;
                    }
                    $member_data = Member::whereId($mLoan->customer_id)->select('id', 'first_name', 'last_name')->first();
                    /************* Head Implement ****************/
                    if ($req['loan_emi_payment_mode'] == 1) {
                        $loan_head_id = $mLoan['loan']->head_id;
                        if ($req['bank_transfer_mode'] == 1) {
                            $cheque_id = NULL;
                            $transactionPaymentMode = 2;
                            $payment_type = 2;
                            $amount_from_id = $member_data->id;
                            // $amount_from_name = customGetMemberData($req['associate_member_id'])->first_name . ' ' . customGetMemberData($req['associate_member_id'])->last_name;
                            $cheque_type = NULL;
                            $amount_from_name = ($member_data->first_name) ? $member_data->first_name : null . ($member_data->last_name ? $member_data->last_name : null);
                            $cheque_no = NULL;
                            $cheque_date = NULL;
                            $cheque_bank_from = NULL;
                            $cheque_bank_ac_from = NULL;
                            $cheque_bank_ifsc_from = NULL;
                            $cheque_bank_branch_from = NULL;
                            $cheque_bank_to = NULL;
                            $cheque_bank_ac_to = NULL;
                            $transction_no = $req['utr_transaction_number'];
                            $v_no = NULL;
                            $v_date = NULL;
                            $ssb_account_id_from = NULL;
                            $transction_bank_from = substr($req['customer_ifsc_code'], 0, 4);
                            $transction_bank_ac_from = $req['customer_account_number'];
                            $transction_bank_ifsc_from = $req['customer_ifsc_code'];
                            $transction_bank_branch_from = null;
                            $banc_acc_datta = SamraddhBankAccount::where('account_no', $req['bank_acc_no'])->first(['company_id', 'bank_id', 'account_no', 'ifsc_code', 'id', 'account_head_id']);
                            $transction_bank_to = $banc_acc_datta['bank_id'];
                            $transction_bank_ac_to = $req['bank_acc_no'];
                            $company_name = $req['bank_name'];
                            $ifsc = $banc_acc_datta['ifsc_code'];
                            $bankId =  $banc_acc_datta->id;
                            $head_id =  $banc_acc_datta->account_head_id;
                            $company_bankId = $banc_acc_datta['bank_id'];
                        }
                        $principalbranchDayBook = CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $createDayBook, $req['associate_member_id'], $member_id = ($req['loan_type'] == 'loan') ? $mLoan->applicant_id : $mLoan->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $deposit, '' . $mLoan->account_number . ' EMI collection', 'Bank A/C Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . 'Bank A/C Cr ' . ($deposit) . '',  'CR', $payment_type, 'INR',  $v_no, $ssb_account_id_from = NULL, $cheque_no, $transction_no, $req['application_date'], date("H:i:s"), 1, Auth::user()->id, $req['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id, $companyId);
                        $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($DayBookref, $bank_id = $company_bankId, $account_id = $bankId, 5, 52, $loanId, $createDayBook, $req['associate_member_id'], ($req['loan_type'] == 'loan') ? $mLoan->applicant_id : $mLoan->member_id, $req['branch'], $deposit, $deposit, $deposit, 'EMI collection', 'Online A/C Cr. ' . ($deposit) . '', 'Online A/C Cr. ' . ($deposit) . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $req['application_date'], $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $req['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);
                    }
                    /************* Head Implement ****************/
                    /*---------- commission script  start  ---------*/
                    $daybookId = $createDayBook;
                    $total_amount = $req['deposite_amount'];
                    $percentage = 2;
                    $month = NULL;
                    $type_id = $req['loan_id'];
                    $type = 4;
                    $branch_id = $req['branch'];
                    $commission_type = 0;
                    $carder = $associateDetail->current_carder_id ?? null;
                    $associate_exist = 0;
                    $percentInDecimal = $percentage / 100;
                    $commission_amount = round($percentInDecimal * $total_amount, 4);
                    $associateCommission['member_id'] = $associate_id;
                    $associateCommission['branch_id'] = $branch_id;
                    $associateCommission['type'] = $type;
                    $associateCommission['type_id'] = $type_id;
                    $associateCommission['day_book_id'] = $daybookId;
                    $associateCommission['total_amount'] = $total_amount;
                    $associateCommission['month'] = $month;
                    $associateCommission['commission_amount'] = $commission_amount;
                    $associateCommission['percentage'] = $percentage;
                    $associateCommission['commission_type'] = $commission_type;
                    // $date = \App\Models\Daybook::where('id', $daybookId)->first();
                    $associateCommission['created_at'] = $req['created_at'];
                    $associateCommission['pay_type'] = 4;
                    $associateCommission['carder_id'] = $carder;
                    $associateCommission['associate_exist'] = $associate_exist;
                    /*---------- commission script  end  ---------*/
                    $req['recovery_module'] = 1;
                    $createLoanDayBook = CommanController::createLoanDayBook(
                        $DayBookref,
                        $DayBookref,
                        $mLoan->loan_type,
                        0,
                        $loanId,
                        $lId = NULL,
                        $mLoan->account_number,
                        ($req['loan_type'] == 'loan') ? $mLoan->applicant_id : $mLoan->member_id,
                        $roi,
                        $principal_amount,
                        $dueAmount,
                        $req['deposite_amount'],
                        $desType,
                        $req['branch'],
                        getBranchCode($req['branch'])->branch_code,
                        'CR',
                        'INR',
                        $paymentMode,
                        $cheque_dd_no,
                        $bank_name,
                        $branch_name = NULL,
                        $paymentDate,
                        $online_payment_id,
                        1,
                        1,
                        $cheque_date,
                        $account_number,
                        // substr($req['customer_ifsc_code'],0, 4),
                        // $member_data->id,
                        // ($member_data->first_name)?$member_data->first_name:null.($member_data->last_name?$member_data->last_name:null),
                        // $req['branch'],
                        substr($req['customer_ifsc_code'], 0, 4),
                        // $req['associate_name'],
                        ($member_data->first_name) ? $member_data->first_name : null . ($member_data->last_name ? $member_data->last_name : null),
                        $associateDetail['id'],
                        // $req['branch'],
                        $member_data->id,
                        $totalDailyInterest,
                        $totalDayInterest,
                        0
                        /**penelty */
                        ,
                        $companyId,
                        $req['recovery_module']
                    );
                    $heatcreatee = $this->headTransaction($createLoanDayBook, $transactionPaymentMode, 1, $in_case_of_fail);
                    if ($heatcreatee != true) {
                        return response()->json('Emi issue for account ' . $req['account_number'], 302);
                        dd("Da");
                    }
                    $totalDepsoit = LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                    // event(new UserActivity($createLoanDayBook,'Loan Emi',$req));
                    $text = 'Dear Member,Received Rs.' .  $req['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($req['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
                    $temaplteId = 1207166308935249821;
                    $contactNumber = array();
                    $memberDetail = Member::find($mLoan->customer_id);
                    $contactNumber[] = $memberDetail->mobile_no;
                    $sendToMember = new Sms();
                    $sendToMember->sendSms( $contactNumber, $text, $temaplteId);

                    
                    array_push($assas, $text);
                } else {
                    /**
                     * 
                     * Below code is for
                     * Failed case
                     * 
                     */
                    $globaldate = date('Y-m-d', strtotime(convertDate($req['date'])));
                    // $mLoan = \App\Models\Memberloans::with(['loanMember', 'loan'])->where('id', $req['loan_id'])->first();
                    $loanId = $req['loan_id'];
                    if ($req['loan_type'] == 'loan') {
                        $mLoan = Memberloans::with(['loanMember', 'loan', 'loanBranch'])->where('id', $req['loan_id'])->first();
                        $member_auto_id = $mLoan['applicant_id'];
                    } elseif ($req['loan_type'] == 'Group loan') {
                        $mLoan = Grouploans::with(['loanMember', 'loanBranch', 'loan'])->where('id', $req['loan_id'])->first();
                        $member_auto_id = $mLoan['member_id'];
                    }
                    $ssbHead = \App\Models\Plans::where('company_id', $mLoan['company_id'])->where('plan_category_code', 'S')->first();
                    $companyId = $mLoan['company_id'];
                    $paymentHead = $ssbHead->deposit_head_id;
                    $getBounceChargeSetting = \App\Models\HeadSetting::where('head_id', 435)->first();

                    $loanBounceCharges = \App\Models\LoanCharge::where('min_amount', '<=', $mLoan['amount'])->where('max_amount', '>=', $mLoan['amount'])->where('loan_id', $mLoan['loan_type'])->where('type', 4)->where('status', 1)->where('tenure', $mLoan['emi_period'])->where('emi_option', $mLoan['emi_option'])->where('effective_from', '<=', (string) $globaldate)->first();

                    $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $mLoan['company_id'])->exists();
                    $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id', 'applicable_date')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
                    $ssbAccountDetails = $ssbBalance = \App\Models\SavingAccount::find($mLoan->ssb_id);
                    $bounceGstAmount = 0;
                    $entryTime = date('H:i:s');
                    Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['date']))));
                    if ($getGstSetting) {
                        if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                            $bounceGstAmount = (($loanBounceCharges->charge * $getBounceChargeSetting->gst_percentage) / 100) / 2;
                            $cgstHead = 171;
                            $sgstHead = 172;
                            $IntraState = true;
                        } else {
                            $bounceGstAmount = ($loanBounceCharges->charge * $getBounceChargeSetting->gst_percentage) / 100;
                            $cgstHead = 170;
                            $IntraState = false;
                        }
                        $penalty = 0;
                    } else {
                        $penalty = 0;
                    }
                    $bounceGstAmount = ceil($bounceGstAmount);


                    if (isset($loanBounceCharges)) {
                        // dd('hjkhj');
                        $deductAmount = $loanBounceCharges['charge'];
                        $totalAmountBounce = $loanBounceCharges['charge'] + $bounceGstAmount + $bounceGstAmount;
                        // dd($bounceGstAmount);
                        // DB::beginTransaction();

                        $dayBookRefs = \App\Http\Controllers\Admin\CommanController::createBranchDayBookReference($totalAmountBounce);
                        $record1 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->whereDate('created_at', '<=', date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['date']))))->where('is_deleted', 0)->whereCompanyId($companyId)->orderby('id', 'desc')
                            ->first();
                        // dd($record1);

                        $ssb['saving_account_id'] = $ssbAccountDetails->id;
                        $ssb['account_no'] = $ssbAccountDetails->account_no;
                        $ssb['opening_balance'] = $record1->opening_balance ?? 0 - $totalAmountBounce;
                        $ssb['branch_id'] = $req['branch'];
                        $ssb['type'] = 9;
                        $ssb['deposit'] = 0;
                        $ssb['withdrawal'] = $totalAmountBounce;
                        $ssb['description'] = 'Emi Bounce Charge to ' . $mLoan->account_number;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['company_id'] = $companyId;
                        $ssb['payment_mode'] = 9;
                        $ssb['daybook_ref_id'] = $dayBookRefs;
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['date'])));
                        $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                        $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                        // update saving account current balance
                        $ssbBalance = \App\Models\SavingAccount::find($ssbAccountDetails->id);
                        $ssbBalance->balance = $ssbBalance->balance - $totalAmountBounce;
                        $ssbBalance->save();
                        $record2 = \App\Models\SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                            ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($req['date']))))->where('is_deleted', 0)->get();
                        foreach ($record2 as $key => $value) {
                            $nsResult = \App\Models\SavingAccountTranscation::find($value->id);
                            $nsResult['opening_balance'] = $value->opening_balance;
                            $nsResult['company_id'] = $companyId;
                            $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['date'])));
                            $nsResult->save();
                        }
                        $data['saving_account_transaction_id'] = $ssb_transaction_id;
                        $data['loan_id'] = $req['loan_id'];
                        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['date'])));
                        $satRefId = null;
                        $updateSsbDayBook = self::updateSsbDayBookAmount($req['amount'], $ssbAccountDetails['account_no'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($req['date']))), $companyId);
                        // set next emi due date and is_bounce
                        $mlData = \App\Models\Memberloans::find($req['loan_id']);
                        $emiDueDate = $mlData['emi_due_date'] ?? $req['date'];

                        if ($mlData['emi_option'] == 1) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 month', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 2) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 week', strtotime($emiDueDate)));
                        } elseif ($mlData['emi_option'] == 3) {
                            $emiDueDate = date('Y-m-d', strtotime('+1 day', strtotime($emiDueDate)));
                        } else {
                            $emiDueDate = $mlData['emi_due_date'];
                        }

                        $loanData['is_bounce'] = 1;
                        $loanData['emi_due_date'] = $emiDueDate;
                        $mlData->update($loanData);
                        // End set next emi due date and is_bounce

                        // ECS transaction table entery



                        // Ecs trasaction table entry End 
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, $paymentHead, 4, 551, $mLoan->ssb_id, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'DR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $mLoan['company_id']);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 435, 5, 551, null, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $deductAmount, 'Bounce Charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $deductAmount . '', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $mLoan['company_id']);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 4, 551, $mLoan->ssb_id, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $totalAmountBounce, 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $totalAmountBounce . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($req['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 551, $member_auto_id, $dayBookRefs, $mLoan['associate_member_id'], null, $branch_id_to = NULL, $mLoan['branch_id'], $loanBounceCharges['charge'], 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To ' . $mLoan['account_number'] . ' A/C Dr ' . $loanBounceCharges['charge'] . '', 'Bounce charge To Bank A/C Cr ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($req['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);



                        // Bounce charge entry in loan_day_book  commented as per anup sir instruction 04-03-24

                        // $createLoanDayBook = self::createLoanDayBook($dayBookRefs, $dayBookRefs, $mLoan['loan_type'], 3, $mLoan->ssb_id, $lId = NULL, $ssbAccountDetails->account_no, $member_auto_id, 0, 0, 0, $totalAmountBounce, "Bounce Charge from saving account  $ssbAccountDetails->account_no", $mLoan['branch_id'], getBranchCode($mLoan['branch_id'])->branch_code, 'CR', 'INR', 4, NULL, NULL, $branch_name = NULL, $paymentDate, NULL, 1, 1, NULL, $mLoan->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $mLoan['company_id'], null, $bounceGstAmount, $bounceGstAmount);

                        // Calculate intrest through cron
                        $stateId = branchName()->state_id;
                        $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                        $currentDate = date('Y-m-d', strtotime($currentDate));
                        $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $req['account_number'], 0]);
                        // Calculate intrest through cron

                        // Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                        if (isset($bounceGstAmount) && $bounceGstAmount > 0) {


                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 171, 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge CGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $mLoan['company_id']);

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRefs, $mLoan['branch_id'], $bank_id = NULL, $bank_ac_id = NULL, 172, 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, '' . $mLoan['account_number'] . ' ' . 'Bounce Charge SGST ', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no = null, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $mLoan['company_id']);



                            $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 553, null, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST ' . $mLoan['account_number'] . '', 'Bounce Charge SGST' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);


                            $branchDayBook = CommanController::branchDayBookNew($dayBookRefs, $mLoan['branch_id'], 5, 552, null, $dayBookRefs, $mLoan['associate_member_id'], $member_auto_id, $branch_id_to = NULL, $mLoan['branch_id'], $bounceGstAmount, 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'Bounce charge CGST  ' . $mLoan['account_number'] . '', 'Bounce charge CGST ' . $mLoan['account_number'] . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $mLoan['company_id']);
                            Session::get('created_at');
                            $createdGstTransaction = CommanController::gstTransaction($dayBookRefs, $getGstSettingno->gst_no, null, $totalAmountBounce, $getBounceChargeSetting->gst_percentage, ($IntraState == false ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true ? $bounceGstAmount : 0), ($IntraState == true) ? $bounceGstAmount + $bounceGstAmount : $bounceGstAmount, 435, date("Y-m-d", strtotime(convertDate($req['date']))), 'BC435', $mLoan['customer_id'], $mLoan['branch_id'], $mLoan['company_id']);
                        }
                        $importData = [
                            'loan_type' => substr($req['loan_type'], 0, 1),
                            'account_number' => $req['account_number'],
                            'amount' => $req['amount'],
                            'loan_id' => $req['loan_id'],
                            'date' => $req['date'],
                            'daybook_ref_id' => $dayBookRefs,
                            'bank_acc_no' => $req['bank_acc_no'],
                            'transaction_status' => 0,
                            'bounce_charge' => $totalAmountBounce,
                            'utr_transaction_number' => $req['utr_transaction_number'],
                            'bank_name' => $req['bank_name'],
                            'company_id' => $req['company_id'],
                            'transaction_type' => 1,
                            'branch_id' => $req['branch'],
                            'associate_id' => $associateDetail->id,
                            'cgst_charge' => $bounceGstAmount,
                            'sgst_charge' => $bounceGstAmount,
                        ];
                        $ecs = ECSTransaction::create($importData);
                        // end Bounce charge gst is greater than 0 then gst entries will go in all the related tables
                        $res = ['line' => 0, 'message' => "Bounce charge has been deducted from your account. !"];
                    }

                    if ($req['loan_type'] == 'loan') {
                        $mlResult = Memberloans::find($req['loan_id']);
                    } elseif ($req['loan_type'] == 'Group loan') {
                        $mlResult = Grouploans::find($req['loan_id']);
                    }
                    $update['is_bounce'] = 1;
                    $mlResult->update($update);

                    $text = 'Dear Member, Your Loan ECS bounced on ' . date('d/m/Y', strtotime(convertDate($req['date']))) . ' Bounce charge Rs ' . $totalAmountBounce . ' deducted from your SSB ' . $ssbAccountDetails->account_no . ' Samraddh Bestwin Microfinance';
                             $temaplteId = 1207171074323291072;
                             $contactNumber = array();
                             $memberDetail = \App\Models\Member::find($mLoan->customer_id);
                             $contactNumber[] = $memberDetail->mobile_no;
                             $sendToMember = new Sms();
                             $sendToMember->sendSms($contactNumber, $text, $temaplteId);
                }
            }
            DB::commit();
            return response()->json('success', 200);
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getLine(), $ex->getMessage(), $ex->getFile(), $ex->getCode());
            return back()->with('alert', $ex->getMessage() . '' . $ex->getLine());
        }
    }
    public function headTransaction($loanDaybookId, $paymentMode, $loanType, $in_case_of_fail = null)
    {
        try {
            $allHeadAccruedEntry = array();
            $allHeadPrincipleEntry = array();
            $allHeadpaymentEntry = array();
            $allHeadpaymentEntry2 = array();
            $calculatedDate = '';
            $value = \App\Models\LoanDayBooks::findorfail($loanDaybookId);
            $loansDetail = \App\Models\Loans::where('id', $value->loan_type)->first();
            if ($loansDetail->loan_type == 'L') {
                $loansRecord = Memberloans::where('account_number', $value->account_number)->first();
                $subType = 545;
            } else {
                $loansRecord = Grouploans::where('account_number', $value->account_number)->first();
                $subType = 546;
            }
            $calculatedDate = date('Y-m-d', strtotime($value->created_at));
            $date = $value;
            $rr = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->where('id', '<', $value->id)->orderBY('created_at', 'desc')->first();
            $rangeDate = (isset($date->created_at)) ? date('Y-m-d', strtotime($date->created_at)) : $calculatedDate;
            $stateId = branchName()->state_id;
            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
            $currentDate = date('Y-m-d', strtotime($currentDate));
            $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$rangeDate, $value->account_number, 0]);
            if (isset($rr->created_at)) {
                $strattDate = date('Y-m-d', strtotime($rr->created_at));;
                $endDate = date('Y-m-d', strtotime($date->created_at));;
            } else {
                $strattDate = date('Y-m-d', strtotime($loansRecord->approve_date));;
                $endDate = $calculatedDate;
            }
            $accuredSumCR = \App\Models\AllHeadTransaction::where('type', '5')->where('sub_type', $subType)->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $loansRecord->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
            $accuredSumDR = \App\Models\AllHeadTransaction::where('type', '5')->where('sub_type', $subType)->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $loansRecord->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
            $emiData = \App\Models\LoanEmisNew::where('emi_date', $rangeDate)->where('loan_type', $value->loan_type)->where('loan_id', $value->loan_id)->where('is_deleted', '0')->first();
            if (!isset($emiData)) {
                DB::rollback();
                return false;
            }
            $accuredSum = $accuredSumDR - $accuredSumCR;

            if ($value->deposit <= $accuredSum) {
                $accruedAmount = $value->deposit;
                $principalAmount = 0;
            } else {
                $accruedAmount = $accuredSum;
                $principalAmount = $value->deposit - $accuredSum;
            }
            $paymentHead = '';
            if ($value->payment_mode == 0) {
                $paymentHead = 28;
            }
            if ($value->payment_mode == 4) {
                $ssbHead = \App\Models\Plans::where('company_id', $loansRecord->company_id)->where('plan_category_code', 'S')->first();
                $paymentHead = $ssbHead->deposit_head_id;
            }
            if ($value->payment_mode == 1 || $value->payment_mode == 2 || $value->payment_mode == 3) {
                $getSamraddhData = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                $getHead = \App\Models\SamraddhBank::where('id', $getSamraddhData->bank_id)->first();
                $paymentHead = $getHead->account_head_id;
                $bankId = $getSamraddhData->bank_id;
                $bankAcId = $getSamraddhData->account_id;
            }


            $allHeadAccruedEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $loansDetail->ac_head_id,
                'type' => 5,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'sub_type' => $subType,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $accruedAmount,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'CR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',


                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => Auth::user()->id,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL


            ];

            $allHeadPrincipleEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $loansDetail->head_id,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'type' => 5,
                'sub_type' => ($loansDetail->loan_type != 'G') ? 52 : 55,
                'type_id' => $emiData->id,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $principalAmount,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'CR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => Auth::user()->id,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL


            ];
            $allHeadpaymentEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $paymentHead,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'type' => 5,
                'sub_type' => ($loansDetail->loan_type != 'G') ? 52 : 55,
                'type_id' => $emiData->id,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $value->deposit,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'DR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => Auth::user()->id,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL


            ];



            $dataInsert1 = \App\Models\AllHeadTransaction::insert($allHeadAccruedEntry);
            $dataInsert2 = \App\Models\AllHeadTransaction::insert($allHeadPrincipleEntry);
            $dataInsert3 = \App\Models\AllHeadTransaction::insert($allHeadpaymentEntry);
            // DB::commit();
            return true;
        } catch (\Exception $ex) {
            // DB::rollback();
            dd($ex->getMessage(), $ex->getLine(), $ex->getCode());
            return back()->with('alert', $ex->getMessage());
        }
    }

    public static function updateSsbDayBookAmount($amount, $account_number, $date, $companyId)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $getCurrentBranchRecord = \App\Models\SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', $entryDate)->whereCompanyId($companyId)->where('is_deleted', 0)->first();
        if (isset($getCurrentBranchRecord->id)) {
            $bResult = \App\Models\SavingAccountTranscation::find($getCurrentBranchRecord->id);
            $bData['opening_balance'] = $getCurrentBranchRecord->opening_balance - $amount;
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
        }
        $getNextBranchRecord = \App\Models\SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', '>', $entryDate)->whereCompanyId($companyId)->where('is_deleted', 0)->orderby('created_at', 'ASC')
            ->get();
        if ($getNextBranchRecord) {
            foreach ($getNextBranchRecord as $key => $value) {
                $sResult = \App\Models\SavingAccountTranscation::find($value->id);
                $sData['opening_balance'] = $value->opening_balance - $amount;
                $sData['company_id'] = $companyId;
                $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $sResult->update($sData);
            }
        }
    }
}
