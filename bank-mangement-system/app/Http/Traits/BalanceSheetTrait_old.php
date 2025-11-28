<?php
namespace App\Http\Traits;
use DB;
trait BalanceSheetTrait
{
    public function getHeadData($model, $model2, $request)
    {
        $branch = '';
        // Fetch all the data according to model
        if (isset($request->date)) {
            $date = $request->date;
        } elseif (isset($request->start_date)) {
            $date = $request->start_date;
        }
        if (isset($request->end_date)) {
            $end_date = $request->end_date;
        } elseif (isset($request->ends_date)) {
            $end_date = $request->ends_date;
        } elseif (isset($request->to_date)) {
            $end_date = $request->to_date;
        }
        if (isset($request->branch)) {
            $branch = $request->branch;
        } elseif (isset($request->branch_filter)) {
            $branch = $request->branch_filter;
        } elseif (isset($request->branch_id)) {
            $branch = $request->branch_id;
        } elseif (isset($_GET["branch_id"])) {
            $branch = $_GET["branch_id"];
        }
        if (isset($request->head_id)) {
            $head_id = $request->head_id;
        } elseif (isset($request->head)) {
            $head_id = $request->head;
        }
        $head_ids = array($head_id);
        $accounthead = $model2::where('head_id', $head_id)->where('status', 0)->first();
        $subHeadsIDS = $model2::where('head_id', $head_id)->where('status', 0)->pluck('head_id')->toArray();
        $return_array = 0;
        if (count($subHeadsIDS) > 0) {
            $head_ids = array_merge($head_ids, $subHeadsIDS);
            $return_array = get_change_sub_account_head($head_ids, $subHeadsIDS, true);
        }
        if ($return_array) {
            foreach ($return_array as $key => $value) {
                $ids[] = $value;
            }
        }
        if (count($ids) > 0) {
            $data = $model::with(['branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->whereIn('head_id', $ids)->where('is_deleted', 0)->where('amount', '!=', 0);
            $dataCR = $model::with('branch')->whereIn('head_id', $ids)->where('amount', '!=', 0)->where('is_deleted', 0)->where('amount', '!=', 0);
        } else {
            $data = $model::with(['branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->whereIn('head_id', [$head_id])->where('is_deleted', 0);
            $dataCR = $model::with('branch')->whereIn('head_id', $ids)->where('amount', '!=', 0)->where('is_deleted', 0);
        }
        if ($branch != '') {
            $data = $data->where('branch_id', $branch);
        }
        if ($date != '') {
            $convertDate = date("Y-m-d", strtotime(convertDate($date)));
            $end_date = date("Y-m-d", strtotime(convertDate($end_date)));
            $data = $data->whereBetween('entry_date', [$convertDate, $end_date]);
        }
        if (isset($_POST['pages'])) {
            if ($_POST['pages'] == 1) {
                $totalAmount = 0;
            } else {
                $totalAmount = $_POST['total'];
            }
        }
        if ($branch != '') {
            $dataCR = $dataCR->where('branch_id', $branch);
        }
        if ($date != '') {
            $date = date("Y-m-d", strtotime(convertDate($date)));
            $to_date = date("Y-m-d", strtotime(convertDate($end_date)));
            $dataCR = $dataCR->whereBetween('entry_date', [$date, $to_date]);
        }
        if (isset($_POST['pages'])) {
            if ($_POST['pages'] == "1") {
                $length = ($_POST['pages']) * $_POST['length'];
            } else {
                $length = ($_POST['pages'] - 1) * $_POST['length'];
            }
            $dataCR = $dataCR->offset(0)->limit($length)->get();
        } else {
            $dataCR = $dataCR->get();
        }
        if ($accounthead->cr_nature == 1) {
            //echo "111"; die;
            $totalDR = $dataCR->where('payment_type', 'DR')->sum('amount');
            $totalCR = $dataCR->where('payment_type', 'CR')->sum('amount');
            $totalAmount = $totalCR - $totalDR;
        } else {
            //echo "222"; die;
            $totalDR = $dataCR->where('payment_type', 'DR')->sum('amount');
            $totalCR = $dataCR->where('payment_type', 'CR')->sum('amount');
            $totalAmount = $totalDR - $totalCR;
        }
        if (isset($_POST['pages'])) {
            if ($_POST['pages'] == "1") {
                $totalAmount = 0;
            }
        }
        //     if($_POST['pages'] == 1)
        //     {
        //         $totalAmount  = 0;
        //     }
        //     else{
        //         $totalAmount  = $_POST['total'];
        //     }
        //     if(count($ids)>0)
        //     {
        //         $dataCR = $model::with('branch')->whereIn('head_id',$ids)->where('is_deleted',0);
        //     } else {
        //         $dataCR = $model::with('branch')->whereIn('head_id',[$head_id])->where('is_deleted',0);
        //     }
        //     if($branch != '')
        //     {
        //         $dataCR = $dataCR->where('branch_id',$branch);
        //     }
        //     if($date != '')
        //     {
        //         $startdate = date("Y-m-d", strtotime(convertDate($date)));
        //         $enddate = date("Y-m-d", strtotime(convertDate($end_date)));
        //         $dataCR = $dataCR->whereBetween('entry_date',[$startdate,$enddate]);
        //     }
        //     if($_POST['pages'] == "1"){
        //         $length = ($_POST['pages']) * $_POST['length'];
        //     } else {
        //         $length = ($_POST['pages']-1) * $_POST['length'];
        //     }
        //     $dataCR = $dataCR->offset(0)->limit($length)->get();
        //     if($accounthead->cr_nature == 1)
        //     {
        //         //echo "111"; die;
        //         $totalDR = $dataCR->where('payment_type','DR')->sum('amount');
        //         $totalCR = $dataCR->where('payment_type','CR')->sum('amount');
        //         $totalAmountssssss = $totalCR - $totalDR;
        //     } else{
        //         //echo "222"; die;
        //         $totalDR = $dataCR->where('payment_type','DR')->sum('amount');
        //         $totalCR = $dataCR->where('payment_type','CR')->sum('amount');
        //         $totalAmountssssss = $totalDR - $totalCR;
        //     }
        //     if($_POST['pages'] == "1"){
        //         $totalAmountssssss = 0;
        //     }
        return [$data, $accounthead, $dataCR, $totalAmount];
    }
    public function getCompleteDetail($value, $isDaybook = NULL)
    {
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
            $record = \App\Models\ReceivedVoucher::where('id', $value->type_id)->first();
            if ($record) {
                $type = $record->particular;
            } else {
                $type = "N/A";
            }
        }
        $branchName = 'N/A';
        if ($isDaybook != 1) {
            if (isset($value['branch'])) {
                $branchName = $value['branch']->name;
            }
        }
        // Member Name, Member Account and Member Id
        $memberData = getMemberInvestment($value->type_id);
        $loanData = getLoanDetail($value->type_id);
        $groupLoanData = getGroupLoanDetailById($value->type_id);
        $DemandAdviceData = \App\Models\DemandAdvice::where('id', $value->type_id)->where('is_deleted',0)->first();
        
        $freshExpenseData = \App\Models\DemandAdviceExpense::where('id', $value->type_id)->first();
        $memberName = '';
        $memberAccount = '';
        $plan_name = '';
        $memberId = '';
        $associate_name = '';
        $associateno = '';
        $rent_type = 'N/A';
        $tags = '';
        if (isset($getTransType->tags)) {
            $tags = $getTransType->tags;
        }
        if ($value->payment_mode == 6) {
            $rentPaymentDetail = \App\Models\RentLiabilityLedger::with('rentLib')->where('id', $value->type_transaction_id)->first();
            $salaryDetail = \App\Models\EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
        } else {
            $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $value->type_transaction_id)->first();
            $salaryDetail = \App\Models\EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
        }
        if ($value->type == 14) {
            $voucherDetail = \App\Models\ReceivedVoucher::where('id', $value->type_transaction_id)->first();
            if ($voucherDetail != '') {
                if ($voucherDetail->type == 1) {
                    if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                        $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                        $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                        if (isset($bankAccount)) {
                            $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                        }
                        $memberName = AccountHeads::where('head_id', $voucherDetail->director_id)->first();
                        $memberName = $memberName->sub_head;
                    }
                }
                if ($voucherDetail->type == 2) {
                    if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                        $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                        $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                        if (isset($bankAccount)) {
                            $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                        }
                        $memberName = \App\Models\AccountHeads::where('head_id', $voucherDetail->shareholder_id)->first();
                        $memberName = $memberName->sub_head;
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
                        // $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                        $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                        if (isset($bankAccount)) {
                            $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                        } else {
                            $memberName = getAcountHead($voucherDetail->eli_loan_id);
                        }
                    }
                }
            }
        }
        if ($value->type == 1) {
            if ($value->type_id) {
                // $memberName = getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name;
                // $memberId = getMemberData($value->type_id)->member_id;
                // $memberAccount = getMemberData($value->type_id)->member_id;

                $memberName = !empty($value->memberCompany->member) ? $value->memberCompany->member->first_name. ' ' . $value->memberCompany->member->last_name : 'N/A';
                $memberId = !empty($value->memberCompany->member) ? $value->memberCompany->member->member_id : '';
                $memberAccount = $memberId ;
                $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name. ' ' . $value->associateMember->last_name : '';


            }
        } elseif ($value->type == 2) {
            if ($value->type_id) {
                $memberName = !empty( $value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member->first_name. ' ' . $value->member_investment->memberCompany->member->last_name : 'N/A';
                $memberId = !empty( $value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member_id : 'N/A';
                $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name. ' ' . $value->associateMember->last_name : 'N/A';
            }
        } elseif ($value->type == 3) {
            if ($value->member_id) {    
              
                $memberName = !empty( $value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member->first_name. ' ' . $value->member_investment->memberCompany->member->last_name : 'N/A';
                $memberId = !empty( $value->member_investment->memberCompany) ? $value->member_investment->memberCompany->member_id : 'N/A';
                $plan_name = !empty($value->member_investment->plan) ? $value->member_investment->plan->name : 'N/A';
                $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name. ' ' . $value->associateMember->last_name : 'N/A';
                $associateno = !empty($value->associateMember) ? $value->associateMember->associate_no : 'N/A';


            //     $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
            //     $plan_name = getPlanDetail($memberData->plan_id)->name;
            //     $memberId = getMemberData($value->member_id)->member_id;
             }
            if ($memberData) {
                $memberAccount = $memberData->account_number;
            }
        } elseif ($value->type == 4) {
            
            $plan_name ='Saving Account';
            if ($value->sub_type == 45) {
                $memberId = !empty(getMemberData($value->member_id)) ? getMemberData($value->member_id)->associate_no : 'N/A';
                $memberName = !empty(getMemberData($value->member_id)) ? getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name : 'N/A';
            } else {
                if ($value->member_id) {
                    
                    $memberName = $value->memberCompanybyMemberId->member->first_name . ' ' .  $value->memberCompanybyMemberId->member->last_name;
                    $memberId =  $value->memberCompanybyMemberId->member->member_id;
                }
            }
            if ($value->associate_id) {
                $associate_name =  $value->associateMember->first_name. ' ' . $value->associateMember->last_name;
                $associateno = $value->associateMember->associate_no;
            }
            if ($value->sub_type == 42) {
                $memberAccount = \App\Models\SavingAccountTranscation::where('id', $value->type_transaction_id)->first();
                if (isset($memberAccount->account_no)) {
                    $memberAccount = $memberAccount->account_no;
                }
            } else {
                $memberAccount = getSsbAccountNumber($value->type_id);
                if ($memberAccount) {
                   

                    $memberAccount = $memberAccount->account_no;
                }
            }
            if ($memberData) {
                $memberAccount = $memberData->account_number;
            }
        } elseif ($value->type == 5) {
            if ($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57 || $value->sub_type == 511 || $value->sub_type == 513 || $value->sub_type == 515 || $value->sub_type == 523 || $value->sub_type == 525 || $value->sub_type == 527 || $value->sub_type == 528 || $value->sub_type == 529 || $value->sub_type == 530 || $value->sub_type == 531 || $value->sub_type == 532 || $value->sub_type == 533 || $value->sub_type == 534 || $value->sub_type == 535) {
                if ($loanData) {
                    
                    $memberName = $loanData->member->first_name . ' ' .$loanData->member->last_name ;
                    $memberId = $loanData->loanMemberCompany->member_id ;
                    $plan_name = $loanData->loan->name;
                    $memberAccount = $loanData->account_number;
                    $associate_name = $loanData->loanMemberCustom->first_name . ' ' .$loanData->loanMemberCustom->last_name ;
                }
            } elseif ($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58 || $value->sub_type == 512 || $value->sub_type == 514 || $value->sub_type == 516 || $value->sub_type == 518 || $value->sub_type == 524 || $value->sub_type == 526 || $value->sub_type == 526 || $value->sub_type == 536 || $value->sub_type == 537 || $value->sub_type == 538 || $value->sub_type == 539 || $value->sub_type == 540 || $value->sub_type == 541 || $value->sub_type == 542 || $value->sub_type == 543 || $value->sub_type == 544) {
                if ($groupLoanData) {

                    $memberName = $groupLoanData->loanMemberCompanyid->member->first_name . ' ' .$groupLoanData->loanMemberCompanyid->member->last_name ;
                    $memberId = $groupLoanData->loanMemberCompanyid->member_id ;
                    $plan_name = $groupLoanData->loan->name;
                    $memberAccount = $groupLoanData->account_number;
                    $associate_name = $groupLoanData->children->first_name . ' ' .$groupLoanData->children->last_name ;





                    $memberName = !empty(getMemberData($loanData->applicant_id)) ? getMemberData($groupLoanData->member_id)->first_name . ' ' . getMemberData($groupLoanData->member_id)->last_name : 'N/A';
                    $memberId = !empty(getMemberData($loanData->applicant_id)) ? getMemberData($groupLoanData->member_id)->member_id : '';
                }
                $memberAccount = $groupLoanData->account_number;
                $plan_name = 'Group Loan';
            }
        } elseif ($value->type == 6) {
            if (isset($salaryDetail['ledger_employee']->employee_name)) {
                $memberName = $salaryDetail['ledger_employee']->employee_name;
                $memberAccount = $salaryDetail['ledger_employee']->employee_code;
            } elseif (isset($salaryDetail['salary_employee']->employee_name)) {
                $memberName = $salaryDetail['salary_employee']->employee_name;
                $memberAccount = $salaryDetail['salary_employee']->employee_code;
            }
        } elseif ($value->type == 7 || $value->type == 18) {
            $data = \App\Models\SamraddhBank::where('id', $value->transction_bank_to)->first();
            if (isset($data->bank_name)) {
                $a = getSamraddhBankAccountId($value->transction_bank_to);
                $memberName = $data->bank_name;
                $memberAccount = $a->account_no;
            }
            if (isset($value->bank_id)) {
                $a = getSamraddhBankAccountId($value->bank_id);
                $bankDetail = getSamraddhBank($value->bank_id);
                if (isset($bankDetail->bank_name)) {
                    $memberName = $bankDetail->bank_name;
                }
                if (isset($a->account_no)) {
                    $memberAccount = $a->account_no;
                }
            }
        } elseif ($value->type == 8) {
            $data = \App\Models\SamraddhBank::where('id', $value->transction_bank_to)->first();
            if (isset($data->bank_name)) {
                $a = getSamraddhBankAccountId($data->id);
                $memberName = $data->bank_name;
                $memberAccount = $a->account_no;
            }
        } elseif ($value->type == 9) {
            $associate_id = getMemberData($value->member_id)->associate_no;
            $associate_name = 'N/A';
            if (isset($value->member_id)) {
                $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                $memberId = getMemberData($value->member_id)->associate_no;
                $memberAccount = getMemberData($value->member_id)->associate_no;
            }
        } elseif ($value->type == 10) {
            if ($rentPaymentDetail['rentLib']) {
                if ($rentPaymentDetail) {
                    $memberName = $rentPaymentDetail['rentLib']->owner_name;
                }
            }
            if (isset($rentPaymentDetail['rentLib']->rent_type)) {
                $rent_type = $rentPaymentDetail['rentLib']->rent_type;
            }
            $rent_details = DB::table('account_heads')->where("head_id", $rent_type)->first();
            if (!empty($rent_details)) {
                $rent_type = $rent_details->sub_head;
            }
            $memberAccount = 'N/A';
        } elseif ($value->type == 11) {
            if ($DemandAdviceData['employee_name']) {
                $memberName = $DemandAdviceData->party_name;
            }
            $memberAccount = 'N/A';
        } elseif ($value->type == 12) {
            if ($value->sub_type == 121 || $value->sub_type == 122) {
                if (!empty($salaryDetail['salary_employee'])) {
                    $memberName = $salaryDetail['salary_employee']->employee_name;
                    $memberAccount = $salaryDetail['salary_employee']->employee_code;
                }
            }
        } elseif ($value->type == 13) {
            if ($value->sub_type == 131) {
                if ($freshExpenseData) {
                    $memberAccount = $freshExpenseData['advices']->voucher_number;
                    $memberId = $freshExpenseData->bill_number;
                    $memberName = $freshExpenseData->party_name;
                }
            }
            if ($value->sub_type == 132) {
                if ($freshExpenseData) {
                    $memberName = $DemandAdviceData->employee_name;
                    $memberAccount = $DemandAdviceData->employee_code;
                    $memberId = $DemandAdviceData->employee_code;
                }
            }
			// print_r($DemandAdviceData->investment_id); die;
            if ($value->sub_type == 133) {
                
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number ?? '';
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id) ?? '';
                $plan_name = getPlanDetail($plan_id->plan_id)->name ?? '';
                $memberName = getMemberDatalatest($plan_id->member_id)->first_name ?? '' . ' ' . getMemberDatalatest($plan_id->member_id)->last_name ?? '';
                $memberId = getMemberData($plan_id->member_id)->member_id ?? '';
            }
            if ($value->sub_type == 134) {
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $memberName = getMemberData($plan_id->member_id)->first_name . ' ' . getMemberData($plan_id->member_id)->last_name;
                $memberId = getMemberData($plan_id->member_id)->member_id;
            }
            if ($value->sub_type == 135) {
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $memberName = getMemberData($plan_id->member_id)->first_name . ' ' . getMemberData($plan_id->member_id)->last_name;
                $memberId = getMemberData($plan_id->member_id)->member_id;
            }
            if ($value->sub_type == 136) {
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $memberName = getMemberData($plan_id->member_id)->first_name . ' ' . getMemberData($plan_id->member_id)->last_name;
                $memberId = getMemberData($plan_id->member_id)->member_id;
            }
            if ($value->sub_type == 137) {
                $data = getMemberInvestment($DemandAdviceData->investment_id);
                if (isset($data->account_number)) {
                    $memberAccount = $data->account_number;
                    $memberName = getMemberData($data->member_id)->first_name . ' ' . getMemberData($data->member_id)->last_name;
                    $memberId = getMemberData($data->member_id)->member_id;
                }
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
            if ($value->sub_type == 139) {
                if (!empty($rentPaymentDetail['rentLib'])) {
                    if ($rentPaymentDetail) {
                        $memberName = $rentPaymentDetail['rentLib']->owner_name;
                    }
                }
                if (isset($rentPaymentDetail['rentLib']->rent_type)) {
                    $rent_type = $rentPaymentDetail['rentLib']->rent_type;
                }
                $rent_details = DB::table('account_heads')->where("head_id", $rent_type)->first();
                if (!empty($rent_details)) {
                    $rent_type = $rent_details->sub_head;
                }
            }
        } elseif ($value->type == 14) {
            if ($value->sub_type == 143) {
                $data = \App\Models\Employee::where('id', $value->type_id)->first();
                if (isset($data->employee_name)) {
                    $memberName = $data->employee_name;
                    $memberId = $data->employee_code;
                    $memberAccount = $memberId;
                }
            } else {
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
                            $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id);
                            if (isset($memberAccount->account_no)) {
                                $memberAccount = $memberAccount->account_no;
                            }
                            $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                            if (isset($bankAccount)) {
                                $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                            }
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
            } else if ($value->sub_type == 302 || $value->sub_type == 303) {
                $detail = \App\Models\CompanyBoundTransaction::where('daybook_ref_id', $value->daybook_ref_id)->first();
                if ($detail) {
                    $record = \App\Models\CompanyBound::where('id', $detail->bound_id)->first();
                    $memberAccount = $record->fd_no;
                    $memberName = $record->bank_name;
                }
            }
        } elseif ($value->type == 21) {
            $memberName = getMemberDatalatest($value->member_id)->first_name . ' ' . getMemberDatalatest($value->member_id)->last_name;
            $memberId = getMemberDatalatest($value->member_id)->member_id;
            $memberAccount = getMemberDatalatest($value->member_id)->member_id;
        }
        if ($value->type == 20) {
            $record = \App\Models\Expense::where('id', $value->type_transaction_id)->first();
            if (isset($record->bill_no)) {
                $memberAccount = 'Bill No.' . $record->bill_no;
            }
            $name = \App\Models\BillExpense::where('daybook_refid', $value->daybook_ref_id)->first();
            if (isset($name->party_name)) {
                $memberName = $name->party_name;
            }
            if (isset($record->account_head_id) && $record->sub_head1 && $record->sub_head2) {
                $mainHead = getAcountHeadData($record->account_head_id);
                $subHead = getAcountHeadData($record->sub_head1);
                $subHead2 = getAcountHeadData($record->sub_head2);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead . '/' . $subHead2;
            } elseif (isset($record->account_head_id) && $record->sub_head1) {
                $mainHead = getAcountHeadData($record->account_head_id);
                $subHead = getAcountHeadData($record->sub_head1);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead;
            } elseif (isset($record->account_head_id)) {
                $mainHead = getAcountHeadData($record->account_head_id);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead;
            }
        } elseif ($value->type == 26) {
            $record = \App\Models\BankingLedger::where('id', $value->type_id)->first();
            if (isset($record->vendor_type)) {
                if ($record->vendor_type == 0) {
                    $memberDetail = \App\Models\RentLiability::where('id', $record->vendor_type_id)->first();
                    $memberName = $memberDetail->owner_name;
                    $memberId = $memberDetail->employee_code;
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
                $rent_type = $record->description;
                $memberName = $rent_type;
            }
        }
        if ($value->type == 10) {
            $type = $value->description;
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
        if ($value->type == 27) {
            $data = \App\Models\Vendor::select('id', 'name', 'company_name')->where('id', $value->type_id)->first();
            if ($value->sub_type == 271) {
                $data2 = \App\Models\VendorBill::select('id', 'bill_number')->where('id', $value->type_transaction_id)->first();
                $memberName = $data->company_name . ' (' . $data->name . ')';
                $memberAccount = $data2->bill_number;
            }
            if ($value->sub_type == 272) {
                $data2 = \App\Models\VendorBillPayment::select('id', 'vendor_bill_id')->where('id', $value->type_transaction_id)->first();
                $data3 = \App\Models\VendorBill::select('id', 'bill_number')->where('id', $data2->vendor_bill_id)->first();
                $memberName = $data->company_name . ' (' . $data->name . ')';
                $memberAccount = $data3->bill_number;
            }
            if ($value->sub_type == 275) {
                $memberName = $data->company_name . ' (' . $data->name . ')';
            }
        }
        if ($value->type == 25) {
            $memberName = $value->description;
        }
        if ($value->type == 29) {
            if ($value->sub_type == 291 || $value->sub_type == 295) {
                $ssbdata = getSavingAccountMemberId($value->type_id);
                $memberAccount = $ssbdata->account_no;
                $memberName = getMemberDetails($ssbdata->member_id)->first_name . ' ' . getMemberDetails($ssbdata->member_id)->last_name;
            }
            if ($value->sub_type == 292) {
                $ssbdata = \App\Models\DebitCard::where('id', $value->type_transaction_id)->first();
                if (isset($ssbdata->ssb_id)) {
                    $ssbdata = getSavingAccountMemberId($ssbdata->ssb_id);
                    $memberAccount = $ssbdata->account_no;
                    $memberId = $ssbdata->account_no;
                    $memberName = getMemberDetails($ssbdata->member_id)->first_name . ' ' . getMemberDetails($ssbdata->member_id)->last_name;
                }
            }
        }
      
        return ['memberName' => $memberName, 'memberAccount' => $memberAccount, 'type' => $type, 'plan_name' => $plan_name, 'rent_type' => $rent_type, 'memberId' => $memberId, 'associate_name' => $associate_name, 'branchName' => $branchName, 'associateno' => $associateno, 'tag' => $tags];
    }
    public function getCompleteDetail2($value, $isDaybook = NULL)
    {
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
            $record = \App\Models\ReceivedVoucher::where('id', $value->type_id)->first();
            if ($record) {
                $type = $record->particular;
            } else {
                $type = "N/A";
            }
        }
        $branchName = 'N/A';
        if ($isDaybook != 1) {
            if (isset($value['branch'])) {
                $branchName = $value['branch']->name;
            }
        }
        // Member Name, Member Account and Member Id
        $memberData = getMemberInvestment($value->type_id);
        $loanData = getLoanDetail($value->type_id);
        $groupLoanData = getGroupLoanDetailById($value->type_id);
        $DemandAdviceData = \App\Models\DemandAdvice::where('id', $value->type_id)->where('is_deleted', 0)->first();

        $freshExpenseData = \App\Models\DemandAdviceExpense::where('id', $value->type_id)->first();
        $memberName = '';
        $memberAccount = '';
        $plan_name = '';
        $memberId = '';
        $associate_name = '';
        $associateno = '';
        $rent_type = 'N/A';
        $tags = '';
        if (isset($getTransType->tags)) {
            $tags = $getTransType->tags;
        }
        if ($value->payment_mode == 6) {
            $rentPaymentDetail = \App\Models\RentLiabilityLedger::with('rentLib')->where('id', $value->type_transaction_id)->first();
            $salaryDetail = \App\Models\EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
        } else {
            $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $value->type_transaction_id)->first();
            $salaryDetail = \App\Models\EmployeeSalary::with('salary_employee')->where('id', $value->type_transaction_id)->first();
        }
        if ($value->type == 14) {
            $voucherDetail = \App\Models\ReceivedVoucher::where('id', $value->type_transaction_id)->first();
            if ($voucherDetail != '') {
                if ($voucherDetail->type == 1) {
                    if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                        $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                        $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                        if (isset($bankAccount)) {
                            $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                        }
                        $memberName = AccountHeads::where('head_id', $voucherDetail->director_id)->first();
                        $memberName = $memberName->sub_head;
                    }
                }
                if ($voucherDetail->type == 2) {
                    if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                        $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                        $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                        if (isset($bankAccount)) {
                            $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                        }
                        $memberName = \App\Models\AccountHeads::where('head_id', $voucherDetail->shareholder_id)->first();
                        $memberName = $memberName->sub_head;
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
                        // $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                        $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                        if (isset($bankAccount)) {
                            $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                        } else {
                            $memberName = getAcountHead($voucherDetail->eli_loan_id);
                        }
                    }
                }
            }
        }
        if ($value->type == 1) {
            if ($value->type_id) {
                // $memberName = getMemberData($value->type_id)->first_name . ' ' . getMemberData($value->type_id)->last_name;
                // $memberId = getMemberData($value->type_id)->member_id;
                // $memberAccount = getMemberData($value->type_id)->member_id;

                $memberName = !empty($value->memberCompany->member) ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : 'N/A';
                $memberId = !empty($value->memberCompany->member) ? $value->memberCompany->member->member_id : '';
                $memberAccount = $memberId;
                $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : '';
            }
        } elseif ($value->type == 2) {
            if ($value->type_id) {
                $memberName = !empty($value->memberInvestment->memberCompany) ? $value->memberInvestment->memberCompany->member->first_name . ' ' . $value->memberInvestment->memberCompany->member->last_name : 'N/A';
                $memberId = !empty($value->memberInvestment->memberCompany) ? $value->memberInvestment->memberCompany->member_id : 'N/A';
                $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : 'N/A';
            }
        } elseif ($value->type == 3) {
            if ($value->member_id) {
                $memberName = !empty($value->memberInvestment->memberCompany) ? $value->memberInvestment->memberCompany->member->first_name . ' ' . $value->memberInvestment->memberCompany->member->last_name : 'N/A';
                $memberId = !empty($value->memberInvestment->memberCompany) ? $value->memberInvestment->memberCompany->member_id : 'N/A';
                $plan_name = !empty($value->memberInvestment->plan) ? $value->memberInvestment->plan->name : 'N/A';
                $associate_name = !empty($value->associateMember) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : 'N/A';
                $associateno = !empty($value->associateMember) ? $value->associateMember->associate_no : 'N/A';


                //     $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                //     $plan_name = getPlanDetail($memberData->plan_id)->name;
                //     $memberId = getMemberData($value->member_id)->member_id;
            }
            if ($memberData) {
                $memberAccount = $memberData->account_number;
            }
        } elseif ($value->type == 4) {

            $plan_name = 'Saving Account';
            if ($value->sub_type == 45) {
                $memberId = !empty(getMemberData($value->member_id)) ? getMemberData($value->member_id)->associate_no : 'N/A';
                $memberName = !empty(getMemberData($value->member_id)) ? getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name : 'N/A';
            } else {
                if ($value->member_id) {

                    // $memberName = $value->memberCompanybyMemberId->member->first_name . ' ' .  $value->memberCompanybyMemberId->member->last_name ?? 'N/A';
                    // $memberId =  $value->memberCompanybyMemberId->member->member_id ?? 'N/A';
                }
            }
            if ($value->associate_id) {
                $id = $value->associate_id ;
                $column = ['first_name', 'last_name', 'id','associate_no'];
                $memberData = memberFieldData($column,  $id, 'id')->toArray();
                $associate_name = $memberData ? $memberData[0]['first_name'] . " " . $memberData[0]['last_name']??'' : 'N/A';
                $associateno = $memberData ? $memberData[0]['associate_no'] : 'N/A';
            }
            if ($value->sub_type == 42) {
                $memberAccount = \App\Models\SavingAccountTranscation::where('id', $value->type_transaction_id)->first();
                if (isset($memberAccount->account_no)) {
                    $memberAccount = $memberAccount->account_no;
                }
            } else {
                $memberAccount = getSsbAccountNumber($value->type_id);
                if ($memberAccount) {


                    $memberAccount = $memberAccount->account_no;
                }
            }
            // if ($memberData) {
            //     $memberAccount = $memberData->account_number;
            // }
        } elseif ($value->type == 5) {
            if ($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57 || $value->sub_type == 511 || $value->sub_type == 513 || $value->sub_type == 515 || $value->sub_type == 523 || $value->sub_type == 525 || $value->sub_type == 527 || $value->sub_type == 528 || $value->sub_type == 529 || $value->sub_type == 530 || $value->sub_type == 531 || $value->sub_type == 532 || $value->sub_type == 533 || $value->sub_type == 534 || $value->sub_type == 535) {
                if ($loanData) {
                    $memberName = $loanData->member->first_name . ' ' .$loanData->member->last_name ;
                        $memberId = $loanData->loanMemberCompany->member_id ;
                        $plan_name = $loanData->loan->name;
                        $memberAccount = $loanData->account_number;
                        $associate_name = $loanData->loanMemberCustom ? $loanData->loanMemberCustom->first_name . ' ' .$loanData->loanMemberCustom->last_name??'' :'N/A' ;
                }
            } elseif ($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58 || $value->sub_type == 512 || $value->sub_type == 514 || $value->sub_type == 516 || $value->sub_type == 518 || $value->sub_type == 524 || $value->sub_type == 526 || $value->sub_type == 526 || $value->sub_type == 536 || $value->sub_type == 537 || $value->sub_type == 538 || $value->sub_type == 539 || $value->sub_type == 540 || $value->sub_type == 541 || $value->sub_type == 542 || $value->sub_type == 543 || $value->sub_type == 544) {
                if ($groupLoanData) {
                    $memberName = $groupLoanData->member->first_name . ' ' .$groupLoanData->member->last_name ;
                    $memberId = $groupLoanData->loanMemberCompany->member_id ;
                    $plan_name = $groupLoanData->loan->name;
                    $memberAccount = $groupLoanData->account_number;
                    $associate_name =(isset($value->associate_id)) ? customGetMemberData($value->associate_id)->first_name.' '.customGetMemberData($value->associate_id)->last_name : '' ;
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
        } elseif ($value->type == 7 || $value->type == 18) {
            $data = \App\Models\SamraddhBank::where('id', $value->transction_bank_to)->first();
            if (isset($data->bank_name)) {
                $a = getSamraddhBankAccountId($value->transction_bank_to);
                $memberName = $data->bank_name;
                $memberAccount = $a->account_no;
            }
            if (isset($value->bank_id)) {
                $a = getSamraddhBankAccountId($value->bank_id);
                $bankDetail = getSamraddhBank($value->bank_id);
                if (isset($bankDetail->bank_name)) {
                    $memberName = $bankDetail->bank_name;
                }
                if (isset($a->account_no)) {
                    $memberAccount = $a->account_no;
                }
            }
        } elseif ($value->type == 8) {
            $data = \App\Models\SamraddhBank::where('id', $value->transction_bank_to)->first();
            if (isset($data->bank_name)) {
                $a = getSamraddhBankAccountId($data->id);
                $memberName = $data->bank_name;
                $memberAccount = $a->account_no;
            }
        } elseif ($value->type == 9) {
            $associate_id = getMemberData($value->member_id)->associate_no;
            $associate_name = 'N/A';
            if (isset($value->member_id)) {
                $memberName = getMemberData($value->member_id)->first_name . ' ' . getMemberData($value->member_id)->last_name;
                $memberId = getMemberData($value->member_id)->associate_no;
                $memberAccount = getMemberData($value->member_id)->associate_no;
            }
        } elseif ($value->type == 10) {
            if ($rentPaymentDetail['rentLib']) {
                if ($rentPaymentDetail) {
                    $memberName = $rentPaymentDetail['rentLib']->owner_name;
                }
            }
            if (isset($rentPaymentDetail['rentLib']->rent_type)) {
                $rent_type = $rentPaymentDetail['rentLib']->rent_type;
            }
            $rent_details = DB::table('account_heads')->where("head_id", $rent_type)->first();
            if (!empty($rent_details)) {
                $rent_type = $rent_details->sub_head;
            }
            $memberAccount = 'N/A';
        } elseif ($value->type == 11) {
            if ($DemandAdviceData['employee_name']) {
                $memberName = $DemandAdviceData->party_name;
            }
            $memberAccount = 'N/A';
        } elseif ($value->type == 12) {
            if ($value->sub_type == 121 || $value->sub_type == 122) {
                if (!empty($salaryDetail['salary_employee'])) {
                    $memberName = $salaryDetail['salary_employee']->employee_name;
                    $memberAccount = $salaryDetail['salary_employee']->employee_code;
                }
            }
        } elseif ($value->type == 13) {
            if ($value->sub_type == 131) {
                if ($freshExpenseData) {
                    $memberAccount = $freshExpenseData['advices']->voucher_number;
                    $memberId = $freshExpenseData->bill_number;
                    $memberName = $freshExpenseData->party_name;
                }
            }
            if ($value->sub_type == 132) {
                if ($freshExpenseData) {
                    $memberName = $DemandAdviceData ? $DemandAdviceData->employee_name : 'N/A';
                    $memberAccount = $DemandAdviceData ? $DemandAdviceData->employee_code : 'N/A';
                    $memberId = $DemandAdviceData ? $DemandAdviceData->employee_code : 'N/A';
                }
            }
            // print_r($DemandAdviceData->investment_id); die;
            if ($value->sub_type == 133) {

                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $dataaa = getmemberIdfromautoId($plan_id->member_id);
                $memberId = $dataaa->member_id;
                $column = ['first_name', 'last_name', 'id'];
                $id = $dataaa->customer_id;
                $memberData = memberFieldData($column,  $id, 'id')->toArray();
                $memberName = $memberData ? $memberData[0]['first_name'] . " " . $memberData[0]['last_name']??'' : 'N/A';
            }
            if ($value->sub_type == 134) {
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $dataaa = getmemberIdfromautoId($plan_id->member_id);
                $memberId = $dataaa->member_id;
                $column = ['first_name', 'last_name', 'id'];
                $id = $dataaa->customer_id;
                $memberData = memberFieldData($column,  $id, 'id')->toArray();
                $memberName = $memberData ? $memberData[0]['first_name'] . " " . $memberData[0]['last_name'] ?? '' : 'N/A';
            }
            if ($value->sub_type == 135) {
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $memberName = getMemberData($plan_id->member_id)->first_name . ' ' . getMemberData($plan_id->member_id)->last_name;
                $memberId = getMemberData($plan_id->member_id)->member_id;
            }
            if ($value->sub_type == 136) {
                $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
                $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
                $plan_name = getPlanDetail($plan_id->plan_id)->name;
                $memberName = getMemberData($plan_id->member_id)->first_name . ' ' . getMemberData($plan_id->member_id)->last_name;
                $memberId = getMemberData($plan_id->member_id)->member_id;
            }
            if ($value->sub_type == 137) {
                $data = getMemberInvestment($DemandAdviceData->investment_id);
                if (isset($data->account_number)) {
                    $memberAccount = $data->account_number;
                    $dataaa = getmemberIdfromautoId($data->member_id);
                    $memberId = $dataaa->member_id;
                    $column = ['first_name', 'last_name', 'id'];
                    $id = $dataaa->customer_id;
                    $memberData = memberFieldData($column,  $id, 'id')->toArray();
                    $memberName = $memberData ? $memberData[0]['first_name'] . " " . $memberData[0]['last_name']??'' : 'N/A';
                }
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
            if ($value->sub_type == 139) {
                if (!empty($rentPaymentDetail['rentLib'])) {
                    if ($rentPaymentDetail) {
                        $memberName = $rentPaymentDetail['rentLib']->owner_name;
                    }
                }
                if (isset($rentPaymentDetail['rentLib']->rent_type)) {
                    $rent_type = $rentPaymentDetail['rentLib']->rent_type;
                }
                $rent_details = DB::table('account_heads')->where("head_id", $rent_type)->first();
                if (!empty($rent_details)) {
                    $rent_type = $rent_details->sub_head;
                }
            }
        } elseif ($value->type == 14) {
            if ($value->sub_type == 143) {
                $data = \App\Models\Employee::where('id', $value->type_id)->first();
                if (isset($data->employee_name)) {
                    $memberName = $data->employee_name;
                    $memberId = $data->employee_code;
                    $memberAccount = $memberId;
                }
            } else {
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
                            $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id);
                            if (isset($memberAccount->account_no)) {
                                $memberAccount = $memberAccount->account_no;
                            }
                            $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                            if (isset($bankAccount)) {
                                $memberAccount = $memberAccount . '(' . $bankAccount->bank_name . ')';
                            }
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
            } else if ($value->sub_type == 302 || $value->sub_type == 303) {
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
            $memberAccount = getMemberData($value->member_id)->member_id;
        }
        if ($value->type == 20) {
            $record = \App\Models\Expense::where('id', $value->type_transaction_id)->first();
            if (isset($record->bill_no)) {
                $memberAccount = 'Bill No.' . $record->bill_no;
            }
            $name = \App\Models\BillExpense::where('daybook_refid', $value->daybook_ref_id)->first();
            if (isset($name->party_name)) {
                $memberName = $name->party_name;
            }
            if (isset($record->account_head_id) && $record->sub_head1 && $record->sub_head2) {
                $mainHead = getAcountHeadData($record->account_head_id);
                $subHead = getAcountHeadData($record->sub_head1);
                $subHead2 = getAcountHeadData($record->sub_head2);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead . '/' . $subHead2;
            } elseif (isset($record->account_head_id) && $record->sub_head1) {
                $mainHead = getAcountHeadData($record->account_head_id);
                $subHead = getAcountHeadData($record->sub_head1);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead;
            } elseif (isset($record->account_head_id)) {
                $mainHead = getAcountHeadData($record->account_head_id);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead;
            }
        } elseif ($value->type == 26) {
            $record = \App\Models\BankingLedger::where('id', $value->type_id)->first();
            if (isset($record->vendor_type)) {
                if ($record->vendor_type == 0) {
                    $memberDetail = \App\Models\RentLiability::where('id', $record->vendor_type_id)->first();
                    $memberName = $memberDetail->owner_name;
                    $memberId = $memberDetail->employee_code;
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
                $rent_type = $record->description;
                $memberName = $rent_type;
            }
        }
        if ($value->type == 10) {
            $type = $value->description;
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
        if ($value->type == 27) {
            $data = \App\Models\Vendor::select('id', 'name', 'company_name')->where('id', $value->type_id)->first();
            if ($value->sub_type == 271) {
                $data2 = \App\Models\VendorBill::select('id', 'bill_number')->where('id', $value->type_transaction_id)->first();
                $memberName = $data->company_name . ' (' . $data->name . ')';
                $memberAccount = $data2->bill_number;
            }
            if ($value->sub_type == 272) {
                $data2 = \App\Models\VendorBillPayment::select('id', 'vendor_bill_id')->where('id', $value->type_transaction_id)->first();
                $data3 = \App\Models\VendorBill::select('id', 'bill_number')->where('id', $data2->vendor_bill_id)->first();
                $memberName = $data->company_name . ' (' . $data->name . ')';
                $memberAccount = $data3->bill_number;
            }
            if ($value->sub_type == 275) {
                $memberName = $data->company_name . ' (' . $data->name . ')';
            }
        }
        if ($value->type == 25) {
            $memberName = $value->description;
        }
        if ($value->type == 29) {
            if ($value->sub_type == 291 || $value->sub_type == 295) {
                $ssbdata = getSavingAccountMemberId($value->type_id);
                $memberAccount = $ssbdata->account_no;
                $memberName = getMemberDetails($ssbdata->member_id)->first_name . ' ' . getMemberDetails($ssbdata->member_id)->last_name;
            }
            if ($value->sub_type == 292) {
                $ssbdata = \App\Models\DebitCard::where('id', $value->type_transaction_id)->first();
                if (isset($ssbdata->ssb_id)) {
                    $ssbdata = getSavingAccountMemberId($ssbdata->ssb_id);
                    $memberAccount = $ssbdata->account_no;
                    $memberId = $ssbdata->account_no;
                    $memberName = getMemberDetails($ssbdata->member_id)->first_name . ' ' . getMemberDetails($ssbdata->member_id)->last_name;
                }
            }
        }

        return ['memberName' => $memberName, 'memberAccount' => $memberAccount, 'type' => $type, 'plan_name' => $plan_name, 'rent_type' => $rent_type, 'memberId' => $memberId, 'associate_name' => $associate_name, 'branchName' => $branchName, 'associateno' => $associateno, 'tag' => $tags];
    }
    private function getLoanFrombankDetails($value)
    {
        $memberAccount = ($value->sub_type == 171) ? ($value->load('loanFromBank') ? $value->loanFromBank->loan_account_number : '') : (($value->sub_type == 172) ? ($value->load('loanEmi') ? $value->loanEmi->loanBank->loan_account_number : '') : '');
        $memberName = ($value->sub_type == 171) ? ($value->load('loanFromBank') ? $value->loanFromBank->bank_name : '') : (($value->sub_type == 172) ? ($value->load('loanEmi') ? $value->loanEmi->loan_bank_name : '') : '');
        $memberId = ($value->sub_type == 172) ? ($value->load('loanEmi') ? $value->loanEmi->account_head_id : '') : '';
        $data = [
            'memberAccount' => $memberAccount,
            'memberName' => $memberName,
            'memberId' => $memberId,
            'associate_name' => '',
            'associateno' => '',
        ];
        return $data;
    }
    private function getRentDetails($value)
    {
        $memberName = ($value->load('rentPayment')) ? $value->rentPayment->rentLib->owner_name : 'N/A';
        $data = [
            'memberAccount' => 'N/A',
            'memberName' => $memberName,
            'memberId' => 'N/A',
            'associate_name' =>'N/A',
            'associateno' => 'N/A',
        ];
        return $data;
    }
    private function getAssociateDetails($value)
    {
        $memberId = ($value->load('memberCompany')) ? (isset($value->member_id) ? $value->memberCompany->member_id : '') : '';
        $memberName = isset($value->member_id) && $value->memberCompany->load('member') ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : '';
        $associate_name =($value->load('associateMember')) ?  (isset($value->associate_id) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : '') : '';
        $associateno = ($value->load('associateMember')) ? ((isset( $value->associateMember->associate_no)) ? $value->associateMember->associate_no : ''): '';
        $data = [
            'memberAccount' => 'N/A',
            'memberName' => $memberName,
            'memberId' => $memberId,
            'associate_name' => $associate_name,
            'associateno' => $associateno,
         ];
         return $data;
    }
    private function getBranhcToHoDetails($value)
    {
        $memberName = isset($value->head_id) && $value->load('AccountHeads') ? $value->AccountHeads->sub_head : 'N/A';
        $memberAccount =  isset($value->head_id) && $value->load('AccountHeads') ? $value->AccountHeads->sub_head : 'N/A';
        $data = [
            'memberAccount' => 'N/A',
            'memberName' => $memberName,
            'memberId' => 'N/A',
            'associate_name' => 'N/A',
            'associateno' =>   'N/A',
         ];
         return $data;
    }
    private function getBankToBankDetails($value)
    {
        $data = isset($value->transction_bank_to) ? ($value->load('fundTransferBranchToHo')) ?? '' : '';
        $memberName = $data ? $data->bank_name : '';
        $memberAccount =  $memberName ? getSamraddhBankAccountId($data->id)->account_no : 'N/A';
        $data = [
            'memberAccount' => $memberAccount,
            'memberName' => $memberName??'N/A',
            'memberId' => 'N/A',
            'associate_name' => 'N/A',
            'associateno' =>   'N/A',
         ];
         return $data;
    }
    private function getDemandDetails($value)
    {   
        $freshExpenseData = $value->load('demand_advices_fresh_expenses') ? $value->demand_advices_fresh_expenses :'';
        $DemandAdviceData = $value->load('DemandAdvice') ?  $value->DemandAdvice : '';
        $rentPaymentDetail = $value->load('rentPayment') ? $value->rent_payment ? $value->rent_payment->rentLib : '' : '';
        // $mi = $value->load('member_investment') ?? '' ;
       
         if (in_array($value->sub_type, [131, 137])) {
            $memberAccount = $freshExpenseData ? $freshExpenseData['advices']->voucher_number : null;
            $memberId = $freshExpenseData ? $freshExpenseData->bill_number : null;
            $memberName = $freshExpenseData ? $freshExpenseData->party_name : null;
        }
        if ($value->sub_type == 132 && $freshExpenseData) {
            $memberName = $DemandAdviceData->employee_name;
            $memberAccount = $DemandAdviceData->employee_code;
            $memberId = $DemandAdviceData->employee_code;
        }
        if (in_array($value->sub_type, [133, 134, 135, 136])) {
            $memberAccount =$DemandAdviceData->investment->account_number;

            $memberId = $DemandAdviceData->investment->memberCompany->member_id;
            $memberName = $DemandAdviceData->investment->memberCompany->member->first_name.' '.$DemandAdviceData->investment->memberCompany->member->last_name ?? '';
            $associateno = $DemandAdviceData->investment->associateMember->associate_no;
        }
        if ($value->sub_type == 137) {
            $data = getMemberInvestment($DemandAdviceData->investment_id);
            if (isset($data->account_number)) {
                $memberAccount = $data->account_number;
                $memberId = getmemberIdfromautoId($data->member_id)->member_id;
                $column = ['first_name', 'last_name', 'id'];
                $id = $data->customer_id;
                $memberData = memberFieldData($column, $id, 'id')->toArray();
                $memberName = $memberData ? $memberData[0]['first_name'] . " " . $memberData[0]['last_name'] : "N/A";
            }
            $plan_name = getPlanDetail($data->plan_id)->name;
        }
        if ($value->sub_type == 142 && $freshExpenseData) {
            $memberName = $freshExpenseData->party_name;
            $memberAccount = $freshExpenseData['advices']->voucher_number;
            $memberId = $freshExpenseData->bill_number;
        }
        if ($value->sub_type == 139 && !empty($rentPaymentDetail['rentLib'])) {
            $memberName = $rentPaymentDetail['rentLib']->owner_name;
            $rent = isset($rentPaymentDetail['rentLib']->rent_type) ? $rentPaymentDetail['rentLib']->rent_type : null;
            $rent_type = getAcountHead($rent)??null;
        }
        $data = [
            'memberAccount' => $memberAccount ?? 'N/A',
            'memberName' => $memberName??'N/A',
            'memberId' => $memberId ?? 'N/A',
            'associate_name' =>  'N/A',
            'associateno' => $associateno ??'N/A',
         ];
         return $data;
    }
    private function getSalaryDetails($value)
    {
        $memberName = ($value->load('salaryPayment'))? $value->salaryPayment->salary_employee->employee_name : '';
        $memberId = ($value->load('salaryPayment'))? $value->salaryPayment->salary_employee->employee_code : '';
        
        $data = [
           'memberAccount' => 'N/A',
           'memberName' => $memberName ?? 'N/A',
           'memberId' =>$memberId ?? 'N/A',
           'associate_name' =>'N/A',
        ];
        return $data;
    }
    private function getVoucherDetails($value)
    {
        // $rvoucher = $value->type_transaction_id ? $value->load('receivedVoucher') : '' ;
        // $rvoucherba = $rvoucher->receive_bank_ac_id ?  $rvoucher->load('samraddhbankaccount') : '';
        // $rvouchersb = $rvoucher->load('samraddhBank') ?? '';
        $voucherDetail = $value->load('receivedVoucher') ? $value->receivedVoucher : '';
        if ($value->sub_type == 143) {
            $data = getEmployeeData($value->type_id);
            if (isset($data->employee_name)) {
                $memberName = $data->employee_name;
                $memberId = $data->employee_code;
                $memberAccount = $memberId;
            }
        } elseif ($voucherDetail !== '' && in_array($voucherDetail->type, [1, 2, 3])) {
            if ($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2) {
                $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
                $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                if (isset($bankAccount)) {
                    $memberAccount .= '(' . $bankAccount->bank_name . ')';
                }
            }
            if ($voucherDetail->type == 3) {
                $memberId = getEmployeeData($voucherDetail->employee_id)->employee_code;
            }
            if ($voucherDetail->type == 4) {
                $memberAccount = getSamraddhBankAccountId($voucherDetail->bank_ac_id)->account_no;
            }
            if ($voucherDetail->type == 5 && isset($voucherDetail)) {
                $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id);
                if (isset($memberAccount->account_no)) {
                    $memberAccount = $memberAccount->account_no;
                }
                $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
                if (isset($bankAccount)) {
                    $memberAccount .= '(' . $bankAccount->bank_name . ')';
                }
            }
        }
        $data = [
            'memberAccount' => $memberAccount??'N/A',
            'memberName' => $memberName??'N/A',
            'memberId' =>$memberId??'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function getAccountHeaddetails($value)
    {
        $memberName = $value->load('AccountHeads')->sub_head; 
        $data = [
            'memberAccount' => 'N/A',
            'memberName' => $memberName,
            'memberId' =>'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function getCompanyBoundDetails($value)
    {
        if ($value->sub_type == 301) {
            $detail = $value->load('companybound')  ? $value->companybound : '';
            if ($detail) {
                $memberAccount = $detail->fd_no;
                $memberName = $detail->bank_name;
            }
        } elseif ($value->sub_type == 302 || $value->sub_type == 303) {
            $detail = $value->load('companyboundtransactions') ? $value->companyboundtransactions : '';
            if ($detail) {
                $record = getCompanyBoundDetail($detail->bound_id);
                if ($record) {
                    $memberAccount = $record->fd_no;
                    $memberName = $record->bank_name;
                }
            }
        }
        $data = [
            'memberAccount' => $memberAccount??'N/A',
            'memberName' => $memberName,
            'memberId' =>'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function getMemberDetails($value)
    {
        $custom = customGetMemberData($value->member_id);
        $data = [
            'memberAccount' => $custom->account_number??'N/A',
            'memberName' => $custom ? $custom->first_name . ' ' . $custom->last_name??'' : 'N/A',
            'memberId' =>$custom->member_id??'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function getExpenseDetails($value)
    {
        $record = $value->load('expense') ? $value->expense: '';
        $mainHead = getAcountHeadData($record->account_head_id);
            if (isset($record->bill_no)) {
                $memberAccount = 'Bill No.' . $record->bill_no;
            }
            $name = $value->load('billExpense') ? $value->billExpense: '';
            if (isset($name->party_name)) {
                $memberName = $name->party_name;
            }
            if (isset($record->account_head_id) && $record->sub_head1 && $record->sub_head2) {                
                $subHead = getAcountHeadData($record->sub_head1);
                $subHead2 = getAcountHeadData($record->sub_head2);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead . '/' . $subHead2;
            } elseif (isset($record->account_head_id) && $record->sub_head1) {               
                $subHead = getAcountHeadData($record->sub_head1);
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead . '/' . $subHead;
            } elseif (isset($record->account_head_id)) {               
                $plan_name = 'INDIRECT EXPENSE /' . $mainHead;
            }
        $data = [
            'memberAccount' => $memberAccount??'N/A',
            'memberName' => $memberName??'',
            'memberId' =>'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function getBankingLedgerDetails($value)
    {
        $record = $value->load('bankingLedger') ? $value->bankingLedger : '';
        if ($record && isset($record->vendor_type)) {
            $vendorTypeMapping = [
                0 => ['model' => RentLiability::class, 'name_field' => 'owner_name', 'id_field' => 'employee_code'],
                1 => ['model' => Employee::class, 'name_field' => 'employee_name', 'id_field' => 'employee_code'],
                2 => ['model' => Member::class, 'name_field' => 'first_name', 'id_field' => 'associate_no'],
                3 => ['model' => Vendor::class, 'name_field' => 'name', 'id_field' => null],
                4 => ['model' => Vendor::class, 'name_field' => 'name', 'id_field' => null],
                5 => ['model' => Vendor::class, 'name_field' => 'name', 'id_field' => null],
            ];
            $vendorType = $record->vendor_type;
            if (isset($vendorTypeMapping[$vendorType])) {
                $model = $vendorTypeMapping[$vendorType]['model'];
                $nameField = $vendorTypeMapping[$vendorType]['name_field'];
                $idField = $vendorTypeMapping[$vendorType]['id_field'];
                $vendorDetail = $model::where('id', $record->vendor_type_id)->first();
                if ($vendorDetail) {
                    $memberName = $vendorDetail->$nameField;
                    $memberId = $idField ? $vendorDetail->$idField : null;
                }
            }
        }
        $data = [
            'memberAccount' => 'N/A',
            'memberName' => $memberName??'',
            'memberId' =>$memberId??'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function getVendorDetails($value)
    {
        $vendor = getVendorDetail($value->type_id);
        if ($value->sub_type == 271) {
            $data2 = getVendorBillDetail($value->type_transaction_id);
            $memberName = $vendor->company_name . ' (' . $vendor->name . ')';
            $memberAccount = $data2->bill_number;
        }
        if ($value->sub_type == 272) {
            $data2 = getVendorBillPaymentDetail($value->type_transaction_id);
            $data3 = getVendorBillDetail($data2->vendor_bill_id);
            $memberName = $vendor->company_name . ' (' . $vendor->name . ')';
            $memberAccount = $data3->bill_number;
        }
        if ($value->sub_type == 275) {
            $memberName = $vendor->company_name . ' (' . $vendor->name . ')';
        }
        $data = [
            'memberAccount' => $memberAccount??'N/A',
            'memberName' => $memberName??'',
            'memberId' =>'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    private function savingAccountmemberDetails($value)
    {
        if ($value->sub_type == 291 || $value->sub_type == 295) {
            $ssbdata = getSavingAccountMemberId($value->type_id);
        } elseif ($value->sub_type == 292) {
            $ssbdata = getDebitCardDetail($value->type_transaction_id);
        }
        if (isset($ssbdata)) {
            $memberAccount = $ssbdata->account_no;
            $memberId = $ssbdata->account_no;
            $memberName = getMemberDetails($ssbdata->member_id)->first_name . ' ' . getMemberDetails($ssbdata->member_id)->last_name;
        }
        $data = [
            'memberAccount' => $memberAccount??'N/A',
            'memberName' => $memberName??'',
            'memberId' =>$memberId??'N/A',
            'associate_name' => 'N/A',
            'associateno' =>'N/A',
         ];
         return $data;
    }
    public function balancesheettraitfunction($value,$isDaybook = NULL)
    {
        $plan_name = '';
        $rent_type = '';
        $tags = '';
        $transactionType = $value->transactionType['title'] ?? 'N/A';
        $data = ($value->type === 3)
        ? $this->getInvestmentDetails($value)
        : (($value->type === 4)
            ? $this->getSavingDetails($value)
            : (($value->type === 17)
                ? $this->getLoanFrombankDetails($value)
                : (($value->type === 10)
                    ? $this->getRentDetails($value)
                    : ((($value->type === 2) || ($value->type === 9 )) 
                        ? $this->getAssociateDetails($value)
                        :((($value->type === 7) || ($value->type == 18))
                            ? $this->getBranhcToHoDetails($value) 
                            : (($value->type === 8) 
                                ? $this->getBankToBankDetails($value)
                                : ((($value->type === 11) || ($value->type === 13))
                                    ? $this->getDemandDetails($value)
                                    : (($value->type === 12) 
                                        ? $this->getSalaryDetails($value)
                                        : (($value->type === 14)
                                            ? $this->getVoucherDetails($value)
                                            : ((($value->type === 15) || ($value->type === 16))
                                                ? $this->getAccountHeaddetails($value)
                                                : (($value->type === 30)
                                                    ?  $this->getCompanyBoundDetails($value)
                                                    : (($value->type === 21)
                                                        ? $this->getMemberDetails($value)
                                                        : (($value->type === 20)
                                                            ? $this->getExpenseDetails($value)
                                                            : (($value->type === 26) 
                                                                ? $this->getBankingLedgerDetails($value)
                                                                : (($value->type === 27)
                                                                    ? $this->getVendorDetails($value)
                                                                    : (($value->type === 29)
                                                                        ? $this->savingAccountmemberDetails($value)
                                                                        : (($value->type === 5) 
                                                                        ?$this->getLoanDetails($value)
                                                                        : 
                                                                        $this->getMemberDetails($value))
                                                                    )
                                                                )
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        )
    ;
        return [
            'memberName' => ucwords($data['memberName']) ?? 'N/A', 
            'memberAccount' => $data['memberAccount']  ?? 'N/A', 
            'type' => $transactionType  ?? 'N/A', 
            'plan_name' => $plan_name  ?? 'N/A', 
            'rent_type' => $rent_type  ?? 'N/A', 
            'memberId' => $value->id  ?? 'N/A', 
            'associate_name' =>  $data['associate_name']  ?? 'N/A', 
            'associateno' =>  $data['associateno']  ?? 'N/A', 
            'tag' => $tags  ?? 'N/A'
        ];
    }
    private function getSavingDetails($value)
    {
        $memberAccount = $value->load('savingAccount') ? isset($value->savingAccount) ? $value->savingAccount->account_no  : '' :'';
        $memberId = ($value->load('memberCompany')) ? (isset($value->member_id) ? $value->memberCompany->member_id : '') : '';
        $memberName = isset($value->member_id) && $value->memberCompany->load('member') ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : '';
        $associate_name =($value->load('associateMember')) ? $value->associateMember ? (isset($value->associate_id) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : '') : '' : '';
        $associateno = ($value->load('associateMember')) ? ((isset( $value->associateMember->associate_no)) ? $value->associateMember->associate_no : ''): '';
         $data = [
            'memberAccount' => $memberAccount??'N/A',
            'memberId' => $memberId,
            'memberName' => $memberName,
            'associate_name' => $associate_name,
            'associateno' => $associateno,
        ];
        return $data;
    }
    private function getInvestmentDetails($value)
    {
        $memberAccount = $value->load('member_investment') ? $value->member_investment->account_number  : 'N/A';
        $memberId = ($value->load('memberCompany')) ? (isset($value->member_id) ? $value->memberCompany->member_id : '') : '';
        $memberName = isset($value->member_id) && $value->memberCompany->load('member') ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : '';
        $associate_name =($value->load('associateMember')) ?  (isset($value->associate_id) ? $value->associateMember->first_name . ' ' . $value->associateMember->last_name : '') : '';
        $associateno = ($value->load('associateMember')) ? ((isset( $value->associateMember->associate_no)) ? $value->associateMember->associate_no : ''): '';
        $data = [
            'memberAccount' => $memberAccount,
            'memberId' => $memberId,
            'memberName' => $memberName,
            'associate_name' => $associate_name,
            'associateno' => $associateno,
        ];
        return $data;
    }

    private function getLoanDetails($value)
    {
        $loanArray = [5,52,53,57,511,513,515,519,523,525,527,528,529,530,531,532,533,534,535];
        if(in_array($value->sub_type,$loanArray))
        {
            $memberAccount = $value->load('loans') ? $value->loans->account_number  : 'N/A';
            $memberId = ($value->load('memberCompany')) ? (isset($value->member_id) ? $value->memberCompany->member_id : '') : '';
            $memberName = isset($value->member_id) && $value->memberCompany->load('member') ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : '';
            $associate_name =($value->load('loans.loanMemberCustom')) ?  (isset($value->type_id) ? $value->loans->loanMemberCustom->first_name . ' ' . $value->loans->loanMemberCustom->last_name : '') : '';
            $associateno = ($value->load('loans.loanMemberCustom')) ? ((isset( $value->loans->loanMemberCustom->associate_no)) ? $value->loans->loanMemberCustom->associate_no : ''): '';
        }
        else{
            $memberAccount = $value->load('grouploans') ? $value->grouploans->account_number  : 'N/A';
            $memberId = ($value->load('memberCompany')) ? (isset($value->member_id) ? $value->memberCompany->member_id : '') : '';
            $memberName = isset($value->member_id) && $value->memberCompany->load('member') ? $value->memberCompany->member->first_name . ' ' . $value->memberCompany->member->last_name : '';
            $associate_name =($value->load('grouploans.loanMemberAssociate')) ?  (isset($value->type_id) ? $value->grouploans->loanMemberAssociate->first_name . ' ' . $value->grouploans->loanMemberAssociate->last_name : '') : '';
            $associateno = ($value->load('grouploans.loanMemberAssociate')) ? ((isset( $value->grouploans->loanMemberAssociate->associate_no)) ? $value->grouploans->loanMemberAssociate->associate_no : ''): '';
        }
        
        $data = [
            'memberAccount' => $memberAccount,
            'memberId' => $memberId,
            'memberName' => $memberName,
            'associate_name' => $associate_name,
            'associateno' => $associateno,
        ];
        return $data;    
    }



}